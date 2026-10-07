<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\MobilizationRecommendation;
use App\Models\OperationalRound;
use App\Models\OperationalStaffingStandard;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OperationalStaffingStandardController extends Controller
{
    public function index()
    {
        $standards = OperationalStaffingStandard::with('approver')
            ->orderBy('unit_type')
            ->orderBy('shift_code')
            ->orderByDesc('effective_from')
            ->get();

        $unitTypes = collect(Department::OPERATIONAL_UNIT_TYPES)
            ->except('outpatient')
            ->all();

        return view('operations.standards', [
            'standards' => $standards,
            'unitTypes' => $unitTypes,
            'shifts' => OperationalRound::STAFFING_SHIFTS,
        ]);
    }

    public function store(Request $request)
    {
        $allowedUnitTypes = array_diff(array_keys(Department::OPERATIONAL_UNIT_TYPES), ['outpatient']);
        $validated = $request->validate([
            'unit_type' => ['required', 'string', 'in:' . implode(',', $allowedUnitTypes)],
            'shift_code' => ['required', 'string', 'in:' . implode(',', array_keys(OperationalRound::STAFFING_SHIFTS))],
            'patients_per_nurse' => ['required', 'numeric', 'min:0.01', 'max:9999'],
            'minimum_nurses_per_shift' => ['required', 'integer', 'min:0', 'max:1000'],
            'source_name' => ['required', 'string', 'max:255'],
            'source_version' => ['nullable', 'string', 'max:100'],
            'source_url' => ['nullable', 'url', 'max:2048'],
            'effective_from' => ['required', 'date'],
            'effective_to' => ['nullable', 'date', 'after_or_equal:effective_from'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $isActive = $request->boolean('is_active');
        $standard = DB::transaction(function () use ($validated, $isActive, $request) {
            if ($isActive) {
                $this->prepareEffectivePeriod(
                    $validated['unit_type'],
                    $validated['shift_code'],
                    $validated['effective_from']
                );
                $this->supersedePendingRecommendations();
            }

            return OperationalStaffingStandard::create([
                'unit_type' => $validated['unit_type'],
                'shift_code' => $validated['shift_code'],
                'patients_per_nurse' => $validated['patients_per_nurse'],
                'minimum_nurses_per_shift' => $validated['minimum_nurses_per_shift'],
                'source_name' => $validated['source_name'],
                'source_version' => $validated['source_version'] ?? null,
                'source_url' => $validated['source_url'] ?? null,
                'effective_from' => $validated['effective_from'],
                'effective_to' => $validated['effective_to'] ?? null,
                'is_active' => $isActive,
                'approved_by' => $isActive ? $request->user()->id : null,
                'approved_at' => $isActive ? now() : null,
            ]);
        });

        return redirect()->route('operations.standards.index')->with(
            'success',
            $standard->is_active
                ? 'Target ratio saved and approved for use from its effective date.'
                : 'Target ratio saved as inactive. It will not be used for calculations until activated.'
        );
    }

    public function updateStatus(Request $request, OperationalStaffingStandard $standard)
    {
        $validated = $request->validate([
            'is_active' => ['required', 'boolean'],
        ]);

        $activate = (bool) $validated['is_active'];

        DB::transaction(function () use ($standard, $activate, $request) {
            $statusChanged = $standard->is_active !== $activate;

            if ($activate) {
                $this->prepareEffectivePeriod(
                    $standard->unit_type,
                    $standard->shift_code,
                    $standard->effective_from->toDateString(),
                    $standard->id
                );
            }

            $standard->is_active = $activate;
            $standard->approved_by = $activate ? $request->user()->id : $standard->approved_by;
            $standard->approved_at = $activate ? now() : $standard->approved_at;
            $standard->save();

            if ($statusChanged) {
                $this->supersedePendingRecommendations();
            }
        });

        return redirect()->route('operations.standards.index')->with(
            'success',
            $activate ? 'Target ratio activated.' : 'Target ratio deactivated.'
        );
    }

    private function supersedePendingRecommendations(): void
    {
        MobilizationRecommendation::query()
            ->where('status', MobilizationRecommendation::STATUS_PENDING)
            ->update([
                'status' => MobilizationRecommendation::STATUS_SUPERSEDED,
                'decision_note' => 'Superseded because operational staffing standards changed.',
                'updated_at' => now(),
            ]);
    }

    /**
     * Preserve prior approved targets for historical dates while closing any
     * earlier open-ended period on the day before a newly active version starts.
     */
    private function prepareEffectivePeriod(
        string $unitType,
        string $shiftCode,
        string $effectiveFrom,
        ?int $exceptId = null
    ): void {
        $query = OperationalStaffingStandard::query()
            ->where('unit_type', $unitType)
            ->where('shift_code', $shiftCode)
            ->where('is_active', true);

        if ($exceptId !== null) {
            $query->where('id', '!=', $exceptId);
        }

        $query->whereDate('effective_from', '<', $effectiveFrom)
            ->where(function ($query) use ($effectiveFrom) {
                $query->whereNull('effective_to')
                    ->orWhereDate('effective_to', '>=', $effectiveFrom);
            })
            ->update([
                'effective_to' => Carbon::parse($effectiveFrom)->subDay()->toDateString(),
                'updated_at' => now(),
            ]);

        $sameStart = OperationalStaffingStandard::query()
            ->where('unit_type', $unitType)
            ->where('shift_code', $shiftCode)
            ->where('is_active', true)
            ->whereDate('effective_from', $effectiveFrom);

        if ($exceptId !== null) {
            $sameStart->where('id', '!=', $exceptId);
        }

        $sameStart->update(['is_active' => false, 'updated_at' => now()]);
    }
}
