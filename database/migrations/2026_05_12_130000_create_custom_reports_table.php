<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custom_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('module', ['reservations', 'finance', 'rooms', 'guests', 'pos']);
            $table->json('columns');
            $table->json('filters')->nullable();
            $table->string('sort_by')->nullable();
            $table->enum('sort_direction', ['asc', 'desc'])->default('asc');
            $table->string('group_by')->nullable();
            $table->boolean('is_shared')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['team_id', 'module']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_reports');
    }
};
