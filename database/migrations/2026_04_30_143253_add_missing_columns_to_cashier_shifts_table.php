<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cashier_shifts', function (Blueprint $t) {
            if (!Schema::hasColumn('cashier_shifts', 'shift_date')) {
                $t->date('shift_date')->after('user_id')->nullable();
            }
            if (!Schema::hasColumn('cashier_shifts', 'system_balance')) {
                $t->decimal('system_balance', 12, 2)->after('closing_balance')->nullable();
            }
            if (!Schema::hasColumn('cashier_shifts', 'variance')) {
                $t->decimal('variance', 12, 2)->after('system_balance')->nullable();
            }
            if (!Schema::hasColumn('cashier_shifts', 'notes')) {
                $t->text('notes')->after('variance')->nullable();
            }
            if (!Schema::hasColumn('cashier_shifts', 'status')) {
                $t->enum('status', ['open', 'closed', 'approved'])->after('notes')->default('open');
            }
            if (!Schema::hasColumn('cashier_shifts', 'approved_by')) {
                $t->unsignedBigInteger('approved_by')->after('status')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('cashier_shifts', function (Blueprint $t) {
            $t->dropColumn(['shift_date', 'system_balance', 'variance', 'notes', 'status', 'approved_by']);
        });
    }
};
