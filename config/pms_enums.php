<?php

return [
    'reservation_statuses' => [
        'confirmed' => ['en' => 'Confirmed', 'ar' => 'مؤكد'],
        'canceled' => ['en' => 'Canceled', 'ar' => 'ملغي'],
        'awaiting-confirmation' => ['en' => 'Awaiting Confirmation', 'ar' => 'بانتظار التأكيد'],
        'awaiting-payment' => ['en' => 'Awaiting Payment', 'ar' => 'بانتظار الدفع'],
        'timeout' => ['en' => 'Timeout', 'ar' => 'انتهت المهلة'],
        'hold' => ['en' => 'Hold', 'ar' => 'حجز مؤقت'],
    ],
    'unit_statuses' => [
        1 => ['en' => 'Maintenance', 'ar' => 'صيانة'],
        2 => ['en' => 'Cleaning', 'ar' => 'تنظيف'],
        3 => ['en' => 'Out of Order', 'ar' => 'خارج الخدمة'],
        4 => ['en' => 'Available', 'ar' => 'متاح'],
        5 => ['en' => 'Occupied', 'ar' => 'مشغول'],
        6 => ['en' => 'Booked', 'ar' => 'محجوز'],
        7 => ['en' => 'Inspection', 'ar' => 'فحص'],
    ],
    'payment_methods' => [
        'Cash' => ['en' => 'Cash', 'ar' => 'نقدي'],
        'Mada' => ['en' => 'Mada', 'ar' => 'مدى'],
        'Visa' => ['en' => 'Visa', 'ar' => 'فيزا'],
        'BankTransfer' => ['en' => 'Bank Transfer', 'ar' => 'تحويل بنكي'],
        'Promissory' => ['en' => 'Promissory', 'ar' => 'سند لأمر'],
    ],
    'customer_types' => [
        1 => ['en' => 'Individual', 'ar' => 'فرد'],
        2 => ['en' => 'Corporate', 'ar' => 'شركة'],
        3 => ['en' => 'Agent', 'ar' => 'وكيل'],
    ],
    'id_types' => [
        1 => ['en' => 'National ID', 'ar' => 'هوية وطنية'],
        2 => ['en' => 'Iqama', 'ar' => 'إقامة'],
        3 => ['en' => 'Passport', 'ar' => 'جواز سفر'],
        4 => ['en' => 'GCC ID', 'ar' => 'هوية خليجية'],
    ],
];
