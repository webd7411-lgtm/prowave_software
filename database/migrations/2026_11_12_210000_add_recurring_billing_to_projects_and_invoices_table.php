<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('billing_type')->default('one_time')->after('project_type'); // one_time, monthly, annually
            $table->decimal('recurring_amount', 15, 2)->nullable()->after('budget');
            $table->date('billing_start_date')->nullable()->after('recurring_amount');
            $table->date('next_billing_date')->nullable()->after('billing_start_date')->index();
            $table->unsignedSmallInteger('billing_alert_day')->default(5)->after('next_billing_date'); // monthly: day of month (1-31), annually: days before renewal (e.g. 7/15)
            $table->date('last_invoiced_at')->nullable()->after('billing_alert_day');
            $table->index(['billing_type']);
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->foreignId('project_id')->nullable()->after('quotation_id')->constrained('projects')->nullOnDelete();
            $table->string('billing_cycle_label')->nullable()->after('notes'); // e.g. "Monthly (Nov 2026)", "Annual (2026-2027)"
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
            $table->dropColumn(['project_id', 'billing_cycle_label']);
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->dropIndex(['billing_type']);
            $table->dropIndex(['next_billing_date']);
            $table->dropColumn([
                'billing_type',
                'recurring_amount',
                'billing_start_date',
                'next_billing_date',
                'billing_alert_day',
                'last_invoiced_at',
            ]);
        });
    }
};
