<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── 1. night_audit_occupancy_snapshot (must come before night_audit_log) ──
        if (!Schema::hasTable('night_audit_occupancy_snapshot')) {
            Schema::create('night_audit_occupancy_snapshot', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('team_id');
                $t->date('business_date');
                $t->tinyInteger('run_number');
                $t->boolean('is_final')->default(true);
                $t->unsignedBigInteger('total_rooms');
                $t->unsignedBigInteger('rooms_available');
                $t->unsignedBigInteger('rooms_occupied');
                $t->unsignedBigInteger('rooms_cleaning');
                $t->unsignedBigInteger('rooms_maintenance');
                $t->unsignedBigInteger('rooms_complimentary');
                $t->unsignedBigInteger('rooms_house_use');
                $t->unsignedBigInteger('rooms_day_use');
                $t->boolean('is_backfill')->default(false);
                $t->decimal('occupancy_pct', 5, 2);
                $t->decimal('adr', 10, 2);
                $t->decimal('revpar', 10, 2);
                $t->unsignedBigInteger('arrivals_count');
                $t->unsignedBigInteger('departures_count');
                $t->unsignedBigInteger('stayovers_count');
                $t->unsignedBigInteger('noshows_count');
                $t->unsignedBigInteger('cancellations_count');
                $t->unsignedBigInteger('new_bookings_count');
                $t->decimal('room_revenue', 14, 2);
                $t->decimal('room_revenue_complimentary', 14, 2);
                $t->decimal('service_revenue', 14, 2);
                $t->decimal('noshow_revenue', 14, 2);
                $t->decimal('adjustment_revenue', 14, 2);
                $t->decimal('rebate_amount', 14, 2);
                $t->decimal('total_revenue', 14, 2);
                $t->decimal('vat_total', 14, 2);
                $t->decimal('ewa_total', 14, 2);
                $t->decimal('total_deposits_collected', 14, 2);
                $t->decimal('total_promissory_created', 14, 2);
                $t->decimal('total_promissory_collected', 14, 2);
                $t->decimal('outstanding_promissory_balance', 14, 2);
                $t->unsignedBigInteger('adults_count');
                $t->unsignedBigInteger('children_count');
                $t->timestamp('created_at')->useCurrent();
                $t->foreign('team_id')->references('id')->on('teams')->cascadeOnDelete();
            });
        }

        // ── 2. night_audit_log ──
        if (!Schema::hasTable('night_audit_log')) {
            Schema::create('night_audit_log', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('team_id');
                $t->date('business_date');
                $t->tinyInteger('run_number')->default(1);
                $t->enum('status', ['running', 'completed', 'failed', 'cancelled']);
                $t->enum('triggered_by', ['auto', 'manual']);
                $t->unsignedBigInteger('triggered_by_user_id')->nullable();
                $t->timestamp('started_at')->nullable();
                $t->timestamp('completed_at')->nullable();
                $t->json('steps_completed')->nullable();
                $t->json('steps_failed')->nullable();
                $t->unsignedBigInteger('noshows_flagged')->default(0);
                $t->unsignedBigInteger('noshow_charges_posted')->default(0);
                $t->unsignedBigInteger('transactions_frozen')->default(0);
                $t->unsignedBigInteger('occupancy_snapshot_id')->nullable();
                $t->unsignedBigInteger('rerun_of_log_id')->nullable();
                $t->timestamp('dw_synced_at')->nullable();
                $t->text('notes')->nullable();
                $t->unique(['team_id', 'business_date', 'run_number'], 'uk_night_audit_run');
                $t->foreign('team_id')->references('id')->on('teams')->cascadeOnDelete();
                $t->foreign('occupancy_snapshot_id')->references('id')->on('night_audit_occupancy_snapshot');
            });
        }

        // ── 3. no_show_charge_rules ──
        if (!Schema::hasTable('no_show_charge_rules')) {
            Schema::create('no_show_charge_rules', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('team_id');
                $t->string('name', 100);
                $t->date('start_date');
                $t->date('end_date');
                $t->enum('charge_type', ['fixed', 'percentage']);
                $t->decimal('charge_amount', 10, 2)->default(0);
                $t->enum('applies_to', ['all', 'daily', 'monthly']);
                $t->boolean('is_active')->default(true);
                $t->unsignedBigInteger('created_by')->nullable();
                $t->timestamps();
                $t->softDeletes();
                $t->foreign('team_id')->references('id')->on('teams')->cascadeOnDelete();
            });
        }

        // ── 4. night_audit_noshow_log ──
        if (!Schema::hasTable('night_audit_noshow_log')) {
            Schema::create('night_audit_noshow_log', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('night_audit_log_id');
                $t->unsignedBigInteger('reservation_id')->nullable();
                $t->unsignedBigInteger('team_id')->nullable();
                $t->date('business_date')->nullable();
                $t->date('original_date_in')->nullable();
                $t->decimal('charge_amount', 10, 2)->default(0);
                $t->unsignedBigInteger('charge_transaction_id')->nullable();
                $t->unsignedBigInteger('rule_id')->nullable();
                $t->enum('action_taken', ['flagged_only', 'charged_and_cancelled', 'cancelled_only']);
                $t->timestamp('created_at')->useCurrent();
                $t->foreign('night_audit_log_id')->references('id')->on('night_audit_log')->cascadeOnDelete();
                $t->foreign('reservation_id')->references('id')->on('reservations')->nullOnDelete();
                $t->foreign('charge_transaction_id')->references('id')->on('transactions')->nullOnDelete();
                $t->foreign('rule_id')->references('id')->on('no_show_charge_rules')->nullOnDelete();
            });
        }

        // ── 5. night_audit_snapshot_queue ──
        if (!Schema::hasTable('night_audit_snapshot_queue')) {
            Schema::create('night_audit_snapshot_queue', function (Blueprint $t) {
                $t->id();
                $t->foreignId('snapshot_id')->constrained('night_audit_occupancy_snapshot')->cascadeOnDelete();
                $t->unsignedBigInteger('team_id');
                $t->date('business_date');
                $t->enum('status', ['pending', 'inprogress', 'done', 'failed'])->default('pending');
                $t->timestamp('queued_at')->useCurrent();
                $t->timestamp('picked_up_at')->nullable();
                $t->timestamp('completed_at')->nullable();
                $t->text('error_message')->nullable();
                $t->foreign('team_id')->references('id')->on('teams')->cascadeOnDelete();
            });
        }

        // ── 6. reservation_audit_locks ──
        if (!Schema::hasTable('reservation_audit_locks')) {
            Schema::create('reservation_audit_locks', function (Blueprint $t) {
                $t->unsignedBigInteger('reservation_id')->primary();
                $t->date('locked_from_date');
                $t->unsignedBigInteger('locked_by_audit');
                $t->unsignedBigInteger('team_id');
                $t->timestamp('created_at')->useCurrent();
                $t->foreign('reservation_id')->references('id')->on('reservations');
                $t->foreign('locked_by_audit')->references('id')->on('night_audit_log');
                $t->foreign('team_id')->references('id')->on('teams')->cascadeOnDelete();
            });
        }

        // ── 7. business_date_transactions ──
        if (!Schema::hasTable('business_date_transactions')) {
            Schema::create('business_date_transactions', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('team_id')->index();
                $t->unsignedBigInteger('transaction_id')->index();
                $t->date('business_date')->index();
                $t->timestamps();
                $t->unique('transaction_id', 'uq_bdt_transaction');
                $t->index(['team_id', 'business_date'], 'idx_bdt_team_date');
            });
        }

        // ── 8. room_status_log ──
        if (!Schema::hasTable('room_status_log')) {
            Schema::create('room_status_log', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('unit_id');
                $t->unsignedBigInteger('team_id');
                $t->tinyInteger('from_status');
                $t->tinyInteger('to_status');
                $t->unsignedBigInteger('changed_by')->nullable();
                $t->string('change_reason', 100)->nullable();
                $t->string('reference_type', 50)->nullable();
                $t->unsignedBigInteger('reference_id')->nullable();
                $t->timestamp('changed_at')->useCurrent();
                $t->foreign('unit_id')->references('id')->on('units');
                $t->foreign('team_id')->references('id')->on('teams')->cascadeOnDelete();
            });
        }
        if (!Schema::hasTable('early_late_charge_configs')) {
            Schema::create('early_late_charge_configs', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('team_id');
                $t->enum('charge_type', ['early_checkin', 'late_checkout']);
                $t->tinyInteger('tier_from_hour');
                $t->tinyInteger('tier_to_hour');
                $t->enum('rate_type', ['fixed', 'percentage_first_night', 'percentage_nightly_rate']);
                $t->decimal('rate_amount', 8, 2);
                $t->enum('applies_to', ['all', 'daily', 'monthly']);
                $t->boolean('is_active')->default(true);
                $t->unsignedBigInteger('created_by')->nullable();
                $t->timestamps();
                $t->foreign('team_id')->references('id')->on('teams')->cascadeOnDelete();
            });
        }

        // ── 10. checkout_balance_transfers ──
        if (!Schema::hasTable('checkout_balance_transfers')) {
            Schema::create('checkout_balance_transfers', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('team_id');
                $t->unsignedBigInteger('reservation_id');
                $t->enum('transfer_type', ['to_promissory', 'to_credit_note', 'refunded', 'waived']);
                $t->decimal('amount', 12, 2);
                $t->unsignedBigInteger('promissory_id')->nullable();
                $t->unsignedBigInteger('refund_transaction_id')->nullable();
                $t->timestamp('transferred_at')->useCurrent();
                $t->unsignedBigInteger('transferred_by');
                $t->text('notes')->nullable();
                $t->foreign('reservation_id')->references('id')->on('reservations');
                $t->foreign('team_id')->references('id')->on('teams')->cascadeOnDelete();
            });
        }

        // ── 11. invoice_credit_notes ──
        if (!Schema::hasTable('invoice_credit_notes')) {
            Schema::create('invoice_credit_notes', function (Blueprint $t) {
                $t->id();
                $t->string('number', 191)->index();
                $t->unsignedBigInteger('reservation_invoice_id')->nullable()->index();
                $t->unsignedBigInteger('team_id')->nullable()->index();
                $t->unsignedBigInteger('created_by')->nullable()->index();
                $t->timestamps();
                $t->softDeletes();
                $t->longText('is_reported_to_zatca')->nullable();
                $t->json('payload')->nullable();
                $t->unsignedBigInteger('service_log_id')->nullable();
            });
        }

        // ── 12. invoice_transfers ──
        if (!Schema::hasTable('invoice_transfers')) {
            Schema::create('invoice_transfers', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('team_id');
                $t->unsignedBigInteger('reservation_id');
                $t->unsignedBigInteger('company_id')->nullable();
                $t->unsignedBigInteger('reservation_invoice_id')->nullable();
                $t->unsignedBigInteger('promissory_id');
                $t->decimal('transfer_amount', 12, 4);
                $t->timestamp('transferred_at')->useCurrent();
                $t->unsignedBigInteger('transferred_by');
                $t->text('notes')->nullable();
                $t->foreign('reservation_id')->references('id')->on('reservations');
                $t->foreign('company_id')->references('id')->on('companies');
                $t->foreign('promissory_id')->references('id')->on('promissories');
                $t->foreign('team_id')->references('id')->on('teams')->cascadeOnDelete();
            });
        }

        // ── 13. commission_payments ──
        if (!Schema::hasTable('commission_payments')) {
            Schema::create('commission_payments', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('team_id');
                $t->unsignedBigInteger('source_id');
                $t->unsignedBigInteger('reservation_id')->nullable();
                $t->date('period_from');
                $t->date('period_to');
                $t->decimal('room_revenue_base', 12, 2);
                $t->decimal('commission_rate', 5, 2);
                $t->enum('commission_type', ['percentage', 'fixed']);
                $t->decimal('commission_amount', 12, 2);
                $t->enum('status', ['pending', 'approved', 'paid', 'cancelled'])->default('pending');
                $t->unsignedBigInteger('approved_by')->nullable();
                $t->timestamp('paid_at')->nullable();
                $t->string('payment_reference', 100)->nullable();
                $t->timestamps();
                $t->foreign('source_id')->references('id')->on('sources')->cascadeOnDelete();
                $t->foreign('reservation_id')->references('id')->on('reservations')->nullOnDelete();
                $t->foreign('team_id')->references('id')->on('teams')->cascadeOnDelete();
            });
        }

        // ── ALTER: teams – add night audit columns ──
        Schema::table('teams', function (Blueprint $t) {
            if (!Schema::hasColumn('teams', 'business_date'))
                $t->date('business_date')->nullable()->after('slug')->comment('Current open business date');
            if (!Schema::hasColumn('teams', 'night_audit_cutoff_time'))
                $t->time('night_audit_cutoff_time')->default('03:00:00')->after('business_date');
            if (!Schema::hasColumn('teams', 'night_audit_auto_run_time'))
                $t->time('night_audit_auto_run_time')->default('06:00:00')->after('night_audit_cutoff_time');
            if (!Schema::hasColumn('teams', 'night_audit_auto_enabled'))
                $t->boolean('night_audit_auto_enabled')->default(true)->after('night_audit_auto_run_time');
            if (!Schema::hasColumn('teams', 'last_night_audit_at'))
                $t->timestamp('last_night_audit_at')->nullable()->after('night_audit_auto_enabled');
            if (!Schema::hasColumn('teams', 'last_night_audit_by'))
                $t->unsignedBigInteger('last_night_audit_by')->nullable()->after('last_night_audit_at');
            if (!Schema::hasColumn('teams', 'currency'))
                $t->string('currency', 4)->default('SAR')->after('name');
        });

        // ── ALTER: service_categories – add rev_type ──
        if (Schema::hasTable('service_categories') && !Schema::hasColumn('service_categories', 'rev_type')) {
            Schema::table('service_categories', function (Blueprint $t) {
                $t->string('rev_type', 30)->nullable()->after('deleted_at')
                    ->comment('POS | room | F&B | other');
            });
        }

        // ── ALTER: reservations – add primary_payment_method ──
        if (!Schema::hasColumn('reservations', 'primary_payment_method')) {
            Schema::table('reservations', function (Blueprint $t) {
                $t->string('primary_payment_method', 30)->nullable()
                    ->comment('Cash | Mada | Visa | BankTransfer | Promissory');
            });
        }
    }

    public function down(): void
    {
        // Reverse ALTER tables
        if (Schema::hasColumn('reservations', 'primary_payment_method')) {
            Schema::table('reservations', function (Blueprint $t) {
                $t->dropColumn('primary_payment_method');
            });
        }
        if (Schema::hasColumn('service_categories', 'rev_type')) {
            Schema::table('service_categories', function (Blueprint $t) {
                $t->dropColumn('rev_type');
            });
        }
        Schema::table('teams', function (Blueprint $t) {
            $cols = ['business_date','night_audit_cutoff_time','night_audit_auto_run_time',
                     'night_audit_auto_enabled','last_night_audit_at','last_night_audit_by','currency'];
            $drop = [];
            foreach ($cols as $c) { if (Schema::hasColumn('teams', $c)) $drop[] = $c; }
            if ($drop) $t->dropColumn($drop);
        });

        // Drop created tables in reverse FK order
        Schema::dropIfExists('commission_payments');
        Schema::dropIfExists('invoice_transfers');
        Schema::dropIfExists('invoice_credit_notes');
        Schema::dropIfExists('checkout_balance_transfers');
        Schema::dropIfExists('early_late_charge_configs');
        Schema::dropIfExists('room_status_log');
        Schema::dropIfExists('business_date_transactions');
        Schema::dropIfExists('reservation_audit_locks');
        Schema::dropIfExists('night_audit_snapshot_queue');
        Schema::dropIfExists('night_audit_noshow_log');
        Schema::dropIfExists('no_show_charge_rules');
        Schema::dropIfExists('night_audit_log');
        Schema::dropIfExists('night_audit_occupancy_snapshot');
    }
};
