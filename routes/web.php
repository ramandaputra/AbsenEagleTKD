<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DojangController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SalaryController;
use App\Http\Controllers\TrainingScheduleController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Guest / Splash Routes
Route::get('/', [AuthController::class, 'splash'])->name('splash');
Route::get('/splash', [AuthController::class, 'splash'])->name('web.splash');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::post('/login-pin', [AuthController::class, 'loginPin'])->name('login.pin');
    Route::post('/quick-login', [AuthController::class, 'quickLogin'])->name('quick-login');
});

// Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile & Rekening
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::post('/profile/pin', [ProfileController::class, 'updatePin'])->name('profile.pin');

    // Attendance (Mandiri, direct save, no GPS)
    Route::get('/attendances', [AttendanceController::class, 'index'])->name('attendances.index');
    Route::get('/attendances/check-in', [AttendanceController::class, 'create'])->name('attendances.create');
    Route::post('/attendances/check-in', [AttendanceController::class, 'store'])->name('attendances.store');
    Route::put('/attendances/{attendance}', [AttendanceController::class, 'update'])->name('attendances.update');

    // Schedules & Duty Piket Mengajar
    Route::get('/schedules', [TrainingScheduleController::class, 'index'])->name('schedules.index');
    Route::post('/schedules', [TrainingScheduleController::class, 'store'])->name('schedules.store');
    Route::delete('/schedules/{schedule}', [TrainingScheduleController::class, 'destroy'])->name('schedules.destroy');

    // Personal Salary / Honor
    Route::get('/salary', [SalaryController::class, 'index'])->name('salary.index');
    Route::get('/salary/slip/{month}/{year}', [SalaryController::class, 'slip'])->name('salary.slip');

    // Admin & Superadmin Group
    Route::middleware('role:admin,superadmin')->group(function () {
        // Reports
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/export/excel', [ReportController::class, 'exportExcel'])->name('reports.export.excel');
        Route::get('/reports/export/pdf', [ReportController::class, 'exportPdf'])->name('reports.export.pdf');

        // Activity Logs
        Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');

        // Admin Payroll Management
        Route::get('/admin/salary', [SalaryController::class, 'adminIndex'])->name('salary.admin.index');
        Route::get('/admin/salary/slip/{coach}/{month}/{year}', [SalaryController::class, 'adminSlip'])->name('salary.admin.slip');
        Route::post('/admin/salary/generate', [SalaryController::class, 'adminGenerateAll'])->name('salary.admin.generate');
        Route::post('/admin/salary/{coach}/adjustment', [SalaryController::class, 'updateAdjustment'])->name('salary.admin.adjustment');
        Route::post('/admin/salary/{payroll}/status', [SalaryController::class, 'updateStatus'])->name('salary.admin.status');
    });

    // Superadmin Only Group
    Route::middleware('role:superadmin')->group(function () {
        // Dojangs
        Route::get('/dojangs', [DojangController::class, 'index'])->name('dojangs.index');
        Route::post('/dojangs', [DojangController::class, 'store'])->name('dojangs.store');
        Route::put('/dojangs/{dojang}', [DojangController::class, 'update'])->name('dojangs.update');
        Route::delete('/dojangs/{dojang}', [DojangController::class, 'destroy'])->name('dojangs.destroy');

        // Coaches & Users
        Route::get('/coaches', [UserController::class, 'index'])->name('users.index');
        Route::post('/coaches', [UserController::class, 'store'])->name('users.store');
        Route::put('/coaches/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/coaches/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });
});
