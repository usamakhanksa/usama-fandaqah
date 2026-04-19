<?php

use Illuminate\Support\Facades\Route;
use Surelab\Settings\Http\Controllers\ChannelController;

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
    'namespace' => 'Surelab\Settings\Http\Controllers'
], function () {
    Route::get('/integrations-log-shms', 'LogController@shomos');
    Route::get('/jawaly-log', 'LogController@jawaly');
    Route::get('/unifonic-log', 'LogController@unifonic');
    Route::get('/integrations-log/{key}', 'LogController@scth');
    Route::get('/get-general-settings' , 'SettingsController@getGeneralSettings');
    Route::post('/update-channel-status','SettingsController@updateChannelStatus');
    Route::get('/get_counters', 'SettingsController@getCounters');
    Route::put('/update_counters', 'SettingsController@updateCounters');
    Route::get('/get/{group}', 'SettingsController@get');
    Route::get('/installed', 'SettingsController@installed');
    Route::get('/integrations/{key}', 'SettingsController@getIntegrations');
    Route::get('/checkConnection/{key}', 'SettingsController@checkConnection');


    Route::post('/register', 'SettingsController@register');
    Route::post('/disconnect', 'SettingsController@disconnect');
    Route::put('/update', 'SettingsController@process');
    Route::put('/update-domain', 'WebsiteSettingsController@updateDomain');
    Route::put('/check-domain', 'WebsiteSettingsController@checkDomain');
    Route::post('/clear-private-domain', 'WebsiteSettingsController@clearPrivateDomain');
    Route::put('/check-dns', 'WebsiteSettingsController@checkDns');
    Route::get('/website-settings/{team}', 'WebsiteSettingsController@websiteSettings');
    Route::put('/update-website-settings/{team}', 'WebsiteSettingsController@updateWebsiteSettings');
    Route::put('/update-website-gallery/{team}', 'WebsiteSettingsController@updateWebsiteGallery');
    Route::post('/upload-file', 'WebsiteSettingsController@uploadFile');
    Route::post('/upload-favicon', 'WebsiteSettingsController@uploadFavicon');
    Route::get('/fetchSmsIntegration', 'SettingsController@fetchSmsIntegration');
    Route::get('/getUserObject', 'SettingsController@getUserObject');
    Route::post('/sendEmailVerification', 'SettingsController@sendEmailVerification');
    Route::post('/checkSmsVerification', 'SettingsController@checkSmsVerification');
    Route::post('/sendSmsVerification', 'SettingsController@sendSmsVerification');
    Route::post('/updateUserProfileData', 'SettingsController@updateUserProfileData');

    Route::post('/enable-or-disable-photo-album', 'WebsiteSettingsController@enableOrDisablPhotoAlbum');
    Route::post('/slider-upload-handler', 'WebsiteSettingsController@sliderUploadHandler');
    Route::post('/photo-album-upload-handler', 'WebsiteSettingsController@photosUploadHandler');
    Route::get('/slides/{team}', 'WebsiteSettingsController@getSlides');
    Route::get('/photos/{team}', 'WebsiteSettingsController@getPhotos');
    Route::delete('/slides/{id}/delete', 'WebsiteSettingsController@deleteSlide');
    Route::get('/get-related-teams-with-settings/{team}', 'WebsiteSettingsController@getRelatedTeamsWithSettings');
    Route::post('/store-featured-unit-categories/{team}', 'WebsiteSettingsController@storeFeaturedUnitCategories');
    Route::post('/about-us-upload-handler/{team}', 'WebsiteSettingsController@aboutUsUploadHandler');
    Route::post('/intro-video-handler/{team}', 'WebsiteSettingsController@introVideo');
    Route::post('/add-new-page', 'WebsiteSettingsController@addPage');
    Route::get('/get-pages', 'WebsiteSettingsController@getPages');
    Route::get('/get-page-details/{id}', 'WebsiteSettingsController@getPageDetails');
    Route::put('/edit-page', 'WebsiteSettingsController@editPage');
    Route::delete('/delete-page/{id}', 'WebsiteSettingsController@deletePage');
    Route::get('/get-team-info', 'WebsiteSettingsController@getTeamInfo');
    Route::get('/integration-controls', 'SettingsController@getIntegrationControls');
    Route::post('/check-4jawaly-credentials', 'SettingsController@checkJawalyCredentials');
    Route::post('/connectWithJawaly', 'SettingsController@connectWithJawaly');

    Route::get('get-team-settings', 'SettingsController@teamSettings');
    Route::post('save-facitily-settings', 'SettingsController@saveFacilitySettings');
    Route::post('addReservationService', 'SettingsController@addReservationService');
    Route::post('editReservationService', 'SettingsController@editReservationService');
    Route::post('deleteReservationService', 'SettingsController@deleteReservationService');
    Route::get('getReservationServices', 'SettingsController@getReservationServices');
    Route::get('getReservationSettings', 'SettingsController@getReservationSettings');
    Route::get('getMaintenanceSettings', 'SettingsController@getMaintenanceSettings');
    Route::post('createActionType', 'SettingsController@createActionType');
    Route::post('editActionType', 'SettingsController@editActionType');
    Route::post('deleteActionType', 'SettingsController@deleteActionType');
    Route::post('getActionTypes', 'SettingsController@getActionTypes');
    Route::post('connectWithZatcaPhaseTwo', 'SettingsController@connectWithZatcaPhaseTwo');
});
Route::get('/get-units', [ChannelController::class, 'getUnits']);
//update-units
Route::post('/update-units', [ChannelController::class, 'updateUnits']);
//get-reservations
Route::get('/get-reservations', [ChannelController::class, 'getReservations']);
//categories
Route::post('/categories', [ChannelController::class, 'getCategories']);
//push-rooms
Route::post('/push-rooms', [ChannelController::class, 'staahAvailability']);

Route::get('/get', [ChannelController::class, 'get']);

