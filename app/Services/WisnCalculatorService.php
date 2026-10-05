<?php

namespace App\Services;

use App\Models\Department;

class WisnCalculatorService
{
    public function calculateDepartmentStaffing(Department $department): array
    {
        $awt = $department->available_working_time_hours;
        $healthServiceFTE = 0;
        $supportTimePercentage = 0;
        $aafHours = 0;
        $breakdown = [];

        foreach ($department->activities as $activity) {

            if ($activity->activity_type === 'health_service') {
                // Standard Workload = AWT / Time Standard
                $standardWorkload = $awt / $activity->time_standard_hours;
                // Required Staff = Annual Volume / Standard Workload
                $requiredStaff = $activity->annual_volume / $standardWorkload;
                $healthServiceFTE += $requiredStaff;

                $breakdown[] = [
                    'activity'          => $activity->activity_name,
                    'type'              => 'Health Service',
                    'standard_workload' => round($standardWorkload),
                    'required_staff'    => round($requiredStaff, 2),
                ];

            } elseif ($activity->activity_type === 'support') {
                // Support activities consume a fraction of each shift
                $hoursPerShift = $department->working_hours_per_day ?: 8;
                $percentage = $activity->time_standard_hours / $hoursPerShift;
                $supportTimePercentage += $percentage;

                $breakdown[] = [
                    'activity'             => $activity->activity_name,
                    'type'                 => 'Support Allowance',
                    'allowance_percentage' => round($percentage * 100, 1) . '%',
                ];

            } elseif ($activity->activity_type === 'additional') {
                // Additional Allowance Factor: cross-category duties (e.g. teaching).
                // Counted as direct FTE: total hours spent / AWT.
                $aafHours += ($activity->annual_volume ?? 0) * $activity->time_standard_hours;

                $breakdown[] = [
                    'activity'    => $activity->activity_name,
                    'type'        => 'Additional (AAF)',
                    'total_hours' => round(($activity->annual_volume ?? 0) * $activity->time_standard_hours, 1),
                ];
            }
        }

        // Category Allowance Factor: inflates clinical FTE to cover support time lost
        $caf = 1;
        if ($supportTimePercentage > 0 && $supportTimePercentage < 1) {
            $caf = 1 / (1 - $supportTimePercentage);
        }

        // Additional Allowance Factor FTE
        $aafFte = $awt > 0 ? $aafHours / $awt : 0;

        // Total Required Staff = (Health Service FTE × CAF) + AAF FTE
        $totalRequiredStaff = ($healthServiceFTE * $caf) + $aafFte;

        // WISN Ratio = Current Staff / Required Staff
        $wisnRatio = $totalRequiredStaff > 0
            ? $department->current_staff / $totalRequiredStaff
            : 0;

        return [
            'department_id'                => $department->id,
            'department_name'              => $department->name,
            'department_type'              => $department->type,
            'current_staff'                => $department->current_staff,
            'awt_hours'                    => $awt,
            'health_service_fte'           => round($healthServiceFTE, 2),
            'support_allowance_multiplier' => round($caf, 3),
            'aaf_fte'                      => round($aafFte, 2),
            'total_required_staff'         => round($totalRequiredStaff, 2),
            'wisn_ratio'                   => round($wisnRatio, 2),
            'status'                       => $this->determineStatus($wisnRatio),
            'breakdown'                    => $breakdown,
        ];
    }

    private function determineStatus(float $ratio): string
    {
        if ($ratio <= 0)   return 'no_data';
        if ($ratio < 0.90) return 'critical';
        if ($ratio < 1.0)  return 'borderline';
        if ($ratio == 1.0) return 'adequate';
        return 'surplus';
    }
}
