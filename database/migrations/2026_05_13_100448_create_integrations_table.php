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
        Schema::create('integrations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('team_id')->index();
            $table->string('name');
            $table->string('name_ar')->nullable();
            $table->string('slug')->unique();
            $table->enum('integration_type', ['channel_manager', 'accounting', 'government', 'payment_gateway', 'crm', 'pos', 'pms', 'other']);
            $table->enum('provider', ['shomoos', 'zatca', 'qoyod', 'stripe', 'tabby', 'tamara', 'site_minder', 'dhisco', 'oracle', 'custom']);
            $table->string('base_url')->nullable();
            $table->string('api_key')->nullable();
            $table->string('api_secret')->nullable();
            $table->json('config')->nullable();
            $table->boolean('is_active')->default(false);
            $table->timestamp('last_sync_at')->nullable();
            $table->enum('last_sync_status', ['success', 'failed', 'partial'])->nullable();
            $table->text('last_error')->nullable();
            $table->enum('sync_frequency', ['real_time', 'hourly', 'daily', 'manual'])->default('manual');
            $table->enum('status', ['pending_setup', 'testing', 'active', 'suspended', 'error']);
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('team_id')->references('id')->on('teams')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['team_id', 'slug']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('integrations');
    }
};
