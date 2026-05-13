<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('credit_note_items');
        Schema::dropIfExists('credit_notes');

        Schema::create('credit_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reservation_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('guest_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            
            $table->string('credit_note_number')->index();
            $table->string('zatca_uuid')->nullable()->unique();
            $table->date('credit_note_date')->index();
            
            $table->enum('reason', ['cancellation', 'correction', 'discount', 'partial_refund', 'other'])->default('cancellation');
            $table->text('reason_description')->nullable();
            
            $table->decimal('sub_total', 12, 2);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('taxable_amount', 12, 2);
            $table->decimal('vat_amount', 12, 2);
            $table->decimal('vat_percentage', 5, 2)->default(15.00);
            $table->decimal('total_amount', 12, 2);
            
            $table->string('currency', 3)->default('SAR');
            $table->text('notes')->nullable();
            
            $table->enum('status', ['draft', 'confirmed', 'cancelled'])->default('draft')->index();
            
            $table->boolean('is_zatca_reported')->default(false);
            $table->enum('zatca_status', ['not_reported', 'pending', 'reported', 'accepted', 'rejected', 'error'])->default('not_reported')->index();
            $table->timestamp('zatca_submitted_at')->nullable();
            $table->json('zatca_response')->nullable();
            $table->longText('zatca_xml')->nullable();
            $table->text('zatca_qr_code')->nullable();
            
            $table->foreignId('transaction_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->unique(['team_id', 'credit_note_number']);
        });

        Schema::create('credit_note_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('credit_note_id')->constrained()->cascadeOnDelete();
            $table->foreignId('original_item_id')->nullable()->constrained('invoice_items')->nullOnDelete();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            
            $table->string('product_name');
            $table->string('product_name_ar')->nullable();
            $table->text('description')->nullable();
            
            $table->decimal('quantity', 10, 2)->default(1);
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('sub_total', 12, 2)->default(0);
            
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('taxable_amount', 12, 2)->default(0);
            $table->decimal('vat_amount', 12, 2)->default(0);
            $table->decimal('vat_percentage', 5, 2)->default(15.00);
            $table->decimal('total_amount', 12, 2)->default(0);
            
            $table->enum('item_type', [
                'room_charge', 'fb_charge', 'service_charge', 'other_charge', 'tax', 'fee'
            ])->default('room_charge');
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credit_note_items');
        Schema::dropIfExists('credit_notes');
    }
};
