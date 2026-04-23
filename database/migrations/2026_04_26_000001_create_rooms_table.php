<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (Schema::hasTable('rooms')) {
            Schema::table('rooms', function (Blueprint $table) {
                if (!Schema::hasColumn('rooms', 'room_number')) {
                    if (Schema::hasColumn('rooms', 'number')) {
                        $table->renameColumn('number', 'room_number');
                    } else {
                        $table->string('room_number')->unique()->after('id');
                    }
                }
                if (!Schema::hasColumn('rooms', 'type')) {
                    $table->string('type')->after('room_number')->nullable();
                }
                if (!Schema::hasColumn('rooms', 'capacity')) {
                    $table->integer('capacity')->default(2)->after('floor');
                }
                if (!Schema::hasColumn('rooms', 'base_price')) {
                    $table->decimal('base_price', 10, 2)->default(0)->after('capacity');
                }
                if (!Schema::hasColumn('rooms', 'features')) {
                    $table->json('features')->nullable()->after('status');
                }
                if (!Schema::hasColumn('rooms', 'deleted_at')) {
                    $table->softDeletes();
                }
            });
        } else {
            Schema::create('rooms', function (Blueprint $table) {
                $table->id();
                $table->string('room_number')->unique();
                $table->string('type'); // e.g., Royal Suite, Executive King
                $table->string('floor')->nullable();
                $table->integer('capacity')->default(2);
                $table->decimal('base_price', 10, 2);
                $table->enum('status', ['clean', 'dirty', 'inspecting', 'out_of_order'])->default('clean');
                $table->json('features')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down(): void {
        Schema::dropIfExists('rooms');
    }
};
