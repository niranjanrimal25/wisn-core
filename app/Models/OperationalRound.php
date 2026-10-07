<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OperationalRound extends Model
{
    public const SHIFT_MIDNIGHT = 'midnight';
    public const SHIFT_DAY = 'day';
    public const SHIFT_NIGHT = 'night';

    public const SHIFTS = [
        self::SHIFT_MIDNIGHT => 'Midnight census (12:00 AM)',
        self::SHIFT_DAY => 'Day handover (7:00 AM)',
        self::SHIFT_NIGHT => 'Night handover (7:00 PM)',
    ];

    public const STAFFING_SHIFTS = [
        self::SHIFT_DAY => 'Day shift (7:00 AM)',
        self::SHIFT_NIGHT => 'Night shift (7:00 PM)',
    ];

    protected $fillable = [
        'census_date',
        'shift_code',
        'observed_at',
        'captured_at',
        'entered_by',
        'notes',
    ];

    protected $casts = [
        'census_date' => 'date',
        'observed_at' => 'datetime',
        'captured_at' => 'datetime',
    ];

    public function snapshots(): HasMany
    {
        return $this->hasMany(DepartmentOperationalSnapshot::class);
    }

    public function recommendations(): HasMany
    {
        return $this->hasMany(MobilizationRecommendation::class);
    }

    public function enteredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'entered_by');
    }
}
