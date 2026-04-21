<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('unit_types')) {
            Schema::create('unit_types', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('unit_statuses')) {
            Schema::create('unit_statuses', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->string('color')->default('#9ca3af');
                $table->timestamps();
            });
        }

        Schema::table('units', function (Blueprint $table) {
            if (!Schema::hasColumn('units', 'room_id')) {
                $table->foreignId('room_id')->nullable()->after('id')->constrained();
            }
            if (!Schema::hasColumn('units', 'room_floor_id')) {
                $table->foreignId('room_floor_id')->nullable()->after('room_id')->constrained();
            }
            if (!Schema::hasColumn('units', 'unit_type_id')) {
                $table->foreignId('unit_type_id')->nullable()->after('room_floor_id')->constrained('unit_types');
            }
            if (!Schema::hasColumn('units', 'unit_status_id')) {
                $table->foreignId('unit_status_id')->nullable()->after('unit_type_id')->constrained('unit_statuses');
            }
            if (!Schema::hasColumn('units', 'capacity')) {
                $table->unsignedTinyInteger('capacity')->default(2)->after('status');
            }
            if (!Schema::hasColumn('units', 'beds')) {
                $table->unsignedTinyInteger('beds')->default(1)->after('capacity');
            }
            if (!Schema::hasColumn('units', 'baths')) {
                $table->unsignedTinyInteger('baths')->default(1)->after('beds');
            }
            if (!Schema::hasColumn('units', 'thumbnail')) {
                $table->string('thumbnail')->nullable()->after('baths');
            }
        });

        if (!Schema::hasTable('check_in_records')) {
            Schema::create('check_in_records', function (Blueprint $table) {
                $table->id();
                $table->foreignId('reservation_id')->constrained();
                $table->foreignId('unit_id')->constrained();
                $table->date('date');
                $table->string('time', 20);
                $table->text('note')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('check_out_records')) {
            Schema::create('check_out_records', function (Blueprint $table) {
                $table->id();
                $table->foreignId('reservation_id')->constrained();
                $table->foreignId('unit_id')->constrained();
                $table->date('date');
                $table->string('time', 20);
                $table->text('note')->nullable();
                $table->decimal('final_charges', 10, 2)->default(0);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('check_out_records');
        Schema::dropIfExists('check_in_records');

        Schema::table('units', function (Blueprint $table) {
            $table->dropConstrainedForeignId('room_id');
            $table->dropConstrainedForeignId('room_floor_id');
            $table->dropConstrainedForeignId('unit_type_id');
            $table->dropConstrainedForeignId('unit_status_id');
            $table->dropColumn(['capacity', 'beds', 'baths', 'thumbnail']);
        });

        Schema::dropIfExists('unit_statuses');
        Schema::dropIfExists('unit_types');
    }
};
