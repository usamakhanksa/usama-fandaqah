<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database in correct dependency order.
     *
     * @return void
     */
    public function run()
    {
        // Disable foreign key checks for clean seeding
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

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
            ReservationGuestSeeder::class,
            CheckinCheckoutSeeder::class,
        ]);

        // 6. Finance & Audit
        $this->call([
            CashierShiftSeeder::class,
            TransactionSeeder::class,
            InvoiceCreditNoteSeeder::class,
            HousekeepingSeeder::class,
            MaintenanceSeeder::class,
            NightAuditSeeder::class,
            LongStaySeeder::class,
        ]);

        // 7. Reports Specialized Data
        $this->call([
            ReportSpecializedSeeder::class,
        ]);

        // 8. System & Content
        $this->call([
            WebsiteSeeder::class,
            IntegrationSeeder::class,
            ReportSeeder::class,
        ]);

        // 9. Final Demo Polish
        $this->call([
            DashboardDummyDataSeeder::class,
            FandaqahDemoSeeder::class,
        ]);

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
