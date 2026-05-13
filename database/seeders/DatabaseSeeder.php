<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database in correct dependency order.
     *
     * @return void
     */
    public function run()
    {
        // 1. Foundation Lookups
        $this->call([
            CountryCitySeeder::class,
            HotelTypeSeeder::class,
            PaymentMethodBankSenderSeeder::class,
        ]);

        // 2. Multi-tenancy & Access
        $this->call([
            TeamSeeder::class,
            UserRolePermissionSeeder::class,
            SidebarSeeder::class,
            DashboardWidgetSeeder::class,
        ]);

        // 3. Inventory & Setup
        $this->call([
            UnitCategorySeeder::class,
            UnitFeatureOptionSeeder::class,
            UnitSeeder::class,
            SourceChannelSeeder::class,
            ServiceCategorySeeder::class,
            ServiceSeeder::class,
        ]);

        // 4. Marketing & Guests
        $this->call([
            CompanyAndGroupSeeder::class,
            CustomerSeeder::class,
            OfferSpecialPricePromoSeeder::class,
        ]);

        // 5. Operations
        $this->call([
            ReservationSeeder::class,
            // ReservationGuestSeeder::class,
            // CheckinCheckoutSeeder::class,
        ]);

        // 6. Finance & Audit
        $this->call([
            CashierShiftSeeder::class,
            TransactionSeeder::class,
            InvoiceCreditNoteSeeder::class,
            HousekeepingSeeder::class,
            // MaintenanceSeeder::class,
            // NightAuditSeeder::class,
        ]);

        // 7. Reports Specialized Data
        $this->call([
            ReportSpecializedSeeder::class,
        ]);

        // 8. System & Content
        $this->call([
            WebsiteSeeder::class,
            IntegrationSeeder::class,
            // NotificationSeeder::class,
            // ReportSeeder::class,
        ]);
    }
}
