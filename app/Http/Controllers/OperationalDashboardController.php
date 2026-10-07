<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\MobilizationRecommendation;
use App\Models\OperationalRound;
use App\Models\OperationalStaffingStandard;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OperationalDashboardController extends Controller
{
    public function index(Request $request)
    {
        $timezone = config('operations.timezone', 'Asia/Kathmandu');
        $today = Carbon::now($timezone)->toDateString();
        $validated = $request->validate([
            'date' => ['nullable', 'date_format:Y-m-d', 'before_or_equal:' . $today],
            'shift' => ['nullable', 'string', Rule::in(array_keys(OperationalRound::SHIFTS))],
        ]);

        $selectedDate = $validated['date'] ?? $today;
        $selectedShift = $validated['shift'] ?? $this->defaultShift($timezone);
        $departments = Department::query()
            ->where('operational_unit_type', '!=', 'outpatient')
            ->orderBy('name')
            ->get();

        $rounds = OperationalRound::query()
            ->whereDate('census_date', $selectedDate)
            ->with([
                'enteredBy',
                'snapshots.department',
                'snapshots.staffingStandard',
                'recommendations.fromDepartment',
                'recommendations.toDepartment',
                'recommendations.reviewer',
            ])
            ->orderByDesc('captured_at')
            ->orderByDesc('id')
            ->get();

        // If a handover was corrected, the most recently recorded revision is shown.
        $roundsByShift = $rounds
            ->groupBy('shift_code')
            ->map(fn ($shiftRounds) => $shiftRounds->first());

        $snapshotsByShift = [];
        foreach (array_keys(OperationalRound::SHIFTS) as $shiftCode) {
            $round = $roundsByShift->get($shiftCode);
            $snapshotsByShift[$shiftCode] = $round
                ? $round->snapshots->keyBy('department_id')
                : collect();
        }

        $selectedRound = $roundsByShift->get($selectedShift);
        $snapshots = $selectedRound
            ? $selectedRound->snapshots->keyBy('department_id')
            : collect();
        $recommendations = $selectedRound
            ? $selectedRound->recommendations->sortBy('id')->values()
            : collect();

        $currentStandards = collect();
        if ($selectedShift !== OperationalRound::SHIFT_MIDNIGHT) {
            $unitTypes = $departments->pluck('operational_unit_type')->unique()->values();
            $currentStandards = OperationalStaffingStandard::query()
                ->whereIn('unit_type', $unitTypes)
                ->where('shift_code', $selectedShift)
                ->where('is_active', true)
                ->whereNotNull('approved_by')
                ->whereNotNull('approved_at')
                ->whereDate('effective_from', '<=', $selectedDate)
                ->where(function ($query) use ($selectedDate) {
                    $query->whereNull('effective_to')
                        ->orWhereDate('effective_to', '>=', $selectedDate);
                })
                ->orderByDesc('effective_from')
                ->orderByDesc('id')
                ->get()
                ->unique('unit_type')
                ->keyBy('unit_type');
        }

        $departmentIds = $departments->modelKeys();
        sort($departmentIds);
        $snapshotDepartmentIds = $selectedRound
            ? $selectedRound->snapshots->pluck('department_id')->map(fn ($id) => (int) $id)->all()
            : [];
        sort($snapshotDepartmentIds);
        $isComplete = $selectedRound !== null && $departmentIds === $snapshotDepartmentIds;

        $freshnessMinutes = max(1, (int) config('operations.recommendation_freshness_minutes', 30));
        $isStaffedShift = in_array($selectedShift, [OperationalRound::SHIFT_DAY, OperationalRound::SHIFT_NIGHT], true);
        $isStale = $isStaffedShift && $selectedRound !== null
            ? (!$isComplete || $selectedRound->observed_at === null || $selectedRound->observed_at->lte(Carbon::now($timezone)->subMinutes($freshnessMinutes)))
            : false;

        $summary = [
            'patients' => $snapshots->sum('patient_count'),
            'on_duty' => $snapshots->sum(fn ($snapshot) => $snapshot->on_duty_staff ?? 0),
            'required' => $snapshots->sum(fn ($snapshot) => $snapshot->calculated_required_staff ?? 0),
            'units_below_target' => $snapshots->filter(fn ($snapshot) =>
                $snapshot->on_duty_staff !== null
                && $snapshot->calculated_required_staff !== null
                && $snapshot->on_duty_staff < $snapshot->calculated_required_staff
            )->count(),
            'units_with_target' => $snapshots->filter(fn ($snapshot) => $snapshot->calculated_required_staff !== null)->count(),
            'unit_count' => $departments->count(),
        ];

        $pendingCount = $recommendations
            ->where('status', MobilizationRecommendation::STATUS_PENDING)
            ->count();

        $previousDate = Carbon::parse($selectedDate, $timezone)->subDay()->toDateString();
        $nextDate = Carbon::parse($selectedDate, $timezone)->addDay()->toDateString();

        return view('operations.index', compact(
            'departments',
            'selectedDate',
            'selectedShift',
            'roundsByShift',
            'snapshotsByShift',
            'selectedRound',
            'snapshots',
            'recommendations',
            'currentStandards',
            'summary',
            'pendingCount',
            'isStale',
            'isComplete',
            'freshnessMinutes',
            'previousDate',
            'nextDate',
            'today',
        ));
    }

    private function defaultShift(string $timezone): string
    {
        $hour = Carbon::now($timezone)->hour;

        if ($hour < 7) {
            return OperationalRound::SHIFT_MIDNIGHT;
        }
        if ($hour < 19) {
            return OperationalRound::SHIFT_DAY;
        }

        return OperationalRound::SHIFT_NIGHT;
    }
}
