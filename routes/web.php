<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EsewaPaymentController;
use App\Http\Controllers\PricingController;
use App\Http\Controllers\PurchaseController;
use Illuminate\Support\Facades\Route;

Route::get('/pricing', [PricingController::class, 'index'])->name('pricing');

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route(auth()->user()->dashboardRouteName());
    }

    return app(AuthController::class)->showLoginForm();
})->name('login');

Route::middleware('guest')->group(function () {
    Route::post('/', [AuthController::class, 'login'])->name('login.attempt');

    Route::get('/auth/google/redirect', [AuthController::class, 'redirectToGoogle'])->name('auth.google.redirect');
    Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/activity-log', [ActivityLogController::class, 'index'])
        ->can('viewLogViewer')
        ->name('activity-log.index');

    Route::post('/checkout/{package}', [CheckoutController::class, 'store'])
        ->middleware('throttle:10,1')
        ->name('checkout.store');

    Route::get('/purchases', [PurchaseController::class, 'index'])->name('purchases.index');

    Route::get('/purchases/{purchase}', [PurchaseController::class, 'show'])
        ->can('view', 'purchase')
        ->name('purchases.show');

    Route::get('/purchases/{purchase}/invoice', [PurchaseController::class, 'invoice'])
        ->can('view', 'purchase')
        ->name('purchases.invoice');
});

Route::get('/payment/esewa/success', [EsewaPaymentController::class, 'success'])->name('payment.esewa.success');
Route::get('/payment/esewa/failure/{purchase}', [EsewaPaymentController::class, 'failure'])->name('payment.esewa.failure');
