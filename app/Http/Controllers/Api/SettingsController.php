<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{HotelAmenity, LedgerNumber, CustomerGroup, ReservationResource, MaintenanceCategory};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SettingsController extends Controller
{
    public function index($category)
    {
        return match($category) {
            'amenities' => response()->json($this->getAmenities()),
            'ledger-numbers' => response()->json($this->getLedgerNumbers()),
            'customer-groups' => response()->json($this->getCustomerGroups()),
            'reservation-resources' => response()->json($this->getReservationResources()),
            'maintenance-categories' => response()->json($this->getMaintenanceCategories()),
            'general' => response()->json($this->getGeneralSettings()),
            'facility' => response()->json($this->getFacilitySettings()),
            'integrations' => response()->json($this->getIntegrationSettings()),
            'users-roles' => response()->json($this->getUsersRolesSettings()),
            'documents' => response()->json($this->getDocumentSettings()),
            'notifications' => response()->json($this->getNotificationSettings()),
            'finance' => response()->json($this->getFinanceSettings()),
            'activity-logs' => response()->json($this->getActivityLogs()),
            'website' => response()->json($this->getWebsiteSettings()),
            'rating' => response()->json($this->getRatingSettings()),
            'included-services' => response()->json($this->getIncludedServices()),
            default => response()->json(['message' => 'Settings category loaded', 'data' => []])
        };
    }

    public function updateGlobal(Request $request)
    {
        return response()->json(['message' => 'Settings updated successfully']);
    }

    // Data generation methods
    private function getAmenities()
    {
        return [
            ['id' => 1, 'name' => 'Free WiFi', 'code' => 'WIFI', 'group' => 'Internet'],
            ['id' => 2, 'name' => 'Swimming Pool', 'code' => 'POOL', 'group' => 'Recreation'],
            ['id' => 3, 'name' => 'Fitness Center', 'code' => 'GYM', 'group' => 'Recreation'],
            ['id' => 4, 'name' => 'Spa & Wellness', 'code' => 'SPA', 'group' => 'Wellness'],
            ['id' => 5, 'name' => 'Restaurant', 'code' => 'REST', 'group' => 'Dining'],
            ['id' => 6, 'name' => 'Room Service', 'code' => 'ROOM_SVC', 'group' => 'Dining'],
            ['id' => 7, 'name' => 'Parking', 'code' => 'PARK', 'group' => 'Transport'],
            ['id' => 8, 'name' => 'Airport Shuttle', 'code' => 'SHUTTLE', 'group' => 'Transport'],
        ];
    }

    private function getLedgerNumbers()
    {
        return [
            ['id' => 1, 'name' => 'Room Revenue', 'code' => '4000', 'group' => 'Revenue'],
            ['id' => 2, 'name' => 'Food & Beverage', 'code' => '4100', 'group' => 'Revenue'],
            ['id' => 3, 'name' => 'Other Revenue', 'code' => '4200', 'group' => 'Revenue'],
            ['id' => 4, 'name' => 'Cash', 'code' => '1000', 'group' => 'Assets'],
            ['id' => 5, 'name' => 'Accounts Receivable', 'code' => '1100', 'group' => 'Assets'],
            ['id' => 6, 'name' => 'Accounts Payable', 'code' => '2000', 'group' => 'Liabilities'],
        ];
    }

    private function getCustomerGroups()
    {
        return [
            ['id' => 1, 'name' => 'VIP Guests', 'code' => 'VIP', 'group' => 'Premium'],
            ['id' => 2, 'name' => 'Corporate', 'code' => 'CORP', 'group' => 'Business'],
            ['id' => 3, 'name' => 'Travel Agents', 'code' => 'TA', 'group' => 'Business'],
            ['id' => 4, 'name' => 'Government', 'code' => 'GOV', 'group' => 'Official'],
            ['id' => 5, 'name' => 'Walk-in', 'code' => 'WALK', 'group' => 'Individual'],
        ];
    }

    private function getReservationResources()
    {
        return [
            ['id' => 1, 'name' => 'Conference Room A', 'code' => 'CONF_A', 'group' => 'Meeting'],
            ['id' => 2, 'name' => 'Conference Room B', 'code' => 'CONF_B', 'group' => 'Meeting'],
            ['id' => 3, 'name' => 'Banquet Hall', 'code' => 'BANQ', 'group' => 'Events'],
            ['id' => 4, 'name' => 'Garden Area', 'code' => 'GARDEN', 'group' => 'Events'],
        ];
    }

    private function getMaintenanceCategories()
    {
        return [
            ['id' => 1, 'name' => 'Electrical', 'code' => 'ELEC', 'group' => 'Technical'],
            ['id' => 2, 'name' => 'Plumbing', 'code' => 'PLUMB', 'group' => 'Technical'],
            ['id' => 3, 'name' => 'HVAC', 'code' => 'HVAC', 'group' => 'Climate'],
            ['id' => 4, 'name' => 'Furniture', 'code' => 'FURN', 'group' => 'Interior'],
            ['id' => 5, 'name' => 'Appliances', 'code' => 'APPL', 'group' => 'Equipment'],
        ];
    }

    private function getGeneralSettings()
    {
        return [
            ['id' => 1, 'name' => 'Hotel Name', 'code' => 'hotel_name', 'group' => 'Basic'],
            ['id' => 2, 'name' => 'Address', 'code' => 'address', 'group' => 'Basic'],
            ['id' => 3, 'name' => 'Phone Number', 'code' => 'phone', 'group' => 'Contact'],
            ['id' => 4, 'name' => 'Email', 'code' => 'email', 'group' => 'Contact'],
            ['id' => 5, 'name' => 'Currency', 'code' => 'currency', 'group' => 'Financial'],
            ['id' => 6, 'name' => 'Tax Rate (%)', 'code' => 'tax_rate', 'group' => 'Financial'],
        ];
    }

    private function getFacilitySettings()
    {
        return [
            ['id' => 1, 'name' => 'Total Rooms', 'code' => 'total_rooms', 'group' => 'Capacity'],
            ['id' => 2, 'name' => 'Floors', 'code' => 'floors', 'group' => 'Capacity'],
            ['id' => 3, 'name' => 'Check-in Time', 'code' => 'checkin_time', 'group' => 'Policy'],
            ['id' => 4, 'name' => 'Checkout Time', 'code' => 'checkout_time', 'group' => 'Policy'],
            ['id' => 5, 'name' => 'Timezone', 'code' => 'timezone', 'group' => 'Regional'],
        ];
    }

    private function getIntegrationSettings()
    {
        return [
            ['id' => 1, 'name' => 'Shomoos API', 'code' => 'shomoos', 'group' => 'Government'],
            ['id' => 2, 'name' => 'ZATCA Integration', 'code' => 'zatca', 'group' => 'Compliance'],
            ['id' => 3, 'name' => 'Payment Gateway', 'code' => 'payment_gw', 'group' => 'Financial'],
            ['id' => 4, 'name' => 'Channel Manager', 'code' => 'channel_mgr', 'group' => 'OTA'],
        ];
    }

    private function getUsersRolesSettings()
    {
        return [
            ['id' => 1, 'name' => 'Admin Role', 'code' => 'admin', 'group' => 'System'],
            ['id' => 2, 'name' => 'Manager Role', 'code' => 'manager', 'group' => 'Management'],
            ['id' => 3, 'name' => 'Receptionist Role', 'code' => 'receptionist', 'group' => 'Front Desk'],
            ['id' => 4, 'name' => 'Housekeeping Role', 'code' => 'housekeeping', 'group' => 'Operations'],
        ];
    }

    private function getDocumentSettings()
    {
        return [
            ['id' => 1, 'name' => 'Invoice Template', 'code' => 'invoice_tpl', 'group' => 'Templates'],
            ['id' => 2, 'name' => 'Receipt Template', 'code' => 'receipt_tpl', 'group' => 'Templates'],
            ['id' => 3, 'name' => 'Registration Card', 'code' => 'reg_card', 'group' => 'Templates'],
            ['id' => 4, 'name' => 'Contract Template', 'code' => 'contract_tpl', 'group' => 'Templates'],
        ];
    }

    private function getNotificationSettings()
    {
        return [
            ['id' => 1, 'name' => 'Reservation Confirmation', 'code' => 'res_confirm', 'group' => 'Email'],
            ['id' => 2, 'name' => 'Check-in Reminder', 'code' => 'checkin_rem', 'group' => 'Email'],
            ['id' => 3, 'name' => 'Checkout Reminder', 'code' => 'checkout_rem', 'group' => 'SMS'],
            ['id' => 4, 'name' => 'Payment Receipt', 'code' => 'payment_rcpt', 'group' => 'Email'],
        ];
    }

    private function getFinanceSettings()
    {
        return [
            ['id' => 1, 'name' => 'Default Payment Method', 'code' => 'def_payment', 'group' => 'Payment'],
            ['id' => 2, 'name' => 'Credit Limit', 'code' => 'credit_limit', 'group' => 'AR'],
            ['id' => 3, 'name' => 'Late Fee (%)', 'code' => 'late_fee', 'group' => 'Charges'],
            ['id' => 4, 'name' => 'Tax Settings', 'code' => 'tax_settings', 'group' => 'Compliance'],
        ];
    }

    private function getActivityLogs()
    {
        return [
            ['id' => 1, 'name' => 'Login Attempts', 'code' => 'login_logs', 'group' => 'Security'],
            ['id' => 2, 'name' => 'Reservation Changes', 'code' => 'res_changes', 'group' => 'Operations'],
            ['id' => 3, 'name' => 'Financial Transactions', 'code' => 'fin_trans', 'group' => 'Finance'],
            ['id' => 4, 'name' => 'System Changes', 'code' => 'sys_changes', 'group' => 'System'],
        ];
    }

    private function getWebsiteSettings()
    {
        return [
            ['id' => 1, 'name' => 'Site Title', 'code' => 'site_title', 'group' => 'General'],
            ['id' => 2, 'name' => 'SEO Keywords', 'code' => 'seo_keywords', 'group' => 'SEO'],
            ['id' => 3, 'name' => 'Social Media Links', 'code' => 'social_links', 'group' => 'Social'],
            ['id' => 4, 'name' => 'Booking Engine', 'code' => 'booking_engine', 'group' => 'Booking'],
        ];
    }

    private function getRatingSettings()
    {
        return [
            ['id' => 1, 'name' => 'Auto-request Reviews', 'code' => 'auto_review', 'group' => 'Automation'],
            ['id' => 2, 'name' => 'Review Platforms', 'code' => 'review_plat', 'group' => 'Platforms'],
            ['id' => 3, 'name' => 'Rating Display', 'code' => 'rating_display', 'group' => 'Display'],
        ];
    }

    private function getIncludedServices()
    {
        return [
            ['id' => 1, 'name' => 'Breakfast', 'code' => 'breakfast', 'group' => 'Dining'],
            ['id' => 2, 'name' => 'Airport Transfer', 'code' => 'airport', 'group' => 'Transport'],
            ['id' => 3, 'name' => 'Welcome Drink', 'code' => 'welcome_drink', 'group' => 'Amenities'],
            ['id' => 4, 'name' => 'Late Checkout', 'code' => 'late_checkout', 'group' => 'Services'],
        ];
    }
}
