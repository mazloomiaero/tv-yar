<?php

declare(strict_types=1);

use App\Http\Controllers\Operator\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', DashboardController::class)->name('dashboard');

require __DIR__.'/operator-operations.php';
