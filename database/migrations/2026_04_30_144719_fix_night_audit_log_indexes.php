<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('night_audit_log', function (Blueprint $t) {
            // Check if index exists before adding
            $indexes = DB::select("SHOW INDEXES FROM night_audit_log WHERE Key_name = 'idx_night_audit_log_team'");
            if (empty($indexes)) {
                $t->index('team_id', 'idx_night_audit_log_team');
            }
            
            // Drop old unique constraint if it exists
            $uniques = DB::select("SHOW INDEXES FROM night_audit_log WHERE Key_name = 'night_audit_log_team_id_business_date_unique'");
            if (!empty($uniques)) {
                $t->dropUnique('night_audit_log_team_id_business_date_unique');
            }
            
            // Add new unique constraint including run_number if not already there
            $newUnique = DB::select("SHOW INDEXES FROM night_audit_log WHERE Key_name = 'uk_night_audit_run'");
            if (empty($newUnique)) {
                $t->unique(['team_id', 'business_date', 'run_number'], 'uk_night_audit_run');
            }
        });
    }

    public function down(): void
    {
        Schema::table('night_audit_log', function (Blueprint $t) {
            $t->dropUnique('uk_night_audit_run');
            $t->unique(['team_id', 'business_date'], 'night_audit_log_team_id_business_date_unique');
            $t->dropIndex('idx_night_audit_log_team');
        });
    }
};
