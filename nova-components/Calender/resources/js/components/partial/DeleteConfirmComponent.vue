<template>
  <sweet-modal :enable-mobile-fullscreen="false"  :pulse-on-block="false" :hide-close-button="true" overlay-theme="dark" ref="deleteNote" class="delete_confirm_modal">
    <div class="delete_confirm_modal_content">
      <loading :active.sync="isLoading" :is-full-page="false" />
      <h1>{{__('are you sure ?')}}</h1>
      <h2>{{__('Deletion cannot be undone, would you like to continue ?')}}</h2>
      <div class="buttons_delete">
        <button id="confirm-delete-button" @click="deleteNote" class="yes_delete_button">{{__('Yes, delete !')}}</button>
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
        name: "DeleteConfirmComponent",
        components : {Loading},
        props : ['id'],
        data(){
            return {
                isLoading : false
            }
        },
        methods : {
          deleteNote(){
                this.isLoading = true;
                 axios
                    .delete(`/nova-vendor/calender/comments/${this.id}`)
                    .then(res => {
                        this.isLoading = false;
                        if(res.data.success) {
                            this.$refs.deleteNote.close();
                            Nova.$emit('close-edit-modal');
                            Nova.$emit('note-on-reservation-deleted');
                            this.$toasted.show(this.__('Note on reservation deleted successfully'), {type: 'success'});
                        }
                    })
            },
            stepBack(){
                this.$refs.deleteNote.close();
            }
        }
    }
</script>
