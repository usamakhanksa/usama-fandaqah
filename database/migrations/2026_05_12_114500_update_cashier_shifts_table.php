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
        Schema::table('cashier_shifts', function (Blueprint $blueprint) {
            // Rename/Adjust existing columns if necessary, but better to just add missing ones
            if (!Schema::hasColumn('cashier_shifts', 'shift_number')) {
                $blueprint->string('shift_number')->nullable()->after('id');
            }
            if (!Schema::hasColumn('cashier_shifts', 'expected_closing_balance')) {
                $blueprint->decimal('expected_closing_balance', 12, 2)->nullable()->after('opening_balance');
            }
            if (!Schema::hasColumn('cashier_shifts', 'actual_closing_balance')) {
                $blueprint->decimal('actual_closing_balance', 12, 2)->nullable()->after('expected_closing_balance');
            }
            if (!Schema::hasColumn('cashier_shifts', 'variance_reason')) {
                $blueprint->text('variance_reason')->nullable()->after('variance');
            }
            if (!Schema::hasColumn('cashier_shifts', 'total_cash_received')) {
                $blueprint->decimal('total_cash_received', 12, 2)->default(0)->after('variance_reason');
            }
            if (!Schema::hasColumn('cashier_shifts', 'total_cash_paid')) {
                $blueprint->decimal('total_cash_paid', 12, 2)->default(0)->after('total_cash_received');
            }
            if (!Schema::hasColumn('cashier_shifts', 'total_card_received')) {
                $blueprint->decimal('total_card_received', 12, 2)->default(0)->after('total_cash_paid');
            }
            if (!Schema::hasColumn('cashier_shifts', 'total_other_received')) {
                $blueprint->decimal('total_other_received', 12, 2)->default(0)->after('total_card_received');
            }
            if (!Schema::hasColumn('cashier_shifts', 'total_transactions')) {
                $blueprint->integer('total_transactions')->default(0)->after('total_other_received');
            }
            if (!Schema::hasColumn('cashier_shifts', 'approval_notes')) {
                $blueprint->text('approval_notes')->nullable()->after('approved_at');
            }
            if (!Schema::hasColumn('cashier_shifts', 'rejected_by')) {
                $blueprint->foreignId('rejected_by')->nullable()->constrained('users')->after('approval_notes');
            }
            if (!Schema::hasColumn('cashier_shifts', 'rejected_at')) {
                $blueprint->timestamp('rejected_at')->nullable()->after('rejected_by');
            }
            if (!Schema::hasColumn('cashier_shifts', 'rejection_reason')) {
                $blueprint->text('rejection_reason')->nullable()->after('rejected_at');
            }
            if (!Schema::hasColumn('cashier_shifts', 'deleted_at')) {
                $blueprint->softDeletes();
            }

            // Change status enum if needed
            // $blueprint->enum('status', ['open', 'closed', 'pending_approval', 'approved', 'rejected'])->change();
        });

        Schema::create('cashier_shift_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cashier_shift_id')->constrained('cashier_shifts')->cascadeOnDelete();
            $table->foreignId('transaction_id')->constrained('payments')->cascadeOnDelete(); // Assuming transactions are in payments table based on previous conversations or generic 'transactions'
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cashier_shift_transactions');
        Schema::table('cashier_shifts', function (Blueprint $table) {
            // Drop columns in reverse if needed
        });
    }
};
