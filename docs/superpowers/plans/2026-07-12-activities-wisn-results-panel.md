# Activities WISN Results Panel Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Show a live WISN calculation results panel at the bottom of the activities page so users can see FTE breakdown, ratio, and status without navigating to the dashboard.

**Architecture:** Inject `WisnCalculatorService` into `WorkloadActivityController::index()`, pass `$wisnResult` to the view, and render a full-width results panel below the existing 2-column grid. The panel only renders when the department has at least one activity.

**Tech Stack:** Laravel 10, Blade, Tailwind CSS, PHP 8.x

## Global Constraints

- No new routes, models, or migrations — this is a view/controller change only
- Follow existing Tailwind class patterns from `dashboard.blade.php` and `activities/index.blade.php`
- Colour coding must match dashboard: red=critical, amber=borderline, green=adequate, blue=surplus
- Panel only renders when `$activities->count() > 0`
- `WisnCalculatorService` must not be modified

---

### Task 1: Pass WISN result from controller to view

**Files:**
- Modify: `app/Http/Controllers/WorkloadActivityController.php` (lines 21–25)
- Create: `tests/Feature/ActivitiesWisnResultTest.php`

**Interfaces:**
- Consumes: `App\Services\WisnCalculatorService::calculateDepartmentStaffing(Department $department): array`
- Produces: `$wisnResult` array passed to view — keys used by Task 2: `awt_hours`, `health_service_fte`, `support_allowance_multiplier`, `aaf_fte`, `total_required_staff`, `wisn_ratio`, `status`, `current_staff`

- [ ] **Step 1: Write the failing feature test**

Create `tests/Feature/ActivitiesWisnResultTest.php`:

```php
<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\User;
use App\Models\WorkloadActivity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivitiesWisnResultTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsUser(): self
    {
        return $this->actingAs(User::factory()->create());
    }

    public function test_wisn_result_is_passed_to_view(): void
    {
        $department = Department::factory()->create([
            'working_days_per_year' => 260,
            'public_holidays'       => 13,
            'annual_leave_days'     => 18,
            'sick_leave_days'       => 12,
            'training_days'         => 5,
            'working_hours_per_day' => 8,
            'current_staff'         => 10,
        ]);

        // volume = awt / time = 1696 / 0.5 = 3392 → required = 1.0, ratio = 10/1.0 = 10.0
        WorkloadActivity::factory()->create([
            'department_id'       => $department->id,
            'activity_type'       => 'health_service',
            'time_standard_hours' => 0.5,
            'annual_volume'       => 3392,
        ]);

        $this->actingAsUser()
            ->get(route('activities.index', $department->id))
            ->assertViewHas('wisnResult')
            ->assertViewHas('wisnResult.awt_hours', 1696)
            ->assertViewHas('wisnResult.total_required_staff', 1.0)
            ->assertViewHas('wisnResult.wisn_ratio', 10.0)
            ->assertViewHas('wisnResult.status', 'surplus');
    }
}
```

- [ ] **Step 2: Run the test to confirm it fails**

```bash
php artisan test tests/Feature/ActivitiesWisnResultTest.php --filter test_wisn_result_is_passed_to_view
```

Expected: FAIL — `wisnResult` key not found in view data.

- [ ] **Step 3: Check if Department and WorkloadActivity factories exist**

```bash
ls database/factories/
```

If `DepartmentFactory.php` or `WorkloadActivityFactory.php` are missing, create them:

`database/factories/DepartmentFactory.php`:
```php
<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class DepartmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name'                  => $this->faker->words(2, true) . ' Department',
            'type'                  => 'general',
            'current_staff'         => 10,
            'working_days_per_year' => 260,
            'public_holidays'       => 13,
            'annual_leave_days'     => 18,
            'sick_leave_days'       => 12,
            'training_days'         => 5,
            'working_hours_per_day' => 8,
        ];
    }
}
```

`database/factories/WorkloadActivityFactory.php`:
```php
<?php

namespace Database\Factories;

use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;

class WorkloadActivityFactory extends Factory
{
    public function definition(): array
    {
        return [
            'department_id'       => Department::factory(),
            'activity_name'       => $this->faker->words(3, true),
            'activity_type'       => 'health_service',
            'time_standard_hours' => 0.5,
            'annual_volume'       => 1000,
        ];
    }
}
```

Add `use HasFactory;` to `app/Models/Department.php` and `app/Models/WorkloadActivity.php` if not already present.

- [ ] **Step 4: Update the controller `index` method**

In `app/Http/Controllers/WorkloadActivityController.php`, replace:

```php
public function index(Department $department)
{
    $activities = $department->activities;
    return view('activities.index', compact('department', 'activities'));
}
```

with:

```php
public function index(Department $department)
{
    $activities = $department->activities;
    $wisnResult = app(\App\Services\WisnCalculatorService::class)
                      ->calculateDepartmentStaffing($department);
    return view('activities.index', compact('department', 'activities', 'wisnResult'));
}
```

- [ ] **Step 5: Run the test to confirm it passes**

```bash
php artisan test tests/Feature/ActivitiesWisnResultTest.php --filter test_wisn_result_is_passed_to_view
```

Expected: PASS.

- [ ] **Step 6: Commit**

```bash
git add app/Http/Controllers/WorkloadActivityController.php \
        tests/Feature/ActivitiesWisnResultTest.php \
        database/factories/DepartmentFactory.php \
        database/factories/WorkloadActivityFactory.php
git commit -m "feat: pass WISN calculation result to activities view"
```

---

### Task 2: Render the WISN results panel in the view

**Files:**
- Modify: `resources/views/activities/index.blade.php`

**Interfaces:**
- Consumes: `$wisnResult` array from Task 1 — keys: `awt_hours`, `health_service_fte`, `support_allowance_multiplier`, `aaf_fte`, `total_required_staff`, `wisn_ratio`, `status`, `current_staff`
- Consumes: `$activities` (Eloquent collection) — used for `$activities->count() > 0` gate

- [ ] **Step 1: Write a feature test for the panel rendering**

Add to `tests/Feature/ActivitiesWisnResultTest.php`:

```php
public function test_results_panel_is_visible_when_activities_exist(): void
{
    $department = Department::factory()->create(['current_staff' => 10]);

    WorkloadActivity::factory()->create([
        'department_id'       => $department->id,
        'activity_type'       => 'health_service',
        'time_standard_hours' => 0.5,
        'annual_volume'       => 1696,
    ]);

    $this->actingAsUser()
        ->get(route('activities.index', $department->id))
        ->assertSee('WISN Calculation Result')
        ->assertSee('Health Service FTE')
        ->assertSee('Total Required');
}

public function test_results_panel_is_hidden_when_no_activities(): void
{
    $department = Department::factory()->create();

    $this->actingAsUser()
        ->get(route('activities.index', $department->id))
        ->assertDontSee('WISN Calculation Result');
}
```

- [ ] **Step 2: Run to confirm both tests fail**

```bash
php artisan test tests/Feature/ActivitiesWisnResultTest.php
```

Expected: the two new tests FAIL (panel not in view yet), the Task 1 test still PASS.

- [ ] **Step 3: Add the results panel to the view**

In `resources/views/activities/index.blade.php`, add the following immediately before the closing `</x-app-layout>` tag:

```blade
    {{-- WISN RESULTS PANEL --}}
    @if($activities->count() > 0)
    <div class="pb-6 max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-800 flex items-center gap-2">
                    <svg class="w-4 h-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 11h.01M12 11h.01M15 11h.01M4.929 4.929A10 10 0 1019.07 19.07 10 10 0 004.93 4.928z" />
                    </svg>
                    WISN Calculation Result
                </h3>
                @php
                    $gap = $wisnResult['current_staff'] - $wisnResult['total_required_staff'];
                @endphp
                <span class="text-xs text-gray-400">
                    Gap:
                    <span class="{{ $gap >= 0 ? 'text-emerald-600' : 'text-red-600' }} font-semibold">
                        {{ $gap >= 0 ? '+' : '' }}{{ round($gap, 2) }} nurses
                    </span>
                </span>
            </div>

            {{-- Metric tiles --}}
            <div class="grid grid-cols-2 sm:grid-cols-5 divide-y sm:divide-y-0 sm:divide-x divide-gray-100">
                <div class="px-5 py-4">
                    <div class="text-xs text-gray-400 uppercase tracking-wider font-semibold mb-1">AWT (hrs)</div>
                    <div class="text-xl font-bold text-gray-900 font-mono">{{ $wisnResult['awt_hours'] }}</div>
                </div>
                <div class="px-5 py-4">
                    <div class="text-xs text-gray-400 uppercase tracking-wider font-semibold mb-1">Health Service FTE</div>
                    <div class="text-xl font-bold text-gray-900 font-mono">{{ $wisnResult['health_service_fte'] }}</div>
                </div>
                <div class="px-5 py-4">
                    <div class="text-xs text-gray-400 uppercase tracking-wider font-semibold mb-1">CAF</div>
                    <div class="text-xl font-bold text-gray-900 font-mono">{{ $wisnResult['support_allowance_multiplier'] }}</div>
                </div>
                <div class="px-5 py-4">
                    <div class="text-xs text-gray-400 uppercase tracking-wider font-semibold mb-1">AAF FTE</div>
                    <div class="text-xl font-bold text-gray-900 font-mono">{{ $wisnResult['aaf_fte'] }}</div>
                </div>
                <div class="px-5 py-4">
                    <div class="text-xs text-gray-400 uppercase tracking-wider font-semibold mb-1">Total Required</div>
                    <div class="text-xl font-bold text-gray-900 font-mono">{{ $wisnResult['total_required_staff'] }}</div>
                </div>
            </div>

            {{-- Ratio + status --}}
            <div class="px-6 py-4 border-t border-gray-100 flex items-center gap-4">
                <div class="text-3xl font-extrabold font-mono
                    {{ $wisnResult['wisn_ratio'] <= 0    ? 'text-gray-400'    :
                      ($wisnResult['wisn_ratio'] < 0.90  ? 'text-red-600'     :
                      ($wisnResult['wisn_ratio'] < 1.0   ? 'text-amber-600'   :
                                                           'text-emerald-600')) }}">
                    {{ $wisnResult['wisn_ratio'] }}
                </div>
                <div class="text-xs text-gray-400 font-medium">WISN Ratio</div>
                <div class="ml-2">
                    @if($wisnResult['status'] === 'critical')
                        <span class="inline-flex items-center gap-1 px-3 py-1 text-xs font-semibold rounded-full bg-red-50 text-red-700 border border-red-100">
                            <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Critical
                        </span>
                    @elseif($wisnResult['status'] === 'borderline')
                        <span class="inline-flex items-center gap-1 px-3 py-1 text-xs font-semibold rounded-full bg-amber-50 text-amber-700 border border-amber-100">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Borderline
                        </span>
                    @elseif($wisnResult['status'] === 'adequate')
                        <span class="inline-flex items-center gap-1 px-3 py-1 text-xs font-semibold rounded-full bg-emerald-50 text-emerald-700 border border-emerald-100">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Adequate
                        </span>
                    @elseif($wisnResult['status'] === 'surplus')
                        <span class="inline-flex items-center gap-1 px-3 py-1 text-xs font-semibold rounded-full bg-blue-50 text-blue-700 border border-blue-100">
                            <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span> Surplus
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 px-3 py-1 text-xs font-semibold rounded-full bg-gray-50 text-gray-500 border border-gray-100">
                            <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span> No Data
                        </span>
                    @endif
                </div>
                <p class="ml-auto text-xs text-gray-400">
                    Target ratio: 1.0 — higher means more staff than required
                </p>
            </div>
        </div>
    </div>
    @endif
```

- [ ] **Step 4: Run all three tests**

```bash
php artisan test tests/Feature/ActivitiesWisnResultTest.php
```

Expected: all 3 tests PASS.

- [ ] **Step 5: Manual visual check**

```bash
php artisan serve
```

1. Go to any department's activities page with at least one activity — confirm the results panel appears at the bottom with correct values.
2. Go to a department with zero activities — confirm the panel is absent.
3. Add a second activity and refresh — confirm the panel values update.

- [ ] **Step 6: Commit**

```bash
git add resources/views/activities/index.blade.php \
        tests/Feature/ActivitiesWisnResultTest.php
git commit -m "feat: add WISN results panel to activities page"
```
