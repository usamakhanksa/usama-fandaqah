<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use App\Models\SpecialPrice;
use App\Models\UnitCategory;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class SpecialPriceController extends Controller
{
    public function index()
    {
        $specialPrices = SpecialPrice::with('roomType')->paginate(10);
        return Inertia::render('Marketing/SpecialPrices/Index', [
            'specialPrices' => $specialPrices
        ]);
    }

    public function create()
    {
        return Inertia::render('Marketing/SpecialPrices/Create', [
            'roomTypes' => UnitCategory::all()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price_type' => 'required|in:room_rate,package_rate,seasonal_rate,corporate_rate',
            'room_type_id' => 'nullable|exists:unit_categories,id',
            'rate_amount' => 'required|numeric',
            'rate_type' => 'required|in:fixed,percentage_off,amount_off',
            'valid_from' => 'required|date',
            'valid_to' => 'required|date|after:valid_from',
            'priority' => 'required|integer',
        ]);

        $validated['team_id'] = currentTeam()->id;
        $validated['created_by'] = Auth::id();

        SpecialPrice::create($validated);

        return redirect()->route('special-prices.index')->with('success', 'Special price created successfully.');
    }

    public function toggle(SpecialPrice $specialPrice)
    {
        $specialPrice->update(['is_active' => !$specialPrice->is_active]);
        return back()->with('success', 'Status toggled.');
    }

    public function calendar()
    {
        return Inertia::render('Marketing/SpecialPrices/Calendar');
    }
}
