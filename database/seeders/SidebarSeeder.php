<?php

namespace Database\Seeders;

use App\Models\SidebarItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SidebarSeeder extends Seeder
{
    public function run()
    {
        DB::table('sidebar_items')->truncate();

        $menu = [
            'dashboard' => [
                'label' => 'Dashboard',
                'icon'  => 'LayoutDashboardIcon',
                'children' => [
                    ['label' => 'Main Dashboard',       'url' => '/dashboard',                    'permission' => 'dashboard.view'],
                    ['label' => 'Occupancy Dashboard',  'url' => '/dashboard/occupancy',          'permission' => 'dashboard.view'],
                    ['label' => 'Revenue Dashboard',    'url' => '/dashboard/revenue',            'permission' => 'dashboard.view'],
                    ['label' => 'Front Desk Dashboard', 'url' => '/dashboard/front-desk',         'permission' => 'dashboard.view'],
                    ['label' => 'Finance Dashboard',    'url' => '/dashboard/finance',            'permission' => 'dashboard.view'],
                    ['label' => 'Night Audit Status',   'url' => '/night-audit',                  'permission' => 'night_audit.view'],
                ],
            ],
            'reservations' => [
                'label' => 'Reservations',
                'icon'  => 'CalendarIcon',
                'children' => [
                    ['label' => 'All Reservations',     'url' => '/reservations/management',      'permission' => 'reservations.view'],
                    ['label' => 'Quick Book',           'url' => '/reservations/quick-create',    'permission' => 'reservations.create'],
                    ['label' => 'New Reservation',      'url' => '/new-reservation',              'permission' => 'reservations.create'],
                    ['label' => 'Calendar',             'url' => '/reservations/calendar',        'permission' => 'reservations.view'],
                    ['label' => 'Arrivals Today',       'url' => '/reservations/arrivals',        'permission' => 'reservations.view'],
                    ['label' => 'Departures Today',     'url' => '/reservations/departures',      'permission' => 'reservations.view'],
                    ['label' => 'In-House Guests',      'url' => '/reservations/in-house',        'permission' => 'reservations.view'],
                    ['label' => 'Online Reservations',  'url' => '/reservations/online',          'permission' => 'reservations.view'],
                    ['label' => 'OTA Reservations',     'url' => '/reservations/ota',             'permission' => 'reservations.view'],
                    ['label' => 'Group Reservations',   'url' => '/reservations/groups',          'permission' => 'reservations.view'],
                    ['label' => 'Room Transfers',       'url' => '/reservations/transfers',       'permission' => 'reservations.view'],
                    ['label' => 'Stay Extensions',      'url' => '/reservations/extensions',      'permission' => 'reservations.view'],
                    ['label' => 'Digital Contracts',    'url' => '/reservations/contracts',       'permission' => 'reservations.view'],
                    ['label' => 'Digital Signatures',   'url' => '/reservations/signatures',      'permission' => 'reservations.view'],
                    ['label' => 'Cancellations',        'url' => '/reservations/cancellations',   'permission' => 'reservations.view'],
                    ['label' => 'Messages',             'url' => '/reservations/messages',        'permission' => 'reservations.view'],
                    ['label' => 'Audit Locks',          'url' => '/reservations/audit-locks',     'permission' => 'reservations.view'],
                ],
            ],
            'front_desk' => [
                'label' => 'Front Desk',
                'icon'  => 'ConciergeBellIcon',
                'children' => [
                    ['label' => 'Check-In',             'url' => '/front-desk/check-in',          'permission' => 'reservations.checkin'],
                    ['label' => 'Check-Out',            'url' => '/front-desk/check-out',         'permission' => 'reservations.checkout'],
                    ['label' => 'Walk-In Booking',      'url' => '/front-desk/walk-in',           'permission' => 'reservations.create'],
                    ['label' => 'Guest Registration',   'url' => '/front-desk/registration',      'permission' => 'reservations.update'],
                    ['label' => 'Room Assignment',      'url' => '/front-desk/room-assignment',   'permission' => 'reservations.update'],
                    ['label' => 'Room Swap',            'url' => '/front-desk/room-swap',         'permission' => 'reservations.transfer'],
                    ['label' => 'Early Check-In',       'url' => '/front-desk/early-check-in',    'permission' => 'checkin.override_early_charge'],
                    ['label' => 'Late Checkout',        'url' => '/front-desk/late-checkout',     'permission' => 'checkout.override_late_charge'],
                    ['label' => 'No-Show Handling',     'url' => '/front-desk/no-show',           'permission' => 'reservations.noshow'],
                    ['label' => 'Wake-Up Calls',        'url' => '/front-desk/wake-up-calls',     'permission' => 'front-desk.wake-up-calls'],
                    ['label' => 'IPTV Guest Needs',     'url' => '/front-desk/iptv-needs',        'permission' => 'front-desk.iptv'],
                    ['label' => 'Registration Cards',   'url' => '/front-desk/registration-cards','permission' => 'reservations.print'],
                    ['label' => 'Balance Transfer',     'url' => '/front-desk/balance-transfer',  'permission' => 'finance.balance_transfer'],
                ],
            ],
            'housekeeping' => [
                'label' => 'Rooms & Housekeeping',
                'icon'  => 'BedDoubleIcon',
                'children' => [
                    ['label' => 'Units / Rooms',        'url' => '/units',                        'permission' => 'units.view'],
                    ['label' => 'Unit Categories',      'url' => '/unit-categories',              'permission' => 'units.view'],
                    ['label' => 'Availability Board',   'url' => '/units/availability',           'permission' => 'units.view'],
                    ['label' => 'Status Board',         'url' => '/units/status-board',           'permission' => 'units.view'],
                    ['label' => 'Room Types',           'url' => '/room-types',                   'permission' => 'manage_room_options'],
                    ['label' => 'Room Floors',          'url' => '/room-floors',                  'permission' => 'manage_room_options'],
                    ['label' => 'Housekeeping Board',   'url' => '/housekeeping/board',           'permission' => 'housekeeping.view'],
                    ['label' => 'Unit Cleanings',       'url' => '/unit-cleanings',               'permission' => 'housekeeping.view'],
                    ['label' => 'Maintenance Requests', 'url' => '/unit-maintenances',            'permission' => 'maintenance.view'],
                    ['label' => 'Room Status Log',      'url' => '/room-status-log',              'permission' => 'units.view'],
                    ['label' => 'Unit Features',        'url' => '/unit-features',                'permission' => 'settings.view'],
                    ['label' => 'Unit Options',         'url' => '/unit-options',                 'permission' => 'settings.view'],
                    ['label' => 'Category Services',    'url' => '/unit-category-services',       'permission' => 'settings.view'],
                ],
            ],
            'serviced_apartments' => [
                'label' => 'Serviced Apartments',
                'icon'  => 'BuildingIcon',
                'children' => [
                    ['label' => 'Buildings',            'url' => '/long-stay/buildings',          'permission' => 'units.view'],
                    ['label' => 'Lease Agreements',     'url' => '/long-stay/contracts',          'permission' => 'contracts.view'],
                    ['label' => 'Utility Meters',       'url' => '/long-stay/meters',             'permission' => 'finance.view'],
                    ['label' => 'Tenant Profiles',      'url' => '/long-stay/tenants',            'permission' => 'customers.view'],
                    ['label' => 'Unit Inventory',       'url' => '/long-stay/inventory',          'permission' => 'units.view'],
                ],
            ],
            'guests' => [
                'label' => 'Guests & Companies',
                'icon'  => 'UsersIcon',
                'children' => [
                    ['label' => 'Guest Directory',      'url' => '/guests',                       'permission' => 'guests.view'],
                    ['label' => 'Customer Profiles',    'url' => '/customers',                    'permission' => 'customers.view'],
                    ['label' => 'Companies',            'url' => '/companies',                    'permission' => 'companies.view'],
                    ['label' => 'Company Groups',       'url' => '/company-groups',               'permission' => 'ar.company_groups.view'],
                    ['label' => 'Blocked Guests',       'url' => '/blocked-guests',               'permission' => 'guests.block'],
                    ['label' => 'Turnaway Logs',        'url' => '/turnaway-logs',                'permission' => 'guests.view'],
                    ['label' => 'Turnaway Reasons',     'url' => '/turnaway-reasons',             'permission' => 'settings.view'],
                    ['label' => 'Highlights',           'url' => '/highlights',                   'permission' => 'settings.view'],
                    ['label' => 'Merge Duplicates',     'url' => '/customers/merge',              'permission' => 'customers.merge'],
                ],
            ],
            'marketing' => [
                'label' => 'Marketing & Sales',
                'icon'  => 'LocalOfferIcon',
                'children' => [
                    ['label' => 'Offers',               'url' => '/marketing/offers',             'permission' => 'marketing.offers.view'],
                    ['label' => 'Special Prices',       'url' => '/marketing/special-prices',     'permission' => 'marketing.special_prices.view'],
                    ['label' => 'Promo Codes',          'url' => '/marketing/promo-codes',        'permission' => 'marketing.promo_codes.view'],
                    ['label' => 'Vouchers',             'url' => '/marketing/vouchers',           'permission' => 'marketing.vouchers.view'],
                    ['label' => 'Pricing Preview',      'url' => '/marketing/pricing-preview',    'permission' => 'marketing.pricing_preview.view'],
                    ['label' => 'Booking Sources',      'url' => '/marketing/sources',            'permission' => 'marketing.sources.view'],
                ],
            ],
            'pos' => [
                'label' => 'POS & Services',
                'icon'  => 'ShoppingCartIcon',
                'children' => [
                    ['label' => 'POS Dashboard',        'url' => '/pos/dashboard',                'permission' => 'pos.view'],
                    ['label' => 'New Sale',             'url' => '/pos/sale',                     'permission' => 'pos.create'],
                    ['label' => 'Service Categories',   'url' => '/pos/service-categories',       'permission' => 'service-categories.view'],
                    ['label' => 'Services',             'url' => '/pos/services-manage',          'permission' => 'services.view'],
                    ['label' => 'Service Logs',         'url' => '/pos/service-logs',             'permission' => 'service-logs.view'],
                    ['label' => 'Quick Payments',       'url' => '/pos/quick-payments',           'permission' => 'pos.create'],
                    ['label' => 'POS Transactions',     'url' => '/pos/pos-transactions',         'permission' => 'transactions.view'],
                    ['label' => 'Qoyod Mapping',        'url' => '/pos/service-qoyods',           'permission' => 'settings.view'],
                    ['label' => 'POS Store',            'url' => '/pos/store',                    'permission' => 'pos.view'],
                ],
            ],


            'finance' => [
                'label' => 'Finance & Accounting',
                'icon'  => 'BanknoteIcon',
                'children' => [
                    ['label' => 'Receipts',             'url' => '/finance/receipts',               'permission' => 'receipts.view'],
                    ['label' => 'Payments',             'url' => '/finance/payments',               'permission' => 'payments.view'],
                    ['label' => 'Invoices',             'url' => '/finance/invoices',               'permission' => 'invoices.view'],
                    ['label' => 'Expenses',             'url' => '/financial/expenses',           'permission' => 'transactions.view'],
                    ['label' => 'Bills',                'url' => '/financial/bills',              'permission' => 'transactions.view'],
                    ['label' => 'Banks',                'url' => '/finance/banks',                'permission' => 'banks.view'],
                    ['label' => 'Payment Senders',      'url' => '/finance/senders',              'permission' => 'senders.view'],
                    ['label' => 'Commission Payments',  'url' => '/finance/commission-payments',   'permission' => 'commission_payments.view'],
                    ['label' => 'Credit Notes',         'url' => '/finance/credit-notes',         'permission' => 'credit_notes.view', 'icon' => 'RemoveCircleIcon'],
                    ['label' => 'Payment Correction',   'url' => '/finance/payment-correction',   'permission' => 'transactions.view'],
                    ['label' => 'Cashier Shifts',       'url' => '/finance/cashier-shifts',       'permission' => 'cashier_shifts.view'],
                    ['label' => 'Room Adjustments',     'url' => '/operations/room-adjustments',  'permission' => 'transactions.view'],
                    ['label' => 'Travel Agents',        'url' => '/finance/travel-agents',        'permission' => 'transactions.view'],
                ],
            ],
            'ar' => [
                'label' => 'Accounts Receivable',
                'icon'  => 'ReceiptIcon',
                'children' => [
                    ['label' => 'Invoice Transfers',    'url' => '/finance/invoice-transfers',         'permission' => 'invoice_transfers.view', 'icon' => 'ArrowRightLeftIcon'],
                    ['label' => 'Promissory Notes',     'url' => '/finance/promissory-notes',          'permission' => 'promissory_notes.view', 'icon' => 'FileTextIcon'],
                    ['label' => 'Note Collections',      'url' => '/finance/promissory-collections',    'permission' => 'promissory_collections.view', 'icon' => 'BanknoteIcon'],
                    ['label' => 'Company Groups',       'url' => '/ar/company-groups',            'permission' => 'ar.company_groups.view'],
                    ['label' => 'City Ledger',          'url' => '/ar/city-ledger',               'permission' => 'transactions.view'],
                ],
            ],
            'night_audit' => [
                'label' => 'Night Audit',
                'icon'  => 'MoonIcon',
                'children' => [
                    ['label' => 'Night Audit Dashboard','url' => '/night-audit',                  'permission' => 'night_audit.view'],
                    ['label' => 'Night Audit Control',  'url' => '/operations/night-audit',       'permission' => 'night_audit.view'],
                    ['label' => 'No-Show Rules',        'url' => '/operations/no-show-rules',     'permission' => 'night_audit.view'],
                ],
            ],
            'reports' => [
                'label' => 'Reports & Analytics',
                'icon'  => 'BarChart3Icon',
                'children' => [
                    ['label' => 'Reports Dashboard',               'url' => '/reports',                      'permission' => 'reports.view'],
                    ['label' => 'Daily Report',                    'url' => '/reports/daily',                'permission' => 'reports.daily'],
                    ['label' => 'Occupancy Report',                'url' => '/reports/occupancy',            'permission' => 'reports.occupancy'],
                    ['label' => 'Revenue Report',                  'url' => '/reports/revenue',              'permission' => 'reports.revenue'],
                    ['label' => 'Forecast Report',                 'url' => '/reports/forecast-history',     'permission' => 'reports.forecast'],
                    ['label' => 'No-Show Report',                  'url' => '/reports/no-show',              'permission' => 'reports.noshow'],
                    ['label' => 'Cancellation Report',             'url' => '/reports/cancellation',         'permission' => 'reports.cancellation'],
                    ['label' => 'Commission Report',               'url' => '/reports/commission',           'permission' => 'reports.commission'],
                    ['label' => 'Paid-Outs Report',                'url' => '/reports/paid-outs',            'permission' => 'reports.paidouts'],
                    ['label' => 'Turnaway Report',                 'url' => '/reports/turnaway',             'permission' => 'reports.turnaway'],
                    ['label' => 'Source Performance',             'url' => '/reports/source-performance',   'permission' => 'reports.source_performance'],
                    ['label' => 'Company AR',                     'url' => '/reports/company-ar',           'permission' => 'reports.company_ar'],
                    ['label' => 'Trial Balance',                  'url' => '/reports/trial-balance',        'permission' => 'reports.trial_balance'],
                    ['label' => 'HK Discrepancy',                 'url' => '/reports/housekeeping-discrepancy','permission' => 'reports.housekeeping_discrepancy'],
                    ['label' => 'ADR & RevPAR',                   'url' => '/reports/adr-revpar',           'permission' => 'reports.adr'],
                    ['label' => 'Custom Reports',                 'url' => '/reports/custom-reports',       'permission' => 'reports.custom_create'],
                    ['label' => 'Scheduled Reports',              'url' => '/reports/report-schedules',     'permission' => 'reports.schedule_create'],
                ],
            ],
            'channel_manager' => [
                'label' => 'Channel Manager',
                'icon'  => 'PlugZapIcon',
                'children' => [
                    ['label' => 'Channel Manager',      'url' => '/channel-manager',              'permission' => 'settings.view'],
                    ['label' => 'Availability & Rates', 'url' => '/channel-manager/availability-rates', 'permission' => 'settings.view'],
                    ['label' => 'Channel Reservations', 'url' => '/channel-manager/reservations', 'permission' => 'reservations.view'],
                ],
            ],
            'settings' => [
                'label' => 'Settings',
                'icon'  => 'SettingsIcon',
                'children' => [
                    ['label' => 'Settings',             'url' => '/settings',                     'permission' => 'settings.view'],
                    ['label' => 'User Groups',          'url' => '/user-groups',                  'permission' => 'settings.view'],
                    ['label' => 'Leads',                'url' => '/leads',                        'permission' => 'settings.view'],
                ],
            ],
        ];

        $order = 10;
        foreach ($menu as $modKey => $module) {
            $parentKey = "mod_{$modKey}";

            SidebarItem::updateOrCreate(
                ['item_key' => $parentKey],
                [
                    'label_en'   => $module['label'],
                    'label_ar'   => $module['label'],
                    'icon'       => $module['icon'],
                    'module'     => $modKey,
                    'order'      => $order,
                    'is_visible' => true,
                ]
            );

            $subOrder = 10;
            foreach ($module['children'] as $child) {
                $slug    = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '_', $child['label']));
                $itemKey = "item_{$modKey}_{$slug}";

                SidebarItem::updateOrCreate(
                    ['item_key' => $itemKey],
                    [
                        'label_en'   => $child['label'],
                        'label_ar'   => $child['label'],
                        'url'        => $child['url'],
                        'parent_key' => $parentKey,
                        'module'     => $modKey,
                        'order'      => $subOrder,
                        'permission' => $child['permission'],
                        'is_visible' => true,
                    ]
                );

                // Grant to Super Admin and Admin roles
                $roles = DB::table('roles')->whereIn('name', ['Super Admin', 'Admin'])->get();
                foreach ($roles as $role) {
                    DB::table(config('novapermissions.role_permission_table', 'role_permission'))->insertOrIgnore([
                        'role_id'        => $role->id,
                        'permission_slug'=> $child['permission'],
                        'created_at'     => now(),
                        'updated_at'     => now(),
                    ]);
                }

                $subOrder += 10;
            }

            $order += 10;
        }
    }
}
