<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Qoyod Sync Logs
        Schema::create('qoyod_sync_logs', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->foreignId('team_id')->constrained()->onDelete('cascade');
            $blueprint->enum('sync_type', ['invoices', 'payments', 'credit_notes', 'accounts', 'full']);
            $blueprint->enum('status', ['pending', 'in_progress', 'completed', 'failed'])->default('pending');
            $blueprint->integer('records_synced')->default(0);
            $blueprint->integer('records_failed')->default(0);
            $blueprint->timestamp('started_at')->nullable();
            $blueprint->timestamp('completed_at')->nullable();
            $blueprint->text('error_message')->nullable();
            $blueprint->json('qoyod_response')->nullable();
            $blueprint->foreignId('triggered_by')->constrained('users');
            $blueprint->timestamps();
        });

        // Paid-outs
        Schema::create('paid_outs', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->foreignId('team_id')->constrained()->onDelete('cascade');
            $blueprint->foreignId('reservation_id')->nullable()->constrained()->onDelete('set null');
            $blueprint->foreignId('guest_id')->nullable()->constrained()->onDelete('set null');
            $blueprint->string('paid_out_number'); // Unique per team handled in code/validation
            $blueprint->date('paid_out_date');
            $blueprint->decimal('amount', 12, 2);
            $blueprint->text('description');
            $blueprint->enum('category', ['taxi', 'laundry', 'postage', 'courier', 'other']);
            $blueprint->string('vendor_name')->nullable();
            $blueprint->string('receipt_number')->nullable();
            $blueprint->enum('payment_method', ['cash', 'card']);
            $blueprint->foreignId('cashier_shift_id')->nullable()->constrained()->onDelete('set null');
            $blueprint->foreignId('transaction_id')->nullable()->constrained()->onDelete('set null');
            $blueprint->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $blueprint->foreignId('approved_by')->nullable()->constrained('users');
            $blueprint->timestamp('approved_at')->nullable();
            $blueprint->foreignId('created_by')->constrained('users');
            $blueprint->timestamps();
            $blueprint->softDeletes();

            $blueprint->unique(['team_id', 'paid_out_number']);
        });

        // Payment Corrections
        Schema::create('payment_corrections', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->foreignId('team_id')->constrained()->onDelete('cascade');
            $blueprint->foreignId('original_payment_id')->constrained('payments');
            $blueprint->enum('correction_type', ['amount_correction', 'method_correction', 'date_correction', 'account_correction', 'full_reversal']);
            $blueprint->json('original_values');
            $blueprint->json('corrected_values');
            $blueprint->text('reason');
            $blueprint->date('correction_date');
            $blueprint->foreignId('reversal_transaction_id')->nullable()->constrained('transactions')->onDelete('set null');
            $blueprint->enum('status', ['pending', 'approved', 'rejected', 'completed'])->default('pending');
            $blueprint->foreignId('approved_by')->nullable()->constrained('users');
            $blueprint->timestamp('approved_at')->nullable();
            $blueprint->foreignId('created_by')->constrained('users');
            $blueprint->timestamps();
            $blueprint->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_corrections');
        Schema::dropIfExists('paid_outs');
        Schema::dropIfExists('qoyod_sync_logs');
    }
};
