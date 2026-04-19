<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('room_floors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedTinyInteger('level')->unique();
            $table->timestamps();
        });

        Schema::table('rooms', function (Blueprint $table) {
            $table->string('name')->nullable()->after('number');
            $table->foreignId('room_floor_id')->nullable()->after('room_type_id')->constrained('room_floors');
            $table->decimal('price_per_day', 10, 2)->default(0)->after('floor');
            $table->string('gender')->default('all')->after('status');
            $table->string('thumbnail')->nullable()->after('gender');
        });

        Schema::create('room_metrics', function (Blueprint $table) {
            $table->id();
            $table->date('metric_date');
            $table->unsignedInteger('total_rooms');
            $table->unsignedInteger('available_rooms');
            $table->unsignedInteger('not_ready_rooms');
            $table->unsignedInteger('booked_rooms');
            $table->timestamps();
        });

        Schema::create('occupancy_metrics', function (Blueprint $table) {
            $table->id();
            $table->date('metric_date');
            $table->unsignedInteger('unit_occupancy');
            $table->unsignedInteger('total_guests');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('occupancy_metrics');
        Schema::dropIfExists('room_metrics');

        Schema::table('rooms', function (Blueprint $table) {
            $table->dropConstrainedForeignId('room_floor_id');
            $table->dropColumn(['name', 'price_per_day', 'gender', 'thumbnail']);
        });

        Schema::dropIfExists('room_floors');
    }
};
