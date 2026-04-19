<template>
  <div class="item_reservation_button">
    <button class="main_button" @click="openInvoiceModal">{{__('Reservation Summary')}}</button>
    <sweet-modal :enable-mobile-fullscreen="false" :pulse-on-block="false"  width="70%" :title="__('Reservation Summary')+' #'+reservation.number" overlay-theme="dark" ref="reservationSummaryModal" class="summary_modal">
      <div class="share_button_reservation">
        <a class="pdf_button" :href="'/home/reservation/pdf/reservation-summary/' + reservation.hash_id " target="_blank"></a>
        <a v-permission="'print reservation summary'" class="print_button" :href="'/home/print/reservationSummary/' + reservation.hash_id " target="_blank"></a>
        <form id="reservation_summary_form" target="_blank" method="post"  style="display: none" action="/home/print/reservationSummary">
          <input type="hidden" :value="reservation.id" name="reservation_id">
        </form>
      </div><!-- share_button_reservation -->
      <div class="embed_area">
        <iframe id="reservationSummary" v-if="summarySrc"  :src="summarySrc"></iframe>
      </div><!-- embed_area -->
    </sweet-modal>
  </div>
</template>

<script>
    export default {
        name : 'reservation-summary',
        props: ["reservation"],
        components: {

        },
        data() {
            return {
                summarySrc : null
            }
        },
        methods: {
            openInvoiceModal() {
                // console.log(this.reservation)
                 this.summarySrc = '/home/print/reservationSummary/' + this.reservation.hash_id + '?type=embed'

                 $( '#reservationSummary' ).attr( 'src', function ( i, val ) { return val; });
                this.$refs.reservationSummaryModal.open()
            },
        },

    }
</script>

<style lang="scss" scoped>
.summary_modal {
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
