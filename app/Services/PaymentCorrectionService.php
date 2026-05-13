<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\PaymentCorrectionLog;
use App\Team;
use App\Transaction;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PaymentCorrectionService
{
    /**
     * Execute a payment correction for a frozen transaction.
     *
     * Rules:
     *  - The original transaction is NEVER modified.
     *  - Wrong payment method  → correction withdraw + new deposit (same amount, new method)
     *  - Overcharge            → correction withdraw for the difference
     *  - Undercharge           → supplementary deposit for the difference
     *  - Both wrong            → full correction withdraw + new deposit at correct amount/method
     *  - All correction transactions are linked via correction_of_transaction_id
     *  - Corrections are posted to the current open business date of the team
     *
     * @param  Transaction  $frozen
     * @param  string       $correctPaymentType
     * @param  float        $correctAmount
     * @param  string|null  $reason
     * @param  User         $actor
     * @return PaymentCorrectionLog
     */
    public function correct(
        Transaction $frozen,
        string $correctPaymentType,
        float $correctAmount,
        ?string $reason,
        User $actor
    ): PaymentCorrectionLog {
        if (!$frozen->is_freezed) {
            throw new \RuntimeException('Only frozen transactions can be corrected.');
        }

        $team            = Team::findOrFail($frozen->team_id);
        $businessDate    = $team->business_date;
        $originalAmount  = abs($frozen->amount) / 100;       // stored in cents
        $originalMethod  = $frozen->meta['payment_type'] ?? 'unknown';

        $methodChanged  = ($originalMethod !== $correctPaymentType);
        $amountChanged  = (abs($originalAmount - $correctAmount) >= 0.01);
        $difference     = round($correctAmount - $originalAmount, 2);

        // Determine correction type
        $correctionType = $this->resolveCorrectionType($methodChanged, $amountChanged, $difference);

        $withdrawId = null;
        $depositId  = null;

        DB::transaction(function () use (
            $frozen, $correctPaymentType, $correctAmount, $originalAmount,
            $businessDate, $actor, $difference, $correctionType,
            &$withdrawId, &$depositId
        ) {
            switch ($correctionType) {
                case 'wrong_payment_method':
                    // Withdraw the original amount, re-deposit with new method
                    $withdrawId = $this->createCorrectionWithdraw($frozen, $originalAmount, $correctPaymentType, $businessDate, $actor);
                    $depositId  = $this->createCorrectionDeposit($frozen, $originalAmount, $correctPaymentType, $businessDate, $actor);
                    break;

                case 'overcharge':
                    // Withdraw only the difference (over-collected)
                    $withdrawId = $this->createCorrectionWithdraw($frozen, abs($difference), $correctPaymentType, $businessDate, $actor);
                    break;

                case 'undercharge':
                    // Supplementary deposit for the shortfall
                    $depositId = $this->createCorrectionDeposit($frozen, abs($difference), $correctPaymentType, $businessDate, $actor);
                    break;

                case 'wrong_method_and_amount':
                    // Full reversal + new deposit at correct amount/method
                    $withdrawId = $this->createCorrectionWithdraw($frozen, $originalAmount, $correctPaymentType, $businessDate, $actor);
                    $depositId  = $this->createCorrectionDeposit($frozen, $correctAmount, $correctPaymentType, $businessDate, $actor);
                    break;
            }
        });

        $log = PaymentCorrectionLog::create([
            'team_id'                  => $frozen->team_id,
            'frozen_transaction_id'    => $frozen->id,
            'created_by'               => $actor->id,
            'original_payment_type'    => $originalMethod,
            'original_amount'          => $originalAmount,
            'correct_payment_type'     => $correctPaymentType,
            'correct_amount'           => $correctAmount,
            'correction_type'          => $correctionType,
            'correction_withdraw_id'   => $withdrawId,
            'correction_deposit_id'    => $depositId,
            'posted_business_date'     => $businessDate,
            'reason'                   => $reason,
        ]);

        // Activity log
        ActivityLog::create([
            'log_name'       => 'finance',
            'description'    => "Payment correction ({$correctionType}) applied to frozen transaction #{$frozen->id}",
            'subject_type'   => PaymentCorrectionLog::class,
            'subject_id'     => $log->id,
            'causer_type'    => User::class,
            'causer_id'      => $actor->id,
            'properties'     => json_encode([
                'frozen_tx_id'         => $frozen->id,
                'correction_type'      => $correctionType,
                'original_amount'      => $originalAmount,
                'correct_amount'       => $correctAmount,
                'original_method'      => $log->original_payment_type,
                'correct_method'       => $correctPaymentType,
                'reason'               => $reason,
                'posted_business_date' => $businessDate,
            ]),
        ]);

        return $log;
    }

    // ─── Private helpers ────────────────────────────────────────────────────────

    private function resolveCorrectionType(bool $methodChanged, bool $amountChanged, float $difference): string
    {
        if ($methodChanged && $amountChanged) return 'wrong_method_and_amount';
        if ($methodChanged)                  return 'wrong_payment_method';
        if ($amountChanged && $difference < 0) return 'overcharge';
        if ($amountChanged && $difference > 0) return 'undercharge';

        throw new \RuntimeException('No difference detected — correction is unnecessary.');
    }

    /** Create a correction withdraw transaction */
    private function createCorrectionWithdraw(
        Transaction $frozen,
        float $amount,
        string $paymentType,
        ?string $businessDate,
        User $actor
    ): int {
        $meta = array_merge((array) $frozen->meta, [
            'payment_type'             => $paymentType,
            'is_correction'            => true,
            'correction_direction'     => 'withdraw',
            'business_date'            => $businessDate,
        ]);

        return DB::table('transactions')->insertGetId([
            'payable_type'                => $frozen->payable_type,
            'payable_id'                  => $frozen->payable_id,
            'wallet_id'                   => $frozen->wallet_id,
            'type'                        => 'withdraw',
            'transaction_flag'            => 'managerial',
            'amount'                      => -abs((int) round($amount * 100)),
            'amount_without_tax'          => -abs((int) round($amount * 100)),
            'confirmed'                   => 1,
            'is_public'                   => 1,
            'is_freezed'                  => 0,
            'uuid'                        => (string) Str::uuid(),
            'correction_of_transaction_id' => $frozen->id,
            'team_id'                     => $frozen->team_id,
            'created_by'                  => $actor->id,
            'meta'                        => json_encode($meta),
            'description'                 => 'Correction withdraw for frozen TX#' . $frozen->id,
            'created_at'                  => now(),
            'updated_at'                  => now(),
        ]);
    }

    /** Create a correction deposit transaction */
    private function createCorrectionDeposit(
        Transaction $frozen,
        float $amount,
        string $paymentType,
        ?string $businessDate,
        User $actor
    ): int {
        $meta = array_merge((array) $frozen->meta, [
            'payment_type'             => $paymentType,
            'is_correction'            => true,
            'correction_direction'     => 'deposit',
            'business_date'            => $businessDate,
        ]);

        return DB::table('transactions')->insertGetId([
            'payable_type'                => $frozen->payable_type,
            'payable_id'                  => $frozen->payable_id,
            'wallet_id'                   => $frozen->wallet_id,
            'type'                        => 'deposit',
            'transaction_flag'            => 'managerial',
            'amount'                      => abs((int) round($amount * 100)),
            'amount_without_tax'          => abs((int) round($amount * 100)),
            'confirmed'                   => 1,
            'is_public'                   => 1,
            'is_freezed'                  => 0,
            'uuid'                        => (string) Str::uuid(),
            'correction_of_transaction_id' => $frozen->id,
            'team_id'                     => $frozen->team_id,
            'created_by'                  => $actor->id,
            'meta'                        => json_encode($meta),
            'description'                 => 'Correction deposit for frozen TX#' . $frozen->id,
            'created_at'                  => now(),
            'updated_at'                  => now(),
        ]);
    }
}
