<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('reservations')) {
            Schema::table('reservations', function (Blueprint $table) {
                if (! Schema::hasColumn('reservations', 'checked_in_at')) {
                    $table->timestamp('checked_in_at')->nullable()->after('check_in');
                }
                if (! Schema::hasColumn('reservations', 'checked_out_at')) {
                    $table->timestamp('checked_out_at')->nullable()->after('checked_in_at');
                }
            });
        }

        $this->createIfMissing('profiles', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->index();
            $table->string('phone')->nullable();
            $table->enum('type', ['guest', 'contact', 'company'])->default('guest');
            $table->string('nationality')->nullable();
            $table->string('passport_no')->nullable();
            $table->boolean('vip_status')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

        $this->createIfMissing('activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->nullable()->constrained('profiles')->nullOnDelete();
            $table->nullableMorphs('actionable');
            $table->enum('type', ['call', 'meeting', 'task']);
            $table->string('subject');
            $table->date('due_date')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('assigned_to_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        $this->createIfMissing('memberships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained('profiles')->cascadeOnDelete();
            $table->string('program_id');
            $table->string('membership_number')->unique();
            $table->unsignedInteger('points_balance')->default(0);
            $table->date('tier_expiry')->nullable();
            $table->timestamps();
        });

        $this->createIfMissing('sales_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained('profiles')->cascadeOnDelete();
            $table->enum('account_type', ['corporate', 'agent']);
            $table->decimal('credit_limit', 12, 2)->default(0);
            $table->decimal('commission_percent', 5, 2)->default(0);
            $table->string('tax_id')->nullable();
            $table->timestamps();
        });

        $this->createIfMissing('scheduled_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('report_name');
            $table->json('parameters_json')->nullable();
            $table->enum('frequency', ['daily', 'weekly']);
            $table->text('email_recipients')->nullable();
            $table->timestamp('last_run_at')->nullable();
            $table->timestamps();
        });

        $this->createIfMissing('safe_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('action', ['open', 'close']);
            $table->decimal('amount', 12, 2)->default(0);
            $table->timestamp('logged_at')->useCurrent();
            $table->timestamps();
        });

        $this->createIfMissing('reservation_change_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')->constrained('reservations')->cascadeOnDelete();
            $table->foreignId('from_room_id')->nullable()->constrained('rooms')->nullOnDelete();
            $table->foreignId('to_room_id')->nullable()->constrained('rooms')->nullOnDelete();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('changed_at')->useCurrent();
            $table->text('reason')->nullable();
            $table->timestamps();
        });

        $this->createIfMissing('maintenance_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')->nullable()->constrained('reservations')->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('maintenance_categories')->nullOnDelete();
            $table->text('description');
            $table->enum('status', ['open', 'in_progress', 'closed'])->default('open');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        $this->createIfMissing('resource_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        $this->createIfMissing('reservation_resource_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')->constrained('reservations')->cascadeOnDelete();
            $table->foreignId('resource_category_id')->constrained('resource_categories')->cascadeOnDelete();
            $table->unsignedInteger('quantity')->default(1);
            $table->timestamps();
        });

        $this->createIfMissing('employee_contracts', function (Blueprint $table) {
            $table->id();
            $table->string('employee_name');
            $table->string('contract_type');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->enum('status', ['active', 'expired', 'draft'])->default('active');
            $table->timestamps();
        });

        $this->createIfMissing('ledger_sequences', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->string('prefix');
            $table->unsignedBigInteger('next_number')->default(1);
            $table->timestamps();
        });

        $this->createIfMissing('profile_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        $this->createIfMissing('included_services', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('included_services');
        Schema::dropIfExists('profile_groups');
        Schema::dropIfExists('ledger_sequences');
        Schema::dropIfExists('employee_contracts');
        Schema::dropIfExists('reservation_resource_items');
        Schema::dropIfExists('resource_categories');
        Schema::dropIfExists('maintenance_requests');
        Schema::dropIfExists('reservation_change_logs');
        Schema::dropIfExists('safe_logs');
        Schema::dropIfExists('scheduled_reports');
        Schema::dropIfExists('sales_accounts');
        Schema::dropIfExists('memberships');
        Schema::dropIfExists('activities');
        Schema::dropIfExists('profiles');

        if (Schema::hasTable('reservations')) {
            Schema::table('reservations', function (Blueprint $table) {
                if (Schema::hasColumn('reservations', 'checked_in_at')) {
                    $table->dropColumn('checked_in_at');
                }
                if (Schema::hasColumn('reservations', 'checked_out_at')) {
                    $table->dropColumn('checked_out_at');
                }
            });
        }
    }

    private function createIfMissing(string $table, callable $callback): void
    {
        if (! Schema::hasTable($table)) {
            Schema::create($table, $callback);
        }
    }
};
