<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

$loginPath = trim((string) config('bazar.login_path', 'yasm-gerbang'), '/');

Route::middleware('guest')->group(function () use ($loginPath) {
    Route::get($loginPath, [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post($loginPath, [AuthenticatedSessionController::class, 'store']);

    Route::get($loginPath.'/lupa-sandi', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    Route::post($loginPath.'/lupa-sandi', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');

    Route::get($loginPath.'/sandi-baru/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    Route::post($loginPath.'/sandi-baru', [NewPasswordController::class, 'store'])
        ->name('password.store');
});

Route::middleware('auth')->group(function () {
    Route::get('verify-email', EmailVerificationPromptController::class)
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');

    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    Route::put('password', [PasswordController::class, 'update'])->name('password.update');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});
