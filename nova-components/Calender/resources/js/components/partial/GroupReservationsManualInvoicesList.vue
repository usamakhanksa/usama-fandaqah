<template>
  <div>
    <sweet-modal :enable-mobile-fullscreen="false" :pulse-on-block="true" :title="__('Invoices')" overlay-theme="dark" ref="groupInvoicesListModal" class="invoices_list_modal" @open="openGroupInvoicesListModal">
      <div class="table_invoices">
        <table>
          <thead>
            <tr>
              <th>{{__('Invoice Number')}}</th>
              <th>{{__('Amount')}}</th>
              <th>{{__('From')}}</th>
              <th>{{__('To')}}</th>
              <th width="15%"></th>
            </tr>
          </thead>
          <tbody>
            <template v-if="invoices.length">
              <tr v-for="(invoice,i) in invoices" :key="i">
                <td>{{invoice.number}}</td>
                <td>{{(invoice.data.amount).toFixed(2)}}</td>
                <td>{{invoice.from | formatDateSpecial }}</td>
                <td>{{invoice.to | formatDateSpecial }}</td>
                <td class="actions">
                  <!-- <a v-if="i === 0 && reservation.checked_out === null" @click="deleteInvoice(invoice)" class="appearance-none cursor-pointer text-70 hover:text-primary cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" aria-labelledby="delete" role="presentation" class="fill-current"><path fill-rule="nonzero" d="M6 4V2a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2h5a1 1 0 0 1 0 2h-1v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6H1a1 1 0 1 1 0-2h5zM4 6v12h12V6H4zm8-2V2H8v2h4zM8 8a1 1 0 0 1 1 1v6a1 1 0 0 1-2 0V9a1 1 0 0 1 1-1zm4 0a1 1 0 0 1 1 1v6a1 1 0 0 1-2 0V9a1 1 0 0 1 1-1z"></path></svg>
                  </a> -->

                  <a  class="cursor-pointer text-70 hover:text-primary cursor-pointer" @click="openInvoiceModal(invoice)" ><svg xmlns="http://www.w3.org/2000/svg" width="22" height="18" viewBox="0 0 22 16" aria-labelledby="view" role="presentation"><path d="M16.56 13.66a8 8 0 0 1-11.32 0L.3 8.7a1 1 0 0 1 0-1.42l4.95-4.95a8 8 0 0 1 11.32 0l4.95 4.95a1 1 0 0 1 0 1.42l-4.95 4.95-.01.01zm-9.9-1.42a6 6 0 0 0 8.48 0L19.38 8l-4.24-4.24a6 6 0 0 0-8.48 0L2.4 8l4.25 4.24h.01zM10.9 12a4 4 0 1 1 0-8 4 4 0 0 1 0 8zm0-2a2 2 0 1 0 0-4 2 2 0 0 0 0 4z"></path></svg></a>
                  <a v-if="!invoice.invoice_credit_note && currentInOrderCreditNoteId === invoice.id && (occ || !reservation.checked_out)" class="add_credit_note_button" @click="!can_add_credit_note ? '#' : openConfirmAddCreditNote(invoice)"></a>
                  <a v-if="invoice.invoice_credit_note" class="show_credit_note_button" @click="openShowCreditNoteModal(invoice)"></a>

                </td>
              </tr>
            </template>
          </tbody>
        </table>
      </div><!-- table_invoices -->
    </sweet-modal>

    <group-reservations-single-invoice ref="openDisplayInvoice" 
        :invoice="invoice"  
        :isApplicableToAddCreditNote="isApplicableToAddCreditNote" 
        :reservation="reservation"
        :occ="occ"
        />

    <group-reservations-invoice-credit-note-confirm 
      :invoice="targetInvoice" 
      ref="addCreditNoteButton" 
    />    
    <group-reservations-invoice-credit-note  
      ref="showGroupCreditNote" 
      v-if="injectCreditNoteModal"
      :reservation="reservation" 
      :invoice="targetInvoice" 
      :credit_note="targetInvoice.invoice_credit_note" 
      :locale="local" 
    /> 
  </div>
</template>

<script>
    import GroupReservationsSingleInvoice from './GroupReservationsSingleInvoice.vue';
    import GroupReservationsInvoiceCreditNoteConfirm from "./GroupReservationsInvoiceCreditNoteConfirm";
    import GroupReservationsInvoiceCreditNote from "./GroupReservationsInvoiceCreditNote";
    export default {
        name: "GroupReservationsManualInvoicesList",
        components: {GroupReservationsSingleInvoice,GroupReservationsInvoiceCreditNoteConfirm,GroupReservationsInvoiceCreditNote},
        props: ['invoices','reservation','occ'],
        data(){
            return {
                invoices : [],
                invoice : {},
                local : Nova.config.local,
                currentInOrderCreditNoteId : null,
                holder_invoices : [],
                targetInvoice : null,
                injectCreditNoteModal : false,
                openClosedContract : false, 
                isApplicableToAddCreditNote : false,
                can_add_credit_note : Nova.app.$hasPermission('add credit note')
            }
        },
        methods : {
            openGroupInvoicesListModal(){
                if(this.invoices.length){
                    this.holder_invoices = _.filter(this.invoices, function(invoice) {
                        return invoice.invoice_credit_note === null;
                    });
                    if(this.holder_invoices.length){
                        this.currentInOrderCreditNoteId = this.holder_invoices[0].id;
                    }
                }
                // this.$refs.groupInvoicesListModal.open();
            },
            openInvoiceModal(invoice){
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
                this.invoice = invoice;
                this.$refs.openDisplayInvoice.$refs.groupReservationsSingleInvoiceModal.open();
            },
          
            openConfirmAddCreditNote(invoice){
                this.targetInvoice = invoice;
                this.$refs.addCreditNoteButton.$refs.confirmCreditNoteModal.open();
            },
            openShowCreditNoteModal(invoice){
                this.injectCreditNoteModal = true;
                this.targetInvoice = invoice;
                setTimeout(() => {
                  this.$refs.showGroupCreditNote.$refs.groupInvoiceCreditNoteModal.open();
                }, 50);
            },
        },
        mounted() {
            //    Nova.$on('open-group-invoices-list' , (obj) => {
            //     this.invoices = obj.invoices;
            //     if(this.invoices.length){
            //         this.holder_invoices = _.filter(this.invoices, function(invoice) {
            //             return invoice.invoice_credit_note === null;
            //         });
            //         if(this.holder_invoices.length){
            //             this.currentInOrderCreditNoteId = this.holder_invoices[0].id;
            //         }
            //     }
            //     this.reservation = obj.reservation;
            //     this.$refs.groupInvoicesListModal.open();
            // });

            Nova.$on('group-invoice-credit-note-added' , () => {
              setTimeout(() => {
                if(this.$refs.groupInvoicesListModal){
                  this.$refs.groupInvoicesListModal.close();
                }
              }, 50);
            })

            // Nova.$on('open-invoice-list-modal-from-for-group-reservation' , (obj) => {
            //     this.invoices = obj.invoices;
            //     this.openClosedContract = obj.occ;
            //     this.reservation = obj.reservation;
            //     if(this.invoices.length){
            //         this.holder_invoices = _.filter(this.invoices, function(invoice) {
            //             return invoice.invoice_credit_note === null;
            //         });
            //         if(this.holder_invoices.length){
            //             this.currentInOrderCreditNoteId = this.holder_invoices[0].id;
            //         }
            //     }
            //     this.reservation = obj.reservation;
            //     this.$refs.invoicesListModal.open();
            // })

            // Nova.$on('open-invoices-list' , (obj) => {
            //     this.invoices = obj.invoices;

            //     if(this.invoices.length){
            //         this.holder_invoices = _.filter(this.invoices, function(invoice) {
            //             return invoice.invoice_credit_note === null;
            //         });
            //         if(this.holder_invoices.length){
            //             this.currentInOrderCreditNoteId = this.holder_invoices[0].id;
            //         }
            //     }
            //     this.reservation = obj.reservation;
            //     this.$refs.invoicesListModal.open();
            // })

            // Nova.$on('reservation_checked_out' , (reservation) => {
            //     this.reservation = reservation ;
            //     this.invoices = _.orderBy(this.reservation.invoices, 'number', 'desc');
            // })



        }
    }
</script>

<style lang="scss">

.invoices_list_modal {
  .sweet-content {
    max-height: 500px;
    overflow-y: auto;
    display: block;
    scrollbar-width: thin;
    scrollbar-color: #ccc #f5f5f5;
    &::-webkit-scrollbar {width: 6px;}
    &::-webkit-scrollbar-track {background: #f5f5f5;}
    &::-webkit-scrollbar-thumb {background: #ccc;}
    &::-webkit-scrollbar-thumb:window-inactive {background: #f5f5f5;}
  } /* sweet-content */
  .table_invoices {
    width: 100%;
    overflow: auto;
    margin: 0 auto 10px;
    scrollbar-width: thin;
    scrollbar-color: #ccc #f5f5f5;
    &::-webkit-scrollbar {width: 6px;}
    &::-webkit-scrollbar-track {background: #f5f5f5;}
    &::-webkit-scrollbar-thumb {background: #ccc;}
    &::-webkit-scrollbar-thumb:window-inactive {background: #f5f5f5;}
    table {
      border: 1px solid #e2e8f0;
      width: 100%;
      th {
        padding:  5px;
        line-height: normal;
        font-weight: normal;
        font-size: 15px;
        border: 1px solid #5E697C;
        vertical-align: middle;
        text-align: center;
        color: #ffffff;
        background: #4a5568;
      } /* th */
      tbody {
        tr {
          background: #fff;
          td {
            text-align: center;
            padding: 5px;
            vertical-align: middle;
            line-height: normal;
            font-size: 15px;
            border: 1px solid #ced4dc;
            color: #000000;
            font-weight: normal;
            background: #ffffff;
            &.actions {
                display: flex;
                justify-content: center;
                align-items: center;

                a {
                    display: block;
                    height: 20px;
                    width: 20px;
                    background-position: center center;
                    background-size: contain;
                    background-repeat: no-repeat;
                    margin: 5px;
                    cursor: pointer;

                    &.show_credit_note_button {
                        background-image: url("data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiA/PjxzdmcgZGF0YS1uYW1lPSIxLURvY3VtZW50IiBpZD0iXzEtRG9jdW1lbnQiIHZpZXdCb3g9IjAgMCA0OCA0OCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48dGl0bGUvPjxwYXRoIGQ9Ik00Mi43MSw4LjI5bC04LThBMSwxLDAsMCwwLDM0LDBIOEEzLDMsMCwwLDAsNSwzVjQ1YTMsMywwLDAsMCwzLDNINDBhMywzLDAsMCwwLDMtM1Y5QTEsMSwwLDAsMCw0Mi43MSw4LjI5Wk0zNSwzLjQxLDM5LjU5LDhIMzZhMSwxLDAsMCwxLTEtMVpNNDEsNDVhMSwxLDAsMCwxLTEsMUg4YTEsMSwwLDAsMS0xLTFWM0ExLDEsMCwwLDEsOCwySDMzVjdhMywzLDAsMCwwLDMsM2g1WiIvPjxyZWN0IGhlaWdodD0iMiIgd2lkdGg9IjIwIiB4PSIxNiIgeT0iMTgiLz48cmVjdCBoZWlnaHQ9IjIiIHdpZHRoPSIyNCIgeD0iMTIiIHk9IjI0Ii8+PHJlY3QgaGVpZ2h0PSIyIiB3aWR0aD0iMjQiIHg9IjEyIiB5PSIzMCIvPjxyZWN0IGhlaWdodD0iMiIgd2lkdGg9IjE2IiB4PSIxMiIgeT0iMzYiLz48cmVjdCBoZWlnaHQ9IjIiIHdpZHRoPSIyIiB4PSIzNCIgeT0iMzYiLz48cmVjdCBoZWlnaHQ9IjIiIHdpZHRoPSIyIiB4PSIzMCIgeT0iMzYiLz48L3N2Zz4=");

                    }
                    &.add_credit_note_button {
                        background-image: url("data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiA/Pjxzdmcgdmlld0JveD0iMCAwIDM2MCA0MTAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGRlZnM+PHN0eWxlPi5jbHMtMXtmaWxsOiNjNWM2ZWY7fS5jbHMtMntmaWxsOiMzODU4Njg7fS5jbHMtM3tmaWxsOiM0MjY1NzI7fS5jbHMtNHtmaWxsOiM2YWUxODQ7fS5jbHMtNXtmaWxsOiNhOGZmOGM7fS5jbHMtNntmaWxsOiMzODlkOWM7fTwvc3R5bGU+PC9kZWZzPjx0aXRsZS8+PGcgZGF0YS1uYW1lPSJMYXllciAyIiBpZD0iTGF5ZXJfMiI+PGcgZGF0YS1uYW1lPSJMYXllciAxIiBpZD0iTGF5ZXJfMS0yIj48cGF0aCBjbGFzcz0iY2xzLTEiIGQ9Ik0yODcsMzk5bDktNmExMywxMywwLDAsMS0xMywxM0gxOUExMywxMywwLDAsMSw2LDM5M1YyMUExMywxMywwLDAsMSwxOSw4SDEzTDM1LjUsMzYzLjVhMTMsMTMsMCwwLDAsMTMsMTNaIi8+PHJlY3QgY2xhc3M9ImNscy0yIiBoZWlnaHQ9IjE0LjAzIiB3aWR0aD0iODQuNzIiIHg9IjY2LjI4IiB5PSI3Mi40MiIvPjxyZWN0IGNsYXNzPSJjbHMtMiIgaGVpZ2h0PSIxNC4wMyIgd2lkdGg9Ijg0LjczIiB4PSI2Ni4yOCIgeT0iMTQ2Ljg4Ii8+PHJlY3QgY2xhc3M9ImNscy0yIiBoZWlnaHQ9IjE0LjAzIiB3aWR0aD0iMTM2LjA3IiB4PSI2Ni4yOCIgeT0iMTA4LjM3Ii8+PHJlY3QgY2xhc3M9ImNscy0yIiBoZWlnaHQ9IjE0LjAzIiB3aWR0aD0iODQuNzIiIHg9IjY2LjI4IiB5PSIyMzcuNDIiLz48cmVjdCBjbGFzcz0iY2xzLTIiIGhlaWdodD0iMTQuMDMiIHdpZHRoPSI4NC43MyIgeD0iNjYuMjgiIHk9IjMxMS44OCIvPjxyZWN0IGNsYXNzPSJjbHMtMiIgaGVpZ2h0PSIxNC4wMyIgd2lkdGg9IjEzNi4wNyIgeD0iNjYuMjgiIHk9IjI3My4zNyIvPjxwYXRoIGNsYXNzPSJjbHMtMyIgZD0iTTI4Myw0MTBIMTlBMTksMTksMCwwLDEsMCwzOTFWMTlBMTksMTksMCwwLDEsMTksMEgyODNhMTksMTksMCwwLDEsMTksMTlWMzkxQTE5LDE5LDAsMCwxLDI4Myw0MTBaTTE5LDEyYTcsNywwLDAsMC03LDdWMzkxYTcsNywwLDAsMCw3LDdIMjgzYTcsNywwLDAsMCw3LTdWMTlhNyw3LDAsMCwwLTctN1oiLz48Y2lyY2xlIGNsYXNzPSJjbHMtNCIgY3g9IjI5NS41IiBjeT0iMjI5LjUiIHI9IjU4LjUiLz48cGF0aCBjbGFzcz0iY2xzLTUiIGQ9Ik0zNDcsMjI2LjVhNTguNTIsNTguNTIsMCwwLDEtNjUsNTguMTQsNTguNSw1OC41LDAsMCwwLDAtMTE2LjI4LDU4LjUyLDU4LjUyLDAsMCwxLDY1LDU4LjE0WiIvPjxwYXRoIGNsYXNzPSJjbHMtNiIgZD0iTTMwNiwyODQuNTJhNTguNSw1OC41LDAsMSwxLDAtMTE2LDU4LjUsNTguNSwwLDAsMCwwLDExNloiLz48cGF0aCBjbGFzcz0iY2xzLTMiIGQ9Ik0yOTUuNSwyOTFBNjQuNSw2NC41LDAsMSwxLDM2MCwyMjYuNSw2NC41Nyw2NC41NywwLDAsMSwyOTUuNSwyOTFabTAtMTE3QTUyLjUsNTIuNSwwLDEsMCwzNDgsMjI2LjUsNTIuNTYsNTIuNTYsMCwwLDAsMjk1LjUsMTc0WiIvPjxwYXRoIGNsYXNzPSJjbHMtMyIgZD0iTTI4NC42MywyMTF2MzcuNzVjMCw5LjY1LDE1LDkuNjcsMTUsMFYyMTFjMC05LjY1LTE1LTkuNjctMTUsMFoiLz48cGF0aCBjbGFzcz0iY2xzLTMiIGQ9Ik0zMTEsMjIyLjM4SDI3My4yNWMtOS42NSwwLTkuNjcsMTUsMCwxNUgzMTFjOS42NSwwLDkuNjctMTUsMC0xNVoiLz48L2c+PC9nPjwvc3ZnPg==");

                    }
                } /* a */
            }

          } /* td */
        } /* tr */
      } /* tbody */
    } /* table */
  } /* table_invoices */
} /* invoices_list_modal */
</style>
