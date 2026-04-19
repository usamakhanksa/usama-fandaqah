<?php

namespace SureLab\Calender\Http\Controllers;

use App\IptvGuestNeed;
use Illuminate\Http\Request;
use App\Services\CustomPagination;

use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class PermissionCheckerController extends Controller
{

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkMultiplePermissions(Request $request)
    {
        $user_id = $request->user_id;
        $team_id = $request->team_id;
        $permissions = $request->permissions; // array of slugs

        return checkPermissionForUserInMultipleRolesForTeam($user_id, $team_id, $permissions);

    }



   
}
