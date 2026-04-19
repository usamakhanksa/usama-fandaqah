<template>
    <div class="item_reservation_button">
        <button class="main_button" @click="openInvoiceModal">{{__('Invoice Prototype')}}</button>
        <sweet-modal @close="resetIframe"  :enable-mobile-fullscreen="false" :pulse-on-block="false"  width="70%" :title="__('Invoice')" overlay-theme="dark" ref="liveInvoiceModal" class="company_invoice_modal">
        <div class="share_button_reservation">

            <social-sharing
                :url="`${base_url}/home/group-reservation/company-live-invoice-prototype?hs=${this.reservation.hash_id}&locale=${this.locale}&type=embed`"
                :description="__('Please have a look on this tax invoice') + ' - ' + team_name"
                network="whatsapp"
            inline-template >
            <network
                network="whatsapp"
            >
                <a href="#" class="whatsapp_button"></a>
            </network>
            </social-sharing>
            <a v-permission="'print invoices'" class="print_button"  :href="`/home/group-reservation/company-live-invoice-prototype?hs=${this.reservation.hash_id}&locale=${this.locale}`" target="_blank"></a>
        </div><!-- share_button_reservation -->
        <div class="embed_area">
            <iframe id="callLiveInvoiceIframeId" :src="invoiceSrc"></iframe>
        </div><!-- embed_area -->
        </sweet-modal>
    </div>
</template>

<script>
export default {
    name: 'company-live-invoice',
    props: ['reservation'],
    data(){
        return {
            locale : null,
            invoiceSrc : null,
            base_url : null,
            team_name : Nova.app.currentTeam.name
        }
    },
    methods: {
        openInvoiceModal() {
            this.callIframe();
            this.base_url = window.location.origin;
            this.$refs.liveInvoiceModal.open();
        },
        callIframe(){
            this.invoiceSrc = `/home/group-reservation/company-live-invoice-prototype?hs=${this.reservation.hash_id}&locale=${this.locale}&type=embed`;
            $( '#callLiveInvoiceIframeId' ).attr( 'src', function ( i, val ) { return val; });
        },
        resetIframe(){
            this.invoiceSrc = null;
        }
    },
    mounted(){
        this.locale = Nova.config.local;
    }
}
</script>

<style lang="scss">
  .company_invoice_modal {
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
  } /* company_invoice_modal */

</style>
