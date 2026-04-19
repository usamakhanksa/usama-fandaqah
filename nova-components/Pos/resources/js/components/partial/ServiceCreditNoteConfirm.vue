<template>
    <sweet-modal :enable-mobile-fullscreen="false" :pulse-on-block="true" :hide-close-button="true" overlay-theme="dark" ref="confirmCreditNoteModal" class="delete_confirm_modal">
            <div class="delete_confirm_modal_content">
                <loading :active.sync="isLoading" :is-full-page="false"></loading>
                <h1>{{__('Are you sure to create a credit note for this service transaction ?')}}</h1>
                <div class="buttons_delete">
                    <button id="confirm-delete-button" @click="issueCreditNote" class="yes_create_button">{{__('Yes, Create')}}</button>
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
    name: 'ServiceCreditNoteConfirm',
    components: {
        Loading
    },
    props: ['ref'],
    data: () => {
        return {
            isLoading: false,
            transaction_id: null
        }
    },
    methods: {
        issueCreditNote() {
            this.isLoading = true;
            const params = {
                transaction_id: this.transaction_id
            };
            Nova.request()
                .put("/nova-vendor/pos/service-log/create-credit-note", params)
                .then((response) => {
                    //this.disableUpdateCreditNoteBtn = false;
                    //reload table data
                    Nova.$emit("service-transaction-updated", true);
                    //closes current modal
                    this.$refs.confirmCreditNoteModal.close();
                    this.isLoading = false;
                    this.transaction_id = null;
                })
                .catch((err) => {
                    //this.disableUpdateCreditNoteBtn = false;
                    //reload table data
                    Nova.$emit("service-transaction-updated", true);
                    //closes current modal
                    this.$refs.confirmCreditNoteModal.close();
                    this.isLoading = false;
                    this.transaction_id = null;
                });
        },
    },
    mounted() {
        Nova.$on("open-credit-note-confirm-modal", (transaction) => {
          this.transaction_id = transaction.id;
          this.$refs.confirmCreditNoteModal.open();
        });
    }
}
</script>