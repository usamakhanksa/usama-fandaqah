<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Drop existing if they exist to ensure clean state with new schema
        Schema::dropIfExists('invoice_taxes');
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');

        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reservation_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('guest_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            
            $table->string('invoice_number')->index();
            $table->string('zatca_uuid')->nullable()->unique();
            $table->enum('zatca_invoice_type', [
                'standard', 
                'simplified', 
                'simplified_credit_note', 
                'standard_credit_note'
            ])->default('simplified');
            
            $table->datetime('invoice_date')->index();
            $table->date('due_date')->nullable();
            $table->datetime('supply_date')->nullable();
            
            $table->decimal('sub_total', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->string('discount_reason')->nullable();
            $table->decimal('taxable_amount', 12, 2)->default(0);
            $table->decimal('vat_amount', 12, 2)->default(0);
            $table->decimal('vat_percentage', 5, 2)->default(15.00);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->decimal('grand_total', 12, 2)->default(0);
            $table->decimal('rounding_amount', 12, 2)->default(0);
            
            $table->string('currency')->default('SAR');
            $table->decimal('exchange_rate', 10, 4)->default(1.0000);
            
            $table->string('payment_terms')->nullable();
            $table->text('notes')->nullable();
            $table->text('internal_notes')->nullable();
            
            $table->enum('status', [
                'draft', 'sent', 'confirmed', 'paid', 'partially_paid', 'cancelled', 'void'
            ])->default('draft')->index();
            
            $table->boolean('is_zatca_reported')->default(false);
            $table->enum('zatca_status', [
                'not_reported', 'pending', 'reported', 'accepted', 'rejected', 'error'
            ])->default('not_reported')->index();
            
            $table->timestamp('zatca_submitted_at')->nullable();
            $table->json('zatca_response')->nullable();
            $table->longText('zatca_xml')->nullable();
            $table->text('zatca_qr_code')->nullable();
            $table->string('zatca_hash')->nullable();
            $table->string('zatca_previous_hash')->nullable();
            $table->text('zatca_signature')->nullable();
            
            $table->timestamp('cancelled_at')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('cancellation_reason')->nullable();
            
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->unique(['team_id', 'invoice_number']);
            $table->index('company_id');
        });

        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            
            $table->string('product_name');
            $table->string('product_name_ar')->nullable();
            $table->text('description')->nullable();
            
            $table->decimal('quantity', 10, 2)->default(1);
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('sub_total', 12, 2)->default(0);
            
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->string('discount_reason')->nullable();
            
            $table->decimal('taxable_amount', 12, 2)->default(0);
            $table->decimal('vat_amount', 12, 2)->default(0);
            $table->decimal('vat_percentage', 5, 2)->default(15.00);
            $table->decimal('total_amount', 12, 2)->default(0);
            
            $table->enum('item_type', [
                'room_charge', 'fb_charge', 'service_charge', 'other_charge', 'tax', 'fee'
            ])->default('room_charge')->index();
            
            $table->nullableMorphs('reference');
            $table->integer('sort_order')->default(0);
            
            $table->timestamps();
            $table->index('team_id');
        });

        Schema::create('invoice_taxes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            
            $table->enum('tax_type', ['vat', 'municipal_tax', 'tourism_tax', 'other'])->default('vat');
            $table->string('tax_name');
            $table->decimal('tax_percentage', 5, 2);
            $table->decimal('tax_amount', 12, 2);
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_taxes');
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
    }
};
