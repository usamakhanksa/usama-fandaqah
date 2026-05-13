<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add team_id to tables that should be team-scoped but don't have it yet
        $tablesToAddTeamId = [
            'rooms',           // Rooms belong to a specific hotel/team
            'room_types',      // Room types are team-specific
            'guests',          // Guests are associated with a team's reservations
            'room_floors',     // Floors are part of a team's property
            'room_metrics',    // Metrics are team-specific
            'occupancy_metrics', // Metrics are team-specific
            'company_profiles', // Company profiles belong to a team
            'contact_people',  // Contacts belong to team's companies
            'company_drafts',  // Drafts are team-specific
            'uploaded_media',  // Media belongs to team resources
            'promo_codes',     // Promos are team-specific
            'reservation_notes', // Notes are for team's reservations
            'financial_records', // Records relate to team's bookings
            'brands',          // POS brands are team-specific
            'product_categories', // Product categories are team-specific
            'product_sub_categories', // Subcategories are team-specific
            'p_o_s_channels',  // POS channels are team-specific
            'p_o_s_stores',    // POS stores are team-specific
            'channels',        // Booking channels are team-specific
            'channel_rate_plans', // Rate plans are team-specific
            'channel_reservations', // Channel reservations are team-specific
            'ledger_numbers',  // Ledger numbers are team-specific
            'maintenance_categories', // Maintenance categories are team-specific
            'maintenance_tickets', // Tickets are for team's units
            'housekeeping_tasks', // Tasks are for team's units
            'hotel_amenities', // Amenities are team-specific
            'customer_groups', // Customer groups are team-specific
            'reservation_resources', // Resources are team-specific
            'leads',           // Leads might be team-specific
            'service_categories', // Service categories are team-specific
            'global_settings', // Global settings might need team association
            'stay_charge_configs', // Configs are team-specific
            'stay_charge_overrides', // Overrides are team-specific
        ];

        foreach ($tablesToAddTeamId as $tableName) {
            if (Schema::hasTable($tableName) && !Schema::hasColumn($tableName, 'team_id')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->unsignedBigInteger('team_id')->nullable()->after('id')->index();
                    $table->foreign('team_id')->references('id')->on('teams')->nullOnDelete();
                });
            }
        }

        // Add missing indexes for performance
        $indexedColumns = [
            ['reservations', 'status'],
            ['reservations', 'check_in'],
            ['reservations', 'check_out'],
            ['reservations', 'created_at'],
            ['reservations', 'deleted_at'],
            ['units', 'status'],
            ['units', 'unit_type_id'],
            ['units', 'unit_status_id'],
            ['units', 'created_at'],
            ['units', 'deleted_at'],
            ['transactions', 'status'],
            ['transactions', 'confirmed'],
            ['transactions', 'created_at'],
            ['transactions', 'is_public'],
            ['service_logs', 'type'],
            ['service_logs', 'created_at'],
            ['service_logs', 'business_date'],
            ['guests', 'name'],
            ['guests', 'email'],
            ['guests', 'phone'],
            ['companies', 'name'],
            ['companies', 'email'],
            ['companies', 'tax_number'],
            ['users', 'email'],
            ['users', 'current_team_id'],
            // Skip sources.name as it's a JSON column
            ['promo_codes', 'code'],
            ['promo_codes', 'expires_at'],
            ['check_in_records', 'date'],
            ['check_out_records', 'date'],
            ['service_logs', 'business_date'],
            ['service_logs', 'is_freezed'],
            ['transactions', 'is_freezed'],
            ['transactions', 'business_date'],
            ['promissories', 'due_date'],
            ['promissories', 'status'],
            ['cashier_shifts', 'status'],
            ['cashier_shifts', 'shift_date'],
            ['night_audit_log', 'status'],
            ['night_audit_log', 'business_date'],
            ['night_audit_occupancy_snapshot', 'business_date'],
            ['night_audit_occupancy_snapshot', 'occupancy_pct'],
            ['reservation_extensions', 'created_at'],
            ['reservation_ratings', 'rating'],
            ['reservation_audit_locks', 'locked_from_date'],
        ];

        foreach ($indexedColumns as [$tableName, $columnName]) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, $columnName)) {
                $indexName = "idx_{$tableName}_{$columnName}";
                $existingIndexes = DB::select("SHOW INDEX FROM {$tableName} WHERE Column_name = ?", [$columnName]);
                
                // Only add index if it doesn't already exist
                if (empty($existingIndexes)) {
                    // Special handling for JSON columns
                    if ($columnName === 'name' && $tableName === 'sources') {
                        // For JSON columns, we can't directly index them
                        // Instead, we could create a generated column if needed
                        continue;
                    } else {
                        Schema::table($tableName, function (Blueprint $table) use ($columnName) {
                            $table->index($columnName);
                        });
                    }
                }
            }
        }

        // Add indexes to existing team_id columns that don't have them
        $tablesToIndexTeamId = [
            'reservations',
            'transactions',
            'service_logs',
            'promissories',
            'cashier_shifts',
            'companies',
            'sources',
            'company_notes',
            'night_audit_log',
            'night_audit_occupancy_snapshot',
            'night_audit_noshow_log',
            'night_audit_snapshot_queue',
            'reservation_audit_locks',
            'business_date_transactions',
            'room_status_log',
            'early_late_charge_configs',
            'checkout_balance_transfers',
            'invoice_credit_notes',
            'invoice_transfers',
            'commission_payments',
            'company_groups',
            'payment_correction_logs',
            'room_status_logs'
        ];

        foreach ($tablesToIndexTeamId as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'team_id')) {
                // Check if index already exists
                $existingIndexes = DB::select("SHOW INDEX FROM {$tableName} WHERE Column_name = 'team_id'");
                if (empty($existingIndexes)) {
                    Schema::table($tableName, function (Blueprint $table) {
                        $table->index('team_id');
                    });
                }
            }
        }

        // Add missing foreign key constraints where they should exist
        // Make sure reservations table has proper foreign keys
        if (Schema::hasTable('reservations')) {
            if (Schema::hasColumn('reservations', 'guest_id') && 
                !DB::select("SELECT * FROM information_schema.KEY_COLUMN_USAGE 
                             WHERE TABLE_NAME = 'reservations' 
                             AND COLUMN_NAME = 'guest_id' 
                             AND CONSTRAINT_NAME != 'PRIMARY'")) {
                Schema::table('reservations', function (Blueprint $table) {
                    $table->foreign('guest_id')->references('id')->on('guests')->nullOnDelete();
                });
            }
            
            if (Schema::hasColumn('reservations', 'room_id') && 
                !DB::select("SELECT * FROM information_schema.KEY_COLUMN_USAGE 
                             WHERE TABLE_NAME = 'reservations' 
                             AND COLUMN_NAME = 'room_id' 
                             AND CONSTRAINT_NAME != 'PRIMARY'")) {
                Schema::table('reservations', function (Blueprint $table) {
                    $table->foreign('room_id')->references('id')->on('rooms')->nullOnDelete();
                });
            }
            
            if (Schema::hasColumn('reservations', 'source_id') && 
                !DB::select("SELECT * FROM information_schema.KEY_COLUMN_USAGE 
                             WHERE TABLE_NAME = 'reservations' 
                             AND COLUMN_NAME = 'source_id' 
                             AND CONSTRAINT_NAME != 'PRIMARY'")) {
                Schema::table('reservations', function (Blueprint $table) {
                    $table->foreign('source_id')->references('id')->on('sources')->nullOnDelete();
                });
            }
        }
        
        // Add missing FK for transactions
        if (Schema::hasTable('transactions')) {
            if (Schema::hasColumn('transactions', 'created_by') && 
                !DB::select("SELECT * FROM information_schema.KEY_COLUMN_USAGE 
                             WHERE TABLE_NAME = 'transactions' 
                             AND COLUMN_NAME = 'created_by' 
                             AND CONSTRAINT_NAME != 'PRIMARY'")) {
                Schema::table('transactions', function (Blueprint $table) {
                    $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
                });
            }
        }
        
        // Add missing FK for service_logs
        if (Schema::hasTable('service_logs')) {
            if (Schema::hasColumn('service_logs', 'user_id') && 
                !DB::select("SELECT * FROM information_schema.KEY_COLUMN_USAGE 
                             WHERE TABLE_NAME = 'service_logs' 
                             AND COLUMN_NAME = 'user_id' 
                             AND CONSTRAINT_NAME != 'PRIMARY'")) {
                Schema::table('service_logs', function (Blueprint $table) {
                    $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
                });
            }
            
            if (Schema::hasColumn('service_logs', 'transaction_id') && 
                !DB::select("SELECT * FROM information_schema.KEY_COLUMN_USAGE 
                             WHERE TABLE_NAME = 'service_logs' 
                             AND COLUMN_NAME = 'transaction_id' 
                             AND CONSTRAINT_NAME != 'PRIMARY'")) {
                Schema::table('service_logs', function (Blueprint $table) {
                    $table->foreign('transaction_id')->references('id')->on('transactions')->nullOnDelete();
                });
            }
        }
    }

    public function down(): void
    {
        // Reverse team_id additions
        $tablesToRemoveTeamId = [
            'rooms', 'room_types', 'guests', 'room_floors', 'room_metrics', 
            'occupancy_metrics', 'company_profiles', 'contact_people', 
            'company_drafts', 'uploaded_media', 'promo_codes', 
            'reservation_notes', 'financial_records', 'brands', 
            'product_categories', 'product_sub_categories', 
            'p_o_s_channels', 'p_o_s_stores', 'channels', 
            'channel_rate_plans', 'channel_reservations', 
            'ledger_numbers', 'maintenance_categories', 
            'maintenance_tickets', 'housekeeping_tasks', 
            'hotel_amenities', 'customer_groups', 
            'reservation_resources', 'leads', 
            'service_categories', 'global_settings', 
            'stay_charge_configs', 'stay_charge_overrides'
        ];

        foreach ($tablesToRemoveTeamId as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'team_id')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropForeign(['team_id']);
                    $table->dropColumn('team_id');
                });
            }
        }
    }
};