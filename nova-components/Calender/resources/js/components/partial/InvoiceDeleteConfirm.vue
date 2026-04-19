<template>
  <sweet-modal :enable-mobile-fullscreen="false" :pulse-on-block="true" :hide-close-button="true" overlay-theme="dark" ref="deleteInvoiceModal" class="delete_confirm_modal">
    <div class="delete_confirm_modal_content">
      <loading :active.sync="isLoading" :is-full-page="false"></loading>
      <loading :active.sync="loading" :is-full-page="false"></loading>
      <h1>{{__('are you sure ?')}}</h1>
      <h2>{{__('Deletion cannot be undone, would you like to continue ?')}}</h2>
      <div class="buttons_delete">
        <button id="confirm-delete-button" @click="deleteInvoice(invoice.id)" class="yes_delete_button">{{__('Yes, delete !')}}</button>
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
        name: "InvoiceDeleteConfirm",
        components : {Loading},
        props : ['invoice','refParent'],
        data(){
            return {
                isLoading : false
            }
        },
        methods : {
            deleteInvoice(id){
                this.isLoading = true;
                axios.post('/nova-vendor/calender/reservation/deleteInvoice/' + id)
                    .then((res) => {

                        this.$refs.deleteInvoiceModal.close();

                        // In case delete from invoice list || delete from invoice popup
                        let refParent = this.refParent;
                        this.$parent.$refs[refParent].close();

                        this.isLoading = false;

                        Nova.$emit('invoice-deleted');

                        this.$toasted.show(this.__('Invoice has been deleted successfully'), {
                            duration : 2000,
                            type: 'success',
                            position: "top-center",
                        });
                        return false;
                    })
                    .catch((err) => {
                        console.log(err);
                    })
            },
            stepBack(){
                this.$refs.deleteInvoiceModal.close();
            }
        }
    }
</script>