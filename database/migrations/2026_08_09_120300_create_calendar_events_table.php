<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('calendar_events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('event_type')->default('event')->index(); // event, holiday, announcement, reminder
            $table->date('event_date')->index();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->boolean('all_day')->default(true);
            $table->string('color', 20)->default('#2563EB');
            $table->text('description')->nullable();
            $table->string('location')->nullable();
            $table->boolean('reminder')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['event_date', 'event_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calendar_events');
    }
};
