<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Department;
use App\Services\WisnCalculatorService;

class DashboardController extends Controller
{
    protected $wisnService;

    // Inject your custom mathematical service
    public function __construct(WisnCalculatorService $wisnService)
    {
        $this->wisnService = $wisnService;
    }

    public function index()
    {
        // Get all departments and their associated workload activities
        $departments = Department::with('activities')->get();
        
        $departmentResults = [];
        $totalCurrentStaff = 0;
        $totalRequiredStaff = 0;

        // Process each department through the WHO WISN math engine
        foreach ($departments as $dept) {
            $calculation = $this->wisnService->calculateDepartmentStaffing($dept);
            $departmentResults[] = $calculation;
            
            $totalCurrentStaff += $calculation['current_staff'];
            $totalRequiredStaff += $calculation['total_required_staff'];
        }

        // Calculate the overall facility WISN ratio
        $facilityRatio = $totalRequiredStaff > 0 ? round($totalCurrentStaff / $totalRequiredStaff, 2) : 0;

        // Pass all this data to the Blade view
        return view('dashboard', [
            'departmentResults' => $departmentResults,
            'totalCurrentStaff' => $totalCurrentStaff,
            'totalRequiredStaff' => round($totalRequiredStaff, 2),
            'facilityRatio' => $facilityRatio
        ]);
    }
}