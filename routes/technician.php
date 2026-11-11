<?php

declare(strict_types=1);

use App\Http\Controllers\Technician\AssignmentController;
use App\Http\Controllers\Technician\DashboardController;
use App\Http\Controllers\Technician\PaymentController;
use App\Http\Controllers\Technician\ServiceReportController;
use Illuminate\Support\Facades\Route;

Route::get('/', DashboardController::class)->name('dashboard');

Route::middleware('throttle:technician-assignments')->group(function (): void {
    Route::get('/assignments', [AssignmentController::class, 'index'])->name('assignments.index');
    Route::get('/assignments/{assignment}', [AssignmentController::class, 'show'])->name('assignments.show');
});

Route::middleware('throttle:technician-reports')->group(function (): void {
    Route::get('/assignments/{assignment}/reports/create', [ServiceReportController::class, 'create'])->name('reports.create');
    Route::post('/assignments/{assignment}/reports', [ServiceReportController::class, 'store'])->name('reports.store');
    Route::get('/reports/{report}', [ServiceReportController::class, 'show'])->name('reports.show');
});

Route::middleware('throttle:technician-payments')->group(function (): void {
    Route::get('/invoices/{invoice}/payments/create', [PaymentController::class, 'create'])->name('payments.create');
    Route::post('/invoices/{invoice}/payments', [PaymentController::class, 'store'])->name('payments.store');
});
