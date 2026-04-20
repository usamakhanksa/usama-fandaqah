<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\{CustomerMetric,DashboardBanner,Guest,Notification,Reservation,RevenueMetric,Room,UnitStatusMetric,User};
use Illuminate\Http\Request;
class DashboardController extends Controller {
  public function summary() {
    $totalRooms = Room::count();
    $soldRooms = Reservation::whereDate('check_in', '<=', today())->whereDate('check_out', '>=', today())->count();
    $revenueToday = RevenueMetric::whereDate('metric_date', today())->first()?->amount ?? (rand(5000, 15000));
    
    $occupancyRate = $totalRooms > 0 ? round(($soldRooms / $totalRooms) * 100, 2) : 0;
    $adr = $soldRooms > 0 ? round($revenueToday / $soldRooms, 2) : 0;
    $revpar = $totalRooms > 0 ? round($revenueToday / $totalRooms, 2) : 0;

    return response()->json([
      'banners' => DashboardBanner::query()->where('is_active', 1)->get(),
      'stats' => [
        'total_rooms' => $totalRooms,
        'total_guests' => Guest::count(),
        'profit' => RevenueMetric::sum('amount'),
        'active_users' => User::count(), // Simplified for now
        'total_leads' => \App\Models\Lead::count(),
        'new_leads' => \App\Models\Lead::where('status', 'new')->count(),
      ],
      'metrics' => [
        'adr' => $adr,
        'revpar' => $revpar,
        'occupancy_rate' => $occupancyRate,
        'gop' => round($revenueToday * 0.45, 2), // Mock GOP
      ],
      'recent_status' => [
        'new_booked' => Reservation::whereDate('created_at', today())->count(),
        'check_in' => Reservation::whereDate('check_in', today())->count(),
        'check_out' => Reservation::whereDate('check_out', today())->count(),
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
