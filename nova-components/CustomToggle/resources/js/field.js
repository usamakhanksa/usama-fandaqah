Nova.booting((Vue, router, store) => {
    Vue.component('index-custom-toggle', require('./components/IndexField'))
    Vue.component('detail-custom-toggle', require('./components/DetailField'))
    Vue.component('form-custom-toggle', require('./components/FormField'))
})
