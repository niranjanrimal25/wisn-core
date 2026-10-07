# WISN Core — Application Architecture

This file is the architecture reference for the current Laravel application. It maps the major screens and user actions to routes, controllers, services, models, database tables, and views. It is not the click-by-click beginner operating guide; that walkthrough will be prepared separately.

## 1. What the application does

WISN Core is a single-facility Laravel 10 prototype with two related but deliberately separate areas:

1. **Annual WISN workforce planning.** Department workload activities, time standards, available working time, and annual staff headcount are used to calculate required staffing and a WISN ratio.
2. **Operations census and shift coverage.** Inpatient census is entered as directly observed counts at 12:00 AM, 7:00 AM, and 7:00 PM. Actual on-duty nurse counts are entered at 7:00 AM and 7:00 PM only. Optional unit-and-shift targets can calculate required nurses and aggregate move suggestions for human review.

Operations does not change WISN results, annual headcount, a staff roster, or actual on-duty counts. The app is not connected to an EHR or live roster and does not store patient names or assign named nurses.

## 2. Current empty-database test state

The operator reports having run `php artisan migrate:fresh` and then seeded only one administrator. For that test run, the intended initial state is:

- the migrations have created the tables;
- one administrator account exists;
- there are no departments, workload activities, census rounds, snapshots, recommendations, or staffing standards.

`migrate:fresh` drops and recreates the database tables; it does not itself create the administrator. `AdminUserSeeder` creates/updates the demo administrator. **Do not run the full `DatabaseSeeder` when you want to practise entering everything manually:** it deletes current workload activities and departments before inserting sample departments and activities. The full seeder is intended only for a disposable demo database. No operational staffing ratio is seeded by either seeder.

The demo seeder sets `admin@wisn.org` to a demo password and administrator access. Change demo credentials and review account provisioning before any real deployment.

## 3. Request and code-flow model

A typical browser request follows this path:

```text
Browser form or link
  → named route in routes/web.php (or routes/auth.php)
  → middleware (authentication, optional email-verification gate, admin role)
  → controller action
  → validation and business logic
  → Eloquent model / relationship and database query
  → Blade view or redirect/download response
```

The application uses server-rendered Blade pages. There is no separate front-end API or client-side application controlling the workflows. Forms use Laravel CSRF tokens; `@method('PUT')`, `@method('PATCH')`, and `@method('DELETE')` make HTML forms submit the corresponding Laravel method.

### Important implementation note about email verification

`routes/web.php` places application screens inside `auth` and `verified` middleware. However, `App\\Models\\User` currently does not implement Laravel's `MustVerifyEmail` contract—the import is commented out. Laravel's `verified` middleware only enforces verified-email state for users implementing that contract. Treat the current route declaration and actual enforcement as distinct until this is deliberately confirmed or changed.

## 4. Routes and their responsibilities

### Public and authentication routes

`/` returns the public welcome page. `routes/auth.php` provides Breeze registration, login, logout, password reset, email-verification, and password-confirmation routes. Registration creates a regular user; the database default for `is_admin` is false. The `is_admin` flag is not mass assignable through the normal user profile form.

### Authenticated application routes

The following routes are declared in `routes/web.php` inside the `auth` and `verified` group:

| Method and URL | Route name | Controller/action | Main result |
|---|---|---|---|
| `GET /dashboard` | `dashboard` | `DashboardController@index` | Calculates annual WISN results and renders `resources/views/dashboard.blade.php`. |
| `GET /operations` | `operations.index` | `OperationalDashboardController@index` | Reads a date/shift selection and renders the inpatient census and shift-staffing page. |
| `POST /operations/snapshots` | `operations.snapshots.store` | `OperationalSnapshotController@store` | Validates a complete set of unit counts and asks `OperationalStaffingService` to save a new round. |
| `GET /operations/standards` | `operations.standards.index` | `OperationalStaffingStandardController@index` | Lists staffing targets and shows the admin configuration form. Admin middleware applies. |
| `POST /operations/standards` | `operations.standards.store` | `OperationalStaffingStandardController@store` | Creates a source-backed target, optionally active/approved. Admin middleware applies. |
| `PATCH /operations/standards/{standard}/status` | `operations.standards.status` | `OperationalStaffingStandardController@updateStatus` | Activates or deactivates a target. Admin middleware applies. |
| `POST /operations/recommendations/{recommendation}/approve` | `operations.recommendations.approve` | `MobilizationRecommendationController@approve` | Records an aggregate recommendation review through the staffing service. |
| `POST /operations/recommendations/{recommendation}/decline` | `operations.recommendations.decline` | `MobilizationRecommendationController@decline` | Records a decline through the staffing service. |
| Resource routes for `/departments` | `departments.*` | `DepartmentController` | Lists, creates, edits, updates, shows, or deletes a department. The current `show()` action is empty; the UI uses the index, create, and edit pages. |
| `GET /departments/{department}/activities` | `activities.index` | `WorkloadActivityController@index` | Lists the activities belonging to a department and displays the add-activity form. |
| `POST /departments/{department}/activities` | `activities.store` | `WorkloadActivityController@store` | Validates and creates an activity through the department relationship. |
| `GET /departments/{department}/activities/{activity}/edit` | `activities.edit` | `WorkloadActivityController@edit` | Loads the edit form. |
| `PUT /departments/{department}/activities/{activity}` | `activities.update` | `WorkloadActivityController@update` | Validates and updates the activity. |
| `DELETE /departments/{department}/activities/{activity}` | `activities.destroy` | `WorkloadActivityController@destroy` | Deletes the activity. |
| `GET /report` | `report.generate` | `ReportController@generate` | Calculates annual WISN, renders the PDF Blade template with DomPDF, and downloads a facility report. |
| `GET /help` | `help` | `HelpController@index` | Renders static methodology help. |
| `GET /profile` | `profile.edit` | `ProfileController@edit` | Displays the signed-in user's profile, password, and account-deletion sections. |
| `PATCH /profile` | `profile.update` | `ProfileController@update` | Updates the user's name/email; changing email clears `email_verified_at`. |
| `PUT /password` | `password.update` | Breeze `PasswordController@update` | Updates the signed-in user's password. |
| `DELETE /profile` | `profile.destroy` | `ProfileController@destroy` | Requires the current password, logs out, deletes the user's account, and invalidates the session. |

There is **no separate administrator profile page or separate Admin Profile controller**. Administrators use the same profile routes and views as other users; the administrator role only unlocks staffing-standard configuration. Recommendation approve/decline routes require authentication, but are not restricted to administrators by the current route group.

## 5. Main files and layers

| Concern | Key files |
|---|---|
| Main route registration | `routes/web.php`, `routes/auth.php` |
| Middleware | `app/Http/Kernel.php`, `app/Http/Middleware/EnsureUserIsAdmin.php` |
| Department CRUD | `app/Http/Controllers/DepartmentController.php`, `resources/views/departments/` |
| Activity CRUD | `app/Http/Controllers/WorkloadActivityController.php`, `app/Models/WorkloadActivity.php`, `resources/views/activities/` |
| Annual WISN calculation | `app/Services/WisnCalculatorService.php` |
| Annual dashboard | `app/Http/Controllers/DashboardController.php`, `resources/views/dashboard.blade.php` |
| WISN report download | `app/Http/Controllers/ReportController.php`, `resources/views/reports/wisn-summary.blade.php` |
| Operations dashboard and entry | `app/Http/Controllers/OperationalDashboardController.php`, `app/Http/Controllers/OperationalSnapshotController.php`, `app/Services/OperationalStaffingService.php`, `resources/views/operations/index.blade.php` |
| Staffing standards | `app/Http/Controllers/OperationalStaffingStandardController.php`, `app/Models/OperationalStaffingStandard.php`, `resources/views/operations/standards.blade.php` |
| Move suggestion review | `app/Http/Controllers/MobilizationRecommendationController.php`, `app/Models/MobilizationRecommendation.php` |
| User profile and authentication | `app/Http/Controllers/ProfileController.php`, `app/Http/Controllers/Auth/`, `resources/views/profile/`, `resources/views/auth/` |
| Database structure and sample data | `database/migrations/`, `database/seeders/` |

## 6. Departments, classifications, AWT, and activities

### Department fields and where their choices come from

The department create/edit forms are `resources/views/departments/create.blade.php` and `edit.blade.php`. `DepartmentController@create` and `@edit` pass the operational classification choices into those views.

| Form item | Current source and meaning |
|---|---|
| Department Name | Free text stored in `departments.name`. |
| Department Type (`type`) | The current choice list is written directly in the Blade form: `Inpatient - Standard`, `Inpatient - High Acuity`, `Outpatient`, `Emergency`, and `Surgical/OT`. It is a WISN/report classification, not the Operations ratio lookup key. Server validation currently requires a string but does not validate against this exact list. |
| Operations Unit Classification (`operational_unit_type`) | Options come from `Department::OPERATIONAL_UNIT_TYPES`, passed from `DepartmentController` to the view. Values include general, pediatric, maternity, surgical, ICU, PICU, NICU, Emergency admitted/observation, other inpatient, and outpatient. The outpatient category is excluded from Operations. |
| Current Staff Headcount (WISN) | Integer in `departments.current_staff`. It is annual planning headcount used in the WISN ratio, **not** the number of nurses on duty at a handover. |
| AWT inputs | Six stored fields: `working_days_per_year`, `public_holidays`, `annual_leave_days`, `sick_leave_days`, `training_days`, and `working_hours_per_day`. The create form defaults to 260, 13, 18, 12, 5, and 8 respectively; these are editable planning values, not immutable application constants. |
| Available Working Time | Not stored as a separate current column. `Department::availableWorkingTimeHours()` calculates it from those six input fields. The browser shows a JavaScript preview; server-side WISN calculations read the model accessor. |

The standard example defaults yield:

```text
AWT = (260 - 13 - 18 - 12 - 5) × 8 = 1,696 hours per nurse per year
```

The database migration includes defaults for the AWT breakdown. `DepartmentController` validates input ranges and also rejects a set of leave/holiday/training deductions that leave zero or negative net working days. `Department::create($validated)` or `$department->update($validated)` persists the submitted fields. If `type` is exactly `Outpatient`, the controller forces the operational classification to `outpatient`; if it is exactly `Emergency`, it forces `emergency_inpatient`.

### Department data flow

```text
GET /departments
  → DepartmentController@index
  → Department::withCount('activities')
  → departments.index

GET /departments/create
  → DepartmentController@create
  → Department::OPERATIONAL_UNIT_TYPES passed to departments.create

POST /departments
  → DepartmentController@store
  → validation + positive net AWT days check + Outpatient/Emergency mapping
  → Department::create($validated)
  → departments.index redirect
```

`Route::resource('departments', ...)` also registers edit/update/delete/show actions. Deleting a department with operational snapshots is blocked by the controller to protect history; otherwise the database relationship cascades deletion to workload activities. Operational tables also use restrictive department foreign keys.

### Workload activities

`WorkloadActivity` belongs to a `Department`; a department has many activities. The table is `workload_activities`, with `department_id`, `activity_name`, `activity_type`, `time_standard_hours`, and nullable `annual_volume`.

- `health_service`: direct clinical workload; time standard and annual volume contribute to health-service FTE.
- `support`: work such as handover/meetings; the annual volume is nullable, and service logic treats the time standard as hours per shift.
- `additional`: an additional allowance such as teaching; time standard and annual volume contribute to additional hours/FTE.

Activity type choices are shown in `resources/views/activities/index.blade.php` and `edit.blade.php`; the database enum and controller validation use `health_service`, `support`, and `additional`. `WorkloadActivityController` validates the name, type, positive numeric time standard, and annual volume for health-service/additional activities. On create it calls `$department->activities()->create($validated)`. The dashboard/report calculate from the saved activities on each request; calculated WISN results are not saved as department columns.

## 7. Annual WISN calculation and screens

### `WisnCalculatorService`

`app/Services/WisnCalculatorService.php` is the calculation layer used by both the dashboard and PDF report. Given a department with activities, it:

1. Reads AWT from the `Department` accessor.
2. For each health-service activity, calculates standard workload as `AWT / time_standard_hours`, then required FTE as `annual_volume / standard_workload`.
3. For support activities, sums `time_standard_hours / working_hours_per_day` into a support fraction.
4. Calculates the category allowance factor (CAF) as `1 / (1 - support_fraction)` only when the combined fraction is greater than zero and less than one; otherwise the implementation leaves CAF at 1.
5. Calculates additional allowance FTE as `(annual_volume × time_standard_hours) / AWT` when AWT is positive.
6. Calculates total required staff as `(health_service_FTE × CAF) + additional_FTE`.
7. Calculates WISN ratio as `current_staff / total_required_staff` when total required staff is positive; otherwise it returns zero/no-data status.

Status thresholds in the current service are: ratio ≤ 0 → `no_data`; below 0.90 → `critical`; below 1.00 → `borderline`; exactly 1.00 → `adequate`; above 1.00 → `surplus`.

### Annual dashboard and report flow

```text
GET /dashboard
  → DashboardController@index
  → Department::with('activities')->get()
  → WisnCalculatorService::calculateDepartmentStaffing() per department
  → facility totals/ratio
  → resources/views/dashboard.blade.php
```

The facility ratio is `sum(current_staff) / sum(total_required_staff)`, with a zero guard. The report route uses the same calculation service, adds each department's AWT input breakdown, then calls `Pdf::loadView('reports.wisn-summary', ...)` and downloads `WISN_Facility_Staffing_Report.pdf`. This is the current PDF export; there is no separate Operations PDF report route.

## 8. Operations: inpatient census and on-duty staffing

### Categories and time points

`Department::OPERATIONAL_UNIT_TYPES` provides the unit taxonomy. OPD/outpatient units are filtered out of the Operations screens and snapshot submissions. Emergency counts mean admitted or observation-bed patients, not walk-in visits.

`OperationalRound` supports:

- `midnight`: census at exactly 12:00 AM; no on-duty nurse count and no staffing recommendation calculation.
- `day`: 7:00 AM handover; census plus actual nurses who came on duty.
- `night`: 7:00 PM handover; census plus actual nurses who came on duty.

Census is entered directly as observed; there is no admissions/discharges/transfers calculation. A same-day submission is rejected until its scheduled observation time has passed. Dates use `OPERATIONS_TIMEZONE` (default `Asia/Kathmandu`); day/night recommendation freshness uses `OPERATIONS_RECOMMENDATION_FRESHNESS_MINUTES` (default 30 minutes).

### Operational storage

| Table | Role |
|---|---|
| `operational_rounds` | One complete handover submission/revision: census date, shift code, scheduled `observed_at`, actual `captured_at`, entering user, and round notes. |
| `department_operational_snapshots` | One row per inpatient department and round: observed patient count, on-duty count when applicable, selected staffing-standard ID, calculated required count when applicable, and notes. A unique round/department pair prevents duplicate unit rows in one round. |
| `operational_staffing_standards` | Admin-entered target by inpatient unit type and day/night shift: patients per nurse, optional minimum, source/name/version/URL, effective dates, active state, approver, and approval time. No target ratios are seeded. |
| `mobilization_recommendations` | Aggregate proposed nurse counts between source and destination units, rationale, status, reviewer, review time, and decision note. |

Older operational migrations include `high_acuity_patient_count` and `required_on_duty_staff` columns. They remain for migration/backward compatibility, but the current Operations form and service use direct census, actual on-duty counts, `staffing_standard_id`, and `calculated_required_staff` instead.

### Standards and requirement calculation

Only an active, approved standard whose unit type, shift, and effective date match is applicable. For a staffed handover:

```text
required nurses = max(minimum nurses per shift,
                      ceil(observed patients / patients per nurse))
```

If no standard applies, the saved required count remains blank and the unit is ineligible for target-based suggestions. The selected standard ID is saved with the snapshot, preserving which version informed the calculation. Admin activation of a new standard manages effective periods and supersedes pending move suggestions.

### Operations data flow and safety checks

```text
GET /operations?date=...&shift=...
  → OperationalDashboardController@index
  → latest saved revision per handover + units + applicable standards
  → resources/views/operations/index.blade.php

POST /operations/snapshots
  → OperationalSnapshotController@store validates date, handover, counts,
    and that every current inpatient unit is included (OPD excluded)
  → OperationalStaffingService::recordRound runs in a transaction
  → snapshots saved with the applicable standard and calculated requirement
  → aggregate donor/recipient suggestions generated for 7 AM/7 PM only

POST /operations/recommendations/{id}/approve|decline
  → MobilizationRecommendationController
  → OperationalStaffingService records review
```

A correction creates a new round rather than overwriting the previous round; the dashboard shows the latest revision for that date/shift. A new round supersedes older pending suggestions. A target activation/status change also supersedes pending suggestions. Before an approval is saved, the service checks that the recommendation is still pending, the round is the latest complete round, the 7 AM/7 PM snapshot is fresh, the inpatient-unit list has not changed, the saved standards remain applicable, and the proposed move will not put its source below or destination above its recorded requirement.

The matching logic creates count-level proposals from units with surplus to units with deficits; it is a prioritization aid, not a clinical schedule. Approval/decline only records a human review. It never changes on-duty counts, department headcount, the roster, or WISN results. `DepartmentController@destroy` refuses to delete departments with operational history.

## 9. Administrator and user profiles

There is one profile flow for every signed-in account:

- Profile information form → `PATCH /profile` → `ProfileController@update` → `ProfileUpdateRequest` → save `name`/`email` on `User`.
- Password form → `PUT /password` → Breeze `PasswordController`.
- Delete account form → `DELETE /profile` → `ProfileController@destroy`; current password is required, then the session is logged out/invalidated and the user is deleted.

`users.is_admin` is a boolean added by the Operations migration and defaults to false. `EnsureUserIsAdmin` is registered as the `admin` middleware alias in `app/Http/Kernel.php`. It protects only the target-standard configuration routes. `AdminUserSeeder` assigns the demo admin flag directly. There is no UI for changing a user's role or a separate admin-profile screen.

## 10. Database migrations, seeders, and data lifecycle

Important migrations include:

- `2026_05_18_044256_create_departments_table.php` and `2026_05_18_044256_create_workload_activities_table.php` for WISN data.
- `2026_05_18_add_awt_breakdown_to_departments.php` for the six AWT inputs; the earlier stored AWT column is replaced by the model accessor.
- `2026_10_05_*` migrations for operational rounds, snapshots, and recommendations.
- `2026_10_06_000001_add_inpatient_shift_census_and_staffing_standards.php` for admin role, operational classification, standards, and extra snapshot/round fields. Existing-unit classifications use name/type heuristics and should be reviewed; newly created classifications are chosen through the department form.

Seeder behavior:

- `AdminUserSeeder` creates/updates the demo administrator.
- `DatabaseSeeder` calls `AdminUserSeeder`, then deletes workload activities and departments and creates the sample WISN departments/activities. It does **not** seed operational staffing standards.
- `NepalDataSeeder` is also a demo-data seeder and should not be run on a database containing data to preserve.

Foreign keys preserve operational audit history by restricting deletion of a department referenced by a snapshot or recommendation. Activity rows cascade when a department without protected operational history is deleted. User references for approver/reviewer/entry are nullable on user deletion.

## 11. Views and frontend technology

- Blade templates are under `resources/views/`; the authenticated layout/navigation is under `resources/views/layouts/`.
- Tailwind CSS and Vite build the frontend assets; the pages are server-rendered rather than an API-driven single-page app.
- The department form's live AWT preview is JavaScript convenience only; PHP validation and the model accessor remain authoritative.
- `dashboard.blade.php` uses Chart.js for annual WISN visualizations.
- `reports/wisn-summary.blade.php` is the DomPDF template and uses print-friendly inline styling.

## 12. Tests and known boundaries

Relevant tests are `tests/Feature/OperationalStaffingTest.php` and `tests/Unit/WisnCalculatorServiceTest.php`. The feature tests cover configured standards, handover inputs, census-only midnight, outpatient exclusion, recommendation review/freshness/superseding, and protecting departments with history. The unit regression test exercises annual WISN calculations. Use a disposable test database; do not use a real operational database with `migrate:fresh` or the destructive demo seeder.

Current system boundaries:

- No EHR, patient movement feed, staffing roster, or named nurse assignment.
- No target ratio is selected on behalf of a facility; local clinical governance must approve and enter it.
- Operations data is manually entered and must not contain patient-identifying information.
- Annual WISN headcount and point-in-time on-duty counts are distinct measures and must not be substituted for one another.
- The code is a prototype and its staffing suggestions are not a clinical staffing order.
