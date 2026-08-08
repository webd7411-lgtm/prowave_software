<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE users CHANGE department department_label VARCHAR(255) NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE users CHANGE department_label department VARCHAR(255) NULL');
    }
};
