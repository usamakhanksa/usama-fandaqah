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
        Schema::create('group_reservations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('team_id')->index(); // Added for multi-tenancy
            $table->unsignedBigInteger('company_id')->nullable()->index();
            $table->decimal('balance', 14, 2)->nullable(); // Converted from double
            $table->json('data');
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('team_id')->references('id')->on('teams')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('group_reservations');
    }
};
