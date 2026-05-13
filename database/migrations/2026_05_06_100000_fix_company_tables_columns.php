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
        Schema::table('company_groups', function (Blueprint $table) {
            if (!Schema::hasColumn('company_groups', 'description')) {
                $table->json('description')->nullable()->after('name');
            }
            if (!Schema::hasColumn('company_groups', 'discount_rate')) {
                $table->decimal('discount_rate', 5, 2)->default(0)->after('description');
            }
        });

        Schema::table('company_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('company_profiles', 'entity_type')) {
                $table->string('entity_type')->default('corporate')->after('id');
            }
            if (!Schema::hasColumn('company_profiles', 'is_demo')) {
                $table->boolean('is_demo')->default(false)->after('status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('company_groups', function (Blueprint $table) {
            $table->dropColumn(['description', 'discount_rate']);
        });

        Schema::table('company_profiles', function (Blueprint $table) {
            $table->dropColumn(['entity_type', 'is_demo']);
        });
    }
};
