Nova.booting((Vue, router, store) => {
    router.addRoutes([
        {
            name: 'techincal-support',
            path: '/techincal-support',
            component: require('./components/Tool'),
        },
        {
            name: 'show-ticket',
            path: '/techincal-support/ticket/:id',
            component: require('./components/ticket'),
        },
    ])
})
