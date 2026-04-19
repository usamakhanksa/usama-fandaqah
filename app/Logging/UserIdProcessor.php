<?php
namespace App\Logging;

use Illuminate\Support\Facades\Auth;

class UserIdProcessor
{
    public function __invoke(array $record)
    {
        if (Auth::check()) {
            $record['extra']['team_id'] =  auth()->user()->current_team_id;
        }

        return $record;
    }
}
