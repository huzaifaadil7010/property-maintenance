<?php

use App\Http\Controllers\DeleteTempFileController;
use App\Http\Controllers\StoreTempFileController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified', 'current.organization'])->group(function () {
    Route::get('dashboard', function () {
        return redirect()->to(Auth::user()?->getDashboardUrl() ?? route('home'));
    })->name('dashboard');

    require __DIR__.'/settings.php';
});

Route::middleware(['auth'])->group(function () {
    Route::post('store-temp-file', StoreTempFileController::class)->name('temp-files.store');
    Route::delete('delete-temp-file', DeleteTempFileController::class)->name('temp-files.delete');
});
