<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('payments');
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            
            // Core fields
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->string('payment_number');
            $table->foreignId('reservation_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('folio_id')->nullable();
            $table->foreignId('guest_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('transaction_id')->nullable();
            
            // Financial fields
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('currency', 3)->default('SAR');
            $table->decimal('exchange_rate', 10, 4)->default(1.0000);
            $table->decimal('amount_base', 12, 2)->default(0);
            $table->date('payment_date')->nullable();
            
            // Payment method fields
            $table->string('payment_method', 50);
            $table->string('payment_reference')->nullable();
            $table->string('card_type', 50)->nullable();
            $table->string('card_last_four', 4)->nullable();
            $table->string('card_holder_name', 100)->nullable();
            $table->date('check_date')->nullable();
            $table->string('check_number', 50)->nullable();
            $table->unsignedBigInteger('bank_id')->nullable();
            $table->unsignedBigInteger('sender_id')->nullable();
            
            // Status and workflow fields
            $table->string('status', 20)->default('pending');
            $table->unsignedBigInteger('reversal_id')->nullable();
            $table->timestamp('reversed_at')->nullable();
            $table->unsignedBigInteger('reversed_by')->nullable();
            $table->string('reversal_reason', 500)->nullable();
            
            // Commission fields
            $table->decimal('commission_amount', 12, 2)->default(0);
            $table->decimal('commission_rate',5, 2)->default(0);
            $table->unsignedBigInteger('travel_agent_id')->nullable();
            
            // Extra fields for indexes/foreign keys in original broken file
            $table->unsignedBigInteger('shift_id')->nullable();
            $table->unsignedBigInteger('cashier_id')->nullable();

            // Metadata and audit fields
            $table->json('metadata')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('team_id');
            $table->index('payment_number');
            $table->index('reservation_id');
            $table->index('guest_id');
            $table->index('status');
            $table->index('payment_method');
            
            // Composite unique
            $table->unique(['team_id', 'payment_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
