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
    'namespace' => 'SureLab\Calender\Http\Controllers',
], function () {
    Route::get('/', 'UnitController@calenderUnits');
    Route::get('/reserved', 'UnitController@calenderUnitsReserved');
    Route::get('/units/available', 'UnitController@available');
    Route::get('/units', 'UnitController@Index');
    Route::any('nd/payments/{uid}/results', 'TeamController@payment_results')->name('request_payment_results');
    Route::post('/upgrade/update_subscription', 'TeamController@update_subscription');
    Route::post('/units/status', 'UnitController@status');
    Route::get('/unit/{id}/{start_date}/{end_date}', 'UnitController@show');
    Route::get('/units/{start_date}/{end_date}', 'UnitController@get_units');
    Route::get('unit/getTeamSettingDayStart', 'UnitController@getTeamSettingDayStart');
    Route::get('/customer/search', 'CustomerController@filter');
    Route::get('/guest/search', 'GuestController@filter');

    Route::get('/dashboard', 'UnitController@counts');
    //    Route::post('/units/reservationNightsCount', 'UnitController@reservationNightsCount');

    Route::get('calculations', 'UnitController@calculations');
    Route::get('notification-steps', 'UnitController@notificationSteps');
    Route::get('unit-reservation-popover', 'UnitController@unitReservationPopover');

    //    Route::get('/reservations', 'ReservationController@index');
    Route::post('/reservation', 'ReservationController@store');

    Route::post('/reservation/add-invoice', 'ReservationController@add_invoice');
    Route::post('/reservation/delete-transaction', 'ReservationController@deleteTransaction');
    Route::post('/reservation/guest', 'ReservationController@storeGuest');
    Route::delete('/reservation/guest/{id}/{reservation}', 'ReservationController@deleteGuest');
    Route::post('/reservation/checks', 'ReservationController@storeChecks');
    Route::post('/reservation/update-customer', 'ReservationController@updateCustomer');
    Route::post('/reservation/cancel', 'ReservationController@cancel');
    Route::post('/reservation/cancel-fees', 'ReservationController@cancelFees');

    Route::put('/reservation/update_reservation', 'ReservationController@update_reservation');
    Route::post('/reservation/transaction', 'ReservationController@storeTransaction');
    Route::get('/reservations/{reservation}/transactions', 'ReservationController@getTransactions');
    Route::post('/reservations/{reservation}/transactions', 'ReservationController@updateReservationBalance');
    
    Route::put('/reservation/transaction', 'ReservationController@updateTransaction');
    Route::get('/reservation/service-list', 'ReservationController@services');
    Route::get('/reservation/{id}', 'ReservationController@show');
    Route::post('/reservation/{reservation}/messages', 'ReservationController@messages');

    Route::post('comments', 'ReservationController@comment');
    Route::put('comments', 'ReservationController@updateComment');
    Route::delete('comments/{id}', 'ReservationController@deleteComment');
    Route::post('/hashTransactionId', 'ReservationController@hashTransactionId');
    Route::post('/cancel-online', 'ReservationController@cancelOnline');
    Route::post('/confirm-online', 'ReservationController@confirmOnline');

    Route::get('/terms', 'TermsController@getTerms');
    Route::get('/terms/cash-receipt', 'TermsController@showCashReceipt');
    Route::get('/terms/payment-voucher', 'TermsController@showPaymentVoucher');

    Route::post('/reservation/delete-service', 'ReservationController@deleteService');
    Route::post('/reservation/reservation-statistics-blocks', 'ReservationController@reservationStatisticsBlocks');

    Route::get('/term', 'TermsController@getTermDetails');

    Route::post('/customers/customersCount', 'CustomerController@customersCount');
    Route::post('/reservations-management-excel', 'ReservationController@reservationsManagementExcel');
    Route::post('/reservations/management/excel-report', 'ReservationController@reservationsManagementExcelReport');

    Route::get('/check-previous-reservation-and-future-reservations', 'UnitController@checkPreviousReservationAndFutureReservations');
    Route::get('/services-tax-info', 'UnitController@servicesTaxInfo');

    Route::post('/reservation/addServices', 'ReservationController@addServices');
    Route::post('/reservation/updateServices', 'ReservationController@updateServices');

    Route::get('/check-services-categories', 'ReservationController@checkServicesCategories');

    Route::get('/unit/selectors', 'UnitController@selectors');
    Route::get('/unit/commonSelectors', 'UnitController@commonSelectors');

    Route::get('/checkCustomerNotes', 'CustomerController@getCustomerNotes');

    Route::post('/reservation/deleteInvoice/{id}', 'ReservationController@deleteInvoice');
    Route::get('/reservationInvoices', 'ReservationController@getReservationInvoices');
    Route::get('/invoices/checkPreviousInvoices', 'ReservationController@checkPreviousInvoices');
    Route::get('/unit/{id}', 'UnitController@getUnit');

    Route::get('/unit/{id}/get-special-prices/{start_date}/{end_date}', 'UnitController@getSpecialPrices');
    Route::get('/unit/{id}/get-offers/{start_date}/{end_date}', 'UnitController@getOffers');

    Route::get('/check-convert-unit-to-under-cleaning', 'UnitController@checkConvertToUnderCleaningFromCheckout');

    Route::get('/reservations/units-filter-values', 'ReservationController@unitFilterValues');
    Route::get('/reservations/customer-categories', 'ReservationController@customerCategories');
    Route::get('/reservations-data', 'ReservationController@getReservationsData');
    Route::get('/reservations-data-have-services', 'ReservationController@getReservationsHaveServicesData');
    Route::post('/reservations-data-statistics', 'ReservationController@getReservationsDataStatistics');

    Route::delete('reservations/{id}/delete', 'ReservationController@deleteReservation');

    Route::get('/customer-creditor-debtor', 'ReservationController@customerCreditorDebtor');
    Route::get('/company-creditor-debtor', 'ReservationController@companyCreditorDebtor');
    Route::get('/customer-total-balance', 'ReservationController@customerTotalBalance');
    Route::get('/reservation-balance', 'ReservationController@reservationBalance');

    Route::get('getLastReservationId', 'ReservationController@getLastReservationId');

    Route::get('/re-enable-dateout', 'UnitController@renableDateOut');

    Route::get('/check-current-chekedin-reservation', 'ReservationController@checkCheckinReservation');
    Route::get('/last-checkedin-reservation', 'ReservationController@lastCheckedinReservation');
    Route::get('/reset-reservation-checkout', 'ReservationController@resetReservationCheckedOut');

    Route::post('/change-unit-status', 'ReservationController@changeUnitStatus');

    Route::post('/update-invoice', 'ReservationController@updateInvoice');
    Route::get('/get-occupied-data', 'UnitController@getOccupiedData');
    Route::get('/reservations-dates', 'UnitController@getReservationsDates');
    Route::get('/cancel-checkout', 'ReservationController@cancelCheckout');

    Route::get('/check-change', 'ReservationController@getCurrentChange');
    Route::get('/check-add-service-capability', 'ReservationController@checkAddServiceCapability');

    Route::get('safe-balance', 'UnitController@getSafeBalance');
    Route::get('get-awaiting-confirmation-reservations-count', 'UnitController@getAwaitingConfirmationReservationsCount');

    Route::get('overlap-check', 'UnitController@checkOverlapped');

    Route::get('/companies/{company}/reservations/list', 'ReservationController@getCompanyReservations');
    Route::post('/link-reservations', 'ReservationController@linkReservations');
    Route::post('/unlink-reservation/{reservation}', 'ReservationController@unlinkReservation');
    //------------- New Group Reservation Korsi Fl Kolob -----------------
    Route::get('/companies/{company}/attachable-reservations', 'ReservationController@getCompanAttachableReservations');
    Route::get('group-reservation/sibling/{reservation}/check-insurance-transaction', 'ReservationController@checkInsuranceTransactionForGroupReservation');
    Route::get('group-reservation/{reservation}/is-last', 'ReservationController@checkIfCurrentReservationIsTheLastOneToCheckout');
    Route::get('/customers/{customer}', 'CustomerController@getCustomerInfo');
    Route::post('reservation/{reservation}/automated-group-invoice', 'ReservationController@createAutomatedGroupInvoice');
    Route::get('/customers/{customer}', 'CustomerController@getCustomerInfo');
    Route::post('reservation/add-credit-note/{reservationInvoice}', 'ReservationController@createCreditNote');
    Route::get('reservation/invoice/{reservationInvoice}/credit-note', 'ReservationController@checkCreditNote');
    Route::get('reservation/{id}/get-invoices', 'ReservationController@getInvoices');
    Route::post('units/get-available-units', 'UnitController@getAvailableUnitsForGroupReservation');
    Route::post('units/create-reservations', 'UnitController@createReservations');
    Route::get('/reservation-noc/{id}', 'UnitController@showReservationWithoutCustomer');
    Route::post('/reservation/create-group-invoice', 'ReservationController@createGroupInvoice');
    Route::post('/reservation/create-invoice-for-free-services', 'ReservationController@createInvoiceForFreeServices');

    Route::post('/create-entity-and-checkout', 'ReservationController@createEntityAndCheckout');
    Route::post('/convert-to-group-reservation', 'ReservationController@convertToGroupReservation');
    Route::post('/update-grp-price', 'ReservationController@updateGroupReservationPriceThroughUnitCategory');

    Route::get('/reservations/sources', 'ReservationController@reservationsSources');

    Route::post('/send-payment-sms', 'ReservationController@sendPaymentSms');
    Route::post('/send-payment-email', 'ReservationController@sendPaymentEmail');

    Route::get('/fetch-reservations-list-from-channel-manager', 'ChannelManagerController@fetchReservations');

    Route::post('reservation/post-invoice-to-zatca/{id}/{invoice_type}/{invoice_sub_type}/{mark_credit_notes_as_sent}', 'ReservationController@pushInvoiceToZatca');

    Route::get('/get-qualified-for-checkin-reservations', 'ReservationController@getQualifiedForCheckinReservations');
    Route::post('/doCheckinGroupReservations', 'ReservationController@doCheckinGroupReservations');
    Route::post('/doCheckoutGroupReservations', 'ReservationController@doCheckoutGroupReservations');

    Route::post('/reservation/get-zatca-receipt', 'ReservationController@getZatcaReceipt');

    Route::get('/get-qualified-for-cancel-reservations','ReservationController@getQualifiedForCancelReservations');
    Route::post('/reservation/group/cancel','ReservationController@cancelGroupReservations');
    Route::post('/reservation/check-cancel/is-included-in-an-invoice','ReservationController@checkReservationCanBeCanceled');
    Route::get('/reservation/check/has-customer','ReservationController@reservationHasCustomer');
    Route::post('/reservation/{reservation}/remove-company','ReservationController@removeCompanyFromReservation');
    Route::post('/reservation/{reservation}/company/{company_id}/change','ReservationController@changeCompanyOnReservation');

    Route::get('/reservations/unit-category-filter-values', 'ReservationController@unitCategoryFilterValues');

    Route::get('server/current-date','ReservationController@getServerDate');

    Route::post('/reservations/update-reservation-prices', 'ReservationController@updateReservationPrices');

    Route::get('server/current-date','ReservationController@getServerDate');

    Route::post('/reservation/send-contract-via-sms','ContractSMSController@sendContractViaSMS');

    Route::post('/sms/printless/send','SMSSendPrintlessController@send');

    Route::post('/reservation/send-promissory-via-sms', 'ContractSMSController@sendPromissoryViaSMS');

    Route::get('/reservation/fetch-main/{id}', 'ReservationController@getMainReservation');

    Route::get('/check-permissions-for-user-in-multiple-roles-for-team', 'PermissionCheckerController@checkMultiplePermissions');

    Route::get('/reservation/call-grp-mapper/{id}','ReservationController@callGrpMapper');
});
