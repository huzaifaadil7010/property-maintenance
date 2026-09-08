<?php

use App\Http\Controllers\Technician\DashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'current.organization', 'role:technician'])
    ->prefix('technician')
    ->as('technician.')
    ->group(function (): void {
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    });
