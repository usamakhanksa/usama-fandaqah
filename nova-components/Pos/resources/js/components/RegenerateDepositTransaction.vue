<template>
  <sweet-modal :enable-mobile-fullscreen="false" :pulse-on-block="false" :hide-close-button="true" overlay-theme="dark" ref="regenerateConfirm" class="delete_confirm_modal">
    <div class="delete_confirm_modal_content">
      <loading :active.sync="isLoading" :is-full-page="false" />
      <h1>{{__('are you sure ?')}}</h1>
      <h2>{{__('Do you wish to recreate the deposit transaction?')}}</h2>
      <div class="alert_info"  v-if="type == 'deposit' && !transaction_deleted">{{__('Note : Deposit transaction attached to this service will be deleted !!')}}</div>
      <div class="buttons_delete">
        <button id="confirm-delete-button" @click="createTransaction(id)" class="yes_delete_button create">{{__('Yes, create !')}}</button>
        <button type="button" @click="stepBack()" class="back_delete_button"> {{__('Do not retreat !')}}</button>
      </div>
    </div>
  </sweet-modal>
</template>

<script>
    import Loading from 'vue-loading-overlay';
    // Import stylesheet
    import 'vue-loading-overlay/dist/vue-loading.css';
    export default {
        name: "RegenerateDepositTransaction",
        components : {Loading},
        props : ['transaction_id'],
        data(){
            return {
                isLoading : false
            }
        },
        methods : {
          createTransaction(id){

                this.isLoading = true;
                axios.post(`/nova-vendor/pos/regenerate-transaction` , {
                    transaction_id : this.transaction_id
                })
                        .then( response => {
                            this.isLoading = false;

                            this.$toasted.success(Nova.app.__('Transaction has been re-created successfully'), {
                              duration: 3000
                            });
                            Nova.$emit(`transaction-recreated`);
                            this.$refs.regenerateConfirm.close();

                        })
            },
            stepBack(){
                this.$refs.regenerateConfirm.close();
            }
        }
    }
</script>

<style lang="scss">

 .alert {
      div > span {
          margin: 0 auto 15px;
          border-radius: 5px;
          padding: 10px;
          text-align: center;
          color: #b7791f;
          border: 1px solid #fbd38d;
          background: #fffaf0;
          font-size: 15px;
          display: block;
          -webkit-transition: all 0.2s ease-in-out;
          -moz-transition: all 0.2s ease-in-out;
          -o-transition: all 0.2s ease-in-out;
          transition: all 0.2s ease-in-out;


      }

      /* span */
  }
.delete_confirm {
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

button.create{
    background: #7ab358 !important;
    &:hover {
        background: #5a8541 !important;
    }
}
</style>
