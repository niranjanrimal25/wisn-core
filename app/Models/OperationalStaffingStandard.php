<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OperationalStaffingStandard extends Model
{
    use HasFactory;

    protected $fillable = [
        'unit_type',
        'shift_code',
        'patients_per_nurse',
        'minimum_nurses_per_shift',
        'source_name',
        'source_version',
        'source_url',
        'effective_from',
        'effective_to',
        'is_active',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'patients_per_nurse' => 'decimal:2',
        'minimum_nurses_per_shift' => 'integer',
        'effective_from' => 'date',
        'effective_to' => 'date',
        'is_active' => 'boolean',
        'approved_at' => 'datetime',
    ];

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public static function currentFor(string $unitType, string $shiftCode, string $date): ?self
    {
        if ($shiftCode === OperationalRound::SHIFT_MIDNIGHT) {
            return null;
        }

        return static::query()
            ->where('unit_type', $unitType)
            ->where('shift_code', $shiftCode)
            ->where('is_active', true)
            ->whereNotNull('approved_by')
            ->whereNotNull('approved_at')
            ->whereDate('effective_from', '<=', $date)
            ->where(function ($query) use ($date) {
                $query->whereNull('effective_to')
                    ->orWhereDate('effective_to', '>=', $date);
            })
            ->orderByDesc('effective_from')
            ->orderByDesc('id')
            ->first();
    }
}
