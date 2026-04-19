<template>
  <loading-view :loading="initialLoading">
      <bread-crumb />
    <div id="revenue_tax_fee_report_page">
      <div class="title">{{__('Total Revenues, Taxes & Fees')}}</div>
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
            <date-time-picker-from :enable-seconds="false" :enable-time="false" :date-format="'Y-m-d'" :twelve-hour-time="false" :placeholder="__('Select Date From')" />
          </div><!-- item -->
          <div class="item">
            <date-time-picker-to :enable-seconds="false" :enable-time="false" :date-format="'Y-m-d'" :twelve-hour-time="false" :placeholder="__('Select Date To')" />
          </div><!-- item -->
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
                  <tr>
                    <td>{{__('Leasing Revenue')}}</td>
                    <td>{{revenueStatistics.leasing_revenue ? revenueStatistics.leasing_revenue +  ' ' + __(currency) : '-' }}</td>
                  </tr>
                  <tr>
                    <td>{{__('Services Revenue')}}</td>
                    <td>{{revenueStatistics.services_revenue ? revenueStatistics.services_revenue +  ' ' + __(currency) : '-' }}</td>
                  </tr>
                  <tr>
                    <td>{{__('Total Revenue')}}</td>
                    <td>{{revenueStatistics.total_revenue ?  revenueStatistics.total_revenue + ' ' + __(currency) : '-' }}</td>
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
                  <tr><td>{{revenueStatistics.total_ewa ? revenueStatistics.total_ewa + ' ' + __(currency) : '-' }}</td></tr>
                </tbody>
              </table>
            </div><!-- table-responsive -->
          </div><!-- item2 -->
          <div class="item">
            <div class="table-responsive">
              <table>
                <tbody>
                  <tr>
                    <td>{{__('Vat on reservation')}}</td>
                    <td>{{revenueStatistics.vat_on_reservation ? revenueStatistics.vat_on_reservation + ' ' + __(currency) : '-' }}</td>
                  </tr>
                  <tr>
                    <td>{{__('Vat on services')}}</td>
                    <td>{{revenueStatistics.vat_on_services ?  revenueStatistics.vat_on_services + ' ' + __(currency) : '-' }}</td>
                  </tr>
                  <tr>
                    <td>{{__('Total Vat')}}</td>
                    <td>{{revenueStatistics.total_vat ? revenueStatistics.total_vat + ' ' + __(currency) : '-' }}</td>
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
                    <td>{{revenueStatistics.ttx_on_reservation ? revenueStatistics.ttx_on_reservation + ' ' + __(currency) : '-'  }}</td>
                  </tr>
                  <tr>
                    <td>{{__('Ttx on services')}}</td>
                    <td>{{revenueStatistics.ttx_on_services ? revenueStatistics.ttx_on_services + ' ' + __(currency) : '-'  }}</td>
                  </tr>
                  <tr>
                    <td>{{__('Total Ttx')}}</td>
                    <td>{{revenueStatistics.total_ttx ?  revenueStatistics.total_ttx + ' ' + __(currency) : '-' }}</td>
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
                  <tr><td>{{revenueStatistics.total_reservations ? revenueStatistics.total_reservations + ' ' + __(currency) : '-' }}</td></tr>
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
              <div class="w-full flex flex-row-reverse flex-wrap my-2 items-center" v-if="revenueResults.length">
                  <svg @click="excelExport()" class="cursor mx-4" xmlns="http://www.w3.org/2000/svg" width="23.308" height="23.308" viewBox="0 0 23.308 23.308"><path d="M24.213,3H16V5.675h2.717V7.5H16V9.275h2.689v1.793H16v1.793h2.689v1.793H16v1.793h2.689V18.24H16v2.689h8.213a.768.768,0,0,0,.751-.78V3.78A.768.768,0,0,0,24.213,3ZM23.172,18.24H19.586V16.447h3.586Zm0-3.586H19.586V12.861h3.586Zm0-3.586H19.586V9.275h3.586Zm0-3.586H19.586V5.689h3.586Z" transform="translate(-1.657 -0.311)" fill="#333b45"/><path d="M0,2.59V20.719l13.447,2.589V0ZM8.505,16.208,6.941,13.25a2.623,2.623,0,0,1-.184-.608H6.733a4.6,4.6,0,0,1-.21.634l-1.57,2.931H2.516l2.894-4.54L2.763,7.128H5.251l1.3,2.723a4.756,4.756,0,0,1,.273.766h.025q.077-.266.285-.792l1.443-2.7h2.279l-2.723,4.5,2.8,4.578Z" fill="#333b45"/></svg>
                  <svg @click="printReport()" class="cursor" xmlns="http://www.w3.org/2000/svg" width="19.339" height="19.339" viewBox="0 0 19.339 19.339"><path d="M17.471,17.471v1.934a1.934,1.934,0,0,1-1.934,1.934H7.8a1.934,1.934,0,0,1-1.934-1.934V17.471H3.934A1.934,1.934,0,0,1,2,15.537v-5.8A1.94,1.94,0,0,1,3.934,7.8H5.868V3.934A1.94,1.94,0,0,1,7.8,2h7.735a1.934,1.934,0,0,1,1.934,1.934V7.8H19.4a1.934,1.934,0,0,1,1.934,1.934v5.8A1.934,1.934,0,0,1,19.4,17.471Zm0-1.934H19.4v-5.8H3.934v5.8H5.868V13.6A1.94,1.94,0,0,1,7.8,11.669h7.735A1.934,1.934,0,0,1,17.471,13.6ZM15.537,7.8V3.934H7.8V7.8ZM7.8,13.6v5.8h7.735V13.6Z" transform="translate(-2 -2)" fill="#333b45"/></svg>
              </div>


                <div class="table-responsive">
                  <table class="table w-full">
                    <thead>
                      <tr>
                        <th colspan="6"></th>
                        <th colspan="2">{{__('Revenue')}}</th>
                        <th colspan="2"></th>
                        <th colspan="2">{{__('VAT')}}</th>
                        <th></th>
                        <template  v-if="hasTtx">
                        <th colspan="2">{{__('TTX')}}</th>
                        </template>
                        <th colspan="2"></th>
                      </tr>
                      <tr>
                        <th>{{__('Reservation Number')}}</th>
                        <th>{{__('Unit Number')}}</th>
                        <th>{{__('Customer name')}}</th>
                        <th>{{__('From Date')}}</th>
                        <th>{{__('To Date')}}</th>
                        <th>{{__('Nights Count')}}</th>
                        <!-- This part for revenue header -->
                        <th>{{__('Leasing')}}</th>
                        <th>{{ __('Services') }}</th>
                        <th>{{__('Total Revenue')}}</th>
                        <th>{{__('Ewa')}}</th>
                        <!-- This part for vat header -->
                        <th>{{__('On Leasing')}}</th>
                        <th>{{__('On Services')}}</th>
                        <th>{{__('Total VAT')}}</th>
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
                      <tr class="hover:bg-grey-lighter" v-if="revenueResults.length" v-for="(revenue , index) in revenueResults" :key="index">
                        <td class="py-2 text-center border-b border-grey-light">
                          <a class="no-underline dim text-primary font-bold view_reservation" href="" :data-attr-id="revenue.reservation_id"> {{revenue.reservation_number}} </a>
                        </td>
                        <td class="py-2 text-center border-b border-grey-light">{{revenue.unit_number}}</td>
                        <td class="py-2 text-center border-b border-grey-light">{{revenue.customer_name}}</td>
                        <td class="py-2 text-center border-b border-grey-light">{{revenue.from_date}}</td>
                        <td class="py-2 text-center border-b border-grey-light">{{revenue.to_date}}</td>
                        <td class="py-2 text-center border-b border-grey-light"> {{revenue.nights_count}} </td>
                        <td class="py-2 text-center border-b border-grey-light"> {{revenue.leasing_price}} </td>
                        <td class="py-2 text-center border-b border-grey-light"> {{revenue.services}} </td>
                        <td class="py-2 text-center border-b border-grey-light"> {{revenue.total_revenue}} </td>
                        <td class="py-2 text-center border-b border-grey-light"> {{revenue.total_ewa}} </td>
                        <td class="py-2 text-center border-b border-grey-light"> {{revenue.vat_on_reservation}} </td>
                        <td class="py-2 text-center border-b border-grey-light"> {{revenue.vat_on_services}} </td>
                        <td class="py-2 text-center border-b border-grey-light"> {{revenue.total_vat}} </td>
                        <template  v-if="hasTtx">
                          <td class="py-2 text-center border-b border-grey-light"> {{revenue.ttx_on_reservation}} </td>
                          <td class="py-2 text-center border-b border-grey-light"> {{revenue.ttx_on_services}} </td>
                          <td class="py-2 text-center border-b border-grey-light"> {{revenue.total_ttx}} </td>
                        </template>
                        <td class="py-2 text-center border-b border-grey-light"> {{revenue.total_reservation}} </td>
                      </tr>
                      <tr v-if="!revenueResults.length">
                        <td colspan="17" class="text-center">{{__('No revenue data found')}}</td>
                      </tr>
                    </tbody>
                  </table>
                </div><!-- table-responsive -->
                <div class="w-full flex flex-wrap mb-4 mt-3 justify-center" v-if="revenueResults.length">
                  <pagination
                    :page-count="revenuePagination.lastPage"
                    :page-range="3"
                    :margin-pages="2"
                    :click-handler="getRevenueTaxFeeData"
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
                  >
                  </pagination>
                  <div class="w-full flex justify-between mt-4 mb-2">
                      <p>{{__('Results')}}  : {{__('From')}} ( {{revenuePagination.from}} ) - {{__('To')}}  ( {{revenuePagination.to}} )</p>
                      <p>{{__('Reservations Count')}}  : {{revenuePagination.totalPages}}</p>
                  </div>
 </div>
              </div>

          <!-- Print -->
          <form id="revenue_tax_form" target="_blank" method="post"  style="display: none" action="/home/print/revenueTaxFeesReport">
              <input type="hidden" :value="JSON.stringify(allRevenueData)" name="allRevenueData">
              <input type="hidden" :value="JSON.stringify(revenueStatistics)" name="revenueStatistics">
              <input type="hidden" :value="JSON.stringify(hasTtx)" name="hasTtx">

          </form>

      </div><!-- transactions_table -->
      </div><!-- content_page -->
    </div><!-- revenue_tax_fee_report_page -->
  </loading-view>
</template>

<script>

    import Pagination from './Pagination';
    import Loading from 'vue-loading-overlay';
    import moment from 'moment';

    import DateTimePickerFrom from './DateTimePickerFrom';
    import DateTimePickerTo from './DateTimePickerTo';
    import flatpickr from 'flatpickr'

    import {Arabic} from "flatpickr/dist/l10n/ar.js"
    import 'vue-loading-overlay/dist/vue-loading.css';

    import XLSX from 'xlsx';
    import printJS from 'print-js';
    import Breadcrumb from './BreadCrumb';
    flatpickr.localize(Arabic);

    export default {
        name: "safe-movement-report",
        components:{
            Pagination ,
            Loading,
            DateTimePickerFrom,
            DateTimePickerTo,
            Breadcrumb
        },
        data() {
            return {
                currency :Nova.app.currentTeam.currency,

                revenuePagination:{},
                revenueStatistics : {},
                revenueResults : [],
                allRevenueData :{},
                isRevenueLoading: true,
                fullPage: false,
                dateFrom:null,
                dateTo:null,
                locale: null,
                selectedDate: {
                    start:null ,
                    end:null
                },
                isDisabledSearchBtn:true,
                isDateSelected : false ,
                isGeneralLoading : false,
                durations : [],
                showData : false,
                hasTtx : false

            }
        },
        created() {
            // just for now
            this.initialLoading = false;
            // this.getRevenueTaxFeeData();
            this.locale = Nova.config.local ;

            Nova.$on('to-change' , (val) => {
                this.dateTo = val ;
            });

            Nova.$on('from-change' , (val) => {
                this.dateFrom = val ;
                Nova.$emit('current-from-date' , this.dateFrom) ;
            });

        },
        methods:{

            getRevenueTaxFeeData(dPagi=1){

                dPagi = '/nova-vendor/transactions-feature/revenueTaxFeeReport?page='+ dPagi + '&dateFrom='+ this.dateFrom + '&dateTo=' + this.dateTo ;
                this.isRevenueLoading = true ;

                Nova.request().get(dPagi)
                    .then((res)=>{
                        if(res.data.flag === 'data_found'){
                            this.revenueResults = res.data.reservations.data;

                            this.revenuePagination = {
                                currentPage : res.data.reservations.current_page ,
                                lastPage : res.data.reservations.last_page ,
                                from : res.data.reservations.from,
                                to : res.data.reservations.to,
                                totalPages : res.data.reservations.total,
                                pathPage : res.data.reservations.path + '?page=',
                                firstPageUrl : res.data.reservations.first_page_url ,
                                lastPageUrl : res.data.reservations.last_page_url ,
                                nextPageUrl : res.data.reservations.next_page_url ,
                                prevPageUrl : res.data.reservations.prev_page_url ,

                            };
                            // Calculations
                            this.revenueStatistics = {
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

                            // all data without pagination
                            this.allRevenueData = res.data.reservations_formatted ;

                            this.hasTtx = res.data.hasAtLeastOneTourismTaxApplied ;

                            // this.totalDepositAmount = res.data.total_deposit;
                            this.isGeneralLoading = false ;

                        }else{

                            this.isGeneralLoading = false ;
                            this.revenueResults = [] ;
                            this.revenueStatistics = {
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
                        this.isRevenueLoading = false ;
                    })
                    .catch((err)=>{
                        this.$toasted.show(err , {type : 'error'}) ;
                    })

            },

            capitalizeFirstLetter(str){
                return str.charAt(0).toUpperCase() + str.slice(1);
            },

            filterTransactions(){
                this.isGeneralLoading = true ;
                this.getRevenueTaxFeeData();
            },
            resetFilters(){
                Nova.$emit('reset-dates') ;
                this.isGeneralLoading = true ;
                this.dateFrom = null;
                this.dateTo = null;
                this.isDateSelected = false ;
                this.isDisabledSearchBtn = true ;
                // this.getRevenueTaxFeeData();
            },
            excelExport(){
                this.isRevenueLoading = true ;
                Nova.request().post('/nova-vendor/transactions-feature/revenueTaxFeeReportExcel', {
                    reportData : this.allRevenueData,
                    hasTtx : this.hasTtx
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
            }

        },
        computed: {
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


                        this.getRevenueTaxFeeData();
                        this.showData = true ;
                    }else{
                        this.showData = false;
                    }
                },
                deep: true
            }
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
        width: 20%;
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
</style>
