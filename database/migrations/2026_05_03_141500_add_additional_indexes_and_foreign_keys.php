<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add composite indexes for common query patterns
        if (Schema::hasTable('transactions')) {
            // Index for querying transactions by team and status
            if (Schema::hasColumn('transactions', 'team_id') && 
                !DB::select("SHOW INDEX FROM transactions WHERE Key_name = 'idx_transactions_team_status'")) {
                Schema::table('transactions', function (Blueprint $table) {
                    $table->index(['team_id', 'confirmed', 'type'], 'idx_transactions_team_status');
                });
            }
            
            // Index for querying by payable type and ID
            if (!DB::select("SHOW INDEX FROM transactions WHERE Key_name = 'idx_transactions_payable'")) {
                Schema::table('transactions', function (Blueprint $table) {
                    $table->index(['payable_type', 'payable_id'], 'idx_transactions_payable');
                });
            }
            
            // Index for business date queries (only if team_id exists)
            if (Schema::hasColumn('transactions', 'team_id') &&
                !DB::select("SHOW INDEX FROM transactions WHERE Key_name = 'idx_transactions_business_date'")) {
                Schema::table('transactions', function (Blueprint $table) {
                    $table->index(['team_id', 'business_date'], 'idx_transactions_business_date');
                });
            }
        }
        
        if (Schema::hasTable('reservations')) {
            // Index for checking overlapping reservations
            if (!DB::select("SHOW INDEX FROM reservations WHERE Key_name = 'idx_reservations_unit_dates'")) {
                Schema::table('reservations', function (Blueprint $table) {
                    $table->index(['unit_id', 'check_in', 'check_out'], 'idx_reservations_unit_dates');
                });
            }
            
            // Index for common reservation queries (only if team_id exists)
            if (Schema::hasColumn('reservations', 'team_id') &&
                !DB::select("SHOW INDEX FROM reservations WHERE Key_name = 'idx_reservations_status_dates'")) {
                Schema::table('reservations', function (Blueprint $table) {
                    $table->index(['team_id', 'status', 'check_in'], 'idx_reservations_status_dates');
                });
            }
        }
        
        if (Schema::hasTable('service_logs')) {
            // Index for common service log queries (only if team_id exists)
            if (Schema::hasColumn('service_logs', 'team_id') &&
                !DB::select("SHOW INDEX FROM service_logs WHERE Key_name = 'idx_service_logs_team_type'")) {
                Schema::table('service_logs', function (Blueprint $table) {
                    $table->index(['team_id', 'type', 'business_date'], 'idx_service_logs_team_type');
                });
            }
        }
        
        if (Schema::hasTable('units')) {
            // Index for availability queries
            if (!DB::select("SHOW INDEX FROM units WHERE Key_name = 'idx_units_status_category'")) {
                Schema::table('units', function (Blueprint $table) {
                    $table->index(['unit_status_id', 'unit_type_id', 'status'], 'idx_units_status_category');
                });
            }
        }
        
        // Add missing foreign key constraints
        if (Schema::hasTable('reservations')) {
            // Add FK constraint for unit_id if not exists
            if (Schema::hasColumn('reservations', 'unit_id')) {
                $fkExists = DB::select("
                    SELECT CONSTRAINT_NAME 
                    FROM information_schema.KEY_COLUMN_USAGE 
                    WHERE TABLE_SCHEMA = DATABASE()
                      AND TABLE_NAME = 'reservations'
                      AND COLUMN_NAME = 'unit_id'
                      AND REFERENCED_TABLE_NAME = 'units'
                ");
                
                if (empty($fkExists)) {
                    Schema::table('reservations', function (Blueprint $table) {
                        $table->foreign('unit_id')->references('id')->on('units')->nullOnDelete();
                    });
                }
            }
            
            // Add FK constraint for company_id if not exists
            if (Schema::hasColumn('reservations', 'company_id')) {
                $fkExists = DB::select("
                    SELECT CONSTRAINT_NAME 
                    FROM information_schema.KEY_COLUMN_USAGE 
                    WHERE TABLE_SCHEMA = DATABASE()
                      AND TABLE_NAME = 'reservations'
                      AND COLUMN_NAME = 'company_id'
                      AND REFERENCED_TABLE_NAME = 'companies'
                ");
                
                if (empty($fkExists)) {
                    Schema::table('reservations', function (Blueprint $table) {
                        $table->foreign('company_id')->references('id')->on('companies')->nullOnDelete();
                    });
                }
            }
        }
        
        if (Schema::hasTable('transactions')) {
            // Add FK for wallet_id if exists and no constraint, AND if the wallets table exists
            if (Schema::hasColumn('transactions', 'wallet_id') && Schema::hasTable('wallets')) {
                $fkExists = DB::select("
                    SELECT CONSTRAINT_NAME 
                    FROM information_schema.KEY_COLUMN_USAGE 
                    WHERE TABLE_SCHEMA = DATABASE()
                      AND TABLE_NAME = 'transactions'
                      AND COLUMN_NAME = 'wallet_id'
                      AND REFERENCED_TABLE_NAME = 'wallets'
                ");
                
                if (empty($fkExists)) {
                    Schema::table('transactions', function (Blueprint $table) {
                        $table->foreign('wallet_id')->references('id')->on('wallets')->nullOnDelete();
                    });
                }
            }
            
            // Add FK for unit_category_id if exists and no constraint, AND if the unit_categories table exists
            if (Schema::hasColumn('transactions', 'unit_category_id') && Schema::hasTable('unit_categories')) {
                $fkExists = DB::select("
                    SELECT CONSTRAINT_NAME 
                    FROM information_schema.KEY_COLUMN_USAGE 
                    WHERE TABLE_SCHEMA = DATABASE()
                      AND TABLE_NAME = 'transactions'
                      AND COLUMN_NAME = 'unit_category_id'
                      AND REFERENCED_TABLE_NAME = 'unit_categories'
                ");
                
                if (empty($fkExists)) {
                    Schema::table('transactions', function (Blueprint $table) {
                        $table->foreign('unit_category_id')->references('id')->on('unit_categories')->nullOnDelete();
                    });
                }
            }
        }
        
        // Add indexes for soft-deleted tables to improve queries with withTrashed()
        $softDeleteTables = [
            'reservations', 'transactions', 'service_logs', 'promissories', 
            'companies', 'company_notes', 'users', 'roles', 'units', 
            'guests', 'service_logs_notes', 'invoice_credit_notes'
        ];
        
        foreach ($softDeleteTables as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'deleted_at')) {
                // Add an index on deleted_at if it doesn't exist
                $indexExists = DB::select("SHOW INDEX FROM {$tableName} WHERE Column_name = 'deleted_at'");
                if (empty($indexExists)) {
                    Schema::table($tableName, function (Blueprint $table) {
                        $table->index(['deleted_at']);
                    });
                }
            }
        }
        
        // Add full-text search indexes where appropriate
        if (Schema::hasTable('guests')) {
            // Add full-text index on guest name for search
            try {
                if (!DB::select("SHOW INDEX FROM guests WHERE Key_name = 'ft_guests_search'")) {
                    DB::statement("ALTER TABLE guests ADD FULLTEXT(name)");
                }
            } catch (\Exception $e) {
                // Fulltext might not be supported, skip
            }
        }
        
        if (Schema::hasTable('companies')) {
            // Add full-text index on company name for search
            try {
                if (!DB::select("SHOW INDEX FROM companies WHERE Key_name = 'ft_companies_search'")) {
                    DB::statement("ALTER TABLE companies ADD FULLTEXT(name)");
                }
            } catch (\Exception $e) {
                // Fulltext might not be supported, skip
            }
        }
    }

    public function down(): void
    {
        // Drop the indexes we added
        
        if (Schema::hasTable('transactions')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->dropIndexIfExists('idx_transactions_team_status');
                $table->dropIndexIfExists('idx_transactions_payable');
                $table->dropIndexIfExists('idx_transactions_business_date');
                
                // Try to drop foreign keys safely
                try {
                    if (Schema::hasTable('unit_categories') && Schema::hasColumn('transactions', 'unit_category_id')) {
                        $table->dropForeignIfExists(['unit_category_id']);
                    }
                    if (Schema::hasTable('wallets') && Schema::hasColumn('transactions', 'wallet_id')) {
                        $table->dropForeignIfExists(['wallet_id']);
                    }
                } catch (\Exception $e) {
                    // Ignore if FK doesn't exist
                }
            });
        }
        
        if (Schema::hasTable('reservations')) {
            Schema::table('reservations', function (Blueprint $table) {
                $table->dropIndexIfExists('idx_reservations_unit_dates');
                $table->dropIndexIfExists('idx_reservations_status_dates');
                
                // Try to drop foreign keys safely
                try {
                    $table->dropForeignIfExists(['unit_id']);
                    $table->dropForeignIfExists(['company_id']);
                } catch (\Exception $e) {
                    // Ignore if FK doesn't exist
                }
            });
        }
        
        if (Schema::hasTable('service_logs')) {
            Schema::table('service_logs', function (Blueprint $table) {
                $table->dropIndexIfExists('idx_service_logs_team_type');
            });
        }
        
        if (Schema::hasTable('units')) {
            Schema::table('units', function (Blueprint $table) {
                $table->dropIndexIfExists('idx_units_status_category');
            });
        }
        
        // Remove soft delete indexes we added
        $softDeleteTables = [
            'reservations', 'transactions', 'service_logs', 'promissories', 
            'companies', 'company_notes', 'users', 'roles', 'units', 
            'guests', 'service_logs_notes', 'invoice_credit_notes'
        ];
        
        foreach ($softDeleteTables as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'deleted_at')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropIndexIfExists(['deleted_at']);
                });
            }
        }
        
        // Remove full-text indexes
        if (Schema::hasTable('guests')) {
            try {
                DB::statement("ALTER TABLE guests DROP INDEX IF EXISTS name");
            } catch (\Exception $e) {
                // Ignore if index doesn't exist
            }
        }
        
        if (Schema::hasTable('companies')) {
            try {
                DB::statement("ALTER TABLE companies DROP INDEX IF EXISTS name");
            } catch (\Exception $e) {
                // Ignore if index doesn't exist
            }
        }
    }
};