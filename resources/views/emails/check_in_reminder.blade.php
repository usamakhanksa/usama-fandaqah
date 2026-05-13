@component('mail::message')
# Check-In Reminder

Dear {{ $reservation->customer->name }},

This is a friendly reminder that your reservation (#{{ $reservation->number }}) is scheduled to begin on {{ $reservation->date_in }}.

Unit: {{ $reservation->unit->unit_number }}

@if($customMessage)
{{ $customMessage }}
@endif

Please contact us if you need to make any changes to your reservation.

@component('mail::button', ['url' => config('app.url')])
View Reservation
@endcomponent

Thank you,<br>
{{ config('app.name') }}
@endcomponent