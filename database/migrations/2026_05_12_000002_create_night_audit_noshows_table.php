<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('night_audit_noshows', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('team_id');
            $table->date('business_date');
            $table->unsignedBigInteger('reservation_id');
            $table->unsignedBigInteger('guest_id');
            $table->string('room_type');
            $table->dateTime('expected_arrival');
            $table->decimal('no_show_charge', 12, 2);
            $table->boolean('charge_applied')->default(false);
            $table->boolean('waived')->default(false);
            $table->unsignedBigInteger('waived_by')->nullable();
            $table->text('waive_reason')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->foreign('team_id')->references('id')->on('teams')->onDelete('cascade');
            $table->foreign('reservation_id')->references('id')->on('reservations')->onDelete('cascade');
            $table->foreign('guest_id')->references('id')->on('guests')->onDelete('cascade');
            $table->foreign('waived_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('night_audit_noshows');
    }
};
?>
