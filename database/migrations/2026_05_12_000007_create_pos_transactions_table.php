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
        Schema::create('pos_transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('team_id');
            $table->string('transaction_number');
            $table->unsignedBigInteger('reservation_id')->nullable();
            $table->unsignedBigInteger('guest_id')->nullable();
            $table->unsignedBigInteger('room_id')->nullable();
            $table->decimal('subtotal', 12, 2);
            $table->decimal('vat_amount', 12, 2);
            $table->decimal('total_amount', 12, 2);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->string('discount_reason')->nullable();
            $table->decimal('service_charge', 12, 2)->default(0);
            $table->enum('payment_method', ['cash', 'card', 'room_charge', 'complimentary']);
            $table->enum('payment_status', ['unpaid', 'paid', 'partially_paid', 'voided']);
            $table->unsignedBigInteger('cashier_shift_id')->nullable();
            $table->enum('transaction_type', ['sale', 'refund', 'void']);
            $table->unsignedBigInteger('parent_transaction_id')->nullable();
            $table->timestamp('voided_at')->nullable();
            $table->unsignedBigInteger('voided_by')->nullable();
            $table->text('void_reason')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('team_id')->references('id')->on('teams')->onDelete('cascade');
            $table->foreign('reservation_id')->references('id')->on('reservations')->onDelete('set null');
            $table->foreign('guest_id')->references('id')->on('guests')->onDelete('set null');
            $table->foreign('room_id')->references('id')->on('units')->onDelete('set null');
            $table->foreign('cashier_shift_id')->references('id')->on('cashier_shifts')->onDelete('set null');
            $table->foreign('parent_transaction_id')->references('id')->on('pos_transactions')->onDelete('set null');
            $table->foreign('voided_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');

            $table->unique(['team_id', 'transaction_number'], 'pos_transactions_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pos_transactions');
    }
};
?>
