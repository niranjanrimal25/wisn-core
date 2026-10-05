# Design: WISN Results Panel on Activities Page

**Date:** 2026-07-12  
**Scope:** Add a live WISN calculation results panel to `departments/{id}/activities`

---

## Problem

The activities page is where users enter workload data, but WISN results (Required FTE, Ratio, Status) only appear on the dashboard. Users must navigate away to see the impact of the data they just entered.

---

## Solution

Add a full-width WISN Calculation Result panel at the bottom of the activities page, below the existing 2-column grid (Add form + Workload table). The panel only renders when the department has at least one activity.

---

## Changes

### 1. `WorkloadActivityController::index()`

Inject `WisnCalculatorService` and compute the result for the department. Pass `$wisnResult` to the view.

```php
public function index(Department $department)
{
    $activities  = $department->activities;
    $wisnResult  = app(WisnCalculatorService::class)
                       ->calculateDepartmentStaffing($department);
    return view('activities.index', compact('department', 'activities', 'wisnResult'));
}
```

### 2. `resources/views/activities/index.blade.php`

Below the closing `</div>` of the 2-column grid, add a conditional full-width panel:

**Condition:** `@if($activities->count() > 0)`

**Panel layout:**

```
┌─────────────────────────────────────────────────────────────────┐
│  WISN Calculation Result                                        │
├──────────┬──────────────────┬───────┬──────────┬───────────────┤
│ AWT(hrs) │ Health Svc FTE   │  CAF  │ AAF FTE  │ Total Req.    │
│  1,696   │     10.61        │ 1.000 │   0.00   │    10.61      │
├──────────┴──────────────────┴───────┴──────────┴───────────────┤
│  WISN Ratio: 0.60   [● Critical]   Gap: −0.61 nurses           │
└─────────────────────────────────────────────────────────────────┘
```

- Row 1: 5 metric tiles (AWT, Health FTE, CAF, AAF FTE, Total Required)
- Row 2: Large WISN ratio number + coloured status badge + gap figure
- Colour coding matches dashboard: red=critical, amber=borderline, green=adequate, blue=surplus
- Gap = `current_staff − total_required_staff` (negative means shortage)

---

## What does NOT change

- The Add Activity form
- The Current Workload Profile table
- The WISN stepper
- Dashboard, DepartmentController, WisnCalculatorService — untouched
