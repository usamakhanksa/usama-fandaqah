<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Get all existing data
        $existingData = DB::table('team_counters')->get();
        
        // Drop the old table
        Schema::dropIfExists('team_counters');
        
        // Create the table with the correct column name
        Schema::create('team_counters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->string('key');
            $table->string('prefix');
            $table->integer('value')->default(0);
            $table->timestamps();

            $table->unique(['team_id', 'key', 'prefix']);
        });
        
        // Insert the old data back
        foreach ($existingData as $data) {
            DB::table('team_counters')->insert([
                'id' => $data->id,
                'team_id' => $data->team_id,
                'key' => $data->type,  // Map old 'type' column to new 'key' column
                'prefix' => $data->prefix,
                'value' => $data->value,
                'created_at' => $data->created_at,
                'updated_at' => $data->updated_at,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Get all existing data
        $existingData = DB::table('team_counters')->get();
        
        // Drop the current table
        Schema::dropIfExists('team_counters');
        
        // Recreate with old column name
        Schema::create('team_counters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->string('prefix');
            $table->integer('value')->default(0);
            $table->timestamps();

            $table->unique(['team_id', 'type', 'prefix']);
        });
        
        // Insert the old data back
        foreach ($existingData as $data) {
            DB::table('team_counters')->insert([
                'id' => $data->id,
                'team_id' => $data->team_id,
                'type' => $data->key,  // Map new 'key' column back to old 'type' column
                'prefix' => $data->prefix,
                'value' => $data->value,
                'created_at' => $data->created_at,
                'updated_at' => $data->updated_at,
            ]);
        }
    }
};