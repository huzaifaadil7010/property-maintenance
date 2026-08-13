<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified', 'current.organization'])->group(function () {
    Route::get('dashboard', function () {
        return redirect()->to(Auth::user()?->getDashboardUrl() ?? route('home'));
    })->name('dashboard');

    require __DIR__.'/settings.php';
});

