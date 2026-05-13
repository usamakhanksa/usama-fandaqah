<?php

namespace App\Services;

use App\Models\CommissionPayment;
use App\Models\CommissionPaymentDetail;
use App\Models\Reservation;
use App\Models\Company;
use App\Models\TeamCounter;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CommissionCalculationService
{
    /**
     * Calculate agent commission based on reservations in a period.
     */
    public function calculateAgentCommission($agentId, $from, $to)
    {
        $reservations = Reservation::where('company_id', $agentId)
            ->whereBetween('check_out', [$from, $to])
            ->where('status', 'checked_out')
            ->with(['guest', 'transactions', 'source'])
            ->get();

        $details = [];
        $totalCommission = 0;

        foreach ($reservations as $reservation) {
            // Room revenue: transactions linked to reservation with category 'reservation'
            $roomRevenue = $reservation->transactions()
                ->where('type', 'withdraw')
                ->where('meta->category', 'reservation')
                ->sum('amount');
            
            // F&B revenue: transactions with category 'pos'
            $fbRevenue = $reservation->transactions()
                ->where('type', 'withdraw')
                ->where('meta->category', 'pos')
                ->sum('amount');

            // Other revenue
            $otherRevenue = $reservation->transactions()
                ->where('type', 'withdraw')
                ->whereNotIn('meta->category', ['reservation', 'pos'])
                ->sum('amount');

            $rate = $reservation->source?->commission_rate ?: 10;
            $amount = ($roomRevenue * $rate) / 100;

            $details[] = [
                'reservation_id' => $reservation->id,
                'reservation_code' => $reservation->code,
                'guest_name' => $reservation->guest?->name ?? 'N/A',
                'room_revenue' => $roomRevenue,
                'fb_revenue' => $fbRevenue,
                'other_revenue' => $otherRevenue,
                'commission_rate' => $rate,
                'commission_amount' => $amount,
            ];

            $totalCommission += $amount;
        }

        return [
            'total_commission' => $totalCommission,
            'details' => $details,
        ];
    }

    /**
     * Generate a commission payment record with details.
     */
    public function generateCommissionPayment($agentId, $from, $to, $paymentData = [])
    {
        return DB::transaction(function () use ($agentId, $from, $to, $paymentData) {
            $calculation = $this->calculateAgentCommission($agentId, $from, $to);
            
            $teamId = auth()->user()->current_team_id ?? Company::find($agentId)->team_id;
            
            // Generate payment number: COM-{YYYY}{MM}-{0001}
            $now = Carbon::now();
            $prefix = 'COM-' . $now->format('Ym') . '-';
            
            $counter = TeamCounter::where('team_id', $teamId)
                ->where('type', 'commission_payment')
                ->first();
                
            if (!$counter) {
                $counter = TeamCounter::create([
                    'team_id' => $teamId,
                    'type' => 'commission_payment',
                    'count' => 0
                ]);
            }
            
            $counter->increment('count');
            $paymentNumber = $prefix . str_pad($counter->count, 4, '0', STR_PAD_LEFT);

            $payment = CommissionPayment::create([
                'team_id' => $teamId,
                'travel_agent_id' => $agentId,
                'commission_period_from' => $from,
                'commission_period_to' => $to,
                'payment_number' => $paymentNumber,
                'total_commission' => $calculation['total_commission'],
                'total_paid' => $paymentData['total_paid'] ?? $calculation['total_commission'],
                'payment_method' => $paymentData['payment_method'] ?? 'bank_transfer',
                'bank_id' => $paymentData['bank_id'] ?? null,
                'reference_number' => $paymentData['reference_number'] ?? null,
                'payment_date' => $paymentData['payment_date'] ?? now(),
                'status' => $paymentData['status'] ?? 'paid',
                'notes' => $paymentData['notes'] ?? null,
                'created_by' => auth()->id() ?? 1,
            ]);

            foreach ($calculation['details'] as $detail) {
                CommissionPaymentDetail::create([
                    'commission_payment_id' => $payment->id,
                    'reservation_id' => $detail['reservation_id'],
                    'commission_rate' => $detail['commission_rate'],
                    'commission_amount' => $detail['commission_amount'],
                    'room_revenue' => $detail['room_revenue'],
                    'fb_revenue' => $detail['fb_revenue'],
                    'other_revenue' => $detail['other_revenue'],
                ]);
            }

            return $payment;
        });
    }

    /**
     * Get agent-wise commission summary for a team and period.
     */
    public function getCommissionSummary($teamId, $period)
    {
        // period could be 'YYYY-MM'
        $start = Carbon::parse($period)->startOfMonth();
        $end = Carbon::parse($period)->endOfMonth();

        return Company::where('team_id', $teamId)
            ->whereHas('reservations', function ($q) use ($start, $end) {
                $q->whereBetween('check_out', [$start, $end])
                  ->where('status', 'checked_out');
            })
            ->get()
            ->map(function ($agent) use ($start, $end) {
                $calc = $this->calculateAgentCommission($agent->id, $start->toDateString(), $end->toDateString());
                return [
                    'agent_id' => $agent->id,
                    'agent_name' => $agent->name,
                    'total_commission' => $calc['total_commission'],
                    'reservations_count' => count($calc['details']),
                ];
            });
    }
}
