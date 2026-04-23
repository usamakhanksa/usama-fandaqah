<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('booking_events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('property_reference')->nullable();
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->enum('type', ['viewing', 'inspection', 'public_event']);
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('booking_events');
    }
};
