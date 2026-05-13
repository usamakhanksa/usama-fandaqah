<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWebsiteAndNotificationTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('website_settings')) {
            Schema::create('website_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('team_id')->index();
            $table->string('key')->index();
            $table->text('value')->nullable();
            $table->string('type')->default('string'); // string, json, image
            $table->timestamps();
            
            $table->unique(['team_id', 'key']);
            $table->foreign('team_id')->references('id')->on('teams')->onDelete('cascade');
        });
        }

        if (!Schema::hasTable('website_pages')) {
            Schema::create('website_pages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('team_id')->index();
            $table->string('title_en');
            $table->string('title_ar')->nullable();
            $table->string('slug')->index();
            $table->longText('content_en')->nullable();
            $table->longText('content_ar')->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamps();
            
            $table->foreign('team_id')->references('id')->on('teams')->onDelete('cascade');
        });
        }

        if (!Schema::hasTable('notification_controls')) {
            Schema::create('notification_controls', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('team_id')->index();
            $table->string('event_key')->index(); // e.g., 'reservation_confirmed', 'check_in'
            $table->boolean('send_sms')->default(false);
            $table->boolean('send_email')->default(false);
            $table->boolean('send_whatsapp')->default(false);
            $table->text('sms_template')->nullable();
            $table->text('email_template')->nullable();
            $table->timestamps();
            
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
        Schema::dropIfExists('notification_controls');
        Schema::dropIfExists('website_pages');
        Schema::dropIfExists('website_settings');
    }
}
