<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIntegrationTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('integrations')) {
            Schema::create('integrations', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // e.g., 'STAAH', 'Qoyod', 'ZATCA'
            $table->string('provider')->nullable();
            $table->string('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('available_options')->nullable();
            $table->timestamps();
        });
        }

        if (!Schema::hasTable('integration_settings')) {
            Schema::create('integration_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('team_id')->index();
            $table->unsignedBigInteger('integration_id')->index();
            $table->json('config')->nullable();
            $table->boolean('is_enabled')->default(false);
            $table->timestamp('last_sync_at')->nullable();
            $table->string('sync_status')->nullable();
            $table->timestamps();
            
            $table->unique(['team_id', 'integration_id']);
            $table->foreign('team_id')->references('id')->on('teams')->onDelete('cascade');
            $table->foreign('integration_id')->references('id')->on('integrations')->onDelete('cascade');
        });
        }

        if (!Schema::hasTable('integration_logs')) {
            Schema::create('integration_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('team_id')->index();
            $table->unsignedBigInteger('integration_id')->index();
            $table->string('endpoint')->nullable();
            $table->string('method', 10)->nullable();
            $table->integer('response_code')->nullable();
            $table->longText('payload')->nullable();
            $table->longText('response')->nullable();
            $table->integer('duration_ms')->nullable();
            $table->timestamps();
            
            $table->foreign('team_id')->references('id')->on('teams')->onDelete('cascade');
            $table->foreign('integration_id')->references('id')->on('integrations')->onDelete('cascade');
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
        Schema::dropIfExists('integration_logs');
        Schema::dropIfExists('integration_settings');
        Schema::dropIfExists('integrations');
    }
}
