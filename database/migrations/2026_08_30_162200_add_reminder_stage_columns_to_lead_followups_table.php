<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lead_followups', function (Blueprint $table) {
            if (!Schema::hasColumn('lead_followups', 'reminded_60m_at')) {
                $table->timestamp('reminded_60m_at')->nullable();
            }
            if (!Schema::hasColumn('lead_followups', 'reminded_30m_at')) {
                $table->timestamp('reminded_30m_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('lead_followups', function (Blueprint $table) {
            $table->dropColumn(['reminded_60m_at', 'reminded_30m_at']);
        });
    }
};
