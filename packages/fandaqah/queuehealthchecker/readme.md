*Configs*
mail.ms_mail_url -> Url to send mail
mail.ms_mail_username
mail.ms_mail_password
app.queue_failed_notifier -> failed notifier

*Don't forget to register provider*
Add in Providers array in config.php
Fandaqah\Queuehealthchecker\QueueHealthCheckProvider::class

*Add command in Console/Kernel.php*
$schedule->command('queue:health-check')->everyMinute();