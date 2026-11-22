<?php

declare(strict_types=1);

use App\Http\Controllers\Intake\WebsiteIntakeController;
use Illuminate\Support\Facades\Route;

Route::post('/website-request', [WebsiteIntakeController::class, 'store'])
    ->middleware(['throttle:website-intake', 'intake.key'])
    ->name('website-request');
