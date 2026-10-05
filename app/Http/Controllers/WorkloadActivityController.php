<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\WorkloadActivity;
use Illuminate\Http\Request;

class WorkloadActivityController extends Controller
{
    private function validationRules(): array
    {
        return [
            'activity_name'       => 'required|string|max:255',
            'activity_type'       => 'required|in:health_service,support,additional',
            'time_standard_hours' => 'required|numeric|min:0.01',
            'annual_volume'       => 'nullable|integer|min:1|required_if:activity_type,health_service|required_if:activity_type,additional',
        ];
    }

    public function index(Department $department)
    {
        $activities = $department->activities;
        return view('activities.index', compact('department', 'activities'));
    }

    public function store(Request $request, Department $department)
    {
        $validated = $request->validate($this->validationRules());
        $department->activities()->create($validated);
        return redirect()->route('activities.index', $department->id)
            ->with('success', 'Activity added. WISN calculations updated.');
    }

    public function edit(Department $department, WorkloadActivity $activity)
    {
        return view('activities.edit', compact('department', 'activity'));
    }

    public function update(Request $request, Department $department, WorkloadActivity $activity)
    {
        $validated = $request->validate($this->validationRules());
        $activity->update($validated);
        return redirect()->route('activities.index', $department->id)
            ->with('success', 'Activity updated successfully.');
    }

    public function destroy(Department $department, WorkloadActivity $activity)
    {
        $activity->delete();
        return redirect()->route('activities.index', $department->id)
            ->with('success', 'Activity removed.');
    }
}
