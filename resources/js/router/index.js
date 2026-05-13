import { createRouter, createWebHistory } from 'vue-router';

// Auth
import LoginPage from '../pages/LoginPage.vue';

// Dashboards
import OverviewDashboard from '../pages/dashboards/OverviewDashboard.vue';
import NightAuditDashboard from '../pages/dashboards/NightAuditDashboard.vue';
import OccupancyDashboard from '../pages/dashboards/OccupancyDashboard.vue';
import RevenueDashboard from '../pages/dashboards/RevenueDashboard.vue';
import FrontDeskDashboard from '../pages/dashboards/FrontDeskDashboard.vue';
import FinanceDashboard from '../pages/dashboards/FinanceDashboard.vue';
import ARDashboard from '../pages/dashboards/ARDashboard.vue';
import CashierDashboard from '../pages/dashboards/CashierDashboard.vue';
import CommissionDashboard from '../pages/dashboards/CommissionDashboard.vue';
import MetabaseDashboard from '../pages/dashboards/MetabaseDashboard.vue';
import IntegrationHealthDashboard from '../pages/dashboards/IntegrationHealthDashboard.vue';

// Operations
import NightAuditControl from '../pages/operations/NightAuditControl.vue';
import NoShowRules from '../pages/operations/NoShowRules.vue';

// Shared
import SimplePage from '../pages/SimplePage.vue';
import RoomsPage from '../pages/RoomsPage.vue';
import GuestsPage from '../pages/GuestsPage.vue';
import SettingsPage from '../pages/SettingsPage.vue';
import ReportsPage from '../pages/ReportsPage.vue';
import UserGroupingPage from '../pages/UserGroupingPage.vue';
import LeadsPage from '../pages/LeadsPage.vue';

// Reservations
import ReservationSchedulePage from '../pages/ReservationSchedulePage.vue';
import ReservationCreatePage from '../pages/ReservationCreatePage.vue';
import ReservationSuccessPage from '../pages/ReservationSuccessPage.vue';
import ReservationQuickCreatePage from '../pages/ReservationQuickCreatePage.vue';
import ReservationCalendarPage from '../pages/ReservationCalendarPage.vue';
import ReservationArrivalsPage from '../pages/ReservationArrivalsPage.vue';
import ReservationDeparturesPage from '../pages/ReservationDeparturesPage.vue';
import BookingDetailsPage from '../pages/BookingDetailsPage.vue';
import ReservationManagementPage from '../pages/ReservationManagementPage.vue';
import ReservationInHousePage from '../pages/ReservationInHousePage.vue';
import ReservationOnlinePage from '../pages/ReservationOnlinePage.vue';
import ReservationOTAPage from '../pages/ReservationOTAPage.vue';
import ReservationGroupPage from '../pages/ReservationGroupPage.vue';
import ReservationGroupCreatePage from '../pages/ReservationGroupCreatePage.vue';
import ReservationTransfersPage from '../pages/ReservationTransfersPage.vue';
import ReservationExtensionsPage from '../pages/ReservationExtensionsPage.vue';
import ReservationContractsPage from '../Pages/ReservationContractsPage.vue';
import ReservationSignaturesPage from '../pages/ReservationSignaturesPage.vue';
import ReservationRatingsPage from '../pages/ReservationRatingsPage.vue';
import ReservationCancellationsPage from '../pages/ReservationCancellationsPage.vue';
import ReservationMessagesPage from '../pages/ReservationMessagesPage.vue';
import ReservationAuditLocksPage from '../pages/ReservationAuditLocksPage.vue';
import ReservationGuestsPage from '../Pages/ReservationGuestsPage.vue';
import ReservationRoomsPage from '../Pages/ReservationRoomsPage.vue';
import NewReservationPage from '../pages/NewReservationPage.vue';

// Front Desk
import FrontDeskCheckInPage from '../pages/FrontDeskCheckInPage.vue';
import FrontDeskCheckOutPage from '../pages/FrontDeskCheckOutPage.vue';
import FrontDeskWalkInPage from '../pages/FrontDeskWalkInPage.vue';
import FrontDeskRegistrationPage from '../pages/FrontDeskRegistrationPage.vue';
import FrontDeskRoomAssignmentPage from '../pages/FrontDeskRoomAssignmentPage.vue';
import FrontDeskRoomSwapPage from '../pages/FrontDeskRoomSwapPage.vue';
import FrontDeskStayChargePage from '../pages/FrontDeskStayChargePage.vue';
import FrontDeskNoShowPage from '../pages/FrontDeskNoShowPage.vue';
import FrontDeskWakeUpCallsPage from '../pages/FrontDeskWakeUpCallsPage.vue';
import FrontDeskIptvNeedsPage from '../pages/FrontDeskIptvNeedsPage.vue';
import FrontDeskRegistrationCardsPage from '../pages/FrontDeskRegistrationCardsPage.vue';
import FrontDeskBalanceTransferPage from '../pages/FrontDeskBalanceTransferPage.vue';
import OperationsCheckoutPage from '../pages/OperationsCheckoutPage.vue';

// Rooms & Housekeeping
import UnitsPage from '../pages/UnitsPage.vue';
import UnitCategoriesPage from '../pages/UnitCategoriesPage.vue';
import UnitsAvailabilityPage from '../pages/UnitsAvailabilityPage.vue';
import UnitsStatusBoardPage from '../pages/UnitsStatusBoardPage.vue';
import HousekeepingBoardPage from '../pages/HousekeepingBoardPage.vue';
import UnitCleaningsPage from '../pages/UnitCleaningsPage.vue';
import UnitMaintenancesPage from '../pages/UnitMaintenancesPage.vue';
import RoomStatusLogPage from '../pages/RoomStatusLogPage.vue';
import RoomTypesPage from '../pages/RoomTypesPage.vue';
import RoomFloorsPage from '../pages/RoomFloorsPage.vue';
import UnitFeaturesPage from '../pages/UnitFeaturesPage.vue';
import UnitOptionsPage from '../pages/UnitOptionsPage.vue';
import UnitCategoryServicesPage from '../pages/UnitCategoryServicesPage.vue';

// Guests & Companies
import GuestDirectoryPage from '../pages/GuestDirectoryPage.vue';
import CustomersPage from '../pages/CustomersPage.vue';
import CompaniesPage from '../pages/CompaniesPage.vue';
import CompanyGroupsPage from '../pages/CompanyGroupsPage.vue';
import BlockedGuestsPage from '../pages/BlockedGuestsPage.vue';
import TurnawayLogsPage from '../pages/TurnawayLogsPage.vue';
import TurnawayReasonsPage from '../pages/TurnawayReasonsPage.vue';
import HighlightsPage from '../pages/HighlightsPage.vue';
import CustomerMergePage from '../pages/CustomerMergePage.vue';

// POS & Services
import PosDashboardPage from '../pages/PosDashboardPage.vue';
import ServiceCategoriesManagePage from '../pages/ServiceCategoriesManagePage.vue';
import ServicesManagePage from '../pages/ServicesManagePage.vue';
import PosSalePage from '../pages/PosSalePage.vue';
import ServiceLogsPage from '../pages/ServiceLogsPage.vue';
import QuickPaymentsPage from '../pages/QuickPaymentsPage.vue';
import PosTransactionsPage from '../pages/PosTransactionsPage.vue';
import ServiceQoyodPage from '../pages/ServiceQoyodPage.vue';

// Legacy POS (product-based)
import POSStorePage from '../pages/POSStorePage.vue';
import POSServicesPage from '../pages/POSServicesPage.vue';
import POSServiceCreatePage from '../pages/POSServiceCreatePage.vue';
import POSProductsPage from '../pages/POSProductsPage.vue';
import POSBrandsPage from '../pages/POSBrandsPage.vue';
import POSCategoriesPage from '../pages/POSCategoriesPage.vue';
import POSSubCategoriesPage from '../pages/POSSubCategoriesPage.vue';
import ServiceCategoriesPage from '../pages/ServiceCategoriesPage.vue';

// Finance
import FinancialManagementPage from '../pages/FinancialManagementPage.vue';
import FinancialEntryWizardPage from '../pages/FinancialEntryWizardPage.vue';
import FinancialSuccessPage from '../pages/FinancialSuccessPage.vue';
import RoomAdjustments from '../pages/finance/RoomAdjustments.vue';
import PaymentCorrection from '../pages/finance/PaymentCorrection.vue';
import CashierShiftsPage from '../pages/finance/CashierShiftsPage.vue';
import RoomStatusLogsPage from '../pages/finance/RoomStatusLogsPage.vue';
import TravelAgentsPage from '../pages/finance/TravelAgentsPage.vue';
import CommissionsDashboard from '../pages/finance/CommissionsDashboard.vue';

// AR
import InvoiceTransferPage from '../pages/ar/InvoiceTransferPage.vue';
import PromissoryPaymentLogPage from '../Pages/PromissoryPaymentLogPage.vue';
import PromissoriesPage from '../pages/PromissoriesPage.vue';
import CityLedgerPage from '../pages/CityLedgerPage.vue';

// Channel Manager
import ChannelManagerPage from '../pages/ChannelManagerPage.vue';
import ManageCategoriesPage from '../pages/ManageCategoriesPage.vue';
import ChannelReservationsPage from '../pages/ChannelReservationsPage.vue';

const routes = [
  // ── Auth ──────────────────────────────────────────────────────
  { path: '/login', component: LoginPage, name: 'login' },

  // ── Dashboards ────────────────────────────────────────────────
  { path: '/dashboard', component: OverviewDashboard, name: 'dashboard' },
  { path: '/night-audit', component: NightAuditDashboard, name: 'night-audit' },
  { path: '/dashboard/occupancy', component: OccupancyDashboard, name: 'occupancy' },
  { path: '/dashboard/revenue', component: RevenueDashboard, name: 'revenue' },
  { path: '/dashboard/front-desk', component: FrontDeskDashboard, name: 'front-desk-dashboard' },
  { path: '/dashboard/finance', component: FinanceDashboard, name: 'finance-dashboard' },
  { path: '/dashboard/integration-health', component: IntegrationHealthDashboard, name: 'integration-health' },
  { path: '/dashboard/ar', component: ARDashboard, name: 'ar-dashboard' },
  { path: '/dashboard/cashier', component: CashierDashboard, name: 'cashier-dashboard' },
  { path: '/dashboard/commissions', component: CommissionDashboard, name: 'commission-dashboard' },
  { path: '/dashboard/metabase', component: MetabaseDashboard, name: 'metabase-dashboard' },

  // ── Operations ────────────────────────────────────────────────
  { path: '/operations/night-audit', component: NightAuditControl, name: 'night-audit-control' },
  { path: '/operations/no-show-rules', component: NoShowRules, name: 'no-show-rules' },
  { path: '/operations/room-adjustments', component: RoomAdjustments, name: 'room-adjustments' },
  { path: '/operations/insurance-recovery/create', component: OperationsCheckoutPage, props: { mode: 'insurance' } },
  { path: '/operations/payment-indebtedness/create', component: OperationsCheckoutPage, props: { mode: 'payment' } },
  { path: '/operations/check-out/create', component: OperationsCheckoutPage, props: { mode: 'checkout' } },
  { path: '/operations/check-out/success/:id', component: ReservationSuccessPage, props: { flow: 'checkout' } },

  // ── Reservations ──────────────────────────────────────────────
  { path: '/reservations', redirect: '/reservations/management' },
  { path: '/reservations/management', component: ReservationManagementPage },
  { path: '/reservations/management/:bookingId', component: BookingDetailsPage },
  { path: '/reservations/management/:bookingId/financial', component: BookingDetailsPage },
  { path: '/reservations/management/:bookingId/notes', component: BookingDetailsPage },
  { path: '/reservations/schedule', component: ReservationSchedulePage },
  { path: '/reservations/create', component: ReservationCreatePage },
  { path: '/reservations/create/details', component: ReservationCreatePage },
  { path: '/reservations/create/visitor', component: ReservationCreatePage },
  { path: '/reservations/create/payment', component: ReservationCreatePage },
  { path: '/reservations/success/:bookingId', component: ReservationSuccessPage },
  { path: '/reservations/quick-create', component: ReservationQuickCreatePage },
  { path: '/reservations/calendar', component: ReservationCalendarPage, name: 'reservations.calendar' },
  { path: '/reservations/arrivals', component: ReservationArrivalsPage, name: 'reservations.arrivals' },
  { path: '/reservations/departures', component: ReservationDeparturesPage, name: 'reservations.departures' },
  { path: '/reservations/in-house', component: ReservationInHousePage, name: 'reservations.in-house' },
  { path: '/reservations/online', component: ReservationOnlinePage, name: 'reservations.online' },
  { path: '/reservations/ota', component: ReservationOTAPage, name: 'reservations.ota' },
  { path: '/reservations/groups', component: ReservationGroupPage, name: 'reservations.groups' },
  { path: '/reservations/groups/create', component: ReservationGroupCreatePage, name: 'reservations.groups.create' },
  { path: '/reservations/transfers', component: ReservationTransfersPage, name: 'reservation-transfers.index' },
  { path: '/reservations/extensions', component: ReservationExtensionsPage, name: 'reservation-extensions.index' },
  { path: '/reservations/contracts', component: ReservationContractsPage, name: 'reservation-contracts.index' },
  { path: '/reservations/signatures', component: ReservationSignaturesPage, name: 'reservation-signatures.index' },
  { path: '/reservations/ratings', component: ReservationRatingsPage, name: 'reservation-ratings.index' },
  { path: '/reservations/cancellations', component: ReservationCancellationsPage, name: 'reservations.cancellations' },
  { path: '/reservations/messages', component: ReservationMessagesPage, name: 'reservation-messages.index' },
  { path: '/reservations/audit-locks', component: ReservationAuditLocksPage, name: 'reservations.audit-locks' },
  { path: '/reservations/:reservation/guests', component: ReservationGuestsPage, name: 'reservation-guests.index' },
  { path: '/reservations/:reservation/rooms', component: ReservationRoomsPage, name: 'reservation-rooms.index' },
  { path: '/reservations/:id', component: BookingDetailsPage },
  { path: '/new-reservation', component: NewReservationPage, name: 'new-reservation' },
  { path: '/new-reservation/:id', component: NewReservationPage, name: 'edit-reservation' },

  // ── Front Desk ────────────────────────────────────────────────
  { path: '/front-desk/check-in', component: FrontDeskCheckInPage, name: 'front-desk.check-in' },
  { path: '/front-desk/check-out', component: FrontDeskCheckOutPage, name: 'front-desk.check-out' },
  { path: '/front-desk/walk-in', component: FrontDeskWalkInPage, name: 'front-desk.walk-in' },
  { path: '/front-desk/registration', component: FrontDeskRegistrationPage, name: 'front-desk.registration' },
  { path: '/front-desk/room-assignment', component: FrontDeskRoomAssignmentPage, name: 'front-desk.room-assignment' },
  { path: '/front-desk/room-swap', component: FrontDeskRoomSwapPage, name: 'front-desk.room-swap' },
  { path: '/front-desk/early-check-in', component: FrontDeskStayChargePage, name: 'front-desk.early-check-in' },
  { path: '/front-desk/late-checkout', component: FrontDeskStayChargePage, name: 'front-desk.late-checkout' },
  { path: '/front-desk/no-show', component: FrontDeskNoShowPage, name: 'front-desk.no-show' },
  { path: '/front-desk/wake-up-calls', component: FrontDeskWakeUpCallsPage, name: 'wake-up-calls.index' },
  { path: '/front-desk/iptv-needs', component: FrontDeskIptvNeedsPage, name: 'iptv-needs.index' },
  { path: '/front-desk/registration-cards', component: FrontDeskRegistrationCardsPage, name: 'front-desk.registration-cards' },
  { path: '/front-desk/balance-transfer', component: FrontDeskBalanceTransferPage, name: 'front-desk.balance-transfer' },

  // ── Rooms & Housekeeping ──────────────────────────────────────
  { path: '/units', component: UnitsPage, name: 'units.index' },
  { path: '/units/availability', component: UnitsAvailabilityPage, name: 'units.availability' },
  { path: '/units/status-board', component: UnitsStatusBoardPage, name: 'units.status-board' },
  { path: '/unit-categories', component: UnitCategoriesPage, name: 'unit-categories.index' },
  { path: '/housekeeping/board', component: HousekeepingBoardPage, name: 'housekeeping.board' },
  { path: '/unit-cleanings', component: UnitCleaningsPage, name: 'unit-cleanings.index' },
  { path: '/unit-maintenances', component: UnitMaintenancesPage, name: 'unit-maintenances.index' },
  { path: '/room-status-log', component: RoomStatusLogPage, name: 'room-status-log.index' },
  { path: '/room-types', component: RoomTypesPage, name: 'room-types.index' },
  { path: '/room-floors', component: RoomFloorsPage, name: 'room-floors.index' },
  { path: '/unit-features', component: UnitFeaturesPage, name: 'unit-features.index' },
  { path: '/unit-options', component: UnitOptionsPage, name: 'unit-options.index' },
  { path: '/unit-category-services', component: UnitCategoryServicesPage, name: 'unit-category-services.index' },

  // ── Guests & Companies ────────────────────────────────────────
  { path: '/guests', component: GuestDirectoryPage, name: 'guests.index' },
  { path: '/customers', component: CustomersPage, name: 'customers.index' },
  { path: '/customers/merge', component: CustomerMergePage, name: 'customers.merge' },
  { path: '/companies', component: CompaniesPage, name: 'companies.index' },
  { path: '/company-groups', component: CompanyGroupsPage, name: 'company-groups.index' },
  { path: '/blocked-guests', component: BlockedGuestsPage, name: 'blocked-guests.index' },
  { path: '/turnaway-logs', component: TurnawayLogsPage, name: 'turnaway-logs.index' },
  { path: '/turnaway-reasons', component: TurnawayReasonsPage, name: 'turnaway-reasons.index' },
  { path: '/highlights', component: HighlightsPage, name: 'highlights.index' },

  // ── POS & Services ────────────────────────────────────────────
  { path: '/pos/dashboard', component: PosDashboardPage, name: 'pos.dashboard' },
  { path: '/pos/sale', component: PosSalePage, name: 'pos.sale' },
  { path: '/pos/service-categories', component: ServiceCategoriesManagePage, name: 'service-categories.index' },
  { path: '/pos/services-manage', component: ServicesManagePage, name: 'services.manage' },
  { path: '/pos/service-logs', component: ServiceLogsPage, name: 'service-logs.index' },
  { path: '/pos/quick-payments', component: QuickPaymentsPage, name: 'quick-payments.index' },
  { path: '/pos/pos-transactions', component: PosTransactionsPage, name: 'pos-transactions.index' },
  { path: '/pos/service-qoyods', component: ServiceQoyodPage, name: 'service-qoyods.index' },
  // Legacy POS (product-based)
  { path: '/pos', redirect: '/pos/store' },
  { path: '/pos/store', component: POSStorePage },
  { path: '/pos/orders', component: POSStorePage },
  { path: '/pos/services', component: POSServicesPage },
  { path: '/pos/services/create', component: POSServiceCreatePage },
  { path: '/pos/transactions', component: PosTransactionsPage },
  { path: '/pos/products', component: POSProductsPage },
  { path: '/pos/products/brands', component: POSBrandsPage },
  { path: '/pos/products/categories', component: POSCategoriesPage },
  { path: '/pos/products/sub-categories', component: POSSubCategoriesPage },
  { path: '/services', component: ServiceCategoriesPage },

  // ── Finance ───────────────────────────────────────────────────
  { path: '/financial', redirect: '/financial/receipts' },
  { path: '/financial/receipts', component: FinancialManagementPage },
  { path: '/financial/receipts/create', component: FinancialEntryWizardPage },
  { path: '/financial/receipts/success/:id', component: FinancialSuccessPage },
  { path: '/financial/expenses', component: FinancialManagementPage },
  { path: '/financial/expenses/create', component: FinancialEntryWizardPage },
  { path: '/financial/expenses/success/:id', component: FinancialSuccessPage },
  { path: '/financial/bills', component: FinancialManagementPage },
  { path: '/financial/fund-movement', component: FinancialManagementPage },
  { path: '/financial/credit-notes', component: FinancialManagementPage },
  { path: '/finance/payment-correction', component: PaymentCorrection, name: 'payment-correction' },
  { path: '/finance/cashier-shifts', component: CashierShiftsPage, name: 'cashier-shifts' },
  { path: '/finance/room-status-logs', component: RoomStatusLogsPage, name: 'room-status-logs' },
  { path: '/finance/travel-agents', component: TravelAgentsPage, name: 'travel-agents' },
  { path: '/finance/commissions', component: CommissionsDashboard, name: 'commissions' },

  // ── AR ────────────────────────────────────────────────────────
  { path: '/ar/invoice-transfers', component: InvoiceTransferPage, name: 'invoice-transfers' },
  { path: '/ar/promissories', component: PromissoriesPage, name: 'ar.promissories' },
  { path: '/ar/promissory-payment-logs', component: PromissoryPaymentLogPage },
  { path: '/ar/company-groups', component: CompanyGroupsPage },
  { path: '/ar/city-ledger', component: CityLedgerPage },

  // ── Channel Manager ───────────────────────────────────────────
  { path: '/channel-manager', component: ChannelManagerPage },
  { path: '/channel-manager/availability-rates', component: ManageCategoriesPage },
  { path: '/channel-manager/reservations', component: ChannelReservationsPage },

  // ── User Groups ───────────────────────────────────────────────
  { path: '/user-groups', component: UserGroupingPage },
  { path: '/user-groups/roles/create', component: UserGroupingPage },
  { path: '/user-groups/roles/:id/edit', component: UserGroupingPage },

  // ── Misc ──────────────────────────────────────────────────────
  { path: '/leads', component: LeadsPage, name: 'leads' },
  { path: '/rooms', component: RoomsPage },
  { path: '/settings', component: SettingsPage },
  { path: '/reports', component: ReportsPage },
  { path: '/profile', component: SettingsPage, name: 'profile' },
  { path: '/:pathMatch(.*)*', redirect: '/dashboard' },
];

const router = createRouter({ history: createWebHistory(), routes });

router.beforeEach((to, from, next) => {
  const isAuth = localStorage.getItem('auth_fandaqah');
  if (to.name !== 'login' && !isAuth) next({ name: 'login' });
  else if (to.name === 'login' && isAuth) next({ name: 'dashboard' });
  else next();
});

export default router;
