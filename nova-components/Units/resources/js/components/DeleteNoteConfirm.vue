<template>
  <sweet-modal :enable-mobile-fullscreen="false"  :pulse-on-block="false" :hide-close-button="true" overlay-theme="dark" ref="deleteNoteConfirm" class="delete_confirm_modal">
    <div class="delete_confirm_modal_content">
      <loading :active.sync="isLoading" :is-full-page="false" />
      <h1>{{__('are you sure ?')}}</h1>
      <h2>{{__('Deletion cannot be undone, would you like to continue ?')}}</h2>
      <div class="buttons_delete">
        <button id="confirm-delete-button" @click="deleteNote" class="yes_delete_button">{{__('Yes, delete !')}}</button>
        <button type="button" @click="stepBack" class="back_delete_button"> {{__('Do not retreat !')}}</button>
      </div>
    </div>
  </sweet-modal>
</template>

<script>
    import Loading from 'vue-loading-overlay';
    // Import stylesheet
    import 'vue-loading-overlay/dist/vue-loading.css';
    export default {
        name: "DeleteNoteConfirm",
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
                axios.delete(`/apidata/notes/${this.id}`)
                .then(response => {
                    this.$toasted.success(this.__('Note has been deleted successfully'), {
                            duration: 3000
                    })
                    Nova.$emit('note-deleted');
                    this.$refs.deleteNoteConfirm.close();
                    this.isLoading = false;
                })
            },
            stepBack(){
                this.$refs.deleteNoteConfirm.close();
            }
        }
    }
</script>
