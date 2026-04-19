<?php

namespace App\Http\Middleware;

use App\Exceptions\ValidationException;
use App\Team;
use Closure;
use Illuminate\Http\Request;

class HasManageTeam
{
    /**
     * @param Request $request
     * @param Closure $next
     * @return mixed
     * @throws Exception
     */
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->user()->teams->contains($request->current_team_id)) {
            throw new ValidationException("this user cant manage this team", 490);
        }

        return $next($request);
    }
}
