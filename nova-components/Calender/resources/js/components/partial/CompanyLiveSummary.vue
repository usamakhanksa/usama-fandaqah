<template>
    <div class="item_reservation_button">
        <button class="main_button" @click="openInvoiceModal">{{__('Reservation Summary')}}</button>
        <sweet-modal @close="resetIframe"  :enable-mobile-fullscreen="false" :pulse-on-block="false"  width="70%" :title="__('Reservation Summary')" overlay-theme="dark" ref="liveSummaryModal" class="summary_modal">
        <div class="share_button_reservation">

            <social-sharing
                :url="`${base_url}/home/group-reservation/company-live-summary/sharable?hs=${this.reservation.hash_id}&locale=${this.locale}&type=embed`"
                :description="__('Please have a look on reservation summary with number : ') + main_contract_number + ' - ' + team_name"
                network="whatsapp"
                inline-template
            >
                <network network="whatsapp">
                    <a href="#" class="whatsapp_button"></a>
                </network>
            </social-sharing>
            <a v-permission="'print reservation summary'" class="print_button" :href="`/home/group-reservation/company-live-summary?hs=${this.reservation.hash_id}&locale=${this.locale}`" target="_blank"></a>
        </div><!-- share_button_reservation -->
        <div class="embed_area">
            <iframe id="callLiveSummaryIframeId" :src="summarySrc"></iframe>
        </div><!-- embed_area -->
        </sweet-modal>
    </div>
</template>

<script>
export default {
    name: 'company-live-summary',
    props: ['reservation'],
    data(){
        return {
            locale : null,
            summarySrc : null,
            base_url : null,
            team_name : Nova.app.currentTeam.name,
            main_contract_number : null
        }
    },
    methods: {
        openInvoiceModal() {
            this.main_contract_number = this.reservation.attachable_id ?  this.reservation.number.replace(/\D/g,'') : this.reservation.number;
            this.callIframe();
            this.base_url = window.location.origin;
            this.$refs.liveSummaryModal.open();
        },
        callIframe(){
            this.summarySrc = `/home/group-reservation/company-live-summary?hs=${this.reservation.hash_id}&locale=${this.locale}&type=embed`;
            $( '#callLiveSummaryIframeId' ).attr( 'src', function ( i, val ) { return val; });
        },
        resetIframe(){
            this.summarySrc = null;
        }
    },
    mounted(){
        this.locale = Nova.config.local;
    }
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

