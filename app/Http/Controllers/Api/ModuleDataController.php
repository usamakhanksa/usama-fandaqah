<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ModuleDataController extends Controller
{
    public function show(): JsonResponse
    {
        $path = (string) request()->query('path', '');

        $moduleMap = [
            'dashboard' => ['table' => 'reservations', 'label' => 'Reservations', 'columns' => ['id', 'code', 'check_in', 'check_out', 'created_at']],
            'client-relations/profiles' => ['table' => 'guests', 'label' => 'Guest Profiles', 'columns' => ['id', 'name', 'email', 'phone', 'created_at']],
            'client-relations/activities' => ['table' => 'activity_logs', 'label' => 'Activities', 'columns' => ['id', 'description', 'created_at']],
            'client-relations/memberships' => ['table' => 'customer_groups', 'label' => 'Memberships', 'columns' => ['id', 'name', 'description', 'created_at']],
            'client-relations/sales' => ['table' => 'bookings', 'label' => 'Sales', 'columns' => ['id', 'reservation_id', 'total_amount', 'created_at']],
            'bookings/reservations' => ['table' => 'reservations', 'label' => 'Reservations', 'columns' => ['id', 'code', 'guest_id', 'check_in', 'check_out']],
            'bookings/blocks' => ['table' => 'reservation_resources', 'label' => 'Blocks', 'columns' => ['id', 'name', 'status', 'start_date', 'end_date']],
            'bookings/events' => ['table' => 'activities', 'label' => 'Events', 'columns' => ['id', 'title', 'description', 'created_at']],
            'front-desk/arrivals' => ['table' => 'reservations', 'label' => 'Arrivals', 'columns' => ['id', 'code', 'check_in', 'reservation_status_id']],
            'front-desk/in-house' => ['table' => 'check_in_records', 'label' => 'In-House', 'columns' => ['id', 'reservation_id', 'unit_id', 'date', 'time']],
            'front-desk/departures' => ['table' => 'check_out_records', 'label' => 'Departures', 'columns' => ['id', 'reservation_id', 'unit_id', 'date', 'final_charges']],
            'front-desk/workspace' => ['table' => 'notifications', 'label' => 'Workspace', 'columns' => ['id', 'title', 'body', 'created_at']],
            'inventory-rooms/room-management' => ['table' => 'rooms', 'label' => 'Rooms', 'columns' => ['id', 'number', 'name', 'status', 'price_per_day']],
            'inventory-rooms/housekeeping' => ['table' => 'housekeeping_tasks', 'label' => 'Housekeeping', 'columns' => ['id', 'unit_id', 'status', 'created_at']],
            'inventory-rooms/restrictions' => ['table' => 'units', 'label' => 'Unit Restrictions', 'columns' => ['id', 'number', 'status', 'created_at']],
            'financials/accounts-receivable' => ['table' => 'invoices', 'label' => 'Accounts Receivable', 'columns' => ['id', 'number', 'amount', 'status', 'created_at']],
            'financials/cashiering' => ['table' => 'payments', 'label' => 'Cashiering', 'columns' => ['id', 'booking_id', 'amount', 'method', 'created_at']],
            'financials/end-of-day' => ['table' => 'fund_movements', 'label' => 'End Of Day', 'columns' => ['id', 'type', 'amount', 'created_at']],
            'financials/comp-accounting' => ['table' => 'financial_records', 'label' => 'Comp Accounting', 'columns' => ['id', 'module', 'amount', 'created_at']],
            'misc/exports' => ['table' => 'notifications', 'label' => 'Exports', 'columns' => ['id', 'title', 'created_at']],
            'misc/interfaces' => ['table' => 'notifications', 'label' => 'Interfaces', 'columns' => ['id', 'title', 'created_at']],
            'misc/service-requests' => ['table' => 'services', 'label' => 'Service Requests', 'columns' => ['id', 'name', 'description', 'created_at']],
        ];

        $fallback = ['table' => 'notifications', 'label' => 'Module Data', 'columns' => ['id', 'title', 'body', 'created_at']];
        $config = $moduleMap[$path] ?? $fallback;

        if (! DB::getSchemaBuilder()->hasTable($config['table'])) {
            return response()->json([
                'path' => $path,
                'label' => $config['label'],
                'count' => 0,
                'rows' => [],
            ]);
        }

        $columns = array_values(array_filter($config['columns'], fn ($column) => DB::getSchemaBuilder()->hasColumn($config['table'], $column)));
        if (empty($columns)) {
            $columns = ['id'];
        }

        $query = DB::table($config['table']);
        if (in_array('created_at', $columns, true)) {
            $query->latest('created_at');
        } else {
            $query->orderByDesc('id');
        }

        return response()->json([
            'path' => $path,
            'label' => $config['label'],
            'count' => DB::table($config['table'])->count(),
            'rows' => $query->limit(8)->get($columns),
        ]);
    }
}
