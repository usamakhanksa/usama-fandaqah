<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('payment_correction_logs')) {
            Schema::create('payment_correction_logs', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('team_id')->index();
                $t->unsignedBigInteger('frozen_transaction_id')->comment('The original frozen transaction being corrected');
                $t->unsignedBigInteger('created_by')->nullable()->comment('User who performed the correction');

                // What the original transaction had
                $t->string('original_payment_type', 50)->nullable();
                $t->decimal('original_amount', 14, 2);

                // What the correction specifies
                $t->string('correct_payment_type', 50);
                $t->decimal('correct_amount', 14, 2);

                // Correction type resolved
                $t->enum('correction_type', [
                    'wrong_payment_method',  // withdraw + re-deposit, same amount
                    'overcharge',            // withdraw for difference
                    'undercharge',           // supplementary deposit
                    'wrong_method_and_amount', // both wrong — full correction
                ]);

                // Resulting transaction IDs (nullable until created)
                $t->unsignedBigInteger('correction_withdraw_id')->nullable()
                    ->comment('Reversal withdraw transaction');
                $t->unsignedBigInteger('correction_deposit_id')->nullable()
                    ->comment('New deposit transaction (method change or undercharge)');

                // Business date on which corrections were posted
                $t->date('posted_business_date')->nullable();

                $t->text('reason')->nullable();
                $t->timestamps();

                $t->foreign('team_id')->references('id')->on('teams')->cascadeOnDelete();
                $t->index(['team_id', 'frozen_transaction_id'], 'idx_pcl_team_tx');
            });
        }

        // Seed the finance.payment_correction permission (idempotent)
        if (Schema::hasTable('permissions')) {
            \DB::table('permissions')->insertOrIgnore([
                'name'       => 'finance.payment_correction',
                'slug'       => 'finance.payment_correction',
                'group'      => 'finance',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_correction_logs');
    }
};
