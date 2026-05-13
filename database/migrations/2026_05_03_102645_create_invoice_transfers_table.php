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
        // Check if table exists
        if (!Schema::hasTable('invoice_transfers')) {
            Schema::create('invoice_transfers', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('team_id')->index();
                $table->unsignedBigInteger('reservation_id')->index();
                $table->unsignedBigInteger('invoice_id')->index()->nullable(); // New system might not have invoice model yet
                $table->unsignedBigInteger('company_id')->index();
                $table->unsignedBigInteger('promissory_id')->index()->nullable();
                
                $table->decimal('amount', 15, 2);
                $table->unsignedBigInteger('transferred_by')->index();
                $table->timestamp('transferred_at');
                $table->text('notes')->nullable();
                
                $table->timestamps();
                $table->softDeletes();
            });
        } else {
            // Table exists, modify columns to match expected structure
            // Handle the case where both amount and transfer_amount columns exist
            
            // If both amount and transfer_amount exist, consolidate them
            $columns = Schema::getColumnListing('invoice_transfers');
            
            if (in_array('transfer_amount', $columns) && in_array('amount', $columns)) {
                // Copy data from transfer_amount to amount if amount is null
                DB::statement('UPDATE invoice_transfers SET amount = transfer_amount WHERE amount IS NULL OR amount = 0');
                
                // Then drop the transfer_amount column
                Schema::table('invoice_transfers', function (Blueprint $table) {
                    $table->dropColumn('transfer_amount');
                });
            } elseif (in_array('transfer_amount', $columns) && !in_array('amount', $columns)) {
                // Rename transfer_amount to amount
                Schema::table('invoice_transfers', function (Blueprint $table) {
                    $table->renameColumn('transfer_amount', 'amount');
                });
            }
            
            // Ensure promissory_id allows NULL
            if (Schema::hasColumn('invoice_transfers', 'promissory_id')) {
                DB::statement('ALTER TABLE invoice_transfers MODIFY promissory_id BIGINT UNSIGNED NULL');
            }
            
            // Make sure transferred_by is NOT NULL and indexed
            if (Schema::hasColumn('invoice_transfers', 'transferred_by')) {
                DB::statement('ALTER TABLE invoice_transfers MODIFY transferred_by BIGINT UNSIGNED NOT NULL');
                if (!Schema::hasIndex('invoice_transfers', 'invoice_transfers_transferred_by_index')) {
                    Schema::table('invoice_transfers', function (Blueprint $table) {
                        $table->index(['transferred_by'], 'invoice_transfers_transferred_by_index');
                    });
                }
            } else {
                Schema::table('invoice_transfers', function (Blueprint $table) {
                    $table->unsignedBigInteger('transferred_by')->index()->after('amount');
                });
            }
            
            // Ensure amount column matches expected precision
            if (Schema::hasColumn('invoice_transfers', 'amount')) {
                DB::statement('ALTER TABLE invoice_transfers MODIFY amount DECIMAL(15, 2)');
            } else {
                Schema::table('invoice_transfers', function (Blueprint $table) {
                    $table->decimal('amount', 15, 2)->after('promissory_id');
                });
            }
            
            // Add invoice_id column if not exists
            if (!Schema::hasColumn('invoice_transfers', 'invoice_id')) {
                Schema::table('invoice_transfers', function (Blueprint $table) {
                    $table->unsignedBigInteger('invoice_id')->index()->nullable()->after('reservation_id');
                });
            }
            
            // Add timestamps if they don't exist
            if (!Schema::hasColumn('invoice_transfers', 'created_at')) {
                Schema::table('invoice_transfers', function (Blueprint $table) {
                    $table->timestamp('created_at')->nullable();
                });
            }
            
            if (!Schema::hasColumn('invoice_transfers', 'updated_at')) {
                Schema::table('invoice_transfers', function (Blueprint $table) {
                    $table->timestamp('updated_at')->nullable();
                });
            }
            
            // Add soft deletes if not exists
            if (!Schema::hasColumn('invoice_transfers', 'deleted_at')) {
                Schema::table('invoice_transfers', function (Blueprint $table) {
                    $table->timestamp('deleted_at')->nullable();
                });
            }
            
            // Handle reservation_invoice_id - rename or remove if needed
            if (Schema::hasColumn('invoice_transfers', 'reservation_invoice_id')) {
                Schema::table('invoice_transfers', function (Blueprint $table) {
                    $table->dropColumn('reservation_invoice_id');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_transfers');
    }
};