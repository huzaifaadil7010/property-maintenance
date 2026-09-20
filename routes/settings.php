<?php

use App\Http\Controllers\Settings\BillingController;
use App\Http\Controllers\Settings\CancelSubscriptionController;
use App\Http\Controllers\Settings\CreateSubscriptionIntentController;
use App\Http\Controllers\Settings\DeletePaymentMethodController;
use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\Settings\ResumeSubscriptionController;
use App\Http\Controllers\Settings\SecurityController;
use App\Http\Controllers\Settings\SetDefaultPaymentMethodController;
use App\Http\Controllers\Settings\StorePaymentMethodController;
use App\Http\Controllers\Settings\SubscriptionController;
use App\Http\Controllers\Settings\SwitchSubscriptionPlanController;
use Illuminate\Auth\Middleware\RequirePassword;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', '/settings/profile');

    Route::get('settings/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('settings/profile', [ProfileController::class, 'update'])->name('profile.update');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::delete('settings/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('settings/security', [SecurityController::class, 'edit'])
        ->middleware(RequirePassword::class)
        ->name('security.edit');

    Route::put('settings/password', [SecurityController::class, 'update'])
        ->middleware('throttle:6,1')
        ->name('user-password.update');

    Route::inertia('settings/appearance', 'settings/appearance')->name('appearance.edit');

    Route::middleware('role:owner')->prefix('settings/billing')->as('billing.')->group(function (): void {
        Route::get('/', [BillingController::class, 'index'])->name('index');
        Route::post('subscription-intent', CreateSubscriptionIntentController::class)->name('subscription-intent');
        Route::post('subscriptions', [SubscriptionController::class, 'store'])->name('subscriptions.store');
        Route::patch('subscriptions/plan', SwitchSubscriptionPlanController::class)->name('subscriptions.switch-plan');
        Route::patch('subscriptions/cancel', CancelSubscriptionController::class)->name('subscriptions.cancel');
        Route::patch('subscriptions/resume', ResumeSubscriptionController::class)->name('subscriptions.resume');
        Route::post('payment-methods', StorePaymentMethodController::class)->name('payment-methods.store');
        Route::patch('payment-methods/{paymentMethod}/default', SetDefaultPaymentMethodController::class)->name('payment-methods.default');
        Route::delete('payment-methods/{paymentMethod}', DeletePaymentMethodController::class)->name('payment-methods.destroy');
    });
});

Route::get('.well-known/passkey-endpoints', function () {
    return response()->json([
        'enroll' => route('security.edit'),
        'manage' => route('security.edit'),
    ]);
})->name('well-known.passkeys');
