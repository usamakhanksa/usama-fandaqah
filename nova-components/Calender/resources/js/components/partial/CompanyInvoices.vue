<template>
  <div>
    <div class="all_invoices_items">
        <div class="invoice_item" v-for="(invoice,i) in invoices.slice(0,3)" :key="i">
            <div class="col_right" style="cursor:pointer;" @click="openAutomatedInvoiceModal(invoice)">
            <span>{{__('Invoice Number')}} : <p>{{invoice.number}}</p></span>
            <span>{{__('Amount')}} : <p>{{ (invoice.data.amount).toFixed(2) }}  {{__(currency)}}</p></span>
            </div><!-- col_right -->
            <div class="col_left">
            <time>{{invoice.created_at | formatDate}}</time>
            <div class="name">
                <p v-if="invoice.invoice_credit_note" style="cursor:pointer;" @click="openCreditNoteModalDirect(invoice)">{{__('Credit Note')}}</p>
                <label style="cursor:pointer;" @click="openAutomatedInvoiceModal(invoice)">{{__('invoice')}}</label>
            </div>
            </div><!-- col_left -->
        </div><!-- invoice_item -->
        <div v-if="invoices.length > 3" @click="openInvoicesListModal" class="more_invoice"></div>
    </div>
    <div class="block_bottom">
            <button @click="showAddGroupInvoiceConfirm"  v-if="!hasInvoicesWithoutCreditNotes && reservation.checked_out != null && occ && reservation.status == 'confirmed'" class="add_invoice">{{ __('Add Invoice') }}</button>
            <button v-if="invoices.length > 3"  @click="openInvoicesListModal" class="more">{{__('more')}} ({{invoices.length}}) ..</button>
    </div><!-- block_bottom -->

    <sweet-modal v-if="targetInvoice" :enable-mobile-fullscreen="false" :pulse-on-block="false"  width="70%" :title="__('Invoice')" overlay-theme="dark" ref="companyInvoiceModal" @close="resetIframe" class="invoice_modal">
      <div class="share_button_reservation">
            <social-sharing
                :url="`${base_url}/home/group-reservation/company-live-invoice?inv=${this.targetInvoice.hash_id}&locale=${this.locale}&type=embed`"
                :description="__('Please have a look on this tax invoice') + ' - ' + team_name"
                network="whatsapp"
            inline-template >
            <network
                network="whatsapp"
            >
                <a href="#" class="whatsapp_button"></a>
            </network>
            </social-sharing>
            <a v-permission="'print invoices'" class="print_button" :href="`/home/group-reservation/company-live-invoice?inv=${this.targetInvoice.hash_id}&locale=${locale}`" target="_blank"></a>
            <a v-if="!targetInvoice.invoice_credit_note && isApplicableToAddCreditNote && (reservation.checked_out === null || occ)" class="add_credit_note_button" @click="openConfirmAddCreditNote"></a>
            <a v-if="targetInvoice.invoice_credit_note" class="show_credit_note_button" @click="openShowCreditNoteModal"></a>
        </div><!-- share_button_reservation -->
        <div class="embed_area">
            <iframe id="callAutomatedInvoiceIframeId" :src="invoiceSrc"></iframe>
        </div><!-- embed_area -->
    </sweet-modal>

    <sweet-modal :enable-mobile-fullscreen="false" :pulse-on-block="true" :hide-close-button="true" overlay-theme="dark" ref="confirmGenerateGroupInvoice" class="delete_confirm_modal">
        <div class="delete_confirm_modal_content">
        <loading :active.sync="isLoading" :is-full-page="false"></loading>
        <h1>{{__('Are you sure to generate group invoice ?')}}</h1>
        <div class="buttons_delete">
            <button id="confirm-delete-button" @click="addGroupInvoice" class="yes_create_button">{{__('Yes, Create')}}</button>
            <button type="button" @click="stepBack()" class="back_delete_button"> {{__('Step Back')}}</button>
        </div>
        </div>
    </sweet-modal>

    <invoice-credit-note-confirm v-if="targetInvoice" :invoice="targetInvoice" ref="addCreditNoteButton" />
    <invoice-credit-note v-if="targetInvoice"  :locale="locale" :invoice="targetInvoice" :reservation="reservation" :credit_note="targetInvoice.invoice_credit_note" ref="creditNote" />
  </div>
</template>


<script>
    import Loading from 'vue-loading-overlay';
    // Import stylesheet
    import 'vue-loading-overlay/dist/vue-loading.css';
    import InvoiceCreditNote from "./InvoiceCreditNote";
    import InvoiceCreditNoteConfirm from "./InvoiceCreditNoteConfirm";
    export default {
        name: "company-invoices",
        components : {InvoiceCreditNote,InvoiceCreditNoteConfirm,Loading},
        props : ['invoices','reservation', 'occ'],
        data(){
            return {
                locale : null,
                invoiceSrc : null,
                base_url : null,
                team_name : Nova.app.currentTeam.name,
                targetInvoice : null,
                holder_invoices : null,
                isApplicableToAddCreditNote : false,
                hasInvoicesWithoutCreditNotes : false,
                isLoading : false,
                currency :Nova.app.currentTeam.currency,


            }
        },
        methods : {
            showAddGroupInvoiceConfirm(){
                this.$refs.confirmGenerateGroupInvoice.open();
            },
            addGroupInvoice(){
                this.isLoading = true;
                axios
                    .post(`/nova-vendor/calender/reservation/${this.reservation.id}/automated-group-invoice`)
                    .then((response) => {
                        Nova.$emit('update');
                        this.$toasted.show(this.__('Automated Invoice has been generated successfully'), {
                            duration : 3000,
                            type: 'success',
                            position : 'top-center'
                        });
                        this.isLoading = false;
                        this.hasInvoicesWithoutCreditNotes = true;
                        this.$refs.confirmGenerateGroupInvoice.close();
                    })
            },
            stepBack(){
                this.$refs.confirmGenerateGroupInvoice.close();
            },
            openInvoicesListModal(){
                Nova.$emit('open-invoice-list-modal-from-for-group-reservation' , {invoices : this.invoices , reservation : this.reservation , occ : this.occ });
            },
            openInvoice(){
                this.$refs.invoice_modal.open();
            },
            openCreditNoteModalDirect(invoice){
                this.targetInvoice = invoice;
                setTimeout(() => {
                    this.$refs.creditNote.$refs.invoiceCreditNoteModal.open();
                    return;
                }, 50);
            },
            openConfirmAddCreditNote(){
                this.$refs.addCreditNoteButton.$refs.confirmCreditNoteModal.open();
            },
            openShowCreditNoteModal(){
                this.$refs.creditNote.$refs.invoiceCreditNoteModal.open();
            },
            openAutomatedInvoiceModal(invoice) {
                if(this.invoices.length){
                    this.holder_invoices = _.filter(this.invoices, function(invoice) {
                        return invoice.invoice_credit_note === null;
                    });
                    if(this.holder_invoices.length){
                        let firstPotentialInvoice = this.holder_invoices[0];
                        if(firstPotentialInvoice.id == invoice.id){
                            this.isApplicableToAddCreditNote = true;
                        }else{
                            this.isApplicableToAddCreditNote = false;
                        }
                    }else{
                        this.isApplicableToAddCreditNote = false;
                    }

                }else{
                    this.isApplicableToAddCreditNote = false;
                }
                this.targetInvoice = invoice;
                this.callIframe();
                this.base_url = window.location.origin;
                setTimeout(() => {
                    this.$refs.companyInvoiceModal.open();
                }, 0);
            },
            callIframe(){
                this.invoiceSrc = `/home/group-reservation/company-live-invoice?inv=${this.targetInvoice.hash_id}&locale=${this.locale}&type=embed`;
                $( '#callLiveInvoiceIframeId' ).attr( 'src', function ( i, val ) { return val; });
            },
            resetIframe(){
                this.invoiceSrc = null;
            }
        },
        mounted() {
            this.locale = Nova.config.local;

             Nova.$on('credit-note-added' , ()=> {
                this.$refs.companyInvoiceModal.close();
                this.hasInvoicesWithoutCreditNotes = false;
            })

            setTimeout(() => {
                if(this.invoices.length){
                    let holder_invoices = _.filter(this.invoices, function(invoice) {
                        return invoice.invoice_credit_note === null;
                    });
                    if(!holder_invoices.length){
                        this.hasInvoicesWithoutCreditNotes = false;
                    }else{
                        this.hasInvoicesWithoutCreditNotes = true;
                    }
                }
            }, 0);
        }
    }
</script>

<style lang="scss">
.invoice_modal {
    .sweet-modal {
        @media (min-width: 768px) and (max-width: 991px) {
            width: 95% !important;
        } /* @media */
    } /* sweet-modal */
    .embed_area {
        max-height: 500px;
        height: 100%;
        overflow-y: auto;
        display: block !important;
        scrollbar-width: thin;
        scrollbar-color: #ccc #f5f5f5;
        &::-webkit-scrollbar {width: 6px;}
        &::-webkit-scrollbar-track {background: #f5f5f5;}
        &::-webkit-scrollbar-thumb {background: #ccc;}
        &::-webkit-scrollbar-thumb:window-inactive {background: #f5f5f5;}
        @media (min-width: 320px) and (max-width: 480px) {
            display: none !important;
        } /* @media */
        iframe {
            width: 100%;
            height: 100%;
            min-height: 500px;
        } /* iframe */
    } /* embed_area */
} /* contract_modal */
</style>
