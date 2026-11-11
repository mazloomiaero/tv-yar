<?php

declare(strict_types=1);

use App\Http\Controllers\Technical\DashboardController;
use App\Http\Controllers\Technical\DeviceReceptionController;
use App\Http\Controllers\Technical\ServiceRequestController;
use Illuminate\Support\Facades\Route;

Route::get('/', DashboardController::class)->name('dashboard');

Route::middleware('throttle:technical-requests')->group(function (): void {
    Route::get('/service-requests', [ServiceRequestController::class, 'index'])
        ->middleware('permission:requests.view')
        ->name('service-requests.index');
    Route::get('/service-requests/{serviceRequest}', [ServiceRequestController::class, 'show'])
        ->middleware('permission:requests.view')
        ->name('service-requests.show');
    Route::post('/service-requests/{serviceRequest}/assignments', [ServiceRequestController::class, 'assign'])
        ->middleware('permission:requests.assign')
        ->name('service-requests.assignments.store');
    Route::post('/service-requests/{serviceRequest}/receptions', [DeviceReceptionController::class, 'store'])
        ->middleware('permission:requests.change_status')
        ->name('service-requests.receptions.store');
    Route::post('/service-requests/{serviceRequest}/receptions/delivery', [DeviceReceptionController::class, 'deliver'])
        ->middleware('permission:requests.change_status')
        ->name('service-requests.receptions.deliver');
    Route::get('/service-requests/{serviceRequest}/receptions/pdf', [DeviceReceptionController::class, 'pdf'])
        ->middleware('permission:requests.view')
        ->name('service-requests.receptions.pdf');
    Route::get('/calendar', [ServiceRequestController::class, 'calendar'])
        ->middleware('permission:requests.view')
        ->name('calendar');
});
