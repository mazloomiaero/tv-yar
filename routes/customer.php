<?php

declare(strict_types=1);

use App\Http\Controllers\Customer\DashboardController;
use App\Http\Controllers\Customer\FeedbackController;
use App\Http\Controllers\Customer\InvoiceController;
use App\Http\Controllers\Customer\PaymentController;
use App\Http\Controllers\Customer\ProfileController;
use App\Http\Controllers\Customer\ReceptionController;
use Illuminate\Support\Facades\Route;

Route::get('/', DashboardController::class)->name('dashboard');

Route::middleware('throttle:customer-portal')->group(function (): void {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/service-requests/{serviceRequest}/reception', [ReceptionController::class, 'show'])
        ->name('service-requests.reception');
    Route::get('/service-requests/{serviceRequest}/reception/pdf', [ReceptionController::class, 'pdf'])
        ->name('service-requests.reception.pdf');
    Route::get('/service-requests/{serviceRequest}/feedback', [FeedbackController::class, 'create'])
        ->name('service-requests.feedback.create');
    Route::post('/service-requests/{serviceRequest}/feedback', [FeedbackController::class, 'store'])
        ->name('service-requests.feedback.store');
    Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('/invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
});

Route::middleware('throttle:customer-payments')->group(function (): void {
    Route::post('/invoices/{invoice}/payments/gateway', [PaymentController::class, 'store'])
        ->name('invoices.payments.gateway');
});
