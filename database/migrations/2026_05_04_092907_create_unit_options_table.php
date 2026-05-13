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
        Schema::create('unit_options', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('team_id');
            $table->json('name'); // For multilingual support
            $table->decimal('price', 10, 2)->default(0);
            $table->json('description')->nullable(); // For multilingual support
            $table->boolean('active')->default(true);
            $table->timestamps();
            
            $table->foreign('team_id')->references('id')->on('teams')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unit_options');
    }
};
