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
            <div id="revenue_tax_fee_report_page">
                <div class="title">{{__('Revenues, Taxes & Fees Report')}}</div>
                <div class="content_page">
                    <div class="alert">
          <span>
            <svg class="fill-current w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
            <path d="M12.432 0c1.34 0 2.01.912 2.01 1.957 0 1.305-1.164 2.512-2.679 2.512-1.269 0-2.009-.75-1.974-1.99C9.789 1.436 10.67 0 12.432 0zM8.309 20c-1.058 0-1.833-.652-1.093-3.524l1.214-5.092c.211-.814.246-1.141 0-1.141-.317 0-1.689.562-2.502 1.117l-.528-.88c2.572-2.186 5.531-3.467 6.801-3.467 1.057 0 1.233 1.273.705 3.23l-1.391 5.352c-.246.945-.141 1.271.106 1.271.317 0 1.357-.392 2.379-1.207l.6.814C12.098 19.02 9.365 20 8.309 20z"/></svg>
            {{__('Please select date from & date to to begin filtering this report')}}
          </span>
                    </div><!-- alert -->
                    <div class="filter_area">
                        <div class="item">
                            <input
                                readonly
                                v-model="dateFrom"
                                ref="datePickerFrom"
                                type="text"
                                :placeholder="__('Date From')"
                            >
                        </div>

                        <div class="item">
                            <input
                                readonly
                                v-model="dateTo"
                                ref="datePickerTo"
                                type="text"
                                :placeholder="__('Date To')"
                            >
                        </div>
                        <div class="item">
                            <select v-model="unitCategoryId">
                                <option value="null" selected>{{__('Unit Category')}}</option>
                                <option v-for="(category,i) in unit_categories" :value="category.value" :key="i">{{ category.name }}</option>
                            </select>
                        </div>
                        <div class="item">
                            <select v-model="ewa_status">

                                <option value="ewa_only">
                                    {{ __("Ewa Fees") }}
                                </option>

                                <option value="full">
                                    {{ __("Ewa + Vat Fees") }}
                                </option>
                            </select>
                        </div>

                        <div class="item">
                            <select v-model="reservation_status">

                                <option value=""  selected disabled >
                                    {{ __("Reservation Status") }}
                                </option>

                                <option value="all">
                                    {{ __("All") }}
                                </option>

                                <option value="pending">
                                    {{ __("Pending") }}
                                </option>

                                <option value="checked_in">
                                    {{ __("Checked In") }}
                                </option>

                                <option value="checked_out">
                                    {{ __("Checked Out") }}
                                </option>

                                <option value="checked_in_checked_out">
                                    {{ __("Checked In + Checked Out") }}
                                </option>

                            </select>
                        </div>
                        
                        <div class="item" v-if="isCalculatePriceByDayEnable">
                            <div class="toggle-switch">
                                <input type="checkbox" id="toggle" v-model="togglePriceByDay"/>
                                <label for="toggle"></label>
                                <p>{{ __("Switch to price by day") }}</p>
                            </div>
                        </div>
                        
                        <!-- <div></div> -->
                        <div class="reset_filters">
                            <button
                                @click="resetFilters()"
                                :disabled="isDisabledSearchBtn"
                                v-tooltip="{
                                targetClasses: ['it-has-a-tooltip'],
                                placement: 'top',
                                content: __('Reset Filters'),
                                classes: ['tooltip_reset']
                              }"
                            >
                            </button>
                        </div><!-- reset_filters -->
                    </div><!-- filter_area -->
                    <div class="general_statistics relative" v-if="showData">
                        <!-- Loader -->
                        <loading :active.sync="isRevenueLoading" :can-cancel="true" :loader="'spinner'" :color="'#7e7d7f'" :is-full-page="fullPage"></loading>
                        <div class="item">
                            <div class="table-responsive">
                                <table>
                                    <tbody>

                                    <tr v-if="ewa_status == 'ewa_only'">
                                        <td>{{__('Total Leasing Revenue')}}</td>
                                        <td>{{calculations.leasing_revenue ? calculations.leasing_revenue : '-' }} <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></td>
                                    </tr>

                                    <tr v-if="ewa_status == 'full'">
                                        <td>{{__('Leasing Revenue')}}</td>
                                        <td>{{calculations.leasing_revenue ? calculations.leasing_revenue : '-' }} <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></td>
                                    </tr>
                                    <tr v-if="ewa_status == 'full'">
                                        <td>{{__('Services Revenue')}}</td>
                                        <td>{{calculations.services_revenue ? calculations.services_revenue  : '-' }} <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></td>
                                    </tr>
                                    <tr v-if="ewa_status == 'full'">
                                        <td>{{__('Total Revenue')}}</td>
                                        <td>{{calculations.total_revenue ?  calculations.total_revenue : '-' }} <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div><!-- table-responsive -->
                        </div><!-- item -->
                        <div class="item2">
                            <div class="table-responsive">
                                <table>
                                    <tbody>
                                    <tr><td>{{__('Total Ewa')}}</td></tr>
                                    <tr><td>{{calculations.total_ewa ? calculations.total_ewa  : '-' }} <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></td></tr>
                                    </tbody>
                                </table>
                            </div><!-- table-responsive -->
                        </div><!-- item2 -->
                        <div class="item" v-if="ewa_status == 'full'">
                            <div class="table-responsive">
                                <table>
                                    <tbody>
                                    <tr>
                                        <td>{{__('Vat on reservation')}}</td>
                                        <td>{{calculations.vat_on_reservation ? calculations.vat_on_reservation : '-' }} <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></td>
                                    </tr>
                                    <tr>
                                        <td>{{__('Vat on services')}}</td>
                                        <td>{{calculations.vat_on_services ?  calculations.vat_on_services : '-' }} <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></td>
                                    </tr>
                                    <tr>
                                        <td>{{__('Total Vat')}}</td>
                                        <td>{{calculations.total_vat ? calculations.total_vat : '-' }} <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div><!-- table-responsive -->
                        </div><!-- item -->
                        <div class="item" v-if="hasTtx">
                            <div class="table-responsive">
                                <table>
                                    <tbody>
                                    <tr>
                                        <td>{{__('Ttx on reservation')}}</td>
                                        <td>{{calculations.ttx_on_reservation ? calculations.ttx_on_reservation : '-'  } <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span>}</td>
                                    </tr>
                                    <tr>
                                        <td>{{__('Ttx on services')}}</td>
                                        <td>{{calculations.ttx_on_services ? calculations.ttx_on_services : '-'  } <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span>}</td>
                                    </tr>
                                    <tr>
                                        <td>{{__('Total Ttx')}}</td>
                                        <td>{{calculations.total_ttx ?  calculations.total_ttx : '-' }} <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div><!-- table-responsive -->
                        </div><!-- item -->
                        <div class="item2">
                            <div class="table-responsive">
                                <table>
                                    <tbody>
                                    <tr><td>{{__('The Total')}}</td></tr>
                                    <tr><td>{{calculations.total_reservations ? calculations.total_reservations : '-' }} <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></td></tr>
                                    </tbody>
                                </table>
                            </div><!-- table-responsive -->
                        </div><!-- item2 -->
                    </div><!-- general_statistics -->
                    <div class="transactions_table" v-if="showData">
                        <div class="relative">

                            <!-- Loader -->
                            <loading :active.sync="isRevenueLoading"
                                     :can-cancel="true"
                                     :loader="'spinner'"
                                     :color="'#7e7d7f'"
                                     :is-full-page="fullPage"></loading>
                            <!-- Export Options -->
                            <div class="w-full flex flex-row-reverse flex-wrap my-2 items-center" v-if="data.length">
                                <svg @click="excelExport()" class="cursor mx-4 cursor-pointer" xmlns="http://www.w3.org/2000/svg" width="23.308" height="23.308" viewBox="0 0 23.308 23.308"><path d="M24.213,3H16V5.675h2.717V7.5H16V9.275h2.689v1.793H16v1.793h2.689v1.793H16v1.793h2.689V18.24H16v2.689h8.213a.768.768,0,0,0,.751-.78V3.78A.768.768,0,0,0,24.213,3ZM23.172,18.24H19.586V16.447h3.586Zm0-3.586H19.586V12.861h3.586Zm0-3.586H19.586V9.275h3.586Zm0-3.586H19.586V5.689h3.586Z" transform="translate(-1.657 -0.311)" fill="#333b45"/><path d="M0,2.59V20.719l13.447,2.589V0ZM8.505,16.208,6.941,13.25a2.623,2.623,0,0,1-.184-.608H6.733a4.6,4.6,0,0,1-.21.634l-1.57,2.931H2.516l2.894-4.54L2.763,7.128H5.251l1.3,2.723a4.756,4.756,0,0,1,.273.766h.025q.077-.266.285-.792l1.443-2.7h2.279l-2.723,4.5,2.8,4.578Z" fill="#333b45"/></svg>
                                <svg @click="printReport()" class="cursor  cursor-pointer" xmlns="http://www.w3.org/2000/svg" width="19.339" height="19.339" viewBox="0 0 19.339 19.339"><path d="M17.471,17.471v1.934a1.934,1.934,0,0,1-1.934,1.934H7.8a1.934,1.934,0,0,1-1.934-1.934V17.471H3.934A1.934,1.934,0,0,1,2,15.537v-5.8A1.94,1.94,0,0,1,3.934,7.8H5.868V3.934A1.94,1.94,0,0,1,7.8,2h7.735a1.934,1.934,0,0,1,1.934,1.934V7.8H19.4a1.934,1.934,0,0,1,1.934,1.934v5.8A1.934,1.934,0,0,1,19.4,17.471Zm0-1.934H19.4v-5.8H3.934v5.8H5.868V13.6A1.94,1.94,0,0,1,7.8,11.669h7.735A1.934,1.934,0,0,1,17.471,13.6ZM15.537,7.8V3.934H7.8V7.8ZM7.8,13.6v5.8h7.735V13.6Z" transform="translate(-2 -2)" fill="#333b45"/></svg>
                            </div>


                            <div class="table-responsive">
                                <table class="table w-full">
                                    <thead>
                                    <tr v-if="ewa_status == 'full'">
                                        <th colspan="8"></th>
                                        <th :colspan="ewa_status == 'full' ? 2 : 1">{{__('Revenue')}}</th>
                                        <th :colspan="ewa_status == 'full' ? 2 : 1"></th>
                                        <th colspan="2" v-if="ewa_status == 'full'">{{__('VAT')}}</th>
                                        <th v-if="ewa_status == 'full'"></th>
                                        <template  v-if="hasTtx">
                                            <th colspan="2">{{__('TTX')}}</th>
                                        </template>
                                        <th colspan="2"></th>
                                    </tr>
                                    <tr>
                                        <th>{{__('Reservation Number')}}</th>
                                        <th>{{__('Reservation Status')}}</th>
                                        <th>{{__('The Unit')}}</th>
                                        <th>{{__('Customer name')}}</th>
                                        <th>{{__('Source')}}</th>
                                        <th>{{__('From Date')}}</th>
                                        <th>{{__('To Date')}}</th>
                                        <th>{{__('Nights Count')}}</th>
                                        <!-- This part for revenue header -->
                                        <th>{{__('Leasing')}}</th>
                                        <th v-if="ewa_status == 'full'">{{ __('Services') }}</th>
                                        <th>{{__('Total Revenue')}}</th>
                                        <th>{{__('Ewa')}}</th>
                                        <!-- This part for vat header -->
                                        <th v-if="ewa_status == 'full'">{{__('On Leasing')}}</th>
                                        <th v-if="ewa_status == 'full'">{{__('On Services')}}</th>
                                        <th v-if="ewa_status == 'full'">{{__('Total VAT')}}</th>
                                        <template  v-if="hasTtx">
                                            <!-- This part for ttx header -->
                                            <th>{{__('On Leasing')}}</th>
                                            <th>{{__('On Services')}}</th>
                                            <th>{{__('Total TTX')}}</th>
                                        </template>
                                        <th>{{__('The Total')}}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr class="hover:bg-grey-lighter" v-if="data.length" v-for="(revenue , index) in data" :key="index">
                                        <td class="py-2 text-center border-b border-grey-light">
                                            <a class="no-underline dim text-primary font-bold view_reservation" href="" :data-attr-id="revenue.reservation_id"> {{revenue.reservation_number}} </a>
                                        </td>
                                        <td>
                                            <div v-if="revenue.reservation_status == 'pending'" class="pending">
                                                {{ __("Pending") }}
                                            </div>
                                            <div
                                                v-if="revenue.reservation_status == 'checked-in'"
                                                class="checked_in"
                                            >
                                                {{ __("Checked in") }}
                                            </div>
                                            <div
                                                v-if="revenue.reservation_status == 'checked-out'"
                                                class="checked_out"
                                            >
                                                {{ __("Checked out") }}
                                            </div>
                                        </td>
                                        <td class="py-2 text-center border-b border-grey-light">{{(revenue.unit_number && revenue.unit_name) ? revenue.unit_number + ' - ' + revenue.unit_name[locale] : '-'}}</td>
                                        <td class="py-2 text-center border-b border-grey-light">{{revenue.customer_name}}</td>
                                        <td class="py-2 text-center border-b border-grey-light">  {{ getLocalizedText(revenue.reservation_source) }}
                                        </td>
                                        <td class="py-2 text-center border-b border-grey-light">{{revenue.from_date}}</td>
                                        <td class="py-2 text-center border-b border-grey-light">{{revenue.to_date}}</td>
                                        <td class="py-2 text-center border-b border-grey-light"> {{revenue.nights_count.toFixed(2)}} </td>
                                        <td class="py-2 text-center border-b border-grey-light"> {{revenue.leasing_price.toFixed(2)}} </td>
                                        <td v-if="ewa_status == 'full'" class="py-2 text-center border-b border-grey-light"> {{revenue.services.toFixed(2)}} </td>
                                        <td class="py-2 text-center border-b border-grey-light"> {{revenue.total_revenue.toFixed(2)}} </td>
                                        <td class="py-2 text-center border-b border-grey-light"> {{revenue.total_ewa.toFixed(2)}} </td>
                                        <td v-if="ewa_status == 'full'" class="py-2 text-center border-b border-grey-light"> {{revenue.vat_on_reservation.toFixed(2)}} </td>
                                        <td v-if="ewa_status == 'full'" class="py-2 text-center border-b border-grey-light"> {{revenue.vat_on_services.toFixed(2)}} </td>
                                        <td v-if="ewa_status == 'full'" class="py-2 text-center border-b border-grey-light"> {{revenue.total_vat.toFixed(2)}} </td>
                                        <template  v-if="hasTtx">
                                            <td class="py-2 text-center border-b border-grey-light"> {{revenue.ttx_on_reservation.toFixed(2)}} </td>
                                            <td class="py-2 text-center border-b border-grey-light"> {{revenue.ttx_on_services.toFixed(2)}} </td>
                                            <td class="py-2 text-center border-b border-grey-light"> {{revenue.total_ttx.toFixed(2)}} </td>
                                        </template>
                                        <td class="py-2 text-center border-b border-grey-light"> {{revenue.total_reservation.toFixed(2)}} </td>
                                    </tr>
                                    <tr v-if="!data.length">
                                        <td colspan="17" class="text-center">{{__('No data found')}}</td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div><!-- table-responsive -->
                            <div class="w-full flex flex-wrap mb-4 mt-3 justify-center" v-if="data.length">
                                <pagination
                                    v-if="paginator.total > per_page"
                                    :page-count="paginator.lastPage"
                                    :page-range="3"
                                    :margin-pages="2"
                                    :value="paginator.currentPage"
                                    :prev-text="__('Previous')"
                                    :next-text="__('Next')"
                                    :container-class="'pagination  w-full flex justify-center'"
                                    :page-class="'page-item'"
                                    :page-link-class="'page-link'"
                                    :prev-link-class="'page-link'"
                                    :next-link-class="'page-link'"
                                    :prev-class="'page-item'"
                                    :next-class="'page-item'"
                                    :first-last-button="true"
                                    :first-button-text="__('First')"
                                    :last-button-text="__('Last')"
                                    @input="getCurrentPage($event)"
                                >
                                </pagination>
                                <div class="w-full flex justify-between mt-4 mb-2">
                                    <p>{{__('Results')}}  : {{__('From')}} ( {{paginator.from}} ) - {{__('To')}}  ( {{paginator.to}} )</p>
                                    <p>{{__('Reservations Count')}}  : {{paginator.total}}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Print -->
                        <form id="revenue_tax_form" target="_blank" method="post"  style="display: none" action="/home/print/revenueTaxFeesReport">
                            <input type="hidden" :value="JSON.stringify(all_data)" name="all_data">
                            <input type="hidden" :value="JSON.stringify(calculations)" name="calculations">
                            <input type="hidden" :value="JSON.stringify(hasTtx)" name="hasTtx">
                            <input type="hidden" name="dateFilter" :value="JSON.stringify(dateValues)">
                            <input type="hidden" name="ewa_status" :value="ewa_status">
                            <input type="hidden" name="reservation_status" :value="reservation_status">
                        </form>

                    </div><!-- transactions_table -->
                </div><!-- content_page -->
            </div><!-- revenue_tax_fee_report_page -->
        </div>
</template>

<script>

import Pagination from './Pagination';
import Loading from 'vue-loading-overlay';
import moment from 'moment';
import flatpickr from 'flatpickr'
import 'vue-loading-overlay/dist/vue-loading.css';
import XLSX from 'xlsx';

export default {
    name: "revenue-and-taxes-report",
    components:{
        Pagination ,
        Loading,
    },
    data() {
        return {
            paginator : {},
            calculations : {},
            data : [],
            all_data : [],
            isRevenueLoading: true,
            fullPage: false,
            dateFrom:null,
            dateTo:null,
            currency :Nova.app.currentTeam.currency,
            locale : Nova.config.local,
            isDisabledSearchBtn:true,
            isDateSelected : false ,
            durations : [],
            currency :Nova.app.currentTeam.currency,
            showData : false,
            hasTtx : false,
            per_page : 20,
            query : {},
            selectedPage : 1,
            legacyQuery : {},
            team_id : Nova.config.user.current_team_id,
            crumbs : [],
            ewa_status : 'full',
            reservation_status : 'all',
            unitCategoryId : null,
            unit_categories : [],
            togglePriceByDay: false,
            isCalculatePriceByDayEnable: Nova.app.currentTeam.check_calculate_price_by_day_enable
        }
    },
    methods:{
        getLocalizedText(name) {
    try {
      if (!name) return '-'; // If name is null or undefined, return '-'

      const parsedName = JSON.parse(name.name); // Parse the JSON string
      return parsedName[this.locale] || parsedName['en'] || '-'; // Fallback to 'en' or '-'
    } catch (e) {
      return '-'; // In case of invalid JSON, return '-'
    }
  },

        getCurrentPage(page){
            this.selectedPage = page;
        },
        getData(){
            this.isRevenueLoading = true ;

             let config = {
                headers : {
                    'x-team' : this.team_id,
                    'x-localization' : this.locale
                },
                params : this.$route.query
            }
            axios.get(window.FANDAQAH_API_URL + `/reservations/revenue-and-tax-report` , config)
                .then((res)=>{

                    if(res.data.flag === 'data_found'){
                        this.data = res.data.reservations.data;

                        this.paginator = {
                            currentPage : res.data.reservations.current_page ,
                            lastPage : res.data.reservations.last_page ,
                            from : res.data.reservations.from,
                            to : res.data.reservations.to,
                            total : res.data.reservations.total,
                            pathPage : res.data.reservations.path + '?page=',
                            firstPageUrl : res.data.reservations.first_page_url ,
                            lastPageUrl : res.data.reservations.last_page_url ,
                            nextPageUrl : res.data.reservations.next_page_url ,
                            prevPageUrl : res.data.reservations.prev_page_url ,

                        };
                        // Calculations
                        this.calculations = {
                            leasing_revenue : res.data.calculations.leasing_revenue ,
                            services_revenue : res.data.calculations.services_revenue ,
                            total_revenue : res.data.calculations.total_revenue ,
                            total_ewa : res.data.calculations.total_ewa ,
                            vat_on_reservation : res.data.calculations.vat_on_reservation,
                            vat_on_services : res.data.calculations.vat_on_services,
                            total_vat : res.data.calculations.total_vat,
                            ttx_on_reservation : res.data.calculations.ttx_on_reservation,
                            ttx_on_services : res.data.calculations.ttx_on_services,
                            total_ttx : res.data.calculations.total_ttx,
                            total_reservations : res.data.calculations.total_reservations
                        };
                        this.all_data = res.data.all_reservations ;

                        this.hasTtx = res.data.hasAtLeastOneTourismTaxApplied ;
                    }else{

                        this.data = [] ;
                        this.calculations = {
                            leasing_revenue : 0 ,
                            services_revenue : 0 ,
                            total_revenue : 0 ,
                            total_ewa : 0 ,
                            vat_on_reservation : 0,
                            vat_on_services : 0,
                            total_vat : 0,
                            ttx_on_reservation : 0,
                            ttx_on_services : 0,
                            total_ttx : 0,
                            total_reservations : 0
                        };
                    }

                    this.query = this.$route.query;
                    this.isRevenueLoading = false ;
                })
                .catch((err)=>{
                    this.$toasted.show(err , {type : 'error'}) ;
                })

        },

        capitalizeFirstLetter(str){
            return str.charAt(0).toUpperCase() + str.slice(1);
        },

        resetFilters(){

            let opt = {}
            opt["page"] = 1;
            this.$router.push({
                name : 'revenue-and-taxes-report',
                query: Object.assign({}, this.legacyQuery, opt)
            } , () => {
                this.dateFrom = null;
                this.dateTo = null;
                this.isDateSelected = false ;
                this.isDisabledSearchBtn = true ;
                this.ewa_status = 'full';
                this.reservation_status = 'all',
                this.unitCategoryId = null;
                this.getData();
            })
        },
        excelExport(){
            console.log(this.all_data);

   this.all_data = this.all_data.map(reservation => ({
    ...reservation,
    leasing_price: parseFloat(String(reservation.leasing_price).replace(/,/g, '')),
    nights_count: parseFloat(String(reservation.nights_count).replace(/,/g, '')),
    total_ewa: parseFloat(String(reservation.total_ewa).replace(/,/g, '')),
    services: parseFloat(String(reservation.services).replace(/,/g, '')),
    total_reservation: parseFloat(String(reservation.total_reservation).replace(/,/g, '')),
    total_revenue: parseFloat(String(reservation.total_revenue).replace(/,/g, '')),
    total_ttx: parseFloat(String(reservation.total_ttx).replace(/,/g, '')),
    total_vat: parseFloat(String(reservation.total_vat).replace(/,/g, '')),
    ttx_on_reservation: parseFloat(String(reservation.ttx_on_reservation).replace(/,/g, '')),
    ttx_on_services: parseFloat(String(reservation.ttx_on_services).replace(/,/g, '')),
    vat_on_reservation: parseFloat(String(reservation.vat_on_reservation).replace(/,/g, '')),
    vat_on_services: parseFloat(String(reservation.vat_on_services).replace(/,/g, ''))
}));

            this.isRevenueLoading = true ;
            axios.post(window.FANDAQAH_API_URL + `/reservations/revenue-and-tax-report-excel`, {
                data : this.all_data,
                lang : this.locale,
                ewa_status : this.ewa_status,
                reservation_status : this.reservation_status
            }).then(response => {

                // Data coming from my tool controller
                let dataToBeExported = response.data.data;
                // Export Json Data as worksheet
                let revenueWs = XLSX.utils.json_to_sheet(dataToBeExported);
                // New workbook instance
                let wb = XLSX.utils.book_new(); // make Workbook of Excel
                // Adding worksheet to workbook
                XLSX.utils.book_append_sheet(wb, revenueWs, this.__('Revenues Report')); // sheetAName is name of Worksheet

                // Export file
                XLSX.writeFile(wb, this.__('Revenues Report') + '.xlsx');
                // fire success toast
                this.$toasted.show(this.__('Report was exported successfully'), {type: 'success'});
                this.isRevenueLoading = false ;
            });

        },
        printReport(){
            $('#revenue_tax_form').submit();
        },
        getUnitCategoryFilterValues() {
            Nova.request()
            .get("/nova-vendor/calender/reservations/unit-category-filter-values")
            .then((response) => {
                this.unit_categories = response.data;
            });
        },

    },
    computed: {
        dateFormat() {
            return  'Y-m-d'
        },
        dateValues() {
            const { dateFrom, dateTo } = this
            return {
                dateFrom,
                dateTo
            }
        }
    },
    watch:{
        dateValues: {
            handler: function(val) {

                if((val.dateFrom != null && val.dateFrom != "" ) && (val.dateTo != null && val.dateTo != "" )){
                    this.isDisabledSearchBtn = false ;
                    this.isDateSelected = true ;

                    // doing some checks
                    let dtFrom = new Date(val.dateFrom);
                    let dtTo = new Date(val.dateTo);

                    if(dtFrom > dtTo || dtTo < dtFrom){
                        this.$toasted.show(this.__('Please Make Sure to Supply Valid Dates'), {type: 'error'});
                        return false ;
                    }

                    let opt = {}
                    opt["date_from"] = val.dateFrom;
                    opt["date_to"] = val.dateTo;
                    opt["ewa_status"] = this.ewa_status;
                    opt["reservation_status"] = this.reservation_status;
                    opt["page"] = 1;
                    this.$router.push({
                        name : 'revenue-and-taxes-report',
                        query: Object.assign({}, this.$route.query, opt)
                    } , () => {
                        this.getData();
                    })
                    this.showData = true ;
                }else{
                    this.showData = false;
                }
            },
            deep: true
        },
        selectedPage: function (val) {
            if(val){
                let opt = {}
                opt["page"] = val;
                this.$router.push({
                    name : 'revenue-and-taxes-report',
                    query: Object.assign({}, this.$route.query, opt)
                } , () => {
                    this.getData();
                })
            }
        },
        ewa_status: function (val) {
            if(val != null){
                let opt = {}
                opt["ewa_status"] = val;
                opt["page"] = 1;
                this.$router.push({
                    name : 'revenue-and-taxes-report',
                    query: Object.assign({}, this.$route.query, opt)
                } , () => {
                    this.getData();
                })
            }

            if(val == ''){
                let opt = {}
                opt["page"] = 1;
                this.$router.push({
                    name : 'revenue-and-taxes-report',
                    query: Object.assign({}, this.$route.query, opt)
                } , () => {
                    this.getData();
                })
            }
        },
        reservation_status: function (val) {
            if(val != null){
                let opt = {}
                opt["reservation_status"] = val;
                opt["page"] = 1;
                this.$router.push({
                    name : 'revenue-and-taxes-report',
                    query: Object.assign({}, this.$route.query, opt)
                } , () => {
                    this.getData();
                })
            }

            if(val == ''){
                let opt = {}
                opt["page"] = 1;
                this.$router.push({
                    name : 'revenue-and-taxes-report',
                    query: Object.assign({}, this.$route.query, opt)
                } , () => {
                    this.getData();
                })
            }
        },
        unitCategoryId: function (val) {
            console.log(val);
            if(val != null || val != 'null'){
                console.log('am not null');
                let opt = {}
                opt["by_unit_category"] = val;
                opt["page"] = 1;
                this.$router.push({
                    name : 'revenue-and-taxes-report',
                    query: Object.assign({}, this.$route.query, opt)
                } , () => {
                    this.getData();
                })
            }

            if(val == null || val == 'null'){
                console.log('am here');
                let opt = {}
                opt["by_unit_category"] = null;
                opt["page"] = 1;
                this.$router.push({
                    name : 'revenue-and-taxes-report',
                    query: Object.assign({}, this.$route.query, opt)
                } , () => {
                    this.getData();
                })
            }
        },
        togglePriceByDay: function (val) {
            let opt = {}
            opt["by_price_by_day"] = val;
            opt["page"] = 1;
            this.$router.push({
                    name : 'revenue-and-taxes-report',
                    query: Object.assign({}, this.$route.query, opt)
            } , () => {
                if(this.dateFrom != null && this.dateTo != null) {
                    this.getData();
                }
            })  
        }
    },
    mounted() {

        this.$router.replace('/reports/revenue-and-taxes?per_page=20')
        const self = this
        this.$nextTick(() => {
            this.flatpickrFrom = flatpickr(this.$refs.datePickerFrom, {
                enableTime: false,
                enableSeconds: false,
                disableMobile: "true",
                dateFormat: this.dateFormat,
                allowInput: false,
                mode: 'single',
                time_24hr: true,
                onReady() {
                    self.$refs.datePickerFrom.parentNode.classList.add('date-filter')
                },
                onChange(){
                    self.dateFrom = self.$refs.datePickerFrom.value;
                },
                locale : self.locale
            })

            this.flatpickrTo = flatpickr(this.$refs.datePickerTo, {
                enableTime: false,
                enableSeconds: false,
                disableMobile: "true",
                dateFormat: this.dateFormat,
                allowInput: false,
                mode: 'single',
                time_24hr: true,
                onReady() {
                    self.$refs.datePickerTo.parentNode.classList.add('date-filter')
                },
                onChange(){
                    self.dateTo = self.$refs.datePickerTo.value;
                },
                locale : self.locale
            })

        })

        this.per_page = this.$route.query.per_page;
        this.crumbs = [
            {
                text : 'Home',
                to : '/dashboards/main'
            },

            {
                text : 'Reports',
                to : '/reports'
            },

            {
                text : 'Revenues, Taxes & Fees Report',
                to : '#'
            }
        ];

        this.query = this.$route.query;
        this.legacyQuery = this.$route.query;
        this.getUnitCategoryFilterValues();
    }
}
</script>

<style lang="scss">
#revenue_tax_fee_report_page {
    margin: 0 auto;
    border: 1px solid #ddd;
    border-radius: .5rem;
    box-shadow: 0 2px 4px 0 rgba(0, 0, 0, 0.05);
    overflow: hidden;
    .title{
        background: #f7fafc;
        border-bottom: 1px solid #ddd;
        padding: .75rem;
        color: #000;
        font-size: 1.125rem;
        display: block;
    } /* title */
    .content_page {
        background: #fff;
        padding: 10px;
        .alert {
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            span {
                display: flex;
                background-color: #fff3cd;
                border: 1px solid #ffeeba;
                color: #856404;
                font-size: 15px;
                border-radius: 100px;
                padding: 10px 30px;
                align-items: center;
                justify-content: center;
                svg {
                    margin: 0 0 0 6px;
                } /* svg */
            } /* span */
        } /* alert */
        .filter_area {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            justify-content: flex-start;
            margin: 0 -10px;
            .item {
                width: 16.66%;
                padding: 0 10px;
                margin: 0 0 10px;
                @media (min-width: 320px) and (max-width: 480px) {
                    width: 50%;
                } /* media */
                @media (min-width: 481px) and (max-width: 767px) {
                    width: 33.33333%;
                } /* media */
                @media (min-width: 768px) and (max-width: 991px) {
                    width: 25%;
                } /* media */
                input {
                    background: #fafafa;
                    height: 40px;
                    padding: 0 10px;
                    font-size: 15px;
                    border: 1px solid #ddd !important;
                    color: #000;
                    width: 100%;
                    border-radius: 4px !important;
                    outline: none;
                } /* input */
                select {
                    background-color: #fafafa;
                    background-image: url("data:image/svg+xml,%3C%3Fxml version='1.0'%3F%3E%3Csvg xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' version='1.1' id='Layer_1' x='0px' y='0px' viewBox='0 0 512.011 512.011' style='enable-background:new 0 0 512.011 512.011;' xml:space='preserve' width='512px' height='512px' class=''%3E%3Cg%3E%3Cg%3E%3Cg%3E%3Cpath d='M505.755,123.592c-8.341-8.341-21.824-8.341-30.165,0L256.005,343.176L36.421,123.592c-8.341-8.341-21.824-8.341-30.165,0 s-8.341,21.824,0,30.165l234.667,234.667c4.16,4.16,9.621,6.251,15.083,6.251c5.462,0,10.923-2.091,15.083-6.251l234.667-234.667 C514.096,145.416,514.096,131.933,505.755,123.592z' data-original='%23000000' class='active-path' fill='%23000000'/%3E%3C/g%3E%3C/g%3E%3C/g%3E%3C/svg%3E%0A");
                    background-repeat: no-repeat;
                    background-size: 14px;
                    background-position: 10px center;
                    height: 40px;
                    padding: 0 10px;
                    font-size: 15px;
                    border: 1px solid #ddd !important;
                    color: #000;
                    width: 100%;
                    border-radius: 4px !important;
                    outline: none;
                    -webkit-appearance: none;
                    -moz-appearance: none;
                    -o-appearance: none;
                    appearance: none;
                } /* select */
            } /* item */
            .reset_filters {
                width: auto;
                display: flex;
                padding: 0 10px;
                justify-content: flex-end;
                margin: 0 0 10px;
                button {
                    height: 40px;
                    width: 40px;
                    background-color: #718096;
                    border-radius: 4px;
                    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16.866' height='18.447' viewBox='0 0 16.866 18.447'%3E%3Cg transform='translate(0 0)'%3E%3Cpath d='M24.417,3.658a7.354,7.354,0,0,1,9.56-.252l-2.189.083a.509.509,0,0,0,.019,1.017h.019l3.36-.124a.508.508,0,0,0,.49-.509v-.06h0L35.552.49a.509.509,0,1,0-1.017.038l.079,2.083A8.364,8.364,0,0,0,23.735,2.9a8.367,8.367,0,0,0-2.516,8.178.506.506,0,0,0,.493.388.441.441,0,0,0,.121-.015.509.509,0,0,0,.373-.614A7.349,7.349,0,0,1,24.417,3.658Z' transform='translate(-20.982 0)' fill='%23ffffff'/%3E%3Cpath d='M91.8,185.6a.508.508,0,1,0-.987.241,7.348,7.348,0,0,1-11.832,7.387l2.215-.2a.509.509,0,1,0-.094-1.013l-3.349.3a.508.508,0,0,0-.46.554l.3,3.349a.508.508,0,0,0,.5.463.183.183,0,0,0,.045,0,.508.508,0,0,0,.46-.554l-.181-2.038a8.308,8.308,0,0,0,4.833,1.842c.143.008.286.011.426.011A8.365,8.365,0,0,0,91.8,185.6Z' transform='translate(-75.175 -178.237)' fill='%23ffffff'/%3E%3C/g%3E%3C/svg%3E");
                    background-repeat: no-repeat;
                    background-position: center center;
                    background-size: 20px;
                    -webkit-transition: all 0.2s ease-in-out;
                    -moz-transition: all 0.2s ease-in-out;
                    -o-transition: all 0.2s ease-in-out;
                    transition: all 0.2s ease-in-out;
                    &:hover {
                        background-color: #5E6D83;
                    } /* hover */
                } /* button */
            } /* reset_filters */
        } /* filter_area */
        .table-responsive {
            overflow: auto;
            width: 100%;
            padding: 0 0 15px 0;
            @media (min-width: 320px) and (max-width: 767px) {
                overflow: auto;
                width: 100%;
                padding: 0 0 15px 0;
            } /* @media */
        } /* table-responsive */
        .transactions_table {
            margin: 20px auto;
            table {
                thead {
                    tr {
                        th {
                            padding: 10px 5px;
                            line-height: normal;
                            font-weight: normal;
                            font-size: 15px;
                            border: 1px solid #5E697C;
                            vertical-align: middle;
                            text-align: center !important;
                            color: #ffffff;
                            background: #4a5568;
                            letter-spacing: 0;
                        } /* th */
                    } /* tr */
                } /* thead */
                tbody {
                    tr {
                        background: #fff;
                        td {
                            text-align: center !important;
                            padding: 6px 5px;
                            vertical-align: middle;
                            line-height: normal;
                            font-size: 15px;
                            border: 1px solid #ced4dc;
                            color: #000000;
                            font-weight: normal;
                            height: auto;
                            background: #ffffff;
                            min-width: auto;
                        } /* td */
                    } /* tr */
                } /* tbody */
            } /* table */
        } /* transactions_table */
        .general_statistics {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            margin: 0 -15px;
            @media (min-width: 320px) and (max-width: 767px) {
                display: block;
                margin: 0 auto;
            } /* @media */
            .item {
                width: 23%;
                padding: 15px;
                @media (min-width: 320px) and (max-width: 767px) {
                    width: 100%;
                    padding: 0;
                } /* @media */
                table {
                    width: 100%;
                    border: 1px solid #e2e8f0;
                    tbody {
                        tr {
                            td {
                                text-align: center;
                                padding: 10px;
                                vertical-align: middle;
                                line-height: 20px;
                                font-size: 15px;
                                border: 1px solid #ced4dc;
                                color: #000000;
                                background: #fff;
                                &:first-child {
                                    background: #4a5568;
                                    text-align: right;
                                    color: #fff;
                                    border-color: #5E697C;
                                } /* first-child */
                            } /* td */
                        } /* tr */
                    } /* tbody */
                } /* table */
            } /* item */
            .item2 {
                width: 15.5%;
                padding: 15px;
                @media (min-width: 320px) and (max-width: 767px) {
                    width: 100%;
                    padding: 0;
                } /* @media */
                table {
                    width: 100%;
                    border: 1px solid #e2e8f0;
                    tbody {
                        tr {
                            td {
                                text-align: center;
                                padding: 10px;
                                vertical-align: middle;
                                line-height: 20px;
                                font-size: 15px;
                                border: 1px solid #ced4dc;
                                color: #000000;
                                background: #fff;
                            } /* td */
                            &:first-child {
                                td {
                                    background: #4a5568;
                                    text-align: right;
                                    color: #fff;
                                    border-color: #5E697C;
                                } /* td */
                            } /* first-child */
                        } /* tr */
                    } /* tbody */
                } /* table */
            } /* item2 */
        } /* general_statistics */
    } /* content_page */
} /* revenue_tax_fee_report_page */
.pending {
  --text-opacity: 1;
  color: rgba(66, 153, 225, var(--text-opacity));
}
.checked_in {
  --text-opacity: 1;
  color: rgba(72, 187, 120, var(--text-opacity));
}
.checked_out {
  --text-opacity: 1;
  color: rgba(245, 101, 101, var(--text-opacity));
}

/* Container for the toggle switch */
.toggle-switch {
    position: relative;
    width: 100%;
    height: 40px;
    display: -webkit-box;
    display: -ms-flexbox;
    display: flex;
    gap: 5px;
    padding: 5px;
    background: #ffffff;
    border-radius: 10px;
    align-items: center;
    border: 1px solid #ddd;
}
.toggle-switch p {
    font-size: 11px;
    margin: 0;
    font-weight: 600;
    color: #808080;

}

/* Hide the default checkbox */
.toggle-switch input[type="checkbox"] {
  display: none;
}

/* Custom toggle switch track */
.toggle-switch label {
    display: block;
    width: 50px;
    height: 25px;
    background: #a6a6a6;
    border-radius: 7px;
    cursor: pointer;
    position: relative;
    -webkit-transition: background 0.3s ease;
    transition: background 0.3s ease;
}

/* Circle inside the toggle */
.toggle-switch label::after {
    content: "";
    position: absolute;
    top: 5px;
    left: 5px;
    width: 15px;
    height: 14px;
    background: white;
    border-radius: 30%;
    -webkit-box-shadow: #00000033;
    box-shadow: #00000033;
    -webkit-transition: -webkit-transform 0.3s ease;
    transition: -webkit-transform 0.3s ease;
    transition: transform 0.3s ease;
    transition: transform 0.3s ease, -webkit-transform 0.3s ease;
}

/* Checked state */
.toggle-switch input[type="checkbox"]:checked + label {
    background: #ff9090;
}

.toggle-switch input[type="checkbox"]:checked + label::after {
  transform: translateX(25px);
}

</style>
