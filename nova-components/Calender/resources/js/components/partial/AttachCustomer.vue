<template>
  <div>
    <button @click="openAttachCustomerModal" class="more">{{__('Attach customer to reservation')}}</button>
    <sweet-modal class="update_customer_modal relative" :enable-mobile-fullscreen="false" :pulse-on-block="false" :title="__('Attach customer to reservation')" overlay-theme="dark" ref="modal">
       <loading
                :active.sync="loading"
                :can-cancel="false"
                :is-full-page="fullPage"
        />
      <div class="holder">
        <div class="search_criteria">
            <div class="title">{{__('Search Customers')}} : </div>
            <div class="radios_area">
                <label class="custom_radio" for="by_name_attach">
                    <input type="radio" id="by_name_attach" value="by_name" v-model="search_attribute">
                    <span class="checkmark"></span>
                    <p>{{__('Name')}}</p>
                </label><!-- custom_radio -->
                <label class="custom_radio" for="by_id_number_attach" >
                    <input type="radio" id="by_id_number_attach" value="by_id_number" v-model="search_attribute">
                    <span class="checkmark"></span>
                    <p>{{__('ID Number')}}</p>
                </label><!-- custom_radio -->
                <label class="custom_radio" for="by_email_attach">
                    <input type="radio" id="by_email_attach" value="by_email" v-model="search_attribute">
                    <span class="checkmark"></span>
                    <p>{{__('Email')}}</p>
                </label><!-- custom_radio -->
                <label class="custom_radio" for="by_phone_attach" >
                    <input type="radio" id="by_phone_attach" value="by_phone" v-model="search_attribute">
                    <span class="checkmark"></span>
                    <p>{{__('Phone')}}</p>
                </label><!-- custom_radio -->

            </div><!-- radios_area -->
        </div>
        <div class="formgroup">
            <autocomplete
            from='edit-customer'
            ref='autocomplete'
            :initialValue="null"
            :initialDisplay="null"
            input-class="customer_search"
            :source="setCustomersEndPoint"
            @selected="setCustomer"
            @enter="setCustomer"
            @clear="clearedCustomer"
            :resultsValue="customer.id"
            :resultsProperty="customer.id"
            :resultsDisplay="formatForDropDown"
            :placeholder="__(searchPlaceHolder)"
            delay="500"
            >
                <div slot="noResults" style="display:flex; align-content:center;">
                    <p>{{__('No customers found')}} ... </p>
                </div>
            </autocomplete>


        <!-- <div class="creditor_debitor" v-if="reservations_count > 1" @click="openCustomerCreditorDebitorModal">
            <span id="creditor_debitor_span">
                <p v-if="total_balance > 0">{{__('Customer')}} <b class="text-green-500">{{__('Creditor')}}</b> - {{__('Show Reservations History')}}</p>
                <p v-if="total_balance < 0">{{__('Customer')}} <b class="text-red-500">{{__('Debtor')}}</b> - {{__('Show Reservations History')}}</p>
                <p v-if="total_balance == 0">{{__('Show Reservations History')}}</p>
            </span>
        </div> -->
          <!-- <div v-if="collection.length" @click="openPromissoryListModal" class="cursor-pointer alert-warning">
              {{__('There are un-fulfilled promissories on customer :customer' , {customer : customer.name})}}
            </div> -->
        </div><!-- formgroup -->
      </div>

      <div class="row_group">
        <div class="col">
          <label >{{__('Name')}}<i>*</i></label>
          <input type="text" v-model="customer.name" :placeholder="__('Name')">
        </div><!-- col-->
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
          <p v-if="!customerValidPhone" style="color:#ce1025;">{{__('Phone number is not valid')}}</p>

        </div><!-- col-->
      </div><!-- row_group-->
      <div class="row_group">
        <div class="col">
          <label >{{__('Customer Type')}}<i v-if="reservation.SCTH || reservation.SHMS">*</i></label>
          <select v-model="customer.customer_type" @change="customerTypeChange($event.target.value)">
            <option value="" selected="selected">{{__('Choose a type')}}</option>
            <option :value="v" v-for="(t,v) in customer_types" :key="v">{{t}}</option>
          </select>
        </div><!-- col-->
        <div class="col">
          <label >{{__('Nationality')}}<i v-if="reservation.SCTH || reservation.SHMS">*</i></label>
          <select v-model="customer.country_id">
            <option value="" selected="selected">{{__('Choose a country')}}</option>
            <option :value="n.code" v-for="(n, index) in nationalities" :key="index">{{n.title}}</option>
          </select>
        </div><!-- col-->
      </div><!-- row_group-->
      <div class="formgroup" v-if="customer.customer_type == '3'">
        <label>{{__('Visa Number')}} <i v-if="reservation.SHMS">*</i></label>
        <input type="text" v-model="customer.visa_number" :placeholder="__('Visa Number')">
      </div><!-- formgroup-->
      <div class="row_group">
        <div class="col">
          <label >{{__('ID Type')}}<i v-if="reservation.SHMS">*</i></label>
          <select v-model="customer.id_type">
            <option value="" selected="selected">{{__('Choose a type')}}</option>
            <option :value="type.code" v-for="(type, index) in shomos_ids" :key="index">{{type.title}}</option>
          </select>
        </div><!-- col-->
        <div class="col">
          <label>{{customer.id_type == 5 ? __('Passport Number') : __('ID Number')}}<i v-if="reservation.SHMS || reservation.checked_in">*</i></label>
          <input type="text" v-model="customer.id_number" :placeholder="customer.id_type == 5 ? __('Passport Number') : __('ID Number')">
        </div><!-- col-->
      </div>

      <div class="formgroup"  v-if="reservation.SHMS">
          <label>{{__('Version Number')}}<i v-if="reservation.SHMS">*</i></label>
          <input type="number" min="1"  v-model="customer.id_serial_number" :placeholder="__('Version Number')">
      </div><!-- formgroup-->
           <div class="row_group">
                            <div class="col">
<!--                                <label>{{__('Birthday Date')}}<i v-if="SCTH || SHMS">*</i></label>-->
                                <label>{{__('Birthday Date')}} <i v-if="reservation.SHMS">*</i></label>
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
                                />
                            </div><!-- col -->
                            <div class="col">
                                <label>{{__('Birthday Date Hijri')}}</label>
                                <input
                                        type="text"
                                        v-model="hijriDate"
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
                                />
                            </div><!-- col -->
                        </div><!-- row_group -->
      <div class="row_group">
        <div class="col">
          <label>{{__('Gender')}}</label>
          <select v-model="customer.gender">
            <option value="male">{{__('Male')}}</option>
            <option value="female">{{__('Female')}}</option>
          </select>
        </div><!-- col-->
        <div class="col">
          <label >{{__('Email address')}}</label>
          <input type="email" v-model="customer.email" :placeholder="__('Email address')">
        </div><!-- col-->
      </div><!-- row_group-->

      <div class="row_group">
        <div class="col" v-if="reservation.SHMS">
          <label>{{ __('Coming Away') }}</label>
          <select v-model="customer.coming_away">
            <option :value="coming_away.id" v-for="(coming_away, index) in coming_aways" :key="index">
              {{coming_away.title}}
            </option>
          </select>
        </div><!-- col-->

        <div class="col" v-if="showHighlight">
          <label>{{__('Highlight')}}</label>
          <select v-model="customer.highlight_id">
            <option :value="highlight.id" :disabled="!highlight.status" v-for="(highlight, index) in highlights" :key="index">{{highlight.name}}</option>
          </select>
        </div><!-- col-->
        <div class="col" v-if="showHighlight">
          <label v-if="customer.highlight_id">{{__('Color')}}</label>
          <div v-if="customer.highlight_id" v-html="highlight()"></div>
        </div><!-- col-->
      </div><!-- row_group-->

      <div class="action_btns">
        <button class="update_customer_button_attach" :disabled="loading || !customerValidPhone" @click="send(false)">{{__('Attach customer to reservation')}}</button>
        <button class="update_customer_button_attach" :disabled="loading || !customerValidPhone" @click="send(true)">{{__('Attach customer to all reservations')}}</button>
      </div>
    </sweet-modal>
      <!-- customer creditor debtor modal -->
        <customer-creditor-debtor ref="initCustomerCreditorDebitorModal" :customer_id="customer.id" :reservation_id="reservation.id" :total_balance="total_balance" :reservations_count="reservations_count"/>


    <customer-promissories-unfulfilled ref="initPromissoryList" :isLoading="promissoryLoaderActive" :customer="customer" :collection="collection" :paginator="paginator" />

  </div>
</template>

<script>
     import Loading from 'vue-loading-overlay';
    // Import stylesheet
    import 'vue-loading-overlay/dist/vue-loading.css';
     import CustomerPromissoriesUnfulfilled from '../CustomerPromissoriesUnfulfilled';
     import CustomerCreditorDebtor from '../CustomerCreditorDebtor';
     import allCountries from '../../allCountries';
     import VueDatetimeJs from 'vue-datetime-js';
     import HijrahDate from 'hijrah-date';

    export default {
        name: "attach-customer",
        props: ['reservation'],
        components : {
          CustomerPromissoriesUnfulfilled,
          CustomerCreditorDebtor,
          Loading,
        datePicker: VueDatetimeJs,

        },
        computed : {
               maxDate(){
                return moment().format('YYYY-MM-DD');
            },
            maxDateHijriDate(){
                return momentHijri().format('iYYYY-iMM-iDD');
            },
        },
        data: () => {
            return {
                show_color: false,
                loading: null,
                nationalities: [],
                id_types: [],
                customer_types: [],
                coming_aways : [
                    {
                        'id': 7,
                        'title': Nova.app.__('Air'),
                    },
                    {
                        'id': 8,
                        'title': Nova.app.__('Sea'),
                    },
                    {
                        'id': 9,
                        'title': Nova.app.__('Land'),
                    },
                ],
                customer : {
                    id: null,
                    phone: null,
                    email: null,
                    id_number: null,
                    customer_type: null,
                    id_type: null,
                    custmer_type: null,
                    gender: null,
                    id_expire_date: null,
                    country_id: null,
                    birthday_date: null,
                    visa_number: null,
                    id_serial_number: null,
                    coming_away: null,
                },
                unit: {
                    SCTH : false
                },
                customer_endpoint: '/nova-vendor/calender/customer/search?q=',
                highlights: [],
                collection : [],
                paginator : {},
                hijriDate: '',
                current_page : 1,
                promissoryLoaderActive : false,
                total_balance : 0 ,
                reservations_count : 0,
                 commonSelectors: {
                    nationalities: null,
                    customer_types: null,
                    id_types: null,
                    purpose_of_visit: null

                },
                search_attribute : 'by_name',
                searchPlaceHolder : "Search By Name",
                showHighlight : false,
                customerValidPhone: true,
                customerPhoneCountry : null,
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

]
            }
        },
        computed:{
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
          checkThePhone(phone){
            this.customerValidPhone = phone.isValid;
            this.customerPhoneCountry = phone.country.name;
          },
          setCustomersEndPoint(input){
              return `/nova-vendor/calender/customer/search?q=${input}&search_attribute=${this.search_attribute}`;
          },
          getTotalBalance(){
              Nova.request().get(`/nova-vendor/calender/customer-total-balance?id=${this.customer.id}`)
              .then(response => {
                  this.total_balance = parseFloat(response.data.total_balance).toFixed(2);
                  this.reservations_count = response.data.reservations_count;
                  if(this.reservations_count === 1 && this.route_name === 'reservation'){
                      this.reservations_count = 0;
                  }
              })
          },
          openCustomerCreditorDebitorModal(){
                this.$refs.initCustomerCreditorDebitorModal.$refs.customerCreditorDebtorModal.open();
          },
          openPromissoryListModal(){
            this.$refs.initPromissoryList.$refs.promissoriesModal.open();
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
                console.log(this.customer);
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
           getUnfulfilledPromissories(){
                this.promissoryLoaderActive = true;
                Nova.request().get(window.FANDAQAH_API_URL + `/promissories/unfullfield-promissories?status=pending&customer_id=${this.customer.id}&per_page=10&page=${this.current_page}`)
                    .then(response => {
                        this.collection = response.data.data;
                        this.paginator = {
                            currentPage : response.data.meta.current_page ,
                            lastPage : response.data.meta.last_page ,
                            from : response.data.meta.from,
                            to : response.data.meta.to,
                            totalResults : response.data.meta.total,
                            pathPage : response.data.meta.path + '?page=',
                            firstPageUrl : response.data.links.first ,
                            lastPageUrl : response.data.links.last ,
                            nextPageUrl : response.data.links.next ,
                            prevPageUrl : response.data.links.prev ,
                        };

                        this.promissoryLoaderActive = false;

                    })
            },
            showColor() {
                this.show_color = true;
            },
            highlight() {
                let highlight = this.highlights.filter((elem) => {
                    return elem.id == this.customer.highlight_id;
                })

                let style = "border-color: "+highlight[0].color+";background: "+highlight[0].color+";";

                return '<label class="customer_highlight" style="'+style+'">'+highlight[0].name+'</label>'
            },
            getHighlights() {
                axios.get('/apidata/highlights')
                    .then(response => {
                        this.highlights = response.data.data;
                        // this.source_id = (response.data.data[0].status) ? response.data.data[0].id:null;
                    }).catch(err => {
                    this.loading = false;
                })
            },
            openAttachCustomerModal() {
                this.emitClearCustomer();
                this.commonSelectorsFunction();
                this.getNationalities();
                this.getHighlights();
                // this.fillCustomer();
                this.$refs.modal.open();
            },
            fillCustomer(){
                this.loading = true;
                axios.get(`/nova-vendor/calender/customers/${this.reservation.customer_id}`)
                .then(response => {
                    this.customer = {
                        id: response.data.id,
                        name: response.data.name,
                        phone: response.data.phone,
                        email: response.data.email,
                        id_number: response.data.id_number,
                        id_type: response.data.id_type,
                        customer_type: response.data.customer_type,
                        gender: response.data.gender,
                        id_expire_date: response.data.id_expire_date,
                        country_id: response.data.country_id,
                        birthday_date: response.data.birthday_date,
                        highlight_id: response.data.highlight_id,
                        visa_number: response.data.visa_number,
                        id_serial_number: response.data.id_serial_number,
                        coming_away: response.data.coming_away,
                    }
                    Nova.$emit('defaults_to_auto_complete_when_open' , {
                        customer_id : this.customer.id,
                        display : this.customer.name + ' ' + this.customer.phone
                    });
                    this.getTotalBalance();
                    this.getUnfulfilledPromissories();
                    this.customerTypeChange(this.customer.customer_type);
                    this.loading = false;

                })
            },
            formatForDropDown(customer){
                return customer.name + ' ' + customer.phone + ''
            },
            setCustomer(customer){
                this.customer = customer.selectedObject
                if(!this.customer.highlight && this.customer.team_id != Nova.config.user.current_team_id){
                    this.showHighlight = false;
                }
                customer.display = customer.selectedObject.name;
            },
            clearedCustomer: function () {

                this.customer = {
                    id: null,
                    phone: null,
                    email: null,
                    id_number: null,
                    id_type: null,
                    customer_type: null,
                    gender: null,
                    id_expire_date: null,
                    country_id: null,
                    birthday_date: null,
                    highlight_id: null,
                    visa_number: null,
                    id_serial_number: null,
                }
                this.collection = [];
                this.paginator = {};
                this.reservations_count = 1;
                this.total_balance = 0;
                // customer.display = this.reservation.customer.name + ' ' + this.reservation.customer.phone;

                Nova.$emit('defaults_to_auto_complete_when_clear' , {
                    customer_id : null
                });

            },

            emitClearCustomer(){

                this.$refs.autocomplete.clear();
                // this.$refs.autocomplete.focus();
                this.customer = {
                    id: null,
                    phone: null,
                    email: null,
                    id_number: null,
                    id_type: null,
                    customer_type: null,
                    gender: null,
                    id_expire_date: null,
                    country_id: null,
                    birthday_date: null,
                    highlight_id: null,
                    visa_number: null,
                    id_serial_number: null,
                }
                this.collection = [];
                this.paginator = {};
                this.reservations_count = 1;
                this.total_balance = 0;
            },
             invalidPhone(number){
                var regex = /^[+]*[(]{0,1}[\u0030-\u0039\u0660-\u0669]{1,3}[)]{0,1}[-\s\./\u0030-\u0039\u0660-\u0669]*$/g;
                var regex1 = /^[+]*[(]{0,1}[\u0030-\u0039\u0660-\u0669]{1,3}[)]{0,1}[-\s\./\u0030-\u0039\u0660-\u0669]*$/g;

                var result = (regex.test(number) ||  regex1.test(number));

                return result;
            },
            send(attach_option=false) {
               
                if(!this.customer.name || !this.customer.phone){
                    this.$toasted.show(this.__('Please fill all customer info'), {type: 'error'})
                    return;
                }

                if (((!this.customer.visa_number && this.customer.customer_type == '3')||
                    !this.customer.id_number ||
                    !this.customer.name ||
                    !this.customer.phone ||
                    !this.customer.id_type ||
                    !this.customer.country_id ||
                    !this.customer.id_serial_number
                    ) && this.reservation.SHMS ) {
                    this.$toasted.show(this.__('Please fill all customer info'), {type: 'error'})
                    return;
                }

                if (this.customer.phone) {
                    this.customer.phone.replace(/\s/g, "");
                    if (!this.invalidPhone(this.customer.phone)) {
                        this.$toasted.show(this.__('this phone is not valid'), {type: 'error'})
                        return;
                    }
                }

                if(!this.customer.id_number && this.reservation.checked_in){
                    this.$toasted.show(this.__('ID Number is required'), {type: 'error'})
                    return;
                }
                this.loading = true;


                axios
                    .post('/nova-vendor/calender/reservation/update-customer', {
                        id: this.reservation.id,
                        customer: this.customer,
                        attach_customer: true,
                        attach_in_all_reservation : attach_option
                    })
                    .then(response => {
                     
                        if(!response.data.success && response.data.message == 'id_number_taken'){
                            this.loading = false;
                            this.$toasted.show(this.__('Id number is taken , it must be unique'), {type: 'error'})
                            return;
                        }
                        Nova.$emit('attached-customer-successfully');
                        if(attach_option){
                          this.$toasted.show(this.__('Customer has been attached to all related reservations successfully'), {type: 'success'});
                        }else{
                          this.$toasted.show(this.__('Customer has been attached to the reservation successfully'), {type: 'success'});
                        }
                        this.$refs.modal.close();
                        this.loading = false;

                    }).catch(err => {
                    this.loading = false;
                    this.$toasted.show(this.__(err), {type: 'error'})
                })
            },
             commonSelectorsFunction() {
                axios.get('/nova-vendor/calender/unit/commonSelectors')
                    .then((res) => {

                        this.commonSelectors.nationalities = res.data.nationalities;
                        this.commonSelectors.id_types = res.data.id_types;
                        this.commonSelectors.customer_types = res.data.customer_types;
                        this.commonSelectors.purpose_of_visit = res.data.purpose_of_visit;
                        this.customer_types =  this.commonSelectors.customer_types;
                        this.id_types = this.commonSelectors.id_types;

                    })
            },
            customerTypeChange(id){

                 if (id == 1) {
                    this.nationalities = this.commonSelectors.nationalities.filter(n => n.code == Nova.app.currentTeam.country_code)
                    this.customer.country_id = Nova.app.currentTeam.country_code
                    // this.id_types = this.commonSelectors.id_types.filter(n => [1, 2, 3, 4, 5].includes(n.id))
                    this.shomos_ids = this.shomoos_id_types.filter(t => [1, 2, 5].includes(t.code));


                } else if (id == 2) {
                    this.nationalities = this.commonSelectors.nationalities.filter(n => n.is_gcc && n.code != Nova.app.currentTeam.country_code)
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
                // if(id == 1){
                //     this.nationalities = this.nationalities.filter(n => n.code == 113 )
                //     this.customer.country_id = 113
                //     this.id_types = this.customer.id_types.filter(n => [1,2,3,4,5].includes(n.id) )
                // }
                // else if(id == 2){
                //     this.nationalities = this.nationalities.filter(n => n.is_gcc && n.code != 113 )
                //     this.id_types = this.customer.id_types.filter(n => [2,6].includes(n.id) )

                // }else if (id == 3){
                //     this.nationalities = this.nationalities.filter(n => !n.is_gcc)
                //     this.id_types = this.customer.id_types.filter(n => [2].includes(n.id) )

                // }else if (id == 4){
                //     this.nationalities = this.nationalities.filter(n => !n.is_gcc)
                //     this.id_types = this.customer.id_types.filter(n => [2,3,7,8].includes(n.id) )
                // }

            },
            getNationalities(){
                axios
                    .get('/api/countries')
                    .then(response => {
                        this.nationalities = response.data.data;
                    }).catch(err => {
                })
            },
        },

        watch : {
            search_attribute : {
                handler(val){
                    switch (val) {
                        case 'by_id_number':
                            this.searchPlaceHolder = 'Search By ID Number - you should write the full number';
                            break;
                        case 'by_email':
                            this.searchPlaceHolder = 'Search By Email';
                            break;
                        case 'by_phone':
                            this.searchPlaceHolder = 'Search By Phone - you should write the full number';
                            break;
                        default:
                            this.searchPlaceHolder = 'Search By Name';
                            break;
                    }
                }
            },
            customer: {
                handler(val){
                    if(val.id){
                        if(this.customer.id_type != 2){
                            if(val.id_number){
                                 this.customer.id_number = val.id_number.replace(/([٠١٢٣٤٥٦٧٨٩])|([۰۱۲۳۴۵۶۷۸۹])/g, (m, $1, $2) => m.charCodeAt(0) - ($1 ? 1632 : 1776))
                                this.customer.id_number  = val.id_number.replace(/[^\d.\d]/g,'')
                            }

                        }else{
                            if(val.id_number){
                                    this.customer.id_number = val.id_number.replace(/([٠١٢٣٤٥٦٧٨٩])|([۰۱۲۳۴۵۶۷۸۹])/g, (m, $1, $2) => m.charCodeAt(0) - ($1 ? 1632 : 1776))
                                this.customer.id_number  = val.id_number.replace(/[^a-z0-9]/gi,'')
                            }

                        }
                    }

                },
                deep: true
            },
        },
         mounted(){
          // Nova.$on('get-reservation' , (id) => {  this.$refs.modal.close(); })
          // Nova.$on('call-get-unfilfilled-promissories' , (page) => {
          //     this.current_page = page;
          //     this.getUnfulfilledPromissories()
          // })
          // this.customer = this.reservation.customer
          // this.id_types = this.reservation.customer.id_types
          // this.customer_types = this.reservation.customer.customer_types
        }

    }
</script>

<style lang="scss">

.attach_btn {
    button {
      background: #ffffff;
      border-radius: 5px;
      border: 1px solid #4099de;
      min-width: 100px;
      height: 35px;
      line-height: 35px;
      font-size: 15px;
      color: #4099de;
      padding: 0 15px;
      -webkit-transition: all 0.2s ease-in-out;
      -moz-transition: all 0.2s ease-in-out;
      -o-transition: all 0.2s ease-in-out;
      transition: all 0.2s ease-in-out;
      @media (min-width: 320px) and (max-width: 767px) {
        min-width: auto;
      } /* Mobile */
      &:hover {
        background: #4099de;
        color: #ffffff;
      } /* hover */
    } /* button */
} /* attach_btn */

  .creditor_debitor {
              span#creditor_debitor_span {
                margin: 10px 15px;
                border-radius: 5px;
                padding: 10px;
                text-align: center;
                color: #b7791f  !important;
                border: 1px solid #fbd38d;
                background: #fffaf0;
                font-size: 15px  !important;
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
                } /* hover */
              } /* span */
  } /* customerNotes */

.alert-warning {
        margin: 10px 15px;
        border-radius: 5px;
        padding: 10px;
        text-align: center;
        color: #b7791f;
        border: 1px solid #fbd38d;
        background: #fffaf0;
        font-size: 15px;
        cursor: pointer;
}

.update_customer_modal {
  .sweet-content {
    max-height: 500px;
    overflow-y: auto;
    display: block;
    scrollbar-width: thin;
    scrollbar-color: #ccc #f5f5f5;
    &::-webkit-scrollbar {width: 6px;}
    &::-webkit-scrollbar-track {background: #f5f5f5;}
    &::-webkit-scrollbar-thumb {background: #ccc;}
    &::-webkit-scrollbar-thumb:window-inactive {background: #f5f5f5;}
  } /* sweet-content */

  .search_criteria{
                        display: flex;
                        align-items: center;
                        justify-content: flex-start;
                        padding: 0 0 10px;
                        margin-bottom: 10px;
                        border-bottom: 1px solid #ddd;

                        .title {
                            margin: 0 0 0 20px;
                             @media (min-width: 320px) and (max-width: 767px) {
                                margin-bottom: 5px;
                            }
                        }
                        .radios_area {
                        display: flex;
                        align-items: center;
                        flex-wrap: wrap;

                        label.custom_radio {
                            display: block;
                            position: relative;
                            padding: 0 30px 0 0;
                            cursor: pointer;
                            color: #7E8790;
                            line-height: 30px;
                            margin: 0 0 0 50px;
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
                                margin: 0 50px 0  0;
                            } /* rtl */
                            &:hover {
                                .checkmark {background: #fafafa;}
                                p {color: #444444;}
                            } /* hover */
                            input {
                                position: absolute;
                                opacity: 0;
                                cursor: pointer;
                                height: 0;
                                width: 0;
                                &:checked ~ {
                                    .checkmark {
                                        background: #fafafa;
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
                                top: 0;
                                right: 0;
                                height: 20px;
                                width: 20px;
                                background-color: #fcfcfc;
                                border: 1px solid #e8e8e8;
                                border-radius: 100%;
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
                                    background: #0A80D8;
                                    position: absolute;
                                    top: 4px;
                                    right: 4px;
                                    width: 10px;
                                    height: 10px;
                                    opacity: 0;
                                    visibility: hidden;
                                    border-radius: 100%;
                                    -webkit-transform: scale(0);
                                    -moz-transform: scale(0);
                                    -o-transform: scale(0);
                                    transform: scale(0);
                                    -webkit-transition: all 0.2s ease-in-out;
                                    -moz-transition: all 0.2s ease-in-out;
                                    -o-transition: all 0.2s ease-in-out;
                                    transition: all 0.2s ease-in-out;
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
                            &:last-of-type{
                                margin: 0;
                            }
                             @media (min-width: 320px) and (max-width: 767px) {
                                margin: 0;
                            }
                        } /* label */
                        @media (min-width: 320px) and (max-width: 767px) {
                            display: grid;
                            gap: 5px;
                            grid-template-columns: repeat(2, minmax(0, 1fr));
                        }
                    } /* radios_area */
                        @media (min-width: 320px) and (max-width: 767px) {
                            flex-direction: column;
                        }
                    } /* search_criteria */
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
    } /* autocomplete__box */
    ul.autocomplete__results {
      border: 1px solid #ddd !important;
      border-radius: 0 0 5px 5px !important;
      margin: -3px 0 0 0 !important;
      background: #f5f5f5 !important;
     li.autocomplete__results__item {
                                 color: #000 !important;
                                    font-size: 15px !important;
                                    border-bottom: 1px solid #ddd !important;
                                    padding: 10px !important;
                                    display: flex !important;
                                    align-items: center !important;
                                    justify-content: space-between !important;
                                span {
                                    &.user{
                                            display: flex;
                                            align-items: center;
                                            justify-content: flex-start;
                                        &::before{
                                                content: "";
                                                width: 20px;
                                                height: 20px;
                                                background-position: center center;
                                                background-size: 20px 20px;
                                                background-repeat: no-repeat;
                                                margin: 0 0 0 5px;
                                                // background-image: url("data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiA/PjwhRE9DVFlQRSBzdmcgIFBVQkxJQyAnLS8vVzNDLy9EVEQgU1ZHIDEuMS8vRU4nICAnaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkJz48c3ZnIGVuYWJsZS1iYWNrZ3JvdW5kPSJuZXcgMCAwIDQ4IDQ4IiBoZWlnaHQ9IjQ4cHgiIGlkPSJMYXllcl8xIiB2ZXJzaW9uPSIxLjEiIHZpZXdCb3g9IjAgMCA0OCA0OCIgd2lkdGg9IjQ4cHgiIHhtbDpzcGFjZT0icHJlc2VydmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiPjxwYXRoIGNsaXAtcnVsZT0iZXZlbm9kZCIgZD0iTTI0LDQ1QzEyLjQwMiw0NSwzLDM1LjU5OCwzLDI0UzEyLjQwMiwzLDI0LDNzMjEsOS40MDIsMjEsMjFTMzUuNTk4LDQ1LDI0LDQ1eiAgIE0zNS42MzMsMzljLTAuMTU3LTAuMjMxLTAuMzU1LTAuNTE4LTAuNTE0LTAuNzQyYy0wLjI3Ny0wLjM5NC0wLjU1NC0wLjc4OC0wLjgwMi0xLjE3OEMzNC4zMDUsMzcuMDYyLDMyLjkzNSwzNS4yMjQsMjgsMzUgIGMtMS43MTcsMC0yLjk2NS0xLjI4OC0yLjk2OC0zLjA2NkwyNSwzMWMwLTAuMTM1LTAuMDE2LDAuMTQ4LDAsMHYtMWwxLTFjMC43MzEtMC4zMzksMS42Ni0wLjkwOSwyLjM5NS0xLjQ2NGwwLjEzNS0wLjA5MyAgQzI5LjExMSwyNy4wNzQsMjkuOTIzLDI2LjI5NywzMCwyNmwwLjAzNi0wLjM4MUMzMC40MDksMjMuNjk2LDMxLDIwLjE5OCwzMSwxOWMwLTQuNzEtMi4yOS03LTctN2MtNC43NzUsMC03LDIuMjI0LTcsNyAgYzAsMS4yMywwLjU5MSw0LjcxMSwwLjk2Myw2LjYxNmwwLjAzNSwwLjM1MmMwLjA2MywwLjMxMywwLjc5OSwxLjA1NCwxLjQ0OSwxLjQ2MmwwLjA5OCwwLjA2MkMyMC4zMzMsMjguMDQzLDIxLjI3NSwyOC42NTcsMjIsMjkgIGwxLDF2MWMwLjAxNCwwLjEzOCwwLTAuMTQ2LDAsMGwtMC4wMzMsMC45MzRjMCwxLjc3NS0xLjI0NiwzLjA2NC0yLjg4MywzLjA2NGMtMC4wMDEsMC0wLjAwMiwwLTAuMDAzLDAgIGMtNC45NTYsMC4yMDEtNi4zOTMsMi4wNzctNi4zOTUsMi4wNzdjLTAuMjUyLDAuMzk2LTAuNTI4LDAuNzg5LTAuODA3LDEuMTg0Yy0wLjE1NywwLjIyNC0wLjM1NSwwLjUxLTAuNTEzLDAuNzQxICBjMy4yMTcsMi40OTgsNy4yNDUsNCwxMS42MzMsNFMzMi40MTYsNDEuNDk4LDM1LjYzMywzOXogTTI0LDVDMTMuNTA3LDUsNSwxMy41MDcsNSwyNGMwLDUuMzg2LDIuMjUsMTAuMjM3LDUuODUsMTMuNjk0ICBDMTEuMjMyLDM3LjEyOSwxMS42NCwzNi41NjUsMTIsMzZjMCwwLDEuNjctMi43NDMsOC0zYzAuNjQ1LDAsMC45NjctMC40MjIsMC45NjctMS4wNjZoMC4wMDFDMjAuOTY3LDMxLjQxMywyMC45NjcsMzEsMjAuOTY3LDMxICBjMC0wLjEzLTAuMDIxLTAuMjQ3LTAuMDI3LTAuMzczYy0wLjcyNC0wLjM0Mi0xLjU2NC0wLjgxNC0yLjUzOS0xLjQ5NGMwLDAtMi40LTEuNDc2LTIuNC0zLjEzM2MwLDAtMS01LjExNi0xLTcgIGMwLTQuNjQ0LDEuOTg2LTksOS05YzYuOTIsMCw5LDQuMzU2LDksOWMwLDEuODM4LTEsNy0xLDdjMCwxLjYxMS0yLjQsMy4xMzMtMi40LDMuMTMzYy0wLjk1NSwwLjcyMS0xLjgwMSwxLjIwMi0yLjU0MywxLjU0NiAgYy0wLjAwNSwwLjEwOS0wLjAyMywwLjIwOS0wLjAyMywwLjMyMWMwLDAtMC4wMDEsMC40MTMtMC4wMDEsMC45MzRoMC4wMDFDMjcuMDMzLDMyLjU3OCwyNy4zNTUsMzMsMjgsMzNjNi40MjQsMC4yODgsOCwzLDgsMyAgYzAuMzYsMC41NjUsMC43NjcsMS4xMjksMS4xNDksMS42OTRDNDAuNzQ5LDM0LjIzNyw0MywyOS4zODYsNDMsMjRDNDMsMTMuNTA3LDM0LjQ5Myw1LDI0LDV6IiBmaWxsLXJ1bGU9ImV2ZW5vZGQiLz48L3N2Zz4=");
                                                background-image: url("data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiA/PjxzdmcgaWQ9Ik91dGxpbmVkIiB2aWV3Qm94PSIwIDAgMzIgMzIiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PHRpdGxlLz48ZyBpZD0iRmlsbCI+PHBhdGggZD0iTTI0LDE3SDhhNSw1LDAsMCwwLTUsNXY3SDVWMjJhMywzLDAsMCwxLDMtM0gyNGEzLDMsMCwwLDEsMywzdjdoMlYyMkE1LDUsMCwwLDAsMjQsMTdaIi8+PHBhdGggZD0iTTE2LDE1YTYsNiwwLDEsMC02LTZBNiw2LDAsMCwwLDE2LDE1Wk0xNiw1YTQsNCwwLDEsMS00LDRBNCw0LDAsMCwxLDE2LDVaIi8+PC9nPjwvc3ZnPg==");
                                        }
                                    }
                                    &.phone{
                                         width: auto !important;
                                          color: #000 !important;
                                        direction: ltr !important;
                                        display: flex !important;
                                        align-items: center !important;
                                        justify-content: flex-start !important;

                                        &::before{
                                                content: "";
                                                width: 20px;
                                                height: 20px;
                                                background-position: center center;
                                                background-size: 20px 20px;
                                                background-repeat: no-repeat;
                                                margin: 0 5px 0 0;
                                                background-image: url("data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiA/PjwhRE9DVFlQRSBzdmcgIFBVQkxJQyAnLS8vVzNDLy9EVEQgU1ZHIDEuMS8vRU4nICAnaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkJz48c3ZnIGhlaWdodD0iNTEycHgiIGlkPSJMYXllcl8xIiBzdHlsZT0iZW5hYmxlLWJhY2tncm91bmQ6bmV3IDAgMCA1MTIgNTEyOyIgdmVyc2lvbj0iMS4xIiB2aWV3Qm94PSIwIDAgNTEyIDUxMiIgd2lkdGg9IjUxMnB4IiB4bWw6c3BhY2U9InByZXNlcnZlIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIj48cGF0aCBkPSJNNDE1LjksMzM1LjVjLTE0LjYtMTUtNTYuMS00My4xLTgzLjMtNDMuMWMtNi4zLDAtMTEuOCwxLjQtMTYuMyw0LjNjLTEzLjMsOC41LTIzLjksMTUuMS0yOSwxNS4xYy0yLjgsMC01LjgtMi41LTEyLjQtOC4yICBsLTEuMS0xYy0xOC4zLTE1LjktMjIuMi0yMC0yOS4zLTI3LjRsLTEuOC0xLjljLTEuMy0xLjMtMi40LTIuNS0zLjUtMy42Yy02LjItNi40LTEwLjctMTEtMjYuNi0yOWwtMC43LTAuOCAgYy03LjYtOC42LTEyLjYtMTQuMi0xMi45LTE4LjNjLTAuMy00LDMuMi0xMC41LDEyLjEtMjIuNmMxMC44LTE0LjYsMTEuMi0zMi42LDEuMy01My41Yy03LjktMTYuNS0yMC44LTMyLjMtMzIuMi00Ni4ybC0xLTEuMiAgYy05LjgtMTItMjEuMi0xOC0zMy45LTE4Yy0xNC4xLDAtMjUuOCw3LjYtMzIsMTEuNmMtMC41LDAuMy0xLDAuNy0xLjUsMWMtMTMuOSw4LjgtMjQsMjAuOS0yNy44LDMzLjJjLTUuNywxOC41LTkuNSw0Mi41LDE3LjgsOTIuNCAgYzIzLjYsNDMuMiw0NSw3Mi4yLDc5LDEwNy4xYzMyLDMyLjgsNDYuMiw0My40LDc4LDY2LjRjMzUuNCwyNS42LDY5LjQsNDAuMyw5My4yLDQwLjNjMjIuMSwwLDM5LjUsMCw2NC4zLTI5LjkgIEM0NDIuMywzNzAuOCw0MzEuNSwzNTEuNiw0MTUuOSwzMzUuNXogTTQwNC40LDM5MS40Yy0yMCwyNC4yLTMxLjUsMjQuMi01Mi4zLDI0LjJjLTIwLjMsMC01MS44LTE0LTg0LjItMzcuMyAgYy0zMS0yMi40LTQ0LjgtMzIuNy03NS45LTY0LjZjLTMyLjktMzMuNy01My42LTYxLjgtNzYuNC0xMDMuNWMtMjQuMS00NC4xLTIxLjQtNjMuNC0xNi41LTc5LjNjMi42LTguNSwxMC40LTE3LjYsMjEtMjQuMiAgYzAuNS0wLjMsMS0wLjcsMS42LTFjNS4zLTMuNCwxNC4xLTkuMSwyMy43LTkuMWM4LDAsMTUuMSw0LDIxLjksMTIuM2wxLDEuMmMyNS41LDMxLjIsNDUuNCw1OC44LDMwLjQsNzkuMiAgYy0xMC42LDE0LjMtMTYuMiwyNC0xNS4zLDM0YzAuOCw5LjcsNy4zLDE3LDE3LjEsMjhsMC43LDAuOGMxNi4xLDE4LjIsMjAuNywyMywyNy4xLDI5LjVjMS4xLDEuMSwyLjIsMi4zLDMuNSwzLjZsMS44LDEuOSAgYzcuNCw3LjcsMTEuNSwxMS45LDMwLjMsMjguNGwxLjEsMWM4LDcsMTMuOSwxMi4xLDIyLjUsMTIuMWM4LjksMCwxOC43LTUuNiwzNy4zLTE3LjVjMS45LTEuMiw0LjYtMS45LDgtMS45ICBjMjEuNywwLDU5LjEsMjQuOCw3Mi4yLDM4LjNDNDE3LDM1OS43LDQyMywzNjguOSw0MDQuNCwzOTEuNHoiLz48L3N2Zz4=");
                                        }
                                    }
                                }
                                &:hover {
                                    background: #f0f0f0;
                                }


                            } /* autocomplete__results__item */
    } /* autocomplete__results */
  } /* formgroup */
  .row_group {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    flex-wrap: wrap;
    margin: 0 -10px;
    @media (min-width: 320px) and (max-width: 767px) {
      margin: 0;
    } /* Mobile */
    .col {
      width: 50%;
      padding: 0 10px;
      margin: 0 0 10px;
      @media (min-width: 320px) and (max-width: 767px) {
        width: 100%;
        padding: 0;
      } /* Mobile */
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
        } /* rtl */
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
          } /* rtl */
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
            } /* iti-flag */
            span.dropdown-arrow {
              width: auto;
              margin: 0 5px 0 0;
              display: inline-block !important;
              font-size: inherit !important;
              [dir="ltr"] & {
                margin: 0 0 0 5px;
              } /* ltr */
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
              } /* ltr */
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
                color: #666 !important;
                font-size: inherit !important;
              } /* span */
            } /* li */
          } /* ul */
        } /* dropdown */
        input {
          width: 76%;
          border-radius: 0 !important;
          height: 38px !important;
          border: none !important;
          padding: 0 10px 0 0 !important;
          [dir="ltr"] & {
            padding: 0 0 0 10px !important;
          } /* ltr */
        } /* input */
      } /* vue-tel-input */
    } /* col */
  } /* row_group */
  label {
    display: block;
    margin: 0 auto 5px;
    font-size: 15px;
    i {
      display: inline-block !important;
      margin: 0 5px 0 0;
      color: #f00 !important;
      font-style: normal;
    } /* i */
  } /* label */
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
    } /* readonly */
    &.customer_search {
      background: transparent !important;
      border: none !important;
      height: 40px !important;
      border-radius: 0 !important;
      padding: 0 10px !important;
      display: block;
    } /* customer_search */
  } /* input */
  label.customer_highlight {
    height: 40px;
    border-radius: 4px;
    text-align: center;
    font-size: 15px;
    line-height: 40px;
    color: #000;
    margin: 0 auto;
  } /* customer_highlight */
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
  } /* select */
  button.update_customer_button_attach , button.update_customer_button {
    background: #4099de;
    padding: 0 !important;
    border-radius: 5px;
    border: 1px solid #4099de;
    min-width: 100px;
    height: 35px;
    line-height: 35px;
    font-size: 15px;
    color: #ffffff;
    width: 40% !important;
    text-align: center !important;
    justify-content: center !important;
    -webkit-transition: all 0.2s ease-in-out;
    -moz-transition: all 0.2s ease-in-out;
    -o-transition: all 0.2s ease-in-out;
    transition: all 0.2s ease-in-out;
    &:hover {
      background: #0071C9;
      border-color: #0071C9;
    } /* hover */
    @media (min-width: 320px) and (max-width: 767px) {
      margin: 2px !important;
      padding: 4px !important;
      font-size: 13px !important;
      line-height: 14px !important;
    } /* Mobile */
  } /* update_customer_button_attach */

  button.clear_customer {
    background: #4099de;
    border-radius: 5px;
    border: 1px solid #4099de;
    min-width: 100px;
    height: 35px;
    line-height: 35px;
    font-size: 15px;
    padding: 0 15px;
    color: #ffffff;
    // width: 100%;
    margin: 5px auto 0;
    -webkit-transition: all 0.2s ease-in-out;
    -moz-transition: all 0.2s ease-in-out;
    -o-transition: all 0.2s ease-in-out;
    transition: all 0.2s ease-in-out;
    &:hover {
      background: #0071C9;
      border-color: #0071C9;
    } /* hover */
  } /* clear_customer */
} /* update_customer_modal */

.action_btns {
  display: flex;
  justify-content: space-around;
}
</style>
