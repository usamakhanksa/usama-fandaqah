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

Route::group([
    'middleware' => 'auth:api',
    'namespace' => 'Surelab\TechincalSupport\Http\Controllers',
], function () {
    Route::get('/tickets', 'TechincalSupportController@index');
    Route::get('/tickets/{id}', 'TechincalSupportController@show');
    Route::post('/comment/{id}', 'TechincalSupportController@addComment');
    Route::post('/tickets','TechincalSupportController@store');

});
