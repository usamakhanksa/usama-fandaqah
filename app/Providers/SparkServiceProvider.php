<?php

namespace App\Providers;

use Carbon\Carbon;
use Laravel\Cashier\Cashier;
use Laravel\Spark\Spark;
use Laravel\Spark\Providers\AppServiceProvider as ServiceProvider;

class SparkServiceProvider extends ServiceProvider
{
    /**
     * Your application and company details.
     *
     * @var array
     */
    protected $details = [
        'vendor' => 'Fandaqah',
        'product' => 'Sure LLC',
        'street' => 'Uthman bin affan',
        'location' => 'AlWaha,Riyadh',
        'phone' => '+966 500372573',
    ];

    /**
     * The address where customer support e-mails should be sent.
     *
     * @var string
     */
    protected $sendSupportEmailsTo = 'hamad@maxsys.sa';

    /**
     * All of the application developer e-mail addresses.
     *
     * @var array
     */
    protected $developers = [
        'hamad@maxsys.sa',
        'myasser@sure.com.sa'
    ];

    /**
     * Indicates if the application will expose an API.
     *
     * @var bool
     */
    protected $usesApi = true;

    /**
     * @throws \Exception
     */
    public function booted()
    {
        Cashier::useCurrency('SAR', 'SAR ');

        Spark::useStripe()->noCardUpFront()->teamTrialDays(15);

        Spark::teamPlan('Basic', 'team-basic')
            ->price(1)
            ->yearly()
        ;
        Spark::teamPlan('Fre', 'team-free');
        Spark::useDefaultRole('member');
    }

    public function register()
    {
        Spark::prefixTeamsAs('hotel');
        Spark::ensureEmailIsVerified();

    }
}
