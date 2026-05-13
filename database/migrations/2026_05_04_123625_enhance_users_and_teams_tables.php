<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class EnhanceUsersAndTeamsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'billing_address')) {
                $table->string('billing_address')->nullable();
                $table->string('billing_address_line_2')->nullable();
                $table->string('billing_city')->nullable();
                $table->string('billing_state')->nullable();
                $table->string('billing_zip', 25)->nullable();
                $table->string('billing_country', 2)->nullable();
                $table->string('vat_id', 50)->nullable();
                $table->text('extra_billing_information')->nullable();
                $table->timestamp('trial_ends_at')->nullable();
                $table->timestamp('last_read_announcements_at')->nullable();
            }
            
            // Indexes
            if (Schema::hasColumn('users', 'role_id')) {
                $table->index('role_id', 'users_role_id_index');
            }
        });

        Schema::table('teams', function (Blueprint $table) {
            if (!Schema::hasColumn('teams', 'billing_address')) {
                $table->string('card_brand')->nullable();
                $table->string('card_last_four', 4)->nullable();
                $table->string('card_country')->nullable();
                $table->string('billing_address')->nullable();
                $table->string('billing_address_line_2')->nullable();
                $table->string('billing_city')->nullable();
                $table->string('billing_state')->nullable();
                $table->string('billing_zip', 25)->nullable();
                $table->string('billing_country', 2)->nullable();
                $table->string('vat_id', 50)->nullable();
                $table->text('extra_billing_information')->nullable();
            }

            // Additional PMS fields
            if (!Schema::hasColumn('teams', 'phone')) {
                $table->string('phone')->nullable();
                $table->string('email')->nullable();
                $table->string('website')->nullable();
                $table->string('logo')->nullable();
                $table->string('favicon')->nullable();
                $table->string('commercial_register')->nullable();
            }

            // Indexes
            $table->index('owner_id', 'teams_owner_id_index');
            if (Schema::hasColumn('teams', 'slug')) {
                $table->index('slug', 'teams_slug_index');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'billing_address', 'billing_address_line_2', 'billing_city', 'billing_state', 
                'billing_zip', 'billing_country', 'vat_id', 'extra_billing_information', 
                'trial_ends_at', 'last_read_announcements_at'
            ]);
        });

        Schema::table('teams', function (Blueprint $table) {
            $table->dropColumn([
                'card_brand', 'card_last_four', 'card_country', 'billing_address', 
                'billing_address_line_2', 'billing_city', 'billing_state', 'billing_zip', 
                'billing_country', 'vat_id', 'extra_billing_information', 'phone', 
                'email', 'website', 'logo', 'favicon', 'commercial_register'
            ]);
        });
    }
}
