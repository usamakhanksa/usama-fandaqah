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
        Schema::create('sidebar_items', function (Blueprint $table) {
            $table->id();
            $table->string('item_key')->unique()->index();
            $table->string('label_en');
            $table->string('label_ar');
            $table->string('icon')->nullable();
            $table->string('route_name')->nullable();
            $table->string('url')->nullable();
            $table->string('permission')->nullable();
            $table->string('module')->index();
            $table->string('parent_key')->nullable()->index();
            $table->integer('order')->default(0);
            $table->text('badge_count_query')->nullable();
            $table->json('active_route_patterns')->nullable();
            $table->boolean('is_visible')->default(true);
            $table->boolean('is_beta')->default(false);
            $table->boolean('is_external')->default(false);
            $table->enum('device_visibility', ['all', 'desktop', 'tablet', 'mobile'])->default('all');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sidebar_items');
    }
};
