<?php

namespace App\Http\Controllers\Api;

use App\Team;
use App\Unit;
use App\User;
use Carbon\Carbon;
use App\Reservation;
use App\Subscription;
use GuzzleHttp\Client;
use Laravel\Spark\Spark;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use App\Exceptions\ValidationException;
use App\Http\Resources\EmployeeResource;
use Illuminate\Support\Facades\Validator;

class BillsController extends Controller
{
    /**
     * handle callback.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    public function callback(Request $request)
    {
        $this->handleSubscription($request);
        return redirect('/');
    }

    /**
     * handle webhook.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    public function webhook(Request $request)
    {
        $this->handleSubscription($request);
        return response()->json(['message' => 'success']);
    }

    /**
     * get bill Details from get bills.
     *
     * @param  int $bill_id
     * @return array|null
     */
    protected function billDetails($bill_id)
    {
        $client = new Client(['base_uri' => config('bills.url'), 'verify' => false]);
        $params = [
            'query' => [
                'application_id' => config('bills.client_id'),
                'application_secret' => config('bills.secret'),
            ]
        ];
        $engine = $client->get('/api/v1/bills/'.$bill_id, $params);

        // the payload coming from sure bills end point
        $data = json_decode($engine->getBody()->getContents(), true);

        return isset($data['data']) ? $data['data'] : null;
    }


    /**
     * handle Subscription.
     *
     * @param  int $request
     */
    protected function handleSubscription($request)
    {
        $pieces = explode("-", $request->reference_id);
        $team_id = $pieces[0];
        $license_id = $pieces[1];

        $team = Team::find($team_id);
        $bill = $this->billDetails($request->bill_id);

        $ends_at = $team->ends_at ?  Carbon::parse($team->ends_at)->addYears(1) : Carbon::now()->addYears(1);

        $isSubscriptionFound = Subscription::where([
                                                    'team_id' => $team_id,
                                                    'name'  =>  'team-basic',
                                                    'stripe_plan' => 'team-basic',
                                                    'bill_id' => $request->bill_id 
                                                ])->exists();

        if(isset($bill['status']) && $bill['status'] == 'paid'){

            if(!$isSubscriptionFound){
                $subscription = new Subscription;
                $subscription->team_id        = $team->id;
                $subscription->name           = 'team-basic';
                $subscription->stripe_plan    = 'team-basic';
                $subscription->bill_id        = $request->bill_id;
                $subscription->contract_price = $bill['total'];
                $subscription->contract_note  = 'عن طريق Sure Bills &nbsp;<br> <a href="'.$bill['pay_url'].'" target="_blank">رابط الفاتورة</a>&nbsp;<br>المبلغ المدفوع :' . $bill['total'].'&nbsp;<br> اشترك في الخطة رقم'.$license_id;
                $subscription->trial_ends_at  = null;
                $subscription->ends_at        = $ends_at;
                $subscription->created_at     = Carbon::now();
                $subscription->updated_at     = Carbon::now();
                $subscription->shomos         = ($license_id == 2);
                $subscription->save();
            }
           
        } else {
            $client = new Client(['base_uri' => config('bills.url'), 'verify' => false]);
            $params = [
                'query' => [
                    'application_id' => config('bills.client_id'),
                    'application_secret' => config('bills.secret'),
                ]
            ];
            $client->put('/api/v1/bills/'.$request->bill_id . '/cancel', $params);
        }
    }
}
