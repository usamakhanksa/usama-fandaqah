<template>
  <div class="item_reservation_button">
    <button v-permission="'view guests'" class="main_button" @click="open">{{__('Guests')}} ({{$parent.reservation.reservation_guests.length}})</button>
    <sweet-modal class="add_guests_modal" :enable-mobile-fullscreen="false" :pulse-on-block="false" :title="__('Guests')" overlay-theme="dark" ref="modal">
      <div class="loading_area" v-if="loading"><loader /></div>
      <div class="inputs_row" v-permission="'add guests'">
        <div class="search_criteria">
            <div class="title">{{__('Search Guests')}} : </div>
            <div class="radios_area">
                <label class="custom_radio" for="by_name">
                    <input type="radio" id="by_name" value="by_name" v-model="search_attribute">
                    <span class="checkmark"></span>
                    <p>{{__('Name')}}</p>
                </label><!-- custom_radio -->
                <label class="custom_radio" for="by_id_number" >
                    <input type="radio" id="by_id_number" value="by_id_number" v-model="search_attribute">
                    <span class="checkmark"></span>
                    <p>{{__('ID Number')}}</p>
                </label><!-- custom_radio -->
                <label class="custom_radio" for="by_email">
                    <input type="radio" id="by_email" value="by_email" v-model="search_attribute">
                    <span class="checkmark"></span>
                    <p>{{__('Email')}}</p>
                </label><!-- custom_radio -->
                <label class="custom_radio" for="by_phone" >
                    <input type="radio" id="by_phone" value="by_phone" v-model="search_attribute">
                    <span class="checkmark"></span>
                    <p>{{__('Phone')}}</p>
                </label><!-- custom_radio -->

            </div><!-- radios_area -->
        </div>
        <div class="formgroup" style="width: 100%; margin-bottom: 15px;">
            <autocomplete
                    :source="setCustomersEndPoint"
                    :resultsValue="guest.id"
                    :resultsProperty="guest.id"
                    :resultsDisplay="formatForDropDown"
                    @selected="selectedCustomer"
                    @enter="selectedCustomer1"
                    @clear="clearedCustomer"
                    delay="500"
                    :placeholder="__(searchPlaceHolder)"
            >
            <div slot="noResults" style="display:flex; align-content:center;">
                <p>{{__('No customers found')}} ... </p>
            </div>
            </autocomplete>
        </div><!-- formgroup -->

         <!-- Customer list of guests -->
        <div class="input_group" style="width: 100%;" v-if="reservation.customer && reservation.customer.guests.length">
          <label>{{__('Select Guest')}}</label>
          <select  v-model="selectedGuest" @change="setGuest">
            <option value="" selected="selected">{{__('Select Guest')}}</option>
            <option :value="g" v-for="(g, i) in reservation.customer.guests" :key="i">{{g.name}}</option>
          </select>
        </div><!-- input_group -->
        <!-- name -->
        <div class="input_group">
          <label>{{__('Name')}}<i>*</i></label>
          <input type="text" v-model="guest.name" :placeholder="__('Name')"/>
        </div><!-- input_group -->

        <!-- gender -->
        <div class="input_group">
          <label>{{__('Gender')}} </label>
          <select v-model="guest.gender">
            <option value="" selected="selected">{{__('Choose a gender')}}</option>
            <option :value="v" v-for="(p,v) in genders">{{__(p)}}</option>
          </select>
        </div><!-- input_group -->

        <!-- date of birth -->
        <div class="input_group">
          <label>{{__('Birthday Date')}}<i v-if="reservation.SHMS">*</i></label>
          <input
          type="text"
          :value="guest.birthday_date | formatDateWithoutTime"
          @input="value => guest.birthday_date = value"
          id="custom_georgian_input"
          :placeholder="__('Birthday Date')"
  />
  <date-picker
          :auto-submit="true"
          @input="getGeorgianDate($event)"
          v-model="guest.birthday_date"
          :locale="calendarLocale"
          :calendar="'georgian'"
          min="1900-01-01"
          :max="maxDate"
          type="date"
          element="custom_georgian_input"
          format="YYYY-MM-DD"
  />
          <!-- <flat-pickr
            v-model="guest.birthday_date"
            @input="getGeorgianDate($event)"
            class="form-control"
            :placeholder="__('Birthday Date')"
            :config="config"
          >
          </flat-pickr> -->
          <!-- <vcc-date-picker
            class='v-date-picker'
            :locale="vcc_local"
            mode='single'
            v-model="guest.birthday_date"
            show-caps
            is-expanded
            :columns="$screens({ default: 1  })"
            :popoverExpanded="true"
            :popover="{placement: 'bottom' , visibility : 'click'}"
            :input-props="{readonly: true}"
            :titlePosition="vcc_local == 'ar' ? 'left' : 'right'"
            ref="vcalendar"
            :masks = "{
              title: 'MMMM YYYY',
              weekdays: 'WWW',
              navMonths: 'MMM',
              input: ['DD-MM-YYYY', 'D'],
              dayPopover: 'D',
              data: ['DD-MM-YYYY', 'D']
            }"
            :max-date="new Date()"

          >
          </vcc-date-picker> -->

        </div><!-- input_group -->
        <div class="input_group">
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

          </div>


        <!-- Customer relation_type -->
        <div class="input_group">
          <label>{{__('Relationship Type')}}
            <!-- <i v-if="reservation.SHMS">*</i></label> -->
          </label>
          <select v-model="guest.relation_type">
            <option value="" selected="selected">{{__('Choose a relationship type')}}</option>
            <option :value="v" v-for="(p,v) in relationships">{{__(p)}}</option>
          </select>
        </div><!-- input_group -->

        <!-- Customer Type -->
        <div class="input_group">
          <label>{{__('Customer Type')}}<i v-if="reservation.SHMS">*</i></label>
          <select v-model="guest.customer_type" @change="customerTypeChange($event.target.value)">
            <option value="" selected="selected">{{__('Choose a type')}}</option>
            <option :value="v" v-for="(t,v) in customer_types">{{t}}</option>
          </select>
        </div><!-- input_group -->

        <!-- country_id -->
        <div class="input_group">
          <label>{{__('Nationality')}}<i v-if="reservation.SHMS">*</i></label>
          <select v-model="guest.country_id">
            <option value="" selected="selected">{{__('Choose a country')}}</option>
            <option :value="n.code" v-for="(n, index) in nationalities">{{n.title}}</option>
          </select>
        </div><!-- input_group -->

        <!-- ID Type -->
        <div class="input_group">
          <label>{{__('ID Type')}}<i v-if="reservation.SHMS">*</i></label>
          <select v-model="guest.id_type">
            <option value="" selected="selected">{{__('Choose a type')}}</option>
            <option :value="type.code" v-for="(type, index) in shomos_ids">{{type.title}}</option>
          </select>
        </div><!-- input_group -->

        <!-- ID Number -->
        <div class="input_group">
          <label>{{ guest.id_type == 5 ? __('Passport Number') : __('ID Number')}} <i v-if="reservation.SHMS">*</i>
          </label>
          <input type="text" v-model="guest.id_number" :placeholder="guest.id_type == 5 ? __('Passport Number') : __('ID Number')">
        </div><!-- input_group -->

        <!-- ID Serial Number -->
        <div class="input_group" v-if="reservation.SHMS">
          <label>{{__('ID Serial Number')}}<i v-if="reservation.SHMS">*</i></label>
          <input type="text" v-model="guest.id_serial_number" :placeholder="__('ID Serial Number')">
        </div><!-- input_group -->

        <!-- visa Number -->
        <div class="input_group" v-if="guest.customer_type == '3'">
          <label>{{__('Visa Number')}}<i v-if="reservation.SHMS">*</i></label>
          <input type="text" v-model="guest.visa_number" :placeholder="__('Visa Number')">
        </div><!-- input_group -->

        <button class="add_guests_button" :disabled="loading" @click="send" v-html="__(buttonText)"></button>
      </div><!-- inputs_row -->
      <div class="no_guests" v-if="reservation.reservation_guests.length === 0">
        <div class="icon"></div>
        <span>{{__('No Guests has been attached to this reservation yet!')}}</span>
      </div><!-- no_guests -->
      <div class="guests_table" v-if="!loading && reservation.reservation_guests.length >0">
        <table>
          <tr>
            <th>{{__('Name')}}</th>
            <th>{{__('ID Number')}}</th>
            <th>{{__('Gender')}}</th>
            <th>{{__('Relation Type')}}</th>
            <th>{{__('Customer Type')}}</th>
            <th>{{__('Id Type')}}</th>
            <th>{{__('Serial Number')}}</th>
            <th>{{__('Nationality')}}</th>
            <th v-if="reservation.SHMS">{{__('Shomoos Status')}}</th>
            <th>
          <a :href="'/home/print/guests/' +  reservation.id "  target="_blank" :title="__('Print')" class="print"></a>
            </th>
          </tr>
          <tr v-for="(guest,i) in reservation.reservation_guests" :key="i">
            <td>{{ guest.name }}</td>
            <td>{{ guest.id_number ? guest.id_number : '-'  }}</td>
            <td>{{ guest.gender ? __(genders[guest.gender]) : '-' }}</td>
            <td>{{ guest.relation_type ? __(relationships[guest.relation_type]) : '-' }}</td>
            <td>{{ guest.guest_type_string ? guest.guest_type_string : '-' }}</td>
            <td>{{ guest.id_type_string ? guest.id_type_string : '-' }}</td>
            <td>{{ guest.id_serial_number ? guest.id_serial_number : '-' }}</td>
            <td>{{ guest.nationality_string ? guest.nationality_string : '-' }}</td>
            <td v-if="reservation.SHMS">
              <span
                        v-if="guest.shomoos_escort_id"
                        class="indicators enabled"
                        data-toggle="tooltip"
                        data-placement="top"
                        title="Tooltip on top"
                      ></span>
                      <span
                        v-else
                        class="indicators not_enabled"
                        data-toggle="tooltip"
                        data-placement="top"
                        title="Tooltip on top"
                      ></span>
            </td>
            <td><button class="delete_button" v-permission="'delete guests'" @click="deleteGuest(guest.id)"></button></td>
          </tr>
        </table>
      </div><!-- guests_table -->
    </sweet-modal>
    <GuestDeleteConfirm ref="guestRef" :id="guestIdToDelete" :reservation_id="reservation.id"/>
  </div>
</template>

<script>
    import GuestDeleteConfirm from './GuestDeleteConfirm.vue';
    import HijrahDate from 'hijrah-date';
    import momentHijri from 'moment-hijri';
    import flatPickr from 'vue-flatpickr-component';
    import 'flatpickr/dist/flatpickr.css';
    import VueDatetimeJs from 'vue-datetime-js'
    import { Arabic } from "flatpickr/dist/l10n/ar.js"
    export default {
        name: "Guests",
        props: ['reservation'],
        components: {
          GuestDeleteConfirm,
          flatPickr,
          datePicker: VueDatetimeJs,
        },
        computed: {
          maxDate(){
                return moment().format('YYYY-MM-DD');
            },
            maxDateHijriDate(){
                return momentHijri().format('iYYYY-iMM-iDD');
            },
        },
        data: () => {
            return {
                loading: null,
                selectedGuest : {},
                guest: {
                    id: null,
                    name: null,
                    gender: null,
                    relation_type: null,
                    customer_type: null,
                    id_type: null,
                    birthday_date:null,
                    visa_number: null,
                    id_serial_number: null,
                    id_number: null,
                    country_id : null,
                    calendarLocale: 'en'
                },
                idtypes: [],
                search_attribute : 'by_name',
                searchPlaceHolder : "Search Customers",
                countries: [],
                nationalities: [],
                id_types: [],
                customer_types: [],
                genders: {
                    1: 'Male',
                    2: 'Female',
                },
                relationships: {
                    1: 'Son',
                    2: 'Daughter',
                    3: 'Wife',
                    4: 'Brother',
                    5: 'Sister',
                    6: 'Father',
                    7: 'Mother',
                    8: 'Husband',
                    9: 'Other',
                },
                SHMS : false,
                buttonText : 'Add Guest',
                guestIdToDelete : null,
                vcc_local : Nova.config.local,
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
                ],
                hijriDate: '',
                config: {
                  wrap: false, // set wrap to true only when using 'input-group'
                  altFormat: 'Y-m-d',
                  altInput: true,
                  dateFormat: 'Y-m-d',
                  locale: Nova.config.local == 'en' ? 'en' : Arabic, // locale for this instance only
                  enableTime: false,
                  time_24hr: false,
                  disableMobile: true,
                  minuteIncrement : 1,
                  clickOpens : true,
                  maxDate : new Date()
                },
                processType : 'add'
            }
        },
        created(){
            this.customer_types = this.reservation.customer ? this.reservation.customer.customer_types : null;
        },
        methods: {
            selectedCustomer(customer) {
                this.guest = customer.selectedObject

                if(!this.guest.highlight && this.guest.team_id != Nova.config.user.current_team_id){
                    this.showHighlight = false;
                }
                this.customerTypeChange(this.customer.customer_type)
                customer.display = customer.selectedObject.name;
                Nova.request().get('/nova-vendor/calender/checkCustomerNotes?id=' + customer.selectedObject.id)
                    .then(res  => {
                        this.customerNotes = res.data
                        // Nova.$emit('customer-creditor-debtor' , customer.selectedObject.id);

                    }).catch((err) => {
                    console.log(err)
                })

            },
            selectedCustomer1(customer) {
                customer.display = customer.selectedObject.name
            },
            setCustomersEndPoint(input){

                return `/nova-vendor/calender/guest/search?q=${input}&search_attribute=${this.search_attribute}`;
            },
            formatForDropDown(customer) {
                // return customer.name
                return customer.name + '  ||  ' + customer.phone
            },
            open() {
                this.getNationalities();
                this.guest = {};
                this.selectedGuest = {};
                this.buttonText = 'Add Guest';
                this.$refs.modal.open()
            },
            setGuest(){

              this.guest = {
                    id: this.selectedGuest.id,
                    reservation_id : this.selectedGuest.reservation_id,
                    name: this.selectedGuest.name,
                    gender: this.selectedGuest.gender,
                    relation_type: this.selectedGuest.relation_type,
                    customer_type: this.selectedGuest.customer_type,
                    birthday_date: this.selectedGuest.birthday_date,
                    id_type: this.selectedGuest.id_type,
                    visa_number: this.selectedGuest.visa_number,
                    id_serial_number: this.selectedGuest.id_serial_number,
                    id_number: this.selectedGuest.id_number,
                    country_id: this.selectedGuest.country_id,
                }

              if(!this.reservation.checked_in){
                  this.$toasted.show(this.__('Please make sure that main guest has checked in first'), {type: 'error'});
                  return;
              }
              // validation to check if this escort attached on another active reservation or not
              // active reservation means reservation with checked_in , but not checked_out
              if(this.selectedGuest.reservation && this.selectedGuest.reservation.checked_in && !this.selectedGuest.reservation.checked_out &&  (this.selectedGuest.reservation_id != this.reservation.id)) {
                  this.$toasted.show(this.__('You can not attach this escort cause it is already attached on an active reservation'), {type: 'error'})
                  return;
              }

                if(this.guest.customer_type){
                    this.customerTypeChange(this.guest.customer_type);
                }

                if(!this.reservation.reservation_guests.length){
                    this.buttonText = 'Add Guest';
                }else{
                    this.buttonText = 'Add Guest';
                    this.processType = 'add';
                    this.reservation.reservation_guests.forEach(item => {
                            // console.log('item');
                            // console.log(item);
                            // console.log('selected Guest');
                            // console.log(this.selectedGuest);
                        if(item.id === this.selectedGuest.id){
                            this.buttonText = 'Update Guest';
                            this.processType = 'update';
                            return;
                        }
                        // else{
                        //     this.buttonText = 'Update Guest';
                        //     return;
                        // }
                    });
                }

            },
            send() {
                if(!this.reservation.checked_in){
                  this.$toasted.show(this.__('Please make sure that main guest has checked in first'), {type: 'error'})
                  return;
                }

                // if (!this.guest.name || !this.guest.id_number || !this.guest.gender || !this.guest.relation_type) {
                if (!this.guest.name) {
                    this.$toasted.show(this.__('Please fill all guest info'), {type: 'error'})
                    return;
                }

                if ( (!this.guest.id_serial_number || !this.guest.id_type || !this.guest.customer_type || !this.guest.birthday_date) && this.reservation.SHMS) {
                    this.$toasted.show(this.__('Please fill all customer info'), {type: 'error'})
                    return;
                }

                this.loading = true;
                this.guest.customer_id = this.reservation.customer_id

                let message = 'Guest added successfully';

                if(this.guest.id && this.guest.reservation_id){
                    message = 'Guest updated successfully'
                }
                axios
                    .post('/nova-vendor/calender/reservation/guest', {
                        guest: this.guest,
                        current_reservation_id : this.reservation.id,
                        processType : this.processType
                    })
                    .then(response => {


                        if(response.data && !response.data.success){
                             this.$toasted.show(this.__(response.data.message), {type: 'error'});
                             this.loading = false;
                             return;
                        }
                        this.$emit('update-reservation')
                        this.guest.id = null;
                        this.guest.name = null;
                        this.guest.id_number = null;
                        this.guest.gender = null;
                        this.guest.birthday_date = null;
                        this.guest.relation_type = null;
                        this.guest.customer_type = null;
                        this.guest.id_type = null;
                        this.guest.visa_number = null;
                        this.guest.id_serial_number = null;
                        this.guest.country_id = null;
                        this.$toasted.show(this.__('Process went successfully'), {type: 'success'});
                        this.loading = false;
                        this.selectedGuest = {};
                    }).catch(err => {
                    this.loading = false;
                    this.$toasted.show(this.__(err), {type: 'error'})
                })

            },
            deleteGuest(id) {
                this.guestIdToDelete = id;
                setTimeout(() => {
                    this.$refs.guestRef.$refs.deleteGuestModal.open();
                }, 0);
            },
            getNationalities(){
                axios
                    .get('/api/countries')
                    .then(response => {
                        this.nationalities = response.data.data;
                        this.countries = response.data.data;
                    }).catch(err => {
                })
            },
            customerTypeChange(id){
                if(id == 1){
                    this.nationalities = this.countries.filter(n => n.code ==  Nova.app.currentTeam.country_code )
                    this.guest.country_id =  Nova.app.currentTeam.country_code
                    // this.id_types = this.idtypes.filter(n => [1,2,3,4,5].includes(n.id) )
                    this.shomos_ids = this.shomoos_id_types.filter(t => [1, 2, 5].includes(t.code));

                }
                else if(id == 2){
                    this.nationalities = this.countries.filter(n => n.is_gcc && n.code !=  Nova.app.currentTeam.country_code )
                    // this.id_types = this.idtypes.filter(n => [2,6].includes(n.id) )
                    this.shomos_ids = this.shomoos_id_types.filter(t => [3,5].includes(t.code));



                }else if (id == 3){
                    this.nationalities = this.countries.filter(n => n.code != Nova.app.currentTeam.country_code)
                    // this.id_types = this.idtypes.filter(n => [2].includes(n.id) )
                    this.shomos_ids = this.shomoos_id_types.filter(t => [5,6].includes(t.code));


                }else if (id == 4){
                    this.nationalities = this.countries.filter(n => n.code != Nova.app.currentTeam.country_code)
                    // this.id_types = this.idtypes.filter(n => [2,3,7,8].includes(n.id) )
                    this.shomos_ids = this.shomoos_id_types.filter(t => [ 4, 5].includes(t.code));

                }
            },

            ltrim(str) {
                if (str == null) return str;
                return str.trim();
            },
            getHijriDate(event) {
                let hijriArr = event.split("-");

                let yearTransformed = this.convertNumbers(hijriArr[0]);
                let monthTransformed = this.convertNumbers(hijriArr[1]);
                let dayTransformed = this.convertNumbers(hijriArr[2]);

                let hijrahDate = new HijrahDate(parseInt(yearTransformed), parseInt(monthTransformed) - 1, dayTransformed);

                this.hijriDate = yearTransformed + '-' + monthTransformed + '-' + dayTransformed;
                this.guest.birthday_date = moment(hijrahDate.toGregorian()).format('YYYY-MM-DD');

            },
            getGeorgianDate(event) {
                let georgianArr = event.split("-");

                let yearTransformed = this.convertNumbers(georgianArr[0]);
                let monthTransformed = this.convertNumbers(georgianArr[1]);
                let dayTransformed = this.convertNumbers(georgianArr[2]);

                let date = new Date(parseInt(yearTransformed), parseInt(monthTransformed) - 1, dayTransformed);
                let hijrahDate = new HijrahDate(date);
                this.hijriDate = hijrahDate.format('yyyy-M-d');

                this.guest.birthday_date = yearTransformed + '-' + monthTransformed + '-' + dayTransformed;
            },
            // Replace arabic & persian numbers to english numbers
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
        },
        mounted(){

          this.calendarLocale = Nova.config.local;
            Nova.$on('update-reservation' , () => {
                this.buttonText = 'Add Guest';
                this.guest = {};
                this.selectedGuest = {};
            });
        },
    }
</script>

<style lang="scss">
.print{
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='19.339' height='19.339' viewBox='0 0 19.339 19.339'%3E%3Cpath d='M17.471,17.471v1.934a1.934,1.934,0,0,1-1.934,1.934H7.8a1.934,1.934,0,0,1-1.934-1.934V17.471H3.934A1.934,1.934,0,0,1,2,15.537v-5.8A1.94,1.94,0,0,1,3.934,7.8H5.868V3.934A1.94,1.94,0,0,1,7.8,2h7.735a1.934,1.934,0,0,1,1.934,1.934V7.8H19.4a1.934,1.934,0,0,1,1.934,1.934v5.8A1.934,1.934,0,0,1,19.4,17.471Zm0-1.934H19.4v-5.8H3.934v5.8H5.868V13.6A1.94,1.94,0,0,1,7.8,11.669h7.735A1.934,1.934,0,0,1,17.471,13.6ZM15.537,7.8V3.934H7.8V7.8ZM7.8,13.6v5.8h7.735V13.6Z' transform='translate(-2 -2)' fill='%23ffffff'/%3E%3C/svg%3E");

    display: block;
    height: 25px;
    width: 25px;
    outline: none;
    background-position: center center;
    background-size: 20px 20px;
    background-repeat: no-repeat;
    color: #0000;

    }
span.indicators {
  display: inline-block;
  position: relative;
  &::after {
    content: "";
    width: 10px;
    height: 10px;
    border-radius: 100%;
    float: right;
    margin: 5px 0 0 10px;
  } /* after */
  &.enabled {
    &::after {
      background: #38c172;
    } /* after */
  } /*enabled  */
  &.maintenance {
    &::after {
      background: #aab8c0;
    } /* after */
  } /*maintenance  */
  &.cleaning {
    &::after {
      background: #ff9100;
    } /* after */
  } /*cleaning  */
  &.not_enabled {
    &::after {
      background: #ff0000;
    } /* after */
  } /*not_enabled  */
  &.timeout {
    &::after {
      background: #a07930;
    } /* after */
  } /*not_enabled  */

  &.awaiting_payment {
    &::after {
      background: #4299e1;
    } /* after */
  } /*not_enabled  */
} /* span */
.search_criteria{
    display: flex;
    align-items: center;
    justify-content: flex-start;
    padding: 0 0 10px;
    border-bottom: 1px solid #ddd;
    margin-bottom: 10px;

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
.add_guests_modal {
  .sweet-content {
    overflow: auto;
    max-height: 500px;
    display: block;
    position: relative;
    scrollbar-width: thin;
    scrollbar-color: #ccc #f5f5f5;
    &::-webkit-scrollbar {width: 6px;}
    &::-webkit-scrollbar-track {background: #f5f5f5;}
    &::-webkit-scrollbar-thumb {background: #ccc;}
    &::-webkit-scrollbar-thumb:window-inactive {background: #f5f5f5;}
  } /* sweet-content */
  .loading_area {
    position: absolute;
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    background: #ffffff90;
    svg {
      width: 60px;
      height: auto;
      display: block;
      margin: 0 auto;
    } /* svg */
  } /* loading_area */
  .inputs_row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    border-bottom: 1px solid #dddddd;
    padding: 0 0 15px;
    margin: 0 0 15px;
    .input_group {
      width: 48%;
      margin: 0 0 15px;
      @media (min-width: 320px) and (max-width: 767px) {
        width: 100%;
        margin: 0 0 10px;
      } /* Mobile */
      label {
        display: block;
        color: #000;
        font-size: 15px;
        margin: 0 auto 5px;
        i {
          display: inline-block !important;
          margin: 0 5px 0 0;
          color: #f00 !important;
          font-style: normal;
        } /* i */
      } /* label */
      input {
        background: #fafafa;
        width: 100%;
        border: 1px solid #dddddd !important;
        height: 40px;
        padding: 0 10px;
        font-size: 15px;
        border-radius: 5px !important;
        &[disabled="disabled"] {
          background: #ddd;
          border-color: #c4c4c4 !important;
          cursor: not-allowed;
        } /* disabled */
      } /* input */
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
        &[disabled="disabled"] {
          background-color: #ddd !important;
          border-color: #c4c4c4;
          cursor: not-allowed;
        } /* disabled */
        [dir="ltr"] & {
          background-position: 97% center;
        } /* ltr */
      } /* select */
    } /* input_group */
    button.add_guests_button {
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
      -moz-transition: all 0.2s ease-in-out;
      -o-transition: all 0.2s ease-in-out;
      transition: all 0.2s ease-in-out;
      &:hover {
        background: #0071C9;
        border-color: #0071C9;
      } /* hover */
    } /* add_guests_button */
  } /* inputs_row */
  .no_guests {
    margin: 30px auto;
    text-align: center;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-wrap: wrap;
    .icon {
      background-image: url("data:image/svg+xml,%3C%3Fxml version='1.0'%3F%3E%3Csvg xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' version='1.1' id='Capa_1' x='0px' y='0px' viewBox='0 0 17.924 17.924' style='enable-background:new 0 0 17.924 17.924;' xml:space='preserve' width='512px' height='512px'%3E%3Cg%3E%3Cg%3E%3Cg%3E%3Cpolygon points='12.475,8.868 12.481,8.859 12.48,8.858 ' data-original='%23030104' class='active-path' data-old_color='%23030104' fill='%23DDDDDD'/%3E%3Cpath d='M17.211,10.107c0,0-0.183-0.249-0.616-0.415c0,0-0.036-0.011-0.092-0.029 c-0.386-0.181-0.752-0.297-0.752-0.297c-0.078-0.028-0.146-0.056-0.21-0.083c-0.261-0.13-0.45-0.279-0.45-0.279 s-0.002-0.002-0.203-0.203c0.361-0.374,0.626-0.882,0.709-1.368c0.064-0.066,0.129-0.172,0.157-0.341 c0.052-0.188,0.117-0.517,0.002-0.67c-0.007-0.008-0.013-0.015-0.02-0.022c0.108-0.396,0.246-1.214-0.244-1.772 c-0.044-0.056-0.318-0.384-0.906-0.558l-0.28-0.097c-0.463-0.143-0.754-0.174-0.766-0.176c-0.021-0.002-0.043,0-0.063,0.005 c-0.016,0.005-0.071,0.019-0.113,0.013c-0.111-0.016-0.277,0.041-0.307,0.053c-0.038,0.015-0.934,0.374-1.205,1.208 c-0.025,0.067-0.134,0.422,0.01,1.292c-0.021,0.014-0.04,0.032-0.058,0.054c-0.116,0.153-0.051,0.48,0.002,0.669 c0.027,0.167,0.092,0.272,0.155,0.339c0.072,0.48,0.302,0.942,0.605,1.296c0,0,0.23,0.398,0.259,0.435 c0.729,1.08,0.821,3.162,0.83,3.396l0.001,0.015l-0.001,0.016c-0.012,0.728-0.374,0.981-0.568,1.065 c-0.094,0.042-0.192,0.079-0.291,0.117c0.049-0.072,0.097-0.197,0.1-0.418c0,0-0.071-3.059-0.688-3.972 c0,0-0.176-0.24-0.593-0.399c0,0-0.034-0.011-0.089-0.028c-0.37-0.174-0.722-0.285-0.722-0.285 c-0.075-0.027-0.142-0.053-0.202-0.08c-0.25-0.125-0.433-0.268-0.433-0.268s-0.002-0.002-0.195-0.196 c0.347-0.36,0.602-0.848,0.681-1.314c0.063-0.063,0.125-0.166,0.152-0.328c0.049-0.181,0.112-0.497,0.001-0.644 c-0.006-0.007-0.012-0.014-0.018-0.021c0.104-0.381,0.236-1.167-0.235-1.704c-0.043-0.053-0.307-0.369-0.871-0.536l-0.27-0.092 C8.97,3.348,8.69,3.317,8.678,3.316c-0.02-0.002-0.041,0-0.061,0.006C8.601,3.326,8.549,3.34,8.508,3.335 c-0.106-0.016-0.266,0.039-0.294,0.05C8.178,3.399,7.317,3.744,7.056,4.546c-0.024,0.065-0.128,0.406,0.01,1.242 C7.045,5.802,7.027,5.819,7.011,5.84C6.9,5.986,6.963,6.302,7.012,6.483c0.027,0.161,0.089,0.263,0.15,0.326 c0.069,0.461,0.29,0.906,0.582,1.246L7.661,8.182L7.659,8.174L7.656,8.191C7.657,8.188,7.659,8.184,7.66,8.182v0.001L7.655,8.191 C7.538,8.556,6.137,8.982,6.137,8.982C5.721,9.141,5.545,9.381,5.545,9.381c-0.616,0.913-0.688,3.971-0.688,3.971 c0.003,0.219,0.05,0.342,0.097,0.414c-0.095-0.035-0.192-0.071-0.283-0.111c-0.194-0.084-0.557-0.339-0.568-1.066v-0.016v-0.016 c0.009-0.234,0.102-2.314,0.817-3.376c0.039-0.054,0.167-0.214,0.403-0.379c0,0,0-0.001-0.001-0.001 c0.361-0.374,0.626-0.882,0.709-1.368C6.095,7.367,6.16,7.261,6.189,7.092c0.052-0.188,0.117-0.517,0.001-0.67 C6.184,6.414,6.177,6.407,6.171,6.4c0.108-0.396,0.246-1.214-0.244-1.772C5.886,4.572,5.611,4.244,5.023,4.07l-0.28-0.097 C4.28,3.83,3.989,3.799,3.977,3.797c-0.021-0.002-0.043,0-0.063,0.005C3.898,3.808,3.843,3.822,3.8,3.816 C3.69,3.8,3.524,3.857,3.494,3.869C3.456,3.883,2.561,4.242,2.289,5.077c-0.025,0.067-0.133,0.422,0.01,1.292 C2.278,6.383,2.258,6.401,2.241,6.423c-0.116,0.153-0.05,0.48,0.001,0.669c0.027,0.167,0.092,0.272,0.155,0.339 c0.072,0.48,0.302,0.942,0.606,1.296L2.917,8.859L2.915,8.85L2.911,8.868c0.001-0.003,0.004-0.007,0.005-0.01v0.001L2.911,8.868 C2.79,9.248,1.332,9.691,1.332,9.691c-0.433,0.166-0.616,0.415-0.616,0.415C0.075,11.056,0,13.171,0,13.171 c0.008,0.482,0.217,0.533,0.217,0.533c1.473,0.657,3.785,0.773,3.785,0.773c0.125,0.004,0.24-0.003,0.356-0.01l0.003,0.012 c0,0,0.87-0.045,1.881-0.23c1.219,0.295,2.46,0.358,2.46,0.358c0.119,0.003,0.231-0.004,0.342-0.011l0.003,0.012 c0,0,1.295-0.066,2.538-0.379c1.053,0.201,1.979,0.248,1.979,0.248c0.124,0.004,0.24-0.003,0.356-0.01l0.003,0.012 c0,0,2.312-0.117,3.785-0.773c0,0,0.208-0.051,0.216-0.534C17.927,13.173,17.852,11.058,17.211,10.107z' data-original='%23030104' class='active-path' data-old_color='%23030104' fill='%23DDDDDD'/%3E%3C/g%3E%3C/g%3E%3C/g%3E%3C/svg%3E%0A");
      background-size: 90px;
      width: 92px;
      height: 60px;
      display: block;
      margin: 0 auto;
      background-position: center center;
      background-repeat: no-repeat;
    } /* icon */
    span {
      display: block;
      width: 100%;
      font-size: 16px;
      margin: 7px auto 0;
      color: #dddddd;
    } /* span */
  } /* no_guests */
  .guests_table {
    overflow-x: auto;
    max-width: 100%;
    margin: 5px auto 10px;
    max-height: 300px;
    scrollbar-width: thin;
    scrollbar-color: #ccc #f5f5f5;
    &::-webkit-scrollbar {width: 6px;}
    &::-webkit-scrollbar-track {background: #f5f5f5;}
    &::-webkit-scrollbar-thumb {background: #ccc;}
    &::-webkit-scrollbar-thumb:window-inactive {background: #f5f5f5;}
    table {
      width: 100%;
      th {
        background: #4a5568;
        text-align: center !important;
        color: #ffffff;
        font-weight: normal;
        font-size: 15px;
        padding: 10px 5px;
        border: 1px solid #5E697C;
        vertical-align: middle;
        white-space: nowrap;
      } /* th */
      td {
        background: #ffffff;
        border: 1px solid #dddddd;
        text-align: center;
        vertical-align: middle;
        padding: 5px;
        font-size: 15px;
        color: #000;
        button.delete_button {
          background-image: url("data:image/svg+xml,%3C%3Fxml version='1.0'%3F%3E%3Csvg xmlns='http://www.w3.org/2000/svg' height='512px' viewBox='-40 0 427 427.00131' width='512px'%3E%3Cg%3E%3Cpath d='m232.398438 154.703125c-5.523438 0-10 4.476563-10 10v189c0 5.519531 4.476562 10 10 10 5.523437 0 10-4.480469 10-10v-189c0-5.523437-4.476563-10-10-10zm0 0' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%23C12020'/%3E%3Cpath d='m114.398438 154.703125c-5.523438 0-10 4.476563-10 10v189c0 5.519531 4.476562 10 10 10 5.523437 0 10-4.480469 10-10v-189c0-5.523437-4.476563-10-10-10zm0 0' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%23C12020'/%3E%3Cpath d='m28.398438 127.121094v246.378906c0 14.5625 5.339843 28.238281 14.667968 38.050781 9.285156 9.839844 22.207032 15.425781 35.730469 15.449219h189.203125c13.527344-.023438 26.449219-5.609375 35.730469-15.449219 9.328125-9.8125 14.667969-23.488281 14.667969-38.050781v-246.378906c18.542968-4.921875 30.558593-22.835938 28.078124-41.863282-2.484374-19.023437-18.691406-33.253906-37.878906-33.257812h-51.199218v-12.5c.058593-10.511719-4.097657-20.605469-11.539063-28.03125-7.441406-7.421875-17.550781-11.5546875-28.0625-11.46875h-88.796875c-10.511719-.0859375-20.621094 4.046875-28.0625 11.46875-7.441406 7.425781-11.597656 17.519531-11.539062 28.03125v12.5h-51.199219c-19.1875.003906-35.394531 14.234375-37.878907 33.257812-2.480468 19.027344 9.535157 36.941407 28.078126 41.863282zm239.601562 279.878906h-189.203125c-17.097656 0-30.398437-14.6875-30.398437-33.5v-245.5h250v245.5c0 18.8125-13.300782 33.5-30.398438 33.5zm-158.601562-367.5c-.066407-5.207031 1.980468-10.21875 5.675781-13.894531 3.691406-3.675781 8.714843-5.695313 13.925781-5.605469h88.796875c5.210937-.089844 10.234375 1.929688 13.925781 5.605469 3.695313 3.671875 5.742188 8.6875 5.675782 13.894531v12.5h-128zm-71.199219 32.5h270.398437c9.941406 0 18 8.058594 18 18s-8.058594 18-18 18h-270.398437c-9.941407 0-18-8.058594-18-18s8.058593-18 18-18zm0 0' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%23C12020'/%3E%3Cpath d='m173.398438 154.703125c-5.523438 0-10 4.476563-10 10v189c0 5.519531 4.476562 10 10 10 5.523437 0 10-4.480469 10-10v-189c0-5.523437-4.476563-10-10-10zm0 0' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%23C12020'/%3E%3C/g%3E%3C/svg%3E%0A");
          background-repeat: no-repeat;
          background-position: center center;
          background-size: 20px;
          width: 30px;
          height: 30px;
        } /* delete_button */
      } /* td */
    } /* table */
  } /* guests_table */
} /* add_guests_modal */
</style>
