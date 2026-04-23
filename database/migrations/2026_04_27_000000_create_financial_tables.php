<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        // 1. Accounts Receivable (AR)
        Schema::create('ar_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('company_name')->unique();
            $table->string('contact_person');
            $table->string('email');
            $table->string('phone');
            $table->decimal('credit_limit', 12, 2)->default(0);
            $table->decimal('current_balance', 12, 2)->default(0);
            $table->enum('status', ['active', 'suspended', 'closed'])->default('active');
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Financial Transactions (Cashiering / AR Postings)
        Schema::create('financial_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('booking_reference')->nullable();
            $table->foreignId('ar_account_id')->nullable()->constrained('ar_accounts')->nullOnDelete();
            $table->enum('type', ['charge', 'payment', 'refund', 'adjustment']);
            $table->decimal('amount', 12, 2);
            $table->string('payment_method')->nullable(); // Cash, Mada, Visa, Amex, CityLedger
            $table->string('description');
            $table->string('receipt_number')->unique();
            $table->date('transaction_date');
            $table->timestamps();
        });

        // 3. Comp Accounting
        Schema::create('comp_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('booking_reference');
            $table->enum('department', ['rooms', 'f_and_b', 'spa', 'transport', 'other']);
            $table->decimal('value_amount', 10, 2);
            $table->string('reason');
            $table->string('approved_by');
            $table->date('date_posted');
            $table->timestamps();
        });

        // 4. End of Day (EOD) Logs
        Schema::create('eod_processes', function (Blueprint $table) {
            $table->id();
            $table->date('audit_date')->unique();
            $table->decimal('total_revenue', 15, 2)->default(0);
            $table->decimal('total_payments', 15, 2)->default(0);
            $table->decimal('total_comps', 12, 2)->default(0);
            $table->enum('status', ['pending', 'completed', 'failed'])->default('pending');
            $table->string('run_by')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('eod_processes');
        Schema::dropIfExists('comp_transactions');
        Schema::dropIfExists('financial_transactions');
        Schema::dropIfExists('ar_accounts');
    }
};
