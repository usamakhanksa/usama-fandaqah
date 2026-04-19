<template>
  <loading-view :loading="initialLoading">
      <bread-crumb />
    <div id="invoices_reports_page">
      <div class="title">{{__('Reservations Invoices Report')}}</div>
      <div class="content_page">
        <div class="filter_area">
          <div class="item">
            <date-time-picker-from :enable-seconds="false" :enable-time="false" :date-format="'Y-m-d'" :twelve-hour-time="true" :placeholder="__('Select Period From')" />
          </div><!-- item -->
          <div class="item">
            <date-time-picker-to :enable-seconds="false" :enable-time="false" :date-format="'Y-m-d'" :twelve-hour-time="true" :placeholder="__('Select Period To')" />
          </div><!-- item -->
          <div class="item">
            <input
              type="text"
              v-model="reservationNumber"
              :placeholder="__('Reservation Number')"
              @change="reservationNumberSearch"
            >
          </div><!-- item -->
          <div class="item">
            <input
              type="text"
              v-model="invoiceNumber"
              :placeholder="__('Invoice Number')"
              @change="invoiceNumberSearch"
            >
          </div><!-- item -->
          <div class="item">
            <select @change="employeeNameSearch" v-model="employeeId">
              <option value="null" selected="selected">{{__('Employee Name')}}</option>
              <template v-if="employees.length">
                <template v-for="employee in employees">
                  <option :value="employee.id">{{employee.name}}</option>
                </template>
              </template>
            </select>
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

        <hr>

        <div class="action_buttons">
          <button type="button" class="excel_button" @click="excelExport"></button>
          <button type="button" class="print_button" @click="printInvoices"></button>
        </div><!-- action_buttons -->

        <div class="table_area">
          <loading :active.sync="isLoading" :can-cancel="true" :loader="'spinner'" :color="'#7e7d7f'" :is-full-page="fullPage"></loading>
          <div class="table_responsive">
            <table>
              <thead>
                <tr>
                  <th>{{__('Invoice Number')}}</th>
                  <th>{{__('Reservation Number')}}</th>
                  <th>{{__('Customer name')}}</th>
                  <th>{{__('Invoice Amount')}}</th>
                  <th>{{__('Invoice Creation Date')}}</th>
                  <th>{{__('Period From')}}</th>
                  <th>{{__('Period To')}}</th>
                  <th>{{__('Employee Name')}}</th>
                  <th>{{__('Actions')}}</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="invoicesCollection.length" v-for="(invoice,index) in invoicesCollectionPaginated" :key="index">
                  <td>{{invoice.number}}</td>
                  <td><router-link :to="'/reservation/' + invoice.reservation_id">#{{invoice.reservation.number}}</router-link></td>
                  <td>{{invoice.reservation.customer.name}}</td>
                  <td>{{ invoice.data ? (invoice.data.amount).toFixed(2) : 0.00 }} {{__(currency)}}</td>
                  <td>{{invoice.created_at | formatDateWithTime}}</td>
                  <td>{{invoice.from | formatDateWithoutTime}}</td>
                  <td>{{invoice.to | formatDateWithoutTime}}</td>
                  <td>{{ invoice.creator ?  invoice.creator.name : '-'}}</td>
                  <td>
                    <div class="action">
                      <a :href="invoice.print_url" target="_blank"  :title="__('Print Invoice')" >
                        <svg xmlns="http://www.w3.org/2000/svg" width="19.339" height="30" viewBox="0 0 22 16"><path d="M17.471,17.471v1.934a1.934,1.934,0,0,1-1.934,1.934H7.8a1.934,1.934,0,0,1-1.934-1.934V17.471H3.934A1.934,1.934,0,0,1,2,15.537v-5.8A1.94,1.94,0,0,1,3.934,7.8H5.868V3.934A1.94,1.94,0,0,1,7.8,2h7.735a1.934,1.934,0,0,1,1.934,1.934V7.8H19.4a1.934,1.934,0,0,1,1.934,1.934v5.8A1.934,1.934,0,0,1,19.4,17.471Zm0-1.934H19.4v-5.8H3.934v5.8H5.868V13.6A1.94,1.94,0,0,1,7.8,11.669h7.735A1.934,1.934,0,0,1,17.471,13.6ZM15.537,7.8V3.934H7.8V7.8ZM7.8,13.6v5.8h7.735V13.6Z" transform="translate(-2 -2)" fill="#333b45"/></svg>
                      </a>
                      <a :href="invoice.pdf_url" target="_blank"  :title="__('Export Pdf')" >
                        <svg xmlns="http://www.w3.org/2000/svg" width="19.339" height="30" viewBox="0 0 22 16"><g transform="translate(-44.806)"><path d="M119.662,298.026c0-.385-.267-.614-.738-.614a1.635,1.635,0,0,0-.391.037v1.234a1.481,1.481,0,0,0,.317.024C119.352,298.708,119.662,298.454,119.662,298.026Z" transform="translate(-70.184 -283.12)" fill="#333b45"/><path d="M194.232,297.681a1.948,1.948,0,0,0-.428.037v2.734a1.712,1.712,0,0,0,.329.019,1.3,1.3,0,0,0,1.414-1.463A1.2,1.2,0,0,0,194.232,297.681Z" transform="translate(-141.838 -283.376)" fill="#333b45"/><path d="M57.812,0H48.506a2.469,2.469,0,0,0-2.466,2.466v9.118H45.8a.994.994,0,0,0-.994.994V18.6a.993.993,0,0,0,.994.994h.241v1.1a2.469,2.469,0,0,0,2.466,2.466H61.2A2.469,2.469,0,0,0,63.668,20.7V5.836Zm-10.4,13.665a7.789,7.789,0,0,1,1.277-.086,2,2,0,0,1,1.277.335,1.222,1.222,0,0,1,.447.967,1.336,1.336,0,0,1-.384.992,1.938,1.938,0,0,1-1.358.44,2.447,2.447,0,0,1-.323-.018v1.494h-.936ZM61.2,21.658h-12.7a.957.957,0,0,1-.955-.956V19.6H59.387a.993.993,0,0,0,.994-.994V12.578a.994.994,0,0,0-.994-.994H47.551V2.466a.956.956,0,0,1,.955-.954L57.247,1.5V4.733a1.711,1.711,0,0,0,1.71,1.71l3.165-.009L62.158,20.7A.956.956,0,0,1,61.2,21.658ZM51.017,17.77v-4.1a8.511,8.511,0,0,1,1.277-.086,2.691,2.691,0,0,1,1.712.446,1.849,1.849,0,0,1,.707,1.575,2.118,2.118,0,0,1-.695,1.693,3,3,0,0,1-1.928.539A8.181,8.181,0,0,1,51.017,17.77Zm6.767-2.43v.769h-1.5v1.681h-.949V13.61H57.89v.775H56.284v.955Z" fill="#333b45"/></g></svg>
                      </a>
                      <button v-if="!invoice.reservation.checked_out" @click="openDeleteInvoiceModal(invoice)" :title="__('Delete')">
                        <icon type="delete" width="22" height="30" view-box="0 0 22 16" />
                      </button>
                    </div><!-- action -->
                  </td>
                </tr>
                <tr v-if="!invoicesCollection.length">
                  <td colspan="9" class="text-center">{{__('No invoices found')}}</td>
                </tr>
              </tbody>
            </table>
            <div v-if="invoicesCollection.length">
              <pagination
                :page-count="invoicesPagination.lastPage"
                :page-range="3"
                :margin-pages="2"
                :click-handler="getReservationsInvoices"
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
                <p>{{__('Results')}}  : {{__('From')}} ( {{invoicesPagination.from}} ) - {{__('To')}}  ( {{invoicesPagination.to}} )</p>
                <p>{{__('Invoices Count')}}  : {{invoicesPagination.totalPages}}</p>
              </div>
            </div><!-- invoicesCollection -->
            <form id="invoices_form" target="_blank" method="post"  style="display: none" action="/home/print/invoicesReportPrint">
                <input type="hidden" :value="JSON.stringify(this.collection)" name="invoicesData">
            </form>
          </div><!-- table_responsive -->
        </div><!-- table_area -->
      </div><!-- content_page -->
    </div><!-- invoices_reports_page -->
    <sweet-modal :enable-mobile-fullscreen="false" :pulse-on-block="true" :title="__('Delete Invoice')" overlay-theme="dark" ref="deleteInvoiceModal">
      <div class="relative mx-auto justify-center z-20">
        <loading :active.sync="isLoading" :is-full-page="false"></loading>
        <loading :active.sync="canDeleteCheckLoader" :is-full-page="false"></loading>
        <template v-if="canDelete">
          <div class="p-8">
            <p class="text-lg text-black leading-normal">{{__('Are you sure to delete this invoice ?')}}</p>
          </div>
          <div class="bg-30 px-6 py-3 flex -mx-2 -mb-2">
            <div class="ml-auto">
              <button type="button" @click="stepBack()"  class="btn text-80 font-normal h-9 px-3 mr-3 btn-link"> {{__('Back')}}</button>
              <button id="confirm-delete-button"  @click="deleteInvoice(invoice.id)"   class="btn btn-default btn-danger">{{__('delete')}}</button>
            </div>
          </div>
        </template>
        <template v-else>
          <div class="p-8">
            <p class="text-lg text-black leading-normal">{{__('Sorry , a newest invoice already found and must be deleted first to proceed')}}</p>
          </div>
        </template>
      </div>
    </sweet-modal>
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

    flatpickr.localize(Arabic);

    import BreadCrumb from "./BreadCrumb";

    export default {
        name: "InvoicesReport",
        components : {
            Pagination ,
            Loading,
            DateTimePickerFrom,
            DateTimePickerTo,
            BreadCrumb,
        },
        data(){
            return {
                isLoading: true,
                fullPage: false,
                currency :Nova.app.currentTeam.currency,
                invoicesCollection : [],
                collection  : null,
                invoicesCollectionPaginated : {},
                invoicesPagination:{},
                invoice : {},
                locale: null,
                dateFrom:null,
                dateTo:null,
                canDelete : true,
                canDeleteCheckLoader : true,
                invoiceNumber : null,
                reservationNumber : null,
                employeeId : null,
                employees : []

            }
        },
        methods : {
            getReservationsInvoices(dPagi=1){

                dPagi = '/nova-vendor/transactions-feature/reservationsInvoices?page='+ dPagi + '&dateFrom='+ this.dateFrom + '&dateTo=' + this.dateTo + '&invoiceNumber=' + this.invoiceNumber + '&reservationNumber=' + this.reservationNumber + "&employeeId=" + this.employeeId ;
                this.isLoading = true ;

                Nova.request().get(dPagi)
                    .then((res)=>{



                        // invoices data
                        this.invoicesCollection = res.data.invoices;
                        this.collection =  res.data.invoices;
                        // invoices data paginated
                        this.invoicesCollectionPaginated = res.data.invoicesPaginated.data;

                        // invoices pagination
                        this.invoicesPagination = {
                            currentPage : res.data.invoicesPaginated.current_page ,
                            lastPage : res.data.invoicesPaginated.last_page ,
                            from : res.data.invoicesPaginated.from,
                            to : res.data.invoicesPaginated.to,
                            totalPages : res.data.invoicesPaginated.total,
                            pathPage : res.data.invoicesPaginated.path + '?page=',
                            firstPageUrl : res.data.invoicesPaginated.first_page_url ,
                            lastPageUrl : res.data.invoicesPaginated.last_page_url ,
                            nextPageUrl : res.data.invoicesPaginated.next_page_url ,
                            prevPageUrl : res.data.invoicesPaginated.prev_page_url ,

                        };


                        this.isLoading = false;


                    })
                    .catch((err)=>{
                        this.$toasted.show(err , {type : 'error'}) ;
                    })

            },
            openDeleteInvoiceModal(invoice){
                this.invoice = invoice;
                this.checkCanDeleteInvoice(invoice);
                this.$refs.deleteInvoiceModal.open();
            },
            checkCanDeleteInvoice(invoice){
                this.canDeleteCheckLoader = true;
                axios.get('/nova-vendor/calender/invoices/checkPreviousInvoices?invoice_id=' + invoice.id + '&reservation_id=' + invoice.reservation_id)
                    .then((res) => {

                        if(res.data){
                            this.canDelete = true;
                        }else{
                            this.canDelete = false;
                        }

                        this.canDeleteCheckLoader = false;


                    })
                    .catch((err) => {
                        console.log(err);
                    })
            },
            deleteInvoice(id){
                this.isLoading = true;
                axios.post('/nova-vendor/calender/reservation/deleteInvoice/' + id)
                    .then((res) => {

                        this.$refs.deleteInvoiceModal.close();

                        // In case delete from invoice list || delete from invoice popup
                       this.$refs.deleteInvoiceModal.close();

                        this.$toasted.show(this.__('Invoice has been deleted successfully'), {
                            duration : 2000,
                            type: 'success',
                            position: "top-center",
                        });


                        this.isLoading = true;
                        this.getReservationsInvoices();
                    })
                    .catch((err) => {
                        console.log(err);
                    })
            },
            stepBack(){
                this.$refs.deleteInvoiceModal.close();
            },
            resetFilters(){
                Nova.$emit('reset-dates') ;
                this.isGeneralLoading = true ;
                this.dateFrom = null;
                this.dateTo = null;
                this.reservationNumber = null;
                this.invoiceNumber = null;
                this.employeeId = null;
                this.getReservationsInvoices();
            },
            excelExport(){
                this.isRevenueLoading = true ;
                Nova.request().post('/nova-vendor/transactions-feature/invoicesExcelReport', {
                    invoices : JSON.stringify(this.collection)
                }).then(response => {

                    // Data coming from my tool controller
                    let dataToBeExported = response.data.data;
                    // Export Json Data as worksheet
                    let revenueWs = XLSX.utils.json_to_sheet(dataToBeExported);
                    // New workbook instance
                    let wb = XLSX.utils.book_new(); // make Workbook of Excel
                    // Adding worksheet to workbook
                    XLSX.utils.book_append_sheet(wb, revenueWs, this.__('Reservations Invoices Report')); // sheetAName is name of Worksheet

                    // Export file
                    XLSX.writeFile(wb, this.__('Reservations Invoices Report') + '.xlsx');
                    // fire success toast
                    this.$toasted.show(this.__('Reservations Invoices Report Exported Successfully'), {type: 'success'});
                    this.isRevenueLoading = false ;
                }).catch((err) => {
                    console.log(err)
                });

            },
            printInvoices(){
                $('#invoices_form').submit();
            },
            reservationNumberSearch(){
                this.getReservationsInvoices();
            },
            invoiceNumberSearch(){
                this.getReservationsInvoices();
            },
            employeeNameSearch(){

                this.getReservationsInvoices();
            },
            fetchEmployees(){
                axios.get('/nova-vendor/transactions-feature/fetchEmployees')
                    .then((res) => {
                        this.employees = res.data;
                    })
                    .catch((err) => {
                        console.log(err);
                    })
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
        watch : {
            dateValues: {
                handler: function(val) {

                    // if((val.dateFrom != null && val.dateFrom != "" ) && (val.dateTo != null && val.dateTo != "" )){
                    //     // doing some checks
                    //     let dtFrom = new Date(val.dateFrom);
                    //     let dtTo = new Date(val.dateTo);
                    //
                    //     if(dtFrom > dtTo || dtTo < dtFrom){
                    //         this.$toasted.show(this.__('Please Make Sure to Supply Valid Dates'), {type: 'error'});
                    //         return false ;
                    //     }
                    //
                    //
                    // }


                    if(val.dateFrom != null && val.dateFrom != ''){
                        this.getReservationsInvoices();
                    }

                    if(val.dateTo != null && val.dateTo != ''){
                        this.getReservationsInvoices();
                    }


                },
                deep: true
            },
            reservationNumber : function(val){
                if(val === ''){
                    this.reservationNumber = null ;
                }
            },
            invoiceNumber : function(val){
                if(val === ''){
                    this.invoiceNumber = null ;
                }
            },
            employeeId : function(val){
                if(val === ''){
                    this.employeeId = null ;
                }
            }
        },
        created() {
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
        mounted() {
            this.getReservationsInvoices();
            this.fetchEmployees();
        }
    }
</script>

<style lang="scss">
  #invoices_reports_page {
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
          width: 100%;
          display: flex;
          padding: 0 10px;
          margin: 0 0 10px;
          justify-content: flex-end;
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
      hr {
        margin: 20px auto;
        border-color: #ddd;
        &:last-of-type {
          margin: 0 0 20px;
        } /* last-of-type */
      } /* hr */
      .action_buttons {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        margin: 0 auto 10px;
        button {
          display: block;
          height: 30px;
          width: 30px;
          margin: 0 10px 0 0;
          outline: none;
          background-position: center center;
          background-size: 25px;
          background-repeat: no-repeat;
          &.excel_button {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='23.308' height='23.308' viewBox='0 0 23.308 23.308'%3E%3Cpath d='M24.213,3H16V5.675h2.717V7.5H16V9.275h2.689v1.793H16v1.793h2.689v1.793H16v1.793h2.689V18.24H16v2.689h8.213a.768.768,0,0,0,.751-.78V3.78A.768.768,0,0,0,24.213,3ZM23.172,18.24H19.586V16.447h3.586Zm0-3.586H19.586V12.861h3.586Zm0-3.586H19.586V9.275h3.586Zm0-3.586H19.586V5.689h3.586Z' transform='translate(-1.657 -0.311)' fill='%23333b45'/%3E%3Cpath d='M0,2.59V20.719l13.447,2.589V0ZM8.505,16.208,6.941,13.25a2.623,2.623,0,0,1-.184-.608H6.733a4.6,4.6,0,0,1-.21.634l-1.57,2.931H2.516l2.894-4.54L2.763,7.128H5.251l1.3,2.723a4.756,4.756,0,0,1,.273.766h.025q.077-.266.285-.792l1.443-2.7h2.279l-2.723,4.5,2.8,4.578Z' fill='%23333b45'/%3E%3C/svg%3E");
          } /* excel_button */
          &.print_button {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='19.339' height='19.339' viewBox='0 0 19.339 19.339'%3E%3Cpath d='M17.471,17.471v1.934a1.934,1.934,0,0,1-1.934,1.934H7.8a1.934,1.934,0,0,1-1.934-1.934V17.471H3.934A1.934,1.934,0,0,1,2,15.537v-5.8A1.94,1.94,0,0,1,3.934,7.8H5.868V3.934A1.94,1.94,0,0,1,7.8,2h7.735a1.934,1.934,0,0,1,1.934,1.934V7.8H19.4a1.934,1.934,0,0,1,1.934,1.934v5.8A1.934,1.934,0,0,1,19.4,17.471Zm0-1.934H19.4v-5.8H3.934v5.8H5.868V13.6A1.94,1.94,0,0,1,7.8,11.669h7.735A1.934,1.934,0,0,1,17.471,13.6ZM15.537,7.8V3.934H7.8V7.8ZM7.8,13.6v5.8h7.735V13.6Z' transform='translate(-2 -2)' fill='%23333b45'/%3E%3C/svg%3E");
          } /* print_button */
        } /* button */
      } /* action_buttons */
      .table_area {
        position: relative;
        .table_responsive {
          @media (min-width: 320px) and (max-width: 767px) {
            overflow: auto;
          } /* media */
          table {
            width: 100%;
            @media (min-width: 320px) and (max-width: 767px) {
              margin: 0 auto 15px;
            } /* media */
            thead {
              th {
                background: #4a5568;
                border: 1px solid #5E697C;
                font-size: 15px;
                padding: 10px;
                vertical-align: middle;
                color: #fff;
                font-weight: normal;
                text-align: center !important;
              } /* th */
            } /* thead */
            tbody {
              td {
                background: #fafafa;
                border: 1px solid #d3d3d3;
                color: #000;
                vertical-align: middle;
                padding: 10px;
                font-size: 15px;
                line-height: 20px;
                text-align: center !important;
                height: auto;
                .text-left {
                  text-align: inherit !important;
                } /* text-left */
                label#customer-label {
                  display: inline-block;
                  font-size: 14px;
                  border-radius: 4px;
                  padding: 3px 10px;
                  min-width: 60px;
                } /* customer-label */
                a {
                  color: #4099de;
                  font-weight: bold;
                  cursor: pointer;
                  &:hover {
                    color: #0071C9;
                  } /* hover */
                } /* a */
                .action {
                  display: flex;
                  align-items: center;
                  justify-content: center;
                  flex-wrap: wrap;
                  a, button {
                    margin: 5px !important;
                    color: #b3b9bf;
                    svg {
                      display: block;
                      path {
                        fill: #b3b9bf;
                        &:hover {
                          fill: #4099de;
                        } /* hover */
                      } /*  path*/
                    } /* svg */
                    &:hover {
                      color: #4099de;
                    } /* hover */
                  } /* a */
                } /* action */
              } /* td */
            } /* tbody */
          } /* table */
        } /* table_responsive */
      } /* table_area */
    } /* content_page */
  } /* invoices_reports_page */
</style>
