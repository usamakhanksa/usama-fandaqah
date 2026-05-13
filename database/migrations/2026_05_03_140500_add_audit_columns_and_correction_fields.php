<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add audit and correction columns to financial tables
        
        // Enhance transactions table with additional audit fields
        if (Schema::hasTable('transactions')) {
            if (!Schema::hasColumn('transactions', 'correction_reason')) {
                Schema::table('transactions', function (Blueprint $table) {
                    $table->text('correction_reason')->nullable()->after('correction_of_transaction_id');
                });
            }
            
            if (!Schema::hasColumn('transactions', 'reversed_at')) {
                Schema::table('transactions', function (Blueprint $table) {
                    $table->timestamp('reversed_at')->nullable()->after('correction_reason');
                });
            }
            
            if (!Schema::hasColumn('transactions', 'reversal_transaction_id')) {
                Schema::table('transactions', function (Blueprint $table) {
                    $table->unsignedBigInteger('reversal_transaction_id')->nullable()->after('reversed_at');
                    $table->foreign('reversal_transaction_id')->references('id')->on('transactions')->nullOnDelete();
                });
            }
        }
        
        // Enhance service_logs table with additional audit fields
        if (Schema::hasTable('service_logs')) {
            if (!Schema::hasColumn('service_logs', 'correction_reason')) {
                Schema::table('service_logs', function (Blueprint $table) {
                    $table->text('correction_reason')->nullable()->after('is_freezed');
                });
            }
            
            if (!Schema::hasColumn('service_logs', 'corrected_by')) {
                Schema::table('service_logs', function (Blueprint $table) {
                    $table->unsignedBigInteger('corrected_by')->nullable()->after('correction_reason');
                    $table->foreign('corrected_by')->references('id')->on('users')->nullOnDelete();
                });
            }
        }
        
        // Add audit fields to reservations table
        if (Schema::hasTable('reservations')) {
            if (!Schema::hasColumn('reservations', 'created_by')) {
                Schema::table('reservations', function (Blueprint $table) {
                    $table->unsignedBigInteger('created_by')->nullable()->after('id');
                    $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
                });
            }
            
            if (!Schema::hasColumn('reservations', 'updated_by')) {
                Schema::table('reservations', function (Blueprint $table) {
                    $table->unsignedBigInteger('updated_by')->nullable()->after('created_by');
                    $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
                });
            }
            
            if (!Schema::hasColumn('reservations', 'locked_by')) {
                Schema::table('reservations', function (Blueprint $table) {
                    $table->unsignedBigInteger('locked_by')->nullable()->after('updated_by');
                    $table->foreign('locked_by')->references('id')->on('users')->nullOnDelete();
                });
            }
            
            if (!Schema::hasColumn('reservations', 'locked_reason')) {
                Schema::table('reservations', function (Blueprint $table) {
                    $table->string('locked_reason', 255)->nullable()->after('locked_by');
                });
            }
        }
        
        // Add audit fields to invoices table if it exists
        if (Schema::hasTable('invoices')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->string('zatca_uuid', 36)->nullable()->after('status');
                $table->string('zatca_qr_code')->nullable()->after('zatca_uuid');
                $table->text('zatca_response')->nullable()->after('zatca_qr_code');
                $table->timestamp('reported_to_zatca_at')->nullable()->after('zatca_response');
                $table->boolean('is_zatca_reported')->default(false)->after('reported_to_zatca_at');
            });
        }
        
        // Add demo-safe flags where needed
        $tablesToAddDemoFlag = [
            'reservations',
            'transactions',
            'service_logs',
            'promissories',
            'units',
            'guests',
            'companies'
        ];
        
        foreach ($tablesToAddDemoFlag as $tableName) {
            if (Schema::hasTable($tableName) && !Schema::hasColumn($tableName, 'is_demo')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->boolean('is_demo')->default(false)->after('created_at');
                });
            }
        }
        
        // Add business_date to transactions if not already present
        if (Schema::hasTable('transactions') && !Schema::hasColumn('transactions', 'business_date')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->date('business_date')->nullable()->after('confirmed');
                $table->index('business_date');
            });
        }
        
        // Add business_date to promissories if not already present
        if (Schema::hasTable('promissories') && !Schema::hasColumn('promissories', 'business_date')) {
            Schema::table('promissories', function (Blueprint $table) {
                $table->date('business_date')->nullable()->after('due_date');
                $table->index('business_date');
            });
        }
        
        // Add audit fields to cashier_shifts
        if (Schema::hasTable('cashier_shifts')) {
            if (!Schema::hasColumn('cashier_shifts', 'opened_by')) {
                Schema::table('cashier_shifts', function (Blueprint $table) {
                    $table->unsignedBigInteger('opened_by')->nullable()->after('user_id');
                    $table->foreign('opened_by')->references('id')->on('users')->nullOnDelete();
                });
            }
            
            if (!Schema::hasColumn('cashier_shifts', 'closed_by')) {
                Schema::table('cashier_shifts', function (Blueprint $table) {
                    $table->unsignedBigInteger('closed_by')->nullable()->after('opened_by');
                    $table->foreign('closed_by')->references('id')->on('users')->nullOnDelete();
                });
            }
        }
    }

    public function down(): void
    {
        // Remove audit and correction columns added
        
        // From transactions table
        if (Schema::hasTable('transactions')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->dropForeign(['reversal_transaction_id']);
                $table->dropColumn(['correction_reason', 'reversed_at', 'reversal_transaction_id']);
            });
        }
        
        // From service_logs table
        if (Schema::hasTable('service_logs')) {
            Schema::table('service_logs', function (Blueprint $table) {
                $table->dropForeign(['corrected_by']);
                $table->dropColumn(['correction_reason', 'corrected_by']);
            });
        }
        
        // From reservations table
        if (Schema::hasTable('reservations')) {
            Schema::table('reservations', function (Blueprint $table) {
                $table->dropForeign(['created_by']);
                $table->dropForeign(['updated_by']);
                $table->dropForeign(['locked_by']);
                $table->dropColumn(['created_by', 'updated_by', 'locked_by', 'locked_reason']);
            });
        }
        
        // From invoices table
        if (Schema::hasTable('invoices')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->dropColumn(['zatca_uuid', 'zatca_qr_code', 'zatca_response', 'reported_to_zatca_at', 'is_zatca_reported']);
            });
        }
        
        // From cashier_shifts table
        if (Schema::hasTable('cashier_shifts')) {
            Schema::table('cashier_shifts', function (Blueprint $table) {
                $table->dropForeign(['opened_by']);
                $table->dropForeign(['closed_by']);
                $table->dropColumn(['opened_by', 'closed_by']);
            });
        }
        
        // Remove demo flags
        $tablesToRemoveDemoFlag = [
            'reservations',
            'transactions',
            'service_logs',
            'promissories',
            'units',
            'guests',
            'companies'
        ];
        
        foreach ($tablesToRemoveDemoFlag as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'is_demo')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropColumn('is_demo');
                });
            }
        }
        
        // Remove business_date columns
        if (Schema::hasTable('transactions') && Schema::hasColumn('transactions', 'business_date')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->dropColumn('business_date');
            });
        }
        
        if (Schema::hasTable('promissories') && Schema::hasColumn('promissories', 'business_date')) {
            Schema::table('promissories', function (Blueprint $table) {
                $table->dropColumn('business_date');
            });
        }
    }
};