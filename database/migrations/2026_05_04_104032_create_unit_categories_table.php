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
        Schema::create('unit_categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('team_id')->nullable()->index();
            $table->json('name')->nullable();
            $table->boolean('enable_price_include_tax')->default(false);
            
            // Financial fields - converted from double to decimal
            $table->decimal('sunday_day_price', 12, 2)->default(0);
            $table->decimal('monday_day_price', 12, 2)->default(0);
            $table->decimal('tuesday_day_price', 12, 2)->default(0);
            $table->decimal('wednesday_day_price', 12, 2)->default(0);
            $table->decimal('thursday_day_price', 12, 2)->default(0);
            $table->decimal('friday_day_price', 12, 2)->default(0);
            $table->decimal('saturday_day_price', 12, 2)->default(0);
            
            $table->text('general_features')->nullable();
            $table->text('special_features')->nullable();
            $table->string('main_image')->nullable();
            $table->json('description')->nullable();
            $table->json('short_description')->nullable();
            $table->string('youtube_link')->nullable();
            $table->unsignedBigInteger('status')->default(0);
            $table->unsignedBigInteger('order')->default(0);
            $table->unsignedBigInteger('type_id')->nullable();
            
            $table->decimal('month_price', 12, 2)->nullable();
            $table->decimal('hour_price', 12, 2)->nullable();
            
            $table->decimal('min_sunday_day_price', 12, 2)->default(0);
            $table->decimal('min_monday_day_price', 12, 2)->default(0);
            $table->decimal('min_tuesday_day_price', 12, 2)->default(0);
            $table->decimal('min_wednesday_day_price', 12, 2)->default(0);
            $table->decimal('min_thursday_day_price', 12, 2)->default(0);
            $table->decimal('min_friday_day_price', 12, 2)->default(0);
            $table->decimal('min_saturday_day_price', 12, 2)->default(0);
            $table->decimal('min_month_price', 12, 2)->default(0);
            
            $table->tinyInteger('show_in_website')->default(1)->index();
            $table->decimal('unit_size', 12, 2)->nullable();
            $table->string('number_of_adults')->default('1');
            $table->unsignedBigInteger('number_of_children')->default(0);
            $table->unsignedBigInteger('number_of_beds')->default(0);
            $table->boolean('enable_staah_pricing')->default(false);
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['team_id', 'order', 'type_id', 'status'], 'team_order_type_status_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unit_categories');
    }
};
