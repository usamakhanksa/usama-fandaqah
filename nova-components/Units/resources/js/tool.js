import UpdateUnitCategory from './components/UpdateUnitCategory';
import VCalendar from 'v-calendar';
import moment from "moment";


Nova.booting((Vue, router, store) => {

    // Some Date Filters
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


    // using v-calendar
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


    Vue.component('units-card', require('./components/Card'))
    router.addRoutes([
        {
            name: 'units',
            path: '/units',
            component: require('./components/Tool'),
            permissions: ['view units'],
            beforeEnter: (to, from, next) => {
                if (Nova.app.$hasPermission('view units')) {
                    next();
                } else {
                    next('/403');
                }
            }
        },
        {
            name: 'units.status',
            path: '/units/status',
            component: require('./components/ReservationsTable'),
            permissions: ['watch unit housing'],
            beforeEnter: (to, from, next) => {
                if (Nova.app.$hasPermission('watch unit housing')) {
                    next();
                } else {
                    next('/403');
                }
            }
        },

        {
            name: 'units.housekeeping',
            path: '/units/housekeeping',
            component: require('./components/Housekeeping'),
            permissions: ['watch unit housing'],
            beforeEnter: (to, from, next) => {
                if (Nova.app.$hasPermission('watch unit housing')) {
                    next();
                } else {
                    next('/403');
                }
            }
        },

        {
            name: 'units.offersAndSpecialPrices',
            path: '/units/offers-and-special-prices',
            component: require('./components/OffersAndSpecialPrices'),
            meta:{
                breadcrumb:[
                    {
                        name : 'Home',
                        link : '/dashboards/main'
                    },
                    {
                        name : 'Units',
                        link : '/resources/units'
                    },

                    {
                        name : 'Offers & Special Prices',
                        link : '#'
                    },


                ]
            },
            permissions: ['view units'],
            beforeEnter: (to, from, next) => {
                if (Nova.app.$hasPermission('view units')) {
                    next();
                } else {
                    next('/403');
                }
            }
        },

        {
            name: 'units.levelsControl',
            path: '/units/levels-control',
            component: require('./components/LevelsControl'),
            meta:{
                breadcrumb:[
                    {
                        name : 'Home',
                        link : '/dashboards/main'
                    },
                    {
                        name : 'Units',
                        link : '/resources/units'
                    },

                    {
                        name : 'Levels Control',
                        link : '#'
                    },


                ]
            },
            permissions: ['view units'],
            beforeEnter: (to, from, next) => {
                if (Nova.app.$hasPermission('view units')) {
                    next();
                } else {
                    next('/403');
                }
            }
        },
        {
            name: 'units.discountCouponsControl',
            path: '/units/discount-coupons-control',
            component: require('./components/DiscountCouponsControl'),
            meta:{
                breadcrumb:[
                    {
                        name : 'Home',
                        link : '/dashboards/main'
                    },
                    {
                        name : 'Units',
                        link : '/resources/units'
                    },

                    {
                        name : 'Discount Coupons',
                        link : '#'
                    },


                ]
            },
            permissions: ['view units'],
            beforeEnter: (to, from, next) => {
                if (Nova.app.$hasPermission('view units')) {
                    next();
                } else {
                    next('/403');
                }
            }
        },

        {
            name: 'notes',
            path: '/notes',
            component: require('./components/notes'),
        },
    ])
});

Nova.booting((Vue, router, store) => {


    Vue.component('update-unit-category', require('./components/UpdateUnitCategory'));

    router.beforeEach((to, from, next) => {
        
        // Override Units Resource Table and Pagination Links  Component
        if(to.name === 'custom-index' && to.params.resourceName === 'units'){
            Vue.component('card', require('./components/modules/Card'));
            Vue.component('resource-table', require('./components/modules/Units'));
            Vue.component('pagination-links', require('./components/modules/PaginationLinks'));
        }else if(to.name === 'custom-index' && to.params.resourceName === 'unit-categories'){
            Vue.component('card', require('./components/modules/Card'));
            Vue.component('resource-table', require('./components/modules/UnitCategories'));
            Vue.component('pagination-links', require('./components/modules/PaginationLinks'));
        }else if(to.name === 'custom-index' && to.params.resourceName === 'unit-general-features'){
            Vue.component('card', require('./components/modules/Card'));
            Vue.component('resource-table', require('./components/modules/UnitGeneralFeatures'));
            Vue.component('pagination-links', require('./components/modules/PaginationLinks'));
        }else if(to.name === 'custom-index' && to.params.resourceName === 'unit-special-features'){
            Vue.component('card', require('./components/modules/Card'));
            Vue.component('resource-table', require('./components/modules/UnitSpecialFeatures'));
            Vue.component('pagination-links', require('./components/modules/PaginationLinks'));
        }else if(to.name === 'custom-index' && to.params.resourceName === 'unit-options'){
            Vue.component('card', require('./components/modules/Card'));
            Vue.component('resource-table', require('./components/modules/UnitOptions'));
            Vue.component('pagination-links', require('./components/modules/PaginationLinks'));
        }
        else{
            // I need this else to get back the native components otherwise all resource-table and pagination-links will be overridden in the whole system cause this is javascript
            // So am gonna get those two core components from nova and will be located inside core folder in our card
            Vue.component('card', require('./components/core/Card'));
            Vue.component('resource-table', require('./components/core/ResourceTable'));
            Vue.component('pagination-links', require('./components/core/PaginationLinks'));
        }


        let customComponent = null;
        if (to.name === "custom-edit" && to.params.resourceName === "unit-categories") {
            customComponent = 'update-unit-category';
        }
        if (customComponent && Vue.options.components[customComponent]) {
            next({
                name: 'update-unit-category',
                params: Object.assign({}, to.params, {component: customComponent}),
                query: to.query
            });
        } else {
            next();
        }
    });

    router.addRoutes([
        {
            name: 'update-unit-category',
            path: '/resources/unit-categories/:resourceId/edit',
            component: UpdateUnitCategory,
            props: function props(route) {
                return {
                    component: route.params.component,
                    resourceName: 'unit-categories',
                    resourceId: route.params.resourceId,
                    viaResource: route.query.viaResource,
                    viaResourceId: route.query.viaResourceId,
                    viaRelationship: route.query.viaRelationship
                };
            },
            permissions: ['view units'],
            beforeEnter: (to, from, next) => {
                if (Nova.app.$hasPermission('view units')) {
                    next();
                } else {
                    next('/403');
                }
            }
        },
        {
            path: '/resources/units',
            permissions: ['view units'],
            beforeEnter: (to, from, next) => {
                if (Nova.app.$hasPermission('view units')) {
                    next();
                } else {
                    next('/403');
                }
            }
        }
    ]);
});
