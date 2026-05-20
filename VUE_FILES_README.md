# Fandaqah Hotel PMS - Vue.js Documentation

## Overview

This document provides comprehensive documentation for all Vue.js files in the Fandaqah Hotel PMS application.

## Project Structure

```
resources/js/
├── App.vue                 # Main application component
├── main.js                 # Application entry point
├── router/
│   └── index.js            # Vue Router configuration
├── services/
│   └── api.js              # Axios API client
├── i18n/
│   ├── index.js            # Internationalization setup
│   ├── en.json             # English translations
│   └── ar.json             # Arabic translations
├── pages/                  # Page components (routed views)
└── components/             # Reusable components
```

## Pages Documentation

### Authentication
| Route | Component | Description |
|-------|-----------|-------------|
| `/login` | LoginPage.vue | User login page |

### Dashboards
| Route | Component | Description |
|-------|-----------|-------------|
| `/dashboard` | OverviewDashboard.vue | Main overview dashboard |
| `/dashboard/occupancy` | OccupancyDashboard.vue | Occupancy analytics |
| `/dashboard/revenue` | RevenueDashboard.vue | Revenue analytics |
| `/dashboard/front-desk` | FrontDeskDashboard.vue | Front desk operations |
| `/dashboard/finance` | FinanceDashboard.vue | Financial overview |
| `/dashboard/ar` | ARDashboard.vue | Accounts receivable dashboard |
| `/dashboard/cashier` | CashierDashboard.vue | Cashier operations |
| `/dashboard/commissions` | CommissionDashboard.vue | Commission tracking |
| `/dashboard/metabase` | MetabaseDashboard.vue | Metabase reports integration |
| `/dashboard/integration-health` | IntegrationHealthDashboard.vue | System integrations status |
| `/night-audit` | NightAuditDashboard.vue | Night audit dashboard |

### Reservations
| Route | Component | Description |
|-------|-----------|-------------|
| `/reservations` | ReservationManagementPage.vue | Main reservation list (redirect) |
| `/reservations/management` | ReservationManagementPage.vue | Reservation management |
| `/reservations/management/:bookingId` | BookingDetailsPage.vue | Booking details view |
| `/reservations/schedule` | ReservationSchedulePage.vue | Reservation schedule |
| `/reservations/create` | ReservationCreatePage.vue | Create reservation wizard |
| `/reservations/quick-create` | ReservationQuickCreatePage.vue | Quick reservation creation |
| `/reservations/success/:bookingId` | ReservationSuccessPage.vue | Success page after booking |
| `/reservations/calendar` | ReservationCalendarPage.vue | Calendar view |
| `/reservations/arrivals` | ReservationArrivalsPage.vue | Today's arrivals |
| `/reservations/departures` | ReservationDeparturesPage.vue | Today's departures |
| `/reservations/in-house` | ReservationInHousePage.vue | In-house guests |
| `/reservations/online` | ReservationOnlinePage.vue | Online reservations |
| `/reservations/ota` | ReservationOTAPage.vue | OTA channel reservations |
| `/reservations/groups` | ReservationGroupPage.vue | Group reservations |
| `/reservations/groups/create` | ReservationGroupCreatePage.vue | Create group reservation |
| `/reservations/transfers` | ReservationTransfersPage.vue | Room transfers |
| `/reservations/extensions` | ReservationExtensionsPage.vue | Stay extensions |
| `/reservations/contracts` | ReservationContractsPage.vue | Digital contracts |
| `/reservations/signatures` | ReservationSignaturesPage.vue | Signature management |
| `/reservations/ratings` | ReservationRatingsPage.vue | Guest ratings |
| `/reservations/cancellations` | ReservationCancellationsPage.vue | Cancellation management |
| `/reservations/messages` | ReservationMessagesPage.vue | Reservation messages |
| `/reservations/audit-locks` | ReservationAuditLocksPage.vue | Audit locks |
| `/new-reservation` | NewReservationPage.vue | New reservation page |
| `/new-reservation/:id` | NewReservationPage.vue | Edit reservation |

### Front Desk Operations
| Route | Component | Description |
|-------|-----------|-------------|
| `/front-desk/check-in` | FrontDeskCheckInPage.vue | Guest check-in |
| `/front-desk/check-out` | FrontDeskCheckOutPage.vue | Guest check-out |
| `/front-desk/walk-in` | FrontDeskWalkInPage.vue | Walk-in booking |
| `/front-desk/registration` | FrontDeskRegistrationPage.vue | Guest registration |
| `/front-desk/room-assignment` | FrontDeskRoomAssignmentPage.vue | Room assignment |
| `/front-desk/room-swap` | FrontDeskRoomSwapPage.vue | Room swap |
| `/front-desk/early-check-in` | FrontDeskStayChargePage.vue | Early check-in |
| `/front-desk/late-checkout` | FrontDeskStayChargePage.vue | Late checkout |
| `/front-desk/no-show` | FrontDeskNoShowPage.vue | No-show handling |
| `/front-desk/wake-up-calls` | FrontDeskWakeUpCallsPage.vue | Wake-up calls |
| `/front-desk/iptv-needs` | FrontDeskIptvNeedsPage.vue | IPTV guest needs |
| `/front-desk/registration-cards` | FrontDeskRegistrationCardsPage.vue | Registration cards |
| `/front-desk/balance-transfer` | FrontDeskBalanceTransferPage.vue | Balance transfer |

### Rooms & Housekeeping
| Route | Component | Description |
|-------|-----------|-------------|
| `/units` | UnitsPage.vue | Units/rooms list |
| `/units/availability` | UnitsAvailabilityPage.vue | Availability board |
| `/units/status-board` | UnitsStatusBoardPage.vue | Status board |
| `/unit-categories` | UnitCategoriesPage.vue | Unit categories |
| `/housekeeping/board` | HousekeepingBoardPage.vue | Housekeeping board |
| `/unit-cleanings` | UnitCleaningsPage.vue | Cleaning management |
| `/unit-maintenances` | UnitMaintenancesPage.vue | Maintenance requests |
| `/room-status-log` | RoomStatusLogPage.vue | Room status log |
| `/room-types` | RoomTypesPage.vue | Room types |
| `/room-floors` | RoomFloorsPage.vue | Room floors |
| `/unit-features` | UnitFeaturesPage.vue | Unit features |
| `/unit-options` | UnitOptionsPage.vue | Unit options |
| `/unit-category-services` | UnitCategoryServicesPage.vue | Category services |

### Guests & Companies
| Route | Component | Description |
|-------|-----------|-------------|
| `/guests` | GuestDirectoryPage.vue | Guest directory |
| `/customers` | CustomersPage.vue | Customer profiles |
| `/customers/merge` | CustomerMergePage.vue | Merge duplicate customers |
| `/companies` | CompaniesPage.vue | Company management |
| `/company-groups` | CompanyGroupsPage.vue | Company groups |
| `/blocked-guests` | BlockedGuestsPage.vue | Blocked guests list |
| `/turnaway-logs` | TurnawayLogsPage.vue | Turnaway logs |
| `/turnaway-reasons` | TurnawayReasonsPage.vue | Turnaway reasons |
| `/highlights` | HighlightsPage.vue | Guest highlights |

### POS & Services
| Route | Component | Description |
|-------|-----------|-------------|
| `/pos/dashboard` | PosDashboardPage.vue | POS dashboard |
| `/pos/sale` | PosSalePage.vue | New sale |
| `/pos/service-categories` | ServiceCategoriesManagePage.vue | Service categories |
| `/pos/services-manage` | ServicesManagePage.vue | Services management |
| `/pos/service-logs` | ServiceLogsPage.vue | Service logs |
| `/pos/quick-payments` | QuickPaymentsPage.vue | Quick payments |
| `/pos/pos-transactions` | POSTransactionsPage.vue | POS transactions |
| `/pos/service-qoyods` | ServiceQoyodPage.vue | Qoyod integration |
| `/pos` | POSStorePage.vue | POS store (redirect) |
| `/pos/store` | POSStorePage.vue | Store management |
| `/pos/services` | POSServicesPage.vue | POS services |
| `/pos/services/create` | POSServiceCreatePage.vue | Create service |
| `/pos/products` | POSProductsPage.vue | Products |
| `/pos/products/brands` | POSBrandsPage.vue | Product brands |
| `/pos/products/categories` | POSCategoriesPage.vue | Product categories |
| `/pos/products/sub-categories` | POSSubCategoriesPage.vue | Product sub-categories |
| `/services` | ServiceCategoriesPage.vue | Service categories |

### Finance
| Route | Component | Description |
|-------|-----------|-------------|
| `/financial` | ReceiptsIndex.vue | Financial (redirect to receipts) |
| `/financial/receipts` | ReceiptsIndex.vue | Receipts management |
| `/financial/receipts/create` | FinancialEntryWizardPage.vue | Create receipt |
| `/financial/receipts/success/:id` | FinancialSuccessPage.vue | Receipt success |
| `/financial/expenses` | FinancialManagementPage.vue | Expenses |
| `/financial/bills` | FinancialManagementPage.vue | Bills |
| `/financial/fund-movement` | FinancialManagementPage.vue | Fund movement |
| `/financial/credit-notes` | CreditNotesIndex.vue | Credit notes |
| `/finance/receipts` | ReceiptsIndex.vue | Finance receipts |
| `/finance/payments` | PaymentsIndex.vue | Payments |
| `/finance/invoices` | InvoicesIndex.vue | Invoices |
| `/finance/banks` | BanksIndex.vue | Banks |
| `/finance/senders` | SendersIndex.vue | Payment senders |
| `/finance/commission-payments` | CommissionPaymentsIndex.vue | Commission payments |
| `/finance/payment-correction` | PaymentCorrection.vue | Payment correction |
| `/finance/cashier-shifts` | CashierShiftsPage.vue | Cashier shifts |
| `/finance/room-status-logs` | RoomStatusLogsPage.vue | Room status logs |
| `/finance/travel-agents` | TravelAgentsPage.vue | Travel agents |
| `/finance/commissions` | CommissionsDashboard.vue | Commissions dashboard |
| `/finance/guest-ledger` | GuestLedgerPage.vue | Guest ledger |
| `/finance/deposit-ledger` | DepositLedgerPage.vue | Deposit ledger |
| `/finance/invoice-transfers` | InvoiceTransfersIndex.vue | Invoice transfers |
| `/finance/promissory-notes` | PromissoryNotesIndex.vue | Promissory notes |
| `/finance/promissory-collections` | PromissoryCollectionsIndex.vue | Promissory collections |

### Reports
| Route | Component | Description |
|-------|-----------|-------------|
| `/reports` | ReportsPage.vue | Reports dashboard |
| `/reports/daily` | DailyReport.vue | Daily report |
| `/reports/occupancy` | OccupancyReport.vue | Occupancy report |
| `/reports/revenue` | RevenueReport.vue | Revenue report |
| `/reports/forecast-history` | ForecastReport.vue | Forecast history |
| `/reports/no-show` | NoShowReport.vue | No-show report |
| `/reports/cancellation` | CancellationReport.vue | Cancellation report |
| `/reports/commission` | CommissionReport.vue | Commission report |
| `/reports/paid-outs` | PaidOutsReport.vue | Paid-outs report |
| `/reports/turnaway` | TurnawayReport.vue | Turnaway report |
| `/reports/source-performance` | SourcePerformanceReport.vue | Source performance |
| `/reports/company-ar` | CompanyArReport.vue | Company AR report |
| `/reports/trial-balance` | TrialBalanceReport.vue | Trial balance |
| `/reports/housekeeping-discrepancy` | HousekeepingDiscrepancyReport.vue | HK discrepancy |
| `/reports/adr-revpar` | AdrRevparReport.vue | ADR & RevPAR report |
| `/reports/custom-reports` | CustomReports/Index.vue | Custom reports list |
| `/reports/custom-reports/create` | CustomReports/Create.vue | Create custom report |
| `/reports/custom-reports/:id` | CustomReports/Show.vue | View custom report |
| `/reports/report-schedules` | Schedules/Index.vue | Scheduled reports |
| `/reports/report-schedules/create` | Schedules/Create.vue | Create schedule |
| `/reports/report-schedules/:id/edit` | Schedules/Edit.vue | Edit schedule |

### Marketing
| Route | Component | Description |
|-------|-----------|-------------|
| `/marketing/offers` | Marketing/Offers/Index.vue | Offers management |
| `/marketing/promo-codes` | Marketing/PromoCodes/Index.vue | Promo codes |
| `/marketing/pricing-preview` | Marketing/PricingPreview/Index.vue | Pricing preview |

### AR (Accounts Receivable)
| Route | Component | Description |
|-------|-----------|-------------|
| `/ar/invoice-transfers` | InvoiceTransferPage.vue | Invoice transfers |
| `/ar/promissories` | PromissoriesPage.vue | Promissory notes |
| `/ar/promissory-payment-logs` | PromissoryPaymentLogPage.vue | Payment logs |
| `/ar/company-groups` | CompanyGroupsPage.vue | Company groups |
| `/ar/city-ledger` | CityLedgerPage.vue | City ledger |
| `/ar/aging` | CityLedgerPage.vue | AR aging |
| `/ar/credit-utilization` | CityLedgerPage.vue | Credit utilization |

### Channel Manager
| Route | Component | Description |
|-------|-----------|-------------|
| `/channel-manager` | ChannelManagerPage.vue | Channel manager |
| `/channel-manager/availability-rates` | ManageCategoriesPage.vue | Availability & rates |
| `/channel-manager/reservations` | ChannelReservationsPage.vue | Channel reservations |

### Operations
| Route | Component | Description |
|-------|-----------|-------------|
| `/operations/night-audit` | NightAuditControl.vue | Night audit control |
| `/operations/night-audit/status` | NightAuditControl.vue | Audit status |
| `/operations/night-audit/logs` | NightAuditLogsPage.vue | Audit logs |
| `/operations/night-audit/rerun` | NightAuditRerunPage.vue | Rerun audit |
| `/operations/night-audit/backfill` | NightAuditBackfillPage.vue | Historical backfill |
| `/operations/night-audit/locks` | AuditLocksPage.vue | Audit locks |
| `/operations/no-show-rules` | NoShowRules.vue | No-show rules |
| `/operations/no-show-preview` | NoShowPreviewPage.vue | No-show preview |
| `/operations/room-adjustments` | RoomAdjustments.vue | Room adjustments |
| `/operations/insurance-recovery/create` | OperationsCheckoutPage.vue | Insurance recovery |
| `/operations/payment-indebtedness/create` | OperationsCheckoutPage.vue | Payment indebtedness |
| `/operations/check-out/create` | OperationsCheckoutPage.vue | Check-out |
| `/operations/check-out/success/:id` | ReservationSuccessPage.vue | Check-out success |

### Settings
| Route | Component | Description |
|-------|-----------|-------------|
| `/settings` | SettingsPage.vue | Main settings |
| `/settings/night-audit` | NightAuditSettingsPage.vue | Night audit settings |
| `/settings/early-late` | EarlyLateSettingsPage.vue | Early/late charges |
| `/settings/no-show` | NoshowSettingsPage.vue | No-show rules settings |
| `/settings/revenue-types` | RevenueTypesPage.vue | Revenue types |
| `/settings/roles` | RolesPermissionsPage.vue | Roles & permissions |
| `/settings/sidebar` | SidebarAccessPage.vue | Sidebar access control |

### User Groups
| Route | Component | Description |
|-------|-----------|-------------|
| `/user-groups` | UserGroupingPage.vue | User groups |
| `/user-groups/roles/create` | UserGroupingPage.vue | Create role |
| `/user-groups/roles/:id/edit` | UserGroupingPage.vue | Edit role |

### Miscellaneous
| Route | Component | Description |
|-------|-----------|-------------|
| `/leads` | LeadsPage.vue | Leads |
| `/rooms` | RoomsPage.vue | Rooms (alias) |
| `/profile` | SettingsPage.vue | User profile |

## Components

### Core Components
| Component | Location | Description |
|-----------|----------|-------------|
| SidebarNav | components/SidebarNav.vue | Main sidebar navigation |
| SidebarMenuItem | components/SidebarMenuItem.vue | Sidebar menu item |
| HeaderBar | components/HeaderBar.vue | Top header bar |
| BaseModal | components/BaseModal.vue | Reusable modal |
| UserMenu | components/UserMenu.vue | User dropdown menu |
| NotificationDropdown | components/NotificationDropdown.vue | Notifications |

### Dashboard Components
| Component | Location | Description |
|-----------|----------|-------------|
| KpiCard | components/dashboards/KpiCard.vue | KPI metric card |
| EmptyState | components/dashboards/EmptyState.vue | Empty state placeholder |
| DashboardFilterBar | components/dashboards/DashboardFilterBar.vue | Filter controls |

### Room/Unit Components
| Component | Location | Description |
|-----------|----------|-------------|
| UnitCard | components/UnitCard.vue | Unit display card |
| UnitStatusChart | components/UnitStatusChart.vue | Status distribution chart |
| UnitsModeSwitch | components/UnitsModeSwitch.vue | View mode toggle |
| UnitsFilterBar | components/UnitsFilterBar.vue | Filter controls |
| UnitsLegend | components/UnitsLegend.vue | Status legend |

### Reservation Components
| Component | Location | Description |
|-----------|----------|-------------|
| ReservationStepper | components/ReservationStepper.vue | Multi-step wizard |
| ReservationsTable | components/ReservationsTable.vue | Reservation list table |
| QuickModeCard | components/QuickModeCard.vue | Quick action card |
| QuickActionCard | components/QuickActionCard.vue | Action button card |

### Guest/Customer Components
| Component | Location | Description |
|-----------|----------|-------------|
| GuestsTable | components/GuestsTable.vue | Guest list table |
| GuestsFilterBar | components/GuestsFilterBar.vue | Guest filters |
| GuestTypeBadge | components/GuestTypeBadge.vue | Guest type badge |
| PersonProfileForm | components/PersonProfileForm.vue | Profile form |
| PersonCompanyTabs | components/PersonCompanyTabs.vue | Profile/Company tabs |

### POS Components
| Component | Location | Description |
|-----------|----------|-------------|
| POSCartSidebar | components/POSCartSidebar.vue | Shopping cart |
| POSCartItem | components/POSCartItem.vue | Cart item |
| POSCategoryTile | components/POSCategoryTile.vue | Category tile |
| POSCategoryTabs | components/POSCategoryTabs.vue | Category tabs |
| POSProductsTable | components/POSProductsTable.vue | Products table |
| POSServicesTable | components/POSServicesTable.vue | Services table |

### Finance Components
| Component | Location | Description |
|-----------|----------|-------------|
| PaymentCorrectionModal | components/finance/PaymentCorrectionModal.vue | Payment correction |
| AdjustmentModal | components/finance/AdjustmentModal.vue | Adjustment form |

### Common Components
| Component | Location | Description |
|-----------|----------|-------------|
| AdvancedFilter | components/common/AdvancedFilter.vue | Advanced filter |
| SearchInput | components/SearchInput.vue | Search input |
| PaginationControls | components/PaginationControls.vue | Pagination |
| UploadDropzone | components/UploadDropzone.vue | File upload |
| CalendarWidget | components/CalendarWidget.vue | Calendar picker |
| BannerCarousel | components/BannerCarousel.vue | Image carousel |
| AvatarGroup | components/AvatarGroup.vue | Avatar collection |

## Services

### API Service
**Location:** `resources/js/services/api.js`

Axios-based API client configured with:
- Base URL: `/api`
- Authentication: Bearer token from `localStorage.sanctum_token`
- CSRF token handling for Laravel Sanctum
- 401 response interceptor for session expiry

### Usage Example
```javascript
import api from '@/services/api';

// GET request
const response = await api.get('/sidebar');

// POST request
await api.post('/reservations', data);

// With authentication
// Token is automatically included from localStorage
```

## Internationalization (i18n)

| File | Language | Description |
|------|----------|-------------|
| `i18n/en.json` | English | English translations |
| `i18n/ar.json` | Arabic | Arabic translations |

### Usage
```javascript
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const label = t('messages.welcome');
```

## Router Configuration

**Location:** `resources/js/router/index.js`

### Navigation Guards
- `beforeEach`: Auth check - redirects to `/login` if not authenticated
- Auto-redirects to `/dashboard` if already logged in and accessing `/login`

### Route Metadata
All routes include:
- `name`: Named route for programmatic navigation
- `component`: Vue component to render

## State Management

The application uses **Pinia** for state management (initialized in `main.js`).

## Build & Development

```bash
# Install dependencies
npm install

# Development server
npm run dev

# Production build
npm run build

# Lint
npm run lint
```

## Tech Stack

- **Vue 3** - Progressive JavaScript framework
- **Vue Router 4** - Official router
- **Pinia** - State management
- **Axios** - HTTP client
- **VueApexCharts** - Charts library
- **Tailwind CSS** - Utility-first CSS
- **Lucide Vue Next** - Icon library
- **Heroicons** - Additional icons
- **vue-i18n** - Internationalization
- **Vite** - Build tool

## Notes

- All API calls are prefixed with `/api`
- Authentication uses Laravel Sanctum tokens stored in `localStorage.sanctum_token`
- RTL support is enabled for Arabic locale
- The application uses a catch-all route `/:pathMatch(.*)*` that redirects to `/dashboard`
