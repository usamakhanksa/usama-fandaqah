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
        Schema::table('promissory_payment_log', function (Blueprint $table) {
            if (Schema::hasColumn('promissory_payment_log', 'amount')) {
                $table->renameColumn('amount', 'amount_applied');
            }
            if (Schema::hasColumn('promissory_payment_log', 'payment_method')) {
                $table->renameColumn('payment_method', 'payment_type');
            }
            if (!Schema::hasColumn('promissory_payment_log', 'applied_at')) {
                $table->timestamp('applied_at')->nullable();
            }
            if (!Schema::hasColumn('promissory_payment_log', 'applied_by')) {
                $table->unsignedBigInteger('applied_by')->nullable();
            }
            if (!Schema::hasColumn('promissory_payment_log', 'is_reversed')) {
                $table->boolean('is_reversed')->default(false);
            }
            if (!Schema::hasColumn('promissory_payment_log', 'notes')) {
                $table->text('notes')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('promissory_payment_log', function (Blueprint $table) {
            // ...
        });
    }
};
