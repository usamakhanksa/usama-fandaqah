<template>
    <div>
        <sweet-modal :enable-mobile-fullscreen="false" :pulse-on-block="false" :title="__('Edit Customer')" overlay-theme="dark" @open="setData" ref="edit" class="cancel_reservation_modal customer_modal_crud">

            <div class="customer_update relative">
                <loading :active="isLoading" :loader="'spinner'" :color="'#7e7d7f'" :opacity="0.7" :height="20" :width="20" :is-full-page="false"></loading>

                <div class="row_group">
                    <div class="col">
                        <label>{{__('Name')}}<i>*</i></label>
                        <input type="text" v-model="customer.name" :placeholder="__('Name')">
                    </div><!-- name col -->
                    <div class="col">
                        <label>{{__('Phone Number')}}<i>*</i></label>
                        <vue-tel-input
                        :defaultCountry="countryDefaultIso"
                        @onInput="checkThePhone($event)"
                        :required="true"
                        :enabledFlags="true"
                        name="phone"
                        :placeholder="__('Enter Phone Number')"
                        :inputOptions="{ showDialCode: false, tabindex: 0 }"
                        v-model="customer.phone"
                        >
                        </vue-tel-input>
                    </div><!-- phone col -->
                </div><!-- row_group -->
                <div class="row_group">
                    <div class="col">
                        <label>{{__('Customer Type')}}</label>
                        <select v-model="customer.customer_type"
                                @change="customerTypeChange($event.target.value)">
                            <option value="" selected="selected">{{__('Choose a type')}}</option>
                            <option :value="v" v-for="(t,v) in customer_types" :key="v">{{t}}</option>
                        </select>
                    </div><!-- customer type col -->
                    <div class="col">
                        <label>{{__('Nationality')}}</label>
                        <select v-model="customer.country_id">
                            <option value="" selected="selected">{{__('Choose a country')}}</option>
                            <option :value="n.code" v-for="(n, index) in nationalities" :key="index">
                                {{n.title}}
                            </option>
                        </select>
                    </div><!-- nationality col -->
                </div><!-- row_group -->
                <div class="row_group">
                    <div class="col">
                        <label>{{__('Id Type')}}</label>
                        <select v-model="customer.id_type">
                            <option value="" selected="selected">{{__('Choose a type')}}</option>
                            <option :value="item.id" v-for="(item, index) in shomos_ids" :key="index">{{item.title}}</option>
                        </select>
                    </div><!-- id type col -->
                    <div class="col">
                        <label>{{ customer.id_type == 2 ? __('Passport Number') : __('ID Number')}}</label>
                        <input type="text" v-model="customer.id_number" :placeholder="customer.id_type == 2 ? __('Passport Number') : __('ID Number')">
                    </div><!-- id number col -->
                </div><!-- row_group -->
                <div class="row_group">
                    <div class="id_expire">
                        <label>{{__('ID Expire Date')}}</label>
                        <input
                                type="text"
                                :value="customer.id_expire_date | formatDateWithoutTime"
                                id="custom_georgian_id_expire_input"
                                :placeholder="__('ID Expire Date')"
                        />
                        <date-picker
                                :auto-submit="true"
                                @input="getIdExpireDate($event)"
                                v-model="customer.id_expire_date"
                                :locale="calendarLocale"
                                :calendar="'georgian'"
                                min="1900-01-01"
                                type="date"
                                element="custom_georgian_id_expire_input"
                                format="YYYY-MM-DD"
                                ref="id_expire_date_ref"
                        />
                    </div><!-- id expire date milday col -->
                </div>
                <div class="row_group">
                    <div class="col">
                        <label>{{__('Gender')}}</label>
                        <select v-model="customer.gender">
                            <option value="male">{{__('Male')}}</option>
                            <option value="female">{{__('Female')}}</option>
                        </select>
                    </div><!-- gender col -->
                    <div class="col">
                        <label>{{__('Email address')}}</label>
                        <input type="email" v-model="customer.email" :placeholder="__('Email address')">
                    </div><!-- email address col -->
                </div><!-- row_group -->
                <div class="row_group">
                    <div class="col">
                        <label>{{__('Birthday Date')}}</label>
                        <input
                                type="text"
                                :value="customer.birthday_date | formatDateWithoutTime"
                                @input="value => customer.birthday_date = value"
                                id="custom_georgian_input"
                                :placeholder="__('Birthday Date')"
                        />
                        <date-picker
                                :auto-submit="true"
                                @input="getGeorgianDate($event)"
                                v-model="customer.birthday_date"
                                :locale="calendarLocale"
                                :calendar="'georgian'"
                                min="1900-01-01"
                                :max="maxDate"
                                type="date"
                                element="custom_georgian_input"
                                format="YYYY-MM-DD"
                                ref="birth_day_date"
                        />
                    </div><!-- birthday milday col -->
                    <div class="col">
                        <label>{{__('Birthday Date Hijri')}}</label>
                        <input
                                type="text"
                                :value="hijriDate | formatDateSpecial"
                                id="custom_hijri_input"
                                :placeholder="__('Hijri Birth Date')"
                        />
                        <date-picker
                                :auto-submit="true"
                                @input="getHijriDate($event)"
                                v-model="hijriDate"
                                :locale="calendarLocale"
                                :calendar="'hijri'"
                                min="1300-01-01"
                                :max="maxDateHijriDate"
                                type="date"
                                element="custom_hijri_input"
                                format="iYYYY-iM-iD"
                                ref="hijri_birth_day_date"
                        />
                    </div><!-- birthday hijiri col -->
                </div><!-- row_group -->
                 <div class="row_group">
                    <div class="address">
                        <label>{{__('Address')}}</label>
                        <input
                                type="text"
                                v-model="customer.address"
                                :placeholder="__('Address')"
                        />

                    </div><!-- address col -->
                </div>
                <div class="row_group">
                    <div class="col">
                        <label>{{__('Work')}}</label>
                        <input type="text" v-model="customer.work" :placeholder="__('Work')">
                    </div><!-- name col -->
                    <div class="col">
                        <label>{{__('Work Phone')}}</label>
                        <input type="text" v-model="customer.work_phone" :placeholder="__('Work Phone')">
                    </div><!-- phone col -->
                </div><!-- row_group -->
                <div class="row_group">
                    <div class="col">
                        <label>{{__('Highlight')}}</label>
                        <select v-model="customer.highlight_id">
                            <option :value="null" :selected="!customer.highlight_id ? 'selected' : '' ">{{__('Choose a highlight')}}</option>     
                            <option :value="highlight.id" :disabled="!highlight.status"
                                    v-for="(highlight, index) in highlights" :key="index">
                                {{highlight.name}}
                            </option>
                        </select>
                    </div><!-- highlight col -->
                    <div class="col">
                        <label v-if="customer.highlight_id">{{__('Color')}}</label>
                        <div v-if="customer.highlight_id" v-html="highlight()"></div>
                    </div><!-- highlight color col -->
                </div><!-- row_group -->
                <div class="row_group">
                    <div class="col" v-if="customer.customer_type == '3'">
                        <label>{{__('Visa Number')}}</label>
                        <input type="text" v-model="customer.visa_number" :placeholder="__('Visa Number')">
                    </div><!-- visa number col -->
                    <div class="col" v-if="SHMS">
                        <label>{{__('ID Serial Number')}}</label>
                        <input type="number" min="1" v-model="customer.id_serial_number"
                                            class=" border rounded w-full py-2 px-3 border-gray-300 leading-tight focus:outline-none focus:shadow-outline"
                                            :placeholder="__('ID Serial Number')">
                    </div><!-- id serial number col -->
                </div><!-- row_group -->
            </div><!-- customer_details -->
        <div class="action_buttons">
            <button class="w-full update_button" @click="update()">{{__('Update Customer')}}</button>
        </div><!-- action_buttons -->
    </sweet-modal>
    </div>

</template>

<script>
import HijrahDate from 'hijrah-date';
import momentHijri from 'moment-hijri';
import flatPickr from 'vue-flatpickr-component';
import 'flatpickr/dist/flatpickr.css';
import VueDatetimeJs from 'vue-datetime-js'
import moment from 'moment';
import allCountries from '../../../../Calender/resources/js/allCountries';

import Loading from 'vue-loading-overlay';

export default {
    name: 'update-customer',
    components: {
        flatPickr,
        datePicker: VueDatetimeJs,
        Loading
    },
    props: ['highlights','customer_obj' , 'component_from'],
    data() {
        return {
            highlights: [],
            customer_types: [],
            SCTH: false,
            SHMS: false,
            purpose_of_visit: [],
            id_types: [],
            coming_aways: [
                {
                    'id': 7,
                    'title': 'Air',
                },
                {
                    'id': 8,
                    'title': 'Sea',
                },
                {
                    'id': 9,
                    'title': 'Land',
                },
            ],
            customer: {
                id: null,
                phone: null,
                email: null,
                id_number: null,
                customer_type: 1,
                id_type: 1,
                country_id:  Nova.app.currentTeam.country_code,
                id_expire_date: null,
                birthday_date: null,
                gender: null,
                label: null,
                highlight_id: null,
                coming_away: null,
                visa_number: null,
                id_serial_number: null,
                work: null,
                work_phone: null,
                address: null
            },
            crumbs : [],
            nationalities: [],
            id_types: [],
            hijriDate: '',
            calendarLocale: '',
            commonSelectors: {
                nationalities: null,
                customer_types: null,
                id_types: null,
                purpose_of_visit: null
            },
            current_team_id : Nova.config.user.current_team_id,
            isLoading : false,
            shomoos_id_types : [

{
    "title" : Nova.app.__('National ID'),
    "code" : 1,
},

{
    "title" : Nova.app.__('Card family ID'),
    "code" : 2,
},

{
    "title" : Nova.app.__('GCC ID'),
    "code" : 3
},

{
    "title" : Nova.app.__('Residence'),
    "code" : 4,
},

{
    "title" : Nova.app.__('Passport'),
    "code" : 5,
},

{
    "title" : Nova.app.__('Visit identity'),
    "code" : 6,
},
],
shomos_ids : [
{
"title" : Nova.app.__('National ID'),
"code" : 1,
},

{
"title" : Nova.app.__('Card family ID'),
"code" : 2,
},
{
"title" : Nova.app.__('Passport'),
"code" : 5,
}

] ,
            customerPhoneCountry: null,
            customerValidPhone: true,
        }
    },
    computed:{
        maxDate(){
            return moment().format('YYYY-MM-DD');
        },
        maxDateHijriDate(){
            return momentHijri().format('iYYYY-iMM-iDD');
        },
        countryDefaultIso() {
                var iso = 'SA';
                var team_country = Nova.app.currentTeam.country;
                allCountries.forEach(function (country) {

                    if (country.name.includes(team_country.title.en) || country.name.includes(team_country.title.ar)) {
                        iso = country.iso2;
                    }
                });

                return iso;

            }
    },
    methods: {
        setData(){
            this.customer = this.customer_obj;
            this.$refs.id_expire_date_ref.visible = false;
            this.$refs.birth_day_date.visible = false;
            this.$refs.hijri_birth_day_date.visible = false;
            this.getUtilities();
        },
        update() {
            if(!this.validateForm()){return;}
            this.isLoading = true;
            axios
            .put(`/nova-vendor/new/customers/${this.customer.id}` , {customer : this.customer})
            .then(response => {

                if(response.data.success){
                    this.isLoading = false;
                    this.$toasted.show(this.__(response.data.message), {type: 'success'});
                    this.$refs.edit.close();
                    Nova.$emit(this.component_from == 'show' ? 'get-customer-info' : 'call-customers-query');
                }else{
                    this.isLoading = false;
                    this.$toasted.show(response.data.message, {type: 'error'});
                    return;
                }

            });
        },
        validateForm(){
            if (this.customer.phone) {
                if (!this.invalidPhone(this.customer.phone)) {
                    this.$toasted.show(this.__('this phone is not valid'), {type: 'error'})
                    this.isLoading = false;
                    return false;
                }
            }

            if (this.customer.phone) {
                this.customer.phone.replace(/\s/g, "");
            }

            if (this.customer.email) {
                var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
                if (!re.test(String(this.customer.email).toLowerCase())) {
                    this.$toasted.show(this.__('this email is not valid'), {type: 'error'})
                    this.isLoading = false;
                    return false;
                }
            }
            if (!this.customer.name || !this.customer.phone) {
                this.$toasted.show(this.__('Please fill customer info'), {type: 'error'})
                this.isLoading = false;
                return false;
            }

            // if (((!this.customer.visa_number && this.customer.customer_type == '3')||
            //     !this.customer.name ||
            //     !this.customer.phone ||
            //     !this.customer.country_id ||
            //     !this.customer.id_type ||
            //     !this.customer.id_number ||
            //     !this.customer.id_serial_number
            //     ) && (this.SHMS)) {
            //     this.$toasted.show(this.__('Please fill all customer info'), {type: 'error'})
            //     this.isLoading = false;
            //     return false;
            // }
            // if ((!this.customer.country_id ||
            //     !this.customer.gender
            //     ) && this.SCTH) {
            //     this.$toasted.show(this.__('Please fill all customer info'), {type: 'error'})
            //     this.isLoading = false;
            //     return false;
            // }

            if(!this.customer.birthday_date && this.SCTH){
                this.customer.birthday_date = 0 ;
            }

            return true;
        },
        customerTypeChange(id) {

            if (id == 1) {
                this.nationalities = this.commonSelectors.nationalities.filter(n => n.code ==  Nova.app.currentTeam.country_code)
                this.customer.country_id =  Nova.app.currentTeam.country_code
                // this.id_types = this.commonSelectors.id_types.filter(n => [1, 2, 3, 4, 5].includes(n.id))
                this.shomos_ids = this.shomoos_id_types.filter(t => [1, 2, 5].includes(t.code));

            } else if (id == 2) {
                this.nationalities = this.commonSelectors.nationalities.filter(n => n.is_gcc && n.code !=  Nova.app.currentTeam.country_code)
                // this.id_types = this.commonSelectors.id_types.filter(n => [2, 6].includes(n.id))
                this.shomos_ids = this.shomoos_id_types.filter(t => [3,5].includes(t.code));


            } else if (id == 3) {
                this.nationalities = this.commonSelectors.nationalities.filter(n => n.code != Nova.app.currentTeam.country_code)
                // this.id_types = this.commonSelectors.id_types.filter(n => [2].includes(n.id))
                this.shomos_ids = this.shomoos_id_types.filter(t => [5,6].includes(t.code));


            } else if (id == 4) {
                this.nationalities = this.commonSelectors.nationalities.filter(n => n.code != Nova.app.currentTeam.country_code)
                // this.id_types = this.commonSelectors.id_types.filter(n => [2, 3, 7, 8].includes(n.id))
                this.shomos_ids = this.shomoos_id_types.filter(t => [ 4, 5].includes(t.code));

            }
        },
        getUtilities() {
            axios.get('/nova-vendor/new/customers/utilites/get')
                .then(response => {
                    this.commonSelectors.nationalities = response.data.nationalities;
                    this.commonSelectors.id_types = response.data.id_types;
                    this.commonSelectors.customer_types = response.data.customer_types;
                    this.purpose_of_visit = response.data.purpose_of_visit;
                    this.SCTH = response.data.SCTH;
                    this.SHMS = response.data.SHMS;
                    this.customer_types = response.data.customer_types;
                    this.customerTypeChange(this.customer.customer_type);
                });
        },
        getHijriDate(event) {
            let hijriArr = event.split("-");

            let yearTransformed = this.convertNumbers(hijriArr[0]);
            let monthTransformed = this.convertNumbers(hijriArr[1]);
            let dayTransformed = this.convertNumbers(hijriArr[2]);

            let hijrahDate = new HijrahDate(parseInt(yearTransformed), parseInt(monthTransformed) - 1, dayTransformed);

            this.hijriDate = yearTransformed + '-' + monthTransformed + '-' + dayTransformed;
            this.customer.birthday_date = moment(hijrahDate.toGregorian()).format('YYYY-MM-DD');
        },
        getGeorgianDate(event) {

            let georgianArr = event.split("-");

            let yearTransformed = this.convertNumbers(georgianArr[0]);
            let monthTransformed = this.convertNumbers(georgianArr[1]);
            let dayTransformed = this.convertNumbers(georgianArr[2]);

            let date = new Date(parseInt(yearTransformed), parseInt(monthTransformed) - 1, dayTransformed);
            let hijrahDate = new HijrahDate(date);
            this.hijriDate = hijrahDate.format('yyyy-M-d');

            this.customer.birthday_date = yearTransformed + '-' + monthTransformed + '-' + dayTransformed;
        },
        getIdExpireDate(event){
           let georgianArr = event.split("-");

            let yearTransformed = this.convertNumbers(georgianArr[0]);
            let monthTransformed = this.convertNumbers(georgianArr[1]);
            let dayTransformed = this.convertNumbers(georgianArr[2]);
            this.customer.id_expire_date = yearTransformed + '-' + monthTransformed + '-' + dayTransformed;
        },
        convertNumbers(str) {
            let persianNumbers = [/۰/g, /۱/g, /۲/g, /۳/g, /۴/g, /۵/g, /۶/g, /۷/g, /۸/g, /۹/g],
                arabicNumbers = [/٠/g, /١/g, /٢/g, /٣/g, /٤/g, /٥/g, /٦/g, /٧/g, /٨/g, /٩/g];

            if (typeof str === 'string') {
                for (var i = 0; i < 10; i++) {
                    str = str.replace(persianNumbers[i], i).replace(arabicNumbers[i], i);
                }
            }
            return str;
        },
        highlight() {
                let highlight = this.highlights.filter((elem) => {
                    return elem.id == this.customer.highlight_id;
                })

                let style = "text-align: center;border: 1px solid " + highlight[0].color + "; padding: 5px 10px; border-radius: 4px; background: " + highlight[0].color + "; color: black;";

                return '<label class="form-control border rounded w-full py-2 px-3 border-gray-300 focus:outline-none focus:shadow-outline" style="' + style + '">' + highlight[0].name + '</label>'
        },
        invalidPhone(number){
            var regex = /^[+]*[(]{0,1}[\u0030-\u0039\u0660-\u0669]{1,3}[)]{0,1}[-\s\./\u0030-\u0039\u0660-\u0669]*$/g;
            var regex1 = /^[+]*[(]{0,1}[\u0030-\u0039\u0660-\u0669]{1,3}[)]{0,1}[-\s\./\u0030-\u0039\u0660-\u0669]*$/g;

            var result = (regex.test(number) ||  regex1.test(number));

            return result;
        },

    },
    mounted(){
        Nova.config.local == 'en' ? this.calendarLocale = 'en' : this.calendarLocale = 'ar-sa';
        // this.getUtilities();
    }
};
</script>

<style lang="scss">
.customer_modal_crud {
    .sweet-content {
    overflow: auto;
    max-height: 85vh;
    display: block !important;
    scrollbar-width: thin;
    scrollbar-color: #ccc #f5f5f5;
    &::-webkit-scrollbar {width: 6px;}
    &::-webkit-scrollbar-track {background: #f5f5f5;}
    &::-webkit-scrollbar-thumb {background: #ccc;}
    &::-webkit-scrollbar-thumb:window-inactive {background: #f5f5f5;}
    @media (min-width: 320px) and (max-width: 767px) {
        max-height: 500px;
    }
  } /* sweet-content */
}

.update_button {
        display: block;
        background-color: #4099de;
        font-size: 15px;
        padding: 0 20px;
        height: 35px;
        border-radius: 4px !important;
        line-height: 35px;
        color: #fff;
        cursor: pointer;
        outline: none;
        font-weight: normal !important;
        margin: 0 0 15px;
        @media (min-width: 320px) and (max-width: 767px) {
          width: auto;
          &::before {display: none;}
        } /* media */
        &:hover {
          background-color: #0071C9;
        } /* hover */
      } /* a */
@import '~vue-tel-input/dist/vue-tel-input.css';
      .customer_update {
                    .formgroup {
                        display: block;
                        margin: 0 auto 10px;

                        .autocomplete__box {
                            border: 1px solid #ddd !important;
                            background: #fafafa;
                            color: #000;
                            height: 40px;
                            padding: 0 10px;
                            box-shadow: none !important;
                            border-radius: 5px;

                            input {
                                border: none !important;
                                height: 38px !important;
                                border-radius: 0 !important;
                                background: transparent !important;
                            }

                            /* input */
                        }

                        /* autocomplete__box */
                        ul.autocomplete__results {
                            border: 1px solid #ddd;
                            border-radius: 0 0 5px 5px;
                            margin: -3px 0 0 0;
                            background: #f5f5f5;

                            li.autocomplete__results__item {
                                display: block;
                                color: #000;
                                font-size: 15px;

                                &:hover {
                                    background: #f0f0f0;
                                }
                            }

                            /* autocomplete__results__item */
                        }

                        /* autocomplete__results */
                    }

                    /* formgroup */
                    .loader_item {
                        padding: 50px;
                    }

                    /* loader_item */
                    .customerNotes {
                        span {
                            margin: 0 auto 15px;
                            border-radius: 5px;
                            padding: 10px;
                            text-align: center;
                            color: #b7791f;
                            border: 1px solid #fbd38d;
                            background: #fffaf0;
                            font-size: 15px;
                            cursor: pointer;
                            display: block;
                            -webkit-transition: all 0.2s ease-in-out;
                            -moz-transition: all 0.2s ease-in-out;
                            -o-transition: all 0.2s ease-in-out;
                            transition: all 0.2s ease-in-out;

                            &:hover {
                                background: #faf5eb;
                                border-color: #f6ce88;
                                color: #b2741a;
                            }

                            /* hover */
                        }

                        /* span */
                    }

                    /* customerNotes */
                    .customer_notes_modal {
                        .sweet-content {
                            max-height: 500px;
                            overflow-y: auto;
                            display: block !important;
                            scrollbar-width: thin;
                            scrollbar-color: #ccc #f5f5f5;

                            &::-webkit-scrollbar {
                                width: 6px;
                            }

                            &::-webkit-scrollbar-track {
                                background: #f5f5f5;
                            }

                            &::-webkit-scrollbar-thumb {
                                background: #ccc;
                            }

                            &::-webkit-scrollbar-thumb:window-inactive {
                                background: #f5f5f5;
                            }
                        }

                        /* sweet-content */
                        .note_item {
                            display: flex;
                            justify-content: space-between;
                            align-items: center;
                            width: 100%;
                            flex-wrap: wrap;
                            border: 1px solid #ddd;
                            margin: 0 auto 10px;
                            border-radius: 5px;
                            padding: 5px;
                            background: #fdfdfd;

                            .desc {
                                display: block;
                                white-space: pre-line;
                                font-size: 15px;
                                color: #000;
                                margin: 0 0 10px;
                            }

                            /* desc */
                            .user_info {
                                display: flex;
                                align-items: center;
                                justify-content: space-between;
                                width: 100%;

                                .user_account {
                                    display: flex;
                                    align-items: center;
                                    justify-content: flex-start;
                                    font-size: 15px;
                                    color: #666666;

                                    img {
                                        display: block;
                                        width: 35px;
                                        height: 35px;
                                        border-radius: 100%;
                                        margin: 0 0 0 5px;
                                        border: 1px solid #ddd;

                                        [dir="ltr"] & {
                                            margin: 0 5px 0 0;
                                        }

                                        /* ltr */
                                    }

                                    /* img */
                                }

                                /* user_account */
                                time {
                                    display: block;
                                    font-size: 13px;
                                    color: #777777;
                                }

                                /* time */
                            }

                            /* user_info */
                        }

                        /* note_item */
                    }

                    /* customer_notes_modal */
                    .row_group {
                        display: flex;
                        justify-content: space-between;
                        align-items: flex-start;
                        flex-wrap: wrap;
                        margin: 0 -10px;
                        @media (min-width: 320px) and (max-width: 767px) {
                            margin: 0;
                        }
                        .id_expire{
                            width: 100%;
                            padding: 0 10px;
                            margin: 0 0 10px;
                            @media (min-width: 320px) and (max-width: 767px) {
                                width: 100%;
                                padding: 0;
                            }
                        }

                         .address{
                            width: 100%;
                            padding: 0 10px;
                            margin: 0 0 10px;
                            @media (min-width: 320px) and (max-width: 767px) {
                                width: 100%;
                                padding: 0;
                            }
                        }
                        /* Mobile */

                        /* Mobile */
                        .col {
                            width: 50%;
                            padding: 0 10px;
                            margin: 0 0 10px;
                            @media (min-width: 320px) and (max-width: 767px) {
                                width: 100%;
                                padding: 0;
                            }
                            /* Mobile */
                            .vue-tel-input {
                                display: flex;
                                width: 100%;
                                height: 40px;
                                background: #fafafa;
                                border: 1px solid #dddddd !important;
                                line-height: 40px;
                                font-size: 15px;
                                color: #000;
                                border-radius: 4px;
                                padding: 0;
                                text-align: right;
                                align-items: center;
                                box-shadow: none;

                                [dir="ltr"] & {
                                    text-align: left;
                                }

                                /* rtl */
                                .dropdown {
                                    padding: 0;
                                    width: 70px;
                                    background: #f5f5f5;
                                    height: 38px;
                                    border-left: 1px solid #dddddd;
                                    border-radius: 0 4px 4px 0;

                                    [dir="ltr"] & {
                                        border-right: 1px solid #dddddd;
                                        border-left: none;
                                        border-radius: 4px 0 0 4px;
                                    }

                                    /* rtl */
                                    span.selection {
                                        display: flex !important;
                                        height: 40px;
                                        justify-content: center;
                                        align-items: center;
                                        width: auto;
                                        margin: 0 auto;
                                        font-size: 12px !important;

                                        .iti-flag {
                                            margin: 0;
                                        }

                                        /* iti-flag */
                                        span.dropdown-arrow {
                                            width: auto;
                                            margin: 0 5px 0 0;
                                            display: inline-block !important;
                                            font-size: inherit !important;

                                            [dir="ltr"] & {
                                                margin: 0 0 0 5px;
                                            }

                                            /* ltr */
                                        }

                                        /* dropdown-arrow */
                                    }

                                    /* selection */
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

                                        &::-webkit-scrollbar {
                                            width: 6px;
                                        }

                                        &::-webkit-scrollbar-track {
                                            background: #f5f5f5;
                                        }

                                        &::-webkit-scrollbar-thumb {
                                            background: #ccc;
                                        }

                                        &::-webkit-scrollbar-thumb:window-inactive {
                                            background: #f5f5f5;
                                        }

                                        [dir="ltr"] & {
                                            left: 0;
                                            right: auto;
                                        }

                                        /* rtl */
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
                                            }

                                            /* ltr */
                                            .iti-flag {
                                                margin: 0;
                                            }

                                            /* iti-flag */
                                            strong {
                                                display: block;
                                                font-weight: normal;
                                                font-size: 15px;
                                                margin: 0 5px;
                                            }

                                            /* strong */
                                            span {
                                                direction: ltr;
                                                color: #666 !important;
                                                font-size: inherit !important;
                                                margin: 0;
                                            }

                                            /* span */
                                        }

                                        /* li */
                                    }

                                    /* ul */
                                }

                                /* dropdown */
                                input {
                                    width: 76%;
                                    border-radius: 0 !important;
                                    height: 38px !important;
                                    border: none !important;
                                    padding: 0 10px 0 0 !important;

                                    [dir="ltr"] & {
                                        padding: 0 0 0 10px !important;
                                    }

                                    /* ltr */
                                }

                                /* input */
                            }

                            /* vue-tel-input */
                        }

                        /* col */
                    }

                    /* row_group */
                    label {
                        display: block;
                        margin: 0 auto 5px;
                        font-size: 15px;

                        i {
                            display: inline-block !important;
                            margin: 0 5px 0 0;
                            color: #f00 !important;
                            font-style: normal;
                        }

                        /* i */
                    }

                    /* label */
                    input {
                        height: 40px !important;
                        padding: 0 10px !important;
                        color: #000 !important;
                        font-size: 15px !important;
                        border: 1px solid #dddddd !important;
                        background: #fafafa !important;
                        width: 100%;

                        &[readonly="readonly"] {
                            cursor: pointer;
                        }

                        /* readonly */
                        &.customer_search {
                            background: transparent !important;
                            border: none !important;
                            height: 40px !important;
                            border-radius: 0 !important;
                            padding: 0 10px !important;
                            display: block;
                        }

                        /* customer_search */
                    }

                    /* input */
                    label.customer_highlight {
                        height: 40px;
                        border-radius: 4px;
                        text-align: center;
                        font-size: 15px;
                        line-height: 40px;
                        color: #000;
                        margin: 0 auto;
                    }

                    /* customer_highlight */
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

                    /* select */
                }

                /* customer_details */
</style>
