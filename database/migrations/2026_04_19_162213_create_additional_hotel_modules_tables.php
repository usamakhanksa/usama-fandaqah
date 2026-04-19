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
        Schema::create('channels', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('channel_rate_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('channel_id')->constrained();
            $table->string('name');
            $table->string('room_type_id')->nullable();
            $table->timestamps();
        });

        Schema::create('channel_reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('channel_id')->constrained();
            $table->string('booking_id')->unique();
            $table->string('guest_name');
            $table->date('check_in');
            $table->date('check_out');
            $table->decimal('amount', 10, 2);
            $table->string('status');
            $table->boolean('is_posted')->default(false);
            $table->string('unit_number')->nullable();
            $table->timestamps();
        });

        Schema::create('ledger_numbers', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('group')->nullable();
            $table->timestamps();
        });

        Schema::create('maintenance_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('maintenance_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->nullable()->constrained();
            $table->foreignId('maintenance_category_id')->constrained();
            $table->string('subject');
            $table->text('description');
            $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled'])->default('pending');
            $table->timestamps();
        });

        Schema::create('housekeeping_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained();
            $table->string('task_type')->default('cleaning');
            $table->enum('status', ['pending', 'in_progress', 'completed'])->default('pending');
            $table->timestamps();
        });

        Schema::create('hotel_amenities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('icon')->nullable();
            $table->boolean('show_on_website')->default(true);
            $table->timestamps();
        });

        Schema::create('customer_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('discount_percentage', 5, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('reservation_resources', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservation_resources');
        Schema::dropIfExists('customer_groups');
        Schema::dropIfExists('hotel_amenities');
        Schema::dropIfExists('housekeeping_tasks');
        Schema::dropIfExists('maintenance_tickets');
        Schema::dropIfExists('maintenance_categories');
        Schema::dropIfExists('ledger_numbers');
        Schema::dropIfExists('channel_reservations');
        Schema::dropIfExists('channel_rate_plans');
        Schema::dropIfExists('channels');
    }
};
