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
        Schema::create('turnaway_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('team_id');
            $table->unsignedBigInteger('guest_id')->nullable();
            $table->string('guest_name');
            $table->string('guest_phone')->nullable();
            $table->string('requested_room_type')->nullable();
            $table->date('requested_date');
            $table->integer('requested_nights')->default(1);
            $table->enum('reason', ['no_availability', 'rate_disagreement', 'overbooking', 'other']);
            $table->text('reason_detail')->nullable();
            $table->decimal('estimated_revenue_loss', 12, 2)->nullable();
            $table->boolean('alternative_offered')->default(false);
            $table->text('alternative_details')->nullable();
            $table->unsignedBigInteger('turned_away_by');
            $table->timestamps();

            $table->foreign('team_id')->references('id')->on('teams')->onDelete('cascade');
            $table->foreign('guest_id')->references('id')->on('guests')->onDelete('set null');
            $table->foreign('turned_away_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('turnaway_logs');
    }
};
