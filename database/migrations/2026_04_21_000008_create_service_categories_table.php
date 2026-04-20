<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        if (!Schema::hasTable('service_categories')) {
            Schema::create('service_categories', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->json('name')->comment('Bilingual: {en: "", ar: ""}');
                $table->boolean('status')->default(true);
                $table->boolean('show_in_reservation')->default(true);
                $table->boolean('show_in_pos')->default(true);
                $table->integer('display_order')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('service_category_user')) {
            Schema::create('service_category_user', function (Blueprint $table) {
                $table->unsignedBigInteger('service_category_id');
                $table->unsignedBigInteger('user_id');
                $table->primary(['service_category_id', 'user_id']);
            });
        }

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::dropIfExists('service_category_user');
        Schema::dropIfExists('service_categories');
    }
};
