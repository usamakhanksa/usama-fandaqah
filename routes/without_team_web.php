<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/r/{reservation}', 'WelcomeController@rating')->name('rating');
Route::post('/r/{reservation}', 'WelcomeController@storeRating')->name('rating.submit');

Route::put('/rating/{reservation}','WelcomeController@update')->name('rating.update');
