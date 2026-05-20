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
        Schema::dropIfExists('integration_settings');
        Schema::create('integration_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('integration_id');
            $table->unsignedBigInteger('team_id');
            $table->string('setting_key');
            $table->text('setting_value')->nullable();
            $table->enum('setting_type', ['text', 'number', 'boolean', 'json', 'encrypted', 'select']);
            $table->json('options')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_required')->default(false);
            $table->timestamps();

            $table->unique(['integration_id', 'setting_key']);
            $table->foreign('integration_id')->references('id')->on('integrations')->onDelete('cascade');
            $table->foreign('team_id')->references('id')->on('teams')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('integration_settings');
    }
};
