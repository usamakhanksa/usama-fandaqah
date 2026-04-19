import { createRouter, createWebHistory } from 'vue-router';
import DashboardPage from '../pages/DashboardPage.vue';
import RoomsPage from '../pages/RoomsPage.vue';
import GuestsPage from '../pages/GuestsPage.vue';
import SimplePage from '../pages/SimplePage.vue';
import UnitsPage from '../pages/UnitsPage.vue';
import ReservationSchedulePage from '../pages/ReservationSchedulePage.vue';
import ReservationCreatePage from '../pages/ReservationCreatePage.vue';
import ReservationSuccessPage from '../pages/ReservationSuccessPage.vue';
import BookingDetailsPage from '../pages/BookingDetailsPage.vue';

const routes = [
  { path: '/dashboard', component: DashboardPage },
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
  { path: '/reservations/management', component: SimplePage, props: { title: 'Reservations Management' } },
  { path: '/reservations', component: SimplePage, props: { title: 'Reservations Management' } },
  ...['financial', 'services', 'user-groups', 'pos', 'pos/orders', 'pos/services', 'pos/transactions', 'pos/products', 'profile', 'settings'].map((p) => ({ path: `/${p}`, component: SimplePage, props: { title: p } })),
  { path: '/:pathMatch(.*)*', redirect: '/dashboard' },
];

export default createRouter({ history: createWebHistory(), routes });
