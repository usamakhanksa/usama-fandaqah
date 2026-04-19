<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\{HotelAmenity, LedgerNumber, CustomerGroup, ReservationResource, MaintenanceCategory};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SettingsController extends Controller {
    public function index($category) {
        return match($category) {
            'amenities' => response()->json(HotelAmenity::all()),
            'ledger-numbers' => response()->json(LedgerNumber::all()),
            'customer-groups' => response()->json(CustomerGroup::all()),
            'reservation-resources' => response()->json(ReservationResource::all()),
            'maintenance-categories' => response()->json(MaintenanceCategory::all()),
            default => response()->json(['message' => 'Settings category loaded', 'data' => []])
        };
    }
}
