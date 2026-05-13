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
        Schema::table('promo_codes', function (Blueprint $table) {
            // Add missing columns that the seeder expects
            if (!Schema::hasColumn('promo_codes', 'discount_percent')) {
                $table->integer('discount_percent')->nullable()->after('code');
            }
            if (!Schema::hasColumn('promo_codes', 'discount_amount')) {
                $table->decimal('discount_amount', 10, 2)->nullable()->after('discount_percent');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('promo_codes', function (Blueprint $table) {
            if (Schema::hasColumn('promo_codes', 'discount_percent')) {
                $table->dropColumn('discount_percent');
            }
            if (Schema::hasColumn('promo_codes', 'discount_amount')) {
                $table->dropColumn('discount_amount');
            }
        });
    }
};
