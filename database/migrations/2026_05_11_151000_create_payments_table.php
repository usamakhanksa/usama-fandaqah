<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Schema::dropIfExists('payments');
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reservation_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('guest_id')->nullable()->constrained('guests')->nullOnDelete();
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('receipt_id')->nullable()->constrained()->nullOnDelete();
            $table->string('payment_number');
            $table->date('payment_date');
            $table->decimal('amount', 12, 2);
            $table->decimal('original_amount', 12, 2)->nullable();
            $table->string('currency', 3)->default('SAR');
            $table->decimal('exchange_rate', 10, 4)->default(1.0000);
            $table->enum('payment_method', ['cash', 'visa', 'mastercard', 'mada', 'apple_pay', 'bank_transfer', 'cheque', 'online', 'other']);
            $table->enum('payment_type', ['deposit', 'payment', 'partial_payment', 'advance', 'refund', 'adjustment']);
            $table->string('reference_number')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('cheque_number')->nullable();
            $table->string('card_last_four', 4)->nullable();
            $table->string('card_authorization')->nullable();
            $table->text('description')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'confirmed', 'cancelled', 'refunded', 'reversed'])->default('pending');
            $table->boolean('is_advance')->default(false);
            $table->boolean('is_deposit')->default(false);
            $table->foreignId('cashier_shift_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('confirmed_at')->nullable();
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('cancelled_at')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('cancellation_reason')->nullable();
            $table->foreignId('transaction_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('team_id');
            $table->index('reservation_id');
            $table->index('guest_id');
            $table->index('payment_number');
            $table->index('payment_date');
            $table->index('status');
            $table->index('payment_method');
            $table->index('payment_type');
            $table->index('cashier_shift_id');
            $table->index('transaction_id');

            $table->unique(['team_id', 'payment_number']);
        });
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
