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
        if (Schema::hasTable('guests')) {
            return;
        }

        Schema::create('guests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id')->nullable()->index();
            $table->string('shomoos_id')->nullable();
            $table->string('name')->nullable()->index();
            $table->tinyInteger('gender')->nullable();
            $table->tinyInteger('relation_type')->nullable();
            $table->string('id_number')->nullable()->index();
            $table->tinyInteger('id_type')->nullable();
            $table->tinyInteger('customer_type')->nullable();
            $table->unsignedBigInteger('country_id')->nullable()->index(); // Normalized from varchar
            $table->string('id_serial_number')->nullable();
            $table->string('visa_number')->nullable();
            $table->unsignedBigInteger('team_id')->nullable()->index();
            $table->string('shomoos_escort_id')->nullable();
            $table->date('birthday_date')->nullable();
            $table->unsignedBigInteger('reservation_id')->nullable()->index();
            
            $table->timestamps();
            
            $table->foreign('customer_id', 'guests_new_customer_id_foreign')->references('id')->on('customers')->onDelete('cascade');
            $table->foreign('reservation_id', 'guests_new_reservation_id_foreign')->references('id')->on('reservations')->onDelete('cascade');
            $table->foreign('team_id', 'guests_new_team_id_foreign')->references('id')->on('teams')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guests');
    }
};
