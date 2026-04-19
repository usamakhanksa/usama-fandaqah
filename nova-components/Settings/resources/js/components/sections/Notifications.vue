<template>
  <div>
    <nav v-if="crumbs.length">
      <ul class="breadcrumbs">
        <li class="breadcrumbs__item" v-for="crumb in crumbs" v-if="crumb.text != false">
          <router-link :to="crumb.to">{{ __(crumb.text) }}</router-link>
        </li>
      </ul>
    </nav>
    <heading class="mb-6">{{settings.name}}</heading>
    <div id="notification_page">
      <div class="block">
        <div class="title">{{__('Management alerts')}}</div>
        <div class="content">
          <div class="col_item">
            <div class="top_row">
              <span>{{__('Send an alert when a New Reservation')}}</span>
              <label>
                <input type="checkbox">
                <span class="checkmark"></span>
                <p>{{__('Email')}}</p>
              </label>
              <label>
                <input type="checkbox">
                <span class="checkmark"></span>
                <p>{{__('sms')}}</p>
              </label>
            </div><!-- end top_row -->
            <div class="bottom_row">
              <textarea name="" id="" cols="30" rows="10" disabled="disabled">
                هناك حقيقة مثبتة منذ زمن طويل وهي أن المحتوى المقروء لصفحة ما سيلهي القارئ عن التركيز على الشكل الخارجي للنص أو شكل توضع الفقرات في الصفحة التي يقرأها.
              </textarea>
            </div><!-- end bottom_row -->
          </div><!-- end col_item -->
          <div class="col_item">
            <div class="top_row">
              <span>{{__('Send an alert when a Reservation Delete')}}</span>
              <label>
                <input type="checkbox">
                <span class="checkmark"></span>
                <p>{{__('Email')}}</p>
              </label>
              <label>
                <input type="checkbox">
                <span class="checkmark"></span>
                <p>{{__('sms')}}</p>
              </label>
            </div><!-- end top_row -->
            <div class="bottom_row">
              <textarea name="" id="" cols="30" rows="10" disabled="disabled">
                هناك حقيقة مثبتة منذ زمن طويل وهي أن المحتوى المقروء لصفحة ما سيلهي القارئ عن التركيز على الشكل الخارجي للنص أو شكل توضع الفقرات في الصفحة التي يقرأها.
              </textarea>
            </div><!-- end bottom_row -->
          </div><!-- end col_item -->
          <div class="col_item">
            <div class="top_row">
              <span>{{__('Send an alert when a Reservation Cancel')}}</span>
              <label>
                <input type="checkbox">
                <span class="checkmark"></span>
                <p>{{__('Email')}}</p>
              </label>
              <label>
                <input type="checkbox">
                <span class="checkmark"></span>
                <p>{{__('sms')}}</p>
              </label>
            </div><!-- end top_row -->
            <div class="bottom_row">
              <textarea name="" id="" cols="30" rows="10" disabled="disabled">
                هناك حقيقة مثبتة منذ زمن طويل وهي أن المحتوى المقروء لصفحة ما سيلهي القارئ عن التركيز على الشكل الخارجي للنص أو شكل توضع الفقرات في الصفحة التي يقرأها.
              </textarea>
            </div><!-- end bottom_row -->
          </div><!-- end col_item -->
          <div class="col_item">
            <div class="top_row">
              <span>{{__('Daily brief report')}}</span>
              <label>
                <input type="checkbox">
                <span class="checkmark"></span>
                <p>{{__('Email')}}</p>
              </label>
              <label>
                <input type="checkbox">
                <span class="checkmark"></span>
                <p>{{__('sms')}}</p>
              </label>
            </div><!-- end top_row -->
            <div class="bottom_row">
              <textarea name="" id="" cols="30" rows="10" disabled="disabled">
                هناك حقيقة مثبتة منذ زمن طويل وهي أن المحتوى المقروء لصفحة ما سيلهي القارئ عن التركيز على الشكل الخارجي للنص أو شكل توضع الفقرات في الصفحة التي يقرأها.
              </textarea>
            </div><!-- end bottom_row -->
          </div><!-- end col_item -->
          <div class="col_item">
            <div class="top_row">
              <span>{{__('Email him to notify him of alerts')}}</span>
              <div class="add_area">
                <div class="col_add">
                  <input type="email" :placeholder="__('Email')">
                  <button type="button" class="add_mail">{{__('Add')}}</button>
                </div><!-- end col_add -->
                <div class="col_add">
                  <div class="mail_active">demo@demo.com</div>
                  <button type="button" class="clear_mail">{{__('delete')}}</button>
                </div><!-- end col_add -->
              </div><!-- end add_area -->
            </div><!-- end top_row -->
          </div><!-- end col_item -->
          <div class="col_item">
            <div class="top_row">
              <span>{{__('mobile number to reach him alerts')}}</span>
              <div class="add_area">
                <div class="col_add">
                  <vue-tel-input
                    defaultCountry="SA"
                    :required="true"
                    :enabledFlags="true"
                    name="phone"
                    :placeholder="__('Enter Phone Number')"
                    :inputOptions="{ showDialCode: false, tabindex: 0 }"
                  ></vue-tel-input>
                  <button type="button" class="add_mail">{{__('Add')}}</button>
                </div><!-- end col_add -->
                <div class="col_add">
                  <div class="mail_active">+966554555555</div>
                  <button type="button" class="clear_mail">{{__('delete')}}</button>
                </div><!-- end col_add -->
              </div><!-- end add_area -->
            </div><!-- end top_row -->
          </div><!-- end col_item -->
        </div><!-- end content -->
      </div><!-- end block -->
      <div class="block">
        <div class="title">{{__('Member alerts')}}</div>
        <div class="content">
          <div class="col_item">
            <div class="top_row">
              <span>{{__('Automatic message when logging in')}}</span>
              <label>
                <input type="checkbox">
                <span class="checkmark"></span>
                <p>{{__('Email')}}</p>
              </label>
              <label>
                <input type="checkbox">
                <span class="checkmark"></span>
                <p>{{__('sms')}}</p>
              </label>
            </div><!-- end top_row -->
            <div class="bottom_row">
              <textarea name="" id="" cols="30" rows="10" disabled="disabled">
                هناك حقيقة مثبتة منذ زمن طويل وهي أن المحتوى المقروء لصفحة ما سيلهي القارئ عن التركيز على الشكل الخارجي للنص أو شكل توضع الفقرات في الصفحة التي يقرأها.
              </textarea>
              <div class="smalltxt">
                <small>{{__('Characters')}}  : 50</small>
                <small>{{__('Messages')}}  : 1</small>
              </div><!-- end smalltxt -->
              <div class="another_choose">
                <label>
                  <input type="checkbox">
                  <span class="checkmark"></span>
                  <p>{{__('Contract Number')}}</p>
                </label>
                <label>
                  <input type="checkbox">
                  <span class="checkmark"></span>
                  <p>{{__('Date')}}</p>
                </label>
                <label>
                  <input type="checkbox">
                  <span class="checkmark"></span>
                  <p>{{__('Unit name')}}</p>
                </label>
                <label>
                  <input type="checkbox">
                  <span class="checkmark"></span>
                  <p>{{__('Invoice Amount')}}</p>
                </label>
              </div><!-- end another_choose -->
            </div><!-- end bottom_row -->
          </div><!-- end col_item -->
          <div class="col_item">
            <div class="top_row">
              <span>{{__('Automatic message upon check-out')}}</span>
              <label>
                <input type="checkbox">
                <span class="checkmark"></span>
                <p>{{__('Email')}}</p>
              </label>
              <label>
                <input type="checkbox">
                <span class="checkmark"></span>
                <p>{{__('sms')}}</p>
              </label>
            </div><!-- end top_row -->
            <div class="bottom_row">
              <textarea name="" id="" cols="30" rows="10" disabled="disabled">
                هناك حقيقة مثبتة منذ زمن طويل وهي أن المحتوى المقروء لصفحة ما سيلهي القارئ عن التركيز على الشكل الخارجي للنص أو شكل توضع الفقرات في الصفحة التي يقرأها.
              </textarea>
              <div class="smalltxt">
                <small>{{__('Characters')}}  : 50</small>
                <small>{{__('Messages')}}  : 1</small>
              </div><!-- end smalltxt -->
              <div class="another_choose">
                <label>
                  <input type="checkbox">
                  <span class="checkmark"></span>
                  <p>{{__('Contract Number')}}</p>
                </label>
                <label>
                  <input type="checkbox">
                  <span class="checkmark"></span>
                  <p>{{__('Date')}}</p>
                </label>
                <label>
                  <input type="checkbox">
                  <span class="checkmark"></span>
                  <p>{{__('Unit name')}}</p>
                </label>
                <label>
                  <input type="checkbox">
                  <span class="checkmark"></span>
                  <p>{{__('Invoice Amount')}}</p>
                </label>
              </div><!-- end another_choose -->
            </div><!-- end bottom_row -->
          </div><!-- end col_item -->
          <div class="col_item">
            <div class="top_row">
              <span>{{__('Message when confirming the reservation')}}</span>
              <label>
                <input type="checkbox">
                <span class="checkmark"></span>
                <p>{{__('Email')}}</p>
              </label>
              <label>
                <input type="checkbox">
                <span class="checkmark"></span>
                <p>{{__('sms')}}</p>
              </label>
            </div><!-- end top_row -->
            <div class="bottom_row">
              <textarea name="" id="" cols="30" rows="10" disabled="disabled">
                هناك حقيقة مثبتة منذ زمن طويل وهي أن المحتوى المقروء لصفحة ما سيلهي القارئ عن التركيز على الشكل الخارجي للنص أو شكل توضع الفقرات في الصفحة التي يقرأها.
              </textarea>
              <div class="smalltxt">
                <small>{{__('Characters')}}  : 50</small>
                <small>{{__('Messages')}}  : 1</small>
              </div><!-- end smalltxt -->
              <div class="another_choose">
                <label>
                  <input type="checkbox">
                  <span class="checkmark"></span>
                  <p>{{__('Contract Number')}}</p>
                </label>
                <label>
                  <input type="checkbox">
                  <span class="checkmark"></span>
                  <p>{{__('Date')}}</p>
                </label>
                <label>
                  <input type="checkbox">
                  <span class="checkmark"></span>
                  <p>{{__('Unit name')}}</p>
                </label>
                <label>
                  <input type="checkbox">
                  <span class="checkmark"></span>
                  <p>{{__('Invoice Amount')}}</p>
                </label>
              </div><!-- end another_choose -->
            </div><!-- end bottom_row -->
          </div><!-- end col_item -->
        </div><!-- end content -->
      </div><!-- end block -->
      <div class="footer_buttons bg-30 flex p-4 justify-between">
        <button type="submit" class="btn bg-blue-500 hover:bg-blue-400 text-white py-2 px-8">{{ translations['save_settings'] }}</button>
        <button type="button" @click="goBack" class="btn bg-gray-600 hover:bg-gray-500 text-white py-2 px-8">{{ __('Back') }}</button>
      </div><!-- end footer_buttons -->
    </div><!-- end notification_page -->





    <div class="card">
      <form @submit.prevent="updateSettings">
        <div v-for="setting in settings.items">
          <!-- <template v-if="setting.field.attribute === 'alert_new_reservation' || setting.field.attribute === 'alert_reservation_delete' || setting.field.attribute === 'alert_reservation_cancel' || setting.field.attribute === 'alert_daily_report'">
            <notification-check-box
              v-bind:class="`form-${setting.field.textAlign}`"
              v-bind:field="setting.field"
              v-bind:v-model="setting.value"
              ref="components"
            />
          </template>
          <template v-else>
              <component
                  v-bind:is="'form-' + setting.field.component"
                  v-bind:class="`form-${setting.field.textAlign}`"
                  v-bind:field="checkValue(setting.field)"
                  v-bind:v-model="setting.value"
                  ref="components"
              />
          </template> -->
        </div>
      </form>
    </div>
  </div>
</template>

<script>
    import CustomNotificationCheckbox from "./CustomNotificationCheckbox";
    export default {
        components:{
            'notification-check-box' : CustomNotificationCheckbox
        },
        data() {
            return {
                settings: [],
                installed: false,
                reset: true,
                groupName: 'notification',
                crumbs: [],
            }
        },
        mounted() {
            this.crumbs = [
                {
                    text: 'Home',
                    to: '/dashboards/main',
                },
                {
                    text: 'Settings',
                    to: '/settings',
                },
                {
                    text: 'Notifications',
                    to: '#',
                }
            ]
            this.getSettings();
        },
        updated() {
            let children = this.$refs.tabs && this.$refs.tabs.$children ? this.$refs.tabs.$children.length : 0;
            if (children > 0 && (this.$refs.tabs.activeTabHash === '' || this.reset === true)) {
                this.reset = false;
                let hash = this.$refs.tabs.tabs[0].hash;
                this.$refs.tabs.selectTab(hash);
            }
        },
        computed: {
            config: function () {
                return Nova.config.settings_tool.config;
            },
            translations: function () {
                return Nova.config.settings_tool.translations;
            }
        },
        methods: {
            goBack() {
                this.$router.push({path: '/settings'})
            },
            checkValue(value) {
                // if (value.value != null && value.value != [])
                //     value.value = JSON.parse(value.value)
                return value;
            },
            getSettings() {
                Nova.request()
                    .get('/nova-vendor/settings/get/' + this.groupName).then(response => {

                    this.settings = response.data;


                })
                    .catch(error => {
                        this.$toasted.error(this.translations['load_error'], {
                            duration: 3000
                        });
                    });
            },
            updateSettings() {
                let values = this.obtainValues();
                Nova.request()
                    .put("/nova-vendor/settings/update", {values: values, group: this.groupName})
                    .then(response => {
                        // console.log("response.data", response.data)
                        this.settings = response.data.settings
                        let message = response.data.message && response.data.message !== ''
                            ? response.data.message
                            : this.translations['save_success'];
                        this.$toasted.success(message, {
                            duration: 3000
                        })
                    })
                    .catch(error => {
                        this.$toasted.error(this.translations['save_error'], {
                            duration: 3000
                        });
                    });
            },
            obtainValues() {
                let components = this.$refs.components ? this.$refs.components : [];
                let values = {};
                for (let index in components) {
                    values[components[index].fieldAttribute] = components[index].value;
                }
                return values;
            }
        },
    }
</script>

<style lang="scss">
#notification_page {
  background: #ffffff;
  border-radius: 5px;
  overflow: hidden;
  box-shadow: 0 1px 3px 0 rgba(0,0,0,0.1),0 1px 2px 0 rgba(0,0,0,0.06);
  .block {
    .title {
      font-size: 17px;
      background: #F6FBFF;
      padding: 10px;
      color: #000;
      border-bottom: 1px solid #E3E7EB;
    } /* title */
    .content {
      width: 100%;
      padding: 0;
      .col_item {
        padding: 20px;
        border-bottom: 1px solid #eee;
        width: 100%;
        @media (min-width: 320px) and (max-width: 767px) {
          padding: 10px;
        }
        .top_row {
          display: flex;
          flex-wrap: wrap;
          @media (min-width: 320px) and (max-width: 767px) {
            display: block;
          }
          span {
            display: block;
            width: 25%;
            padding: 0 10px;
            @media (min-width: 320px) and (max-width: 767px) {
              width: 100%;
              margin: 0 auto 10px;
              padding: 0;
            }
            @media (min-width: 768px) and (max-width: 991px) {
              width: 40%;
            }
          } /* span */
          label {
            display: block;
            width: 20%;
            position: relative;
            padding-right: 30px;
            cursor: pointer;
            color: #7E8790;
            line-height: 40px;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
            -webkit-transition: all 0.2s ease-in-out;
            -moz-transition: all 0.2s ease-in-out;
            -o-transition: all 0.2s ease-in-out;
            transition: all 0.2s ease-in-out;
            [dir="ltr"] & {
              padding: 0 0 0 30px;
            } /* rtl */
            @media (min-width: 320px) and (max-width: 767px) {
              display: inline-block;
              width: auto;
              margin: 0 0 0 20px;
              [dir="ltr"] & {
                margin: 0 20px 0 0;
              } /* rtl */
            }
            &:hover {
              .checkmark {background: #e8e8e8;}
              p {color: #6C7180;}
            } /* hover */
            input {
              position: absolute;
              opacity: 0;
              cursor: pointer;
              height: 0;
              width: 0;
              &:checked ~ {
                .checkmark {
                  background: #0A80D8;
                  &::after {
                    opacity: 1;
                    visibility: visible;
                    -webkit-transform: scale(1);
                    -moz-transform: scale(1);
                    -o-transform: scale(1);
                    transform: scale(1);
                  } /* after */
                } /* checkmark */
                p {color: #0A80D8;}
              } /* checked */
            } /* input */
            .checkmark {
              position: absolute;
              top: -1px;
              right: 0;
              height: 20px;
              width: 20px;
              background-color: #fcfcfc;
              border: 1px solid #e8e8e8;
              -webkit-transition: all 0.2s ease-in-out;
              -moz-transition: all 0.2s ease-in-out;
              -o-transition: all 0.2s ease-in-out;
              transition: all 0.2s ease-in-out;
              [dir="ltr"] & {
                right: auto;
                left: 0;
              } /* rtl */
              &::after {
                content: "";
                background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='22.681' height='16.867' viewBox='0 0 22.681 16.867'%3E%3Cpath d='M143.914,64.664a1.147,1.147,0,0,0-1.622,0L128.526,78.392,123.559,73a1.147,1.147,0,0,0-1.688,1.554l5.775,6.272a1.146,1.146,0,0,0,.82.37h.025a1.149,1.149,0,0,0,.81-.335l14.611-14.572A1.147,1.147,0,0,0,143.914,64.664Z' transform='translate(-121.568 -64.327)' fill='%23fff'/%3E%3C/svg%3E");
                background-size: 13px;
                background-repeat: no-repeat;
                background-position: center 4px;
                position: absolute;
                top: 0;
                right: 0;
                width: 20px;
                height: 20px;
                line-height: 18px;
                text-align: center;
                font-size: 11px;
                color: #ffffff;
                opacity: 0;
                visibility: hidden;
                -webkit-transform: scale(0);
                -moz-transform: scale(0);
                -o-transform: scale(0);
                transform: scale(0);
                -webkit-transition: all 0.2s ease-in-out;
                -moz-transition: all 0.2s ease-in-out;
                -o-transition: all 0.2s ease-in-out;
                transition: all 0.2s ease-in-out;
                [dir="ltr"] & {
                  right: auto;
                  left: 0;
                } /* rtl */
              } /* after */
            } /* checkmark */
            p {
              display: block;
              line-height: 20px;
              font-size: 16px;
              color: #000;
              -webkit-transition: all 0.2s ease-in-out;
              -moz-transition: all 0.2s ease-in-out;
              -o-transition: all 0.2s ease-in-out;
              transition: all 0.2s ease-in-out;
            } /* p */
          } /* label */
        } /* top_row */
        .bottom_row {
          width: 40%;
          margin: 20px 25% 0 0;
          [dir="ltr"] & {
            margin: 20px 0 0 25%;
          } /* rtl */
          @media (min-width: 320px) and (max-width: 767px) {
            width: auto;
            margin: 15px auto 0;
          }
          @media (min-width: 768px) and (max-width: 991px) {
            width: 60%;
            margin: 20px 40% 0 0;
            [dir="ltr"] & {
              margin: 20px 0 0 40%;
            } /* rtl */
          }
          textarea {
            width: 100%;
            border: 1px solid #BBB;
            padding: 10px;
            font-size: 15px;
            text-align: initial;
            border-radius: 4px;
            white-space: normal;
            line-height: 25px;
            height: 90px;
            background: #ffffff;
            color: #000;
            [dir="ltr"] & {
              text-align: left;
            } /* rtl */
            @media (min-width: 320px) and (max-width: 767px) {
              padding: 5px;
              font-size: 14px;
            }
          } /* textarea */
          .smalltxt {
            display: flex;
            justify-content: space-between;
            margin: 5px auto 0;
            color: #6E6E6E;
            font-size: 15px;
          } /* smalltxt */
          .another_choose {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            margin: 10px auto 0;
            label {
              display: block;
              width: 33.3333%;
              position: relative;
              padding-right: 30px;
              cursor: pointer;
              color: #7E8790;
              line-height: 40px;
              margin: 0 0 15px;
              -webkit-user-select: none;
              -moz-user-select: none;
              -ms-user-select: none;
              user-select: none;
              -webkit-transition: all 0.2s ease-in-out;
              -moz-transition: all 0.2s ease-in-out;
              -o-transition: all 0.2s ease-in-out;
              transition: all 0.2s ease-in-out;
              [dir="ltr"] & {
                padding: 0 0 0 30px;
              } /* rtl */
              &:hover {
                .checkmark {background: #e8e8e8;}
                p {color: #6C7180;}
              } /* hover */
              input {
                position: absolute;
                opacity: 0;
                cursor: pointer;
                height: 0;
                width: 0;
                &:checked ~ {
                  .checkmark {
                    background: #0A80D8;
                    &::after {
                      opacity: 1;
                      visibility: visible;
                      -webkit-transform: scale(1);
                      -moz-transform: scale(1);
                      -o-transform: scale(1);
                      transform: scale(1);
                    } /* after */
                  } /* checkmark */
                  p {color: #0A80D8;}
                } /* checked */
              } /* input */
              .checkmark {
                position: absolute;
                top: -1px;
                right: 0;
                height: 20px;
                width: 20px;
                background-color: #fcfcfc;
                border: 1px solid #e8e8e8;
                -webkit-transition: all 0.2s ease-in-out;
                -moz-transition: all 0.2s ease-in-out;
                -o-transition: all 0.2s ease-in-out;
                transition: all 0.2s ease-in-out;
                [dir="ltr"] & {
                  right: auto;
                  left: 0;
                } /* rtl */
                &::after {
                  content: "";
                  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='22.681' height='16.867' viewBox='0 0 22.681 16.867'%3E%3Cpath d='M143.914,64.664a1.147,1.147,0,0,0-1.622,0L128.526,78.392,123.559,73a1.147,1.147,0,0,0-1.688,1.554l5.775,6.272a1.146,1.146,0,0,0,.82.37h.025a1.149,1.149,0,0,0,.81-.335l14.611-14.572A1.147,1.147,0,0,0,143.914,64.664Z' transform='translate(-121.568 -64.327)' fill='%23fff'/%3E%3C/svg%3E");
                  background-size: 13px;
                  background-repeat: no-repeat;
                  background-position: center 4px;
                  position: absolute;
                  top: 0;
                  right: 0;
                  width: 20px;
                  height: 20px;
                  line-height: 18px;
                  text-align: center;
                  font-size: 11px;
                  color: #ffffff;
                  opacity: 0;
                  visibility: hidden;
                  -webkit-transform: scale(0);
                  -moz-transform: scale(0);
                  -o-transform: scale(0);
                  transform: scale(0);
                  -webkit-transition: all 0.2s ease-in-out;
                  -moz-transition: all 0.2s ease-in-out;
                  -o-transition: all 0.2s ease-in-out;
                  transition: all 0.2s ease-in-out;
                  [dir="ltr"] & {
                    right: auto;
                    left: 0;
                  } /* rtl */
                } /* after */
              } /* checkmark */
              p {
                display: block;
                line-height: 20px;
                font-size: 16px;
                color: #000;
                -webkit-transition: all 0.2s ease-in-out;
                -moz-transition: all 0.2s ease-in-out;
                -o-transition: all 0.2s ease-in-out;
                transition: all 0.2s ease-in-out;
              } /* p */
            } /* label */
          } /* another_choose */
        } /* bottom_row */
      } /* bottom_row */
      .add_area {
        width: 40%;
        @media (min-width: 320px) and (max-width: 767px) {
          width: auto;
        }
        @media (min-width: 768px) and (max-width: 991px) {
          width: 60%;
        }
        .col_add {
          display: flex;
          justify-content: space-between;
          margin: 0 auto 15px;
          input {
            display: block;
            width: 75%;
            height: 40px;
            border: 1px solid #cecece !important;
            padding: 0 10px;
            line-height: 40px;
            font-size: 16px;
            color: #000;
            border-radius: 4px;
            @media (min-width: 320px) and (max-width: 767px) {
              width: 70%;
            }
          } /* input */
          .vue-tel-input {
            display: flex;
            width: 75%;
            height: 40px;
            border: 1px solid #cecece !important;
            line-height: 40px;
            font-size: 16px;
            color: #000;
            border-radius: 4px;
            padding: 0;
            text-align: right;
            align-items: center;
            box-shadow: none;
            [dir="ltr"] & {
              text-align: left;
            } /* rtl */
            @media (min-width: 320px) and (max-width: 767px) {
              width: 70%;
            }
            .dropdown {
              padding: 0;
              width: 70px;
              background: #fafafa;
              height: 38px;
              border-left: 1px solid #cecece;
              border-radius: 0 4px 4px 0;
              [dir="ltr"] & {
                border-right: 1px solid #cecece;
                border-left: none;
                border-radius: 4px 0 0 4px;
              } /* rtl */
              @media (min-width: 320px) and (max-width: 767px) {
                width: 25%;
                padding: 0;
              }
              span.selection {
                display: flex;
                height: 40px;
                justify-content: center;
                align-items: center;
                width: auto;
                margin: 0 auto;
                .iti-flag {
                  margin: 0;
                } /* iti-flag */
                span.dropdown-arrow {
                  width: auto;
                  margin: 0;
                } /* dropdown-arrow */
              } /* selection */
              ul {
                margin: 0 auto;
                left: auto;
                right: 0;
                width: auto;
                min-width: 210px;
                top: 43px;
                max-width: 386px;
                border-radius: 4px;
                text-align: inherit;
                scrollbar-width: thin;
                scrollbar-color: #ccc #f5f5f5;
                &::-webkit-scrollbar {width: 6px;}
                &::-webkit-scrollbar-track {background: #f5f5f5;}
                &::-webkit-scrollbar-thumb {background: #ccc;}
                &::-webkit-scrollbar-thumb:window-inactive {background: #f5f5f5;}
                [dir="ltr"] & {
                  left: 0;
                  right: auto;
                } /* rtl */
                @media (min-width: 320px) and (max-width: 767px) {
                  width: 319px;
                }
                li {
                  direction: rtl;
                  display: flex;
                  align-items: center;
                  justify-content: flex-start;
                  padding: 3px 10px;
                  line-height: normal;
                  font-weight: normal;
                  color: #000;
                  [dir="ltr"] & {
                    direction: ltr;
                  } /* rtl */
                  .iti-flag {
                    margin: 0;
                  } /* iti-flag */
                  strong {
                    display: block;
                    font-weight: normal;
                    font-size: 15px;
                    margin: 0 5px;
                  } /* strong */
                  span {
                    direction: ltr;
                    color: #666;
                  } /* span */
                } /* li */
              } /* ul */
            } /* dropdown */
            input {
              width: 75%;
              padding: 0 10px;
              height: 38px;
              border: none !important;
            } /* input */
          } /* vue-tel-input */
          .mail_active {
            display: block;
            width: 75%;
            height: 40px;
            border: 1px solid #F7F7F7;
            padding: 0 10px;
            line-height: 40px;
            font-size: 16px;
            color: #000;
            border-radius: 4px;
            background: #F7F7F7;
            cursor: not-allowed;
            direction: ltr;
            text-align: right;
            [dir="ltr"] & {
              text-align: left;
            } /* rtl */
            @media (min-width: 320px) and (max-width: 767px) {
              width: 70%;
            }
          } /* mail_active */
          button {
            width: 20%;
            height: 40px;
            border-radius: 4px;
            line-height: 40px;
            font-size: 17px;
            display: block;
            color: #ffffff;
            @media (min-width: 320px) and (max-width: 767px) {
              width: 27%;
            }
            &.add_mail {
              background: #4099DE;
              &:hover {background: #2C85CA;}
            } /* add_mail */
            &.clear_mail {
              background: #DE4040;
              &:hover {background: #CF3131;}
            } /* add_mail */
          } /* button */
        } /* col_add */
      } /* add_area */
    } /* content */
  } /* block */
} /* notification_page */
</style>
