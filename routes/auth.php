<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\CustomerLogoutController;
use App\Http\Controllers\Auth\CustomerOtpLoginController;
use App\Http\Controllers\Auth\CustomerPasswordLoginController;
use App\Http\Controllers\Auth\InternalLoginController;
use App\Http\Controllers\Auth\InternalLogoutController;
use App\Http\Controllers\Auth\InternalOtpLoginController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest:web')->group(function (): void {
    Route::get('/login', [InternalLoginController::class, 'create'])->name('login');
    Route::post('/login', [InternalLoginController::class, 'store'])
        ->middleware('throttle:internal-login')
        ->name('login.store');
    Route::get('/login/otp', [InternalOtpLoginController::class, 'create'])->name('login.otp.form');
    Route::post('/login/otp', [InternalOtpLoginController::class, 'send'])
        ->middleware('throttle:internal-otp')
        ->name('login.otp');
    Route::get('/login/verify', [InternalOtpLoginController::class, 'showVerify'])->name('login.verify');
    Route::post('/login/verify', [InternalOtpLoginController::class, 'verify'])
        ->middleware('throttle:internal-otp')
        ->name('login.verify.store');
});

Route::post('/logout', InternalLogoutController::class)
    ->middleware('auth:web')
    ->name('logout');

Route::middleware('guest:customer')->group(function (): void {
    Route::get('/customer/login', [CustomerPasswordLoginController::class, 'create'])->name('customer.login');
    Route::post('/customer/login', [CustomerPasswordLoginController::class, 'store'])
        ->middleware('throttle:customer-login')
        ->name('customer.login.store');
    Route::get('/customer/login/otp', [CustomerOtpLoginController::class, 'create'])->name('customer.login.otp.form');
    Route::post('/customer/login/otp', [CustomerOtpLoginController::class, 'send'])
        ->middleware('throttle:customer-otp')
        ->name('customer.login.otp');
    Route::get('/customer/login/verify', [CustomerOtpLoginController::class, 'showVerify'])->name('customer.login.verify');
    Route::post('/customer/login/verify', [CustomerOtpLoginController::class, 'verify'])
        ->middleware('throttle:customer-otp')
        ->name('customer.login.verify.store');
});

Route::post('/customer/logout', CustomerLogoutController::class)
    ->middleware('auth:customer')
    ->name('customer.logout');
