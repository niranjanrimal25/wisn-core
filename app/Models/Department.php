<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Department extends Model
{
    use HasFactory;

    public const OPERATIONAL_UNIT_TYPES = [
        'general_ward' => 'General inpatient ward',
        'pediatric_ward' => 'Pediatric ward',
        'maternity_ward' => 'Maternity / obstetric ward',
        'surgical_ward' => 'Surgical ward',
        'icu' => 'Intensive Care Unit (ICU)',
        'picu' => 'Pediatric Intensive Care Unit (PICU)',
        'nicu' => 'Neonatal Intensive Care Unit (NICU)',
        'emergency_inpatient' => 'Emergency admitted / observation beds',
        'other_inpatient' => 'Other inpatient nursing unit',
        'outpatient' => 'Outpatient Department (excluded from Operations)',
    ];

    protected $guarded = [];

    public function isOperationalInpatientUnit(): bool
    {
        return $this->operational_unit_type !== 'outpatient';
    }

    public function activities()
    {
        return $this->hasMany(WorkloadActivity::class);
    }

    public function operationalSnapshots()
    {
        return $this->hasMany(DepartmentOperationalSnapshot::class);
    }

    protected function availableWorkingTimeHours(): Attribute
    {
        return Attribute::get(fn () =>
            ($this->working_days_per_year
                - $this->public_holidays
                - $this->annual_leave_days
                - $this->sick_leave_days
                - $this->training_days
            ) * $this->working_hours_per_day
        );
    }
}
