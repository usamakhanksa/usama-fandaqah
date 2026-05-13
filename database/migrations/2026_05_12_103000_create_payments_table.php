<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('payments');

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->onDelete('cascade');
            $table->foreignId('reservation_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('folio_id')->nullable(); // (if folios exist)
            $table->foreignId('guest_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('company_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('invoice_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('receipt_id')->nullable()->constrained()->onDelete('set null');
            
            $table->string('payment_number');
            $table->date('payment_date');
            $table->decimal('amount', 12, 2);
            $table->decimal('original_amount', 12, 2)->nullable();
            $table->string('currency')->default('SAR');
            $table->decimal('exchange_rate', 10, 4)->default(1.0000);
            
            $table->enum('payment_method', [
                'cash', 'visa', 'mastercard', 'mada', 'apple_pay', 
                'bank_transfer', 'cheque', 'online', 'other'
            ]);
            
            $table->enum('payment_type', [
                'deposit', 'payment', 'partial_payment', 'advance', 'refund', 'adjustment'
            ]);
            
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
            
            $table->foreignId('cashier_shift_id')->nullable()->constrained()->onDelete('set null');
            
            $table->timestamp('confirmed_at')->nullable();
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->onDelete('set null');
            
            $table->timestamp('cancelled_at')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('cancellation_reason')->nullable();
            
            $table->foreignId('transaction_id')->nullable()->constrained()->onDelete('set null');
            
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            
            $table->timestamps();
            $table->softDeletes();
            
            // Unique payment number per team
            $table->unique(['team_id', 'payment_number']);
            
            // Indexes
            $table->index('team_id');
            $table->index('reservation_id');
            $table->index('guest_id');
            $table->index('payment_number');
            $table->index('payment_date');
            $table->index('status');
            $table->index('cashier_shift_id');
        });

        // Add check constraint for amount > 0
        DB::statement('ALTER TABLE payments ADD CONSTRAINT check_payment_amount_positive CHECK (amount > 0)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
