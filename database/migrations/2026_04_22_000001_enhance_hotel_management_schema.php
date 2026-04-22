<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->addMissingReservationKeysAndIndexes();
        $this->addOperationalColumns();
        $this->createEnhancementTables();
        $this->createReportingViews();
    }

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS occupancy_dashboard');

        if (Schema::hasTable('housekeeping_tasks')) {
            Schema::table('housekeeping_tasks', function (Blueprint $table) {
                foreach (['team_id', 'reservation_id', 'assigned_to_user_id'] as $column) {
                    if (Schema::hasColumn('housekeeping_tasks', $column)) {
                        $table->dropConstrainedForeignId($column);
                    }
                }

                foreach (['notes', 'scheduled_for', 'completed_at', 'priority'] as $column) {
                    if (Schema::hasColumn('housekeeping_tasks', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        foreach (['audit_logs', 'dynamic_pricing_rules', 'loyalty_points', 'guest_communications'] as $table) {
            Schema::dropIfExists($table);
        }

        if (Schema::hasTable('teams')) {
            Schema::table('teams', function (Blueprint $table) {
                foreach (['timezone', 'default_language', 'logo_path'] as $column) {
                    if (Schema::hasColumn('teams', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        if (Schema::hasTable('guests')) {
            Schema::table('guests', function (Blueprint $table) {
                if (Schema::hasColumn('guests', 'nationality_country_id')) {
                    $table->dropConstrainedForeignId('nationality_country_id');
                }
                if (Schema::hasColumn('guests', 'preferences')) {
                    $table->dropColumn('preferences');
                }
            });
        }

        if (Schema::hasTable('units')) {
            Schema::table('units', function (Blueprint $table) {
                foreach (['floor', 'building', 'last_renovated_at'] as $column) {
                    if (Schema::hasColumn('units', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        if (Schema::hasTable('reservations')) {
            Schema::table('reservations', function (Blueprint $table) {
                foreach (['cancellation_reason', 'is_no_show_charged'] as $column) {
                    if (Schema::hasColumn('reservations', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }

    private function addMissingReservationKeysAndIndexes(): void
    {
        if (! Schema::hasTable('reservations')) {
            return;
        }

        Schema::table('reservations', function (Blueprint $table) {
            if (Schema::hasColumn('reservations', 'company_id') && Schema::hasTable('company_profiles')) {
                $table->index('company_id', 'reservations_company_id_idx');
                $table->foreign('company_id', 'reservations_company_id_fk')
                    ->references('id')
                    ->on('company_profiles')
                    ->nullOnDelete();
            }

            if (Schema::hasColumn('reservations', 'group_reservation_id') && Schema::hasTable('group_reservations')) {
                $table->index('group_reservation_id', 'reservations_group_reservation_id_idx');
                $table->foreign('group_reservation_id', 'reservations_group_reservation_id_fk')
                    ->references('id')
                    ->on('group_reservations')
                    ->nullOnDelete();
            }

            if (Schema::hasColumn('reservations', 'team_id') && Schema::hasTable('teams')) {
                $table->index('team_id', 'reservations_team_id_idx');
                $table->foreign('team_id', 'reservations_team_id_fk')
                    ->references('id')
                    ->on('teams')
                    ->nullOnDelete();
            }
        });
    }

    private function addOperationalColumns(): void
    {
        if (Schema::hasTable('reservations')) {
            Schema::table('reservations', function (Blueprint $table) {
                if (! Schema::hasColumn('reservations', 'cancellation_reason')) {
                    $table->text('cancellation_reason')->nullable()->after('status');
                }
                if (! Schema::hasColumn('reservations', 'is_no_show_charged')) {
                    $table->boolean('is_no_show_charged')->default(false)->after('cancellation_reason');
                }
            });
        }

        if (Schema::hasTable('units')) {
            Schema::table('units', function (Blueprint $table) {
                if (! Schema::hasColumn('units', 'floor')) {
                    $table->string('floor', 20)->nullable()->after('number');
                }
                if (! Schema::hasColumn('units', 'building')) {
                    $table->string('building')->nullable()->after('floor');
                }
                if (! Schema::hasColumn('units', 'last_renovated_at')) {
                    $table->timestamp('last_renovated_at')->nullable()->after('building');
                }
            });
        }

        if (Schema::hasTable('guests')) {
            Schema::table('guests', function (Blueprint $table) {
                if (! Schema::hasColumn('guests', 'nationality_country_id') && Schema::hasTable('countries')) {
                    $table->foreignId('nationality_country_id')->nullable()->after('gender')->constrained('countries')->nullOnDelete();
                }
                if (! Schema::hasColumn('guests', 'preferences')) {
                    $table->json('preferences')->nullable()->after('nationality_country_id');
                }
            });
        }

        if (Schema::hasTable('teams')) {
            Schema::table('teams', function (Blueprint $table) {
                if (! Schema::hasColumn('teams', 'timezone')) {
                    $table->string('timezone', 64)->default('UTC')->after('name');
                }
                if (! Schema::hasColumn('teams', 'default_language')) {
                    $table->string('default_language', 10)->default('en')->after('timezone');
                }
                if (! Schema::hasColumn('teams', 'logo_path')) {
                    $table->string('logo_path')->nullable()->after('photo_url');
                }
            });
        }

        if (Schema::hasTable('housekeeping_tasks')) {
            Schema::table('housekeeping_tasks', function (Blueprint $table) {
                if (! Schema::hasColumn('housekeeping_tasks', 'team_id') && Schema::hasTable('teams')) {
                    $table->foreignId('team_id')->nullable()->after('unit_id')->constrained('teams')->nullOnDelete();
                }
                if (! Schema::hasColumn('housekeeping_tasks', 'reservation_id')) {
                    $table->foreignId('reservation_id')->nullable()->after('team_id')->constrained('reservations')->nullOnDelete();
                }
                if (! Schema::hasColumn('housekeeping_tasks', 'assigned_to_user_id')) {
                    $table->foreignId('assigned_to_user_id')->nullable()->after('reservation_id')->constrained('users')->nullOnDelete();
                }
                if (! Schema::hasColumn('housekeeping_tasks', 'priority')) {
                    $table->string('priority', 20)->default('normal')->after('status');
                }
                if (! Schema::hasColumn('housekeeping_tasks', 'notes')) {
                    $table->text('notes')->nullable()->after('priority');
                }
                if (! Schema::hasColumn('housekeeping_tasks', 'scheduled_for')) {
                    $table->timestamp('scheduled_for')->nullable()->after('notes');
                }
                if (! Schema::hasColumn('housekeeping_tasks', 'completed_at')) {
                    $table->timestamp('completed_at')->nullable()->after('scheduled_for');
                }
            });
        }
    }

    private function createEnhancementTables(): void
    {
        if (! Schema::hasTable('guest_communications')) {
            Schema::create('guest_communications', function (Blueprint $table) {
                $table->id();
                $table->foreignId('guest_id')->nullable()->constrained('guests')->nullOnDelete();
                $table->foreignId('reservation_id')->nullable()->constrained('reservations')->nullOnDelete();
                $table->foreignId('team_id')->nullable()->constrained('teams')->nullOnDelete();
                $table->enum('channel', ['email', 'sms', 'whatsapp', 'push']);
                $table->enum('message_type', ['transactional', 'marketing'])->default('transactional');
                $table->string('subject')->nullable();
                $table->text('message_body');
                $table->timestamp('sent_at')->nullable();
                $table->string('provider_message_id')->nullable();
                $table->json('meta')->nullable();
                $table->timestamps();

                $table->index(['team_id', 'channel']);
            });
        }

        if (! Schema::hasTable('loyalty_points')) {
            Schema::create('loyalty_points', function (Blueprint $table) {
                $table->id();
                $table->foreignId('guest_id')->constrained('guests')->cascadeOnDelete();
                $table->foreignId('team_id')->nullable()->constrained('teams')->nullOnDelete();
                $table->integer('points')->default(0);
                $table->enum('type', ['earn', 'redeem', 'adjustment']);
                $table->string('reference_type')->nullable();
                $table->unsignedBigInteger('reference_id')->nullable();
                $table->text('note')->nullable();
                $table->timestamp('effective_at')->useCurrent();
                $table->timestamps();

                $table->index(['guest_id', 'team_id']);
            });
        }

        if (! Schema::hasTable('dynamic_pricing_rules')) {
            Schema::create('dynamic_pricing_rules', function (Blueprint $table) {
                $table->id();
                $table->foreignId('team_id')->nullable()->constrained('teams')->nullOnDelete();
                $table->foreignId('room_type_id')->nullable()->constrained('room_types')->nullOnDelete();
                $table->string('name');
                $table->enum('adjustment_type', ['percentage', 'fixed']);
                $table->decimal('adjustment_value', 10, 2);
                $table->unsignedTinyInteger('occupancy_threshold')->nullable();
                $table->date('start_date')->nullable();
                $table->date('end_date')->nullable();
                $table->boolean('is_active')->default(true);
                $table->unsignedTinyInteger('priority')->default(10);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('audit_logs')) {
            Schema::create('audit_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('team_id')->nullable()->constrained('teams')->nullOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('event');
                $table->string('auditable_type');
                $table->unsignedBigInteger('auditable_id');
                $table->json('old_values')->nullable();
                $table->json('new_values')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->string('user_agent')->nullable();
                $table->timestamp('occurred_at')->useCurrent();
                $table->timestamps();

                $table->index(['auditable_type', 'auditable_id']);
            });
        }
    }

    private function createReportingViews(): void
    {
        if (! Schema::hasTable('reservations') || ! Schema::hasTable('units')) {
            return;
        }

        $dateInColumn = Schema::hasColumn('reservations', 'date_in') ? 'date_in' : (Schema::hasColumn('reservations', 'check_in') ? 'check_in' : null);
        $priceColumn = Schema::hasColumn('reservations', 'total_price') ? 'total_price' : null;
        $checkInMarker = Schema::hasColumn('reservations', 'checked_in') ? 'checked_in' : (Schema::hasColumn('reservations', 'checked_in_at') ? 'checked_in_at' : null);

        if (! $dateInColumn || ! $priceColumn) {
            return;
        }

        DB::statement('DROP VIEW IF EXISTS occupancy_dashboard');

        $checkedInFilter = $checkInMarker ? "AND r.{$checkInMarker} IS NOT NULL" : '';

        DB::statement(
            "CREATE VIEW occupancy_dashboard AS
            SELECT
                COALESCE(r.team_id, 0) AS team_id,
                DATE(r.{$dateInColumn}) AS stay_date,
                COUNT(DISTINCT r.unit_id) AS occupied_units,
                COUNT(*) AS reservations_count,
                SUM(r.{$priceColumn}) AS revenue,
                AVG(r.{$priceColumn}) AS adr
            FROM reservations r
            WHERE r.status = 'confirmed' {$checkedInFilter}
            GROUP BY team_id, stay_date"
        );
    }
};
