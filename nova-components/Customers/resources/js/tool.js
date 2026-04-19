import VueTelInput from 'vue-tel-input';

Nova.booting((Vue, router, store) => {
    Vue.filter('formatDateSpecial', function(value) {
        if (value) {
            return moment(String(value)).format('YYYY/MM/DD')
        }
    });

    Vue.filter('formatDateWithTime', function(value) {
        if (value) {
            return moment(String(value)).format('YYYY/MM/DD hh:mm A')
        }
    });
    Vue.use(VueTelInput);

    const routes = [
        {
            name: 'new-customers',
            path: '/new/customers',
            component: require('./components/Tool'),
            permissions: ['add customers'],
            beforeEnter: (to, from, next) => {
                if(Nova.app.$hasPermission('add customers'))
                {
                    next();
                } else {
                    next('/403');
                }
                
            }
        },
        {
            name: 'new-customers-create',
            path: '/new/customers/create',
            component: require('./components/create'),
            permissions: ['add customers'],
            beforeEnter: (to, from, next) => {
                if(Nova.app.$hasPermission('add customers'))
                {
                    next();
                } else {
                    next('/403');
                }
                
            }
        },
        {
            name: 'new-customers-show',
            path: '/new/customers/:id',
            component: require('./components/show'),
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
            name: 'companies',
            path: '/companies',
            component: require('./components/companies/ListCompanies'),
            permissions: ['view companies'],
            beforeEnter: (to, from, next) => {
                if(Nova.app.$hasPermission('view companies'))
                {
                    next();
                } else {
                    next('/403');
                }
                
            }
        },

        {
            name: 'company',
            path: '/companies/:id/profile',
            component: require('./components/companies/CompanyProfile'),
            props: true,
            permissions: ['view company profile'],
            beforeEnter: (to, from, next) => {
                if(Nova.app.$hasPermission('view company profile'))
                {
                    next();
                } else {
                    next('/403');
                }
                
            }
        },

        // {
        //     name: 'bulk-reservation',
        //     path: '/bulk-reservation/:date',
        //     component: require('./components/bulk/NewReservation'),
        // },
    ]

    router.addRoutes(routes);
})
