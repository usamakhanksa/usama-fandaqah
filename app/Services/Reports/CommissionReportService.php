<?php

namespace App\Services\Reports;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Reservation;
use App\Models\CommissionPayment;
use App\Models\Source;

class CommissionReportService extends ReportService
{
    /**
     * Get commission owed by travel agent/OTA for a period
     */
    public function getCommissionSummary(Carbon $startDate, Carbon $endDate)
    {
        $teamId = $this->teamId;

        // Get all sources that are travel agents/OTA with commission rate
        $sources = Source::where('team_id', $teamId)
            ->where('is_travel_agent', true)
            ->withCount(['reservations as reservation_count' => function($q) use ($startDate, $endDate) {
                $q->whereDate('check_in', '>=', $startDate)
                  ->whereDate('check_in', '<=', $endDate);
            }])
            ->withSum(['reservations as total_room_revenue' => function($q) use ($startDate, $endDate) {
                $q->whereDate('check_in', '>=', $startDate)
                  ->whereDate('check_in', '<=', $endDate);
            }], 'room_revenue')
            ->get();

        $results = [];
        foreach ($sources as $source) {
            $commissionRate = $source->commission_rate ?? 0;
            $totalRevenue = $source->total_room_revenue ?? 0;
            $commissionAmount = $totalRevenue * ($commissionRate / 100);

            $results[] = [
                'source_id' => $source->id,
                'source_name' => $source->name,
                'commission_rate' => $commissionRate,
                'reservation_count' => $source->reservation_count,
                'total_revenue' => $totalRevenue,
                'commission_amount' => round($commissionAmount, 2),
                'paid_amount' => 0, // Will be calculated from payments
                'unpaid_amount' => round($commissionAmount, 2),
            ];
        }

        return $results;
    }

    /**
     * Get commission by reservation with details
     */
    public function getCommissionByReservation(Carbon $startDate, Carbon $endDate, $sourceId = null)
    {
        $teamId = $this->teamId;

        $query = Reservation::where('team_id', $teamId)
            ->whereDate('check_in', '>=', $startDate)
            ->whereDate('check_in', '<=', $endDate)
            ->whereHas('source', function($q) {
                $q->where('is_travel_agent', true);
            })
            ->with(['source', 'unit.unitType', 'guest']);

        if ($sourceId) {
            $query->where('source_id', $sourceId);
        }

        $reservations = $query->get();

        $data = [];
        foreach ($reservations as $reservation) {
            $source = $reservation->source;
            $commissionRate = $source?->commission_rate ?? 0;
            $roomRevenue = $reservation->room_revenue ?? $reservation->total_amount;
            $commissionAmount = $roomRevenue * ($commissionRate / 100);

            // Check if paid via CommissionPaymentDetail
            $paid = DB::table('commission_payment_details')
                ->where('reservation_id', $reservation->id)
                ->sum('commission_amount');

            $data[] = [
                'reservation_id' => $reservation->id,
                'reservation_code' => $reservation->code,
                'check_in' => $reservation->check_in?->toDateString(),
                'check_out' => $reservation->check_out?->toDateString(),
                'source_name' => $source?->name ?? 'N/A',
                'guest_name' => $reservation->guest?->full_name ?? 'N/A',
                'room_revenue' => $roomRevenue,
                'commission_rate' => $commissionRate,
                'commission_amount' => round($commissionAmount, 2),
                'paid_amount' => $paid,
                'unpaid_amount' => round($commissionAmount - $paid, 2),
                'status' => $paid >= $commissionAmount ? 'Paid' : 'Unpaid',
            ];
        }

        return $data;
    }

    /**
     * Get paid vs unpaid commissions summary
     */
    public function getPaidUnpaidSummary(Carbon $startDate, Carbon $endDate)
    {
        $teamId = $this->teamId;

        $paid = DB::table('commission_payment_details')
            ->join('commission_payments', 'commission_payment_details.commission_payment_id', '=', 'commission_payments.id')
            ->where('commission_payments.team_id', $teamId)
            ->whereBetween('commission_payments.payment_date', [$startDate, $endDate])
            ->where('commission_payments.status', 'paid')
            ->sum('commission_payment_details.commission_amount');

        $unpaid = $this->getTotalUnpaidCommission($startDate, $endDate);

        return [
            'total_commission' => $paid + $unpaid,
            'paid_commission' => $paid,
            'unpaid_commission' => $unpaid,
            'paid_percentage' => ($paid + $unpaid) > 0 ? round(($paid / ($paid + $unpaid)) * 100, 2) : 0,
        ];
    }

    /**
     * Get unpaid commissions
     */
    public function getUnpaidCommissions(Carbon $startDate, Carbon $endDate)
    {
        return $this->getCommissionByReservation($startDate, $endDate)
            ->where('status', 'Unpaid')
            ->values();
    }

    /**
     * Compare commission rates across agents
     */
    public function getCommissionRateComparison()
    {
        $teamId = $this->teamId;

        return Source::where('team_id', $teamId)
            ->where('is_travel_agent', true)
            ->whereNotNull('commission_rate')
            ->select(
                'name',
                'commission_rate',
                DB::raw('COUNT(reservations.id) as reservation_count')
            )
            ->leftJoin('reservations', 'sources.id', '=', 'reservations.source_id')
            ->groupBy('sources.id', 'sources.name', 'sources.commission_rate')
            ->orderBy('commission_rate', 'desc')
            ->get();
    }

    /**
     * Get total unpaid commission for a period
     */
    private function getTotalUnpaidCommission(Carbon $startDate, Carbon $endDate)
    {
        $teamId = $this->teamId;

        // Sum of all reservation-based commissions minus what's been paid
        $totalCommission = DB::table('reservations')
            ->join('sources', 'reservations.source_id', '=', 'sources.id')
            ->where('reservations.team_id', $teamId)
            ->whereDate('reservations.check_in', '>=', $startDate)
            ->whereDate('reservations.check_in', '<=', $endDate)
            ->where('sources.is_travel_agent', true)
            ->select(DB::raw('SUM(reservations.total_amount * (sources.commission_rate / 100)) as total'))
            ->value('total') ?? 0;

        $paid = DB::table('commission_payment_details')
            ->join('commission_payments', 'commission_payment_details.commission_payment_id', '=', 'commission_payments.id')
            ->where('commission_payments.team_id', $teamId)
            ->whereBetween('commission_payments.payment_date', [$startDate, $endDate])
            ->sum('commission_payment_details.commission_amount');

        return max(0, $totalCommission - $paid);
    }
}
