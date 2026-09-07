<?php

use App\Http\Controllers\Resident\ConfirmMaintenanceRequestResolutionController;
use App\Http\Controllers\Resident\DashboardController;
use App\Http\Controllers\Resident\MaintenanceRequestsController;
use App\Http\Controllers\Resident\ReopenMaintenanceRequestController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'current.organization', 'role:resident'])
    ->prefix('resident')
    ->as('resident.')
    ->group(function (): void {
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('maintenance-requests', [MaintenanceRequestsController::class, 'index'])->name('maintenance-requests');
        Route::post('maintenance-requests', [MaintenanceRequestsController::class, 'store'])->name('maintenance-requests.store');
        Route::get('maintenance-requests/{maintenanceRequest}', [MaintenanceRequestsController::class, 'show'])->name('maintenance-requests.show');
        Route::patch('maintenance-requests/{maintenanceRequest}/confirm-resolution', ConfirmMaintenanceRequestResolutionController::class)->name('maintenance-requests.confirm-resolution');
        Route::patch('maintenance-requests/{maintenanceRequest}/reopen', ReopenMaintenanceRequestController::class)->name('maintenance-requests.reopen');
    });
