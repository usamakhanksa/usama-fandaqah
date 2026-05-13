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
        // First, drop the foreign key constraint for company_id if it exists
        $this->dropForeignKey('invoice_transfers', 'invoice_transfers_company_id_foreign');
        
        // Now modify the company_id column to be NOT NULL
        DB::statement('ALTER TABLE invoice_transfers MODIFY company_id BIGINT UNSIGNED NOT NULL');
        
        // Make sure promissory_id allows NULL
        DB::statement('ALTER TABLE invoice_transfers MODIFY promissory_id BIGINT UNSIGNED NULL');
        
        // Make sure transferred_by is NOT NULL and add index if not exists
        DB::statement('ALTER TABLE invoice_transfers MODIFY transferred_by BIGINT UNSIGNED NOT NULL');
        if (!Schema::hasIndex('invoice_transfers', 'invoice_transfers_transferred_by_index')) {
            Schema::table('invoice_transfers', function (Blueprint $table) {
                $table->index(['transferred_by'], 'invoice_transfers_transferred_by_index');
            });
        }
        
        // Ensure amount column matches expected precision
        DB::statement('ALTER TABLE invoice_transfers MODIFY amount DECIMAL(15, 2) NOT NULL');
        
        // Add created_at and updated_at if they don't exist
        if (!Schema::hasColumn('invoice_transfers', 'created_at')) {
            Schema::table('invoice_transfers', function (Blueprint $table) {
                $table->timestamp('created_at')->nullable()->after('notes');
            });
        }
        
        if (!Schema::hasColumn('invoice_transfers', 'updated_at')) {
            Schema::table('invoice_transfers', function (Blueprint $table) {
                $table->timestamp('updated_at')->nullable()->after('created_at');
            });
        }
        
        // Remove reservation_invoice_id if it exists
        if (Schema::hasColumn('invoice_transfers', 'reservation_invoice_id')) {
            Schema::table('invoice_transfers', function (Blueprint $table) {
                $table->dropColumn('reservation_invoice_id');
            });
        }
        
        // Remove transfer_amount if it exists (since we have amount)
        if (Schema::hasColumn('invoice_transfers', 'transfer_amount')) {
            Schema::table('invoice_transfers', function (Blueprint $table) {
                $table->dropColumn('transfer_amount');
            });
        }
        
        // Recreate the foreign key constraint
        Schema::table('invoice_transfers', function (Blueprint $table) {
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the foreign key constraint
        $this->dropForeignKey('invoice_transfers', 'invoice_transfers_company_id_foreign');
        
        // Revert changes - make company_id nullable again
        DB::statement('ALTER TABLE invoice_transfers MODIFY company_id BIGINT UNSIGNED NULL');
        DB::statement('ALTER TABLE invoice_transfers MODIFY promissory_id BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE invoice_transfers MODIFY amount DECIMAL(12, 4) NOT NULL');
        
        // Drop the index if it was created
        $indexExists = DB::select("SHOW INDEXES FROM invoice_transfers WHERE Key_name = ?", ['invoice_transfers_transferred_by_index']);
        if (!empty($indexExists)) {
            Schema::table('invoice_transfers', function (Blueprint $table) {
                $table->dropIndex(['transferred_by']);
            });
        }
        
        // Add back the removed columns if needed
        if (!Schema::hasColumn('invoice_transfers', 'reservation_invoice_id')) {
            Schema::table('invoice_transfers', function (Blueprint $table) {
                $table->unsignedBigInteger('reservation_invoice_id')->nullable()->after('company_id');
            });
        }
        
        if (!Schema::hasColumn('invoice_transfers', 'transfer_amount')) {
            Schema::table('invoice_transfers', function (Blueprint $table) {
                $table->decimal('transfer_amount', 12, 4)->after('promissory_id');
            });
        }
        
        // Recreate the original foreign key constraint
        Schema::table('invoice_transfers', function (Blueprint $table) {
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
        });
    }
    
    /**
     * Drop foreign key if it exists
     */
    private function dropForeignKey($tableName, $constraintName)
    {
        $sql = "SELECT COUNT(*) AS foreign_key_exists 
                FROM information_schema.TABLE_CONSTRAINTS 
                WHERE information_schema.TABLE_CONSTRAINTS.CONSTRAINT_TYPE = 'FOREIGN KEY' 
                AND information_schema.TABLE_CONSTRAINTS.CONSTRAINT_NAME = ?
                AND information_schema.TABLE_CONSTRAINTS.TABLE_SCHEMA = DATABASE()
                AND information_schema.TABLE_CONSTRAINTS.TABLE_NAME = ?";
        
        $result = DB::select($sql, [$constraintName, $tableName]);
        
        if ($result[0]->foreign_key_exists > 0) {
            DB::statement("ALTER TABLE `{$tableName}` DROP FOREIGN KEY `{$constraintName}`");
        }
    }
};