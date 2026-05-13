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
        Schema::create('form_integrations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('team_id')->index();
            $table->unsignedBigInteger('integration_id');
            $table->string('form_name');
            $table->string('form_url');
            $table->json('field_mapping');
            $table->boolean('auto_approve')->default(false);
            $table->enum('status', ['active', 'paused', 'draft']);
            $table->integer('submission_count')->default(0);
            $table->timestamp('last_submission_at')->nullable();
            $table->string('webhook_secret')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('team_id')->references('id')->on('teams')->onDelete('cascade');
            $table->foreign('integration_id')->references('id')->on('integrations')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_integrations');
    }
};
