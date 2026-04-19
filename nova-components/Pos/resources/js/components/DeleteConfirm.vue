<template>
  <sweet-modal :enable-mobile-fullscreen="false" :pulse-on-block="false" :hide-close-button="true" overlay-theme="dark" ref="deleteConfirm" class="delete_confirm_modal">
    <div class="delete_confirm_modal_content">
      <loading :active.sync="isLoading" :is-full-page="false" />
      <h1>{{__('are you sure ?')}}</h1>
      <h2>{{__('Deletion cannot be undone, would you like to continue ?')}}</h2>
      <div class="alert_info"  v-if="type == 'deposit' && !transaction_deleted">{{__('Note : Deposit transaction attached to this service will be deleted !!')}}</div>
      <div class="buttons_delete">
        <button id="confirm-delete-button" @click="deleteResource(id)" class="yes_delete_button">{{__('Yes, delete !')}}</button>
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
        name: "DeleteConfirm",
        components : {Loading},
        props : ['id' , 'type' , 'transaction_deleted'],
        data(){
            return {
                isLoading : false
            }
        },
        methods : {
          deleteResource(id){
                this.isLoading = true;
                axios.get(`/nova-vendor/pos/deleteTransaction?id=${id}`)
                        .then( response => {
                         
                          this.$toasted.success(Nova.app.__('Resource has been deleted successfully'), {
                              duration: 3000
                            });
                            Nova.$emit(`service-transaction-deleted`);
                            this.$refs.deleteConfirm.close();
                            this.isLoading = false;
                         
                        })
                        .catch( err  => {
                            this.isLoading = false;
                            if (err.response && err.response.data && err.response.data.message) {
                                this.$toasted.show(this.__(err.response.data.message), { type: 'error' });
                            } else {
                                this.$toasted.show(this.__('An error occurred while updating the transaction'), { type: 'error' });
                            }
                        });
            },
            stepBack(){
                this.$refs.deleteConfirm.close();
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
</style>
