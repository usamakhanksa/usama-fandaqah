<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreGuestRequest;
use App\Http\Resources\GuestResource;
use App\Models\Guest;
use Illuminate\Http\Request;

class GuestController extends Controller
{
    public function index(Request $request)
    {
        $query = Guest::query()->withCount('reservations')->whereNull('company_profile_id');

        if ($request->filled('search')) {
            $term = $request->string('search');
            $query->where(fn ($q) => $q->where('name', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%")
                ->orWhere('phone', 'like', "%{$term}%")
                ->orWhere('card_id', 'like', "%{$term}%"));
        }

        if ($request->filled('gender') && $request->string('gender') !== 'all') {
            $query->where('gender', $request->string('gender'));
        }

        if ($request->filled('type') && $request->string('type') !== 'all') {
            $query->where('type', $request->string('type'));
        }

        if ($request->string('sort', 'name_asc')->value() === 'name_desc') {
            $query->orderByDesc('name');
        } else {
            $query->orderBy('name');
        }

        return GuestResource::collection($query->paginate($request->integer('per_page', 10))->withQueryString());
    }

    public function store(StoreGuestRequest $request)
    {
        return new GuestResource(Guest::create($request->validated()));
    }

    public function update(StoreGuestRequest $request, Guest $guest)
    {
        $guest->update($request->validated());

        return new GuestResource($guest->refresh()->loadCount('reservations'));
    }

    public function destroy(Guest $guest)
    {
        $guest->delete();

        return response()->json(['message' => 'Guest deleted']);
    }
}
