import VCalendar from "v-calendar";
import moment from "moment";
import excel from "vue-excel-export";

Nova.booting((Vue, router, store) => {
    Vue.use(excel);
    Vue.filter('formatDateWithoutTime', function(value) {
        if (value) {
            return moment(String(value)).format('YYYY/MM/DD')
        }
    });
    Vue.filter('formatDateWithTime', function(value) {
        if (value) {
            return moment(String(value)).format('YYYY/MM/DD hh:mm')
        }
    });
    const routes = [

        /* --------------------------------------------- New Routes ----------------------------------------------------------- */
        {
            name : 'reports',
            path : '/reports',
            component : require('./components/Tool'),
            permissions: ['view reports'],
            beforeEnter: (to, from, next) => {
                if (Nova.app.$hasPermission('view reports')) {
                    next();
                } else {
                    next('/403');
                }
            }
        },
        {
            name: 'transactions-report',
            path: '/reports/transactions',
            component : require('./components/TransactionsReport'),
            permissions: ['view reports'],
            beforeEnter: (to, from, next) => {
                if (Nova.app.$hasPermission('view reports')) {
                    next();
                } else {
                    next('/403');
                }
            }
        },
        {
            name: 'safe-report',
            path: '/reports/safe',
            component : require('./components/SafeReport'),
            permissions: ['view reports'],
            beforeEnter: (to, from, next) => {
                if (Nova.app.$hasPermission('view reports')) {
                    next();
                } else {
                    next('/403');
                }
            }
        },

        {
            name: 'customers-report',
            path: '/reports/customers',
            component : require('./components/CustomersReport'),
            permissions: ['view reports'],
            beforeEnter: (to, from, next) => {
                if (Nova.app.$hasPermission('view reports')) {
                    next();
                } else {
                    next('/403');
                }
            }
        },

        {
            name: 'units-available-report',
            path: '/reports/units-available',
            component : require('./components/AvailableUnitsReport'),
            permissions: ['view reports'],
            beforeEnter: (to, from, next) => {
                if (Nova.app.$hasPermission('view reports')) {
                    next();
                } else {
                    next('/403');
                }
            }
        },

        {
            name: 'services-report',
            path: '/reports/services',
            component : require('./components/ServicesReport'),
            permissions: ['view reports'],
            beforeEnter: (to, from, next) => {
                if (Nova.app.$hasPermission('view reports')) {
                    next();
                } else {
                    next('/403');
                }
            }
        },

        {
            name: 'monthly-report',
            path: '/reports/monthly',
            component : require('./components/MonthlyReport'),
            permissions: ['view reports'],
            beforeEnter: (to, from, next) => {
                if (Nova.app.$hasPermission('view reports')) {
                    next();
                } else {
                    next('/403');
                }
            }
        },

        {
            name: 'units-reservations-report',
            path: '/reports/units-reservations',
            component : require('./components/UnitsReservationsReport'),
            permissions: ['view reports'],
            beforeEnter: (to, from, next) => {
                if (Nova.app.$hasPermission('view reports')) {
                    next();
                } else {
                    next('/403');
                }
            }
        },

        {
            name: 'units-occupied-report',
            path: '/reports/units-occupied',
            component : require('./components/UnitsOccupiedReport'),
            permissions: ['view reports'],
            beforeEnter: (to, from, next) => {
                if (Nova.app.$hasPermission('view reports')) {
                    next();
                } else {
                    next('/403');
                }
            }
        },
        {
            name: 'units-occupied-report-all',
            path: '/reports/units-occupied-all',
            component : require('./components/UnitsOccupiedReportAll'),
        },

        {
            name: 'units-cleaning-report',
            path: '/reports/units-cleaning',
            component : require('./components/UnitsCleaningReport'),
            permissions: ['view reports'],
            beforeEnter: (to, from, next) => {
                if (Nova.app.$hasPermission('view reports')) {
                    next();
                } else {
                    next('/403');
                }
            }
        },

        {
            name: 'units-maintenance-report',
            path: '/reports/units-maintenance',
            component : require('./components/UnitsMaintenanceReport'),
            permissions: ['view reports'],
            beforeEnter: (to, from, next) => {
                if (Nova.app.$hasPermission('view reports')) {
                    next();
                } else {
                    next('/403');
                }
            }
        },

        {
            name: 'units-reservation-transfer-report',
            path: '/reports/units-reservation-transfer',
            component : require('./components/UnitsReservationTransferReport'),
            permissions: ['view reports'],
            beforeEnter: (to, from, next) => {
                if (Nova.app.$hasPermission('view reports')) {
                    next();
                } else {
                    next('/403');
                }
            }
        },

        {
            name: 'revenue-and-taxes-report',
            path: '/reports/revenue-and-taxes',
            component : require('./components/RevenueAndTaxesReport'),
            permissions: ['view reports'],
            beforeEnter: (to, from, next) => {
                if (Nova.app.$hasPermission('view reports')) {
                    next();
                } else {
                    next('/403');
                }
            }
        },

        {
            name: 'reservation-sources-report',
            path: '/reports/reservation-sources',
            component : require('./components/ReservationsSourcesReport'),
            permissions: ['view reports'],
            beforeEnter: (to, from, next) => {
                if (Nova.app.$hasPermission('view reports')) {
                    next();
                } else {
                    next('/403');
                }
            }
        },

        {
            name: 'employees-contracts-report',
            path: '/reports/employees-contracts',
            component : require('./components/EmployeesContractsReport'),
            permissions: ['view reports'],
            beforeEnter: (to, from, next) => {
                if (Nova.app.$hasPermission('view reports')) {
                    next();
                } else {
                    next('/403');
                }
            }
        },

        {
            name: 'invoices-report',
            path: '/reports/invoices',
            component : require('./components/InvoicesReport'),
            permissions: ['view reports'],
            beforeEnter: (to, from, next) => {
                if (Nova.app.$hasPermission('view reports')) {
                    next();
                } else {
                    next('/403');
                }
            }
        },

        {
            name: 'balady-report',
            path: '/reports/balady',
            component : require('./components/BaladyReport'),
            permissions: ['view reports'],
            beforeEnter: (to, from, next) => {
                if (Nova.app.$hasPermission('view reports')) {
                    next();
                } else {
                    next('/403');
                }
            }
        },
        {
            name: 'balady-report-v2',
            path: '/reports/balady-v2',
            component : require('./components/BaladyReportV2'),
        },

        {
            name: "zatca-einvoices",
            path: "/reports/zatca-einvoices",
            component: require("./components/ZatcaEInvoice"),
            permissions: ['view reports'],
            beforeEnter: (to, from, next) => {
                if (Nova.app.$hasPermission('view reports')) {
                    next();
                } else {
                    next('/403');
                }
            }
        },

        
        {
            name: 'manager-flash-report',
            path: '/reports/manager-flash-report',
            component : require('./components/ManagerFlashReport'),
            permissions: ['manager flash report'],
            beforeEnter: (to, from, next) => {
                if (Nova.app.$hasPermission('manager flash report')) {
                    next();
                } else {
                    next('/403');
                }
            }
        },
        {
            name: 'history-forecast',
            path: '/reports/history-forecast',
            component : require('./components/HistoryForecast'),
            permissions: ['history forecast report'],
            beforeEnter: (to, from, next) => {
                if (Nova.app.$hasPermission('history forecast report')) {
                    next();
                } else {
                    next('/403');
                }
            }
        },
        {
            name: 'trial-balance',
            path: '/reports/trial-balance',
            component : require('./components/TrialBalanceReport'),
            permissions: ['trial balance report'],
            beforeEnter: (to, from, next) => {
                if (Nova.app.$hasPermission('trial balance report')) {
                    next();
                } else {
                    next('/403');
                }
            }
        },
        
        {
            name: 'housekeeping-discrepancies',
            path: '/reports/housekeeping-discrepancies',
            component : require('./components/HouseKeepingDiscrepancy'),
            permissions: ['housekeeping discrepancies report'],
            beforeEnter: (to, from, next) => {
                if (Nova.app.$hasPermission('housekeeping discrepancies report')) {
                    next();
                } else {
                    next('/403');
                }
            }
        },

        {
            name: 'paid-outs',
            path: '/reports/paid-outs',
            component : require('./components/PaidOutsReport'),
            permissions: ['paid outs report'],
            beforeEnter: (to, from, next) => {
                if (Nova.app.$hasPermission('paid outs report')) {
                    next();
                } else {
                    next('/403');
                }
            }
        },

        

        /* --------------------------------------------- New Routes ----------------------------------------------------------- */




        /* --------------------------------------------- Legacy Routes ----------------------------------------------------------- */



        {
            name: 'transactions-feature.deposit',
            path: '/transactions-feature/deposit',
            component: require('./components/sections/DepositTransactions'),
            props: route => {
                return {
                    resourceName: 'transactions',
                    viaResource: route.query.viaResource,
                    viaResourceId: route.query.viaResourceId,
                    viaRelationship: route.query.viaRelationship,
                    transactionType: 'deposit'
                }
            },
            meta:{
                breadcrumb:[
                    {
                        name : 'Home',
                        link : '/dashboards/main'
                    },

                    {
                        name : 'Reports',
                        link : '/dashboard/reports'
                    },

                    {
                        name : 'Deposits',
                        link : '/transactions-feature/deposit'
                    }

                ]
            },
            permissions: ['view reports'],
            beforeEnter: (to, from, next) => {
                if (Nova.app.$hasPermission('view reports')) {
                    next();
                } else {
                    next('/403');
                }
            }

        },
        {
            name: 'transactions-feature.withdraw',
            path: '/transactions-feature/withdraw',
            component: require('./components/sections/WithdrawTransactions'),
            props: route => {
                return {
                    resourceName: 'transactions',
                    viaResource: route.query.viaResource,
                    viaResourceId: route.query.viaResourceId,
                    viaRelationship: route.query.viaRelationship,
                    transactionType : 'withdraw'
                }
            },

            meta:{
                breadcrumb:[
                    {
                        name : 'Home',
                        link : '/dashboards/main'
                    },

                    {
                        name : 'Reports',
                        link : '/dashboard/reports'
                    },

                    {
                        name : 'Withdraws',
                        link : '/transactions-feature/withdraw'
                    }

                ]
            },
            permissions: ['view reports'],
            beforeEnter: (to, from, next) => {
                if (Nova.app.$hasPermission('view reports')) {
                    next();
                } else {
                    next('/403');
                }
            }

        },


        {
            name: 'transactions-feature.balady-report',
            path: '/transactions-feature/balady-report',
            component: require('./components/sections/BaladyReport'),
            meta:{
                breadcrumb:[
                    {
                        name : 'Home',
                        link : '/dashboards/main'
                    },

                    {
                        name : 'Reports',
                        link : '/dashboard/reports'
                    },

                    {
                        name : 'Balady Report',
                        link : '/transactions-feature/balady-report'
                    }

                ]
            },
            permissions: ['view reports'],
            beforeEnter: (to, from, next) => {
                if (Nova.app.$hasPermission('view reports')) {
                    next();
                } else {
                    next('/403');
                }
            }



        },

        {
            name: 'transactions-feature.safe-movement-report',
            path: '/transactions-feature/safe-movement-report',
            component: require('./components/sections/SafeMovementReport'),
            meta:{
                breadcrumb:[
                    {
                        name : 'Home',
                        link : '/dashboards/main'
                    },

                    {
                        name : 'Reports',
                        link : '/dashboard/reports'
                    },

                    {
                        name : 'The Safe Movement Report',
                        link : '/transactions-feature/safe-movement-report'
                    }

                ]
            },
            permissions: ['view reports'],
            beforeEnter: (to, from, next) => {
                if (Nova.app.$hasPermission('view reports')) {
                    next();
                } else {
                    next('/403');
                }
            }
        },

        {
            name: 'transactions-feature.revenue-tax-fee-report',
            path: '/transactions-feature/revenue-tax-fee-report',
            component: require('./components/sections/RevenueTaxFeeReport'),
            meta:{
                breadcrumb:[
                    {
                        name : 'Home',
                        link : '/dashboards/main'
                    },

                    {
                        name : 'Reports',
                        link : '/dashboard/reports'
                    },

                    {
                        name : 'Revenues & Taxes , Fees',
                        link : '/transactions-feature/revenue-tax-fee-report'
                    }

                ]
            },
            permissions: ['view reports'],
            beforeEnter: (to, from, next) => {
                if (Nova.app.$hasPermission('view reports')) {
                    next();
                } else {
                    next('/403');
                }
            }
        },
        {
            name: 'transactions-feature.reservation-resources',
            path: '/transactions-feature/reservation-resources',
            component: require('./components/sections/ReservationResources'),
            props: route => {
                return {
                    resourceName: 'reservations',
                    lens : 'reservation-resources',
                    viaResource: route.query.viaResource,
                    viaResourceId: route.query.viaResourceId,
                    viaRelationship: route.query.viaRelationship,

                }
            },
            meta:{
                breadcrumb:[
                    {
                        name : 'Home',
                        link : '/dashboards/main'
                    },
                    {
                        name : 'Reports',
                        link : '/dashboard/reports'
                    },

                    {
                        name : 'Reservation Resources',
                        link : '/transactions-feature/reservation-resources'
                    },


                ]
            },
            permissions: ['view reports'],
            beforeEnter: (to, from, next) => {
                if (Nova.app.$hasPermission('view reports')) {
                    next();
                } else {
                    next('/403');
                }
            }

        },
        {
            name: 'transactions-feature.employee-contracts',
            path: '/transactions-feature/employee-contracts',
            component: require('./components/sections/EmployeeContractsReport'),
            props: route => {
                return {
                    resourceName: 'reservations',
                    lens : 'employee-contracts',
                    viaResource: route.query.viaResource,
                    viaResourceId: route.query.viaResourceId,
                    viaRelationship: route.query.viaRelationship,

                }
            },
            meta:{
                breadcrumb:[
                    {
                        name : 'Home',
                        link : '/'
                    },

                    {
                        name : 'Reports',
                        link : '/dashboard/reports'
                    },

                    {
                        name : 'Employee Contracts',
                        link : '/transactions-feature/employee-contracts'
                    },


                ]
            },
            permissions: ['view reports'],
            beforeEnter: (to, from, next) => {
                if (Nova.app.$hasPermission('view reports')) {
                    next();
                } else {
                    next('/403');
                }
            }
        },
        {
            name: 'transactions-feature.invoices',
            path: '/transactions-feature/invoices',
            component: require('./components/sections/InvoicesReport'),
            meta:{
                breadcrumb:[
                    {
                        name : 'Home',
                        link : '/'
                    },

                    {
                        name : 'Reports',
                        link : '/dashboard/reports'
                    },

                    {
                        name : 'Invoices Report',
                        link : '/transactions-feature/invoices'
                    },


                ]
            },
            permissions: ['view reports'],
            beforeEnter: (to, from, next) => {
                if (Nova.app.$hasPermission('view reports')) {
                    next();
                } else {
                    next('/403');
                }
            }
        },

        {
            name: 'transactions-feature.services-report',
            path: '/transactions-feature/services-report',
            component: require('./components/sections/ServicesReport'),
            meta:{
                breadcrumb:[
                    {
                        name : 'Home',
                        link : '/'
                    },

                    {
                        name : 'Reports',
                        link : '/dashboard/reports'
                    },

                    {
                        name : 'Services Report',
                        link : '/transactions-feature/services-report'
                    },


                ]
            },
            permissions: ['view reports'],
            beforeEnter: (to, from, next) => {
                if (Nova.app.$hasPermission('view reports')) {
                    next();
                } else {
                    next('/403');
                }
            }
        },

        {
            name: 'reservations-with-services-included-report',
            path: '/reservations-with-services-included-report',
            component: require('./components/ReservationsWithServicesIncludedReport'),
            meta:{
                breadcrumb:[
                    {
                        name : 'Home',
                        link : '/'
                    },

                    {
                        name : 'Reports',
                        link : '/dashboard/reports'
                    },

                    {
                        name : 'Reservations With Services Included Report',
                        link : '/reservations-with-services-included-report'
                    },


                ]
            },
            permissions: ['view reports'],
            beforeEnter: (to, from, next) => {
                if (Nova.app.$hasPermission('view reports')) {
                    next();
                } else {
                    next('/403');
                }
            }
        },

        /* --------------------------------------------- Legacy Routes ----------------------------------------------------------- */



    ];
    router.addRoutes(routes);
    Vue.use(VCalendar, {
        firstDayOfWeek: 1,
        componentPrefix: 'vcc',// Monday
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


    Vue.component('transaction-table', require('./components/sections/TransactionTable'));
    Vue.component('transactions-table-row', require('./components/sections/TransactionsTableRow'));
    Vue.component('total-cash' , require('./components/helpers/TotalCashCard'));
    Vue.component('total-bank-cash' , require('./components/helpers/TotalBankCashCard'));
    Vue.component('total' , require('./components/helpers/TotalCard'));
    Vue.component('big-filter-card' , require('./components/helpers/BigFilterCard'));
    Vue.component('statistics' , require('./components/helpers/Statistics'));



})
