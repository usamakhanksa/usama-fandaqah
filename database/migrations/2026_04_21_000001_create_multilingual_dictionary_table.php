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
        Schema::create('translations', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique()->index();
            $table->json('value'); // Stores {"en": "...", "ar": "..."}
            $table->string('group')->index()->default('general');
            $table->timestamps();
        });

        // REFACTOR ACTION_TYPES: Add JSON 'name' column and migrate data
        if (Schema::hasTable('action_types')) {
            Schema::table('action_types', function (Blueprint $table) {
                if (!Schema::hasColumn('action_types', 'name')) {
                    $table->json('name')->nullable()->after('action');
                }
            });

            // DATA MIGRATION: action_types
            DB::table('action_types')->orderBy('id')->chunk(100, function ($rows) {
                foreach ($rows as $row) {
                    $translation = [
                        'en' => $row->name_en ?? '',
                        'ar' => $row->name_ar ?? '',
                    ];
                    DB::table('action_types')
                        ->where('id', $row->id)
                        ->update(['name' => json_encode($translation)]);
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('translations');
        
        if (Schema::hasTable('action_types')) {
            Schema::table('action_types', function (Blueprint $table) {
                $table->dropColumn('name');
            });
        }
    }
};
