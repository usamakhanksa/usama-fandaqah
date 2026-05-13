<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class MasterDataController extends Controller
{
    /**
     * Allowed tables for generic CRUD
     */
    protected $allowedTables = [
        'countries' => ['permission' => 'manage_locations'],
        'cities' => ['permission' => 'manage_locations'],
        'id_types' => ['permission' => 'manage_identity_options'],
        'banks' => ['permission' => 'manage_finance_options'],
        'senders' => ['permission' => 'manage_finance_options'],
        'unit_options' => ['permission' => 'manage_room_options'],
        'turnaway_reasons' => ['permission' => 'manage_guest_options'],
        'service_categories' => ['permission' => 'manage_pos_options'],
        'announcements' => ['permission' => 'manage_website_options'],
        'highlights' => ['permission' => 'manage_guest_options'],
        'room_types' => ['permission' => 'manage_room_options'],
        'room_floors' => ['permission' => 'manage_room_options'],
        'sources' => ['permission' => 'manage_marketing_options'],
        'payment_methods' => ['permission' => 'manage_finance_options'],
        'hotel_types' => ['permission' => 'manage_room_options'],
        'breakfast_prices' => ['permission' => 'manage_reservation_options'],
        'ewa_mappers' => ['permission' => 'manage_finance_options'],
        'website_settings' => ['permission' => 'manage_website_options'],
        'website_pages' => ['permission' => 'manage_website_options'],
        'notification_controls' => ['permission' => 'manage_website_options'],
    ];

    /**
     * List all allowed tables
     */
    public function tables()
    {
        return response()->json([
            'tables' => array_keys($this->allowedTables),
            'count' => count($this->allowedTables)
        ]);
    }

    /**
     * List records from a master table
     */
    public function index(Request $request, $table)
    {
        $this->validateTable($table);
        $this->checkPermission($table, 'view');

        $query = DB::table($table);

        // Team scoping if column exists
        if (Schema::hasColumn($table, 'team_id')) {
            $query->where('team_id', $request->user()->current_team_id);
        }

        // Soft deletes if column exists
        if (Schema::hasColumn($table, 'deleted_at')) {
            $query->whereNull('deleted_at');
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->string('search');
            $columns = Schema::getColumnListing($table);
            $query->where(function($q) use ($search, $columns) {
                foreach ($columns as $column) {
                    if (in_array($column, ['name', 'title', 'label_en', 'label_ar'])) {
                        $q->orWhere($column, 'like', "%$search%");
                    }
                }
            });
        }

        return response()->json($query->latest()->paginate($request->integer('per_page', 15)));
    }

    /**
     * Store a new record
     */
    public function store(Request $request, $table)
    {
        $this->validateTable($table);
        $this->checkPermission($table, 'create');

        $data = $request->all();
        
        // Add team_id if required
        if (Schema::hasColumn($table, 'team_id')) {
            $data['team_id'] = $request->user()->current_team_id;
        }

        $id = DB::table($table)->insertGetId($data);

        return response()->json([
            'message' => __('Record created successfully'),
            'id' => $id
        ], 201);
    }

    /**
     * Update a record
     */
    public function update(Request $request, $table, $id)
    {
        $this->validateTable($table);
        $this->checkPermission($table, 'update');

        $query = DB::table($table)->where('id', $id);

        if (Schema::hasColumn($table, 'team_id')) {
            $query->where('team_id', $request->user()->current_team_id);
        }

        $query->update($request->except(['id', 'team_id', 'created_at', 'updated_at']));

        return response()->json(['message' => __('Record updated successfully')]);
    }

    /**
     * Delete a record
     */
    public function destroy(Request $request, $table, $id)
    {
        $this->validateTable($table);
        $this->checkPermission($table, 'delete');

        $query = DB::table($table)->where('id', $id);

        if (Schema::hasColumn($table, 'team_id')) {
            $query->where('team_id', $request->user()->current_team_id);
        }

        if (Schema::hasColumn($table, 'deleted_at')) {
            $query->update(['deleted_at' => now()]);
        } else {
            $query->delete();
        }

        return response()->json(['message' => __('Record deleted successfully')]);
    }

    protected function validateTable($table)
    {
        if (!array_key_exists($table, $this->allowedTables)) {
            // Log the table name for debugging
            \Log::warning('Master data table not allowed: ' . $table);
            abort(404, "Table '{$table}' not allowed or not found in master data registry.");
        }
    }

    protected function checkPermission($table, $action)
    {
        $permission = $this->allowedTables[$table]['permission'];
        // if (!auth()->user()->hasPermissionTo($permission)) {
        //     abort(403, "Unauthorized: Requires $permission");
        // }
    }
}
