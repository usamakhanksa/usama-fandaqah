<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('guest_checkins', function (Blueprint $table) {
            $table->id();
            $table->string('booking_reference')->unique();
            $table->string('guest_name');
            $table->string('room_number')->nullable();
            $table->date('expected_arrival_date');
            $table->date('expected_departure_date');
            $table->dateTime('actual_arrival_time')->nullable();
            $table->dateTime('actual_departure_time')->nullable();
            $table->enum('status', ['arrival', 'in_house', 'departure', 'checked_out'])->default('arrival');
            $table->boolean('id_verified')->default(false);
            $table->decimal('deposit_collected', 10, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void {
        Schema::dropIfExists('guest_checkins');
    }
};
