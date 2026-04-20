<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        if (!Schema::hasTable('room_floors')) {
            Schema::create('room_floors', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name');
                $table->json('name_translations')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('room_types')) {
            Schema::create('room_types', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name');
                $table->json('name_translations')->nullable();
                $table->decimal('base_price', 14, 2)->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('rooms')) {
            Schema::create('rooms', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('room_floor_id')->nullable();
                $table->unsignedBigInteger('room_type_id')->nullable();
                $table->string('number')->index();
                $table->string('status')->default('available');
                $table->decimal('price_per_day', 14, 2)->default(0);
                $table->timestamps();
                $table->softDeletes();
            });
        }

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::dropIfExists('rooms');
        Schema::dropIfExists('room_types');
        Schema::dropIfExists('room_floors');
    }
};
