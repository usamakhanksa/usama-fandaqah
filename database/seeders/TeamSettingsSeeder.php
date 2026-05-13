<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Team;
use App\Setting;

class TeamSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Get the demo team
        $team = Team::where('slug', 'demo-hotel')->first();
        
        if (!$team) {
            $this->command->error('Demo team not found. Please run TeamSeeder first.');
            return;
        }

        // Define hotel settings
        $settings = [
            // Hotel information
            [
                'key' => 'hotel_name',
                'value' => json_encode(['en' => 'Fandaqah Demo Hotel', 'ar' => 'فندق ديمو فنداق']),
                'team_id' => $team->id,
            ],
            [
                'key' => 'hotel_address',
                'value' => json_encode(['en' => 'King Abdulaziz Road, Al Olaya, Riyadh', 'ar' => 'طريق الملك عبد العزيز، العليا، الرياض']),
                'team_id' => $team->id,
            ],
            [
                'key' => 'hotel_email',
                'value' => 'info@demo.hotel',
                'team_id' => $team->id,
            ],
            [
                'key' => 'hotel_phone_number',
                'value' => '+966111234567',
                'team_id' => $team->id,
            ],
            [
                'key' => 'tax_number',
                'value' => '1234567890',
                'team_id' => $team->id,
            ],
            
            // VAT settings
            [
                'key' => 'tax',
                'value' => '15',
                'team_id' => $team->id,
            ],
            [
                'key' => 'tourism_tax',
                'value' => '5',
                'team_id' => $team->id,
            ],
            [
                'key' => 'accommodation_tax',
                'value' => '10',
                'team_id' => $team->id,
            ],
            
            // Location settings for ZATCA
            [
                'key' => 'city',
                'value' => 'Riyadh',
                'team_id' => $team->id,
            ],
            [
                'key' => 'city_ar',
                'value' => 'الرياض',
                'team_id' => $team->id,
            ],
            [
                'key' => 'district',
                'value' => 'Al Olaya',
                'team_id' => $team->id,
            ],
            [
                'key' => 'district_ar',
                'value' => 'ال العليا',
                'team_id' => $team->id,
            ],
            [
                'key' => 'street',
                'value' => 'King Abdulaziz Road',
                'team_id' => $team->id,
            ],
            [
                'key' => 'street_ar',
                'value' => 'طريق الملك عبد العزيز',
                'team_id' => $team->id,
            ],
            [
                'key' => 'building_number',
                'value' => '1234',
                'team_id' => $team->id,
            ],
            [
                'key' => 'postal_code',
                'value' => '12345',
                'team_id' => $team->id,
            ],
            
            // Business day settings
            [
                'key' => 'day_start',
                'value' => '06:00',
                'team_id' => $team->id,
            ],
            [
                'key' => 'day_end',
                'value' => '06:00',
                'team_id' => $team->id,
            ],
            
            // Reservation settings
            [
                'key' => 'reservation_default_status',
                'value' => 'confirmed',
                'team_id' => $team->id,
            ],
            [
                'key' => 'automatic_renewal_of_reservations',
                'value' => '0',
                'team_id' => $team->id,
            ],
            
            // Breakfast prices
            [
                'key' => 'breakfast_price',
                'value' => '50',
                'team_id' => $team->id,
            ],
            [
                'key' => 'breakfast_price_kids',
                'value' => '25',
                'team_id' => $team->id,
            ],
            
            // No-show rules
            [
                'key' => 'noshow_auto_charge',
                'value' => '1',
                'team_id' => $team->id,
            ],
            [
                'key' => 'noshow_charge_full_amount',
                'value' => '1',
                'team_id' => $team->id,
            ],
            
            // Early/Late Charge Rules
            [
                'key' => 'early_checkin_charges',
                'value' => '1',
                'team_id' => $team->id,
            ],
            [
                'key' => 'late_checkout_charges',
                'value' => '1',
                'team_id' => $team->id,
            ],
            [
                'key' => 'early_checkin_charge_amount',
                'value' => '50',
                'team_id' => $team->id,
            ],
            [
                'key' => 'late_checkout_charge_amount',
                'value' => '50',
                'team_id' => $team->id,
            ],
            
            // Payment settings
            [
                'key' => 'default_payment_method',
                'value' => 'cash',
                'team_id' => $team->id,
            ],
            [
                'key' => 'allow_partial_payments',
                'value' => '1',
                'team_id' => $team->id,
            ],
            
            // Notification settings
            [
                'key' => 'send_confirmation_emails',
                'value' => '1',
                'team_id' => $team->id,
            ],
            [
                'key' => 'send_reminder_emails',
                'value' => '1',
                'team_id' => $team->id,
            ],
            
            // Feature announcements
            [
                'key' => 'enable_feature_announcements',
                'value' => '1',
                'team_id' => $team->id,
            ],
            
            // ZATCA settings
            [
                'key' => 'zatca_production_mode',
                'value' => '0', // Sandbox mode for demo
                'team_id' => $team->id,
            ],
            
            // Calculate price by day setting
            [
                'key' => 'calculate_price_by_day',
                'value' => '1',
                'team_id' => $team->id,
            ],
        ];

        foreach ($settings as $setting) {
            // Only encode as JSON if it's not already encoded
            $valueToStore = $setting['value'];
            if (is_string($valueToStore) && !json_decode($valueToStore, true)) {
                // If it's a simple string, store it as-is
                $finalValue = $valueToStore;
            } else {
                // If it's already JSON or an array, encode it
                $finalValue = $valueToStore;
            }

            \DB::table('settings')->updateOrInsert(
                [
                    'key' => $setting['key'],
                    'team_id' => $setting['team_id'],
                ],
                [
                    'key' => $setting['key'],
                    'team_id' => $setting['team_id'],
                    'value' => $finalValue,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}