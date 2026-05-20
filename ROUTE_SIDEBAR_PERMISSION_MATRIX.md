# ROUTE_SIDEBAR_PERMISSION_MATRIX.md - Fandaqah Hotel PMS

## A. DASHBOARDS
| Sidebar Item | Route Name | Permission | Status |
|--------------|------------|------------|--------|
| Main Dashboard | `dashboard.main` | `view dashboard` | ✅ |
| Occupancy Dashboard | `dashboard.occupancy` | `view occupancy dashboard` | ✅ |
| Revenue Dashboard | `dashboard.revenue` | `view revenue dashboard` | ✅ |
| Night Audit Dashboard | `night-audit.dashboard` | `view night audit` | ✅ |
| Metabase Reports | `reports.metabase` | `view metabase reports` | ✅ |

## B. RESERVATIONS + FRONT DESK
| Sidebar Item | Route Name | Permission | Status |
|--------------|------------|------------|--------|
| All Reservations | `reservations.index` | `reservations.view` | 🔄 |
| Quick Reservation | `reservations.quick` | `reservations.create` | 📋 |
| Calendar Grid | `reservations.calendar` | `reservations.view` | 🔄 |
| Arrivals | `reservations.arrivals` | `reservations.view` | 🔄 |
| Departures | `reservations.departures` | `reservations.view` | 🔄 |
| In-House Guests | `reservations.in-house` | `reservations.view` | 🔄 |
| Group Reservations | `group-reservations.index` | `reservations.view` | 🔄 |

## C. SERVICED APARTMENTS + LONG-STAY
| Sidebar Item | Route Name | Permission | Status |
|--------------|------------|------------|--------|
| Buildings | `long-stay.buildings` | `units.view` | ✅ |
| Lease Agreements | `long-stay.contracts` | `contracts.view` | ✅ |
| Utility Meters | `long-stay.meters` | `finance.view` | ✅ |
| Tenant Profiles | `long-stay.tenants` | `customers.view` | ✅ |
| Unit Inventory | `long-stay.inventory` | `units.view` | ✅ |

## D. ROOMS + HOUSEKEEPING + MAINTENANCE
| Sidebar Item | Route Name | Permission | Status |
|--------------|------------|------------|--------|
| Housekeeping Board | `housekeeping.index` | `housekeeping.view` | 📋 |
| Maintenance Requests | `maintenance.index` | `maintenance.view` | 📋 |
| Room Status Logs | `room-status-logs.index` | `room_status.view` | ✅ |

## E. GUESTS + CUSTOMERS + COMPANIES
| Sidebar Item | Route Name | Permission | Status |
|--------------|------------|------------|--------|
| Guest Directory | `guests.index` | `guests.view` | 🔄 |
| Companies | `companies.index` | `companies.view` | 🔄 |
| Company Groups | `company-groups.index` | `ar.manage_company_groups` | ✅ |
| Blocked Guests | `blocked-guests.index` | `guests.view` | 📋 |

## F. POS + SERVICES
| Sidebar Item | Route Name | Permission | Status |
|--------------|------------|------------|--------|
| Services | `services.index` | `pos.view` | 🔄 |
| POS Sales | `pos.orders` | `pos.view` | 🔄 |
| Service Logs | `service-logs.index` | `pos.view` | 🔄 |

## G. FINANCE + ACCOUNTING
| Sidebar Item | Route Name | Permission | Status |
|--------------|------------|------------|--------|
| Transactions | `transactions.index` | `view financial` | 🔄 |
| Invoices | `invoices.index` | `view financial` | 🔄 |
| Receipts | `receipts.index` | `view financial` | 🔄 |
| Payments | `payments.index` | `view financial` | 🔄 |
| Promissories | `promissories.index` | `view promissories` | ✅ |
| City Ledger / AR | `city-ledger.index` | `ar.city_ledger.view` | ✅ |
| Trial Balance | `trial-balance.index` | `view financial` | ✅ |
| Cashier Shifts | `cashier-shifts.index` | `cashier.view` | ✅ |

## H. NIGHT AUDIT
| Sidebar Item | Route Name | Permission | Status |
|--------------|------------|------------|--------|
| Run Night Audit | `night-audit.run` | `night_audit.run` | ✅ |
| Audit Status | `night-audit.status` | `view night audit` | ✅ |
| Audit Logs | `night-audit.logs` | `night_audit.view_log` | ✅ |
| No-Show Preview | `no-show-preview` | `view noshow rules` | ✅ |

## I. REPORTS + ANALYTICS
| Sidebar Item | Route Name | Permission | Status |
|--------------|------------|------------|--------|
| Daily Report | `reports.daily` | `view reports` | 🔄 |
| Occupancy Report | `reports.occupancy` | `view reports` | 🔄 |
| Revenue Report | `reports.revenue` | `view reports` | 🔄 |
| ADR / RevPAR | `reports.adr-revpar` | `view reports` | 🔄 |

## L. INTEGRATIONS
| Sidebar Item | Route Name | Permission | Status |
|--------------|------------|------------|--------|
| ZATCA Settings | `integrations.zatca` | `configure integrations` | 🔄 |
| Qoyod Sync | `integrations.qoyod` | `configure integrations` | 🔄 |
| Shomoos Logs | `integrations.shomoos` | `view integrations` | 🔄 |

## M. SETTINGS + SYSTEM ADMIN
| Sidebar Item | Route Name | Permission | Status |
|--------------|------------|------------|--------|
| Team Profile | `settings.team` | `view settings` | ✅ |
| Users | `settings.users` | `user and roles` | ✅ |
| Roles & Perms | `settings.roles` | `user and roles` | ✅ |
| Activity Logs | `settings.activity-log` | `view activity logs` | ✅ |
