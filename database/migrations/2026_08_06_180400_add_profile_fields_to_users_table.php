<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('avatar')->nullable()->after('password');
            $table->string('phone')->nullable()->after('avatar');
            $table->string('job_title')->nullable()->after('phone');
            $table->string('department')->nullable()->after('job_title');
            $table->string('bio', 1000)->nullable()->after('department');
            $table->string('theme', 20)->default('light')->after('bio');
            $table->string('language', 10)->default('en')->after('theme');
            $table->string('locale', 10)->default('en')->after('language');
            $table->string('timezone', 50)->nullable()->after('locale');
            $table->string('date_format', 20)->default('d M, Y')->after('timezone');
            $table->boolean('is_active')->default(true)->after('date_format');
            $table->timestamp('last_login_at')->nullable()->after('is_active');
            $table->string('last_login_ip')->nullable()->after('last_login_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'avatar', 'phone', 'job_title', 'department', 'bio',
                'theme', 'language', 'locale', 'timezone', 'date_format',
                'is_active', 'last_login_at', 'last_login_ip',
            ]);
        });
    }
};
