<?php

use App\Http\Controllers\Organization\DashboardController;
use App\Http\Controllers\Organization\PropertiesController;
use App\Http\Controllers\Organization\ResidentsController;
use App\Http\Controllers\Organization\UnitsController;
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

        Route::get('residents', [ResidentsController::class, 'index'])->name('residents');
    });
