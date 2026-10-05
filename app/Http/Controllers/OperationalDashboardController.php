<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\MobilizationRecommendation;
use App\Models\OperationalRound;

class OperationalDashboardController extends Controller
{
    public function index()
    {
        $departments = Department::query()->orderBy('name')->get();
        $latestRound = OperationalRound::query()
            ->with([
                'enteredBy',
                'snapshots.department',
                'recommendations.fromDepartment',
                'recommendations.toDepartment',
                'recommendations.reviewer',
            ])
            ->orderByDesc('captured_at')
            ->orderByDesc('id')
            ->first();

        $snapshots = $latestRound
            ? $latestRound->snapshots->keyBy('department_id')
            : collect();
        $recommendations = $latestRound
            ? $latestRound->recommendations->sortBy('id')->values()
            : collect();

        $staleAfterMinutes = max(1, (int) config('operations.stale_after_minutes', 30));
        $currentDepartmentIds = $departments->modelKeys();
        sort($currentDepartmentIds);
        $snapshotDepartmentIds = $latestRound
            ? $latestRound->snapshots->pluck('department_id')->map(fn ($id) => (int) $id)->all()
            : [];
        sort($snapshotDepartmentIds);
        $isComplete = $latestRound && $currentDepartmentIds === $snapshotDepartmentIds;
        $isStale = $latestRound
            ? (!$isComplete || $latestRound->captured_at->lte(now()->subMinutes($staleAfterMinutes)))
            : false;

        $summary = [
            'patients' => $snapshots->sum('patient_count'),
            'high_acuity' => $snapshots->sum('high_acuity_patient_count'),
            'on_duty' => $snapshots->sum('on_duty_staff'),
            'required' => $snapshots->sum('required_on_duty_staff'),
            'departments_below_target' => $snapshots->filter(fn ($snapshot) =>
                $snapshot->on_duty_staff < $snapshot->required_on_duty_staff)->count(),
        ];

        $pendingCount = $recommendations
            ->where('status', MobilizationRecommendation::STATUS_PENDING)
            ->count();

        return view('operations.index', compact(
            'departments',
            'latestRound',
            'snapshots',
            'recommendations',
            'summary',
            'pendingCount',
            'isStale',
            'isComplete',
            'staleAfterMinutes',
        ));
    }
}
