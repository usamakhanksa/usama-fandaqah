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
        Schema::create('blocked_guests', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('team_id');
            $table->unsignedBigInteger('guest_id');
            $table->text('reason');
            $table->unsignedBigInteger('blocked_by');
            $table->dateTime('blocked_at');
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('unblocked_by')->nullable();
            $table->timestamp('unblocked_at')->nullable();
            $table->text('unblock_reason')->nullable();
            $table->enum('severity', ['warning', 'do_not_rent', 'blacklisted']);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('team_id')->references('id')->on('teams')->onDelete('cascade');
            $table->foreign('guest_id')->references('id')->on('guests')->onDelete('cascade');
            $table->foreign('blocked_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('unblocked_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blocked_guests');
    }
};
