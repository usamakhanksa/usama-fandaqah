<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop existing if they exist
        Schema::dropIfExists('invoice_transfer_items');
        Schema::dropIfExists('invoice_transfers');

        Schema::create('invoice_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            
            $table->foreignId('from_invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->foreignId('to_invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
            
            $table->foreignId('from_guest_id')->nullable()->constrained('guests')->nullOnDelete();
            $table->foreignId('to_guest_id')->nullable()->constrained('guests')->nullOnDelete();
            
            $table->foreignId('from_company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->foreignId('to_company_id')->nullable()->constrained('companies')->nullOnDelete();
            
            $table->foreignId('from_reservation_id')->nullable()->constrained('reservations')->nullOnDelete();
            $table->foreignId('to_reservation_id')->nullable()->constrained('reservations')->nullOnDelete();
            
            $table->string('transfer_number')->index();
            $table->date('transfer_date')->index();
            
            $table->decimal('amount', 12, 2);
            $table->decimal('vat_amount', 12, 2);
            $table->decimal('total_amount', 12, 2);
            
            $table->text('reason');
            $table->enum('transfer_type', ['full', 'partial', 'item_level'])->default('item_level');
            
            $table->enum('status', ['pending', 'approved', 'rejected', 'completed', 'reversed'])->default('pending')->index();
            
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            
            $table->foreignId('rejected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();
            
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('reversed_at')->nullable();
            
            $table->foreignId('created_by')->constrained('users');
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->unique(['team_id', 'transfer_number']);
            $table->index('from_invoice_id');
            $table->index('to_invoice_id');
        });

        Schema::create('invoice_transfer_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_transfer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('from_invoice_item_id')->constrained('invoice_items')->cascadeOnDelete();
            $table->foreignId('to_invoice_item_id')->nullable()->constrained('invoice_items')->nullOnDelete();
            $table->decimal('amount', 12, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_transfer_items');
        Schema::dropIfExists('invoice_transfers');
    }
};
