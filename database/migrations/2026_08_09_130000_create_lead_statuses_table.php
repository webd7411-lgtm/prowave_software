<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lead_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('slug', 100)->unique();
            $table->string('color', 20)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        $statuses = [
            'New' => ['new', '#6c757d'], 'Contacted' => ['contacted', '#0dcaf0'],
            'Qualified' => ['qualified', '#0d6efd'], 'Proposal Sent' => ['proposal_sent', '#6f42c1'],
            'Negotiation' => ['negotiation', '#fd7e14'], 'Meeting Scheduled' => ['meeting_scheduled', '#198754'],
            'Won' => ['won', '#198754'], 'Lost' => ['lost', '#dc3545'], 'Converted' => ['converted', '#20c997'],
        ];

        foreach ($statuses as $name => [$slug, $color]) {
            DB::table('lead_statuses')->insert([
                'name' => $name, 'slug' => $slug, 'color' => $color,
                'sort_order' => array_search($name, array_keys($statuses)) + 1,
                'is_active' => true, 'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_statuses');
    }
};