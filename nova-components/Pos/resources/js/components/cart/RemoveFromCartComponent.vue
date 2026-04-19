<template>
  <sweet-modal :enable-mobile-fullscreen="false" :pulse-on-block="false" :hide-close-button="true" overlay-theme="dark" ref="removeConfirm"  class="delete_confirm_modal">
    <div class="delete_confirm_modal_content">
      <loading :active.sync="isLoading" :is-full-page="false" />
      <h1>{{__('are you sure ?')}}</h1>
      <h2>{{__('Deletion cannot be undone, would you like to continue ?')}}</h2>
      <div class="buttons_delete">
        <button id="confirm-delete-button" @click="removeItem()" class="yes_delete_button">{{__('Yes, delete !')}}</button>
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
        name: "RemoveFromCartComponent",
        components : {Loading},
        data(){
            return {
                isLoading : false,
                uuid : null
            }
        },
        methods : {
            removeItem(){
                this.isLoading = true ;
                Nova.$emit('remove-item-from-cart-confirmed' , this.uuid)

                let self = this;
                setTimeout(function(){
                    self.isLoading = false;
                    self.$refs.removeConfirm.close();
                }, 500);
            },
            stepBack(){
                this.$refs.removeConfirm.close();
            }
        },
        mounted(){
            Nova.$on('remove-item-from-cart-confirm' , (uuid) => {
                this.uuid = uuid ;
                this.$refs.removeConfirm.open();
            })
        }
    }
</script>