<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('reservation_drafts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained();
            $table->string('reference')->unique();
            $table->unsignedTinyInteger('current_step')->default(1);
            $table->json('details_payload')->nullable();
            $table->json('visitor_payload')->nullable();
            $table->json('payment_payload')->nullable();
            $table->timestamps();
        });

        Schema::create('promo_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('type')->default('percent');
            $table->decimal('value', 10, 2)->default(0);
            $table->decimal('max_discount', 10, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        Schema::create('reservation_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained();
            $table->foreignId('user_id')->nullable()->constrained();
            $table->text('note');
            $table->timestamps();
        });

        Schema::create('financial_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained();
            $table->string('label');
            $table->decimal('amount', 10, 2);
            $table->string('type')->default('debit');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_records');
        Schema::dropIfExists('reservation_notes');
        Schema::dropIfExists('promo_codes');
        Schema::dropIfExists('reservation_drafts');
    }
};
