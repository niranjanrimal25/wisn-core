# CLAUDE.md — wisn-core

WHO WISN nursing staffing tool for Nepalese hospitals. Laravel 10 web app that automates Workload Indicators of Staffing Need (WISN) calculations and visualises results on a dashboard with Chart.js charts and a downloadable PDF report.

---

## Quick start

```bash
php artisan migrate
php artisan db:seed        # seeds 4 demo departments (ICU, ED, Medical Ward, Surgical)
php artisan serve
```

Open http://127.0.0.1:8000 — register a user, then log in.

---

## Key commands

| Task | Command |
|---|---|
| Run migrations | `php artisan migrate` |
| Re-seed demo data | `php artisan db:seed` (idempotent — wipes and recreates) |
| Start dev server | `php artisan serve` |
| Build front-end assets | `npm run dev` |
| List all routes | `php artisan route:list` |
| Tinker (REPL) | `php artisan tinker` |

---

## Project layout (what matters)

```
app/
  Http/Controllers/
    DashboardController.php       — loads all depts, runs WISN calculations, passes to view
    DepartmentController.php      — CRUD for departments; validates 6 AWT breakdown fields
    WorkloadActivityController.php — CRUD for activities nested under a department
    ReportController.php          — generates downloadable PDF via DomPDF
    HelpController.php            — serves the static /help methodology guide
  Models/
    Department.php                — has computed accessor available_working_time_hours
    WorkloadActivity.php          — belongs to Department
  Services/
    WisnCalculatorService.php     — ALL WISN maths live here; see ARCHITECTURE.md

database/
  migrations/
    2026_05_18_044256_create_departments_table.php
    2026_05_18_044256_create_workload_activities_table.php
    2026_05_18_add_awt_breakdown_to_departments.php   ← adds 6 AWT component columns
  seeders/
    DatabaseSeeder.php            — 4 Nepalese hospital departments with 28 activities

resources/views/
  dashboard.blade.php             — summary cards + Chart.js charts + department table
  departments/
    index.blade.php / create.blade.php / edit.blade.php
  activities/
    index.blade.php               — add form + workload table + dynamic WISN stepper
    edit.blade.php                — edit a single activity
  reports/wisn-summary.blade.php  — DomPDF PDF template (inline CSS only)
  help.blade.php                  — static WISN methodology reference page
  layouts/app.blade.php           — master layout; has @stack('scripts') before </body>

routes/web.php                    — all routes under auth+verified middleware
```

---

## Data model in one sentence

A **Department** has many **WorkloadActivities**. `WisnCalculatorService` reads both to compute staffing requirements. There is no User → Department ownership — all departments are shared across all logged-in users (single-facility prototype).

---

## AWT: the most important concept

Available Working Time is **not stored** as a flat number. It is computed on the fly from 6 columns on the `departments` table:

```
AWT = (working_days_per_year - public_holidays - annual_leave_days
       - sick_leave_days - training_days) × working_hours_per_day
```

The `Department` model exposes this as `$department->available_working_time_hours` via an Eloquent `Attribute::get()` accessor. Nothing else in the codebase needs to change when the breakdown fields are edited.

Nepal-standard defaults: 260 working days, 13 holidays, 18 annual leave, 12 sick leave, 5 training, 8 hrs/day → **AWT = 1,696 hrs/nurse/year**.

---

## Activity types and their calculation roles

| Type | Purpose | Required fields | How used in calculation |
|---|---|---|---|
| `health_service` | Direct patient care | `time_standard_hours`, `annual_volume` | `Required FTE = annual_volume / (AWT / time_standard_hours)` |
| `support` | Recurring shift duties (handover, rounds) | `time_standard_hours` only | `CAF = 1 / (1 − Σ fractions)` inflates health FTE |
| `additional` | Cross-dept duties (teaching) | `time_standard_hours`, `annual_volume` | `AAF FTE = (volume × time) / AWT` added directly to total |

Final: `Required Staff = (Health Service FTE × CAF) + AAF FTE`

---

## WISN ratio status thresholds

| Ratio | Status | Colour |
|---|---|---|
| ≤ 0 | `no_data` | gray |
| < 0.90 | `critical` | red |
| 0.90–0.99 | `borderline` | yellow |
| 1.00 | `adequate` | green |
| > 1.00 | `surplus` | blue |

---

## Gotchas

- **Drop the old AWT column**: The original `available_working_time_hours` integer column was dropped by `2026_05_18_add_awt_breakdown_to_departments`. If you roll back past that migration, the model accessor will break. The accessor name matches the old column name intentionally so all existing service code keeps working.
- **DomPDF requires inline CSS**: The PDF view `reports/wisn-summary.blade.php` must use `<style>` in `<head>`, no external stylesheets.
- **Chart.js via CDN**: Loaded in `dashboard.blade.php` via `@push('scripts')`. The layout must have `@stack('scripts')` before `</body>` — it does.
- **No Hospital model**: This is a single-facility prototype. All departments belong to the same implicit hospital. Multi-facility support would require adding a `hospitals` table and `hospital_id` FK on `departments`.
- **Seeder is destructive**: `php artisan db:seed` deletes all activities then all departments before re-seeding. Do not run in production with real data.
- **Support activity `working_hours_per_day`**: The CAF calculation divides `time_standard_hours` by `$department->working_hours_per_day` (not hardcoded 8). If a department uses a non-8-hour shift, this matters.

---

## Front-end

No npm build step is needed to run the app in development — Vite is used but assets are served hot. Tailwind CSS classes are used throughout. Chart.js 4.4.0 is loaded from CDN only on the dashboard page.

---

## Proposal reference

The academic proposal is at `WISN_Proposal_Final (1).pdf`. `docs/superpowers/plans/2026-05-18-wisn-feature-gaps.md` documents the gap analysis and the 7 implementation tasks that were executed to bring the codebase in line with the proposal.
