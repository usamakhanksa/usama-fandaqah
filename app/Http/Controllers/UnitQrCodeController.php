<?php

namespace App\Http\Controllers;

use App\Team;
use App\Unit;
use App\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class UnitQrCodeController extends Controller
{
    public function generate(Request $request, $id)
    {
        $unit = Unit::with('team')->find($id);
        if(!$unit) {
            return redirect()->to('/');
        }

        $team = $unit->team;

        $base_url = 'https://' . env('AWS_BUCKET').'.s3.'.env('AWS_DEFAULT_REGION').'.amazonaws.com/';
        $hotel_logo = null;
        if($team->photo_url) {
            $hotel_logo = $base_url . $team->photo_url;
        }

        $secretKey = env('FANDAQAH_UNIT_SIGNED_URL_SECRET');

        $signature = hash_hmac('sha256', $team->id . '-' . $unit->id, $secretKey);

        // 1. Construct the URL for the guest data form
        $unitQrUrl = url('/h/' . $team->id . '/u/' . $unit->id . "?s={$signature}");

        // 2. Generate the QR code as an SVG image
        $qrCodeSvg = QrCode::size(500)->generate($unitQrUrl);




        return view('guest.unit-qrcode', [
            'qrCodeSvg' => $qrCodeSvg,
            'hotel' => $team,
            'hotel_logo' => $hotel_logo,
            'unit_name' => json_decode($unit->getOriginal('name')),
            'room_number' => $unit->unit_number,
        ]);


    }

    public function guestRequestActionsPage(Request $request,$team_id,$unit_id){

        $signature = $request->query('s');
        $secretKey = env('FANDAQAH_UNIT_SIGNED_URL_SECRET');

        // Recalculate signature
        $expectedSignature = hash_hmac('sha256', $team_id . '-' . $unit_id, $secretKey);


        if (!hash_equals($expectedSignature, $signature)) {
            abort(403, 'Fandaqah Says Invalid or tampered link.');
        }

        $unit = Unit::with('team')->find($unit_id);
        if(!$unit) {
            return redirect()->to('/');
        }

        $base_url = 'https://' . env('AWS_BUCKET').'.s3.'.env('AWS_DEFAULT_REGION').'.amazonaws.com/';
        $hotel_logo = null;
        if($unit->team->photo_url) {
            $hotel_logo = $base_url . $unit->team->photo_url;
        }


        $latest_checked_in_booking = Reservation::with(['customer','company','wallet','unit','team'])
        ->whereNull('checked_out')
        ->whereNotNull('checked_in')
        ->where('status','confirmed')
        ->whereNull('deleted_at')
        ->where('unit_id',$unit->id)
        ->where('team_id',$unit->team_id)
        ->first();


        if(!$latest_checked_in_booking || !$latest_checked_in_booking->customer){
            return view('guest.iptv-guest-access-forbidden', [
                'success' => false,
                'status' => 403,
                'message' => 'room is empty , no checked in guest'
            ]);
        }




        return view('guest.iptv-guest-need-action-buttons-form', [
            'hotel_logo' => $hotel_logo,
            'reservation_id' => $latest_checked_in_booking->id,
            'hotel' => $unit->team,
            'unit_id' => $unit->id,
            'room_number' => $unit->unit_number,
            'guest_api_url' => env('FANDAQAH_API_IPTV_URL') . 'guest-need',
            'iptv_token' => env('IPTV_JWT_AUTHORIZATION'),
            'id_number' => $latest_checked_in_booking->customer ? $latest_checked_in_booking->customer->id_number : null
        ]);
    }

    public function qrForbiddenAccess(Request $request){
        return view('guest.iptv-guest-access-forbidden', [
            'success' => false,
            'status' => 403,
            'message' => 'invlaid id number'
        ]);
    }


}
