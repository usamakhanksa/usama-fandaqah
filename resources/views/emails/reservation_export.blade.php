@component('mail::message')
# Reservation Export

Hello,

Please find attached the reservation export data as requested.

@component('mail::button', ['url' => config('app.url')])
Visit Dashboard
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent