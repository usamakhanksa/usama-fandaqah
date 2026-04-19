import { SweetModal } from "sweet-modal-vue";
import VCalendar from "v-calendar";
import excel from "vue-excel-export";

Nova.booting((Vue, router, store) => {
    Vue.use(excel);
    Vue.use(VCalendar, {
        firstDayOfWeek: 1,
        componentPrefix: "vcc", // Monday
        datePickerTintColor: "#ddd", // Monday
        titlePosition: "left", // Monday
        masks: {
            title: "MMMM YYYY",
            weekdays: "WW",
            navMonths: "MMM",
            input: ["DD-MM-YYYY", "L"],
            dayPopover: "L",
            data: ["DD-MM-YYYY", "L"],
        },
    });

    Vue.filter("formatDateWithAmPm", function (value) {
        if (value) {
            return moment(String(value)).format("YYYY/MM/DD hh:mm A");
        }
    });

    Vue.component("sweet-modal", SweetModal);

    const routes = [
        {
            name: "financial-management",
            path: "/financial-management",
            component: require("./components/FinancialIndex"),
            meta: {
                breadcrumb: [
                    {
                        name: "Home",
                        link: "/dashboards/main",
                    },

                    {
                        name: "Financial Management",
                        link: "/financial-management",
                    },
                ],
            },
            permissions: ['view financial'],
            beforeEnter: (to, from, next) => {
                if(Nova.app.$hasPermission('view financial'))
                {
                    next();
                } else {
                    next('/403');
                }
                
            }
        },
        {
            name: "transactions",
            path: "/transactions",
            component: require("./components/Transactions"),
            permissions: ['view financial'],
            beforeEnter: (to, from, next) => {
                if(Nova.app.$hasPermission('view financial'))
                {
                    next();
                } else {
                    next('/403');
                }
                
            }
        },

        {
            name: "promissories",
            path: "/promissories",
            component: require("./components/Promissories"),
            permissions: ['view financial'],
            beforeEnter: (to, from, next) => {
                if(Nova.app.$hasPermission('view financial'))
                {
                    next();
                } else {
                    next('/403');
                }
                
            }
        },
        {
            name: "credit-notes",
            path: "/credit-notes",
            component: require("./components/CreditNotes"),
            permissions: ['view financial'],
            beforeEnter: (to, from, next) => {
                if(Nova.app.$hasPermission('view financial'))
                {
                    next();
                } else {
                    next('/403');
                }
                
            }
        },
        {
            name: "zatca-einvoices-management",
            path: "/zatca-einvoices-management",
            component: require("./components/ZatcaEInvoice"),
            permissions: ['view financial'],
            beforeEnter: (to, from, next) => {
                if(Nova.app.$hasPermission('view financial'))
                {
                    next();
                } else {
                    next('/403');
                }
                
            }
        },
        {
            name: "online-payments",
            path: "/online-payments",
            component: require("./components/OnlinePayments"),
            permissions: ['view financial'],
            beforeEnter: (to, from, next) => {
                if(Nova.app.$hasPermission('view financial'))
                {
                    next();
                } else {
                    next('/403');
                }
                
            }
        },

        {
            name: "online-payments-service-invoices",
            path: "/online-payments/service-invoices",
            component: require("./components/OnlinePaymentsServiceInvoices"),
            permissions: ['view financial'],
            beforeEnter: (to, from, next) => {
                if(Nova.app.$hasPermission('view financial'))
                {
                    next();
                } else {
                    next('/403');
                }
                
            }
        },
    ]

    router.addRoutes(routes);
});
