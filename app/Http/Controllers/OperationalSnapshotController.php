<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Services\OperationalStaffingService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class OperationalSnapshotController extends Controller
{
    public function store(Request $request, OperationalStaffingService $staffingService)
    {
        $departments = Department::query()->orderBy('name')->get();

        if ($departments->isEmpty()) {
            return redirect()->route('operations.index')
                ->with('error', 'Add at least one department before recording an operational snapshot.');
        }

        $validated = $request->validate([
            'departments' => ['required', 'array'],
            'departments.*.patient_count' => ['required', 'integer', 'min:0', 'max:100000'],
            'departments.*.high_acuity_patient_count' => ['required', 'integer', 'min:0', 'max:100000'],
            'departments.*.on_duty_staff' => ['required', 'integer', 'min:0', 'max:65535'],
            'departments.*.required_on_duty_staff' => ['required', 'integer', 'min:1', 'max:65535'],

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
                'departments' => 'The department list changed while this form was open. Reload the page and enter the latest figures for every department.',
            ]);
        }

        foreach ($validated['departments'] as $departmentId => $input) {
            if ((int) $input['high_acuity_patient_count'] > (int) $input['patient_count']) {
                throw ValidationException::withMessages([
                    "departments.{$departmentId}.high_acuity_patient_count" => 'High-acuity patients cannot exceed the total patient census.',
                ]);
            }
        }

        $inputs = [];
        foreach ($departments as $department) {
            $inputs[$department->id] = $validated['departments'][$department->id];
        }

        $staffingService->recordRound(
            $inputs,
            $request->user(),
            $validated['round_notes'] ?? null,
        );

        return redirect()->route('operations.index')
            ->with('success', 'Operational snapshot saved. Recommendations use the shift-level targets you entered and do not change WISN results or department headcounts.');
    }
}
