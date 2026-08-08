<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_work_updates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->date('date');
            $table->text('tasks_completed')->nullable();
            $table->text('work_description')->nullable();
            $table->text('problems')->nullable();
            $table->decimal('hours_worked', 4, 1)->nullable();
            $table->text('tomorrow_plan')->nullable();
            $table->text('additional_notes')->nullable();
            $table->string('status', 30)->default('submitted');
            $table->text('review_remarks')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('reviewed_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();

            $table->unique(['employee_id', 'date']);
            $table->index(['date']);
            $table->index(['status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_work_updates');
    }
};

