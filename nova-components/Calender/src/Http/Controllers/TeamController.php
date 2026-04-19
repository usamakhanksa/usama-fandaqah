<?php

namespace SureLab\Calender\Http\Controllers;

use App\Http\Controllers\Controller;
use App\PaymentLog;
use App\Team;
use FrittenKeeZ\Vouchers\Facades\Vouchers;
use FrittenKeeZ\Vouchers\Models\Voucher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Laravel\Cashier\Exceptions\SubscriptionCreationFailed;
use Laravel\Nova\Nova;
use Laravel\Spark\Events\Teams\Subscription\TeamSubscribed;
use Laravel\Spark\Spark;
use Laravel\Spark\TeamSubscription;

class TeamController extends Controller
{
    public function check_coupon(Request $request): JsonResponse
    {
        $valid = Vouchers::redeemable($request->get('voucher_code'));
        $data = [];
        if ($valid) {
            $plan = Spark::paidTeamPlans();
            $voucher = Voucher::where('code', $request->get('voucher_code'))->first();
            $t = $plan->price - ($voucher->amount / 100 * $plan->price);
            $vat = $t * .05;
            $data = [
                'discount_percentage' => $voucher->amount,
                'price_before_discount' => $plan->price,
                'price_after_discount' => $t,
                'vat' => $vat,
                'total' => $t + $vat,
            ];
        }
        return response()->json(['valid' => $valid, 'data' => $data]);
    }

    public function update_subscription(Request $request)
    {
        $plan = Spark::paidTeamPlans();

        $next = now()->addYear();
        /** @var Team $team */
        $team = \Auth::user()->currentTeam;

        if ($subscription = $team->subscriptions()->first() and $subscription->provider_plan == 'team-basic' and !$subscription->ended()) {
            dd($subscription, $subscription->ended());
            throw new SubscriptionCreationFailed();
        }

        $total = $plan->price + ($plan->price * .05);

        $uid = uniqid();

        if ($request->coupon_added and $request->voucher_code and Vouchers::redeemable($request->voucher_code)) {
            $voucher = Voucher::where('code', $request->voucher_code)->first();
            $total = $plan->price - ($voucher->amount / 100 * $plan->price);
            $vat = $total * .05;
            $total+= $vat;

            $uid = $request->voucher_code . "-" . $team->id . "-" . $uid;
        }

        
    }

    public function payment_results($uid)
    {
        
    }
}
