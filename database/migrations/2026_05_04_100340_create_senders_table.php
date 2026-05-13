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
        if (!Schema::hasTable('senders')) {
            Schema::create('senders', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('team_id');
                $table->json('name'); // For multilingual support
                $table->string('type')->nullable(); // local, international, internal, corporate, agency, ota
                $table->boolean('active')->default(true);
                $table->timestamps();
                
                $table->foreign('team_id')->references('id')->on('teams')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('senders');
    }
};
