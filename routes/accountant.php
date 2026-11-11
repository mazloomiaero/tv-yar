<?php

declare(strict_types=1);

use App\Http\Controllers\Accountant\DashboardController;
use App\Http\Controllers\Accountant\FinancialReportController;
use App\Http\Controllers\Accountant\InvoiceController;
use App\Http\Controllers\Accountant\PaymentController;
use Illuminate\Support\Facades\Route;

Route::get('/', DashboardController::class)->name('dashboard');

Route::middleware('throttle:accountant-invoices')->group(function (): void {
    Route::get('/financial-report', FinancialReportController::class)->name('financial-report');

    Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('/invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
    Route::get('/service-requests/{serviceRequest}/invoices/create', [InvoiceController::class, 'create'])
        ->name('service-requests.invoices.create');
    Route::post('/service-requests/{serviceRequest}/invoices', [InvoiceController::class, 'store'])
        ->name('service-requests.invoices.store');
});

Route::middleware('throttle:accountant-payments')->group(function (): void {
    Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::get('/payments/{payment}', [PaymentController::class, 'show'])->name('payments.show');
    Route::post('/payments/{payment}/confirm', [PaymentController::class, 'confirm'])->name('payments.confirm');
});
