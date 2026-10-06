<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\WorkloadActivity;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(AdminUserSeeder::class);

        WorkloadActivity::query()->delete();
        Department::query()->delete();

        $awt = [
            'working_days_per_year' => 260,
            'public_holidays'       => 13,
            'annual_leave_days'     => 18,
            'sick_leave_days'       => 12,
            'training_days'         => 5,
            'working_hours_per_day' => 8,
            // AWT = (260-13-18-12-5)*8 = 212*8 = 1,696 hrs/nurse/year
        ];

        // ICU — Expected WISN ratio ≈ 0.71 (Critical)
        $icu = Department::create(array_merge($awt, [
            'name'          => 'Intensive Care Unit (ICU)',
            'type'          => 'Inpatient - High Acuity',
            'current_staff' => 6,
        ]));
        $icu->activities()->createMany([
            ['activity_name' => 'Continuous patient monitoring & assessment', 'activity_type' => 'health_service', 'time_standard_hours' => 0.33, 'annual_volume' => 17520],
            ['activity_name' => 'Medication administration',                  'activity_type' => 'health_service', 'time_standard_hours' => 0.25, 'annual_volume' => 8760],
            ['activity_name' => 'IV line care & maintenance',                 'activity_type' => 'health_service', 'time_standard_hours' => 0.25, 'annual_volume' => 5840],
            ['activity_name' => 'Ventilator & equipment management',          'activity_type' => 'health_service', 'time_standard_hours' => 0.50, 'annual_volume' => 2920],
            ['activity_name' => 'Family communication & counseling',          'activity_type' => 'health_service', 'time_standard_hours' => 0.25, 'annual_volume' => 2920],
            ['activity_name' => 'Shift handover & briefing',                  'activity_type' => 'support',        'time_standard_hours' => 0.50, 'annual_volume' => null],
            ['activity_name' => 'Multidisciplinary ward rounds',              'activity_type' => 'support',        'time_standard_hours' => 1.00, 'annual_volume' => null],
        ]);

        // Emergency Department — Expected WISN ratio ≈ 0.91 (Borderline)
        $ed = Department::create(array_merge($awt, [
            'name'          => 'Emergency Department',
            'type'          => 'Emergency',
            'current_staff' => 7,
        ]));
        $ed->activities()->createMany([
            ['activity_name' => 'Patient triage & initial assessment', 'activity_type' => 'health_service', 'time_standard_hours' => 0.25, 'annual_volume' => 14600],
            ['activity_name' => 'Wound care & dressing',              'activity_type' => 'health_service', 'time_standard_hours' => 0.50, 'annual_volume' => 5475],
            ['activity_name' => 'Medication administration',          'activity_type' => 'health_service', 'time_standard_hours' => 0.17, 'annual_volume' => 10950],
            ['activity_name' => 'IV line insertion & management',     'activity_type' => 'health_service', 'time_standard_hours' => 0.33, 'annual_volume' => 3650],
            ['activity_name' => 'Patient stabilization & monitoring', 'activity_type' => 'health_service', 'time_standard_hours' => 0.50, 'annual_volume' => 2190],
            ['activity_name' => 'Shift handover',                     'activity_type' => 'support',        'time_standard_hours' => 0.50, 'annual_volume' => null],
            ['activity_name' => 'Documentation & records completion', 'activity_type' => 'support',        'time_standard_hours' => 1.00, 'annual_volume' => null],
        ]);

        // General Medical Ward — Expected WISN ratio ≈ 0.78 (Critical)
        $medWard = Department::create(array_merge($awt, [
            'name'          => 'General Medical Ward',
            'type'          => 'Inpatient - Standard',
            'current_staff' => 11,
        ]));
        $medWard->activities()->createMany([
            ['activity_name' => 'Comprehensive nursing assessment',        'activity_type' => 'health_service', 'time_standard_hours' => 0.50, 'annual_volume' => 16425],
            ['activity_name' => 'Medication administration rounds',        'activity_type' => 'health_service', 'time_standard_hours' => 0.25, 'annual_volume' => 24638],
            ['activity_name' => 'Wound dressing & care',                  'activity_type' => 'health_service', 'time_standard_hours' => 0.50, 'annual_volume' => 2464],
            ['activity_name' => 'Vital signs monitoring',                 'activity_type' => 'health_service', 'time_standard_hours' => 0.08, 'annual_volume' => 32850],
            ['activity_name' => 'Patient education & discharge planning', 'activity_type' => 'health_service', 'time_standard_hours' => 0.33, 'annual_volume' => 8213],
            ['activity_name' => 'Shift handover',                         'activity_type' => 'support',        'time_standard_hours' => 0.50, 'annual_volume' => null],
            ['activity_name' => 'Administrative documentation',           'activity_type' => 'support',        'time_standard_hours' => 0.50, 'annual_volume' => null],
        ]);

        // Surgical Ward — Expected WISN ratio ≈ 1.05 (Adequate)
        $surgWard = Department::create(array_merge($awt, [
            'name'          => 'Surgical Ward',
            'type'          => 'Surgical/OT',
            'current_staff' => 10,
        ]));
        $surgWard->activities()->createMany([
            ['activity_name' => 'Post-operative vital signs monitoring',      'activity_type' => 'health_service', 'time_standard_hours' => 0.08, 'annual_volume' => 30660],
            ['activity_name' => 'Surgical wound dressing changes',            'activity_type' => 'health_service', 'time_standard_hours' => 0.50, 'annual_volume' => 5110],
            ['activity_name' => 'Pain assessment & management',              'activity_type' => 'health_service', 'time_standard_hours' => 0.17, 'annual_volume' => 20440],
            ['activity_name' => 'Patient mobilization assistance',           'activity_type' => 'health_service', 'time_standard_hours' => 0.33, 'annual_volume' => 10220],
            ['activity_name' => 'Discharge preparation & patient education', 'activity_type' => 'health_service', 'time_standard_hours' => 0.50, 'annual_volume' => 2555],
            ['activity_name' => 'Shift handover & surgical briefing',        'activity_type' => 'support',        'time_standard_hours' => 0.50, 'annual_volume' => null],
            ['activity_name' => 'Surgical ward round attendance',            'activity_type' => 'support',        'time_standard_hours' => 1.00, 'annual_volume' => null],
        ]);
    }
}
