<?php

namespace Fandaqah\Queuehealthchecker;

use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class WatchQueueHealth extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue:health-check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command watch queues health and notify the user if any queue stales';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $lastProcessed = Cache::get('queue:last_processed_at');

        if (!$lastProcessed) {
            $this->warn('No queue activity recorded yet.');
            return;
        }

        $diff = now()->diffInMinutes($lastProcessed);
            
        if ($diff > 60) { // e.g. alert if no jobs processed in last 10 minutes
            
            $this->error("Queue seems stale! Last activity: {$diff} minutes ago");
            
            $view = view('queuehealth::stale', ['app_name' => config('app.name')])->render();

            $data = [
                'replyTo' => null,
                'email' => config('app.queue_failed_notifier'),
                'template' => json_encode($view),
                'subject' => "Queue stalled since $diff minutes",
            ];
          
            $client = new Client();
            
            $client->request('POST', config('mail.ms_mail_url') . '/api/send-mail', [
                'json' => $data,
                'headers' => [
                    'Authorization' => 'Basic ' . base64_encode(config('mail.ms_mail_username') . ':' . config('mail.ms_mail_password')),
                    'Content-Type' => 'application/json',
                ]
            ]);
        } else {
            $this->info("Queue healthy. Last job {$diff} minutes ago.");
        }
    }
}
