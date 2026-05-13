<?php

namespace App\Services;

use App\Models\CommissionPayment;
use App\Reservation;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CommissionService
{
    /**
     * Calculate and create/update commission for a reservation.
     */
    public function processReservationCommission(Reservation $reservation)
    {
        $source = $reservation->source;
        if (!$source || !$source->is_travel_agent) {
            return null;
        }

        // Calculate room revenue base (usually total price minus taxes/services if specified)
        // For simplicity, we use total_price here or a custom room_revenue field if exists
        $roomRevenue = $reservation->total_price; 
        
        $rate = $source->commission_rate ?: 0;
        $type = $source->commission_type ?: 'percentage';
        
        $amount = 0;
        if ($type === 'percentage') {
            $amount = ($roomRevenue * $rate) / 100;
        } else {
            $amount = $rate; // Fixed
        }

        return CommissionPayment::updateOrCreate(
            ['reservation_id' => $reservation->id],
            [
                'source_id' => $source->id,
                'team_id' => $reservation->team_id,
                'period_from' => $reservation->date_in,
                'period_to' => $reservation->date_out,
                'room_revenue_base' => $roomRevenue,
                'commission_rate' => $rate,
                'commission_type' => $type,
                'commission_amount' => $amount,
                'status' => 'pending'
            ]
        );
    }

    /**
     * Approve a commission payment.
     */
    public function approveCommission(CommissionPayment $payment, $userId)
    {
        if ($payment->status !== 'pending') {
            throw new \Exception('Only pending commissions can be approved.');
        }

        $payment->update([
            'status' => 'approved',
            'approved_by' => $userId,
            'approved_at' => now()
        ]);

        return $payment;
    }

    /**
     * Mark a commission as paid.
     */
    public function markAsPaid(CommissionPayment $payment, $reference, $paidAt = null)
    {
        if ($payment->status !== 'approved') {
            throw new \Exception('Only approved commissions can be marked as paid.');
        }

        $payment->update([
            'status' => 'paid',
            'paid_at' => $paidAt ?: now(),
            'payment_reference' => $reference
        ]);

        return $payment;
    }

    /**
     * Get monthly summary for a team.
     */
    public function getMonthlySummary($teamId, $year = null, $month = null)
    {
        $year = $year ?: now()->year;
        $month = $month ?: now()->month;

        return CommissionPayment::where('team_id', $teamId)
            ->whereYear('period_from', $year)
            ->whereMonth('period_from', $month)
            ->select('status', DB::raw('count(*) as count'), DB::raw('sum(commission_amount) as total'))
            ->groupBy('status')
            ->get();
    }
}
