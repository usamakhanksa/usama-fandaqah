<?php

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

Route::post('/storeTransaction', 'Surelab\FinancialManagement\Http\Controllers\ToolController@storeTransaction');
Route::get('/transaction', 'Surelab\FinancialManagement\Http\Controllers\ToolController@transactionDetails');
Route::post('/updateTransaction', 'Surelab\FinancialManagement\Http\Controllers\ToolController@updateTransaction');
Route::post('/fetchStatistics', 'Surelab\FinancialManagement\Http\Controllers\ToolController@fetchStatistics');
Route::post('/deleteTransaction', 'Surelab\FinancialManagement\Http\Controllers\ToolController@deleteTransaction');
Route::get('/managementBalance', 'Surelab\FinancialManagement\Http\Controllers\ToolController@managementBalance');

Route::post('/financial-management-excel', 'Surelab\FinancialManagement\Http\Controllers\ToolController@finanacialManagementExcel');
Route::get('/get-transactions', 'Surelab\FinancialManagement\Http\Controllers\ToolController@getTransactions');
Route::get('/get-terms', 'Surelab\FinancialManagement\Http\Controllers\ToolController@getTerms');

Route::post('/promissory/add-transaction', 'Surelab\FinancialManagement\Http\Controllers\ToolController@addPromissoryTransaction');
Route::get('hashTransactionId', 'Surelab\FinancialManagement\Http\Controllers\ToolController@hashTransactionId');
Route::get('vat-setting', 'Surelab\FinancialManagement\Http\Controllers\ToolController@getVatSetting');
Route::post('/process-zatca-einvoice/{team_id}', 'Surelab\FinancialManagement\Http\Controllers\ToolController@processZatcaEInvoice');

Route::get('/all-teams', 'Surelab\FinancialManagement\Http\Controllers\ToolController@getAllTeams');
Route::get('/payment/get-service-invoicing', 'Surelab\FinancialManagement\Http\Controllers\ToolController@getPaymentServiceInvoices');
Route::post('/reservations/get-numbers', 'Surelab\FinancialManagement\Http\Controllers\ToolController@getReservationsNumbers');

Route::post('/promissories/delete', 'Surelab\FinancialManagement\Http\Controllers\ToolController@deletePromissory');
