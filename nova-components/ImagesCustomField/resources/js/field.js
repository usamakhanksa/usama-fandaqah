Nova.booting((Vue, router, store) => {
    Vue.component('index-images-custom-field', require('./components/IndexField'))
    Vue.component('detail-images-custom-field', require('./components/DetailField'))
    Vue.component('form-images-custom-field', require('./components/FormField'))
})
