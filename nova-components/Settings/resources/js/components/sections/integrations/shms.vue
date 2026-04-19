<template>
  <div class="integration_col">
    <div class="integration_item">
      <div class="mt-5 mb-5 loader_item" v-if="loading">
        <loader class="text-60" width="40"/>
      </div><!-- loader_item -->
      <div class="status_label">
        <label v-if="integration_shomoos_version_one" class="connected">{{__('Connected With Shomoos V1')}}</label>
        <label v-else-if="data.integration && !integration_shomoos_version_one" class="connected">{{__('Connected')}}</label>
        <label v-if="!data.integration" class="notconnected">{{__('Not Connected')}}</label>
      </div><!-- status_label -->
      <new-shomos-log ref="shmsLog"></new-shomos-log>
      <div v-if="!loading">
        <div class="imgthumb cursor-pointer" @click="openLog()">
          <img src="/images/shomoos_v2.jpeg" :alt="data.name['en']"/>
        </div><!-- imgthumb -->
        <div class="name">
          <a href="https://web.shomoos.com.sa/Portal/ar" target="_blank">{{data.name[local]}}</a>
          <span><button type="button" v-tooltip.top-center="__('How to integrate with shmoos')" @click="openshmsinfo()"></button></span>
          <!-- <span><a  v-tooltip.top-center="__('How to integrate with shmoos')" ref="shmoosGuide" target="_blank" href="https://fandaqah.freshdesk.com/support/solutions/articles/61000178638-%D8%A7%D9%84%D8%B1%D8%A8%D8%B7-%D9%85%D8%B9-%D8%B4%D9%85%D9%88%D8%B3"></a></span> -->
        </div><!-- name -->
        <div class="desc">{{__("SHOMOS Integration")}}</div>
        <div class="date_integration">
          <button v-if="data.integration && integration_shomoos_version_one" @click="open()" class="connect"> {{__('Connect With Shomoos V2')}}</button>
          <button v-if="!data.integration && !integration_shomoos_version_one" @click="open()" class="connect"> {{__('Connect With Shomoos V2')}}</button>
          <!-- <button v-if="data.integration && !integration_shomoos_version_one" @click="openDisconnect()" class="disconnect"> {{__('Disconnect !')}}</button> -->
        </div><!-- date_integration -->
      </div><!-- loading -->

      <!-- SHOMOS Connecting Modal -->
      <sweet-modal :enable-mobile-fullscreen="false" :pulse-on-block="false" class="SHOMOS_Connecting_Modal" ref="scthModal" blocking overlay-theme="dark" :title="__('SHOMOS Integration')">
        <div class="loader_item" v-if="formLoading">
          <loader width="40"/>
          <span>{{__('Connecting')}}</span>
        </div><!-- loader_item -->
        <div v-if="!loading && !formLoading">
          <div class="alert_shomos">{{__('Please enter your SHOMOS credentials')}}</div>
          <div class="input_group">
            <label for="User Id">{{__('User Id')}}</label>
            <input type="text" v-model="data.fields.userid" :placeHolder="__('User Id')">
          </div><!-- input_group -->
          <div class="input_group">
            <label for="Branch Code">{{__('Branch Code')}}</label>
            <input type="text" v-model="data.fields.branchcode" :placeHolder="__('Branch Code')">
          </div><!-- input_group -->
          <div class="input_group">
            <label for="Branch Secret">{{__('Branch Secret Key used in transactions')}}</label>
            <input type="text" v-model="data.fields.branchsecret" :placeHolder="__('Branch Secret Key used in transactions')">
          </div><!-- input_group -->
          <!-- <div class="input_group">
            <label for="Token">{{__('Token')}}</label>
            <input type="text" v-model="data.fields.token" :placeHolder="__('Token')">
          </div> -->
          <button @click="send">{{__('Connect')}}</button>
        </div><!-- loading -->
      </sweet-modal>
      <!-- SHOMOS Connecting Modal -->

      <sweet-modal ref="scthDisconnectModal" icon="warning" overlay-theme="dark" modal-theme="dark" title="SHOMOS Integration">
        <div class="mt-5 mb-5 text-center" v-if="formLoading">
          <loader class="text-60 text-warning" width="40"/>
          <div class="mb-2 text-2xl block font-bold no-underline  text-warning">Disconnecting</div>
        </div>
        <div class="text-center" v-if="!formLoading">
          <a class="mb-2 text-2xl block font-bold no-underline text-warning"> This action will disconnect your account from {{data.name['en']}} , please be careful ! </a> <button class=" btn btn-block btn btn-default btn-danger mt-2" @click="disconnect()">DISCONNECT</button>
        </div>
      </sweet-modal>


      <!-- SHMS Info -->
      <sweet-modal ref="shmsInfoModal" :enable-mobile-fullscreen="false" :pulse-on-block="false" class="Shms_Info_Modal" blocking overlay-theme="dark"  :title="__('How to integrate with shmoos')">
        <span>
          لماذا الربط مع شموس؟
          <p>ضمان عدم نسيان ترحيل عقود وبيانات النزلاء لوزارة الداخلية وتلافي اي مشاكل لاحقا.</p>
        </span>
        <span>
          ماهي خدمة الربط مع شموس في فندقة؟
          <p>عد الربط مع شموس فان برنامج فندقة يرحل بشكل آلي بيانات العقود والنزلاء لبرنامج شموس دون الحاجة لإدخالها بشكل يدوي في برنامج شموس كل مره.</p>
        </span>
        <span>
          كيفية الربط مع الخدمة؟
          <ul>
            <li>سجل دخول لحسابك في برنامج فندقة.</li>
            <li>الذهاب للإعدادات.</li>
            <li>ثم الضغط على إعدادات التكامل.</li>
            <li>ستجد خدمة شموس في حال كانت "غير متصل" باللون الاحمر، أضغط عليها وسيظهر لك المعلومات المطلوبة للربط مع شموس.</li>
            <li>ادخل رقم هوية مدير الفرع المسجل فى شموس2</li>
            <li> ادخل كود الفرع المسجل فى شهادة الفرع شموس2</li>
            <li>ادخل الرقم السري للفرع المستخدم فى العمليات حسب اعدادات شموس2</li>
            <li>قم بالربط وسيتم الربط مع الخدمة بشكل مباشر في حال المعلومات صحيحة</li>
          </ul>
        </span>
        <img src="/images/integration_shms_2.png" alt="shmsInfo1">
        <span>
          اين تجد المعلومات الخاصة بالربط ؟
          <ul>
            <li>يرجي زياره هذا الرابط للاطلاع علي الدليل العام للربط <a class="general_guide" target="_blank" href="https://workdrive.zohoexternal.com/external/0f237426fca3fbc8daaba6112d4bde6f5e913e3ea12c08d213313bbcfb14ecc1">اضغط هنا لعرض الدليل العام</a></li>
          </ul>
        </span>
      </sweet-modal>
      <!-- SHMS Info -->

    </div><!-- integration_item -->
  </div><!-- integration_col -->
</template>

<script>

    import NewShomosLog from './log/NewShomosLog';

    export default {
        name: "shomoos",
        components: {
            NewShomosLog
        },
        data() {
            return {
                data: null,
                key: 'SHMS',
                loading: true,
                local: Nova.config.local,
                formLoading: false,
                suspect_shms : false,
                integration_shomoos_version_one : Spark.state.currentTeam.integration_shomoos_version_one
            }
        },
        methods: {
            openLog() {
                this.$refs.shmsLog.openLog()
            },
            async open() {
                const response = await axios.get('/nova-vendor/settings/integration-controls?type=shms');
                this.suspect_shms = response.data;

                if(this.suspect_shms){
                    this.$toasted.show(this.__('Please contact the administration to connect with SHMS'), {
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

                if (Spark.state.currentTeam.current_billing_plan == 'team-free') {
                    this.$toasted.show(Nova.app.__('Your account on the free plan must be upgraded to access to connect with SHMS'), { type: 'error' });
                    return;
                }
                this.$refs.scthModal.open()
            },
            openDisconnect() {
                this.$refs.scthDisconnectModal.open()
            },
            openshmsinfo() {
                this.$refs.shmsInfoModal.open()
            },
            disconnect() {
                this.formLoading = true
                Nova.request()
                    .post('/nova-vendor/settings/disconnect', {
                        key: 'SHMS',
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

                if (!this.data.fields.userid || !this.data.fields.branchcode || !this.data.fields.branchsecret) {

                    this.$toasted.error('Please fill all credentials ', {
                        duration: 3000
                    });
                    return;
                }

                this.loading = true;
                this.formLoading = true;
                Nova.request()
                    .post('/nova-vendor/settings/register', {
                        key: 'SHMS',
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
                .get('/nova-vendor/settings/integrations/SHMS').then(response => {
                this.data = response.data
                console.log(this.data);
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
.SHOMOS_Connecting_Modal {
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
  .alert_shomos {
    background: #fff3cd;
    border: 1px solid #ffeeba;
    color: #856404;
    text-align: center;
    padding: 15px;
    border-radius: 4px;
    font-size: 15px;
    margin: 0 auto 15px;
  } /* alert_shomos */
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
} /* SHOMOS_Connecting_Modal */

.Shms_Info_Modal {
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
  span {
    display: block;
    margin: 20px auto;
    font-weight: bold;
    font-size: 15px;
    p {
      display: block;
      font-size: 15px;
      font-weight: normal;
      margin: 5px auto 0;
    } /* p */
    ul {
      list-style: unset;
      padding: 10px 30px;
      li {
        margin: 0 auto 10px;
        font-size: 15px;
        color: #000;
        font-weight: normal;
      } /* li */
    } /* ul */
  } /* span */
  img {
    display: block;
    margin: 15px auto;
    max-width: 100%;
    max-height: none;
    height: auto;
    width: auto;
    border: 1px solid #ddd;
  } /* img */
} /* Shms_Info_Modal */

.general_guide {
    height: 35px;
    width: 100%;
    color: #ffffff;
    background: #4099de;
    border: 1px solid #4099de;
    border-radius: 5px;
    padding: 4px;
    margin: 15px auto;
    cursor: pointer;
    -webkit-transition: all 0.2s ease-in-out;
    transition: all 0.2s ease-in-out;
    &:hover {background: #4099de;}
  } /* button */
</style>
