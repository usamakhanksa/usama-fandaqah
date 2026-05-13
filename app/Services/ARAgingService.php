<?php

namespace App\Services;

use App\Models\Promissory;
use App\Models\CompanyProfile;
use App\Models\CompanyGroup;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ARAgingService
{
    public function getAgingReport($teamId, $filters = [])
    {
        $query = CompanyProfile::whereHas('promissories', function($q) {
            $q->where('status', 'pending');
        });

        if (!empty($filters['company_group_id'])) {
            $query->where('company_group_id', $filters['company_group_id']);
        }

        if (!empty($filters['company_id'])) {
            $query->where('id', $filters['company_id']);
        }

        $companies = $query->with(['companyGroup', 'promissories' => function($q) {
            $q->where('status', 'pending');
        }])->get();

        $report = $companies->map(function($company) {
            $promissories = $company->promissories;
            
            $buckets = [
                'current' => 0,
                '30' => 0,
                '60' => 0,
                '90' => 0,
                '90plus' => 0,
            ];

            foreach ($promissories as $p) {
                $days = Carbon::parse($p->created_at)->diffInDays(now());
                $balance = $p->total_amount - $p->collected_amount;

                if ($days <= 30) $buckets['30'] += $balance;
                elseif ($days <= 60) $buckets['60'] += $balance;
                elseif ($days <= 90) $buckets['90'] += $balance;
                else $buckets['90plus'] += $balance;
                
                $buckets['current'] += $balance;
            }

            return [
                'company_id' => $company->id,
                'company_name' => $company->company_name,
                'group_name' => $company->companyGroup->name ?? 'No Group',
                'credit_limit' => $company->credit_limit,
                'balance' => $buckets['current'],
                'utilization' => $company->credit_limit > 0 ? round(($buckets['current'] / $company->credit_limit) * 100, 2) : 0,
                'buckets' => $buckets,
            ];
        });

        return $report;
    }

    public function getGroupExposure($teamId)
    {
        $groups = CompanyGroup::where('team_id', $teamId)
            ->with(['companies.promissories' => function($q) {
                $q->where('status', 'pending');
            }])->get();

        return $groups->map(function($group) {
            $totalExposure = 0;
            foreach ($group->companies as $company) {
                foreach ($company->promissories as $p) {
                    $totalExposure += ($p->total_amount - $p->collected_amount);
                }
            }

            return [
                'id' => $group->id,
                'name' => $group->name,
                'credit_limit' => $group->credit_limit,
                'exposure' => $totalExposure,
                'available' => $group->credit_limit - $totalExposure,
                'utilization' => $group->credit_limit > 0 ? round(($totalExposure / $group->credit_limit) * 100, 2) : 0,
            ];
        });
    }

    public function getDashboardStats($teamId)
    {
        $exposure = $this->getGroupExposure($teamId);
        
        return [
            'total_receivable' => $exposure->sum('exposure'),
            'total_limit' => $exposure->sum('credit_limit'),
            'avg_utilization' => $exposure->avg('utilization'),
            'group_stats' => $exposure,
            'aging_summary' => $this->getAgingSummary($teamId),
        ];
    }

    private function getAgingSummary($teamId)
    {
        $promissories = Promissory::where('team_id', $teamId)
            ->where('status', 'pending')
            ->get();

        $summary = [
            '30' => 0, '60' => 0, '90' => 0, '90plus' => 0
        ];

        foreach ($promissories as $p) {
            $days = Carbon::parse($p->created_at)->diffInDays(now());
            $balance = $p->total_amount - $p->collected_amount;

            if ($days <= 30) $summary['30'] += $balance;
            elseif ($days <= 60) $summary['60'] += $balance;
            elseif ($days <= 90) $summary['90'] += $balance;
            else $summary['90plus'] += $balance;
        }

        return $summary;
    }
}
