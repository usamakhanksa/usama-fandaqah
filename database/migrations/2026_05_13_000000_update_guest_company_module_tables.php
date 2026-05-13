<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('blocked_guests')) {
            Schema::table('blocked_guests', function (Blueprint $table) {
                if (!Schema::hasColumn('blocked_guests', 'guest_id')) {
                    $table->unsignedBigInteger('guest_id')->nullable()->after('customer_id');
                    $table->foreign('guest_id')->references('id')->on('guests')->nullOnDelete();
                }
                if (!Schema::hasColumn('blocked_guests', 'severity')) {
                    $table->string('severity')->nullable()->after('reason');
                }
                if (!Schema::hasColumn('blocked_guests', 'notes')) {
                    $table->text('notes')->nullable()->after('severity');
                }
                if (!Schema::hasColumn('blocked_guests', 'is_active')) {
                    $table->boolean('is_active')->default(true)->after('end_date');
                }
                if (!Schema::hasColumn('blocked_guests', 'blocked_at')) {
                    $table->dateTime('blocked_at')->nullable()->after('is_active');
                }
                if (!Schema::hasColumn('blocked_guests', 'unblocked_at')) {
                    $table->dateTime('unblocked_at')->nullable()->after('blocked_at');
                }
                if (!Schema::hasColumn('blocked_guests', 'unblocked_by')) {
                    $table->unsignedBigInteger('unblocked_by')->nullable()->after('unblocked_at');
                }
            });
        }

        if (Schema::hasTable('turnaway_logs')) {
            Schema::table('turnaway_logs', function (Blueprint $table) {
                if (!Schema::hasColumn('turnaway_logs', 'guest_id')) {
                    $table->unsignedBigInteger('guest_id')->nullable()->after('team_id');
                    $table->foreign('guest_id')->references('id')->on('guests')->nullOnDelete();
                }
                if (!Schema::hasColumn('turnaway_logs', 'requested_date')) {
                    $table->date('requested_date')->nullable()->after('room_type_requested');
                }
                if (!Schema::hasColumn('turnaway_logs', 'requested_nights')) {
                    $table->unsignedInteger('requested_nights')->nullable()->after('requested_date');
                }
                if (!Schema::hasColumn('turnaway_logs', 'reason')) {
                    $table->enum('reason', ['no_availability', 'rate_disagreement', 'overbooking', 'other'])->nullable()->after('guest_phone');
                }
                if (!Schema::hasColumn('turnaway_logs', 'reason_detail')) {
                    $table->text('reason_detail')->nullable()->after('reason');
                }
                if (!Schema::hasColumn('turnaway_logs', 'estimated_revenue_loss')) {
                    $table->decimal('estimated_revenue_loss', 14, 2)->nullable()->after('reason_detail');
                }
                if (!Schema::hasColumn('turnaway_logs', 'alternative_offered')) {
                    $table->boolean('alternative_offered')->default(false)->after('estimated_revenue_loss');
                }
                if (!Schema::hasColumn('turnaway_logs', 'alternative_details')) {
                    $table->text('alternative_details')->nullable()->after('alternative_offered');
                }
                if (!Schema::hasColumn('turnaway_logs', 'turned_away_by')) {
                    $table->unsignedBigInteger('turned_away_by')->nullable()->after('alternative_details');
                }
            });
        }

        if (Schema::hasTable('turnaway_reasons')) {
            Schema::table('turnaway_reasons', function (Blueprint $table) {
                if (!Schema::hasColumn('turnaway_reasons', 'is_active')) {
                    $table->boolean('is_active')->default(true)->after('name_ar');
                }
            });
        }

        if (Schema::hasTable('company_groups')) {
            Schema::table('company_groups', function (Blueprint $table) {
                if (!Schema::hasColumn('company_groups', 'group_code')) {
                    $table->string('group_code')->nullable()->after('name_ar');
                }
                if (!Schema::hasColumn('company_groups', 'description')) {
                    $table->text('description')->nullable()->after('group_code');
                }
                if (!Schema::hasColumn('company_groups', 'notes')) {
                    $table->text('notes')->nullable()->after('description');
                }
                if (!Schema::hasColumn('company_groups', 'is_active')) {
                    $table->boolean('is_active')->default(true)->after('notes');
                }
            });
        }

        if (Schema::hasTable('company_notes')) {
            Schema::table('company_notes', function (Blueprint $table) {
                if (!Schema::hasColumn('company_notes', 'note_type')) {
                    $table->string('note_type')->default('general')->after('body');
                }
                if (!Schema::hasColumn('company_notes', 'priority')) {
                    $table->string('priority')->default('normal')->after('note_type');
                }
                if (!Schema::hasColumn('company_notes', 'pinned')) {
                    $table->boolean('pinned')->default(false)->after('priority');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('company_notes')) {
            Schema::table('company_notes', function (Blueprint $table) {
                if (Schema::hasColumn('company_notes', 'note_type')) $table->dropColumn('note_type');
                if (Schema::hasColumn('company_notes', 'priority')) $table->dropColumn('priority');
                if (Schema::hasColumn('company_notes', 'pinned')) $table->dropColumn('pinned');
            });
        }

        if (Schema::hasTable('company_groups')) {
            Schema::table('company_groups', function (Blueprint $table) {
                if (Schema::hasColumn('company_groups', 'group_code')) $table->dropColumn('group_code');
                if (Schema::hasColumn('company_groups', 'description')) $table->dropColumn('description');
                if (Schema::hasColumn('company_groups', 'notes')) $table->dropColumn('notes');
                if (Schema::hasColumn('company_groups', 'is_active')) $table->dropColumn('is_active');
            });
        }

        if (Schema::hasTable('turnaway_reasons')) {
            Schema::table('turnaway_reasons', function (Blueprint $table) {
                if (Schema::hasColumn('turnaway_reasons', 'is_active')) $table->dropColumn('is_active');
            });
        }

        if (Schema::hasTable('turnaway_logs')) {
            Schema::table('turnaway_logs', function (Blueprint $table) {
                if (Schema::hasColumn('turnaway_logs', 'guest_id')) {
                    $table->dropForeign(['guest_id']);
                    $table->dropColumn('guest_id');
                }
                if (Schema::hasColumn('turnaway_logs', 'requested_date')) $table->dropColumn('requested_date');
                if (Schema::hasColumn('turnaway_logs', 'requested_nights')) $table->dropColumn('requested_nights');
                if (Schema::hasColumn('turnaway_logs', 'reason')) $table->dropColumn('reason');
                if (Schema::hasColumn('turnaway_logs', 'reason_detail')) $table->dropColumn('reason_detail');
                if (Schema::hasColumn('turnaway_logs', 'estimated_revenue_loss')) $table->dropColumn('estimated_revenue_loss');
                if (Schema::hasColumn('turnaway_logs', 'alternative_offered')) $table->dropColumn('alternative_offered');
                if (Schema::hasColumn('turnaway_logs', 'alternative_details')) $table->dropColumn('alternative_details');
                if (Schema::hasColumn('turnaway_logs', 'turned_away_by')) $table->dropColumn('turned_away_by');
            });
        }

        if (Schema::hasTable('blocked_guests')) {
            Schema::table('blocked_guests', function (Blueprint $table) {
                if (Schema::hasColumn('blocked_guests', 'guest_id')) {
                    $table->dropForeign(['guest_id']);
                    $table->dropColumn('guest_id');
                }
                if (Schema::hasColumn('blocked_guests', 'severity')) $table->dropColumn('severity');
                if (Schema::hasColumn('blocked_guests', 'notes')) $table->dropColumn('notes');
                if (Schema::hasColumn('blocked_guests', 'is_active')) $table->dropColumn('is_active');
                if (Schema::hasColumn('blocked_guests', 'blocked_at')) $table->dropColumn('blocked_at');
                if (Schema::hasColumn('blocked_guests', 'unblocked_at')) $table->dropColumn('unblocked_at');
                if (Schema::hasColumn('blocked_guests', 'unblocked_by')) $table->dropColumn('unblocked_by');
            });
        }
    }
};
