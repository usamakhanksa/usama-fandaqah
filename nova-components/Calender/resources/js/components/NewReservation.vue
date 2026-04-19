<template>
    <div id="new_reservation_page">
        <loading
                :active.sync="isLoading"
                :can-cancel="false"
                :is-full-page="fullPage"
        />
        <!-- <OnlineReservations
                v-if="unit && onlineReserved"
                :reservations="this.unit.online_reservations"
        /> -->


<!--        <template v-if="unit">{{unit.reservation.prices}}</template>-->
        <div class="row_cols">
            <div class="right_col">
                <companies-component />


                <reservation-details
                        :units="units"
                        :selectedDate="selectedDate"
                        :customUnit="unit"
                        @setrange="setRange"
                        @setunit="setUnit"
                        ref="details"
                />

                <br>

                <div class="mb-4 breakfast_details relative" v-if="rent_type ==2 && tax_enabled ==1 && remove_vat_from_monthly_reservations == 1 ">

                    <div class="switch_group">
                        <label>{{__('Remove vat')}}</label> &nbsp;
                        <div class="switch">
                            <input type="checkbox" v-model="remove_vat" @change="removeVat">
                            <span class="slider round" />
                        </div><!-- switch -->
                    </div>



                     </div>

                <div class="reservation_details relative">


                      <!-- Reservation Services selector must be here  -->
                      <div  class="multi-select-services input_group">
                        <label>{{__('Services included in price')}} :</label>
                       <!-- select is here  -->
                       <vueMultiSelect
                        v-model="reservation_services_selected"
                        :options="options"
                        :filters="filters"
                        :btnLabel="multi_select_btn_label"
                        :selectOptions="multi_select_data"/>
                    </div>

                </div><!-- Reservation Services  -->

            </div><!-- right_col -->
            <div class="left_col">
                <div class="customer_details">
                    <div class="search_criteria">
                        <div class="title">{{__('Search Customers')}} : </div>
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
                    <div class="loader_item" v-if="loading">
                        <loader/>
                    </div><!-- loader_item -->
                    <div v-if="!loading">
                        <div class="customerNotes" v-if="customerNotes.length">
                            <span @click="openDisplayNotesModal">{{__('Customer Have Notes', {count: customerNotes.length })}}</span>
                        </div><!-- customerNotes -->
                        <sweet-modal :enable-mobile-fullscreen="false" :pulse-on-block="false" :title="__('Notes')"
                                     overlay-theme="dark" class="customer_notes_modal" ref="notesModal">
                            <div class="note_item" v-for="(note,i) in customerNotes" :key="i">
                                <div class="desc">{{ltrim(note.comment)}}</div>
                                <div class="user_info">
                                    <div class="user_account">
                                        <img v-if="note.commenter.photo_url" alt=""
                                             v-bind:src="'https://fandaqah.s3.eu-central-1.amazonaws.com/'+note.commenter.photo_url"/>
                                        {{ note.commenter.name }}
                                    </div><!-- user_account -->
                                    <time>{{note.created_at | formatDate }}</time>
                                </div><!-- user_info -->
                            </div><!-- note_item -->
                        </sweet-modal>


                          <div class="customerNotes" v-if="customer && reservations_count >= 1" @click="openCustomerCreditorDebitorModal">
                                <span>
                                    <p>{{__('Show Reservations History')}}</p>
                                </span>
                            </div>
                        <!-- customer creditor debtor modal -->
                        <CustomerCreditorDebtorInNewReservation v-if="customer"  ref="initCustomerCreditorDebitorInNewReservationModal" :customer="customer" />


                        <div v-if="collection.length" @click="openPromissoryListModal" class="cursor-pointer alert-warning">
                            {{__('There are un-fulfilled promissories on customer :customer' , {customer : customer.name})}}
                        </div>
                        <!-- Customer has un-fulfilled promissories -->
                        <customer-promissories-unfulfilled ref="initPromissoryList" :isLoading="promissoryLoaderActive" :customer="customer" :collection="collection" :paginator="paginator" />
                        <div class="row_group">
                            <div class="col">
                                <label>{{__('Name')}}<i>*</i></label>
                                <input type="text" v-model="customer.name" :placeholder="__('Name')">
                            </div><!-- col -->
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
                                    <option value="" selected="selected">{{__('Choose a country')}} </option>
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
                                    <option :value="null" :selected="!customer.highlight_id ? 'selected' : '' ">{{__('Choose a highlight')}}</option>
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
            </div><!-- left_col -->
        </div><!-- row_cols -->
        <div v-if="!loading">

            <div class="date_picker_alert rounded-sm mb-4" role="alert" v-if="specialPrices.length && rent_type == 1">


                <p v-for="(specialPrice , i) in specialPrices" :key="i" >{{__('Period  :start_date - :end_date has special price' , {start_date :
                    formatOfferAndSpecialPriceDate(specialPrice.start_date) , end_date :
                    formatOfferAndSpecialPriceDate(specialPrice.end_date) })}} <b class="cursor-pointer"
                                                                            @click="openSpecialPriceDetailsModal(specialPrice)">{{__('details')}}</b>
                </p>

            </div><!-- date_picker_alert -->

            <div :class="{'green_alert' : applyOffers , 'gray_alert' : !applyOffers}" class="green_alert rounded-sm mb-4" role="alert" v-if="offers.length && rent_type == 1">

                 <div class="switch_group">
                    <label>{{__('Do you wish to apply the below offers ?')}}</label> &nbsp;
                    <div class="switch">
                    <input type="checkbox" v-model="applyOffers" @change="handleApplyOffers($event)">
                    <span class="slider round" />
                    </div><!-- switch -->
                </div><!-- switch_group -->

                <p v-for="(offer , i) in offers" :key="i" >
                    {{
                        __('Period  :start_date - :end_date has offer' ,
                            {start_date : formatOfferAndSpecialPriceDate(offer.start_date) , end_date :formatOfferAndSpecialPriceDate(offer.end_date) }
                        )
                    }}
                    <b class="cursor-pointer" @click="openOfferDetailsModal(offer)">{{__('details')}}</b>
                </p>

            </div><!-- green_alert -->
            <div v-if="unit">
                <div class="loader_item" v-if="loading">
                    <loader/>
                </div><!-- loader_item -->
                <div class="total_result">
                    <div class="top_rows">
                        <div class="col_right">
                            <span>{{__('Unit Rental')}} : #{{ unit.unit_number }}</span>
                            <span>{{__('From')}} : {{ formatDate(this.selectedDate.start) }}</span>
                            <span>{{__('To')}} : {{ formatDate(this.selectedDate.end) }}</span>
                        </div><!-- col_right -->
                        <div class="col_left">
                            <p> <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span> {{ parseFloat(unit.reservation.prices.price).toFixed(2) }}</p>
                        </div><!-- col_left -->
                    </div><!-- top_rows -->
                    <div class="bottom_rows">
                        <ul>
                            <li>
                                <span>{{__('Subtotal')}}</span>
                                <subtotal-component v-model="subtotal_price"/>
<!--                                <span>{{__('Subtotal')}}</span>-->
<!--                                <p><input type="number" class="alter_total new_subtotal_input" v-model="subtotal_price"-->
<!--                                          @input="updateTotalsReverse"/> {{ unit.reservation.prices.currency }}</p>-->
                            </li>
                            <li v-if="unit.reservation.prices.ewa_parentage">
                                <span>{{__('Ewa')}} ({{ unit.reservation.prices.ewa_parentage }}%)</span>
                                <div class="total_ewa">  <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span> {{ parseFloat(total_ewa).toFixed(2) }} </div>
                            </li>
                            <li v-if="unit.reservation.prices.vat_parentage">
                                <span>{{__('VAT')}} ({{ unit.reservation.prices.vat_parentage }}%)</span>
                                <div class="total_ewa">  <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span> {{ parseFloat(total_vat).toFixed(2) }} </div>
                            </li>
                            <li v-if="unit.reservation.prices.tourism_percentage">
                                <span>{{__('Tourism Tax')}} ({{ unit.reservation.prices.tourism_percentage }}%)</span>
                                <div class="total_ewa">{{ parseFloat(total_ttx).toFixed(2) }} {{ __(currency) }}</div>
                            </li>
                            <li>
                                <span>{{__('Total')}}</span>
                                <total-price-component  v-model="total_price"/>
<!--                                <p><input type="number" class="total" v-model="total_price" @input="updateTotals"/>-->
<!--                                </p>-->
                            </li>
                        </ul>
                    </div><!-- top_rows -->
                </div><!-- total_result -->
            </div><!-- unit -->
            <div class="button_area" v-if="unit">
                <button @click="sendReservation" :disabled="disable_reservation || !customerValidPhone">{{__('Reserve')}}</button>
            </div><!-- button_area -->
        </div><!-- loading -->

        <!-- Special Price Details Modal -->
        <special-price-details v-if="specialPriceObject"
                               :locale="locale"
                               :specialPriceObject="specialPriceObject"
                               ref="specialPriceDetails"
        />
        <offer-details v-if="offerObject"
                               :locale="locale"
                               :offerDetailsObject="offerObject"
                               ref="offerDetails"
        />

    </div><!-- new_reservation_page -->
</template>

<script>
    import vueMultiSelect from 'vue-multi-select';
    import CompaniesComponent from './partial/CompaniesComponent.vue';
    import CustomerCreditorDebtorInNewReservation from './CustomerCreditorDebtorInNewReservation';
    import CustomerPromissoriesUnfulfilled from './CustomerPromissoriesUnfulfilled';
    import TotalPriceComponent from './partial/TotalPriceComponent.vue';
    import SubtotalComponent from './partial/SubtotalComponent.vue';
    import HijrahDate from 'hijrah-date';
    import momentHijri from 'moment-hijri';
    import moment from 'moment';
    import {
        Swatches
    } from 'vue-color';

    import OnlineReservations from './OnlineReservations';
    // Import component
    import Loading from 'vue-loading-overlay';
    // Import stylesheet
    import 'vue-loading-overlay/dist/vue-loading.css';

    import flatPickr from 'vue-flatpickr-component';
    import 'flatpickr/dist/flatpickr.css';

    import VueDatetimeJs from 'vue-datetime-js'

    import SpecialPriceDetails from './partial/SpecialPriceDetails';
    import OfferDetails from './partial/OfferDetails';

    import allCountries from '../allCountries.js';

    export default {
        name: "new-reservation",
        components: {
            OnlineReservations,
            Loading,
            'swatches-picker': Swatches,
            flatPickr,
            datePicker: VueDatetimeJs,
            SpecialPriceDetails,
            OfferDetails,
            TotalPriceComponent,
            SubtotalComponent,
            CustomerCreditorDebtorInNewReservation,
            CustomerPromissoriesUnfulfilled,
            CompaniesComponent,
            vueMultiSelect
        },
        props: {
            range: {
                type: Object,
                default: {
                    start: null,
                    end: null
                }
            },
        },
        data: () => {
            return {
                remove_vat: false,
                discounts  : [],
                reservationPeriod : [],
                daysOffersCollector : [],
                datesHasSpecialPrice : [],
                datesDoesntHaveSpecialPrice : [],
                daysIncludedInOffers : [],
                specialPricesCollector : [],
                offers : [],
                offersIds : [],
                offerObject : {},
                applyOffers : false,
                specialPrices: [],
                specialPricesIds : [],
                specialPriceObject: {},
                showSpecialPriceNotifier: false,
                show_color: false,
                loading: false,
                isLoading: false,
                purpose: 7,
                fullPage: true,
                onlineReserved: false,
                highlights: [],
                customer_types: [],
                SCTH: false,
                SHMS: false,
                purpose_of_visit: [],
                id_types: [],
                source_id: null,
                source_num : null,
                unit_id: null,
                unit: null,
                units: null,
                currency: Nova.app.currentTeam.currency,
                old_highlight_id: null,
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
                price_type: 'Day',
                total_price: 0,
                subtotal_price: 0,
                total_ewa: 0,
                total_vat: 0,
                total_ttx: 0,
                change_rate: 0,
                locale: Nova.config.local,
                disable_reservation: false,
                start_date: null,
                notes: null,
                crumbs: [],
                nationalities: [],
                selectedDate: this.range,
                rent_type: 1,
                hijriDate: '',
                customerNotes: [],
                calendarLocale: '',
                commonSelectors: {
                    nationalities: null,
                    customer_types: null,
                    id_types: null,
                    purpose_of_visit: null

                },
                monitor : 0,
                subtotalsArrayBasedOnDate : [],
                defaultPrices : [] ,
                defaultInCaseOfSpecialPriceFound : [],
                collection : [],
                paginator : {},
                current_page : 1,
                remove_vat_from_monthly_reservations : false,
                tax_enabled : false,
                promissoryLoaderActive : false,
                total_balance : 0 ,
                reservations_count : 0,
                current_team_id : Nova.config.user.current_team_id,
                company_id : null,
                attachable_reservation : null,
                search_attribute : 'by_name',
                searchPlaceHolder : "Search By Name",
                showHighlight : true,
                customerValidPhone: true,
                customerPhoneCountry : null,

                reservation_group_type : null,
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
                reservation_services_selected : [],
                multi_select_btn_label: reservation_services_selected => `${Nova.app.__('Selected Services')} (${reservation_services_selected.length})`,
                multi_select_data: [
                    {
                        reservation_services: [],
                    },
                ],
                filters: [{
                    nameAll: Nova.app.__('Select all'),
                    nameNotAll: Nova.app.__('Do not select any'),
                    func() {
                    return true;
                    },
                }],
                options: {
                    multi: true,
                    groups: true,
                    labelName: 'reservationServiceIdentifier',
                    labelList: 'reservation_services',
                    groupName: 'title',

                    cssSelected: option => (option.selected ? { 'background-color': '#4099de' , 'color': '#fff' } : ''),
                },
            }
        },
        computed: {
            // a computed getter
            minDate() {
                let user = Nova.config.user
                let permisions = user.roles.find(x => x.team_id == user.current_team_id)['permissions']
                return permisions.includes('booking past') ? false : new Date();
            },
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
            getSettings(){
                axios.get('/nova-vendor/settings/getReservationSettings')
                .then(response => {
                    // console.log(response.data);
                    this.remove_vat_from_monthly_reservations = response.data.remove_vat_from_monthly_reservations;
                    this.tax_enabled = response.data.tax;

                })


            },
            getReservationServices(){
                axios.get('/nova-vendor/settings/getReservationServices?all=true')
                .then(response => {
                    const self = this;
                    let arr = [];
                    var reservation_services = response.data;
                    if(reservation_services.length){
                        reservation_services.map(function(service, key) {
                                let obj = {};
                                obj.reservationServiceIdentifier = self.locale == 'ar' ?  service.name_ar  : service.name_en;
                                obj.id = service.id;
                                arr.push(obj);
                        });
                        this.multi_select_data[0].reservation_services = arr;
                    }
                })

            },
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
              this.$refs.initCustomerCreditorDebitorInNewReservationModal.$refs.customerCreditorDebtorModal.open();
            },

            openPromissoryListModal(){
            this.$refs.initPromissoryList.$refs.promissoriesModal.open();
          },
            invalidPhone(number){
                var regex = /^[+]*[(]{0,1}[\u0030-\u0039\u0660-\u0669]{1,3}[)]{0,1}[-\s\./\u0030-\u0039\u0660-\u0669]*$/g;
                var regex1 = /^[+]*[(]{0,1}[\u0030-\u0039\u0660-\u0669]{1,3}[)]{0,1}[-\s\./\u0030-\u0039\u0660-\u0669]*$/g;

                var result = (regex.test(number) ||  regex1.test(number));

                return result;
            },
            openDisplayNotesModal() {
                this.$refs.notesModal.open()
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
            showColor() {
                this.show_color = true;
            },
            highlight() {
                let highlight = this.highlights.filter((elem) => {
                    return elem.id == this.customer.highlight_id;
                })

                let style = "text-align: center;border: 1px solid " + highlight[0].color + "; padding: 5px 10px; border-radius: 4px; background: " + highlight[0].color + "; color: black;";

                return '<label class="form-control border rounded w-full py-2 px-3 border-gray-300 focus:outline-none focus:shadow-outline" style="' + style + '">' + highlight[0].name + '</label>'
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
            getUnitTotal() {
                this.loading = true;
                let start = moment(String(this.selectedDate.start)).format('YYYY-MM-DD');
                let end = moment(String(this.selectedDate.end)).format('YYYY-MM-DD');
                axios
                    .get('/nova-vendor/calender/unit/' + this.unit_id + '/' + start + '/' + end, {
                        params: {
                            rent_type: this.rent_type
                        }
                    })
                    .then(response => {
                        this.unit = response.data;
                        this.onlineReserved = this.unit.online_reservations.length > 0;
                        if (this.unit.has_reservation > 0) {
                            this.disable_reservation = true;
                            this.$toasted.show(this.__('This unit has a reservation that intersect with selected dates !'), {type: 'error'})
                        } else {
                            this.disable_reservation = false;
                        }
                        this.loading = false;
                        this.total_price = this.unit.reservation.prices.total_price_raw;
                        this.subtotal_price = this.unit.reservation.prices.sub_total;
                        this.total_ewa = this.unit.reservation.prices.total_ewa;
                        this.total_vat = this.unit.reservation.prices.total_vat;
                        this.total_ttx = this.unit.reservation.prices.total_tourism;

                        if(this.remove_vat){
                            this.removeVat();
                        }


                    }).catch(err => {
                    this.loading = false;
                    // this.$toasted.show(this.__(err), {type: 'error'})
                })

            },
            getVatOrEwaFromTotalPrice(price, percentage, other) {
                let vatDivisor = 1 + (percentage / 100);
                let priceBeforeVat = price / vatDivisor;
                return price - priceBeforeVat - other
            },
            getTourismTotal(price, percentage, other) {
                let ttxDivisor = 1 + (percentage / 100);
                let priceBeforeTtx = price / ttxDivisor;
                return price - priceBeforeTtx - other
            },
            updateTotals() {

                // Credit to @abdullah Ghanem - edit by emad
                let x = this.total_price;
                let e = this.unit.reservation.prices.ewa_parentage / 100;
                let v = this.unit.reservation.prices.vat_parentage / 100;
                let t = this.unit.reservation.prices.tourism_percentage / 100;
                let y = x / (1 + e + t + v + (v * e));


                this.subtotal_price = y;
                this.unit.reservation.prices.price = this.subtotal_price
                this.total_ttx = (this.subtotal_price / 100) * this.unit.reservation.prices.tourism_percentage;
                this.total_ewa = (this.subtotal_price / 100) * this.unit.reservation.prices.ewa_parentage;
                // this.total_vat =  Number(Number(this.total_price) - Number(this.subtotal_price) - Number(this.total_ttx) - Number(this.total_ewa)).toFixed(2) ;
                let subtotal_with_ewa = Number((this.subtotal_price / 100) * this.unit.reservation.prices.ewa_parentage) + Number(this.subtotal_price);
                this.total_vat = (subtotal_with_ewa / 100) * this.unit.reservation.prices.vat_parentage;
                this.unit.reservation.prices.total_price = this.total_price
                this.unit.reservation.prices.total_price_raw = this.total_price


            },
            getPricingChangeRate(newPrice, oldPrice) {
                return ((newPrice / oldPrice) - 1) * 100
            },
            updateTotalsReverse() {

                // this.unit.change_rate = this.getPricingChangeRate(this.subtotal_price, this.unit.reservation.prices.sub_total);

                let subtotal_with_ewa = Number((this.subtotal_price / 100) * this.unit.reservation.prices.ewa_parentage) + Number(this.subtotal_price);
                this.total_ewa = parseFloat((this.subtotal_price / 100) * this.unit.reservation.prices.ewa_parentage)
                this.total_vat = parseFloat((subtotal_with_ewa / 100) * this.unit.reservation.prices.vat_parentage)
                this.total_ttx = parseFloat((this.subtotal_price / 100) * this.unit.reservation.prices.tourism_percentage)
                this.total_price = parseFloat(subtotal_with_ewa + Number(this.total_vat) + Number(this.total_tourism))
                this.unit.reservation.prices.total_price_raw = parseFloat(subtotal_with_ewa + Number(this.total_vat) + Number(this.total_ttx))
                this.unit.reservation.prices.total_price = this.unit.reservation.prices.total_price_raw;
                this.total_price = this.unit.reservation.prices.total_price;
                this.unit.reservation.prices.price = this.subtotal_price;

                if(this.remove_vat){
                    this.removeVat();
                }

            },
            removeVat() {
                // console.log(this.tax_enabled , this.remove_vat_from_monthly_reservations);
                // Store the original VAT value for reuse

                if(this.rent_type == 1){
                    this.remove_vat = false;
                }
                console.log(this.remove_vat);
    if (!this.original_vat) {
        this.original_vat = this.total_vat;
        // orginal vat percentage
        this.original_vat_percentage = this.unit.reservation.prices.vat_parentage;
    }

    let subtotal_with_ewa = this.subtotal_price + this.total_ewa;
    let total_price = subtotal_with_ewa + this.original_vat; // Use the original VAT value for calculations

    // console.log(this.unit.reservation);

    if (this.remove_vat) {
        // console.log('remove vat');
        this.unit.reservation.prices.total_vat = 0;
        this.unit.reservation.prices.total_price = total_price - this.original_vat; // Subtract the original VAT
        this.unit.reservation.prices.total_price_raw = total_price - this.original_vat;
        this.unit.reservation.prices.vat_parentage = 0;
    } else {
        // console.log('add vat');
        this.unit.reservation.prices.total_vat = this.original_vat; // Restore the original VAT
        this.unit.reservation.prices.total_price = total_price;
        this.unit.reservation.prices.total_price_raw = total_price;
        this.unit.reservation.prices.vat_parentage = this.original_vat_percentage;
    }

    this.total_vat = this.unit.reservation.prices.total_vat; // Update the VAT value
    this.total_price = this.unit.reservation.prices.total_price_raw; // Update the total price
    // console.log(this.unit.reservation.prices);
},
            formatForDropDown(customer) {
                // return customer.name
                return customer.name + '  ||  ' + customer.phone
            },
             getUnfulfilledPromissories(page = 1){
                 this.promissoryLoaderActive = true;
                Nova.request().get(window.FANDAQAH_API_URL + `/promissories/unfullfield-promissories?status=pending&customer_id=${this.customer.id}&td=${this.current_team_id}&per_page=10&page=${this.current_page}`)
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
            selectedCustomer(customer) {
                this.customer = customer.selectedObject

                if(!this.customer.highlight && this.customer.team_id != Nova.config.user.current_team_id){
                    this.showHighlight = false;
                }
                this.customerTypeChange(this.customer.customer_type)
                customer.display = customer.selectedObject.name;
                Nova.request().get('/nova-vendor/calender/checkCustomerNotes?id=' + customer.selectedObject.id)
                    .then(res  => {
                        this.customerNotes = res.data
                        // Nova.$emit('customer-creditor-debtor' , customer.selectedObject.id);

                        this.getTotalBalance();
                    }).catch((err) => {
                    console.log(err)
                })

                this.getUnfulfilledPromissories();
            },
            selectedCustomer1(customer) {
                customer.display = customer.selectedObject.name
            },
            clearedCustomer(customer) {

                this.old_highlight_id = null;
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
                this.collection = [];
                this.paginator = {};
                this.reservations_count = 0;
                this.total_balance = 0;
            },
            getStartDate() {
                return moment(String(this.selectedDate.start)).format('LL');
            },
            getEndDate() {
                return moment(String(this.selectedDate.end)).format('LL');
            },
            alphanumeric(source_num)
            {
                var letters = /^[0-9a-zA-Z]+$/;
                if(source_num.match(letters))
                {
                    return true;
                }
                else
                {
                    return false;
                }
            },
            sendReservation() {
                if (this.customer.phone) {
                    if (!this.invalidPhone(this.customer.phone)) {
                        this.$toasted.show(this.__('this phone is not valid'), {type: 'error'})
                        return;
                    }
                }
                if (!this.unit_id) {
                    this.$toasted.show(this.__('Select Unit'), {type: 'error'});
                    return;
                }
                if (!this.unit.purpose) {
                    this.unit.purpose = 7;
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

                // if(!this.customer.birthday_date){
                //     this.$toasted.show(this.__('Please fill all customer info'), {type: 'error'})
                //     return;
                // }


                // if (((!this.customer.visa_number && this.customer.customer_type == '3')||
                //     !this.customer.id_number ||
                //     !this.customer.birthday_date ||
                //     !this.customer.name ||
                //     !this.customer.phone ||
                //     !this.customer.id_type ||
                //     !this.customer.country_id ||
                //     !this.customer.id_serial_number
                //     ) && (this.SHMS)) {
                //     this.$toasted.show(this.__('Please fill all customer info'), {type: 'error'})
                //     return;
                // }

                // if ((!this.customer.country_id || !this.customer.gender || !this.customer.id_type || !this.customer.birthday_date || !this.unit.purpose) && this.SCTH) {
                // if ((!this.customer.country_id ||
                //     !this.customer.gender ||
                //     // !this.customer.id_type ||
                //     !this.unit.purpose
                //     ) && this.SCTH) {
                //     this.$toasted.show(this.__('Please fill all customer info'), {type: 'error'})
                //     return;
                // }

                // set birthday_date to Zero for SCTH
                if(!this.customer.birthday_date && this.SCTH){
                    this.customer.birthday_date = 0 ;
                }

                this.unit.change_rate = this.getPricingChangeRate(this.subtotal_price, this.unit.reservation.prices.sub_total);

                if (!Nova.app.$hasPermission('booking without min price')) {
                    let change_rate = this.getPricingChangeRate(this.subtotal_price, this.unit.reservation.prices.min_sub_total);
                    ;
                    if (change_rate < 0) {
                        this.$toasted.show(this.__('Less than the allowed price'), {type: 'error'});
                        return;
                    }
                }

                // if(this.source_num){
                //     if(!this.alphanumeric(this.source_num)){
                //         this.$toasted.show(this.__('Source Num , Please input alphanumeric characters only'), {type: 'error'})
                //         return ;
                //     }
                // }

                if(this.attachable_reservation){

                    let invoices_without_credit_notes = _.filter(this.attachable_reservation.invoices, function(invoice) {
                        return invoice.invoice_credit_note === null;
                    });

                    if(invoices_without_credit_notes.length){
                        // invoices found according to the selected reservation
                        var lastGroupInvoice = invoices_without_credit_notes[0];
                        var theStartDateOfTheNewReservation  = moment(this.selectedDate.start).format('YYYY-MM-DD');

                        // if the new start date for our new reservation not greater than the last invoice date to
                        // we must be not able to add this reservation and we should force user to change the dates
                        if(!(theStartDateOfTheNewReservation > lastGroupInvoice.to)){
                            var available_to_add_from = moment(lastGroupInvoice.to).add(1,'days').format('YYYY/MM/DD');
                            this.$toasted.show(this.__('Can not add this new reservation with those dates becuase of invoices intersection, you can add starting from :available_to_add_from' , {available_to_add_from : available_to_add_from }), {type: 'error'});
                            // Nova.$emit('disable-add-reservatoin-btn-cause-of-group-reservation-invoices-intersection');
                            return;
                        }
                    }

                }


                this.loading = true;
                this.isLoading = true;

                this.specialPricesIds = [];
                this.offersIds = [];

                // check for special prices and offers
                let self = this;
                $.each(this.specialPrices , function(i,obj){
                    self.specialPricesIds.push(obj.id);
                });
                $.each(this.offers , function(i,obj){
                    self.offersIds.push(obj.id);
                });

                if(!this.applyOffers){
                    this.offersIds = [];
                }

                if(!this.isValidBirthdayDate(this.customer.birthday_date)){
                    this.customer.birthday_date = moment(new Date()).format('YYYY-MM-DD');
                }



                axios
                    .post('/nova-vendor/calender/reservation', {
                        customer: this.customer,
                        unit: this.unit,
                        rent_type: this.rent_type,
                        source_id: this.source_id,
                        remove_vat: this.remove_vat,
                        specialPricesCollector : this.specialPricesCollector,
                        source_num : this.source_num,
                        comment: this.notes,
                        total_ewa: this.total_ewa,
                        total_vat: this.total_vat,
                        total_ttx: this.total_ttx,
                        applyOffers : this.applyOffers ,
                        specialPricesIds : this.specialPricesIds,
                        offersIds : this.offersIds,
                        company_id : this.company_id,
                        attachable_reservation_id : this.attachable_reservation ? this.attachable_reservation.id : null,
                        reservation_group_type : this.reservation_group_type,
                        reservation_services_selected : this.reservation_services_selected

                    })
                    .then(response => {
                        this.loading = false;
                        this.isLoading = false;


                        if(!response.data.success && response.data.message == 'unit_has_reservation'){
                            this.$toasted.show(this.__('This unit has a reservation that intersect with selected dates !'), {type: 'error'})
                            return;
                        }

                        if(!response.data.success && response.data.message == 'id_number_taken'){
                            this.$toasted.show(this.__('Id number is taken , it must be unique'), {type: 'error'})
                            return;
                        }

                        if (response.data.success) {
                            this.$toasted.show(this.__('Reservation created successfuly !'), {type: 'success'})
                            this.$router.push({
                                name: 'reservation', params: {
                                    id: response.data.reservation.id
                                }
                            })
                        }
                    }).catch(err => {
                    this.isLoading = false;
                    this.loading = false;
                    this.isLoading = false;
                    this.$toasted.show(this.__(err.response.data.error), {type: 'error'})
                })
            },
            isValidBirthdayDate(date){
                var incoming = new Date(date);
                var today = new Date();
                if(incoming > today) {
                    return false;
                }
                return true;
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
                        this.commonSelectors.purpose_of_visit = res.data.purpose_of_visit;
                        // this.nationalities = res.data.nationalities ;
                        // this.id_types = res.data.id_types ;
                        // this.customer_types = res.data.customer_types ;
                        // this.purpose_of_visit = res.data.purpose_of_visit ;
                    })
            },
            formatDate(date) {
                return Nova.app.__formatDateWithHumanDate(date);
            },
            setRange(data) {

                this.selectedDate = data[0];
                this.rent_type = data[1];
                this.getUnits();
                if (this.unit && !this.unit_id) {
                    this.unit_id = this.unit.id;
                }

                if (this.unit_id) {
                    this.getUnitTotal();
                }
            },
            setUnit(unit_id) {
                if (this.unit && unit_id != this.unit.id) {
                    this.unit = null;
                }
                this.unit_id = unit_id;
                this.loading = true;
                this.getUnitTotal();

            },
            getUnits() {
                this.loading = true;
                let start = moment(String(this.selectedDate.start)).format('YYYY-MM-DD');
                let end = moment(String(this.selectedDate.end)).format('YYYY-MM-DD');
                axios
                    // .get('/nova-vendor/calender/units/' + start + '/' + end)
                    .post('/nova-vendor/calender/units/get-available-units', {

                            start : start,
                            end : end,
                            rent_type : this.rent_type

                    })
                    .then(response => {

                        this.customer_types = response.data.utility.customer_types;
                        this.purpose_of_visit = response.data.utility.purpose_of_visit;
                        this.SCTH = response.data.utility.SCTH;
                        this.SHMS = response.data.utility.SHMS;
                        this.units = response.data.units;

                        if(this.units){
                            let arr = [];
                            this.units.map(function(unit, key) {
                                if(!unit.has_reservation){
                                    arr.push(unit);
                                }
                            });
                            this.units = arr;
                        }

                        if (this.units.filter(e => e.id === this.unit_id).length === 0) {
                            this.unit_id = null;
                        }

                        this.loading = false;
                    }).catch(err => {
                    this.loading = false;
                    // this.$toasted.show(this.__(err), {type: 'error'})
                })
            },
            getSpecialPrices(id , unit , checked=null) {

                this.specialPricesIds = [];

                let start_date = moment(String(this.selectedDate.start)).format('YYYY-MM-DD');
                let end_date = moment(String(this.selectedDate.end)).format('YYYY-MM-DD');
                axios.get(`/nova-vendor/calender/unit/${id}/get-special-prices/${start_date}/${end_date}`)
                    .then(response => {

                        // console.log(response.data);
                        // apply if there are sepcial prices
                         this.specialPricesCollector = [];
                        if(response.data.status === 'special_prices_found'){


                            this.specialPrices = response.data.special_prices;

                            this.datesHasSpecialPrice = response.data.datesHasSpecialPrice;
                            this.datesDoesntHaveSpecialPrice = response.data.datesDoesntHaveSpecialPrice;

                             const uniqueDates = new Set();

                                for (let i = 0; i < this.datesHasSpecialPrice.length; i++) {
                                    const specialDate = this.datesHasSpecialPrice[i].date;

                                    for (let j = 0; j < response.data.incomingDates.length; j++) {
                                        const incomingDate = response.data.incomingDates[j];

                                        // Compare dates and check if the date hasn't been added already
                                        if (specialDate === incomingDate && !uniqueDates.has(incomingDate)) {
                                            // Add special price to incoming date
                                            this.specialPricesCollector.push({
                                                date: incomingDate,
                                                specialPrice: this.datesHasSpecialPrice[i].price
                                            });

                                            // Add the date to the set of unique dates
                                            uniqueDates.add(incomingDate);
                                        }
                                    }
                                }

                                // Add dates from datesDoesntHaveSpecialPrice
                                for (let i = 0; i < this.datesDoesntHaveSpecialPrice.length; i++) {
                                    const date = this.datesDoesntHaveSpecialPrice[i].date;

                                  for(let j = 0; j < response.data.incomingDates.length; j++) {
                                    const incomingDate = response.data.incomingDates[j];

                                    if (date === incomingDate && !uniqueDates.has(incomingDate)) {
                                      this.specialPricesCollector.push({
                                        date: incomingDate,
                                        specialPrice: this.datesDoesntHaveSpecialPrice[i].price
                                      });

                                      uniqueDates.add(incomingDate);
                                    }
                                  }
                                }

                                // remove the repeated dates records in the special prices collector
                                this.specialPricesCollector = this.specialPricesCollector.filter((thing, index, self) =>
                                    index === self.findIndex((t) => (
                                        t.date === thing.date
                                    ))
                                )

                            if(unit){

                                this.total_price =  (response.data.total_price).toFixed(2);
                                this.subtotal_price = response.data.subtotal;
                                this.total_ewa =  response.data.ewaTotal;
                                this.total_vat =  response.data.vatTotal;
                                this.total_ttx =  response.data.ttxTotal;


                                this.unit.reservation.prices.sub_total = response.data.subtotal;
                                this.unit.reservation.prices.total_ewa = response.data.ewaTotal;
                                this.unit.reservation.prices.total_vat = response.data.vatTotal;
                                this.unit.reservation.prices.total_tourism = response.data.ttxTotal;
                                this.unit.reservation.prices.total_price = this.total_price;
                                this.unit.reservation.prices.total_price_raw = this.total_price;

                                this.unit.reservation.prices.price = response.data.subtotal



                            }


                            this.defaultInCaseOfSpecialPriceFound = response.data.defaultInCaseOfSpecialPriceFound;


                        }else{
// get response.unitCategoryDaysPrices and loop through it and push it to specialPricesCollector
                             let unitCategoryDaysPrices = response.data.unitCategoryDaysPrices;
                             this.specialPricesCollector = [];
                             for(let i = 0 ; i < unitCategoryDaysPrices.length ; i++){
                                 this.specialPricesCollector.push({
                                     date : unitCategoryDaysPrices[i].date ,
                                     specialPrice : unitCategoryDaysPrices[i].price
                                 });
                             }


                        }

                        this.reservationPeriod = response.data.incomingDates;




                        // this.datesHasSpecialPrice = response.data.datesHasSpecialPrice;
                        // this.datesDoesntHaveSpecialPrice = response.data.datesDoesntHaveSpecialPrice;

                        this.getOffers(id , unit);

                    });



            },

            getOffers(id , unit){

                this.applyOffers = false;
                let start_date = moment(String(this.selectedDate.start)).format('YYYY-MM-DD');
                let end_date = moment(String(this.selectedDate.end)).format('YYYY-MM-DD');

                axios.get(`/nova-vendor/calender/unit/${id}/get-offers/${start_date}/${end_date}`)
                    .then(response => {


                        if(response.data.status == 'offers_found'){
                            this.offers = response.data.offers;
                            this.daysIncludedInOffers = response.data.daysIncludedInOffers;

                        }else{
                            this.offers = [];
                            this.daysIncludedInOffers = [];

                        }

                        this.discounts = [];
                        this.daysOffersCollector = this.daysIncludedInOffers


                    });


            },

            openSpecialPriceDetailsModal(specialPrice) {
                this.specialPriceObject = specialPrice;
                this.$refs.specialPriceDetails.$refs.specialPriceFromNewReservation.open();
            },
            openOfferDetailsModal(offer) {
                this.offerObject = offer;
                this.$refs.offerDetails.$refs.offerFromNewReservation.open();
            },
            formatOfferAndSpecialPriceDate(date) {
                return moment(date).format('YYYY/MM/DD');
            },
            handleApplyOffers(event){

                // the problem here is the percentage offer when calculated it consumes the subtotal ,
                // and the subtotal is exhangable

                this.applyOffers = event.target.checked
                if(this.applyOffers){


                    if(this.daysOffersCollector.length){


                        let x = this.total_price;
                        let e = this.unit.reservation.prices.ewa_parentage / 100;
                        let v = this.unit.reservation.prices.vat_parentage / 100;
                        let t = this.unit.reservation.prices.tourism_percentage / 100;
                        let y = x / (1 + e + t + v + (v * e));

                        let self = this;


                        /**
                         * The Fucking idea behind the offers in the percentage case , cause i need to push subtotal per day
                         * on both conditions if it has special price or if it doesn't fucking has special price
                         * then check if the date matches the date coming from offers to apply the fucking discount
                         * on the the subtotal price per day and subtract it from the total subtotal WDF WDF WDF WDF
                         */

                        // console.log(this.daysOffersCollector);
                        // console.log(this.datesHasSpecialPrice);
                        // console.log(this.datesDoesntHaveSpecialPrice);

                        let fullDatesArray = this.datesHasSpecialPrice.concat(this.datesDoesntHaveSpecialPrice);
                        let discountPricesArr = [] ;




                        $.each(this.daysOffersCollector , function (i , obj) {

                            if(fullDatesArray.length){
                                let checkObject = fullDatesArray.filter(target => target.date == obj.date);
                                if(checkObject[0]){

                                    // console.log(checkObject[0]);
                                    // console.log(self.daysOffersCollector[i]);

                                    // the step to push into holder array
                                    let checkFoundInPricesArray = discountPricesArr.filter(element => element.date == checkObject[0].date);
                                    if(!checkFoundInPricesArray[0]){
                                        // defaultInCaseOfSpecialPriceFound


                                        if(checkObject[0].price != null){
                                            // console.log(checkObject[0].price )
                                            if(self.daysOffersCollector[i].discount_type == 'percentage'){
                                                // percentage offer
                                                checkObject[0].price = (self.daysOffersCollector[i].discount_amount / 100) * checkObject[0].price;
                                            }else{
                                                // price odder
                                                checkObject[0].price = Number(self.daysOffersCollector[i].discount_amount)
                                            }
                                        }

                                        if(checkObject[0].price == null){

                                            let nullablePriceObject = self.defaultInCaseOfSpecialPriceFound.filter(target => target.date == obj.date);
                                            if(self.daysOffersCollector[i].discount_type == 'percentage'){
                                                // percentage offer
                                                checkObject[0].price = (self.daysOffersCollector[i].discount_amount / 100) * nullablePriceObject[0].price;
                                            }else{
                                                // price odder
                                                checkObject[0].price = Number(self.daysOffersCollector[i].discount_amount)
                                            }
                                            // console.log(self.defaultInCaseOfSpecialPriceFound);
                                        }

                                        // console.log(self.daysOffersCollector[i]);
                                        discountPricesArr.push(checkObject[0]);
                                    }

                                }
                            }



                            if(self.defaultPrices.length){


                                let checkObject = self.defaultPrices.filter(target => target.date == obj.date);


                                if(checkObject[0]){

                                    // the step to push into holder array
                                    let checkFoundInPricesArray = discountPricesArr.filter(element => element.date == checkObject[0].date);
                                    // console.log(checkFoundInPricesArray);
                                    if(!checkFoundInPricesArray[0]){
                                        if(self.daysOffersCollector[i].discount_type == 'percentage'){

                                            // console.log('am here');
                                            // percentage offer
                                            checkObject[0].price = (self.daysOffersCollector[i].discount_amount / 100) * checkObject[0].price;
                                        }else{
                                            // price odder
                                            checkObject[0].price = Number(self.daysOffersCollector[i].discount_amount)
                                        }
                                        // console.log(self.daysOffersCollector[i]);
                                        discountPricesArr.push(checkObject[0]);
                                    }

                                }

                            }




                        });



                        let totalDiscount = 0;

                        if(discountPricesArr.length){
                            $.each(discountPricesArr , function (i,val) {
                                totalDiscount += val.price;
                            })
                        }

                        // console.log(this.discounts);
                        // console.log(this.daysOffersCollector);

                        // let totalDiscount = this.discounts.length ? this.discounts.reduce((a, b) => parseFloat(a) + parseFloat(b), 0) : 0;
                        // let totalDiscount = discountPricesArr.length ? discountPricesArr.reduce((a, b) => parseFloat(a.price) + parseFloat(b.price), 0) : 0;

                        // console.log(totalDiscount);

                        this.subtotal_price = (y - totalDiscount).toFixed(2);
                        this.unit.reservation.prices.price = this.subtotal_price
                        this.total_ttx = ((this.subtotal_price / 100) * this.unit.reservation.prices.tourism_percentage).toFixed(2);
                        this.total_ewa = ((this.subtotal_price / 100) * this.unit.reservation.prices.ewa_parentage).toFixed(2);
                        let subtotal_with_ewa = Number((this.subtotal_price / 100) * this.unit.reservation.prices.ewa_parentage) + Number(this.subtotal_price);
                        this.total_vat = parseFloat((subtotal_with_ewa / 100) * this.unit.reservation.prices.vat_parentage).toFixed(2);
                        this.total_price = (Number(this.subtotal_price) + Number(this.total_ttx) + Number(this.total_ewa) + Number(this.total_vat)).toFixed(2) ;
                        this.unit.reservation.prices.total_price = this.total_price;
                        this.unit.reservation.prices.total_price_raw =  this.total_price;

                        this.unit.reservation.prices.sub_total =  this.subtotal_price;
                        this.unit.reservation.prices.total_ewa = this.total_ewa;
                        this.unit.reservation.prices.total_vat = this.total_vat;
                        this.unit.reservation.prices.total_tourism = this.total_ttx;



                    }
                }else{

                    this.getSpecialPrices(this.unit.id , this.unit , 'off');
                }


            },
        },
        created() {
            this.unit_id = this.$route.params.room_id;
            this.start_date = this.$route.params.date;
            this.selectedDate = {
                start: new Date(moment(String(this.$route.params.date)).toISOString()),
                end: new Date(moment(String(this.$route.params.date)).add('1', 'days').toISOString())
            };
            // this.getUnits();
            this.getHighlights();
            this.crumbs = [
                {
                    text: 'Home',
                    to: '/dashboards/main',
                },
                {
                    text: 'Add Reservation',
                    to: '/resources/reservations',
                },

            ];
        },
        watch: {
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
                    // if(this.customer.id_type != 2){
                    //     this.customer.id_number = val.id_number.replace(/([٠١٢٣٤٥٦٧٨٩])|([۰۱۲۳۴۵۶۷۸۹])/g, (m, $1, $2) => m.charCodeAt(0) - ($1 ? 1632 : 1776))
                    //     this.customer.id_number  = val.id_number.replace(/[^\d.\d]/g,'')
                    // }else{

                    //     this.customer.id_number = val.id_number.replace(/([٠١٢٣٤٥٦٧٨٩])|([۰۱۲۳۴۵۶۷۸۹])/g, (m, $1, $2) => m.charCodeAt(0) - ($1 ? 1632 : 1776))
                    //     this.customer.id_number  = val.id_number.replace(/[^a-z0-9]/gi,'')
                    // }


                    if(val.id === null){
                        this.customerNotes = [];
                        Nova.$emit('clear-collection');
                    }
                },
                deep: true
            },
            purpose(newVal, oldVal) {
                this.unit.purpose = newVal;
            },
            unit(newUnit, oldUnit) {


                this.$refs.details.updateUnit(newUnit);

                if(this.rent_type == 1){

                    if(newUnit){

                        this.getSpecialPrices(newUnit.id , newUnit);
                    }

                }

            },
        },
        mounted() {
            this.getSettings();
            this.getReservationServices();
            this.locale === 'en' ? this.calendarLocale = 'en' : this.calendarLocale = 'ar-sa';
            axios.get('/nova-vendor/calender/unit/selectors')
                .then((res) => {
                    this.nationalities = res.data.nationalities;
                    this.id_types = res.data.id_types;
                    this.nationalities = this.nationalities.filter(n => n.code == Nova.app.currentTeam.country_code)
                    // this.customer.country_id = Nova.app.currentTeam.country_code
                    this.id_types = this.id_types.filter(n => [1, 2, 3, 4, 5].includes(n.id))

                })

            this.commonSelectorsFunction();

            Nova.$on('selectedDateFilterUnits', (selectedDate) => {
                this.selectedDate = selectedDate;
                this.getUnits();

            });

            Nova.$on('subtotal' , (val) => {
                this.subtotal_price = val;
                this.updateTotalsReverse();
            })

            Nova.$on('total_price' , (val) => {
                this.total_price = val;
                this.updateTotals();
            })

            Nova.$on('call-get-unfilfilled-promissories' , (page) => {
              this.current_page = page;
              this.getUnfulfilledPromissories()
          })

          Nova.$on('companyIdSelected' , (val) => {
              this.company_id = val;
          })
          Nova.$on('attachable_reservation' , (reservation) => {
              this.attachable_reservation = reservation;
          })

        //   Nova.$on('disable-add-reservatoin-btn-cause-of-group-reservation-invoices-intersection',() => {
        //     this.disable_reservation = true;
        //   })

            Nova.$on('set-reservation-group-type' , (val) => {
                this.reservation_group_type = val;
            })

            Nova.$on('disable-reservation-button-book-in-past' , (val) => {
                this.disable_reservation = true;
            })

            Nova.$on('enable-reservation-button-book-in-past' , (val) => {
                this.disable_reservation = false;
            })


        },
        beforeDestroy() {
            Nova.$off('customer-creditor-debtor');
            Nova.$off('clear-collection');
            Nova.$off('attachable_reservation');
            // Nova.$off('disable-add-reservatoin-btn-cause-of-group-reservation-invoices-intersection');
        }
    }
</script>

<style lang="scss">
    @import '~vue2-autocomplete-js/dist/style/vue2-autocomplete.css';
    @import '~vue-tel-input/dist/vue-tel-input.css';
    .alert-warning {
        margin: 0 auto 15px;
        border-radius: 5px;
        padding: 10px;
        text-align: center;
        color: #b7791f;
        border: 1px solid #fbd38d;
        background: #fffaf0;
        font-size: 15px;
        cursor: pointer;
    }


    #new_reservation_page {
        .row_cols {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: flex-start;
            margin: 0 -10px;

            .right_col {
                width: 33.3333%;
                padding: 0 10px;
                align-self: stretch;
                margin: 0 0 20px;
                @media (min-width: 320px) and (max-width: 480px) {
                    width: 100%;
                }
                /* Mobile */
                @media (min-width: 481px) and (max-width: 767px) {
                    width: 50%;
                }
                /* Mobile */
                @media (min-width: 768px) and (max-width: 991px) {
                    width: 50%;
                }
                /* Mobile */
                .reservation_details {
                    border-radius: 5px;
                    border: 1px solid #ddd;
                    padding: 10px;
                    background: #fff;
                    box-shadow: 0 2px 4px 0 rgba(0, 0, 0, 0.05);
                    height: auto;
                }

                /* reservation_details */
            }

            /* right_col */
            .left_col {
                width: 66.66666%;
                padding: 0 10px;
                align-self: stretch;
                margin: 0 0 20px;
                @media (min-width: 320px) and (max-width: 480px) {
                    width: 100%;
                }
                /* Mobile */
                @media (min-width: 481px) and (max-width: 767px) {
                    width: 50%;
                }
                /* Mobile */
                @media (min-width: 768px) and (max-width: 991px) {
                    width: 50%;
                }
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

                /* customer_details */
            }

            /* left_col */
        }

        /* row_cols */
        .total_result {
            border-radius: 5px;
            border: 1px solid #ddd;
            padding: 10px;
            background: #fff;
            box-shadow: 0 2px 4px 0 rgba(0, 0, 0, 0.05);
            margin: 0 auto 20px;

            .loader_item {
                padding: 50px;
            }

            /* loader_item */
            .top_rows {
                display: flex;
                flex-wrap: wrap;
                justify-content: space-between;
                align-items: center;

                .col_right {
                    span {
                        display: block;
                        font-size: 15px;
                        margin: 0 0 5px;
                        color: #000;
                    }

                    /* span */
                }

                /* col_right */
                .col_left {
                    font-size: 20px;
                    color: #000;
                    display: flex;
                    align-items: flex-end;
                    justify-content: flex-end;
                    direction: ltr;

                    p {
                        font-size: 35px;
                        margin: 0 7px 0 0;
                        line-height: 1.1;
                    }

                    /* p */
                }

                /* col_left */
            }

            /* top_rows */
            .bottom_rows {
                padding: 10px 0 0;
                margin: 10px 0 0;
                border-top: 1px solid #dddddd;
                display: flex;
                justify-content: flex-end;

                ul {
                    min-width: 33.3333%;
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
                    li {
                        border-bottom: 1px solid #dddddd;
                        margin: 0 auto 10px;
                        padding: 0 0 10px;
                        display: flex;
                        justify-content: space-between;
                        align-items: center;

                        span {
                            display: block;
                            font-size: 15px;
                            color: #000;
                        }

                        /* span */
                        p {
                            display: flex;
                            align-items: center;
                            justify-content: flex-end;
                            font-size: 20px;
                            color: #000;
                            direction: ltr;

                            input {
                                background: #fafafa;
                                border: 1px solid #ddd !important;
                                color: #000;
                                margin: 0 8px 0 0;
                                min-width: 130px;
                                max-width: 100%;
                                text-align: center;
                                font-size: 20px;
                                display: block;
                                width: 130px;

                                &.total {
                                    margin: 0;
                                }
                            }

                            /* input */
                        }

                        /* p */
                        .total_ewa {
                            display: flex;
                            align-items: center;
                            justify-content: flex-end;
                            font-size: 20px;
                            color: #000;
                            direction: ltr;
                        }

                        /* total_ewa */
                        &:last-child {
                            margin: 0;
                            padding: 0;
                            border-bottom: none;
                        }

                        /* last-child  */
                    }

                    /* li */
                }

                /* ul */
            }

            /* bottom_rows */
        }

        /* total_result */
        .button_area {
            display: flex;
            justify-content: flex-end;

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

            /* button */
        }

        /* button_area */
    }

    /* new_reservation_page */

    .date_picker_alert {
        display: block;
        background: #fff3cd;
        border: 1px solid #ffeeba;
        color: #856404;
        padding: 10px;
        font-size: 15px;

        span {
            display: block;
            font-family: 'Dubai-Bold';
        }

        /* span */
    }

    /* date_picker_alert */

    .green_alert {
        display: block;
        background: #d4edda;
        border: 1px solid #c3e6cb;
        color: #155724;
        padding: 10px;
        font-size: 15px;

        span {
            display: block;
            font-family: 'Dubai-Bold';
        }

        /* span */
    }


    .gray_alert{

        display: block;
        background: #e6e6e6;
        border: 1px solid #c5c4c4;
        color: #767776;
        padding: 10px;
        font-size: 15px;

        span {
            display: block;
            font-family: 'Dubai-Bold';
        }

    }

    .switch_group {
      margin: 0 auto 15px;
      display: flex;
      align-items: center;
      justify-content: flex-start;
      flex-wrap: wrap;
      label {
        font-weight: bold;
        margin: 0;
        min-width: 130px;
      } /* label */
      .switch {
        position: relative;
        display: inline-block;
        width: 60px;
        height: 26px;
        input {
          opacity: 0;
          width: 100% !important;
          height: 100% !important;
          z-index: 99;
          position: relative;
          cursor: pointer;
          &:checked ~ {
            .slider {
              background-color: #21b978;
              &:before {
                -webkit-transform: translateX(33px);
                -ms-transform: translateX(33px);
                transform: translateX(33px);
              } /* before */
            } /* slider */
          } /* checked */
          &:focus {
            .slider {
              box-shadow: 0 0 1px #21b978;
            } /* slider */
          } /* focus */
        } /* input */
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
          &:before {
            position: absolute;
            content: "";
            height: 20px;
            width: 20px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            -webkit-transition: .4s;
            transition: .4s;
          } /* before */
          &.round {
            border-radius: 34px;
            &:before {
              border-radius: 50%;
            } /* before */
          } /* round */
        } /* slider */
      } /* switch */
    } /* switch_group */


    .select {
        display: block !important;
        button.btn-select {
            width: 100%;
            height: 40px;
            border: 1px solid #ddd;
            background-color: #fafafa;
            outline: none;
            box-shadow: none;
            padding : 0;
            background-image: url("data:image/svg+xml,%3C%3Fxml version='1.0' encoding='iso-8859-1'%3F%3E%3C!-- Generator: Adobe Illustrator 19.0.0, SVG Export Plug-In . SVG Version: 6.00 Build 0) --%3E%3Csvg version='1.1' id='Layer_1' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' x='0px' y='0px' viewBox='0 0 491.996 491.996' style='enable-background:new 0 0 491.996 491.996;' xml:space='preserve'%3E%3Cg%3E%3Cg%3E%3Cpath d='M484.132,124.986l-16.116-16.228c-5.072-5.068-11.82-7.86-19.032-7.86c-7.208,0-13.964,2.792-19.036,7.86l-183.84,183.848 L62.056,108.554c-5.064-5.068-11.82-7.856-19.028-7.856s-13.968,2.788-19.036,7.856l-16.12,16.128 c-10.496,10.488-10.496,27.572,0,38.06l219.136,219.924c5.064,5.064,11.812,8.632,19.084,8.632h0.084 c7.212,0,13.96-3.572,19.024-8.632l218.932-219.328c5.072-5.064,7.856-12.016,7.864-19.224 C491.996,136.902,489.204,130.046,484.132,124.986z'/%3E%3C/g%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3C/svg%3E%0A");
            background-position: 15px center;
            background-repeat: no-repeat;
            background-size: 14px;
            border-radius: 5px;

            .buttonLabel {
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 0 10px;
                color: #000;
                font-size: 15px;
                span {
                    color: #000;
                    &.caret {
                        display:none;


                    }
                }
            }
        }
        .checkboxLayer {
                margin: 0 auto 5px;
                border-radius: 5px !important;
                border: 1px solid #ddd !important;
                min-width: 339px !important;
                bottom: 100%;
                box-shadow: 0 -6px 12px rgba(0,0,0,0.18) !important;

                div {
                     &:nth-child(2) {
                        display: none;
                    }
                }


                .helperContainer{
                    padding: 8px !important;
                    border-bottom: 1px solid #ddd;
                    margin-bottom: 8px;
                    button.helperButton {
                        min-width: 100px;
                        padding: 0 10px !important;
                        border-radius: 4px !important;
                        margin: 0 !important;
                    }
                }

                .checkBoxContainer{
                    padding: 0 !important;
                    max-height: 250px !important;

                    ul{
                        li{
                            padding: 10px !important;
                            direction: rtl;
                            font-size: 15px;
                            color: #000;
                            height: 45px;

                            span {
                                margin: 0 !important;
                                display: block;
                                &.right {
                                    float: left !important;
                                }
                                &.margin-left-20 {
                                    float: right !important;
                                }
                            }
                        }
                    }
                }
        }


    }
</style>
