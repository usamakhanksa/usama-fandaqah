<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('commission_payments', function (Blueprint $table) {
            if (!Schema::hasColumn('commission_payments', 'reservation_id')) {
                $table->unsignedBigInteger('reservation_id')->index()->after('source_id');
            }
            if (!Schema::hasColumn('commission_payments', 'period_from')) {
                $table->date('period_from')->nullable()->after('reservation_id');
            }
            if (!Schema::hasColumn('commission_payments', 'period_to')) {
                $table->date('period_to')->nullable()->after('period_from');
            }
            if (!Schema::hasColumn('commission_payments', 'room_revenue_base')) {
                $table->decimal('room_revenue_base', 15, 2)->default(0)->after('period_to');
            }
            if (!Schema::hasColumn('commission_payments', 'commission_rate')) {
                $table->decimal('commission_rate', 8, 2)->default(0)->after('room_revenue_base');
            }
            if (!Schema::hasColumn('commission_payments', 'commission_type')) {
                $table->string('commission_type')->default('percentage')->after('commission_rate');
            }
            if (!Schema::hasColumn('commission_payments', 'commission_amount')) {
                // Rename amount to commission_amount if it exists
                if (Schema::hasColumn('commission_payments', 'amount')) {
                    $table->renameColumn('amount', 'commission_amount');
                } else {
                    $table->decimal('commission_amount', 15, 2)->default(0)->after('commission_type');
                }
            }
            if (!Schema::hasColumn('commission_payments', 'status')) {
                $table->string('status')->default('pending')->index()->after('commission_amount');
            }
            if (!Schema::hasColumn('commission_payments', 'approved_by')) {
                $table->unsignedBigInteger('approved_by')->nullable()->after('status');
            }
            if (!Schema::hasColumn('commission_payments', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('approved_by');
            }
            if (!Schema::hasColumn('commission_payments', 'payment_reference')) {
                if (Schema::hasColumn('commission_payments', 'reference')) {
                    $table->renameColumn('reference', 'payment_reference');
                } else {
                    $table->string('payment_reference')->nullable()->after('paid_at');
                }
            }
            if (!Schema::hasColumn('commission_payments', 'notes')) {
                $table->text('notes')->nullable()->after('payment_reference');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('commission_payments', function (Blueprint $table) {
            //
        });
    }
};
