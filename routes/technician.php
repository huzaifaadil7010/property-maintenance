<?php

use App\Http\Controllers\Technician\CompleteMaintenanceRequestWorkController;
use App\Http\Controllers\Technician\DashboardController;
use App\Http\Controllers\Technician\MaintenanceRequestsController;
use App\Http\Controllers\Technician\StartMaintenanceRequestWorkController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'current.organization', 'role:technician'])
    ->prefix('technician')
    ->as('technician.')
    ->group(function (): void {
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('maintenance-requests', [MaintenanceRequestsController::class, 'index'])->name('maintenance-requests');
        Route::get('maintenance-requests/{maintenanceRequest}', [MaintenanceRequestsController::class, 'show'])->name('maintenance-requests.show');
        Route::patch('maintenance-requests/{maintenanceRequest}/start-work', StartMaintenanceRequestWorkController::class)->name('maintenance-requests.start-work');
        Route::patch('maintenance-requests/{maintenanceRequest}/complete-work', CompleteMaintenanceRequestWorkController::class)->name('maintenance-requests.complete-work');
    });
