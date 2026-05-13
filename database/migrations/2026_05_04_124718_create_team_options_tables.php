<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTeamOptionsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('team_contact_persons')) {
            Schema::create('team_contact_persons', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('team_id')->index();
                $table->string('name_en');
                $table->string('name_ar')->nullable();
                $table->string('job_title_en')->nullable();
                $table->string('job_title_ar')->nullable();
                $table->string('phone')->nullable();
                $table->string('email')->nullable();
                $table->boolean('is_primary')->default(false);
                $table->boolean('is_active')->default(true);
                $table->integer('order')->default(10);
                $table->timestamps();
                $table->softDeletes();
                
                $table->foreign('team_id')->references('id')->on('teams')->onDelete('cascade');
            });
        }

        if (!Schema::hasTable('team_counters')) {
            Schema::create('team_counters', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('team_id')->index();
                $table->string('key')->index(); // e.g., 'invoice', 'receipt', 'reservation'
                $table->string('prefix')->nullable();
                $table->string('suffix')->nullable();
                $table->integer('start_from')->default(1);
                $table->integer('current_value')->default(0);
                $table->integer('padding')->default(4);
                $table->timestamps();
                
                $table->unique(['team_id', 'key']);
                $table->foreign('team_id')->references('id')->on('teams')->onDelete('cascade');
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
        Schema::dropIfExists('team_counters');
        Schema::dropIfExists('team_contact_persons');
    }
}
