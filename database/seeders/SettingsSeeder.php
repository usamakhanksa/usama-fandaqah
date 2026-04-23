<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Models\PmsDictionary;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder {
    public function run(): void {
        // General Settings
        Setting::updateOrCreate(['key' => 'general_info'], [
            'group' => 'general',
            'payload' => [
                'hotel_name' => 'Fandaqah Grand Riyadh',
                'hotel_email' => 'info@fandaqah.sa',
                'phone' => '+966 11 123 4567',
                'address' => 'King Fahd Rd, Riyadh, Saudi Arabia',
                'timezone' => 'Asia/Riyadh',
                'currency' => 'SAR',
                'vat_number' => '300012345600003'
            ]
        ]);

        // Finance Settings
        Setting::updateOrCreate(['key' => 'finance_config'], [
            'group' => 'finance',
            'payload' => [
                'vat_percentage' => 15,
                'city_tax' => 5,
                'default_payment_method' => 'Credit Card',
                'invoice_prefix' => 'FAN-',
                'auto_night_audit' => true
            ]
        ]);

        // Website Settings
        Setting::updateOrCreate(['key' => 'website_config'], [
            'group' => 'website',
            'payload' => [
                'primary_color' => '#e95a54',
                'allow_online_booking' => true,
                'meta_title' => 'Fandaqah - Premium Hotel Management',
                'meta_description' => 'The most advanced PMS in Saudi Arabia.'
            ]
        ]);

        // Dictionaries
        $amenities = ['Free WiFi', 'Swimming Pool', 'Gym', 'Spa', 'Restaurant', 'Valet Parking', 'Business Center'];
        foreach ($amenities as $item) {
            PmsDictionary::updateOrCreate(['group' => 'amenities', 'label' => $item], ['is_active' => true]);
        }

        $customerGroups = ['VIP', 'Corporate', 'Government', 'Walk-in', 'OTA'];
        foreach ($customerGroups as $item) {
            PmsDictionary::updateOrCreate(['group' => 'customer_groups', 'label' => $item], ['is_active' => true]);
        }

        $ledgerNumbers = ['1001-Cash', '1002-Bank', '2001-Accounts Payable', '3001-Room Revenue', '3002-F&B Revenue'];
        foreach ($ledgerNumbers as $item) {
            PmsDictionary::updateOrCreate(['group' => 'ledger_numbers', 'label' => $item], ['is_active' => true]);
        }
        
        $cleaningTypes = ['Check-out cleaning', 'Stay-over cleaning', 'Deep Clean', 'Touch up'];
        foreach ($cleaningTypes as $item) {
            PmsDictionary::updateOrCreate(['group' => 'maintenance_types', 'label' => $item], ['is_active' => true]);
        }
    }
}
