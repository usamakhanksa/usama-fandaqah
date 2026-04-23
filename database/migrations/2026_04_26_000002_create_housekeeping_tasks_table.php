<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (Schema::hasTable('housekeeping_tasks')) {
            Schema::table('housekeeping_tasks', function (Blueprint $table) {
                // Drop legacy unit_id and its foreign key if they exist
                if (Schema::hasColumn('housekeeping_tasks', 'unit_id')) {
                    // We need to disable FK constraints to drop the column safely if possible, 
                    // but Laravel's dropForeign usually works if we know the name or use the array syntax.
                    try {
                        $table->dropForeign(['unit_id']);
                    } catch (\Exception $e) {
                        // Ignore if foreign key doesn't exist or has different name
                    }
                    $table->dropColumn('unit_id');
                }

                if (!Schema::hasColumn('housekeeping_tasks', 'room_id')) {
                    $table->foreignId('room_id')->after('id')->constrained('rooms')->cascadeOnDelete();
                }
                // ... rest of the columns
                if (!Schema::hasColumn('housekeeping_tasks', 'assigned_to')) {
                    $table->string('assigned_to')->after('room_id')->nullable();
                }
                if (!Schema::hasColumn('housekeeping_tasks', 'task_type')) {
                    $table->enum('task_type', ['daily_refresh', 'deep_clean', 'inspection', 'maintenance'])->after('assigned_to');
                }
                if (!Schema::hasColumn('housekeeping_tasks', 'status')) {
                    $table->enum('status', ['pending', 'in_progress', 'completed'])->default('pending')->after('task_type');
                }
                if (!Schema::hasColumn('housekeeping_tasks', 'notes')) {
                    $table->text('notes')->nullable()->after('status');
                }
                if (!Schema::hasColumn('housekeeping_tasks', 'scheduled_at')) {
                    $table->dateTime('scheduled_at')->nullable()->after('notes');
                }
                if (!Schema::hasColumn('housekeeping_tasks', 'completed_at')) {
                    $table->dateTime('completed_at')->nullable()->after('scheduled_at');
                }
            });
        } else {
            Schema::create('housekeeping_tasks', function (Blueprint $table) {
                $table->id();
                $table->foreignId('room_id')->constrained('rooms')->cascadeOnDelete();
                $table->string('assigned_to')->nullable();
                $table->enum('task_type', ['daily_refresh', 'deep_clean', 'inspection', 'maintenance']);
                $table->enum('status', ['pending', 'in_progress', 'completed'])->default('pending');
                $table->text('notes')->nullable();
                $table->dateTime('scheduled_at')->nullable();
                $table->dateTime('completed_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void {
        Schema::dropIfExists('housekeeping_tasks');
    }
};
