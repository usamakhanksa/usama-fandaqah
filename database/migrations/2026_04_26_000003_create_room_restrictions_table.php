<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (Schema::hasTable('room_restrictions')) {
            Schema::table('room_restrictions', function (Blueprint $table) {
                // Drop legacy unit_id and its foreign key if they exist
                if (Schema::hasColumn('room_restrictions', 'unit_id')) {
                    try {
                        $table->dropForeign(['unit_id']);
                    } catch (\Exception $e) {}
                    $table->dropColumn('unit_id');
                }

                if (!Schema::hasColumn('room_restrictions', 'room_id')) {
                    $table->foreignId('room_id')->after('id')->constrained('rooms')->cascadeOnDelete();
                }
                if (!Schema::hasColumn('room_restrictions', 'start_date')) {
                    $table->date('start_date')->after('room_id');
                }
                if (!Schema::hasColumn('room_restrictions', 'end_date')) {
                    $table->date('end_date')->after('start_date');
                }
                if (!Schema::hasColumn('room_restrictions', 'restriction_type')) {
                    $table->enum('restriction_type', ['cta', 'ctd', 'min_los', 'max_los', 'blackout'])->after('end_date');
                }
                if (!Schema::hasColumn('room_restrictions', 'value')) {
                    $table->integer('value')->nullable()->after('restriction_type');
                }
                if (!Schema::hasColumn('room_restrictions', 'reason')) {
                    $table->text('reason')->nullable()->after('value');
                }
            });
        } else {
            Schema::create('room_restrictions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('room_id')->constrained('rooms')->cascadeOnDelete();
                $table->date('start_date');
                $table->date('end_date');
                $table->enum('restriction_type', ['cta', 'ctd', 'min_los', 'max_los', 'blackout']);
                $table->integer('value')->nullable(); // e.g., 3 for MinLOS 3 days
                $table->text('reason')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void {
        Schema::dropIfExists('room_restrictions');
    }
};
