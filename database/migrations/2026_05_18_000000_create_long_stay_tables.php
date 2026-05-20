<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Buildings table
        if (!Schema::hasTable('buildings')) {
            Schema::create('buildings', function (Blueprint $table) {
                $table->id();
                $table->foreignId('team_id')->constrained()->onDelete('cascade');
                $table->string('name_en');
                $table->string('name_ar')->nullable();
                $table->string('address')->nullable();
                $table->unsignedInteger('total_floors')->default(1);
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // Long-stay contracts
        if (!Schema::hasTable('long_stay_contracts')) {
            Schema::create('long_stay_contracts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('team_id')->constrained()->onDelete('cascade');
                $table->foreignId('unit_id')->constrained()->onDelete('cascade');
                $table->foreignId('customer_id')->constrained()->onDelete('cascade');
                $table->date('start_date');
                $table->date('end_date');
                $table->enum('billing_cycle', ['weekly', 'monthly', 'quarterly', 'yearly'])->default('monthly');
                $table->decimal('amount', 15, 2);
                $table->decimal('security_deposit', 15, 2)->default(0);
                $table->string('status')->default('active'); // active, terminated, completed, renewed
                $table->text('terms')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // Utility meters
        if (!Schema::hasTable('utility_meters')) {
            Schema::create('utility_meters', function (Blueprint $table) {
                $table->id();
                $table->foreignId('team_id')->constrained()->onDelete('cascade');
                $table->foreignId('unit_id')->constrained()->onDelete('cascade');
                $table->string('type'); // electricity, water, gas, internet
                $table->string('meter_number')->unique();
                $table->string('initial_reading')->nullable();
                $table->timestamps();
            });
        }

        // Utility readings
        if (!Schema::hasTable('utility_readings')) {
            Schema::create('utility_readings', function (Blueprint $table) {
                $table->id();
                $table->foreignId('meter_id')->constrained('utility_meters')->onDelete('cascade');
                $table->date('reading_date');
                $table->decimal('reading_value', 15, 2);
                $table->string('image_path')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users');
                $table->timestamps();
            });
        }

        // Unit inventory
        if (!Schema::hasTable('unit_inventories')) {
            Schema::create('unit_inventories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('team_id')->constrained()->onDelete('cascade');
                $table->foreignId('unit_id')->constrained()->onDelete('cascade');
                $table->string('item_name');
                $table->unsignedInteger('quantity')->default(1);
                $table->string('condition')->nullable(); // new, good, damaged
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('unit_inventories');
        Schema::dropIfExists('utility_readings');
        Schema::dropIfExists('utility_meters');
        Schema::dropIfExists('long_stay_contracts');
        Schema::dropIfExists('buildings');
    }
};
