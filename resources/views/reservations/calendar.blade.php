@extends('layouts.app')

@section('title', 'Reservation Calendar')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Reservation Calendar</h1>
        <button 
            onclick="location.reload()" 
            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
        >
            Refresh
        </button>
    </div>

    <div class="bg-white shadow-md rounded-lg p-6">
        <div id="calendar"></div>
    </div>
</div>

<link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/main.min.css' rel='stylesheet' />
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        events: {
            url: '{{ route("calendar.reservations.events") }}',
            method: 'GET',
            extraParams: {
                _token: '{{ csrf_token() }}'
            },
            failure: function() {
                alert('There was an issue fetching the events!');
            }
        },
        eventClick: function(info) {
            // Open reservation details modal
            window.location.href = '/reservations/' + info.event.id;
        },
        loading: function(bool) {
            document.getElementById('loading').style.display = bool ? 'block' : 'none';
        }
    });
    
    calendar.render();
});
</script>
@endsection