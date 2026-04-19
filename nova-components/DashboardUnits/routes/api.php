<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Card API Routes
|--------------------------------------------------------------------------
|
| Here is where you may register API routes for your card. These routes
| are loaded by the ServiceProvider of your card. You're free to add
| as many additional routes to this file as your card may require.
|
*/

// Route::get('/endpoint', function (Request $request) {
//     //
// });
Route::post('/upgrade/check_promo_code', 'SureLab\DashboardUnits\Http\Controllers\PaymentController@check_promo_code');

Route::get('/generate_bill', 'SureLab\DashboardUnits\Http\Controllers\PaymentController@generateBill');

Route::get('/arrivals' , 'SureLab\DashboardUnits\Http\Controllers\DashboardUnitsController@getArrivals');
Route::get('/departures' , 'SureLab\DashboardUnits\Http\Controllers\DashboardUnitsController@getDepartures');
Route::get('/departures-overdue' , 'SureLab\DashboardUnits\Http\Controllers\DashboardUnitsController@getDeparturesOverdue');
Route::get('/awaiting-reservations' , 'SureLab\DashboardUnits\Http\Controllers\DashboardUnitsController@getAwaitingReservations');
Route::get('/get-cleaning-data' , 'SureLab\DashboardUnits\Http\Controllers\DashboardUnitsController@getCleaningData');
Route::post('/get-unit-category-occupancy-data' , 'SureLab\DashboardUnits\Http\Controllers\DashboardUnitsController@getUnitCategoryOccupancyData');
Route::get('countUsers' , 'SureLab\DashboardUnits\Http\Controllers\DashboardUnitsController@countUsers');
Route::get('getUnitCategories' , 'SureLab\DashboardUnits\Http\Controllers\DashboardUnitsController@getUnitCategories');
Route::get('get-day-start-and-day-end-settings' , 'SureLab\DashboardUnits\Http\Controllers\DashboardUnitsController@getDayStartAndDayEndSettings');
Route::post('/createProspect' , 'SureLab\DashboardUnits\Http\Controllers\DashboardUnitsController@createProspect');
Route::get('/getOldProspect' , 'SureLab\DashboardUnits\Http\Controllers\DashboardUnitsController@getOldProspect');
// maintance msg
Route::get('/get-maintance-msg' , 'SureLab\DashboardUnits\Http\Controllers\DashboardUnitsController@getMaintanceMsg');


