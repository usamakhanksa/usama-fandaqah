Nova.booting((Vue, router, store) => {
    Vue.component('index-unit-price', require('./components/IndexField'))
    Vue.component('detail-unit-price', require('./components/DetailField'))
    Vue.component('form-unit-price', require('./components/FormField'))
})
