# ARCHITECTURE.md — wisn-core

## Overview

Single-facility web prototype implementing WHO WISN (Workload Indicators of Staffing Need) methodology for nursing workforce planning in Nepalese hospitals. Built on Laravel 10, Blade/Tailwind frontend, DomPDF for PDF generation, Chart.js for visualisation.

---

## Request flow

```
Browser
  └─ routes/web.php  (auth + verified middleware)
       ├─ DashboardController       → WisnCalculatorService → dashboard.blade.php
       ├─ DepartmentController      → departments/*.blade.php
       ├─ WorkloadActivityController → activities/*.blade.php
       ├─ ReportController          → WisnCalculatorService → DomPDF → download
       └─ HelpController            → help.blade.php
```

All routes require authentication (Laravel Breeze) and email verification.

---

## Database schema

### `departments`

| Column | Type | Notes |
|---|---|---|
| `id` | bigint PK | |
| `name` | string | e.g. "Intensive Care Unit (ICU)" |
| `type` | string nullable | "Inpatient - Standard", "Inpatient - High Acuity", "Outpatient", "Emergency", "Surgical/OT" |
| `current_staff` | integer | actual nurse headcount assigned to this dept |
| `working_days_per_year` | smallint | default 260 |
| `public_holidays` | smallint | default 13 |
| `annual_leave_days` | smallint | default 18 |
| `sick_leave_days` | smallint | default 12 |
| `training_days` | smallint | default 5 |
| `working_hours_per_day` | tinyint | default 8 |
| `created_at` / `updated_at` | timestamps | |

**Computed accessor** (not a real column): `available_working_time_hours`
```
AWT = (working_days_per_year - public_holidays - annual_leave_days
       - sick_leave_days - training_days) × working_hours_per_day
```
Accessible as `$department->available_working_time_hours` throughout the codebase via `Attribute::get()`.

### `workload_activities`

| Column | Type | Notes |
|---|---|---|
| `id` | bigint PK | |
| `department_id` | bigint FK | cascades on delete |
| `activity_name` | string | e.g. "Medication administration" |
| `activity_type` | enum | `health_service`, `support`, `additional` |
| `time_standard_hours` | decimal(8,4) | hours per occurrence (HS/Additional) or hours per shift (Support) |
| `annual_volume` | integer nullable | total occurrences/year; required for health_service + additional, null for support |
| `created_at` / `updated_at` | timestamps | |

### `users` (Laravel Breeze default)

Standard Laravel auth table. No ownership link to departments — all users share all departments (single-facility prototype).

---

## Models

### `App\Models\Department`

- `$guarded = []`
- `hasMany(WorkloadActivity::class)`
- Accessor `availableWorkingTimeHours(): Attribute` — computes AWT from 6 breakdown columns

### `App\Models\WorkloadActivity`

- `$guarded = []`
- `belongsTo(Department::class)`

---

## Service layer: `WisnCalculatorService`

**Location:** `app/Services/WisnCalculatorService.php`

Single public method: `calculateDepartmentStaffing(Department $department): array`

### Calculation steps

```
1. For each health_service activity:
     standardWorkload = AWT / time_standard_hours
     requiredFTE      = annual_volume / standardWorkload
     healthServiceFTE += requiredFTE

2. For each support activity:
     fraction = time_standard_hours / working_hours_per_day
     supportFractionTotal += fraction

3. For each additional activity:
     aafHours += annual_volume * time_standard_hours

4. CAF = 1 / (1 - supportFractionTotal)   [guard: only if 0 < fraction < 1]

5. aafFte = aafHours / AWT                [guard: only if AWT > 0]

6. totalRequired = (healthServiceFTE × CAF) + aafFte

7. wisnRatio = current_staff / totalRequired   [guard: only if totalRequired > 0]
```

### Return array keys

```php
[
  'department_id'                => int,
  'department_name'              => string,
  'department_type'              => string,
  'current_staff'                => int,
  'awt_hours'                    => int,           // computed AWT
  'health_service_fte'           => float (2dp),
  'support_allowance_multiplier' => float (3dp),   // CAF
  'aaf_fte'                      => float (2dp),
  'total_required_staff'         => float (2dp),
  'wisn_ratio'                   => float (2dp),
  'status'                       => string,        // no_data|critical|borderline|adequate|surplus
  'breakdown'                    => array,         // per-activity detail rows
]
```

### Status thresholds

```
ratio <= 0    → 'no_data'
ratio < 0.90  → 'critical'
ratio < 1.00  → 'borderline'
ratio == 1.00 → 'adequate'
ratio > 1.00  → 'surplus'
```

---

## Controllers

### `DashboardController`

- Injects `WisnCalculatorService` via constructor
- Loads all `Department::with('activities')`
- Calls `calculateDepartmentStaffing()` for each
- Computes facility-level ratio: `Σcurrent / Σrequired`
- Passes `$departmentResults`, `$totalCurrentStaff`, `$totalRequiredStaff`, `$facilityRatio` to `dashboard`

### `DepartmentController`

- Standard resource controller
- `validationRules()` private helper validates all 6 AWT breakdown fields
- Cross-field guard in `store()` and `update()`: rejects if `net_days = working_days - Σleaves ≤ 0`

### `WorkloadActivityController`

- Routes nested under `departments/{department}`
- Route model binding on both `{department}` and `{activity}`
- `validationRules()` requires `annual_volume` for `health_service` and `additional` types, nullable for `support`
- Full CRUD: index, store, edit, update, destroy

### `ReportController`

- Same calculation logic as DashboardController (no shared abstraction — by design, keeps report self-contained)
- Attaches `awt_breakdown` array to each result for display in PDF
- Uses `Barryvdh\DomPDF\Facade\Pdf::loadView()`

### `HelpController`

- Single `index()` returning `view('help')` — static content only

---

## Routes

All routes are under `middleware(['auth', 'verified'])`.

```
GET  /dashboard                                          dashboard
GET  /departments                                        departments.index
GET  /departments/create                                 departments.create
POST /departments                                        departments.store
GET  /departments/{department}/edit                      departments.edit
PUT  /departments/{department}                           departments.update
DELETE /departments/{department}                         departments.destroy

GET  /departments/{department}/activities                activities.index
POST /departments/{department}/activities                activities.store
GET  /departments/{department}/activities/{activity}/edit activities.edit
PUT  /departments/{department}/activities/{activity}     activities.update
DELETE /departments/{department}/activities/{activity}   activities.destroy

GET  /report                                             report.generate
GET  /help                                               help
GET  /profile                                            profile.edit
PATCH /profile                                           profile.update
DELETE /profile                                          profile.destroy
```

Auth routes (register/login/password reset) are in `routes/auth.php`.

---

## Views

```
resources/views/
  welcome.blade.php                  public landing page
  dashboard.blade.php                summary cards, Chart.js charts, department table
  help.blade.php                     static WISN methodology reference

  layouts/
    app.blade.php                    master layout — has @stack('scripts') before </body>
    guest.blade.php                  unauthenticated layout
    navigation.blade.php             top nav bar

  departments/
    index.blade.php                  table with Edit / Activities / Delete
    create.blade.php                 form with 6-field AWT breakdown + live JS preview
    edit.blade.php                   same form, pre-populated

  activities/
    index.blade.php                  dynamic WISN stepper + add form + activity table
    edit.blade.php                   edit single activity

  reports/
    wisn-summary.blade.php           DomPDF PDF template (inline CSS only, no external sheets)

  auth/                              Laravel Breeze auth views (6 files)
  profile/                           profile edit partials (4 files)
  components/                        Blade UI components (buttons, inputs, etc.)
```

### Dynamic WISN stepper (activities/index.blade.php)

Computed from the `$activities` collection — no extra query:

```php
$hasHealthService = $activities->where('activity_type', 'health_service')->count() > 0;
$hasSupport       = $activities->where('activity_type', 'support')->count() > 0;
```

| Step 1 | Step 2 | Step 3 | Step 4 |
|---|---|---|---|
| Always ✓ green | ✓ green if health_service exists, else blue (active) | ✓ green if support exists, blue if step 2 done, gray otherwise | Clickable link only when both step 2+3 done |

---

## Frontend

- **CSS**: Tailwind CSS via Vite (`resources/css/app.css`)
- **JS**: Minimal vanilla JS; `@push('scripts')` / `@stack('scripts')` pattern used in create/edit views for AWT live preview
- **Charts**: Chart.js 4.4.0 loaded from CDN only on `dashboard.blade.php`
- **PDF**: DomPDF (`barryvdh/laravel-dompdf`); PDF view must use inline `<style>` only

---

## Key design decisions

| Decision | Rationale |
|---|---|
| AWT as computed accessor, not stored column | The 6 breakdown fields are the source of truth. All existing service code that reads `$department->available_working_time_hours` keeps working with zero changes. |
| `WisnCalculatorService` as injected service | Keeps calculation logic testable and separate from HTTP layer. Used by both Dashboard and Report controllers. |
| No Hospital/Facility model | Prototype scope. All departments are under one implicit facility. Future: add `hospitals` table with `hospital_id` FK on `departments`. |
| Activities nested under departments in routes | Makes route model binding clean and scopes all activity operations to their department. |
| DomPDF for PDF | Available as a Composer package, no external service, works with Blade templates. Constraint: inline CSS only. |
| Chart.js via CDN | Avoids adding JS build complexity. Only loaded on the dashboard page via `@push('scripts')`. |
| Seeder is destructive | Prototype assumption: simulated test data. Never run `db:seed` with real hospital data loaded. |

---

## Operational staffing overlay (added 2026-10)

The `/operations` module is a separate shift-level decision-support flow. It does not change `WisnCalculatorService`, annual activity volumes, AWT, WISN ratios, or `departments.current_staff`.

### Operational data model

- `operational_rounds`: timestamp and user for one manually entered facility-wide snapshot.
- `department_operational_snapshots`: one row per department and round; stores aggregate patient count, high-acuity count, on-duty staff, locally assessed required on-duty staff, and notes.
- `mobilization_recommendations`: aggregate count proposal from one department to another, with pending/approved/declined/superseded status and reviewer audit fields.

No dedicated patient-identifier fields or named nurse roster are stored. Free-text notes are available, so users are instructed not to enter patient identifiers. `required_on_duty_staff` must come from the facility's locally approved shift policy; the prototype intentionally does not invent patient-to-nurse ratios. High-acuity counts are context only in this iteration.

### Operational request flow

```text
POST /operations/snapshots
  → OperationalSnapshotController
  → OperationalStaffingService::recordRound()
  → append-only timestamped snapshots + count-level recommendations

POST /operations/recommendations/{id}/approve|decline
  → MobilizationRecommendationController
  → OperationalStaffingService review with transaction/row locks
  → audit status; do not change roster or department headcount
```

The recommendation allocator only moves counts above a sending unit's entered target to units below their entered target. A new snapshot supersedes pending proposals from older rounds. Approval is blocked if the snapshot is no longer the latest, has exceeded `OPERATIONS_STALE_AFTER_MINUTES` (default 30), or the approved count would breach the recorded source target or exceed the destination target. Departments with operational history cannot be hard-deleted; retain them so past snapshots and recommendations remain auditable. A later archive/deactivate flow can replace this prototype guard.

### Current prototype limits and safety boundary

- Census and shift staffing are manually entered; this is not an EHR/roster integration or push-based real-time feed. The UI marks the data as a manual snapshot and displays staleness.
- Proposals are aggregate counts, not named nurse assignments. Competency, specialty, availability, legal shift rules, and actual execution still require human verification outside the application.
- The app has no role/permission model beyond authenticated and verified users. The current approval route is therefore suitable for a prototype only; restrict approval to authorized nursing managers before operational deployment.
- The snapshot's locally assessed required coverage is not an automatic clinical decision. Validate and configure the policy with the hospital before using recommendations.
- Staff turnover remains a separate longer-term workforce-supply measure; it is not used as a live on-duty count or as an input to the WISN formula.
