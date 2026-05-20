/**
 * Fandaqah Hotel PMS — Sidebar Navigation Store
 * Complete navigation tree for all modules with permission checks and badge counts.
 */
import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import api from '../services/api';

export const useSidebarStore = defineStore('sidebar', () => {
  const isCollapsed = ref(false);
  const isMobileOpen = ref(false);
  const isSearchOpen = ref(false);
  const openGroups = ref({});
  const menuItems = ref([]);
  const loading = ref(false);
  const userPermissions = ref([]);
  const isSuperAdmin = ref(true); // Default to true for demo
  const badges = ref({});

  // ── Complete Navigation Tree ─────────────────────────────
  const defaultNavigation = [
    // ─── DASHBOARD ───
    {
      key: 'dashboard_group',
      label_en: 'Dashboard',
      label_ar: 'لوحة القيادة',
      icon: 'LayoutDashboard',
      order: 10,
      children: [
        { key: 'overview_db', label_en: 'Overview', label_ar: 'نظرة عامة', icon: 'BarChart3', route: '/dashboard', permission: 'view dashboard' },
        { key: 'occupancy_db', label_en: 'Occupancy', label_ar: 'الإشغال', icon: 'PieChart', route: '/dashboard/occupancy', permission: 'view occupancy dashboard' },
        { key: 'revenue_db', label_en: 'Revenue', label_ar: 'الإيرادات', icon: 'TrendingUp', route: '/dashboard/revenue', permission: 'view revenue dashboard' },
        { key: 'frontdesk_db', label_en: 'Front Desk', label_ar: 'مكتب الاستقبال', icon: 'Monitor', route: '/dashboard/front-desk', permission: 'view dashboard' },
        { key: 'finance_db', label_en: 'Finance', label_ar: 'المالية', icon: 'Wallet', route: '/dashboard/finance', permission: 'view financial' },
        { key: 'na_db', label_en: 'Night Audit', label_ar: 'التدقيق الليلي', icon: 'Moon', route: '/night-audit', permission: 'view night audit' },
        { key: 'ar_db', label_en: 'AR Overview', label_ar: 'الذمم', icon: 'Briefcase', route: '/dashboard/ar', permission: 'view financial' },
        { key: 'cashier_db', label_en: 'Cashier', label_ar: 'الصندوق', icon: 'Calculator', route: '/dashboard/cashier', permission: 'cashier.view' },
        { key: 'commission_db', label_en: 'Commissions', label_ar: 'العمولات', icon: 'Award', route: '/dashboard/commissions', permission: 'commission.manage' },
        { key: 'integration_db', label_en: 'Integration Health', label_ar: 'صحة التكامل', icon: 'Activity', route: '/dashboard/integration-health', permission: 'view settings' },
        { key: 'metabase_db', label_en: 'Metabase', label_ar: 'ميتابيس', icon: 'Database', route: '/dashboard/metabase', permission: 'view metabase reports' },
        { key: 'housekeeping_db', label_en: 'Housekeeping', label_ar: 'التدبير المنزلي', icon: 'Sparkles', route: '/dashboard/housekeeping', permission: 'view dashboard' },
      ]
    },

    // ─── RESERVATIONS ───
    {
      key: 'reservations_group',
      label_en: 'Reservations',
      label_ar: 'الحجوزات',
      icon: 'CalendarDays',
      order: 15,
      children: [
        { key: 'all_reservations', label_en: 'All Reservations', label_ar: 'كل الحجوزات', icon: 'List', route: '/reservations/management', permission: 'view reservations' },
        { key: 'new_reservation', label_en: 'New Reservation', label_ar: 'حجز جديد', icon: 'PlusCircle', route: '/reservations/create', permission: 'create reservations' },
        { key: 'quick_create', label_en: 'Quick Create', label_ar: 'إنشاء سريع', icon: 'Zap', route: '/reservations/quick-create', permission: 'create reservations' },
        { key: 'calendar_view', label_en: 'Calendar', label_ar: 'التقويم', icon: 'Calendar', route: '/reservations/calendar', permission: 'view reservations' },
        { key: 'arrivals', label_en: 'Arrivals', label_ar: 'الوصول', icon: 'ArrowDownLeft', route: '/reservations/arrivals', permission: 'view reservations', badge: { key: 'arrivals_today', variant: 'info' } },
        { key: 'departures', label_en: 'Departures', label_ar: 'المغادرة', icon: 'ArrowUpRight', route: '/reservations/departures', permission: 'view reservations', badge: { key: 'departures_today', variant: 'warning' } },
        { key: 'in_house', label_en: 'In-House', label_ar: 'النزلاء', icon: 'Users', route: '/reservations/in-house', permission: 'view reservations' },
        { key: 'online_res', label_en: 'Online', label_ar: 'إلكتروني', icon: 'Globe', route: '/reservations/online', permission: 'view reservations' },
        { key: 'ota_res', label_en: 'OTA', label_ar: 'قنوات OTA', icon: 'Share2', route: '/reservations/ota', permission: 'view reservations' },
        { key: 'group_res', label_en: 'Groups', label_ar: 'المجموعات', icon: 'UsersRound', route: '/reservations/groups', permission: 'view reservations' },
        { key: 'transfers_res', label_en: 'Transfers', label_ar: 'التحويلات', icon: 'ArrowLeftRight', route: '/reservations/transfers', permission: 'view reservations' },
        { key: 'extensions_res', label_en: 'Extensions', label_ar: 'التمديدات', icon: 'CalendarPlus', route: '/reservations/extensions', permission: 'view reservations' },
        { key: 'contracts_res', label_en: 'Contracts', label_ar: 'العقود', icon: 'FileText', route: '/reservations/contracts', permission: 'view reservations' },
        { key: 'cancellations_res', label_en: 'Cancellations', label_ar: 'الإلغاءات', icon: 'XCircle', route: '/reservations/cancellations', permission: 'view reservations' },
        { key: 'messages_res', label_en: 'Messages', label_ar: 'الرسائل', icon: 'MessageSquare', route: '/reservations/messages', permission: 'view reservations', badge: { key: 'unread_messages', variant: 'info' } },
        { key: 'audit_locks_res', label_en: 'Audit Locks', label_ar: 'أقفال التدقيق', icon: 'Lock', route: '/reservations/audit-locks', permission: 'view reservations' },
      ]
    },

    // ─── FRONT DESK ───
    {
      key: 'frontdesk_group',
      label_en: 'Front Desk',
      label_ar: 'مكتب الاستقبال',
      icon: 'ConciergeBell',
      order: 18,
      children: [
        { key: 'checkin', label_en: 'Check-in', label_ar: 'تسجيل الوصول', icon: 'LogIn', route: '/front-desk/check-in', permission: 'create reservations' },
        { key: 'checkout', label_en: 'Check-out', label_ar: 'تسجيل المغادرة', icon: 'LogOut', route: '/front-desk/check-out', permission: 'create reservations' },
        { key: 'walkin', label_en: 'Walk-in', label_ar: 'حجز مباشر', icon: 'UserPlus', route: '/front-desk/walk-in', permission: 'create reservations' },
        { key: 'registration', label_en: 'Registration', label_ar: 'تسجيل', icon: 'ClipboardList', route: '/front-desk/registration', permission: 'create reservations' },
        { key: 'room_assignment', label_en: 'Room Assignment', label_ar: 'تعيين الغرف', icon: 'LayoutGrid', route: '/front-desk/room-assignment', permission: 'create reservations' },
        { key: 'room_swap', label_en: 'Room Swap', label_ar: 'تبديل الغرف', icon: 'Repeat', route: '/front-desk/room-swap', permission: 'create reservations' },
        { key: 'early_checkin', label_en: 'Early Check-in', label_ar: 'وصول مبكر', icon: 'Sunrise', route: '/front-desk/early-check-in', permission: 'create reservations' },
        { key: 'late_checkout', label_en: 'Late Checkout', label_ar: 'مغادرة متأخرة', icon: 'Sunset', route: '/front-desk/late-checkout', permission: 'create reservations' },
        { key: 'no_show', label_en: 'No-Show', label_ar: 'عدم الحضور', icon: 'UserX', route: '/front-desk/no-show', permission: 'view reservations' },
        { key: 'wakeup_calls', label_en: 'Wake-up Calls', label_ar: 'مكالمات الإيقاظ', icon: 'AlarmClock', route: '/front-desk/wake-up-calls', permission: 'view reservations' },
        { key: 'balance_transfer', label_en: 'Balance Transfer', label_ar: 'تحويل الرصيد', icon: 'ArrowRightCircle', route: '/front-desk/balance-transfer', permission: 'view financial' },
        { key: 'reg_cards', label_en: 'Registration Cards', label_ar: 'بطاقات التسجيل', icon: 'CreditCard', route: '/front-desk/registration-cards', permission: 'view reservations' },
      ]
    },

    // ─── NIGHT AUDIT ───
    {
      key: 'night_audit_group',
      label_en: 'Night Audit',
      label_ar: 'التدقيق الليلي',
      icon: 'Moon',
      order: 20,
      children: [
        { key: 'run_na', label_en: 'Run Audit', label_ar: 'تشغيل التدقيق', icon: 'PlayCircle', route: '/operations/night-audit', permission: 'night_audit.run' },
        { key: 'na_status', label_en: 'Status', label_ar: 'الحالة', icon: 'Activity', route: '/operations/night-audit/status', permission: 'view night audit' },
        { key: 'na_logs', label_en: 'Logs', label_ar: 'السجلات', icon: 'FileText', route: '/operations/night-audit/logs', permission: 'night_audit.view_log' },
        { key: 'rerun_na', label_en: 'Rerun', label_ar: 'إعادة التشغيل', icon: 'RotateCcw', route: '/operations/night-audit/rerun', permission: 'night_audit.rerun' },
        { key: 'noshow_preview', label_en: 'No-Show Preview', label_ar: 'معاينة عدم الحضور', icon: 'Eye', route: '/operations/no-show-preview', permission: 'view noshow rules' },
        { key: 'noshow_rules', label_en: 'No-Show Rules', label_ar: 'قواعد عدم الحضور', icon: 'Settings', route: '/operations/no-show-rules', permission: 'view noshow rules' },
        { key: 'backfill', label_en: 'Historical Backfill', label_ar: 'تعبئة تاريخية', icon: 'History', route: '/operations/night-audit/backfill', permission: 'night_audit.rerun_historical' },
        { key: 'audit_locks', label_en: 'Audit Locks', label_ar: 'أقفال التدقيق', icon: 'Lock', route: '/operations/night-audit/locks', permission: 'night_audit.force_run' },
      ]
    },

    // ─── ROOMS & HOUSEKEEPING ───
    {
      key: 'rooms_group',
      label_en: 'Rooms & Housekeeping',
      label_ar: 'الغرف والتدبير',
      icon: 'BedDouble',
      order: 22,
      children: [
        { key: 'all_rooms', label_en: 'All Rooms', label_ar: 'كل الغرف', icon: 'DoorOpen', route: '/units', permission: 'view units' },
        { key: 'room_types', label_en: 'Room Types', label_ar: 'أنواع الغرف', icon: 'Tag', route: '/room-types', permission: 'view units' },
        { key: 'room_floors', label_en: 'Floors', label_ar: 'الطوابق', icon: 'Layers', route: '/room-floors', permission: 'view units' },
        { key: 'availability', label_en: 'Availability', label_ar: 'التوفر', icon: 'CalendarCheck', route: '/units/availability', permission: 'view units' },
        { key: 'status_board', label_en: 'Status Board', label_ar: 'لوحة الحالة', icon: 'LayoutGrid', route: '/units/status-board', permission: 'view units' },
        { key: 'hk_board', label_en: 'Housekeeping Board', label_ar: 'لوحة التنظيف', icon: 'Sparkles', route: '/housekeeping/board', permission: 'view units' },
        { key: 'cleanings', label_en: 'Cleaning Tasks', label_ar: 'مهام التنظيف', icon: 'Brush', route: '/unit-cleanings', permission: 'view units' },
        { key: 'maintenance', label_en: 'Maintenance', label_ar: 'الصيانة', icon: 'Wrench', route: '/unit-maintenances', permission: 'view units' },
        { key: 'room_status_log', label_en: 'Status Log', label_ar: 'سجل الحالة', icon: 'ListChecks', route: '/room-status-log', permission: 'room_status.view' },
        { key: 'features', label_en: 'Features', label_ar: 'الميزات', icon: 'Star', route: '/unit-features', permission: 'view units' },
        { key: 'options', label_en: 'Options', label_ar: 'الخيارات', icon: 'SlidersHorizontal', route: '/unit-options', permission: 'view units' },
      ]
    },

    // ─── GUESTS & COMPANIES ───
    {
      key: 'guests_group',
      label_en: 'Guests & Companies',
      label_ar: 'الضيوف والشركات',
      icon: 'Users',
      order: 25,
      children: [
        { key: 'guest_directory', label_en: 'Guest Directory', label_ar: 'دليل الضيوف', icon: 'Contact', route: '/guests', permission: 'view customers' },
        { key: 'customers', label_en: 'Customers', label_ar: 'العملاء', icon: 'UserCircle', route: '/customers', permission: 'view customers' },
        { key: 'companies', label_en: 'Companies', label_ar: 'الشركات', icon: 'Building2', route: '/companies', permission: 'view customers' },
        { key: 'company_groups', label_en: 'Company Groups', label_ar: 'مجموعات الشركات', icon: 'Network', route: '/company-groups', permission: 'ar.manage_company_groups' },
        { key: 'blocked_guests', label_en: 'Blocked Guests', label_ar: 'ضيوف محظورون', icon: 'ShieldBan', route: '/blocked-guests', permission: 'view customers' },
        { key: 'turnaway', label_en: 'Turnaway Logs', label_ar: 'سجل الرفض', icon: 'UserMinus', route: '/turnaway-logs', permission: 'view customers' },
        { key: 'highlights', label_en: 'Highlights', label_ar: 'أبرز النقاط', icon: 'Sparkle', route: '/highlights', permission: 'view customers' },
        { key: 'merge', label_en: 'Merge Customers', label_ar: 'دمج العملاء', icon: 'Merge', route: '/customers/merge', permission: 'view customers' },
      ]
    },

    // ─── POS & SERVICES ───
    {
      key: 'pos_group',
      label_en: 'POS & Services',
      label_ar: 'نقطة البيع',
      icon: 'ShoppingCart',
      order: 28,
      children: [
        { key: 'pos_dashboard', label_en: 'POS Dashboard', label_ar: 'لوحة نقطة البيع', icon: 'BarChart2', route: '/pos/dashboard', permission: 'view services' },
        { key: 'pos_sale', label_en: 'Point of Sale', label_ar: 'نقطة البيع', icon: 'ShoppingBag', route: '/pos/sale', permission: 'view services' },
        { key: 'service_cats', label_en: 'Service Categories', label_ar: 'فئات الخدمات', icon: 'FolderOpen', route: '/pos/service-categories', permission: 'view services' },
        { key: 'services', label_en: 'Services', label_ar: 'الخدمات', icon: 'Wrench', route: '/pos/services-manage', permission: 'view services' },
        { key: 'service_logs', label_en: 'Service Logs', label_ar: 'سجل الخدمات', icon: 'FileText', route: '/pos/service-logs', permission: 'view services' },
        { key: 'quick_payments', label_en: 'Quick Payments', label_ar: 'مدفوعات سريعة', icon: 'Zap', route: '/pos/quick-payments', permission: 'view services' },
        { key: 'pos_transactions', label_en: 'Transactions', label_ar: 'المعاملات', icon: 'Receipt', route: '/pos/pos-transactions', permission: 'view services' },
      ]
    },

    // ─── FINANCE ───
    {
      key: 'finance_group',
      label_en: 'Finance',
      label_ar: 'المالية',
      icon: 'DollarSign',
      order: 30,
      children: [
        { key: 'guest_ledger', label_en: 'Guest Ledger', label_ar: 'دفتر النزلاء', icon: 'BookOpen', route: '/finance/guest-ledger', permission: 'view financial' },
        { key: 'deposit_ledger', label_en: 'Deposit Ledger', label_ar: 'دفتر الإيداع', icon: 'Archive', route: '/finance/deposit-ledger', permission: 'view financial' },
        { key: 'receipts', label_en: 'Receipts', label_ar: 'الإيصالات', icon: 'Receipt', route: '/finance/receipts', permission: 'view financial' },
        { key: 'payments', label_en: 'Payments', label_ar: 'المدفوعات', icon: 'CreditCard', route: '/finance/payments', permission: 'view financial' },
        { key: 'invoices', label_en: 'Invoices', label_ar: 'الفواتير', icon: 'FileText', route: '/finance/invoices', permission: 'view invoices', badge: { key: 'zatca_pending', variant: 'warning' } },
        { key: 'credit_notes', label_en: 'Credit Notes', label_ar: 'إشعارات دائنة', icon: 'FileMinus', route: '/financial/credit-notes', permission: 'view financial' },
        { key: 'room_adjustments', label_en: 'Room Adjustments', label_ar: 'تعديلات الغرف', icon: 'Sliders', route: '/operations/room-adjustments', permission: 'revenue.adjustment' },
        { key: 'payment_correction', label_en: 'Payment Corrections', label_ar: 'تصحيحات الدفع', icon: 'Edit3', route: '/finance/payment-correction', permission: 'finance.payment_correction' },
        { key: 'cashier_shifts', label_en: 'Cashier Shifts', label_ar: 'ورديات الصندوق', icon: 'Calculator', route: '/finance/cashier-shifts', permission: 'cashier.view', badge: { key: 'open_shifts', variant: 'success' } },
        { key: 'banks', label_en: 'Banks', label_ar: 'البنوك', icon: 'Landmark', route: '/finance/banks', permission: 'view financial' },
        { key: 'senders', label_en: 'Senders', label_ar: 'المرسلون', icon: 'Send', route: '/finance/senders', permission: 'view financial' },
        { key: 'travel_agents', label_en: 'Travel Agents', label_ar: 'وكلاء السفر', icon: 'Map', route: '/finance/travel-agents', permission: 'commission.manage' },
        { key: 'commissions', label_en: 'Commissions', label_ar: 'العمولات', icon: 'Award', route: '/finance/commissions', permission: 'commission.manage' },
        { key: 'commission_payments', label_en: 'Commission Payments', label_ar: 'مدفوعات العمولات', icon: 'Banknote', route: '/finance/commission-payments', permission: 'commission.manage' },
      ]
    },

    // ─── AR MANAGEMENT ───
    {
      key: 'ar_group',
      label_en: 'AR Management',
      label_ar: 'إدارة الذمم',
      icon: 'Briefcase',
      order: 40,
      children: [
        { key: 'city_ledger', label_en: 'City Ledger', label_ar: 'دفتر المدينة', icon: 'Building', route: '/ar/city-ledger', permission: 'ar.city_ledger.view' },
        { key: 'trial_balance', label_en: 'Trial Balance', label_ar: 'ميزان المراجعة', icon: 'Scale', route: '/reports/trial-balance', permission: 'view financial' },
        { key: 'promissories', label_en: 'Promissories', label_ar: 'السندات', icon: 'FileText', route: '/ar/promissories', permission: 'view promissories' },
        { key: 'promissory_logs', label_en: 'Payment Log', label_ar: 'سجل الدفع', icon: 'History', route: '/ar/promissory-payment-logs', permission: 'view promissory payment logs' },
        { key: 'promissory_collections', label_en: 'Collections', label_ar: 'التحصيل', icon: 'Wallet', route: '/finance/promissory-collections', permission: 'view promissories' },
        { key: 'invoice_transfers', label_en: 'Invoice Transfers', label_ar: 'نقل الفواتير', icon: 'Shuffle', route: '/ar/invoice-transfers', permission: 'ar.invoice_transfer' },
        { key: 'ar_aging', label_en: 'AR Aging', label_ar: 'تقادم الذمم', icon: 'Clock', route: '/ar/aging', permission: 'ar.city_ledger.view' },
        { key: 'credit_util', label_en: 'Credit Utilization', label_ar: 'استهلاك الائتمان', icon: 'BarChart', route: '/ar/credit-utilization', permission: 'ar.city_ledger.view' },
      ]
    },

    // ─── REPORTS ───
    {
      key: 'reports_group',
      label_en: 'Reports & Analytics',
      label_ar: 'التقارير والتحليلات',
      icon: 'BarChart3',
      order: 45,
      children: [
        { key: 'daily_report', label_en: 'Daily Report', label_ar: 'التقرير اليومي', icon: 'CalendarDays', route: '/reports/daily', permission: 'view reports' },
        { key: 'occupancy_report', label_en: 'Occupancy', label_ar: 'الإشغال', icon: 'PieChart', route: '/reports/occupancy', permission: 'view reports' },
        { key: 'revenue_report', label_en: 'Revenue', label_ar: 'الإيرادات', icon: 'TrendingUp', route: '/reports/revenue', permission: 'view reports' },
        { key: 'forecast_report', label_en: 'Forecast & History', label_ar: 'التوقعات', icon: 'TrendingUp', route: '/reports/forecast-history', permission: 'view reports' },
        { key: 'noshow_report', label_en: 'No-Show Report', label_ar: 'عدم الحضور', icon: 'UserX', route: '/reports/no-show', permission: 'view reports' },
        { key: 'cancel_report', label_en: 'Cancellation', label_ar: 'الإلغاءات', icon: 'XCircle', route: '/reports/cancellation', permission: 'view reports' },
        { key: 'commission_report', label_en: 'Commission', label_ar: 'العمولات', icon: 'Award', route: '/reports/commission', permission: 'view reports' },
        { key: 'paid_outs_report', label_en: 'Paid Outs', label_ar: 'المدفوعات', icon: 'Banknote', route: '/reports/paid-outs', permission: 'view reports' },
        { key: 'turnaway_report', label_en: 'Turnaway', label_ar: 'الرفض', icon: 'UserMinus', route: '/reports/turnaway', permission: 'view reports' },
        { key: 'source_report', label_en: 'Source Performance', label_ar: 'أداء المصادر', icon: 'Target', route: '/reports/source-performance', permission: 'view reports' },
        { key: 'company_ar', label_en: 'Company AR', label_ar: 'ذمم الشركات', icon: 'Building2', route: '/reports/company-ar', permission: 'view reports' },
        { key: 'trial_balance_report', label_en: 'Trial Balance', label_ar: 'ميزان المراجعة', icon: 'Scale', route: '/reports/trial-balance', permission: 'view reports' },
        { key: 'hk_discrepancy', label_en: 'HK Discrepancy', label_ar: 'اختلافات التنظيف', icon: 'AlertTriangle', route: '/reports/housekeeping-discrepancy', permission: 'view reports' },
        { key: 'adr_revpar', label_en: 'ADR & RevPAR', label_ar: 'ADR و RevPAR', icon: 'BarChart2', route: '/reports/adr-revpar', permission: 'view reports' },
        { key: 'custom_reports', label_en: 'Custom Reports', label_ar: 'تقارير مخصصة', icon: 'FileEdit', route: '/reports/custom-reports', permission: 'view reports' },
        { key: 'report_schedules', label_en: 'Schedules', label_ar: 'جداول التقارير', icon: 'CalendarClock', route: '/reports/report-schedules', permission: 'view reports' },
      ]
    },

    // ─── MARKETING ───
    {
      key: 'marketing_group',
      label_en: 'Marketing',
      label_ar: 'التسويق',
      icon: 'Megaphone',
      order: 48,
      children: [
        { key: 'offers', label_en: 'Offers', label_ar: 'العروض', icon: 'Gift', route: '/marketing/offers', permission: 'view settings' },
        { key: 'promo_codes', label_en: 'Promo Codes', label_ar: 'أكواد الخصم', icon: 'Ticket', route: '/marketing/promo-codes', permission: 'view settings' },
        { key: 'pricing_preview', label_en: 'Pricing Preview', label_ar: 'معاينة الأسعار', icon: 'Eye', route: '/marketing/pricing-preview', permission: 'view settings' },
      ]
    },

    // ─── CHANNEL MANAGER ───
    {
      key: 'channel_group',
      label_en: 'Channel Manager',
      label_ar: 'إدارة القنوات',
      icon: 'Share2',
      order: 50,
      children: [
        { key: 'channel_overview', label_en: 'Overview', label_ar: 'نظرة عامة', icon: 'LayoutDashboard', route: '/channel-manager', permission: 'view settings' },
        { key: 'channel_rates', label_en: 'Rates & Availability', label_ar: 'الأسعار والتوفر', icon: 'DollarSign', route: '/channel-manager/availability-rates', permission: 'view settings' },
        { key: 'channel_reservations', label_en: 'Reservations', label_ar: 'الحجوزات', icon: 'CalendarDays', route: '/channel-manager/reservations', permission: 'view settings' },
      ]
    },

    // ─── SETTINGS ───
    {
      key: 'settings_group',
      label_en: 'Settings',
      label_ar: 'الإعدادات',
      icon: 'Settings',
      order: 60,
      children: [
        { key: 'general_settings', label_en: 'General', label_ar: 'عام', icon: 'Settings2', route: '/settings', permission: 'view settings' },
        { key: 'na_settings', label_en: 'Night Audit', label_ar: 'التدقيق الليلي', icon: 'Moon', route: '/settings/night-audit', permission: 'view settings' },
        { key: 'early_late_settings', label_en: 'Early/Late Charges', label_ar: 'رسوم مبكرة/متأخرة', icon: 'Clock', route: '/settings/early-late', permission: 'view settings' },
        { key: 'noshow_settings', label_en: 'No-Show Rules', label_ar: 'قواعد عدم الحضور', icon: 'ShieldAlert', route: '/settings/no-show', permission: 'view settings' },
        { key: 'rev_types', label_en: 'Revenue Types', label_ar: 'أنواع الإيرادات', icon: 'Tag', route: '/settings/revenue-types', permission: 'view settings' },
        { key: 'roles_perms', label_en: 'Roles & Permissions', label_ar: 'الأدوار والصلاحيات', icon: 'Shield', route: '/settings/roles', permission: 'user and roles' },
        { key: 'sidebar_access', label_en: 'Sidebar Access', label_ar: 'وصول القائمة', icon: 'Menu', route: '/settings/sidebar', permission: 'view settings' },
      ]
    },
  ];

  // ── Actions ─────────────────────────────────────────────
  async function loadNavigation() {
    loading.value = true;
    try {
      const { data } = await api.get('/sidebar');
      if (data.data && data.data.length > 0) {
        menuItems.value = data.data;
      } else {
        menuItems.value = defaultNavigation;
      }
    } catch (err) {
      // Fallback to local navigation when API is unavailable
      console.warn('Using local sidebar navigation (API unavailable)');
      menuItems.value = defaultNavigation;
    } finally {
      loading.value = false;
    }
  }

  function loadBadges() {
    // Sample badge counts for demo
    badges.value = {
      arrivals_today: 8,
      departures_today: 5,
      unread_messages: 3,
      zatca_pending: 7,
      open_shifts: 2,
      failed_jobs: 0,
    };
  }

  function toggleSidebar() {
    isCollapsed.value = !isCollapsed.value;
    localStorage.setItem('sidebar_collapsed', isCollapsed.value);
  }

  function toggleMobile() {
    isMobileOpen.value = !isMobileOpen.value;
  }

  function toggleSearch() {
    isSearchOpen.value = !isSearchOpen.value;
  }

  function toggleGroup(key) {
    openGroups.value[key] = !openGroups.value[key];
  }

  function expandActiveGroup(currentPath) {
    menuItems.value.forEach(item => {
      if