import { createRouter, createWebHistory } from 'vue-router';
import LoginPage from '../pages/LoginPage.vue';
import DashboardPage from '../pages/DashboardPage.vue';
import RoomsPage from '../pages/RoomsPage.vue';
import GuestsPage from '../pages/GuestsPage.vue';
import SimplePage from '../pages/SimplePage.vue';
import FinancialManagementPage from '../pages/FinancialManagementPage.vue';
import FinancialEntryWizardPage from '../pages/FinancialEntryWizardPage.vue';
import FinancialSuccessPage from '../pages/FinancialSuccessPage.vue';
import UnitsPage from '../pages/UnitsPage.vue';
import ReservationSchedulePage from '../pages/ReservationSchedulePage.vue';
import ReservationCreatePage from '../pages/ReservationCreatePage.vue';
import ReservationSuccessPage from '../pages/ReservationSuccessPage.vue';
import BookingDetailsPage from '../pages/BookingDetailsPage.vue';
import ReservationManagementPage from '../pages/ReservationManagementPage.vue';
import OperationsCheckoutPage from '../pages/OperationsCheckoutPage.vue';
import UserGroupingPage from '../pages/UserGroupingPage.vue';
import POSStorePage from '../pages/POSStorePage.vue';
import POSServicesPage from '../pages/POSServicesPage.vue';
import POSServiceCreatePage from '../pages/POSServiceCreatePage.vue';
import POSProductsPage from '../pages/POSProductsPage.vue';
import POSBrandsPage from '../pages/POSBrandsPage.vue';
import POSCategoriesPage from '../pages/POSCategoriesPage.vue';
import POSSubCategoriesPage from '../pages/POSSubCategoriesPage.vue';
import POSTransactionsPage from '../pages/POSTransactionsPage.vue';
import SettingsPage from '../pages/SettingsPage.vue';
import ReportsPage from '../pages/ReportsPage.vue';
import ChannelManagerPage from '../pages/ChannelManagerPage.vue';
import ManageCategoriesPage from '../pages/ManageCategoriesPage.vue';
import LeadsPage from '../pages/LeadsPage.vue';
import ServiceCategoriesPage from '../pages/ServiceCategoriesPage.vue';
import NewReservationPage from '../pages/NewReservationPage.vue';
import ChannelReservationsPage from '../pages/ChannelReservationsPage.vue';

const routes = [
  { path: '/login', component: LoginPage, name: 'login' },
  { path: '/dashboard', component: DashboardPage, name: 'dashboard' },
  { path: '/leads', component: LeadsPage, name: 'leads' },
  { path: '/rooms', component: RoomsPage },
  { path: '/guests', component: GuestsPage },
  { path: '/guests/companies', component: GuestsPage },
  { path: '/guests/company/create', component: GuestsPage },
  { path: '/guests/person/create', component: GuestsPage },
  { path: '/units', component: UnitsPage },
  { path: '/units/daily-status', component: UnitsPage },
  { path: '/units/check-in', component: UnitsPage },
  { path: '/units/check-out', component: UnitsPage },
  { path: '/reservations/schedule', component: ReservationSchedulePage },
  { path: '/reservations/create', component: ReservationCreatePage },
  { path: '/reservations/create/details', component: ReservationCreatePage },
  { path: '/reservations/create/visitor', component: ReservationCreatePage },
  { path: '/reservations/create/payment', component: ReservationCreatePage },
  { path: '/reservations/success/:bookingId', component: ReservationSuccessPage },
  { path: '/reservations/management/:bookingId', component: BookingDetailsPage },
  { path: '/reservations/management/:bookingId/financial', component: BookingDetailsPage },
  { path: '/reservations/management/:bookingId/notes', component: BookingDetailsPage },
  { path: '/reservations/management', component: ReservationManagementPage },
  { path: '/reservations', component: SimplePage, props: { title: 'Reservations Management' } },
  { path: '/operations/insurance-recovery/create', component: OperationsCheckoutPage, props: { mode: 'insurance' } },
  { path: '/operations/payment-indebtedness/create', component: OperationsCheckoutPage, props: { mode: 'payment' } },
  { path: '/operations/check-out/create', component: OperationsCheckoutPage, props: { mode: 'checkout' } },
  { path: '/operations/check-out/success/:id', component: ReservationSuccessPage, props: { flow: 'checkout' } },
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
  { path: '/user-groups', component: UserGroupingPage },
  { path: '/user-groups/roles/create', component: UserGroupingPage },
  { path: '/user-groups/roles/:id/edit', component: UserGroupingPage },
  { path: '/pos', redirect: '/pos/store' },
  { path: '/pos/store', component: POSStorePage },
  { path: '/pos/orders', component: POSStorePage },
  { path: '/pos/services', component: POSServicesPage },
  { path: '/pos/services/create', component: POSServiceCreatePage },
  { path: '/pos/transactions', component: POSTransactionsPage },
  { path: '/pos/products', component: POSProductsPage },
  { path: '/pos/products/brands', component: POSBrandsPage },
  { path: '/pos/products/categories', component: POSCategoriesPage },
  { path: '/pos/products/sub-categories', component: POSSubCategoriesPage },
  { path: '/settings', component: SettingsPage },
  { path: '/reports', component: ReportsPage },
  { path: '/channel-manager', component: ChannelManagerPage },
  { path: '/channel-manager/availability-rates', component: ManageCategoriesPage },
  { path: '/channel-manager/reservations', component: ChannelReservationsPage },
  { path: '/services', component: ServiceCategoriesPage },
  { path: '/new-reservation', component: NewReservationPage, name: 'new-reservation' },
  { path: '/new-reservation/:id', component: NewReservationPage, name: 'edit-reservation' },
  { path: '/profile', component: SimplePage, props: { title: 'profile' } },
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
