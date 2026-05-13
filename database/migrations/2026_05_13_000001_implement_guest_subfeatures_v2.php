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
        // Blocked Guests
        Schema::dropIfExists('blocked_guests');
        Schema::create('blocked_guests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->onDelete('cascade');
            $table->foreignId('guest_id')->constrained('guests')->onDelete('cascade');
            $table->text('reason');
            $table->foreignId('blocked_by')->constrained('users')->onDelete('cascade');
            $table->dateTime('blocked_at');
            $table->boolean('is_active')->default(true);
            $table->foreignId('unblocked_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('unblocked_at')->nullable();
            $table->text('unblock_reason')->nullable();
            $table->enum('severity', ['warning', 'do_not_rent', 'blacklisted'])->default('warning');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Turnaway Reasons
        Schema::dropIfExists('turnaway_reasons');
        Schema::create('turnaway_reasons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('name_ar')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Turnaway Logs
        Schema::dropIfExists('turnaway_logs');
        Schema::create('turnaway_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->onDelete('cascade');
            $table->foreignId('guest_id')->nullable()->constrained('guests')->onDelete('set null');
            $table->string('guest_name');
            $table->string('guest_phone')->nullable();
            $table->string('requested_room_type')->nullable();
            $table->date('requested_date');
            $table->integer('requested_nights')->default(1);
            $table->enum('reason', ['no_availability', 'rate_disagreement', 'overbooking', 'other'])->default('no_availability');
            $table->text('reason_detail')->nullable();
            $table->decimal('estimated_revenue_loss', 12, 2)->nullable();
            $table->boolean('alternative_offered')->default(false);
            $table->text('alternative_details')->nullable();
            $table->foreignId('turned_away_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });

        // Company Groups
        Schema::dropIfExists('company_group_members');
        Schema::dropIfExists('company_groups');
        Schema::create('company_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('name_ar')->nullable();
            $table->text('description')->nullable();
            $table->string('group_code')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Company Group Members
        Schema::create('company_group_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_group_id')->constrained()->onDelete('cascade');
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
        });

        // Company Notes
        Schema::dropIfExists('company_notes');
        Schema::create('company_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->onDelete('cascade');
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->enum('note_type', ['general', 'billing', 'contract', 'credit', 'other'])->default('general');
            $table->string('title');
            $table->text('content');
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->boolean('is_pinned')->default(false);
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });

        // Guest Comments
        Schema::dropIfExists('guest_comments');
        Schema::create('guest_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->onDelete('cascade');
            $table->foreignId('guest_id')->constrained('guests')->onDelete('cascade');
            $table->enum('comment_type', ['general', 'preference', 'complaint', 'vip', 'incident'])->default('general');
            $table->text('comment');
            $table->boolean('is_internal')->default(true);
            $table->enum('priority', ['info', 'warning', 'critical'])->default('info');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guest_comments');
        Schema::dropIfExists('company_notes');
        Schema::dropIfExists('company_group_members');
        Schema::dropIfExists('company_groups');
        Schema::dropIfExists('turnaway_logs');
        Schema::dropIfExists('turnaway_reasons');
        Schema::dropIfExists('blocked_guests');
    }
};
