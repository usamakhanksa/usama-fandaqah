<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('voucher_entity', function (Blueprint $table) {
            $table->unsignedBigInteger('voucher_id');
            $table->string('entity_type');
            $table->unsignedBigInteger('entity_id');
            
            $table->unique(['voucher_id', 'entity_type', 'entity_id'], 'voucher_entity_unique');
            $table->index(['entity_type', 'entity_id'], 'voucher_entity_type_id_index');
            
            $table->foreign('voucher_id')->references('id')->on('vouchers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('voucher_entity');
    }
};
