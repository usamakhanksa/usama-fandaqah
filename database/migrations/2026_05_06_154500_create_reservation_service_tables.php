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
        if (!Schema::hasTable('reservation_services')) {
            Schema::create('reservation_services', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('team_id')->index();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('name')->nullable();
                $table->decimal('price', 12, 2)->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('reservation_service_mappers')) {
            Schema::create('reservation_service_mappers', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('reservation_id')->index();
                $table->unsignedBigInteger('reservation_service_id')->index();
                $table->integer('quantity')->default(1);
                $table->decimal('price', 12, 2)->default(0);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservation_service_mappers');
        Schema::dropIfExists('reservation_services');
    }
};
