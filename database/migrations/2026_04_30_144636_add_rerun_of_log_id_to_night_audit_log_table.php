<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('night_audit_log', function (Blueprint $t) {
            if (!Schema::hasColumn('night_audit_log', 'rerun_of_log_id')) {
                $t->unsignedBigInteger('rerun_of_log_id')->nullable()->after('occupancy_snapshot_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('night_audit_log', function (Blueprint $t) {
            $t->dropColumn('rerun_of_log_id');
        });
    }
};
