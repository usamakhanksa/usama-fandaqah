<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('booking_blocks', function (Blueprint $table) {
            $table->id();
            $table->string('property_reference');
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('reason', ['maintenance', 'owner_use', 'other'])->default('maintenance');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('booking_blocks');
    }
};
