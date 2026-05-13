<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('highlights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->onDelete('cascade');
            $table->json('name');
            $table->string('color', 20)->default('#2196F3');
            $table->tinyInteger('status')->default(1);
            $table->integer('order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('customer_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('customer_id');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('body');
            $table->enum('type', ['general', 'preference', 'complaint'])->default('general');
            $table->timestamps();
            $table->softDeletes();
            $table->index(['team_id', 'customer_id']);
        });

        Schema::create('blocked_guests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->string('guest_name')->nullable();
            $table->string('id_number')->nullable();
            $table->text('reason');
            $table->enum('block_type', ['permanent', 'temporary'])->default('permanent');
            $table->date('end_date')->nullable();
            $table->foreignId('blocked_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->index(['team_id', 'customer_id']);
        });

        Schema::create('turnaway_reasons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('name_ar')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
        });

        Schema::create('turnaway_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->string('guest_name');
            $table->string('guest_phone')->nullable();
            $table->unsignedBigInteger('reason_id')->nullable();
            $table->string('room_type_requested')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->index(['team_id', 'date']);
        });

        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->onDelete('cascade');
            $table->morphs('commentable');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->text('body');
            $table->timestamps();
            $table->index(['team_id', 'commentable_type', 'commentable_id']);
        });

        Schema::create('merge_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('primary_customer_id');
            $table->unsignedBigInteger('merged_customer_id');
            $table->json('fields_kept')->nullable();
            $table->foreignId('merged_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('merge_logs');
        Schema::dropIfExists('comments');
        Schema::dropIfExists('turnaway_logs');
        Schema::dropIfExists('turnaway_reasons');
        Schema::dropIfExists('blocked_guests');
        Schema::dropIfExists('customer_notes');
        Schema::dropIfExists('highlights');
    }
};
