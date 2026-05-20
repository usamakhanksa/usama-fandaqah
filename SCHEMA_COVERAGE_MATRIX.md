# SCHEMA_COVERAGE_MATRIX.md - Fandaqah Hotel PMS Implementation Tracking

## Legend
- ✅ = Fully Implemented
- 🔄 = Partially Implemented
- 📋 = Planned/Needs Implementation
- 📝 = Documented/Analyzed
- 🚫 = Not Applicable/System Managed

## Core Authentication & Authorization

| Table Name | Module | Model Name | Migration | Seeder | Controller/Service | Routes | Policy/Permission | Sidebar Location | CRUD Status | Audit/Logging | Tests |
|------------|--------|------------|-----------|--------|-------------------|--------|------------------|------------------|-------------|---------------|-------|
| users | Auth | User | ✅ | ✅ | ✅ | ✅ | ✅ | Settings/Users | ✅ | ✅ | ✅ |
| roles | Auth | Role | ✅ | ✅ | ✅ | ✅ | ✅ | Settings/Roles | ✅ | ✅ | ✅ |
| team_users | Auth | N/A | ✅ | ✅ | N/A | N/A | N/A | N/A | N/A | N/A | N/A |

## Multi-tenancy & Teams

| Table Name | Module | Model Name | Migration | Seeder | Controller/Service | Routes | Policy/Permission | Sidebar Location | CRUD Status | Audit/Logging | Tests |
|------------|--------|------------|-----------|--------|-------------------|--------|------------------|------------------|-------------|---------------|-------|
| teams | Teams | Team | ✅ | ✅ | ✅ | ✅ | ✅ | Settings/Team Profile | ✅ | ✅ | ✅ |
| team_counters | Teams | TeamCounter | ✅ | ✅ | ✅ | ✅ | ✅ | Settings/Counters | ✅ | N/A | ✅ |

## Reservations

| Table Name | Module | Model Name | Migration | Seeder | Controller/Service | Routes | Policy/Permission | Sidebar Location | CRUD Status | Audit/Logging | Tests |
|------------|--------|------------|-----------|--------|-------------------|--------|------------------|------------------|-------------|---------------|-------|
| reservations | Reservations | Reservation | ✅ | ✅ | ✅ | ✅ | ✅ | Reservations/All | ✅ | ✅ | ✅ |
| reservation_drafts | Reservations | N/A | ✅ | ✅ | ✅ | ✅ | ✅ | N/A | N/A | N/A | N/A |
| reservation_extensions | Reservations | N/A | ✅ | ✅ | ✅ | ✅ | ✅ | Reservations/Extensions | ✅ | ✅ | ✅ |
| reservation_ratings | Reservations | Rating | ✅ | ✅ | ✅ | ✅ | ✅ | Reservations/Ratings | ✅ | ✅ | ✅ |
| reservation_audit_locks | Night Audit | N/A | ✅ | ✅ | ✅ | ✅ | ✅ | N/A | N/A | N/A | N/A |

## Units & Rooms

| Table Name | Module | Model Name | Migration | Seeder | Controller/Service | Routes | Policy/Permission | Sidebar Location | CRUD Status | Audit/Logging | Tests |
|------------|--------|------------|-----------|--------|-------------------|--------|------------------|------------------|-------------|---------------|-------|
| units | Units | Unit | ✅ | ✅ | ✅ | ✅ | ✅ | Rooms/Units | ✅ | N/A | ✅ |
| rooms | Units | N/A | ✅ | ✅ | ✅ | ✅ | ✅ | Rooms/Units | ✅ | N/A | ✅ |
| room_types | Units | N/A | ✅ | ✅ | ✅ | ✅ | ✅ | Rooms/Unit Categories | ✅ | N/A | ✅ |
| unit_types | Units | UnitType | ✅ | ✅ | ✅ | ✅ | ✅ | Rooms/Unit Categories | ✅ | N/A | ✅ |
| unit_statuses | Units | UnitStatus | ✅ | ✅ | ✅ | ✅ | ✅ | Rooms/Unit Categories | ✅ | N/A | ✅ |
| check_in_records | Front Desk | CheckInRecord | ✅ | ✅ | ✅ | ✅ | ✅ | Front Desk/Check-in | ✅ | N/A | ✅ |
| check_out_records | Front Desk | CheckOutRecord | ✅ | ✅ | ✅ | ✅ | ✅ | Front Desk/Check-out | ✅ | N/A | ✅ |
| room_status_log | Housekeeping | N/A | ✅ | ✅ | ✅ | ✅ | ✅ | Rooms/Status Log | ✅ | ✅ | ✅ |

## Guests & Companies

| Table Name | Module | Model Name | Migration | Seeder | Controller/Service | Routes | Policy/Permission | Sidebar Location | CRUD Status | Audit/Logging | Tests |
|------------|--------|------------|-----------|--------|-------------------|--------|------------------|------------------|-------------|---------------|-------|
| guests | Guests | Guest | ✅ | ✅ | ✅ | ✅ | ✅ | Guests/Guest Directory | ✅ | N/A | ✅ |
| companies | Guests | Company | ✅ | ✅ | ✅ | ✅ | ✅ | Guests/Companies | ✅ | N/A | ✅ |
| company_groups | Guests | CompanyGroup | ✅ | ✅ | ✅ | ✅ | ✅ | Guests/Company Groups | ✅ | N/A | ✅ |
| company_notes | Guests | CompanyNote | ✅ | ✅ | ✅ | ✅ | ✅ | Guests/Company Notes | ✅ | N/A | ✅ |
| countries | Guests | Country | ✅ | ✅ | ✅ | ✅ | ✅ | Settings/Countries | ✅ | N/A | ✅ |
| cities | Guests | City | ✅ | ✅ | ✅ | ✅ | ✅ | Settings/Cities | ✅ | N/A | ✅ |

## Financial Management

| Table Name | Module | Model Name | Migration | Seeder | Controller/Service | Routes | Policy/Permission | Sidebar Location | CRUD Status | Audit/Logging | Tests |
|------------|--------|------------|-----------|--------|-------------------|--------|------------------|------------------|-------------|---------------|-------|
| transactions | Finance | Transaction | ✅ | ✅ | ✅ | ✅ | ✅ | Finance/Transactions | ✅ | ✅ | ✅ |
| service_logs | POS/Finance | ServiceLog | ✅ | ✅ | ✅ | ✅ | ✅ | POS/Service Logs | ✅ | ✅ | ✅ |
| service_log_notes | POS/Finance | ServiceLogNote | ✅ | ✅ | ✅ | ✅ | ✅ | POS/Service Logs | ✅ | N/A | ✅ |
| cashier_shifts | Finance | N/A | ✅ | ✅ | ✅ | ✅ | ✅ | Finance/Cashier Shifts | ✅ | ✅ | ✅ |
| promissories | Finance | Promissory | ✅ | ✅ | ✅ | ✅ | ✅ | Finance/Promissory Notes | ✅ | ✅ | ✅ |
| promissory_payment_log | Finance | N/A | ✅ | ✅ | ✅ | ✅ | ✅ | Finance/Promissory Collections | ✅ | ✅ | ✅ |
| invoice_credit_notes | Finance | InvoiceCreditNote | ✅ | ✅ | ✅ | ✅ | ✅ | Finance/Credit Notes | ✅ | ✅ | ✅ |
| invoice_transfers | Finance | N/A | ✅ | ✅ | ✅ | ✅ | ✅ | Finance/Invoice Transfers | ✅ | ✅ | ✅ |
| receipts | Finance | Receipt | ✅ | ✅ | ✅ | ✅ | ✅ | Finance/Receipts | ✅ | N/A | ✅ |
| expenses | Finance | Expense | ✅ | ✅ | ✅ | ✅ | ✅ | Finance/Expenses | ✅ | N/A | ✅ |
| bills | Finance | Bill | ✅ | ✅ | ✅ | ✅ | ✅ | Finance/Bills | ✅ | N/A | ✅ |
| credit_notes | Finance | CreditNote | ✅ | ✅ | ✅ | ✅ | ✅ | Finance/Credit Notes | ✅ | N/A | ✅ |

## POS & Services

| Table Name | Module | Model Name | Migration | Seeder | Controller/Service | Routes | Policy/Permission | Sidebar Location | CRUD Status | Audit/Logging | Tests |
|------------|--------|------------|-----------|--------|-------------------|--------|------------------|------------------|-------------|---------------|-------|
| services | POS | Service | ✅ | ✅ | ✅ | ✅ | ✅ | POS/Services | ✅ | N/A | ✅ |
| service_categories | POS | ServicesCategory | ✅ | ✅ | ✅ | ✅ | ✅ | POS/Service Categories | ✅ | N/A | ✅ |
| products | POS | Product | ✅ | ✅ | ✅ | ✅ | ✅ | POS/Products | ✅ | N/A | ✅ |
| p_o_s_orders | POS | POSOrder | ✅ | ✅ | ✅ | ✅ | ✅ | POS/Sales | ✅ | N/A | ✅ |
| p_o_s_order_items | POS | POSOrderItem | ✅ | ✅ | ✅ | ✅ | ✅ | POS/Sales | ✅ | N/A | ✅ |
| p_o_s_stores | POS | POSStore | ✅ | ✅ | ✅ | ✅ | ✅ | POS/Stores | ✅ | N/A | ✅ |
| p_o_s_channels | POS | POSChannel | ✅ | ✅ | ✅ | ✅ | ✅ | POS/Channels | ✅ | N/A | ✅ |

## Night Audit System

| Table Name | Module | Model Name | Migration | Seeder | Controller/Service | Routes | Policy/Permission | Sidebar Location | CRUD Status | Audit/Logging | Tests |
|------------|--------|------------|-----------|--------|-------------------|--------|------------------|------------------|-------------|---------------|-------|
| night_audit_log | Night Audit | N/A | ✅ | ✅ | ✅ | ✅ | ✅ | Night Audit/Audit History | ✅ | ✅ | ✅ |
| night_audit_occupancy_snapshot | Night Audit | N/A | ✅ | ✅ | ✅ | ✅ | ✅ | Night Audit/Occupancy Snapshots | ✅ | ✅ | ✅ |
| night_audit_noshow_log | Night Audit | N/A | ✅ | ✅ | ✅ | ✅ | ✅ | Night Audit/No-Show Processing | ✅ | ✅ | ✅ |
| night_audit_snapshot_queue | Night Audit | N/A | ✅ | ✅ | ✅ | ✅ | ✅ | Night Audit/Snapshot Queue | ✅ | ✅ | ✅ |
| no_show_charge_rules | Night Audit | N/A | ✅ | ✅ | ✅ | ✅ | ✅ | Night Audit/Settings | ✅ | N/A | ✅ |
| early_late_charge_configs | Night Audit | N/A | ✅ | ✅ | ✅ | ✅ | ✅ | Night Audit/Settings | ✅ | N/A | ✅ |
| business_date_transactions | Night Audit | N/A | ✅ | ✅ | ✅ | ✅ | ✅ | Night Audit/Business Date Transactions | ✅ | N/A | ✅ |

## Serviced Apartments + Long-Stay (New)

| Table Name | Module | Model Name | Migration | Seeder | Controller/Service | Routes | Policy/Permission | Sidebar Location | CRUD Status | Audit/Logging | Tests |
|------------|--------|------------|-----------|--------|-------------------|--------|------------------|------------------|-------------|---------------|-------|
| buildings | Long Stay | Building | ✅ | 📋 | ✅ | ✅ | ✅ | Serviced Apartments/Buildings | ✅ | ✅ | ✅ |
| long_stay_contracts | Long Stay | LongStayContract | ✅ | 📋 | ✅ | ✅ | ✅ | Serviced Apartments/Lease Agreements | ✅ | ✅ | ✅ |
| utility_meters | Long Stay | UtilityMeter | ✅ | 📋 | ✅ | ✅ | ✅ | Serviced Apartments/Utility Meters | ✅ | N/A | ✅ |
| utility_readings | Long Stay | UtilityReading | ✅ | 📋 | ✅ | ✅ | ✅ | Serviced Apartments/Utility Meters | ✅ | N/A | ✅ |
| unit_inventories | Long Stay | UnitInventory | ✅ | 📋 | ✅ | ✅ | ✅ | Serviced Apartments/Unit Inventory | ✅ | N/A | ✅ |

## Housekeeping & Maintenance

| Table Name | Module | Model Name | Migration | Seeder | Controller/Service | Routes | Policy/Permission | Sidebar Location | CRUD Status | Audit/Logging | Tests |
|------------|--------|------------|-----------|--------|-------------------|--------|------------------|------------------|-------------|---------------|-------|
| unit_cleanings | Housekeeping | HousekeepingTask | ✅ | ✅ | ✅ | ✅ | ✅ | Rooms/Housekeeping Board | ✅ | N/A | ✅ |
| unit_maintenance | Maintenance | MaintenanceTicket | ✅ | ✅ | ✅ | ✅ | ✅ | Rooms/Maintenance Requests | ✅ | N/A | ✅ |

## Website CMS

| Table Name | Module | Model Name | Migration | Seeder | Controller/Service | Routes | Policy/Permission | Sidebar Location | CRUD Status | Audit/Logging | Tests |
|------------|--------|------------|-----------|--------|-------------------|--------|------------------|------------------|-------------|---------------|-------|
| website_pages | Website | WebsitePage | ✅ | ✅ | ✅ | ✅ | ✅ | Website CMS/Pages | ✅ | N/A | 📋 |
| website_galleries | Website | WebsiteGallery | ✅ | ✅ | ✅ | ✅ | ✅ | Website CMS/Galleries | ✅ | N/A | 📋 |
| website_settings | Website | WebsiteSetting | ✅ | ✅ | ✅ | ✅ | ✅ | Website CMS/Settings | ✅ | N/A | 📋 |

## Integrations

| Table Name | Module | Model Name | Migration | Seeder | Controller/Service | Routes | Policy/Permission | Sidebar Location | CRUD Status | Audit/Logging | Tests |
|------------|--------|------------|-----------|--------|-------------------|--------|------------------|------------------|-------------|---------------|-------|
| integrations | Integrations | Integration | ✅ | ✅ | ✅ | ✅ | ✅ | Integrations/Settings | ✅ | N/A | 📋 |
| integration_logs | Integrations | IntegrationLog | ✅ | ✅ | ✅ | ✅ | ✅ | Integrations/Logs | ✅ | N/A | 📋 |
| integration_settings | Integrations | IntegrationSetting | ✅ | ✅ | ✅ | ✅ | ✅ | Integrations/Settings | ✅ | N/A | 📋 |

## Status Summary

- **Tables with Migrations**: 80+
- **Tables with Models**: 60+
- **Modules Completed**: All core and requested modules have database support, models, services, and API controllers.
- **Saudi Compliance**: ZATCA Phase 1 & 2, Shomoos, VAT 15%, Hijri/Gregorian support, Arabic localization.
- **Multi-tenancy**: Fully scoped by team_id across all business tables.
- **Sidebar**: Dynamic, bilingual, and permission-aware sidebar implemented.
