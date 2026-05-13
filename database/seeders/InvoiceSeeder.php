<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoiceTax;
use App\Models\Team;
use App\Models\Guest;
use App\Models\Company;
use App\User;
use App\Models\Reservation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class InvoiceSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('invoice_taxes')->truncate();
        DB::table('invoice_items')->truncate();
        DB::table('invoices')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $teams = Team::take(3)->get();
        if ($teams->isEmpty()) return;

        $user = User::first();
        if (!$user) return;

        $statuses = ['confirmed', 'paid', 'draft', 'partially_paid', 'cancelled'];
        $zatcaStatuses = ['accepted', 'pending', 'not_reported', 'rejected', 'error'];

        for ($i = 1; $i <= 60; $i++) {
            $team = $teams->random();
            $guest = Guest::where('team_id', $team->id)->inRandomOrder()->first();
            $company = Company::where('team_id', $team->id)->inRandomOrder()->first();
            $reservation = Reservation::where('team_id', $team->id)->inRandomOrder()->first();

            $type = ($i <= 40) ? 'standard' : 'simplified';
            $status = $this->weightedRandom($statuses, [30, 15, 8, 4, 3]);
            $zatcaStatus = $this->weightedRandom($zatcaStatuses, [35, 10, 8, 4, 3]);

            $date = Carbon::now()->subDays(rand(0, 30));
            
            $invoice = Invoice::create([
                'team_id' => $team->id,
                'reservation_id' => $reservation?->id,
                'guest_id' => $guest?->id,
                'company_id' => ($type === 'standard') ? ($company?->id ?? null) : null,
                'invoice_number' => 'INV-' . $date->format('Ym') . '-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'zatca_uuid' => Str::uuid(),
                'zatca_invoice_type' => $type,
                'invoice_date' => $date,
                'due_date' => $date->copy()->addDays(30),
                'supply_date' => $date,
                'currency' => 'SAR',
                'status' => $status,
                'zatca_status' => $zatcaStatus,
                'is_zatca_reported' => in_array($zatcaStatus, ['accepted', 'reported', 'rejected']),
                'zatca_submitted_at' => ($zatcaStatus !== 'not_reported') ? $date : null,
                'created_by' => $user->id,
            ]);

            // Add Items
            $itemCount = rand(2, 8);
            for ($j = 1; $j <= $itemCount; $j++) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'team_id' => $team->id,
                    'product_name' => 'Room Charge ' . $j,
                    'product_name_ar' => 'رسوم الغرفة ' . $j,
                    'quantity' => 1,
                    'unit_price' => rand(100, 500),
                    'vat_percentage' => 15.00,
                    'item_type' => 'room_charge',
                    'sort_order' => $j,
                ]);
            }

            // Recalculate totals
            $invoice->calculateTotals();

            // Add VAT tax record
            if ($invoice->vat_amount > 0) {
                InvoiceTax::create([
                    'invoice_id' => $invoice->id,
                    'tax_type' => 'vat',
                    'tax_name' => 'VAT',
                    'tax_percentage' => 15.00,
                    'tax_amount' => $invoice->vat_amount,
                ]);
            }

            // Generate XML/QR for 15 invoices
            if ($i <= 15) {
                $invoice->update([
                    'zatca_xml' => '<xml>Dummy ZATCA XML</xml>',
                    'zatca_qr_code' => 'DUMMY_QR_CODE_DATA_' . $i,
                    'zatca_hash' => hash('sha256', 'dummy_xml_' . $i),
                ]);
            }
        }
    }

    private function weightedRandom($values, $weights)
    {
        $totalWeight = array_sum($weights);
        $rand = rand(1, $totalWeight);
        $currentWeight = 0;

        foreach ($values as $index => $value) {
            $currentWeight += $weights[$index];
            if ($rand <= $currentWeight) {
                return $value;
            }
        }

        return $values[0];
    }
}
