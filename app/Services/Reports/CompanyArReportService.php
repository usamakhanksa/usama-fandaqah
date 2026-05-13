<?php

namespace App\Services\Reports;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\Promissory;

class CompanyArReportService extends ReportService
{
    /**
     * Get AR summary by company with aging buckets
     */
    public function getArSummary(Carbon $asOfDate)
    {
        $teamId = $this->teamId;

        // Get all companies with outstanding balances
        $companies = Company::where('team_id', $teamId)->get();
        
        $results = [];
        foreach ($companies as $company) {
            // Get all unpaid/partial paid invoices for this company
            $invoices = Invoice::where('team_id', $teamId)
                ->where('company_id', $company->id)
                ->whereIn('status', ['sent', 'partially_paid'])
                ->where('invoice_date', '<=', $asOfDate)
                ->get();

            $totalOutstanding = $invoices->sum('amount_due');
            $totalCurrent = 0;
            $total30 = $total60 = $total90 = $total120 = 0;

            foreach ($invoices as $invoice) {
                $daysOutstanding = $asOfDate->diffInDays($invoice->invoice_date);
                $amount = $invoice->amount_due;

                if ($daysOutstanding <= 0) {
                    $totalCurrent += $amount;
                } elseif ($daysOutstanding <= 30) {
                    $total30 += $amount;
                } elseif ($daysOutstanding <= 60) {
                    $total60 += $amount;
                } elseif ($daysOutstanding <= 90) {
                    $total90 += $amount;
                } else {
                    $total120 += $amount;
                }
            }

            if ($totalOutstanding > 0) {
                $results[] = [
                    'company_id' => $company->id,
                    'company_name' => $company->name,
                    'current' => $totalCurrent,
                    'days_30' => $total30,
                    'days_60' => $total60,
                    'days_90' => $total90,
                    'days_120_plus' => $total120,
                    'total_outstanding' => $totalOutstanding,
                ];
            }
        }

        return collect($results)->sortByDesc('total_outstanding')->values();
    }

    /**
     * Get payment history for a specific company
     */
    public function getPaymentHistory($companyId, Carbon $startDate, Carbon $endDate)
    {
        $teamId = $this->teamId;

        return DB::table('transactions')
            ->where('team_id', $teamId)
            ->where('payable_type', 'App\Models\Invoice')
            ->whereHas('payable', function($q) use ($companyId) {
                $q->where('company_id', $companyId);
            })
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->select(
                DB::raw('DATE(created_at) as payment_date'),
                DB::raw('SUM(amount) as total_paid'),
                DB::raw('COUNT(*) as payment_count')
            )
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('payment_date')
            ->get();
    }

    /**
     * Get AR aging summary (totals across all companies)
     */
    public function getAgingSummary(Carbon $asOfDate)
    {
        $teamId = $this->teamId;

        $invoices = Invoice::where('team_id', $teamId)
            ->whereIn('status', ['sent', 'partially_paid'])
            ->where('invoice_date', '<=', $asOfDate)
            ->get();

        $totalCurrent = $total30 = $total60 = $total90 = $total120 = 0;

        foreach ($invoices as $invoice) {
            $daysOutstanding = $asOfDate->diffInDays($invoice->invoice_date);
            $amount = $invoice->amount_due;

            if ($daysOutstanding <= 0) {
                $totalCurrent += $amount;
            } elseif ($daysOutstanding <= 30) {
                $total30 += $amount;
            } elseif ($daysOutstanding <= 60) {
                $total60 += $amount;
            } elseif ($daysOutstanding <= 90) {
                $total90 += $amount;
            } else {
                $total120 += $amount;
            }
        }

        return [
            'current' => $totalCurrent,
            'days_30' => $total30,
            'days_60' => $total60,
            'days_90' => $total90,
            'days_120_plus' => $total120,
            'total' => $totalCurrent + $total30 + $total60 + $total90 + $total120,
        ];
    }

    /**
     * Calculate AR turnover ratio
     */
    public function getArTurnoverRatio(Carbon $startDate, Carbon $endDate)
    {
        $teamId = $this->teamId;

        // Total credit sales for the period
        $creditSales = Invoice::where('team_id', $teamId)
            ->whereBetween('invoice_date', [$startDate, $endDate])
            ->sum('total_amount');

        // Average AR = (Beginning AR + Ending AR) / 2
        $beginningAR = $this->getArTotal($startDate);
        $endingAR = $this->getArTotal($endDate);
        $averageAR = ($beginningAR + $endingAR) / 2;

        if ($averageAR > 0) {
            $turnoverRatio = $creditSales / $averageAR;
            $daysSalesOutstanding = $averageAR > 0 ? (365 / $turnoverRatio) : 0;
            
            return [
                'credit_sales' => $creditSales,
                'average_ar' => $averageAR,
                'turnover_ratio' => round($turnoverRatio, 2),
                'days_sales_outstanding' => round($daysSalesOutstanding, 0),
            ];
        }

        return [
            'credit_sales' => $creditSales,
            'average_ar' => 0,
            'turnover_ratio' => 0,
            'days_sales_outstanding' => 0,
        ];
    }

    private function getArTotal(Carbon $date)
    {
        $invoices = Invoice::where('invoice_date', '<=', $date)
            ->get();
        
        $total = 0;
        foreach ($invoices as $invoice) {
            $payments = $invoice->transactions()->where('kind', 'payment')->sum('amount');
            $total += $invoice->total_amount - $payments;
        }
        
        return $total;
    }
}
