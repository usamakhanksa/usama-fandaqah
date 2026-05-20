<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Team;
use App\Models\Reservation;
use Illuminate\Support\Str;

class InvoiceCreditNoteSeeder extends Seeder
{
    public function run()
    {
        $team = Team::where('slug', 'fandaqah-palace')->first() ?: Team::first();
        if (!$team) return;

        $reservations = Reservation::where('team_id', $team->id)->take(5)->get();
        if ($reservations->count() === 0) {
            $reservations = Reservation::take(5)->get();
        }

        $userId = DB::table('users')->first()?->id ?: 1;

        foreach ($reservations as $index => $res) {
            $amount = $res->total_price ?: 1500.00;
            if ($amount <= 0) {
                $amount = 1500.00;
            }
            $vat = round($amount * 0.15, 2);
            $total = $amount + $vat;

            // 1. Create the Invoice
            $invoiceId = DB::table('invoices')->insertGetId([
                'team_id' => $res->team_id ?: $team->id,
                'reservation_id' => $res->id,
                'guest_id' => $res->guest_id,
                'company_id' => $res->company_id,
                'invoice_number' => 'INV-' . rand(10000, 99999),
                'zatca_uuid' => Str::uuid()->toString(),
                'zatca_invoice_type' => 'simplified',
                'invoice_date' => now()->subDays(rand(1, 10)),
                'due_date' => now()->addDays(7),
                'supply_date' => now()->subDays(rand(1, 10)),
                'sub_total' => $amount,
                'discount_amount' => 0.00,
                'taxable_amount' => $amount,
                'vat_amount' => $vat,
                'vat_percentage' => 15.00,
                'total_amount' => $total,
                'grand_total' => $total,
                'currency' => 'SAR',
                'status' => 'paid',
                'is_zatca_reported' => true,
                'zatca_status' => 'reported',
                'created_by' => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 2. Create the Invoice Item
            $itemId = DB::table('invoice_items')->insertGetId([
                'invoice_id' => $invoiceId,
                'team_id' => $res->team_id ?: $team->id,
                'product_name' => 'Room Stay Charge',
                'product_name_ar' => 'رسوم إقامة الغرفة',
                'quantity' => 1.00,
                'unit_price' => $amount,
                'sub_total' => $amount,
                'discount_amount' => 0.00,
                'taxable_amount' => $amount,
                'vat_amount' => $vat,
                'vat_percentage' => 15.00,
                'total_amount' => $total,
                'item_type' => 'room_charge',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 3. Create a Credit Note for a couple of invoices
            if ($index >= 3) {
                $cnAmount = 200.00;
                $cnVat = round($cnAmount * 0.15, 2);
                $cnTotal = $cnAmount + $cnVat;

                $creditNoteId = DB::table('credit_notes')->insertGetId([
                    'team_id' => $res->team_id ?: $team->id,
                    'invoice_id' => $invoiceId,
                    'reservation_id' => $res->id,
                    'guest_id' => $res->guest_id,
                    'company_id' => $res->company_id,
                    'credit_note_number' => 'CN-' . rand(10000, 99999),
                    'zatca_uuid' => Str::uuid()->toString(),
                    'credit_note_date' => now()->toDateString(),
                    'reason' => 'correction',
                    'reason_description' => 'Rate adjustment correction',
                    'sub_total' => $cnAmount,
                    'discount_amount' => 0.00,
                    'taxable_amount' => $cnAmount,
                    'vat_amount' => $cnVat,
                    'vat_percentage' => 15.00,
                    'total_amount' => $cnTotal,
                    'currency' => 'SAR',
                    'status' => 'confirmed',
                    'is_zatca_reported' => true,
                    'zatca_status' => 'reported',
                    'created_by' => $userId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('credit_note_items')->insert([
                    'credit_note_id' => $creditNoteId,
                    'original_item_id' => $itemId,
                    'team_id' => $res->team_id ?: $team->id,
                    'product_name' => 'Room Stay Rate Correction',
                    'product_name_ar' => 'تصحيح سعر إقامة الغرفة',
                    'quantity' => 1.00,
                    'unit_price' => $cnAmount,
                    'sub_total' => $cnAmount,
                    'discount_amount' => 0.00,
                    'taxable_amount' => $cnAmount,
                    'vat_amount' => $cnVat,
                    'vat_percentage' => 15.00,
                    'total_amount' => $cnTotal,
                    'item_type' => 'room_charge',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}