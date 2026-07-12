<?php

use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified', 'current.organization'])->group(function () {
    Route::get('dashboard', function () {
        return redirect()->route('organization.dashboard');
    })->name('dashboard');
});

require __DIR__.'/settings.php';
