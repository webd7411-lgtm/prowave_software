<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1. Expense Categories
        Schema::create('expense_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('color', 20)->default('#6c757d');
            $table->string('icon', 50)->default('bi-tag');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->boolean('is_system')->default(false); // prevent deletion
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 2. Income Categories
        Schema::create('income_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('color', 20)->default('#198754');
            $table->string('icon', 50)->default('bi-arrow-down-circle');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->boolean('is_system')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 3. Payment Methods
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('icon', 50)->default('bi-credit-card');
            $table->boolean('is_active')->default(true)->index();
            $table->boolean('is_system')->default(false);
            $table->timestamps();
        });

        // 4. Cash Accounts
        Schema::create('cash_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('account_code')->unique();
            $table->decimal('opening_balance', 15, 2)->default(0);
            $table->decimal('current_balance', 15, 2)->default(0);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 5. Bank Accounts
        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('bank_name');
            $table->string('account_title');
            $table->string('account_number')->nullable();
            $table->string('iban')->nullable();
            $table->string('branch')->nullable();
            $table->string('swift_code')->nullable();
            $table->decimal('opening_balance', 15, 2)->default(0);
            $table->decimal('current_balance', 15, 2)->default(0);
            $table->string('currency', 10)->default('PKR');
            $table->string('status')->default('active')->index();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 6. Vendors / Suppliers
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->string('vendor_number')->unique();
            $table->string('name');
            $table->string('contact_person')->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('whatsapp', 50)->nullable();
            $table->string('email')->nullable()->index();
            $table->text('address')->nullable();
            $table->string('category')->nullable()->index();
            $table->text('notes')->nullable();
            $table->string('status')->default('active')->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });

        // 7. Expenses
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('expense_number')->unique();
            $table->foreignId('category_id')->nullable()->constrained('expense_categories')->nullOnDelete();
            $table->decimal('amount', 15, 2)->default(0);
            $table->date('expense_date')->index();
            $table->string('account_type')->default('cash'); // cash / bank
            $table->foreignId('cash_account_id')->nullable()->constrained('cash_accounts')->nullOnDelete();
            $table->foreignId('bank_account_id')->nullable()->constrained('bank_accounts')->nullOnDelete();
            $table->foreignId('payment_method_id')->nullable()->constrained('payment_methods')->nullOnDelete();
            $table->string('reference_number')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('vendor_id')->nullable()->constrained('vendors')->nullOnDelete();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->string('receipt_path')->nullable();
            $table->string('status')->default('pending')->index(); // pending, approved, rejected, paid
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_remarks')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('added_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });

        // 8. Income
        Schema::create('income_records', function (Blueprint $table) {
            $table->id();
            $table->string('income_number')->unique();
            $table->foreignId('category_id')->nullable()->constrained('income_categories')->nullOnDelete();
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
            $table->foreignId('payment_id')->nullable()->constrained('payments')->nullOnDelete();
            $table->decimal('amount', 15, 2)->default(0);
            $table->date('income_date')->index();
            $table->string('account_type')->default('bank'); // cash / bank
            $table->foreignId('cash_account_id')->nullable()->constrained('cash_accounts')->nullOnDelete();
            $table->foreignId('bank_account_id')->nullable()->constrained('bank_accounts')->nullOnDelete();
            $table->foreignId('payment_method_id')->nullable()->constrained('payment_methods')->nullOnDelete();
            $table->string('reference_number')->nullable();
            $table->text('description')->nullable();
            $table->string('attachment_path')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('added_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });

        // 9. Financial Transactions (Unified Ledger)
        Schema::create('financial_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->string('type')->index(); // income, expense, transfer, payment, refund, adjustment
            $table->string('account_type')->index(); // cash, bank
            $table->foreignId('cash_account_id')->nullable()->constrained('cash_accounts')->nullOnDelete();
            $table->foreignId('bank_account_id')->nullable()->constrained('bank_accounts')->nullOnDelete();
            $table->text('description')->nullable();
            $table->decimal('debit', 15, 2)->default(0);
            $table->decimal('credit', 15, 2)->default(0);
            $table->decimal('balance_after', 15, 2)->default(0);
            $table->date('transaction_date')->index();
            $table->morphs('source'); // polymorphic to Expense/Income/Transfer/Payment
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['account_type', 'cash_account_id']);
            $table->index(['account_type', 'bank_account_id']);
        });

        // 10. Account Transfers
        Schema::create('account_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('transfer_number')->unique();
            $table->string('from_account_type'); // cash, bank
            $table->foreignId('from_cash_account_id')->nullable()->constrained('cash_accounts')->nullOnDelete();
            $table->foreignId('from_bank_account_id')->nullable()->constrained('bank_accounts')->nullOnDelete();
            $table->string('to_account_type'); // cash, bank
            $table->foreignId('to_cash_account_id')->nullable()->constrained('cash_accounts')->nullOnDelete();
            $table->foreignId('to_bank_account_id')->nullable()->constrained('bank_accounts')->nullOnDelete();
            $table->decimal('amount', 15, 2)->default(0);
            $table->date('transfer_date')->index();
            $table->string('reference')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 11. Financial Adjustments
        Schema::create('financial_adjustments', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->string('account_type'); // cash, bank
            $table->foreignId('cash_account_id')->nullable()->constrained('cash_accounts')->nullOnDelete();
            $table->foreignId('bank_account_id')->nullable()->constrained('bank_accounts')->nullOnDelete();
            $table->string('adjustment_type'); // increase, decrease
            $table->decimal('amount', 15, 2)->default(0);
            $table->date('adjustment_date')->index();
            $table->text('reason');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 12. Vendor Payments
        Schema::create('vendor_payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_number')->unique();
            $table->foreignId('vendor_id')->constrained('vendors')->restrictOnDelete();
            $table->foreignId('expense_id')->nullable()->constrained('expenses')->nullOnDelete();
            $table->decimal('amount', 15, 2)->default(0);
            $table->date('payment_date')->index();
            $table->string('account_type')->default('cash');
            $table->foreignId('cash_account_id')->nullable()->constrained('cash_accounts')->nullOnDelete();
            $table->foreignId('bank_account_id')->nullable()->constrained('bank_accounts')->nullOnDelete();
            $table->foreignId('payment_method_id')->nullable()->constrained('payment_methods')->nullOnDelete();
            $table->string('reference_number')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 13. Payment Reminders
        Schema::create('payment_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->string('channel')->default('manual'); // manual, email, whatsapp, sms
            $table->text('message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->string('status')->default('sent');
            $table->foreignId('sent_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach ([
            'payment_reminders',
            'vendor_payments',
            'financial_adjustments',
            'account_transfers',
            'financial_transactions',
            'income_records',
            'expenses',
            'vendors',
            'bank_accounts',
            'cash_accounts',
            'payment_methods',
            'income_categories',
            'expense_categories',
        ] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
