import moment from 'moment'
import VCalendar from 'v-calendar';
import VTooltip from 'v-tooltip'

Nova.booting((Vue, router, store) => {
    Vue.use(VTooltip);
    Vue.use(VCalendar, {
        firstDayOfWeek: 1,
        componentPrefix: 'vcuh',// Monday
        datePickerTintColor: '#ddd',// Monday
        titlePosition: 'left',// Monday
        masks: {
            title: 'MMMM YYYY',
            weekdays: 'WW',
            navMonths: 'MMM',
            input: ['DD-MM-YYYY', 'L'],
            dayPopover: 'L',
            data: ['DD-MM-YYYY', 'L']
        },
    });

    Vue.filter('formatDateSpecial', function (value) {
        if (value) {
            return moment(String(value)).format('YYYY/MM/DD')
        }
    });

    Vue.filter('formatDateWithAmPm', function(value) {
        if (value) {
            return moment(String(value)).format('YYYY/MM/DD hh:mm')
        }
    });

    // using v-calendar
    Vue.use(VCalendar, {
        firstDayOfWeek: 1,
        componentPrefix: 'vcn',// Monday
        datePickerTintColor: '#ddd',// Monday
        titlePosition: 'left',// Monday
        masks: {
            title: 'MMMM YYYY',
            weekdays: 'WW',
            navMonths: 'MMM',
            input: ['DD-MM-YYYY', 'L'],
            dayPopover: 'L',
            data: ['DD-MM-YYYY', 'L']
        },
    });
    Vue.component('DashboardUnits', require('./components/Card'));
    Vue.component('trial', require('./components/partial/Trial'));
    Vue.component('subscription-end', require('./components/partial/SubscriptionEnd'));
    Vue.component('complete-info', require('./components/partial/CompleteInfo'));
    Vue.component('go-to-cleaning', require('./components/partial/GoToCleaning'));
    Vue.component('go-to-maintenance', require('./components/partial/GoToMaintenance'));
    Vue.component('make-unit-available', require('./components/partial/MakeUnitAvailable'));
    Vue.component('panel-arrival', require('./components/partial/Panel-Arrival'));
    Vue.component('panel-departure', require('./components/partial/Panel-Departure'));
    Vue.component('panel-departure-overdue', require('./components/partial/Panel-Departure-Overdue'));
    Vue.component('panel-awaiting-payment-reservations', require('./components/partial/AwaitingPaymentReservations'));
    Vue.component('panel-awaiting-confirmation-reservations', require('./components/partial/AwaitingConfirmationReservations'));
    Vue.component('pagination', require('./components/Pagination'));

    const routes = [
        {
            name: 'housing',
            path: '/units/housing',
            component: require('./components/UnitHousing'),
            permissions: ['watch unit housing'],
            beforeEnter: (to, from, next) => {
                if(Nova.app.$hasPermission('watch unit housing'))
                {
                    next();
                } else {
                    next('/403');
                }
                
            }
        },
        {
            name: 'alraedah-finance',
            path: '/alraedah-finance/create-prospect',
            component: require('./components/partial/AlraedahFinanceComponent'),
        },
    ]

    router.addRoutes(routes);
});
