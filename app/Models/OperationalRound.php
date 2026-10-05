<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OperationalRound extends Model
{
    protected $fillable = [
        'captured_at',
        'entered_by',
        'notes',
    ];

    protected $casts = [
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
