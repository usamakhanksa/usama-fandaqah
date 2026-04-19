<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Country;
use Illuminate\Http\Request;

class LookupController extends Controller
{
    public function countries()
    {
        return response()->json(Country::query()->orderBy('name')->get());
    }

    public function cities(Request $request)
    {
        $query = City::query()->orderBy('name');
        if ($request->filled('country_id')) {
            $query->where('country_id', $request->integer('country_id'));
        }

        return response()->json($query->get());
    }
}
