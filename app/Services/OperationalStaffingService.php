<?php

namespace App\Services;

use App\Models\Department;
use App\Models\DepartmentOperationalSnapshot;
use App\Models\MobilizationRecommendation;
use App\Models\OperationalRound;
use App\Models\OperationalStaffingStandard;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Inpatient point-in-time census and shift-level staffing calculations.
 *
 * This service intentionally does not call or modify WisnCalculatorService:
 * annual workload-based WISN and live shift coverage answer different questions.
 */
class OperationalStaffingService
{
    /**
     * Save one complete, directly observed census for a facility handover.
     * Census values are never derived from unrecorded admissions, discharges,
     * or transfers. Staffing requirements are calculated only when a current,
     * approved, effective unit-and-shift standard exists.
     *
     * @param array<int|string, array<string, mixed>> $departmentInputs
     */
    public function recordRound(
        array $departmentInputs,
        User $user,
        string $censusDate,
        string $shiftCode,
        ?string $roundNotes = null
    ): OperationalRound {
        $timezone = config('operations.timezone', 'Asia/Kathmandu');
        $observedTime = match ($shiftCode) {
            OperationalRound::SHIFT_MIDNIGHT => '00:00:00',
            OperationalRound::SHIFT_DAY => '07:00:00',
            OperationalRound::SHIFT_NIGHT => '19:00:00',
            default => throw ValidationException::withMessages([
                'shift_code' => 'Choose the midnight, day, or night census time.',
            ]),
        };
        $observedAt = Carbon::parse($censusDate . ' ' . $observedTime, $timezone)
            ->setTimezone(config('app.timezone', 'UTC'));
        $nowAtFacility = Carbon::now($timezone);
        if ($censusDate === $nowAtFacility->toDateString() && $observedAt->gt($nowAtFacility)) {
            throw ValidationException::withMessages([
                'shift_code' => 'This handover time has not happened yet. Record the census after observing that handover.',
            ]);
        }

        return DB::transaction(function () use (
            $departmentInputs,
            $user,
            $censusDate,
            $shiftCode,
            $roundNotes,
            $observedAt
        ) {
            $departments = Department::query()
                ->where('operational_unit_type', '!=', 'outpatient')
                ->orderBy('name')
                ->get();

            if ($departments->isEmpty()) {
                throw ValidationException::withMessages([
                    'departments' => 'Add at least one inpatient nursing unit before recording a census.',
                ]);
            }

            // Any newly recorded handover makes earlier unreviewed move suggestions stale.
            MobilizationRecommendation::query()
                ->where('status', MobilizationRecommendation::STATUS_PENDING)
                ->update([
                    'status' => MobilizationRecommendation::STATUS_SUPERSEDED,
                    'decision_note' => 'Superseded by a newer operational census snapshot.',
                    'updated_at' => now(),
                ]);

            $round = OperationalRound::create([
                'census_date' => $censusDate,
                'shift_code' => $shiftCode,
                'observed_at' => $observedAt,
                'captured_at' => now(),
                'entered_by' => $user->getKey(),
                'notes' => $roundNotes,
            ]);

            foreach ($departments as $department) {
                $input = $departmentInputs[$department->id];
                $patientCount = (int) $input['patient_count'];
                $isStaffedHandover = $shiftCode !== OperationalRound::SHIFT_MIDNIGHT;
                $onDutyStaff = $isStaffedHandover ? (int) $input['on_duty_staff'] : null;
                $standard = $isStaffedHandover
                    ? OperationalStaffingStandard::currentFor(
                        $department->operational_unit_type,
                        $shiftCode,
                        $censusDate
                    )
                    : null;

                $requiredStaff = null;
                if ($standard !== null) {
                    $ratio = (float) $standard->patients_per_nurse;
                    $ratioBasedRequirement = $ratio > 0 ? (int) ceil($patientCount / $ratio) : 0;
                    $requiredStaff = max((int) $standard->minimum_nurses_per_shift, $ratioBasedRequirement);
                }

                $round->snapshots()->create([
                    'department_id' => $department->id,
                    'patient_count' => $patientCount,
                    'on_duty_staff' => $onDutyStaff,
                    'staffing_standard_id' => $standard?->id,
                    'calculated_required_staff' => $requiredStaff,
                    'notes' => $input['notes'] ?? null,
                ]);
            }

            $snapshots = $round->snapshots()
                ->with(['department', 'staffingStandard'])
                ->get();

            if ($shiftCode !== OperationalRound::SHIFT_MIDNIGHT) {
                $this->createRecommendations($round, $snapshots);
            }

            return $round;
        });
    }

    /**
     * Record a human review of an aggregate mobilization possibility. This does
     * not assign named staff, change on-duty counts, or alter WISN headcounts.
     */
    public function approve(
        MobilizationRecommendation $recommendation,
        User $reviewer,
        ?string $decisionNote = null
    ): MobilizationRecommendation {
        return DB::transaction(function () use ($recommendation, $reviewer, $decisionNote) {
            $round = OperationalRound::query()
                ->whereKey($recommendation->operational_round_id)
                ->lockForUpdate()
                ->firstOrFail();

            $lockedRecommendation = MobilizationRecommendation::query()
                ->whereKey($recommendation->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $this->ensurePending($lockedRecommendation);
            $this->ensureLatestRound($round);
            $this->ensureFresh($round);

            $source = $round->snapshots()
                ->where('department_id', $lockedRecommendation->from_department_id)
                ->lockForUpdate()
                ->firstOrFail();
            $destination = $round->snapshots()
                ->where('department_id', $lockedRecommendation->to_department_id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($source->calculated_required_staff === null || $destination->calculated_required_staff === null) {
                throw ValidationException::withMessages([
                    'recommendation' => 'A current approved target is missing for one of these units; this suggestion cannot be approved.',
                ]);
            }

            $approvedOutgoing = $round->recommendations()
                ->where('from_department_id', $source->department_id)
                ->where('status', MobilizationRecommendation::STATUS_APPROVED)
                ->sum('staff_count');

            $approvedIncoming = $round->recommendations()
                ->where('to_department_id', $destination->department_id)
                ->where('status', MobilizationRecommendation::STATUS_APPROVED)
                ->sum('staff_count');

            $remainingAtSource = (int) $source->on_duty_staff
                - (int) $approvedOutgoing
                - (int) $lockedRecommendation->staff_count;

            if ($remainingAtSource < (int) $source->calculated_required_staff) {
                throw ValidationException::withMessages([
                    'recommendation' => 'This proposed move would take the sending unit below its calculated requirement.',
                ]);
            }

            $staffAtDestination = (int) $destination->on_duty_staff
                + (int) $approvedIncoming
                + (int) $lockedRecommendation->staff_count;

            if ($staffAtDestination > (int) $destination->calculated_required_staff) {
                throw ValidationException::withMessages([
                    'recommendation' => 'This proposed move would exceed the receiving unit’s calculated requirement.',
                ]);
            }

            $lockedRecommendation->forceFill([
                'status' => MobilizationRecommendation::STATUS_APPROVED,
                'reviewed_by' => $reviewer->getKey(),
                'reviewed_at' => now(),
                'decision_note' => $decisionNote,
            ])->save();

            return $lockedRecommendation->fresh([
                'fromDepartment',
                'toDepartment',
                'reviewer',
            ]);
        });
    }

    /** Record a decline without changing the operational snapshot. */
    public function decline(
        MobilizationRecommendation $recommendation,
        User $reviewer,
        ?string $decisionNote = null
    ): MobilizationRecommendation {
        return DB::transaction(function () use ($recommendation, $reviewer, $decisionNote) {
            OperationalRound::query()
                ->whereKey($recommendation->operational_round_id)
                ->lockForUpdate()
                ->firstOrFail();

            $lockedRecommendation = MobilizationRecommendation::query()
                ->whereKey($recommendation->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $this->ensurePending($lockedRecommendation);

            $lockedRecommendation->forceFill([
                'status' => MobilizationRecommendation::STATUS_DECLINED,
                'reviewed_by' => $reviewer->getKey(),
                'reviewed_at' => now(),
                'decision_note' => $decisionNote,
            ])->save();

            return $lockedRecommendation->fresh([
                'fromDepartment',
                'toDepartment',
                'reviewer',
            ]);
        });
    }

    /** @param Collection<int, DepartmentOperationalSnapshot> $snapshots */
    private function createRecommendations(OperationalRound $round, Collection $snapshots): void
    {
        $eligibleSnapshots = $snapshots->filter(fn (DepartmentOperationalSnapshot $snapshot) =>
            $snapshot->on_duty_staff !== null && $snapshot->calculated_required_staff !== null
        );

        $donors = $eligibleSnapshots
            ->filter(fn (DepartmentOperationalSnapshot $snapshot) =>
                $snapshot->on_duty_staff > $snapshot->calculated_required_staff)
            ->sortBy(fn (DepartmentOperationalSnapshot $snapshot) => $snapshot->department->name)
            ->values();

        $recipients = $eligibleSnapshots
            ->filter(fn (DepartmentOperationalSnapshot $snapshot) =>
                $snapshot->on_duty_staff < $snapshot->calculated_required_staff)
            ->sortBy(fn (DepartmentOperationalSnapshot $snapshot) => $snapshot->department->name)
            ->values();

        $remainingDeficits = $recipients->mapWithKeys(fn (DepartmentOperationalSnapshot $snapshot) => [
            $snapshot->department_id => $snapshot->calculated_required_staff - $snapshot->on_duty_staff,
        ]);

        foreach ($donors as $donor) {
            $remainingSurplus = $donor->on_duty_staff - $donor->calculated_required_staff;

            foreach ($recipients as $recipient) {
                $deficit = (int) $remainingDeficits->get($recipient->department_id, 0);

                if ($remainingSurplus <= 0) {
                    break;
                }
                if ($deficit <= 0) {
                    continue;
                }

                $staffCount = min($remainingSurplus, $deficit);
                $round->recommendations()->create([
                    'from_department_id' => $donor->department_id,
                    'to_department_id' => $recipient->department_id,
                    'staff_count' => $staffCount,
                    'status' => MobilizationRecommendation::STATUS_PENDING,
                    'rationale' => sprintf(
                        '%s has %d nurse(s) above its configured target (%s patient(s) per nurse); %s is %d nurse(s) below its configured target (%s patient(s) per nurse). Review locally before acting.',
                        $donor->department->name,
                        $donor->on_duty_staff - $donor->calculated_required_staff,
                        $donor->staffingStandard->patients_per_nurse,
                        $recipient->department->name,
                        $recipient->calculated_required_staff - $recipient->on_duty_staff,
                        $recipient->staffingStandard->patients_per_nurse,
                    ),
                ]);

                $remainingSurplus -= $staffCount;
                $remainingDeficits->put($recipient->department_id, $deficit - $staffCount);
            }
        }
    }

    private function ensurePending(MobilizationRecommendation $recommendation): void
    {
        if ($recommendation->status !== MobilizationRecommendation::STATUS_PENDING) {
            throw ValidationException::withMessages([
                'recommendation' => 'This recommendation has already been reviewed or superseded.',
            ]);
        }
    }

    private function ensureLatestRound(OperationalRound $round): void
    {
        $latestRoundId = OperationalRound::query()
            ->whereNotNull('census_date')
            ->orderByDesc('captured_at')
            ->orderByDesc('id')
            ->value('id');

        if ((int) $latestRoundId !== (int) $round->getKey()) {
            throw ValidationException::withMessages([
                'recommendation' => 'A newer operational snapshot exists. Review recommendations from the latest snapshot instead.',
            ]);
        }

        $currentDepartmentIds = Department::query()
            ->where('operational_unit_type', '!=', 'outpatient')
            ->orderBy('id')
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $snapshotDepartmentIds = $round->snapshots()
            ->orderBy('department_id')
            ->pluck('department_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        if ($currentDepartmentIds !== $snapshotDepartmentIds) {
            throw ValidationException::withMessages([
                'recommendation' => 'The inpatient nursing-unit list changed after this snapshot. Record a complete new snapshot before approving recommendations.',
            ]);
        }

        if (!in_array($round->shift_code, array_keys(OperationalRound::STAFFING_SHIFTS), true) || $round->census_date === null) {
            throw ValidationException::withMessages([
                'recommendation' => 'Only a dated 7 AM or 7 PM staffing snapshot can be approved.',
            ]);
        }

        $roundSnapshots = $round->snapshots()->with('department')->get();
        foreach ($roundSnapshots as $snapshot) {
            $currentStandard = OperationalStaffingStandard::currentFor(
                $snapshot->department->operational_unit_type,
                $round->shift_code,
                $round->census_date->toDateString()
            );

            if ($currentStandard?->id !== $snapshot->staffing_standard_id) {
                throw ValidationException::withMessages([
                    'recommendation' => 'An applicable staffing standard changed after this snapshot. Record a new handover snapshot before reviewing moves.',
                ]);
            }
        }
    }

    private function ensureFresh(OperationalRound $round): void
    {
        $freshnessMinutes = max(1, (int) config('operations.recommendation_freshness_minutes', 30));
        $observedAt = $round->observed_at;

        if ($observedAt === null || $observedAt->lte(now(config('operations.timezone', 'Asia/Kathmandu'))->subMinutes($freshnessMinutes))) {
            throw ValidationException::withMessages([
                'recommendation' => 'This handover census is too old to approve safely. Record a fresh operational snapshot first.',
            ]);
        }
    }
}
