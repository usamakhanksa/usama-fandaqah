<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SafeTransaction;
use App\Models\SavedReport;
use Illuminate\Support\Str;

class AnalyticsSeeder extends Seeder {
    public function run(): void {
        // Safe Transactions (Saudi Context)
        SafeTransaction::create([
            'type' => 'deposit', 
            'amount' => 150000, 
            'reference_number' => 'DEP-ALRAJHI-01', 
            'category' => 'Bank Transfer - Al Rajhi', 
            'description' => 'Weekly revenue transfer from main Al Rajhi account', 
            'transaction_date' => now()->subDays(2), 
            'performed_by' => 'Finance Manager'
        ]);

        SafeTransaction::create([
            'type' => 'withdrawal', 
            'amount' => 5000, 
            'reference_number' => 'WIT-PETTY-01', 
            'category' => 'Petty Cash Replenishment', 
            'description' => 'Front desk cash float replenishment for weekend shift', 
            'transaction_date' => now()->subDay(), 
            'performed_by' => 'Duty Manager'
        ]);

        SafeTransaction::create([
            'type' => 'deposit', 
            'amount' => 24500, 
            'reference_number' => 'DEP-VAULT-02', 
            'category' => 'Cash Drop', 
            'description' => 'End of day cash collection from POS outlets', 
            'transaction_date' => now(), 
            'performed_by' => 'Night Auditor'
        ]);

        // Saved Reports
        SavedReport::create([
            'name' => 'Monthly ZATCA Revenue', 
            'report_type' => 'revenues', 
            'filters' => ['date_range' => 'month', 'include_vat' => true], 
            'is_favorite' => true
        ]);

        SavedReport::create([
            'name' => 'Weekly Housekeeping Status', 
            'report_type' => 'cleaning', 
            'filters' => ['date_range' => 'week'], 
            'is_favorite' => true
        ]);
        
        SavedReport::create([
            'name' => 'Annual Occupancy Forecast', 
            'report_type' => 'occupancy', 
            'filters' => ['date_range' => 'year'], 
            'is_favorite' => true
        ]);
    }
}
