# MODULE_MAP.md - Fandaqah Hotel PMS Module Architecture

## 1. Core Modules

### Dashboards
- **Path**: `app/Services/Dashboard`, `app/Http/Controllers/Api/DashboardController.php`, `app/Nova/Dashboards`
- **Features**: KPI cards, role-aware widgets, charts.
- **Status**: 🔄 Partially implemented.

### Reservations + Front Desk
- **Path**: `app/Services/Reservation`, `app/Http/Controllers/Api/ReservationController.php`, `app/Models/Reservation.php`, `app/Nova/Reservation.php`
- **Features**: CRUD, check-in/out, room swap, extensions, shomoos.
- **Status**: 🔄 Partially implemented.

### Serviced Apartments + Long-Stay
- **Path**: `app/Models/Unit.php`, `app/Models/ContractService.php` (Needs more investigation)
- **Features**: Units, floors, zones, lease agreements, utility meters.
- **Status**: 📋 Needs completion.

### Rooms + Housekeeping + Maintenance
- **Path**: `app/Models/Room.php`, `app/Models/HousekeepingTask.php`, `app/Models/MaintenanceTicket.php`
- **Features**: Status tracking, cleaning tasks, maintenance requests.
- **Status**: 🔄 Partially implemented.

### Guests + Customers + Companies
- **Path**: `app/Services/Guest`, `app/Http/Controllers/Api/GuestController.php`, `app/Models/Guest.php`, `app/Models/Company.php`
- **Features**: Guest directory, corporate accounts, credit limits.
- **Status**: 🔄 Partially implemented.

### POS + Services
- **Path**: `app/Http/Controllers/Api/PosController.php`, `app/Models/Service.php`, `app/Models/POSOrder.php`
- **Features**: POS sales, service logs, VAT calculation.
- **Status**: 🔄 Partially implemented.

### Finance + Accounting
- **Path**: `app/Services/Finance`, `app/Http/Controllers/Api/Finance`, `app/Models/Transaction.php`, `app/Models/Invoice.php`
- **Features**: Receipts, payments, invoices, ZATCA, Qoyod sync.
- **Status**: 🔄 Partially implemented.

### Night Audit
- **Path**: `app/Services/NightAudit`, `app/Http/Controllers/Api/NightAuditController.php`, `app/Models/NightAuditLog.php`
- **Features**: Business date advancement, transaction freeze, snapshots.
- **Status**: 🔄 Partially implemented.

### Reports + Analytics
- **Path**: `app/Services/Reports`, `app/Http/Controllers/Api/ReportsController.php`
- **Features**: Daily, monthly, occupancy, revenue, ADR/RevPAR.
- **Status**: 🔄 Partially implemented.

### Marketing + Revenue
- **Path**: `app/Models/Source.php`, `app/Models/PromoCode.php`, `app/Models/Offer.php`
- **Features**: Sources, promo codes, special prices.
- **Status**: 🔄 Partially implemented.

### Website CMS + Booking Engine
- **Path**: `app/Models/WebsitePage.php`, `app/Models/WebsiteSetting.php`
- **Features**: CMS, gallery, public booking preview.
- **Status**: 📋 Needs completion.

### Integrations
- **Path**: `app/Integration`, `app/Services/ZATCA`, `app/Models/Integration.php`
- **Features**: ZATCA, Qoyod, Shomoos, SMS gateways.
- **Status**: 🔄 Partially implemented.

### Settings + System Admin
- **Path**: `app/Models/Team.php`, `app/Models/Role.php`, `app/Models/Permission.php`
- **Features**: Multi-tenancy, RBAC, VAT settings.
- **Status**: 🔄 Partially implemented.

## 2. Technical Infrastructure
- **Framework**: Laravel 11.0
- **Admin Panel**: Laravel Nova (custom)
- **Frontend**: Vue 3 + Vite
- **Multi-tenancy**: Team-based (team_id scoping)
- **Compliance**: ZATCA Phase 1 & 2, Shomoos
- **Money**: DECIMAL(10,2) or higher
- **Localization**: Arabic/English, LTR/RTL
