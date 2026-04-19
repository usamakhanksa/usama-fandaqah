import UUID from 'vue-uuid';
import VCalendar from 'v-calendar';
import SocialSharing from 'vue-social-sharing';
import VueTelInput from 'vue-tel-input';
Nova.booting((Vue, router, store) => {
    Vue.use(UUID);
    Vue.use(SocialSharing)
    Vue.use(VueTelInput);
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

    const routes = [
        {
            name: 'pos',
            path: '/pos',
            component: require('./components/PosComponent'),
            permissions: ['view pos'],
            beforeEnter: (to, from, next) => {
                if(Nova.app.$hasPermission('view pos') || Nova.app.$hasPermission('View POS'))
                {
                    next();
                } else {
                    next('/403');
                }
            }
        },
        // {
        //     name: 'pos.operations',
        //     path: '/pos/operations',
        //     component: require('./components/Operations'),
        //     meta:{
        //         breadcrumb:[
        //             {
        //                 name : 'Home',
        //                 link : '/'
        //             },

        //             {
        //                 name : 'POS',
        //                 link : '/pos'
        //             },

        //             {
        //                 name : 'Operations',
        //                 link : '/pos/operations'
        //             },


        //         ]
        //     },
        // },

        {
            name: 'pos.services-management',
            path: '/pos/services-management',
            component: require('./components/ServicesManagement.vue'),
            meta:{
                breadcrumb:[
                    {
                        name : 'Home',
                        link : '/'
                    },

                    {
                        name : 'POS',
                        link : '/pos'
                    },

                    {
                        name : 'Operations',
                        link : '/pos/services-management'
                    },


                ]
            },
            permissions: ['view pos'],
            beforeEnter: (to, from, next) => {
                if(Nova.app.$hasPermission('view pos') || Nova.app.$hasPermission('View POS'))
                {
                    next();
                } else {
                    next('/403');
                }
            }
        },
    ]

    router.addRoutes(routes)
})
