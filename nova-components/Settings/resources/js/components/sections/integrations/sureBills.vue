<template>
  <div class="integration_col">
    <div class="integration_item">
      <div class="mt-5 mb-5 loader_item" v-if="loading">
        <loader class="text-60" width="40"/>
      </div><!-- loader_item -->
      <div class="status_label">
        <label v-if="data.integration" class="connected">{{__('Connected')}}</label>
        <label v-if="!data.integration" class="notconnected">{{__('Not Connected')}}</label>
      </div><!-- status_label -->
      <div v-if="!loading">
        <div class="imgthumb cursor-pointer sure_bills" @click="openLog()">
          <img src="/images/sure_bills.png" :alt="data.name['en']"/>
        </div><!-- imgthumb -->
        <div class="name">
          <a :href="data.url" target="_blank"> شور للفواتير</a>
          <span><button type="button" v-tooltip.top-center="__('How to integrate with Sure Bills')" @click="openbillsinfo()"></button></span>
        </div><!-- name -->
        <div class="desc">الربط مع <a href="https://bills.surepay.sa/" target="_blanck" title=" شور للفواتير "> شور للفواتير </a></div>
        <div class="date_integration">
          <button v-if="!data.integration" @click="open()" class="connect"> {{__('Connect')}}</button>
          <button v-if="data.integration" @click="openDisconnect()" class="disconnect"> {{__('Disconnect !')}}</button>
        </div><!-- date_integration -->
      </div><!-- loading -->


      <sweet-modal ref="sure_billsModal" :enable-mobile-fullscreen="false" :pulse-on-block="false" class="SureBills_Modal" blocking overlay-theme="dark" :title="__('Sure Bills Integration') ">
        <div class="loader_item" v-if="formLoading">
          <loader width="40"/>
          <span>{{__('Connecting')}}</span>
        </div><!-- loader_item -->
        <div v-if="!loading && !formLoading">
          <div class="alert_sure_bills">قم بتعبئة البيانات التي تم تزويدكم بها من قبل  <a href="https://bills.surepay.sa/" target="_blanck" title="شور للفواتير">شور للفواتير</a></div>
          <div class="input_group">
            <label for="Client Id">{{__('Client Id')}}</label>
            <input type="text" v-model="data.fields.client_id" :placeHolder="__('Client Id')">
          </div><!-- input_group -->
          <div class="input_group">
            <label for="Secret">{{__('Secret')}}</label>
            <input type="text" v-model="data.fields.secret" :placeHolder="__('Secret')">
          </div><!-- input_group -->
          <div class="input_group">
            <label for="webhook Url">{{__('webhook Url')}}</label>
            <input type="text" v-model="data.fields.webhook_url" :placeHolder="__('webhook Url')">
          </div><!-- input_group -->          
          <div class="input_group">
            <label for="Webhook Secret">{{__('Webhook Secret')}}</label>
            <input type="text" v-model="data.fields.webhook_secret" :placeHolder="__('Webhook Secret')">
          </div><!-- input_group -->
          <button @click="send">{{__('Connect')}}</button>
        </div>
      </sweet-modal>

      <sweet-modal ref="sure_billsDisconnectModal" icon="warning" overlay-theme="dark" modal-theme="dark" title="Sure Bills Integration">
        <div class="mt-5 mb-5 text-center" v-if="formLoading">
          <loader class="text-60 text-warning" width="40"/>
          <div class="mb-2 text-2xl block font-bold no-underline  text-warning">Disconnecting</div>
        </div>
        <div class="text-center" v-if="!formLoading">
          <a class="mb-2 text-2xl block font-bold no-underline text-warning"> This action will disconnect your account from {{data.name['en']}} , please be careful ! </a>
          <button class=" btn btn-block btn btn-default btn-danger mt-2" @click="disconnect()">DISCONNECT</button>
        </div>
      </sweet-modal>

      
      <!-- sure bills Info -->
      <sweet-modal ref="billsInfoModal" :enable-mobile-fullscreen="false" :pulse-on-block="false" class="Bills_Info_Modal" blocking overlay-theme="dark"  :title="__('How to integrate with Sure Bills')">
        
      </sweet-modal>
      <!-- sure bills Info -->

    </div><!-- integration_item -->
  </div><!-- integration_col -->
</template>

<script>
    export default {
        name: "sure_bills",
        data() {
            return {
                data: null,
                key: 'SureBills',
                loading: true,
                local: Nova.config.local,
                formLoading: false,
            }
        },
        methods: {
            open() {
                this.$refs.sure_billsModal.open()
            },
            openDisconnect() {
                this.$refs.sure_billsDisconnectModal.open()
            },
            openbillsinfo() {
                this.$refs.billsInfoModal.open()
            },
            disconnect() {
                this.formLoading = true
                Nova.request()
                    .post('/nova-vendor/settings/disconnect', {
                        key: 'SureBills',
                    }).then(response => {

                    if (response.data.success) {
                        this.data = response.data.settings
                        this.data.fields = JSON.parse(this.data.fields)
                        this.$refs.sure_billsDisconnectModal.close()
                        this.loading = false;
                        this.formLoading = false;
                        this.$toasted.show('Fandaqah Disconnected Successfully', {type: 'success'})

                    } else {
                        this.loading = false;
                        this.formLoading = false;
                        this.$toasted.show('Fail to disconnect !', {type: 'error'})

                    }

                    console.log("response.data", response.data)


                })
                    .catch(error => {
                        this.$toasted.error(error, {
                            duration: 3000
                        });
                        this.loading = false;
                        this.formLoading = false;
                    });
            },
            send() {

                if (!this.data.fields.client_id || !this.data.fields.secret || !this.data.fields.webhook_url || !this.data.fields.webhook_secret) {

                    this.$toasted.error('Please fill all credentials ', {
                        duration: 3000
                    });
                    return;
                }

                this.loading = true;
                this.formLoading = true;
                Nova.request()
                    .post('/nova-vendor/settings/register', {
                        key: 'SureBills',
                        values: this.data.fields
                    }).then(response => {

                    if (response.data.success) {
                        this.data = response.data.settings
                        this.data.fields = JSON.parse(this.data.fields)
                        this.$refs.sure_billsModal.close()
                        this.loading = false;
                        this.formLoading = false;
                        this.$toasted.show('Fandaqah Integrated Successfully', {type: 'success'})

                    } else {
                        this.loading = false;
                        this.formLoading = false;
                        this.$toasted.show('Fail to connect !', {type: 'error'})

                    }

                    console.log("response.data", response.data)


                })
                    .catch(error => {
                        this.$toasted.error(error, {
                            duration: 3000
                        });
                        this.loading = false;
                        this.formLoading = false;
                    });


            }
        },

        mounted() {
            Nova.request()
                .get('/nova-vendor/settings/integrations/SureBills').then(response => {

                this.data = response.data
                this.data.fields = JSON.parse(this.data.fields)
                console.log({'hhh': this.data})
                this.loading = false;


            })
                .catch(error => {
                    this.$toasted.error(error, {
                        duration: 3000
                    });
                    this.loading = false;
                });
        }
    }
</script>

<style lang="scss">
.SureBills_Modal {
  .sweet-content {
    overflow: auto;
    max-height: 500px;
    display: block !important;
    position: relative;
    scrollbar-width: thin;
    scrollbar-color: #ccc #f5f5f5;
    &::-webkit-scrollbar {width: 6px;}
    &::-webkit-scrollbar-track {background: #f5f5f5;}
    &::-webkit-scrollbar-thumb {background: #ccc;}
    &::-webkit-scrollbar-thumb:window-inactive {background: #f5f5f5;}
  } /* sweet-content */
  .loader_item {
    position: absolute;
    top: 0;
    bottom: 0;
    left: 0;
    right: 0;
    background: rgba(255, 255, 255, 0.8);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-wrap: wrap;
    svg {
      display: block;
      margin: 0 auto;
      width: 60px !important;
      height: auto;
      align-self: flex-end;
    } /* svg */
    span {
      display: table;
      margin: 20px auto 0;
      font-size: 20px;
      width: 100%;
      align-self: baseline;
      text-align: center;
      color: #000;
    } /* span */
  } /* loader_item */
  .alert_sure_bills {
    background: #fff3cd;
    border: 1px solid #ffeeba;
    color: #856404;
    text-align: center;
    padding: 15px;
    border-radius: 4px;
    font-size: 15px;
    margin: 0 auto 15px;
  } /* alert_sure_bills */
  .input_group {
    margin: 0 auto 10px;
    label {
      display: block;
      font-size: 15px;
      margin: 0 auto 5px;
      color: #000;
    } /* label */
    input {
      height: 40px;
      padding: 0 10px;
      color: #000;
      font-size: 15px;
      border: 1px solid #dddddd !important;
      background: #fafafa;
      width: 100%;
    } /* input */
  } /* input_group */
  button {
    height: 35px;
    width: 100%;
    color: #ffffff;
    background: #4099de;
    border: 1px solid #4099de;
    border-radius: 5px;
    padding: 0;
    margin: 15px auto;
    cursor: pointer;
    -webkit-transition: all 0.2s ease-in-out;
    transition: all 0.2s ease-in-out;
    &:hover {background: #4099de;}
  } /* button */
} /* SureBills_Modal */

.Bills_Info_Modal {
  .sweet-content {
    overflow: auto;
    max-height: 500px;
    display: block !important;
    position: relative;
    scrollbar-width: thin;
    scrollbar-color: #ccc #f5f5f5;
    &::-webkit-scrollbar {width: 6px;}
    &::-webkit-scrollbar-track {background: #f5f5f5;}
    &::-webkit-scrollbar-thumb {background: #ccc;}
    &::-webkit-scrollbar-thumb:window-inactive {background: #f5f5f5;}
  } /* sweet-content */
} /* Bills_Info_Modal */
</style>
