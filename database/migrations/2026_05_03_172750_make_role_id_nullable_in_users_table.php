<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Get the foreign key name dynamically
        $foreignKeyResults = DB::select("
            SELECT CONSTRAINT_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = 'users'
            AND COLUMN_NAME = 'role_id'
            AND REFERENCED_TABLE_NAME = 'roles'
        ");
        
        // Drop the foreign key constraint if it exists
        if (!empty($foreignKeyResults)) {
            $constraintName = $foreignKeyResults[0]->CONSTRAINT_NAME;
            DB::statement("ALTER TABLE users DROP FOREIGN KEY {$constraintName}");
        }
        
        // Modify the column to be nullable
        DB::statement("ALTER TABLE users MODIFY role_id INT UNSIGNED NULL");
    }

    public function down(): void
    {
        // Make the column not nullable again
        DB::statement("ALTER TABLE users MODIFY role_id INT UNSIGNED NOT NULL");
        
        // Re-add the foreign key constraint
        DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_id_foreign FOREIGN KEY (role_id) REFERENCES roles(id)");
    }
};