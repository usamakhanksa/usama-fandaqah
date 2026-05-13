# SCHEMA_AUDIT.md - Fandaqah Hotel PMS Database Analysis

## Overview
Analysis of the Fandaqah Hotel Property Management System database schema based on existing migrations and models.

## Database Statistics
- **Total Migration Files**: 43 (as of May 3, 2026)
- **Estimated Total Tables**: ~80+ tables
- **Database Type**: MySQL (based on Laravel migrations)
- **Multi-tenancy**: Implemented with team_id scoping
- **Soft Deletes**: Implemented across most business tables
- **Primary Keys**: Standard unsignedBigInteger id columns

## Core Business Modules Identified

### 1. Authentication & Authorization
- **Tables**: users, roles, team_users
- **Features**: Role-based access control, team-based scoping, soft deletes
- **Multi-tenancy**: Yes (via team_users relationship)
- **Foreign Keys**: users.id -> roles.team_id, team_users constraints

### 2. Hotels & Teams (Multi-tenancy)
- **Tables**: teams, team_users, team_counters
- **Features**: Business date management, night audit settings, currency support
- **Multi-tenancy**: Core tenant concept
- **Columns**: business_date, night_audit_cutoff_time, night_audit_auto_run_time, currency

### 3. Reservations
- **Tables**: reservations, reservation_drafts, reservation_extensions, reservation_ratings
- **Features**: Check-in/out, status tracking, company associations, audit locks, shomoos verification
- **Multi-tenancy**: team_id on reservations
- **Financial**: Audit locks, noshow flagging, cancellation tracking
- **Foreign Keys**: guest_id, company_id, source_id

### 4. Units/Rooms
- **Tables**: units, rooms, room_types, unit_types, unit_statuses
- **Features**: Status tracking, capacity management, floor assignments
- **Multi-tenancy**: Implicit through team-scoped reservations
- **Foreign Keys**: unit_type_id, unit_status_id

### 5. Guests & Companies
- **Tables**: guests, companies, company_groups, company_notes, countries, cities
- **Features**: Corporate accounts, credit limits, tax info, contact people
- **Multi-tenancy**: team_id on companies
- **Compliance**: Tax number fields, address components for ZATCA

### 6. Financial Management
- **Tables**: transactions, service_logs, cashier_shifts, promissories, promissory_payment_log, invoice_credit_notes, invoice_transfers
- **Features**: Multiple payment methods, audit trails, night audit integration, ZATCA compliance
- **Multi-tenancy**: team_id on all financial tables
- **Financial**: Decimal precision for amounts, tax calculations, correction mechanisms

### 7. POS & Services
- **Tables**: services, service_logs, service_log_notes, products, p_o_s_orders, p_o_s_order_items
- **Features**: POS operations, inventory, multiple store channels
- **Multi-tenancy**: Team scope required

### 8. Night Audit System
- **Tables**: night_audit_log, night_audit_occupancy_snapshot, night_audit_noshow_log, night_audit_snapshot_queue
- **Features**: Automated nightly processing, occupancy tracking, revenue calculations
- **Business Logic**: Business date management, freezing transactions
- **Metrics**: ADR, RevPAR, occupancy percentages

### 9. Sources & Commissions
- **Tables**: sources, commission_payments
- **Features**: OTA/travel agent tracking, commission calculations
- **Multi-tenancy**: team_id on sources

## Foreign Key Analysis

### Strong Relationship Patterns
1. **Team Scoping**: Most business tables have team_id foreign key
2. **User References**: Created_by, updated_by patterns across tables
3. **Reservation Centric**: Many tables link to reservations
4. **Financial Chain**: Transactions -> Service_logs -> Reservations

### Missing Foreign Keys
- Some older tables may lack foreign key constraints (legacy support)
- Cross-module relationships sometimes missing direct FKs

## Multi-Tenancy Implementation
- **Core Concept**: teams table as tenants
- **Scoping Pattern**: team_id column on business tables
- **User Assignment**: current_team_id on users table
- **Relationship Pattern**: Team model relationships scope data

## Soft Delete Implementation
- **Pattern**: deleted_at timestamp column
- **Coverage**: Most business tables support soft deletes
- **Financial Caution**: Critical financial tables should avoid soft deletes

## Money Field Analysis
- **Data Type**: Mixed use of decimal vs bigInteger for amounts
- **Precision**: Mostly (10,2) or higher for financial data
- **Concern**: Some tables use bigInteger for amounts (should be decimal)

## Index Analysis
- **Primary**: Standard id primary keys
- **Common**: Indexes on created_at, updated_at, team_id
- **Business**: Status, date, and lookup indexes
- **Missing**: Some potentially missing indexes for high-volume queries

## Audit Trail Implementation
- **Activity Log**: Dedicated activity_logs table
- **Action Events**: Possible Nova action events
- **Financial**: Transaction and service log tracking
- **Business**: Reservation audit locks and business date tracking

## Saudi Compliance Features
- **ZATCA Fields**: invoice_credit_notes table with zatca fields
- **Tax Components**: Separate tax amount columns
- **Address Requirements**: Detailed address components for tax invoices
- **Shomoos**: Verification status fields in reservations

## Schema Risks Identified

### High Priority
1. **Money Precision**: Some tables use bigInteger instead of decimal for amounts
2. **Foreign Key Consistency**: Some legacy tables may lack proper constraints
3. **Team Scoping**: Verify all business tables have team_id
4. **Financial Integrity**: Ensure financial tables handle corrections properly

### Medium Priority
1. **Index Coverage**: Verify frequently queried columns are indexed
2. **Data Types**: JSON vs TEXT for flexible fields
3. **Default Values**: Consistent defaults across similar fields
4. **Nullable Fields**: Validate business requirements for nullable fields

### Low Priority
1. **Column Naming**: Consistency across the schema
2. **Comments**: Descriptive column comments

## Recommendations for Phase 1 Migration

### Baseline Strategy
1. **Preserve Existing**: Maintain current schema for operational systems
2. **Standardize**: Apply consistent patterns to new tables
3. **Correct**: Fix identified schema risks in new additions
4. **Document**: Comprehensive migration documentation

### Enhancement Opportunities
1. **Add Missing Indexes**: team_id, business_date, status, reservation_id
2. **Financial Corrections**: Add correction/reversal columns where missing
3. **Audit Improvements**: Add audit columns where missing
4. **Saudi Compliance**: Ensure all financial tables support ZATCA

## Conclusion
The current schema provides a solid foundation for a hotel PMS with multi-tenancy and Saudi compliance features. However, attention is needed for consistency in financial data types, comprehensive team scoping, and performance optimizations through indexing.

Next step: Create SCHEMA_COVERAGE_MATRIX.md to track all tables, models, and implementation status.