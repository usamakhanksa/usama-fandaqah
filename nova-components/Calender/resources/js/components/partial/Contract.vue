<template>
  <div class="item_reservation_button">
    <button class="main_button" @click="openContractModal">{{__('Contract')}}</button>
    
    <sweet-modal :enable-mobile-fullscreen="false" :pulse-on-block="false"  width="70%" :title="__('Contract')+' #'+reservation.number" overlay-theme="dark" ref="contractModal" class="contract_modal relative">
      <loading
        :active="sendContractSmsLoading"
        :loader="'spinner'"
        :color="'#7e7d7f'"
        :opacity="0.6"
        :is-full-page="false"
      >
    </loading>
    <div class="mt-6 mb-6 space-y-4 sm:space-y-0 sm:flex sm:justify-center sm:gap-4" v-if="reservation && reservation.signed_contracts_count">

      <!-- Signed Contracts Button -->
      <button
            @click="openSignedContractsModal"
            class="flex items-center justify-between gap-2 px-6 py-4 w-full sm:w-52 bg-green-600 hover:bg-green-700 text-white text-base font-semibold rounded-xl shadow-md transition"
        >
            <span>{{ __('Signed Contracts') }} ({{ reservation.signed_contracts_count }})</span>
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
            <path d="M5 13l4 4L19 7"></path>
            </svg>
        </button>
      </div>
      <div class="share_button_reservation">
        <social-sharing :url=" reservation.url_current + '/home/reservation/contract/' + reservation.hash_id + '/public?type=embed&lang=' + locale" inline-template >
          <network network="whatsapp">
            <a href="#" class="whatsapp_button"></a>
          </network>
        </social-sharing>
<!--        <a class="pdf_button" :href="'/home/reservation/pdf/contract/' + reservation.hash_id " target="_blank"></a>-->
        <a v-permission="'print reservation contract'" class="print_button" :href="'/home/reservation/contract/' + reservation.hash_id " target="_blank"></a>
        <button
        @click="sendContractViaSMS"
        :disabled="smsDisabled"
        :title="__('Send Contract Via SMS')"
        v-if="check_sms &&  (reservation.customer && reservation.customer.phone)"
        class="sms_contract_button">{{ __(smsButtonText) }}</button>
      </div><!-- share_button_reservation -->
      <div class="embed_area">
<!--        <embed :src="'/home/reservation/pdf/contract/' + reservation.hash_id " type="application/pdf">-->
        <iframe v-if="contractSrc" id="callIframeId" :src="contractSrc"></iframe>
      </div><!-- embed_area -->
    </sweet-modal>

    <signed-contracts :reservation="reservation" ref="sModalRef" />

  </div>

</template>

<script>
    import SignedContracts from './SignedContracts';
    import SocialSharing from 'vue-social-sharing'
    import Loading from 'vue-loading-overlay';
    import 'vue-loading-overlay/dist/vue-loading.css';
    export default {
        props: ["reservation","check_sms", "check_user_signature"],
        components: {
            Loading,
            SignedContracts
        },
        data() {
            return {
                contractSrc : null,
                isLoading: false,
                locale: 'ar',
                sendContractSmsLoading : false,
                team: null,
                smsDisabled: false,
                smsButtonText: 'SMS',
                countdown: 60,
                countdownTimer: null,
            }
        },
        methods: {
            openSignedContractsModal(){
                this.$refs.sModalRef.$refs.signedContractsModal.open();
            },
            openContractModal() {
                Nova.$emit('refresh-reservation');
                this.contractSrc = '/home/reservation/contract/' + this.reservation.hash_id + '?type=embed'
                $( '#callIframeId' ).attr( 'src', function ( i, val ) { return val; });
                this.$refs.contractModal.open()
            },
            startCountdown() {
              this.smsDisabled = true;
              this.smsButtonText = `${this.countdown}s`;

              this.countdownTimer = setInterval(() => {
                this.countdown--;

                if (this.countdown > 0) {
                  this.smsButtonText = `${this.countdown}s`;
                } else {
                  clearInterval(this.countdownTimer);
                  this.smsDisabled = false;
                  this.smsButtonText = 'SMS';
                  this.countdown = 60; // reset for next use
                }
              }, 1000);
            },
            sendContractViaSMS(){
              if(!this.check_user_signature && this.team.enable_digital_signature && this.team.enable_digital_signature !== 0) {
                this.$toasted.show(this.__('Please add a signature before sending the SMS') + '&nbsp' + `<a href="${window.location.origin}/home/profile" target="_blank">${this.__('Click here to add signature')}</a>`, {type: 'error'})
                return;
              }
              this.startCountdown();
              this.sendContractSmsLoading = true;
              axios
              .post('/nova-vendor/calender/reservation/send-contract-via-sms' , {
                  team_id : this.reservation.team_id,
                  contract_url : this.reservation.url_current + '/home/reservation/contract/' + this.reservation.hash_id + '/public?type=embed&lang=' + this.locale,
                  customer_phone : this.reservation.customer.phone,
                  lang : this.locale,
                  reservation_id : this.reservation.id
              })
              .then(response => {
                this.$toasted.show(this.__('The sending is underway - the message will be received within 1 minute'), {type: 'success'})
                this.sendContractSmsLoading = false
              })
            }
        },
        mounted() {
          this.locale = Nova.config.local;
          Nova.$on("loading", (isLoading) => {
            this.isLoading = isLoading;
          });
          this.team = Nova.app.currentTeam;
        }

    }
</script>

<style lang="scss">

.sms_contract_button{
        display: block !important;
        height: 35px !important;
        width: 50px !important;
        border-radius: 5px !important;
        background-position: center center !important;
        background-size: 30px !important;
        background-repeat: no-repeat !important;
        background-color: #dedede !important;
        margin: 5px !important;
        cursor: pointer !important;
        font-weight: 600 !important;
        &:hover {
            background-color: #bebcbc !important;
        }
  }
 .contract_modal {
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
