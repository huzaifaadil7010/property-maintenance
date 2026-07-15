<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PropertiesController;
use App\Http\Controllers\UnitsController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified', 'current.organization', 'role:owner'])
    ->prefix('organization/{organization}')
    ->as('organization.')
    ->group(function () {
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('properties', [PropertiesController::class, 'index'])->name('properties');

        Route::get('units', [UnitsController::class, 'index'])->name('units');
    });
