<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRoomRequest;
use App\Http\Resources\RoomResource;
use App\Models\Guest;
use App\Models\OccupancyMetric;
use App\Models\Room;
use App\Models\RoomMetric;
use App\Models\RoomType;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RoomController extends Controller
{
    public function metrics()
    {
        $latestRoomMetrics = RoomMetric::query()->latest('metric_date')->first();
        $latestOccupancy = OccupancyMetric::query()->latest('metric_date')->first();

        return response()->json([
            'total_rooms' => Room::count(),
            'available_rooms' => Room::where('status', 'available')->count(),
            'not_ready_rooms' => Room::where('status', 'not_ready')->count(),
            'booked_rooms' => Room::whereIn('status', ['occupied', 'reserved'])->count(),
            'unit_occupancy' => $latestOccupancy?->unit_occupancy ?? 0,
            'total_guests' => Guest::count(),
            'delta' => [
                'total_rooms' => 2.7,
                'available_rooms' => 1.4,
                'not_ready_rooms' => -4.2,
                'booked_rooms' => 3.9,
            ],
            'history' => $latestRoomMetrics,
        ]);
    }

    public function filters()
    {
        return response()->json([
            'sort' => ['latest', 'price_asc', 'price_desc', 'name_asc'],
            'gender' => ['all', 'male', 'female'],
            'room_types' => RoomType::query()->orderBy('name')->get(['id', 'name']),
            'statuses' => ['available', 'occupied', 'reserved', 'maintenance', 'cleaning', 'not_ready'],
        ]);
    }

    public function index(Request $request)
    {
        $query = Room::query()->with(['roomType', 'roomFloor']);

        if ($request->filled('search')) {
            $term = $request->string('search');
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('number', 'like', "%{$term}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('gender') && $request->string('gender') !== 'all') {
            $query->where('gender', $request->string('gender'));
        }

        if ($request->filled('room_type_id')) {
            $query->where('room_type_id', $request->integer('room_type_id'));
        }

        match ($request->string('sort', 'latest')->value()) {
            'price_asc' => $query->orderBy('price_per_day'),
            'price_desc' => $query->orderByDesc('price_per_day'),
            'name_asc' => $query->orderBy('name'),
            default => $query->latest(),
        };

        return RoomResource::collection($query->paginate($request->integer('per_page', 10))->withQueryString());
    }

    public function store(StoreRoomRequest $request)
    {
        return new RoomResource(Room::create($request->validated()));
    }

    public function update(StoreRoomRequest $request, Room $room)
    {
        $room->update($request->validated());

        return new RoomResource($room->refresh()->load(['roomType', 'roomFloor']));
    }

    public function destroy(Room $room)
    {
        $room->delete();

        return response()->json(['message' => 'Room deleted']);
    }

    public function availability(Request $request)
    {
        $date = $request->date('date', today());
        $rooms = Room::where('status', 'available')->count();

        return response()->json(['date' => $date->toDateString(), 'available_rooms' => $rooms]);
    }

    public function export(Request $request): StreamedResponse
    {
        $rows = Room::query()->with(['roomType', 'roomFloor'])->latest()->limit(1000)->get();

        $headers = ['Content-Type' => 'text/csv', 'Content-Disposition' => 'attachment; filename="rooms.csv"'];

        return response()->stream(function () use ($rows) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Room Name', 'Room Type', 'Room Floor', 'Price Per Day', 'Status']);
            foreach ($rows as $row) {
                fputcsv($file, [$row->id, $row->name, $row->roomType?->name, $row->roomFloor?->name ?? $row->floor, $row->price_per_day, $row->status]);
            }
            fclose($file);
        }, 200, $headers);
    }
}
