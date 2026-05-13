@extends('layouts.app')

@section('title', 'Arrivals & Departures')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Arrivals & Departures</h1>
        <div class="flex space-x-4">
            <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Today
            </button>
            <button class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                Tomorrow
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Arrivals Today -->
        <div class="bg-white shadow-md rounded-lg p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                <span class="bg-blue-100 text-blue-800 rounded-full w-8 h-8 flex items-center justify-center mr-2">+</span>
                Arrivals Today ({{ count($arrivalsToday) }})
            </h2>
            <div class="space-y-4">
                @forelse($arrivalsToday as $reservation)
                <div class="border border-gray-200 rounded-lg p-4">
                    <div class="flex justify-between">
                        <div>
                            <p class="font-medium">{{ $reservation->customer->name }}</p>
                            <p class="text-sm text-gray-600">Unit: {{ $reservation->unit->unit_number }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-gray-600">{{ $reservation->date_in->format('H:i') }}</p>
                            <p class="text-xs text-gray-500">#{{ $reservation->number }}</p>
                        </div>
                    </div>
                    <div class="mt-2 flex justify-between items-center">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                            {{ $reservation->reservation_category_type }}
                        </span>
                        <button class="text-blue-600 hover:text-blue-900 text-sm">
                            Check-in
                        </button>
                    </div>
                </div>
                @empty
                <p class="text-gray-500 text-center py-4">No arrivals scheduled for today</p>
                @endforelse
            </div>
        </div>

        <!-- Departures Today -->
        <div class="bg-white shadow-md rounded-lg p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                <span class="bg-red-100 text-red-800 rounded-full w-8 h-8 flex items-center justify-center mr-2">→</span>
                Departures Today ({{ count($departuresToday) }})
            </h2>
            <div class="space-y-4">
                @forelse($departuresToday as $reservation)
                <div class="border border-gray-200 rounded-lg p-4">
                    <div class="flex justify-between">
                        <div>
                            <p class="font-medium">{{ $reservation->customer->name }}</p>
                            <p class="text-sm text-gray-600">Unit: {{ $reservation->unit->unit_number }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-gray-600">{{ $reservation->date_out->format('H:i') }}</p>
                            <p class="text-xs text-gray-500">#{{ $reservation->number }}</p>
                        </div>
                    </div>
                    <div class="mt-2 flex justify-between items-center">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            Checked In
                        </span>
                        <button class="text-green-600 hover:text-green-900 text-sm">
                            Check-out
                        </button>
                    </div>
                </div>
                @empty
                <p class="text-gray-500 text-center py-4">No departures scheduled for today</p>
                @endforelse
            </div>
        </div>

        <!-- In-House Guests -->
        <div class="bg-white shadow-md rounded-lg p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                <span class="bg-green-100 text-green-800 rounded-full w-8 h-8 flex items-center justify-center mr-2">✓</span>
                In-House Guests ({{ count($inHouseGuests) }})
            </h2>
            <div class="space-y-4">
                @forelse($inHouseGuests as $reservation)
                <div class="border border-gray-200 rounded-lg p-4">
                    <div class="flex justify-between">
                        <div>
                            <p class="font-medium">{{ $reservation->customer->name }}</p>
                            <p class="text-sm text-gray-600">Unit: {{ $reservation->unit->unit_number }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-gray-600">Stay: {{ $reservation->nights }} nights</p>
                            <p class="text-xs text-gray-500">#{{ $reservation->number }}</p>
                        </div>
                    </div>
                    <div class="mt-2 flex justify-between items-center">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            Check-in: {{ $reservation->checked_in->format('M j, Y') }}
                        </span>
                        <button class="text-blue-600 hover:text-blue-900 text-sm">
                            View
                        </button>
                    </div>
                </div>
                @empty
                <p class="text-gray-500 text-center py-4">No in-house guests</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection