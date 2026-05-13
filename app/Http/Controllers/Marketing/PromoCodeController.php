<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use App\Models\PromoCode;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class PromoCodeController extends Controller
{
    public function index()
    {
        $promoCodes = PromoCode::paginate(10);
        return Inertia::render('Marketing/PromoCodes/Index', [
            'promoCodes' => $promoCodes
        ]);
    }

    public function create()
    {
        return Inertia::render('Marketing/PromoCodes/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:promo_codes,code',
            'name' => 'required|string|max:255',
            'discount_type' => 'required|in:percentage,fixed_amount,free_night',
            'discount_value' => 'required|numeric',
            'valid_from' => 'required|date',
            'valid_to' => 'required|date|after:valid_from',
            'max_usage' => 'nullable|integer',
        ]);

        $validated['team_id'] = currentTeam()->id;
        $validated['created_by'] = Auth::id();

        PromoCode::create($validated);

        return redirect()->route('promo-codes.index')->with('success', 'Promo code created.');
    }

    public function validateCode(Request $request)
    {
        $code = $request->code;
        $promo = PromoCode::where('code', $code)
            ->where('is_active', true)
            ->where('valid_from', '<=', now())
            ->where('valid_to', '>=', now())
            ->first();

        if (!$promo) {
            return response()->json(['valid' => false, 'message' => 'Invalid or expired promo code.'], 404);
        }

        if ($promo->max_usage && $promo->current_usage >= $promo->max_usage) {
            return response()->json(['valid' => false, 'message' => 'Promo code usage limit reached.'], 400);
        }

        return response()->json([
            'valid' => true,
            'promo' => $promo
        ]);
    }
}
