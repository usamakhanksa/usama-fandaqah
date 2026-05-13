<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use App\Models\Offer;
use App\Models\UnitCategory;
use App\Models\Source;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class OfferController extends Controller
{
    public function index()
    {
        $offers = Offer::orderBy('sort_order')->paginate(10);
        return Inertia::render('Marketing/Offers/Index', [
            'offers' => $offers
        ]);
    }

    public function create()
    {
        return Inertia::render('Marketing/Offers/Create', [
            'roomTypes' => UnitCategory::all(),
            'sources' => Source::all()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'offer_type' => 'required|in:percentage_discount,fixed_discount,free_night,early_bird,last_minute,package,loyalty',
            'discount_value' => 'required|numeric',
            'discount_percentage' => 'nullable|numeric',
            'valid_from' => 'required|date',
            'valid_to' => 'required|date|after:valid_from',
            'min_nights' => 'required|integer|min:1',
            'applicable_room_types' => 'nullable|array',
            'applicable_sources' => 'nullable|array',
        ]);

        $validated['team_id'] = currentTeam()->id;
        $validated['created_by'] = Auth::id();

        Offer::create($validated);

        return redirect()->route('offers.index')->with('success', 'Offer created successfully.');
    }

    public function edit(Offer $offer)
    {
        return Inertia::render('Marketing/Offers/Edit', [
            'offer' => $offer,
            'roomTypes' => UnitCategory::all(),
            'sources' => Source::all()
        ]);
    }

    public function update(Request $request, Offer $offer)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'offer_type' => 'required|in:percentage_discount,fixed_discount,free_night,early_bird,last_minute,package,loyalty',
            'discount_value' => 'required|numeric',
            'valid_from' => 'required|date',
            'valid_to' => 'required|date|after:valid_from',
        ]);

        $offer->update($validated);

        return redirect()->route('offers.index')->with('success', 'Offer updated successfully.');
    }

    public function toggle(Offer $offer)
    {
        $offer->update(['is_active' => !$offer->is_active]);
        return back()->with('success', 'Offer status toggled.');
    }

    public function destroy(Offer $offer)
    {
        $offer->delete();
        return redirect()->route('offers.index')->with('success', 'Offer deleted.');
    }
}
