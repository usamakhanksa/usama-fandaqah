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
        // 检查cashier_shift_id字段是否已存在，如果不存在则添加
        if (!Schema::hasColumn('transactions', 'cashier_shift_id')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->unsignedBigInteger('cashier_shift_id')->nullable()->after('is_freezed');
                $table->index('cashier_shift_id');
            });
        } else {
            // 如果字段已存在，则更新其属性（防止之前创建了但没有索引等情况）
            Schema::table('transactions', function (Blueprint $table) {
                $table->unsignedBigInteger('cashier_shift_id')->nullable()->change();
                $table->index('cashier_shift_id');
            });
        }

        Schema::table('cashier_shifts', function (Blueprint $table) {
            if (!Schema::hasColumn('cashier_shifts', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('approved_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex(['cashier_shift_id']); // 删除索引
            $table->dropColumn('cashier_shift_id');
        });

        Schema::table('cashier_shifts', function (Blueprint $table) {
            $table->dropColumn('approved_at');
        });
    }
};