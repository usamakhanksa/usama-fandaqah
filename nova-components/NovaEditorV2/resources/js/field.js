Nova.booting((Vue, router, store) => {
    Vue.component('index-nova-editor-v2', require('./components/IndexField'))
    Vue.component('detail-nova-editor-v2', require('./components/DetailField'))
    Vue.component('form-nova-editor-v2', require('./components/FormField'))
})
