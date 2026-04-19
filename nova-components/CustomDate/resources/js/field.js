Nova.booting((Vue, router, store) => {
    Vue.component('index-custom-date', require('./components/IndexField'))
    Vue.component('detail-custom-date', require('./components/DetailField'))
    Vue.component('form-custom-date', require('./components/FormField'))
})
