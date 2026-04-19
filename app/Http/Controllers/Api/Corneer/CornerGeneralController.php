<?php
/**
 *  * Created by PhpStorm.
 * User: Mohamed Yasser ( yasoesr@gmail.com )
 * Date: 9/22/19
 * Time: 4:03 PM
 */

namespace App\Http\Controllers\API\Corneer;

use App\Team;
use App\Unit;
use App\Customer;
use Carbon\Carbon;
use App\UnitOption;
use App\Reservation;
use Carbon\CarbonPeriod;
use App\Handlers\Settings;
use App\OnlineReservation;
use App\Mail\NewReservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Vinkla\Hashids\Facades\Hashids;
use App\Http\Controllers\Controller;
use Spatie\MediaLibrary\Models\Media;
use App\Events\OnlineReservationCreated;
use App\Http\Resources\Corneer\UnitResource;
use App\Http\Requests\OnlineReservationRequest;
use Surelab\Settings\Http\Controllers\WebsiteSettingsController;

class CornerGeneralController extends Controller
{
    public function list($id , $date_start, $date_end)
    {

        return  UnitResource::collection(
            Unit::whereEnabled(true)->whereTeamId($id)->where('status', '=', 1)->available($date_start)->paginate()
        );
    }

    public function index(Request $request)
    {
        return  UnitResource::collection(
            Unit::whereEnabled(true)->whereTeamId($request->get('id'))->where('status', '=', 1)->paginate()
        );
    }

    public function getUnit($id, $date_start, $date_end)
    {
        /** @var Unit $unit */
        $unit = Unit::find(Hashids::decode($id)[0]);

        $date_start = Carbon::parse($date_start);
        $date_end = Carbon::parse($date_end);
        $diff_days = $date_start->diffInDays($date_end);
        $diff_nights = ($diff_days === 0) ? 1 : $diff_days;

        $result = [
            'id' => $unit->id,
            'hash'  =>  Hashids::encode($unit->id),
            'unit_number' => $unit->unit_number,
            'name' => $unit->name,
            'main_image'    =>  is_null($unit->getFirstMediaUrl('main')) || empty($unit->getFirstMediaUrl('main')) ? asset('images/placeholder.jpg') : $unit->getFirstMediaUrl('main'),
            'description' =>  $unit->description,
            'youtube_link' =>  $unit->youtube_link,
            'short_description' =>  $unit->short_description,
            'has_reservation' => count($unit->getReservations($date_start)) > 0,
            'prices' => [
                'day' => $unit->dayPrice($date_start->format('l')),
                'month' => $unit->monthPrice(),
                'hour' => $unit->hourPrice(),
            ],
            'reservation' => [
                'start_date' => $date_start->format('d-m-Y'),
                'end_date' => $date_end->format('d-m-Y'),
                'days' => $diff_days,
                'nights' => $diff_nights,
                'prices' => $unit->getDatesFromRange($date_start, $date_end),
            ],
            'reservations_date' => $unit->getReservationsDates(),
            'images'  =>  $this->transformMediaCollection($unit->getMedia('images')),
            'options'   =>  $unit->options(),
            'special_features'  =>  \App\UnitSpecialFeature::whereIn('id', explode(',', $unit->special_features))->pluck('name')->toArray(),
            'general_features'  =>  \App\UnitGeneralFeature::whereIn('id', explode(',', $unit->general_features))->pluck('name')->toArray(),
        ];

        return response()->json($result);
    }

    private function transformMediaCollection($media)
    {
        $gallery = [];
        /** @var Media $media */
        foreach ($media as $media) {
            $gallery[]  = $media->getFullUrl();
        }

        return $gallery;
    }

    public function makeBooking(Request $request)
    {
        $customer = Customer::whereEmail($request->get('email'))->where('team_id', '=', $request->get('teamId'))->first();

        if (is_null($customer)) {
            $customer = Customer::create([
                'name' =>  $request->get('first_name') .' '. $request->get('last_name'),
                'email' =>  $request->get('email'),
                'phone' =>  $request->get('phone'),
                'team_id'   =>  $request->get('teamId')
            ]);
        }

        /** @var OnlineReservation $date */
        $date = OnlineReservation::create([
            'customer_id'   =>  $customer->id,
            'date_in'   =>  $request->get('in_date'),
            'date_out'   =>  $request->get('out_date'),
            'price'   =>  $request->get('price'),
            'nights'   =>  $request->get('nights'),
            'unit_id'   =>  Hashids::decode($request->get('id'))[0],
            'team_id'   =>  $request->get('teamId')
        ]);
        $reservation = $date;
        $hotelEmail =  DB::table('settings')->where('key' , '=' , 'day_end')->where('team_id' , '=' , $reservation->team_id)->value('value');
        $hotelEmail = ($hotelEmail != "" && !is_null($hotelEmail) && filter_var($hotelEmail, FILTER_VALIDATE_EMAIL)) ? $hotelEmail : 'no-reply@fandaqah.com';

        $subject = 'تفاصيل حجزك رقم ';
        $subject .= $reservation->number;
        $subject .= ' - بانتظار التاكيد - ';
        $subject .= $reservation->team->name;
        $url = $reservation->team->private_domain ? $reservation->team->private_domain :  $reservation->team->slug . '.' . env('CUSTOMER_WEBSITE_DOMAIN');

        $data = [
            'to' => $customer->email,
            'reply_to' => $hotelEmail,
            'subject' => __('Payment Receipt For Reservation : :number',['number' => $reservation->number], 'ar'),
            'html' => view('email.new_reservation')
                ->with(['reservation' =>  $reservation, 'owner' => $reservation->team->owner, 'url'    =>  $url])->render(),
        ];

        $send = sendMailUsingMailMicroservice($data);


        $subject = 'حجز جديد رقم ';
        $subject .= $reservation->number;
        $subject .= ' - بانتظار التاكيد - ';
        $subject .= $reservation->team->name;
        $data = [
            'to' => $reservation->team->owner->email,
            'reply_to' => $hotelEmail,
            'subject' => __('Payment Receipt For Reservation : :number',['number' => $reservation->number], 'ar'),
            'html' => view('email.new_reservation')
                ->with(['reservation' =>  $reservation, 'owner' => $reservation->team->owner, 'url'    =>  $url])->render(),
        ];
        $send = sendMailUsingMailMicroservice($data);

        // \Mail::to($customer)->send(new NewReservation($date));
        // \Mail::to($date->team->owner)->send(new NewReservation($date, true));

        $response = array_merge($date->toArray(), [
            'hash'  => Hashids::encode($date->id)
        ]);

        event(new OnlineReservationCreated($date));

        return response()->json($response);
    }

    public function getReservation($id)
    {
        /** @var OnlineReservation $reservation */
        $reservation = OnlineReservation::withoutGlobalScope('team_id')->find(Hashids::decode($id)[0]);

//        $response = array_merge($reservation->toArray(), [
//            'hash'  => Hashids::encode($date->id)
//        ]);

        return response()->json($reservation->toArray());
    }

    public function getSlugByDomain($domain)
    {
        $team = Team::ofPrivateDomain($domain)->first();

        return $team ? ['slug' => $team->slug] : null;
    }

    public function getSettings(Request $request)
    {
        $team = Team::find($request->get('id'));
        return (new WebsiteSettingsController)->websiteSettings($team);
    }

    public function getTeam()
    {
        return response()->json(Team::find(\View::getShared()['currentTeamId'])->toArray());
    }


}
