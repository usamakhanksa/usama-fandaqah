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
Route::get('/get-total', 'CustomerReviewsController@getTotal');
Route::get('/get-reviews', 'CustomerReviewsController@getReviews');
Route::get('/get-review/{rating}', 'CustomerReviewsController@getReview');
Route::post('/approve-review/{rating}', 'CustomerReviewsController@approveReview');
Route::put('/update-review/{rating}', 'CustomerReviewsController@updateReview');

Route::get('/get-teams', 'CustomerReviewsController@getTeams');
