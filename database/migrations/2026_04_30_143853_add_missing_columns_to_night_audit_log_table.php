<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('night_audit_log', function (Blueprint $t) {
            if (!Schema::hasColumn('night_audit_log', 'run_number')) {
                $t->tinyInteger('run_number')->default(1)->after('business_date');
            }
            if (!Schema::hasColumn('night_audit_log', 'triggered_by_user_id')) {
                $t->unsignedBigInteger('triggered_by_user_id')->nullable()->after('triggered_by');
            }
            if (!Schema::hasColumn('night_audit_log', 'steps_completed')) {
                $t->json('steps_completed')->nullable()->after('completed_at');
            }
            if (!Schema::hasColumn('night_audit_log', 'steps_failed')) {
                $t->json('steps_failed')->nullable()->after('steps_completed');
            }
            if (!Schema::hasColumn('night_audit_log', 'noshows_flagged')) {
                $t->unsignedBigInteger('noshows_flagged')->default(0)->after('steps_failed');
            }
            if (!Schema::hasColumn('night_audit_log', 'noshow_charges_posted')) {
                $t->unsignedBigInteger('noshow_charges_posted')->default(0)->after('noshows_flagged');
            }
            if (!Schema::hasColumn('night_audit_log', 'transactions_frozen')) {
                $t->unsignedBigInteger('transactions_frozen')->default(0)->after('noshow_charges_posted');
            }
            if (!Schema::hasColumn('night_audit_log', 'occupancy_snapshot_id')) {
                $t->unsignedBigInteger('occupancy_snapshot_id')->nullable()->after('transactions_frozen');
            }
            if (!Schema::hasColumn('night_audit_log', 'notes')) {
                $t->text('notes')->nullable()->after('occupancy_snapshot_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('night_audit_log', function (Blueprint $t) {
            $t->dropColumn(['run_number', 'triggered_by_user_id', 'steps_completed', 'steps_failed', 'noshows_flagged', 'noshow_charges_posted', 'transactions_frozen', 'occupancy_snapshot_id', 'notes']);
        });
    }
};
