<?php

namespace App\Http\Controllers\Api;

use Aghanem\Jawaly\Facades\Jawaly;
use App\Http\Controllers\Controller;
use App\Http\Resources\EmployeeResource;
use App\Integration;
use App\User;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class TeamController extends Controller
{
    /**
     * get transaction index method
     *
     * @return App\Http\Resources\TransactionResource
     */
    public function employees(Request $request)
    {
        $current_team_id = $request->get('current_team_id');
        $transactions = QueryBuilder::for(User::class)
            ->allowedIncludes(['customer'])
            ->allowedFilters([
                AllowedFilter::exact('id'),
                AllowedFilter::scope('by_creator'),
            ])
            ->where(['current_team_id' => $current_team_id])
            ->defaultSort('-id')
            ->paginate($request->get('per_page', 30));

        return EmployeeResource::collection($transactions)->additional([
            'meta' => []
        ]);
    }
    /**
     * get transaction index method
     *
     * @return App\Http\Resources\TransactionResource
     */
    public function jawaly_balance(Request $request)
    {
        if ($integration = Integration::findByKeyAndTeamId('Jawaly', $request->team_id)->first()) {

            $credentials = json_decode($integration->values);
            $app_id = $credentials->api_key;
            $app_sec = $credentials->api_secret;
            $app_hash  = base64_encode("$app_id:$app_sec");
            $base_url = "https://api-sms.4jawaly.com/api/v1/";
            $query = [];
            $query["is_active"] = 1; // get active only
            $query["order_by"] = "id"; // package_points, current_points, expire_at or id (default)
            $query["order_by_type"] = "desc"; // desc or asc
            $query["page"] = 1;
            $query["page_size"] = 10;
            $query["return_collection"] = 1; // if you want to get all collection
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $base_url . 'account/area/me/packages?' . http_build_query($query),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER => array(
                    'Accept: application/json',
                    'Content-Type: application/json',
                    'Authorization: Basic ' . $app_hash
                ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);

            $responseDecoded = json_decode($response);
            if ($responseDecoded && $responseDecoded->code == 400) {
                return response()->json(['success' => false, 'message' => $responseDecoded->message,  'balance' => 0]);
            }
            if ($responseDecoded && $responseDecoded->code == 200) {
                return response()->json(['success' => true, 'message' => $responseDecoded->message,  'balance' => $responseDecoded->total_balance]);
            }
        }
        return response()->json(['balance' => 0]);
    }
}
