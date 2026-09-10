<?php

use App\Http\Controllers\Technician\DashboardController;
use App\Http\Controllers\Technician\MaintenanceRequestsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'current.organization', 'role:technician'])
    ->prefix('technician')
    ->as('technician.')
    ->group(function (): void {
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('maintenance-requests', [MaintenanceRequestsController::class, 'index'])->name('maintenance-requests');
        Route::get('maintenance-requests/{maintenanceRequest}', [MaintenanceRequestsController::class, 'show'])->name('maintenance-requests.show');
    });
