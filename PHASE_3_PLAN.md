# PHASE 3 PLAN - ROLES, PERMISSIONS, AND ACCESS CONTROL

## Overview
This document outlines the implementation plan for Phase 3 of the Fandaqah Hotel PMS, focusing on implementing granular Role-Based Access Control (RBAC) system with comprehensive permissions for all required roles in the hotel industry.

## Roles to Implement

### 1. Super Admin
- Full system access
- Multi-hotel oversight
- System configuration
- User management across all hotels

### 2. Hotel Owner
- Full access to owned properties
- Financial oversight
- Staff management
- Strategic reporting

### 3. General Manager
- All operational modules access
- Financial reporting
- Staff scheduling
- Guest relations oversight

### 4. Front Desk Manager
- Reservation management
- Check-in/check-out oversight
- Guest services coordination
- Front office staff supervision

### 5. Front Desk Agent
- Reservation handling
- Check-in/check-out processing
- Guest registration
- Basic inquiry handling

### 6. Housekeeping Supervisor
- Housekeeping task management
- Room status oversight
- Cleaning schedule coordination
- Staff assignment

### 7. Housekeeper
- Room cleaning assignments
- Status updates
- Supply requests
- Issue reporting

### 8. Maintenance User
- Maintenance requests handling
- Equipment tracking
- Repair scheduling
- Vendor coordination

### 9. Accountant
- Financial transactions review
- Invoice management
- Payment processing
- Financial reporting

### 10. Cashier
- Cash handling
- Payment processing
- Cashier shift management
- End-of-shift reconciliation

### 11. Revenue Manager
- Pricing strategy
- Occupancy optimization
- Market analysis
- Rate management

### 12. Marketing Manager
- Promotions management
- Campaign oversight
- Guest communication
- Channel management

### 13. Auditor
- Financial audits
- Compliance checks
- Transaction reviews
- Reporting accuracy

### 14. Integration Admin
- Third-party integrations
- API management
- System connectivity
- Data synchronization

### 15. Read-only Viewer
- Reporting access only
- Data viewing without modification
- Dashboard access
- Historical data review

## Module-Specific Permissions

### Reservations Module
- reservations.view
- reservations.create
- reservations.update
- reservations.delete
- reservations.checkin
- reservations.checkout
- reservations.cancel
- reservations.transfer
- reservations.extend
- reservations.noshow
- reservations.export

### Units/Rooms Module
- units.view
- units.create
- units.update
- units.delete
- units.status
- units.maintenance
- units.cleaning
- units.export

### Transactions Module
- transactions.view
- transactions.create
- transactions.update
- transactions.reverse
- transactions.export

### Invoices Module
- invoices.view
- invoices.create
- invoices.credit-note
- invoices.send-zatca
- invoices.print
- invoices.export

### Night Audit Module
- night-audit.view
- night-audit.run
- night-audit.rerun
- night-audit.close
- night-audit.reopen
- night-audit.export

### Settings Module
- settings.view
- settings.update

### Users Module
- users.view
- users.create
- users.update
- users.delete
- users.impersonate

### Integrations Module
- integrations.view
- integrations.update
- integrations.test

### Reports Module
- reports.view
- reports.export

## Implementation Approach

### 1. Permission Structure Setup
- Create comprehensive permission list in config file
- Implement permission seeding
- Link permissions to roles in database

### 2. Role Creation and Assignment
- Create default roles for each hotel upon setup
- Implement role assignment interface
- Create role-based middleware

### 3. Permission Checking Implementation
- Blade directives for UI permission checks
- Middleware for route protection
- Method-level permission checks in controllers

### 4. Dynamic Permission Assignment
- Interface for assigning permissions to roles
- Role inheritance system
- Permission cascading rules

### 5. Testing
- Role-based access tests
- Permission inheritance tests
- Security tests to ensure proper access control

## Technical Implementation

### Database Changes
- Roles table with team_id relationship
- Permissions table with hierarchical structure
- Role-permission pivot table
- User-role pivot table with team scoping

### Configuration Files
- Permissions configuration with localized names
- Role hierarchy definition
- Default permissions for each role type

### Middleware Implementation
- Role-based access middleware
- Permission-based access middleware
- Team-scoped access middleware

## Expected Deliverables

1. Complete RBAC system implementation
2. Default roles and permissions seeding
3. Permission management interface
4. Role assignment functionality
5. Middleware for access control
6. Tests for all permission scenarios
7. Documentation for the RBAC system