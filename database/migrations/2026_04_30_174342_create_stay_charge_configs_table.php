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
        Schema::create('stay_charge_configs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('team_id')->index();
            $table->enum('charge_type', ['early_checkin', 'late_checkout']);
            $table->time('tier_from_hour');
            $table->time('tier_to_hour');
            $table->enum('rate_type', ['fixed', 'percentage_first_night', 'percentage_nightly_rate']);
            $table->decimal('rate_amount', 15, 2);
            $table->string('applies_to')->default('all'); // Can be 'all' or comma-separated unit type IDs
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Note: Overlap check will be handled in application logic (Validator/Service)
        });

        Schema::create('stay_charge_overrides', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('team_id')->index();
            $table->unsignedBigInteger('reservation_id')->index();
            $table->enum('charge_type', ['early_checkin', 'late_checkout']);
            $table->decimal('original_amount', 15, 2);
            $table->decimal('overridden_amount', 15, 2);
            $table->text('reason');
            $table->unsignedBigInteger('user_id'); // Who did the override
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stay_charge_configs');
        Schema::dropIfExists('stay_charge_overrides');
    }
};
