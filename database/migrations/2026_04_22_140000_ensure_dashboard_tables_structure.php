<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('dashboard_kpis')) {
            Schema::create('dashboard_kpis', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->string('label');
                $table->string('value');
                $table->string('trend')->nullable();
                $table->string('icon')->nullable();
                $table->string('color')->nullable();
                $table->boolean('is_active')->default(true);
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('quick_stats')) {
            Schema::create('quick_stats', function (Blueprint $table) {
                $table->id();
                $table->string('label');
                $table->string('value');
                $table->string('icon')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('recent_activities')) {
            Schema::create('recent_activities', function (Blueprint $table) {
                $table->id();
                $table->string('type');
                $table->string('description');
                $table->foreignId('user_id')->nullable()->constrained();
                $table->string('icon')->nullable();
                $table->string('color')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('daily_reports')) {
            Schema::create('daily_reports', function (Blueprint $table) {
                $table->id();
                $table->date('report_date')->unique();
                $table->decimal('total_revenue', 15, 2)->default(0);
                $table->integer('occupied_rooms')->default(0);
                $table->decimal('adr', 10, 2)->default(0);
                $table->decimal('revpar', 10, 2)->default(0);
                $table->timestamps();
            });
        } else {
            Schema::table('daily_reports', function (Blueprint $table) {
                if (!Schema::hasColumn('daily_reports', 'occupied_rooms')) {
                    $table->integer('occupied_rooms')->default(0)->after('total_revenue');
                }
                if (!Schema::hasColumn('daily_reports', 'adr')) {
                    $table->decimal('adr', 10, 2)->default(0)->after('occupied_rooms');
                }
                if (!Schema::hasColumn('daily_reports', 'revpar')) {
                    $table->decimal('revpar', 10, 2)->default(0)->after('adr');
                }
            });
        }

        if (Schema::hasTable('reservations')) {
            Schema::table('reservations', function (Blueprint $table) {
                if (!Schema::hasColumn('reservations', 'status')) {
                    $table->string('status', 20)->default('confirmed')->after('reservation_status_id');
                }
                if (!Schema::hasColumn('reservations', 'total_price')) {
                    $table->decimal('total_price', 12, 2)->default(0)->after('status');
                }
                if (!Schema::hasColumn('reservations', 'team_id')) {
                    $table->foreignId('team_id')->nullable()->after('total_price')->constrained('teams')->nullOnDelete();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('reservations')) {
            Schema::table('reservations', function (Blueprint $table) {
                $table->dropColumn(['status', 'total_price', 'team_id']);
            });
        }
        Schema::dropIfExists('daily_reports');
        Schema::dropIfExists('recent_activities');
        Schema::dropIfExists('quick_stats');
        Schema::dropIfExists('dashboard_kpis');
    }
};
