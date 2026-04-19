<template>
  <div class="item_reservation_button">
    <sweet-modal :enable-mobile-fullscreen="false" :pulse-on-block="false" :title="__('Confirm Transfer')" overlay-theme="dark" ref="confirmTransfer" class="online_payment_transfer">
      <loading :active.sync="loading" :can-cancel="true" :loader="'spinner'" :color="'#7e7d7f'" :is-full-page="false"></loading>
      <span>{{__('Are you sure to confirm this transfer ?')}}</span>
      <button class="confirm_button" @click="confirm">{{__('Confirm Transfer')}}</button>
    </sweet-modal>
  </div>
</template>

<script>
    import Loading from 'vue-loading-overlay';
    import 'vue-loading-overlay/dist/vue-loading.css';
    export default {
        name: "transfer-confirm",
        components : {
            Loading
        },
        props : ['id'],
        data: () => {
            return {
                current_user_id : Nova.config.userId
            }
        },
        methods: {
            open() {
                this.loading = false;
                this.$refs.confirmReservationModal.open();
            },
            confirm() {
                this.loading = true;
                axios.post(window.HYPERPAY_PAYMENT_URL + `api/hyperpay/mtransfer/${this.id}/by/${this.current_user_id}`)
                .then(response => {
                    if(response.data.success){
                       Nova.$emit('call-payments-again-after successfull-transfer', this.id);
                    }
                })

                this.loading = false;
                this.$refs.confirmTransfer.close();
            },
            stepBack(){
                this.$refs.confirmTransfer.close();
            }


        },
        mounted() {

        }

    }
</script>
<style lang="scss" scoped>
.online_payment_transfer {

 span {
    padding: 10px 20px;
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
