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
                $table->text('description')->nullable();
            }
            if (!Schema::hasColumn('company_groups', 'discount_rate')) {
                $table->decimal('discount_rate', 5, 2)->default(0.00);
            }
            if (!Schema::hasColumn('company_groups', 'credit_limit')) {
                $table->decimal('credit_limit', 15, 2)->default(0.00);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('company_groups', function (Blueprint $table) {
            $table->dropColumn(['description', 'discount_rate', 'credit_limit']);
        });
    }
};
