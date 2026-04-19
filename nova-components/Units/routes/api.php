<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Tool API Routes
|--------------------------------------------------------------------------
|
| Here is where you may register API routes for your tool. These routes
| are loaded by the ServiceProvider of your tool. They are protected
| by your tool's "Authorize" middleware by default. Now, go build!
|
*/

// Route::get('/endpoint', function (Request $request) {
//     //
// });

//Route::post('/reservation', 'ReservationController@store');


Route::group([
    'middleware' => 'auth:api',
    'namespace' => 'Surelab\Units\Http\Controllers',
], function () {

    Route::get('get-unit-category-name/{category}', 'UnitController@getUnitCategory');
    Route::get('get_units/{category}', 'UnitController@get_units');
    Route::post('update_selected_units', 'UnitController@update_selected_units');
    Route::get('status', 'UnitController@index');
    Route::get('housekeeping', 'UnitController@housekeeping')->name('housekeeping');

    Route::get('categories' , 'UnitController@getCategories');

    // this is where the magic happens -_-
    Route::get('get-resources/{model_name}' , 'UnitController@getResources');
    Route::delete('delete-resource' , 'UnitController@deleteResource');

    Route::get('offers-and-special-prices-categories' , 'UnitController@getOffersAndSpecialPricesCategories');
    Route::post('store-offer' , 'UnitController@storeOffer');
    Route::get('get-offers' , 'UnitController@getOffers');
    Route::put('update-offer' , 'UnitController@updateOffer');

    Route::post('store-special-price' , 'UnitController@storeSpecialPrice');
    Route::put('update-special-price' , 'UnitController@updateSpecialPrice');

    Route::get('reservations-table-data' , 'UnitController@reservationsTable');
    Route::get('activity-details' , 'UnitController@getActivityDetails');
    Route::get('term' , 'UnitController@getTerm');
    //staah api
    Route::post('push-rooms' , 'UnitController@staahAvailability');

    Route::post('/reservations-table/guess-reservations' , 'UnitController@guessReservations');

});
