<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DepartmentOperationalSnapshot extends Model
{
    protected $fillable = [
        'operational_round_id',
        'department_id',
        'patient_count',
        'on_duty_staff',
        'staffing_standard_id',
        'calculated_required_staff',
        'notes',
    ];

    protected $casts = [
        'patient_count' => 'integer',
        'on_duty_staff' => 'integer',
        'staffing_standard_id' => 'integer',
        'calculated_required_staff' => 'integer',
    ];

    public function operationalRound(): BelongsTo
    {
        return $this->belongsTo(OperationalRound::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function staffingStandard(): BelongsTo
    {
        return $this->belongsTo(OperationalStaffingStandard::class, 'staffing_standard_id');
    }
}
