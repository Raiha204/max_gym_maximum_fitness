<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EquipmentController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\MembershipController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\WalkInController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('login'));

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Members & Prepaid Memberships (5-Step Wizard + Digital QR Card + Renewals)
    Route::resource('memberships', MembershipController::class);
    Route::post('/memberships/{membership}/renew', [MembershipController::class, 'renew'])->name('memberships.renew');

    // Redesigned QR Scanner & Manual Search Attendance Module
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::get('/attendance/verify', [AttendanceController::class, 'verify'])->name('attendance.verify');
    Route::post('/attendance', [AttendanceController::class, 'store'])->name('attendance.store');

    // Walk-In Daily Sessions (Anonymous — No Walk-In Name, Cash or GCash Only)
    Route::get('/walk-ins', [WalkInController::class, 'index'])->name('walk-ins.index');
    Route::post('/walk-ins', [WalkInController::class, 'store'])->name('walk-ins.store');

    // Membership Payments Ledger & Revenue Reports
    Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::get('/reports', [PaymentController::class, 'reports'])->name('reports.index');

    // Equipment & Maintenance
    Route::resource('equipment', EquipmentController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::get('/maintenance', [MaintenanceController::class, 'index'])->name('maintenance.index');
    Route::post('/maintenance', [MaintenanceController::class, 'store'])->name('maintenance.store');
    Route::patch('/maintenance/{maintenance}/status', [MaintenanceController::class, 'updateStatus'])->name('maintenance.status');
    Route::patch('/maintenance/{maintenance}/resolve', [MaintenanceController::class, 'resolve'])->name('maintenance.resolve');
    Route::delete('/maintenance/{maintenance}', [MaintenanceController::class, 'destroy'])->name('maintenance.destroy');
});
