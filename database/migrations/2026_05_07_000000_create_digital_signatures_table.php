<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('digital_signatures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->nullable()->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('ref_id')->nullable();
            $table->string('type'); // reservation, reservation_user, user, promissory, contract, registration
            $table->text('signature_base64')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index(['team_id', 'type']);
            $table->index(['ref_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('digital_signatures');
    }
};
