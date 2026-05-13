<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            
            // Core fields
            $table->unsignedBigInteger('invoice_id');
            $table->unsignedBigInteger('team_id');
            
            // Item details
            $table->string('description');
            $table->decimal('quantity', 10, 2)->default(1);
            $table->decimal('unit_price', 12, 2);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(15.00);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('total_price', 12, 2)->default(0);
            
            // Classification
            $table->enum('item_type', ['room_charge', 'food_beverage', 'service', 'tax', 'fee', 'other'])->default('room_charge');
            
            // Polymorphic reference
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            
            // Sorting and metadata
            $table->integer('sort_order')->default(0);
            $table->json('metadata')->nullable();
            
            // Timestamps
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('invoice_id')->references('id')->on('invoices')->cascadeOnDelete();
            $table->foreign('team_id')->references('id')->on('teams')->cascadeOnDelete();
            
            // Indexes
            $table->index('invoice_id');
            $table->index('team_id');
            $table->index('item_type');
            $table->index(['reference_type', 'reference_id']);
            $table->index('sort_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
    }
};
