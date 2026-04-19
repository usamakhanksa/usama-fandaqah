<?php

namespace SureLab\Calender\Http\Controllers;

use App\Http\Controllers\Controller;
use App\IptvGuestNeed;
use App\Services\CustomPagination;
use App\Term;
use App\Unit;
use App\UnitCategory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Laravel\Nova\Http\Requests\NovaRequest;

class IptvController extends Controller
{


    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function getNotifications(Request $request)
    {
        $team_id = $request->get('ti');

        $iptv_notifications = getIptvRequests($team_id);


        return response()->json((new CustomPagination($iptv_notifications))->paginate(20));
    }

    public function fulfillRequest(Request $request)
    {
        $iptv_request_id = $request->get('id');

        $iptv_request = IptvGuestNeed::find($iptv_request_id);
        $iptv_request->treated_by = auth()->user()->id;
        $iptv_request->is_treated = 1;
        $iptv_request->save();

        return response()->json([
            'success' => true,
            'message' => 'request fulfilled'
        ]);
    }


    

   
}
