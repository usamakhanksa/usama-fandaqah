# Hotel PMS Module Scaffold (Laravel 11 + Nova 4 + Vue 3)

This document maps each requested page to:
1. Required table(s)
2. Required columns
3. Migration status in this repository
4. Minimum seed expectations

## Implemented in this change
- Added shared empty-page scaffolding for all requested routes in SPA (`ModuleScaffoldPage`).
- Added route metadata catalog for tables/columns/seeds (`moduleScaffoldPages`).
- Added migration `2026_04_21_000100_scaffold_hotel_modules_schema.php` for missing schema.
- Added `HotelModuleScaffoldSeeder` for default setup values.

## Task-wise execution checklist

### 1) Backend setup
- Run `php artisan migrate`
- New migration includes: `profiles`, `activities`, `memberships`, `sales_accounts`, `scheduled_reports`, `safe_logs`, `reservation_change_logs`, `maintenance_requests`, `resource_categories`, `reservation_resource_items`, `employee_contracts`, `ledger_sequences`, `profile_groups`, `included_services`
- Alters `reservations` with `checked_in_at`, `checked_out_at`

### 2) Seeders
- `DatabaseSeeder` now calls `HotelModuleScaffoldSeeder`
- Seeder inserts defaults for:
  - `resource_categories`
  - `profile_groups`
  - `included_services`
  - `ledger_sequences`
  - `scheduled_reports`

### 3) Nova resources (next step)
Generate resources for each table and add lenses/metrics:
- Dashboard metrics: today's arrivals, departures, occupancy %, housekeeping status
- Front desk lenses: arrivals, in-house, departures
- Reports: query-only read resources and cards

### 4) Frontend routing
All requested pages are scaffolded as empty pages with backend requirements shown directly in UI.

### 5) API endpoints (next step)
Add report/data endpoints for:
- Occupancy ratio
- Revenue & taxes
- Deposits/withdraws
- Cleaning/maintenance movement
- Reservation transfers

