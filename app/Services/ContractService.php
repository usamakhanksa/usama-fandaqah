<?php

namespace App\Services;

namespace App\Services;

use App\User;
use App\Company;
use App\Reservation;
use App\DigitalSignature;
use Illuminate\Support\Str;
use App\ReservationContract;
use Illuminate\Support\Facades\Storage;

class ContractService
{
    /**
     * Generate a new draft contract version for a reservation.
     */
    public function makeContractSnapshot(Reservation $reservation,$status = 'draft',$code = null): ReservationContract
    {
        // Count existing versions
        $latestVersionNumber = ReservationContract::where('reservation_id', $reservation->id)->count();
        $version = ($latestVersionNumber + 1);
        $signature = null;
        $signature_signee = null;

        if($reservation->reservation_type == 'single'){
            $digital_signature = DigitalSignature::where('team_id',$reservation->team_id)->where('ref_id',$reservation->id)->where('type','reservation')->first();
            if($digital_signature){
                $signature =  gzuncompress(base64_decode($digital_signature->signature_base64));
                if($reservation->reservation_type == 'group'){
                    $signature_signee = Company::find($reservation->company_id);
                } else if ($reservation->reservation_type == 'single') {
                    $signature_signee = $reservation->customer;
                }
                if(isset($signature_signee)) {
                    $signature_signee = $signature_signee->name;
                }
            }else{
                $signature = null;
            }

            $official_signature = null;
            $official_signature_signee = null;
            $official_signature_object = Reservation::getReservationOfficialSignature($reservation->team_id, $reservation->id);

            if(\Auth()->check() && !isset($official_signature_object)) {
                $official_signature_object = DigitalSignature::transactionUserSignReservation($reservation->id, $reservation->team_id);
            }
            if(isset($official_signature_object)) {
                $official_signature = $official_signature_object->signature;
                $official_signature_signee = User::find($official_signature_object->user_id);
                if(isset($official_signature_signee)) {
                    $official_signature_signee = $official_signature_signee->name;
                }
            }
             // Render HTML and snapshot
            $html = view(getSettingItem($reservation->team_id , 'print_contract_in_two_lang') ?  'print.new-contract' : 'print.contract', [
                'r' => $reservation,
                'team' => $reservation->team,
                'locale' => app()->getLocale() ,
                'print' => false,
                'signature' => $signature,
                'signature_signee' => $signature_signee,
                'official_signature_signee' => $official_signature_signee,
                'official_signature' => $official_signature
            ])->render();
            
        }else{

            $reservation_id = $reservation->id;
            $balances  = [];
            $reservations_total_prices = [];
            $reservations_total_services = [];
            $reservations_subtotals = [];
            $reservations_taxes = [];
            $vat_taxes = [];
            $ewa_taxes = [];
            $ttx_taxes = [];
            $reservations_services_without_taxes = [];
            $reservations_services_taxes = [];
            $reservations_deposit_insurance_transactions = [];
            $reservation = Reservation::find($reservation_id);
            $push_main_reservation_to_collection = false;
            $reservations_paid = [];

            if($reservation->reservation_type == 'group'){
                $main_reservation = null ;
                if(is_null($reservation->attachable_id)){
                    $main_reservation = $reservation;
                    $push_main_reservation_to_collection = false;
                }else{
                    $main_reservation = Reservation::find($reservation->attachable_id);
                    $push_main_reservation_to_collection = true;
                }
    
                if($main_reservation->status == 'canceled'){
                    $reservations = Reservation::with('wallet','unit','customer')
                    ->where('reservation_type' , 'group')
                    ->where('company_id' , $reservation->company_id)
                    ->where(function ($query) use($reservation,$main_reservation) {
                        return $query->with('unit')->where('id',$reservation->id)->orWhere('attachable_id',$main_reservation->id);
                    })
                    ->where('status' , 'canceled')
                    // ->whereNull('checked_out')
                    ->whereNull('deleted_at')
                    ->orderBy('created_at')
                    ->get();
                }else{
                    $reservations = Reservation::with('wallet','unit','customer')
                    ->where('reservation_type' , 'group')
                    ->where('company_id' , $reservation->company_id)
                    ->where(function ($query) use($reservation,$main_reservation) {
                        return $query->with('unit')->where('id',$reservation->id)->orWhere('attachable_id',$main_reservation->id);
                    })
                    ->where('status' , 'confirmed')
                    // ->whereNull('checked_out')
                    ->whereNull('deleted_at')
                    ->orderBy('created_at')
                    ->get();
                }
    
                if($push_main_reservation_to_collection){
                    $reservations->push($main_reservation);
                }
    
    
                foreach($reservations as $reservationObject){
                    $balances [] = $reservationObject->wallet->decimal_places == 3 ? $reservationObject->balance / 1000 : $reservationObject->balance / 100;
                    $reservations_total_prices [] = $reservationObject->total_price;
                    $reservations_total_services [] = $reservationObject->getServicesSum();
                    $reservations_subtotals [] = $reservationObject->sub_total;
                    $reservations_taxes [] = $reservationObject->ewa_total + $reservationObject->vat_total + $reservationObject->ttx_total;
                    $reservations_services_without_taxes [] = $reservationObject->getServicesWithoutTaxesSum();
                    $reservations_services_taxes [] = $reservationObject->getServicesTaxesSum();
                    $reservations_deposit_insurance_transactions [] = $reservationObject->depositInsuranceTransactions()->sum('amount');
                    $reservations_paid [] = $reservationObject->getDepositSum() - $reservationObject->getWithdrawSum();
                    $vat_taxes [] = $reservationObject->vat_total;
                    $ewa_taxes [] = $reservationObject->ewa_total;
                    $ttx_taxes [] = $reservationObject->ttx_total;
                }
    
                $digital_signature = DigitalSignature::where('team_id',$main_reservation->team_id)->where('ref_id',$main_reservation->id)->where('type','reservation')->first();
                if($digital_signature){
                    $signature =  gzuncompress(base64_decode($digital_signature->signature_base64));
                    if($main_reservation->reservation_type == 'group'){
                        $signature_signee = Company::find($main_reservation->company_id);
                    } else if ($reservation->reservation_type == 'single') {
                        $signature_signee = $main_reservation->customer;
                    }
                    if(isset($signature_signee)) {
                        $signature_signee = $signature_signee->name;
                    }
                }else{
                    $signature = null;
                }
        
                $official_signature = null;
                $official_signature_signee = null;
                $official_signature_object = Reservation::getReservationOfficialSignature($main_reservation->team_id, $main_reservation->id);
        
                if(\Auth()->check() && !isset($official_signature_object)) {
                    $official_signature_object = DigitalSignature::transactionUserSignReservation($main_reservation->id, $main_reservation->team_id);
                }
                if(isset($official_signature_object)) {
                    $official_signature = $official_signature_object->signature;
                    $official_signature_signee = User::find($official_signature_object->user_id);
                    if(isset($official_signature_signee)) {
                        $official_signature_signee = $official_signature_signee->name;
                    }
                }
            
            }

            $html =  view(getSettingItem($main_reservation->team_id , 'print_contract_in_two_lang') ? 'print.new-group-contract' : 'print.company_live_contract', [
                'units_count' => $reservations->count(),
                'main_reservation' => $main_reservation,
                'company' => $main_reservation->company,
                'team' => $main_reservation->company->team,
                'calculations' => [
                    'reservations_total_prices_with_services' => number_format(array_sum($reservations_subtotals) + array_sum($reservations_total_services) + array_sum($reservations_taxes),2),
                    'reservations_subtotals' => number_format(array_sum($reservations_subtotals),2),
                    'reservations_services_without_taxes' => number_format(array_sum($reservations_services_without_taxes),2),
                    'leasing_with_services' => number_format(array_sum($reservations_subtotals) + array_sum($reservations_services_without_taxes) , 2),
                    'reservations_taxes' => number_format(array_sum($reservations_taxes),2),
                    'reservations_services_taxes' => number_format(array_sum($reservations_services_taxes),2),
                    'reservations_deposit_insurance_transactions' => number_format(array_sum($reservations_deposit_insurance_transactions) / ($main_reservation->wallet->decimal_places == 3 ? 1000 : 100 ),2),
                    'reservations_paid' => number_format(array_sum($reservations_paid),2),
                    'vat_taxes' => number_format(array_sum($vat_taxes),2),
                    'ewa_taxes' => number_format(array_sum($ewa_taxes),2),
                    'ttx_taxes' => number_format(array_sum($ttx_taxes),2)
                ],
                'dates_calculations' => startAndEndDateCalculatorWithNightsForGroupContract($reservations),
                'group_balance' => array_sum($balances),
                'locale' => app()->getLocale() ,
                'print' => false,
                'signature' => $signature,
                'signature_signee' => $signature_signee,
                'official_signature_signee' => $official_signature_signee,
                'official_signature' => $official_signature
            ])->render();

        }

        $contract = ReservationContract::create([
            'reservation_id' => $reservation->id,
            'team_id' => $reservation->team_id,
            'status' => $status,
            'signed_at' => $status == 'signed' ? now() : null,
            'html_path' => '',
            'version' => $version,
            'shorten_url_code' => $code
        ]);

       
        $uuid = $contract->uuid;
        $htmlPath = "contracts/{$uuid}.html";

        Storage::disk(env('FILESYSTEM_DRIVER'))->put($htmlPath, $html);

        $contract->update([
            'html_path' => $htmlPath,
        ]);

        // safley remove the signature cause i already have now a snapshot stored in s3
        $digital_signature->delete($digital_signature->id);
  

        return $contract;
    }

    /**
     * Mark a contract as signed.
     */
    public function markAsSigned(ReservationContract $contract): void
    {
        $contract->update([
            'status' => 'signed',
            'signed_at' => now(),
        ]);
    }

    /**
     * Send contract to guest (stub for future integration).
     */
    public function sendContractToGuest(ReservationContract $contract): void
    {
        // You could trigger an email or notification here.
        // For example:
        // Notification::route('mail', $contract->reservation->guest_email)
        //     ->notify(new ContractSignatureRequestNotification($contract));
    }
}

