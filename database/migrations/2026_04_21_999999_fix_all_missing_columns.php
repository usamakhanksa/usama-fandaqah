<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // 1. Units Table Fixes
        Schema::table('units', function (Blueprint $table) {
            if (!Schema::hasColumn('units', 'room_floor_id')) {
                $table->bigInteger('room_floor_id')->unsigned()->nullable()->after('room_id');
            }
            if (!Schema::hasColumn('units', 'number')) {
                $table->string('number', 50)->nullable()->after('name');
            }
            if (!Schema::hasColumn('units', 'unit_number')) {
                $table->string('unit_number', 50)->nullable()->after('number');
            }
        });

        // 2. Countries Table Fixes
        if (!Schema::hasTable('countries')) {
            Schema::create('countries', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('code')->nullable();
                $table->json('title')->nullable();
                $table->string('iso2', 2)->unique();
                $table->string('phone_code', 8);
                $table->timestamps();
            });
        } else {
            Schema::table('countries', function (Blueprint $table) {
                if (!Schema::hasColumn('countries', 'iso2')) $table->string('iso2', 2)->unique();
                if (!Schema::hasColumn('countries', 'phone_code')) $table->string('phone_code', 8);
                if (!Schema::hasColumn('countries', 'code')) $table->string('code')->nullable();
                if (!Schema::hasColumn('countries', 'title')) $table->json('title')->nullable();
            });
        }

        // 3. Permissions Table Fixes
        Schema::table('permissions', function (Blueprint $table) {
            if (!Schema::hasColumn('permissions', 'module')) {
                $table->string('module', 255)->nullable()->after('name');
            }
        });

        // 4. Promo Codes Table Fixes
        if (Schema::hasTable('promo_codes')) {
            Schema::table('promo_codes', function (Blueprint $table) {
                if (!Schema::hasColumn('promo_codes', 'code')) {
                    $table->string('code', 255)->unique()->after('id');
                }
            });
        }

        // 5. Leads Table Creation
        if (!Schema::hasTable('leads')) {
            Schema::create('leads', function (Blueprint $table) {
                $table->id();
                $table->string('first_name');
                $table->string('last_name');
                $table->string('email');
                $table->string('phone');
                $table->string('company')->nullable();
                $table->text('message')->nullable();
                $table->string('status')->default('pending');
                $table->timestamps();
            });
        }

        // 6. Unit Statuses Slug
        Schema::table('unit_statuses', function (Blueprint $table) {
            if (!Schema::hasColumn('unit_statuses', 'slug')) {
                $table->string('slug')->unique()->after('name');
            }
        });

        // 7. Dynamic Service Categories (Ensure table exists if migration missed)
        if (!Schema::hasTable('service_categories')) {
            Schema::create('service_categories', function (Blueprint $table) {
                $table->id();
                $table->bigInteger('team_id')->unsigned()->nullable();
                $table->longText('name')->nullable();
                $table->bigInteger('status')->unsigned()->default(0);
                $table->tinyInteger('show_in_reservation')->default(1);
                $table->tinyInteger('show_in_pos')->default(1);
                $table->bigInteger('order')->unsigned()->default(0);
                $table->longText('users')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        // No down needed for fixing migrations
    }
};
