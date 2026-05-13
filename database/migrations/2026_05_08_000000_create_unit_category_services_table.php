<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unit_category_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('unit_category_id');
            $table->unsignedBigInteger('service_id');
            $table->boolean('is_included')->default(false);
            $table->decimal('price_override', 10, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['unit_category_id', 'service_id']);
            $table->index(['team_id', 'unit_category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unit_category_services');
    }
};
