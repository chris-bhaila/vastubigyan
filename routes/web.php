<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\Admin\AnalysisController as AdminAnalysisController;
use App\Http\Controllers\Admin\PackageController as AdminPackageController;
use App\Http\Controllers\Admin\PurchaseController as AdminPurchaseController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\AnalysisController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CreditController;
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

    Route::get('/purchases/{purchase}', [PurchaseController::class, 'show'])
        ->can('view', 'purchase')
        ->name('purchases.show');

    Route::get('/purchases/{purchase}/invoice', [PurchaseController::class, 'invoice'])
        ->can('view', 'purchase')
        ->name('purchases.invoice');

    // Buying, balances and starting analyses are a plain-user concern; admins
    // and super_admins manage the system from /admin instead and never have
    // their own credits to spend.
    Route::middleware('role:user')->group(function () {
        Route::post('/checkout/{package}', [CheckoutController::class, 'store'])
            ->middleware('throttle:10,1')
            ->name('checkout.store');

        Route::get('/purchases', [PurchaseController::class, 'index'])->name('purchases.index');

        Route::get('/credits', [CreditController::class, 'index'])->name('credits.index');

        Route::get('/analyses', [AnalysisController::class, 'index'])->name('analyses.index');

        // These static /analyses/create* paths must stay registered before
        // the /analyses/{analysis} wildcard below, or the wildcard route
        // would shadow them (e.g. "create" would be matched as an id).
        Route::middleware('credits.required')->group(function () {
            Route::get('/analyses/create', [AnalysisController::class, 'create'])->name('analyses.create');
            Route::post('/analyses/create', [AnalysisController::class, 'storeStep1'])->name('analyses.step1');
            Route::get('/analyses/create/land', [AnalysisController::class, 'step2'])->name('analyses.step2');
            Route::post('/analyses', [AnalysisController::class, 'store'])->name('analyses.store');
        });
    });

    Route::get('/analyses/{analysis}', [AnalysisController::class, 'show'])
        ->can('view', 'analysis')
        ->name('analyses.show');

    // The authorized file route: owner or admin only, never a public URL.
    Route::get('/analyses/{analysis}/files/{field}', [AnalysisController::class, 'file'])
        ->name('analyses.files');
});

Route::middleware(['auth', 'role:admin,super_admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::get('/users/{user}', [AdminUserController::class, 'show'])->name('users.show');

    Route::post('/users/{user}/credits', [AdminUserController::class, 'storeCredits'])
        ->middleware('can:manage-credits')
        ->name('users.credits.store');

    Route::get('/purchases', [AdminPurchaseController::class, 'index'])->name('purchases.index');

    Route::post('/purchases/{purchase}/refund', [AdminPurchaseController::class, 'refund'])
        ->middleware('can:manage-credits')
        ->name('purchases.refund');

    Route::get('/analyses', [AdminAnalysisController::class, 'index'])->name('analyses.index');

    Route::middleware('can:manage-credits')->group(function () {
        Route::get('/packages', [AdminPackageController::class, 'index'])->name('packages.index');
        Route::get('/packages/{package}/edit', [AdminPackageController::class, 'edit'])->name('packages.edit');
        Route::put('/packages/{package}', [AdminPackageController::class, 'update'])->name('packages.update');
    });
});

Route::get('/payment/esewa/success', [EsewaPaymentController::class, 'success'])->name('payment.esewa.success');
Route::get('/payment/esewa/failure/{purchase}', [EsewaPaymentController::class, 'failure'])->name('payment.esewa.failure');
