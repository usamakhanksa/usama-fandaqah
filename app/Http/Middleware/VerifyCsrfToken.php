<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * Indicates whether the XSRF-TOKEN cookie should be set on the response.
     *
     * @var bool
     */
    protected $addHttpCookie = true;

    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        '*/impersonate',
        '/home/print/transactionsReportPrint',
        '/home/print/servicesTransactionsReportPrint',
        "/home/print/customersReport",
        "/home/print/unitsReport",
        "/home/print/safeMovementReport",
        "/home/print/safeMovementReportAll",
        "/home/print/safeMovementReportStatisticsOnly",
        "/home/print/revenueTaxFeesReport",
        "/home/print/reservationResources",
        "/home/print/reservationsReport",
        "/home/print/reservations-print-report",
        "/home/print/reservationSummary",
        "/home/print/contractsReport",
        "/home/print/invoicesReportPrint",
        "/home/reservation/pos-print",
        'webhook-receiving-url',
        "/home/print/baladyReport",
        "/home/print/promissoriesPrint",
        "/home/print/receiptReport",
        "/api/callback"
    ];
}
