<template>
  <div>
    <loading :active.sync="isLoading" :can-cancel="true" :is-full-page="fullPage"></loading>
    <a href="javascript:;" title="#" @click="send" v-permission="'change to under cleaning'">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 480 480" fill="#FF9019"><path d="M416 336h-12.5c-8.8 0-17.5 2.1-25.3 6a40.67 40.67 0 0 1-36.3 0c-4.6-2.2-9.4-3.9-14.4-4.8L343 216h17c4.4 0 8-3.6 8-8v-32c0-4.4-3.6-8-8-8h-32v-24c0-2.1-.8-4.2-2.3-5.7L288 100.7V75.8l12.1-9.7 20.7 41.4 14.3-7.2-22.3-44.5 9.9-7.9H352c4.4 0 8-3.6 8-8V8c0-4.4-3.6-8-8-8h-80c-39.7 0-72 32.3-72 72 0 4.4 3.6 8 8 8h16v20.7l-21.7 21.7c-1.5 1.5-2.3 3.5-2.3 5.7v40h-16v-64c0-1-.2-2-.6-2.9-.1-.3-.3-.6-.5-.9-.2-.4-.4-.8-.7-1.2L152 61.2V8c0-4.4-3.6-8-8-8H96c-4.4 0-8 3.6-8 8v53.2L57.8 99c-.3.4-.5.8-.7 1.2-.2.3-.3.6-.5.9-.4.9-.6 1.9-.6 2.9v64H24c-4.4 0-8 3.6-8 8v32c0 4.4 3.6 8 8 8h17l31.1 241c.5 4 3.9 7 7.9 7h188.3a47.85 47.85 0 0 0 35.7 16h12.5c8.8 0 17.5-2.1 25.3-6a40.67 40.67 0 0 1 36.3 0c7.9 3.9 16.5 6 25.3 6H416c26.5 0 48-21.5 48-48v-48c0-26.5-21.5-48-48-48zM328 16h16v16h-16V16zm24 168v16H240v-16h112zM216.6 64c4-27.5 27.6-48 55.4-48h40v20.2L277.2 64h-60.6zM272 80v16h-32V80h32zm-56 51.3l19.3-19.3h41.4l35.3 35.3V168h-96v-36.7zm-8 52.7h16v56c0 3 1.7 5.8 4.4 7.2l48 24c2.3 1.2 4.1 3.2 4.9 5.7.8 2.7.5 5.6-.8 8-2.3 4.6-7.9 6.5-12.5 4.2l-32.5-16.2c-4-1.9-8.8-.3-10.7 3.7-.5 1.1-.8 2.3-.8 3.5v80c0 8.8-7.2 16-16 16v-72h-16v72h-16v-72h-16v72h-16v-72h-16v72c-8.8 0-16-7.2-16-16V184h96zM104 16h32v40h-32V16zm-4.2 56h40.3l19.2 24H80.6l19.2-24zM72 112h96v56H72v-56zm-40 88v-16h64v16H32zm224 184v48c0 5.5 1 10.9 2.8 16H87L57.1 216H96v144a31.97 31.97 0 0 0 32 32h80a31.97 31.97 0 0 0 32-32v-67.1l20.9 10.4c12.6 6.2 27.9 1 34.1-11.6 0-.1.1-.1.1-.2 3.1-6.1 3.6-13.3 1.5-19.8s-6.8-11.9-13-15L240 235.1V216h86.9l-15.5 120H304c-26.5 0-48 21.5-48 48zm192 48a31.97 31.97 0 0 1-32 32h-12.5c-6.3 0-12.5-1.5-18.2-4.3-16-7.9-34.7-7.9-50.6 0-5.6 2.8-11.9 4.3-18.2 4.3H304a31.97 31.97 0 0 1-32-32v-12.3c8.8 7.9 20.2 12.3 32 12.3h12.5c8.8 0 17.5-2.1 25.3-6a40.67 40.67 0 0 1 36.3 0c7.9 3.9 16.5 6 25.3 6H416c11.8 0 23.2-4.4 32-12.3V432zm-32-16h-12.5c-6.3 0-12.5-1.5-18.2-4.3-16-7.9-34.7-7.9-50.6 0-5.6 2.8-11.9 4.3-18.2 4.3H304a32 32 0 1 1 0-64h12.5c6.3 0 12.5 1.5 18.2 4.3 16 7.9 34.7 7.9 50.6 0 5.6-2.8 11.9-4.3 18.2-4.3H416a32 32 0 1 1 0 64zm-8-48h16v16h-16zm-80 0h16v16h-16zm-32 8h16v16h-16zm80 0h16v16h-16z"/></svg>
      {{ __('To Cleaning') }}
    </a>
  </div>
</template>

<script>
    // Import component
    import Loading from 'vue-loading-overlay';
    // Import stylesheet
    import 'vue-loading-overlay/dist/vue-loading.css';
    export default {
        name: "GoToCleaning",
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
                        type: 'cleaning'
                    })
                    .then(response => {
                        setTimeout(() => {
                            this.$toasted.show(this.__('Unit status has been changed to under cleaning successfully'), {type: 'success'});
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
