<?php

namespace Surelab\TechincalSupport\Http\Controllers;

use App\Http\Controllers\Controller;
use CURLFile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TechincalSupportController extends Controller
{

    /**
     * get tickets list
     */
    public function index(Request $request): JsonResponse
    {

        $page = ($request->page) ? $request->page : 1;
        $user = auth()->user();
        $handeskUrl = env('HANDESK_URL', 'http://handesk.test');
        $handeskToken = env('HANDESK_TOKEN', 'the-api-token');

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $handeskUrl . '/api/tickets?page=' . $page,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_POSTFIELDS => '{
            "requester":{
                "name":"' . $user->name . '",
                "email":"' . $user->email . '"
            },
            "hotel_id" : "' . $user->current_team_id . '"
        }',
            CURLOPT_HTTPHEADER => array(
                'token: ' . $handeskToken,
                'Content-Type: application/json'
            ),
        ));

        $response = json_decode(curl_exec($curl));
        curl_close($curl);
        if (isset($response->data))
            return response()->json(['success' => true, 'message' => 'Tickets get successfully', 'data' => $response->data]);
        else
            return response()->json(['success' => false, 'message' => 'Something is wrong', 'data' => $response]);
    }

    /**
     * get ticket by id
     * @params $id int
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        $user = auth()->user();
        $handeskUrl = env('HANDESK_URL', 'http://handesk.test');
        $handeskToken = env('HANDESK_TOKEN', 'the-api-token');

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $handeskUrl . '/api/tickets/' . $id,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_POSTFIELDS => '{
                "requester":{
                    "name":"' . $user->name . '",
                    "email":"' . $user->email . '"
                }
            }',
            CURLOPT_HTTPHEADER => array(
                'token: ' . $handeskToken,
                'Content-Type: application/json'
            ),
        ));

        $response = json_decode(curl_exec($curl));

        curl_close($curl);

        if (isset($response->data->id))
            return response()->json(['success' => true, 'message' => 'Ticket retrived successfully', 'data' => $response->data]);
        else
            return response()->json(['success' => false, 'message' => 'Something went wrong']);
    }

    /**
     * add comment on teckit
     */

    public function addComment(Request $request, $id)
    {

        $user = auth()->user();
        $handeskUrl = env('HANDESK_URL', 'http://handesk.test');
        $handeskToken = env('HANDESK_TOKEN', 'the-api-token');

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $handeskUrl . '/api/tickets/' . $id . '/comments',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => '{
            "language":"ar",
            "requester":{
                    "name":"' . $user->name . '",
                    "email":"' . $user->email . '"
                },
            "body":"' . $request->comment . '"
        }',
            CURLOPT_HTTPHEADER => array(
                'token: ' . $handeskToken,
                'Content-Type: application/json'
            ),
        ));

        $response = json_decode(curl_exec($curl));

        curl_close($curl);

        if (isset($response->data->id))
            return response()->json(['success' => true, 'message' => 'Comment created']);
        else
            return response()->json(['success' => false, 'message' => 'Something went wrong']);
    }
    /**
     * store new technical support ticket
     */
    public function store(Request $request): JsonResponse
    {

        $user = auth()->user();
        $handeskUrl = env('HANDESK_URL', 'http://handesk.test');
        $handeskToken = env('HANDESK_TOKEN', 'the-api-token');
        $hotel_info = [
            'hotel_id' => $user->current_team_id,
            'hotel_name' => $user->currentTeam()->name,
            'main_category' => $request->get('main_category'),
            'sub_category' => $request->get('sub_category')
        ];
        $team_id = 1 ;
        $data = array('requester' => ['name' => $user->name, 'email' => $user->email, 'fandaqah_team_id' => $user->current_team_id, 'fandaqah_team_name' => 'hala'], 'title' => $request->title, 'body' => $request->body, 'hotel_info' => $hotel_info , 'team_id' => $team_id);
        httpBuildQueryForCurl($data, $postData);
        if ($request->hasFile('attachment'))
            $postData['attachment'] = new CURLFile($request->file('attachment')->getPathName());



        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $handeskUrl . '/api/tickets',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $postData,
            CURLOPT_HTTPHEADER => array(
                'token: ' . $handeskToken
            ),
        ));

        $response = json_decode(curl_exec($curl));
        curl_close($curl);
        if (isset($response->data->id))
            return response()->json(['success' => true, 'message' => 'Ticket added successfully']);
        else
            return response()->json(['success' => false, 'message' => 'Something went wrong', 'data' => $response]);
    }
}
