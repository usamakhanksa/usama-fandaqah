<?php

namespace App\Http\Controllers;

use App\Models\GuestCheckin;
use App\Models\WorkspaceTask;
use App\Http\Requests\ProcessCheckinRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FrontDeskController extends Controller {
    public function index(Request $request) {
        $view = $request->get('view', 'arrivals'); // arrivals, in_house, departures
        $search = $request->get('search');

        $query = GuestCheckin::query()->when($search, fn($q) => $q->search($search));

        if ($view === 'arrivals') {
            $query->where('status', 'arrival')->whereDate('expected_arrival_date', '<=', today());
        } elseif ($view === 'in_house') {
            $query->where('status', 'in_house');
        } elseif ($view === 'departures') {
            $query->whereIn('status', ['in_house', 'departure'])
                  ->whereDate('expected_departure_date', '<=', today());
        }

        $guests = $query->orderBy('expected_arrival_date')->paginate(15)->withQueryString();

        return Inertia::render('FrontDesk/Index', [
            'guests' => $guests,
            'currentView' => $view,
            'filters' => ['search' => $search],
            'counts' => [
                'arrivals' => GuestCheckin::where('status', 'arrival')->whereDate('expected_arrival_date', '<=', today())->count(),
                'in_house' => GuestCheckin::where('status', 'in_house')->count(),
                'departures' => GuestCheckin::whereIn('status', ['in_house', 'departure'])->whereDate('expected_departure_date', '<=', today())->count(),
            ]
        ]);
    }

    public function process(ProcessCheckinRequest $request, GuestCheckin $guestCheckin) {
        $data = $request->validated();
        
        // Auto-set timestamps based on status change
        if ($data['status'] === 'in_house' && !$guestCheckin->actual_arrival_time) {
            $data['actual_arrival_time'] = now();
        } elseif ($data['status'] === 'checked_out' && !$guestCheckin->actual_departure_time) {
            $data['actual_departure_time'] = now();
        }

        $guestCheckin->update($data);
        return redirect()->back()->with('success', 'Guest record updated successfully.');
    }
}
