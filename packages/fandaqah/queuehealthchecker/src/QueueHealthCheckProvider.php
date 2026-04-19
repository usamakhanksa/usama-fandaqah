<?php

namespace Fandaqah\Queuehealthchecker;

use GuzzleHttp\Client;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Queue;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Support\Facades\Cache;
class QueueHealthCheckProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
        Queue::after(function (JobProcessed $event) {
            Cache::put('queue:last_processed_at', now(), 3600);
        });
        
        $this->commands([
            WatchQueueHealth::class
        ]);

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'queuehealth');

    }
}
