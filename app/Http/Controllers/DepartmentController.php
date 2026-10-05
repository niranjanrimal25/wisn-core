<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    private function validationRules(): array
    {
        return [
            'name'                  => 'required|string|max:255',
            'type'                  => 'required|string|max:255',
            'current_staff'         => 'required|integer|min:0',
            'working_days_per_year' => 'required|integer|min:1|max:366',
            'public_holidays'       => 'required|integer|min:0|max:50',
            'annual_leave_days'     => 'required|integer|min:0|max:60',
            'sick_leave_days'       => 'required|integer|min:0|max:60',
            'training_days'         => 'required|integer|min:0|max:30',
            'working_hours_per_day' => 'required|integer|min:4|max:24',
        ];
    }

    public function index()
    {
        $departments = Department::withCount('activities')->get();
        return view('departments.index', compact('departments'));
    }

    public function create()
    {
        return view('departments.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->validationRules());
        $netDays = $validated['working_days_per_year']
            - $validated['public_holidays']
            - $validated['annual_leave_days']
            - $validated['sick_leave_days']
            - $validated['training_days'];
        if ($netDays <= 0) {
            return back()->withErrors(['working_days_per_year' =>
                'Total leave/holiday days must be less than working days per year. Computed net days: ' . $netDays])->withInput();
        }
        Department::create($validated);
        return redirect()->route('departments.index')->with('success', 'Department added successfully!');
    }

    public function show(Department $department) {}

    public function edit(Department $department)
    {
        return view('departments.edit', compact('department'));
    }

    public function update(Request $request, Department $department)
    {
        $validated = $request->validate($this->validationRules());
        $netDays = $validated['working_days_per_year']
            - $validated['public_holidays']
            - $validated['annual_leave_days']
            - $validated['sick_leave_days']
            - $validated['training_days'];
        if ($netDays <= 0) {
            return back()->withErrors(['working_days_per_year' =>
                'Total leave/holiday days must be less than working days per year. Computed net days: ' . $netDays])->withInput();
        }
        $department->update($validated);
        return redirect()->route('departments.index')->with('success', 'Department updated successfully!');
    }

    public function destroy(Department $department)
    {
        $department->delete();
        return redirect()->route('departments.index')->with('success', 'Department deleted permanently.');
    }
}
