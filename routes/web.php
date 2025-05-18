<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\VehicleRequestController;
use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\SystemLogController;
use App\Http\Controllers\VehicleAssignmentController;
use App\Http\Controllers\VehicleUsageLogController;
use App\Http\Middleware\ApproverMiddleware;
use App\Http\Middleware\AdminMiddleware;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Authentication Routes
Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [LoginController::class, 'login']);
Route::post('logout', [LoginController::class, 'logout'])->name('logout');

// Guest Routes
Route::get('/', function () {
    return redirect()->route('login');
});

// Authenticated Routes
Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Vehicle Requests - Tidak bisa diakses oleh approver
    Route::middleware(['can:access-vehicle-requests'])->group(function () {
        Route::resource('vehicle-requests', VehicleRequestController::class);
        Route::get('vehicle-requests-available', [VehicleRequestController::class, 'available'])->name('vehicle-requests.available');
    });
    
    // Approvals - Index dan Show bisa diakses oleh admin dan approver
    Route::get('approvals', [ApprovalController::class, 'index'])->name('approvals.index');
    Route::get('approvals/{approval}', [ApprovalController::class, 'show'])->name('approvals.show');
    
    // Approve dan Reject - validasi approver dilakukan di controller
    Route::post('approvals/{approval}/approve', [ApprovalController::class, 'approve'])->name('approvals.approve');
    Route::post('approvals/{approval}/reject', [ApprovalController::class, 'reject'])->name('approvals.reject');
    
    // Vehicles
    Route::resource('vehicles', VehicleController::class);
    Route::get('vehicles-update-status', [VehicleController::class, 'updateAllStatuses'])->name('vehicles.update-status');
    
    // Maintenance
    Route::resource('maintenance', MaintenanceController::class);
    Route::post('maintenance/{maintenance}/update-status', [MaintenanceController::class, 'updateStatus'])->name('maintenance.update-status');
    
    // Drivers
    Route::resource('drivers', DriverController::class);
    
    // Vehicle Assignments - Hanya bisa diakses oleh admin
    Route::middleware(['can:admin'])->group(function () {
        Route::get('vehicle-usage', [VehicleAssignmentController::class, 'index'])->name('vehicle-usage.index');
        Route::get('vehicle-usage/history', [VehicleAssignmentController::class, 'history'])->name('vehicle-usage.history');
        Route::get('vehicle-usage/all-logs', [VehicleUsageLogController::class, 'index'])->name('vehicle-usage.all-logs')->defaults('assignment', 'all');
        Route::get('vehicle-usage/{assignment}', [VehicleAssignmentController::class, 'show'])->name('vehicle-usage.show');
        Route::post('vehicle-usage/{assignment}/status', [VehicleAssignmentController::class, 'updateStatus'])->name('vehicle-usage.update-status');
        
        // Vehicle Usage Logs
        Route::get('vehicle-usage/{assignment}/logs', [VehicleUsageLogController::class, 'index'])->name('vehicle-usage.logs.index');
        Route::get('vehicle-usage/{assignment}/logs/create', [VehicleUsageLogController::class, 'create'])->name('vehicle-usage.logs.create');
        Route::post('vehicle-usage/{assignment}/logs', [VehicleUsageLogController::class, 'store'])->name('vehicle-usage.logs.store');
        Route::get('vehicle-usage/{assignment}/logs/{log}', [VehicleUsageLogController::class, 'show'])->name('vehicle-usage.logs.show');
        Route::get('vehicle-usage/{assignment}/logs/{log}/edit', [VehicleUsageLogController::class, 'edit'])->name('vehicle-usage.logs.edit');
        Route::put('vehicle-usage/{assignment}/logs/{log}', [VehicleUsageLogController::class, 'update'])->name('vehicle-usage.logs.update');
        Route::delete('vehicle-usage/{assignment}/logs/{log}', [VehicleUsageLogController::class, 'destroy'])->name('vehicle-usage.logs.destroy');
    });
    
    // Profile
    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    
    // System Logs - Validasi admin dilakukan di controller
    Route::get('system-logs', [SystemLogController::class, 'index'])->name('system-logs.index');
    
    // Laporan
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/export', [ReportController::class, 'export'])->name('reports.export');
});
