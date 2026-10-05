<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MobilizationRecommendation extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_DECLINED = 'declined';
    public const STATUS_SUPERSEDED = 'superseded';

    protected $fillable = [
        'operational_round_id',
        'from_department_id',
        'to_department_id',
        'staff_count',
        'status',
        'rationale',
        'reviewed_by',
        'reviewed_at',
        'decision_note',
    ];

    protected $casts = [
        'staff_count' => 'integer',
        'reviewed_at' => 'datetime',
    ];

    public function operationalRound(): BelongsTo
    {
        return $this->belongsTo(OperationalRound::class);
    }

    public function fromDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'from_department_id');
    }

    public function toDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'to_department_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
