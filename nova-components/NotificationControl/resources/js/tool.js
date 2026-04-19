Nova.booting((Vue, router, store) => {
    router.addRoutes([
        {
            name: 'notification-control',
            path: '/notification-control',
            component: require('./components/NotificationControl'),
            meta:{
                breadcrumb:[
                    {
                        name : 'Home',
                        link : '/'
                    },
                    {
                        name : 'Settings',
                        link : '/settings'
                    },
                    {
                        name : 'Notifications Settings Control',
                        link : '/notification-control'
                    }

                ]
            },
        },
    ])
})
