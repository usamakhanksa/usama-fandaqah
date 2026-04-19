<template>
   <sweet-modal :enable-mobile-fullscreen="false" :pulse-on-block="true" :hide-close-button="true" overlay-theme="dark" ref="confirmCreditNoteModal" class="delete_confirm_modal">
        <div class="delete_confirm_modal_content">
        <loading :active.sync="isLoading" :is-full-page="false"></loading>
        <h1>{{__('Are you sure to create a credit note for this invoice ?')}}</h1>
        <div class="buttons_delete">
            <button id="confirm-delete-button" @click="createCreditNote" class="yes_create_button">{{__('Yes, Create')}}</button>
            <button type="button" @click="stepBack()" class="back_delete_button"> {{__('Step Back')}}</button>
        </div>
        </div>
    </sweet-modal>
</template>

<script>
    import Loading from 'vue-loading-overlay';
    // Import stylesheet
    import 'vue-loading-overlay/dist/vue-loading.css';
    export default {
        name: "group-reservations-invoice-credit-note-confirm",
        components : {Loading},
        props : ['invoice'],
        data(){
            return {
                isLoading : false
            }
        },
        methods : {
            createCreditNote(){
                this.isLoading = true;
                axios.post(`/nova-vendor/calender/reservation/add-credit-note/${this.invoice.id}`)
                .then((response) => {
                     if(response.data.success){
                         this.isLoading = false;
                          this.$toasted.show(this.__('Credit note has been created successfully'), {
                            duration : 5000,
                            type: 'success',
                            position: "top-center",
                        });
                        this.$refs.confirmCreditNoteModal.close();
                        Nova.$emit('group-invoice-credit-note-added');
                     }
                })
            },
            stepBack(){
                this.$refs.confirmCreditNoteModal.close();
            }
        }
    }
</script>
