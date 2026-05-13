<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 先删除引用此表的外键约束
        if (Schema::hasTable('night_audit_noshow_log')) {
            Schema::table('night_audit_noshow_log', function (Blueprint $table) {
                $constraintExists = $this->checkForeignConstraintExists('night_audit_noshow_log', 'night_audit_noshow_log_rule_id_foreign');
                if ($constraintExists) {
                    $table->dropForeign('night_audit_noshow_log_rule_id_foreign');
                }
            });
        }
        
        // 删除旧表
        Schema::dropIfExists('no_show_charge_rules');
        
        // 创建新表
        Schema::create('no_show_charge_rules', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('team_id');
            $t->string('name', 100);
            $t->date('start_date');
            $t->date('end_date');
            $t->enum('charge_type', ['fixed', 'percentage']);
            $t->decimal('charge_amount', 10, 2)->default(0);
            $t->enum('applies_to', ['all', 'daily', 'monthly']);
            $t->boolean('is_active')->default(true);
            $t->unsignedBigInteger('created_by')->nullable();
            $t->timestamps();
            $t->softDeletes();
            
            $t->foreign('team_id')->references('id')->on('teams')->cascadeOnDelete();
        });
        
        // 重新添加外键约束
        if (Schema::hasTable('night_audit_noshow_log')) {
            Schema::table('night_audit_noshow_log', function (Blueprint $table) {
                $table->foreign('rule_id')->references('id')->on('no_show_charge_rules')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('no_show_charge_rules');
    }
    
    private function checkForeignConstraintExists($tableName, $constraintName): bool
    {
        $result = \DB::select("
            SELECT CONSTRAINT_NAME
            FROM information_schema.TABLE_CONSTRAINTS 
            WHERE CONSTRAINT_SCHEMA = DATABASE()
            AND TABLE_NAME = ?
            AND CONSTRAINT_NAME = ?
            AND CONSTRAINT_TYPE = 'FOREIGN KEY'
        ", [$tableName, $constraintName]);
        
        return count($result) > 0;
    }
};
