<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('units', function (Blueprint $table) {
            if (!Schema::hasColumn('units', 'unit_category_id')) {
                $table->foreignId('unit_category_id')->nullable()->after('name')->constrained('unit_categories');
            }
            if (!Schema::hasColumn('units', 'floor')) {
                $table->string('floor')->nullable()->after('unit_category_id');
            }
            if (!Schema::hasColumn('units', 'capacity')) {
                $table->string('capacity')->default('2')->after('floor');
            }
            if (!Schema::hasColumn('units', 'beds')) {
                $table->string('beds')->default('1')->after('capacity');
            }
            if (!Schema::hasColumn('units', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('beds');
            }
            if (!Schema::hasColumn('units', 'enabled')) {
                $table->boolean('enabled')->default(true)->after('is_active');
            }
            if (!Schema::hasColumn('units', 'unit_number')) {
                $table->string('unit_number')->nullable()->after('team_id');
            }
            
            // 重命名number字段为legacy_number以防有冲突
            if (Schema::hasColumn('units', 'number')) {
                $table->renameColumn('number', 'legacy_number');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('units', function (Blueprint $table) {
            $table->dropConstrainedForeignId('unit_category_id');
            $table->dropColumn(['floor', 'capacity', 'beds', 'is_active', 'enabled', 'unit_number']);
            
            // 如果存在legacy_number，则重命名为number
            if (Schema::hasColumn('units', 'legacy_number')) {
                $table->renameColumn('legacy_number', 'number');
            }
        });
    }
};