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
        // Drop the old columns from the first migration format
        if (Schema::hasColumn('team_counters', 'reservation_counter')) {
            Schema::table('team_counters', function (Blueprint $table) {
                $table->dropColumn([
                    'reservation_counter',
                    'transaction_counter',
                    'invoice_counter',
                    'service_log_counter',
                    'promissory_counter'
                ]);
            });
        }

        // Add the generic key-value columns if they don't exist
        if (!Schema::hasColumn('team_counters', 'key')) {
            Schema::table('team_counters', function (Blueprint $table) {
                $table->string('key')->index()->after('team_id');
                $table->string('prefix')->nullable()->after('key');
                $table->string('suffix')->nullable()->after('prefix');
                $table->integer('start_from')->default(1)->after('suffix');
                $table->integer('current_value')->default(0)->after('start_from');
                $table->integer('padding')->default(4)->after('current_value');
                
                $table->unique(['team_id', 'key']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('team_counters', 'key')) {
            Schema::table('team_counters', function (Blueprint $table) {
                $table->dropColumn(['key', 'prefix', 'suffix', 'start_from', 'current_value', 'padding']);
            });
        }
    }
};
