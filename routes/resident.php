<?php

use App\Http\Controllers\Resident\DashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'current.organization', 'role:resident'])
    ->prefix('resident')
    ->as('resident.')
    ->group(function (): void {
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    });
