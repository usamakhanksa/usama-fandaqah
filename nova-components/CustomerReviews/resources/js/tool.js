Nova.booting((Vue, router, store) => {
    router.addRoutes([
        {
            name: 'customer-reviews',
            path: '/customer-reviews',
            component: require('./components/Tool'),
        },
    ])
})
