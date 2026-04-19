<template>
  <div>
    <loading :active.sync="isLoading" :can-cancel="true" :is-full-page="fullPage"></loading>
    <a href="javascript:;" title="#" @click="send" v-permission="'change to under maintenance'">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" fill="#AAB8C0"><path d="M430.2 122c7.2 7.2 16.1 3.9 19.3 1.6l56.2-40.2c3.9-2.8 6.3-7.4 6.3-12.2V15a14.98 14.98 0 0 0-15-15h-56.2c-4.8 0-9.4 2.3-12.2 6.3l-40.2 56.2c-4.3 6-3.6 14.1 1.6 19.3l9.5 9.5-119.4 119.4-41.2-41.2v-66.1C238.9 46.4 192.5 0 135.5 0H79.2c-6.1 0-11.5 3.7-13.9 9.3-2.3 5.6-1 12.1 3.3 16.3l48.2 48.2c11.9 11.9 11.9 31.2 0 43-11.9 11.9-31.2 11.9-43 0L25.6 68.6c-4.3-4.3-10.7-5.6-16.3-3.3C3.7 67.7 0 73.2 0 79.2v56.2c0 57 46.4 103.4 103.4 103.4h66.1l10.5 10.5L5.6 388.8c-3.3 2.7-5.4 6.6-5.6 10.9a15.06 15.06 0 0 0 4.4 11.4l96.4 96.4c2.8 2.8 6.6 4.4 10.6 4.4h.8c4.3-.2 8.2-2.3 10.9-5.6L262.6 332l10.5 10.5v66.1c0 57 46.4 103.4 103.4 103.4h56.2c6.1 0 11.5-3.7 13.9-9.3 2.3-5.6 1-12.1-3.3-16.3l-48.2-48.2c-11.9-11.9-11.9-31.2 0-43 11.9-11.9 31.2-11.9 43 0l48.2 48.2c4.3 4.3 10.7 5.6 16.3 3.3s9.3-7.8 9.3-13.9v-56.2c0-57-46.4-103.4-103.4-103.4h-66.1L301.2 232l119.4-119.4 9.6 9.4zM167.3 403.1l-58.4-58.4 146-116.8 29.2 29.2-116.8 146zm19-189.9c-2.8-2.8-6.6-4.4-10.6-4.4h-72.3c-40.5 0-73.4-32.9-73.4-73.4v-20L52.6 138c24.1 24.1 62.4 23.1 85.5 0 23.4-23.4 23.8-61.7 0-85.5L115.5 30h20c40.5 0 73.4 32.9 73.4 73.4v72.3c0 4 1.6 7.8 4.4 10.6l20.3 20.3-30 24-17.3-17.4zm-76.1 261.3l-72.7-72.7 47.8-38.3 63.1 63.1-38.2 47.9zm226.1-171.4h72.3c40.5 0 73.4 32.9 73.4 73.4v20l-22.6-22.6c-24.1-24.1-62.4-23.1-85.5 0-23.4 23.4-23.8 61.7 0 85.5l22.6 22.6h-20c-40.5 0-73.4-32.9-73.4-73.4v-72.3c0-4-1.6-7.8-4.4-10.6l-17.3-17.3 24-30 20.3 20.3c2.8 2.9 6.7 4.4 10.6 4.4zM448.5 30H482v33.5l-39.6 28.3-22.2-22.2L448.5 30z"/></svg>
      {{ __('To Maintenance') }}
    </a>
  </div>
</template>

<script>
    // Import component
    import Loading from 'vue-loading-overlay';
    // Import stylesheet
    import 'vue-loading-overlay/dist/vue-loading.css';
    export default {
        name: "GoToMaintenance",
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
                        type: 'maintenance'
                    })
                    .then(response => {
                        setTimeout(() => {
                            this.$toasted.show(this.__('Unit status has been changed to under maintenance successfully'), {type: 'success'});
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
