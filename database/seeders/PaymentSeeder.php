<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Payment;
use App\Models\Team;
use App\Reservation;
use App\Guest;
use App\Company;
use App\User;
use App\Models\CashierShift;
use App\Models\Receipt;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PaymentSeeder extends Seeder
{
    public function run()
    {
        // Clear existing payments
        DB::table('payments')->delete();

        $teams = Team::take(3)->get();
        if ($teams->isEmpty()) return;

        $reservations = Reservation::all();
        $guests = Guest::all();
        $companies = Company::all();
        $users = User::all();
        $receipts = Receipt::all();
        $shifts = CashierShift::all();

        // Target distributions
        $statuses = array_merge(
            array_fill(0, 55, 'confirmed'),
            array_fill(0, 15, 'pending'),
            array_fill(0, 7, 'cancelled'),
            array_fill(0, 3, 'reversed')
        );
        shuffle($statuses);

        $methods = array_merge(
            array_fill(0, 25, 'cash'),
            array_fill(0, 20, 'mada'),
            array_fill(0, 15, 'visa'),
            array_fill(0, 10, 'mastercard'),
            array_fill(0, 5, 'bank_transfer'),
            array_fill(0, 3, 'apple_pay'),
            array_fill(0, 2, 'cheque')
        );
        shuffle($methods);

        $types = array_merge(
            array_fill(0, 15, 'deposit'),
            array_fill(0, 50, 'payment'),
            array_fill(0, 8, 'partial_payment'),
            array_fill(0, 5, 'advance'),
            array_fill(0, 2, 'refund')
        );
        shuffle($types);

        $multiCurrency = [
            ['currency' => 'USD', 'rate' => 3.75],
            ['currency' => 'EUR', 'rate' => 4.05],
            ['currency' => 'GBP', 'rate' => 4.75],
            ['currency' => 'USD', 'rate' => 3.75],
            ['currency' => 'EUR', 'rate' => 4.05],
        ];

        for ($i = 0; $i < 80; $i++) {
            $team = $teams->random();
            $method = $methods[$i] ?? 'cash';
            $status = $statuses[$i] ?? 'confirmed';
            $type = $types[$i] ?? 'payment';
            
            $currency = 'SAR';
            $exchangeRate = 1.0000;
            $originalAmount = null;

            if ($i < 5) {
                $currency = $multiCurrency[$i]['currency'];
                $exchangeRate = $multiCurrency[$i]['rate'];
            }

            $amount = rand(100, 2000);
            if ($currency !== 'SAR') {
                $originalAmount = $amount;
                $amount = round($originalAmount * $exchangeRate, 2);
            }

            $paymentDate = Carbon::now()->subDays(rand(0, 30));

            $payment = Payment::create([
                'team_id' => $team->id,
                'reservation_id' => $reservations->isNotEmpty() ? $reservations->random()->id : null,
                'guest_id' => $guests->isNotEmpty() ? $guests->random()->id : null,
                'company_id' => $companies->isNotEmpty() && rand(1, 5) === 1 ? $companies->random()->id : null,
                'receipt_id' => $receipts->isNotEmpty() && rand(1, 10) === 1 ? $receipts->random()->id : null,
                'payment_date' => $paymentDate,
                'amount' => $amount,
                'original_amount' => $originalAmount,
                'currency' => $currency,
                'exchange_rate' => $exchangeRate,
                'payment_method' => $method,
                'payment_type' => $type,
                'status' => $status,
                'is_advance' => $type === 'advance',
                'is_deposit' => $type === 'deposit',
                'cashier_shift_id' => $shifts->isNotEmpty() ? $shifts->random()->id : null,
                'created_by' => $users->random()->id,
                'confirmed_at' => $status === 'confirmed' ? $paymentDate : null,
                'confirmed_by' => $status === 'confirmed' ? $users->random()->id : null,
                'cancelled_at' => in_array($status, ['cancelled', 'reversed']) ? $paymentDate->addHours(2) : null,
                'cancelled_by' => in_array($status, ['cancelled', 'reversed']) ? $users->random()->id : null,
                'cancellation_reason' => in_array($status, ['cancelled', 'reversed']) ? 'Customer requested' : null,
            ]);

            // If confirmed, maybe create transaction?
            // The PaymentService handles this, but for seeder we might want to manually link if we are not using the service
            // However, the prompt says "IMPLEMENT COMPLETE PAYMENTS MODULE" and seeder should link to existing.
        }
    }
}
