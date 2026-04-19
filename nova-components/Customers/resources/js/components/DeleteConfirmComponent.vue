<template>
  <sweet-modal :enable-mobile-fullscreen="false"  :pulse-on-block="false" :hide-close-button="true" overlay-theme="dark" ref="delete" class="delete_confirm_modal">
    <div class="delete_confirm_modal_content">
      <loading :active.sync="isLoading" :is-full-page="false" />
      <h1>{{__('are you sure ?')}}</h1>
      <h2>{{__('Deletion cannot be undone, would you like to continue ?')}}</h2>
      <div class="buttons_delete">
        <button v-if="customer_id" id="confirm-delete-button" @click="deleteCustomer" class="yes_delete_button">{{__('Yes, delete !')}}</button>
        <button v-if="type == 'company'" id="confirm-delete-button" @click="deleteCompanyNote" class="yes_delete_button">{{__('Yes, delete !')}}</button>
        <button v-else id="confirm-delete-button" @click="deleteNote" class="yes_delete_button">{{__('Yes, delete !')}}</button>
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
        props : ['customer_id','note_id','type'],
        data(){
            return {
                isLoading : false
            }
        },
        methods : {
            deleteCustomer(){
                this.isLoading = true;
                 axios
                    .delete(`/nova-vendor/new/customers/${this.customer_id}`)
                    .then(response => {
                        this.isLoading = false;
                        if(response.data.success) {
                            this.$refs.delete.close();
                            Nova.$emit('call-customers-query');
                            this.$toasted.show(this.__(response.data.message), {type: 'success'});
                        }
                    })
            },
            deleteNote(){

                this.isLoading = true;
                 axios
                    .delete(`/nova-vendor/new/customers/notes/${this.note_id}`)
                    .then(response => {
                        this.isLoading = false;
                        if(response.data.success) {
                            this.$refs.delete.close();
                            Nova.$emit('call-customer-notes-query');
                            this.$toasted.show(this.__('Note deleted successfully'), {type: 'success'});
                        }
                    })
            },
            deleteCompanyNote(){
                this.isLoading = true;
                 axios
                    .delete(`/nova-vendor/new/customers/companies/notes/${this.note_id}`)
                    .then(response => {
                        this.isLoading = false;
                        if(response.data.success) {
                            this.$refs.delete.close();
                            Nova.$emit('call-companies-notes-query');
                            this.$toasted.show(this.__('Note deleted successfully'), {type: 'success'});
                        }
                    })
            },
            stepBack(){
                this.$refs.delete.close();
            },
        }
    }
</script>
