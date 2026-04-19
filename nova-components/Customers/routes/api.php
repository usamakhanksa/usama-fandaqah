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

// });


Route::group([
    'middleware' => 'auth:api',
    'namespace' => 'Surelab\Customers\Http\Controllers',
], function () {

    Route::get('/', 'CustomerController@index');
    Route::post('/', 'CustomerController@store');
    Route::get('/{id}', 'CustomerController@show');
    Route::put('/{id}', 'CustomerController@update');
    Route::delete('/{id}', 'CustomerController@destroy');
    Route::post('/filter', 'CustomerController@filters');
    Route::get('/utilites/get' , 'CustomerController@getUtilities');
    Route::get('/reservations/get' , 'CustomerController@getCustomerReservations');
    Route::get('/notes/list' , 'CustomerController@getCustomerNotes');
    Route::post('/notes/store' , 'CustomerController@storeCustomerNote');
    Route::put('/notes/{id}' , 'CustomerController@updateCustomerNote');
    Route::delete('/notes/{id}' , 'CustomerController@deleteCustomerNote');


    Route::get('/companies/list', 'CompanyController@list');
    Route::get('/companies/{company}', 'CompanyController@show');
    Route::post('/companies/create', 'CompanyController@store');
    Route::put('/companies/update', 'CompanyController@update');
    Route::get('/companies/{company}/reservations/list', 'CompanyController@getCompanyReservations');
    Route::get('/companies/{company}/notes/list', 'CompanyController@getCompanyNotes');
    Route::post('/companies/{company}/notes/store' , 'CompanyController@storeCompanyNote');
    Route::put('/companies/notes/{companyNote}' , 'CompanyController@updateCompanyNote');

    Route::delete('/companies/notes/{companyNote}' , 'CompanyController@deleteCompanyNote');

    Route::get('/company/target/search' , 'CompanyController@search');

    Route::post('/individuals/create', 'CompanyController@storeIndividual');

    Route::post('/individuals/createFromCustomer', 'CompanyController@storeIndividualFromCustomer');

});



