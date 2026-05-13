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
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->text('metadata')->nullable();
            $table->datetime('starts_at')->nullable()->index();
            $table->datetime('expires_at')->nullable()->index();
            $table->datetime('redeemed_at')->nullable()->index();
            $table->decimal('amount', 14, 2)->default(0); // Using decimal for monetary value
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vouchers');
    }
};
