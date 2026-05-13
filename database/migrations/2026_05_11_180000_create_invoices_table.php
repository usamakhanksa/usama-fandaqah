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
        Schema::dropIfExists('invoices');
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            
            // Core fields
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->string('invoice_number');
            $table->enum('invoice_type', ['standard', 'simplified', 'debit_note', 'credit_note'])->default('standard');
            $table->string('invoice_type_code')->default('388'); 
            $table->foreignId('reservation_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('guest_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('folio_id')->nullable();
            
            // Date fields
            $table->date('invoice_date');
            $table->time('invoice_time')->nullable();
            $table->date('due_date')->nullable();
            
            // Financial fields
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->enum('discount_type', ['percentage', 'fixed'])->nullable();
            $table->decimal('discount_rate', 5, 2)->nullable();
            $table->decimal('tax_amount', 12, 2)->default(0); 
            $table->decimal('tax_rate', 5, 2)->default(15.00);
            $table->decimal('other_charges', 12, 2)->default(0); 
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->decimal('amount_paid', 12, 2)->default(0);
            $table->decimal('amount_due', 12, 2)->default(0);
            $table->string('currency')->default('SAR');
            
            // Status fields
            $table->enum('status', ['draft', 'sent', 'paid', 'partially_paid', 'voided', 'cancelled'])->default('draft');
            
            // ZATCA fields
            $table->boolean('is_zatca_reported')->default(false);
            $table->uuid('zatca_uuid')->nullable(); 
            $table->string('zatca_hash')->nullable(); 
            $table->text('zatca_qr_code')->nullable(); 
            $table->longText('zatca_xml')->nullable(); 
            $table->enum('zatca_status', ['not_sent', 'pending', 'accepted', 'rejected', 'cancelled'])->default('not_sent');
            $table->timestamp('zatca_sent_at')->nullable();
            $table->timestamp('zatca_responded_at')->nullable();
            $table->text('zatca_rejection_reason')->nullable();
            $table->string('zatca_clearance_number')->nullable();
            
            // Payment and billing fields
            $table->string('payment_method')->nullable();
            $table->string('billing_name')->nullable();
            $table->text('billing_address')->nullable();
            $table->string('billing_city')->nullable();
            $table->string('billing_state')->nullable();
            $table->string('billing_postal_code')->nullable();
            $table->string('billing_country')->default('SA');
            $table->string('billing_vat_number')->nullable(); 
            $table->string('billing_cr_number')->nullable(); 
            $table->string('billing_email')->nullable();
            $table->string('billing_phone')->nullable();
            
            // Additional fields
            $table->text('notes')->nullable();
            $table->text('internal_notes')->nullable();
            $table->text('terms')->nullable();
            $table->json('metadata')->nullable();
            
            // Audit fields
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('team_id');
            $table->index(['team_id', 'invoice_number']);
            $table->index('reservation_id');
            $table->index('guest_id');
            $table->index('company_id');
            $table->index('invoice_date');
            $table->index('status');
            $table->index('zatca_status');
            $table->index('due_date');
        });
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
