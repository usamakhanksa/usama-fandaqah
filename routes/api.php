<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\{BookingController,DashboardController,ReservationController,SearchController};
Route::middleware('auth:sanctum')->group(function(){
  Route::get('/dashboard/summary',[DashboardController::class,'summary']);
  Route::get('/dashboard/customer-analytics',[DashboardController::class,'customerAnalytics']);
  Route::get('/dashboard/revenue-metrics',[DashboardController::class,'revenueMetrics']);
  Route::get('/dashboard/unit-status',[DashboardController::class,'unitStatus']);
  Route::get('/notifications',[DashboardController::class,'notifications']);
  Route::get('/reservations',[ReservationController::class,'index']);
  Route::get('/rooms/availability',[ReservationController::class,'availability']);
  Route::post('/bookings',[BookingController::class,'store']);
  Route::put('/bookings/{booking}',[BookingController::class,'update']);
  Route::get('/search',[SearchController::class,'autocomplete']);
});
