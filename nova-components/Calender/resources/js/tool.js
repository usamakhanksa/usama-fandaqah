import VCalendar from 'v-calendar';
import VueTelInput from 'vue-tel-input';
import {SweetModal} from 'sweet-modal-vue';

import VSuperSelect from "v-super-select";
import Dashboard from "./components/Dashboard";
import SocialSharing from 'vue-social-sharing'
import VTooltip from 'v-tooltip'
import moment from 'moment'


Nova.booting((Vue, router, store) => {
    Vue.use(VTooltip)
    Vue.use(moment)

    // router.afterEach((to, from, next) => {
    //     Nova.request()
    //         .get('/check-user-needs-verifiication')
    //         .then(({ data }) => {
            
    //             if (data.needs_verification) {

                 
    //                var current_lang =  document.querySelector('html').getAttribute('lang');
    //                var sentence = current_lang == 'ar' ? 'المستخدم غير فعال لعدم اجراء تحقق البريد الالكتروني ورقم الجوال' : 'User is inactive due to not verifying email and mobile number';
    //                var btnText = current_lang == 'ar' ? 'التحقق الان' : 'Verify now'
    //                 Vue.toasted.show(sentence, { 
    //                     router,
    //                     type : 'error',
    //                     position : 'top-center',
    //                     duration : 5000,
    //                     // you can pass a multiple actions as an array of actions
    //                     action : [
    //                         // {
    //                         //     text : 'Ignore',
    //                         //     onClick : (e, toastObject) => {
    //                         //         toastObject.goAway(0);
    //                         //     }
    //                         // },
    //                         {
    //                             text : btnText,
    //                             // router navigation
    //                             push : { 
    //                                 name : 'settings.profile',
    //                                 // this will prevent toast from closing
    //                                 dontClose : true
    //                             }
    //                         }
    //                     ]
    //                 });

                  
                 
    //             } 
                
    //         })
        
    // })
    

    Vue.filter('truncate', function (text, length, suffix) {
        if (text.length > length) {
            return text.substring(0, length) + suffix;
        } else {
            return text;
        }
    });
      
    Vue.filter('formatDate', function(value) {
      if (value) {
        return moment(String(value)).format('YYYY/MM/DD hh:mm')
      }
    });

    Vue.filter('formatDateWithAmPm', function(value) {
        if (value) {
            return moment(String(value)).format('YYYY/MM/DD hh:mm A')
        }
    });

    Vue.filter('formatDateWithoutTime', function(value) {
        if (value) {
            return moment(String(value)).format('YYYY-MM-DD')
        }
    });

    Vue.filter('formatDateSpecial', function(value) {
        if (value) {
            return moment(String(value)).format('YYYY/MM/DD')
        }
    });

    Vue.filter('formatDate24', function(value) {
        if (value) {
            return moment(String(value)).format('YYYY/MM/DD HH:mm')
        }
    });

    Vue.component('error-404', require('./components/404-error'));
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
    Vue.component('reservation-invoices', require('./components/partial/ReservationInvoices'));
    Vue.component('unit', require('./components/Unit'));
    Vue.component('customer-table', require('./components/CustomerTable'));
    Vue.component('customer-table', require('./components/CustomerTableRow'));
    Vue.component('cash-receipt', require('./components/partial/CashReceipt'));
    Vue.component('checkout-cash-receipt', require('./components/partial/checkout/CashReceipt'));
    Vue.component('checkout-payment-voucher', require('./components/partial/checkout/PaymentVoucher'));
    Vue.component('edit-cash-receipt', require('./components/partial/EditCashReceipt'));
    Vue.component('payment-voucher', require('./components/partial/PaymentVoucher'));
    Vue.component('edit-payment-voucher', require('./components/partial/EditPaymentVoucher'));
    Vue.component('reservation-guest', require('./components/partial/Guests'));
    Vue.component('reservation-contract', require('./components/partial/Contract'));
    Vue.component('reservation-summary', require('./components/partial/ReservationSummary'));
    Vue.component('reservation-check-in', require('./components/partial/CheckIn'));
    Vue.component('reservation-check-out', require('./components/partial/CheckOut'));
    Vue.component('reservation-cancel', require('./components/partial/Cancel'));
    Vue.component('reservation-edit', require('./components/partial/EditReservation'));
    Vue.component('reservation-edit-customer', require('./components/partial/EditCustomer'));
    Vue.component('reservation-logs', require('./components/partial/ReservationLogs'));
    Vue.component('services-statement', require('./components/partial/ServicesStatement'));
    Vue.component('days-details', require('./components/partial/daysDetails'));
    Vue.component('reservation-details', require('./components/partial/ReservationDetails'));
    Vue.component('autocomplete', require('./components/AutoComplete'));
    Vue.component('vue-tel-input', VueTelInput);
    Vue.component('sweet-modal', SweetModal);
    Vue.component('v-super-select', VSuperSelect);
    Vue.component('social-sharing', SocialSharing);
    const routes = [
        {
            name: 'new-reservation',
            path: '/new-reservation/:date/:room_id?/:changed?',
            component: require('./components/NewReservation'),
            permissions: ['create reservations'],
            mode: 'history',
            beforeEnter: (to, from, next) => {
                if(Nova.app.$hasPermission('create reservations'))
                {
                    next();
                } else {
                    next('/403');
                }
                
            }
        },
        {
            name: 'new-group-reservation',
            path: '/new-group-reservation',
            component: require('./components/NewGroupReservation'),
            permissions: ['create reservations'],
            beforeEnter: (to, from, next) => {
                if(Nova.app.$hasPermission('create reservations'))
                {
                    next();
                } else {
                    next('/403');
                }
                
            }
        },

        {
            name: 'reservation',
            path: '/reservation/:id',
            component: require('./components/Reservation'),
            permissions: ['view reservations'],
            beforeEnter: (to, from, next) => {
                if(Nova.app.$hasPermission('view reservations'))
                {
                    next();
                } else {
                    next('/403');
                }
                
            }
        },

        {
            name: 'customers',
            path: '/customers',
            component: require('./components/Customers'),
            props: route => {
                return {
                    resourceName: 'customers',
                    viaResource: route.query.viaResource,
                    viaResourceId: route.query.viaResourceId,
                    viaRelationship: route.query.viaRelationship,
                }
            },
            permissions: ['view customers'],
            beforeEnter: (to, from, next) => {
                if(Nova.app.$hasPermission('view customers'))
                {
                    next();
                } else {
                    next('/403');
                }
                
            }
        },

        {
            name: 'report-initial',
            path: '/dashboard/:dashboardName',
            component: Dashboard,
            props: true,
            meta:{
                breadcrumb:[
                    {
                        name : 'Home',
                        link : '/dashboards/main'
                    },

                    {
                        name : 'Reports',
                        link : '/dashboard/reports'
                    }

                ]
            },
            permissions: ['view reports'],
            beforeEnter: (to, from, next) => {
                if(Nova.app.$hasPermission('view reports'))
                {
                    next();
                } else {
                    next('/403');
                }
                
            }
        },

        {
            name: 'reservations-management',
            path: '/reservations-management',
            component: require('./components/ReservationManagementComponent'),
            permissions: ['view reservations'],
            beforeEnter: (to, from, next) => {
                if(Nova.app.$hasPermission('view reservations'))
                {
                    next();
                } else {
                    next('/403');
                }
                
            }
        },

        {
            name: 'reservation-noc',
            path: '/reservation-noc/:id',
            component: require('./components/ReservationWithoutCustomer'),
            permissions: ['view reservations'],
            beforeEnter: (to, from, next) => {
                if(Nova.app.$hasPermission('view reservations'))
                {
                    next();
                } else {
                    next('/403');
                }
                
            }
        },


        {
            name: 'notifications',
            path: '/notifications',
            component: require('./components/Notifications'),
        },
    ];

    router.addRoutes(routes);

    Vue.mixin({
        methods: {
            __printReceipt: function (xml, meta) {
                return new Promise(async (resolve, reject) => {
                    const postData = {
                        invoiceXML: xml,
                        meta: {
                            ...(meta.credit_note_number && {
                                credit_note_number: meta.credit_note_number
                                    ? meta.credit_note_number
                                    : null,
                            }),
                            ...(meta.invoice_reference_number && {
                                invoice_reference_number: meta.invoice_reference_number
                                    ? meta.invoice_reference_number
                                    : null,
                            }),
                            ...(meta.debit_note_number && {
                                debit_note_number: meta.debit_note_number
                                    ? meta.debit_note_number
                                    : null,
                            }),
                            ...(meta.invoice_type && {
                                invoice_type: meta.invoice_type
                                    ? meta.invoice_type
                                    : null,
                            }),
                        },
                    };
                
                    const response = await axios({
                        url: `/nova-vendor/calender/reservation/get-zatca-receipt`,
                        method: "POST",
                        data: postData,
                        responseType: "blob", // important
                    });
            
                    if (response.status !== 200) {
                        reject(new Error("Failed to fetch PDF"));
                        return;
                    }
            
                    let filename = `reciept.pdf`;
                   
                    if(response.headers["content-disposition"]) {
                        filename = response.headers["content-disposition"]
                            .split(";")
                            .find((n) => n.includes("filename"))
                            .replace("filename=", "")
                            .replace(/"/g, "");
                    }
                   
                    const url = window.URL.createObjectURL(
                        new Blob([response.data])
                    );
                    const link = document.createElement("a");
                    link.href = url;
                    link.setAttribute("download", filename.trim());
                    document.body.appendChild(link);
                    link.click();
                    link.remove();
                    resolve(true);
                });
            }
        }
    });
    // router.afterEach((to, from, next) => {
    //     const routeName = to.name;
    //     console.log('route name',routeName);
    //     const filteredRoute = routes.find((route) => route.name === routeName);
    //     if(filteredRoute) {
    //         let perm = [];
    //         filteredRoute.permissions.forEach((permission) => {
    //             perm.push(Nova.app.$hasPermission(permission));
    //         })
    //         next();
    //     }
    //     // if(!filteredRoute && routes.length > 0) {
    //     //     next('/403');
    //     // }
    // })
    // router.beforeEach((to, from, next) => {
    //     const routeName = to.name;
    //     console.log('route name',routeName);
    //     const filteredRoute = routes.find((route) => route.name === routeName);
    //     console.log(filteredRoute);
    //     if(filteredRoute) {
    //         let perm = [];
    //         filteredRoute.permissions.forEach((permission) => {
    //             perm.push(Nova.app.$hasPermission(permission));
    //         })
    //         next({name: '/'});
    //     }
    //     // if(!filteredRoute && routes.length > 0) {
    //     //     next('/403');
    //     // }
       
    //     //     if(filteredRoutes.length > 0) {
    //     //         filteredRoutes.forEach((route) => {
    //     //             let perm = [];
    //     //             const permissions = route.permissions;
    //     //             permissions.forEach(permission => {
    //     //                 perm.push(Nova.app.$hasPermission(permission));
    //     //             });
    //     //             if(perm.every(value => value === true)) {
    //     //                 console.log('permitted');
    //     //                 next();
    //     //             }
    //     //             next('/403');
    //     //     });
    //     // next('/403');
    //     // }
    // })
})

