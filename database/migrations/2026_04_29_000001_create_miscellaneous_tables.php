<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('system_interfaces', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., ZATCA, Shomoos
            $table->string('provider');
            $table->enum('type', ['government', 'payment_gateway', 'door_lock', 'erp', 'other']);
            $table->enum('status', ['connected', 'disconnected', 'degraded', 'maintenance'])->default('disconnected');
            $table->string('api_endpoint')->nullable();
            $table->json('config_keys')->nullable(); // Safely store non-sensitive config structure
            $table->timestamp('last_sync_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('data_exports', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('format', ['csv', 'pdf', 'xml', 'xlsx']);
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->string('file_path')->nullable();
            $table->integer('file_size_kb')->nullable();
            $table->string('requested_by');
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        Schema::create('pms_service_requests', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number')->unique();
            $table->string('title');
            $table->text('description');
            $table->enum('category', ['hardware', 'software', 'network', 'account', 'other']);
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->enum('status', ['open', 'in_progress', 'waiting_on_vendor', 'resolved', 'closed'])->default('open');
            $table->string('reported_by');
            $table->string('assigned_to')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void {
        Schema::dropIfExists('pms_service_requests');
        Schema::dropIfExists('data_exports');
        Schema::dropIfExists('system_interfaces');
    }
};
