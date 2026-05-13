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
        if (Schema::hasTable('checkout_balance_transfers')) {
            return;
        }
        Schema::create('checkout_balance_transfers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('team_id')->index();
            $table->unsignedBigInteger('reservation_id')->index();
            $table->decimal('balance_before', 15, 2);
            $table->decimal('amount_resolved', 15, 2);
            $table->decimal('balance_after', 15, 2);
            $table->string('resolution_type'); // collect_now, signed_promissory, unsigned_promissory, corporate_transfer, refund_now, credit_note
            $table->unsignedBigInteger('reference_id')->nullable(); 
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('checkout_balance_transfers');
    }
};
