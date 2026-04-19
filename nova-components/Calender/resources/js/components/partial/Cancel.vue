<template>
  <div class="item_reservation_button">
    <button  class="main_button cancel" v-if="!quick" @click="open">{{__('Cancel Reservation')}}</button>
    <sweet-modal :enable-mobile-fullscreen="false" :pulse-on-block="false" :title="__('Cancel Reservation')" overlay-theme="dark" ref="cancelModal" class="cancel_reservation_modal">
      <loading :active.sync="loading" :can-cancel="true" :loader="'spinner'" :color="'#7e7d7f'" :is-full-page="false"></loading>
      <textarea name="cancelres" id="" cols="30" rows="3" :placeholder="__('The reason for canceling the reservation')" v-model="cancelReason"></textarea>
      <div class="form-group">
        <label>{{__('Cancellation Fees')}}</label>
        <input type="number" class="form-control" v-model="cancellationFees" min="0" step="0.01">
      </div>
      <div class="form-group">
        <label>{{__('No Show Fees')}}</label>
        <input type="number" class="form-control" v-model="noShowFees" min="0" step="0.01">
      </div>
      <button class="cancel_button" @click="send">{{__('Cancel Reservation')}}</button>
    </sweet-modal>
  </div>
</template>

<script>
    import Loading from 'vue-loading-overlay';
    import 'vue-loading-overlay/dist/vue-loading.css';
    export default {
        name: "Guests",
        components : {
            Loading
        },
        props : ['quick','reservation'],
        data: () => {
            return {
                loading: false,
                time: moment().format('HH:mm'),
                cancelReason : null,
                cancellationFees: 0,
                noShowFees: 0,
                invoices : [],
                quickReservation : null,
                quickModal : {
                    from : null ,
                    modal : null
                }
            }
        },
        methods: {
            async open() {

                const response = await axios.post(`/nova-vendor/calender/reservation/check-cancel/is-included-in-an-invoice`, {
                    main_id: this.reservation.attachable_id ? this.reservation.attachable_id : this.reservation.id,
                    current_id : this.reservation.id
                });
                 if(!response.data.can_be_canceled){
                    this.$toasted.show(this.__('Sorry , we can\'t cancel this reservation cause it has invoices , please add credit note for them first to cancel this reservation'), {
                        duration : 5000,
                        type: 'error',
                        position: "top-center",
                    });
                    return false;
                 }

                if (this.reservation.staah_booking_id){
                    this.$toasted.show(this.__('Can not cancel this reservation cause it is coming from channel manager'), {
                        duration: 5000,
                        type: 'error',
                        position: "top-center",
                    });
                    return false;
                }

                if(this.reservation.reservation_type == 'group' && this.reservation.attachable_reservations_count){
                    this.$toasted.show(this.__('You can not cancel main reservation'), {
                        duration : 5000,
                        type: 'error',
                        position: "top-center",
                    });
                    return false;
                }
               axios.get(`/nova-vendor/calender/reservation/${this.reservation.id}/get-invoices`)
                .then((response) => {
                    if(response.data.length){
                        let holder_invoices = _.filter(response.data, function(invoice) {
                            return invoice.invoice_credit_note === null;
                        });
                        if(holder_invoices.length){
                            this.$toasted.show(this.__('Sorry , we can\'t cancel this reservation cause it has invoices , please add credit note for them first to cancel this reservation'), {
                                duration : 5000,
                                type: 'error',
                                position: "top-center",
                            });
                           return false;
                        }else{
                            this.$refs.cancelModal.open();
                        }
                    }else{
                         this.$refs.cancelModal.open();
                    }
                });

            },
            send() {

                this.loading = true;
                axios
                    .post('/nova-vendor/calender/reservation/cancel', {
                        id: this.$parent.reservation.id,
                        reason: this.cancelReason,
                        cancellation_fees: this.cancellationFees,
                        no_show_fees: this.noShowFees
                    })
                    .then(response => {
                        Nova.$emit('update-reservation')
                        this.$refs.cancelModal.close();
                        this.$toasted.show(this.__('Reservation Canceled successfully'), {type: 'success'});
                        this.loading = false;
                    }).catch(err => {
                        this.loading = false;
                        this.$toasted.show(this.__(err), {type: 'error'})
                    })

            },


        },
        mounted() {
            this.invoices = this.$parent.reservation.invoices ;

            Nova.$on('invoice-deleted' , () => {

                axios.get('/nova-vendor/calender/reservationInvoices?id=' +  this.$parent.reservation.id)
                    .then((res) => {
                        this.invoices = _.orderBy(res.data, 'number', 'desc');
                    })
                    .catch((err) => {
                        console.log(err);
                    })
            });

            Nova.$on('invoice-added' , (invoices) => {
                this.invoices = invoices ;
            });

            Nova.$on('quick-open-cancel-reservation-modal' , (reservation) => {
                this.quickReservation = reservation;
                if(this.$parent.reservation.id === reservation.id){
                    this.open();
                }

            });
        }

    }
</script>
<style lang="scss" scoped>
.cancel_reservation_modal {
  textarea {
    width: 100%;
    padding: 10px;
    border-radius: 5px;
    background: #fafafa;
    border: 1px solid #ddd;
    font-size: 15px;
    color: #000;
    margin: 0 auto 10px;
  } /* textarea */
  .form-group {
    margin-bottom: 15px;
    label {
      display: block;
      margin-bottom: 5px;
      font-size: 14px;
      color: #606060;
    }
    input {
      width: 100%;
      padding: 8px;
      border-radius: 5px;
      background: #fafafa;
      border: 1px solid #ddd;
      font-size: 14px;
      color: #000;
    }
  }
  button.cancel_button {
    height: 35px;
    background: #e74444;
    width: 100%;
    border-radius: 5px;
    font-size: 15px;
    color: #fff;
    &:hover {background: #dd3a3a;}
  } /* cancel_button */
} /* add_comment_modal */
</style>
