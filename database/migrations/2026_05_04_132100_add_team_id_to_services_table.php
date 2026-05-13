<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTeamIdToServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('services')) {
            Schema::table('services', function (Blueprint $table) {
                if (!Schema::hasColumn('services', 'team_id')) {
                    $table->unsignedBigInteger('team_id')->nullable()->index()->after('id');
                }
                if (!Schema::hasColumn('services', 'service_category_id')) {
                    $table->unsignedBigInteger('service_category_id')->nullable()->index()->after('team_id');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('services')) {
            Schema::table('services', function (Blueprint $table) {
                $table->dropColumn(['team_id', 'service_category_id']);
            });
        }
    }
}
