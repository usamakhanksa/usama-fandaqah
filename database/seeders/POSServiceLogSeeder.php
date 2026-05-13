<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Team;
use App\Models\Reservation;
use App\Service;
use App\ServiceLog;
use App\Transaction;
use Carbon\Carbon;

class POSServiceLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Get the demo team
        $team = Team::where('slug', 'demo-hotel')->first();
        
        if (!$team) {
            $this->command->error('Demo team not found. Please run TeamSeeder first.');
            return;
        }

        // Get reservations that are checked in or confirmed
        $reservations = Reservation::where('team_id', $team->id)
            ->whereIn('status', ['confirmed', 'checked_in', 'checked_out'])
            ->limit(15)
            ->get();
        
        if ($reservations->count() === 0) {
            $this->command->error('No reservations found. Please run ReservationSeeder first.');
            return;
        }

        // Get services
        $services = Service::where('team_id', $team->id)->get();
        
        if ($services->count() === 0) {
            $this->command->error('No services found. Please run ServiceSeeder first.');
            return;
        }

        // Create service logs and corresponding transactions
        foreach ($reservations as $index => $reservation) {
            // Create 2-4 service logs per reservation
            $numServices = rand(2, 4);
            
            for ($i = 0; $i < $numServices; $i++) {
                // Select a random service
                $service = $services->random();
                
                // Calculate amount based on service price and quantity
                $quantity = rand(1, 3);
                $amount = $service->price * $quantity;
                
                // Calculate decimals (for SAR, usually 2 digits)
                $decimals = 2;
                
                // Determine date based on reservation status
                $logDate = $reservation->status === 'checked_out' 
                    ? Carbon::parse($reservation->check_out)->subDays(rand(0, $reservation->check_in->diffInDays($reservation->check_out)))
                    : Carbon::parse($reservation->check_in)->addDays(rand(0, 2));
                
                // Create service log
                $serviceLog = ServiceLog::create([
                    'team_id' => $team->id,
                    'user_id' => rand(1, 5), // Random user ID
                    'type' => 'sale',
                    'number' => 'SL' . str_pad($index * 10 + $i + 1, 4, '0', STR_PAD_LEFT),
                    'amount' => $amount * 100, // Store in smallest currency unit
                    'decimals' => $decimals,
                    'meta' => [
                        'service_id' => $service->id,
                        'service_name' => json_decode($service->name, true)['en'],
                        'quantity' => $quantity,
                        'rate' => $service->price,
                        'date' => $logDate->format('Y-m-d'),
                        'description' => "POS service for reservation " . $reservation->code
                    ],
                    'is_subtraction' => false,
                    'active_note' => 'POS service transaction for reservation ' . $reservation->code,
                    'is_freezed' => $reservation->status !== 'checked_in', // Frozen if not currently checked in
                    'business_date' => $logDate,
                    'created_at' => $logDate,
                    'updated_at' => $logDate,
                ]);

                // Create corresponding transaction
                $transaction = Transaction::create([
                    'payable_type' => 'App\Models\Reservation',
                    'payable_id' => $reservation->id,
                    'wallet_id' => 1, // Default wallet
                    'team_id' => $team->id,
                    'type' => 'withdraw',
                    'transaction_flag' => 'normal',
                    'is_insurance' => false,
                    'amount' => $amount,
                    'amount_without_tax' => $service->is_tax_applied ? $amount / (1 + ($service->tax_percentage / 100)) : $amount,
                    'enable_tax_on_withdraw' => $service->is_tax_applied,
                    'tax_percentage' => $service->is_tax_applied ? $service->tax_percentage : 0,
                    'tax_amount' => $service->is_tax_applied ? $amount - ($amount / (1 + ($service->tax_percentage / 100))) : 0,
                    'supplier_tax_number' => null,
                    'invoice_number' => 'INV' . str_pad($index * 10 + $i + 1, 6, '0', STR_PAD_LEFT),
                    'is_public' => false,
                    'is_promissory' => false,
                    'is_attached_to_invoice' => true,
                    'kind' => 'pos_service',
                    'description' => json_decode($service->name, true)['en'] . ' for reservation ' . $reservation->code,
                    'confirmed' => true,
                    'meta' => [
                        'service_id' => $service->id,
                        'service_name' => json_decode($service->name, true)['en'],
                        'quantity' => $quantity,
                        'rate' => $service->price,
                        'payment_type' => 'credit_card',
                        'date' => $logDate->format('Y-m-d'),
                        'description' => "POS service for reservation " . $reservation->code
                    ],
                    'number' => 'TXN' . str_pad($index * 10 + $i + 1, 6, '0', STR_PAD_LEFT),
                    'uuid' => uniqid('txn_', true),
                    'is_advance_deposit' => false,
                    'is_freezed' => $reservation->status !== 'checked_in', // Frozen if not currently checked in
                    'business_date' => $logDate,
                    'zatca_status' => 'submitted', // For demo purposes
                    'zatca_invoice_id' => 'ZATCA' . str_pad($index * 10 + $i + 1, 8, '0', STR_PAD_LEFT),
                    'zatca_uuid' => uniqid('zatca_', true),
                    'zatca_qr_code' => 'ZATCA_QR_' . $index . '_' . $i,
                    'vat_calculation_basis' => 'exclusive',
                    'vat_category' => $service->is_tax_applied ? 'standard' : 'zero-rated',
                    'tourism_tax_amount' => 0, // No tourism tax for services in this demo
                    'accommodation_tax_amount' => 0, // No accommodation tax for services
                    'created_at' => $logDate,
                    'updated_at' => $logDate,
                ]);

                // Link service log to transaction
                $serviceLog->update(['transaction_id' => $transaction->id]);
            }
        }
        
        // Create some additional service logs for checked-in guests without corresponding transactions yet
        $checkedInReservations = $reservations->filter(function($res) {
            return $res->status === 'checked_in';
        });
        
        foreach ($checkedInReservations as $index => $reservation) {
            // Create 1-2 additional service logs for currently checked-in guests
            $numAdditionalServices = rand(1, 2);
            
            for ($i = 0; $i < $numAdditionalServices; $i++) {
                $service = $services->random();
                $quantity = rand(1, 2);
                $amount = $service->price * $quantity;
                $decimals = 2;
                $logDate = Carbon::today(); // Today's date for ongoing services
                
                $serviceLog = ServiceLog::create([
                    'team_id' => $team->id,
                    'user_id' => rand(1, 5), // Random user ID
                    'type' => 'sale',
                    'number' => 'SL' . str_pad(150 + $index * 10 + $i + 1, 4, '0', STR_PAD_LEFT),
                    'amount' => $amount * 100, // Store in smallest currency unit
                    'decimals' => $decimals,
                    'meta' => [
                        'service_id' => $service->id,
                        'service_name' => json_decode($service->name, true)['en'],
                        'quantity' => $quantity,
                        'rate' => $service->price,
                        'date' => $logDate->format('Y-m-d'),
                        'description' => "Additional POS service for reservation " . $reservation->code
                    ],
                    'is_subtraction' => false,
                    'active_note' => 'Additional POS service transaction for reservation ' . $reservation->code,
                    'is_freezed' => false, // Not frozen since guest is currently checked in
                    'business_date' => $logDate,
                    'created_at' => $logDate,
                    'updated_at' => $logDate,
                ]);

                // Create corresponding transaction
                $transaction = Transaction::create([
                    'payable_type' => 'App\Models\Reservation',
                    'payable_id' => $reservation->id,
                    'wallet_id' => 1, // Default wallet
                    'team_id' => $team->id,
                    'type' => 'withdraw',
                    'transaction_flag' => 'normal',
                    'is_insurance' => false,
                    'amount' => $amount,
                    'amount_without_tax' => $service->is_tax_applied ? $amount / (1 + ($service->tax_percentage / 100)) : $amount,
                    'enable_tax_on_withdraw' => $service->is_tax_applied,
                    'tax_percentage' => $service->is_tax_applied ? $service->tax_percentage : 0,
                    'tax_amount' => $service->is_tax_applied ? $amount - ($amount / (1 + ($service->tax_percentage / 100))) : 0,
                    'supplier_tax_number' => null,
                    'invoice_number' => 'INV' . str_pad(1500 + $index * 10 + $i + 1, 6, '0', STR_PAD_LEFT),
                    'is_public' => false,
                    'is_promissory' => false,
                    'is_attached_to_invoice' => true,
                    'kind' => 'pos_service',
                    'description' => json_decode($service->name, true)['en'] . ' additional for reservation ' . $reservation->code,
                    'confirmed' => true,
                    'meta' => [
                        'service_id' => $service->id,
                        'service_name' => json_decode($service->name, true)['en'],
                        'quantity' => $quantity,
                        'rate' => $service->price,
                        'payment_type' => 'credit_card',
                        'date' => $logDate->format('Y-m-d'),
                        'description' => "Additional POS service for reservation " . $reservation->code
                    ],
                    'number' => 'TXN' . str_pad(1500 + $index * 10 + $i + 1, 6, '0', STR_PAD_LEFT),
                    'uuid' => uniqid('txn_', true),
                    'is_advance_deposit' => false,
                    'is_freezed' => false, // Not frozen since guest is currently checked in
                    'business_date' => $logDate,
                    'zatca_status' => 'pending', // Pending for ongoing guests
                    'zatca_invoice_id' => 'ZATCA' . str_pad(1500 + $index * 10 + $i + 1, 8, '0', STR_PAD_LEFT),
                    'zatca_uuid' => uniqid('zatca_', true),
                    'zatca_qr_code' => 'ZATCA_ADD_' . $index . '_' . $i,
                    'vat_calculation_basis' => 'exclusive',
                    'vat_category' => $service->is_tax_applied ? 'standard' : 'zero-rated',
                    'tourism_tax_amount' => 0,
                    'accommodation_tax_amount' => 0,
                    'created_at' => $logDate,
                    'updated_at' => $logDate,
                ]);

                // Link service log to transaction
                $serviceLog->update(['transaction_id' => $transaction->id]);
            }
        }
    }
}