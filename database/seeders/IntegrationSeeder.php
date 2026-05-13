<?php

namespace Database\Seeders;

use App\Models\Integration;
use App\Models\IntegrationLog;
use App\Models\IntegrationSetting;
use App\Models\FormIntegration;
use App\Models\ApiConsumer;
use App\Models\ApiToken;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class IntegrationSeeder extends Seeder
{
    public function run()
    {
        $teams = Team::all();

        foreach ($teams as $team) {
            // Create integrations for each team
            $integrations = [
                [
                    'name' => 'ZATCA - Fatoora',
                    'name_ar' => 'زاتكا - فاتورة',
                    'slug' => 'zatca-fatoora',
                    'integration_type' => 'government',
                    'provider' => 'zatca',
                    'status' => 'active',
                    'is_active' => true,
                    'sync_frequency' => 'real_time',
                    'notes' => 'Saudi e-invoicing Phase 2 compliance',
                ],
                [
                    'name' => 'Qoyod ERP',
                    'name_ar' => 'قيود إدارة العمليات',
                    'slug' => 'qoyod-erp',
                    'integration_type' => 'accounting',
                    'provider' => 'qoyod',
                    'status' => 'active',
                    'is_active' => true,
                    'sync_frequency' => 'daily',
                    'notes' => 'Cloud accounting software integration',
                ],
                [
                    'name' => 'Shomoos Police Reporting',
                    'name_ar' => 'شموس إبلاغ الشرطة',
                    'slug' => 'shomoos-police',
                    'integration_type' => 'government',
                    'provider' => 'shomoos',
                    'status' => 'active',
                    'is_active' => true,
                    'sync_frequency' => 'real_time',
                    'notes' => 'Guest registration with Saudi police',
                ],
                [
                    'name' => 'Stripe Payments',
                    'name_ar' => 'مدفوعات ستريب',
                    'slug' => 'stripe-payments',
                    'integration_type' => 'payment_gateway',
                    'provider' => 'stripe',
                    'status' => 'testing',
                    'is_active' => false,
                    'sync_frequency' => 'real_time',
                    'notes' => 'Online payment processing',
                ],
                [
                    'name' => 'Tabby - Buy Now Pay Later',
                    'name_ar' => 'تابي - اشتر الآن ادفع لاحقا',
                    'slug' => 'tabby-bnpl',
                    'integration_type' => 'payment_gateway',
                    'provider' => 'tabby',
                    'status' => 'pending_setup',
                    'is_active' => false,
                    'sync_frequency' => 'real_time',
                    'notes' => 'Saudi BNPL payment provider',
                ],
                [
                    'name' => 'Tamara - Buy Now Pay Later',
                    'name_ar' => 'تمارة - اشتر الآن ادفع لاحقا',
                    'slug' => 'tamara-bnpl',
                    'integration_type' => 'payment_gateway',
                    'provider' => 'tamara',
                    'status' => 'pending_setup',
                    'is_active' => false,
                    'sync_frequency' => 'real_time',
                    'notes' => 'Saudi BNPL payment provider',
                ],
                [
                    'name' => 'Site Minder Channel Manager',
                    'name_ar' => 'مدير القناة سايت مايندر',
                    'slug' => 'siteminder-channel',
                    'integration_type' => 'channel_manager',
                    'provider' => 'site_minder',
                    'status' => 'active',
                    'is_active' => true,
                    'sync_frequency' => 'daily',
                    'notes' => 'OTA inventory and rate sync',
                ],
            ];

            $admin = $team->users()->where('role', 'admin')->first() ?? $team->users()->first();

            foreach ($integrations as $integrationData) {
                $integrationData['team_id'] = $team->id;
                $integrationData['created_by'] = $admin->id;

                $integration = Integration::firstOrCreate(
                    ['team_id' => $team->id, 'slug' => $integrationData['slug']],
                    $integrationData
                );

                // Add integration settings
                $this->createIntegrationSettings($integration, $admin);

                // Add integration logs
                $this->createIntegrationLogs($integration, $admin);
            }

            // Create form integrations
            $this->createFormIntegrations($team, $admin);

            // Create API consumers
            $this->createApiConsumers($team, $admin);

            // Create API tokens
            $this->createApiTokens($team, $admin);
        }
    }

    private function createIntegrationSettings(Integration $integration, User $user)
    {
        $settingsData = [
            'zatca-fatoora' => [
                ['setting_key' => 'api_url', 'setting_value' => 'https://sandbox.zatca.gov.sa', 'setting_type' => 'text', 'is_required' => true],
                ['setting_key' => 'api_key', 'setting_value' => null, 'setting_type' => 'encrypted', 'is_required' => true],
                ['setting_key' => 'csid', 'setting_value' => null, 'setting_type' => 'encrypted', 'is_required' => true],
                ['setting_key' => 'secret', 'setting_value' => null, 'setting_type' => 'encrypted', 'is_required' => true],
                ['setting_key' => 'compliance_mode', 'setting_value' => 'phase_two', 'setting_type' => 'select', 'is_required' => false],
            ],
            'qoyod-erp' => [
                ['setting_key' => 'api_base_url', 'setting_value' => 'https://api.qoyod.com', 'setting_type' => 'url', 'is_required' => true],
                ['setting_key' => 'api_key', 'setting_value' => null, 'setting_type' => 'encrypted', 'is_required' => true],
                ['setting_key' => 'auto_sync_invoices', 'setting_value' => 'true', 'setting_type' => 'boolean', 'is_required' => false],
            ],
            'shomoos-police' => [
                ['setting_key' => 'api_endpoint', 'setting_value' => 'https://shomoos.gov.sa/api', 'setting_type' => 'text', 'is_required' => true],
                ['setting_key' => 'auth_token', 'setting_value' => null, 'setting_type' => 'encrypted', 'is_required' => true],
            ],
        ];

        $key = Str::slug($integration->name);
        $settings = $settingsData[$key] ?? [];

        foreach ($settings as $setting) {
            IntegrationSetting::updateOrCreate(
                [
                    'integration_id' => $integration->id,
                    'setting_key' => $setting['setting_key'],
                ],
                [
                    'team_id' => $integration->team_id,
                    'setting_value' => $setting['setting_value'],
                    'setting_type' => $setting['setting_type'],
                    'is_required' => $setting['is_required'],
                ]
            );
        }
    }

    private function createIntegrationLogs(Integration $integration, User $user)
    {
        $actions = ['push_guest', 'pull_reservation', 'sync_inventory', 'webhook_received', 'data_validated'];
        $logTypes = ['success', 'info', 'warning'];

        for ($i = 0; $i < 5; $i++) {
            IntegrationLog::create([
                'team_id' => $integration->team_id,
                'integration_id' => $integration->id,
                'log_type' => $logTypes[array_rand($logTypes)],
                'action' => $actions[array_rand($actions)],
                'direction' => rand(0, 1) ? 'inbound' : 'outbound',
                'status_code' => [200, 201, 400, 422][array_rand([200, 201, 400, 422])],
                'execution_time_ms' => rand(50, 2000),
                'performed_by' => $user->id,
                'request_payload' => ['sample' => 'payload'],
                'response_payload' => ['result' => 'success'],
                'created_at' => now()->subMinutes(rand(1, 60)),
            ]);
        }
    }

    private function createFormIntegrations(Team $team, User $user)
    {
        $integration = Integration::where('team_id', $team->id)->where('slug', 'zatca-fatoora')->first();

        if ($integration) {
            FormIntegration::firstOrCreate(
                ['team_id' => $team->id, 'integration_id' => $integration->id],
                [
                    'form_name' => 'Online Booking Form',
                    'form_url' => route('reservations.create'),
                    'field_mapping' => [
                        'guest_name' => 'guest.name',
                        'guest_email' => 'guest.email',
                        'check_in_date' => 'reservation.check_in_date',
                        'check_out_date' => 'reservation.check_out_date',
                    ],
                    'auto_approve' => true,
                    'status' => 'active',
                    'created_by' => $user->id,
                ]
            );
        }
    }

    private function createApiConsumers(Team $team, User $user)
    {
        $consumers = [
            [
                'name' => 'OTA Integration Client',
                'description' => 'Third-party OTA integration',
                'rate_limit_per_minute' => 120,
            ],
            [
                'name' => 'Mobile App Backend',
                'description' => 'Hotel mobile application',
                'rate_limit_per_minute' => 300,
            ],
            [
                'name' => 'Analytics Dashboard',
                'description' => 'External analytics provider',
                'rate_limit_per_minute' => 60,
            ],
        ];

        foreach ($consumers as $consumerData) {
            ApiConsumer::firstOrCreate(
                ['team_id' => $team->id, 'name' => $consumerData['name']],
                [
                    'description' => $consumerData['description'],
                    'rate_limit_per_minute' => $consumerData['rate_limit_per_minute'],
                    'allowed_ips' => ['127.0.0.1', '192.168.1.0/24'],
                    'is_active' => true,
                    'created_by' => $user->id,
                ]
            );
        }
    }

    private function createApiTokens(Team $team, User $user)
    {
        $consumers = ApiConsumer::where('team_id', $team->id)->get();

        foreach ($consumers as $consumer) {
            for ($i = 0; $i < 2; $i++) {
                ApiToken::create([
                    'team_id' => $team->id,
                    'api_consumer_id' => $consumer->id,
                    'name' => "{$consumer->name} Token {$i + 1}",
                    'token' => Str::random(64),
                    'abilities' => ['read', 'write'],
                    'expires_at' => now()->addYear(),
                    'is_active' => true,
                    'created_by' => $user->id,
                ]);
            }
        }
    }
}