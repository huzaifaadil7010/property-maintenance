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
    ->group(function () {
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('properties', [PropertiesController::class, 'index'])->name('properties');

        Route::get('units', [UnitsController::class, 'index'])->name('units');

        Route::get('residents', [ResidentsController::class, 'index'])->name('residents');
    });
