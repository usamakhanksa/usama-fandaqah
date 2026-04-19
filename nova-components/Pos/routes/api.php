<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'Surelab\Pos\Http\Controllers'] , function(){
    Route::get('services-categories' , 'PosController@getServicesCategories');
    Route::get('category/{cat_id}/services' , 'PosController@getServicesPerCategory');
    Route::get('get-taxes-settings-information' , 'PosController@getTaxesSettingsInformation');
    Route::get('get-occupied-units' , 'PosController@getOccupiedUnits');
    Route::get('deleteTransaction' , 'PosController@deleteTransaction');
    Route::post('add-services' , 'PosController@addServices');
    Route::post('add-services-general' , 'PosController@addServicesGeneral');
    Route::put('update-service-transaction' , 'PosController@updateServiceTransaction');
    Route::post('check-delete-update-capability/{id}' , 'PosController@checkDeleteUpdateCapability');
    Route::post('regenerate-transaction' , 'PosController@regenerateTransaction');

    Route::post('createPostponedTransaction' , 'PosController@createPostponedTransaction');

    //Route::get('service-logs/sync-invoice-to-zatca', 'PosController@syncInvoiceToZatca');


    //zatca phase 2
    //Route::post('service-log/sync-invoice-to-zatca/{id}/{invoice_type}', 'PosController@syncInvoiceToZatca');
    Route::put('service-log/sync-invoice-to-zatca' , 'PosController@syncUpdateInvoiceToZatca');
    Route::get('service-log/zatca-einvoices/{transaction_id}' , 'PosController@getZatcaEInvoices');
    Route::put('service-log/create-credit-note', 'PosController@createCreditNote');
});
