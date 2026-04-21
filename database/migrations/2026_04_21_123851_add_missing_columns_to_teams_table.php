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
        Schema::table('teams', function (Blueprint $table) {
            if (!Schema::hasColumn('teams', 'deleted_at')) {
                $table->softDeletes();
            }
            if (!Schema::hasColumn('teams', 'trial_ends_at')) {
                $table->timestamp('trial_ends_at')->nullable();
            }
            if (!Schema::hasColumn('teams', 'current_billing_plan')) {
                $table->string('current_billing_plan')->nullable();
            }
            if (!Schema::hasColumn('teams', 'enable_website')) {
                $table->boolean('enable_website')->default(false);
            }
            if (!Schema::hasColumn('teams', 'private_domain')) {
                $table->string('private_domain')->nullable();
            }
            if (!Schema::hasColumn('teams', 'enable_private_domain')) {
                $table->boolean('enable_private_domain')->default(false);
            }
            if (!Schema::hasColumn('teams', 'private_domain_status')) {
                $table->string('private_domain_status')->nullable();
            }
            if (!Schema::hasColumn('teams', 'country_id')) {
                $table->unsignedBigInteger('country_id')->nullable();
            }
            if (!Schema::hasColumn('teams', 'ends_at')) {
                $table->timestamp('ends_at')->nullable();
            }
            if (!Schema::hasColumn('teams', 'enable_zatca_phase_two')) {
                $table->boolean('enable_zatca_phase_two')->default(false);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->dropColumn([
                'deleted_at', 'trial_ends_at', 'current_billing_plan', 
                'enable_website', 'private_domain', 'enable_private_domain', 
                'private_domain_status', 'country_id', 'ends_at', 'enable_zatca_phase_two'
            ]);
        });
    }
};
