<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('team_counters');
        Schema::create('team_counters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->string('key');
            $table->string('prefix');
            $table->integer('value')->default(0);
            $table->timestamps();

            $table->unique(['team_id', 'key', 'prefix']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('team_counters');
    }
};