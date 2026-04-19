<template>
  <div v-if="data.integration !== undefined" class="integration_col">
    <div class="integration_item">
      <div class="status_label">
        <label v-if="data.integration" class="connected">{{__('Connected')}}</label>
        <label v-if="!data.integration" class="notconnected">{{__('Not Connected')}}</label>
      </div>
      <!-- <jawaly-log ref="jawalyLog" v-if="data.integration"></jawaly-log> -->
      <div>
        <div class="imgthumb cursor-pointer" @click="openLog()">
          <!-- <img :src="data.logo" :alt="data.name['en']"/> -->
            <title>logo-ar</title>
            <img src="/images/zatca.png" alt="zatca-einvoicing"/>          
        </div><!-- imgthumb -->
        <div class="name">
          <p>{{ __('Zatca Phase Two') }}</p>
          <span><button type="button" v-tooltip.top-center="__('How to integrate with zatca phase 2')" @click="openZatcainfo()"></button></span>
        </div><!-- name -->

        <div v-if="!data.integration" class="desc">{{ __('integrate with')}} 
          <a :href="'https://zatca.gov.sa/en/E-Invoicing/Introduction/Pages/Roll-out-phases.aspx'" target="_blanck" :title="__('Zatca Phase Two')"> {{ __('Zatca Phase Two') }} </a>
        </div>

        <!-- <div class="desc" v-else> {{__('your Balnace')}} : {{ balance}}</div> -->
        <div class="date_integration">
          <button v-if="!data.integration" @click="open()" class="connect"> {{__('Connect')}}</button>
          <!-- <button v-if="data.integration" @click="openDisconnect()" class="disconnect"> {{__('Disconnect !')}}</button> -->
        </div><!-- date_integration -->
      </div><!-- loading -->

      <sweet-modal ref="zatcaPhase2Modal" :enable-mobile-fullscreen="false" :pulse-on-block="false" class="Jawaly_Modal" blocking overlay-theme="dark" :title="__('Zatca Phase Two Integration')">
        <div class="relative">
               <loading :active.sync="loading"
               :can-cancel="true"
               :is-full-page="false"></loading>
          <div class="alert_jawaly">
              {{ __('Note: Ensure that the invoice is correct as per phase 2 compliance requirements you cannot edit or delete the invoice once it has been marked as Sent') }}
          </div>
          <div class="input_group">
            <label for="OTP">{{ __('Enter OTP and Generate CSID') }}</label>
            <input type="number" maxlength="6" v-model="data.fields.otp" :placeHolder="__('OTP')">
          </div>
          <button @click="send">{{__('Generate CSID')}}</button>
        </div>
      </sweet-modal>
      <sweet-modal ref="zatcaPhaseTwoDisconnectModal" icon="warning" overlay-theme="dark" modal-theme="dark"
                    :title="__('Zatca Phase Two Integration')">
          <div class="mt-5 mb-5 text-center" v-if="formLoading">
              <loader class="text-60 text-warning" width="40"/>
              <div class="mb-2 text-2xl block font-bold no-underline  text-warning">
                  {{ __('Disconnecting')}}
              </div>
          </div>
          <div class="text-center" v-if="!formLoading">
              <a class="mb-2 text-2xl block font-bold no-underline text-warning">{{ __('This action will disconnect your account from')}} {{__(data.name['en'])}} , {{ __('please be careful !')}} </a>
              <button class=" btn btn-block btn btn-default btn-danger mt-2" @click="disconnect()">
                {{ __('DISCONNECT')}}
              </button>
          </div>
      </sweet-modal>
      <sweet-modal ref="zatcaInfoModal" :enable-mobile-fullscreen="false" :pulse-on-block="false" class="Fonic_Info_Modal" blocking overlay-theme="dark"  :title="__('Zatca E-Invoicing Service')">
         <span>
           {{ __('Generate OTP in the Fatoora Portal')}}
           <ul>
             <li>{{ __('Set your tax number under general settings')}}</li>
             <li class="list-none"><img :src="`/images/integration_zatca_phase_2/${local}/set_tax_number_final.png`" alt="tax number" /></li>
             <li>{{ __('Login to the Fatoora portal')}}</li>
             <li class="list-none"><img :src="`/images/integration_zatca_phase_2/${local}/login-zatca-final.png`" alt="login fatoora" /></li>
             <li>{{ __('Click Onboard New Solution Unit/Device')}}</li>
             <li class="list-none"><img :src="`/images/integration_zatca_phase_2/${local}/onboard_device_final.png`" alt="fatoora onboard devices"/></li>
             <li>{{ __('Enter the number of devices for which you want to generate OTP. If you’re using only Fandaqah PMS, enter 1')}}</li>
             <li class="list-none"><img :src="`/images/integration_zatca_phase_2/${local}/generate_otp_final.png`" alt="fatoora board device"/></li>
             <li>{{ __('Your OTP will be generated in the portal and you can copy or export it as a file')}}</li>
             <li class="list-none"><img :src="`/images/integration_zatca_phase_2/${local}/copy_code_final.png`" alt="fatoora otp codes"/></li>
             <li>{{ __('Using this OTP, you can Generate CSID in Fandaqah PMS')}}</li>
             <li class="list-none"><img :src="`/images/integration_zatca_phase_2/${local}/fill_otp_final.png`" alt="fatoora otp codes"/></li>
             <li>
              <div class="alert_jawaly">
                {{ __('The OTP generated by the Fatoora portal will be valid only for one hour. You’ll have to enter the OTP in Fandaqah PMS within one hour') }}
              </div>
             </li>
           </ul>
         </span>
      </sweet-modal>
     

    </div><!-- integration_item -->
  </div><!-- integration_col -->
</template>

<script>
    import Loading from 'vue-loading-overlay';
    // Import stylesheet
    import 'vue-loading-overlay/dist/vue-loading.css';
    import Pagination from './log/Pagination';
    export default {
        name: "zatca-phase-two",
        components: {
            Loading,
            Pagination
        },
        data() {
            return {
                data: null,
                key: 'ZatcaPhaseTwo',
                loading: true,
                local: Nova.config.local,
                team: Nova.app.currentTeam,
                formLoading: false,
                paginator : {},
                logs_data: []
            }
        },
        methods: {
             jobStatusClass(status) {
                if (status === 'pending') return 'secondary';
                if (status === 'processed') return 'success';
                if (status === 'failed') return 'danger';
            },
            getLogs(page = 1){
              this.loading = true;
              Nova.request()
                  .get('/nova-vendor/settings/jawaly-log?page=' + page)
                  .then(response => {
                      this.logs_data = response.data.data;
                      this.paginator = {
                      currentPage: response.data.meta.current_page,
                      lastPage: response.data.meta.last_page,
                      from: response.data.meta.from,
                      to: response.data.meta.to,
                      totalResults: response.data.meta.total,
                      pathPage: response.data.meta.path + "?page=",
                      firstPageUrl: response.data.links.first,
                      lastPageUrl: response.data.links.last,
                      nextPageUrl: response.data.links.next,
                      prevPageUrl: response.data.links.prev,
                    };
                    this.loading = true;
                    this.$refs.jawalyLogsModal.open();
                  })
                  .catch(error => {
                      this.$toasted.error(error, {
                          duration: 3000
                      });
                      this.loading = false;
                  });
            },
            openLog() {
              this.getLogs();
            },
            open() {
              if(!this.team.check_integration_zatca_phase_two_enable) {
                    this.$toasted.show(this.__('Please contact the administration to connect with Zatca Phase 2'), {
                        type: 'error',
                        duration : 5000,
                        action: {
                            text: this.__('Goto Techincal Support'),
                            push: {
                                name: 'techincal-support',
                                dontClose: true
                            }
                        },
                    });
                    return;
              }
              this.$refs.zatcaPhase2Modal.open()
            },
            openDisconnect() {
                this.$refs.zatcaPhaseTwoDisconnectModal.open()
            },
            openZatcainfo() {
                this.$refs.zatcaInfoModal.open()
            },
            disconnect() {
                this.formLoading = true
                Nova.request()
                    .post('/nova-vendor/settings/disconnect', {
                        key: this.key
                    }).then(response => {

                    if (response.data.success) {
                        this.data = response.data.settings
                        this.data.fields = JSON.parse(this.data.fields)
                        this.$refs.zatcaPhaseTwoDisconnectModal.close()
                        this.loading = false;
                        this.formLoading = false;
                        this.$toasted.show(this.__('Fandaqah Disconnected Successfully'), {type: 'success'})

                    } else {
                        this.loading = false;
                        this.formLoading = false;
                        this.$toasted.show(this.__('Fail to disconnect !'), {type: 'error'})
                    }
                })
                    .catch(error => {
                        this.$toasted.error(error, {
                            duration: 3000
                        });
                        this.loading = false;
                        this.formLoading = false;
                    });
            },
            async send() {
                  if (!this.data.fields.otp) {
                    this.loading = false;
                    this.formLoading = false;
                    this.$toasted.error(this.__('Please fill all credentials '), {
                        duration: 3000
                    });
                    return;
                  }  
                  this.formLoading = true
                  this.loading = true;   
                  Nova.request()
                    .post('/nova-vendor/settings/connectWithZatcaPhaseTwo', {
                        key: this.key,
                        otp: this.data.fields.otp
                    }).then((response) => {
                      this.data = response.data.settings;
                      this.data.fields = JSON.parse(this.data.fields);
                      this.$refs.zatcaPhase2Modal.close();
                      this.$toasted.show(this.__('Fandaqah Integrated Successfully'), {type: 'success'});
                    }).catch((e) => {
                      this.$toasted.show(response.data.message, {type: 'error'});
                      this.resetFieldValues();
                    }).finally(() => {
                      this.formLoading = false;
                      this.loading = false;
                    })
            },
            
        },
        resetFieldValues() {
          this.data.fields.otp = ""
        },

        mounted() {            
           Nova.request()
                .get(`/nova-vendor/settings/integrations/${this.key}`).then(response => {
                  this.loading = false;
                  if(response.data.fields !== undefined) {
                    this.data = response.data;
                    this.data.fields = JSON.parse(this.data.fields)
                  }
            })
                .catch(error => {  
                   this.loading = false;
                    this.$toasted.error(error, {
                        duration: 3000
                    });
                    
                })
         
        }
    }
</script>

<style scoped>
.list-none {
  list-style: none;
}
.jawaly table {
  width: 100%;
  border: 1px solid #ddd;
}
.jawaly table thead th {
  background: #3b73bd;
  border: 1px solid #2c64ae;
  color: #fff;
  font-family: Dubai-Medium;
  font-weight: normal;
  font-size: 15px;
}
.jawaly table tbody td {
  text-align: center;
  font-size: 15px;
  padding: 10px;
  font-family: Dubai-Regular;
  background: #fafafa;
  border: 1px solid #ddd;
  color: #000;
}
.jawaly table tbody td.badge-secondary {color: gray;}
.jawaly table tbody td.badge-success {color: green;}
.jawaly table tbody td.badge-danger {color: red;}
.pagination {
  display: flex;
  padding-left: 0;
  list-style: none;
  border-radius: .25rem;
  margin: 20px auto 5px;
}
.page-link {
  position: relative;
  display: block;
  padding: .5rem .75rem;
  margin-left: -1px;
  line-height: 1.25;
  color: #007bff;
  background-color: #fff;
  border: 1px solid #dee2e6;
  font-size: 14px;
}
.page-item:first-child .page-link {
  margin-left: 0;
  border-top-left-radius: .25rem;
  border-bottom-left-radius: .25rem;
}
.page-item.disabled .page-link {
  color: #6c757d;
  pointer-events: none;
  cursor: auto;
  background-color: #fff;
  border-color: #dee2e6;
}
.page-item.active .page-link {
  z-index: 1;
  color: #fff;
  background-color: #007bff;
  border-color: #007bff;
}
html:lang(ar) ul.pagination li.page-item:first-child a {
  border-radius: 0 .25rem .25rem 0 !important;
  margin: 0 0 0 -1px !important;
}
html:lang(ar) ul.pagination li.page-item:last-child a {
  border-radius: .25rem 0 0 .25rem !important;
  margin: 0 -1px 0 0 !important;
}
</style>
<style lang="scss">
.Jawaly_Modal {
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
  .alert_jawaly {
    background: #fff3cd;
    border: 1px solid #ffeeba;
    color: #856404;
    text-align: center;
    padding: 15px;
    border-radius: 4px;
    font-size: 15px;
    margin: 0 auto 15px;
  } /* alert_jawaly */
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
} /* Jawaly_Modal */


.Jawaly_Info_Modal {
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
  ul {
    list-style: unset;
    padding: 10px 30px;
    li {
      margin: 0 auto 10px;
      font-size: 15px;
      color: #000;
    } /* li */
  } /* ul */
  img {
    display: block;
    margin: 15px auto;
    max-width: 100%;
    max-height: none;
    height: auto;
    width: auto;
    border: 1px solid #ddd;
  } /* img */
} /* Jawaly_Info_Modal */
</style>
