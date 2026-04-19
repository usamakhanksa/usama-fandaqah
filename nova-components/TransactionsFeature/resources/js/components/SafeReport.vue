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
            <div id="safe_movement_report_page">
                <div class="title">{{__('The Safe Movement Report')}}</div>
                <div class="content_page">
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
                            <select v-model="paymentType">
                                <option value="" selected>{{__('Select Payment Type')}}</option>
                                <option value="cash">{{__('Cash')}}</option>
                                <option value="bank-transfer">{{__('Bank Transfer')}}</option>
                                <option value="mada">{{__('Mada')}}</option>
                                <option value="credit">{{__('Credit Card')}}</option>
                                <option value="credit-payment">{{__('Credit Payment')}}</option>
                            </select>
                        </div>

                        <div class="item" v-show="employees.length">
                            <select v-model="employeeId">
                                <option value="" selected>{{__('Employee')}}</option>
                                <option v-for="(employee,i) in employees" :value="employee.id" :key="i">{{ employee.name }}</option>
                            </select>
                        </div>

                 

                    </div><!-- filter_area -->

                    <div class="filter_area_actions">

                            <button
                                    class="btn_reset"
                                    @click="resetFilters()"
                                    :disabled="resetDisabled"
                                    v-tooltip="{
                                    targetClasses: ['it-has-a-tooltip'],
                                    placement: 'top',
                                    content: __('Reset Filters'),
                                    classes: ['tooltip_reset']
                                }"
                                >
                            </button>
                                <button
                                    class="search_button"
                                    @click="getData"
                                    :disabled="resetDisabled"
                                    v-tooltip="{
                                    targetClasses: ['it-has-a-tooltip'],
                                    placement: 'top',
                                    content: __('Apply Search Factors'),
                                    classes: ['tooltip_reset']
                                }"
                                >
                                {{ __('Search') }}
                                </button>
                            </div>

                    <hr>

                    <div class="statistics_area">

                        <ul>
                            <li>
                                <span>{{__('Total Deposit Transactions')}}</span>
                                <p class="d-flex">{{ Math.abs(depositWithoutFormat).toFixed(2) }} <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else> &nbsp; {{ __(currency) }}</span></p>
                            </li>
                            <li>
                                <span>{{__('Total Withdraw Transactions')}}</span>
                                <p class="d-flex">{{Math.abs(withdrawWithoutFormat).toFixed(2)}} <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else> &nbsp; {{ __(currency) }}</span></p>
                            </li>
                            <li>
                                <span>{{__('Safe Balance')}}</span>
                                <p class="d-flex">{{ (depositWithoutFormat - withdrawWithoutFormat).toFixed(2) }} <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else> &nbsp; {{ __(currency) }}</span></p>
                            </li>

                            <li>
                                <span>{{__('Management Balance')}}</span>
                                <p class="d-flex">{{ ( Math.abs(toManagement) - Math.abs(fromManagement)).toFixed(2) }} <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else> &nbsp; {{ __(currency) }}</span></p>
                            </li>

                        </ul>
                    </div><!-- statistics_area -->

                    <hr>

                    <div class="action_buttons">
                        <button type="button"
                                class="print_button"
                                @click="printStatisticsOnly"
                                v-tooltip="{
                                    targetClasses: ['it-has-a-tooltip'],
                                    placement: 'top',
                                    content: __('Print Statistics Only'),
                                    classes: ['tooltip_reset']
                                }"
                        ></button>

                        <button type="button"
                                class="print_button"
                                @click="printReport('safe_deposit_report_all')"
                                v-tooltip="{
                                    targetClasses: ['it-has-a-tooltip'],
                                    placement: 'top',
                                    content: __('Print The Whole Report'),
                                    classes: ['tooltip_reset']
                                }"
                        ></button>

                    </div><!-- action_buttons -->
                    <div class="bonds_statistics">

                        <div class="col">

                            <div class="col_title"><span class="deposit_icon">{{__('Deposit Statistics')}}</span></div>
                            <div class="col_content relative">
                                <loading :active.sync="depositStatisticsLoading" :can-cancel="true" :loader="'bars'" :color="'#007bff'" :is-full-page="fullPage"></loading>

                                <ul>
                                    <li>{{__('Total Cash')}} <p class="deposit_price">{{depositStatistics.cash}}</p> <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></li>
                                    <li>{{__('Total Bank Transfer')}} <p class="deposit_price">{{depositStatistics.bank_transfer}}</p> <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></li>
                                    <li>{{__('Total Mada')}} <p class="deposit_price">{{depositStatistics.mada}}</p> <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></li>
                                    <li>{{__('Total Credit Card')}} <p class="deposit_price">{{depositStatistics.credit_card}}</p> <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></li>
                                    <li>{{__('Total Funds Transferred From Management')}} <p v-if="depositStatistics" class="deposit_price">{{Math.abs(depositStatistics.total_funds_from_management).toFixed(2)}}</p><span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></li>
                                    <li>{{__('Total insurance')}} <p class="deposit_price">{{Math.abs(depositStatistics.total_insurance).toFixed(2)}}</p> <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></li>
                                </ul>
                            </div><!-- col_content -->
                        </div><!-- col -->
                        <div class="col">
                            <div class="col_title"><span class="withdraw_icon">{{__('Withdraw Statistics')}}</span></div>
                            <div class="col_content relative">
                                    <loading :active.sync="withdrawStatisticsLoading" :can-cancel="true" :loader="'bars'" :color="'#007bff'" :is-full-page="fullPage"></loading>

                                <ul>
                                    <li>{{__('Total Cash')}} <p class="withdraw_price">{{withdrawStatistics.cash}}</p> <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></li>
                                    <li>{{__('Total Bank Transfer')}} <p class="withdraw_price">{{withdrawStatistics.bank_transfer}}</p> <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></li>
                                    <li>{{__('Total Mada')}} <p class="withdraw_price">{{withdrawStatistics.mada}}</p> <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></li>
                                    <li>{{__('Total Credit Card')}} <p class="withdraw_price">{{withdrawStatistics.credit_card}}</p> <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></li>
                                    <li>{{__('Total Funds Transferred To Management')}} <p v-if="withdrawStatistics" class="withdraw_price">{{ Math.abs(withdrawStatistics.total_funds_to_management).toFixed(2)}}</p> <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></li>
                                    <li>{{__('Total insurance')}} <p class="withdraw_price">{{Math.abs(withdrawStatistics.total_retrieval_insurance).toFixed(2)}}</p> <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></li>
                                </ul>
                            </div><!-- col_content -->
                        </div><!-- col -->
                    </div><!-- bonds_statistics -->

                    <hr>

                    <div class="bonds_row">
                        <div class="col relative">
                            <loading :active.sync="isDepositLoading" :can-cancel="true" :loader="'bars'" :color="'#007bff'" :is-full-page="fullPage"></loading>
                            <div class="col_title">
                                <span>{{__('Deposit Transactions')}}</span>
                                <div class="buttons_area" v-if="depositResults.length">
                                    <button type="button" class="excel_button" @click="excelExport('deposit')"></button>
                                    <button type="button" class="print_button" @click="printReport('deposit')"></button>
                                </div><!-- buttons_area -->
                            </div><!-- col_title -->
                            <div class="col_content">
                                <div class="table-responsive">
                                    <table>
                                        <thead>
                                        <tr>
                                            <th>{{__('Transaction Number')}}</th>
                                            <th>{{__('Amount')}}</th>
                                            <th>{{__('Payment Method')}}</th>
                                            <th>{{__('For')}}</th>
                                            <th>{{__('Employee')}}</th>
                                            <th>{{__('The Date')}}</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                            <template v-if="depositResults.length">
                                                <tr v-for="(obj , index) in depositResults" :key="index">
                                                    <td>{{ obj.transaction_number }}</td>
                                                    <td>{{ Math.abs(obj.transaction_amount / (obj.wallet_decimal_places == 2 ? 100 : 1000)).toFixed(2) }}</td>
                                                    <td>{{ obj.transaction_payment_method == 'credit' ? __('Credit Card') : __(capitalizeFirstLetter(obj.transaction_payment_method)) }}</td>
                                                    <td v-if="obj.transaction_statement && obj.transaction_statement == 'Billed Online' ">{{__('Billed Online')}}</td>
                                                    <td v-else-if="obj.transaction_statement && obj.transaction_statement == 'various_services'">{{__('Various services')}}</td>
                                                    <td v-else>{{obj.transaction_statement ? obj.transaction_statement : '-'}}</td>
                                                    <td>{{obj.transaction_employee ? obj.transaction_employee : '-' }}</td>
                                                    <td>{{obj.transaction_date_receipt ? obj.transaction_date_receipt : '-'}}</td>
                                                </tr>
                                            </template>
                                            <template v-else>
                                                <td class="text-center p-5" colspan="6">{{__('No Deposit Transactions Matches Your Criteria')}}</td>
                                            </template>
                                        </tbody>
                                    </table>
                                </div><!-- table-responsive -->
                                <div class="pagination_area" v-if="depositResults.length">
                                    <pagination
                                        v-if="depositPagination.lastPage > 1"
                                        :page-count="depositPagination.lastPage"
                                        :page-range="3"
                                        :margin-pages="2"
                                        :value="depositPagination.currentPage"
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
                                        @input="getCurrentPageDeposits($event)"
                                    >
                                    </pagination>
                                </div><!-- pagination_area -->
                                <div class="Results_area" v-if="depositResults.length">
                                    <p>{{__('Results')}}  : {{__('From')}} ( {{depositPagination.fromPage}} ) - {{__('To')}}  ( {{depositPagination.toPage}} )</p>
                                    <p>{{__('Deposit Transactions Count')}}  : {{depositPagination.totalPages}}</p>
                                </div><!-- Results_area -->
                            </div><!-- col_content -->
                        </div><!-- col -->
                        <div class="col relative">
                            <loading :active.sync="isWithdrawLoading" :can-cancel="true" :loader="'bars'" :color="'#007bff'" :is-full-page="fullPage"></loading>
                            <div class="col_title">
                                <span>{{__('Withdraw Transactions')}}</span>
                                <div class="buttons_area" v-if="withdrawResults.length">
                                    <button type="button" class="excel_button" @click="excelExport('withdraw')"></button>
                                    <button type="button" class="print_button" @click="printReport('withdraw')"></button>
                                </div><!-- buttons_area -->
                            </div><!-- col_title -->
                            <div class="col_content">
                                <div class="table-responsive">
                                    <table>
                                        <thead>
                                        <tr>
                                            <th>{{__('Transaction Number')}}</th>
                                            <th>{{__('Amount')}}</th>
                                            <!-- <th>{{__('Exchange to')}}</th> -->
                                            <th>{{__('Payment Method')}}</th>
                                            <th>{{__('For')}}</th>
                                            <th>{{__('Employee')}}</th>
                                            <th>{{__('The Date')}}</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <template v-if="withdrawResults.length">
                                             <tr v-for="(obj , index) in withdrawResults" :key="index">
                                                    <td>{{ obj.transaction_number }}</td>
                                                    <td>{{ Math.abs(obj.transaction_amount / (obj.wallet_decimal_places == 2 ? 100 : 1000)).toFixed(2) }}</td>
                                                    <td>{{ obj.transaction_payment_method == 'credit' ? __('Credit Card') : __(capitalizeFirstLetter(obj.transaction_payment_method)) }}</td>
                                                    <td v-if="obj.transaction_statement && obj.transaction_statement == 'Billed Online' ">{{__('Billed Online')}}</td>
                                                    <td v-else-if="obj.transaction_statement && obj.transaction_statement == 'various_services'">{{__('Various services')}}</td>
                                                    <td v-else>{{obj.transaction_statement ? obj.transaction_statement : '-'}}</td>
                                                    <td>{{obj.transaction_employee ? obj.transaction_employee : '-' }}</td>
                                                    <td>{{obj.transaction_date_receipt ? obj.transaction_date_receipt : '-'}}</td>
                                            </tr>
                                        </template>
                                        <template v-else>
                                            <td class="text-center p-5" colspan="6">{{__('No Withdraw Transactions Matches Your Criteria')}}</td>
                                        </template>

                                        </tbody>
                                    </table>
                                </div><!-- table-responsive -->
                                <div class="pagination_area" v-if="withdrawResults.length">
                                    <pagination
                                        v-if="withdrawPagination.lastPage > 1"
                                        :page-count="withdrawPagination.lastPage"
                                        :page-range="3"
                                        :margin-pages="2"
                                        :value="withdrawPagination.currentPage"
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
                                        @input="getCurrentPageWithdraws($event)"
                                    >
                                    </pagination>
                                </div><!-- pagination_area -->
                                <div class="Results_area" v-if="withdrawResults.length">
                                    <p>{{__('Results')}}  : {{__('From')}} ( {{withdrawPagination.fromPage}} ) - {{__('To')}}  ( {{withdrawPagination.toPage}} )</p>
                                    <p>{{__('Withdraw Transactions Count')}}  : {{withdrawPagination.totalPages}}</p>
                                </div><!-- Results_area -->
                            </div><!-- col_content -->
                        </div><!-- col -->
                    </div><!-- bonds_row -->

                </div><!--content_page  -->
            </div><!-- safe_movement_report_page -->

            <form id="safe_withdraw_report" target="_blank" method="post"  style="display: none" action="/home/print/safeMovementReport">
                <input type="hidden" name="type" value="safe_withdraw_report">
                <input type="hidden" name="query" :value="JSON.stringify(withdrawQuery)">
                <input type="hidden" name="team_id" :value="team_id">
                <input type="hidden" name="statistics" :value="JSON.stringify(withdrawStatistics)">
            </form>


            <form id="safe_deposit_report" target="_blank" method="post"  style="display: none" action="/home/print/safeMovementReport">
                <input type="hidden" name="type" value="safe_deposit_report">
                <input type="hidden" name="query" :value="JSON.stringify(depositQuery)">
                <input type="hidden" name="team_id" :value="team_id">
                <input type="hidden" name="statistics" :value="JSON.stringify(depositStatistics)">
            </form>

            <form id="safe_deposit_report_all" target="_blank" method="post"  style="display: none" action="/home/print/safeMovementReportAll">
                <input type="hidden" name="type" value="safe_deposit_report_all">
                <input type="hidden" name="depositStatistics" :value="JSON.stringify(depositStatistics)">
                <input type="hidden" name="withdrawStatistics" :value="JSON.stringify(withdrawStatistics)">
                <input type="hidden" :value="JSON.stringify(generalStatistics)" name="generalStatistics">
                 <input type="hidden" name="withdrawQuery" :value="JSON.stringify(withdrawQuery)">
                 <input type="hidden" name="depositQuery" :value="JSON.stringify(depositQuery)">
                 <input type="hidden" name="team_id" :value="team_id">
            </form>

            <form id="report_statistics" target="_blank" method="post"  style="display: none" action="/home/print/safeMovementReportStatisticsOnly">
                <input type="hidden" name="depositStatistics" :value="JSON.stringify(depositStatistics)">
                <input type="hidden" name="withdrawStatistics" :value="JSON.stringify(withdrawStatistics)">
                <input type="hidden" :value="JSON.stringify(generalStatistics)" name="generalStatistics">
            </form>
        </div>

</template>

<script>

    import Pagination from './Pagination';
    import Loading from 'vue-loading-overlay';

    import flatpickr from 'flatpickr'

    import {Arabic} from "flatpickr/dist/l10n/ar.js"
    import 'vue-loading-overlay/dist/vue-loading.css';

    import XLSX from 'xlsx';
    flatpickr.localize(Arabic);

    export default {
        name: "safe-movement-report",
        components:{
            Pagination ,
            Loading,
        },
        data() {
            return {
                withdrawPagination:{},
                withdrawStatistics : {
                    cash : (0).toFixed(2) ,
                    bank_transfer : (0).toFixed(2) ,
                    mada : (0).toFixed(2) ,
                    credit_card : (0).toFixed(2) ,
                    total_funds_to_management : (0).toFixed(2) ,
                    total_retrieval_insurance : (0).toFixed(2) ,
                },
                withdrawResults : [],
                allWithdrawTransactions :{},
                depositPagination:{},
                depositStatistics : {
                    cash : (0).toFixed(2) ,
                    bank_transfer : (0).toFixed(2),
                    mada : (0).toFixed(2),
                    credit_card : (0).toFixed(2),
                    total_funds_from_management : (0).toFixed(2),
                    total_insurance : (0).toFixed(2),
                },
                depositResults : [],
                allDepositTransactions :{},
                isDepositLoading: false,
                isWithdrawLoading: false,
                fullPage: false,
                totalWithdrawAmount : 0 ,
                totalDepositAmount : 0 ,
                fixedTotalFundsToManagement : 0,
                changeableTotalFundsToManagement:0,
                dateFrom:null,
                dateTo:null,
                locale: Nova.config.local,
                selectedDate: {
                    start:null ,
                    end:null
                },
                isDisabledSearchBtn:true,
                isDateSelected : false ,
                isGeneralLoading : false,
                creditIsNegative : false,
                actualCredit : 0 ,
                generalStatistics : {},
                withdrawWithoutFormat : 0,
                depositWithoutFormat : 0,
                toManagement : 0 ,
                fromManagement : 0,
                team_id : Nova.config.user.current_team_id,
                depositQuery : {},
                withdrawQuery : {},
                crumbs : [],
                depositsPage : 1,
                withdrawsPage : 1,
                appendFilters : false,
                resetDisabled : true,
                withdrawStatisticsLoading : false,
                depositStatisticsLoading : false,
                time_12hrs_enabled : false,
                selectedPageDeposit : 1,
                selectedPageWithdraw : 1,
                paymentType : '',
                currency :Nova.app.currentTeam.currency,
                employees : [],
                employeeId : ''
            }
        },

        mounted() {

            this.getSearchWithTimeSetting();
            this.getEmployees();
            // this.getWithdrawTransactions();
            // this.getDepositTransactions();
            // this.getTotalsDeposit();
            // this.getTotalsWithdraw();
            this.locale = Nova.config.local ;
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
                    text : 'The Safe Movement Report',
                    to : '#'
                }
            ];

        },
        methods:{
            getCurrentPageDeposits(page){
                this.selectedPageDeposit = page;
            },
            getCurrentPageWithdraws(page){
                this.selectedPageWithdraw = page;
            },
            drawCalendars(){
                    const self = this;
                    this.flatpickrFrom = flatpickr(this.$refs.datePickerFrom, {
                        enableTime: true,
                        enableSeconds: false,
                        disableMobile: "true",
                        // onClose: this.getTransactions(),
                        dateFormat: this.dateFormat,
                        allowInput: false,
                        mode: 'single',
                        time_24hr: !self.time_12hrs_enabled,
                        onReady() {
                            self.$refs.datePickerFrom.parentNode.classList.add('date-filter')
                        },
                        onChange(){
                            self.dateFrom = self.$refs.datePickerFrom.value;
                        },
                        locale : self.locale
                    })

                    this.flatpickrTo = flatpickr(this.$refs.datePickerTo, {
                        enableTime: true,
                        enableSeconds: false,
                        disableMobile: "true",
                        // onClose: this.getTransactions(),
                        dateFormat: this.dateFormat,
                        allowInput: false,
                        mode: 'single',
                        time_24hr: !self.time_12hrs_enabled,
                        onReady() {
                            self.$refs.datePickerTo.parentNode.classList.add('date-filter')
                        },
                        onChange(){
                            self.dateTo = self.$refs.datePickerTo.value;
                        },
                        locale : self.locale
                    })

            },
            getSearchWithTimeSetting(){
                axios.get('/nova-vendor/transactions-feature/search-with-time')
                .then(res => {
                    this.time_12hrs_enabled = res.data.time_12hrs_enabled;
                    this.drawCalendars();
                })
            },

            getDepositTransactions(){

                this.isDepositLoading = true ;
                let params = {
                        'type' : 'deposit',
                        'page' : this.selectedPageDeposit
                };

                if(this.appendFilters){
                    params = {
                            'type' : 'deposit',
                            'page' : this.selectedPageDeposit,
                            'filter[by_date_from]' : this.dateFrom,
                            'filter[by_date_to]' : this.dateTo,
                            'filter[by_payment_type]' : this.paymentType,
                            'filter[by_employee_id]' : this.employeeId,
                            'exclude_rebate': true
                    };
                }

                let config = {
                    headers : {
                        'x-team' : this.team_id,
                        'x-localization' : this.locale
                    },
                    params : params
                }

                this.depositQuery = params;

                axios.get(window.FANDAQAH_API_URL + `/transactions/list` , config)
                // axios.get(url)
                    .then((res)=>{


                            if(res.data.data.length){
                                this.depositResults = res.data.data ;
                                // this.depositPagination = {
                                //     currentPage : res.data.meta.current_page ,
                                //     lastPage : res.data.meta.last_page ,
                                //     fromPage : res.data.meta.from,
                                //     toPage : res.data.meta.to,
                                //     totalPages : res.data.meta.total,
                                //     pathPage : res.data.meta.path + '?page=',
                                //     firstPageUrl : res.data.links.first_page_url ,
                                //     lastPageUrl : res.data.links.last_page_url ,
                                //     nextPageUrl : res.data.links.next_page_url ,
                                //     prevPageUrl : res.data.links.prev_page_url ,

                                // };

                            }else{

                                this.generalStatistics['dateFrom'] = this.dateFrom;
                                this.generalStatistics['dateTo'] = this.dateTo;
                                this.depositWithoutFormat = 0;
                                this.fromManagement = 0;
                                this.generalStatistics['fromManagement'] = this.fromManagement;
                                this.generalStatistics['depositWithoutFormat'] = this.depositWithoutFormat;
                                this.depositResults = [] ;
                                this.allDepositTransactions = [];
                            }


                        this.isDepositLoading = false ;
                    })
                    .catch((err)=>{
                        this.$toasted.show(err , {type : 'error'}) ;
                    })

            },
            getTotalsDeposit(){
                this.depositStatisticsLoading = true;
                let params = {
                        'type' : 'deposit',
                        'page' : this.selectedPageDeposit
                };

                if(this.appendFilters){
                    params = {
                            'type' : 'deposit',
                            'page' : this.selectedPageDeposit,
                            'filter[by_date_from]' : this.dateFrom,
                            'filter[by_date_to]' : this.dateTo,
                            'filter[by_payment_type]' : this.paymentType,
                            'filter[by_employee_id]' : this.employeeId,
                            'exclude_rebate': true
                    };
                }

                let config = {
                    headers : {
                        'x-team' : this.team_id,
                        'x-localization' : this.locale
                    },
                    params : params
                }

            this.depositQuery = params;
            axios.get(window.FANDAQAH_API_URL + `/transactions/totals` , config)

            // axios.get(url)
            .then(res => {


                   this.depositStatistics = {
                        cash : res.data.calculations.cash ,
                        bank_transfer : res.data.calculations.bank_transfer,
                        mada : res.data.calculations.mada,
                        credit_card : res.data.calculations.credit,
                        total_funds_from_management : res.data.calculations.total_funds_from_management,
                        total_insurance : res.data.calculations.total_insurance,
                    }

                    this.totalDepositAmount = res.data.calculations.total;
                    this.fromManagement = res.data.calculations.total_funds_from_management;
                    this.allDepositTransactions = res.data.transactions_ids;
                    this.isGeneralLoading = false ;
                    this.generalStatistics['dateFrom'] = this.dateFrom;
                    this.generalStatistics['dateTo'] = this.dateTo;
                    this.generalStatistics['fromManagement'] = this.fromManagement;
                    this.generalStatistics['depositWithoutFormat'] = res.data.calculations.total_without_format;
                    this.depositWithoutFormat =  res.data.calculations.total_without_format;
                    this.depositStatisticsLoading = false;


                     this.depositPagination = {
                        // currentPage : res.data.pagination.current_page ,
                        currentPage : this.depositResults.current_page ,
                        lastPage : res.data.pagination.last_page ,
                        fromPage : res.data.pagination.from,
                        toPage : res.data.pagination.to,
                        totalPages : res.data.pagination.total,
                        pathPage : res.data.pagination.path + '?page=',
                        firstPageUrl : res.data.pagination.first_page_url ,
                        lastPageUrl : res.data.pagination.last_page_url ,
                        nextPageUrl : res.data.pagination.next_page_url ,
                        prevPageUrl : res.data.pagination.prev_page_url
                    };


            })
            },
            getWithdrawTransactions(){

                this.isWithdrawLoading = true ;

                let params = {
                        'type' : 'withdraw',
                        'page' : this.selectedPageWithdraw
                };

                if(this.appendFilters){
                    params = {
                            'type' : 'withdraw',
                            'page' : this.selectedPageWithdraw,
                            'filter[by_date_from]' : this.dateFrom,
                            'filter[by_date_to]' : this.dateTo,
                            'filter[by_payment_type]' : this.paymentType,
                            'filter[by_employee_id]' : this.employeeId,
                    };
                }

                let config = {
                    headers : {
                        'x-team' : this.team_id,
                        'x-localization' : this.locale
                    },
                    params : params
                }

                this.withdrawQuery = params;
                axios.get(window.FANDAQAH_API_URL + `/transactions/list` , config)
                // axios.get(url)
                    .then((res)=>{


                        if(res.data.data.length){
                            this.withdrawResults = res.data.data ;
                            // this.withdrawPagination = {
                            //     currentPage : res.data.meta.current_page ,
                            //     lastPage : res.data.meta.last_page ,
                            //     fromPage : res.data.meta.from,
                            //     toPage : res.data.meta.to,
                            //     totalPages : res.data.meta.total,
                            //     pathPage : res.data.meta.path + '?page=',
                            //     firstPageUrl : res.data.links.first_page_url ,
                            //     lastPageUrl : res.data.links.last_page_url ,
                            //     nextPageUrl : res.data.links.next_page_url ,
                            //     prevPageUrl : res.data.links.prev_page_url ,

                            // };



                        }else{
                            this.withdrawWithoutFormat = 0;
                            this.toManagement = 0;
                            this.generalStatistics['withdrawWithoutFormat'] = this.withdrawWithoutFormat;
                            this.generalStatistics['toManagement'] = this.toManagement;
                            this.withdrawResults = [];
                            this.allWithdrawTransactions = [];
                        }
                        this.isWithdrawLoading = false ;
                    })
                    .catch((err)=>{
                        this.$toasted.show(err , {type : 'error'}) ;
                    })
            },
            getTotalsWithdraw(){
                this.withdrawStatisticsLoading = true;
                let params = {
                        'type' : 'withdraw',
                        'page' : this.selectedPageWithdraw
                };

                if(this.appendFilters){
                    params = {
                            'type' : 'withdraw',
                            'page' : this.selectedPageWithdraw,
                            'filter[by_date_from]' : this.dateFrom,
                            'filter[by_date_to]' : this.dateTo,
                            'filter[by_payment_type]' : this.paymentType,
                            'filter[by_employee_id]' : this.employeeId,
                    };
                }

                let config = {
                    headers : {
                        'x-team' : this.team_id,
                        'x-localization' : this.locale
                    },
                    params : params
                }
                this.withdrawQuery = params;
                axios.get(window.FANDAQAH_API_URL + `/transactions/totals` , config)

            // axios.get(url)
            .then(res => {


                   this.withdrawStatistics = {
                        cash : res.data.calculations.cash ,
                        bank_transfer : res.data.calculations.bank_transfer,
                        mada : res.data.calculations.mada,
                        credit_card : res.data.calculations.credit,
                        total_funds_to_management : res.data.calculations.total_funds_to_management,
                        total_retrieval_insurance : res.data.calculations.total_retrieval_insurance,
                    }

                    this.toManagement = res.data.calculations.total_funds_to_management;
                    this.allWithdrawTransactions = res.data.transactions_ids ;
                    this.totalWithdrawAmount = res.data.calculations.total ;
                    this.isGeneralLoading = false ;

                    this.generalStatistics['withdrawWithoutFormat'] = res.data.calculations.total_without_format;
                    this.generalStatistics['toManagement'] = this.toManagement;
                    this.withdrawWithoutFormat = res.data.calculations.total_without_format;
                    this.withdrawStatisticsLoading = false;


                    this.withdrawPagination = {
                        // currentPage : res.data.pagination.current_page ,
                        currentPage : this.withdrawResults.current_page ,
                        lastPage : res.data.pagination.last_page ,
                        fromPage : res.data.pagination.from,
                        toPage : res.data.pagination.to,
                        totalPages : res.data.pagination.total,
                        pathPage : res.data.pagination.path + '?page=',
                        firstPageUrl : res.data.pagination.first_page_url ,
                        lastPageUrl : res.data.pagination.last_page_url ,
                        nextPageUrl : res.data.pagination.next_page_url ,
                        prevPageUrl : res.data.pagination.prev_page_url
                    };
            })
            },
            capitalizeFirstLetter(str){
                return str.charAt(0).toUpperCase() + str.slice(1);
            },

            resetFilters(){

                this.isGeneralLoading = true ;
                this.dateFrom = null;
                this.dateTo = null;
                this.paymentType = '';
                this.employeeId = '';
                this.appendFilters = false;
                this.selectedPageDeposit = 1;
                this.selectedPageWithdraw = 1;
                // this.getDepositTransactions();
                // this.getWithdrawTransactions();
                // this.getTotalsDeposit();
                // this.getTotalsWithdraw();

                this.depositResults = [] ;
                this.allDepositTransactions = [];

                this.withdrawResults = [] ;
                this.allWithdrawTransactions = [];

                this.resetDisabled = true;

                this.depositStatistics = {
                    cash : (0).toFixed(2) ,
                    bank_transfer : (0).toFixed(2),
                    mada : (0).toFixed(2),
                    credit_card : (0).toFixed(2),
                    total_funds_from_management : (0).toFixed(2),
                    total_insurance : (0).toFixed(2),
                }

                this.withdrawStatistics = {
                    cash : (0).toFixed(2) ,
                    bank_transfer : (0).toFixed(2),
                    mada : (0).toFixed(2),
                    credit_card : (0).toFixed(2),
                    total_funds_to_management : (0).toFixed(2),
                    total_retrieval_insurance : (0).toFixed(2),
                }

                this.depositWithoutFormat = (0).toFixed(2);
                this.withdrawWithoutFormat = (0).toFixed(2);
                this.toManagement = (0).toFixed(2);
                this.fromManagement = (0).toFixed(2);

                this.generalStatistics['dateFrom'] = (0).toFixed(2);
                this.generalStatistics['dateTo'] = (0).toFixed(2);
                this.generalStatistics['fromManagement'] = (0).toFixed(2);
                this.generalStatistics['depositWithoutFormat'] = (0).toFixed(2);

                this.generalStatistics['withdrawWithoutFormat'] = (0).toFixed(2);
                this.generalStatistics['toManagement'] = (0).toFixed(2);

                this.withdrawQuery = {};
                this.depositQuery = {};

            },
            excelExport(type){
                let config = {};
                let params = {};
                switch (type) {

                    case 'deposit':
                        params = {
                                    'type' : 'deposit',
                                    'page' : this.withdrawsPage
                        };
                        if(this.appendFilters){
                            params = {
                                    'type' : 'deposit',
                                    'page' : this.withdrawsPage,
                                    'filter[by_date_from]' : this.dateFrom,
                                    'filter[by_date_to]' : this.dateTo,
                                    'filter[by_payment_type]' : this.paymentType,
                                    'filter[by_employee_id]' : this.employeeId,
                            };
                        }

                        config = {
                            headers : {
                                'x-team' : this.team_id,
                                'x-localization' : this.locale
                            },
                            params : params
                        }
                        this.isDepositLoading = true ;
                        axios.get(window.FANDAQAH_API_URL + '/transactions/excel' , config).then(response => {

                            // Data coming from my tool controller
                            let dataToBeExported = response.data.data;
                            // Export Json Data as worksheet
                            let transactionsWs = XLSX.utils.json_to_sheet(dataToBeExported);
                            // New workbook instance
                            let wb = XLSX.utils.book_new(); // make Workbook of Excel
                            // Adding worksheet to workbook
                            XLSX.utils.book_append_sheet(wb, transactionsWs, this.__('Deposit Transactions')); // sheetAName is name of Worksheet

                            // Export file
                            XLSX.writeFile(wb, this.__('Deposit Transactions') + '.xlsx');
                            // fire success toast
                            this.$toasted.show(this.__('Deposit Transactions Report was exported successfully'), {type: 'success'});
                            this.isDepositLoading = false ;
                        });
                        break;

                    case 'withdraw':
                        this.isWithdrawLoading = true ;
                         params = {
                                'type' : 'withdraw',
                                'page' : this.withdrawsPage
                        };

                    if(this.appendFilters){
                        params = {
                                    'type' : 'withdraw',
                                    'page' : this.withdrawsPage,
                                    'filter[by_date_from]' : this.dateFrom,
                                    'filter[by_date_to]' : this.dateTo,
                                    'filter[by_payment_type]' : this.paymentType
                            };
                    }

                    config = {
                        headers : {
                            'x-team' : this.team_id,
                            'x-localization' : this.locale
                        },
                        params : params
                    }
                        axios.get(window.FANDAQAH_API_URL + '/transactions/excel' , config).then(response => {

                            // Data coming from my tool controller
                            let dataToBeExported = response.data.data;
                            // Export Json Data as worksheet
                            let transactionsWs = XLSX.utils.json_to_sheet(dataToBeExported);
                            // New workbook instance
                            let wb = XLSX.utils.book_new(); // make Workbook of Excel
                            // Adding worksheet to workbook
                            XLSX.utils.book_append_sheet(wb, transactionsWs, this.__('Withdraw Transactions')); // sheetAName is name of Worksheet

                            // Export file
                            XLSX.writeFile(wb, this.__('Withdraw Transactions') + '.xlsx');
                            // fire success toast
                            this.$toasted.show(this.__('Withdraw Transactions Report was exported successfully'), {type: 'success'});
                            this.isWithdrawLoading = false ;
                        });
                        break;
                }
            },

            printReport(type){

                if(type == 'deposit'){
                    $('#safe_deposit_report').submit();
                }else if(type == 'withdraw'){
                    $('#safe_withdraw_report').submit();
                }else{
                    $('#safe_deposit_report_all').submit();
                }
            },

            printStatisticsOnly(){
                $('#report_statistics').submit();
            }
            ,
            getEmployees(){
                axios.get(window.FANDAQAH_API_URL + `/users/dropDown?team_id=${this.team_id}`)
                .then(response => {
                    this.employees = response.data.data;
                })
            },
            getData(){
                this.getDepositTransactions();
                this.getWithdrawTransactions();
                this.getTotalsDeposit();
                this.getTotalsWithdraw();
            }
        },
        computed: {
            dateValues() {
                const { dateFrom, dateTo } = this
                return {
                    dateFrom,
                    dateTo
                }
            },
            dateFormat() {
                return  'Y-m-d H:i'
            },

        },
        watch:{
            dateValues: {
                handler: function(val) {

                    if((val.dateFrom != null ) && (val.dateTo != null )){
                        this.appendFilters = true;
                        this.resetDisabled = false;
                        this.depositsPage = 1;
                        this.withdrawsPage = 1;
                        // this.getDepositTransactions();
                        // this.getWithdrawTransactions();
                        // this.getTotalsDeposit();
                        // this.getTotalsWithdraw();
                    }
                },
                deep: true
            },
            paymentType: function (val) {
                if((this.dateFrom != null) && (this.dateTo != null)){
                    this.appendFilters = true;
                        this.resetDisabled = false;
                        this.depositsPage = 1;
                        this.withdrawsPage = 1;
                        // this.getDepositTransactions();
                        // this.getWithdrawTransactions();
                        // this.getTotalsDeposit();
                        // this.getTotalsWithdraw();
                }
            },
            employeeId: function (val) {
                if((this.dateFrom != null) && (this.dateTo != null)){
                    this.appendFilters = true;
                        this.resetDisabled = false;
                        this.depositsPage = 1;
                        this.withdrawsPage = 1;
                        // this.getDepositTransactions();
                        // this.getWithdrawTransactions();
                        // this.getTotalsDeposit();
                        // this.getTotalsWithdraw();
                }
            },
            selectedPageDeposit : function(val){
                this.getDepositTransactions();
            },
            selectedPageWithdraw : function(val){
                this.getWithdrawTransactions();
            }
        },

    }
</script>

<style lang="scss">
    #safe_movement_report_page {
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
                    width: 25%;
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
                        cursor: pointer;
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
                } /* item */
                .item_button {
                    width: auto;
                    padding: 0 10px;
                    margin: 0 0 10px;
                    display: flex;
                    align-items: center;
                    &.search {
                        button {
                            background-image: url("data:image/svg+xml,%3C%3Fxml version='1.0'%3F%3E%3Csvg xmlns='http://www.w3.org/2000/svg' height='512px' version='1.1' viewBox='-1 0 136 136.21852' width='512px' class=''%3E%3Cg%3E%3Cg id='surface1'%3E%3Cpath d='M 93.148438 80.832031 C 109.5 57.742188 104.03125 25.769531 80.941406 9.421875 C 57.851562 -6.925781 25.878906 -1.460938 9.53125 21.632812 C -6.816406 44.722656 -1.351562 76.691406 21.742188 93.039062 C 38.222656 104.707031 60.011719 105.605469 77.394531 95.339844 L 115.164062 132.882812 C 119.242188 137.175781 126.027344 137.347656 130.320312 133.269531 C 134.613281 129.195312 134.785156 122.410156 130.710938 118.117188 C 130.582031 117.980469 130.457031 117.855469 130.320312 117.726562 Z M 51.308594 84.332031 C 33.0625 84.335938 18.269531 69.554688 18.257812 51.308594 C 18.253906 33.0625 33.035156 18.269531 51.285156 18.261719 C 69.507812 18.253906 84.292969 33.011719 84.328125 51.234375 C 84.359375 69.484375 69.585938 84.300781 51.332031 84.332031 C 51.324219 84.332031 51.320312 84.332031 51.308594 84.332031 Z M 51.308594 84.332031 ' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%23FFFFFF'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E%0A");
                            height: 40px;
                            background-color: #0A80D8;
                            padding: 0;
                            border-radius: 4px;
                            font-size: 0px;
                            color: #fff;
                            width: 40px;
                            outline: none;
                            background-position: center center;
                            background-repeat: no-repeat;
                            background-size: 20px;
                            -webkit-transition: all 0.2s ease-in-out;
                            -moz-transition: all 0.2s ease-in-out;
                            -o-transition: all 0.2s ease-in-out;
                            transition: all 0.2s ease-in-out;
                            &[disabled="disabled"] {
                                opacity: 0.6;
                                cursor: not-allowed;
                            } /* disabled */
                            &:hover {
                                background-color: #0071C9;
                            } /* hover */
                        } /* button */
                    } /* search */
                    
                      
                 
                } /* item_button */
                .alert {
                    display: table;
                    border: 1px solid #bee5eb;
                    background: #d1ecf1;
                    padding: 15px;
                    border-radius: 4px;
                    text-align: center;
                    margin: 0 auto 10px;
                    color: #0c5460;
                    p {
                        display: block;
                        margin: 0 auto 5px;
                        font-weight: bold;
                        font-size: 16px;
                    } /* p */
                    span {
                        display: block;
                        b {
                            display: inline-block;
                            font-weight: bold;
                            direction: ltr;
                        } /* b */
                    } /* span */
                } /* alert */
            } /* filter_area */
            hr {
                margin: 5px auto 15px;
                border-color: #ddd;
            } /* hr */
            .statistics_area {
                ul {
                    display: flex;
                    align-items: flex-start;
                    justify-content: flex-start;
                    flex-wrap: wrap;
                    margin: 0 -10px;
                    li {
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
                        span {
                            display: block;
                            font-size: 15px;
                            color: #000;
                            margin: 0 0 5px;
                        } /* span */
                        p {
                            display: block;
                            font-size: 16px;
                            font-weight: bold;
                        } /* p */
                    } /* li */
                } /* ul */
            } /* statistics_area */
            .action_buttons {
                display: flex;
                align-items: center;
                justify-content: space-between;
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
                    &.print_button {
                        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='19.339' height='19.339' viewBox='0 0 19.339 19.339'%3E%3Cpath d='M17.471,17.471v1.934a1.934,1.934,0,0,1-1.934,1.934H7.8a1.934,1.934,0,0,1-1.934-1.934V17.471H3.934A1.934,1.934,0,0,1,2,15.537v-5.8A1.94,1.94,0,0,1,3.934,7.8H5.868V3.934A1.94,1.94,0,0,1,7.8,2h7.735a1.934,1.934,0,0,1,1.934,1.934V7.8H19.4a1.934,1.934,0,0,1,1.934,1.934v5.8A1.934,1.934,0,0,1,19.4,17.471Zm0-1.934H19.4v-5.8H3.934v5.8H5.868V13.6A1.94,1.94,0,0,1,7.8,11.669h7.735A1.934,1.934,0,0,1,17.471,13.6ZM15.537,7.8V3.934H7.8V7.8ZM7.8,13.6v5.8h7.735V13.6Z' transform='translate(-2 -2)' fill='%23333b45'/%3E%3C/svg%3E");
                    } /* print_button */
                } /* button */
            } /* action_buttons */
            .bonds_statistics {
                display: flex;
                align-items: flex-start;
                flex-wrap: wrap;
                margin: 0 -10px;
                justify-content: space-between;
                .col {
                    width: 50%;
                    padding: 0 10px;
                    margin: 0 0 20px;
                    @media (min-width: 320px) and (max-width: 767px) {
                        width: 100%;
                    } /* media */
                    .col_title {
                        border-radius: 4px 4px 0 0;
                        font-size: 17px;
                        padding: 10px;
                        background: #eee;
                        color: #000;
                        border: 1px solid #ddd;
                        border-bottom: none;
                        span {
                            display: block;
                            background-position: right center;
                            background-size: 28px;
                            background-repeat: no-repeat;
                            background-color: transparent;
                            line-height: 30px;
                            padding: 0 38px 0 0;
                            [dir="ltr"] & {
                                padding: 0 0 0 38px;
                                background-position: left center;
                            } /* ltr */
                            &.deposit_icon {
                                background-image: url("data:image/svg+xml,%3Csvg height='35' viewBox='0 0 39 36' width='35' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath class='cls-1' d='M5.78,14.622a0.7,0.7,0,0,0-.7.7V30.71a0.7,0.7,0,0,0,1.4,0V15.321A0.7,0.7,0,0,0,5.78,14.622ZM36.7,12.511a0.694,0.694,0,0,0-.089-0.27L30.149,1.054A0.7,0.7,0,0,0,29.193.8L24.613,3.44,20.877,1.284l-2.425-1.4A0.7,0.7,0,0,0,17.5.141L11.079,11.248l-1,.575H5.78a3.5,3.5,0,0,0-3.5,3.5V30.71a3.5,3.5,0,0,0,3.5,3.5H34.62a3.5,3.5,0,0,0,3.5-3.5V15.321A3.494,3.494,0,0,0,36.7,12.511ZM34.41,11.227l0.347,0.6c-0.045,0-.091,0-0.137,0H34.053A2.107,2.107,0,0,1,34.41,11.227ZM29.286,2.359l0.64,1.108a2.041,2.041,0,0,1-.381.036A2.11,2.11,0,0,1,28.179,3ZM18.359,1.447l1.107,0.639A2.106,2.106,0,0,1,18.1,2.59a2.041,2.041,0,0,1-.381-0.036ZM16.994,3.809a3.489,3.489,0,0,0,1.106.18A3.517,3.517,0,0,0,20.722,2.81l2.491,1.438L13.5,9.849Zm9.93-.087A3.517,3.517,0,0,0,29.545,4.9a3.489,3.489,0,0,0,1.106-.18l3.034,5.251a3.493,3.493,0,0,0-1.091,1.852H24.545c-0.047-.1-0.1-0.192-0.152-0.286a4.45,4.45,0,0,0-3.761-2.329,3.437,3.437,0,0,0-1.716.453,3.5,3.5,0,0,0-1.624,2.162H12.883Zm-4.024,8.1H18.773a2.053,2.053,0,0,1,.843-0.95,2.01,2.01,0,0,1,1.016-.266A2.982,2.982,0,0,1,22.9,11.823ZM36.72,26.513h-4.9a2.1,2.1,0,0,1-2.1-2.1v-2.8a2.1,2.1,0,0,1,2.1-2.1h4.9v7Zm0-8.394h-1.4v-2.8a0.7,0.7,0,0,0-1.4,0v2.8h-2.1a3.5,3.5,0,0,0-3.5,3.5v2.8a3.5,3.5,0,0,0,3.5,3.5h2.1v2.8a0.7,0.7,0,0,0,1.4,0v-2.8h1.4v2.8a2.1,2.1,0,0,1-2.1,2.1H5.78a2.1,2.1,0,0,1-2.1-2.1V15.321a2.1,2.1,0,0,1,2.1-2.1H34.62a2.1,2.1,0,0,1,2.1,2.1v2.8Zm-3.5,2.8a2.1,2.1,0,1,0,2.1,2.1A2.1,2.1,0,0,0,33.22,20.917Zm0,2.8a0.7,0.7,0,1,1,.7-0.7A0.7,0.7,0,0,1,33.22,23.715Z' data-name='Forma 1' id='Forma_1'/%3E%3Cpath class='cls-1' d='M5.78,14.622a0.7,0.7,0,0,0-.7.7V30.71a0.7,0.7,0,0,0,1.4,0V15.321A0.7,0.7,0,0,0,5.78,14.622ZM36.7,12.511a0.694,0.694,0,0,0-.089-0.27L30.149,1.054A0.7,0.7,0,0,0,29.193.8L24.613,3.44,20.877,1.284l-2.425-1.4A0.7,0.7,0,0,0,17.5.141L11.079,11.248l-1,.575H5.78a3.5,3.5,0,0,0-3.5,3.5V30.71a3.5,3.5,0,0,0,3.5,3.5H34.62a3.5,3.5,0,0,0,3.5-3.5V15.321A3.494,3.494,0,0,0,36.7,12.511ZM34.41,11.227l0.347,0.6c-0.045,0-.091,0-0.137,0H34.053A2.107,2.107,0,0,1,34.41,11.227ZM29.286,2.359l0.64,1.108a2.041,2.041,0,0,1-.381.036A2.11,2.11,0,0,1,28.179,3ZM18.359,1.447l1.107,0.639A2.106,2.106,0,0,1,18.1,2.59a2.041,2.041,0,0,1-.381-0.036ZM16.994,3.809a3.489,3.489,0,0,0,1.106.18A3.517,3.517,0,0,0,20.722,2.81l2.491,1.438L13.5,9.849Zm9.93-.087A3.517,3.517,0,0,0,29.545,4.9a3.489,3.489,0,0,0,1.106-.18l3.034,5.251a3.493,3.493,0,0,0-1.091,1.852H24.545c-0.047-.1-0.1-0.192-0.152-0.286a4.45,4.45,0,0,0-3.761-2.329,3.437,3.437,0,0,0-1.716.453,3.5,3.5,0,0,0-1.624,2.162H12.883Zm-4.024,8.1H18.773a2.053,2.053,0,0,1,.843-0.95,2.01,2.01,0,0,1,1.016-.266A2.982,2.982,0,0,1,22.9,11.823ZM36.72,26.513h-4.9a2.1,2.1,0,0,1-2.1-2.1v-2.8a2.1,2.1,0,0,1,2.1-2.1h4.9v7Zm0-8.394h-1.4v-2.8a0.7,0.7,0,0,0-1.4,0v2.8h-2.1a3.5,3.5,0,0,0-3.5,3.5v2.8a3.5,3.5,0,0,0,3.5,3.5h2.1v2.8a0.7,0.7,0,0,0,1.4,0v-2.8h1.4v2.8a2.1,2.1,0,0,1-2.1,2.1H5.78a2.1,2.1,0,0,1-2.1-2.1V15.321a2.1,2.1,0,0,1,2.1-2.1H34.62a2.1,2.1,0,0,1,2.1,2.1v2.8Zm-3.5,2.8a2.1,2.1,0,1,0,2.1,2.1A2.1,2.1,0,0,0,33.22,20.917Zm0,2.8a0.7,0.7,0,1,1,.7-0.7A0.7,0.7,0,0,1,33.22,23.715Z' data-name='Forma 1 copy' id='Forma_1_copy'/%3E%3C/svg%3E");
                            } /* deposit_icon */
                            &.withdraw_icon {
                                background-image: url("data:image/svg+xml,%3Csvg height='35' viewBox='0 0 39 36' width='35' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath class='cls-1' d='M31.948,25.344l-1.953-1.985a0.577,0.577,0,0,0-.749-0.063l-8.51,6.261-3.013-1.416,2.645-.293a0.576,0.576,0,0,0,.507-0.575V24.786a0.576,0.576,0,0,0-.576-0.575H17.418l-4.851-.495a0.576,0.576,0,0,0-.288.046L8.971,25.177H2.743v1.151H9.092a0.576,0.576,0,0,0,.23-0.046L12.6,24.849l4.73,0.483h2.414V26.76l-2.132.265L14.52,26.7a0.577,0.577,0,0,0-.305,1.093L20.6,30.724a0.577,0.577,0,0,0,.576-0.058l8.377-6.145,1.152,1.2-9.627,8.4h-2.61L9.282,30.707a0.576,0.576,0,0,0-.2-0.04H2.743v1.151h6.24l9.184,3.418a0.577,0.577,0,0,0,.2.035h2.927a0.577,0.577,0,0,0,.38-0.144l10.244-8.942,0.028-.026A0.575,0.575,0,0,0,31.948,25.344ZM33.48,15.131l-3.042-3.159a0.576,0.576,0,0,0-.369-0.178l-6.222-.575H21.474l0.115-1.726,2.253-.455,3.146,0.7a0.578,0.578,0,0,0,.2,0l3.059-.363L30.1,8.226l-2.961.316-3.169-.7a0.473,0.473,0,0,0-.236,0l-2.806.575a0.576,0.576,0,0,0-.467.529l-0.184,2.8c0,0.012,0,.024,0,0.036a0.576,0.576,0,0,0,.577.574H23.8L29.753,12.9,32.818,16.1a0.576,0.576,0,0,0,.415.178h4.079V15.131H33.48Zm-1-11.98a0.512,0.512,0,0,0-.219-0.1l-9.794-2.3a0.577,0.577,0,0,0-.473.109l-6.337,4.6,0.68,0.932,6.124-4.448,9.414,2.21L36.96,8.1l0.7-.926ZM23.121,4.261a0.577,0.577,0,0,0-.473,0L19.192,5.987l0.518,1.024,3.226-1.605,2.639,1.076L26,5.412Zm-5.957,6.376-1.014-.15a0.676,0.676,0,0,1-.732-0.535,0.806,0.806,0,0,1,.945-0.547h0.415a0.861,0.861,0,0,1,.945.547v0.155h1.152V9.952a1.912,1.912,0,0,0-2.1-1.726H16.363a1.912,1.912,0,0,0-2.1,1.726A1.842,1.842,0,0,0,15.994,11.6l1,0.15a0.7,0.7,0,0,1,.732.535,0.861,0.861,0,0,1-.945.547H16.363a0.861,0.861,0,0,1-.945-0.547V12.127H14.266v0.155a1.925,1.925,0,0,0,2.1,1.7h0.415a1.939,1.939,0,0,0,2.114-1.669A1.843,1.843,0,0,0,17.164,10.637Zm3.975,1.07a4.607,4.607,0,1,1-.38-2.532L21.8,8.692a5.739,5.739,0,1,0,.49,3.171ZM15.994,7.65h1.152V8.8H15.994V7.65Z' data-name='Forma 1'/%3E%3Cpath class='cls-1' d='M31.948,25.344l-1.953-1.985a0.577,0.577,0,0,0-.749-0.063l-8.51,6.261-3.013-1.416,2.645-.293a0.576,0.576,0,0,0,.507-0.575V24.786a0.576,0.576,0,0,0-.576-0.575H17.418l-4.851-.495a0.576,0.576,0,0,0-.288.046L8.971,25.177H2.743v1.151H9.092a0.576,0.576,0,0,0,.23-0.046L12.6,24.849l4.73,0.483h2.414V26.76l-2.132.265L14.52,26.7a0.577,0.577,0,0,0-.305,1.093L20.6,30.724a0.577,0.577,0,0,0,.576-0.058l8.377-6.145,1.152,1.2-9.627,8.4h-2.61L9.282,30.707a0.576,0.576,0,0,0-.2-0.04H2.743v1.151h6.24l9.184,3.418a0.577,0.577,0,0,0,.2.035h2.927a0.577,0.577,0,0,0,.38-0.144l10.244-8.942,0.028-.026A0.575,0.575,0,0,0,31.948,25.344ZM33.48,15.131l-3.042-3.159a0.576,0.576,0,0,0-.369-0.178l-6.222-.575H21.474l0.115-1.726,2.253-.455,3.146,0.7a0.578,0.578,0,0,0,.2,0l3.059-.363L30.1,8.226l-2.961.316-3.169-.7a0.473,0.473,0,0,0-.236,0l-2.806.575a0.576,0.576,0,0,0-.467.529l-0.184,2.8c0,0.012,0,.024,0,0.036a0.576,0.576,0,0,0,.577.574H23.8L29.753,12.9,32.818,16.1a0.576,0.576,0,0,0,.415.178h4.079V15.131H33.48Zm-1-11.98a0.512,0.512,0,0,0-.219-0.1l-9.794-2.3a0.577,0.577,0,0,0-.473.109l-6.337,4.6,0.68,0.932,6.124-4.448,9.414,2.21L36.96,8.1l0.7-.926ZM23.121,4.261a0.577,0.577,0,0,0-.473,0L19.192,5.987l0.518,1.024,3.226-1.605,2.639,1.076L26,5.412Zm-5.957,6.376-1.014-.15a0.676,0.676,0,0,1-.732-0.535,0.806,0.806,0,0,1,.945-0.547h0.415a0.861,0.861,0,0,1,.945.547v0.155h1.152V9.952a1.912,1.912,0,0,0-2.1-1.726H16.363a1.912,1.912,0,0,0-2.1,1.726A1.842,1.842,0,0,0,15.994,11.6l1,0.15a0.7,0.7,0,0,1,.732.535,0.861,0.861,0,0,1-.945.547H16.363a0.861,0.861,0,0,1-.945-0.547V12.127H14.266v0.155a1.925,1.925,0,0,0,2.1,1.7h0.415a1.939,1.939,0,0,0,2.114-1.669A1.843,1.843,0,0,0,17.164,10.637Zm3.975,1.07a4.607,4.607,0,1,1-.38-2.532L21.8,8.692a5.739,5.739,0,1,0,.49,3.171ZM15.994,7.65h1.152V8.8H15.994V7.65Z' data-name='Forma 1 copy 2'/%3E%3C/svg%3E");
                            } /* withdraw_icon */
                        } /* span */
                    } /* col_title */
                    .col_content {
                        padding: 10px;
                        border: 1px solid #ddd;
                        background: #fcfcfc;
                        border-radius: 0 0 4px 4px;
                        ul {
                            display: flex;
                            align-items: flex-start;
                            justify-content: space-between;
                            margin: 0 -10px;
                            flex-wrap: wrap;
                            li {
                                width: 50%;
                                padding: 0 10px;
                                margin: 0 0 10px;
                                color: #000;
                                font-size: 15px;
                                @media (min-width: 320px) and (max-width: 767px) {
                                    width: 100%;
                                } /* media */
                                p {
                                    display: inline-block;
                                    font-weight: bold;
                                    margin: 0 3px;
                                    &.deposit_price {
                                        color: #38a169;
                                    } /* deposit_price */
                                    &.withdraw_price {
                                        color: #e53e3e;
                                    } /* withdraw_price */
                                } /* p */
                            } /* li */
                        } /* ul */
                    } /* col_content */
                } /* col */
            } /* bonds_statistics */
            .bonds_row {
                display: flex;
                align-items: flex-start;
                flex-wrap: wrap;
                margin: 0 -10px;
                justify-content: space-between;
                .col {
                    width: 50%;
                    padding: 0 10px;
                    margin: 0 0 20px;
                    @media (min-width: 320px) and (max-width: 767px) {
                        width: 100%;
                    } /* media */
                    .col_title {
                        border-radius: 4px 4px 0 0;
                        padding: 10px;
                        background: #eee;
                        border: 1px solid #ddd;
                        border-bottom: none;
                        display: flex;
                        align-items: center;
                        justify-content: space-between;
                        span {
                            display: block;
                            font-size: 17px;
                            color: #000;
                        } /* span */
                        .buttons_area {
                            display: flex;
                            align-items: center;
                            justify-content: flex-end;
                            button {
                                display: block;
                                height: 25px;
                                width: 25px;
                                margin: 0 10px 0 0;
                                outline: none;
                                background-position: center center;
                                background-size: 20px;
                                background-repeat: no-repeat;
                                &.excel_button {
                                    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='23.308' height='23.308' viewBox='0 0 23.308 23.308'%3E%3Cpath d='M24.213,3H16V5.675h2.717V7.5H16V9.275h2.689v1.793H16v1.793h2.689v1.793H16v1.793h2.689V18.24H16v2.689h8.213a.768.768,0,0,0,.751-.78V3.78A.768.768,0,0,0,24.213,3ZM23.172,18.24H19.586V16.447h3.586Zm0-3.586H19.586V12.861h3.586Zm0-3.586H19.586V9.275h3.586Zm0-3.586H19.586V5.689h3.586Z' transform='translate(-1.657 -0.311)' fill='%23333b45'/%3E%3Cpath d='M0,2.59V20.719l13.447,2.589V0ZM8.505,16.208,6.941,13.25a2.623,2.623,0,0,1-.184-.608H6.733a4.6,4.6,0,0,1-.21.634l-1.57,2.931H2.516l2.894-4.54L2.763,7.128H5.251l1.3,2.723a4.756,4.756,0,0,1,.273.766h.025q.077-.266.285-.792l1.443-2.7h2.279l-2.723,4.5,2.8,4.578Z' fill='%23333b45'/%3E%3C/svg%3E");
                                } /* excel_button */
                                &.print_button {
                                    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='19.339' height='19.339' viewBox='0 0 19.339 19.339'%3E%3Cpath d='M17.471,17.471v1.934a1.934,1.934,0,0,1-1.934,1.934H7.8a1.934,1.934,0,0,1-1.934-1.934V17.471H3.934A1.934,1.934,0,0,1,2,15.537v-5.8A1.94,1.94,0,0,1,3.934,7.8H5.868V3.934A1.94,1.94,0,0,1,7.8,2h7.735a1.934,1.934,0,0,1,1.934,1.934V7.8H19.4a1.934,1.934,0,0,1,1.934,1.934v5.8A1.934,1.934,0,0,1,19.4,17.471Zm0-1.934H19.4v-5.8H3.934v5.8H5.868V13.6A1.94,1.94,0,0,1,7.8,11.669h7.735A1.934,1.934,0,0,1,17.471,13.6ZM15.537,7.8V3.934H7.8V7.8ZM7.8,13.6v5.8h7.735V13.6Z' transform='translate(-2 -2)' fill='%23333b45'/%3E%3C/svg%3E");
                                } /* print_button */
                            } /* button */
                        } /* buttons_area */
                    } /* col_title */
                    .col_content {
                        padding: 10px;
                        border: 1px solid #ddd;
                        background: #fcfcfc;
                        border-radius: 0 0 4px 4px;
                        .table-responsive {
                            width: 100%;
                            margin: 0 auto 20px;
                            @media (min-width: 320px) and (max-width: 991px) {
                                overflow: auto;
                            } /* media */
                            table {
                                width: 100%;
                                border: 1px solid #e2e8f0;
                                display: table;
                                thead {
                                    tr {
                                        th {
                                            padding: 10px 5px;
                                            line-height: 20px;
                                            font-weight: normal;
                                            font-size: 15px;
                                            border: 1px solid #5E697C;
                                            vertical-align: middle;
                                            text-align: center;
                                            color: #ffffff;
                                            background: #4a5568;
                                        } /* th */
                                    } /* tr */
                                } /* thead */
                                tbody {
                                    tr {
                                        td {
                                            text-align: center;
                                            padding: 10px 5px;
                                            vertical-align: middle;
                                            line-height: 20px;
                                            font-size: 15px;
                                            border: 1px solid #ced4dc;
                                            color: #000000;
                                            font-weight: normal;
                                            height: 3.3rem;
                                            background: #ffffff;
                                        } /* td */
                                    } /* tr */
                                } /* tbody */
                            } /* table */
                        } /* table-responsive */
                        .pagination_area {
                            margin: 0 auto 20px;
                            ul {
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                flex-wrap: wrap;
                                border-radius: 0;
                                width: 100%;
                                li {
                                    a {
                                        @media (min-width: 320px) and (max-width: 767px) {
                                            font-size: 13px;
                                            padding: 5px;
                                        } /* media */
                                    } /* a */
                                    &:first-child {
                                        a {
                                            border-radius: 0 4px 4px 0 !important;
                                            [dir="ltr"] & {
                                                border-radius: 4px 0 0 4px !important;
                                            } /* ltr */
                                        } /* a */
                                    } /* first-child */
                                    &:last-child {
                                        a {
                                            border-radius: 4px 0 0 4px !important;
                                            [dir="ltr"] & {
                                                border-radius: 0 4px 4px 0 !important;
                                            } /* ltr */
                                        } /* a */
                                    } /* last-child */
                                } /* li */
                            } /* ul */
                        } /* pagination_area */
                        .Results_area {
                            display: flex;
                            align-items: center;
                            justify-content: space-between;
                            flex-wrap: wrap;
                            p {
                                display: block;
                                margin: 10px 0 0;
                                font-size: 15px;
                                color: #000;
                            } /* p */
                        } /* Results_area */
                    } /* col_content */
                } /* col */
            } /* bonds_row */
        } /* content_page */
    } /* safe_movement_report_page */
    .d-flex{
        display: flex !important;
    }

    .search_button{
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
    }

    .filter_area_actions{
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .btn_reset {
        height: 40px;
        width: 40px;
        background-color: #718096;
        border-radius: 4px;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16.866' height='18.447' viewBox='0 0 16.866 18.447'%3E%3Cg transform='translate(0 0)'%3E%3Cpath d='M24.417,3.658a7.354,7.354,0,0,1,9.56-.252l-2.189.083a.509.509,0,0,0,.019,1.017h.019l3.36-.124a.508.508,0,0,0,.49-.509v-.06h0L35.552.49a.509.509,0,1,0-1.017.038l.079,2.083A8.364,8.364,0,0,0,23.735,2.9a8.367,8.367,0,0,0-2.516,8.178.506.506,0,0,0,.493.388.441.441,0,0,0,.121-.015.509.509,0,0,0,.373-.614A7.349,7.349,0,0,1,24.417,3.658Z' transform='translate(-20.982 0)' fill='%23ffffff'/%3E%3Cpath d='M91.8,185.6a.508.508,0,1,0-.987.241,7.348,7.348,0,0,1-11.832,7.387l2.215-.2a.509.509,0,1,0-.094-1.013l-3.349.3a.508.508,0,0,0-.46.554l.3,3.349a.508.508,0,0,0,.5.463.183.183,0,0,0,.045,0,.508.508,0,0,0,.46-.554l-.181-2.038a8.308,8.308,0,0,0,4.833,1.842c.143.008.286.011.426.011A8.365,8.365,0,0,0,91.8,185.6Z' transform='translate(-75.175 -178.237)' fill='%23ffffff'/%3E%3C/g%3E%3C/svg%3E");
        background-position: center center;
        background-size: 20px;
        background-repeat: no-repeat;
        transition: all 0.2s ease-in-out;
        -webkit-transition: all 0.2s ease-in-out;
    }



</style>
