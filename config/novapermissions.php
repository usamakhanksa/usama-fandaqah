<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Permissions
    |--------------------------------------------------------------------------
    |
    | Here you can configure all permissions available in the application.
    |
    */
    'permissions' => [
        // Reservations module
        'view reservations' => [
            'name' => [
                'en' => 'View Reservations',
                'ar' => 'عرض الحجوزات',
            ],
            'group' => 'reservations',
        ],
        'create reservations' => [
            'name' => [
                'en' => 'Create Reservations',
                'ar' => 'إنشاء الحجوزات',
            ],
            'group' => 'reservations',
        ],
        'update reservations' => [
            'name' => [
                'en' => 'Update Reservations',
                'ar' => 'تحديث الحجوزات',
            ],
            'group' => 'reservations',
        ],
        'delete reservations' => [
            'name' => [
                'en' => 'Delete Reservations',
                'ar' => 'حذف الحجوزات',
            ],
            'group' => 'reservations',
        ],
        'checkin reservations' => [
            'name' => [
                'en' => 'Check-in Reservations',
                'ar' => 'تسجيل الدخول للحجوزات',
            ],
            'group' => 'reservations',
        ],
        'checkout reservations' => [
            'name' => [
                'en' => 'Check-out Reservations',
                'ar' => 'تسجيل الخروج للحجوزات',
            ],
            'group' => 'reservations',
        ],
        'cancel reservations' => [
            'name' => [
                'en' => 'Cancel Reservations',
                'ar' => 'إلغاء الحجوزات',
            ],
            'group' => 'reservations',
        ],
        'transfer reservations' => [
            'name' => [
                'en' => 'Transfer Reservations',
                'ar' => 'نقل الحجوزات',
            ],
            'group' => 'reservations',
        ],
        'extend reservations' => [
            'name' => [
                'en' => 'Extend Reservations',
                'ar' => 'تمديد الحجوزات',
            ],
            'group' => 'reservations',
        ],
        'noshow reservations' => [
            'name' => [
                'en' => 'Mark No-show Reservations',
                'ar' => 'تحديد الحجوزات المتخلفة',
            ],
            'group' => 'reservations',
        ],
        'export reservations' => [
            'name' => [
                'en' => 'Export Reservations',
                'ar' => 'تصدير الحجوزات',
            ],
            'group' => 'reservations',
        ],
        'import reservations' => [
            'name' => [
                'en' => 'Import Reservations',
                'ar' => 'استيراد الحجوزات',
            ],
            'group' => 'reservations',
        ],
        'approve reservations' => [
            'name' => [
                'en' => 'Approve Reservations',
                'ar' => 'الموافقة على الحجوزات',
            ],
            'group' => 'reservations',
        ],

        // Units module
        'view units' => [
            'name' => [
                'en' => 'View Units',
                'ar' => 'عرض الوحدات',
            ],
            'group' => 'units',
        ],
        'create units' => [
            'name' => [
                'en' => 'Create Units',
                'ar' => 'إنشاء وحدات',
            ],
            'group' => 'units',
        ],
        'update units' => [
            'name' => [
                'en' => 'Update Units',
                'ar' => 'تحديث الوحدات',
            ],
            'group' => 'units',
        ],
        'delete units' => [
            'name' => [
                'en' => 'Delete Units',
                'ar' => 'حذف الوحدات',
            ],
            'group' => 'units',
        ],
        'status units' => [
            'name' => [
                'en' => 'Change Unit Status',
                'ar' => 'تغيير حالة الوحدة',
            ],
            'group' => 'units',
        ],
        'maintenance units' => [
            'name' => [
                'en' => 'Unit Maintenance',
                'ar' => 'صيانة الوحدة',
            ],
            'group' => 'units',
        ],
        'cleaning units' => [
            'name' => [
                'en' => 'Unit Cleaning',
                'ar' => 'تنظيف الوحدة',
            ],
            'group' => 'units',
        ],
        'export units' => [
            'name' => [
                'en' => 'Export Units',
                'ar' => 'تصدير الوحدات',
            ],
            'group' => 'units',
        ],

        // Transactions module
        'view transactions' => [
            'name' => [
                'en' => 'View Transactions',
                'ar' => 'عرض المعاملات',
            ],
            'group' => 'transactions',
        ],
        'create transactions' => [
            'name' => [
                'en' => 'Create Transactions',
                'ar' => 'إنشاء المعاملات',
            ],
            'group' => 'transactions',
        ],
        'update transactions' => [
            'name' => [
                'en' => 'Update Transactions',
                'ar' => 'تحديث المعاملات',
            ],
            'group' => 'transactions',
        ],
        'delete transactions' => [
            'name' => [
                'en' => 'Delete Transactions',
                'ar' => 'حذف المعاملات',
            ],
            'group' => 'transactions',
        ],
        'reverse transactions' => [
            'name' => [
                'en' => 'Reverse Transactions',
                'ar' => 'عكس المعاملات',
            ],
            'group' => 'transactions',
        ],
        'export transactions' => [
            'name' => [
                'en' => 'Export Transactions',
                'ar' => 'تصدير المعاملات',
            ],
            'group' => 'transactions',
        ],

        // Invoices module
        'view invoices' => [
            'name' => [
                'en' => 'View Invoices',
                'ar' => 'عرض الفواتير',
            ],
            'group' => 'invoices',
        ],
        'create invoices' => [
            'name' => [
                'en' => 'Create Invoices',
                'ar' => 'إنشاء الفواتير',
            ],
            'group' => 'invoices',
        ],
        'credit-note invoices' => [
            'name' => [
                'en' => 'Issue Credit Notes',
                'ar' => 'إصدار ملاحظات الائتمان',
            ],
            'group' => 'invoices',
        ],
        'send-zatca invoices' => [
            'name' => [
                'en' => 'Send to ZATCA',
                'ar' => 'إرسال إلى الهيئة العامة للزكاة والدخل',
            ],
            'group' => 'invoices',
        ],
        'print invoices' => [
            'name' => [
                'en' => 'Print Invoices',
                'ar' => 'طباعة الفواتير',
            ],
            'group' => 'invoices',
        ],
        'export invoices' => [
            'name' => [
                'en' => 'Export Invoices',
                'ar' => 'تصدير الفواتير',
            ],
            'group' => 'invoices',
        ],

        // Night Audit module
        'view night-audit' => [
            'name' => [
                'en' => 'View Night Audit',
                'ar' => 'عرض تدقيق الليل',
            ],
            'group' => 'night-audit',
        ],
        'run night-audit' => [
            'name' => [
                'en' => 'Run Night Audit',
                'ar' => 'تشغيل تدقيق الليل',
            ],
            'group' => 'night-audit',
        ],
        'rerun night-audit' => [
            'name' => [
                'en' => 'Re-run Night Audit',
                'ar' => 'إعادة تشغيل تدقيق الليل',
            ],
            'group' => 'night-audit',
        ],
        'close night-audit' => [
            'name' => [
                'en' => 'Close Night Audit',
                'ar' => 'إغلاق تدقيق الليل',
            ],
            'group' => 'night-audit',
        ],
        'reopen night-audit' => [
            'name' => [
                'en' => 'Re-open Night Audit',
                'ar' => 'إعادة فتح تدقيق الليل',
            ],
            'group' => 'night-audit',
        ],
        'export night-audit' => [
            'name' => [
                'en' => 'Export Night Audit',
                'ar' => 'تصدير تدقيق الليل',
            ],
            'group' => 'night-audit',
        ],

        // Settings module
        'view settings' => [
            'name' => [
                'en' => 'View Settings',
                'ar' => 'عرض الإعدادات',
            ],
            'group' => 'settings',
        ],
        'update settings' => [
            'name' => [
                'en' => 'Update Settings',
                'ar' => 'تحديث الإعدادات',
            ],
            'group' => 'settings',
        ],

        // Users module
        'view users' => [
            'name' => [
                'en' => 'View Users',
                'ar' => 'عرض المستخدمين',
            ],
            'group' => 'users',
        ],
        'create users' => [
            'name' => [
                'en' => 'Create Users',
                'ar' => 'إنشاء مستخدمين',
            ],
            'group' => 'users',
        ],
        'update users' => [
            'name' => [
                'en' => 'Update Users',
                'ar' => 'تحديث المستخدمين',
            ],
            'group' => 'users',
        ],
        'delete users' => [
            'name' => [
                'en' => 'Delete Users',
                'ar' => 'حذف المستخدمين',
            ],
            'group' => 'users',
        ],
        'impersonate users' => [
            'name' => [
                'en' => 'Impersonate Users',
                'ar' => 'تقمص هوية المستخدمين',
            ],
            'group' => 'users',
        ],

        // Integrations module
        'view integrations' => [
            'name' => [
                'en' => 'View Integrations',
                'ar' => 'عرض التكامل',
            ],
            'group' => 'integrations',
        ],
        'update integrations' => [
            'name' => [
                'en' => 'Update Integrations',
                'ar' => 'تحديث التكامل',
            ],
            'group' => 'integrations',
        ],
        'test integrations' => [
            'name' => [
                'en' => 'Test Integrations',
                'ar' => 'اختبار التكامل',
            ],
            'group' => 'integrations',
        ],

        // Reports module
        'view reports' => [
            'name' => [
                'en' => 'View Reports',
                'ar' => 'عرض التقارير',
            ],
            'group' => 'reports',
        ],
        'export reports' => [
            'name' => [
                'en' => 'Export Reports',
                'ar' => 'تصدير التقارير',
            ],
            'group' => 'reports',
        ],

        // Guests module
        'view guests' => [
            'name' => [
                'en' => 'View Guests',
                'ar' => 'عرض الضيوف',
            ],
            'group' => 'guests',
        ],
        'create guests' => [
            'name' => [
                'en' => 'Create Guests',
                'ar' => 'إنشاء ضيوف',
            ],
            'group' => 'guests',
        ],
        'update guests' => [
            'name' => [
                'en' => 'Update Guests',
                'ar' => 'تحديث الضيوف',
            ],
            'group' => 'guests',
        ],
        'delete guests' => [
            'name' => [
                'en' => 'Delete Guests',
                'ar' => 'حذف الضيوف',
            ],
            'group' => 'guests',
        ],
        'export guests' => [
            'name' => [
                'en' => 'Export Guests',
                'ar' => 'تصدير الضيوف',
            ],
            'group' => 'guests',
        ],

        // Companies module
        'view companies' => [
            'name' => [
                'en' => 'View Companies',
                'ar' => 'عرض الشركات',
            ],
            'group' => 'companies',
        ],
        'create companies' => [
            'name' => [
                'en' => 'Create Companies',
                'ar' => 'إنشاء شركات',
            ],
            'group' => 'companies',
        ],
        'update companies' => [
            'name' => [
                'en' => 'Update Companies',
                'ar' => 'تحديث الشركات',
            ],
            'group' => 'companies',
        ],
        'delete companies' => [
            'name' => [
                'en' => 'Delete Companies',
                'ar' => 'حذف الشركات',
            ],
            'group' => 'companies',
        ],
        'export companies' => [
            'name' => [
                'en' => 'Export Companies',
                'ar' => 'تصدير الشركات',
            ],
            'group' => 'companies',
        ],

        // Services module
        'view services' => [
            'name' => [
                'en' => 'View Services',
                'ar' => 'عرض الخدمات',
            ],
            'group' => 'services',
        ],
        'create services' => [
            'name' => [
                'en' => 'Create Services',
                'ar' => 'إنشاء خدمات',
            ],
            'group' => 'services',
        ],
        'update services' => [
            'name' => [
                'en' => 'Update Services',
                'ar' => 'تحديث الخدمات',
            ],
            'group' => 'services',
        ],
        'delete services' => [
            'name' => [
                'en' => 'Delete Services',
                'ar' => 'حذف الخدمات',
            ],
            'group' => 'services',
        ],
        'export services' => [
            'name' => [
                'en' => 'Export Services',
                'ar' => 'تصدير الخدمات',
            ],
            'group' => 'services',
        ],

        // Promissory notes module
        'view promissory notes' => [
            'name' => [
                'en' => 'View Promissory Notes',
                'ar' => 'عرض الأوراق التجارية',
            ],
            'group' => 'promissory-notes',
        ],
        'create promissory notes' => [
            'name' => [
                'en' => 'Create Promissory Notes',
                'ar' => 'إنشاء أوراق تجارية',
            ],
            'group' => 'promissory-notes',
        ],
        'update promissory notes' => [
            'name' => [
                'en' => 'Update Promissory Notes',
                'ar' => 'تحديث الأوراق التجارية',
            ],
            'group' => 'promissory-notes',
        ],
        'delete promissory notes' => [
            'name' => [
                'en' => 'Delete Promissory Notes',
                'ar' => 'حذف الأوراق التجارية',
            ],
            'group' => 'promissory-notes',
        ],
        'collect promissory notes' => [
            'name' => [
                'en' => 'Collect Promissory Notes',
                'ar' => 'تحصيل الأوراق التجارية',
            ],
            'group' => 'promissory-notes',
        ],
        'export promissory notes' => [
            'name' => [
                'en' => 'Export Promissory Notes',
                'ar' => 'تصدير الأوراق التجارية',
            ],
            'group' => 'promissory-notes',
        ],
        'restore promissory notes' => [
            'name' => [
                'en' => 'Restore Promissory Notes',
                'ar' => 'استعادة الأوراق التجارية',
            ],
            'group' => 'promissory-notes',
        ],
        'force delete promissory notes' => [
            'name' => [
                'en' => 'Force Delete Promissory Notes',
                'ar' => 'حذف دائم للأوراق التجارية',
            ],
            'group' => 'promissory-notes',
        ],

        // Sidebar & PMS Core Permissions (New)
        'dashboard.view' => ['name' => ['en' => 'View Main Dashboard', 'ar' => 'عرض لوحة التحكم الرئيسية'], 'group' => 'dashboard'],
        'dashboard.occupancy' => ['name' => ['en' => 'View Occupancy Dashboard', 'ar' => 'عرض لوحة تحكم الإشغال'], 'group' => 'dashboard'],
        'dashboard.revenue' => ['name' => ['en' => 'View Revenue Dashboard', 'ar' => 'عرض لوحة تحكم الإيرادات'], 'group' => 'dashboard'],
        'reservations.view' => ['name' => ['en' => 'View Reservations', 'ar' => 'عرض الحجوزات'], 'group' => 'reservations'],
        'reservations.create' => ['name' => ['en' => 'Create Reservations', 'ar' => 'إنشاء الحجوزات'], 'group' => 'reservations'],
        'reservations.quick_book' => ['name' => ['en' => 'Quick Booking', 'ar' => 'حجز سريع'], 'group' => 'reservations'],
        'reservations.noshow' => ['name' => ['en' => 'Handle No-show', 'ar' => 'معالجة عدم الحضور'], 'group' => 'reservations'],
        'front_desk.checkin' => ['name' => ['en' => 'Handle Check-in', 'ar' => 'معالجة تسجيل الوصول'], 'group' => 'front_desk'],
        'front_desk.checkout' => ['name' => ['en' => 'Handle Check-out', 'ar' => 'معالجة تسجيل المغادرة'], 'group' => 'front_desk'],
        'front_desk.walkin' => ['name' => ['en' => 'Walk-in Booking', 'ar' => 'حجز مباشر'], 'group' => 'front_desk'],
        'front_desk.registration' => ['name' => ['en' => 'Guest Registration', 'ar' => 'تسجيل الضيوف'], 'group' => 'front_desk'],
        'front_desk.room_assign' => ['name' => ['en' => 'Room Assignment', 'ar' => 'تخصيص الغرف'], 'group' => 'front_desk'],
        'front_desk.early_checkin' => ['name' => ['en' => 'Early Check-in Charge', 'ar' => 'رسوم تسجيل الوصول المبكر'], 'group' => 'front_desk'],
        'front_desk.late_checkout' => ['name' => ['en' => 'Late Checkout Charge', 'ar' => 'رسوم تسجيل المغادرة المتأخر'], 'group' => 'front_desk'],
        'front_desk.balance_transfer' => ['name' => ['en' => 'Balance Transfer', 'ar' => 'تحويل الرصيد'], 'group' => 'front_desk'],
        'units.view' => ['name' => ['en' => 'View Units', 'ar' => 'عرض الوحدات'], 'group' => 'units'],
        'unit_categories.view' => ['name' => ['en' => 'View Unit Categories', 'ar' => 'عرض فئات الوحدات'], 'group' => 'units'],
        'housekeeping.board' => ['name' => ['en' => 'Housekeeping Board', 'ar' => 'لوحة التدبير المنزلي'], 'group' => 'housekeeping'],
        'maintenances.view' => ['name' => ['en' => 'View Maintenance', 'ar' => 'عرض الصيانة'], 'group' => 'housekeeping'],
        'housekeeping.status_log' => ['name' => ['en' => 'Room Status Log', 'ar' => 'سجل حالة الغرف'], 'group' => 'housekeeping'],
        'unit_features.view' => ['name' => ['en' => 'View Unit Features', 'ar' => 'عرض مميزات الوحدات'], 'group' => 'units'],
        'customers.view' => ['name' => ['en' => 'View Customers', 'ar' => 'عرض العملاء'], 'group' => 'guests'],
        'companies.view' => ['name' => ['en' => 'View Companies', 'ar' => 'عرض الشركات'], 'group' => 'guests'],
        'company_groups.view' => ['name' => ['en' => 'View Company Groups', 'ar' => 'عرض مجموعات الشركات'], 'group' => 'guests'],
        'blocked_guests.view' => ['name' => ['en' => 'View Blocked Guests', 'ar' => 'عرض الضيوف المحظورين'], 'group' => 'guests'],
        'turnaway_logs.view' => ['name' => ['en' => 'View Turnaway Logs', 'ar' => 'عرض سجلات الرفض'], 'group' => 'guests'],
        'pos.view' => ['name' => ['en' => 'View POS', 'ar' => 'عرض نقاط البيع'], 'group' => 'pos'],
        'service_categories.view' => ['name' => ['en' => 'View Service Categories', 'ar' => 'عرض فئات الخدمات'], 'group' => 'pos'],
        'services.view' => ['name' => ['en' => 'View Services', 'ar' => 'عرض الخدمات'], 'group' => 'pos'],
        'pos.create' => ['name' => ['en' => 'POS Sale', 'ar' => 'بيع نقاط البيع'], 'group' => 'pos'],
        'service_logs.view' => ['name' => ['en' => 'View Service Logs', 'ar' => 'عرض سجلات الخدمات'], 'group' => 'pos'],
        'pos.quick_payment' => ['name' => ['en' => 'Quick Payment', 'ar' => 'الدفع السريع'], 'group' => 'pos'],
        'reservation_services.view' => ['name' => ['en' => 'View Reservation Services', 'ar' => 'عرض خدمات الحجز'], 'group' => 'pos'],
        'transactions.view' => ['name' => ['en' => 'View Transactions', 'ar' => 'عرض المعاملات'], 'group' => 'finance'],
        'receipts.view' => ['name' => ['en' => 'View Receipts', 'ar' => 'عرض الإيصالات'], 'group' => 'finance'],
        'receipts.create' => ['name' => ['en' => 'Create Receipts', 'ar' => 'إنشاء الإيصالات'], 'group' => 'finance'],
        'receipts.edit' => ['name' => ['en' => 'Edit Receipts', 'ar' => 'تحرير الإيصالات'], 'group' => 'finance'],
        'receipts.delete' => ['name' => ['en' => 'Delete Receipts', 'ar' => 'حذف الإيصالات'], 'group' => 'finance'],
        'receipts.cancel' => ['name' => ['en' => 'Cancel Receipts', 'ar' => 'إلغاء الإيصالات'], 'group' => 'finance'],
        'receipts.print' => ['name' => ['en' => 'Print Receipts', 'ar' => 'طباعة الإيصالات'], 'group' => 'finance'],
        'receipts.export' => ['name' => ['en' => 'Export Receipts', 'ar' => 'تصدير الإيصالات'], 'group' => 'finance'],
        'payments.view' => ['name' => ['en' => 'View Payments', 'ar' => 'عرض المدفوعات'], 'group' => 'finance'],
        'payments.create' => ['name' => ['en' => 'Create Payments', 'ar' => 'إنشاء المدفوعات'], 'group' => 'finance'],
        'payments.edit' => ['name' => ['en' => 'Edit Payments', 'ar' => 'تعديل المدفوعات'], 'group' => 'finance'],
        'payments.delete' => ['name' => ['en' => 'Delete Payments', 'ar' => 'حذف المدفوعات'], 'group' => 'finance'],
        'payments.complete' => ['name' => ['en' => 'Complete Payments', 'ar' => 'إكمال المدفوعات'], 'group' => 'finance'],
        'payments.reverse' => ['name' => ['en' => 'Reverse Payments', 'ar' => 'استعادة المدفوعات'], 'group' => 'finance'],
        'payments.print' => ['name' => ['en' => 'Print Payments', 'ar' => 'طباعة المدفوعات'], 'group' => 'finance'],
        'payment.export' => ['name' => ['en' => 'Export Payments', 'ar' => 'تصدير المدفوعات'], 'group' => 'finance'],
        'invoice.view' => ['name' => ['en' => 'View Invoices', 'ar' => 'عرض الفواتير'], 'group' => 'finance'],
        'invoice.create' => ['name' => ['en' => 'Create Invoices', 'ar' => 'إنشاء الفواتير'], 'group' => 'finance'],
        'invoice.edit' => ['name' => ['en' => 'Edit Invoices', 'ar' => 'تحرير الفواتير'], 'group' => 'finance'],
        'invoice.delete' => ['name' => ['en' => 'Delete Invoices', 'ar' => 'حذف الفواتير'], 'group' => 'finance'],
        'invoice.send' => ['name' => ['en' => 'Send Invoices', 'ar' => 'إرسال الفواتير'], 'group' => 'finance'],
        'invoice.send_zatca' => ['name' => ['en' => 'Send to ZATCA', 'ar' => 'إرسال إلى ZATCA'], 'group' => 'finance'],
        'invoice.void' => ['name' => ['en' => 'Void Invoices', 'ar' => 'إلغاء الفواتير'], 'group' => 'finance'],
        'invoice.print' => ['name' => ['en' => 'Print Invoices', 'ar' => 'طباعة الفواتير'], 'group' => 'finance'],
        'invoice.export' => ['name' => ['en' => 'Export Invoices', 'ar' => 'تصدير الفواتير'], 'group' => 'finance'],
        'night_audit.view' => ['name' => ['en' => 'View Night Audit', 'ar' => 'عرض التدقيق الليلي'], 'group' => 'night_audit'],
        'credit_notes.view' => ['name' => ['en' => 'View Credit Notes', 'ar' => 'عرض إشعارات الخصم'], 'group' => 'finance'],
        'promissories.view' => ['name' => ['en' => 'View Promissories', 'ar' => 'عرض السندات لأمر'], 'group' => 'finance'],
        'cashier_shifts.view' => ['name' => ['en' => 'View Cashier Shifts', 'ar' => 'عرض ورديات الصندوق'], 'group' => 'finance'],
        'banks.view' => ['name' => ['en' => 'View Banks', 'ar' => 'عرض البنوك'], 'group' => 'finance'],
        'finance.paid_outs' => ['name' => ['en' => 'Paid-outs', 'ar' => 'المدفوعات النقدية'], 'group' => 'finance'],
        'finance.corrections' => ['name' => ['en' => 'Corrections / Reversals', 'ar' => 'التصحيحات / الارتجاع'], 'group' => 'finance'],
        'night_audit.view' => ['name' => ['en' => 'View Night Audit', 'ar' => 'عرض التدقيق الليلي'], 'group' => 'night_audit'],
        'night_audit.run' => ['name' => ['en' => 'Run Night Audit', 'ar' => 'تشغيل التدقيق الليلي'], 'group' => 'night_audit'],
        'night_audit.logs' => ['name' => ['en' => 'View Audit History', 'ar' => 'عرض سجل التدقيق'], 'group' => 'night_audit'],
        'night_audit.snapshots' => ['name' => ['en' => 'View Occupancy Snapshots', 'ar' => 'عرض لقطات الإشغال'], 'group' => 'night_audit'],
        'no_show_rules.manage' => ['name' => ['en' => 'Manage No-Show Rules', 'ar' => 'إدارة قواعد عدم الحضور'], 'group' => 'night_audit'],
        'night_audit.process_noshow' => ['name' => ['en' => 'Process No-Shows', 'ar' => 'معالجة عدم الحضور'], 'group' => 'night_audit'],
        'night_audit.frozen' => ['name' => ['en' => 'View Frozen Transactions', 'ar' => 'عرض المعاملات المجمدة'], 'group' => 'night_audit'],
        'night_audit.business_date' => ['name' => ['en' => 'Business Date Transactions', 'ar' => 'معاملات تاريخ العمل'], 'group' => 'night_audit'],
         'reports.daily' => ['name' => ['en' => 'Daily Report', 'ar' => 'التقرير اليومي'], 'group' => 'reports'],
         'reports.occupancy' => ['name' => ['en' => 'Occupancy Report', 'ar' => 'تقرير الإشغال'], 'group' => 'reports'],
         'reports.revenue' => ['name' => ['en' => 'Revenue Report', 'ar' => 'تقرير الإيرادات'], 'group' => 'reports'],
         'reports.adr' => ['name' => ['en' => 'ADR / RevPAR', 'ar' => 'ADR / RevPAR'], 'group' => 'reports'],
         'reports.noshow' => ['name' => ['en' => 'No-Show Report', 'ar' => 'تقرير عدم الحضور'], 'group' => 'reports'],
         'reports.cancellation' => ['name' => ['en' => 'Cancellation Report', 'ar' => 'تقرير الإلغاء'], 'group' => 'reports'],
         'reports.commission' => ['name' => ['en' => 'Commission Report', 'ar' => 'تقرير العمولات'], 'group' => 'reports'],
         'reports.paidouts' => ['name' => ['en' => 'Paid-Outs Report', 'ar' => 'تقرير الصرفيات'], 'group' => 'reports'],
         'reports.turnaway' => ['name' => ['en' => 'Turnaway Report', 'ar' => 'تقرير الرفض'], 'group' => 'reports'],
         'reports.source_performance' => ['name' => ['en' => 'Source Performance', 'ar' => 'أداء المصادر'], 'group' => 'reports'],
         'reports.company_ar' => ['name' => ['en' => 'Company AR', 'ar' => 'الحسابات المدينة للشركات'], 'group' => 'reports'],
         'reports.trial_balance' => ['name' => ['en' => 'Trial Balance', 'ar' => 'ميزان المراجعة'], 'group' => 'reports'],
         'reports.forecast' => ['name' => ['en' => 'Forecast Report', 'ar' => 'تقرير التوقعات'], 'group' => 'reports'],
         'reports.housekeeping_discrepancy' => ['name' => ['en' => 'Housekeeping Discrepancy', 'ar' => 'تقرير اختلافات housekeeping'], 'group' => 'reports'],
         'sources.view' => ['name' => ['en' => 'View Sources', 'ar' => 'عرض مصادر الحجز'], 'group' => 'marketing'],
        'offers.view' => ['name' => ['en' => 'View Offers', 'ar' => 'عرض العروض'], 'group' => 'marketing'],
        'special_prices.view' => ['name' => ['en' => 'View Special Prices', 'ar' => 'عرض أسعار خاصة'], 'group' => 'marketing'],
        'promo_codes.view' => ['name' => ['en' => 'View Promo Codes', 'ar' => 'عرض أكواد الخصم'], 'group' => 'marketing'],
        'vouchers.view' => ['name' => ['en' => 'View Vouchers', 'ar' => 'عرض القسائم'], 'group' => 'marketing'],
        'revenue.pricing_preview' => ['name' => ['en' => 'Pricing Preview', 'ar' => 'معاينة التسعير'], 'group' => 'marketing'],
        'integrations.view' => ['name' => ['en' => 'View Integrations', 'ar' => 'عرض التكاملات'], 'group' => 'integrations'],
        'integrations.logs' => ['name' => ['en' => 'View Integration Logs', 'ar' => 'عرض سجلات التكامل'], 'group' => 'integrations'],
        'integrations.sta_ah' => ['name' => ['en' => 'STA AH Integration', 'ar' => 'تكامل STA AH'], 'group' => 'integrations'],
        'integrations.qoyod' => ['name' => ['en' => 'Qoyod Integration', 'ar' => 'تكامل قيود'], 'group' => 'integrations'],
        'integrations.jawaly' => ['name' => ['en' => 'Jawaly SMS Integration', 'ar' => 'تكامل جوالي SMS'], 'group' => 'integrations'],
        'integrations.api' => ['name' => ['en' => 'API Consumers', 'ar' => 'مستهلكي API'], 'group' => 'integrations'],
        'integrations.webhooks' => ['name' => ['en' => 'Webhook Calls', 'ar' => 'نداءات Webhook'], 'group' => 'integrations'],
        'teams.view' => ['name' => ['en' => 'View Team / Hotel', 'ar' => 'عرض الفريق / الفندق'], 'group' => 'settings'],
        'users.view' => ['name' => ['en' => 'View Users', 'ar' => 'عرض المستخدمين'], 'group' => 'settings'],
        'permissions.view' => ['name' => ['en' => 'View Permissions', 'ar' => 'عرض الصلاحيات'], 'group' => 'settings'],
        'early_late_charge.view' => ['name' => ['en' => 'View Early/Late Charges', 'ar' => 'عرض رسوم المبكر/المتأخر'], 'group' => 'settings'],
        'breakfast_prices.view' => ['name' => ['en' => 'View Breakfast Prices', 'ar' => 'عرض أسعار الإفطار'], 'group' => 'settings'],
        'settings.vat' => ['name' => ['en' => 'VAT Settings', 'ar' => 'إعدادات الضريبة'], 'group' => 'settings'],
        'settings.payment' => ['name' => ['en' => 'Payment Settings', 'ar' => 'إعدادات الدفع'], 'group' => 'settings'],
        'settings.counters' => ['name' => ['en' => 'Auto Numbers', 'ar' => 'الأرقام التلقائية'], 'group' => 'settings'],
        'media.view' => ['name' => ['en' => 'View Media Library', 'ar' => 'عرض مكتبة الوسائط'], 'group' => 'settings'],
        'system.activity' => ['name' => ['en' => 'View Activity Log', 'ar' => 'عرض سجل النشاط'], 'group' => 'system'],
        'system.jobs' => ['name' => ['en' => 'View System Jobs', 'ar' => 'عرض مهام النظام'], 'group' => 'system'],
        'system.telescope' => ['name' => ['en' => 'View Telescope', 'ar' => 'عرض تليسكوب'], 'group' => 'system'],
        'system.audit' => ['name' => ['en' => 'View Audit Trail', 'ar' => 'عرض سجل التدقيق'], 'group' => 'system'],
    ],

    'permission_model' => \Pktharindu\NovaPermissions\Permission::class,

    'role_model' => \Pktharindu\NovaPermissions\Role::class,

    'role_permission_table' => 'role_permission',

    'role_foreign_key' => 'role_id',

    'permission_foreign_key' => 'permission_slug',
];