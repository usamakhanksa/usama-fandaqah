<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('custom_report_id')->nullable()->constrained('custom_reports')->nullOnDelete();
            $table->enum('report_type', ['daily', 'occupancy', 'revenue', 'adr_revpar', 'custom']);
            $table->string('name');
            $table->enum('frequency', ['daily', 'weekly', 'monthly']);
            $table->unsignedTinyInteger('day_of_week')->nullable(); // 0=Sun, 6=Sat
            $table->unsignedTinyInteger('day_of_month')->nullable(); // 1-31
            $table->time('time');
            $table->json('recipients');
            $table->enum('format', ['pdf', 'excel', 'both']);
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_run_at')->nullable();
            $table->timestamp('next_run_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['team_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_schedules');
    }
};
