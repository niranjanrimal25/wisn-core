<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;

class WisnDataSeeder extends Seeder
{
    public function run(): void
    {
        // --- SCENARIO 1: INTENSIVE CARE UNIT (ICU) ---
        // Based on Nepal tertiary care hospitals. 
        // AWT: 2000 hours (Derived from Nepal Gov leave policies: 30 days annual, 12 days sick, 6 days casual, plus Saturdays/Holidays)
        $icu = Department::create([
            'name' => 'Intensive Care Unit (ICU)',
            'type' => 'Inpatient - High Acuity',
            'operational_unit_type' => 'icu',
            'current_staff' => 14, // Common staffing level for a mid-sized ICU in Kathmandu
            'working_days_per_year' => 260,
            'public_holidays'       => 13,
            'annual_leave_days'     => 18,
            'sick_leave_days'       => 12,
            'training_days'         => 5,
            'working_hours_per_day' => 8,
        ]);

        // Add ICU Activities (Simulated based on Nepalese clinical routines)
        $icu->activities()->createMany([
            [
                'activity_name' => 'Continuous Hemodynamic Monitoring & Vitals',
                'activity_type' => 'health_service',
                'time_standard_hours' => 0.5, // 30 mins per assessment
                'annual_volume' => 38000,     // High frequency due to 1:1 or 1:2 patient ratios
            ],
            [
                'activity_name' => 'Intravenous (IV) Medication Administration',
                'activity_type' => 'health_service',
                'time_standard_hours' => 0.33, // 20 mins (preparation, administration, charting)
                'annual_volume' => 25000, 
            ],
            [
                'activity_name' => 'Ventilator & Airway Management (Suctioning)',
                'activity_type' => 'health_service',
                'time_standard_hours' => 0.25, // 15 mins
                'annual_volume' => 18000,
            ],
            [
                'activity_name' => 'Shift Handover & Clinical Notes Writing',
                'activity_type' => 'support',
                'time_standard_hours' => 1.0, // 1 hour per shift
                'annual_volume' => null, // Support activities don't use annual volume in WISN
            ]
        ]);

        // --- SCENARIO 2: GENERAL MEDICAL WARD ---
        $generalWard = Department::create([
            'name' => 'General Medical Ward',
            'type' => 'Inpatient - Standard',
            'operational_unit_type' => 'general_ward',
            'current_staff' => 18,
            'working_days_per_year' => 260,
            'public_holidays'       => 13,
            'annual_leave_days'     => 18,
            'sick_leave_days'       => 12,
            'training_days'         => 5,
            'working_hours_per_day' => 8,
        ]);

        // Add General Ward Activities
        $generalWard->activities()->createMany([
            [
                'activity_name' => 'Routine Oral/IV Medication Rounds',
                'activity_type' => 'health_service',
                'time_standard_hours' => 0.16, // roughly 10 mins per patient encounter
                'annual_volume' => 85000, // Very high volume due to larger bed capacity
            ],
            [
                'activity_name' => 'Wound Dressing & Care',
                'activity_type' => 'health_service',
                'time_standard_hours' => 0.33, // 20 mins
                'annual_volume' => 15000,
            ],
            [
                'activity_name' => 'Patient Discharge Preparation & Education',
                'activity_type' => 'health_service',
                'time_standard_hours' => 0.5, // 30 mins per discharge
                'annual_volume' => 4500,
            ],
            [
                'activity_name' => 'Ward Rounds with Consultant Doctors',
                'activity_type' => 'support',
                'time_standard_hours' => 1.5, // 1.5 hours per day assisting doctors
                'annual_volume' => null, 
            ]
        ]);
    }
}