<?php

return [
    // ── DASHBOARD ──
    [
        'key' => 'dashboard_group',
        'label_en' => 'Dashboard',
        'label_ar' => 'لوحة القيادة',
        'icon' => 'LayoutDashboardIcon',
        'route' => '#',
        'permission' => null,
        'order' => 10,
        'children' => [
            ['key' => 'overview_db', 'label_en' => 'Overview Dashboard', 'label_ar' => 'نظرة عامة', 'icon' => 'BarChart2Icon', 'route' => '/dashboard', 'permission' => 'view dashboard', 'order' => 1],
            ['key' => 'occupancy_db', 'label_en' => 'Occupancy Dashboard', 'label_ar' => 'نسبة الإشغال', 'icon' => 'PieChartIcon', 'route' => '/dashboard/occupancy', 'permission' => 'view occupancy dashboard', 'order' => 2],
            ['key' => 'revenue_db', 'label_en' => 'Revenue Dashboard', 'label_ar' => 'الإيرادات', 'icon' => 'TrendingUpIcon', 'route' => '/dashboard/revenue', 'permission' => 'view revenue dashboard', 'order' => 3],
            ['key' => 'na_db', 'label_en' => 'Night Audit Dashboard', 'label_ar' => 'لوحة التدقيق الليلي', 'icon' => 'MoonIcon', 'route' => '/night-audit', 'permission' => 'view night audit', 'order' => 4],
            ['key' => 'metabase', 'label_en' => 'Metabase Reports', 'label_ar' => 'تقارير ميتابيس', 'icon' => 'DatabaseIcon', 'route' => '/reports/metabase', 'permission' => 'view metabase reports', 'order' => 5],
        ]
    ],

    // ── NIGHT AUDIT ──
    [
        'key' => 'night_audit_group',
        'label_en' => 'Night Audit',
        'label_ar' => 'التدقيق الليلي',
        'icon' => 'MoonIcon',
        'route' => '#',
        'permission' => null,
        'order' => 20,
        'children' => [
            ['key' => 'run_na', 'label_en' => 'Run Night Audit', 'label_ar' => 'تشغيل التدقيق', 'icon' => 'PlayCircleIcon', 'route' => '/operations/night-audit', 'permission' => 'night_audit.run', 'order' => 1],
            ['key' => 'na_status', 'label_en' => 'Audit Status', 'label_ar' => 'حالة التدقيق', 'icon' => 'ActivityIcon', 'route' => '/operations/night-audit/status', 'permission' => 'view night audit', 'order' => 2],
            ['key' => 'na_logs', 'label_en' => 'Audit Logs', 'label_ar' => 'سجلات التدقيق', 'icon' => 'FileTextIcon', 'route' => '/operations/night-audit/logs', 'permission' => 'night_audit.view_log', 'order' => 3],
            ['key' => 'rerun_na', 'label_en' => 'Rerun Audit', 'label_ar' => 'إعادة تشغيل التدقيق', 'icon' => 'RotateCcwIcon', 'route' => '/operations/night-audit/rerun', 'permission' => 'night_audit.rerun', 'order' => 4],
            ['key' => 'noshow_preview', 'label_en' => 'No-Show Preview', 'label_ar' => 'معاينة عدم الحضور', 'icon' => 'EyeIcon', 'route' => '/operations/no-show-preview', 'permission' => 'view noshow rules', 'order' => 5],
            ['key' => 'noshow_rules', 'label_en' => 'No-Show Charge Rules', 'label_ar' => 'قواعد عدم الحضور', 'icon' => 'SettingsIcon', 'route' => '/operations/no-show-rules', 'permission' => 'view noshow rules', 'order' => 6],
            ['key' => 'historical_backfill', 'label_en' => 'Historical Backfill', 'label_ar' => 'تعبئة تاريخية', 'icon' => 'HistoryIcon', 'route' => '/operations/night-audit/backfill', 'permission' => 'night_audit.rerun_historical', 'order' => 7],
            ['key' => 'audit_locks', 'label_en' => 'Audit Locks', 'label_ar' => 'أقفال التدقيق', 'icon' => 'LockIcon', 'route' => '/operations/night-audit/locks', 'permission' => 'night_audit.force_run', 'order' => 8],
        ]
    ],

    // ── FINANCE ──
    [
        'key' => 'finance_group',
        'label_en' => 'Finance',
        'label_ar' => 'المالية',
        'icon' => 'DollarSignIcon',
        'route' => '#',
        'permission' => null,
        'order' => 30,
        'children' => [
            ['key' => 'guest_ledger', 'label_en' => 'Guest Ledger', 'label_ar' => 'دفتر نزلاء', 'icon' => 'UserIcon', 'route' => '/finance/guest-ledger', 'permission' => 'view financial', 'order' => 1],
            ['key' => 'deposit_ledger', 'label_en' => 'Deposit Ledger', 'label_ar' => 'دفتر الإيداع', 'icon' => 'ArchiveIcon', 'route' => '/finance/deposit-ledger', 'permission' => 'view financial', 'order' => 2],
            ['key' => 'city_ledger', 'label_en' => 'City Ledger / AR', 'label_ar' => 'دفتر المدينه', 'icon' => 'BuildingIcon', 'route' => '/ar/city-ledger', 'permission' => 'ar.city_ledger.view', 'order' => 3],
            ['key' => 'trial_balance', 'label_en' => 'Trial Balance', 'label_ar' => 'ميزان المراجعة', 'icon' => 'ScaleIcon', 'route' => '/finance/trial-balance', 'permission' => 'view financial', 'order' => 4],
            ['key' => 'room_adjustments', 'label_en' => 'Room Adjustments', 'label_ar' => 'تعديلات الغرف', 'icon' => 'SlidersIcon', 'route' => '/financial/room-adjustments', 'permission' => 'revenue.adjustment', 'order' => 5],
            ['key' => 'payment_corrections', 'label_en' => 'Payment Corrections', 'label_ar' => 'تصحيحات الدفع', 'icon' => 'Edit3Icon', 'route' => '/financial/payment-corrections', 'permission' => 'finance.payment_correction', 'order' => 6],
            ['key' => 'rebates', 'label_en' => 'Rebates / Write-offs', 'label_ar' => 'الخصومات', 'icon' => 'PercentIcon', 'route' => '/financial/rebates', 'permission' => 'finance.rebate_write_off', 'order' => 7],
            ['key' => 'checkout_transfers', 'label_en' => 'Checkout Balance Transfers', 'label_ar' => 'تحويلات الخروج', 'icon' => 'ArrowRightCircleIcon', 'route' => '/financial/checkout-transfers', 'permission' => 'view checkout balance transfers', 'order' => 8],
        ]
    ],

    // ── AR MANAGEMENT ──
    [
        'key' => 'ar_group',
        'label_en' => 'AR Management',
        'label_ar' => 'إدارة الذمم',
        'icon' => 'BriefcaseIcon',
        'route' => '#',
        'permission' => null,
        'order' => 40,
        'children' => [
            ['key' => 'promissories', 'label_en' => 'Promissories', 'label_ar' => 'السندات الإذنية', 'icon' => 'FileTextIcon', 'route' => '/ar/promissories', 'permission' => 'view promissories', 'order' => 1],
            ['key' => 'promissory_logs', 'label_en' => 'Promissory Payment Log', 'label_ar' => 'سجلات الدفع', 'icon' => 'HistoryIcon', 'route' => '/ar/promissory-payment-logs', 'permission' => 'view promissory payment logs', 'order' => 2],
            ['key' => 'company_groups', 'label_en' => 'Company Groups', 'label_ar' => 'مجموعات الشركات', 'icon' => 'UsersIcon', 'route' => '/ar/company-groups', 'permission' => 'ar.manage_company_groups', 'order' => 3],
            ['key' => 'credit_limits', 'label_en' => 'Companies Credit Limits', 'label_ar' => 'حدود الائتمان', 'icon' => 'ShieldIcon', 'route' => '/ar/credit-limits', 'permission' => 'ar.manage_company_groups', 'order' => 4],
            ['key' => 'invoice_transfers', 'label_en' => 'Invoice Transfers', 'label_ar' => 'نقل الفواتير', 'icon' => 'ShuffleIcon', 'route' => '/ar/invoice-transfers', 'permission' => 'ar.invoice_transfer', 'order' => 5],
            ['key' => 'ar_aging', 'label_en' => 'AR Aging', 'label_ar' => 'تقادم الذمم', 'icon' => 'ClockIcon', 'route' => '/ar/aging', 'permission' => 'ar.city_ledger.view', 'order' => 6],
            ['key' => 'credit_utilization', 'label_en' => 'Company Credit Utilization', 'label_ar' => 'استهلاك الائتمان', 'icon' => 'BarChartIcon', 'route' => '/ar/credit-utilization', 'permission' => 'ar.city_ledger.view', 'order' => 7],
        ]
    ],

    // ── OPERATIONS ──
    [
        'key' => 'operations_group',
        'label_en' => 'Operations',
        'label_ar' => 'العمليات',
        'icon' => 'HardHatIcon',
        'route' => '#',
        'permission' => null,
        'order' => 50,
        'children' => [
            ['key' => 'cashier_shifts', 'label_en' => 'Cashier Shifts', 'label_ar' => 'ورديات الصندوق', 'icon' => 'CreditCardIcon', 'route' => '/finance/cashier-shifts', 'permission' => 'cashier.view', 'order' => 1],
            ['key' => 'room_status', 'label_en' => 'Room Status Log', 'label_ar' => 'سجل حالة الغرف', 'icon' => 'ListIcon', 'route' => '/finance/room-status-logs', 'permission' => 'room_status.view', 'order' => 2],
            ['key' => 'early_late_configs', 'label_en' => 'Early/Late Charge Configs', 'label_ar' => 'رسوم مبكرة/متأخرة', 'icon' => 'ClockIcon', 'route' => '/night-audit/early-late-charges', 'permission' => 'view early late charges', 'order' => 3],
            ['key' => 'travel_agents', 'label_en' => 'Travel Agents', 'label_ar' => 'وكلاء السفر', 'icon' => 'MapIcon', 'route' => '/finance/travel-agents', 'permission' => 'commission.manage', 'order' => 4],
            ['key' => 'commissions', 'label_en' => 'Commission Payments', 'label_ar' => 'مدفوعات العمولات', 'icon' => 'AwardIcon', 'route' => '/finance/commissions', 'permission' => 'commission.manage', 'order' => 5],
        ]
    ],

    // ── SETTINGS ──
    [
        'key' => 'settings_group',
        'label_en' => 'Settings',
        'label_ar' => 'الإعدادات',
        'icon' => 'SettingsIcon',
        'route' => '#',
        'permission' => null,
        'order' => 60,
        'children' => [
            ['key' => 'na_settings', 'label_en' => 'Night Audit Settings', 'label_ar' => 'إعدادات التدقيق', 'icon' => 'MoonIcon', 'route' => '/settings/night-audit', 'permission' => 'view settings', 'order' => 1],
            ['key' => 'early_late_settings', 'label_en' => 'Early/Late Charges', 'label_ar' => 'رسوم إضافية', 'icon' => 'ClockIcon', 'route' => '/settings/early-late', 'permission' => 'view settings', 'order' => 2],
            ['key' => 'noshow_settings', 'label_en' => 'No-Show Rules', 'label_ar' => 'قواعد عدم الحضور', 'icon' => 'SettingsIcon', 'route' => '/settings/no-show', 'permission' => 'view settings', 'order' => 3],
            ['key' => 'rev_types', 'label_en' => 'Service Revenue Types', 'label_ar' => 'أنواع الإيرادات', 'icon' => 'TagIcon', 'route' => '/settings/revenue-types', 'permission' => 'view settings', 'order' => 4],
            ['key' => 'roles_perms', 'label_en' => 'Roles & Permissions', 'label_ar' => 'الأدوار والصلاحيات', 'icon' => 'ShieldIcon', 'route' => '/settings/roles', 'permission' => 'user and roles', 'order' => 5],
            ['key' => 'sidebar_access', 'label_en' => 'Sidebar Menu Access', 'label_ar' => 'وصول القائمة', 'icon' => 'MenuIcon', 'route' => '/settings/sidebar', 'permission' => 'view settings', 'order' => 6],
        ]
    ],
];
