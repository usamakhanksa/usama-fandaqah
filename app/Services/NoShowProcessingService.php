<?php

namespace App\Services;

use App\Models\Reservation;
use App\Models\NoShowChargeRule;
use App\Models\NightAuditNoshowLog;
use App\Models\NightAuditLog;
use App\Team;
use Illuminate\Support\Facades\DB;

class NoShowProcessingService
{
    public function process(Team $team, NightAuditLog $auditLog)
    {
        $businessDate = $team->business_date;
        
        $noShows = Reservation::where('team_id', $team->id)
            ->whereDate('check_in', '<=', $businessDate)
            ->whereIn('status', ['confirmed', 'partial'])
            ->get();

        $processedCount = 0;
        $chargedCount = 0;

        foreach ($noShows as $res) {
            $rule = $this->getApplicableRule($team, $businessDate);
            $chargeAmount = 0;
            $transactionId = null;

            if ($rule) {
                $chargeAmount = $this->calculateCharge($res, $rule);
                if ($chargeAmount > 0) {
                    $transactionId = $this->postNoShowCharge($res, $chargeAmount, $businessDate);
                    $chargedCount++;
                }
            }

            // Update reservation
            $res->status = 'no_show';
            $res->save();

            // Log
            \DB::table('night_audit_noshow_log')->insert([
                'night_audit_log_id' => $auditLog->id,
                'reservation_id' => $res->id,
                'team_id' => $team->id,
                'business_date' => $businessDate,
                'original_date_in' => $res->check_in,
                'charge_amount' => $chargeAmount,
                'charge_transaction_id' => $transactionId,
                'rule_id' => $rule ? $rule->id : null,
                'action_taken' => $chargeAmount > 0 ? 'charged_and_cancelled' : 'cancelled_only',
                'created_at' => now()
            ]);

            $processedCount++;
        }

        return [
            'flagged' => $processedCount,
            'charged' => $chargedCount
        ];
    }

    protected function getApplicableRule($team, $date)
    {
        return NoShowChargeRule::where('team_id', $team->id)
            ->where('is_active', true)
            ->whereDate('start_date', '<=', $date)
            ->whereDate('end_date', '>=', $date)
            ->first();
    }

    protected function calculateCharge($reservation, $rule)
    {
        if ($rule->charge_type === 'fixed') {
            return $rule->charge_amount;
        }
        
        // If percentage, we need the room rate. Assuming $reservation has total_price or similar.
        // For now, let's use a dummy logic if room_rate is missing.
        $rate = $reservation->total_price ?: 0;
        return ($rate * $rule->charge_amount) / 100;
    }

    protected function postNoShowCharge($reservation, $amount, $businessDate)
    {
        return DB::table('transactions')->insertGetId([
            'payable_type' => 'App\Models\Reservation',
            'payable_id' => $reservation->id,
            'team_id' => $reservation->team_id,
            'type' => 'withdraw',
            'amount' => $amount * 100, // Assuming storage in cents/halalas
            'amount_without_tax' => $amount * 100,
            'description' => 'No-show charge posted by night audit',
            'confirmed' => true,
            'uuid' => \Illuminate\Support\Str::uuid(),
            'created_at' => now(),
            'updated_at' => now(),
            'is_freezed' => true // Immediate freeze for audit postings
        ]);
    }
}
