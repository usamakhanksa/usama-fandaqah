<template>
  <div class="integration_col">
    <div class="integration_item">
      <div class="status_label">
        <label v-if="data.integration" class="connected">{{__('Connected')}}</label>
        <label v-if="!data.integration" class="notconnected">{{__('Not Connected')}}</label>
      </div><!-- status_label -->
      <jawaly-log ref="jawalyLog" v-if="data.integration"></jawaly-log>
      <div>
        <div class="imgthumb cursor-pointer" @click="openLog()">
          <!-- <img :src="data.logo" :alt="data.name['en']"/> -->
          <svg width="270px" height="100px" viewBox="0 0 651 146" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
            <title>logo-ar</title>
            <g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                <g id="logo-ar" fill-rule="nonzero">
                    <g id="Group" transform="translate(521.000000, 0.000000)">
                        <path d="M120.4,1 L95.7,1 C90.5,1 86.2,5.2 86.2,10.5 L86.2,59.7 L86.2,69.2 L95.7,69.2 L120.4,69.2 C125.6,69.2 129.9,65 129.9,59.7 L129.9,10.5 C129.8,5.2 125.6,1 120.4,1 Z" id="Path" fill="#5C6BBF"></path>
                        <path d="M120.4,87.2 L95.7,87.2 L86.2,87.2 L86.2,96.7 L86.2,122.4 C86.2,127.6 90.4,131.9 95.7,131.9 L120.4,131.9 C125.6,131.9 129.9,127.7 129.9,122.4 L129.9,96.6 C129.8,91.4 125.6,87.2 120.4,87.2 Z" id="Path" fill="#FFA727"></path>
                        <path d="M58.7,87.2 L9.5,87.2 C4.3,87.2 0,91.4 0,96.7 L0,122.4 C0,127.6 4.2,131.9 9.5,131.9 L58.7,131.9 C63.9,131.9 68.2,127.7 68.2,122.4 L68.2,96.6 L68.2,87.1 L58.7,87.1 L58.7,87.2 Z" id="Path" fill="#EF5350"></path>
                        <path d="M62.3,1.7 C58.8,0.2 54.7,1 52,3.7 L2.8,53 C0.1,55.7 -0.7,59.8 0.8,63.3 C2.3,66.8 5.7,69.1 9.5,69.1 L58.7,69.1 L68.2,69.1 L68.2,59.6 L68.2,10.5 C68.2,6.6 65.9,3.2 62.3,1.7 Z" id="Path" fill="#25C6DA"></path>
                    </g>
                    <g id="Group" transform="translate(0.000000, 16.000000)" fill="#2B2B3D">
                        <path d="M453.4,31.8 C436.2,31.8 424.2,44.3 424.2,62.3 C424.2,68.2 425.6,73.9 428.3,78.6 L429.2,80.1 L401.1,80.1 L405,69.9 C405.8,67.2 405.8,65 405.8,62.3 C405.8,44.4 393.8,31.8 376.6,31.8 C359.4,31.8 347.4,44.3 347.4,62.3 C347.4,79.4 358.2,91.5 374.4,92.5 L375.4,92.6 L382.1,92.6 L372.9,116.2 L377.3,116.2 C383.3,116.2 388.8,112.4 391,106.8 L396.4,92.6 L456.3,92.6 L456.3,92.5 L457.2,92.4 C472.4,90.7 482.7,78.6 482.7,62.3 C482.6,44.3 470.6,31.8 453.4,31.8 Z M391,69.7 L390.7,70.4 L387,80 L376.6,80 C367.6,80 360.8,72.3 360.8,62.2 C360.8,51.8 367.5,44.3 376.7,44.3 C385.5,44.3 391.9,51.5 392.5,62.1 C392.6,64.6 391.4,68.6 391,69.7 Z M453.4,80 C444.3,80 437.5,72.3 437.5,62.2 C437.5,51.8 444.2,44.3 453.4,44.3 C462.6,44.3 469.2,51.8 469.2,62.2 C469.2,72.4 462.5,80 453.4,80 Z" id="Shape"></path>
                        <path d="M454.5,18.7 C459.5,18.7 463.6,14.7 463.6,9.8 C463.6,5 459.5,0.9 454.5,0.9 C449.5,0.9 445.4,4.9 445.4,9.8 C445.5,14.7 449.5,18.7 454.5,18.7 Z" id="Path"></path>
                        <path d="M267.8,104.7 C262.8,104.7 258.7,108.7 258.7,113.6 C258.7,118.5 262.8,122.5 267.8,122.5 C272.8,122.5 276.9,118.5 276.9,113.6 C276.9,108.8 272.7,104.7 267.8,104.7 Z" id="Path"></path>
                        <path d="M47.2,112.2 C42.2,112.2 38.1,116.2 38.1,121.1 C38.1,126 42.2,130 47.2,130 C52.2,130 56.3,126 56.3,121.1 C56.2,116.3 52.1,112.2 47.2,112.2 Z" id="Path"></path>
                        <path d="M22.5,112.2 C17.5,112.2 13.4,116.2 13.4,121.1 C13.4,126 17.5,130 22.5,130 C27.5,130 31.6,126 31.6,121.1 C31.5,116.3 27.4,112.2 22.5,112.2 Z" id="Path"></path>
                        <path d="M333.6,103.7 L333.6,36.9 L319.4,36.9 L319.4,115.4 L321.9,115.4 C328.4,115.5 333.6,110.2 333.6,103.7 Z" id="Path"></path>
                        <path d="M153.4,91.5 L155.9,91.5 L155.9,7.5 L141.7,7.5 L141.7,79.7 C141.7,86.3 147,91.5 153.4,91.5 Z" id="Path"></path>
                        <path d="M111.4,19.3 L111.4,80 L91.8,80 L91.8,31.8 L72.5,31.8 C55.9,31.8 47.5,41.1 47.4,59.5 L47.4,87.5 L26.8,87.5 C17.8,87.5 13.8,82.9 13.7,72.4 L13.7,51.3 L0.4,51.3 L0.4,72.4 C0.4,90.5 9.6,100.1 26.8,100.1 L60.8,100.1 L60.8,59.4 C60.8,49.9 62.7,44.3 72.5,44.3 L78.4,44.3 L78.4,92.5 L125.6,92.5 L125.6,7.5 L123.1,7.5 C116.7,7.5 111.4,12.8 111.4,19.3 Z" id="Path"></path>
                        <path d="M271.5,31.8 C257.6,31.8 247,39.9 243.5,53 L256.4,56.5 C258.4,49.1 264.2,44.4 271.5,44.4 C280.6,44.4 287.3,51.9 287.3,62.2 L287.3,80 L225.5,80 L229.4,69.8 C230.2,67.1 230.2,64.9 230.2,62.2 C230.2,44.3 218.2,31.7 201,31.7 C183.8,31.7 171.8,44.2 171.8,62.2 C171.8,80.1 183.8,92.5 201,92.5 L206.5,92.5 L197.3,116.1 L201.7,116.1 C207.7,116.1 213.2,112.3 215.4,106.7 L220.8,92.5 L300.8,92.5 L300.8,62.2 C300.7,44.3 288.7,31.8 271.5,31.8 Z M215.4,69.7 L215.1,70.4 L211.4,80 L201,80 C192,80 185.1,72.3 185.1,62.2 C185.1,51.8 191.8,44.3 201,44.3 C209.8,44.3 216.2,51.5 216.8,62.1 C217,64.6 215.8,68.6 215.4,69.7 Z" id="Shape"></path>
                    </g>
                </g>
            </g>
        </svg>
        </div><!-- imgthumb -->
        <div class="name">
          <a :href="'https://user.4jawaly.com'" target="_blank">{{ __('4jawaly') }}</a>
          <span><button type="button" v-tooltip.top-center="__('How to integrate with 4jawaly')" @click="openjawalyinfo()"></button></span>

        </div><!-- name -->
        <div v-if="!data.integration" class="desc">{{ __('integrate with')}}
          <a :href="'https://user.4jawaly.com'" target="_blanck" :title="__('4jawaly')"> {{ __('4jawaly') }} </a>
        </div>
        <div class="desc" v-else> {{__('your Balnace')}} : {{ balance}}</div>
        <div class="date_integration">
          <button v-if="!data.integration" @click="open()" class="connect"> {{__('Connect')}}</button>
          <button v-if="data.integration" @click="openDisconnect()" class="disconnect"> {{__('Disconnect !')}}</button>
        </div><!-- date_integration -->
      </div><!-- loading -->

      <sweet-modal ref="jawalyModal" :enable-mobile-fullscreen="false" :pulse-on-block="false" class="Jawaly_Modal" blocking overlay-theme="dark" :title="__('4jawaly Integration') ">
        <div class="relative">
               <loading :active.sync="loading"
               :can-cancel="true"
               :is-full-page="false"></loading>
          <div class="alert_jawaly">{{ __('Write down the data and statistics that were previously provided to you') }} <a :href="'https://user.4jawaly.com'" target="_blanck" :title="__('4jawaly')">{{ __('4jawaly') }}</a></div>
          <div class="input_group">
            <label for="Username">{{__('API KEY')}}</label>
            <input type="text" v-model="data.fields.api_key" :placeHolder="__('API KEY')">
          </div><!-- input_group -->
          <div class="input_group">
            <label for="Password">{{__('API SECRET')}}</label>
            <input type="text" v-model="data.fields.api_secret" :placeHolder="__('API SECRET')">
          </div><!-- input_group -->
          <div class="input_group">
            <label for="Sender">{{__('Sender')}}</label>
            <input type="text" v-model="data.fields.sender" :placeHolder="__('Sender')">
          </div>
          <button @click="send"  v-if="enable_sms">{{__('Connect')}}</button>
        </div>
      </sweet-modal>
      <sweet-modal ref="jawalyDisconnectModal" icon="warning" overlay-theme="dark" modal-theme="dark"
                    :title="__('4jawaly Integration')">
          <div class="mt-5 mb-5 text-center" v-if="formLoading">
              <loader class="text-60 text-warning" width="40"/>
              <div class="mb-2 text-2xl block font-bold no-underline  text-warning">
                  {{ __('Disconnecting')}}
              </div>
          </div>
          <div class="text-center" v-if="!formLoading">
              <a class="mb-2 text-2xl block font-bold no-underline text-warning">{{ __('This action will disconnect your account from')}} {{data.name['en']}} , {{ __('please be careful !')}} </a>
              <button class=" btn btn-block btn btn-default btn-danger mt-2" @click="disconnect()">
                {{ __('DISCONNECT')}}
              </button>

          </div>

      </sweet-modal>
      <sweet-modal ref="jawalyInfoModal" :enable-mobile-fullscreen="false" :pulse-on-block="false" class="Fonic_Info_Modal" blocking overlay-theme="dark"  :title="__('Mobile messaging service')">
         <span>
           كيف تفعيل رسائل الجوال في فندقة؟
           <ul>
             <li>نعمل في فندقة مع مقدم خدمة الرسائل الافضل في السعودية (فور جوالي) .</li>
             <li>يمكنك طلب شحن رصيد خاص بك من خلال مخاطبة خدمة العملاء في فندقة</li>
             <li>او الدخول لحسابك ثم الاعدادات وخدمة رسائل الجوال وطلب شحن رصيد</li>
           </ul>
         </span>
      </sweet-modal>
      <sweet-modal ref="jawalyLogsModal" :enable-mobile-fullscreen="false"  blocking overlay-theme="dark" :title="__('jawaly Log')">
        <div>
            <div class="jawaly">
                <table class="table-auto relative">
                    <loading
                    :active.sync="loading"
                    :can-cancel="true"
                    :is-full-page="false">
                    </loading>
                    <thead>
                    <tr>
                        <th class="px-4 py-2">{{__('Status')}}</th>
                        <th class="px-4 py-2">{{__('Senders Numbers')}}</th>
                        <th class="px-4 py-2">{{__('Sms Message')}}</th>
                        <th class="px-4 py-2">{{__('4Jawaly Message')}}</th>
                    </tr>
                    </thead>
                    <tbody>
                    <template v-if="logs_data.length">
                      <tr v-for="(item,i) in logs_data" :key="i">
                          <td class="border px-4 py-2" :class="'badge-'+jobStatusClass(item.status)">{{
                              __(item.status) }}
                          </td>
                          <td class="border px-4 py-2">{{ item.payload.phone }}</td>
                          <td class="border px-4 py-2" >{{item.payload.message}}</td>
                          <td class="border px-4 py-2" >{{item.payload.serverMsg ? item.payload.serverMsg : '-' }}</td>
                      </tr>
                    </template>
                    <template v-else>
                      <tr>
                        <td colspan="4">{{__('No Data')}}</td>
                      </tr>
                    </template>

                    </tbody>
                </table>
            </div>
            <div class="w-full flex flex-wrap mt-8 justify-center" v-if="paginator && paginator.totalResults > 5">
                 <pagination
                  v-if="paginator.lastPage > 1"
                  :page-count="paginator.lastPage"
                  :page-range="3"
                  :margin-pages="2"
                  :click-handler="getLogs"
                  :value="paginator.currentPage"
                  :prev-text="__('Previous')"
                  :next-text="__('Next')"
                  :container-class="'pagination  w-full flex justify-center'"
                  :page-class="'page-item'"
                  :page-link-class="'page-link'"
                  :prev-link-class="'page-link'"
                  :next-link-class="'page-link'"
                  :prev-class="'page-item'"
                  :next-class="'page-item'"
                  :first-last-button="true"
                  :first-button-text="__('First')"
                  :last-button-text="__('Last')"
                />
            </div>
        </div>
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
        name: "jawaly-new",
        components: {
            Loading,
            Pagination
        },
        data() {
            return {
                balance: 0,
                data: null,
                unifonic: null,
                key: 'Jawaly',
                loading: true,
                local: Nova.config.local,
                formLoading: false,
                paginator : {},
                logs_data: [],
                enable_sms : Nova.app.currentTeam.enable_sms

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
                    this.loading = false;
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
                this.$refs.jawalyModal.open()
            },
            openDisconnect() {
                this.$refs.jawalyDisconnectModal.open()
            },
            openjawalyinfo() {
                this.$refs.jawalyInfoModal.open()
            },
            disconnect() {
                this.formLoading = true
                Nova.request()
                    .post('/nova-vendor/settings/disconnect', {
                        key: 'Jawaly',
                    }).then(response => {

                    if (response.data.success) {
                        this.data = response.data.settings
                        this.data.fields = JSON.parse(this.data.fields)
                        this.$refs.jawalyDisconnectModal.close()
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
                if (!this.data.fields.api_key || !this.data.fields.api_secret || !this.data.fields.sender) {
                  this.loading = false;
                  this.formLoading = false;
                    this.$toasted.error(this.__('Please fill all credentials '), {
                        duration: 3000
                    });
                    return;
                }
                this.loading = true;
                 const response = await axios.post('/nova-vendor/settings/check-4jawaly-credentials', this.data.fields
                 );
                 if(response.data.code == 400){
                     this.$toasted.show(response.data.message, {type: 'error'})
                     this.loading = false;
                     return;
                 }

                 if(response.data.code == 200 && !response.data.items.length){
                     this.$toasted.show(this.__('Sender is not found'), {type: 'error'})
                     this.loading = false;
                     return;
                 }else{
                    Nova.request()
                    .post('/nova-vendor/settings/connectWithJawaly', {
                        key: 'Jawaly',
                        values: this.data.fields
                    }).then(response => {

                        this.data = response.data.settings
                        this.data.fields = JSON.parse(this.data.fields)
                        this.$refs.jawalyModal.close()
                        this.$toasted.show(this.__('Fandaqah Integrated Successfully'), {type: 'success'})
                        this.getBalance();
                        this.loading = false;
                    })
                 }

            },
            getBalance(){
              Nova.request()
                .get('/api/jawaly_balance', {
                    params: {
                      team_id: Spark.state.currentTeam.id
                    }
                }).then(response => {
                    this.balance = response.data.balance
                })
                .catch(error => {
                    this.$toasted.error(error, {
                        duration: 3000
                    });
                    this.loading = false;
                });
            }
        },

        mounted() {
            Nova.request()
                .get('/nova-vendor/settings/integrations/Jawaly').then(response => {
                this.data = response.data
                this.data.fields = JSON.parse(this.data.fields)
                this.loading = false;
            })
                .catch(error => {
                    this.$toasted.error(error, {
                        duration: 3000
                    });
                    this.loading = false;
                });

            this.getBalance();
        }
    }
</script>

<style scoped>
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
