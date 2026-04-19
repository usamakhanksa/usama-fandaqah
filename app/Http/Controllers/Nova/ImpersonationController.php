<?php
/**
 *  * Created by PhpStorm.
 * User: Mohamed Yasser ( yasoesr@gmail.com )
 * Date: 9/25/19
 * Time: 2:37 PM
 */

namespace App\Http\Controllers\Nova;

use App\Http\Controllers\Controller;
use App\Jobs\TeamDeleted;
use App\Jobs\TeamRestored;
use App\Team;
use Illuminate\Http\Request;
use Laravel\Spark\Spark;

class ImpersonationController extends Controller
{
    /**
     * Impersonate the given user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $userId
     * @return \Illuminate\Http\Response
     */
    public function impersonate(Request $request, $userId)
    {
        if (auth()->check())
            auth()->logout();
        session()->flush();

        \Auth::login(Spark::user()->findOrFail($userId));

        return redirect('/home');
    }

    /**
     * @param Request $request
     * @param $teamId
     * @throws \Exception
     */
    public function teamDeleted(Request $request, $teamId)
    {
        $team = Team::withTrashed()->findOrFail($teamId);
        if (!$team->trashed()) {
            $team->delete();
        }
        if ($team) {
            dispatch(new TeamDeleted($team));
        }
    }

    /**
     * @param Request $request
     * @param $teamId
     * @throws \Exception
     */
    public function teamRestored(Request $request, $teamId)
    {
        $team = Team::withTrashed()->findOrFail($teamId);
        if ($team->trashed()) {
            $team->restore();
        }
        if ($team) {
            dispatch(new TeamRestored($team));
        }
    }
}
