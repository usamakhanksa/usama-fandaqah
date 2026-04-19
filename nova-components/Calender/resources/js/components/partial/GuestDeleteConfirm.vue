<template>
  <sweet-modal :enable-mobile-fullscreen="false" :pulse-on-block="true" :hide-close-button="true" overlay-theme="dark" ref="deleteGuestModal" class="delete_confirm_modal">
    <div class="delete_confirm_modal_content">
      <loading :active.sync="isLoading" :is-full-page="false"></loading>
      <loading :active.sync="loading" :is-full-page="false"></loading>
      <h1>{{__('are you sure ?')}}</h1>
      <h2>{{__('Deletion cannot be undone, would you like to continue ?')}}</h2>
      <div class="buttons_delete">
        <button id="confirm-delete-button" @click="deleteGuest(id)" class="yes_delete_button">{{__('Yes, delete !')}}</button>
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
        name: "GuestDeleteConfirm",
        components : {Loading},
        props : ['id','reservation_id'],
        data(){
            return {
                isLoading : false
            }
        },
        methods : {
            deleteGuest(id){
                this.isLoading = true;
                axios
                    .delete(`/nova-vendor/calender/reservation/guest/${this.id}/${this.reservation_id}`)
                    .then(response => {
                        Nova.$emit('update-reservation')
                        this.$toasted.show(this.__('Guest deleted successfully'), {type: 'success'});
                        this.$refs.deleteGuestModal.close();
                        this.isLoading = false;
                    }).catch(err => {

                    this.$toasted.show(this.__(err), {type: 'error'})
                })
                .catch((err) => {
                    console.log(err);
                })
            },
            stepBack(){
                this.$refs.deleteGuestModal.close();
            }
        }
    }
</script>
