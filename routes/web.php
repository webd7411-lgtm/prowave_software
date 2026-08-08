<?php

use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Hr\AttendanceController;
use App\Http\Controllers\Hr\DailyTaskController;
use App\Http\Controllers\Hr\DailyUpdateController;
use App\Http\Controllers\Hr\DesignationController;
use App\Http\Controllers\Hr\DocumentController;
use App\Http\Controllers\Hr\HrDashboardController;
use App\Http\Controllers\Hr\LeaveController;
use App\Http\Controllers\Hr\ReportController;
use App\Http\Controllers\Hr\ShiftController;
use App\Http\Controllers\Hr\StaffController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
})->name('home');

Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'show'])->name('show');
        Route::get('/edit', [ProfileController::class, 'edit'])->name('edit');
        Route::patch('/', [ProfileController::class, 'update'])->name('update');
        Route::patch('/password', [ProfileController::class, 'updatePassword'])->name('password');
        Route::post('/avatar', [ProfileController::class, 'updateAvatar'])->name('avatar');
        Route::patch('/preferences', [ProfileController::class, 'updatePreferences'])->name('preferences');
    });

    Route::prefix('settings')->name('settings.')->middleware('permission:settings.view')->group(function () {
        Route::get('/general', [SettingsController::class, 'general'])->name('general');
        Route::post('/general', [SettingsController::class, 'updateGeneral'])->middleware('permission:settings.edit')->name('general.update');
        Route::get('/security', [SettingsController::class, 'security'])->name('security');
        Route::post('/security', [SettingsController::class, 'updateSecurity'])->middleware('permission:settings.edit')->name('security.update');
    });

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/users/import', [UserController::class, 'import'])
            ->middleware('permission:users.import')
            ->name('users.import');
        Route::post('/users/import', [UserController::class, 'importStore'])
            ->middleware('permission:users.import')
            ->name('users.import.store');
        Route::get('/users/export', [UserController::class, 'export'])
            ->middleware('permission:users.export')
            ->name('users.export');
        Route::get('/users/export-pdf', [UserController::class, 'exportPdf'])
            ->middleware('permission:users.export')
            ->name('users.export-pdf');

        Route::middleware('permission:users.view')->group(function () {
            Route::get('/users', [UserController::class, 'index'])->name('users.index');
            Route::get('/users/data', [UserController::class, 'data'])->name('users.data');
            Route::post('/users/bulk', [UserController::class, 'bulkAction'])
                ->middleware('permission:users.delete|users.activate|users.suspend')
                ->name('users.bulk');
            Route::get('/users/create', [UserController::class, 'create'])
                ->middleware('permission:users.create')
                ->name('users.create');
            Route::post('/users', [UserController::class, 'store'])
                ->middleware('permission:users.create')
                ->name('users.store');
            Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
            Route::get('/users/{user}/login-logs', [UserController::class, 'loginLogs'])->name('users.login-logs');
            Route::get('/users/{user}/edit', [UserController::class, 'edit'])
                ->middleware('permission:users.edit')
                ->name('users.edit');
            Route::patch('/users/{user}', [UserController::class, 'update'])
                ->middleware('permission:users.edit')
                ->name('users.update');
            Route::post('/users/{user}/status', [UserController::class, 'changeStatus'])
                ->middleware('permission:users.activate|users.suspend')
                ->name('users.status');
            Route::delete('/users/{user}', [UserController::class, 'destroy'])
                ->middleware('permission:users.delete')
                ->name('users.destroy');
            Route::post('/users/{user}/restore', [UserController::class, 'restore'])
                ->middleware('permission:users.restore')
                ->name('users.restore');
            Route::delete('/users/{user}/force', [UserController::class, 'forceDelete'])
                ->middleware('permission:users.force-delete')
                ->name('users.force-delete');
        });

        Route::middleware('permission:roles.view')->group(function () {
            Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
            Route::get('/roles/create', [RoleController::class, 'create'])
                ->middleware('permission:roles.create')
                ->name('roles.create');
            Route::post('/roles', [RoleController::class, 'store'])
                ->middleware('permission:roles.create')
                ->name('roles.store');
            Route::get('/roles/{role}/edit', [RoleController::class, 'edit'])
                ->middleware('permission:roles.edit')
                ->name('roles.edit');
            Route::patch('/roles/{role}', [RoleController::class, 'update'])
                ->middleware('permission:roles.edit')
                ->name('roles.update');
            Route::delete('/roles/{role}', [RoleController::class, 'destroy'])
                ->middleware('permission:roles.delete')
                ->name('roles.destroy');
            Route::post('/roles/{role}/permissions', [RoleController::class, 'assignPermissions'])
                ->middleware('permission:permissions.assign')
                ->name('roles.permissions');
        });

        Route::middleware('permission:permissions.view')->group(function () {
            Route::get('/permissions', [PermissionController::class, 'index'])->name('permissions.index');
        });

Route::middleware('permission:departments.view')->group(function () {
            Route::get('/departments', [DepartmentController::class, 'index'])->name('departments.index');
            Route::get('/departments/create', [DepartmentController::class, 'create'])
                ->middleware('permission:departments.create')
                ->name('departments.create');
            Route::post('/departments', [DepartmentController::class, 'store'])
                ->middleware('permission:departments.create')
                ->name('departments.store');
            Route::get('/departments/{department}/edit', [DepartmentController::class, 'edit'])
                ->middleware('permission:departments.edit')
                ->name('departments.edit');
            Route::patch('/departments/{department}', [DepartmentController::class, 'update'])
                ->middleware('permission:departments.edit')
                ->name('departments.update');
            Route::delete('/departments/{department}', [DepartmentController::class, 'destroy'])
                ->middleware('permission:departments.delete')
                ->name('departments.destroy');
        });
    });

    Route::prefix('hr')->name('hr.')->group(function () {
        Route::get('/dashboard', [HrDashboardController::class, 'dashboard'])
            ->middleware('permission:hr_dashboard.view')
            ->name('dashboard');

        Route::middleware('permission:staff.view')->group(function () {
            Route::get('/staff', [StaffController::class, 'index'])->name('staff.index');
            Route::get('/staff/export', [StaffController::class, 'export'])
                ->middleware('permission:staff.export')
                ->name('staff.export');
            Route::get('/staff/export-pdf', [StaffController::class, 'exportPdf'])
                ->middleware('permission:staff.export')
                ->name('staff.export-pdf');
            Route::get('/staff/create', [StaffController::class, 'create'])
                ->middleware('permission:staff.create')
                ->name('staff.create');
            Route::post('/staff', [StaffController::class, 'store'])
                ->middleware('permission:staff.create')
                ->name('staff.store');
            Route::get('/staff/{staff}', [StaffController::class, 'show'])->name('staff.show');
            Route::get('/staff/{staff}/edit', [StaffController::class, 'edit'])
                ->middleware('permission:staff.edit')
                ->name('staff.edit');
            Route::patch('/staff/{staff}', [StaffController::class, 'update'])
                ->middleware('permission:staff.edit')
                ->name('staff.update');
            Route::delete('/staff/{staff}', [StaffController::class, 'destroy'])
                ->middleware('permission:staff.delete')
                ->name('staff.destroy');
            Route::post('/staff/{id}/restore', [StaffController::class, 'restore'])
                ->middleware('permission:staff.delete')
                ->name('staff.restore');
        });

        Route::middleware('permission:designations.view')->group(function () {
            Route::get('/designations', [DesignationController::class, 'index'])->name('designations.index');
            Route::get('/designations/create', [DesignationController::class, 'create'])
                ->middleware('permission:designations.create')
                ->name('designations.create');
            Route::post('/designations', [DesignationController::class, 'store'])
                ->middleware('permission:designations.create')
                ->name('designations.store');
            Route::get('/designations/{designation}/edit', [DesignationController::class, 'edit'])
                ->middleware('permission:designations.edit')
                ->name('designations.edit');
            Route::patch('/designations/{designation}', [DesignationController::class, 'update'])
                ->middleware('permission:designations.edit')
                ->name('designations.update');
            Route::delete('/designations/{designation}', [DesignationController::class, 'destroy'])
                ->middleware('permission:designations.delete')
                ->name('designations.destroy');
        });

        Route::middleware('permission:shifts.view')->group(function () {
            Route::get('/shifts', [ShiftController::class, 'index'])->name('shifts.index');
            Route::get('/shifts/create', [ShiftController::class, 'create'])
                ->middleware('permission:shifts.create')
                ->name('shifts.create');
            Route::post('/shifts', [ShiftController::class, 'store'])
                ->middleware('permission:shifts.create')
                ->name('shifts.store');
            Route::get('/shifts/{shift}/edit', [ShiftController::class, 'edit'])
                ->middleware('permission:shifts.edit')
                ->name('shifts.edit');
            Route::patch('/shifts/{shift}', [ShiftController::class, 'update'])
                ->middleware('permission:shifts.edit')
                ->name('shifts.update');
            Route::delete('/shifts/{shift}', [ShiftController::class, 'destroy'])
                ->middleware('permission:shifts.delete')
                ->name('shifts.destroy');
        });

        Route::middleware('permission:attendance.view')->group(function () {
            Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
            Route::get('/attendance/dashboard', [AttendanceController::class, 'dashboard'])->name('attendance.dashboard');
            Route::post('/attendance', [AttendanceController::class, 'store'])
                ->middleware('permission:attendance.manage')
                ->name('attendance.store');
            Route::post('/attendance/check-in', [AttendanceController::class, 'checkIn'])
                ->middleware('permission:attendance.manage|attendance.create')
                ->name('attendance.check-in');
            Route::post('/attendance/check-out', [AttendanceController::class, 'checkOut'])
                ->middleware('permission:attendance.manage|attendance.create')
                ->name('attendance.check-out');
        });

        Route::middleware('permission:leave.view')->group(function () {
            Route::get('/leave', [LeaveController::class, 'index'])->name('leave.index');
            Route::get('/leave/dashboard', [LeaveController::class, 'dashboard'])->name('leave.dashboard');
            Route::get('/leave/create', [LeaveController::class, 'create'])
                ->middleware('permission:leave.create')
                ->name('leave.create');
            Route::post('/leave', [LeaveController::class, 'store'])
                ->middleware('permission:leave.create')
                ->name('leave.store');
            Route::post('/leave/{leave}/review', [LeaveController::class, 'review'])
                ->middleware('permission:leave.approve|leave.reject')
                ->name('leave.review');
        });

        Route::middleware('permission:tasks.view')->group(function () {
            Route::get('/tasks', [DailyTaskController::class, 'index'])->name('tasks.index');
            Route::get('/tasks/board', [DailyTaskController::class, 'board'])->name('tasks.board');
            Route::get('/tasks/create', [DailyTaskController::class, 'create'])
                ->middleware('permission:tasks.create|tasks.assign')
                ->name('tasks.create');
            Route::post('/tasks', [DailyTaskController::class, 'store'])
                ->middleware('permission:tasks.create|tasks.assign')
                ->name('tasks.store');
            Route::get('/tasks/{task}/edit', [DailyTaskController::class, 'edit'])
                ->middleware('permission:tasks.edit')
                ->name('tasks.edit');
            Route::patch('/tasks/{task}', [DailyTaskController::class, 'update'])
                ->middleware('permission:tasks.edit')
                ->name('tasks.update');
            Route::post('/tasks/{task}/status', [DailyTaskController::class, 'changeStatus'])->name('tasks.status');
            Route::delete('/tasks/{task}', [DailyTaskController::class, 'destroy'])
                ->middleware('permission:tasks.delete')
                ->name('tasks.destroy');
        });

        Route::middleware('permission:daily_reports.view')->group(function () {
            Route::get('/updates', [DailyUpdateController::class, 'index'])->name('updates.index');
            Route::get('/updates/create', [DailyUpdateController::class, 'create'])
                ->middleware('permission:daily_reports.create')
                ->name('updates.create');
            Route::post('/updates', [DailyUpdateController::class, 'store'])
                ->middleware('permission:daily_reports.create')
                ->name('updates.store');
            Route::get('/updates/{update}', [DailyUpdateController::class, 'show'])->name('updates.show');
            Route::post('/updates/{update}/review', [DailyUpdateController::class, 'review'])
                ->middleware('permission:daily_reports.review')
                ->name('updates.review');
        });

        Route::middleware('permission:employee_documents.view')->group(function () {
            Route::get('/documents', [DocumentController::class, 'index'])->name('documents.index');
            Route::post('/documents', [DocumentController::class, 'store'])
                ->middleware('permission:employee_documents.upload')
                ->name('documents.store');
            Route::get('/documents/{document}/download', [DocumentController::class, 'download'])->name('documents.download');
            Route::delete('/documents/{document}', [DocumentController::class, 'destroy'])
                ->middleware('permission:employee_documents.delete')
                ->name('documents.destroy');
        });

        Route::middleware('permission:reports.view')->group(function () {
            Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
            Route::get('/reports/employees', [ReportController::class, 'employees'])->name('reports.employees');
            Route::get('/reports/attendance', [ReportController::class, 'attendance'])->name('reports.attendance');
            Route::get('/reports/leaves', [ReportController::class, 'leaves'])->name('reports.leaves');
            Route::get('/reports/tasks', [ReportController::class, 'tasks'])->name('reports.tasks');
            Route::get('/reports/updates', [ReportController::class, 'updates'])->name('reports.updates');
        });
    });
});

require __DIR__.'/auth.php';
