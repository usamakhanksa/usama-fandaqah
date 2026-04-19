Nova.booting((Vue, router, store) => {
    Vue.component('index-tel-input', require('./components/IndexField'))
    Vue.component('detail-tel-input', require('./components/DetailField'))
    Vue.component('form-tel-input', require('./components/FormField'))
})
