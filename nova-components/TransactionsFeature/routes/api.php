<?php

use Illuminate\Support\Facades\Route;
use Laravel\Nova\Tool;

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

Route::get('/transactionlist', 'Surelab\TransactionsFeature\Http\Controllers\ToolController@Index');
Route::get('/total-cash' , 'Surelab\TransactionsFeature\Http\Controllers\ToolController@total_cash') ;
Route::get('/total-bank-cash' , 'Surelab\TransactionsFeature\Http\Controllers\ToolController@total_bank_cash') ;
Route::get('/grand-total' , 'Surelab\TransactionsFeature\Http\Controllers\ToolController@grand_total') ;
Route::post('/numbers-components' , 'Surelab\TransactionsFeature\Http\Controllers\ToolController@filter_resources') ;
Route::post('/allResourceStatistics' , 'Surelab\TransactionsFeature\Http\Controllers\ToolController@allResourceStatistics') ;
Route::post('/filter-identity-number' , 'Surelab\TransactionsFeature\Http\Controllers\ToolController@filter_identity_number') ;
Route::get('/{resource}/exportExcel' , 'Surelab\TransactionsFeature\Http\Controllers\ToolController@exportExcel');
Route::post('/{resource}/exportPdf' , 'Surelab\TransactionsFeature\Http\Controllers\ToolController@exportPdf');
Route::post('/{resource}/printReport' , 'Surelab\TransactionsFeature\Http\Controllers\ToolController@printReport');
Route::get('/guests' , 'Surelab\TransactionsFeature\Http\Controllers\ToolController@fetchGuests');

Route::post('/monthlyReport' , 'Surelab\TransactionsFeature\Http\Controllers\ToolController@monthlyReport') ;
Route::get('/fetchEmployees' , 'Surelab\TransactionsFeature\Http\Controllers\ToolController@fetchEmployees') ;
Route::post('/hashTransactionId' , 'Surelab\TransactionsFeature\Http\Controllers\ToolController@hashTransactionId') ;



Route::get('/withdrawTransactions' , 'Surelab\TransactionsFeature\Http\Controllers\ToolController@withdrawTransactions') ;
Route::get('/depositTransactions' , 'Surelab\TransactionsFeature\Http\Controllers\ToolController@depositTransactions') ;


Route::post('/safeMovementReportExcel' , 'Surelab\TransactionsFeature\Http\Controllers\ToolController@safeMovementReportExcel') ;
Route::post('/safeMovementReportPdf' , 'Surelab\TransactionsFeature\Http\Controllers\ToolController@safeMovementReportPdf') ;
Route::post('/safeMovementReportPrint' , 'Surelab\TransactionsFeature\Http\Controllers\ToolController@safeMovementReportPrint') ;

Route::get('/revenueTaxFeeReport' , 'Surelab\TransactionsFeature\Http\Controllers\ToolController@revenueTaxFeeReport');
Route::post('/revenueTaxFeeReportPrint' , 'Surelab\TransactionsFeature\Http\Controllers\ToolController@revenueTaxFeeReportPrint') ;
Route::post('/revenueTaxFeeReportExcel' , 'Surelab\TransactionsFeature\Http\Controllers\ToolController@revenueTaxFeeReportExcel') ;


Route::get('/reservationResources' , 'Surelab\TransactionsFeature\Http\Controllers\ToolController@getReservationResources') ;
Route::get('/reservationResourcesTotals' , 'Surelab\TransactionsFeature\Http\Controllers\ToolController@getReservationResourcesTotals') ;
Route::get('/reservationResourceExcel' , 'Surelab\TransactionsFeature\Http\Controllers\ToolController@reservationResourceExcel') ;


Route::post('/refreshData' , 'Surelab\TransactionsFeature\Http\Controllers\ToolController@refreshData') ;
Route::post('/customersInformation' , 'Surelab\TransactionsFeature\Http\Controllers\ToolController@customersInformation') ;
Route::post('/unitsInformation' , 'Surelab\TransactionsFeature\Http\Controllers\ToolController@unitsInformation') ;
Route::post('/contractsInformations' , 'Surelab\TransactionsFeature\Http\Controllers\ToolController@contractsInformations') ;

Route::get('/employeesContracts' , 'Surelab\TransactionsFeature\Http\Controllers\ToolController@getEmployeesContracts') ;
Route::get('/employeesContractsTotals' , 'Surelab\TransactionsFeature\Http\Controllers\ToolController@getEmployeesContractsTotals') ;
Route::get('/employeesContractsExcel' , 'Surelab\TransactionsFeature\Http\Controllers\ToolController@employeeContractsReportExcel') ;


Route::post('/guests-movement-report-excel'   , 'Surelab\TransactionsFeature\Http\Controllers\ToolController@guestsMovementReportExcel');
Route::post('/monthly-report-excel'   , 'Surelab\TransactionsFeature\Http\Controllers\ToolController@monthlyReportExcel');
Route::post('/units-movement-report-excel'   , 'Surelab\TransactionsFeature\Http\Controllers\ToolController@unitsMovementReportExcel');
Route::post('/service-transactions-excel-report'   , 'Surelab\TransactionsFeature\Http\Controllers\ToolController@serviceTransactionsExcelReport');

Route::get('/reservationsInvoices' , 'Surelab\TransactionsFeature\Http\Controllers\ToolController@reservationsInvoices');
Route::post('/invoicesExcelReport' , 'Surelab\TransactionsFeature\Http\Controllers\ToolController@invoicesExcelReport');

Route::get('/services-report' , 'Surelab\TransactionsFeature\Http\Controllers\ToolController@servicesTransactions');

Route::post('/updateTransaction', 'Surelab\TransactionsFeature\Http\Controllers\ToolController@updateTransaction');

Route::delete('/deleteTransaction', 'Surelab\TransactionsFeature\Http\Controllers\ToolController@deleteTransaction');
Route::get('/fetchUnits', 'Surelab\TransactionsFeature\Http\Controllers\ToolController@fetchUnits');

Route::get('services-categories-names' , 'Surelab\TransactionsFeature\Http\Controllers\ToolController@getServicesCategoriesNames');
Route::get('/balady-report' , 'Surelab\TransactionsFeature\Http\Controllers\ToolController@baladyReport');
Route::post('/balady-report-excel' , 'Surelab\TransactionsFeature\Http\Controllers\ToolController@baladyReportExcel');
Route::get('/search-with-time' , 'Surelab\TransactionsFeature\Http\Controllers\ToolController@searchWithTimeSetting');


Route::get('report/units-occupied-all',  'Surelab\TransactionsFeature\Http\Controllers\ToolController@unitsOccupiedReportAll');

