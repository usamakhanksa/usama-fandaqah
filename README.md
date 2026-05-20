# 🏨 Fandaqah Hotel Property Management System (PMS)

Fandaqah is a state-of-the-art, ZATCA-compliant, multi-tenant Hotel Property Management System designed to handle hotel operations, POS transactions, financial bookkeeping, marketing, night audits, and integrations with premium, pixel-perfect user interfaces and robust backend logic.

## Table of Contents
- [Overview](#overview)
- [Features](#features)
- [Sidebar Implementation](#sidebar-implementation)
- [Sample Data](#sample-data)
- [Installation](#installation)
  - [Backend Setup](#backend-setup)
  - [Frontend Setup](#frontend-setup)
- [Usage](#usage)
- [Testing](#testing)
- [Default Credentials](#default-credentials)
- [Contributing](#contributing)
- [License](#license)

## Overview
Fandaqah PMS is a comprehensive solution for hotel management, built with Laravel 10, Vue 3 (Inertia.js), and MySQL. It implements multi-tenancy via `team_id` scoping, role-based access control (RBAC) using Spatie Laravel Permission, and follows a service-layer architecture for clean separation of concerns.

The system includes modules for:
- Reservations and Front Desk Operations
- Housekeeping and Maintenance
- Finance and Accounting (Receipts, Payments, Invoices, Credit Notes)
- Night Audit Engine
- POS and Services
- Integrations (ZATCA, Qoyod, etc.)
- Reports and Analytics
- And much more...

## Features
- **Multi-tenancy**: Complete data isolation between hotels/teams via `team_id` foreign keys
- **Role-Based Access Control (RBAC)**: Granular permissions for 15+ roles with Super Admin bypass
- **ZATCA Phase 2 Compliance**: Automated e-invoicing with XML generation, TLV QR codes, and digital signatures
- **Night Audit Engine**: Automated end-of-day processing with occupancy snapshots, revenue calculations, and transaction freezing
- **Interactive Dashboard**: Permission-aware widgets with real-time KPIs, charts, and quick actions
- **Complete CRUD Operations**: Every module includes full Create, Read, Update, Delete functionality with validation
- **Activity Logging**: Comprehensive audit trails for all financial and operational transactions
- **Multi-language Support**: Full Arabic/English UI with RTL layout support
- **Export Capabilities**: CSV/Excel/PDF exports for reports and lists
- **RESTful API**: Well-documented API endpoints for all modules
- **Automated Testing**: PHPUnit and Selenium test suites for core functionality

## Sidebar Implementation
The sidebar is a dynamic, permission-aware navigation system built with:

### Database Structure
- `sidebar_items` table stores menu items with fields for:
  - `key` (unique identifier)
  - `label_en` / `label_ar` (bilingual labels)
  - `icon` (Font Awesome icon class)
  - `route_name` / `url` (navigation target)
  - `permission` (required permission slug)
  - `module` (grouping category)
  - `parent_id` (for nested menus)
  - `order` (display order)
  - `is_visible` / `is_beta` / `is_external` (visibility flags)
  - `badge_count_query` (SQL snippet for dynamic badges)
  - `active_route_patterns` (for active state detection)

### Service Layer
- `App\Services\SidebarService` provides:
  - `getMenuForUser($user)`: Returns filtered menu items based on user permissions
  - `getBadgeCount($item, $teamId)`: Executes badge count queries with team scoping

### Vue Components
- `SidebarNav.vue`: Main sidebar container with responsive collapse
- `SidebarMenuItem.vue`: Recursive component for nested menu items
- Permission checks using Laravel's `@can` directive via Ziggy Vue plugin
- Active route highlighting using Vue Router's current route
- Badge display for items with `badge_count_query`

### Key Features
- **Permission-Based Visibility**: Users only see menu items they have access to
- **Dynamic Badges**: Real-time counts (e.g., pending approvals, overdue items)
- **Responsive Design**: Collapsible on mobile, hover-expand on desktop
- **Bilingual Support**: Automatic language switching with RTL layout
- **External Links**: Support for external URLs with `is_external` flag
- **Beta Flags**: Mark experimental features with `is_beta` badge
- **Nested Menus**: Unlimited submenu levels via `parent_id` self-reference

## Sample Data
The system includes comprehensive, idempotent seeders that create realistic demo data for two hotel tenants:

### Tenant 1: Fandaqah Palace Hotel & Suites (Primary)
- Complete hotel profile with contact information
- 5 room categories (Standard, Deluxe, Suite, Executive Suite, Presidential)
- 30+ rooms with mixed statuses (available, occupied, maintenance, out-of-order)
- 20+ guest profiles with Saudi/non-Saudi examples
- 5 corporate companies with 2 company groups
- Sample reservations (individual, group, walk-in, online, OTA)
- Financial transactions (receipts, payments, invoices, credit notes)
- Night audit logs with occupancy snapshots
- Cashier shifts (open, closed, approved, rejected)
- Promissory notes and collections
- POS transactions and service logs
- Integration configurations (ZATCA, Qoyod, etc.)

### Tenant 2: Demo Hotel & Resort (Secondary)
- Similar structure with different data for cross-tenant testing
- Demonstrates multi-tenancy isolation

### Key Seeder Classes
- `CountryCitySeeder`, `HotelTypeSeeder`
- `TeamSeeder`, `UserRolePermissionSeeder`, `SidebarSeeder`, `DashboardWidgetSeeder`
- `UnitCategorySeeder`, `UnitFeatureOptionSeeder`, `UnitSeeder`
- `SourceChannelSeeder`, `ServiceCategorySeeder`, `ServiceSeeder`
- `CompanyAndGroupSeeder`, `CustomerSeeder`, `OfferSpecialPricePromoSeeder`
- `ReservationSeeder`, `ReservationGuestSeeder`, `CheckinCheckoutSeeder`
- `CashShiftSeeder`, `TransactionSeeder`, `InvoiceCreditNoteSeeder`
- `HousekeepingSeeder`, `MaintenanceSeeder`, `NightAuditSeeder`
- `IntegrationSeeder`

All seeders use `updateOrCreate` or equivalent patterns to be safely re-runnable.

## Installation
### Prerequisites
- PHP 8.2+
- MySQL/MariaDB
- Node.js 18+ & npm
- Composer

### Backend Setup
```bash
# Clone repository
git clone https://github.com/your-repo/fandaqah-pms.git
cd fandaqah-pms

# Install PHP dependencies
composer install

# Configure environment
cp .env.example .env
php artisan key:generate

# Set up database
# Create MySQL database and update .env credentials
php artisan migrate

# Seed database with sample data
php artisan db:seed

# Optional: Passport for API authentication
php artisan passport:install
```

### Frontend Setup
```bash
# Install Node dependencies
npm install

# Start development server
npm run dev

# For production builds
npm run build
```

## Usage
### Accessing the Application
1. Backend API: `http://localhost:8000/api`
2. Frontend Dashboard: `http://localhost:5173` (Vite dev server)
3. Laravel Nova: `http://localhost:8000/nova` (if installed)

### Default Admin Credentials
- **Email**: `admin@fandaqah-palace.com`
- **Password**: `password`

### Role-Based Access
Login with different credentials to test permission-based sidebar visibility:
- Front Desk Agent: `agent@fandaqah-palace.com` / `password`
- Accountant: `accountant@fandaqah-palace.com` / `password`
- Housekeeping Supervisor: `housekeeping@fandaqah-palace.com` / `password`
- Revenue Manager: `revenue@fandaqah-palace.com` / `password`

### Key Workflows to Test
1. **Reservation Lifecycle**: Create → Check-in → Add Services → Check-out → Generate Invoice
2. **Financial Processing**: Create Receipt/Payment → Reconcile with Invoice → Generate Reports
3. **Night Audit**: Run Manual Audit → View Snapshots → Check Audit Logs
4. **POS Sales**: Create Sale → Process Payment → Print Receipt
5. **Maintenance**: Create Request → Assign Technician → Complete Work Order
6. **Integrations**: Test ZATCA Connection → Submit Invoice → Check Status

## Testing
### PHPUnit
```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --tests=Feature/ReservationTest
```

### Selenium/Dusk
```bash
# Install Dusk (if not already)
composer require --dev laravel/dusk
php artisan dusk:install

# Run browser tests
php artisan dusk
```

### Test Coverage
Tests cover:
- Permission middleware and gates
- CRUD operations for all modules
- Multi-tenancy isolation
- Night audit sequencing
- Financial calculations
- Sidebar permission filtering
- Form validation and requests
- API resource responses

## Default Credentials
| Role | Email | Password |
|------|-------|----------|
| Super Admin | admin@fandaqah-palace.com | password |
| Hotel Owner | owner@fandaqah-palace.com | password |
| General Manager | gm@fandaqah-palace.com | password |
| Front Desk Manager | fm@fandaqah-palace.com | password |
| Front Desk Agent | agent@fandaqah-palace.com | password |
| Housekeeping Supervisor | housekeeping@fandaqah-palace.com | password |
| Housekeeper | housekeeper@fandaqah-palace.com | password |
| Maintenance User | maintenance@fandaqah-palace.com | password |
| Accountant | accountant@fandaqah-palace.com | password |
| Cashier | cashier@fandaqah-palace.com | password |
| Revenue Manager | revenue@fandaqah-palace.com | password |
| Marketing Manager | marketing@fandaqah-palace.com | password |
| Auditor | auditor@fandaqah-palace.com | password |
| Integration Admin | integration@fandaqah-palace.com | password |
| Read-only Viewer | viewer@fandaqah-palace.com | password |

## Contributing
1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

Please ensure:
- Code follows Laravel and Vue best practices
- All new features include tests
- Documentation is updated
- Permissions are properly defined and seeded
- Sidebar items are added for new features
- Sample data is updated if needed

## License
This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Acknowledgments
- Laravel Team for the excellent PHP framework
- Vue.js and Inertia.js teams for the modern frontend stack
- Spatie for Laravel Permission package
- ZATCA guidelines for e-invoicing compliance
- Open-source community for various packages used

---
*Last updated: May 20, 2026*
*Version: 1.0.0*