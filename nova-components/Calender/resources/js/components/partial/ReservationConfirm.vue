<template>
  <div class="item_reservation_button">
    <button  class="main_button confirm_button" v-if="!quick" @click="open">{{__('Confirm Reservation')}}</button>
    <sweet-modal :enable-mobile-fullscreen="false" :pulse-on-block="false" :title="__('Confirm Reservation')" overlay-theme="dark" ref="confirmReservationModal" class="confirm_reservation_modal">
      <loading :active.sync="loading" :can-cancel="true" :loader="'spinner'" :color="'#7e7d7f'" :is-full-page="false"></loading>
      <span>{{__('Are you sure to cofirm this reservation ?')}}</span>
      <button class="confirm_button" @click="confirm">{{__('Confirm Reservation')}}</button>
    </sweet-modal>
  </div>
</template>

<script>
    import Loading from 'vue-loading-overlay';
    import 'vue-loading-overlay/dist/vue-loading.css';
    export default {
        name: "reservation-confirm",
        components : {
            Loading
        },
        props : ['quick' , 'reservation_id'],
        data: () => {
            return {
                loading: false,
                time: moment().format('HH:mm'),
                cancelReason : null,
                invoices : [],
                quickReservation : null,
                quickModal : {
                    from : null ,
                    modal : null
                }
            }
        },
        methods: {
            open() {
                this.loading = false;
                this.$refs.confirmReservationModal.open();
            },
            confirm() {
                this.loading = true;
                axios.post('/nova-vendor/calender/confirm-online', {
                    reservation_id: this.reservation_id,
                })
                .then(response => {
                    this.loading = false;
                    if(response.data.status == 'converted_to_confirmed_reservation'){
                        this.$toasted.show(this.__('Reservation confirmed successfully'), {type: 'success'});
                        Nova.$emit('update-reservation');
                    }

                    if(response.data.status == 'unit_is_not_available'){
                        this.$toasted.show(this.__('Unit is not available to confirm this reservation on it'), {type: 'error'});
                    }

                    this.$refs.confirmReservationModal.close();
                })
            },
            stepBack(){
                this.$refs.confirmReservationModal.close();
            }


        },
        mounted() {

        }

    }
</script>
<style lang="scss" scoped>
.confirm_reservation_modal {

 span {
    padding: 30px 20px;
    line-height: normal;
    display: block;
    font-size: 20px;
    color: #000;
  } /* span */
  button.confirm_button {
    height: 35px;
    background: #4599dd;
    width: 100%;
    border-radius: 5px;
    font-size: 15px;
    color: #fff;
    &:hover {
        background: #0071C9;
        border-color: #0071C9;
    }
  } /* confirm_button */
} /* add_comment_modal */
</style>
