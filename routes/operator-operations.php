<?php

declare(strict_types=1);

use App\Http\Controllers\Operator\AssignmentController;
use App\Http\Controllers\Operator\CustomerController;
use App\Http\Controllers\Operator\DeviceController;
use App\Http\Controllers\Operator\FeedbackController;
use App\Http\Controllers\Operator\ServiceRequestController;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:operator-customers')->group(function (): void {
    Route::get('/customers', [CustomerController::class, 'search'])
        ->middleware('permission:customers.view')
        ->name('customers.search');
    Route::get('/customers/create', [CustomerController::class, 'create'])
        ->middleware('permission:customers.create')
        ->name('customers.create');
    Route::post('/customers', [CustomerController::class, 'store'])
        ->middleware('permission:customers.create')
        ->name('customers.store');
    Route::get('/customers/{customer}/edit', [CustomerController::class, 'edit'])
        ->middleware('permission:customers.edit')
        ->name('customers.edit');
    Route::put('/customers/{customer}', [CustomerController::class, 'update'])
        ->middleware('permission:customers.edit')
        ->name('customers.update');
});

Route::middleware('throttle:operator-devices')->scopeBindings()->group(function (): void {
    Route::get('/customers/{customer}/devices/create', [DeviceController::class, 'create'])
        ->middleware('permission:devices.create')
        ->name('customers.devices.create');
    Route::post('/customers/{customer}/devices', [DeviceController::class, 'store'])
        ->middleware('permission:devices.create')
        ->name('customers.devices.store');
    Route::get('/customers/{customer}/devices/{device}/edit', [DeviceController::class, 'edit'])
        ->middleware('permission:devices.edit')
        ->name('customers.devices.edit');
    Route::put('/customers/{customer}/devices/{device}', [DeviceController::class, 'update'])
        ->middleware('permission:devices.edit')
        ->name('customers.devices.update');
});

Route::middleware('throttle:operator-requests')->group(function (): void {
    Route::get('/service-requests', [ServiceRequestController::class, 'index'])
        ->middleware('permission:requests.view')
        ->name('service-requests.index');
    Route::get('/service-requests/{serviceRequest}', [ServiceRequestController::class, 'show'])
        ->middleware('permission:requests.view')
        ->name('service-requests.show');
    Route::post('/service-requests/{serviceRequest}/assignments', [AssignmentController::class, 'store'])
        ->middleware('permission:requests.assign')
        ->name('service-requests.assignments.store');
    Route::get('/calendar', [AssignmentController::class, 'calendar'])
        ->middleware('permission:requests.view')
        ->name('calendar');
    Route::post('/service-requests/{serviceRequest}/status', [ServiceRequestController::class, 'changeStatus'])
        ->middleware('permission:requests.change_status')
        ->name('service-requests.status');
    Route::post('/service-requests/{serviceRequest}/close', [ServiceRequestController::class, 'close'])
        ->middleware('permission:requests.close')
        ->name('service-requests.close');
    Route::get('/customers/{customer}/service-requests/create', [ServiceRequestController::class, 'create'])
        ->middleware('permission:requests.create')
        ->name('customers.service-requests.create');
    Route::post('/customers/{customer}/service-requests', [ServiceRequestController::class, 'store'])
        ->middleware('permission:requests.create')
        ->name('customers.service-requests.store');
});

Route::middleware('throttle:operator-feedback')->group(function (): void {
    Route::get('/feedback', [FeedbackController::class, 'index'])
        ->middleware('permission:feedback.view')
        ->name('feedback.index');
    Route::get('/feedback/{feedback}', [FeedbackController::class, 'show'])
        ->middleware('permission:feedback.view')
        ->name('feedback.show');
});
