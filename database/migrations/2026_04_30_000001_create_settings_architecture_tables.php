<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (!Schema::hasTable('settings')) {
            Schema::create('settings', function (Blueprint $table) {
                $table->id();
                $table->string('group')->index();
                $table->string('key')->unique();
                $table->json('payload')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('pms_dictionaries')) {
            Schema::create('pms_dictionaries', function (Blueprint $table) {
                $table->id();
                $table->string('group')->index();
                $table->string('label');
                $table->json('meta')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (Schema::hasTable('activity_logs')) {
            Schema::table('activity_logs', function (Blueprint $table) {
                if (!Schema::hasColumn('activity_logs', 'module')) $table->string('module')->after('user_id')->nullable();
                if (!Schema::hasColumn('activity_logs', 'description')) $table->text('description')->after('action')->nullable();
                if (!Schema::hasColumn('activity_logs', 'ip_address')) $table->string('ip_address')->after('description')->nullable();
            });
        }
    }
    public function down(): void {
        Schema::dropIfExists('pms_dictionaries');
        Schema::dropIfExists('settings');
    }
};
