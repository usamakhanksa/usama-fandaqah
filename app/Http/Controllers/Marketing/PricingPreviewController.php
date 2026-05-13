<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use App\Models\Offer;
use App\Models\PromoCode;
use App\Models\SpecialPrice;
use App\Models\UnitCategory;
use App\Models\Source;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PricingPreviewController extends Controller
{
    public function index()
    {
        return Inertia::render('Marketing/PricingPreview/Index', [
            'roomTypes' => UnitCategory::all(),
            'sources' => Source::all()
        ]);
    }

    public function calculate(Request $request)
    {
        $request->validate([
            'room_type_id' => 'required|exists:unit_categories,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'guests' => 'required|integer|min:1',
            'source_id' => 'nullable|exists:sources,id',
            'promo_code' => 'nullable|string'
        ]);

        $roomType = UnitCategory::find($request->room_type_id);
        $baseRate = $roomType->base_price ?? 500; // Fallback for demo
        $nights = \Carbon\Carbon::parse($request->start_date)->diffInDays($request->end_date);
        
        $breakdown = [];
        $totalBase = 0;
        $totalDiscounts = 0;
        
        for ($i = 0; $i < $nights; $i++) {
            $date = \Carbon\Carbon::parse($request->start_date)->addDays($i);
            $dailyRate = $baseRate;
            
            // Check Special Prices
            $special = SpecialPrice::where('room_type_id', $roomType->id)
                ->where('valid_from', '<=', $date)
                ->where('valid_to', '>=', $date)
                ->where('is_active', true)
                ->orderBy('priority', 'desc')
                ->first();
                
            if ($special) {
                if ($special->rate_type === 'fixed') {
                    $dailyRate = $special->rate_amount;
                } elseif ($special->rate_type === 'amount_off') {
                    $dailyRate -= $special->rate_amount;
                } elseif ($special->rate_type === 'percentage_off') {
                    $dailyRate -= ($dailyRate * ($special->rate_amount / 100));
                }
            }
            
            $totalBase += $dailyRate;
            $breakdown[] = [
                'date' => $date->toDateString(),
                'base' => $dailyRate,
                'special_applied' => (bool)$special
            ];
        }

        // Apply Offers
        $offers = Offer::where('is_active', true)
            ->where('valid_from', '<=', $request->start_date)
            ->where('valid_to', '>=', $request->end_date)
            ->where('min_nights', '<=', $nights)
            ->get();
            
        foreach ($offers as $offer) {
            // Check if applicable to room type/source (simplified logic)
            if ($offer->offer_type === 'percentage_discount') {
                $totalDiscounts += ($totalBase * ($offer->discount_value / 100));
            } elseif ($offer->offer_type === 'fixed_discount') {
                $totalDiscounts += $offer->discount_value;
            }
        }

        // Apply Promo Code
        if ($request->promo_code) {
            $promo = PromoCode::where('code', $request->promo_code)
                ->where('is_active', true)
                ->where('valid_from', '<=', now())
                ->where('valid_to', '>=', now())
                ->first();
                
            if ($promo) {
                if ($promo->discount_type === 'percentage') {
                    $totalDiscounts += ($totalBase * ($promo->discount_value / 100));
                } elseif ($promo->discount_type === 'fixed_amount') {
                    $totalDiscounts += $promo->discount_value;
                }
            }
        }

        $taxRate = 0.15; // 15% VAT
        $subtotal = $totalBase - $totalDiscounts;
        $taxAmount = $subtotal * $taxRate;
        $grandTotal = $subtotal + $taxAmount;

        return response()->json([
            'nights' => $nights,
            'base_rate_total' => $totalBase,
            'discounts_total' => $totalDiscounts,
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'grand_total' => $grandTotal,
            'breakdown' => $breakdown
        ]);
    }
}
