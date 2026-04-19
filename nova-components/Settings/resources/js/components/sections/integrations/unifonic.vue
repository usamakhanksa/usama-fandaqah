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
      <unifonic-log ref="unifonicLog" v-if="data.integration"></unifonic-log>
      <div v-if="!loading">
        <div class="imgthumb cursor-pointer" @click="openLog()">
          <img src="/images/WhatsApp Image 2024-11-10 at 13.30.12_20cfe280.jpg" alt="unifonic">
        </div><!-- imgthumb -->
        <div class="name">
          <a :href="data.url" target="_blank">{{__('Mobile Messages')}} <small v-if="data.integration && data.balance">( {{data.balance }} {{__('SMS')}} )</small></a>
          <!-- <span><button type="button" v-tooltip.top-center="__('Mobile messaging service')" @click="openfonicinfo()"></button></span> -->
        </div>
        <div class="low_balance" v-if="data.balance < 1">{{__('No sufficient balance , check you pre-paid messages or top up your wallet and try again')}}</div>
        <div class="desc">{{__("Mobile messaging service")}}
<!--            ( <small @click="openRecharge()">{{__("Credit Recharge Request")}}</small> )-->
        </div>
        <div class="date_integration">
          <button v-if="!data.integration" @click="open()" class="connect"> {{__('Connect')}}</button>
          <button v-if="data.integration" @click="openDisconnect()" class="disconnect"> {{__('Disconnect !')}}</button>
        </div><!-- date_integration -->
      </div><!-- loading -->

      <!-- Unifonic Modal -->
      <sweet-modal :enable-mobile-fullscreen="false" :pulse-on-block="false" class="Unifonic_Modal" ref="unifonicModal" blocking overlay-theme="dark" :title="__('RASIL FANDAQAH Integration')">
        <div class="loader_item" v-if="formLoading">
          <loader width="40"/>
          <span>{{__('Connecting')}}</span>
        </div><!-- loader_item -->
        <div v-if="!loading && !formLoading">
          <div class="alert_unifonic">{{__('Please enter your RASIL FANDAQAH credentials')}}</div>
          <div class="input_group">
            <label for="App_Sid">{{__('API KEY')}}</label>
            <input type="text" v-model="data.fields.appSid" :placeHolder="__('API KEY')">
          </div><!-- input_group -->
          <button @click="send" v-if="enable_sms">{{__('Connect')}}</button>

          <div class="integration-guide">
            <button class="how-to-use-btn" @click="showGuide = !showGuide">
              {{ showGuide ? __('Hide Guide') : __('How to use?') }}
              <i :class="['fas', showGuide ? 'fa-chevron-up' : 'fa-chevron-down']"></i>
            </button>

            <div class="support-message">
              <div class="support-content">
                <i class="fas fa-headset"></i>
                <p>{{__('Feel free to contact Fandaqah Support Team for any assistance.')}}</p>
              </div>
            </div>

            <div class="guide-content" :class="{ 'show': showGuide }">
              <!-- English Version -->
              <div class="guide-section">
                <h3 class="guide-title">SMS Portal Integration Guide</h3>

                <div class="step-item">
                  <h4>1. Registering an Account</h4>
                  <ol>
                    <li>Open the SMS portal: <a href="https://ms-sms.fandaqah.com" target="_blank">ms-sms.fandaqah.com</a></li>
                    <li>Click on "Register" to create a new account.</li>
                    <li>Fill in your details and complete the registration process.</li>
                  </ol>
                </div>

                <div class="step-item">
                  <h4>2. Updating Profile Information</h4>
                  <ol>
                    <li>After registration, go to the "Profile" page.</li>
                    <li>Enter and update your personal information.</li>
                    <li class="note">Note: This information will be used in Zatca Invoicing, so please provide accurate information.</li>
                  </ol>
                </div>

                <div class="step-item">
                  <h4>3. Subscribing to a Plan</h4>
                  <ol>
                    <li>Navigate to the "Dashboard" page.</li>
                    <li>Choose a suitable SMS plan.</li>
                    <li>Subscribe using online secure payment.</li>
                    <li>Once payment is successful, your balance will be credited, and an invoice will be generated.</li>
                  </ol>
                </div>

                <div class="step-item">
                  <h4>4. Obtaining an API Key</h4>
                  <ol>
                    <li>Go to the "My Plans" page.</li>
                    <li>Generate an API key for integration.</li>
                  </ol>
                </div>

                <div class="step-item">
                  <h4>5. Connecting to Fandaqah PMS</h4>
                  <ol>
                    <li>Open the "Integration Settings" page in Fandaqah PMS.</li>
                    <li>Enter the generated API key.</li>
                    <li>Click "Connect".</li>
                    <li>Once connected, you can start using the SMS services.</li>
                  </ol>
                </div>

                <div class="step-item">
                  <h4>6. Viewing SMS History</h4>
                  <ol>
                    <li>Access the "SMS History" page on <a href="https://ms-sms.fandaqah.com" target="_blank">ms-sms.fandaqah.com</a></li>
                    <li>Monitor your SMS consumption and history.</li>
                    <li>Download detailed reports as needed.</li>
                  </ol>
                </div>
              </div>

              <div class="important-note">
                <i class="fas fa-exclamation-circle"></i>
                <p>{{__('Please note that the steps mentioned above are for using Fandaqah as the sender name. If you wish to activate a custom sender name, please contact technical support.')}}</p>
              </div>

              <hr class="divider">

              <!-- Arabic Version -->
              <div class="guide-section rtl">
                <h3 class="guide-title">دليل تكامل بوابة الرسائل النصية</h3>

                <div class="step-item">
                  <h4>١. تسجيل حساب</h4>
                  <ol>
                    <li>فتح موقع بوابة الرسائل النصية: <a href="https://ms-sms.fandaqah.com" target="_blank">ms-sms.fandaqah.com</a></li>
                    <li>النقر على "تسجيل" لإنشاء حساب جديد.</li>
                    <li>ملء المعلومات وإنهاء عملية التسجيل.</li>
                  </ol>
                </div>

                <div class="step-item">
                  <h4>٢. تحديث معلومات الملف الشخصي</h4>
                  <ol>
                    <li>بعد التسجيل، انتقل إلى صفحة "الملف الشخصي".</li>
                    <li>أدخل وحدّث معلوماتك الشخصية.</li>
                    <li class="note">ملاحظة: سيتم استخدام هذه المعلومات في فواتير Zatca، فيرجى تقديم معلومات دقيقة.</li>
                  </ol>
                </div>

                <div class="step-item">
                  <h4>٣. الاشتراك في خطة</h4>
                  <ol>
                    <li>الانتقال إلى صفحة "اللوحة الرئيسية".</li>
                    <li>اختيار خطة الرسائل المناسبة.</li>
                    <li>الاشتراك من خلال دفع آمن عبر الإنترنت.</li>
                    <li>بعد نجاح الدفع، سيتم إضافة الرصيد وتوليد فاتورة على البوابة.</li>
                  </ol>
                </div>

                <div class="step-item">
                  <h4>٤. الحصول على مفتاح API</h4>
                  <ol>
                    <li>انتقل إلى صفحة "خططي".</li>
                    <li>قم بإنشاء مفتاح API للتكامل.</li>
                  </ol>
                </div>

                <div class="step-item">
                  <h4>٥. الربط مع نظام فندقة</h4>
                  <ol>
                    <li>افتح صفحة "إعدادات التكامل" في نظام فندقة.</li>
                    <li>أدخل مفتاح API الذي تم إنشاؤه.</li>
                    <li>انقر على "ربط".</li>
                    <li>بمجرد الاتصال، يمكنك البدء في استخدام خدمات الرسائل النصية.</li>
                  </ol>
                </div>

                <div class="step-item">
                  <h4>٦. عرض سجل الرسائل</h4>
                  <ol>
                    <li>الوصول إلى صفحة "سجل الرسائل" على <a href="https://ms-sms.fandaqah.com" target="_blank">ms-sms.fandaqah.com</a></li>
                    <li>مراقبة استهلاك وسجل الرسائل النصية.</li>
                    <li>تنزيل التقارير التفصيلية عند الحاجة.</li>
                  </ol>
                </div>
              </div>

              <div class="important-note rtl">
                <i class="fas fa-exclamation-circle"></i>
                <p>{{__('يرجى العلم أن الخطوات المذكورة أعلاه خاصة باستخدام Fandaqah كاسم مرسل، وفي حالة الرغبة في تفعيل اسم مرسل خاص يرجى التواصل مع الدعم الفني')}}</p>
              </div>

            </div>
          </div>
        </div><!-- loading -->
      </sweet-modal>
      <!-- Unifonic Modal -->

      <sweet-modal ref="unifonicDisconnectModal" icon="warning" overlay-theme="dark" modal-theme="dark" title="Unifonic Integration">
        <div class="mt-5 mb-5 text-center" v-if="formLoading">
          <loader class="text-60 text-warning" width="40"/>
          <div class="mb-2 text-2xl block font-bold no-underline  text-warning">
            {{ __('Disconnecting')}}
          </div>
        </div>
        <div class="text-center" v-if="!formLoading">
          <a class="mb-2 text-2xl block font-bold no-underline text-warning">
            {{ __('Are you sure to disconnect?')}}
          </a>
          <button class=" btn btn-block btn btn-default btn-danger mt-2" @click="disconnect()">
            {{ __('DISCONNECT')}}
          </button>
        </div>
      </sweet-modal>

      <!-- Recharge Mobile Modal -->
      <!-- <sweet-modal ref="mobileRechargeModal" :enable-mobile-fullscreen="false" :pulse-on-block="false" blocking overlay-theme="dark" :title="__('Request to recharge mobile messages')" class="recharge_mobile_modal">
        <span>قيمة الرسالة الواحدة 12 هللة / اقل كمية للطلب هي 1000 ريال </span>
        <p>لطلب الرسائل ادخل الكمية المطلوبة </p>
        <div class="input_group">
          <input v-model="charge" type="tel">
          <label>قيمة الـ <p>1000</p> رسالة هو <i>140</i> ريال</label>
          <div class="wrong">الرصيد المطلوب اقل من الحد الادنى للطلب ، الحد الادنى هو 1000 رسالة</div>
        </div>input_group -->
        <!-- <button>إرسال الطلب</button> -->
      <!-- </sweet-modal> -->
      <!-- Recharge Mobile Modal -->

      <!-- fonic Info -->
      <!-- <sweet-modal ref="fonicInfoModal" :enable-mobile-fullscreen="false" :pulse-on-block="false" class="Fonic_Info_Modal" blocking overlay-theme="dark"  :title="__('Mobile messaging service')">
         <span>
           كيف تفعيل رسائل الجوال في فندقة؟
           <ul>
             <li>يمكنك الاشتراك فى خدمة رسيل فندقة </li>
             <li>يمكنك طلب شحن رصيد خاص بك من خلال مخاطبة خدمة العملاء في فندقة</li>
             <li>او الدخول لحسابك ثم الاعدادات وخدمة رسائل الجوال وطلب شحن رصيد</li>
           </ul>
         </span>
         <span>
           اسم المرسل ..
           <ul>
             <li>لتتمكن من استخدام اسم فندقك كمرسل لابد من إعتماد ذلك من خلال تعبئة نموذج " شهادة تعهد" وختمها و ارفاق السجل التجاري</li>
             <li>ومن ثم ارسالها لنا لنتمكن من إعتماد إضافة اسم المرسل للقائكة البيضاء وإعتماده من هيئة الاتصالات وتقنية المعلومات</li>
           </ul>
         </span>
         <embed src="/images/unifonic_ksa.pdf" type="application/pdf" width="800px" height="2100px">
      </sweet-modal> -->
      <!-- fonic Info -->

    </div><!-- integration_item -->
  </div><!-- integration_col -->
</template>

<script>
    import UnifonicLog from './log/UnifonicLog';
    export default {
        name: "fsms",
        components: {
            UnifonicLog
        },
        data() {
            return {
                data: null,
                jawaly: null,
                key: 'fsms',
                loading: true,
                local: Nova.config.local,
                formLoading: false,
                charge: 1000 ,
                enable_sms : Nova.app.currentTeam.enable_sms,
                // get sms link from env file
                sms_link: process.env.FSMS_URL,
                showGuide: false,
            }
        },
        watch: {
          charge(num) {
              this.charge = num.replace(/([٠١٢٣٤٥٦٧٨٩])|([۰۱۲۳۴۵۶۷۸۹])/g, (m, $1, $2) => m.charCodeAt(0) - ($1 ? 1632 : 1776))
              this.charge = this.charge.replace(/[^\d]/g,'')
          }
        },
        methods: {
            openLog() {
                this.$refs.unifonicLog.openLog()
            },
            open() {
                this.$refs.unifonicModal.open()
            },
            openDisconnect() {
                this.$refs.unifonicDisconnectModal.open()
            },
            openRecharge() {
                this.$refs.mobileRechargeModal.open()
            },
            openfonicinfo() {
                this.$refs.fonicInfoModal.open()
            },
            async send() {
              this.formLoading = true;
              this.loading = true;
              this.jawaly = null;
              await Nova.request()
                    .get('/nova-vendor/settings/integrations/Jawaly').then(response => {
                    this.jawaly = response.data.integration;
                })
                if (!this.data.fields.appSid) {
                  this.loading = false;
                  this.formLoading = false;
                    this.$toasted.error(this.__('Please fill all credentials '), {
                        duration: 3000
                    });
                    return;
                }

                if (this.jawaly) {
                  this.loading = false;
                  this.formLoading = false;
                    this.$toasted.error(this.__('Please first disconnect 4jawaly'), {
                        duration: 3000
                    });
                    return;
                }
                Nova.request()
                    .post('/nova-vendor/settings/register', {
                        key: 'fsms',
                        values: this.data.fields
                    }).then(response => {

                    if (response.data.success) {

                        this.data = response.data.settings
                        this.data.fields = JSON.parse(this.data.fields)
                        if (this.data.integration) {
                            this.data.fields = JSON.parse(this.data.integration.values)
                        }
                        this.$refs.unifonicModal.close()
                        this.loading = false;
                        this.formLoading = false;
                        this.$toasted.show(this.__('Fandaqah Integrated Successfully'), {type: 'success'})

                    } else {
                        this.loading = false;
                        this.formLoading = false;
                        this.$toasted.error(this.__('Fail to connect !'), {type: 'error'})
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
            disconnect() {
                this.formLoading = true
                Nova.request()
                    .post('/nova-vendor/settings/disconnect', {
                        key: 'fsms',
                    }).then(response => {
                    if (response.data.success) {
                        this.data = response.data.settings
                        this.data.fields = JSON.parse(this.data.fields)
                        this.$refs.unifonicDisconnectModal.close()
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

        },
        mounted() {
            Nova.request()
                .get('/nova-vendor/settings/integrations/fsms').then(response => {

                this.data = response.data
                this.data.fields = JSON.parse(this.data.fields)
                console.log(this.data)
                if (this.data.integration) {
                    this.data.fields = JSON.parse(this.data.integration.values)
                }
                this.loading = false;
            })
                .catch(error => {
                    this.$toasted.error(error, {
                        duration: 3000
                    });
                    this.loading = false;
                });

                // call check connection
                Nova.request()
                    .get('/nova-vendor/settings/checkConnection/fsms').then(response => {

                        //if response is false call disconnect function
                        if (!response.data) {
                            this.disconnect()
                        }
                })
                    .catch(error => {
                        this.$toasted.error(error, {
                            duration: 3000
                        });
                    });
        }
    }
</script>

<style lang="scss">
.Unifonic_Modal {
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
  .alert_unifonic {
    background: #fff3cd;
    border: 1px solid #ffeeba;
    color: #856404;
    text-align: center;
    padding: 15px;
    border-radius: 4px;
    font-size: 15px;
    margin: 0 auto 15px;
  } /* alert_unifonic */
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
  .integration-guide {
    margin-bottom: 30px;

    .how-to-use-btn {
      width: 100%;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      background: #f8fafc;
      border: 1px solid #e2e8f0;
      color: #4099de;
      font-weight: 600;
      margin-bottom: 15px;

      i {
        transition: transform 0.3s ease;
      }

      &:hover {
        background: #f1f5f9;
      }
    }

    .support-message {
      background: linear-gradient(135deg, #e8f4ff 0%, #f0f9ff 100%);
      border: 1px solid #bde0fe;
      border-radius: 8px;
      padding: 15px;
      margin-bottom: 20px;

      .support-content {
        display: flex;
        align-items: center;
        gap: 12px;

        i {
          font-size: 24px;
          color: #4099de;
          flex-shrink: 0;
        }

        p {
          margin: 0;
          color: #2d3748;
          font-size: 14px;
          line-height: 1.5;
        }
      }
    }

    .guide-content {
      max-height: 0;
      overflow: hidden;
      transition: max-height 0.5s ease-out;

      &.show {
        max-height: 3000px; // Adjust this value based on content
        transition: max-height 0.5s ease-in;
      }
    }

    .guide-section {
      margin-bottom: 30px;

      &.rtl {
        direction: rtl;
        text-align: right;
      }
    }

    .guide-title {
      font-size: 1.5rem;
      font-weight: bold;
      color: #2d3748;
      margin-bottom: 20px;
      text-align: center;
    }

    .step-item {
      margin-bottom: 20px;
      padding: 15px;
      background: #f8fafc;
      border-radius: 8px;
      border: 1px solid #e2e8f0;

      h4 {
        color: #4099de;
        font-size: 1.1rem;
        margin-bottom: 10px;
        font-weight: 600;
      }

      ol {
        margin: 0;
        padding-left: 20px;

        li {
          margin-bottom: 8px;
          color: #4a5568;
          line-height: 1.5;

          &.note {
            color: #ed8936;
            font-style: italic;
          }
        }
      }

      a {
        color: #4099de;
        text-decoration: none;

        &:hover {
          text-decoration: underline;
        }
      }
    }

    .divider {
      margin: 30px 0;
      border: 0;
      border-top: 1px solid #e2e8f0;
    }

    .important-note {
      background: #FEF2F2;
      border-left: 4px solid #EF4444;
      padding: 15px;
      margin: 20px 0;
      display: flex;
      align-items: flex-start;
      gap: 12px;

      &.rtl {
        border-left: none;
        border-right: 4px solid #EF4444;
        text-align: right;
        direction: rtl;
      }

      i {
        color: #EF4444;
        font-size: 20px;
        margin-top: 2px;
      }

      p {
        margin: 0;
        color: #991B1B;
        font-size: 14px;
        line-height: 1.6;
        font-weight: 500;
      }
    }
  }
}
.recharge_mobile_modal {
  .sweet-content {
    overflow: auto;
    max-height: 500px;
    display: block;
    scrollbar-width: thin;
    scrollbar-color: #ccc #f5f5f5;
    &::-webkit-scrollbar {width: 6px;}
    &::-webkit-scrollbar-track {background: #f5f5f5;}
    &::-webkit-scrollbar-thumb {background: #ccc;}
    &::-webkit-scrollbar-thumb:window-inactive {background: #f5f5f5;}
  } /* sweet-content */
  span {
    display: block;
    font-size: 16px;
    color: #000;
    margin: 0 auto 5px;
    text-align: center;
  } /* span */
  p {
    display: block;
    font-size: 16px;
    color: #000;
    margin: 0 auto 5px;
    text-align: center;
  } /* p */
  .input_group {
    margin: 30px auto;
    display: table;
    text-align: center;
    input {
      height: 40px;
      padding: 0 10px;
      color: #000;
      font-size: 20px;
      border: 1px solid #dddddd;
      background: #fafafa;
      min-width: 150px;
      max-width: 100%;
      display: block;
      margin: 0 auto;
      text-align: center !important;
      padding: 0 !important;
    } /* input */
    label {
      display: block;
      font-size: 15px;
      margin: 5px auto 0;
      p {
        display: inline-block;
        margin: 0 auto;
        font-weight: bold;
        font-size: inherit;
      } /* p */
      i {
        font-style: normal;
        display: inline-block;
        font-weight: bold;
        font-size: inherit;
      } /* i */
    } /* label */
    .wrong {
      display: block;
      margin: 5px auto 0;
      color: red;
      font-size: 15px;
    } /* wrong */
  } /* input_group */
  button {
    background: #4099de;
    border-radius: 5px;
    border: 1px solid #4099de;
    min-width: 100px;
    height: 35px;
    line-height: 35px;
    font-size: 15px;
    padding: 0 15px;
    color: #ffffff;
    width: 100%;
    -webkit-transition: all 0.2s ease-in-out;
    transition: all 0.2s ease-in-out;
    &:hover {
      background: #0071C9;
      border-color: #0071C9;
    } /* hover */
  } /* button */
} /* recharge_mobile_modal */

.Fonic_Info_Modal {
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
  embed {
    width: 100% !important;
    height: 600px !important;
  } /* embed */
} /* Fonic_Info_Modal */
</style>
