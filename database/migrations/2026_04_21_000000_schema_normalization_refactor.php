<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        // 1. RENAME TABLES
        if (Schema::hasTable('customer') && !Schema::hasTable('customers')) {
            Schema::rename('customer', 'customers');
        }

        // 2. DROP FOREIGN KEYS (Only if they exist)
        if (Schema::hasTable('action_types')) {
            try {
                Schema::table('action_types', function (Blueprint $table) {
                    $table->dropForeign('action_types_team_id_foreign');
                });
            } catch (\Exception $e) {}
        }
        
        if (Schema::hasTable('banks')) {
            try {
                Schema::table('banks', function (Blueprint $table) {
                    $table->dropForeign('banks_team_id_foreign');
                    $table->dropForeign('banks_created_by_foreign');
                });
            } catch (\Exception $e) {}
        }

        // 3. UPGRADE PRIMARY KEYS TO BIGINT
        foreach (['teams', 'users', 'action_types', 'banks', 'cities'] as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $table) {
                    $table->bigIncrements('id')->change();
                });
            }
        }

        // 4. UPGRADE FOREIGN KEY COLUMNS TO BIGINT
        $tablesWithFks = [
            'action_types' => ['team_id'],
            'banks' => ['team_id', 'created_by'],
            'reservations' => ['team_id', 'user_id', 'customer_id'],
            'reservation_invoices' => ['team_id', 'reservation_id'],
            'transactions' => ['team_id', 'reservation_id', 'user_id'],
            'service_logs' => ['team_id', 'transaction_id'],
        ];

        foreach ($tablesWithFks as $tableName => $columns) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($columns) {
                    foreach ($columns as $column) {
                        if (Schema::hasColumn($table->getTable(), $column)) {
                            $table->unsignedBigInteger($column)->nullable()->change();
                        }
                    }
                });
            }
        }

        // 5. RECREATE FOREIGN KEYS
        try {
            if (Schema::hasTable('action_types') && Schema::hasTable('teams')) {
                Schema::table('action_types', function (Blueprint $table) {
                    $table->foreign('team_id')->references('id')->on('teams')->onDelete('cascade');
                });
            }
            if (Schema::hasTable('banks') && Schema::hasTable('teams') && Schema::hasTable('users')) {
                Schema::table('banks', function (Blueprint $table) {
                    $table->foreign('team_id')->references('id')->on('teams')->onDelete('cascade');
                    $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
                });
            }
        } catch (\Exception $e) {}

        // 6. ADD SOFT DELETES
        $auditTables = ['reservation_invoices', 'action_types', 'transactions', 'service_logs', 'banks'];
        foreach ($auditTables as $table) {
            if (Schema::hasTable($table) && !Schema::hasColumn($table, 'deleted_at')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->softDeletes();
                });
            }
        }

        // 7. ADD NIGHT AUDIT FOUNDATION
        $dateSensitiveTables = ['reservations', 'reservation_invoices', 'transactions', 'service_logs'];
        foreach ($dateSensitiveTables as $table) {
            if (Schema::hasTable($table) && !Schema::hasColumn($table, 'business_date')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->date('business_date')->nullable()->after('id')->index();
                });
            }
        }

        // 8. ZATCA FIELDS
        if (Schema::hasTable('reservation_invoices')) {
            Schema::table('reservation_invoices', function (Blueprint $table) {
                if (!Schema::hasColumn('reservation_invoices', 'uuid')) {
                    $table->uuid('uuid')->nullable()->unique()->after('id');
                }
            });
        }

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        
        if (Schema::hasTable('customers') && !Schema::hasTable('customer')) {
            Schema::rename('customers', 'customer');
        }

        Schema::enableForeignKeyConstraints();
    }
};
