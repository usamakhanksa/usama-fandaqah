<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RoomStatusLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoomStatusLogController extends Controller
{
    public function index(Request $request)
    {
        $query = RoomStatusLog::with(['unit', 'user', 'reference'])
            ->where('team_id', Auth::user()->team_id);

        if ($request->has('unit_id')) {
            $query->where('unit_id', $request->unit_id);
        }

        if ($request->has('status')) {
            $query->where('to_status', $request->status);
        }

        if ($request->has('user_id')) {
            $query->where('changed_by', $request->user_id);
        }

        if ($request->has('date_from')) {
            $query->whereDate('changed_at', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('changed_at', '<=', $request->date_to);
        }

        if ($request->has('reason')) {
            $query->where('change_reason', 'like', '%' . $request->reason . '%');
        }

        return $query->orderBy('changed_at', 'desc')->paginate($request->get('per_page', 15));
    }

    public function timeline(Request $request, $unitId)
    {
        return RoomStatusLog::with(['user', 'reference'])
            ->where('unit_id', $unitId)
            ->where('team_id', Auth::user()->team_id)
            ->orderBy('changed_at', 'desc')
            ->get();
    }
}
