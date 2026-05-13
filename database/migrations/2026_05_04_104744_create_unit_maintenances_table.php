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
        Schema::create('unit_maintenances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('unit_id')->index();
            $table->unsignedBigInteger('created_by')->index();
            $table->datetime('start_at')->index();
            $table->datetime('completed_at')->nullable()->index();
            $table->text('note')->nullable();
            $table->unsignedBigInteger('team_id')->index();
            $table->unsignedBigInteger('completed_by')->nullable()->index();
            $table->unsignedBigInteger('action_id')->nullable()->index();
            $table->timestamp('expected_at')->nullable();
            $table->timestamps();
            
            $table->foreign('unit_id')->references('id')->on('units')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('completed_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('team_id')->references('id')->on('teams')->onDelete('cascade');
            $table->foreign('action_id')->references('id')->on('action_types')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unit_maintenances');
    }
};
