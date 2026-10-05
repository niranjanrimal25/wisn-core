<?php

namespace Tests\Unit;

use App\Models\Department;
use App\Services\WisnCalculatorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WisnCalculatorServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_existing_annual_wisn_calculation_keeps_its_expected_results(): void
    {
        $department = Department::create([
            'name' => 'WISN regression ward',
            'type' => 'Inpatient - Standard',
            'current_staff' => 2,
            'working_days_per_year' => 260,
            'public_holidays' => 13,
            'annual_leave_days' => 18,
            'sick_leave_days' => 12,
            'training_days' => 5,
            'working_hours_per_day' => 8,
        ]);

        $department->activities()->createMany([
            [
                'activity_name' => 'Annual clinical workload',
                'activity_type' => 'health_service',
                'time_standard_hours' => 1,
                'annual_volume' => 1696,
            ],
            [
                'activity_name' => 'Shift handover',
                'activity_type' => 'support',
                'time_standard_hours' => 1,
                'annual_volume' => null,
            ],
            [
                'activity_name' => 'Annual teaching activity',
                'activity_type' => 'additional',
                'time_standard_hours' => 0.5,
                'annual_volume' => 1696,
            ],
        ]);

        $result = app(WisnCalculatorService::class)
            ->calculateDepartmentStaffing($department->load('activities'));

        $this->assertSame(1696, $result['awt_hours']);
        $this->assertSame(1.0, $result['health_service_fte']);
        $this->assertSame(1.143, $result['support_allowance_multiplier']);
        $this->assertSame(0.5, $result['aaf_fte']);
        $this->assertSame(1.64, $result['total_required_staff']);
        $this->assertSame(1.22, $result['wisn_ratio']);
        $this->assertSame('surplus', $result['status']);
    }
}
