<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GuestDataFormController extends Controller
{
    public function show(Request $request, $hotel)
    {
        // You can optionally retrieve hotel information here if needed
        // $hotelData = // Fetch hotel data based on $hotel ID

        return view('guest.form', ['hotelId' => $hotel]);
    }
}
