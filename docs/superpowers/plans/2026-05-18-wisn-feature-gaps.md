# WISN Feature Gaps Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Implement the 10 feature gaps identified between the WISN proposal and the current Laravel codebase, including AWT breakdown, Chart.js charts, activity CRUD, AAF calculation, tooltips, stepper, seeder, and PDF/help improvements.

**Architecture:** Laravel 10 backend with Blade/Tailwind frontend. All changes are additive — a new migration for AWT fields, new controller methods for activity CRUD, a computed model accessor replacing the old flat column, Chart.js via CDN injected through a Blade stack, and a database seeder for realistic test data.

**Tech Stack:** PHP 8.1 / Laravel 10, Blade templates, Tailwind CSS, Chart.js 4 (CDN), DomPDF, PHPUnit.

---

## File Map

| File | Action | Purpose |
|---|---|---|
| `database/migrations/2026_05_18_add_awt_breakdown_to_departments.php` | Create | Drops old flat column, adds 6 AWT breakdown columns |
| `app/Models/Department.php` | Modify | Add `availableWorkingTimeHours` computed accessor |
| `app/Services/WisnCalculatorService.php` | Modify | Add AAF (additional activity type) calculation |
| `app/Http/Controllers/DepartmentController.php` | Modify | Update validation for new AWT fields |
| `app/Http/Controllers/WorkloadActivityController.php` | Modify | Add edit, update, destroy methods |
| `routes/web.php` | Modify | Add activity edit/update/destroy routes; add help route |
| `resources/views/departments/create.blade.php` | Modify | Replace AWT single field with 6 breakdown fields + tooltips |
| `resources/views/departments/edit.blade.php` | Modify | Same as create — replace AWT field with breakdown fields |
| `resources/views/activities/index.blade.php` | Modify | Add edit/delete buttons; add 'additional' type; add WISN stepper; add tooltips |
| `resources/views/activities/edit.blade.php` | Create | Form to edit an existing workload activity |
| `resources/views/dashboard.blade.php` | Modify | Add Chart.js WISN ratio bar chart and staffing gap chart |
| `resources/views/layouts/app.blade.php` | Modify | Add `@stack('scripts')` before `</body>` |
| `resources/views/reports/wisn-summary.blade.php` | Modify | Add AWT details, activity breakdown, interpretive guidance |
| `resources/views/help.blade.php` | Create | WISN methodology in-app help page |
| `app/Http/Controllers/HelpController.php` | Create | Returns help view |
| `database/seeders/DatabaseSeeder.php` | Modify | Seed 4 Nepalese hospital departments with realistic activities |

---

## Task 1: AWT Breakdown Migration + Model Accessor

**Files:**
- Create: `database/migrations/2026_05_18_add_awt_breakdown_to_departments.php`
- Modify: `app/Models/Department.php`

- [ ] **Step 1: Create the migration**

Create `database/migrations/2026_05_18_add_awt_breakdown_to_departments.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->dropColumn('available_working_time_hours');
            $table->unsignedSmallInteger('working_days_per_year')->default(260)->after('current_staff');
            $table->unsignedSmallInteger('public_holidays')->default(13)->after('working_days_per_year');
            $table->unsignedSmallInteger('annual_leave_days')->default(18)->after('public_holidays');
            $table->unsignedSmallInteger('sick_leave_days')->default(12)->after('annual_leave_days');
            $table->unsignedSmallInteger('training_days')->default(5)->after('sick_leave_days');
            $table->unsignedTinyInteger('working_hours_per_day')->default(8)->after('training_days');
        });
    }

    public function down(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->dropColumn([
                'working_days_per_year', 'public_holidays', 'annual_leave_days',
                'sick_leave_days', 'training_days', 'working_hours_per_day',
            ]);
            $table->integer('available_working_time_hours')->default(2000);
        });
    }
};
```

- [ ] **Step 2: Add the computed accessor to the Department model**

Replace entire `app/Models/Department.php` with:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Department extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function activities()
    {
        return $this->hasMany(WorkloadActivity::class);
    }

    // Computes AWT from the 6 breakdown fields.
    // Accessible as $department->available_working_time_hours throughout the codebase.
    protected function availableWorkingTimeHours(): Attribute
    {
        return Attribute::get(fn () =>
            ($this->working_days_per_year
                - $this->public_holidays
                - $this->annual_leave_days
                - $this->sick_leave_days
                - $this->training_days
            ) * $this->working_hours_per_day
        );
    }
}
```

- [ ] **Step 3: Run the migration**

```bash
php artisan migrate
```

Expected output: `Running migrations... 2026_05_18_add_awt_breakdown_to_departments ..... DONE`

If it says "column not found" on `dropColumn`, the old column may not exist — wrap the drop in `if (Schema::hasColumn('departments', 'available_working_time_hours'))` in the migration.

- [ ] **Step 4: Update DepartmentController validation — store()**

In `app/Http/Controllers/DepartmentController.php`, replace the `store()` and `update()` validation arrays:

```php
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

public function store(Request $request)
{
    $validated = $request->validate($this->validationRules());
    Department::create($validated);
    return redirect()->route('departments.index')->with('success', 'Department added successfully!');
}

public function update(Request $request, Department $department)
{
    $validated = $request->validate($this->validationRules());
    $department->update($validated);
    return redirect()->route('departments.index')->with('success', 'Department updated successfully!');
}
```

Also remove the old `show()` stub and keep `edit()`, `create()`, `destroy()` unchanged.

Full replacement for `app/Http/Controllers/DepartmentController.php`:

```php
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
        $department->update($validated);
        return redirect()->route('departments.index')->with('success', 'Department updated successfully!');
    }

    public function destroy(Department $department)
    {
        $department->delete();
        return redirect()->route('departments.index')->with('success', 'Department deleted permanently.');
    }
}
```

- [ ] **Step 5: Update the departments/create.blade.php form**

Replace the AWT single-field block (the entire `<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">` block that contains the AWT input) with the AWT breakdown section below. The new `create.blade.php` full content:

```blade
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Add New Department') }}
            </h2>
            <a href="{{ route('departments.index') }}" class="text-gray-600 hover:text-gray-900 font-medium">
                &larr; Back to List
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    @if ($errors->any())
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                            <strong class="font-bold">Whoops! Something went wrong.</strong>
                            <ul class="mt-2 list-disc list-inside text-sm">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('departments.store') }}" method="POST">
                        @csrf

                        <div class="mb-6">
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                                Department Name *
                                <span class="ml-1 text-gray-400 cursor-help" title="The official name of the hospital department or ward (e.g., Intensive Care Unit, Emergency Department).">ⓘ</span>
                            </label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}" required
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
                                placeholder="e.g., Pediatric Ward">
                        </div>

                        <div class="mb-6">
                            <label for="type" class="block text-sm font-medium text-gray-700 mb-1">
                                Department Type *
                                <span class="ml-1 text-gray-400 cursor-help" title="The category of care provided. This helps contextualise workload patterns.">ⓘ</span>
                            </label>
                            <select name="type" id="type" required
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                                <option value="" disabled selected>Select a type...</option>
                                <option value="Inpatient - Standard" {{ old('type') == 'Inpatient - Standard' ? 'selected' : '' }}>Inpatient - Standard</option>
                                <option value="Inpatient - High Acuity" {{ old('type') == 'Inpatient - High Acuity' ? 'selected' : '' }}>Inpatient - High Acuity (ICU/CCU)</option>
                                <option value="Outpatient" {{ old('type') == 'Outpatient' ? 'selected' : '' }}>Outpatient</option>
                                <option value="Emergency" {{ old('type') == 'Emergency' ? 'selected' : '' }}>Emergency</option>
                                <option value="Surgical/OT" {{ old('type') == 'Surgical/OT' ? 'selected' : '' }}>Surgical / Operating Theater</option>
                            </select>
                        </div>

                        <div class="mb-6">
                            <label for="current_staff" class="block text-sm font-medium text-gray-700 mb-1">
                                Current Number of Nurses *
                                <span class="ml-1 text-gray-400 cursor-help" title="The actual headcount of nursing staff currently assigned to this department. Used to calculate the WISN ratio.">ⓘ</span>
                            </label>
                            <input type="number" name="current_staff" id="current_staff" value="{{ old('current_staff', 0) }}" min="0" required
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                        </div>

                        {{-- WHO WISN AWT BREAKDOWN --}}
                        <div class="mb-6 border border-blue-200 rounded-lg p-4 bg-blue-50">
                            <h4 class="font-semibold text-blue-800 mb-1">Available Working Time (AWT) Breakdown</h4>
                            <p class="text-xs text-blue-600 mb-4">
                                WHO WISN requires AWT to be calculated from components: <strong>AWT = (Working Days − Leave Days) × Hours/Day</strong>.
                                Defaults reflect Nepal Government Health Service standards.
                            </p>

                            <div id="awt-preview" class="text-sm font-bold text-blue-700 mb-3"></div>

                            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">
                                        Working Days/Year *
                                        <span class="text-gray-400 cursor-help" title="Total calendar days minus weekends. Nepal 5-day week = ~260 days/year.">ⓘ</span>
                                    </label>
                                    <input type="number" name="working_days_per_year" value="{{ old('working_days_per_year', 260) }}" min="1" max="366" required
                                        class="awt-field w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">
                                        Public Holidays *
                                        <span class="text-gray-400 cursor-help" title="Nepal has approximately 13 official public holidays per year.">ⓘ</span>
                                    </label>
                                    <input type="number" name="public_holidays" value="{{ old('public_holidays', 13) }}" min="0" max="50" required
                                        class="awt-field w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">
                                        Annual Leave Days *
                                        <span class="text-gray-400 cursor-help" title="Paid annual leave entitlement per nurse. Nepal Government standard: 18 days.">ⓘ</span>
                                    </label>
                                    <input type="number" name="annual_leave_days" value="{{ old('annual_leave_days', 18) }}" min="0" max="60" required
                                        class="awt-field w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">
                                        Sick Leave Days *
                                        <span class="text-gray-400 cursor-help" title="Average sick leave taken per nurse per year. Nepal Government standard: 12 days.">ⓘ</span>
                                    </label>
                                    <input type="number" name="sick_leave_days" value="{{ old('sick_leave_days', 12) }}" min="0" max="60" required
                                        class="awt-field w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">
                                        Training Days *
                                        <span class="text-gray-400 cursor-help" title="Days per year spent in mandatory training or continuing education. WHO recommends including these.">ⓘ</span>
                                    </label>
                                    <input type="number" name="training_days" value="{{ old('training_days', 5) }}" min="0" max="30" required
                                        class="awt-field w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">
                                        Working Hours/Day *
                                        <span class="text-gray-400 cursor-help" title="Standard shift length in hours. Nepal Government hospitals typically use 8-hour shifts.">ⓘ</span>
                                    </label>
                                    <input type="number" name="working_hours_per_day" value="{{ old('working_hours_per_day', 8) }}" min="4" max="24" required
                                        class="awt-field w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 text-sm">
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-8 border-t pt-5">
                            <a href="{{ route('departments.index') }}" class="text-gray-600 hover:text-gray-900 font-medium mr-4">Cancel</a>
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded shadow">
                                Save Department
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function updateAwtPreview() {
            const fields = ['working_days_per_year','public_holidays','annual_leave_days','sick_leave_days','training_days','working_hours_per_day'];
            const vals = fields.map(f => parseInt(document.querySelector(`[name="${f}"]`).value) || 0);
            const netDays = vals[0] - vals[1] - vals[2] - vals[3] - vals[4];
            const awt = netDays * vals[5];
            document.getElementById('awt-preview').textContent =
                `Computed AWT: (${vals[0]} − ${vals[1]+vals[2]+vals[3]+vals[4]}) × ${vals[5]} = ${awt} hours/nurse/year`;
        }
        document.querySelectorAll('.awt-field').forEach(el => el.addEventListener('input', updateAwtPreview));
        updateAwtPreview();
    </script>
    @endpush
</x-app-layout>
```

- [ ] **Step 6: Update departments/edit.blade.php with same AWT breakdown**

Replace entire `resources/views/departments/edit.blade.php` with:

```blade
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Edit Department: <span class="text-blue-600">{{ $department->name }}</span>
            </h2>
            <a href="{{ route('departments.index') }}" class="text-gray-600 hover:text-gray-900 font-medium">
                &larr; Back to List
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    @if ($errors->any())
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                            <strong class="font-bold">Whoops! Something went wrong.</strong>
                            <ul class="mt-2 list-disc list-inside text-sm">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('departments.update', $department->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Department Name *
                                <span class="ml-1 text-gray-400 cursor-help" title="The official name of the hospital department or ward.">ⓘ</span>
                            </label>
                            <input type="text" name="name" value="{{ old('name', $department->name) }}" required
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                        </div>

                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Department Type *</label>
                            <select name="type" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                                @foreach(['Inpatient - Standard','Inpatient - High Acuity','Outpatient','Emergency','Surgical/OT'] as $opt)
                                    <option value="{{ $opt }}" {{ old('type', $department->type) == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Current Number of Nurses *
                                <span class="ml-1 text-gray-400 cursor-help" title="Actual headcount currently assigned to this department.">ⓘ</span>
                            </label>
                            <input type="number" name="current_staff" value="{{ old('current_staff', $department->current_staff) }}" min="0" required
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500">
                        </div>

                        <div class="mb-6 border border-blue-200 rounded-lg p-4 bg-blue-50">
                            <h4 class="font-semibold text-blue-800 mb-1">Available Working Time (AWT) Breakdown</h4>
                            <p class="text-xs text-blue-600 mb-4">AWT = (Working Days − Leave Days) × Hours/Day</p>
                            <div id="awt-preview" class="text-sm font-bold text-blue-700 mb-3"></div>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                                @foreach([
                                    ['working_days_per_year', 'Working Days/Year', 'Total calendar days minus weekends (~260 for Nepal 5-day week).', 1, 366],
                                    ['public_holidays', 'Public Holidays', 'Nepal has ~13 official public holidays per year.', 0, 50],
                                    ['annual_leave_days', 'Annual Leave Days', 'Paid annual leave entitlement — Nepal Gov standard: 18 days.', 0, 60],
                                    ['sick_leave_days', 'Sick Leave Days', 'Average sick leave per nurse — Nepal Gov standard: 12 days.', 0, 60],
                                    ['training_days', 'Training Days', 'Mandatory training/CPD days per year. WHO recommends including these.', 0, 30],
                                    ['working_hours_per_day', 'Hours/Day', 'Standard shift length. Nepal Gov hospitals use 8-hour shifts.', 4, 24],
                                ] as [$field, $label, $tip, $min, $max])
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">
                                        {{ $label }} *
                                        <span class="text-gray-400 cursor-help" title="{{ $tip }}">ⓘ</span>
                                    </label>
                                    <input type="number" name="{{ $field }}"
                                        value="{{ old($field, $department->$field) }}"
                                        min="{{ $min }}" max="{{ $max }}" required
                                        class="awt-field w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 text-sm">
                                </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-8 border-t pt-5">
                            <a href="{{ route('departments.index') }}" class="text-gray-600 hover:text-gray-900 font-medium mr-4">Cancel</a>
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded shadow">
                                Update Department
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function updateAwtPreview() {
            const fields = ['working_days_per_year','public_holidays','annual_leave_days','sick_leave_days','training_days','working_hours_per_day'];
            const vals = fields.map(f => parseInt(document.querySelector(`[name="${f}"]`).value) || 0);
            const netDays = vals[0] - vals[1] - vals[2] - vals[3] - vals[4];
            const awt = netDays * vals[5];
            document.getElementById('awt-preview').textContent =
                `Computed AWT: (${vals[0]} − ${vals[1]+vals[2]+vals[3]+vals[4]}) × ${vals[5]} = ${awt} hours/nurse/year`;
        }
        document.querySelectorAll('.awt-field').forEach(el => el.addEventListener('input', updateAwtPreview));
        updateAwtPreview();
    </script>
    @endpush
</x-app-layout>
```

- [ ] **Step 7: Add `@stack('scripts')` to app.blade.php**

In `resources/views/layouts/app.blade.php`, add `@stack('scripts')` just before `</body>`:

```blade
        </div>
        @stack('scripts')
    </body>
</html>
```

---

## Task 2: Activity Edit & Delete (Complete CRUD)

**Files:**
- Modify: `app/Http/Controllers/WorkloadActivityController.php`
- Modify: `routes/web.php`
- Modify: `resources/views/activities/index.blade.php`
- Create: `resources/views/activities/edit.blade.php`

- [ ] **Step 1: Add routes for activity edit, update, destroy**

Replace the two activity routes in `routes/web.php`:

```php
// Activity Management (Nested under specific departments)
Route::get('departments/{department}/activities', [WorkloadActivityController::class, 'index'])->name('activities.index');
Route::post('departments/{department}/activities', [WorkloadActivityController::class, 'store'])->name('activities.store');
Route::get('departments/{department}/activities/{activity}/edit', [WorkloadActivityController::class, 'edit'])->name('activities.edit');
Route::put('departments/{department}/activities/{activity}', [WorkloadActivityController::class, 'update'])->name('activities.update');
Route::delete('departments/{department}/activities/{activity}', [WorkloadActivityController::class, 'destroy'])->name('activities.destroy');
```

- [ ] **Step 2: Add edit, update, destroy to WorkloadActivityController**

Replace entire `app/Http/Controllers/WorkloadActivityController.php`:

```php
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
            'activity_name'      => 'required|string|max:255',
            'activity_type'      => 'required|in:health_service,support,additional',
            'time_standard_hours'=> 'required|numeric|min:0.01',
            'annual_volume'      => 'nullable|integer|min:1|required_if:activity_type,health_service,activity_type,additional',
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
```

- [ ] **Step 3: Create activities/edit.blade.php**

Create `resources/views/activities/edit.blade.php`:

```blade
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Edit Activity &mdash; <span class="text-blue-600">{{ $department->name }}</span>
            </h2>
            <a href="{{ route('activities.index', $department->id) }}" class="text-gray-600 hover:text-gray-900 font-medium">
                &larr; Back to Activities
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                @if ($errors->any())
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        <ul class="list-disc list-inside text-sm">
                            @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('activities.update', [$department->id, $activity->id]) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">
                            Activity Name
                            <span class="text-gray-400 cursor-help" title="A clear description of the nursing task or activity (e.g., Medication administration, Patient triage).">ⓘ</span>
                        </label>
                        <input type="text" name="activity_name" value="{{ old('activity_name', $activity->activity_name) }}" required
                            class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500">
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">
                            Activity Type
                            <span class="text-gray-400 cursor-help" title="Health Service: direct patient care counted by annual volume. Support: recurring shift duties expressed as allowance factor. Additional: cross-category duties like teaching.">ⓘ</span>
                        </label>
                        <select name="activity_type" id="activity_type" required
                            class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500">
                            <option value="health_service" {{ old('activity_type', $activity->activity_type) == 'health_service' ? 'selected' : '' }}>Health Service (Direct Care)</option>
                            <option value="support" {{ old('activity_type', $activity->activity_type) == 'support' ? 'selected' : '' }}>Support (Admin / Meetings / Handover)</option>
                            <option value="additional" {{ old('activity_type', $activity->activity_type) == 'additional' ? 'selected' : '' }}>Additional (Teaching / Cross-department)</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">
                            Time Standard (Hours per Occurrence)
                            <span class="text-gray-400 cursor-help" title="How long this activity takes each time it is performed, in hours (e.g., 0.5 for 30 minutes, 0.08 for 5 minutes).">ⓘ</span>
                        </label>
                        <input type="number" step="0.01" name="time_standard_hours"
                            value="{{ old('time_standard_hours', $activity->time_standard_hours) }}" required
                            class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500"
                            placeholder="e.g., 0.5 for 30 min">
                    </div>

                    <div class="mb-6" id="volume_field">
                        <label class="block text-sm font-medium text-gray-700">
                            Annual Volume
                            <span class="text-gray-400 cursor-help" title="Total number of times this activity occurs in the department in one year. Required for Health Service and Additional activity types.">ⓘ</span>
                        </label>
                        <input type="number" name="annual_volume"
                            value="{{ old('annual_volume', $activity->annual_volume) }}"
                            class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500"
                            placeholder="e.g., 8760 for hourly monitoring in ICU">
                        <p class="text-xs text-gray-500 mt-1">Required for Health Service and Additional types. Leave blank for Support activities.</p>
                    </div>

                    <div class="flex justify-end gap-3">
                        <a href="{{ route('activities.index', $department->id) }}"
                            class="text-gray-600 hover:text-gray-900 font-medium py-2 px-4">Cancel</a>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded shadow">
                            Update Activity
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
```

- [ ] **Step 4: Add Edit/Delete buttons to activities table and add 'Additional' type option to the Add form**

Replace entire `resources/views/activities/index.blade.php`:

```blade
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Manage Activities: <span class="text-blue-600">{{ $department->name }}</span>
            </h2>
            <a href="{{ route('departments.index') }}" class="text-gray-600 hover:text-gray-900 font-medium">
                &larr; Back to Departments
            </a>
        </div>
    </x-slot>

    {{-- WISN SETUP STEPPER --}}
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 pt-6">
        <div class="bg-white border border-gray-200 rounded-lg p-4 flex items-center gap-4 text-sm overflow-x-auto">
            <div class="flex items-center gap-2 text-green-600 font-semibold whitespace-nowrap">
                <span class="w-6 h-6 rounded-full bg-green-600 text-white flex items-center justify-center text-xs">✓</span>
                Step 1: Department Created
            </div>
            <div class="text-gray-300 font-bold">→</div>
            <div class="flex items-center gap-2 text-blue-600 font-semibold whitespace-nowrap">
                <span class="w-6 h-6 rounded-full bg-blue-600 text-white flex items-center justify-center text-xs font-bold">2</span>
                Step 2: Add Health Service Activities
            </div>
            <div class="text-gray-300 font-bold">→</div>
            <div class="flex items-center gap-2 text-gray-400 whitespace-nowrap">
                <span class="w-6 h-6 rounded-full bg-gray-200 text-gray-500 flex items-center justify-center text-xs font-bold">3</span>
                Step 3: Add Support Activities
            </div>
            <div class="text-gray-300 font-bold">→</div>
            <div class="flex items-center gap-2 text-gray-400 whitespace-nowrap">
                <span class="w-6 h-6 rounded-full bg-gray-200 text-gray-500 flex items-center justify-center text-xs font-bold">4</span>
                <a href="{{ route('dashboard') }}" class="hover:text-blue-600">Step 4: View Results →</a>
            </div>
        </div>
    </div>

    <div class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-3 gap-6">

        {{-- ADD ACTIVITY FORM --}}
        <div class="md:col-span-1">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-bold mb-4 border-b pb-2">Add New Activity</h3>

                @if ($errors->any())
                    <div class="text-red-600 text-sm mb-4">Please fix the errors below.</div>
                @endif

                <form action="{{ route('activities.store', $department->id) }}" method="POST">
                    @csrf

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">
                            Activity Name
                            <span class="text-gray-400 cursor-help" title="Describe the nursing task (e.g., Medication administration, Shift handover, Patient triage).">ⓘ</span>
                        </label>
                        <input type="text" name="activity_name" value="{{ old('activity_name') }}" required
                            class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">
                            Activity Type
                            <span class="text-gray-400 cursor-help" title="Health Service: direct patient care with annual volume. Support: recurring shift duties (e.g., handover). Additional: teaching or cross-department duties.">ⓘ</span>
                        </label>
                        <select name="activity_type" id="activity_type" required
                            class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="health_service" {{ old('activity_type') == 'health_service' ? 'selected' : '' }}>Health Service (Direct Care)</option>
                            <option value="support" {{ old('activity_type') == 'support' ? 'selected' : '' }}>Support (Admin / Meetings / Handover)</option>
                            <option value="additional" {{ old('activity_type') == 'additional' ? 'selected' : '' }}>Additional (Teaching / Cross-dept)</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">
                            Time Standard (Hours)
                            <span class="text-gray-400 cursor-help" title="Duration of one occurrence in hours. E.g., 0.5 = 30 min, 0.08 = 5 min, 1.0 = 1 hour.">ⓘ</span>
                        </label>
                        <input type="number" step="0.01" name="time_standard_hours"
                            value="{{ old('time_standard_hours') }}"
                            placeholder="e.g., 0.5 for 30 mins" required
                            class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div class="mb-6" id="volume_field">
                        <label class="block text-sm font-medium text-gray-700">
                            Annual Volume
                            <span class="text-gray-400 cursor-help" title="Total times this activity occurs in this department per year. Required for Health Service and Additional types. Leave blank for Support activities.">ⓘ</span>
                        </label>
                        <input type="number" name="annual_volume" value="{{ old('annual_volume') }}"
                            placeholder="Required for Health Service / Additional"
                            class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Save Activity
                    </button>
                </form>
            </div>
        </div>

        {{-- ACTIVITY LIST --}}
        <div class="md:col-span-2">
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-bold mb-4 border-b pb-2">Current Workload Profile</h3>

                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Activity</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Time (Hrs)</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Volume</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($activities as $activity)
                        <tr>
                            <td class="px-4 py-4 text-sm text-gray-900">{{ $activity->activity_name }}</td>
                            <td class="px-4 py-4 text-sm text-gray-500">
                                @if($activity->activity_type === 'health_service')
                                    <span class="px-2 py-0.5 text-xs rounded-full bg-blue-100 text-blue-800">Health Service</span>
                                @elseif($activity->activity_type === 'support')
                                    <span class="px-2 py-0.5 text-xs rounded-full bg-yellow-100 text-yellow-800">Support</span>
                                @else
                                    <span class="px-2 py-0.5 text-xs rounded-full bg-purple-100 text-purple-800">Additional</span>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-sm text-gray-900 font-bold">{{ $activity->time_standard_hours }}</td>
                            <td class="px-4 py-4 text-sm text-gray-500">{{ $activity->annual_volume ?? '—' }}</td>
                            <td class="px-4 py-4 text-sm flex gap-3">
                                <a href="{{ route('activities.edit', [$department->id, $activity->id]) }}"
                                    class="text-blue-600 hover:text-blue-900 font-medium">Edit</a>
                                <form action="{{ route('activities.destroy', [$department->id, $activity->id]) }}" method="POST"
                                    onsubmit="return confirm('Delete this activity? This will affect WISN calculations.')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900 font-medium">Delete</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-gray-400 italic">
                                No activities yet. Use the form on the left to add your first workload activity.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
```

---

## Task 3: AAF (Additional Activity Type) Calculation

**Files:**
- Modify: `app/Services/WisnCalculatorService.php`

- [ ] **Step 1: Update WisnCalculatorService to handle 'additional' type with AAF**

Replace entire `app/Services/WisnCalculatorService.php`:

```php
<?php

namespace App\Services;

use App\Models\Department;

class WisnCalculatorService
{
    public function calculateDepartmentStaffing(Department $department): array
    {
        $awt = $department->available_working_time_hours;
        $healthServiceFTE = 0;
        $supportTimePercentage = 0;
        $aafHours = 0; // Total hours spent on additional cross-category activities
        $breakdown = [];

        foreach ($department->activities as $activity) {

            if ($activity->activity_type === 'health_service') {
                // Standard Workload = AWT / Time Standard
                $standardWorkload = $awt / $activity->time_standard_hours;
                // Required Staff = Annual Volume / Standard Workload
                $requiredStaff = $activity->annual_volume / $standardWorkload;
                $healthServiceFTE += $requiredStaff;

                $breakdown[] = [
                    'activity'        => $activity->activity_name,
                    'type'            => 'Health Service',
                    'standard_workload' => round($standardWorkload),
                    'required_staff'  => round($requiredStaff, 2),
                ];

            } elseif ($activity->activity_type === 'support') {
                // Support activities consume a % of each 8-hour shift
                $percentage = $activity->time_standard_hours / 8;
                $supportTimePercentage += $percentage;

                $breakdown[] = [
                    'activity'            => $activity->activity_name,
                    'type'                => 'Support Allowance',
                    'allowance_percentage'=> round($percentage * 100, 1) . '%',
                ];

            } elseif ($activity->activity_type === 'additional') {
                // Additional Allowance Factor: time spent on activities across all cadre categories
                // (e.g., teaching, cross-department meetings). Counted as FTE directly.
                $aafHours += ($activity->annual_volume ?? 0) * $activity->time_standard_hours;

                $breakdown[] = [
                    'activity'     => $activity->activity_name,
                    'type'         => 'Additional (AAF)',
                    'total_hours'  => round(($activity->annual_volume ?? 0) * $activity->time_standard_hours, 1),
                ];
            }
        }

        // Category Allowance Factor: adjusts clinical staff for time lost to support tasks
        $caf = 1;
        if ($supportTimePercentage > 0 && $supportTimePercentage < 1) {
            $caf = 1 / (1 - $supportTimePercentage);
        }

        // Additional Allowance Factor (FTE equivalent of cross-category time)
        $aafFte = $awt > 0 ? $aafHours / $awt : 0;

        // Total Required Staff = (Health Service FTE × CAF) + AAF FTE
        $totalRequiredStaff = ($healthServiceFTE * $caf) + $aafFte;

        // WISN Ratio = Current Staff / Required Staff
        $wisnRatio = $totalRequiredStaff > 0
            ? $department->current_staff / $totalRequiredStaff
            : 0;

        return [
            'department_id'              => $department->id,
            'department_name'            => $department->name,
            'department_type'            => $department->type,
            'current_staff'              => $department->current_staff,
            'awt_hours'                  => $awt,
            'health_service_fte'         => round($healthServiceFTE, 2),
            'support_allowance_multiplier' => round($caf, 3),
            'aaf_fte'                    => round($aafFte, 2),
            'total_required_staff'       => round($totalRequiredStaff, 2),
            'wisn_ratio'                 => round($wisnRatio, 2),
            'status'                     => $this->determineStatus($wisnRatio),
            'breakdown'                  => $breakdown,
        ];
    }

    private function determineStatus(float $ratio): string
    {
        if ($ratio <= 0)   return 'no_data';
        if ($ratio < 0.90) return 'critical';
        if ($ratio < 1.0)  return 'borderline';
        if ($ratio == 1.0) return 'adequate';
        return 'surplus';
    }
}
```

---

## Task 4: Chart.js Dashboard Charts

**Files:**
- Modify: `resources/views/dashboard.blade.php`

Note: `app.blade.php` already has `@stack('scripts')` added in Task 1 Step 7.

- [ ] **Step 1: Add two charts to dashboard.blade.php**

Replace entire `resources/views/dashboard.blade.php`:

```blade
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Facility Staffing Dashboard') }}
            </h2>
            <div class="flex gap-3">
                <a href="{{ route('help') }}" class="text-gray-600 hover:text-gray-900 font-medium text-sm">
                    ? WISN Help
                </a>
                <a href="{{ route('report.generate') }}"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded shadow text-sm">
                    Download PDF Report
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- SUMMARY CARDS --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-blue-500">
                    <div class="text-sm text-gray-500 uppercase tracking-wide font-bold">Total Current Nurses</div>
                    <div class="text-3xl font-extrabold text-gray-900 mt-2">{{ $totalCurrentStaff }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-purple-500">
                    <div class="text-sm text-gray-500 uppercase tracking-wide font-bold">Total Required Nurses</div>
                    <div class="text-3xl font-extrabold text-gray-900 mt-2">{{ $totalRequiredStaff }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 {{ $facilityRatio < 1 ? 'border-red-500' : 'border-green-500' }}">
                    <div class="text-sm text-gray-500 uppercase tracking-wide font-bold">Facility WISN Ratio</div>
                    <div class="text-3xl font-extrabold {{ $facilityRatio < 0.9 ? 'text-red-600' : ($facilityRatio < 1 ? 'text-yellow-600' : 'text-green-600') }} mt-2">
                        {{ $facilityRatio }}
                    </div>
                    <div class="text-xs text-gray-500 mt-1">
                        @if($facilityRatio < 0.9) Critical Shortage
                        @elseif($facilityRatio < 1) Minor Shortage
                        @elseif($facilityRatio == 1) Perfectly Balanced
                        @else Surplus
                        @endif
                        &mdash; ratio of 1.0 = ideal
                    </div>
                </div>
            </div>

            {{-- CHARTS ROW --}}
            @if(count($departmentResults) > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-base font-bold mb-4 text-gray-700">WISN Ratio by Department</h3>
                    <p class="text-xs text-gray-400 mb-3">Target: 1.0 (green line). Below = shortage, above = surplus.</p>
                    <canvas id="wisnRatioChart" height="200"></canvas>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-base font-bold mb-4 text-gray-700">Staffing Gap by Department</h3>
                    <p class="text-xs text-gray-400 mb-3">Current nurses vs. required nurses per department.</p>
                    <canvas id="staffingGapChart" height="200"></canvas>
                </div>
            </div>
            @endif

            {{-- DEPARTMENT TABLE --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-bold mb-4 border-b pb-2">Department Analysis</h3>

                    @if(count($departmentResults) === 0)
                        <div class="text-center py-12 text-gray-400">
                            <p class="text-lg mb-2">No departments configured yet.</p>
                            <a href="{{ route('departments.create') }}" class="text-blue-600 hover:underline">Add your first department →</a>
                        </div>
                    @else
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Department</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">AWT (hrs)</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Current Staff</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Required Staff</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">WISN Ratio</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($departmentResults as $result)
                                <tr>
                                    <td class="px-6 py-4 font-medium text-gray-900">
                                        <a href="{{ route('activities.index', $result['department_id']) }}" class="hover:text-blue-600">
                                            {{ $result['department_name'] }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 text-gray-500">{{ $result['awt_hours'] }}</td>
                                    <td class="px-6 py-4">{{ $result['current_staff'] }}</td>
                                    <td class="px-6 py-4 font-bold">{{ $result['total_required_staff'] }}</td>
                                    <td class="px-6 py-4 font-bold">{{ $result['wisn_ratio'] }}</td>
                                    <td class="px-6 py-4">
                                        @if ($result['status'] === 'critical')
                                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Critical Shortage</span>
                                        @elseif($result['status'] === 'borderline')
                                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Borderline</span>
                                        @elseif($result['status'] === 'adequate')
                                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Adequate</span>
                                        @elseif($result['status'] === 'surplus')
                                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">Surplus</span>
                                        @else
                                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-500">No Data</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @endif
                </div>
            </div>

        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        const results = @json($departmentResults);
        const labels = results.map(r => r.department_name);
        const ratios = results.map(r => r.wisn_ratio);
        const current = results.map(r => r.current_staff);
        const required = results.map(r => r.total_required_staff);

        const ratioColors = ratios.map(r =>
            r < 0.9  ? 'rgba(239,68,68,0.8)'  :
            r < 1.0  ? 'rgba(234,179,8,0.8)'  :
                       'rgba(34,197,94,0.8)'
        );

        // Chart 1: WISN Ratio bar chart
        new Chart(document.getElementById('wisnRatioChart'), {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                    label: 'WISN Ratio',
                    data: ratios,
                    backgroundColor: ratioColors,
                    borderColor: ratioColors.map(c => c.replace('0.8','1')),
                    borderWidth: 1,
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: Math.max(2, ...ratios) + 0.2,
                        grid: { color: 'rgba(0,0,0,0.05)' }
                    }
                },
                plugins: {
                    annotation: {},
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: ctx => `WISN Ratio: ${ctx.raw} (target: 1.0)`
                        }
                    }
                }
            }
        });

        // Chart 2: Staffing Gap grouped bar chart
        new Chart(document.getElementById('staffingGapChart'), {
            type: 'bar',
            data: {
                labels,
                datasets: [
                    {
                        label: 'Current Staff',
                        data: current,
                        backgroundColor: 'rgba(59,130,246,0.8)',
                        borderColor: 'rgba(59,130,246,1)',
                        borderWidth: 1,
                    },
                    {
                        label: 'Required Staff',
                        data: required,
                        backgroundColor: 'rgba(168,85,247,0.8)',
                        borderColor: 'rgba(168,85,247,1)',
                        borderWidth: 1,
                    }
                ]
            },
            options: {
                responsive: true,
                scales: {
                    y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' } }
                },
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    </script>
    @endpush
</x-app-layout>
```

---

## Task 5: Database Seeder with 4 Nepalese Hospital Departments

**Files:**
- Modify: `database/seeders/DatabaseSeeder.php`

All departments use AWT = (260 − 13 − 18 − 12 − 5) × 8 = **1,696 hours/nurse/year**.

Expected WISN results after seeding:
- ICU → ratio ≈ 0.71 (Critical)
- Emergency → ratio ≈ 0.91 (Borderline)
- General Medical Ward → ratio ≈ 0.78 (Critical)
- Surgical Ward → ratio ≈ 1.05 (Adequate)

- [ ] **Step 1: Replace DatabaseSeeder.php**

Replace `database/seeders/DatabaseSeeder.php`:

```php
<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\WorkloadActivity;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Wipe existing data so seeder is idempotent
        WorkloadActivity::query()->delete();
        Department::query()->delete();

        $awtDefaults = [
            'working_days_per_year' => 260,
            'public_holidays'       => 13,
            'annual_leave_days'     => 18,
            'sick_leave_days'       => 12,
            'training_days'         => 5,
            'working_hours_per_day' => 8,
            // Computed AWT = (260-13-18-12-5)*8 = 212*8 = 1,696 hours
        ];

        // ----------------------------------------------------------------
        // 1. INTENSIVE CARE UNIT — Expected WISN ratio ≈ 0.71 (Critical)
        // ----------------------------------------------------------------
        $icu = Department::create(array_merge($awtDefaults, [
            'name'          => 'Intensive Care Unit (ICU)',
            'type'          => 'Inpatient - High Acuity',
            'current_staff' => 6,
        ]));

        $icu->activities()->createMany([
            // Health Service Activities (8 beds, ~80% occupancy = 2,336 patient-days/yr)
            ['activity_name' => 'Continuous patient monitoring & assessment', 'activity_type' => 'health_service', 'time_standard_hours' => 0.33, 'annual_volume' => 17520],
            ['activity_name' => 'Medication administration',                  'activity_type' => 'health_service', 'time_standard_hours' => 0.25, 'annual_volume' => 8760],
            ['activity_name' => 'IV line care & maintenance',                 'activity_type' => 'health_service', 'time_standard_hours' => 0.25, 'annual_volume' => 5840],
            ['activity_name' => 'Ventilator & equipment management',          'activity_type' => 'health_service', 'time_standard_hours' => 0.50, 'annual_volume' => 2920],
            ['activity_name' => 'Family communication & counseling',          'activity_type' => 'health_service', 'time_standard_hours' => 0.25, 'annual_volume' => 2920],
            // Support Activities (CAF)
            ['activity_name' => 'Shift handover & briefing',                  'activity_type' => 'support', 'time_standard_hours' => 0.50, 'annual_volume' => null],
            ['activity_name' => 'Multidisciplinary ward rounds',              'activity_type' => 'support', 'time_standard_hours' => 1.00, 'annual_volume' => null],
        ]);

        // ----------------------------------------------------------------
        // 2. EMERGENCY DEPARTMENT — Expected WISN ratio ≈ 0.91 (Borderline)
        // ----------------------------------------------------------------
        $ed = Department::create(array_merge($awtDefaults, [
            'name'          => 'Emergency Department',
            'type'          => 'Emergency',
            'current_staff' => 7,
        ]));

        $ed->activities()->createMany([
            // Health Service Activities (~40 visits/day)
            ['activity_name' => 'Patient triage & initial assessment',  'activity_type' => 'health_service', 'time_standard_hours' => 0.25, 'annual_volume' => 14600],
            ['activity_name' => 'Wound care & dressing',               'activity_type' => 'health_service', 'time_standard_hours' => 0.50, 'annual_volume' => 5475],
            ['activity_name' => 'Medication administration',            'activity_type' => 'health_service', 'time_standard_hours' => 0.17, 'annual_volume' => 10950],
            ['activity_name' => 'IV line insertion & management',       'activity_type' => 'health_service', 'time_standard_hours' => 0.33, 'annual_volume' => 3650],
            ['activity_name' => 'Patient stabilization & monitoring',   'activity_type' => 'health_service', 'time_standard_hours' => 0.50, 'annual_volume' => 2190],
            // Support Activities (CAF)
            ['activity_name' => 'Shift handover',                       'activity_type' => 'support', 'time_standard_hours' => 0.50, 'annual_volume' => null],
            ['activity_name' => 'Documentation & records completion',   'activity_type' => 'support', 'time_standard_hours' => 1.00, 'annual_volume' => null],
        ]);

        // ----------------------------------------------------------------
        // 3. GENERAL MEDICAL WARD — Expected WISN ratio ≈ 0.78 (Critical)
        // ----------------------------------------------------------------
        $medWard = Department::create(array_merge($awtDefaults, [
            'name'          => 'General Medical Ward',
            'type'          => 'Inpatient - Standard',
            'current_staff' => 11,
        ]));

        $medWard->activities()->createMany([
            // Health Service Activities (30 beds, 75% occupancy = 8,213 patient-days/yr)
            ['activity_name' => 'Comprehensive nursing assessment',          'activity_type' => 'health_service', 'time_standard_hours' => 0.50, 'annual_volume' => 16425],
            ['activity_name' => 'Medication administration rounds',          'activity_type' => 'health_service', 'time_standard_hours' => 0.25, 'annual_volume' => 24638],
            ['activity_name' => 'Wound dressing & care',                    'activity_type' => 'health_service', 'time_standard_hours' => 0.50, 'annual_volume' => 2464],
            ['activity_name' => 'Vital signs monitoring',                   'activity_type' => 'health_service', 'time_standard_hours' => 0.08, 'annual_volume' => 32850],
            ['activity_name' => 'Patient education & discharge planning',   'activity_type' => 'health_service', 'time_standard_hours' => 0.33, 'annual_volume' => 8213],
            // Support Activities (CAF)
            ['activity_name' => 'Shift handover',                           'activity_type' => 'support', 'time_standard_hours' => 0.50, 'annual_volume' => null],
            ['activity_name' => 'Administrative documentation',             'activity_type' => 'support', 'time_standard_hours' => 0.50, 'annual_volume' => null],
        ]);

        // ----------------------------------------------------------------
        // 4. SURGICAL WARD — Expected WISN ratio ≈ 1.05 (Adequate)
        // ----------------------------------------------------------------
        $surgWard = Department::create(array_merge($awtDefaults, [
            'name'          => 'Surgical Ward',
            'type'          => 'Surgical/OT',
            'current_staff' => 10,
        ]));

        $surgWard->activities()->createMany([
            // Health Service Activities (20 beds, 70% occupancy = 5,110 patient-days/yr)
            ['activity_name' => 'Post-operative vital signs monitoring',     'activity_type' => 'health_service', 'time_standard_hours' => 0.08, 'annual_volume' => 30660],
            ['activity_name' => 'Surgical wound dressing changes',          'activity_type' => 'health_service', 'time_standard_hours' => 0.50, 'annual_volume' => 5110],
            ['activity_name' => 'Pain assessment & management',             'activity_type' => 'health_service', 'time_standard_hours' => 0.17, 'annual_volume' => 20440],
            ['activity_name' => 'Patient mobilization assistance',          'activity_type' => 'health_service', 'time_standard_hours' => 0.33, 'annual_volume' => 10220],
            ['activity_name' => 'Discharge preparation & patient education','activity_type' => 'health_service', 'time_standard_hours' => 0.50, 'annual_volume' => 2555],
            // Support Activities (CAF)
            ['activity_name' => 'Shift handover & surgical briefing',       'activity_type' => 'support', 'time_standard_hours' => 0.50, 'annual_volume' => null],
            ['activity_name' => 'Surgical ward round attendance',           'activity_type' => 'support', 'time_standard_hours' => 1.00, 'annual_volume' => null],
        ]);
    }
}
```

- [ ] **Step 2: Run the seeder**

```bash
php artisan db:seed
```

Expected output: `Seeding: Database\Seeders\DatabaseSeeder ... Done`

- [ ] **Step 3: Verify seeder data on dashboard**

Open the dashboard. You should see 4 departments with ratios approximately:
- ICU: ~0.71 (Critical — red badge)
- Emergency Department: ~0.91 (Borderline — yellow badge)
- General Medical Ward: ~0.78 (Critical — red badge)
- Surgical Ward: ~1.05 (Adequate — green badge)

---

## Task 6: Enhanced PDF Report

**Files:**
- Modify: `resources/views/reports/wisn-summary.blade.php`
- Modify: `app/Http/Controllers/ReportController.php`

- [ ] **Step 1: Update ReportController to pass AWT breakdown data**

Replace `app/Http/Controllers/ReportController.php`:

```php
<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Services\WisnCalculatorService;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    public function generate(WisnCalculatorService $wisnService)
    {
        $departments = Department::with('activities')->get();
        $departmentResults = [];
        $totalCurrentStaff = 0;
        $totalRequiredStaff = 0;

        foreach ($departments as $dept) {
            $calculation = $wisnService->calculateDepartmentStaffing($dept);
            // Attach AWT breakdown for display in the report
            $calculation['awt_breakdown'] = [
                'working_days'  => $dept->working_days_per_year,
                'public_hols'   => $dept->public_holidays,
                'annual_leave'  => $dept->annual_leave_days,
                'sick_leave'    => $dept->sick_leave_days,
                'training'      => $dept->training_days,
                'hours_per_day' => $dept->working_hours_per_day,
            ];
            $departmentResults[] = $calculation;
            $totalCurrentStaff += $calculation['current_staff'];
            $totalRequiredStaff += $calculation['total_required_staff'];
        }

        $facilityRatio = $totalRequiredStaff > 0
            ? round($totalCurrentStaff / $totalRequiredStaff, 2)
            : 0;

        $pdf = Pdf::loadView('reports.wisn-summary', [
            'departmentResults' => $departmentResults,
            'totalCurrentStaff' => $totalCurrentStaff,
            'totalRequiredStaff'=> round($totalRequiredStaff, 2),
            'facilityRatio'     => $facilityRatio,
            'date'              => now()->format('F j, Y'),
        ]);

        return $pdf->download('WISN_Facility_Staffing_Report.pdf');
    }
}
```

- [ ] **Step 2: Rewrite wisn-summary.blade.php**

Replace entire `resources/views/reports/wisn-summary.blade.php`:

```blade
<!DOCTYPE html>
<html>
<head>
    <title>WISN Nursing Staffing Report</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #333; margin: 0; padding: 20px; }
        .header { text-align: center; margin-bottom: 24px; border-bottom: 3px solid #1d4ed8; padding-bottom: 12px; }
        .header h1 { color: #1d4ed8; margin: 0 0 4px 0; font-size: 20px; }
        .header p { margin: 2px 0; color: #555; font-size: 11px; }
        .metrics { width: 100%; margin-bottom: 20px; border-collapse: collapse; }
        .metrics td { width: 33%; padding: 12px; text-align: center; border: 1px solid #ddd; background: #f0f9ff; }
        .metric-title { font-size: 10px; font-weight: bold; color: #555; text-transform: uppercase; letter-spacing: 0.5px; }
        .metric-value { font-size: 22px; font-weight: bold; margin-top: 4px; }
        .section-title { font-size: 13px; font-weight: bold; color: #1d4ed8; border-bottom: 1px solid #ddd; padding-bottom: 4px; margin: 20px 0 10px 0; }
        table.dept-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table.dept-table th { background: #1d4ed8; color: white; padding: 8px 6px; text-align: left; font-size: 10px; text-transform: uppercase; }
        table.dept-table td { padding: 8px 6px; border-bottom: 1px solid #eee; font-size: 11px; vertical-align: top; }
        table.dept-table tr:nth-child(even) td { background: #f8fafc; }
        .status-critical   { color: #dc2626; font-weight: bold; }
        .status-borderline { color: #ca8a04; font-weight: bold; }
        .status-adequate   { color: #16a34a; font-weight: bold; }
        .status-surplus    { color: #2563eb; font-weight: bold; }
        .awt-box { background: #f0fdf4; border: 1px solid #bbf7d0; padding: 8px 12px; border-radius: 4px; margin-bottom: 6px; font-size: 10px; }
        .guidance-box { background: #fefce8; border: 1px solid #fde68a; padding: 10px 14px; margin-bottom: 10px; border-radius: 4px; }
        .guidance-box h4 { color: #92400e; margin: 0 0 6px 0; font-size: 11px; }
        .guidance-box ul { margin: 0; padding-left: 16px; }
        .guidance-box li { margin-bottom: 3px; font-size: 10px; color: #555; }
        .footer { margin-top: 30px; padding-top: 10px; border-top: 1px solid #ddd; font-size: 10px; text-align: center; color: #888; }
        .breakdown-title { font-size: 10px; font-weight: bold; color: #555; margin-top: 6px; }
        .breakdown-row { font-size: 10px; color: #666; }
    </style>
</head>
<body>

    <div class="header">
        <h1>WHO WISN Nursing Staffing Analysis</h1>
        <p>Workload Indicators of Staffing Need (WISN) Methodology — WHO 2010/2023</p>
        <p>Report Generated: {{ $date }}</p>
    </div>

    {{-- FACILITY SUMMARY --}}
    <table class="metrics">
        <tr>
            <td>
                <div class="metric-title">Current Facility Nurses</div>
                <div class="metric-value">{{ $totalCurrentStaff }}</div>
            </td>
            <td>
                <div class="metric-title">Required Facility Nurses</div>
                <div class="metric-value">{{ $totalRequiredStaff }}</div>
            </td>
            <td>
                <div class="metric-title">Overall WISN Ratio</div>
                <div class="metric-value {{ $facilityRatio < 0.9 ? 'status-critical' : ($facilityRatio < 1 ? 'status-borderline' : 'status-adequate') }}">
                    {{ $facilityRatio }}
                </div>
            </td>
        </tr>
    </table>

    {{-- INTERPRETIVE GUIDANCE --}}
    <div class="guidance-box">
        <h4>How to Interpret the WISN Ratio</h4>
        <ul>
            <li><strong>Ratio &lt; 0.90 (Critical Shortage):</strong> Severe understaffing. Urgent action required — patient safety risk. Consider immediate recruitment or workload redistribution.</li>
            <li><strong>Ratio 0.90–0.99 (Borderline):</strong> Minor shortage. Staff are under pressure. Short-term recruitment or task redistribution is recommended.</li>
            <li><strong>Ratio = 1.00 (Adequate):</strong> Current staffing precisely meets calculated requirements based on actual workload.</li>
            <li><strong>Ratio &gt; 1.00 (Surplus):</strong> More staff than needed for current workload. Consider redeployment or reviewing activity standards.</li>
        </ul>
    </div>

    {{-- DEPARTMENT BREAKDOWN --}}
    <div class="section-title">Department-Level Analysis</div>
    <table class="dept-table">
        <thead>
            <tr>
                <th>Department</th>
                <th>AWT (hrs)</th>
                <th>AWT Breakdown</th>
                <th>Current Staff</th>
                <th>Required Staff</th>
                <th>WISN Ratio</th>
                <th>Status</th>
                <th>Interpretation</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($departmentResults as $result)
            @php
                $gap = $result['current_staff'] - $result['total_required_staff'];
                $abd = $result['awt_breakdown'] ?? null;
            @endphp
            <tr>
                <td><strong>{{ $result['department_name'] }}</strong><br><span style="color:#888;font-size:10px">{{ $result['department_type'] ?? '' }}</span></td>
                <td>{{ $result['awt_hours'] }}</td>
                <td>
                    @if($abd)
                    <span style="font-size:10px;color:#555">
                        ({{ $abd['working_days'] }}&minus;{{ $abd['public_hols']+$abd['annual_leave']+$abd['sick_leave']+$abd['training'] }})&times;{{ $abd['hours_per_day'] }}h
                    </span>
                    @endif
                </td>
                <td>{{ $result['current_staff'] }}</td>
                <td><strong>{{ $result['total_required_staff'] }}</strong></td>
                <td><strong>{{ $result['wisn_ratio'] }}</strong></td>
                <td>
                    @if($result['status'] === 'critical')
                        <span class="status-critical">Critical</span>
                    @elseif($result['status'] === 'borderline')
                        <span class="status-borderline">Borderline</span>
                    @elseif($result['status'] === 'adequate')
                        <span class="status-adequate">Adequate</span>
                    @elseif($result['status'] === 'surplus')
                        <span class="status-surplus">Surplus</span>
                    @else
                        <span style="color:#888">No data</span>
                    @endif
                </td>
                <td style="font-size:10px">
                    @if($gap < 0)
                        Needs {{ abs(round($gap, 1)) }} more nurse(s)
                    @elseif($gap > 0)
                        {{ round($gap, 1) }} nurse(s) available for redeployment
                    @else
                        Staffing balanced
                    @endif
                </td>
            </tr>
            {{-- Activity Breakdown --}}
            @if(!empty($result['breakdown']))
            <tr>
                <td colspan="8" style="padding: 4px 8px 10px 20px; background:#fafafa;">
                    <div class="breakdown-title">Activity Breakdown:</div>
                    @foreach($result['breakdown'] as $item)
                    <div class="breakdown-row">
                        &bull; {{ $item['activity'] }}
                        ({{ $item['type'] }})
                        @if(isset($item['required_staff'])) — {{ $item['required_staff'] }} FTE required @endif
                        @if(isset($item['allowance_percentage'])) — {{ $item['allowance_percentage'] }} of shift @endif
                        @if(isset($item['total_hours'])) — {{ $item['total_hours'] }} hrs/yr @endif
                    </div>
                    @endforeach
                    <div class="breakdown-row" style="margin-top:4px">
                        Health Service FTE: {{ $result['health_service_fte'] }} &times;
                        CAF {{ $result['support_allowance_multiplier'] }}
                        @if($result['aaf_fte'] > 0) + AAF {{ $result['aaf_fte'] }} FTE @endif
                        = <strong>{{ $result['total_required_staff'] }} required</strong>
                    </div>
                </td>
            </tr>
            @endif
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>This report was generated automatically using WHO WISN methodology (WHO, 2010; updated 2023).</p>
        <p>Formula: AWT = (Working Days &minus; Non-Working Days) &times; Hours/Day &nbsp;|&nbsp;
           Standard Workload = AWT &divide; Activity Standard Time &nbsp;|&nbsp;
           Required Staff = &Sigma;(Volume &divide; Standard Workload) &times; CAF + AAF</p>
        <p>WISN Ratio = Current Staff &divide; Required Staff. Target ratio: 1.0</p>
    </div>

</body>
</html>
```

---

## Task 7: In-App Help Page

**Files:**
- Create: `app/Http/Controllers/HelpController.php`
- Create: `resources/views/help.blade.php`
- Modify: `routes/web.php`

- [ ] **Step 1: Create HelpController**

Create `app/Http/Controllers/HelpController.php`:

```php
<?php

namespace App\Http\Controllers;

class HelpController extends Controller
{
    public function index()
    {
        return view('help');
    }
}
```

- [ ] **Step 2: Add help route to routes/web.php**

Add inside the `auth` middleware group, after the report route:

```php
// In-app WISN methodology guide
Route::get('/help', [HelpController::class, 'index'])->name('help');
```

Also add the import at the top:

```php
use App\Http\Controllers\HelpController;
```

- [ ] **Step 3: Create resources/views/help.blade.php**

Create `resources/views/help.blade.php`:

```blade
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            WHO WISN Methodology Guide
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                <h3 class="text-lg font-bold text-blue-900 mb-2">What is WISN?</h3>
                <p class="text-gray-700 text-sm leading-relaxed">
                    The <strong>Workload Indicators of Staffing Need (WISN)</strong> methodology was developed by the World Health Organization (WHO)
                    to provide an evidence-based approach to calculating nursing and health workforce requirements.
                    Unlike population-to-staff ratios, WISN grounds calculations in the <em>actual workload</em> performed by health workers.
                </p>
            </div>

            <div class="bg-white shadow-sm rounded-lg p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4">The 4-Step Process in This Tool</h3>
                <div class="space-y-4">
                    <div class="flex gap-4">
                        <div class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold text-sm flex-shrink-0">1</div>
                        <div>
                            <p class="font-semibold text-gray-800">Create a Department</p>
                            <p class="text-sm text-gray-600 mt-1">Define the ward name, type, current nurse count, and Available Working Time (AWT) breakdown. AWT = the hours a nurse is available for productive work after subtracting holidays, leave, and training days.</p>
                            <p class="text-xs text-gray-400 mt-1">Formula: AWT = (Working Days − Public Holidays − Annual Leave − Sick Leave − Training Days) × Hours per Day</p>
                        </div>
                    </div>
                    <div class="flex gap-4">
                        <div class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold text-sm flex-shrink-0">2</div>
                        <div>
                            <p class="font-semibold text-gray-800">Add Health Service Activities</p>
                            <p class="text-sm text-gray-600 mt-1">List every direct patient care task performed in the department. For each, enter the time standard (how long it takes in hours) and the annual volume (how many times it occurs in a year). The tool computes Standard Workload = AWT ÷ Time Standard, then Required Staff = Annual Volume ÷ Standard Workload.</p>
                        </div>
                    </div>
                    <div class="flex gap-4">
                        <div class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold text-sm flex-shrink-0">3</div>
                        <div>
                            <p class="font-semibold text-gray-800">Add Support Activities</p>
                            <p class="text-sm text-gray-600 mt-1">Add recurring shift duties that consume nursing time but are not direct patient care (e.g., shift handover, ward rounds attendance, administrative documentation). Enter only the time standard in hours per 8-hour shift. The tool calculates the Category Allowance Factor (CAF) = 1 ÷ (1 − total support fraction), which adjusts the clinical staff requirement upward to account for this time.</p>
                            <p class="text-xs text-gray-400 mt-1">Optionally add "Additional" type activities for cross-department duties like teaching, which are calculated as AAF (Additional Allowance Factor).</p>
                        </div>
                    </div>
                    <div class="flex gap-4">
                        <div class="w-8 h-8 rounded-full bg-green-600 text-white flex items-center justify-center font-bold text-sm flex-shrink-0">4</div>
                        <div>
                            <p class="font-semibold text-gray-800">View Results on the Dashboard</p>
                            <p class="text-sm text-gray-600 mt-1">The dashboard shows each department's WISN Ratio = Current Staff ÷ Required Staff. A ratio below 1.0 indicates a shortage; above 1.0 indicates a surplus. Download a PDF report for hospital administration boards.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white shadow-sm rounded-lg p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4">WISN Ratio Interpretation</h3>
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="px-4 py-3 text-left font-medium text-gray-600">WISN Ratio</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-600">Status</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-600">Recommended Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr>
                            <td class="px-4 py-3 font-bold text-red-600">&lt; 0.90</td>
                            <td class="px-4 py-3"><span class="px-2 py-1 rounded-full bg-red-100 text-red-800 text-xs font-semibold">Critical Shortage</span></td>
                            <td class="px-4 py-3 text-gray-600">Urgent recruitment required. Patient safety risk. Consider task redistribution immediately.</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-3 font-bold text-yellow-600">0.90 – 0.99</td>
                            <td class="px-4 py-3"><span class="px-2 py-1 rounded-full bg-yellow-100 text-yellow-800 text-xs font-semibold">Borderline</span></td>
                            <td class="px-4 py-3 text-gray-600">Staff are under pressure. Short-term recruitment or task redistribution recommended.</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-3 font-bold text-green-600">1.00</td>
                            <td class="px-4 py-3"><span class="px-2 py-1 rounded-full bg-green-100 text-green-800 text-xs font-semibold">Adequate</span></td>
                            <td class="px-4 py-3 text-gray-600">Staffing precisely meets calculated requirements. Monitor as workload changes.</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-3 font-bold text-blue-600">&gt; 1.00</td>
                            <td class="px-4 py-3"><span class="px-2 py-1 rounded-full bg-blue-100 text-blue-800 text-xs font-semibold">Surplus</span></td>
                            <td class="px-4 py-3 text-gray-600">More staff than current workload requires. Consider redeployment to understaffed departments.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="bg-white shadow-sm rounded-lg p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-3">Key Formulas</h3>
                <div class="space-y-2 text-sm font-mono bg-gray-50 p-4 rounded text-gray-700">
                    <p><strong>AWT</strong> = (Working Days − Public Holidays − Annual Leave − Sick Leave − Training Days) × Hours/Day</p>
                    <p><strong>Standard Workload</strong> = AWT ÷ Activity Time Standard</p>
                    <p><strong>Health Service FTE</strong> = Σ (Annual Volume ÷ Standard Workload) for all Health Service activities</p>
                    <p><strong>CAF</strong> = 1 ÷ (1 − Σ Support Activity Fractions)</p>
                    <p><strong>AAF FTE</strong> = Σ (Annual Volume × Time Standard) ÷ AWT for Additional activities</p>
                    <p><strong>Required Staff</strong> = (Health Service FTE × CAF) + AAF FTE</p>
                    <p><strong>WISN Ratio</strong> = Current Staff ÷ Required Staff</p>
                </div>
            </div>

            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 text-sm text-gray-500">
                <strong>Reference:</strong> World Health Organization. (2023). <em>Workload Indicators of Staffing Need (WISN): User's Manual</em> (updated ed.).
                WHO, Geneva. Based on WHO WISN methodology (2010, updated 2023).
            </div>

        </div>
    </div>
</x-app-layout>
```

---

## Final: Run Everything

- [ ] **Run migrations and seed**

```bash
php artisan migrate
php artisan db:seed
php artisan serve
```

- [ ] **Verify in browser**
  - Dashboard shows 4 departments with bar charts and ratios
  - ICU shows Critical (~0.71), Emergency shows Borderline (~0.91), Surgical shows Adequate (~1.05)
  - Department create/edit form shows 6 AWT breakdown fields with live preview
  - Activity page shows Edit/Delete buttons and stepper
  - `/help` page loads the WISN guide
  - PDF download includes activity breakdown and interpretive guidance

---

## Self-Review Against Spec

| Gap | Addressed | Task |
|---|---|---|
| AWT breakdown (leave days, holidays) | ✅ | Task 1 |
| Interactive charts (Chart.js) | ✅ | Task 4 |
| Contextual tooltips on all form fields | ✅ | Tasks 1, 2 |
| Step-by-step WISN workflow stepper | ✅ | Task 2 |
| Activity edit/delete | ✅ | Task 2 |
| AAF (Additional Allowance Factor) | ✅ | Task 3 |
| Simulated test data seeders | ✅ | Task 5 |
| PDF report enhancement | ✅ | Task 6 |
| In-app help/guidance page | ✅ | Task 7 |
| Hospital/Facility model | ⚠️ Out of scope for prototype — all departments are under one implicit facility |
