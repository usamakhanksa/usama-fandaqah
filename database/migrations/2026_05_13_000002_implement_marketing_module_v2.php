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
        // Offers Table
        Schema::dropIfExists('offers');
        Schema::create('offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('name_ar')->nullable();
            $table->text('description')->nullable();
            $table->text('description_ar')->nullable();
            $table->enum('offer_type', ['percentage_discount', 'fixed_discount', 'free_night', 'early_bird', 'last_minute', 'package', 'loyalty']);
            $table->decimal('discount_value', 12, 2);
            $table->decimal('discount_percentage', 5, 2)->nullable();
            $table->json('applicable_room_types')->nullable();
            $table->json('applicable_sources')->nullable();
            $table->integer('min_nights')->default(1);
            $table->integer('max_nights')->nullable();
            $table->integer('min_advance_days')->nullable();
            $table->integer('max_advance_days')->nullable();
            $table->date('valid_from');
            $table->date('valid_to');
            $table->date('booking_window_from')->nullable();
            $table->date('booking_window_to')->nullable();
            $table->boolean('is_stackable')->default(false);
            $table->integer('max_usage')->nullable();
            $table->integer('current_usage')->default(0);
            $table->integer('max_usage_per_guest')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('image_path')->nullable();
            $table->text('terms_conditions')->nullable();
            $table->integer('sort_order')->default(0);
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });

        // Special Prices Table
        Schema::dropIfExists('special_prices');
        Schema::create('special_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('name_ar')->nullable();
            $table->enum('price_type', ['room_rate', 'package_rate', 'seasonal_rate', 'corporate_rate']);
            $table->unsignedBigInteger('room_type_id')->nullable(); // fk unit_categories.id
            $table->decimal('rate_amount', 12, 2);
            $table->enum('rate_type', ['fixed', 'percentage_off', 'amount_off']);
            $table->decimal('base_rate', 12, 2)->nullable();
            $table->enum('meal_plan', ['room_only', 'breakfast', 'half_board', 'full_board', 'all_inclusive'])->default('room_only');
            $table->date('valid_from');
            $table->date('valid_to');
            $table->json('days_of_week')->nullable();
            $table->integer('min_los')->default(1);
            $table->integer('max_los')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('priority')->default(0);
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });

        // Promo Codes Table
        Schema::dropIfExists('promo_codes');
        Schema::create('promo_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->onDelete('cascade');
            $table->string('code')->index();
            $table->string('name');
            $table->string('name_ar')->nullable();
            $table->text('description')->nullable();
            $table->enum('discount_type', ['percentage', 'fixed_amount', 'free_night']);
            $table->decimal('discount_value', 12, 2);
            $table->enum('applicable_to', ['all', 'room_types', 'sources', 'rate_codes'])->default('all');
            $table->json('applicable_ids')->nullable();
            $table->date('valid_from');
            $table->date('valid_to');
            $table->integer('max_usage')->nullable();
            $table->integer('current_usage')->default(0);
            $table->integer('max_usage_per_guest')->nullable();
            $table->decimal('min_booking_amount', 12, 2)->nullable();
            $table->integer('min_nights')->default(1);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['team_id', 'code']);
        });

        // Vouchers Table
        Schema::dropIfExists('vouchers');
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->onDelete('cascade');
            $table->string('code')->index();
            $table->string('name');
            $table->string('name_ar')->nullable();
            $table->enum('voucher_type', ['gift', 'credit', 'service', 'stay', 'dining']);
            $table->decimal('value', 12, 2);
            $table->decimal('initial_value', 12, 2);
            $table->decimal('remaining_value', 12, 2);
            $table->string('currency', 3)->default('SAR');
            $table->boolean('is_percentage')->default(false);
            $table->string('purchaser_name')->nullable();
            $table->string('purchaser_email')->nullable();
            $table->string('recipient_name')->nullable();
            $table->string('recipient_email')->nullable();
            $table->text('message')->nullable();
            $table->date('valid_from');
            $table->date('valid_to');
            $table->enum('status', ['active', 'redeemed', 'partially_redeemed', 'expired', 'cancelled'])->default('active');
            $table->timestamp('redeemed_at')->nullable();
            $table->unsignedBigInteger('redeemed_by')->nullable();
            $table->unsignedBigInteger('reservation_id')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['team_id', 'code']);
        });

        // Voucher Redemptions Table
        Schema::dropIfExists('voucher_redemptions');
        Schema::create('voucher_redemptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('voucher_id')->constrained()->onDelete('cascade');
            $table->foreignId('team_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 12, 2);
            $table->unsignedBigInteger('reservation_id')->nullable();
            $table->foreignId('redeemed_by')->constrained('users')->onDelete('cascade');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('voucher_redemptions');
        Schema::dropIfExists('vouchers');
        Schema::dropIfExists('promo_codes');
        Schema::dropIfExists('special_prices');
        Schema::dropIfExists('offers');
    }
};
