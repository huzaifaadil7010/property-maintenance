<?php

use App\Http\Controllers\Organization\AssignMaintenanceRequestTechnicianController;
use App\Http\Controllers\Organization\DashboardController;
use App\Http\Controllers\Organization\MaintenanceRequestsController;
use App\Http\Controllers\Organization\PropertiesController;
use App\Http\Controllers\Organization\ResidentsController;
use App\Http\Controllers\Organization\TechniciansController;
use App\Http\Controllers\Organization\UnitsController;
use App\Http\Controllers\Organization\UpdateMaintenanceRequestStatusController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified', 'current.organization', 'role:owner'])
    ->prefix('organization/{organization}')
    ->as('organization.')
    ->scopeBindings()
    ->group(function () {
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('properties', [PropertiesController::class, 'index'])->name('properties');
        Route::post('properties', [PropertiesController::class, 'store'])->name('properties.store');
        Route::patch('properties/{property}', [PropertiesController::class, 'update'])->name('properties.update');
        Route::delete('properties/{property}', [PropertiesController::class, 'destroy'])->name('properties.destroy');

        Route::get('units', [UnitsController::class, 'index'])->name('units');
        Route::post('units', [UnitsController::class, 'store'])->name('units.store');
        Route::patch('units/{unit}', [UnitsController::class, 'update'])->name('units.update');
        Route::delete('units/{unit}', [UnitsController::class, 'destroy'])->name('units.destroy');

        Route::get('residents', [ResidentsController::class, 'index'])->name('residents');
        Route::get('technicians', [TechniciansController::class, 'index'])->name('technicians');
        Route::get('maintenance-requests', [MaintenanceRequestsController::class, 'index'])->name('maintenance-requests');
        Route::get('maintenance-requests/{maintenanceRequest}', [MaintenanceRequestsController::class, 'show'])->name('maintenance-requests.show');
        Route::patch('maintenance-requests/{maintenanceRequest}/assign-technician', AssignMaintenanceRequestTechnicianController::class)->name('maintenance-requests.assign-technician');
        Route::patch('maintenance-requests/{maintenanceRequest}/status', UpdateMaintenanceRequestStatusController::class)->name('maintenance-requests.update-status');
    });
