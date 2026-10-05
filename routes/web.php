<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\WorkloadActivityController;
use App\Http\Controllers\HelpController;
use App\Http\Controllers\MobilizationRecommendationController;
use App\Http\Controllers\OperationalDashboardController;
use App\Http\Controllers\OperationalSnapshotController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Protect application workflows behind authentication and email verification.
Route::middleware(['auth', 'verified'])->group(function () {
    
    // The annual WISN planning dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Shift-level operational coverage, separate from annual WISN calculations
    Route::get('/operations', [OperationalDashboardController::class, 'index'])->name('operations.index');
    Route::post('/operations/snapshots', [OperationalSnapshotController::class, 'store'])->name('operations.snapshots.store');
    Route::post('/operations/recommendations/{recommendation}/approve', [MobilizationRecommendationController::class, 'approve'])->name('operations.recommendations.approve');
    Route::post('/operations/recommendations/{recommendation}/decline', [MobilizationRecommendationController::class, 'decline'])->name('operations.recommendations.decline');
    
    // Department Management (The CRUD routes)
    Route::resource('departments', DepartmentController::class);
    // Activity Management (Nested under specific departments)
    Route::get('departments/{department}/activities', [WorkloadActivityController::class, 'index'])->name('activities.index');
    Route::post('departments/{department}/activities', [WorkloadActivityController::class, 'store'])->name('activities.store');
    Route::get('departments/{department}/activities/{activity}/edit', [WorkloadActivityController::class, 'edit'])->name('activities.edit');
    Route::put('departments/{department}/activities/{activity}', [WorkloadActivityController::class, 'update'])->name('activities.update');
    Route::delete('departments/{department}/activities/{activity}', [WorkloadActivityController::class, 'destroy'])->name('activities.destroy');
    
    // PDF Report Generation
    Route::get('/report', [ReportController::class, 'generate'])->name('report.generate');

    // WISN Methodology Help
    Route::get('/help', [HelpController::class, 'index'])->name('help');

    // Default Breeze Profile Routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';