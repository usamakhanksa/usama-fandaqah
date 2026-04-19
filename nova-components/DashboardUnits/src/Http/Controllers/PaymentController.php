<?php

namespace SureLab\DashboardUnits\Http\Controllers;

use App\Http\Controllers\Controller;
use App\PaymentLog;
use App\PromoCode;
use App\PromoCodeLog;
use App\Team;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Laravel\Cashier\Exceptions\SubscriptionCreationFailed;
use Laravel\Nova\Nova;
use Laravel\Spark\Events\Teams\Subscription\TeamSubscribed;
use Laravel\Spark\Spark;
use Laravel\Spark\TeamSubscription;

class PaymentController extends Controller
{
    public function check_promo_code(Request $request): JsonResponse
    {
        $promo_code = PromoCode::where('name', $request->get('promo_code'))->first();

        if(isset($promo_code)){
            if($promo_code->is_valid){
	        	PromoCodeLog::create([
	        		'team_id' => auth()->user()->current_team_id,
	        		'user_id' => auth()->user()->id,
	        		'promo_code_id' => $promo_code->id,
	        	]);
				return response()->json([
					'valid' => true,
					'data' => $promo_code,
					'message' => __('Coupon added successfully'),
				]);
	        }else{
	        	return response()->json([
					'valid' => false,
					'data' => null,
					'message' => __('Promo Code has expired'),
				]);
	        }
        }
        return response()->json([
        	'valid' => false,
        	'data' => null,
			'message' => __('Promo Code is invalid'),
        ]);
    }


    /**
     * Generat our sweet Bill
     * @param $reservation
     * @param $deposit_amount
     * @param $total_price
     * @return mixed
     */
    protected function generateBill(Request $request)
    {
        $bills_client_id = config('bills.client_id');
        $bills_secret =  config('bills.secret');

        $promo_code = PromoCode::where('name', $request->promo_code)->first();

        $user = auth()->user();
        $team = $user->currentTeam;
        $items = $request->items;
        $license = json_decode($request->license, true);
        $reference_id = $team->id.'-'.$license['id'].'-'.date("Y");

        $data = [
            'customer_name' => $user->name,
            'customer_email' => $user->email,
            'customer_mobile' => $user->phone,
            'customer_notes' => $team->name,
            'due_date' => Carbon::now()->format('d-m-Y'),
            'expiry_date' =>  0,
            'expiry_hours' =>  6,
            'expiry_minutes' => 0,
            'send_email' =>  false,
            'send_sms' =>  false,
            'add_tax' =>  'on',
            'tax_value' => 15,
            'add_discount' =>  isset($promo_code)? 'on':null,
            'discount_type' =>  isset($promo_code) && $promo_code->discount_type == 'percent' ? 'percentage' : 'fixed',
            'discount_value' =>  isset($promo_code) ? $promo_code->discount_value :null,
            'reference_id' => $reference_id,
            'application_id' => $bills_client_id,
            'application_secret' => $bills_secret
        ];


        foreach ($items as $item) {
        	$item = json_decode($item, true);

            $data['items'][] = [
                'name'     => __($item['name']),
                'price'    => $item['price'],
                'quantity' =>  $item['quantity'],
            ];
        }
        // dd($data);
        $client = new Client(['base_uri' => config('bills.url'), 'verify' => false]);
        $engine = $client->post('api/v1/bills/create',[
            'form_params' => $data,
            'exception' => true,
            'http_errors'=>false,
            "headers" => ['Accept' => 'application/json']
        ]);

        if ($engine->getStatusCode() == 422) {
            $errors = json_decode($engine->getBody()->getContents(),true);
            reset($errors['errors']);
            $first_key = key($errors['errors']);
            $error['key'] = $first_key;
            $error['message'] = $errors['errors'][$first_key][0];

	        return [
	            'errors' => $error,
	        ];
        }

        $payload = json_decode($engine->getBody()->getContents(), true);


        return [
            'bill' => $payload['data'],
            'errors' => $error ?? null
        ];
    }
}
