import moment from 'moment'
Nova.booting((Vue, router, store) => {

    Vue.filter('formatDate', function(value) {
        if (value) {
            return moment(String(value)).format('YYYY/MM/DD hh:mm')
        }
    });
    Vue.component('customer-notes', require('./components/Tool'))
})
