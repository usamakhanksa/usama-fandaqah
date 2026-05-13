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
        // Drop and recreate offers table
        Schema::dropIfExists('offers');
        Schema::create('offers', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('team_id')->index();
                $table->string('name_en');
                $table->string('name_ar')->nullable();
                $table->text('description_en')->nullable();
                $table->text('description_ar')->nullable();
                $table->decimal('discount_percent', 5, 2)->nullable();
                $table->decimal('discount_amount', 10, 2)->nullable();
                $table->date('start_date')->nullable();
                $table->date('end_date')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->softDeletes();
                
                $table->foreign('team_id')->references('id')->on('teams')->onDelete('cascade');
            });

        // Promo codes table
        Schema::dropIfExists('promo_codes');
        Schema::create('promo_codes', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('team_id')->index();
                $table->string('code')->index();
                $table->decimal('discount_percent', 5, 2)->nullable();
                $table->decimal('discount_amount', 10, 2)->nullable();
                $table->integer('max_uses')->nullable();
                $table->integer('uses_count')->default(0);
                $table->date('start_date')->nullable();
                $table->date('end_date')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->softDeletes();
                
                $table->unique(['team_id', 'code']);
                $table->foreign('team_id')->references('id')->on('teams')->onDelete('cascade');
            });

        // Special prices table
        Schema::dropIfExists('special_prices');
        Schema::create('special_prices', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('team_id')->index();
                $table->unsignedBigInteger('unit_category_id')->nullable()->index();
                $table->date('date')->nullable();
                $table->decimal('price', 10, 2)->nullable();
                $table->text('note')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                
                $table->foreign('team_id')->references('id')->on('teams')->onDelete('cascade');
                $table->foreign('unit_category_id')->references('id')->on('unit_categories')->onDelete('cascade');
            });

        // Vouchers table
        Schema::dropIfExists('voucher_entity');
        Schema::dropIfExists('vouchers');
        Schema::create('vouchers', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('team_id')->index();
                $table->string('code')->unique();
                $table->decimal('value', 10, 2);
                $table->enum('type', ['fixed', 'percentage'])->default('fixed');
                $table->date('expiry_date')->nullable();
                $table->boolean('is_used')->default(false);
                $table->unsignedBigInteger('used_by')->nullable();
                $table->timestamp('used_at')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                
                $table->foreign('team_id')->references('id')->on('teams')->onDelete('cascade');
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vouchers');
        Schema::dropIfExists('special_prices');
        Schema::dropIfExists('promo_codes');
        Schema::dropIfExists('offers');
    }
};
