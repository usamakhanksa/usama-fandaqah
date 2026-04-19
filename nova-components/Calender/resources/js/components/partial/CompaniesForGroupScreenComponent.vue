<template>
  <div class="companies_component mb-5">
      <!-- <div class="reservation_type" :style="reservation_type == 'group' ? 'border-bottom: 1px solid #ddd;' : ''">
            <div class="title">{{__('Reservation Type')}} :</div>
            <div class="radios_area">
                <label class="custom_radio" for="single">
                    <input type="radio" id="single" value="single" v-model="reservation_type" >
                    <span class="checkmark"></span>
                    <p>{{__('Single')}}</p>
                </label>
                <label class="custom_radio" for="group" >
                    <input type="radio" id="group" value="group" v-model="reservation_type">
                    <span class="checkmark"></span>
                    <p>{{__('Group')}}</p>
                </label>
            </div>
        </div> -->

        <div class="reservation_type" v-if="reservation_type == 'group'">
            <div class="title">{{__('Group Reservation Type')}} :</div>
            <div class="radios_area">
                <label class="custom_radio" for="company" >
                    <input type="radio" id="company" value="company" v-model="reservation_group_type">
                    <span class="checkmark"></span>
                    <p>{{__('Company')}}</p>
                </label><!-- custom_radio -->
                <label class="custom_radio" for="individual">
                    <input type="radio" id="individual" value="individual" v-model="reservation_group_type">
                    <span class="checkmark"></span>
                    <p>{{__('Individual')}}</p>
                </label><!-- custom_radio -->
            </div><!-- radios_area -->
        </div>
        <div class="search_company" v-if="reservation_type == 'group' && reservation_group_type == 'company'">

            <div class="label_with_button mb-2">
                <label class="custom_company_label" for="group_auto_complete">
                    {{__('Search For Company')}}
                </label>
                <button  v-permission="'create companies'" @click="openAddCompanyModal">{{__('Add Company')}}</button>
            </div>
            <div class="search_company">
                <autocomplete
                    id="group_auto_complete"
                    ref="autoComplete"
                    :source="companies_endpoint"
                    :resultsDisplay="formatForDropDown"
                    @results="companiesFound"
                    @noResults="noCompaniesFound"
                    @selected="companySelected"
                    @clear="companiesInputCleared"
                    :placeholder="__('Search by Name, Phone or Email')"
                >
                <div slot="noResults" style="display:flex; align-content:center;">
                    <p>{{__('No companies found')}} ... </p>
                </div>

                </autocomplete>
            </div>

                <div class="attachable_reservations" v-if="attachable_reservations.length">
                    <company-main-reservation-selector v-if="attachable_reservations" :attachable_reservations="attachable_reservations" />
                </div>

        </div>

        <div class="search_company" v-if="reservation_type == 'group' && reservation_group_type == 'individual'">

            <div class="label_with_button mb-2">
                <label class="custom_company_label" for="group_auto_complete">
                    {{__('Search For Individual')}}
                </label>
                <button  v-permission="'create companies'" @click="openAddIndividualModal">{{__('Add Individual')}}</button>
            </div>
            <div class="search_company" v-if="!hideSearch">
                <autocomplete
                    id="group_auto_complete"
                    ref="autoComplete"
                    :source="companies_endpoint"
                    :resultsDisplay="formatForDropDown"
                    @results="companiesFound"
                    @noResults="noCompaniesFound"
                    @selected="companySelected"
                    @clear="companiesInputCleared"
                    :placeholder="__('Search by Name, or Phone')"
                >
                <div slot="noResults" style="display:flex; align-content:center;">
                    <p>{{__('No indviduals found')}} ... </p>
                </div>

                </autocomplete>
            </div>
            <div class="search_company" v-else>
                <div v-if="hideSearch" class="selected_individual_alert mt-2">
                    <p><b>{{__('Individual Info')}} </b></p>
                    <p>{{__('Name')}} : {{company.name}}</p>
                    <div style="display: flex;"> {{ __('Phone') }} : <span class="block" style="direction: ltr !important;">{{ company.phone }}</span></div>
                </div>
            </div>

                <div class="attachable_reservations" v-if="attachable_reservations.length">
                    <company-main-reservation-selector v-if="attachable_reservations" :attachable_reservations="attachable_reservations" />
                </div>

        </div>

        <sweet-modal :enable-mobile-fullscreen="false"  :pulse-on-block="false" :title="__('Add Company')" overlay-theme="dark" ref="addCompanyReservation" class="add-company relative">

            <loading :active="loading" :loader="'spinner'" :color="'#7e7d7f'" :opacity="0.7"  :is-full-page="false"></loading>
            <!-- Validation -->
            <company-validation v-if="showErrorBag"  :error-bag="errorBag" />
            <div class="row_group">
                <div class="col">
                <label>{{__('Company Name')}}<span>*</span></label>
                <input type="text" v-model="company.name" :placeHolder="__('Company Name')">
                </div>
                <div class="col">
                <label>{{__('Company Phone')}}<span>*</span></label>
                <input type="tel" v-model="company.phone" :placeHolder="__('Company Phone')">
                </div>
            </div>
            <div class="row_group">
                <div class="col">
                <label>{{__('City')}}<span>*</span></label>
                <input type="text" v-model="company.city" :placeHolder="__('City')">
                </div>
                <div class="col">
                <label>{{__('Company Address')}}<span>*</span></label>
                <input type="text" v-model="company.address" :placeHolder="__('Company Address')">
                </div>
            </div>
            <div class="row_group">
                <div class="col">
                <label>{{__('Person In Charge')}}<span>*</span></label>
                <input type="text" v-model="company.person_incharge_name" :placeHolder="__('Person In Charge')">
                </div>
                <div class="col">
                <label>{{__('Person In Charge Phone')}}</label>
                <vue-tel-input
                        :defaultCountry="'SA'"
                        @onInput="checkThePhone($event)"
                        :required="true"
                        :enabledFlags="true"
                        name="phone"
                        :placeholder="__('Person In Charge Phone')"
                        :inputOptions="{ showDialCode: false, tabindex: 0 }"
                        v-model="company.person_incharge_phone"
                        class="mb-2"
                >
                </vue-tel-input>
                <p v-if="!personInChargeValidPhone" style="color:#ce1025;text-align: justify;">{{__('Phone number is not valid')}}</p>
                
                <!-- <input type="text" v-model="company.person_incharge_phone" :placeHolder="__('Person In Charge Phone')"> -->
                </div>
            </div>
            <div class="row_group">
                <div class="col">
                <label>{{__('Company Email')}}</label>
                <input type="text" v-model="company.email" :placeHolder="__('Company Email')">
                </div>
                <div class="col">
                <label>{{__('Tax number')}}</label>
                <input type="text" v-model="company.tax_number" :placeHolder="__('Tax number')">
                </div>
            </div>
            <button :disabled="disabled" @click="createNewCompany">{{__('Add Company')}}</button>
        </sweet-modal>

        <sweet-modal :enable-mobile-fullscreen="false"  :pulse-on-block="false" :title="__('Add Individual')" overlay-theme="dark" ref="addIndividualReservation" class="add-individual relative">

            <loading :active="loading" :loader="'spinner'" :color="'#7e7d7f'" :opacity="0.7"  :is-full-page="false"></loading>
            <!-- <company-validation v-if="showErrorBag"  :error-bag="errorBag" />
            <div class="row_group">
                <div class="col">
                <label>{{__('Individual Name')}}<span>*</span></label>
                <input type="text" v-model="individual.name" :placeHolder="__('Individual Name')">
                </div>
                <div class="col">
                <label>{{__('Individual Phone')}}<span>*</span></label>
                <input type="tel" v-model="individual.phone" :placeHolder="__('Individual Phone')">
                </div>
            </div> -->


            <div class="customer_details">
                    <div class="search_criteria">
                        <div class="title">{{__('Search Customers')}}</div>
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
                    <div class="formgroup">
                        <autocomplete
                                :source="setCustomersEndPoint"
                                :resultsValue="customer.id"
                                :resultsProperty="customer.id"
                                :resultsDisplay="formatForDropDownCustomer"
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
                    <div class="loader_item" v-if="loading">
                        <loader/>
                    </div><!-- loader_item -->
                    <div v-if="!loading">
                        <div class="row_group">
                            <div class="col">
                                <label>{{__('Name')}}<i>*</i></label>
                                <input type="text" v-model="customer.name" :placeholder="__('Name')">
                            </div><!-- col -->
                            <div class="col">
                                <label>{{__('Phone Number')}}<i>*</i></label>
                                <vue-tel-input
                                        :defaultCountry="'SA'"
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

                            </div><!-- col -->
                        </div><!-- row_group -->
                        <div class="row_group">
                            <div class="col">
                                <label>{{__('Customer Type')}}
                                    <!-- <i v-if="SCTH || SHMS">*</i> -->
                                </label>
                                <select v-model="customer.customer_type"
                                        @change="customerTypeChange($event.target.value)">
                                    <option value="" selected="selected">{{__('Choose a type')}}</option>
                                    <option :value="v" v-for="(t,v) in customer_types" :key="v">{{t}}</option>
                                </select>
                            </div><!-- col -->
                            <div class="col">
                                <label>{{__('Nationality')}}
                                    <!-- <i v-if="SCTH || SHMS">*</i> -->
                                </label>
                                <select v-model="customer.country_id">
                                    <option value="" selected="selected">{{__('Choose a country')}}</option>
                                    <option :value="n.code" v-for="(n, index) in nationalities" :key="index">
                                        {{n.title}}
                                    </option>
                                </select>
                            </div><!-- col -->
                        </div><!-- row_group -->
                        <div class="row_group">
                            <div class="col">
                                <label>{{__('Id Type')}}
                                    <!-- <i v-if="SHMS">*</i> -->
                                </label>
                                <select v-model="customer.id_type">
                                    <option value="" disabled selected="selected">{{__('Choose a type')}}</option>

                                    <option  :value="item.code" v-for="(item, index) in shomos_ids" :key="index">{{item.title}}</option>
                                    <!-- <option v-if="!SHMS" :value="item.id" v-for="(item, index) in id_types" :key="index">{{item.title}}</option> -->
                                </select>
                            </div><!-- col -->
                            <div class="col">
                                <label>{{ customer.id_type == 5 ? __('Passport Number') : __('ID Number')}}
                                    <!-- <i v-if="SHMS">*</i> -->
                                </label>
                                <input type="text" v-model="customer.id_number" :placeholder="customer.id_type == 5 ? __('Passport Number') : __('ID Number')">
                            </div>
                        </div><!-- row_group -->
                        <div class="row_group">
                            <div class="col">
                                <label>{{__('Gender')}}
                                    <!-- <i v-if="SCTH">*</i> -->
                                </label>
                                <select v-model="customer.gender">
                                    <option value="male">{{__('Male')}}</option>
                                    <option value="female">{{__('Female')}}</option>
                                </select>
                            </div><!-- col -->
                            <div class="col">
                                <label>{{__('Email address')}}</label>
                                <input type="email" v-model="customer.email" :placeholder="__('Email address')">
                            </div><!-- col -->
                        </div><!-- row_group -->
                        <div class="row_group">
                            <div class="col">
<!--                                <label>{{__('Birthday Date')}}<i v-if="SCTH || SHMS">*</i></label>-->
                                <label>{{__('Birthday Date')}}
                                    <!-- <i v-if="SHMS">*</i> -->
                                </label>
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
                                <label>{{__('Purpose of visit')}}</label>
                                <select v-model="purpose">
                                    <option :value="v" v-for="(p,v) in purpose_of_visit" :key="v">{{p}}</option>
                                </select>
                            </div><!-- col -->
                            <div class="col" v-if="showHighlight">
                                <label>{{__('Highlight')}}</label>
                                <select v-model="customer.highlight_id">
                                    <option :value="highlight.id" :disabled="!highlight.status"
                                            v-for="(highlight, index) in highlights" :key="index">
                                        {{highlight.name}}
                                    </option>
                                </select>
                            </div><!-- col -->
                        </div><!-- row_group -->
                        <div class="row_group">
                            <div class="col" v-if="showHighlight">
                                <label v-if="customer.highlight_id">{{__('Color')}}</label>
                                <div v-if="customer.highlight_id" v-html="highlight()"></div>
                            </div><!-- col -->
                            <div class="col" v-if="customer.customer_type == '3'">
                                <label>{{__('Visa Number')}}
                                    <!-- <i v-if="SHMS">*</i> -->
                                </label>
                                <input type="text" v-model="customer.visa_number" :placeholder="__('Visa Number')">
                            </div><!-- col -->
                        </div><!-- row_group -->
                        <div class="row_group" v-if="SHMS">
                            <div class="col">
                                <label>{{__('Coming Away')}}</label>
                                <select v-model="customer.coming_away">
                                    <option :value="p.id" v-for="(p,v) in coming_aways" :key="v">{{__(p.title)}}</option>
                                </select>
                            </div><!-- col -->
                            <div class="col">
                                <label>{{__('Version Number')}}
                                    <!-- <i v-if="SHMS">*</i> -->
                                </label>
                                <input type="number" min="1" v-model="customer.id_serial_number"
                                                   class=" border rounded w-full py-2 px-3 border-gray-300 leading-tight focus:outline-none focus:shadow-outline"
                                                   :placeholder="__('Version Number')">
                            </div><!-- col -->
                        </div><!-- row_group -->
                    </div><!-- loading -->
                </div><!-- customer_details -->
            

            <button class="mt-3 w-full" :disabled="disabled" @click="createNewIndividual">{{__('Add Individual')}}</button>
        </sweet-modal>


  </div>
</template>

<script>
    import CompanyMainReservationSelector from './CompanyMainReservationSelector';
    import CompanyValidation from './CompanyValidation';
    import Loading from 'vue-loading-overlay';
    import 'vue-loading-overlay/dist/vue-loading.css';
    import allCountries from '../../allCountries.js';
    export default {
        name: "companies-for-group-screen-component",
        components : {
            Loading,
            CompanyValidation,
            CompanyMainReservationSelector
        },
        props : [],
        data(){
            return {
                company_id: null,
                reservation_type : 'group',
                disabled : false,
                company: {
                    name:                   null,
                    phone:                  null,
                    city:                   null,
                    address:                null,
                    person_incharge_name:   null,
                    person_incharge_phone:  null,
                    email:                  null,
                    tax_number:             null
                },
                individual: {
                    name:                   null,
                    phone:                  null,
                },
                errorBag:               null,
                showErrorBag : false,
                loading: false,
                attachable_reservations : [],
                attachable_reservation : null,
                reservation_group_type : null,
                search_attribute : 'by_name',
                searchPlaceHolder : "Search By Name",
                showHighlight : true,
                customerValidPhone: true,
                customerPhoneCountry : null,
                commonSelectors: {
                    nationalities: null,
                    customer_types: null,
                    id_types: null,
                    purpose_of_visit: null

                },
                customer_types: [],
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
                customer: {
                    id: null,
                    phone: null,
                    email: null,
                    id_number: null,
                    customer_type: 1,
                    id_type: 1,
                    country_id: 113,
                    id_expire_date: null,
                    birthday_date: null,
                    gender: null,
                    label: null,
                    highlight_id: null,
                    coming_away: null,
                    visa_number: null,
                    id_serial_number: null,
                },
                SCTH: false,
                SHMS: false,
                hideSearch : false,
                personInChargeValidPhone: true,
                customerPhoneCountry : null,

            }
        },
        watch: {
            customer: {
                handler(val){

                    if(val.id === null){
                        Nova.$emit('clear-collection');
                    }
                },
                deep: true
            },
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
            reservation_type : {
                handler(val){
                    if(val == 'group'){
                        this.reservation_group_type = 'company';
                    }
                },
                deep: true
            },
            reservation_group_type : {
                handler(val){
                    this.companiesInputCleared();
                    this.formatForDropDown();
                    this.companies_endpoint =  `/nova-vendor/new/customers/company/target/search?entity=${val}&q=`;
                    Nova.$emit('set-reservation-group-type', val);
                },
                deep: true
            },
        },
        methods : {
            invalidPhone(number){
                var regex = /^[+]*[(]{0,1}[\u0030-\u0039\u0660-\u0669]{1,3}[)]{0,1}[-\s\./\u0030-\u0039\u0660-\u0669]*$/g;
                var regex1 = /^[+]*[(]{0,1}[\u0030-\u0039\u0660-\u0669]{1,3}[)]{0,1}[-\s\./\u0030-\u0039\u0660-\u0669]*$/g;

                var result = (regex.test(number) ||  regex1.test(number));

                return result;
            },
            clearedCustomer(customer) {
                this.customer = {
                    id: null,
                    phone: null,
                    email: null,
                    id_number: null,
                    id_type: null,
                    id_expire_date: null,
                    country_id: null,
                    birthday_date: null,
                    gender: null,
                    highlight_id: null,
                }
            },
            formatForDropDown(company) {
                if(!company){
                    return '';
                }
                return company.name + ' || ' + company.phone.replace('+','')
            },
            formatForDropDownCustomer(customer) {
                return customer.name + '  ||  ' + customer.phone
            },
            companiesFound(){
            },
            noCompaniesFound(){
                this.company_id = null;
                Nova.$emit('companyIdSelected' , this.company_id);
                Nova.$emit('attachable_reservation' , null);
            },
            companiesInputCleared(){
                this.company_id = null;
                this.attachable_reservations = [];
                Nova.$emit('companyIdSelected' , this.company_id );
                // Nova.$emit('attachable_reservation' , null);
            },
            companySelected(obj){
                this.company_id = obj.value;
                Nova.$emit('companyIdSelected' , this.company_id);
                this.getCompanAttachableReservations();
            },
            openAddCompanyModal() {
                this.clearInputs();
                this.$refs.addCompanyReservation.open();
            },
            openAddIndividualModal(){
                this.customer = {
                    id: null,
                    phone: null,
                    email: null,
                    id_number: null,
                    id_type: null,
                    id_expire_date: null,
                    country_id: null,
                    birthday_date: null,
                    gender: null,
                    highlight_id: null,
                }
                this.clearIndividualInputs();
                this.$refs.addIndividualReservation.open();
            },
            createNewIndividual(){

                
                if (this.customer.phone) {
                    if (!this.invalidPhone(this.customer.phone)) {
                        this.$toasted.show(this.__('this phone is not valid'), {type: 'error'})
                        return;
                    }
                }
                if (this.customer.phone) {
                    this.customer.phone.replace(/\s/g, "");
                }

                if (this.customer.email) {
                    var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
                    if (!re.test(String(this.customer.email).toLowerCase())) {
                        this.$toasted.show(this.__('this email is not valid'), {type: 'error'})
                        return;
                    }
                }
                if (!this.customer.name || !this.customer.phone) {
                    this.$toasted.show(this.__('Please fill customer info'), {type: 'error'})
                    return;
                }


                // set birthday_date to Zero for SCTH
                if(!this.customer.birthday_date && this.SCTH){
                    this.customer.birthday_date = 0 ;
                }

                this.loading = true;
                this.disabled = true;

                axios.post(`/nova-vendor/new/customers/individuals/createFromCustomer`, {customer : this.customer})
                .then(response => {

                    this.loading = false;
                    this.disabled = false;

                    if(!response.data.success && response.data.message == 'id_number_taken'){
                        this.$toasted.show(this.__('Id number is taken , it must be unique'), {type: 'error'})
                        return;
                    }

                    if(response.data.success){
                        this.$refs.addIndividualReservation.close();
                        this.$toasted.show(this.__('Individual added successfully'), {type: 'success'});
                        this.company_id = response.data.company.id;
                        this.company = response.data.company;
                        this.customer = response.data.customer;
                        this.hideSearch = true;
                        Nova.$emit('companyIdSelected' , this.company_id);
                        Nova.$emit('individual-created-from-customer', this.customer.id);
                        this.getCompanAttachableReservations();
                    }



                    
                })

               
                // this.loading = true;
                // this.disabled = true;
                // this.showErrorBag = false;
                // axios.post(`/nova-vendor/new/customers/individuals/create`, {
                //     team_id                 : Nova.config.user.current_team_id,
                //     user_id                 : Nova.config.user.id,
                //     name                    : this.individual.name,
                //     phone                   : this.individual.phone
                // })
                // .then(response => {
                //     this.clearIndividualInputs();
                    
                //     this.loading = false;
                //     this.disabled = false;
                //     this.showErrorBag = false;
                //     this.$refs.addIndividualReservation.close();
                //     this.$toasted.show(this.__('Individual added successfully'), {type: 'success'});
                // }).catch(error => {
                //     this.loading = true;
                //     this.disabled = true;
                //     if (error.response) {

                //         this.loading = false;
                //         this.disabled = false;
                //         this.showErrorBag = true;
                //         this.errorBag = error.response.data.errors
                //     }
                // });


            },
            createNewCompany(){
                if (!this.company.name)
                {
                    this.$toasted.show(this.__("Company name is required"), {type: 'error'})
                    return
                }
                if (!this.company.phone)
                {
                    this.$toasted.show(this.__("Company phone is required"), {type: 'error'})
                    return
                }
                if (!this.company.city)
                {
                    this.$toasted.show(this.__("City is required"), {type: 'error'})
                    return
                }
                if (!this.company.address)
                {
                    this.$toasted.show(this.__("Company address is required"), {type: 'error'})
                    return
                }
                if (!this.company.person_incharge_name)
                {
                    this.$toasted.show(this.__("Person in charge name is required"), {type: 'error'})
                    return
                }


                if(this.company.person_incharge_phone && !this.personInChargeValidPhone){
                    this.$toasted.show(this.__("Person in charge phone is not valid"), {type: 'error'})
                    return
                }

                this.loading = true;
                this.disabled = true;
                this.showErrorBag = false;
                axios.post(`/nova-vendor/new/customers/companies/create`, {
                        team_id                 : Nova.config.user.current_team_id,
                        user_id                 : Nova.config.user.id,
                        name                    : this.company.name,
                        phone                   : this.company.phone,
                        city                    : this.company.city,
                        address                 : this.company.address,
                        person_incharge_name    : this.company.person_incharge_name,
                        person_incharge_phone   : this.company.person_incharge_phone,
                        email                   : this.company.email,
                        tax_number              : this.company.tax_number
                    })
                    .then(response => {

                        this.clearInputs();
                        this.loading = false;
                        this.disabled = false;
                        this.showErrorBag = false;
                        this.$refs.addCompanyReservation.close();
                        this.$toasted.show(this.__('Company added successfully'), {type: 'success'});
                    }).catch(error => {
                        this.loading = true;
                        this.disabled = true;
                        if (error.response) {

                            this.loading = false;
                            this.disabled = false;
                            this.showErrorBag = true;
                            this.errorBag = error.response.data.errors
                        }
                    });

            },
            clearIndividualInputs(){
                this.individual.name                   = null;
                this.individual.phone                  = null;
                this.errorBag               = {};
                this.showErrorBag = false;
            },
            clearInputs(){
                this.company.name                   = null;
                this.company.phone                  = null;
                this.company.city                   = null;
                this.company.address                = null;
                this.company.person_incharge_name   = null;
                this.company.person_incharge_phone  = null;
                this.company.email                  = null;
                this.company.tax_number             = null;
                this.errorBag               = {};
                this.showErrorBag = false;
            },
            getCompanAttachableReservations(){
                axios.get(`/nova-vendor/calender/companies/${this.company_id}/attachable-reservations`)
                .then(response => {
                    if(response.data.length){
                        this.attachable_reservations = response.data;
                    }
                })
            },
            setCustomersEndPoint(input){
                return `/nova-vendor/calender/customer/search?q=${input}&search_attribute=${this.search_attribute}`;
            },
            selectedCustomer(customer) {
                this.customer = customer.selectedObject
                if(!this.customer.highlight && this.customer.team_id != Nova.config.user.current_team_id){
                    this.showHighlight = false;
                }
                this.customerTypeChange(this.customer.customer_type)
                customer.display = customer.selectedObject.name;
            },
            selectedCustomer1(customer) {
                customer.display = customer.selectedObject.name
            },
            customerTypeChange(id) {

                if (id == 1) {
                    this.nationalities = this.commonSelectors.nationalities.filter(n => n.code == Nova.app.currentTeam.country_code)
                    this.customer.country_id = Nova.app.currentTeam.country_code
                    this.shomos_ids = this.shomoos_id_types.filter(t => [1, 2, 5].includes(t.code));
                    // this.id_types = this.commonSelectors.id_types.filter(n => [1, 2, 3, 4, 5].includes(n.id))
                } else if (id == 2) {
                    this.nationalities = this.commonSelectors.nationalities.filter(n => n.is_gcc && n.code != Nova.app.currentTeam.country_code)
                    // this.id_types = this.commonSelectors.id_types.filter(n => [2, 6].includes(n.id))
                    this.shomos_ids = this.shomoos_id_types.filter(t => [3,5].includes(t.code));
                    // this.customer.country_id  = null;

                } else if (id == 3) {
                    this.nationalities = this.commonSelectors.nationalities.filter(n => n.code != Nova.app.currentTeam.country_code)
                    // this.id_types = this.commonSelectors.id_types.filter(n => [2].includes(n.id))
                    this.shomos_ids = this.shomoos_id_types.filter(t => [5,6].includes(t.code));
                    // this.customer.country_id  = null;
                } else if (id == 4) {
                    this.nationalities = this.commonSelectors.nationalities.filter(n => n.code != Nova.app.currentTeam.country_code)
                    // this.id_types = this.commonSelectors.id_types.filter(n => [2, 3, 7, 8].includes(n.id))
                    this.shomos_ids = this.shomoos_id_types.filter(t => [ 4, 5].includes(t.code));
                    // this.customer.country_id  = null;
                }
            },
            commonSelectorsFunction() {
                axios.get('/nova-vendor/calender/unit/commonSelectors')
                    .then((res) => {

                        this.commonSelectors.nationalities = res.data.nationalities;
                        this.commonSelectors.id_types = res.data.id_types;
                        this.commonSelectors.customer_types = res.data.customer_types;
                        this.purpose_of_visit = res.data.purpose_of_visit;
                    })
            },
            checkThePhone(phone){
                this.customerValidPhone = phone.isValid;
                this.customerPhoneCountry = phone.country.name;
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
            checkThePhone(phone){
                this.personInChargeValidPhone = phone.isValid;
                this.customerPhoneCountry = phone.country.name;
            },
        },
        mounted() {
            this.reservation_group_type = 'company';
            axios.get('/nova-vendor/calender/unit/selectors')
            .then((res) => {
                this.nationalities = res.data.nationalities;
                this.id_types = res.data.id_types;
                this.nationalities = this.nationalities.filter(n => n.code == Nova.app.currentTeam.country_code)
                this.customer.country_id = Nova.app.currentTeam.country_code
                this.id_types = this.id_types.filter(n => [1, 2, 3, 4, 5].includes(n.id))

                this.customer_types = res.data.customer_types;
            })

            this.commonSelectorsFunction();
            this.getHighlights();
            Nova.$on('attachable_reservation' , (reservation) => {
                this.attachable_reservation = reservation;
            })
        }
    }
</script>

<style lang="scss">
.search_company {
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
                                margin: 0px;
                                 color: #000;
                                    font-size: 15px;
                                    border-bottom: 1px solid #ddd;
                                    padding: 10px;
                                    display: flex;
                                    align-items: center;
                                    justify-content: space-between;
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
                                        direction: ltr;
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
                                                margin: 0 5px 0 0;
                                                background-image: url("data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiA/PjwhRE9DVFlQRSBzdmcgIFBVQkxJQyAnLS8vVzNDLy9EVEQgU1ZHIDEuMS8vRU4nICAnaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkJz48c3ZnIGhlaWdodD0iNTEycHgiIGlkPSJMYXllcl8xIiBzdHlsZT0iZW5hYmxlLWJhY2tncm91bmQ6bmV3IDAgMCA1MTIgNTEyOyIgdmVyc2lvbj0iMS4xIiB2aWV3Qm94PSIwIDAgNTEyIDUxMiIgd2lkdGg9IjUxMnB4IiB4bWw6c3BhY2U9InByZXNlcnZlIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIj48cGF0aCBkPSJNNDE1LjksMzM1LjVjLTE0LjYtMTUtNTYuMS00My4xLTgzLjMtNDMuMWMtNi4zLDAtMTEuOCwxLjQtMTYuMyw0LjNjLTEzLjMsOC41LTIzLjksMTUuMS0yOSwxNS4xYy0yLjgsMC01LjgtMi41LTEyLjQtOC4yICBsLTEuMS0xYy0xOC4zLTE1LjktMjIuMi0yMC0yOS4zLTI3LjRsLTEuOC0xLjljLTEuMy0xLjMtMi40LTIuNS0zLjUtMy42Yy02LjItNi40LTEwLjctMTEtMjYuNi0yOWwtMC43LTAuOCAgYy03LjYtOC42LTEyLjYtMTQuMi0xMi45LTE4LjNjLTAuMy00LDMuMi0xMC41LDEyLjEtMjIuNmMxMC44LTE0LjYsMTEuMi0zMi42LDEuMy01My41Yy03LjktMTYuNS0yMC44LTMyLjMtMzIuMi00Ni4ybC0xLTEuMiAgYy05LjgtMTItMjEuMi0xOC0zMy45LTE4Yy0xNC4xLDAtMjUuOCw3LjYtMzIsMTEuNmMtMC41LDAuMy0xLDAuNy0xLjUsMWMtMTMuOSw4LjgtMjQsMjAuOS0yNy44LDMzLjJjLTUuNywxOC41LTkuNSw0Mi41LDE3LjgsOTIuNCAgYzIzLjYsNDMuMiw0NSw3Mi4yLDc5LDEwNy4xYzMyLDMyLjgsNDYuMiw0My40LDc4LDY2LjRjMzUuNCwyNS42LDY5LjQsNDAuMyw5My4yLDQwLjNjMjIuMSwwLDM5LjUsMCw2NC4zLTI5LjkgIEM0NDIuMywzNzAuOCw0MzEuNSwzNTEuNiw0MTUuOSwzMzUuNXogTTQwNC40LDM5MS40Yy0yMCwyNC4yLTMxLjUsMjQuMi01Mi4zLDI0LjJjLTIwLjMsMC01MS44LTE0LTg0LjItMzcuMyAgYy0zMS0yMi40LTQ0LjgtMzIuNy03NS45LTY0LjZjLTMyLjktMzMuNy01My42LTYxLjgtNzYuNC0xMDMuNWMtMjQuMS00NC4xLTIxLjQtNjMuNC0xNi41LTc5LjNjMi42LTguNSwxMC40LTE3LjYsMjEtMjQuMiAgYzAuNS0wLjMsMS0wLjcsMS42LTFjNS4zLTMuNCwxNC4xLTkuMSwyMy43LTkuMWM4LDAsMTUuMSw0LDIxLjksMTIuM2wxLDEuMmMyNS41LDMxLjIsNDUuNCw1OC44LDMwLjQsNzkuMiAgYy0xMC42LDE0LjMtMTYuMiwyNC0xNS4zLDM0YzAuOCw5LjcsNy4zLDE3LDE3LjEsMjhsMC43LDAuOGMxNi4xLDE4LjIsMjAuNywyMywyNy4xLDI5LjVjMS4xLDEuMSwyLjIsMi4zLDMuNSwzLjZsMS44LDEuOSAgYzcuNCw3LjcsMTEuNSwxMS45LDMwLjMsMjguNGwxLjEsMWM4LDcsMTMuOSwxMi4xLDIyLjUsMTIuMWM4LjksMCwxOC43LTUuNiwzNy4zLTE3LjVjMS45LTEuMiw0LjYtMS45LDgtMS45ICBjMjEuNywwLDU5LjEsMjQuOCw3Mi4yLDM4LjNDNDE3LDM1OS43LDQyMywzNjguOSw0MDQuNCwzOTEuNHoiLz48L3N2Zz4=");
                                        }
                                    }
                                }
                                &:hover {
                                    background: #f0f0f0;
                                }


                            }

                            /* autocomplete__results__item */
                        }

                        /* autocomplete__results */
}
.label_with_button {
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.companies_component {

    border-radius: 5px;
    border: 1px solid #ddd;
    padding: 10px;
    background: #fff;
    box-shadow: 0 2px 4px 0 rgba(0,0,0,0.05);
        .search_company {


            .attachable_reservations{
                margin: 10px 0;
                  label {
                    display: block;
                    margin: 0px;
                    font-size: 14px;
                    font-weight: 500;
                    color: #000;
                  }
                  select {
                    // margin-top: 10px;
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
                button {
                    background: #4099de;
                    border-radius: 5px;
                    border: 1px solid #4099de;
                    min-width: 33.3333%;
                    height: 35px;
                    line-height: 35px;
                    font-size: 15px;
                    padding: 0 15px;
                    color: #ffffff;
                    -webkit-transition: all 0.2s ease-in-out;
                    -moz-transition: all 0.2s ease-in-out;
                    -o-transition: all 0.2s ease-in-out;
                    transition: all 0.2s ease-in-out;
                    @media (min-width: 320px) and (max-width: 480px) {
                        min-width: 100%;
                        width: 100%;
                    }
                    /* Mobile */
                    @media (min-width: 481px) and (max-width: 767px) {
                        min-width: 100%;
                        width: 100%;
                    }
                    /* Mobile */
                    @media (min-width: 768px) and (max-width: 991px) {
                        min-width: 50%;
                        width: 50%;
                    }
                    /* Mobile */
                    &:hover {
                        background: #0071C9;
                        border-color: #0071C9;
                    }

                    /* hover */
                } /* button */
            }

               .label_with_button {
                display: flex;
                align-items: center;
                justify-content: space-between;
                    label.custom_company_label {
                        font-size: 14px;
                        font-weight: 500;
                        color: #000;
                    }

                    button {
                        min-width: 130px;
                        padding: 0 10px;
                        width: auto;
                        max-width: none;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                    }
            }
        }
        .reservation_type {
            display: flex;

            margin: 0 0 10px;
            padding: 0 0 10px;
            justify-content: flex-start;
            font-size: 15px;
            color: #000;
            flex-wrap: wrap;
            align-items: center;
            .title {
                width: 25%;
                [dir="ltr"] & {
                    width: 30%;
                } /* rtl */
            } /* title */
            .radios_area {
                width: 75%;
                display: flex;
                align-items: center;
                flex-wrap: wrap;
                [dir="ltr"] & {
                    width: 70%;
                } /* rtl */
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
                } /* label */
            } /* radios_area */
        } /* reservation_type */
        .input_group {
            border-bottom: 1px solid #ddd;
            padding: 0 0 10px;
            margin: 0 0 10px;
            label {
                display: block;
                font-size: 15px;
                color: #000;
                margin: 0 auto 5px;
            } /* label */
            select {
                width: 100%;
                background-image: url("data:image/svg+xml,%3C%3Fxml version='1.0' encoding='iso-8859-1'%3F%3E%3C!-- Generator: Adobe Illustrator 19.0.0, SVG Export Plug-In . SVG Version: 6.00 Build 0) --%3E%3Csvg version='1.1' id='Layer_1' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' x='0px' y='0px' viewBox='0 0 491.996 491.996' style='enable-background:new 0 0 491.996 491.996;' xml:space='preserve'%3E%3Cg%3E%3Cg%3E%3Cpath d='M484.132,124.986l-16.116-16.228c-5.072-5.068-11.82-7.86-19.032-7.86c-7.208,0-13.964,2.792-19.036,7.86l-183.84,183.848 L62.056,108.554c-5.064-5.068-11.82-7.856-19.028-7.856s-13.968,2.788-19.036,7.856l-16.12,16.128 c-10.496,10.488-10.496,27.572,0,38.06l219.136,219.924c5.064,5.064,11.812,8.632,19.084,8.632h0.084 c7.212,0,13.96-3.572,19.024-8.632l218.932-219.328c5.072-5.064,7.856-12.016,7.864-19.224 C491.996,136.902,489.204,130.046,484.132,124.986z'/%3E%3C/g%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3C/svg%3E%0A");
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
                } /* ltr */
            } /* select */
        } /* input_group */
        .date_range {
            border-bottom: 1px solid #ddd;
            margin: 0 0 10px;
            padding: 0 0 10px;
            label {
                display: block;
                font-size: 15px;
                color: #000;
                margin: 0 auto 5px;
            } /* label */
            .date_picker_area {
                position: relative;
                input {
                    width: 100%;
                    height: 40px !important;
                    padding: 0 10px !important;
                    background-color: #fafafa !important;
                    border: 1px solid #ddd !important;
                    color: #000;
                    font-size: 15px;
                    cursor: pointer;
                    -webkit-box-sizing: border-box;
                    box-sizing: border-box;
                    -webkit-appearance: none;
                    -moz-appearance: none;
                    -o-appearance: none;
                    appearance: none;
                    border-radius: 5px !important;
                } /* input */
                .vc-popover-content-wrapper {
                    .vc-popover-content {
                        border: 1px solid #dddddd !important;
                        border-radius: 5px !important;
                        .vc-container {
                            background: #fafafa;
                            .vc-title-wrapper {
                                text-align: center;
                                width: 100%;
                                .vc-title {
                                    font-family: 'Dubai-Medium';
                                    font-weight: normal;
                                    font-size: 20px;
                                    line-height: 30px;
                                    height: 30px;
                                    padding: 0 30px;
                                } /* vc-title */
                            } /* vc-title-wrapper */
                            .vc-arrows-container {
                                position: absolute;
                                top: 0;
                                left: 0;
                                right: 0;
                                width: 100%;
                                display: flex;
                                justify-content: space-between !important;
                                align-items: center;
                            } /* vc-arrows-container */
                            .vc-weekday {
                                color: #444444;
                                font-weight: normal;
                                font-size: 13px;
                                margin: 0 5px;
                                @media (min-width: 320px) and (max-width: 480px) {
                                    margin: 0 1px;
                                } /* Mobile */
                                @media (min-width: 481px) and (max-width: 767px) {
                                    margin: 0 3px;
                                } /* Mobile */
                            } /* vc-weekday */
                        } /* vc-container */
                    } /* vc-popover-content */
                } /* vc-popover-content-wrapper */
            } /* date_picker_area */
            .date_picker_alert {
                display: block;
                margin: 10px 0 0;
                background: #fff3cd;
                border: 1px solid #ffeeba;
                color: #856404;
                border-radius: 4px;
                padding: 10px;
                font-size: 15px;
                span {
                    display: block;
                    font-family: 'Dubai-Bold';
                } /* span */
            } /* date_picker_alert */
        } /* date_range */
        .sourceId {
            label {
                display: block;
                font-size: 15px;
                color: #000;
                margin: 0 auto 5px;
            } /* label */
            select {
                width: 100%;
                background-image: url("data:image/svg+xml,%3C%3Fxml version='1.0' encoding='iso-8859-1'%3F%3E%3C!-- Generator: Adobe Illustrator 19.0.0, SVG Export Plug-In . SVG Version: 6.00 Build 0) --%3E%3Csvg version='1.1' id='Layer_1' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' x='0px' y='0px' viewBox='0 0 491.996 491.996' style='enable-background:new 0 0 491.996 491.996;' xml:space='preserve'%3E%3Cg%3E%3Cg%3E%3Cpath d='M484.132,124.986l-16.116-16.228c-5.072-5.068-11.82-7.86-19.032-7.86c-7.208,0-13.964,2.792-19.036,7.86l-183.84,183.848 L62.056,108.554c-5.064-5.068-11.82-7.856-19.028-7.856s-13.968,2.788-19.036,7.856l-16.12,16.128 c-10.496,10.488-10.496,27.572,0,38.06l219.136,219.924c5.064,5.064,11.812,8.632,19.084,8.632h0.084 c7.212,0,13.96-3.572,19.024-8.632l218.932-219.328c5.072-5.064,7.856-12.016,7.864-19.224 C491.996,136.902,489.204,130.046,484.132,124.986z'/%3E%3C/g%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3C/svg%3E%0A");
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
                } /* ltr */
            } /* select */
        } /* sourceId */
        .loader_item {
            padding: 50px;
        } /* loader_item */
        ul {
            li {
                // border-top: 1px solid #dddddd;
                // margin: 10px 0 0;
                // padding: 10px 0 0;
                // display: flex;
                justify-content: flex-start;
                flex-wrap: wrap;
                align-items: center;
                font-size: 15px;
                color: #000000;
                .name {
                    width: 25%;
                    [dir="ltr"] & {
                        width: 30%;
                    } /* rtl */
                } /* name */
                .desc {
                    width: 75%;
                    [dir="ltr"] & {
                        width: 70%;
                    } /* rtl */
                } /* desc */

                .total_price {
                    width: 75%;
                    direction: ltr;
                    font-size: 20px;
                    color: #000;
                    [dir="ltr"] & {
                        width: 70%;
                    } /* rtl */
                } /* total_price */
                .night_count {
                    width: 75%;
                    font-size: 20px;
                    color: #000;
                    [dir="ltr"] & {
                        width: 70%;
                    } /* rtl */
                } /* night_count */
            } /* li */
        } /* ul */

        button {
                background: #4099de;
                border-radius: 5px;
                border: 1px solid #4099de;
                min-width: 33.3333%;
                height: 35px;
                line-height: 35px;
                font-size: 15px;
                padding: 0 15px;
                color: #ffffff;
                -webkit-transition: all 0.2s ease-in-out;
                -moz-transition: all 0.2s ease-in-out;
                -o-transition: all 0.2s ease-in-out;
                transition: all 0.2s ease-in-out;
                @media (min-width: 320px) and (max-width: 480px) {
                    min-width: 100%;
                    width: 100%;
                }
                /* Mobile */
                @media (min-width: 481px) and (max-width: 767px) {
                    min-width: 100%;
                    width: 100%;
                }
                /* Mobile */
                @media (min-width: 768px) and (max-width: 991px) {
                    min-width: 50%;
                    width: 50%;
                }
                /* Mobile */
                &:hover {
                    background: #0071C9;
                    border-color: #0071C9;
                }

                /* hover */
        }
    } /* companies_component */

.add-company {

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
  .input_group {
    display: block;
    margin: 0 auto 10px;
  } /* input_group */
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
      margin: 0 auto 10px;
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
    } /* col */

  } /* row_group */
  label {
    display: block;
    margin: 0 auto 5px;
    font-size: 15px;
    span {
      display: inline-block;
      margin: 0 5px 0 0;
      color: #f00;
      [dir="ltr"] & {
        margin: 0 0 0 5px;
      } /* ltr */
    } /* span */
  } /* label */
  input {
    height: 40px !important;
    padding: 0 10px !important;
    color: #000 !important;
    font-size: 15px !important;
    border: 1px solid #dddddd !important;
    background: #fafafa !important;
    width: 100% !important;
    &[readonly="readonly"] {
      cursor: pointer;
    } /* readonly */
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
  } /* select */
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
    margin: 0 auto 10px;
    -webkit-transition: all 0.2s ease-in-out;
    -moz-transition: all 0.2s ease-in-out;
    -o-transition: all 0.2s ease-in-out;
    transition: all 0.2s ease-in-out;
    &:hover {
      background: #0071C9;
      border-color: #0071C9;
    } /* hover */
  } /* button */
} /* add_company */



  /* Mobile */
  .customer_details {
                    border-radius: 5px;
                    border: 1px solid #ddd;
                    padding: 10px 10px 0;
                    background: #fff;
                    box-shadow: 0 2px 4px 0 rgba(0, 0, 0, 0.05);
                    height: 100%;


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
                                 color: #000;
                                    font-size: 15px;
                                    border-bottom: 1px solid #ddd;
                                    padding: 10px;
                                    display: flex;
                                    align-items: center;
                                    justify-content: space-between;
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
                                        direction: ltr;
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
                                                margin: 0 5px 0 0;
                                                background-image: url("data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiA/PjwhRE9DVFlQRSBzdmcgIFBVQkxJQyAnLS8vVzNDLy9EVEQgU1ZHIDEuMS8vRU4nICAnaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkJz48c3ZnIGhlaWdodD0iNTEycHgiIGlkPSJMYXllcl8xIiBzdHlsZT0iZW5hYmxlLWJhY2tncm91bmQ6bmV3IDAgMCA1MTIgNTEyOyIgdmVyc2lvbj0iMS4xIiB2aWV3Qm94PSIwIDAgNTEyIDUxMiIgd2lkdGg9IjUxMnB4IiB4bWw6c3BhY2U9InByZXNlcnZlIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIj48cGF0aCBkPSJNNDE1LjksMzM1LjVjLTE0LjYtMTUtNTYuMS00My4xLTgzLjMtNDMuMWMtNi4zLDAtMTEuOCwxLjQtMTYuMyw0LjNjLTEzLjMsOC41LTIzLjksMTUuMS0yOSwxNS4xYy0yLjgsMC01LjgtMi41LTEyLjQtOC4yICBsLTEuMS0xYy0xOC4zLTE1LjktMjIuMi0yMC0yOS4zLTI3LjRsLTEuOC0xLjljLTEuMy0xLjMtMi40LTIuNS0zLjUtMy42Yy02LjItNi40LTEwLjctMTEtMjYuNi0yOWwtMC43LTAuOCAgYy03LjYtOC42LTEyLjYtMTQuMi0xMi45LTE4LjNjLTAuMy00LDMuMi0xMC41LDEyLjEtMjIuNmMxMC44LTE0LjYsMTEuMi0zMi42LDEuMy01My41Yy03LjktMTYuNS0yMC44LTMyLjMtMzIuMi00Ni4ybC0xLTEuMiAgYy05LjgtMTItMjEuMi0xOC0zMy45LTE4Yy0xNC4xLDAtMjUuOCw3LjYtMzIsMTEuNmMtMC41LDAuMy0xLDAuNy0xLjUsMWMtMTMuOSw4LjgtMjQsMjAuOS0yNy44LDMzLjJjLTUuNywxOC41LTkuNSw0Mi41LDE3LjgsOTIuNCAgYzIzLjYsNDMuMiw0NSw3Mi4yLDc5LDEwNy4xYzMyLDMyLjgsNDYuMiw0My40LDc4LDY2LjRjMzUuNCwyNS42LDY5LjQsNDAuMyw5My4yLDQwLjNjMjIuMSwwLDM5LjUsMCw2NC4zLTI5LjkgIEM0NDIuMywzNzAuOCw0MzEuNSwzNTEuNiw0MTUuOSwzMzUuNXogTTQwNC40LDM5MS40Yy0yMCwyNC4yLTMxLjUsMjQuMi01Mi4zLDI0LjJjLTIwLjMsMC01MS44LTE0LTg0LjItMzcuMyAgYy0zMS0yMi40LTQ0LjgtMzIuNy03NS45LTY0LjZjLTMyLjktMzMuNy01My42LTYxLjgtNzYuNC0xMDMuNWMtMjQuMS00NC4xLTIxLjQtNjMuNC0xNi41LTc5LjNjMi42LTguNSwxMC40LTE3LjYsMjEtMjQuMiAgYzAuNS0wLjMsMS0wLjcsMS42LTFjNS4zLTMuNCwxNC4xLTkuMSwyMy43LTkuMWM4LDAsMTUuMSw0LDIxLjksMTIuM2wxLDEuMmMyNS41LDMxLjIsNDUuNCw1OC44LDMwLjQsNzkuMiAgYy0xMC42LDE0LjMtMTYuMiwyNC0xNS4zLDM0YzAuOCw5LjcsNy4zLDE3LDE3LjEsMjhsMC43LDAuOGMxNi4xLDE4LjIsMjAuNywyMywyNy4xLDI5LjVjMS4xLDEuMSwyLjIsMi4zLDMuNSwzLjZsMS44LDEuOSAgYzcuNCw3LjcsMTEuNSwxMS45LDMwLjMsMjguNGwxLjEsMWM4LDcsMTMuOSwxMi4xLDIyLjUsMTIuMWM4LjksMCwxOC43LTUuNiwzNy4zLTE3LjVjMS45LTEuMiw0LjYtMS45LDgtMS45ICBjMjEuNywwLDU5LjEsMjQuOCw3Mi4yLDM4LjNDNDE3LDM1OS43LDQyMywzNjguOSw0MDQuNCwzOTEuNHoiLz48L3N2Zz4=");
                                        }
                                    }
                                }
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

   
.selected_individual_alert{
    background: #fff3cd;
    border: 1px solid #ffeeba;
    padding: 10px;
    border-radius: 4px;
    font-size: 15px;
    color: #856404;
    margin: 0 auto 15px;
}             
</style>
