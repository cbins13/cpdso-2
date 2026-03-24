<?php

use App\Http\Controllers\BoxController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\Auth\EmailOtpController;
use App\Http\Controllers\Auth\SocialAuthController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');

    Route::get('/boxes', [BoxController::class, 'index'])->name('boxes.index');
    Route::post('/boxes', [BoxController::class, 'store'])->name('boxes.store');
    Route::get('/boxes/{boxId}', [BoxController::class, 'show'])->name('boxes.show');
    Route::patch('/boxes/{boxId}', [BoxController::class, 'update'])->name('boxes.update');
    Route::delete('/boxes/{boxId}', [BoxController::class, 'destroy'])->name('boxes.destroy');
    Route::patch('/boxes/{boxId}/restore', [BoxController::class, 'restore'])->name('boxes.restore');

    Route::post('/boxes/{boxId}/items', [BoxController::class, 'storeItem'])->name('boxes.items.store');
    Route::patch('/items/{itemId}', [BoxController::class, 'updateItem'])->name('items.update');
    Route::delete('/items/{itemId}', [BoxController::class, 'destroyItem'])->name('items.destroy');
    Route::patch('/items/{itemId}/restore', [BoxController::class, 'restoreItem'])->name('items.restore');

    Route::get('/reports/items-by-box', [ReportController::class, 'itemsByBoxAndOwner'])->name('reports.items-by-box');
    Route::get('/reports/most-common-items', [ReportController::class, 'mostCommonItems'])->name('reports.most-common-items');
});

Route::get('/auth/google', [SocialAuthController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('/auth/google/callback', [SocialAuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');

Route::middleware('guest')->group(function () {
    Route::get('/auth/otp-challenge', [EmailOtpController::class, 'challenge'])->name('auth.otp.challenge');
    Route::post('/auth/otp-challenge', [EmailOtpController::class, 'verifyPending'])
        ->middleware('throttle:otp-verify')
        ->name('auth.otp.verify');
    Route::post('/auth/otp-resend', [EmailOtpController::class, 'resendPending'])
        ->middleware('throttle:otp-resend')
        ->name('auth.otp.resend');
});

Route::middleware('auth')->group(function () {
    Route::post('/email/verify-otp', [EmailOtpController::class, 'verifyEmailOtp'])
        ->middleware('throttle:otp-verify')
        ->name('verification.otp.verify');
    Route::post('/email/verify-otp/resend', [EmailOtpController::class, 'resendVerifyEmailOtp'])
        ->middleware('throttle:otp-resend')
        ->name('verification.otp.resend');
});

require __DIR__.'/settings.php';
