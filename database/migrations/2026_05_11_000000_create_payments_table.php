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
            $table->bigIncrements('id');

            $table->unsignedBigInteger('team_id');
            $table->unsignedBigInteger('reservation_id')->nullable();
            $table->unsignedBigInteger('folio_id')->nullable();
            $table->unsignedBigInteger('guest_id')->nullable();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('invoice_id')->nullable();
            $table->unsignedBigInteger('receipt_id')->nullable();

            $table->string('payment_number');
            $table->date('payment_date');
            $table->decimal('amount', 12, 2);
            $table->decimal('original_amount', 12, 2)->nullable();
            $table->string('currency')->default('SAR');
            $table->decimal('exchange_rate', 10, 4)->default(1.0000);

            $table->string('payment_method');
            $table->string('payment_type');

            $table->string('reference_number')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('cheque_number')->nullable();
            $table->string('card_last_four')->nullable();
            $table->string('card_authorization')->nullable();

            $table->text('description')->nullable();
            $table->text('notes')->nullable();

            $table->string('status');

            $table->boolean('is_advance')->default(false);
            $table->boolean('is_deposit')->default(false);

            $table->unsignedBigInteger('cashier_shift_id')->nullable();

            $table->timestamp('confirmed_at')->nullable();
            $table->unsignedBigInteger('confirmed_by')->nullable();

            $table->timestamp('cancelled_at')->nullable();
            $table->unsignedBigInteger('cancelled_by')->nullable();

            $table->text('cancellation_reason')->nullable();

            $table->unsignedBigInteger('transaction_id')->nullable();

            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->softDeletes();
            $table->timestamps();

            // Indexes
            $table->index(['team_id']);
            $table->index(['reservation_id']);
            $table->index(['guest_id']);
            $table->index(['payment_number']);
            $table->index(['payment_date']);
            $table->index(['status']);
            $table->index(['cashier_shift_id']);

            $table->unique(['team_id', 'payment_number']);

            // Enums-like constraints (string columns)
            // Laravel/Mysql does not support CHECK for older versions reliably; we still add when supported.

            $table->foreign('team_id')->references('id')->on('teams')->onDelete('cascade');

            $table->foreign('reservation_id')->references('id')->on('reservations')->nullOnDelete();
            $table->foreign('guest_id')->references('id')->on('guests')->nullOnDelete();
            $table->foreign('company_id')->references('id')->on('companies')->nullOnDelete();
            $table->foreign('invoice_id')->references('id')->on('invoices')->nullOnDelete();
            $table->foreign('receipt_id')->references('id')->on('receipts')->nullOnDelete();

            $table->foreign('cashier_shift_id')->references('id')->on('cashier_shifts')->nullOnDelete();

            $table->foreign('confirmed_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('cancelled_by')->references('id')->on('users')->nullOnDelete();

            // Ledger linkage: RESTRICT (no cascade) per confirmed requirement
            $table->foreign('transaction_id')->references('id')->on('transactions')->nullOnDelete();

            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
