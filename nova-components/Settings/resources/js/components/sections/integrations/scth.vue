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
      <scth-log ref="scthLog"></scth-log>
      <div v-if="!loading">
        <div class="imgthumb cursor-pointer" @click="openLog()">
          <img src="/images/scth.png" :alt="data.name['en']"/>
        </div><!-- imgthumb -->
        <div class="name">
          <a :href="data.url" target="_blank">وزارة السياحة</a>
          <span><button type="button" v-tooltip.top-center="__('How to integrate with SCTH')" @click="openscthinfo()"></button></span>
          <!-- <span><a v-tooltip.top-center="__('How to integrate with SCTH')" target="_blank" href="https://fandaqah.freshdesk.com/support/solutions/articles/61000051099-%D8%A7%D9%84%D8%B1%D8%A8%D8%B7-%D9%85%D8%B9-%D8%A7%D9%84%D9%85%D9%86%D8%B5%D8%A9-%D8%A7%D9%84%D9%88%D8%B7%D9%86%D9%8A%D8%A9-%D9%84%D9%84%D8%B1%D8%B5%D8%AF-%D8%A7%D9%84%D8%B3%D9%8A%D8%A7%D8%AD%D9%8A"></a></span> -->
        </div><!-- name -->
        <div class="desc">الربط مع <a href="https://ntmp.gov.sa/" target="_blanck" title=" المرصد السياحي "> المرصد السياحي </a></div>
        <div class="date_integration">
          <button v-if="!data.integration" @click="open()" class="connect"> {{__('Connect')}}</button>
          <!-- <button v-if="data.integration" @click="openDisconnect()" class="disconnect"> {{__('Disconnect !')}}</button> -->
        </div><!-- date_integration -->
      </div><!-- loading -->


      <sweet-modal ref="scthModal" :enable-mobile-fullscreen="false" :pulse-on-block="false" class="SCTH_Modal" blocking overlay-theme="dark" :title="__('SCTH Integration') ">
        <div class="loader_item" v-if="formLoading">
          <loader width="40"/>
          <span>{{__('Connecting')}}</span>
        </div><!-- loader_item -->
        <div v-if="!loading && !formLoading">
          <div class="alert_scth">قم بتعبئة البيانات التي تم تزويدكم بها من قبل  <a href="https://ntmp.gov.sa/" target="_blanck" title="المنصة الوطنية للرصد السياحي">المنصة الوطنية للرصد السياحي</a></div>
          <div class="input_group">
            <label for="Username">{{__('Username')}}</label>
            <input type="text" v-model="data.fields.username" :placeHolder="__('Username')">
          </div><!-- input_group -->
          <div class="input_group">
            <label for="Password">{{__('Password')}}</label>
            <input type="text" v-model="data.fields.password" :placeHolder="__('Password')">
          </div><!-- input_group -->
          <div class="input_group">
            <label for="Token">{{__('Token')}}</label>
            <input type="text" v-model="data.fields.token" :placeHolder="__('Token')">
          </div><!-- input_group -->
          <button @click="send">{{__('Connect')}}</button>
        </div>
      </sweet-modal>

        <sweet-modal ref="scthDisconnectModal" icon="warning" overlay-theme="dark" modal-theme="dark"
                     title="SCTH Integration">
            <div class="mt-5 mb-5 text-center" v-if="formLoading">
                <loader class="text-60 text-warning" width="40"/>
                <div class="mb-2 text-2xl block font-bold no-underline  text-warning">
                    Disconnecting
                </div>
            </div>
            <div class="text-center" v-if="!formLoading">
                <a class="mb-2 text-2xl block font-bold no-underline text-warning"> This action will disconnect your
                    account from {{data.name['en']}} , please be careful ! </a>
                <button class=" btn btn-block btn btn-default btn-danger mt-2" @click="disconnect()">DISCONNECT</button>

            </div>

        </sweet-modal>


      <!-- SCTH Info -->
      <sweet-modal ref="scthInfoModal" :enable-mobile-fullscreen="false" :pulse-on-block="false" class="Scth_Info_Modal" blocking overlay-theme="dark"  :title="__('How to integrate with SCTH')">
        <ul>
          <li>قم بتسجيل الدخول في المنصة من خلال الرابط التالي https://ntmp.gov.sa/Login</li>
          <li>ادخل اسم المستخدم و كلمة المرور الخاصة بك وهي نفسها معلومات بوابة مرافقة الايواء السياحي.</li>
          <li>ستظهر لك اسماء المنشأة التابعة لك ، أضغط على ايقونة مشاهدة تفاصيل الربط.</li>
          <li>أضغط على تفاصيل الربط.</li>
          <li>ستجد مفتاح الربط و اسم المستخدم و كلمة المرور (أضغط على علامة العين لتظهر لك كلمة المرور).</li>
          <li>قم بنسخ هذه المعلومات والذهاب لبرنامج فندقة ومن ثم الإعدادات ثم الإعدادات العامة، أضغط على الربط مع منصة الرصد السياحي.</li>
          <li>ادخل المعلومات الخاصة بالمنصة السابقة وهي (مفتاح الربط و اسم المستخدم و كلمة المرور) واضغط على ربط. وسيتم تفعيل الربط في حال كانت المعلومات صحيحة.</li>
        </ul>
        <img src="/images/scthInfo1.png" alt="scthInfo1">
        <img src="/images/scthInfo2.png" alt="scthInfo2">
        <img src="/images/scthInfo3.png" alt="scthInfo3">
      </sweet-modal>
      <!-- SCTH Info -->

    </div><!-- integration_item -->
  </div><!-- integration_col -->
</template>

<script>
    import ScthLog from './log/ScthLog';
    export default {
        name: "scth",
        components: {
            ScthLog
        },
        data() {
            return {
                data: null,
                key: 'SCTH',
                loading: true,
                local: Nova.config.local,
                formLoading: false,
                suspect_scth : false
            }
        },
        methods: {
            openLog() {
                this.$refs.scthLog.openLog()
            },
            async open() {
                const response = await axios.get('/nova-vendor/settings/integration-controls?type=scth');
                this.suspect_shms = response.data;

                if(this.suspect_shms){
                    this.$toasted.show(this.__('Please contact the administration to connect with SCTH'), {
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
                this.$refs.scthModal.open()
            },
            openDisconnect() {
                this.$refs.scthDisconnectModal.open()
            },
            openscthinfo() {
                this.$refs.scthInfoModal.open()
            },
            disconnect() {
                this.formLoading = true
                Nova.request()
                    .post('/nova-vendor/settings/disconnect', {
                        key: 'SCTH',
                    }).then(response => {

                    if (response.data.success) {
                        this.data = response.data.settings
                        this.data.fields = JSON.parse(this.data.fields)
                        this.$refs.scthDisconnectModal.close()
                        this.loading = false;
                        this.formLoading = false;
                        this.$toasted.show('Fandaqah Disconnected Successfully', {type: 'success'})

                    } else {
                        this.loading = false;
                        this.formLoading = false;
                        this.$toasted.show('Fail to disconnect !', {type: 'error'})

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
            send() {

                if (!this.data.fields.username || !this.data.fields.password || !this.data.fields.token) {

                    this.$toasted.error('Please fill all credentials ', {
                        duration: 3000
                    });
                    return;
                }

                this.loading = true;
                this.formLoading = true;
                Nova.request()
                    .post('/nova-vendor/settings/register', {
                        key: 'SCTH',
                        values: this.data.fields
                    }).then(response => {

                    if (response.data.success) {
                        this.data = response.data.settings
                        this.data.fields = JSON.parse(this.data.fields)
                        this.$refs.scthModal.close()
                        this.loading = false;
                        this.formLoading = false;
                        this.$toasted.show('Fandaqah Integrated Successfully', {type: 'success'})

                    } else {
                        this.loading = false;
                        this.formLoading = false;
                        this.$toasted.show('Fail to connect !', {type: 'error'})

                    }



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
                .get('/nova-vendor/settings/integrations/scth').then(response => {

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
        }
    }
</script>

<style lang="scss">
.SCTH_Modal {
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
  .alert_scth {
    background: #fff3cd;
    border: 1px solid #ffeeba;
    color: #856404;
    text-align: center;
    padding: 15px;
    border-radius: 4px;
    font-size: 15px;
    margin: 0 auto 15px;
  } /* alert_scth */
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
} /* SCTH_Modal */


.Scth_Info_Modal {
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
} /* Scth_Info_Modal */
</style>
