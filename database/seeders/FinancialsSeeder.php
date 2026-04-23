<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ArAccount;
use App\Models\FinancialTransaction;
use App\Models\CompTransaction;
use App\Models\EodProcess;
use Illuminate\Support\Str;

class FinancialsSeeder extends Seeder {
    public function run(): void {
        // AR Accounts (Saudi Arabian context)
        $aramco = ArAccount::create([
            'company_name' => 'Saudi Aramco (KSA)', 
            'contact_person' => 'Eng. Abdulrahman Al-Faleh', 
            'email' => 'corporate.reservations@aramco.com', 
            'phone' => '+966 13 874 4444', 
            'credit_limit' => 500000.00, 
            'current_balance' => 125450.75, 
            'status' => 'active'
        ]);

        $stc = ArAccount::create([
            'company_name' => 'STC Group', 
            'contact_person' => 'Ms. Sarah Al-Otaibi', 
            'email' => 'billing@stc.com.sa', 
            'phone' => '+966 11 455 5555', 
            'credit_limit' => 200000.00, 
            'current_balance' => 45000.00, 
            'status' => 'active'
        ]);

        $sabic = ArAccount::create([
            'company_name' => 'SABIC', 
            'contact_person' => 'Mr. Hamad Al-Suwaidi', 
            'email' => 'finance@sabic.com', 
            'phone' => '+966 11 225 8000', 
            'credit_limit' => 350000.00, 
            'current_balance' => 0.00, 
            'status' => 'active'
        ]);

        // Transactions (Cashiering / AR Postings)
        $txs = [
            [
                'booking_reference' => 'FND-100234',
                'type' => 'charge',
                'amount' => 1250.00,
                'payment_method' => 'Visa',
                'description' => 'Room Charge - Suite 404',
                'transaction_date' => today()
            ],
            [
                'booking_reference' => 'FND-100235',
                'type' => 'payment',
                'amount' => 1250.00,
                'payment_method' => 'Visa',
                'description' => 'Full Payment - Guest Folio',
                'transaction_date' => today()
            ],
            [
                'ar_account_id' => $aramco->id,
                'type' => 'charge',
                'amount' => 25800.00,
                'payment_method' => 'CityLedger',
                'description' => 'Corporate Event - Ballroom A',
                'transaction_date' => today()->subDays(2)
            ],
            [
                'booking_reference' => 'FND-100236',
                'type' => 'charge',
                'amount' => 450.00,
                'payment_method' => 'Mada',
                'description' => 'Mini-bar & Laundry',
                'transaction_date' => today()
            ],
        ];

        foreach ($txs as $tx) {
            $tx['receipt_number'] = 'RCPT-' . strtoupper(Str::random(10));
            FinancialTransaction::create($tx);
        }

        // Complimentaries
        CompTransaction::create([
            'booking_reference' => 'FND-VIP-001',
            'department' => 'rooms',
            'value_amount' => 850.00,
            'reason' => 'Honeymoon Package Upgrade',
            'approved_by' => 'General Manager',
            'date_posted' => today()
        ]);

        CompTransaction::create([
            'booking_reference' => 'FND-100237',
            'department' => 'f_and_b',
            'value_amount' => 120.00,
            'reason' => 'Service Recovery - Late Breakfast',
            'approved_by' => 'F&B Manager',
            'date_posted' => today()->subDay()
        ]);

        // EOD (End of Day) - History
        EodProcess::create([
            'audit_date' => today()->subDays(2),
            'total_revenue' => 45800.00,
            'total_payments' => 42000.00,
            'total_comps' => 1200.00,
            'status' => 'completed',
            'run_by' => 'Night Auditor',
            'completed_at' => today()->subDays(2)->addHours(26)
        ]);

        EodProcess::create([
            'audit_date' => today()->subDay(),
            'total_revenue' => 32400.00,
            'total_payments' => 31000.00,
            'total_comps' => 850.00,
            'status' => 'completed',
            'run_by' => 'Night Auditor',
            'completed_at' => today()->subDay()->addHours(26)
        ]);
    }
}
