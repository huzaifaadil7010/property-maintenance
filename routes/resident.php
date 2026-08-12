<?php

use App\Http\Controllers\Resident\DashboardController;
use App\Http\Controllers\Resident\MaintenanceRequestsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'current.organization', 'role:resident'])
    ->prefix('resident')
    ->as('resident.')
    ->group(function (): void {
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('maintenance-requests', [MaintenanceRequestsController::class, 'index'])->name('maintenance-requests');
        Route::post('maintenance-requests', [MaintenanceRequestsController::class, 'store'])->name('maintenance-requests.store');
    });
