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
| users | Auth | User | ✅ | ✅ | ✅ | ✅ | ✅ | Settings/Users | 🔄 | ✅ | 🔄 |
| roles | Auth | Role | ✅ | ✅ | ✅ | ✅ | ✅ | Settings/Roles | 🔄 | ✅ | 🔄 |
| team_users | Auth | N/A | ✅ | ✅ | N/A | N/A | N/A | N/A | N/A | N/A | N/A |

## Multi-tenancy & Teams

| Table Name | Module | Model Name | Migration | Seeder | Controller/Service | Routes | Policy/Permission | Sidebar Location | CRUD Status | Audit/Logging | Tests |
|------------|--------|------------|-----------|--------|-------------------|--------|------------------|------------------|-------------|---------------|-------|
| teams | Teams | Team | ✅ | ✅ | ✅ | ✅ | ✅ | Settings/Team Profile | 🔄 | ✅ | 🔄 |
| team_counters | Teams | TeamCounter | ✅ | 📋 | ✅ | ✅ | ✅ | Settings/Counters | 🔄 | N/A | 🔄 |

## Reservations

| Table Name | Module | Model Name | Migration | Seeder | Controller/Service | Routes | Policy/Permission | Sidebar Location | CRUD Status | Audit/Logging | Tests |
|------------|--------|------------|-----------|--------|-------------------|--------|------------------|------------------|-------------|---------------|-------|
| reservations | Reservations | Reservation | ✅ | 📋 | 📋 | 📋 | 📋 | Reservations/All | 📋 | 📋 | 📋 |
| reservation_drafts | Reservations | N/A | ✅ | 📋 | 📋 | 📋 | 📋 | N/A | N/A | N/A | N/A |
| reservation_extensions | Reservations | N/A | ✅ | 📋 | 📋 | 📋 | 📋 | Reservations/Extensions | 📋 | 📋 | 📋 |
| reservation_ratings | Reservations | Rating | ✅ | 📋 | 📋 | 📋 | 📋 | Reservations/Ratings | 📋 | 📋 | 📋 |
| reservation_audit_locks | Night Audit | N/A | ✅ | 📋 | 📋 | 📋 | 📋 | N/A | N/A | N/A | N/A |

## Units & Rooms

| Table Name | Module | Model Name | Migration | Seeder | Controller/Service | Routes | Policy/Permission | Sidebar Location | CRUD Status | Audit/Logging | Tests |
|------------|--------|------------|-----------|--------|-------------------|--------|------------------|------------------|-------------|---------------|-------|
| units | Units | Unit | ✅ | 📋 | 📋 | 📋 | 📋 | Rooms/Units | 📋 | N/A | 📋 |
| rooms | Units | N/A | ✅ | 📋 | 📋 | 📋 | 📋 | Rooms/Units | 📋 | N/A | 📋 |
| room_types | Units | N/A | ✅ | 📋 | 📋 | 📋 | 📋 | Rooms/Unit Categories | 📋 | N/A | 📋 |
| unit_types | Units | UnitType | ✅ | 📋 | 📋 | 📋 | 📋 | Rooms/Unit Categories | 📋 | N/A | 📋 |
| unit_statuses | Units | UnitStatus | ✅ | 📋 | 📋 | 📋 | 📋 | Rooms/Unit Categories | 📋 | N/A | 📋 |
| check_in_records | Front Desk | CheckInRecord | ✅ | 📋 | 📋 | 📋 | 📋 | Front Desk/Check-in | 📋 | N/A | 📋 |
| check_out_records | Front Desk | CheckOutRecord | ✅ | 📋 | 📋 | 📋 | 📋 | Front Desk/Check-out | 📋 | N/A | 📋 |
| room_status_log | Housekeeping | N/A | ✅ | 📋 | 📋 | 📋 | 📋 | Rooms/Status Log | 📋 | ✅ | 📋 |

## Guests & Companies

| Table Name | Module | Model Name | Migration | Seeder | Controller/Service | Routes | Policy/Permission | Sidebar Location | CRUD Status | Audit/Logging | Tests |
|------------|--------|------------|-----------|--------|-------------------|--------|------------------|------------------|-------------|---------------|-------|
| guests | Guests | Guest | ✅ | 📋 | 📋 | 📋 | 📋 | Guests/Guest Directory | 📋 | N/A | 📋 |
| companies | Guests | Company | ✅ | 📋 | 📋 | 📋 | 📋 | Guests/Companies | 📋 | N/A | 📋 |
| company_groups | Guests | CompanyGroup | ✅ | 📋 | 📋 | 📋 | 📋 | Guests/Company Groups | 📋 | N/A | 📋 |
| company_notes | Guests | CompanyNote | ✅ | 📋 | 📋 | 📋 | 📋 | Guests/Company Notes | 📋 | N/A | 📋 |
| countries | Guests | Country | ✅ | 📋 | 📋 | 📋 | 📋 | Settings/Countries | 📋 | N/A | 📋 |
| cities | Guests | City | ✅ | 📋 | 📋 | 📋 | 📋 | Settings/Cities | 📋 | N/A | 📋 |

## Financial Management

| Table Name | Module | Model Name | Migration | Seeder | Controller/Service | Routes | Policy/Permission | Sidebar Location | CRUD Status | Audit/Logging | Tests |
|------------|--------|------------|-----------|--------|-------------------|--------|------------------|------------------|-------------|---------------|-------|
| transactions | Finance | Transaction | ✅ | 📋 | 📋 | 📋 | 📋 | Finance/Transactions | 📋 | 📋 | 📋 |
| service_logs | POS/Finance | ServiceLog | ✅ | 📋 | 📋 | 📋 | 📋 | POS/Service Logs | 📋 | 📋 | 📋 |
| service_log_notes | POS/Finance | ServiceLogNote | ✅ | 📋 | 📋 | 📋 | 📋 | POS/Service Logs | 📋 | N/A | 📋 |
| cashier_shifts | Finance | N/A | ✅ | 📋 | 📋 | 📋 | 📋 | Finance/Cashier Shifts | 📋 | 📋 | 📋 |
| promissories | Finance | Promissory | ✅ | 📋 | 📋 | 📋 | 📋 | Finance/Promissory Notes | 📋 | 📋 | 📋 |
| promissory_payment_log | Finance | N/A | ✅ | 📋 | 📋 | 📋 | 📋 | Finance/Promissory Collections | 📋 | 📋 | 📋 |
| invoice_credit_notes | Finance | InvoiceCreditNote | ✅ | 📋 | 📋 | 📋 | 📋 | Finance/Credit Notes | 📋 | 📋 | 📋 |
| invoice_transfers | Finance | N/A | ✅ | 📋 | 📋 | 📋 | 📋 | Finance/Invoice Transfers | 📋 | 📋 | 📋 |
| receipts | Finance | Receipt | N/A | 📋 | 📋 | 📋 | 📋 | Finance/Receipts | 📋 | N/A | 📋 |
| expenses | Finance | Expense | N/A | 📋 | 📋 | 📋 | 📋 | Finance/Expenses | 📋 | N/A | 📋 |
| bills | Finance | Bill | N/A | 📋 | 📋 | 📋 | 📋 | Finance/Bills | 📋 | N/A | 📋 |
| credit_notes | Finance | CreditNote | N/A | 📋 | 📋 | 📋 | 📋 | Finance/Credit Notes | 📋 | N/A | 📋 |

## POS & Services

| Table Name | Module | Model Name | Migration | Seeder | Controller/Service | Routes | Policy/Permission | Sidebar Location | CRUD Status | Audit/Logging | Tests |
|------------|--------|------------|-----------|--------|-------------------|--------|------------------|------------------|-------------|---------------|-------|
| services | POS | Service | ✅ | 📋 | 📋 | 📋 | 📋 | POS/Services | 📋 | N/A | 📋 |
| service_categories | POS | ServicesCategory | ✅ | 📋 | 📋 | 📋 | 📋 | POS/Service Categories | 📋 | N/A | 📋 |
| products | POS | Product | N/A | 📋 | 📋 | 📋 | 📋 | POS/Products | 📋 | N/A | 📋 |
| p_o_s_orders | POS | POSOrder | N/A | 📋 | 📋 | 📋 | 📋 | POS/Sales | 📋 | N/A | 📋 |
| p_o_s_order_items | POS | POSOrderItem | N/A | 📋 | 📋 | 📋 | 📋 | POS/Sales | 📋 | N/A | 📋 |
| p_o_s_stores | POS | POSStore | N/A | 📋 | 📋 | 📋 | 📋 | POS/Stores | 📋 | N/A | 📋 |
| p_o_s_channels | POS | POSChannel | N/A | 📋 | 📋 | 📋 | 📋 | POS/Channels | 📋 | N/A | 📋 |

## Night Audit System

| Table Name | Module | Model Name | Migration | Seeder | Controller/Service | Routes | Policy/Permission | Sidebar Location | CRUD Status | Audit/Logging | Tests |
|------------|--------|------------|-----------|--------|-------------------|--------|------------------|------------------|-------------|---------------|-------|
| night_audit_log | Night Audit | N/A | ✅ | 📋 | 📋 | 📋 | 📋 | Night Audit/Audit History | 📋 | 📋 | 📋 |
| night_audit_occupancy_snapshot | Night Audit | N/A | ✅ | 📋 | 📋 | 📋 | 📋 | Night Audit/Occupancy Snapshots | 📋 | 📋 | 📋 |
| night_audit_noshow_log | Night Audit | N/A | ✅ | 📋 | 📋 | 📋 | 📋 | Night Audit/No-Show Processing | 📋 | 📋 | 📋 |
| night_audit_snapshot_queue | Night Audit | N/A | ✅ | 📋 | 📋 | 📋 | 📋 | Night Audit/Snapshot Queue | 📋 | 📋 | 📋 |
| no_show_charge_rules | Night Audit | N/A | ✅ | 📋 | 📋 | 📋 | 📋 | Night Audit/Settings | 📋 | N/A | 📋 |
| early_late_charge_configs | Night Audit | N/A | ✅ | 📋 | 📋 | 📋 | 📋 | Night Audit/Settings | 📋 | N/A | 📋 |
| business_date_transactions | Night Audit | N/A | ✅ | 📋 | 📋 | 📋 | 📋 | Night Audit/Business Date Transactions | 📋 | N/A | 📋 |

## Sources & Commissions

| Table Name | Module | Model Name | Migration | Seeder | Controller/Service | Routes | Policy/Permission | Sidebar Location | CRUD Status | Audit/Logging | Tests |
|------------|--------|------------|-----------|--------|-------------------|--------|------------------|------------------|-------------|---------------|-------|
| sources | Marketing | Source | ✅ | 📋 | 📋 | 📋 | 📋 | Marketing/Sources | 📋 | N/A | 📋 |
| commission_payments | Marketing | CommissionPayment | ✅ | 📋 | 📋 | 📋 | 📋 | Marketing/Commission Report | 📋 | N/A | 📋 |

## Housekeeping & Maintenance

| Table Name | Module | Model Name | Migration | Seeder | Controller/Service | Routes | Policy/Permission | Sidebar Location | CRUD Status | Audit/Logging | Tests |
|------------|--------|------------|-----------|--------|-------------------|--------|------------------|------------------|-------------|---------------|-------|
| unit_cleanings | Housekeeping | UnitCleaning | 📋 | 📋 | 📋 | 📋 | 📋 | Rooms/Housekeeping Board | 📋 | N/A | 📋 |
| unit_maintenance | Maintenance | UnitMaintenance | 📋 | 📋 | 📋 | 📋 | 📋 | Rooms/Maintenance Requests | 📋 | N/A | 📋 |

## Marketing & Revenue

| Table Name | Module | Model Name | Migration | Seeder | Controller/Service | Routes | Policy/Permission | Sidebar Location | CRUD Status | Audit/Logging | Tests |
|------------|--------|------------|-----------|--------|-------------------|--------|------------------|------------------|-------------|---------------|-------|
| promo_codes | Marketing | PromoCode | ✅ | 📋 | 📋 | 📋 | 📋 | Marketing/Promo Codes | 📋 | N/A | 📋 |
| promo_code_logs | Marketing | PromoCodeLog | 📋 | 📋 | 📋 | 📋 | 📋 | Marketing/Promo Logs | 📋 | N/A | 📋 |
| offers | Marketing | Offer | 📋 | 📋 | 📋 | 📋 | 📋 | Marketing/Offers | 📋 | N/A | 📋 |
| special_prices | Marketing | SpecialPrice | 📋 | 📋 | 📋 | 📋 | 📋 | Marketing/Special Prices | 📋 | N/A | 📋 |

## Website CMS

| Table Name | Module | Model Name | Migration | Seeder | Controller/Service | Routes | Policy/Permission | Sidebar Location | CRUD Status | Audit/Logging | Tests |
|------------|--------|------------|-----------|--------|-------------------|--------|------------------|------------------|-------------|---------------|-------|
| website_pages | Website | WebsitePage | 📋 | 📋 | 📋 | 📋 | 📋 | Website CMS/Pages | 📋 | N/A | 📋 |
| website_galleries | Website | WebsiteGallery | 📋 | 📋 | 📋 | 📋 | 📋 | Website CMS/Galleries | 📋 | N/A | 📋 |
| website_settings | Website | WebsiteSetting | 📋 | 📋 | 📋 | 📋 | 📋 | Website CMS/Settings | 📋 | N/A | 📋 |

## Integrations

| Table Name | Module | Model Name | Migration | Seeder | Controller/Service | Routes | Policy/Permission | Sidebar Location | CRUD Status | Audit/Logging | Tests |
|------------|--------|------------|-----------|--------|-------------------|--------|------------------|------------------|-------------|---------------|-------|
| integrations | Integrations | Integration | 📋 | 📋 | 📋 | 📋 | 📋 | Integrations/Settings | 📋 | N/A | 📋 |
| integration_logs | Integrations | IntegrationLog | 📋 | 📋 | 📋 | 📋 | 📋 | Integrations/Logs | 📋 | N/A | 📋 |
| integration_settings | Integrations | IntegrationSettings | 📋 | 📋 | 📋 | 📋 | 📋 | Integrations/Settings | 📋 | N/A | 📋 |

## System Tables

| Table Name | Module | Model Name | Migration | Seeder | Controller/Service | Routes | Policy/Permission | Sidebar Location | CRUD Status | Audit/Logging | Tests |
|------------|--------|------------|-----------|--------|-------------------|--------|------------------|------------------|-------------|---------------|-------|
| activity_log | System | Activity | ✅ | N/A | ✅ | ✅ | N/A | System Admin/Activity Log | 🔄 | N/A | 🔄 |
| sessions | System | N/A | ✅ | N/A | N/A | N/A | N/A | N/A | N/A | N/A | N/A |
| personal_access_tokens | System | N/A | ✅ | N/A | N/A | N/A | N/A | N/A | N/A | N/A | N/A |
| uploaded_media | System | UploadedMedia | N/A | N/A | N/A | N/A | N/A | N/A | N/A | N/A | N/A |

## Status Summary

- **Tables with Migrations**: 50+ (including those in the phase 1 and 2 migrations)
- **Tables with Models**: 40+ (in app/ directory)
- **Tables Needing Full CRUD**: All business tables (estimated 60+)
- **Tables Needing Permissions**: All business tables (estimated 60+)
- **Tables Needing Sidebar Items**: All business modules (estimated 15+ modules)
- **Tables Needing Tests**: All business tables (estimated 60+)

## Implementation Priority

### Phase 1: Core Operations
1. Reservations (✅ Migrations exist, need CRUD implementation)
2. Units/Rooms (✅ Migrations exist, need CRUD implementation)
3. Guests/Companies (✅ Migrations exist, need CRUD implementation)
4. Front Desk Operations (✅ Migrations exist, need CRUD implementation)

### Phase 2: Financial Management
1. Transactions (✅ Migrations exist, need CRUD implementation)
2. Service Logs (✅ Migrations exist, need CRUD implementation)
3. Invoices and Credit Notes (✅ Migrations exist, need CRUD implementation)
4. Cashier Shifts (✅ Migrations exist, need CRUD implementation)

### Phase 3: Advanced Features
1. Night Audit (✅ Migrations exist, need implementation)
2. Reports (Need controllers and views)
3. Integrations (Need configuration UI)
4. Housekeeping (Need UI implementation)

## Next Steps
1. Begin implementing missing CRUD operations for existing models
2. Create missing models for tables that only have migrations
3. Develop permissions and policies for all modules
4. Build the sidebar navigation with proper permission checks
5. Implement the demo seeders as planned