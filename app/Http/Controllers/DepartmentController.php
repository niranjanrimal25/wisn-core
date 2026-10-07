<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DepartmentController extends Controller
{
    private function validationRules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'max:255'],
            'operational_unit_type' => ['required', 'string', Rule::in(array_keys(Department::OPERATIONAL_UNIT_TYPES))],
            'current_staff' => ['required', 'integer', 'min:0'],
            'working_days_per_year' => ['required', 'integer', 'min:1', 'max:366'],
            'public_holidays' => ['required', 'integer', 'min:0', 'max:50'],
            'annual_leave_days' => ['required', 'integer', 'min:0', 'max:60'],
            'sick_leave_days' => ['required', 'integer', 'min:0', 'max:60'],
            'training_days' => ['required', 'integer', 'min:0', 'max:30'],
            'working_hours_per_day' => ['required', 'integer', 'min:4', 'max:24'],
        ];
    }

    public function index()
    {
        $departments = Department::withCount('activities')->get();

        return view('departments.index', compact('departments'));
    }

    public function create()
    {
        return view('departments.create', [
            'operationalUnitTypes' => Department::OPERATIONAL_UNIT_TYPES,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->validationRules());
        if ($validated['type'] === 'Outpatient') {
            $validated['operational_unit_type'] = 'outpatient';
        } elseif ($validated['type'] === 'Emergency') {
            $validated['operational_unit_type'] = 'emergency_inpatient';
        }

        $netDays = $validated['working_days_per_year']
            - $validated['public_holidays']
            - $validated['annual_leave_days']
            - $validated['sick_leave_days']
            - $validated['training_days'];

        if ($netDays <= 0) {
            return back()->withErrors([
                'working_days_per_year' => 'Total leave/holiday days must be less than working days per year. Computed net days: ' . $netDays,
            ])->withInput();
        }

        Department::create($validated);

        return redirect()->route('departments.index')->with('success', 'Department added successfully!');
    }

    public function show(Department $department) {}

    public function edit(Department $department)
    {
        return view('departments.edit', [
            'department' => $department,
            'operationalUnitTypes' => Department::OPERATIONAL_UNIT_TYPES,
        ]);
    }

    public function update(Request $request, Department $department)
    {
        $validated = $request->validate($this->validationRules());
        if ($validated['type'] === 'Outpatient') {
            $validated['operational_unit_type'] = 'outpatient';
        } elseif ($validated['type'] === 'Emergency') {
            $validated['operational_unit_type'] = 'emergency_inpatient';
        }

        $netDays = $validated['working_days_per_year']
            - $validated['public_holidays']
            - $validated['annual_leave_days']
            - $validated['sick_leave_days']
            - $validated['training_days'];

        if ($netDays <= 0) {
            return back()->withErrors([
                'working_days_per_year' => 'Total leave/holiday days must be less than working days per year. Computed net days: ' . $netDays,
            ])->withInput();
        }

        $department->update($validated);

        return redirect()->route('departments.index')->with('success', 'Department updated successfully!');
    }

    public function destroy(Department $department)
    {
        if ($department->operationalSnapshots()->exists()) {
            return redirect()->route('departments.index')->with(
                'error',
                'This department has operational history and cannot be deleted. Keep it to preserve past staffing snapshots and recommendations.'
            );
        }

        $department->delete();

        return redirect()->route('departments.index')->with('success', 'Department deleted permanently.');
    }
}
