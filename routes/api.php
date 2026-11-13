<?php

use App\Http\Controllers\Api\ApiActivityLogController;
use App\Http\Controllers\Api\ApiAttendanceController;
use App\Http\Controllers\Api\ApiAuthController;
use App\Http\Controllers\Api\ApiDashboardController;
use App\Http\Controllers\Api\ApiReportController;
use App\Http\Controllers\Api\ApiSalaryController;
use App\Http\Controllers\Api\ApiScheduleController;
use App\Http\Controllers\Api\ApiUserController;
use App\Models\Dojang;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Public Endpoints
    Route::post('/login', [ApiAuthController::class, 'login']);
    Route::post('/login-pin', [ApiAuthController::class, 'loginPin']);
    Route::get('/pin-users', [ApiAuthController::class, 'pinUsers']);

    // Authenticated Endpoints (Sanctum)
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [ApiAuthController::class, 'logout']);
        Route::get('/me', [ApiAuthController::class, 'me']);

        // Dashboard
        Route::get('/dashboard', [ApiDashboardController::class, 'index']);

        // Attendance (Mandiri, direct save, auto timestamp, photo upload, no GPS)
        Route::get('/attendances', [ApiAttendanceController::class, 'index']);
        Route::post('/attendances/check-in', [ApiAttendanceController::class, 'checkIn']);
        Route::get('/attendances/{attendance}', [ApiAttendanceController::class, 'show']);
        Route::put('/attendances/{attendance}', [ApiAttendanceController::class, 'update']);

        // Training Schedules & Duty Schedules / Assignments
        Route::get('/schedules', [ApiScheduleController::class, 'index']);
        Route::post('/schedules', [ApiScheduleController::class, 'store']);
        Route::get('/schedules/{schedule}', [ApiScheduleController::class, 'show']);
        Route::put('/schedules/{schedule}', [ApiScheduleController::class, 'update']);
        Route::delete('/schedules/{schedule}', [ApiScheduleController::class, 'destroy']);
        Route::get('/my-duty-assignments', [ApiScheduleController::class, 'myDutyAssignments']);

        // Reports & Export
        Route::get('/reports', [ApiReportController::class, 'index']);
        Route::get('/reports/export/excel', [ApiReportController::class, 'exportExcel']);
        Route::get('/reports/export/pdf', [ApiReportController::class, 'exportPdf']);

        // Salary / Honor Recaps (Perhitungan Gaji)
        Route::get('/salary/summary', [ApiSalaryController::class, 'summary']);
        Route::get('/admin/payrolls', [ApiSalaryController::class, 'adminPayrolls']);

        // Users & Roles Management (Superadmin & Admin)
        Route::get('/users', [ApiUserController::class, 'index']);
        Route::post('/users', [ApiUserController::class, 'store']);
        Route::put('/users/{user}', [ApiUserController::class, 'update']);
        Route::delete('/users/{user}', [ApiUserController::class, 'destroy']);
        Route::get('/roles', [ApiUserController::class, 'roles']);

        // Activity Logs
        Route::get('/activity-logs', [ApiActivityLogController::class, 'index']);

        // Master Locations / Dojang
        Route::get('/dojangs', function () {
            return response()->json([
                'success' => true,
                'data' => Dojang::where('is_active', true)->get(),
            ]);
        });
    });
});
