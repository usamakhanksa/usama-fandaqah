<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('permission_role')) {
            Schema::table('permission_role', function (Blueprint $table) {
                if (!Schema::hasColumn('permission_role', 'enabled')) {
                    $table->boolean('enabled')->default(false);
                    $table->boolean('anyone')->default(false);
                    $table->boolean('can_create')->default(false);
                    $table->boolean('can_edit')->default(false);
                    $table->boolean('can_view')->default(false);
                    $table->boolean('can_remove')->default(false);
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('permission_role')) {
            Schema::table('permission_role', function (Blueprint $table) {
                $table->dropColumn(['enabled', 'anyone', 'can_create', 'can_edit', 'can_view', 'can_remove']);
            });
        }
    }
};
