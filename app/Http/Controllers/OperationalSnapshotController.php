<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\OperationalRound;
use App\Services\OperationalStaffingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class OperationalSnapshotController extends Controller
{
    public function store(Request $request, OperationalStaffingService $staffingService)
    {
        $timezone = config('operations.timezone', 'Asia/Kathmandu');
        $today = Carbon::now($timezone)->toDateString();
        $shiftCode = (string) $request->input('shift_code');
        $departments = Department::query()
            ->where('operational_unit_type', '!=', 'outpatient')
            ->orderBy('name')
            ->get();

        if ($departments->isEmpty()) {
            return redirect()->route('operations.index')
                ->with('error', 'Add at least one inpatient nursing unit before recording a census.');
        }

        $staffRules = $shiftCode === OperationalRound::SHIFT_MIDNIGHT
            ? ['nullable']
            : ['required', 'integer', 'min:0', 'max:65535'];

        $validated = $request->validate([
            'census_date' => ['required', 'date_format:Y-m-d', 'before_or_equal:' . $today],
            'shift_code' => ['required', 'string', Rule::in(array_keys(OperationalRound::SHIFTS))],
            'departments' => ['required', 'array'],
            'departments.*.patient_count' => ['required', 'integer', 'min:0', 'max:100000'],
            'departments.*.on_duty_staff' => $staffRules,
            'departments.*.notes' => ['nullable', 'string', 'max:1000'],
            'round_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $submittedIds = collect(array_keys($validated['departments']))
            ->map(fn ($id) => (int) $id)
            ->sort()
            ->values()
            ->all();
        $expectedIds = $departments->modelKeys();
        sort($expectedIds);

        if ($submittedIds !== $expectedIds) {
            throw ValidationException::withMessages([
                'departments' => 'The inpatient-unit list changed while this form was open. Reload the page and enter the latest figures for every unit.',
            ]);
        }

        $inputs = [];
        foreach ($departments as $department) {
            $inputs[$department->id] = $validated['departments'][$department->id];
        }

        $staffingService->recordRound(
            $inputs,
            $request->user(),
            $validated['census_date'],
            $validated['shift_code'],
            $validated['round_notes'] ?? null,
        );

        return redirect()->route('operations.index', [
            'date' => $validated['census_date'],
            'shift' => $validated['shift_code'],
        ])->with(
            'success',
            'Observed census saved. Required staffing is calculated only where an approved, effective unit-and-shift target is configured.'
        );
    }
}
