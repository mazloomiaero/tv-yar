<?php

declare(strict_types=1);

use App\Http\Controllers\Customer\PaymentController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/customer/payments/callback', [PaymentController::class, 'callback'])
    ->middleware('throttle:customer-payments')
    ->name('customer.payments.callback');
