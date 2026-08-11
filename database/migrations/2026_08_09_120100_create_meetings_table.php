<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('meetings', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('type')->default('internal')->index(); // client, internal, project, sales, hr, other
            $table->date('meeting_date')->index();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('location')->nullable();
            $table->string('meeting_link')->nullable();
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->json('participant_ids')->nullable();
            $table->text('description')->nullable();
            $table->text('agenda')->nullable();
            $table->text('notes')->nullable();
            $table->text('decisions')->nullable();
            $table->text('action_items')->nullable();
            $table->date('follow_up_date')->nullable();
            $table->string('status')->default('scheduled')->index(); // scheduled, ongoing, completed, cancelled
            $table->boolean('reminder_sent')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['meeting_date', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meetings');
    }
};
