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
        Schema::create('night_audit_snapshots', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('team_id');
            $table->date('business_date');
            $table->enum('snapshot_type', ['pre_audit', 'post_audit']);
            $table->json('room_status');
            $table->json('guest_counts');
            $table->json('revenue_summary');
            $table->json('occupancy_data');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->unique(['team_id', 'business_date', 'snapshot_type'], 'night_audit_snapshots_unique');
            $table->foreign('team_id')->references('id')->on('teams')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('night_audit_snapshots');
    }
};
?>