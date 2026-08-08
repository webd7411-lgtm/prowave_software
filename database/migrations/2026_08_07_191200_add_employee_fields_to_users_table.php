<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('father_name', 255)->nullable()->after('name');
            $table->string('whatsapp', 30)->nullable()->after('phone');
            $table->string('employment_type', 30)->nullable()->after('designation');
            $table->string('employee_status', 30)->nullable()->after('status');
            $table->decimal('salary', 15, 2)->nullable()->after('employment_type');
            $table->unsignedBigInteger('shift_id')->nullable()->after('salary');

            $table->foreign('shift_id')->references('id')->on('shifts')->nullOnDelete();
            $table->index(['employee_status']);
            $table->index(['shift_id']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['shift_id']);
            $table->dropIndex(['employee_status']);
            $table->dropIndex(['shift_id']);
            $table->dropColumn([
                'father_name', 'whatsapp', 'employment_type', 'employee_status', 'salary', 'shift_id',
            ]);
        });
    }
};

