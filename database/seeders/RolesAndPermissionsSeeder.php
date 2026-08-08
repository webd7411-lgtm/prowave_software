<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissionGroups = [
            'Dashboard' => [
                'dashboard.view',
            ],
            'Users' => [
                'users.view',
                'users.show',
                'users.create',
                'users.edit',
                'users.delete',
                'users.restore',
                'users.force-delete',
                'users.import',
                'users.export',
                'users.activate',
                'users.suspend',
                'users.reset-password',
            ],
            'Roles' => [
                'roles.view',
                'roles.create',
                'roles.edit',
                'roles.delete',
            ],
            'Permissions' => [
                'permissions.view',
                'permissions.assign',
            ],
'Departments' => [
                'departments.view',
                'departments.create',
                'departments.edit',
                'departments.delete',
            ],
            'HR Dashboard' => [
                'hr_dashboard.view',
            ],
            'Staff' => [
                'staff.view',
                'staff.show',
                'staff.create',
                'staff.edit',
                'staff.delete',
                'staff.restore',
                'staff.export',
                'staff.import',
            ],
            'Designations' => [
                'designations.view',
                'designations.create',
                'designations.edit',
                'designations.delete',
            ],
            'Shifts' => [
                'shifts.view',
                'shifts.create',
                'shifts.edit',
                'shifts.delete',
            ],
            'Leave' => [
                'leave.view',
                'leave.create',
                'leave.approve',
                'leave.reject',
            ],
            'Daily Reports' => [
                'daily_reports.view',
                'daily_reports.create',
                'daily_reports.review',
            ],
            'Employee Documents' => [
                'employee_documents.view',
                'employee_documents.upload',
                'employee_documents.delete',
            ],
            'Projects' => [
                'projects.view',
                'projects.create',
                'projects.edit',
                'projects.delete',
                'projects.assign',
            ],
            'Tasks' => [
                'tasks.view',
                'tasks.create',
                'tasks.edit',
                'tasks.delete',
                'tasks.assign',
            ],
            'Clients' => [
                'clients.view',
                'clients.create',
                'clients.edit',
                'clients.delete',
            ],
            'Invoices' => [
                'invoices.view',
                'invoices.create',
                'invoices.edit',
                'invoices.delete',
            ],
            'Expenses' => [
                'expenses.view',
                'expenses.create',
                'expenses.edit',
                'expenses.delete',
            ],
            'Attendance' => [
                'attendance.view',
                'attendance.create',
                'attendance.edit',
                'attendance.approve',
            ],
            'Payroll' => [
                'payroll.view',
                'payroll.create',
                'payroll.edit',
            ],
            'Reports' => [
                'reports.view',
            ],
            'Audit Logs' => [
                'audit.view',
            ],
            'Settings' => [
                'settings.view',
                'settings.edit',
            ],
        ];

        $permissions = [];

        foreach ($permissionGroups as $group => $names) {
            foreach ($names as $name) {
                $permissions[$name] = Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
            }
        }

        $all = array_keys($permissions);

        $roles = [
            'Super Admin' => $all,
            'Administrator' => $all,
'HR Manager' => [
                'dashboard.view',
                'hr_dashboard.view',
                'users.view', 'users.show', 'users.create', 'users.edit', 'users.delete',
                'users.restore', 'users.import', 'users.export', 'users.activate',
                'users.suspend', 'users.reset-password',
                'departments.view', 'departments.create', 'departments.edit', 'departments.delete',
                'staff.view', 'staff.show', 'staff.create', 'staff.edit', 'staff.delete',
                'staff.restore', 'staff.export', 'staff.import',
                'designations.view', 'designations.create', 'designations.edit', 'designations.delete',
                'shifts.view', 'shifts.create', 'shifts.edit', 'shifts.delete',
                'attendance.view', 'attendance.create', 'attendance.edit', 'attendance.approve', 'attendance.manage',
                'tasks.view', 'tasks.create', 'tasks.edit', 'tasks.delete', 'tasks.assign',
                'leave.view', 'leave.create', 'leave.approve', 'leave.reject',
                'daily_reports.view', 'daily_reports.create', 'daily_reports.review',
                'employee_documents.view', 'employee_documents.upload', 'employee_documents.delete',
                'payroll.view', 'payroll.create', 'payroll.edit',
                'roles.view', 'permissions.view',
                'reports.view', 'audit.view', 'settings.view',
            ],
'Project Manager' => [
                'dashboard.view',
                'projects.view', 'projects.create', 'projects.edit', 'projects.delete', 'projects.assign',
                'tasks.view', 'tasks.create', 'tasks.edit', 'tasks.delete', 'tasks.assign',
                'clients.view', 'clients.create', 'clients.edit',
                'users.view', 'users.show',
                'staff.view', 'staff.show',
                'attendance.view',
                'daily_reports.view', 'daily_reports.review',
                'reports.view',
            ],
            'Team Lead' => [
                'dashboard.view',
                'projects.view', 'projects.edit',
                'tasks.view', 'tasks.create', 'tasks.edit', 'tasks.assign',
                'users.view', 'users.show',
                'staff.view', 'staff.show',
                'daily_reports.view', 'daily_reports.review',
            ],
            'Developer' => [
                'dashboard.view',
                'projects.view',
                'tasks.view', 'tasks.edit',
                'daily_reports.view', 'daily_reports.create',
                'attendance.view', 'attendance.create',
                'leave.view', 'leave.create',
            ],
            'Designer' => [
                'dashboard.view',
                'projects.view',
                'tasks.view', 'tasks.edit',
                'daily_reports.view', 'daily_reports.create',
                'attendance.view', 'attendance.create',
                'leave.view', 'leave.create',
            ],
            'QA' => [
                'dashboard.view',
                'projects.view',
                'tasks.view', 'tasks.edit',
                'daily_reports.view', 'daily_reports.create',
                'attendance.view', 'attendance.create',
                'leave.view', 'leave.create',
            ],
            'Sales Executive' => [
                'dashboard.view',
                'clients.view', 'clients.create', 'clients.edit',
                'invoices.view',
                'reports.view',
            ],
            'Accountant' => [
                'dashboard.view',
                'invoices.view', 'invoices.create', 'invoices.edit',
                'expenses.view', 'expenses.create', 'expenses.edit',
                'payroll.view',
                'reports.view',
            ],
            'Client' => [
                'dashboard.view',
                'projects.view',
                'tasks.view',
            ],
        ];

        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);

            if ($roleName === 'Super Admin') {
                $role->syncPermissions($permissions);
            } else {
                $role->syncPermissions($rolePermissions);
            }
        }
    }
}
