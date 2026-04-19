<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Reservation;
use Illuminate\Http\Request;
class ReservationController extends Controller {
  public function index(Request $request){
    $q=Reservation::query()->with(['guest','room','unit']);
    if($request->filled('type')) $q->where('stay_type',$request->string('type'));
    if($request->filled('search')) $q->where('code','like','%'.$request->string('search').'%');
    return $q->orderBy('check_in')->paginate(10);
  }
  public function availability(Request $request){
    $date=$request->date('date',today());
    return response()->json(['available_rooms'=>\App\Models\Room::whereDoesntHave('reservations',fn($r)=>$r->whereDate('check_in','<=',$date)->whereDate('check_out','>=',$date))->count()]);
  }
}
