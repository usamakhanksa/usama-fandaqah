<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\{CustomerMetric,DashboardBanner,Guest,Notification,Reservation,RevenueMetric,Room,UnitStatusMetric,User};
use Illuminate\Http\Request;
class DashboardController extends Controller {
  public function summary() {
    return response()->json([
      'banners'=>DashboardBanner::query()->where('is_active',1)->get(),
      'stats'=>[
        'rooms'=>Room::count(),'guests'=>Guest::count(),'profit'=>RevenueMetric::sum('amount'),
        'active_users'=>User::where('last_seen_at','>=',now()->subDay())->count(),
      ],
      'recent_status'=>[
        'new_booked'=>Reservation::whereDate('created_at',today())->count(),
        'check_in'=>Reservation::whereDate('check_in',today())->count(),
        'check_out'=>Reservation::whereDate('check_out',today())->count(),
      ]
    ]);
  }
  public function customerAnalytics(Request $request){
    $period=$request->get('period','monthly');
    return response()->json(CustomerMetric::where('period',$period)->orderBy('metric_date')->get());
  }
  public function revenueMetrics(){ return response()->json(RevenueMetric::orderBy('metric_date')->get()); }
  public function unitStatus(){ return response()->json(UnitStatusMetric::orderBy('metric_date')->get()); }
  public function notifications(){ return response()->json(Notification::latest()->limit(10)->get()); }
}
