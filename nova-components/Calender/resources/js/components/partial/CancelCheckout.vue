<template>
  <div>
      <button class="main_button cancel" v-if="!quick" @click="open">{{__('Cancel Checkout')}}</button>

      <sweet-modal :enable-mobile-fullscreen="false"  :pulse-on-block="false" :title="__('Cancel Reservation Checkout')" overlay-theme="dark" ref="cancelCheckoutConfirm" class="cancel_checkout">
          <div class="relative mx-auto justify-center z-20">
              <loading
                  :active.sync="loading"
                  :is-full-page="false" />
              <span>{{__('Are you sure to cancel reservation checkout ?')}}</span>
              <div class="bg-30 px-6 py-3 flex -mx-2 -mb-2">
                  <div class="flex justify-end flex-wrap">
                      <button id="confirm-delete-button" @click="confirm"   class="btn btn-default btn-danger m-0">{{__('Reset Checkout')}}</button>
                      <button type="button" @click="stepBack()"  class="btn btn-default bg-gray-400 ml-2"> {{__('Back')}}</button>
                  </div>
              </div>
          </div>
      </sweet-modal>
  </div>
</template>

<script>
    import Loading from 'vue-loading-overlay';
    import 'vue-loading-overlay/dist/vue-loading.css';
    export default {
        name: "cancel-checkout",
        components : {
            Loading
        },
        props : ['quick' , 'reservation_id' , 'unit'],
        data: () => {
            return {
                loading: false,
                locale : Nova.config.local
            }
        },
        methods: {
            open(){
                 
                 if(this.unit.status != 1){
                    this.$toasted.show(this.__('Checkout can not be canceled because unit :name - number :number is under :status' , {name : this.unit.name[this.locale] ,number : this.unit.unit_number , status : this.unit.status ==2 ? this.__('Under Cleaning') : this.__('Under Maintenance')}), {type: 'error'});
                    return;
                }
              this.$refs.cancelCheckoutConfirm.open();
            },
            confirm() {

                this.loading = true;
                axios
                    .get(`/nova-vendor/calender/cancel-checkout?id=${this.reservation_id}`)
                    .then(response => {
                        Nova.$emit('update')
                        // this.$router.push(`reservation/${this.reservation_id}`);
                        this.$toasted.show(this.__('Reservation Checkout Has been reset successfully'), {type: 'success'});
                        this.loading = false;
                        this.$refs.cancelCheckoutConfirm.close();
                    });
            },
            stepBack(){
                this.$refs.cancelCheckoutConfirm.close();
            },


        },

    }
</script>
<style lang="scss" scoped>
.cancel_checkout {
    h2 {
        line-height: 63px;
    } /* h2 */
    span {
        padding: 30px 20px;
        line-height: normal;
        display: block;
        font-size: 20px;
        color: #000;
    } /* span */
} /* delete_confirm_slider_image */
</style>
