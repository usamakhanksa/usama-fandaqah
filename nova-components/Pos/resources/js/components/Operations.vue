<template>
    <loading-view :loading="initialLoading">

        <bread-crumb />

        <div id="services_reports_page">



            <div class="title">{{__('Operations')}}</div>
            <div class="content_page">

                <!--       filter_area -->
                <div class="filter_area">
                    <div class="item">
                        <date-time-picker-from :enable-seconds="false" :enable-time="true" :date-format="'Y-m-d H:i'" :twelve-hour-time="false" :placeholder="__('Date From')" />
                    </div><!-- item -->
                    <div class="item">
                        <date-time-picker-to :enable-seconds="false" :enable-time="true" :date-format="'Y-m-d H:i'" :twelve-hour-time="false" :placeholder="__('Date To')" />
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
                </div>

                <hr>



                <div class="action_buttons">
                    <button type="button" v-show="servicesCollection.length" class="excel_button" @click="excelExport"></button>
                    <button type="button" v-show="servicesCollection.length" class="print_button" @click="printTransactions"></button>
                </div><!-- action_buttons -->


                <div class="table_area">
                    <loading :active.sync="isLoading" :can-cancel="true" :loader="'spinner'" :color="'#7e7d7f'" :is-full-page="fullPage"></loading>
                    <div class="table_responsive">
                        <table>
                            <thead>
                            <tr>
                                <th>{{__('Contract Number')}}</th>
                                <th>{{__('The Unit')}}</th>
                                <th>{{__('Amount')}}</th>
                                <th>{{__('TTX')}}</th>
                                <th>{{__('VAT')}}</th>
                                <th>{{__('Total Sum')}}</th>
                                <th>{{__('Received From')}}</th>
                                <th>{{__('For')}}</th>
                                <th>{{__('Date Receipt')}}</th>
                                <th>{{__('Payment Method')}}</th>
                                <th>{{__('Reference Number')}}</th>
                                <th>{{__('Employee')}}</th>
                                <th>{{__('Actions')}}</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-if="servicesCollection.length" v-for="(obj,index) in servicesCollectionPaginated" :key="index">
                                <td>{{obj.reservation_number}}</td>
                                <td>{{obj.unit_number}}</td>
                                <td>{{ obj.sub_total }} {{__(currency)}}</td>
                                <td>{{ obj.ttx_total }} {{__(currency)}}</td>
                                <td>{{ obj.vat_total }} {{__(currency)}}</td>
                                <td>{{ obj.total_with_taxes }} {{__(currency)}}</td>
                                <td>{{ obj.payable_type == 'App\\Reservation' ?  obj.received_from : obj.from }}</td>
                                <td>{{ obj.for }}</td>
                                <td>{{obj.transaction_date}}</td>
                                <td>{{ __(capitalize(obj.payment_method)) }}</td>
                                <td>{{ obj.reference }}</td>
                                <td>{{ obj.employee }}</td>
                                <td>
                                    <div class="action">


                                        <button  :title="__('Print Transaction')" @click="hashTransactionId(obj.id)"  target="_blank">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="30" viewBox="0 0 22 16"><path d="M17.471,17.471v1.934a1.934,1.934,0,0,1-1.934,1.934H7.8a1.934,1.934,0,0,1-1.934-1.934V17.471H3.934A1.934,1.934,0,0,1,2,15.537v-5.8A1.94,1.94,0,0,1,3.934,7.8H5.868V3.934A1.94,1.94,0,0,1,7.8,2h7.735a1.934,1.934,0,0,1,1.934,1.934V7.8H19.4a1.934,1.934,0,0,1,1.934,1.934v5.8A1.934,1.934,0,0,1,19.4,17.471Zm0-1.934H19.4v-5.8H3.934v5.8H5.868V13.6A1.94,1.94,0,0,1,7.8,11.669h7.735A1.934,1.934,0,0,1,17.471,13.6ZM15.537,7.8V3.934H7.8V7.8ZM7.8,13.6v5.8h7.735V13.6Z" transform="translate(-2 -2)" fill="#333b45"/></svg>
                                        </button>

                                        <button  :title="__('Edit')" @click="openEditTransactionModal(obj)" v-permission="'edit service transaction from pos'">
                                              <icon type="edit" width="22" height="30" view-box="0 0 22 16" />
                                        </button>

                                        <button  :title="__('Delete')" @click="deleteHandler(obj.id)" v-permission="'delete service transaction from pos'">
                                            <icon type="delete" width="22" height="30" view-box="0 0 22 16" />
                                        </button>

                                    </div><!-- action -->
                                </td>
                            </tr>
                            <tr v-if="!servicesCollection.length">
                                <td colspan="14" class="text-center">{{__('No Service Transactions found')}}</td>
                            </tr>
                            </tbody>
                        </table>
                        <br>
                        <div v-if="servicesCollection.length">
                            <pagination
                                :page-count="servicesPagination.lastPage"
                                :page-range="3"
                                :margin-pages="2"
                                :click-handler="getServicesTransactions"
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
                                <p>{{__('Results')}}  : {{__('From')}} ( {{servicesPagination.from}} ) - {{__('To')}}  ( {{servicesPagination.to}} )</p>
                                <p>{{__('Count')}}  : {{servicesPagination.totalPages}}</p>
                            </div>
                        </div><!-- invoicesCollection -->

                        <form id="transactions_form" target="_blank" method="post"  style="display: none" action="/home/print/servicesTransactionsReportPrint">
                            <input type="hidden" :value="servicesCollectionEncoded" name="servicesCollectionEncoded">
                        </form>

                    </div><!-- table_responsive -->
                </div><!-- table_area -->
            </div><!-- content_page -->
        </div><!-- services_reports_page -->

        <deposit-transaction-modal ref="depositTransaction"  />
        <withdraw-transaction-modal ref="withdrawTransaction"  />
        <delete-confirm ref="delete" :id="target_id" />
        <edit-service-transaction ref="initEditServiceTransaction" />

        <a class="display:none;"  :href="'/home/reservation/sub-invoice-service/' + transaction_hash_id  " ref="refReservation" target="_blank"></a>
        <a class="display:none;"  :href="'/home/reservation/sub-invoice-service/' + transaction_hash_id  " ref="refTeam" target="_blank"></a>


    </loading-view>
</template>

<script>

    import DeleteConfirm from './DeleteConfirm'
    import EditServiceTransaction from './partial/EditServiceTransaction'

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
        name: "Operations",
        components : {
            Pagination ,
            Loading,
            DateTimePickerFrom,
            DateTimePickerTo,
            BreadCrumb,
            DeleteConfirm,
            EditServiceTransaction
        },
        data(){
            return {
                isLoading: true,
                fullPage: false,
                servicesCollection : [],
                collection  : null,
                servicesCollectionPaginated : {},
                servicesPagination:{},
                transaction : null,
                service : {},
                locale: null,
                dateFrom:null,
                dateTo:null,
                contractNumber : null,
                employeeId : null,
                employees : [],
                terms : {},
                termsLoading : true,
                kind: null,
                date: moment().format('YYYY-MM-DD'),
                datePicker: moment().toDate(),
                from: null,
                type: null,
                description: null,
                note: null,
                reference: null,
                received_by:null,
                employee : null,
                transaction_hash_id : null,
                target_id : null,
                transactionType : null,
                unitId : null,
                units : [],
                serviceType : null,
                servicesTypes : [],
                servicesCollectionEncoded : [],
                currency :Nova.app.currentTeam.currency,
            }
        },
        methods : {

            openEditTransactionModal(obj){
                Nova.$emit('open-edit-modal' , obj);
            },
            getServicesTransactions(dPagi=1){

                dPagi = '/nova-vendor/transactions-feature/services-report?page='+ dPagi + '&dateFrom='+ this.dateFrom + '&dateTo=' + this.dateTo
                    + '&contractNumber=' + this.contractNumber + '&unitId=' + this.unitId + '&transactionType=' + this.transactionType  +  '&employeeId=' + this.employeeId  + '&serviceType=' + this.serviceType;
                this.isLoading = true ;

                Nova.request().get(dPagi)
                    .then((res)=>{

                        this.servicesCollection = res.data.servicesTransactions;
                        this.servicesCollectionPaginated = res.data.servicesTransactionsPaginated.data;

                        this.servicesPagination = {
                            currentPage : res.data.servicesTransactionsPaginated.current_page ,
                            lastPage : res.data.servicesTransactionsPaginated.last_page ,
                            from : res.data.servicesTransactionsPaginated.from,
                            to : res.data.servicesTransactionsPaginated.to,
                            totalPages : res.data.servicesTransactionsPaginated.total,
                            pathPage : res.data.servicesTransactionsPaginated.path + '?page=',
                            firstPageUrl : res.data.servicesTransactionsPaginated.first_page_url ,
                            lastPageUrl : res.data.servicesTransactionsPaginated.last_page_url ,
                            nextPageUrl : res.data.servicesTransactionsPaginated.next_page_url ,
                            prevPageUrl : res.data.servicesTransactionsPaginated.prev_page_url ,

                        };


                        this.isLoading = false;
                        this.servicesCollectionEncoded = JSON.stringify(res.data.servicesTransactions);


                    })
                    .catch((err)=>{
                        this.$toasted.show(err , {type : 'error'}) ;
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
                this.getServicesTransactions();
            },
            excelExport(){
                this.isRevenueLoading = true ;
                Nova.request().post('/nova-vendor/transactions-feature/service-transactions-excel-report', {
                    transactions : JSON.stringify(this.servicesCollection)
                }).then(response => {

                    // Data coming from my tool controller
                    let dataToBeExported = response.data.data;
                    // Export Json Data as worksheet
                    let revenueWs = XLSX.utils.json_to_sheet(dataToBeExported);
                    // New workbook instance
                    let wb = XLSX.utils.book_new(); // make Workbook of Excel
                    // Adding worksheet to workbook
                    XLSX.utils.book_append_sheet(wb, revenueWs, this.__('Services Report')); // sheetAName is name of Worksheet

                    // Export file
                    XLSX.writeFile(wb, this.__('Services Report') + '.xlsx');
                    // fire success toast
                    this.$toasted.show(this.__('Services Report Exported Successfully'), {type: 'success'});
                    this.isRevenueLoading = false ;
                }).catch((err) => {
                    console.log(err)
                });

            },
            printTransactions(){
                $('#transactions_form').submit();
            },
            capitalize(label){
                if (typeof label !== 'string') return ''
                return label.charAt(0).toUpperCase() + label.slice(1)
            },
            hashTransactionId(transaction_id) {
                Nova.request().post('/nova-vendor/calender/hashTransactionId', {
                    transaction_id: transaction_id
                })
                    .then(response => {
                        this.transaction_hash_id = response.data;

                        Nova.request().get('/nova-vendor/financial-management/transaction?id=' + transaction_id)
                            .then((res)=>{


                                let payable_type = res.data.transaction.payable_type;
                                if(payable_type == 'App\\Reservation'){
                                    this.triggerPrintHref('reservation');
                                }else{
                                    this.triggerPrintHref('team');
                                }
                            });

                    });
            },
            triggerPrintHref(type){
                if(type == 'reservation'){
                    this.$refs.refReservation.click();
                }else{
                    this.$refs.refTeam.click();
                }
            },
            deleteHandler(id){
                this.target_id = id ;
                this.$refs.delete.$refs.deleteConfirm.open();
            },

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


                    if(val.dateFrom != null && val.dateFrom != ''){
                        this.getServicesTransactions();
                    }

                    if(val.dateTo != null && val.dateTo != ''){
                        this.getServicesTransactions();
                    }


                },
                deep: true
            },
        },
        created() {
            this.initialLoading = false;
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
            this.getServicesTransactions();

            Nova.$on('transaction-destroyed' , () => {
                this.getServicesTransactions();
            });

            Nova.$on('service-transaction-updated' , () => {
                this.getServicesTransactions();
            });

            Nova.$on('service-transaction-deleted' , () => {
                this.getServicesTransactions();
            });
        },
        beforeDestroy(){
            Nova.$off('open-edit-modal');
            Nova.$off('current-from-date');
            Nova.$off('service-transaction-updated');
            Nova.$off('service-transaction-deleted');
            Nova.$off('open-edit-modal');
        }
    }
</script>

<style lang="scss">
    #services_reports_page {
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
                                    flex-wrap: nowrap;
                                    button {
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
                                    } /* button */
                                } /* action */
                            } /* td */
                        } /* tbody */
                    } /* table */
                } /* table_responsive */
            } /* table_area */
        } /* content_page */
    } /* services_reports_page */
    .Edit_Transaction_Modal {
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
        .formgroup {
            display: block;
            margin: 0 auto 10px;
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
    } /* Edit_Transaction_Modal */
</style>
