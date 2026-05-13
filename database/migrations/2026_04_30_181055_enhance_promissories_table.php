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
        Schema::table('promissories', function (Blueprint $table) {
            if (!Schema::hasColumn('promissories', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->after('team_id');
            }
            if (!Schema::hasColumn('promissories', 'status')) {
                $table->string('status')->default('pending')->after('collected_amount');
            }
            if (!Schema::hasColumn('promissories', 'signature_status')) {
                $table->string('signature_status')->default('signed')->after('status');
            }
            if (!Schema::hasColumn('promissories', 'unsigned_reason')) {
                $table->text('unsigned_reason')->nullable()->after('signature_status');
            }
            if (!Schema::hasColumn('promissories', 'serial')) {
                $table->string('serial')->nullable()->after('id');
            }
            if (!Schema::hasColumn('promissories', 'notes')) {
                $table->text('notes')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('promissories', function (Blueprint $table) {
            $table->dropColumn(['user_id', 'status', 'signature_status', 'unsigned_reason', 'serial', 'notes']);
        });
    }
};
