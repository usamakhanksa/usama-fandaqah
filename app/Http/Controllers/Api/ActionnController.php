<?php

namespace App\Http\Controllers\Api;
use App\Team;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Reservation;
use App\Http\Controllers\Controller;

class ActionnController extends Controller
{
    public function cancel(Request $request)
    {        $ids = $request->input('models');

        Reservation::whereIn('id', $ids)->update([
            'status' => 'canceled'
          ]);

                  return "succsess";
    }
    public function confirm(Request $request)
    {        $ids = $request->input('models');

        Reservation::whereIn('id', $ids)->update([
            'status' => 'confirmed'
          ]);

                  return "succsess";
    }

}
