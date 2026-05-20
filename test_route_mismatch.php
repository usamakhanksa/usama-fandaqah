<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\SidebarItem;

// Get all sidebar URLs from database
$sidebarItems = SidebarItem::where('is_visible', true)
    ->whereNotNull('url')
    ->where('url', '!=', '#')
    ->pluck('url')
    ->toArray();

// Vue router defined routes
$vueRoutes = [
    '/dashboard',
    '/dashboard/occupancy',
    '/dashboard/revenue',
    '/dashboard/front-desk',
    '/dashboard/finance',
    '/night-audit',
    '/operations/night-audit',
    '/operations/night-audit/status',
    '/operations/night-audit/logs',
    '/operations/night-audit/rerun',
    '/operations/night-audit/backfill',
    '/operations/night-audit/locks',
    '/operations/no-show-rules',
    '/operations/no-show-preview',
    '/operations/room-adjustments',
    '/operations/check-out/create',
    '/operations/check-out/success',
    '/reservations',
    '/reservations/management',
    '/reservations/management/*',
    '/reservations/schedule',
    '/reservations/create',
    '/reservations/create/*',
    '/reservations/success/*',
    '/reservations/quick-create',
    '/reservations/calendar',
    '/reservations/arrivals',
    '/reservations/departures',
    '/reservations/in-house',
    '/reservations/online',
    '/reservations/ota',
    '/reservations/groups',
    '/reservations/groups/create',
    '/reservations/transfers',
    '/reservations/extensions',
    '/reservations/contracts',
    '/reservations/signatures',
    '/reservations/ratings',
    '/reservations/cancellations',
    '/reservations/messages',
    '/reservations/audit-locks',
    '/reservations/*/guests',
    '/reservations/*/rooms',
    '/reservations/*',
    '/new-reservation',
    '/new-reservation/*',
    '/front-desk/check-in',
    '/front-desk/check-out',
    '/front-desk/walk-in',
    '/front-desk/registration',
    '/front-desk/room-assignment',
    '/front-desk/room-swap',
    '/front-desk/early-check-in',
    '/front-desk/late-checkout',
    '/front-desk/no-show',
    '/front-desk/wake-up-calls',
    '/front-desk/iptv-needs',
    '/front-desk/registration-cards',
    '/front-desk/balance-transfer',
    '/units',
    '/units/availability',
    '/units/status-board',
    '/unit-categories',
    '/housekeeping/board',
    '/unit-cleanings',
    '/unit-maintenances',
    '/room-status-log',
    '/room-types',
    '/room-floors',
    '/unit-features',
    '/unit-options',
    '/unit-category-services',
    '/guests',
    '/customers',
    '/customers/merge',
    '/companies',
    '/company-groups',
    '/blocked-guests',
    '/turnaway-logs',
    '/turnaway-reasons',
    '/highlights',
    '/pos/dashboard',
    '/pos/sale',
    '/pos/service-categories',
    '/pos/services-manage',
    '/pos/service-logs',
    '/pos/quick-payments',
    '/pos/pos-transactions',
    '/pos/service-qoyods',
    '/pos',
    '/pos/store',
    '/pos/orders',
    '/pos/services',
    '/pos/services/create',
    '/pos/transactions',
    '/pos/products',
    '/pos/products/brands',
    '/pos/products/categories',
    '/pos/products/sub-categories',
    '/services',
    '/financial',
    '/financial/receipts',
    '/financial/receipts/create',
    '/financial/receipts/success/*',
    '/financial/expenses',
    '/financial/expenses/create',
    '/financial/expenses/success/*',
    '/financial/bills',
    '/financial/fund-movement',
    '/financial/credit-notes',
    '/finance/payment-correction',
    '/finance/cashier-shifts',
    '/finance/room-status-logs',
    '/finance/travel-agents',
    '/finance/commissions',
    '/finance/guest-ledger',
    '/finance/deposit-ledger',
    '/ar/invoice-transfers',
    '/ar/promissories',
    '/ar/promissory-payment-logs',
    '/ar/company-groups',
    '/ar/city-ledger',
    '/ar/aging',
    '/ar/credit-utilization',
    '/channel-manager',
    '/channel-manager/availability-rates',
    '/channel-manager/reservations',
    '/settings',
    '/settings/night-audit',
    '/settings/early-late',
    '/settings/no-show',
    '/settings/revenue-types',
    '/settings/roles',
    '/settings/sidebar',
    '/user-groups',
    '/user-groups/*',
    '/night-audit/early-late-charges',
    '/leads',
    '/rooms',
    '/reports',
    '/profile',
    '/login',
];

echo "=== Sidebar routes NOT in Vue router ===\n";
foreach ($sidebarItems as $url) {
    $url = '/' . ltrim($url, '/');
    $found = false;

    // Check exact match
    if (in_array($url, $vueRoutes)) {
        $found = true;
    }

    // Check pattern match (with *)
    if (!$found) {
        foreach ($vueRoutes as $route) {
            if (strpos($route, '*') !== false) {
                $pattern = str_replace(['*', '/'], ['[^/]+', '\/'], $route);
                $pattern = '/^' . $pattern . '$/';
                if (preg_match($pattern, $url)) {
                    $found = true;
                    break;
                }
            }
        }
    }

    if (!$found) {
        echo "  $url\n";
    }
}

echo "\n=== Vue routes NOT in sidebar ===\n";
foreach ($vueRoutes as $route) {
    if (strpos($route, '*') !== false) continue; // Skip patterns

    $found = false;
    foreach ($sidebarItems as $url) {
        $url = '/' . ltrim($url, '/');
        if ($url === $route || strpos($url, $route) === 0) {
            $found = true;
            break;
        }
    }
    if (!$found) {
        echo "  $route\n";
    }
}
