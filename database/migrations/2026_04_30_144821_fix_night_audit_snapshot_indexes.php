<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('night_audit_occupancy_snapshot', function (Blueprint $table) {
            // Remove duplicates before adding unique constraint
            // Keep only the record with the highest id for each team_id, business_date, run_number combination
            DB::statement("
                DELETE t1 FROM night_audit_occupancy_snapshot t1
                INNER JOIN night_audit_occupancy_snapshot t2 
                WHERE t1.id > t2.id 
                AND t1.team_id = t2.team_id 
                AND t1.business_date = t2.business_date 
                AND t1.run_number = t2.run_number
            ");
            
            // Check if index exists before adding
            $indexes = DB::select("SHOW INDEXES FROM night_audit_occupancy_snapshot WHERE Key_name = 'idx_snapshot_team'");
            if (empty($indexes)) {
                $table->index('team_id', 'idx_snapshot_team');
            }
            
            // Drop old unique constraint if it exists
            $uniques = DB::select("SHOW INDEXES FROM night_audit_occupancy_snapshot WHERE Key_name = 'occupancy_snapshot_unique'");
            $hasOldConstraint = !empty($uniques);
            if ($hasOldConstraint) {
                $table->dropUnique('occupancy_snapshot_unique');
            }
            
            // Add new unique constraint including run_number if not already there
            $newUnique = DB::select("SHOW INDEXES FROM night_audit_occupancy_snapshot WHERE Key_name = 'uk_snapshot_run'");
            if (empty($newUnique)) {
                $table->unique(['team_id', 'business_date', 'run_number'], 'uk_snapshot_run');
            } else {
                // If the new unique constraint already exists, drop it first
                $table->dropUnique('uk_snapshot_run');
                $table->unique(['team_id', 'business_date', 'run_number'], 'uk_snapshot_run');
            }
        });
    }

    public function down(): void
    {
        Schema::table('night_audit_occupancy_snapshot', function (Blueprint $table) {
            $table->dropUnique('uk_snapshot_run');
            
            // Only try to restore the old constraint if it existed before
            $oldUniques = DB::select("SHOW INDEXES FROM night_audit_occupancy_snapshot WHERE Key_name = 'occupancy_snapshot_unique'");
            if (empty($oldUniques)) {
                $table->unique(['team_id', 'business_date'], 'occupancy_snapshot_unique');
            }
            
            $table->dropIndex('idx_snapshot_team');
        });
    }
};