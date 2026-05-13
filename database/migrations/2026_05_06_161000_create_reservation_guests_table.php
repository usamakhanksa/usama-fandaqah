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
        if (!Schema::hasTable('reservation_guests')) {
            Schema::create('reservation_guests', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('reservation_id')->index();
                $table->unsignedBigInteger('guest_id')->index();
                $table->boolean('is_primary')->default(false);
                $table->string('relation')->nullable(); // e.g., Family, Friend, etc.
                $table->timestamps();
                
                $table->foreign('reservation_id')->references('id')->on('reservations')->onDelete('cascade');
                $table->foreign('guest_id')->references('id')->on('guests')->onDelete('cascade');
            });
        }

        // Fix company_groups if team_id is missing
        if (Schema::hasTable('company_groups')) {
            Schema::table('company_groups', function (Blueprint $table) {
                if (!Schema::hasColumn('company_groups', 'team_id')) {
                    $table->unsignedBigInteger('team_id')->nullable()->index()->after('id');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservation_guests');
    }
};
