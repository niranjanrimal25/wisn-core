<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Services\WisnCalculatorService;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    public function generate(WisnCalculatorService $wisnService)
    {
        $departments = Department::with('activities')->get();
        $departmentResults = [];
        $totalCurrentStaff = 0;
        $totalRequiredStaff = 0;

        foreach ($departments as $dept) {
            $calculation = $wisnService->calculateDepartmentStaffing($dept);
            $calculation['awt_breakdown'] = [
                'working_days'  => $dept->working_days_per_year,
                'public_hols'   => $dept->public_holidays,
                'annual_leave'  => $dept->annual_leave_days,
                'sick_leave'    => $dept->sick_leave_days,
                'training'      => $dept->training_days,
                'hours_per_day' => $dept->working_hours_per_day,
            ];
            $departmentResults[] = $calculation;
            $totalCurrentStaff += $calculation['current_staff'];
            $totalRequiredStaff += $calculation['total_required_staff'];
        }

        $facilityRatio = $totalRequiredStaff > 0
            ? round($totalCurrentStaff / $totalRequiredStaff, 2)
            : 0;

        $pdf = Pdf::loadView('reports.wisn-summary', [
            'departmentResults' => $departmentResults,
            'totalCurrentStaff' => $totalCurrentStaff,
            'totalRequiredStaff'=> round($totalRequiredStaff, 2),
            'facilityRatio'     => $facilityRatio,
            'date'              => now()->format('F j, Y'),
        ]);

        return $pdf->download('WISN_Facility_Staffing_Report.pdf');
    }
}