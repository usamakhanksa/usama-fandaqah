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
        if (!Schema::hasTable('company_groups')) {
            Schema::create('company_groups', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('team_id')->index();
                $table->string('name');
                $table->timestamps();
            });
        }

        Schema::table('company_groups', function (Blueprint $table) {
            if (!Schema::hasColumn('company_groups', 'name_ar')) {
                $table->string('name_ar')->nullable()->after('name');
            }
            if (!Schema::hasColumn('company_groups', 'tax_number')) {
                $table->string('tax_number')->nullable()->after('name_ar');
            }
            if (!Schema::hasColumn('company_groups', 'email')) {
                $table->string('email')->nullable()->after('tax_number');
            }
            if (!Schema::hasColumn('company_groups', 'phone')) {
                $table->string('phone')->nullable()->after('email');
            }
            if (!Schema::hasColumn('company_groups', 'address')) {
                $table->string('address')->nullable()->after('phone');
            }
            if (!Schema::hasColumn('company_groups', 'city')) {
                $table->string('city')->nullable()->after('address');
            }
            if (!Schema::hasColumn('company_groups', 'credit_limit')) {
                $table->decimal('credit_limit', 15, 2)->default(0)->after('city');
            }
            if (!Schema::hasColumn('company_groups', 'payment_terms_days')) {
                $table->integer('payment_terms_days')->default(30)->after('credit_limit');
            }
        });

        Schema::table('company_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('company_profiles', 'company_group_id')) {
                $table->unsignedBigInteger('company_group_id')->nullable()->after('id');
            }
            if (!Schema::hasColumn('company_profiles', 'credit_limit')) {
                $table->decimal('credit_limit', 15, 2)->default(0)->after('status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('company_profiles', function (Blueprint $table) {
            $table->dropColumn(['company_group_id', 'credit_limit']);
        });
        Schema::dropIfExists('company_groups');
    }
};
