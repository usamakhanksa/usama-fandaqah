<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('client_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('phone');
            $table->string('national_id')->nullable();
            $table->enum('type', ['tenant', 'buyer', 'investor'])->default('tenant');
            $table->string('city')->nullable();
            $table->text('address')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('client_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_profile_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['call', 'meeting', 'email', 'viewing']);
            $table->string('subject');
            $table->text('description');
            $table->timestamp('scheduled_at')->nullable();
            $table->enum('status', ['pending', 'completed', 'cancelled'])->default('pending');
            $table->timestamps();
        });

        Schema::create('client_memberships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_profile_id')->constrained()->cascadeOnDelete();
            $table->enum('tier', ['standard', 'silver', 'gold', 'platinum'])->default('standard');
            $table->integer('points')->default(0);
            $table->timestamp('joined_at')->useCurrent();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        Schema::create('client_sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_profile_id')->constrained()->cascadeOnDelete();
            $table->string('property_reference');
            $table->decimal('amount', 15, 2);
            $table->enum('status', ['negotiation', 'contract_sent', 'closed', 'lost'])->default('negotiation');
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('client_sales');
        Schema::dropIfExists('client_memberships');
        Schema::dropIfExists('client_activities');
        Schema::dropIfExists('client_profiles');
    }
};
