<?php

use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\MeetingController;
use App\Http\Controllers\AuditLogController;

use App\Http\Controllers\ReportsController;
use App\Http\Controllers\ManagementController;
use App\Http\Controllers\Hr\AttendanceController;
use App\Http\Controllers\Hr\DailyTaskController;
use App\Http\Controllers\Hr\DailyUpdateController;
use App\Http\Controllers\Hr\DesignationController;
use App\Http\Controllers\Hr\HrDashboardController;
use App\Http\Controllers\Hr\LeaveController;
use App\Http\Controllers\Hr\ReportController;
use App\Http\Controllers\Hr\ShiftController;
use App\Http\Controllers\Hr\StaffController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\Crm\{ClientController, CrmDashboardController, FinanceController, FollowupController, LeadController};
use App\Http\Controllers\Finance\{
    FinanceDashboardController,
    ExpenseController,
    IncomeController,
    AccountController,
    TransactionController,
    TransferController,
    VendorController,
    ReceivablesController,
    ReportController as FinanceReportController,
    CategoryController as FinanceCategoryController,
};
use App\Http\Controllers\Projects\{
    ProjectController,
    ProjectTeamController,
    ProjectMilestoneController,
    ProjectTaskController,
    TaskCommentController,
    ProjectFileController,
    ProjectTimeTrackingController,
    ProjectExpenseController,
    ProjectCalendarController,
    ProjectReportController,
    WorkloadController
};
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : view('welcome');
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
        Route::patch('/notifications', [ProfileController::class, 'updateNotificationPreferences'])->name('notifications');
        Route::get('/sessions', [ProfileController::class, 'sessions'])->name('sessions');
        Route::delete('/sessions/{sessionId}', [ProfileController::class, 'revokeSession'])->name('sessions.revoke');
        Route::delete('/sessions', [ProfileController::class, 'revokeOthers'])->name('sessions.revoke-others');
    });

    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::get('/unread-count', [NotificationController::class, 'unreadCount'])->name('unread-count');
        Route::get('/recent', [NotificationController::class, 'recent'])->name('recent');
        Route::get('/{notification}/read', [NotificationController::class, 'markRead'])->name('read');
        Route::post('/mark-all-read', [NotificationController::class, 'markAllRead'])->name('read-all');
        Route::post('/clear', [NotificationController::class, 'destroyAll'])->name('clear');
        Route::delete('/{notification}', [NotificationController::class, 'destroy'])->name('destroy');
    });

    Route::get('/search', [SearchController::class, 'index'])->name('search.index');
    Route::get('/search/data', [SearchController::class, 'data'])->name('search.data');

    Route::middleware('permission:calendar.view')->group(function () {
        Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');
        Route::get('/calendar/events', [CalendarController::class, 'events'])->name('calendar.events');
    });

    Route::middleware('permission:meetings.view')->prefix('meetings')->name('meetings.')->group(function () {
        Route::get('/', [MeetingController::class, 'index'])->name('index');
        Route::post('/', [MeetingController::class, 'store'])->middleware('permission:meetings.create')->name('store');
        Route::get('/{meeting}', [MeetingController::class, 'show'])->name('show');
        Route::patch('/{meeting}', [MeetingController::class, 'update'])->middleware('permission:meetings.edit')->name('update');
        Route::patch('/{meeting}/notes', [MeetingController::class, 'updateNotes'])->middleware('permission:meetings.edit')->name('notes');
        Route::delete('/{meeting}', [MeetingController::class, 'destroy'])->middleware('permission:meetings.delete')->name('destroy');
    });

    Route::middleware('permission:reports.view')->group(function () {
        Route::get('/reports', [ReportsController::class, 'index'])->name('reports.index');
        Route::get('/reports/lead-conversion', [ReportsController::class, 'leadConversion'])->name('reports.lead-conversion');
        Route::get('/reports/lead-sources', [ReportsController::class, 'leadSources'])->name('reports.lead-sources');
        Route::get('/reports/client-revenue', [ReportsController::class, 'clientRevenue'])->name('reports.client-revenue');
        Route::get('/reports/invoice-aging', [ReportsController::class, 'invoiceAging'])->name('reports.invoice-aging');
        Route::get('/reports/task-performance', [ReportsController::class, 'taskPerformance'])->name('reports.task-performance');
        Route::get('/reports/export', [ReportsController::class, 'export'])->name('reports.export');
    });

    Route::get('/management/summary', [ManagementController::class, 'summary'])->middleware('permission:management.view')->name('management.summary');

    Route::middleware('permission:audit.view')->group(function () {
        Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit.index');
        Route::get('/audit-logs/export', [AuditLogController::class, 'export'])->name('audit.export');
    });



    Route::prefix('settings')->name('settings.')->middleware('permission:settings.view')->group(function () {
        Route::get('/general', [SettingsController::class, 'general'])->name('general');
        Route::post('/general', [SettingsController::class, 'updateGeneral'])->middleware('permission:settings.edit')->name('general.update');
        Route::get('/profile', [SettingsController::class, 'profile'])->name('profile');
        Route::post('/profile', [SettingsController::class, 'updateProfile'])->middleware('permission:settings.edit')->name('profile.update');
        Route::get('/branding', [SettingsController::class, 'branding'])->name('branding');
        Route::post('/branding', [SettingsController::class, 'updateBranding'])->middleware('permission:settings.edit')->name('branding.update');
        Route::get('/email', [SettingsController::class, 'email'])->name('email');
        Route::post('/email', [SettingsController::class, 'updateEmail'])->middleware('permission:settings.edit')->name('email.update');
        Route::post('/email/test', [SettingsController::class, 'testEmail'])->middleware('permission:settings.edit')->name('email.test');
Route::get('/notifications', [SettingsController::class, 'notifications'])->name('notifications');
Route::post('/notifications', [SettingsController::class, 'updateNotifications'])->middleware('permission:settings.edit')->name('notifications.update');
Route::get('/whatsapp', [SettingsController::class, 'whatsapp'])->name('whatsapp');
Route::post('/whatsapp', [SettingsController::class, 'updateWhatsapp'])->middleware('permission:settings.edit')->name('whatsapp.update');
Route::post('/whatsapp/test', [SettingsController::class, 'testWhatsapp'])->middleware('permission:settings.edit')->name('whatsapp.test');
        Route::get('/preferences', [SettingsController::class, 'preferences'])->name('preferences');
        Route::post('/preferences', [SettingsController::class, 'updatePreferences'])->middleware('permission:settings.edit')->name('preferences.update');
        Route::get('/leave-types', [SettingsController::class, 'leaveTypes'])->name('leave-types');
        Route::post('/leave-types', [SettingsController::class, 'storeLeaveType'])->middleware('permission:settings.edit')->name('leave-types.store');
        Route::delete('/leave-types/{leaveType}', [SettingsController::class, 'destroyLeaveType'])->middleware('permission:settings.edit')->name('leave-types.destroy');
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

        Route::middleware('permission:reports.view')->group(function () {
            Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
            Route::get('/reports/employees', [ReportController::class, 'employees'])->name('reports.employees');
            Route::get('/reports/attendance', [ReportController::class, 'attendance'])->name('reports.attendance');
            Route::get('/reports/leaves', [ReportController::class, 'leaves'])->name('reports.leaves');
            Route::get('/reports/tasks', [ReportController::class, 'tasks'])->name('reports.tasks');
            Route::get('/reports/updates', [ReportController::class, 'updates'])->name('reports.updates');
        });
    });

    Route::prefix('crm')->name('crm.')->group(function () {
        Route::get('/dashboard', CrmDashboardController::class)->middleware('permission:leads.view')->name('dashboard');
        Route::middleware('permission:leads.view')->group(function () {
            Route::get('/leads', [LeadController::class, 'index'])->name('leads.index');
            Route::get('/leads/pipeline', [LeadController::class, 'pipeline'])->name('leads.pipeline');
            Route::get('/leads/create', [LeadController::class, 'create'])->middleware('permission:leads.create')->name('leads.create');
            Route::post('/leads', [LeadController::class, 'store'])->middleware('permission:leads.create')->name('leads.store');
            Route::get('/leads/export', [LeadController::class, 'export'])->name('leads.export');
            Route::get('/leads/print', [LeadController::class, 'printLeads'])->name('leads.print');
            Route::get('/leads/import/template', [LeadController::class, 'importTemplate'])->name('leads.import-template');
Route::post('/leads/import', [LeadController::class, 'import'])->middleware('permission:leads.create')->name('leads.import');
Route::post('/leads/statuses', [LeadController::class, 'storeStatus'])->middleware('permission:leads.edit')->name('leads.statuses.store');
Route::delete('/leads/statuses/{leadStatus}', [LeadController::class, 'destroyStatus'])->middleware('permission:leads.edit')->name('leads.statuses.destroy');
Route::get('/leads/{lead}', [LeadController::class, 'show'])->name('leads.show');
            Route::get('/leads/{lead}/edit', [LeadController::class, 'edit'])->middleware('permission:leads.edit')->name('leads.edit');
            Route::patch('/leads/{lead}', [LeadController::class, 'update'])->middleware('permission:leads.edit')->name('leads.update');
            Route::patch('/leads/{lead}/status', [LeadController::class, 'updateStatus'])->middleware('permission:leads.edit')->name('leads.status.update');
            Route::post('/leads/{lead}/move', [LeadController::class, 'move'])->middleware('permission:leads.edit')->name('leads.move');
            Route::post('/leads/{lead}/followups', [LeadController::class, 'followup'])->middleware('permission:followups.create')->name('leads.followups.store');
            Route::post('/leads/{lead}/convert', [LeadController::class, 'convert'])->middleware('permission:leads.convert')->name('leads.convert');
            Route::delete('/leads/{lead}', [LeadController::class, 'destroy'])->middleware('permission:leads.delete')->name('leads.destroy');
        });
        Route::middleware('permission:clients.view')->group(function () { Route::resource('clients', ClientController::class); Route::get('/clients/{client}/ledger', [FinanceController::class, 'ledger'])->middleware('permission:ledger.view')->name('clients.ledger'); });
        Route::get('/follow-ups', [FollowupController::class, 'index'])->middleware('permission:followups.view')->name('followups.index');
        Route::patch('/follow-ups/{followup}', [FollowupController::class, 'update'])->middleware('permission:followups.edit')->name('followups.update');
        Route::get('/quotations', [FinanceController::class, 'quotations'])->middleware('permission:quotations.view')->name('quotations.index');
        Route::get('/quotations/create', [FinanceController::class, 'createQuotation'])->middleware('permission:quotations.create')->name('quotations.create');
        Route::post('/quotations', [FinanceController::class, 'storeQuotation'])->middleware('permission:quotations.create')->name('quotations.store');
        Route::get('/quotations/{quotation}', [FinanceController::class, 'showQuotation'])->middleware('permission:quotations.view')->name('quotations.show');
        Route::get('/quotations/{quotation}/edit', [FinanceController::class, 'editQuotation'])->middleware('permission:quotations.edit')->name('quotations.edit');
        Route::patch('/quotations/{quotation}', [FinanceController::class, 'updateQuotation'])->middleware('permission:quotations.edit')->name('quotations.update');
        Route::delete('/quotations/{quotation}', [FinanceController::class, 'destroyQuotation'])->middleware('permission:quotations.delete')->name('quotations.destroy');
        Route::get('/invoices', [FinanceController::class, 'invoices'])->middleware('permission:invoices.view')->name('invoices.index');
        Route::get('/payments', [FinanceController::class, 'payments'])->middleware('permission:payments.view')->name('payments.index');
        Route::post('/payments', [FinanceController::class, 'storePayment'])->middleware('permission:payments.create')->name('payments.store');
    });

    Route::prefix('projects')->name('projects.')->group(function () {
        Route::middleware('permission:projects.view')->group(function () {
            Route::get('/', [ProjectController::class, 'index'])->name('index');
            Route::get('/export', [ProjectController::class, 'export'])->middleware('permission:projects.export')->name('export');
            Route::get('/create', [ProjectController::class, 'create'])->middleware('permission:projects.create')->name('create');
            Route::post('/', [ProjectController::class, 'store'])->middleware('permission:projects.create')->name('store');
            Route::get('/{project}', [ProjectController::class, 'show'])->name('show');
            Route::get('/{project}/edit', [ProjectController::class, 'edit'])->middleware('permission:projects.edit')->name('edit');
            Route::patch('/{project}', [ProjectController::class, 'update'])->middleware('permission:projects.edit')->name('update');
            Route::delete('/{project}', [ProjectController::class, 'destroy'])->middleware('permission:projects.delete')->name('destroy');

            // Team Management
            Route::post('/{project}/team', [ProjectTeamController::class, 'store'])->middleware('permission:project_team.manage')->name('team.store');
            Route::delete('/{project}/team/{member}', [ProjectTeamController::class, 'destroy'])->middleware('permission:project_team.manage')->name('team.destroy');

            // Files Management
            Route::post('/{project}/files', [ProjectFileController::class, 'store'])->middleware('permission:project_files.upload')->name('files.store');
        });

        // Milestones
        Route::post('/milestones', [ProjectMilestoneController::class, 'store'])->middleware('permission:milestones.create')->name('milestones.store');
        Route::patch('/milestones/{milestone}', [ProjectMilestoneController::class, 'update'])->middleware('permission:milestones.edit')->name('milestones.update');
        Route::delete('/milestones/{milestone}', [ProjectMilestoneController::class, 'destroy'])->middleware('permission:milestones.delete')->name('milestones.destroy');

        // Tasks & Kanban
        Route::get('/tasks/list', [ProjectTaskController::class, 'index'])->middleware('permission:project_tasks.view')->name('tasks.index');
        Route::get('/tasks/kanban/{project?}', [ProjectTaskController::class, 'board'])->middleware('permission:project_tasks.view')->name('tasks.kanban');
        Route::post('/tasks', [ProjectTaskController::class, 'store'])->middleware('permission:project_tasks.create')->name('tasks.store');
        Route::get('/tasks/{task}', [ProjectTaskController::class, 'show'])->middleware('permission:project_tasks.view')->name('tasks.show');
        Route::patch('/tasks/{task}', [ProjectTaskController::class, 'update'])->middleware('permission:project_tasks.edit')->name('tasks.update');
        Route::post('/tasks/{task}/status', [ProjectTaskController::class, 'updateStatus'])->middleware('permission:project_tasks.edit')->name('tasks.status');
        Route::delete('/tasks/{task}', [ProjectTaskController::class, 'destroy'])->middleware('permission:project_tasks.delete')->name('tasks.destroy');

        // Comments
        Route::post('/tasks/{task}/comments', [TaskCommentController::class, 'store'])->middleware('permission:task_comments.create')->name('tasks.comments.store');
        Route::delete('/comments/{comment}', [TaskCommentController::class, 'destroy'])->middleware('permission:task_comments.delete')->name('comments.destroy');

        // File download & delete
        Route::get('/files/{file}/download', [ProjectFileController::class, 'download'])->middleware('permission:project_files.view')->name('files.download');
        Route::delete('/files/{file}', [ProjectFileController::class, 'destroy'])->middleware('permission:project_files.delete')->name('files.destroy');

        // Time tracking
        Route::get('/time-tracking/view', [ProjectTimeTrackingController::class, 'index'])->middleware('permission:time_tracking.view')->name('time.index');
        Route::post('/time-tracking/start', [ProjectTimeTrackingController::class, 'startTimer'])->middleware('permission:time_tracking.create')->name('time.start');
        Route::post('/time-tracking/stop/{entry}', [ProjectTimeTrackingController::class, 'stopTimer'])->middleware('permission:time_tracking.create')->name('time.stop');
        Route::post('/time-tracking/manual', [ProjectTimeTrackingController::class, 'storeManual'])->middleware('permission:time_tracking.create')->name('time.manual');

        // Expenses
        Route::post('/expenses', [ProjectExpenseController::class, 'store'])->middleware('permission:project_expenses.create')->name('expenses.store');
        Route::delete('/expenses/{expense}', [ProjectExpenseController::class, 'destroy'])->middleware('permission:project_expenses.edit')->name('expenses.destroy');

        // Calendar
        Route::get('/calendar/view', [ProjectCalendarController::class, 'index'])->middleware('permission:projects.view')->name('calendar');

        // Reports & Workload
        Route::get('/reports/analytics', [ProjectReportController::class, 'index'])->middleware('permission:project_reports.view')->name('reports.index');
        Route::get('/reports/export', [ProjectReportController::class, 'export'])->middleware('permission:project_reports.export')->name('reports.export');
        Route::get('/workload/overview', [WorkloadController::class, 'index'])->middleware('permission:project_team.view')->name('workload');
    });
    // ─── Finance Module ───────────────────────────────────────────────────────
    Route::prefix('finance')->name('finance.')->middleware('permission:finance.view')->group(function () {

        Route::get('/dashboard', FinanceDashboardController::class)->middleware('permission:finance.dashboard')->name('dashboard');

        // Expenses
        Route::middleware('permission:expenses.view')->prefix('expenses')->name('expenses.')->group(function () {
            Route::get('/', [ExpenseController::class, 'index'])->name('index');
            Route::get('/export', [ExpenseController::class, 'export'])->middleware('permission:expenses.export')->name('export');
            Route::get('/create', [ExpenseController::class, 'create'])->middleware('permission:expenses.create')->name('create');
            Route::post('/', [ExpenseController::class, 'store'])->middleware('permission:expenses.create')->name('store');
            Route::get('/{expense}', [ExpenseController::class, 'show'])->name('show');
            Route::get('/{expense}/edit', [ExpenseController::class, 'edit'])->middleware('permission:expenses.edit')->name('edit');
            Route::patch('/{expense}', [ExpenseController::class, 'update'])->middleware('permission:expenses.edit')->name('update');
            Route::post('/{expense}/approve', [ExpenseController::class, 'approve'])->middleware('permission:expenses.approve')->name('approve');
            Route::post('/{expense}/reject', [ExpenseController::class, 'reject'])->middleware('permission:expenses.approve')->name('reject');
            Route::delete('/{expense}', [ExpenseController::class, 'destroy'])->middleware('permission:expenses.delete')->name('destroy');
        });

        // Income
        Route::middleware('permission:income.view')->prefix('income')->name('income.')->group(function () {
            Route::get('/', [IncomeController::class, 'index'])->name('index');
            Route::get('/export', [IncomeController::class, 'export'])->middleware('permission:income.export')->name('export');
            Route::get('/create', [IncomeController::class, 'create'])->middleware('permission:income.create')->name('create');
            Route::post('/', [IncomeController::class, 'store'])->middleware('permission:income.create')->name('store');
            Route::get('/{income}', [IncomeController::class, 'show'])->name('show');
            Route::get('/{income}/edit', [IncomeController::class, 'edit'])->middleware('permission:income.edit')->name('edit');
            Route::patch('/{income}', [IncomeController::class, 'update'])->middleware('permission:income.edit')->name('update');
            Route::delete('/{income}', [IncomeController::class, 'destroy'])->middleware('permission:income.delete')->name('destroy');
        });

        // Accounts
        Route::middleware('permission:accounts.view')->prefix('accounts')->name('accounts.')->group(function () {
            Route::get('/cash', [AccountController::class, 'cashIndex'])->name('cash');
            Route::get('/cash/create', [AccountController::class, 'cashCreate'])->middleware('permission:accounts.create')->name('cash.create');
            Route::post('/cash', [AccountController::class, 'cashStore'])->middleware('permission:accounts.create')->name('cash.store');
            Route::get('/cash/{cashAccount}/edit', [AccountController::class, 'cashEdit'])->middleware('permission:accounts.edit')->name('cash.edit');
            Route::patch('/cash/{cashAccount}', [AccountController::class, 'cashUpdate'])->middleware('permission:accounts.edit')->name('cash.update');
            Route::get('/bank', [AccountController::class, 'bankIndex'])->name('bank');
            Route::get('/bank/create', [AccountController::class, 'bankCreate'])->middleware('permission:accounts.create')->name('bank.create');
            Route::post('/bank', [AccountController::class, 'bankStore'])->middleware('permission:accounts.create')->name('bank.store');
            Route::get('/bank/{bankAccount}/edit', [AccountController::class, 'bankEdit'])->middleware('permission:accounts.edit')->name('bank.edit');
            Route::patch('/bank/{bankAccount}', [AccountController::class, 'bankUpdate'])->middleware('permission:accounts.edit')->name('bank.update');
        });

        // Transactions
        Route::middleware('permission:transactions.view')->prefix('transactions')->name('transactions.')->group(function () {
            Route::get('/', [TransactionController::class, 'index'])->name('index');
            Route::get('/create', [TransactionController::class, 'create'])->middleware('permission:transactions.create')->name('create');
            Route::post('/', [TransactionController::class, 'store'])->middleware('permission:transactions.create')->name('store');
            Route::get('/{transaction}', [TransactionController::class, 'show'])->name('show');
            Route::get('/{transaction}/edit', [TransactionController::class, 'edit'])->middleware('permission:transactions.edit')->name('edit');
            Route::patch('/{transaction}', [TransactionController::class, 'update'])->middleware('permission:transactions.edit')->name('update');
            Route::delete('/{transaction}', [TransactionController::class, 'destroy'])->middleware('permission:transactions.delete')->name('destroy');
        });

        // Transfers
        Route::middleware('permission:transactions.view')->prefix('transfers')->name('transfers.')->group(function () {
            Route::get('/', [TransferController::class, 'index'])->name('index');
            Route::post('/', [TransferController::class, 'store'])->middleware('permission:transactions.create')->name('store');
        });

        // Vendors
        Route::middleware('permission:vendors.view')->prefix('vendors')->name('vendors.')->group(function () {
            Route::get('/', [VendorController::class, 'index'])->name('index');
            Route::get('/create', [VendorController::class, 'create'])->middleware('permission:vendors.create')->name('create');
            Route::post('/', [VendorController::class, 'store'])->middleware('permission:vendors.create')->name('store');
            Route::get('/{vendor}', [VendorController::class, 'show'])->name('show');
            Route::get('/{vendor}/edit', [VendorController::class, 'edit'])->middleware('permission:vendors.edit')->name('edit');
            Route::patch('/{vendor}', [VendorController::class, 'update'])->middleware('permission:vendors.edit')->name('update');
            Route::delete('/{vendor}', [VendorController::class, 'destroy'])->middleware('permission:vendors.delete')->name('destroy');
            Route::post('/{vendor}/payments', [VendorController::class, 'storePayment'])->middleware('permission:payments.create')->name('payments.store');
        });

        // Receivables
        Route::middleware('permission:receivables.view')->prefix('receivables')->name('receivables.')->group(function () {
            Route::get('/', [ReceivablesController::class, 'index'])->name('index');
            Route::post('/invoices/{invoice}/reminder', [ReceivablesController::class, 'sendReminder'])->name('reminder');
            Route::get('/export', [ReceivablesController::class, 'export'])->name('export');
        });

        // Reports
        Route::middleware('permission:financial_reports.view')->prefix('reports')->name('reports.')->group(function () {
            Route::get('/', [FinanceReportController::class, 'index'])->name('index');
            Route::get('/profit-loss', [FinanceReportController::class, 'profitLoss'])->name('profit-loss');
            Route::get('/expenses', [FinanceReportController::class, 'expenseReport'])->name('expenses');
            Route::get('/income', [FinanceReportController::class, 'incomeReport'])->name('income');
            Route::get('/project-profitability', [FinanceReportController::class, 'projectProfitability'])->name('project-profitability');
            Route::get('/office-expenses', [FinanceReportController::class, 'officeExpenses'])->name('office-expenses');
            Route::get('/cash-flow', [FinanceReportController::class, 'cashFlow'])->name('cash-flow');
        });

        // Categories (admin)
        Route::middleware('permission:financial_data.manage')->prefix('categories')->name('categories.')->group(function () {
            Route::get('/', [FinanceCategoryController::class, 'index'])->name('index');
            Route::post('/expense', [FinanceCategoryController::class, 'storeExpense'])->name('expense.store');
            Route::post('/income', [FinanceCategoryController::class, 'storeIncome'])->name('income.store');
            Route::post('/method', [FinanceCategoryController::class, 'storeMethod'])->name('method.store');
            Route::delete('/expense/{expenseCategory}', [FinanceCategoryController::class, 'destroyExpense'])->name('expense.destroy');
            Route::delete('/income/{incomeCategory}', [FinanceCategoryController::class, 'destroyIncome'])->name('income.destroy');
            Route::delete('/method/{paymentMethod}', [FinanceCategoryController::class, 'destroyMethod'])->name('method.destroy');
        });
    });
});

require __DIR__.'/auth.php';
