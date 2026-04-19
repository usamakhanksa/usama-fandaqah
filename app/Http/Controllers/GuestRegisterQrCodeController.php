<?php

namespace App\Http\Controllers;

use App\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class GuestRegisterQrCodeController extends Controller
{
    public function generate(Request $request, $hash)
    {
        $team_id = hashidEncoderAndDecoder($hash,'decode');
        if(is_null($team_id)) {
            return redirect()->to('/');
        }
        
        $team = Team::find($team_id);
        if(is_null($team)) {
            return redirect()->to('/');
        }


        $base_url = 'https://' . env('AWS_BUCKET').'.s3.'.env('AWS_DEFAULT_REGION').'.amazonaws.com/';
        $hotel_logo = null;
        if($team->photo_url) {
            $hotel_logo = $base_url . $team->photo_url;
        } 

        $secretKey = env('FANDAQAH_GUEST_SIGNED_URL_SECRET');

        $signature = hash_hmac('sha256', $team->id, $secretKey);

        // 1. Construct the URL for the guest data form
        $guestFormUrl = env('FANDAQAH_GUEST_REGISTER_APP_URL') . '/h/' . $team->id . "?signature={$signature}";

        // 2. Generate the QR code as an SVG image
        $qrCodeSvg = QrCode::size(500)->generate($guestFormUrl);

        

      
        
        return view('guest.qrcode', [
            'qrCodeSvg' => $qrCodeSvg,
            'hotel' => $team,
            'hotel_logo' => $hotel_logo
        ]);

        // Alternatively, you could save the QR code as a file and return the path:
        // $path = 'qrcodes/hotel_' . $hotel . '.svg';
        // QrCode::size(200)->format('svg')->generate($guestFormUrl, public_path($path));
        // return asset($path);
    }
}
