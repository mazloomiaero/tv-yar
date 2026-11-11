<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\BranchController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\CustomFieldController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DeviceTypeController;
use App\Http\Controllers\Admin\PartController;
use App\Http\Controllers\Admin\ServiceStatusController;
use App\Http\Controllers\Admin\ServiceTypeController;
use App\Http\Controllers\Admin\SkillController;
use App\Http\Controllers\Admin\StaffUserController;
use App\Http\Controllers\Admin\TechnicianController;
use Illuminate\Support\Facades\Route;

Route::get('/', DashboardController::class)->name('dashboard');

Route::middleware('throttle:admin-catalog')->group(function (): void {
    Route::get('/staff', [StaffUserController::class, 'index'])->name('staff.index');
    Route::get('/staff/create', [StaffUserController::class, 'create'])->name('staff.create');
    Route::post('/staff', [StaffUserController::class, 'store'])->name('staff.store');
    Route::get('/staff/{staff}/edit', [StaffUserController::class, 'edit'])->name('staff.edit');
    Route::put('/staff/{staff}', [StaffUserController::class, 'update'])->name('staff.update');
    Route::delete('/staff/{staff}', [StaffUserController::class, 'destroy'])->name('staff.destroy');

    Route::get('/device-types', [DeviceTypeController::class, 'index'])->name('device-types.index');
    Route::get('/device-types/create', [DeviceTypeController::class, 'create'])->name('device-types.create');
    Route::post('/device-types', [DeviceTypeController::class, 'store'])->name('device-types.store');
    Route::get('/device-types/{device_type}/edit', [DeviceTypeController::class, 'edit'])->name('device-types.edit');
    Route::put('/device-types/{device_type}', [DeviceTypeController::class, 'update'])->name('device-types.update');
    Route::delete('/device-types/{device_type}', [DeviceTypeController::class, 'destroy'])->name('device-types.destroy');

    Route::get('/brands', [BrandController::class, 'index'])->name('brands.index');
    Route::get('/brands/create', [BrandController::class, 'create'])->name('brands.create');
    Route::post('/brands', [BrandController::class, 'store'])->name('brands.store');
    Route::get('/brands/{brand}/edit', [BrandController::class, 'edit'])->name('brands.edit');
    Route::put('/brands/{brand}', [BrandController::class, 'update'])->name('brands.update');
    Route::delete('/brands/{brand}', [BrandController::class, 'destroy'])->name('brands.destroy');

    Route::get('/service-types', [ServiceTypeController::class, 'index'])->name('service-types.index');
    Route::get('/service-types/create', [ServiceTypeController::class, 'create'])->name('service-types.create');
    Route::post('/service-types', [ServiceTypeController::class, 'store'])->name('service-types.store');
    Route::get('/service-types/{service_type}/edit', [ServiceTypeController::class, 'edit'])->name('service-types.edit');
    Route::put('/service-types/{service_type}', [ServiceTypeController::class, 'update'])->name('service-types.update');
    Route::delete('/service-types/{service_type}', [ServiceTypeController::class, 'destroy'])->name('service-types.destroy');

    Route::get('/service-statuses', [ServiceStatusController::class, 'index'])->name('service-statuses.index');
    Route::get('/service-statuses/create', [ServiceStatusController::class, 'create'])->name('service-statuses.create');
    Route::post('/service-statuses', [ServiceStatusController::class, 'store'])->name('service-statuses.store');
    Route::get('/service-statuses/{service_status}/edit', [ServiceStatusController::class, 'edit'])->name('service-statuses.edit');
    Route::put('/service-statuses/{service_status}', [ServiceStatusController::class, 'update'])->name('service-statuses.update');
    Route::delete('/service-statuses/{service_status}', [ServiceStatusController::class, 'destroy'])->name('service-statuses.destroy');

    Route::get('/custom-fields', [CustomFieldController::class, 'index'])->name('custom-fields.index');
    Route::get('/custom-fields/create', [CustomFieldController::class, 'create'])->name('custom-fields.create');
    Route::post('/custom-fields', [CustomFieldController::class, 'store'])->name('custom-fields.store');
    Route::get('/custom-fields/{custom_field}/edit', [CustomFieldController::class, 'edit'])->name('custom-fields.edit');
    Route::put('/custom-fields/{custom_field}', [CustomFieldController::class, 'update'])->name('custom-fields.update');
    Route::delete('/custom-fields/{custom_field}', [CustomFieldController::class, 'destroy'])->name('custom-fields.destroy');

    Route::get('/skills', [SkillController::class, 'index'])->name('skills.index');
    Route::get('/skills/create', [SkillController::class, 'create'])->name('skills.create');
    Route::post('/skills', [SkillController::class, 'store'])->name('skills.store');
    Route::get('/skills/{skill}/edit', [SkillController::class, 'edit'])->name('skills.edit');
    Route::put('/skills/{skill}', [SkillController::class, 'update'])->name('skills.update');
    Route::delete('/skills/{skill}', [SkillController::class, 'destroy'])->name('skills.destroy');

    Route::get('/branches', [BranchController::class, 'index'])->name('branches.index');
    Route::get('/branches/create', [BranchController::class, 'create'])->name('branches.create');
    Route::post('/branches', [BranchController::class, 'store'])->name('branches.store');
    Route::get('/branches/{branch}/edit', [BranchController::class, 'edit'])->name('branches.edit');
    Route::put('/branches/{branch}', [BranchController::class, 'update'])->name('branches.update');
    Route::delete('/branches/{branch}', [BranchController::class, 'destroy'])->name('branches.destroy');

    Route::get('/technicians', [TechnicianController::class, 'index'])->name('technicians.index');
    Route::get('/technicians/create', [TechnicianController::class, 'create'])->name('technicians.create');
    Route::post('/technicians', [TechnicianController::class, 'store'])->name('technicians.store');
    Route::get('/technicians/{technician}/edit', [TechnicianController::class, 'edit'])->name('technicians.edit');
    Route::put('/technicians/{technician}', [TechnicianController::class, 'update'])->name('technicians.update');
    Route::delete('/technicians/{technician}', [TechnicianController::class, 'destroy'])->name('technicians.destroy');

    Route::get('/parts', [PartController::class, 'index'])->name('parts.index');
    Route::get('/parts/create', [PartController::class, 'create'])->name('parts.create');
    Route::post('/parts', [PartController::class, 'store'])->name('parts.store');
    Route::get('/parts/{part}/edit', [PartController::class, 'edit'])->name('parts.edit');
    Route::put('/parts/{part}', [PartController::class, 'update'])->name('parts.update');
    Route::delete('/parts/{part}', [PartController::class, 'destroy'])->name('parts.destroy');
});

require __DIR__.'/operator-operations.php';
