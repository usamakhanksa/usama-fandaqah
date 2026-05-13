# MIGRATION STRATEGY FOR FANDAQAH HOTEL PMS

## Overview
This document outlines the migration strategy for enhancing the Fandaqah Hotel Property Management System. The strategy includes baseline migrations based on existing schema and additional enhancement migrations to address missing indexes, foreign keys, team scoping, audit columns, and Saudi compliance features.

## Migration Dependency Order

1. **Existing Migrations** (Already in the system)
   - 2019_12_14_000001_create_personal_access_tokens_table.php
   - 2026_01_01_000000_create_hotel_tables.php
   - 2026_01_02_000000_add_rooms_details_and_metrics_tables.php
   - 2026_01_03_000000_add_guest_company_tables.php
   - 2026_01_04_000000_add_units_housing_tables.php
   - 2026_01_05_000000_add_reservation_workflow_tables.php
   - 2026_01_06_000000_add_financial_management_tables.php
   - 2026_01_07_000000_add_user_grouping_tables.php
   - 2026_01_08_000000_add_pos_module_tables.php
   - 2026_04_19_162213_create_additional_hotel_modules_tables.php
   - 2026_04_20_091845_create_sessions_table.php
   - 2026_04_20_092217_create_activity_log_table.php
   - 2026_04_20_092730_create_teams_and_team_users_tables.php
   - 2026_04_30_103800_add_phase1_pms_tables.php
   - 2026_04_30_103900_add_phase2_night_audit_ar_tables.php
   - 2026_05_03_000000_add_reservation_features_to_reservations_table.php

2. **Enhancement Migrations** (Added by this strategy)
   - 2026_05_03_140000_add_missing_indexes_and_team_scoping.php
   - 2026_05_03_140500_add_audit_columns_and_correction_fields.php
   - 2026_05_03_141000_add_sidebar_registry_tables.php
   - 2026_05_03_141500_add_additional_indexes_and_foreign_keys.php
   - 2026_05_03_142000_add_saudi_compliance_fields.php

## Migration Details

### 1. add_missing_indexes_and_team_scoping.php
- Adds team_id to tables that should be team-scoped but don't have it
- Adds missing indexes for performance optimization
- Adds missing foreign key constraints
- Focuses on tables like: rooms, room_types, guests, company_profiles, promo_codes, etc.

### 2. add_audit_columns_and_correction_fields.php
- Adds audit fields to track who created/updated records
- Adds correction and reversal capabilities for financial records
- Adds ZATCA compliance fields to invoices
- Adds demo-safe flags for demo environments

### 3. add_sidebar_registry_tables.php
- Creates tables for sidebar menu items management
- Creates feature toggles table for enabling/disabling features
- Creates notification templates table
- Adds demo flags to teams

### 4. add_additional_indexes_and_foreign_keys.php
- Adds composite indexes for common query patterns
- Adds missing foreign key constraints
- Improves soft-delete table performance
- Adds full-text search indexes where appropriate

### 5. add_saudi_compliance_fields.php
- Adds ZATCA e-invoicing compliance fields
- Adds Shomoos guest verification fields
- Adds VAT and tax compliance fields
- Adds Saudi-specific address and timezone fields
- Adds Hijri calendar support

## Rollback Safety Notes

1. **Safe Rollbacks**: All migrations include proper down() methods that reverse the changes
2. **Foreign Key Dependencies**: Carefully managed to prevent constraint violations during rollbacks
3. **Data Preservation**: No data is deleted during rollback, only structural changes are reversed
4. **Sequential Rollback**: Rollbacks must happen in reverse order of application

## Production Migration Plan

### Pre-Migration Steps
1. Create a complete backup of the production database
2. Test all migrations on a staging environment identical to production
3. Schedule maintenance window for the migration
4. Notify all users about the planned downtime
5. Disable application access to prevent concurrent modifications

### Migration Execution
1. Stop all application instances and background jobs
2. Run migrations in sequence:
   ```
   php artisan migrate --force
   ```
3. Monitor the migration progress and watch for any errors
4. Verify that all tables have been properly updated
5. Run integrity checks on critical data

### Post-Migration Verification
1. Start application instances one by one
2. Perform smoke tests on critical functionality
3. Verify team scoping works correctly
4. Test financial calculations and reporting
5. Confirm ZATCA/Shomoos integrations work properly
6. Check that sidebar navigation loads correctly

### Rollback Plan (if needed)
If any critical issues are found:
1. Immediately stop all application instances
2. Execute rollback:
   ```
   php artisan migrate:rollback --step=5 --force
   ```
3. Restore from backup if rollback fails
4. Investigate and fix issues before attempting again

## Risk Assessment

### High Risk Areas
- Financial data integrity during addition of correction fields
- Team scoping changes affecting existing data
- Addition of required foreign key constraints

### Mitigation Strategies
- Comprehensive backups before migration
- Thorough testing in staging environment
- Step-by-step migration with verification checkpoints
- Prepared rollback procedures

## Performance Considerations

- Index additions may take time on large datasets
- Foreign key constraint additions may require table rebuilds
- Consider running migrations during low-usage periods
- Monitor database performance during migration

## Next Steps

After successful migration completion, proceed with:
1. Model updates to reflect new columns
2. Policy updates for enhanced permissions
3. Seeder development for demo data
4. Controller/CRUD implementation
5. Frontend development for new features