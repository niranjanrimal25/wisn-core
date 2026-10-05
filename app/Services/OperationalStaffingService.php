<?php

namespace App\Services;

use App\Models\Department;
use App\Models\DepartmentOperationalSnapshot;
use App\Models\MobilizationRecommendation;
use App\Models\OperationalRound;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Shift-level operational coverage calculations.
 *
 * This service intentionally does not call or modify WisnCalculatorService:
 * the operational inputs are point-in-time counts, not annual WISN volumes.
 */
class OperationalStaffingService
{
    /**
     * Persist one complete facility snapshot and make aggregate recommendations.
     * The required_on_duty_staff values are entered from locally approved
     * clinical staffing rules; this service does not invent patient:nurse ratios.
     *
     * @param array<int|string, array<string, mixed>> $departmentInputs
     */
    public function recordRound(array $departmentInputs, User $user, ?string $roundNotes = null): OperationalRound
    {
        return DB::transaction(function () use ($departmentInputs, $user, $roundNotes) {
            $now = now();

            // A new snapshot invalidates unreviewed recommendations based on older data.
            MobilizationRecommendation::query()
                ->where('status', MobilizationRecommendation::STATUS_PENDING)
                ->update([
                    'status' => MobilizationRecommendation::STATUS_SUPERSEDED,
                    'decision_note' => 'Superseded by a newer operational snapshot.',
                    'updated_at' => $now,
                ]);

            $round = OperationalRound::create([
                'captured_at' => $now,
                'entered_by' => $user->getKey(),
                'notes' => $roundNotes,
            ]);

            foreach ($departmentInputs as $departmentId => $input) {
                $round->snapshots()->create([
                    'department_id' => (int) $departmentId,
                    'patient_count' => (int) $input['patient_count'],
                    'high_acuity_patient_count' => (int) $input['high_acuity_patient_count'],
                    'on_duty_staff' => (int) $input['on_duty_staff'],
                    'required_on_duty_staff' => (int) $input['required_on_duty_staff'],
                    'notes' => $input['notes'] ?? null,
                ]);
            }

            $snapshots = $round->snapshots()->with('department')->get();
            $this->createRecommendations($round, $snapshots);

            return $round;
        });
    }

    /**
     * Approve an aggregate recommendation, while preserving the coverage floor
     * entered in the snapshot. This records a human decision; it does not assign
     * named staff or change either department's headcount.
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

            $approvedOutgoing = $round->recommendations()
                ->where('from_department_id', $source->department_id)
                ->where('status', MobilizationRecommendation::STATUS_APPROVED)
                ->sum('staff_count');

            $approvedIncoming = $round->recommendations()
                ->where('to_department_id', $destination->department_id)
                ->where('status', MobilizationRecommendation::STATUS_APPROVED)
                ->sum('staff_count');

            $remainingAtSource = $source->on_duty_staff
                - $approvedOutgoing
                - $lockedRecommendation->staff_count;

            if ($remainingAtSource < $source->required_on_duty_staff) {
                throw ValidationException::withMessages([
                    'recommendation' => 'This move would take the sending department below its recorded required coverage.',
                ]);
            }

            $staffAtDestination = $destination->on_duty_staff
                + $approvedIncoming
                + $lockedRecommendation->staff_count;

            if ($staffAtDestination > $destination->required_on_duty_staff) {
                throw ValidationException::withMessages([
                    'recommendation' => 'This move would exceed the receiving department’s recorded staffing target.',
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
        $donors = $snapshots
            ->filter(fn (DepartmentOperationalSnapshot $snapshot) =>
                $snapshot->on_duty_staff > $snapshot->required_on_duty_staff)
            ->sortBy(fn (DepartmentOperationalSnapshot $snapshot) => $snapshot->department->name)
            ->values();

        $recipients = $snapshots
            ->filter(fn (DepartmentOperationalSnapshot $snapshot) =>
                $snapshot->on_duty_staff < $snapshot->required_on_duty_staff)
            ->sortBy(fn (DepartmentOperationalSnapshot $snapshot) => $snapshot->department->name)
            ->values();

        $remainingDeficits = $recipients->mapWithKeys(fn (DepartmentOperationalSnapshot $snapshot) => [
            $snapshot->department_id => $snapshot->required_on_duty_staff - $snapshot->on_duty_staff,
        ]);

        foreach ($donors as $donor) {
            $remainingSurplus = $donor->on_duty_staff - $donor->required_on_duty_staff;

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
                        '%s has %d staff above its entered coverage target; %s is %d staff below its entered target.',
                        $donor->department->name,
                        $donor->on_duty_staff - $donor->required_on_duty_staff,
                        $recipient->department->name,
                        $recipient->required_on_duty_staff - $recipient->on_duty_staff,
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
            ->orderByDesc('captured_at')
            ->orderByDesc('id')
            ->value('id');

        if ((int) $latestRoundId !== (int) $round->getKey()) {
            throw ValidationException::withMessages([
                'recommendation' => 'A newer operational snapshot exists. Review recommendations from the latest snapshot instead.',
            ]);
        }

        $currentDepartmentIds = Department::query()
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
                'recommendation' => 'The department list changed after this snapshot. Record a complete new snapshot before approving recommendations.',
            ]);
        }
    }

    private function ensureFresh(OperationalRound $round): void
    {
        $staleAfterMinutes = max(1, (int) config('operations.stale_after_minutes', 30));

        if ($round->captured_at->lte(now()->subMinutes($staleAfterMinutes))) {
            throw ValidationException::withMessages([
                'recommendation' => 'This snapshot is too old to approve safely. Record a fresh operational snapshot first.',
            ]);
        }
    }
}
