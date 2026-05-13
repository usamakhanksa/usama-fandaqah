<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add ZATCA compliance fields to transactions table
        if (Schema::hasTable('transactions') && !Schema::hasColumn('transactions', 'zatca_status')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->enum('zatca_status', ['pending', 'submitted', 'accepted', 'rejected'])->default('pending')->after('confirmed');
                $table->string('zatca_invoice_id', 100)->nullable()->after('zatca_status');
                $table->string('zatca_uuid', 36)->nullable()->after('zatca_invoice_id');
                $table->string('zatca_qr_code', 500)->nullable()->after('zatca_uuid');
                $table->text('zatca_response')->nullable()->after('zatca_qr_code');
                $table->timestamp('zatca_submitted_at')->nullable()->after('zatca_response');
                $table->timestamp('zatca_accepted_at')->nullable()->after('zatca_submitted_at');
                $table->index(['zatca_status', 'team_id'], 'idx_transactions_zatca_team');
            });
        }
        
        // Add ZATCA compliance fields to service_logs table
        if (Schema::hasTable('service_logs') && !Schema::hasColumn('service_logs', 'zatca_status')) {
            Schema::table('service_logs', function (Blueprint $table) {
                $table->enum('zatca_status', ['pending', 'submitted', 'accepted', 'rejected'])->default('pending')->after('is_freezed');
                $table->string('zatca_invoice_id', 100)->nullable()->after('zatca_status');
                $table->string('zatca_uuid', 36)->nullable()->after('zatca_invoice_id');
                $table->string('zatca_qr_code', 500)->nullable()->after('zatca_uuid');
                $table->text('zatca_response')->nullable()->after('zatca_qr_code');
                $table->timestamp('zatca_submitted_at')->nullable()->after('zatca_response');
                $table->timestamp('zatca_accepted_at')->nullable()->after('zatca_submitted_at');
                $table->index(['zatca_status', 'team_id'], 'idx_service_logs_zatca_team');
            });
        }
        
        // Add Shomoos verification fields to guests table
        if (Schema::hasTable('guests') && !Schema::hasColumn('guests', 'shomoos_verified_at')) {
            Schema::table('guests', function (Blueprint $table) {
                $table->timestamp('shomoos_verified_at')->nullable()->after('id_number');
                $table->string('shomoos_reference', 100)->nullable()->after('shomoos_verified_at');
                $table->string('shomoos_status', 50)->nullable()->after('shomoos_reference');
                $table->json('shomoos_response')->nullable()->after('shomoos_status');
                $table->index(['shomoos_status', 'nationality'], 'idx_guests_shomoos_nationality');
            });
        }
        
        // Add Shomoos verification fields to reservations table
        if (Schema::hasTable('reservations') && !Schema::hasColumn('reservations', 'shomoos_verification_attempts')) {
            Schema::table('reservations', function (Blueprint $table) {
                $table->integer('shomoos_verification_attempts')->default(0)->after('shomoos_verification_status');
                $table->timestamp('shomoos_last_attempt_at')->nullable()->after('shomoos_verification_attempts');
                $table->text('shomoos_verification_error')->nullable()->after('shomoos_last_attempt_at');
            });
        }
        
        // Add VAT and tax compliance fields to transactions table
        if (Schema::hasTable('transactions')) {
            if (!Schema::hasColumn('transactions', 'vat_calculation_basis')) {
                Schema::table('transactions', function (Blueprint $table) {
                    $table->string('vat_calculation_basis', 20)->nullable()->after('tax_amount'); // inclusive, exclusive
                });
            }
            
            if (!Schema::hasColumn('transactions', 'vat_category')) {
                Schema::table('transactions', function (Blueprint $table) {
                    $table->string('vat_category', 20)->nullable()->after('vat_calculation_basis'); // standard, zero-rated, exempt
                });
            }
        }
        
        // Add tourism tax fields to transactions table
        if (Schema::hasTable('transactions')) {
            if (!Schema::hasColumn('transactions', 'tourism_tax_amount')) {
                Schema::table('transactions', function (Blueprint $table) {
                    $table->decimal('tourism_tax_amount', 12, 2)->default(0)->after('vat_category');
                });
            }
            
            if (!Schema::hasColumn('transactions', 'accommodation_tax_amount')) {
                Schema::table('transactions', function (Blueprint $table) {
                    $table->decimal('accommodation_tax_amount', 12, 2)->default(0)->after('tourism_tax_amount');
                });
            }
        }
        
        // Add SAR currency fields to important tables if not already decimal
        if (Schema::hasTable('transactions')) {
            // Change amount fields to be more precise for SAR currency handling
            Schema::table('transactions', function (Blueprint $table) {
                $table->decimal('amount', 15, 2)->change();
                $table->decimal('amount_without_tax', 15, 2)->default(0)->change();
                $table->decimal('tax_amount', 15, 2)->default(0)->change();
            });
        }
        
        // Add address fields to teams table for ZATCA compliance
        if (Schema::hasTable('teams')) {
            if (!Schema::hasColumn('teams', 'street_name')) {
                Schema::table('teams', function (Blueprint $table) {
                    $table->string('street_name', 255)->nullable()->after('currency');
                });
            }
            
            if (!Schema::hasColumn('teams', 'building_number')) {
                Schema::table('teams', function (Blueprint $table) {
                    $table->string('building_number', 50)->nullable()->after('street_name');
                });
            }
            
            if (!Schema::hasColumn('teams', 'city')) {
                Schema::table('teams', function (Blueprint $table) {
                    $table->string('city', 100)->nullable()->after('building_number');
                });
            }
            
            if (!Schema::hasColumn('teams', 'postal_code')) {
                Schema::table('teams', function (Blueprint $table) {
                    $table->string('postal_code', 20)->nullable()->after('city');
                });
            }
            
            if (!Schema::hasColumn('teams', 'district')) {
                Schema::table('teams', function (Blueprint $table) {
                    $table->string('district', 100)->nullable()->after('postal_code');
                });
            }
            
            if (!Schema::hasColumn('teams', 'country_code')) {
                Schema::table('teams', function (Blueprint $table) {
                    $table->string('country_code', 2)->default('SA')->after('district'); // Saudi Arabia by default
                });
            }
            
            if (!Schema::hasColumn('teams', 'tax_number')) {
                Schema::table('teams', function (Blueprint $table) {
                    $table->string('tax_number', 100)->nullable()->after('country_code');
                });
            }
        }
        
        // Add timezone field to teams for Saudi timezone
        if (Schema::hasTable('teams') && !Schema::hasColumn('teams', 'timezone')) {
            Schema::table('teams', function (Blueprint $table) {
                $table->string('timezone', 50)->default('Asia/Riyadh')->after('tax_number');
            });
        }
        
        // Add Hijri date support fields where needed
        if (Schema::hasTable('reservations')) {
            if (!Schema::hasColumn('reservations', 'check_in_hijri')) {
                Schema::table('reservations', function (Blueprint $table) {
                    $table->string('check_in_hijri', 20)->nullable()->after('check_in');
                });
            }
            
            if (!Schema::hasColumn('reservations', 'check_out_hijri')) {
                Schema::table('reservations', function (Blueprint $table) {
                    $table->string('check_out_hijri', 20)->nullable()->after('check_in_hijri');
                });
            }
        }
    }

    public function down(): void
    {
        // Remove ZATCA compliance fields from transactions table
        if (Schema::hasTable('transactions') && Schema::hasColumn('transactions', 'zatca_status')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->dropIndexIfExists('idx_transactions_zatca_team');
                $table->dropColumn([
                    'zatca_status', 'zatca_invoice_id', 'zatca_uuid', 
                    'zatca_qr_code', 'zatca_response', 'zatca_submitted_at', 
                    'zatca_accepted_at'
                ]);
            });
        }
        
        // Remove ZATCA compliance fields from service_logs table
        if (Schema::hasTable('service_logs') && Schema::hasColumn('service_logs', 'zatca_status')) {
            Schema::table('service_logs', function (Blueprint $table) {
                $table->dropIndexIfExists('idx_service_logs_zatca_team');
                $table->dropColumn([
                    'zatca_status', 'zatca_invoice_id', 'zatca_uuid', 
                    'zatca_qr_code', 'zatca_response', 'zatca_submitted_at', 
                    'zatca_accepted_at'
                ]);
            });
        }
        
        // Remove Shomoos verification fields from guests table
        if (Schema::hasTable('guests') && Schema::hasColumn('guests', 'shomoos_verified_at')) {
            Schema::table('guests', function (Blueprint $table) {
                $table->dropIndexIfExists('idx_guests_shomoos_nationality');
                $table->dropColumn([
                    'shomoos_verified_at', 'shomoos_reference', 
                    'shomoos_status', 'shomoos_response'
                ]);
            });
        }
        
        // Remove Shomoos verification fields from reservations table
        if (Schema::hasTable('reservations') && Schema::hasColumn('reservations', 'shomoos_verification_attempts')) {
            Schema::table('reservations', function (Blueprint $table) {
                $table->dropColumn([
                    'shomoos_verification_attempts', 
                    'shomoos_last_attempt_at', 
                    'shomoos_verification_error'
                ]);
            });
        }
        
        // Remove VAT and tax compliance fields
        if (Schema::hasTable('transactions')) {
            if (Schema::hasColumn('transactions', 'vat_calculation_basis')) {
                Schema::table('transactions', function (Blueprint $table) {
                    $table->dropColumn(['vat_calculation_basis', 'vat_category']);
                });
            }
            
            if (Schema::hasColumn('transactions', 'tourism_tax_amount')) {
                Schema::table('transactions', function (Blueprint $table) {
                    $table->dropColumn(['tourism_tax_amount', 'accommodation_tax_amount']);
                });
            }
        }
        
        // Remove address fields from teams table
        if (Schema::hasTable('teams')) {
            if (Schema::hasColumn('teams', 'street_name')) {
                Schema::table('teams', function (Blueprint $table) {
                    $table->dropColumn([
                        'street_name', 'building_number', 'city', 
                        'postal_code', 'district', 'country_code', 
                        'tax_number', 'timezone'
                    ]);
                });
            }
        }
        
        // Remove Hijri date fields from reservations
        if (Schema::hasTable('reservations')) {
            if (Schema::hasColumn('reservations', 'check_in_hijri')) {
                Schema::table('reservations', function (Blueprint $table) {
                    $table->dropColumn(['check_in_hijri', 'check_out_hijri']);
                });
            }
        }
    }
};