# PHASE 4 PLAN - DEMO SEEDERS

## Overview
This document outlines the implementation plan for Phase 4 of the Fandaqah Hotel PMS, focusing on creating realistic demo data in ordered seed classes that will allow for proper demonstration of all system features and capabilities.

## Seeder Order and Requirements

### 1. CountryCitySeeder
- Seeds countries and cities with Saudi Arabia as primary focus
- Includes major Saudi cities and regions
- Supports Arabic and English names

### 2. HotelTypeSeeder
- Seeds different types of hotels (luxury, economy, etc.)
- Supports different classifications

### 3. TeamSeeder
- Creates a primary demo hotel team
- Sets up team profile with Saudi compliance information
- Includes demo-specific configurations

### 4. UserRolePermissionSeeder
- Creates demo users for different roles
- Assigns appropriate permissions to each user
- Links users to the demo team

### 5. TeamSettingsSeeder
- Sets up hotel-specific configurations
- Configures business date, night audit settings
- Sets up payment methods and VAT configurations

### 6. PaymentMethodBankSenderSeeder
- Seeds various payment methods
- Creates bank and sender information
- Sets up financial configurations

### 7. UnitCategorySeeder
- Creates different unit/room categories
- Sets up pricing for different categories
- Defines capacity and amenities

### 8. UnitFeatureOptionSeeder
- Seeds unit features and options
- Creates icons and descriptions for features
- Links features to categories

### 9. UnitSeeder
- Creates demo units/rooms based on categories
- Sets up room statuses and availability
- Links to floors and room types

### 10. CustomerSeeder
- Creates diverse customer profiles
- Includes Saudi and non-Saudi customers
- Sets up identification details

### 11. CompanyAndGroupSeeder
- Creates demo companies and company groups
- Sets up corporate account details
- Configures credit limits and payment terms

### 12. SourceChannelSeeder
- Seeds reservation sources and channels
- Includes OTA and direct booking sources
- Sets up commission structures

### 13. OfferSpecialPricePromoSeeder
- Creates promotional offers and discounts
- Sets up special pricing rules
- Configures promotional codes

### 14. ReservationSeeder
- Creates diverse reservation types
- Includes individual, group, walk-in, online reservations
- Sets up various statuses and dates

### 15. ReservationGuestSeeder
- Links guests to reservations
- Sets up guest registration details
- Includes Shomoos verification status

### 16. CheckinCheckoutSeeder
- Creates check-in/check-out records
- Simulates actual hotel stays
- Links to reservations and units

### 17. ServiceCategorySeeder
- Creates service categories
- Sets up service pricing
- Defines service tax implications

### 18. ServiceSeeder
- Seeds specific services
- Sets up service details and pricing
- Links to categories

### 19. POSServiceLogSeeder
- Creates POS transactions
- Links to reservations and services
- Sets up service logs

### 20. TransactionSeeder
- Creates financial transactions
- Includes deposits and withdrawals
- Links to reservations and payment methods

### 21. InvoiceCreditNoteSeeder
- Creates invoices and credit notes
- Sets up ZATCA compliance information
- Links to transactions

### 22. PromissorySeeder
- Creates promissory notes
- Sets up collection schedules
- Links to reservations and companies

### 23. CashierShiftSeeder
- Creates cashier shift records
- Sets up opening and closing balances
- Links to users and transactions

### 24. HousekeepingSeeder
- Creates housekeeping tasks
- Sets up cleaning schedules
- Links to units and staff

### 25. MaintenanceSeeder
- Creates maintenance requests
- Sets up repair schedules
- Links to units and vendors

### 26. NightAuditSeeder
- Creates night audit records
- Sets up occupancy snapshots
- Links to business dates

### 27. WebsiteSeeder
- Creates website content
- Sets up pages and galleries
- Configures SEO settings

### 28. IntegrationSeeder
- Creates integration configurations
- Sets up API credentials
- Configures integration settings

### 29. NotificationSeeder
- Creates notification templates
- Sets up triggers and recipients
- Configures notification methods

### 30. ReportSeeder
- Creates sample report data
- Sets up report configurations
- Configures report schedules

## Demo Scenario Requirements

### Primary Hotel Setup
- 1 main hotel with complete profile
- Saudi compliance configurations enabled
- Complete team with roles

### User Roles Representation
- At least 3 different user roles
- Super admin user
- Regular front desk user

### Unit Categories
- 5 different unit categories
- Mix of room types (single, double, suite)
- Different price points

### Units Inventory
- 30+ units with mixed statuses
- Various amenities and features
- Different floors and locations

### Customer Diversity
- 20+ customers with Saudi/non-Saudi examples
- Various identification types
- Corporate and individual profiles

### Reservation Types
- Individual reservation
- Group reservation
- Walk-in booking
- Online reservation
- OTA/channel reservation

### Stay Statuses
- Active checked-in stay
- Completed checked-out stay
- Cancelled reservation
- No-show reservation
- Extended reservation
- Room transfer

### Financial Operations
- POS service sale
- Quick payment
- Cash transaction
- Card transaction
- Bank transfer
- Promissory note
- Invoice and credit note

### Operational Activities
- Open and closed cashier shift
- Night audit run with occupancy snapshot
- Housekeeping tasks
- Maintenance tasks
- Website pages and settings
- Integration configurations

## Implementation Approach

### 1. Sequential Seeder Creation
- Create each seeder class following the dependency order
- Ensure foreign key constraints are respected
- Use factory classes for data generation

### 2. Realistic Data Generation
- Create meaningful data that reflects real hotel operations
- Ensure data relationships are properly maintained
- Include both Arabic and English content

### 3. Demo-Safe Flags
- Mark demo data with is_demo flag where applicable
- Include demo expiration dates
- Ensure data can be safely cleared

### 4. Stable IDs
- Use stable IDs where necessary for relationships
- Maintain consistent data across environments
- Create predictable demo scenarios

### 5. Error Prevention
- Handle foreign key dependency order
- Prevent duplicate entries
- Validate data integrity

## Testing Requirements

### 1. Migration Test
- Run `php artisan migrate:fresh --seed` successfully
- Verify all seeders run without errors
- Check data relationships

### 2. Functional Test
- Verify all demo scenarios work correctly
- Test all role-based permissions
- Validate Saudi compliance features

### 3. Data Integrity Test
- Verify all foreign key relationships
- Check for orphaned records
- Validate soft delete functionality

## Expected Deliverables

1. All 30 seeder classes implemented
2. Complete demo dataset with relationships
3. Successful `migrate:fresh --seed` execution
4. Functional demo environment
5. Documentation for demo scenarios