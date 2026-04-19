<template>
    <div>
        <loading :active.sync="isLoading"
                 :can-cancel="true"
                 :is-full-page="fullPage"></loading>
        <a href="javascript:;" title="#" @click="send" v-permission="'change to available'">
            <svg style="fill: #28a745;" enable-background="new 0 0 512 512" version="1.1" viewBox="0 0 512 512" xml:space="preserve" xmlns="http://www.w3.org/2000/svg">
                                        <path class="st0" d="m437 75c-48.3-48.4-112.6-75-181-75s-132.7 26.6-181 75c-48.4 48.3-75 112.6-75 181s26.6 132.7 75 181 112.6 75 181 75 132.7-26.6 181-75 75-112.6 75-181-26.6-132.7-75-181zm-181 407c-124.6 0-226-101.4-226-226s101.4-226 226-226 226 101.4 226 226-101.4 226-226 226z"/>
                <path class="st0" d="m378.3 173.9c-5.9-5.9-15.4-5.9-21.2 0l-132.5 132.4-69.7-69.7c-5.9-5.9-15.4-5.9-21.2 0s-5.9 15.4 0 21.2l80.3 80.3c2.9 2.9 6.8 4.4 10.6 4.4s7.7-1.5 10.6-4.4l143.1-143.1c5.9-5.8 5.9-15.3 0-21.1z"/>
                                    </svg>
            {{ __('To Available') }}
        </a>
    </div>
</template>

<script>
    // Import component
    import Loading from 'vue-loading-overlay';
    // Import stylesheet
    import 'vue-loading-overlay/dist/vue-loading.css';
    export default {
        name: "MakeUnitAvailable",
        components: {
            Loading
        },
        props: {
            unit: null
        },
        data: () => {
            return {
                isLoading: false,
                fullPage: true
            }
        },
        methods: {
            send() {
                this.isLoading = true;
                axios
                    .post('/nova-vendor/calender/units/status', {
                        unit: this.unit.id,
                        type: 'enabled'
                    })
                    .then(response => {
                        setTimeout(() => {
                            this.$toasted.show(this.__('Unit status has been changed to available successfully'), {type: 'success'});
                            this.isLoading = false;
                            // this.$parent.$parent.getUnits();
                            Nova.$emit('unit-status-self-changed' , response.data.unit);
                        },1000)

                        Nova.$emit('unit-status-setting-changed');

                    }).catch(err => {
                    this.isLoading = false;
                    this.$toasted.show(this.__(err), {type: 'error'})
                });
            },
        },
    }
</script>

<style scoped>

</style>
