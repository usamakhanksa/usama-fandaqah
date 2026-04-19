<template>
    <div>
        <div class="flex w-full mb-4">
            <nav v-if="crumbs.length">
                <ul class="breadcrumbs">
                    <li class="breadcrumbs__item" v-for="(crumb,i) in crumbs" :key="i">
                        <router-link :to="crumb.to">{{ __(crumb.text) }}</router-link>
                    </li>
                </ul>
            </nav>
        </div>
        <card id="website_phrases">
            <div class="title-card title-card p-3 bg-gray-200 text-xl rounded-lg rounded-b-none border-b-2  border-gray-300">{{__('General Website Settings')}}</div>
            <div class="card-content">
                <form @submit.prevent="updateContact">

                      <div class="form-left">
                        <div class="flex border-b border-40">
                            <div class="flex border-b border-40 w-full">
                                <div class="w-1/3 py-6 px-8">
                                    <label for="website_domain" class="inline-block text-80 pt-2 leading-tight mb-2">{{__('Show In Booking Engine')}}</label>
                                </div>
                                <div class="py-6 px-8 w-1/2">
                                    <label class="switch">
                                        <input v-model="show_in_booking_engine" type="checkbox">
                                        <span class="slider round"></span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-left">
                        <div class="flex border-b border-40">
                            <div class="flex border-b border-40 w-full">
                                <div class="w-1/3 py-6 px-8">
                                    <label for="website_domain" class="inline-block text-80 pt-2 leading-tight mb-2">{{__('Booking for one day only')}}</label>
                                </div>
                                <div class="py-6 px-8 w-1/2">
                                    <label class="switch">
                                        <input v-model="enable_part_time_booking" type="checkbox">
                                        <span class="slider round"></span>
                                    </label>
                                    <br>
                                    <small class="text-gray-500">{{__('When enabled , guest will be able to select only one day from calendar')}}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                        <div class="form-left">
                        <div class="flex border-b border-40">
                            <div class="flex border-b border-40 w-full">
                                <div class="w-1/3 py-6 px-8">
                                    <label for="website_domain" class="inline-block text-80 pt-2 leading-tight mb-2">{{__('Enable SmS')}}</label>
                                </div>
                                <div class="py-6 px-8 w-1/2">
                                    <label class="switch">
                                        <input v-model="Enable_SmS" type="checkbox">
                                        <span class="slider round"></span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-left">
                        <div class="flex border-b border-40">
                            <div class="flex border-b border-40 w-full">
                                <div class="w-1/3 py-6 px-8">
                                    <label  class="inline-block text-80 pt-2 leading-tight mb-2">{{__('Deposit Amount Required')}}</label>
                                </div>
                                <div class="py-6 px-8 w-1/2">
                                    <div class="textarea_area">
                                        <select v-model="deposit_percentage">
                                            <option value="100" selected>100%</option>
                                            <option value="75">75%</option>
                                            <option value="50">50%</option>
                                            <option value="25">25%</option>
                                        </select>
                                    </div><!-- textarea_area -->
                                </div>
                            </div>
                        </div>
                    </div>



                    <div class="form-left">
                        <div class="flex border-b border-40">
                            <div class="flex border-b border-40 w-full">
                                <div class="w-1/3 py-6 px-8">
                                    <label for="contact_phone" class="inline-block text-80 pt-2 leading-tight mb-2">{{__('Contact Email')}}</label>
                                </div>
                                <div class="py-6 px-8 w-1/2">
                                    <input id="contact_email" v-model="contact_email" type="text" class="inline-block form-control form-input form-input-bordered text-lg text-90 w-full">
                                    <small class="text-gray-500">{{__('Email shall be provided to enable contact us page in online website')}}</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-left">
                        <div class="flex border-b border-40">
                            <div class="flex border-b border-40 w-full">
                                <div class="w-1/3 py-6 px-8">
                                    <label for="contact_phone" class="inline-block text-80 pt-2 leading-tight mb-2">{{__('Contact Phone')}}</label>
                                </div>
                                <div class="py-6 px-8 w-1/2">
                                    <input id="contact_phone" v-model="contact_phone" type="text" class="inline-block form-control form-input form-input-bordered text-lg text-90 w-full">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-left">
                        <div class="flex border-b border-40">
                            <div class="flex border-b border-40 w-full">
                                <div class="w-1/3 py-6 px-8">
                                    <label for="whatsapp_number" class="inline-block text-80 pt-2 leading-tight mb-2">{{__('Whats App Number')}}</label>
                                </div>
                                <div class="py-6 px-8 w-1/2">
                                    <input id="whatsapp_number" v-model="whatsapp_number" type="text" class="inline-block form-control form-input form-input-bordered text-lg text-90 w-full">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-left">
                        <div class="flex border-b border-40">
                            <div class="flex border-b border-40 w-full">
                                <div class="w-1/3 py-6 px-8">
                                    <label for="contact_phone" class="inline-block text-80 pt-2 leading-tight mb-2">{{__('Available to book from')}}</label>
                                </div>
                                <div class="py-6 px-8 w-1/2">
                                     <vcc-date-picker
                                        :input-props='{readonly: true}'
                                        mode='single'
                                        :value="new Date()"
                                        show-caps
                                        v-model="minimum_calendar_date"
                                        :locale="locale"
                                        :min-date="new Date()"
                                        :masks="{ dayPopover : 'WWW, MMM D, YYYY' , weekdays: 'WWW' }"
                                        :popover="{ placement: 'bottom', visibility: 'click' }"
                                        >
                                        </vcc-date-picker>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-left">
                        <div class="flex border-b border-40">
                            <div class="flex border-b border-40 w-full">
                                <div class="w-1/3 py-6 px-8">
                                    <label for="contact_phone" class="inline-block text-80 pt-2 leading-tight mb-2">{{__('Available to book until')}}</label>
                                </div>
                                <div class="py-6 px-8 w-1/2">
                                     <vcc-date-picker
                                        :input-props='{readonly: true}'
                                        mode='single'
                                        :value="new Date()"
                                        show-caps
                                        v-model="maximum_calendar_date"
                                        :locale="locale"
                                        :min-date="new Date()"
                                        :masks="{ dayPopover : 'WWW, MMM D, YYYY' , weekdays: 'WWW' }"
                                        :popover="{ placement: 'bottom', visibility: 'click' }"
                                        >
                                        </vcc-date-picker>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-left">
                        <div class="flex border-b border-40 w-full">
                            <div class="w-1/3 py-6 px-8">
                                <label  class="inline-block text-80 pt-2 leading-tight mb-2">{{__('Contact Note Ar')}}</label>
                            </div>
                            <div class="py-6 px-8 w-1/2">
                                <vue-editor v-model="contact_note_ar" :editor-toolbar="customToolbar"></vue-editor>
<!--                                <textarea name="contact_note_ar" v-model="contact_note_ar" id="contact_note_ar" cols="30" rows="10" class="inline-block form-control form-input form-input-bordered text-lg text-90 w-full h-40"></textarea>-->
                            </div>
                        </div>

                        <div class="flex border-b border-40 w-full">
                            <div class="w-1/3 py-6 px-8">
                                <label  class="inline-block text-80 pt-2 leading-tight mb-2">{{__('Contact Note En')}}</label>
                            </div>
                            <div class="py-6 px-8 w-1/2">
                                <vue-editor v-model="contact_note_en" :editor-toolbar="customToolbar"></vue-editor>
<!--                                <textarea name="contact_note_en" v-model="contact_note_en" id="contact_note_en" cols="30" rows="10" class="inline-block form-control form-input form-input-bordered text-lg text-90 w-full h-40"></textarea>-->
                            </div>
                        </div>
                    </div>

                    <div class="form-left">
                        <div class="flex border-b border-40">
                            <div class="flex border-b border-40 w-full">
                                <div class="w-1/3 py-6 px-8">
                                    <label for="contact_map_url" class="inline-block text-80 pt-2 leading-tight mb-2">{{__('Address On The Map As URL')}} <button type="button"  @click="openAddressmapUrl" class="addressmapbutton"></button></label>
                                </div>
                                <div class="py-6 px-8 w-1/2">
                                    <input id="contact_map_url" v-model="contact_map_url" type="text" class="inline-block form-control form-input form-input-bordered text-lg text-90 w-full">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-left">
                        <div class="flex border-b border-40">
                            <div class="flex border-b border-40 w-full">
                                <div class="w-1/3 py-6 px-8">
                                    <label for="contact_map_iframe" class="inline-block text-80 pt-2 leading-tight mb-2">{{__('Address On The Map As An Iframe')}} <button type="button"  @click="openAddressmapIframe" class="addressmapbutton"></button></label>
                                </div>
                                <div class="py-6 px-8 w-1/2">
                                    <input id="contact_map_iframe" v-model="contact_map_iframe" type="text" class="inline-block form-control form-input form-input-bordered text-lg text-90 w-full">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-left">
                        <div class="flex border-b border-40 w-full">
                            <div class="w-1/3 py-6 px-8">
                                <label  class="inline-block text-80 pt-2 leading-tight mb-2">{{__('Address Description Ar')}}</label>
                            </div>
                            <div class="py-6 px-8 w-1/2">
                                <vue-editor v-model="contact_address_description_ar" :editor-toolbar="customToolbar"></vue-editor>
                            </div>
                        </div>

                        <div class="flex border-b border-40 w-full">
                            <div class="w-1/3 py-6 px-8">
                                <label  class="inline-block text-80 pt-2 leading-tight mb-2">{{__('Address Description En')}}</label>
                            </div>
                            <div class="py-6 px-8 w-1/2">
                                <vue-editor v-model="contact_address_description_en" :editor-toolbar="customToolbar"></vue-editor>

                            </div>
                        </div>
                    </div>

                    <div class="form-left">
                        <div class="flex border-b border-40">
                            <div class="flex border-b border-40 w-full">
                                <div class="w-1/3 py-6 px-8">
                                    <label for="contact_phone" class="inline-block text-80 pt-2 leading-tight mb-2">{{__('The waiting time until payment is completed')}}</label>
                                </div>
                                <div class="py-6 px-8 w-1/2">
                                    <input id="time_payment_completed" v-model="time_payment_completed" type="text" class="inline-block form-control form-input form-input-bordered text-lg text-90 w-full">
                                    <small class="text-gray-500">{{__('Minutes')}}</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-30 flex p-4 justify-between">
                        <button type="submit" class="btn bg-blue-500 hover:bg-blue-400 text-white py-2 px-8">
                            {{ __('Save') }}
                        </button>
                        <button type="button" @click="goBack" class="btn bg-gray-600 hover:bg-gray-500 text-white py-2 px-8">{{ __('Back') }}</button>
                    </div>
                </form>
            </div>
        </card>

    <sweet-modal :enable-mobile-fullscreen="false"  :pulse-on-block="false" :title="__('Address On The Map As An Iframe')" overlay-theme="dark" ref="addressmapiframe" class="delete_confirm">
      <div class="address_on_map_modal">
        <p>{{ __('Copy the address from Google Maps and paste it into the custom field as in the following scenario') }} :</p>
        <img src="/images/embed-google-map.png" alt="map">
      </div><!-- address_on_map_modal -->
    </sweet-modal>

    <sweet-modal :enable-mobile-fullscreen="false"  :pulse-on-block="false" :title="__('Address On The Map As URL')" overlay-theme="dark" ref="addressmapurl" class="delete_confirm">
      <div class="address_on_map_modal">
        <p>{{ __('Copy the address from Google Maps and paste it into the custom field as in the following scenario') }} :</p>
        <img src="/images/url-google-map.png" alt="map">
      </div><!-- address_on_map_modal -->
    </sweet-modal>



    </div>
</template>

<script>
    import { VueEditor } from "vue2-editor";
    export default {
        name: "General Settings",
        components : {
            VueEditor
        },
        data() {
            return {
                team: Object,
                settings: Object,
                crumbs: [],
                locale : null,
                contact_email : null,
                time_payment_completed: null,
                contact_phone : null,
                whatsapp_number: null,
                contact_note_ar : null,
                contact_note_en : null,
                contact_map_url : null,
                contact_map_iframe : null,
                contact_address_description_ar : null,
                contact_address_description_en : null,
                deposit_percentage : 100,
                customToolbar: [
                    ["bold", "italic", "underline"],
                    [{ list: "ordered" }, { list: "bullet" }] ,
                    [
                        {
                            align: ""
                        }, {
                        align: "center"
                    }, {
                        align: "right"
                    }, {
                        align: "justify"
                    }
                    ]
                ],
                maximum_calendar_date : moment().add(1, 'M').toDate(),
                minimum_calendar_date : moment().toDate(),
                show_in_booking_engine : null,
                Enable_SmS : null,
                enable_part_time_booking : null
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
                    text: 'Website Settings',
                    to: '/settings/website',
                },
                {
                    text: 'General Website Settings',
                    to: '#',
                }
            ];
            this.team = Spark.state.currentTeam;
            this.getSettings();
        },
        methods: {
            getSettings() {
                Nova.request()
                    .get("/nova-vendor/settings/website-settings/"+this.team.id, {})
                    .then(response => {
                        this.settings = response.data;
                        this.contact_email = this.settings.contact_email;
                        this.time_payment_completed = this.settings.time_payment_completed;
                        this.contact_phone = this.settings.contact_phone;
                        this.whatsapp_number = this.settings.whatsapp_number;
                        this.contact_map_url = this.settings.contact_map_url;
                        this.contact_map_iframe = this.settings.contact_map_iframe;
                        this.contact_note_ar = this.settings.contact_note.ar;
                        this.contact_note_en = this.settings.contact_note.en;
                        this.contact_address_description_ar = this.settings.contact_address_description.ar;
                        this.contact_address_description_en = this.settings.contact_address_description.en;
                        this.deposit_percentage = this.settings.deposit_percentage;
                        if(!this.settings.maximum_calendar_date){
                            this.maximum_calendar_date = moment().add(1, 'M').toDate()
                        }else{
                            this.maximum_calendar_date = moment(this.settings.maximum_calendar_date).toDate();
                        }

                        if(!this.settings.minimum_calendar_date){
                            this.minimum_calendar_date = moment().toDate()
                        }else{
                            this.minimum_calendar_date = moment(this.settings.minimum_calendar_date).toDate();
                        }

                        this.show_in_booking_engine = this.settings.show_in_booking_engine
                        this.enable_part_time_booking = this.settings.enable_part_time_booking
                        this.Enable_SmS = this.settings.enable_sms
                    })
                    .catch(error => {
                        console.log(error)
                    });
            },
            updateContact() {
                if(this.minimum_calendar_date > this.maximum_calendar_date){
                    this.$toasted.error(Nova.app.__('Available to book until must be greater than Available to book from'), {
                            duration: 3000
                    });
                    return;
                }
                Nova.request()
                    .put("/nova-vendor/settings/update-website-settings/"+this.team.id, {
                        deposit_percentage : this.deposit_percentage,
                        contact_email: this.contact_email,
                        time_payment_completed: this.time_payment_completed,
                        contact_phone: this.contact_phone,
                        whatsapp_number: this.whatsapp_number,
                        contact_map_url: this.contact_map_url,
                        contact_map_iframe: this.contact_map_iframe,
                        contact_note: {
                            ar: this.contact_note_ar,
                            en: this.contact_note_en
                        },
                        contact_address_description: {
                            ar: this.contact_address_description_ar,
                            en: this.contact_address_description_en
                        },
                        maximum_calendar_date : moment(this.maximum_calendar_date).format('YYYY-MM-DD'),
                        minimum_calendar_date : moment(this.minimum_calendar_date).format('YYYY-MM-DD'),
                        show_in_booking_engine : this.show_in_booking_engine,
                        enable_part_time_booking : this.enable_part_time_booking,
                        enable_sms : this.Enable_SmS
                    })
                    .then(response => {
                        this.settings = response.data;
                        this.$router.push('/settings/website');
                        this.$toasted.success(Nova.app.__('Success'), {
                            duration: 3000
                        })
                    })
                    .catch(error => {
                        console.log(error)
                    });
            },
            goBack() {
                this.$router.push({path: '/settings/website'})
            },

            openAddressmapIframe(){
              this.$refs.addressmapiframe.open();
            },
            openAddressmapUrl(){
              this.$refs.addressmapurl.open();
            }
        },
        created() {
            this.locale = Nova.config.local;
        },
    }
</script>

<style lang="scss">
    select {
        background-image: url("data:image/svg+xml,%3C%3Fxml version='1.0' encoding='iso-8859-1'%3F%3E%3C!-- Generator: Adobe Illustrator 19.0.0, SVG Export Plug-In . SVG Version: 6.00 Build 0) --%3E%3Csvg version='1.1' id='Layer_1' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' x='0px' y='0px' viewBox='0 0 491.996 491.996' style='enable-background:new 0 0 491.996 491.996;' xml:space='preserve'%3E%3Cg%3E%3Cg%3E%3Cpath d='M484.132,124.986l-16.116-16.228c-5.072-5.068-11.82-7.86-19.032-7.86c-7.208,0-13.964,2.792-19.036,7.86l-183.84,183.848 L62.056,108.554c-5.064-5.068-11.82-7.856-19.028-7.856s-13.968,2.788-19.036,7.856l-16.12,16.128 c-10.496,10.488-10.496,27.572,0,38.06l219.136,219.924c5.064,5.064,11.812,8.632,19.084,8.632h0.084 c7.212,0,13.96-3.572,19.024-8.632l218.932-219.328c5.072-5.064,7.856-12.016,7.864-19.224 C491.996,136.902,489.204,130.046,484.132,124.986z'/%3E%3C/g%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3C/svg%3E%0A");
        width: 100%;
        height: 40px !important;
        padding: 0 10px !important;
        background-color: #fafafa !important;
        border: 1px solid #ddd !important;
        color: #000;
        font-size: 15px;
        -webkit-box-sizing: border-box;
        box-sizing: border-box;
        -webkit-appearance: none;
        -moz-appearance: none;
        -o-appearance: none;
        appearance: none;
        border-radius: 5px !important;
        background-position: 15px center;
        background-repeat: no-repeat;
        background-size: 14px;

        [dir="ltr"] & {
            background-position: 97% center;
        }

        /* ltr */
    }
  button.addressmapbutton {
    display: inline-block;
    height: 13px;
    width: 13px;
    background-position: center center;
    background-size: 13px;
    background-repeat: no-repeat;
    outline: none;
    box-shadow: none;
    margin: 0 5px 0 0;
    background-image: url("data:image/svg+xml,%3C%3Fxml version='1.0'%3F%3E%3Csvg xmlns='http://www.w3.org/2000/svg' id='Capa_1' enable-background='new 0 0 524.235 524.235' height='512px' viewBox='0 0 524.235 524.235' width='512px'%3E%3Cg%3E%3Cpath d='m262.118 0c-144.53 0-262.118 117.588-262.118 262.118s117.588 262.118 262.118 262.118 262.118-117.588 262.118-262.118-117.589-262.118-262.118-262.118zm17.05 417.639c-12.453 2.076-37.232 7.261-49.815 8.303-10.651.882-20.702-5.215-26.829-13.967-6.143-8.751-7.615-19.95-3.968-29.997l49.547-136.242h-51.515c-.044-28.389 21.25-49.263 48.485-57.274 12.997-3.824 37.212-9.057 49.809-8.255 7.547.48 20.702 5.215 26.829 13.967 6.143 8.751 7.615 19.95 3.968 29.997l-49.547 136.242h51.499c.01 28.356-20.49 52.564-48.463 57.226zm15.714-253.815c-18.096 0-32.765-14.671-32.765-32.765 0-18.096 14.669-32.765 32.765-32.765s32.765 14.669 32.765 32.765c0 18.095-14.668 32.765-32.765 32.765z' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%231273EB'/%3E%3C/g%3E%3C/svg%3E%0A");
    [dir="ltr"] & {
      margin: 0 0 0 5px;
    } /* ltr */
  }
  .address_on_map_modal {
    padding: 15px;
    text-align: center;
    p {
      display: block;
      margin: 0 auto 15px;
      color: #000;
      font-size: 15px;
      [dir="ltr"] & {
        font-size: 14px;
      } /* ltr */
    } /* p */
    img {
      display: block;
      margin: 0 auto;
      max-width: 100%;
      width: auto;
      height: auto;
      max-height: 100%;
    } /* img */
  } /* address_on_map_modal */

  .switch {
  position: relative;
  display: inline-block;
  width: 60px;
  height: 26px;
  margin: 5px auto;
}
.switch input {
  opacity: 0;
  width: 0;
  height: 0;
}
.slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #ccc;
  -webkit-transition: .4s;
  transition: .4s;
}
.slider:before {
  position: absolute;
  content: "";
  height: 20px;
  width: 20px;
  left: 3px;
  bottom: 3px;
  background-color: white;
  -webkit-transition: .4s;
  transition: .4s;
}
input:checked + .slider {
  background-color: #21b978;
}
input:focus + .slider {
  box-shadow: 0 0 1px #21b978;
}
input:checked + .slider:before {
  -webkit-transform: translateX(33px);
  -ms-transform: translateX(33px);
  transform: translateX(33px);
}
.slider.round {
  border-radius: 34px;
}
.slider.round:before {
  border-radius: 50%;
}
</style>
