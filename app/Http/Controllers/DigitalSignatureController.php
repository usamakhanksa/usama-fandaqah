<?php

namespace App\Http\Controllers;

use App\Reservation;
use App\DigitalSignature;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Intervention\Image\Facades\Image;

class DigitalSignatureController extends Controller
{
    public function signatureStore(Request $request)
    {
        
        $team_id = $request->input('team_id');
        $ref_id = $request->input('ref_id');
        $shorten_url_code = $request->input('shorten_url_code');
        //reservation
        //user
        $type = $request->input('type');
      
        $signature = $request->input('signature');

        $user_id = $request->input('user_id');
        // retain userid if user change from front end
        if($user_id) {
          $user_id = Auth()->user()->id;
        }
     
        $compressedSignature = isset($signature) ? base64_encode(gzcompress($signature)) : null;

        if(isset($team_id) && isset($ref_id)) {
            $check_digital_signature_first = DigitalSignature::where('team_id',$team_id)->where('ref_id',$ref_id)->where('type',$type)->first();
            if($check_digital_signature_first){
                $check_digital_signature_first->delete();
            }
        }
        if(isset($user_id)) {
            $check_digital_signature_first = DigitalSignature::where('user_id',$user_id)->where('type',$type)->first();
            if($check_digital_signature_first){
                $check_digital_signature_first->delete();
                if(!isset($compressedSignature)) {
                    return response()->json(['message' => 'Signature saved successfully!']);
                }
            }
        }
        // Save path to database
        $signature = DigitalSignature::create([
            'team_id' => $team_id ?? null,
            'ref_id' => $ref_id ?? null,
            'type' => $type,
            'user_id' => $user_id ?? null,
            'signature_base64' => $compressedSignature,
        ]);

        /**
         * Create contract snapshot directly after guest signs a contract
         */
        $contract_snapshot = null;
        if($signature){
            $checkReservation = Reservation::findOrFail($ref_id);
            if($checkReservation){
              $contract_snapshot =  contractSnapshotGenerator($checkReservation,'signed',$shorten_url_code);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Signature saved successfully! & Contract snapshort is returned', 
            'snapshot' => $contract_snapshot,
            'aws_storage_url' => 'https://' . env('AWS_BUCKET').'.s3.'.env('AWS_DEFAULT_REGION').'.amazonaws.com/'
        ]);
    }

    public function signatureUncompress (Request $request) {
        $signature = $request->input('signature');
        $uncompressedSignature = gzuncompress(base64_decode($signature));
        return response()->json(['signature' => $uncompressedSignature]);
    }
}
