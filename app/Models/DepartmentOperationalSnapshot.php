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
        'high_acuity_patient_count',
        'on_duty_staff',
        'required_on_duty_staff',
        'notes',
    ];

    protected $casts = [
        'patient_count' => 'integer',
        'high_acuity_patient_count' => 'integer',
        'on_duty_staff' => 'integer',
        'required_on_duty_staff' => 'integer',
    ];

    public function operationalRound(): BelongsTo
    {
        return $this->belongsTo(OperationalRound::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }
}
