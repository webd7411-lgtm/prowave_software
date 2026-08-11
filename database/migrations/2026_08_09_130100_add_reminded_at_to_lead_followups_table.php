<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lead_followups', function (Blueprint $table) {
            $table->timestamp('reminded_at')->nullable()->after('reminder');
        });

        foreach ([
            'whatsapp.api_url' => 'https://api.gateway.com/send-message',
            'whatsapp.api_token' => '',
            'whatsapp.default_number' => '',
        ] as $key => $value) {
            [$group, $k] = explode('.', $key, 2);
            DB::table('settings')->updateOrInsert(
                ['group' => $group, 'key' => $k],
                ['value' => $value, 'type' => 'string', 'created_at' => now(), 'updated_at' => now()]
            );
        }
    }

    public function down(): void
    {
        Schema::table('lead_followups', function (Blueprint $table) {
            $table->dropColumn('reminded_at');
        });
        DB::table('settings')->where('group', 'whatsapp')->delete();
    }
};