<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{ExpenseCategory, IncomeCategory, PaymentMethod, CashAccount, BankAccount};
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class FinanceSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // ── Permissions ────────────────────────────────────────────────────────
        $financePermissions = [
            'finance.view', 'finance.dashboard',
            'expenses.view', 'expenses.create', 'expenses.edit', 'expenses.delete', 'expenses.approve', 'expenses.export',
            'income.view', 'income.create', 'income.edit', 'income.delete', 'income.export',
            'accounts.view', 'accounts.create', 'accounts.edit',
            'transactions.view', 'transactions.create',
            'payments.view', 'payments.create', 'payments.edit',
            'invoices.view', 'invoices.create', 'invoices.edit', 'invoices.delete', 'invoices.export',
            'receivables.view',
            'financial_reports.view', 'financial_reports.export',
            'financial_data.manage',
            'vendors.view', 'vendors.create', 'vendors.edit', 'vendors.delete',
        ];

        $createdPermissions = [];
        foreach ($financePermissions as $perm) {
            $createdPermissions[$perm] = Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // Give Super Admin + Administrator all finance permissions
        foreach (['Super Admin', 'Administrator'] as $roleName) {
            $role = Role::where('name', $roleName)->first();
            if ($role) $role->givePermissionTo($createdPermissions);
        }

        // Accountant role
        $accountant = Role::where('name', 'Accountant')->first();
        if ($accountant) {
            $accountant->givePermissionTo([
                'finance.view', 'finance.dashboard',
                'expenses.view', 'expenses.create', 'expenses.edit', 'expenses.approve', 'expenses.export',
                'income.view', 'income.create', 'income.edit', 'income.export',
                'accounts.view', 'accounts.create', 'accounts.edit',
                'transactions.view',
                'receivables.view',
                'financial_reports.view', 'financial_reports.export',
                'vendors.view', 'vendors.create', 'vendors.edit',
            ]);
        }

        // ── Expense Categories ──────────────────────────────────────────────────
        foreach (ExpenseCategory::defaultCategories() as $cat) {
            ExpenseCategory::firstOrCreate(['slug' => $cat['slug']], array_merge($cat, ['is_active' => true]));
        }

        // ── Income Categories ───────────────────────────────────────────────────
        foreach (IncomeCategory::defaultCategories() as $cat) {
            IncomeCategory::firstOrCreate(['slug' => $cat['slug']], array_merge($cat, ['is_active' => true]));
        }

        // ── Payment Methods ─────────────────────────────────────────────────────
        foreach (PaymentMethod::defaults() as $m) {
            PaymentMethod::firstOrCreate(['slug' => $m['slug']], array_merge($m, ['is_active' => true]));
        }

        // ── Default Cash Account ────────────────────────────────────────────────
        CashAccount::firstOrCreate(['account_code' => 'CASH-001'], [
            'name'            => 'Office Cash',
            'opening_balance' => 0,
            'current_balance' => 0,
            'description'     => 'Main office cash account',
            'is_active'       => true,
        ]);
        CashAccount::firstOrCreate(['account_code' => 'CASH-002'], [
            'name'            => 'Petty Cash',
            'opening_balance' => 0,
            'current_balance' => 0,
            'description'     => 'Petty cash for small expenses',
            'is_active'       => true,
        ]);
    }
}
