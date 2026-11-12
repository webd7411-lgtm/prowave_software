<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('designations', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id')->nullable()->after('department_id');
            $table->foreign('role_id')->references('id')->on('roles')->nullOnDelete();
            $table->index(['role_id']);
        });

        // Best-effort auto-mapping for existing designations that match a role name
        $rolesByLowerName = DB::table('roles')
            ->pluck('id', 'name')
            ->mapWithKeys(fn ($id, $name) => [strtolower($name) => $id]);

        $aliases = [
            'software developer' => 'developer',
            'senior developer' => 'developer',
            'ui/ux designer' => 'designer',
            'graphic designer' => 'designer',
            'qa engineer' => 'qa',
        ];

        $designations = DB::table('designations')->select('id', 'name')->get();
        foreach ($designations as $designation) {
            $key = strtolower(trim($designation->name));
            $roleId = $rolesByLowerName[$key] ?? ($aliases[$key] ?? null ? ($rolesByLowerName[$aliases[$key]] ?? null) : null);

            if ($roleId) {
                DB::table('designations')
                    ->where('id', $designation->id)
                    ->update(['role_id' => $roleId]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('designations', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropIndex(['role_id']);
            $table->dropColumn('role_id');
        });
    }
};
