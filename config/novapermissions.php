<?php

return [
    /*
    |--------------------------------------------------------------------------
    | User model class
    |--------------------------------------------------------------------------
    */

    'userModel' => 'App\User',

    /*
    |--------------------------------------------------------------------------
    | Nova User resource tool class
    |--------------------------------------------------------------------------
    */

    'userResource' => 'App\Nova\User',

    /*
    |--------------------------------------------------------------------------
    | The group associated with the resource
    |--------------------------------------------------------------------------
    */

    'roleResourceGroup' => 'Other',

    /*
    |--------------------------------------------------------------------------
    | Application Permissions
    |--------------------------------------------------------------------------
    */

    'permissions' => [


        'change reservation price before checkin' => [
            'display_name' => 'Change Reservation Price',
            'description'  => 'Can change reservation price',
            'group'        => 'Before Checkin',
        ],
        'change reservation unit before checkin' => [
            'display_name' => 'Change Reservation Unit',
            'description'  => 'Can change reservation unit',
            'group'        => 'Before Checkin',
        ],
        'change reservation source before checkin' => [
            'display_name' => 'Change Reservation Source',
            'description'  => 'Can change reservation source',
            'group'        => 'Before Checkin',
        ],

        'extend reservation before checkin' => [
            'display_name' => 'Extend Reservation',
            'description'  => 'Can extend reservation',
            'group'        => 'Before Checkin',
        ],
        'change reservation rent type before checkin' => [
            'display_name' => 'Change Reservation Rent Type',
            'description'  => 'Can change reservation rent type',
            'group'        => 'Before Checkin',
        ],

        'change reservation calendar date before checkin' => [
            'display_name' => 'Change Reservation Date',
            'description'  => 'Can change reservation date',
            'group'        => 'Before Checkin',
        ],

        'cancel reservation before checkin' => [
            'display_name' => 'Cancel Reservation',
            'description'  => 'Can cancel reservation',
            'group'        => 'Before Checkin',
        ],


        'change reservation price after checkin' => [
            'display_name' => 'Change Reservation Price',
            'description'  => 'Can change reservation price',
            'group'        => 'After Checkin ( before night run )',
        ],

        'change reservation unit after checkin' => [
            'display_name' => 'Change Reservation Unit',
            'description'  => 'Can change reservation unit',
            'group'        => 'After Checkin ( before night run )',
        ],

        'change reservation source after checkin' => [
            'display_name' => 'Change Reservation Source',
            'description'  => 'Can change reservation source',
            'group'        => 'After Checkin ( before night run )',
        ],

        'extend reservation after checkin' => [
            'display_name' => 'Extend Reservation',
            'description'  => 'Can extend reservation',
            'group'        => 'After Checkin ( before night run )',
        ],

        'change reservation rent type after checkin' => [
            'display_name' => 'Change Reservation Rent Type',
            'description'  => 'Can change reservation rent type',
            'group'        => 'After Checkin ( before night run )',
        ],

        'change reservation calendar date after checkin' => [
            'display_name' => 'Change Reservation Date',
            'description'  => 'Can change reservation date',
            'group'        => 'After Checkin ( before night run )',
        ],


        'cancel reservation after checkin' => [
            'display_name' => 'Cancel Reservation',
            'description'  => 'Can cancel reservation',
            'group'        => 'After Checkin ( before night run )',
        ],


        'change reservation price before night run' => [
            'display_name' => 'Change Reservation Price',
            'description'  => 'Can change reservation price',
            'group'        => 'After Checkin ( after night run )',
        ],
        'change reservation unit before night run' => [
            'display_name' => 'Change Reservation Unit',
            'description'  => 'Can change reservation unit',
            'group'        => 'After Checkin ( after night run )',
        ],
        'change reservation source before night run' => [
            'display_name' => 'Change Reservation Source',
            'description'  => 'Can change reservation source',
            'group'        => 'After Checkin ( after night run )',
        ],

        'extend reservation before night run' => [
            'display_name' => 'Extend Reservation',
            'description'  => 'Can extend reservation',
            'group'        => 'After Checkin ( after night run )',
        ],
        'change reservation rent type before night run' => [
            'display_name' => 'Change Reservation Rent Type',
            'description'  => 'Can change reservation rent type',
            'group'        => 'After Checkin ( after night run )',
        ],

        'change reservation calendar date before night run' => [
            'display_name' => 'Change Reservation Date',
            'description'  => 'Can change reservation date',
            'group'        => 'After Checkin ( after night run )',
        ],

        'cancel reservation before night run' => [
            'display_name' => 'Cancel Reservation',
            'description'  => 'Can cancel reservation',
            'group'        => 'After Checkin ( after night run )',
        ],



        'change service price' => [
            'display_name' => 'Change Service Price',
            'description'  => 'Can change service price',
            'group'        => 'Special Permissions',
        ],

        'open closed contract' => [
            'display_name' => 'Open Closed Contract',
            'description'  => 'Can open closed contract',
            'group'        => 'Special Permissions',
        ],

        'change transactions date' => [
            'display_name' => 'Change Transactions Date',
            'description'  => 'change transactions date',
            'group'        => 'Special Permissions',
        ],

        // 'reservation price' => [
        //     'display_name' => 'Edit Reservation Price',
        //     'description'  => 'Can edit reservation price',
        //     'group'        => 'Special Permissions',
        // ],

        'liquidation of dues before departure' => [
            'display_name' => 'Checkout without Liquidation',
            'description'  => 'Checkout without Liquidation',
            'group'        => 'Special Permissions',
        ],
        'booking without min price' => [
            'display_name' => 'booking without min price',
            'description'  => 'booking without min price',
            'group'        => 'Special Permissions',
        ],

        'show statistics in reservation management' => [
            'display_name' => 'Show Statistics in reservation management',
            'description'  => 'booking without min price',
            'group'        => 'Special Permissions',
        ],

        'show safe balance' => [
            'display_name' => 'Show safe balance',
            'description'  => 'Show safe balance',
            'group'        => 'Special Permissions',
        ],

        // 'change unit' => [
        //     'display_name' => 'Change Unit',
        //     'description'  => 'Change Unit',
        //     'group'        => 'Special Permissions',
        // ],
        'checkin debtor customer' => [
            'display_name' => 'Checkin if customer is debtor',
            'description'  => 'Checkin if customer is debtor',
            'group'        => 'Special Permissions',
        ],

        'add invoice' => [
            'display_name' => 'Add Invoice',
            'description'  => 'Add Invoice',
            'group'        => 'Special Permissions',
        ],

        'add credit note' => [
            'display_name' => 'Add Credit Note',
            'description'  => 'Add Credit Note',
            'group'        => 'Special Permissions',
        ],

        'sync data to mytravel' => [
            'display_name' => 'Sync Data To Mytravel',
            'description'  => 'Can Sync Data To Mytravel',
            'group'        => 'Special Permissions',
        ],


        'view reservations' => [
            'display_name' => 'View reservations',
            'description'  => 'Can view reservations',
            'group'        => 'Reservation',
        ],
        'create reservations' => [
            'display_name' => 'Create reservations',
            'description'  => 'Can view reservations',
            'group'        => 'Reservation',
        ],
        // 'edit reservations' => [
        //     'display_name' => 'Edit reservations',
        //     'description'  => 'Can view reservations',
        //     'group'        => 'Reservation',
        // ],
        // 'edit reservations after checkin' => [
        //     'display_name' => 'Edit reservations after checkin',
        //     'description'  => 'Can edit reservations after checkin',
        //     'group'        => 'Reservation',
        // ],
        // 'extend reservations' => [
        //     'display_name' => 'Extend reservations',
        //     'description'  => 'Can extend reservations',
        //     'group'        => 'Reservation',
        // ],
        // 'cancel reservations' => [
        //     'display_name' => 'Cancel reservations',
        //     'description'  => 'Can cancel reservations',
        //     'group'        => 'Reservation',
        // ],
        // 'delete reservations' => [
        //     'display_name' => 'Delete reservations',
        //     'description'  => 'Can view reservations',
        //     'group'        => 'Reservation',
        // ],
        'booking past' => [
            'display_name' => 'booking in the past',
            'description'  => 'Booking in the past',
            'group'        => 'Reservation',
        ],

        'liquid reservation with promissory' => [
            'display_name' => 'Liquid Reservation With Promissory',
            'description'  => 'Can Liquid Reservation With Promissory',
            'group'        => 'Reservation',
        ],


        'check-in customer' => [
            'display_name' => 'Check-in Customer',
            'description'  => 'Can check-in customer',
            'group'        => 'Reservation Management',
        ],

        'check-out customer' => [
            'display_name' => 'Check-out Customer',
            'description'  => 'Can check-out customer',
            'group'        => 'Reservation Management',
        ],
        'edit checkin and checkout time' => [
            'display_name' => 'Edit Checkin & Checkout Time',
            'description'  => 'Can Edit Checkin & Checkout Time',
            'group'        => 'Reservation Management',
        ],

        'view contract' => [
            'display_name' => 'View Contract',
            'description'  => 'View Contract',
            'group'        => 'Reservation Management',
        ],
        'edit customer' => [
            'display_name' => 'Edit Customer',
            'description'  => 'Can edit reservation customer',
            'group'        => 'Reservation Management',
        ],
        'add services' => [
            'display_name' => 'Add Services',
            'description'  => 'Can add services to reservation',
            'group'        => 'Reservation Management',
        ],
        'update transactions balance' => [
            'display_name' => 'Update Transactions Balance',
            'description'  => 'Can update transactions balance',
            'group'        => 'Reservation Management',
        ],
        'add guests' => [
            'display_name' => 'Add Guests',
            'description'  => 'Can add guest in reservation',
            'group'        => 'Reservation Guests',
        ],
        'delete guests' => [
            'display_name' => 'Delete Guests',
            'description'  => 'Can delete guest in reservation',
            'group'        => 'Reservation Guests',
        ],
        'view guests' => [
            'display_name' => 'View Guests',
            'description'  => 'Can view guest in reservation',
            'group'        => 'Reservation Guests',
        ],

        'add comments' => [
            'display_name' => 'Add Comments',
            'description'  => 'Can add comments in reservation',
            'group'        => 'Reservation Comments',
        ],
        'delete comments' => [
            'display_name' => 'Delete Comments',
            'description'  => 'Can delete comments in reservation',
            'group'        => 'Reservation Comments',
        ],
        'delete own comments' => [
            'display_name' => 'Delete Own Comments',
            'description'  => 'Can delete خصر comments in reservation',
            'group'        => 'Reservation Comments',
        ],
        'view comments' => [
            'display_name' => 'View Comments',
            'description'  => 'Can view comments in reservation',
            'group'        => 'Reservation Comments',
        ],




        'view statements' => [
            'display_name' => 'View Statements',
            'description'  => 'Can view financial statement in reservation',
            'group'        => 'Reservation Financial Statement',
        ],
        'delete statements' => [
            'display_name' => 'Delete Statements',
            'description'  => 'Can delete statements in reservation',
            'group'        => 'Reservation Financial Statement',
        ],
        'delete own statements' => [
            'display_name' => 'Delete Own Statements',
            'description'  => 'Can delete own statements in reservation',
            'group'        => 'Reservation Financial Statement',
        ],
        'add payments' => [
            'display_name' => 'Add Payment Vouchers',
            'description'  => 'Can add payment vouchers in reservation',
            'group'        => 'Reservation Financial Statement',
        ],
        'add receipts' => [
            'display_name' => 'Add Cash Receipts',
            'description'  => 'Can add cash receipts in reservation',
            'group'        => 'Reservation Financial Statement',
        ],
        'add rebates' => [
            'display_name' => 'Add Rebates',
            'description'  => 'Can add cash rebates in reservation',
            'group'        => 'Reservation Financial Statement',
        ],
        'edit/delete freezed transactions after business day'=> [
            'display_name' => 'Edit/Delete Freezed Transactions After Business Day',
            'description'  => 'Can Edit/Delete Freezed Transactions After Business Day',
            'group'        => 'Reservation Financial Statement',
        ],
        'edit payments' => [
            'display_name' => 'Edit Payment Vouchers',
            'description'  => 'Can Edit payment vouchers in reservation',
            'group'        => 'Reservation Financial Statement',
        ],
        'edit receipts' => [
            'display_name' => 'Edit Cash Receipts',
            'description'  => 'Can Edit cash receipts in reservation',
            'group'        => 'Reservation Financial Statement',
        ],



        // 'view calenders' => [
        //     'display_name' => 'View Calender',
        //     'description'  => 'Can view reservations on calender',
        //     'group'        => 'Calender',
        // ],


        'view units' => [
            'display_name' => 'View Units',
            'description'  => 'Can view unit management',
            'group'        => 'Units',
        ],

        'watch unit housing' => [
            'display_name' => 'Watch Unit Housing',
            'description'  => 'Can view unit management',
            'group'        => 'Units',
        ],


        'view services' => [
            'display_name' => 'View Services',
            'description'  => 'Can view services management',
            'group'        => 'Services',
        ],
        'general seetings'=> [
            'display_name' => 'General Settings',
            'description'  => 'Can view general settings',
            'group'        => 'Settings',
        ],
        'facility settings' => [
            'display_name' => 'Facility Settings',
            'description'  => 'Can view facility settings',
            'group'        => 'Settings',
        ],
        'integration settings' => [
            'display_name' => 'Integration Settings',
            'description'  => 'Can view integration settings',
            'group'        => 'Settings',
        ],
        'user and roles' => [
            'display_name' => 'User & Roles',
            'description'  => 'Can view user and roles management',
            'group'        => 'Settings',
        ],
        'document settings' => [
            'display_name' => 'Document Settings',
            'description'  => 'Can view document settings',
            'group'        => 'Settings',
        ],
        'notification settings' => [
            'display_name' => 'Notification Settings',
            'description'  => 'Can view notification settings',
            'group'        => 'Settings',
        ],
        'finance settings' => [
            'display_name' => 'Finance Settings',
            'description'  => 'Can view finance settings',
            'group'        => 'Settings',
        ],
        'ledger numbers' => [
            'display_name' => 'Ledger Numbers',
            'description'  => 'Can view ledger numbers',
            'group'        => 'Settings',
        ],
        'reservation resource settings' => [
            'display_name' => 'Reservation Resource Settings',
            'description'  => 'Can view reservation resource settings',
            'group'        => 'Settings',
        ],
        'customer groups settings'=> [
            'display_name' => 'Customer Groups Settings',
            'description'  => 'Can view customer groups settings',
            'group'        => 'Settings',
        ],
        'website settings' => [
            'display_name' => 'Website Settings',
            'description'  => 'Can view website settings',
            'group'        => 'Settings',
        ],
        'rating settings' => [
            'display_name' => 'Rating Settings',
            'description'  => 'Can view rating settings',
            'group'        => 'Settings',
        ],
        'services included in the price' => [
            'display_name' => 'Services Included In The Price',
            'description'  => 'Can view services included in the price',
            'group'        => 'Settings',
        ],
        'maintenance settings' => [
            'display_name' => 'Maintenance Settings',
            'description'  => 'Can view maintenance settings',
            'group'        => 'Settings',
        ],
        'view settings' => [
            'display_name' => 'View Settings',
            'description'  => 'Can view settings management',
            'group'        => 'Settings',
        ],
        //Activity Log
        'view activity log' => [
            'display_name' => 'View Activity Log',
            'description'  => 'Can view activity log',
            'group'        => 'Settings',
        ],
        'view reports' => [
            'display_name' => 'View Reports',
            'description'  => 'Can view reports',
            'group'        => 'Reports',
        ],

        'deposit' => [
            'display_name' => 'Deposits',
            'description'  => 'Can view Deposit',
            'group'        => 'Reports',
        ],

        'withdraw' => [
            'display_name' => 'Withdraw',
            'description'  => 'Can view Withdraw',
            'group'        => 'Reports',
        ],

        'safe movement report' => [
            'display_name' => 'Safe Movement Report',
            'description'  => 'Can view Safe Movement Report',
            'group'        => 'Reports',
        ],

        'customer movement report' => [
            'display_name' => 'Customer Movement Report',
            'description'  => 'Can view Customer Movement Report',
            'group'        => 'Reports',
        ],

        'combined occupieds report' => [
            'display_name' => 'Combined Occupancy Report',
            'description'  => 'Can view all hotels associated to this user Occupancy Report',
            'group'        => 'Reports',
        ],

        'services report' => [
            'display_name' => 'Services Report',
            'description'  => 'Can view Services Report',
            'group'        => 'Reports',
        ],

        'monthly report' => [
            'display_name' => 'Monthly Report',
            'description'  => 'Can view Monthly Report',
            'group'        => 'Reports',
        ],

        'units movement report' => [
            'display_name' => 'Units Movement Report',
            'description'  => 'Can view Units Movement Report',
            'group'        => 'Reports',
        ],

        'occupieds report' => [
            'display_name' => 'Occupancy Report',
            'description'  => 'Can view Occupieds Report',
            'group'        => 'Reports',
        ],

        'cleaning movement report' => [
            'display_name' => 'Cleaning Movement Report',
            'description'  => 'Can view Cleaning Movement Report',
            'group'        => 'Reports',
        ],

        'maintenance movement report' => [
            'display_name' => 'Maintenance Movement Report',
            'description'  => 'Can view Maintenance Movement Report',
            'group'        => 'Reports',
        ],

        'reservation transfers' => [
            'display_name' => 'Reservation Transfers',
            'description'  => 'Can view Reservation Transfers',
            'group'        => 'Reports',
        ],

        'revenues taxes fees' => [
            'display_name' => 'Revenues & Taxes , Fees',
            'description'  => 'Can view Revenues & Taxes , Fees',
            'group'        => 'Reports',
        ],

        'reservation resources report' => [
            'display_name' => 'Reservation Resources Report',
            'description'  => 'Can view Reservation Resources Report',
            'group'        => 'Reports',
        ],

        'employee contracts' => [
            'display_name' => 'Employee Contracts',
            'description'  => 'Can view Employee Contracts',
            'group'        => 'Reports',
        ],

        'invoices report' => [
            'display_name' => 'Invoices Report',
            'description'  => 'Can view Invoices Report',
            'group'        => 'Reports',
        ],

        'balady report' => [
            'display_name' => 'Balady Report',
            'description'  => 'Can view Balady Report',
            'group'        => 'Reports',
        ],






        'add customers' => [
            'display_name' => 'Add Customers',
            'description'  => 'Can add customers list',
            'group'        => 'Customers',
        ],

        'view customers' => [
            'display_name' => 'View Customers',
            'description'  => 'Can view customers list',
            'group'        => 'Customers',
        ],

        'manage customers' => [
            'display_name' => 'Edit Customers',
            'description'  => 'Can view customers list',
            'group'        => 'Customers',
        ],


        'delete customers' => [
            'display_name' => 'Delete Customers',
            'description'  => 'Can view customers list',
            'group'        => 'Customers',
        ],



        'view users' => [
            'display_name' => 'View users',
            'description'  => 'Can view users',
            'group'        => 'User',
        ],

        'create users' => [
            'display_name' => 'Create users',
            'description'  => 'Can create users',
            'group'        => 'User',
        ],

        'edit users' => [
            'display_name' => 'Edit users',
            'description'  => 'Can edit users',
            'group'        => 'User',
        ],

        'delete users' => [
            'display_name' => 'Delete users',
            'description'  => 'Can delete users',
            'group'        => 'User',
        ],

        'view roles' => [
            'display_name' => 'View roles',
            'description'  => 'Can view roles',
            'group'        => 'Role',
        ],


        'view financial' => [
            'display_name' => 'View financial',
            'description'  => 'Can view financial',
            'group'        => 'Financial',
        ],
        'create financial' => [
            'display_name' => 'Create financial',
            'description'  => 'Can Create financial',
            'group'        => 'Financial',
        ],
        'edit financial' => [
            'display_name' => 'Edit financial',
            'description'  => 'Can Edit financial',
            'group'        => 'Financial',
        ],
        'delete financial' => [
            'display_name' => 'Delete financial',
            'description'  => 'Can Delete financial',
            'group'        => 'Financial',
        ],
        'new_reservation_notification' => [
            'display_name' => 'When Reservation Created',
            'description'  => 'When Reservation Created',
            'group'        => 'Notifications',
        ],
        'delete_reservation_notification' => [
            'display_name' => 'When Reservation Deleted',
            'description'  => 'When Reservation Deleted',
            'group'        => 'Notifications',
        ],
        'cancel_reservation_notification' => [
            'display_name' => 'When Reservation Canceled',
            'description'  => 'When Reservation Canceled',
            'group'        => 'Notifications',
        ],
        'daily_brief_report_notification' => [
            'display_name' => 'Daily brief report Notification',
            'description'  => 'Can Daily brief report',
            'group'        => 'Notifications',
        ],

        'view notes' => [
            'display_name' => 'View customer notes',
            'description'  => 'Can view customer notes',
            'group'        => 'Customer Notes',
        ],

        'add notes' => [
            'display_name' => 'Add customer notes',
            'description'  => 'Can add customer notes',
            'group'        => 'Customer Notes',
        ],

        'edit notes' => [
            'display_name' => 'Edit customer notes',
            'description'  => 'Can edit customer notes',
            'group'        => 'Customer Notes',
        ],

        'delete notes' => [
            'display_name' => 'Delete customer notes',
            'description'  => 'Can delete customer notes',
            'group'        => 'Customer Notes',
        ],

        'change to under cleaning' => [
            'display_name' => 'Change Unit To Under Cleaning',
            'description'  => 'Can change unit to under cleaning',
            'group'        => 'Maintenance & Cleaning',
        ],

        'change to under maintenance' => [
            'display_name' => 'Change Unit To Under Maintenance',
            'description'  => 'Can change unit to under maintenance',
            'group'        => 'Maintenance & Cleaning',
        ],

        'change to available' => [
            'display_name' => 'Change Unit To Available',
            'description'  => 'Can change unit to available',
            'group'        => 'Maintenance & Cleaning',
        ],



        // //promo code
        // 'view promo codes' => [
        //     'display_name' => 'view promo codes',
        //     'description'  => 'Can view promo codes',
        //     'group'        => 'Promo Code',
        // ],
        // 'view promo code log' => [
        //     'display_name' => 'view promo code log',
        //     'description'  => 'Can view promo code log',
        //     'group'        => 'Promo Code',
        // ],
        // 'add a new promo code' => [
        //     'display_name' => 'add a new promo code',
        //     'description'  => 'Can add a new promo code',
        //     'group'        => 'Promo Code',
        // ],
        // 'edit existing promo code' => [
        //     'display_name' => 'edit existing promo code',
        //     'description'  => 'Can edit existing promo code',
        //     'group'        => 'Promo Code',
        // ],
        // 'delete promo code' => [
        //     'display_name' => 'delete promo code',
        //     'description'  => 'Can delete promo code',
        //     'group'        => 'Promo Code',
        // ],

        'add service transaction from reservation' => [
            'display_name' => 'Add transaction',
            'description'  => 'can add',
            'group'        => 'Services Transactions In Reservation',
        ],

        'edit service transaction from reservation' => [
            'display_name' => 'Edit transaction',
            'description'  => 'can edit',
            'group'        => 'Services Transactions In Reservation',
        ],
        'delete service transaction from reservation' => [
            'display_name' => 'Delete transaction',
            'description'  => 'can delete',
            'group'        => 'Services Transactions In Reservation',
        ],



        'view pos' => [
            'display_name' => 'View POS',
            'description'  => 'Can view pos',
            'group'        => 'POS',
        ],

        'add pos date' => [
            'display_name' => 'Add pos invoice date',
            'description'  => 'Can add pos date',
            'group'        => 'POS',
        ],

        'add service transaction from pos' => [
            'display_name' => 'Add transaction',
            'description'  => 'can add',
            'group'        => 'POS',
        ],

        'edit service transaction from pos' => [
            'display_name' => 'Edit transaction',
            'description'  => 'can edit',
            'group'        => 'POS',
        ],

        'delete service transaction from pos' => [
            'display_name' => 'Delete transaction',
            'description'  => 'can delete',
            'group'        => 'POS',
        ],


        'view online payment' => [
            'display_name' => 'View Online Payment',
            'description'  => 'Can view Online Payments',
            'group'        => 'Online Payment',
        ],

        'change service price' => [
            'display_name' => 'Change Service Price',
            'description'  => 'Can change service price',
            'group'        => 'Reservation Management',
        ],

        'open closed contract' => [
            'display_name' => 'Open Closed Contract',
            'description'  => 'Can open closed contract',
            'group'        => 'Reservation Management',
        ],


        'view companies' => [
            'display_name' => 'View Companies',
            'description'  => 'Can view companies',
            'group'        => 'Companies',
        ],

        'create companies' => [
            'display_name' => 'Create Companies',
            'description'  => 'Can create companies',
            'group'        => 'Companies',
        ],


        'update companies' => [
            'display_name' => 'Update Companies',
            'description'  => 'Can update companies',
            'group'        => 'Companies',
        ],

        'view company profile' => [
            'display_name' => 'View Company Profile',
            'description'  => 'Can view company profile',
            'group'        => 'Companies',
        ],

        'view companies notes' => [
            'display_name' => 'View Companies Notes',
            'description'  => 'Can view companies notes',
            'group'        => 'Companies Notes',
        ],

        'create companies notes' => [
            'display_name' => 'Create Companies Notes',
            'description'  => 'Can create companies notes',
            'group'        => 'Companies Notes',
        ],

        'update companies notes' => [
            'display_name' => 'Update Companies Notes',
            'description'  => 'Can update companies notes',
            'group'        => 'Companies Notes',
        ],

        'delete companies notes' => [
            'display_name' => 'Delete Companies Notes',
            'description'  => 'Can delete companies notes',
            'group'        => 'Companies Notes',
        ],


        'show customers rating' => [
            'display_name' => 'Show Customers Ratings',
            'description'  => 'Can show customer rating',
            'group'        => 'Customers Ratings',
        ],

        'watch reservations table' => [
            'display_name' => 'Watch Reservations Table',
            'description'  => 'Can watch reservations table',
            'group'        => 'Reservation Management',
        ],

        'edit service price pos' => [
            'display_name' => 'Edit Service Price POS',
            'description'  => 'Can edit service price pos',
            'group'        => 'POS',
        ],

        'manager flash report' => [
            'display_name' => 'Manager Flash Report',
            'description'  => 'Can view Manager Flash Report',
            'group'        => 'Reports',
        ],
        'history forecast report' => [
            'display_name' => 'History & Forecast Report',
            'description'  => 'Can view History & Forecast Report',
            'group'        => 'Reports',
        ],
        'housekeeping discrepancies report' => [
            'display_name' => 'HouseKeeping Discrepancies Report',
            'description'  => 'Can view History & Forecast Report',
            'group'        => 'Reports',
        ],
        'paid outs report' => [
            'display_name' => 'Paid Outs Report',
            'description'  => 'Can view Paid Outs Report',
            'group'        => 'Reports',
        ],
        'trial balance report' => [
            'display_name' => 'Trial Balance Report',
            'description'  => 'Can view Trial Balance Report',
            'group'        => 'Reports',
        ],
          //channel manager permissions
          'view channel manager' => [
            'display_name' => 'View Channel Manager',
            'description'  => 'Can view channel manager',
            'group'        => 'Channel Manager',
        ],

        'hotel amenities' => [
            'display_name' => 'Hotel Amenities',
            'description'  => 'Can view Hotel Amenities',
            'group'        => 'Settings',
        ],

        'can edit reservation day price' => [
            'display_name' => 'Edit Reservation Price By Day',
            'description'  => 'Can view Hotel Amenities',
            'group'        => 'Reservation',
        ],
        //        'create roles' => [
        //            'display_name' => 'Create roles',
        //            'description'  => 'Can create roles',
        //            'group'        => 'Role',
        //        ],
        //
        //        'edit roles' => [
        //            'display_name' => 'Edit roles',
        //            'description'  => 'Can edit roles',
        //            'group'        => 'Role',
        //        ],
        //
        //        'delete roles' => [
        //            'display_name' => 'Delete roles',
        //            'description'  => 'Can delete roles',
        //            'group'        => 'Role',
        //        ],


        'available units report' => [
            'display_name' => 'See Available Units Report',
            'description'  => 'Can view Balady Report',
            'group'        => 'Reports',
        ],

        'delete promissory' => [
            'display_name' => 'Delete Promissory',
            'description'  => 'Can Delete Promissory',
            'group'        => 'Special Permissions',
        ],


        'print invoices' => [
            'display_name' => 'Print Invoices',
            'description'  => 'Can print Invoices',
            'group'        => 'Printing Permissions',
        ],

        'print zatca invoices' => [
            'display_name' => 'Print Zatca Invoices',
            'description'  => 'Can print Zatca Invoices',
            'group'        => 'Printing Permissions',
        ],

        'print reservation contract' => [
            'display_name' => 'Print Reservation Contract',
            'description'  => 'Can Print Reservation Contract',
            'group'        => 'Printing Permissions',
        ],

        'print reservation transactions' => [
            'display_name' => 'Print Reservation Transactions',
            'description'  => 'Can Print Reservation Transactions',
            'group'        => 'Printing Permissions',
        ],

        'print transactions' => [
            'display_name' => 'Print Transactions',
            'description'  => 'Can print Deposite Transactions',
            'group'        => 'Printing Permissions',
        ],

        'print pos transactions' => [
            'display_name' => 'Print POS Transactions',
            'description'  => 'Can print POS Transactions',
            'group'        => 'Printing Permissions',
        ],

        'print reservation summary' => [
            'display_name' => 'Print Reservation Summary',
            'description'  => 'Can print Reservation Summary',
            'group'        => 'Printing Permissions',
        ],

        'print promissory notes' => [
            'display_name' => 'Print Promissory Notes',
            'description'  => 'Can print Promissory Note',
            'group'        => 'Printing Permissions',
        ],




    ],
];
