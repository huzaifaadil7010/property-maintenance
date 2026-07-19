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

        Route::get('units', [UnitsController::class, 'index'])->name('units');

        Route::get('residents', [ResidentsController::class, 'index'])->name('residents');
    });
