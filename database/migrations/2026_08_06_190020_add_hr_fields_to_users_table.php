<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // HR / identification fields
            $table->string('employee_id', 50)->nullable()->after('name');
            $table->string('username', 100)->unique()->nullable()->after('employee_id');
            $table->string('cnic', 30)->unique()->nullable()->after('phone');
            $table->string('gender', 20)->nullable()->after('cnic');
            $table->date('dob')->nullable()->after('gender');
            $table->date('joining_date')->nullable()->after('dob');
            $table->unsignedBigInteger('department_id')->nullable()->after('joining_date');
            $table->string('designation')->nullable()->after('department_id');
            $table->unsignedBigInteger('reporting_manager_id')->nullable()->after('designation');

            // Address
            $table->string('address', 500)->nullable()->after('reporting_manager_id');
            $table->string('city', 100)->nullable()->after('address');
            $table->string('province', 100)->nullable()->after('city');
            $table->string('country', 100)->nullable()->after('province');
            $table->string('postal_code', 20)->nullable()->after('country');
            $table->string('emergency_contact', 255)->nullable()->after('postal_code');
            $table->string('emergency_phone', 30)->nullable()->after('emergency_contact');
            $table->text('notes')->nullable()->after('emergency_phone');

            // Status + lock
            $table->string('status', 20)->default('active')->after('is_active');
            $table->unsignedTinyInteger('login_attempts')->default(0)->after('status');
            $table->timestamp('locked_until')->nullable()->after('login_attempts');

            // Audit columns
            $table->unsignedBigInteger('created_by')->nullable()->after('last_login_ip');
            $table->unsignedBigInteger('updated_by')->nullable()->after('created_by');
            $table->unsignedBigInteger('deleted_by')->nullable()->after('updated_by');

            $table->softDeletes();

            $table->foreign('department_id')->references('id')->on('departments')->nullOnDelete();
            $table->foreign('reporting_manager_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('deleted_by')->references('id')->on('users')->nullOnDelete();

            $table->index(['status']);
            $table->index(['department_id']);
            $table->index(['joining_date']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropForeign(['reporting_manager_id']);
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
            $table->dropForeign(['deleted_by']);
            $table->dropIndex(['status']);
            $table->dropIndex(['department_id']);
            $table->dropIndex(['joining_date']);
            $table->dropSoftDeletes();
            $table->dropColumn([
                'employee_id', 'username', 'cnic', 'gender', 'dob', 'joining_date',
                'department_id', 'designation', 'reporting_manager_id',
                'address', 'city', 'province', 'country', 'postal_code',
                'emergency_contact', 'emergency_phone', 'notes',
                'status', 'login_attempts', 'locked_until',
                'created_by', 'updated_by', 'deleted_by',
            ]);
        });
    }
};
