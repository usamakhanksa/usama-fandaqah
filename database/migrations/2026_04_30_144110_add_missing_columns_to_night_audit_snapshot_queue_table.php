<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('night_audit_snapshot_queue', function (Blueprint $t) {
            if (!Schema::hasColumn('night_audit_snapshot_queue', 'snapshot_id')) {
                $t->unsignedBigInteger('snapshot_id')->after('id')->index();
            }
            if (!Schema::hasColumn('night_audit_snapshot_queue', 'team_id')) {
                $t->unsignedBigInteger('team_id')->after('snapshot_id')->index();
            }
            if (!Schema::hasColumn('night_audit_snapshot_queue', 'business_date')) {
                $t->date('business_date')->after('team_id');
            }
            if (!Schema::hasColumn('night_audit_snapshot_queue', 'status')) {
                $t->enum('status', ['pending', 'inprogress', 'done', 'failed'])->default('pending')->after('business_date');
            }
            if (!Schema::hasColumn('night_audit_snapshot_queue', 'queued_at')) {
                $t->timestamp('queued_at')->useCurrent()->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('night_audit_snapshot_queue', function (Blueprint $t) {
            $t->dropColumn(['snapshot_id', 'team_id', 'business_date', 'status', 'queued_at']);
        });
    }
};
