<template>
  <div>
    <sweet-modal :enable-mobile-fullscreen="false" id="receiptReportModal"  :pulse-on-block="false" :title="__('Receipt Report')" overlay-theme="dark" ref="ReceiptReportModal" @open="clear" class="receipt_report_modal relative">

        <loading :active.sync="isLoading" :can-cancel="true" :loader="'bars'" :color="'#007bff'" :is-full-page="false"></loading>

        <div v-if="showAlert">
            <p class="alert-error">{{__('Maximum search period allowed in receipt report is 24 hours and you exceeded this limit !')}}</p>
        </div>
        <br>
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

                <div class="item" v-show="employees.length">

                    <select v-model="employeeId" @change="filterEmployees">
                        <!-- <p>{{employee.id}}</p> -->
                        <option v-for="(employee,i) in employees" :value="employee.id" :selected="employee.id == employeeId" :key="i">{{ employee.name }}</option>
                    </select>
                </div>


                <div class="item">
                    <select v-model="receiptEmployeeName">
                        <option v-for="(employee,i) in receiptEmployees" :value="employee.name" :key="i">{{ employee.name }}</option>
                    </select>
                </div>

            <div class="reset_filters">
                <button
                    @click="resetFilters()"

                    v-tooltip="{
                    targetClasses: ['it-has-a-tooltip'],
                    placement: 'top',
                    content: __('Reset Filters'),
                    classes: ['tooltip_reset']
                }"
                >
                </button>
            </div><!-- item_button -->

        </div><!-- filter_area -->

                    <hr  v-if="dateFrom && dateTo && hours <= 24 &&  receiptEmployeeName">

                     <div class="statistics_area" v-if="dateFrom && dateTo && hours <= 24 &&  receiptEmployeeName">

                        <ul>
                            <li>
                                <span>{{__('Total Deposit Transactions')}}</span>
                                <p class="d-flex">{{ depositCalculations.total ?  ( depositCalculations.total).toFixed(2) : 0 }} <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></p>
                            </li>
                            <li>
                                <span>{{__('Total Withdraw Transactions')}}</span>
                                <p class="d-flex">{{ withdrawCalculations.total ?  Math.abs(( withdrawCalculations.total).toFixed(2)) : 0 }} <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></p>
                            </li>
                            <li>
                                <span>{{__('Safe Balance')}}</span>
                                <p class="d-flex">{{ depositCalculations.total - withdrawCalculations.total ? (depositCalculations.total - withdrawCalculations.total).toFixed(2)  : 0   }} <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></p>
                            </li>


                        </ul>
                    </div>

                     <div class="action_buttons" v-if="dateFrom && dateTo && hours <= 24 &&  receiptEmployeeName">
                        <button type="button"
                                class="print_button"
                                @click="printStatisticsOnly"
                        ></button>
                    </div>

                    <hr  v-if="dateFrom && dateTo && hours <= 24 &&  receiptEmployeeName">



                    <div class="bonds_statistics" v-if="dateFrom && dateTo && hours <= 24 &&  receiptEmployeeName">
                        <div class="col">
                            <div class="col_title"><span class="deposit_icon">{{__('Deposit Statistics')}}</span></div>
                            <div class="col_content relative">
                                <ul>
                                    <li>{{__('Total Cash')}} <p class="deposit_price">{{ depositCalculations.cash ? (depositCalculations.cash).toFixed(2) : 0 }}</p> <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></li>
                                    <li>{{__('Total Bank Transfer')}} <p class="deposit_price">{{ depositCalculations.bank_transfer ? (depositCalculations.bank_transfer).toFixed(2) : 0 }}</p> <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></li>
                                    <li>{{__('Total Mada')}} <p class="deposit_price">{{ depositCalculations.mada ? (depositCalculations.mada).toFixed(2) : 0  }}</p> <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></li>
                                    <li>{{__('Total Credit Card')}} <p class="deposit_price">{{ depositCalculations.credit ? (depositCalculations.credit).toFixed(2) : 0  }}</p> <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></li>
                                    <li>{{__('Total Funds Transferred From Management')}} <p class="deposit_price">{{depositCalculations.total_funds_from_management ?  Math.abs((depositCalculations.total_funds_from_management).toFixed(2)) : 0 }}</p><span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></li>
                                    <li>{{__('Total insurance')}} <p class="deposit_price">{{Math.abs(depositCalculations.total_insurance).toFixed(2)}}</p> <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></li>
                                </ul>
                            </div>
                        </div>
                        <div class="col">
                            <div class="col_title"><span class="withdraw_icon">{{__('Withdraw Statistics')}}</span></div>
                            <div class="col_content relative">

                                <ul>
                                    <li>{{__('Total Cash')}} <p class="withdraw_price">{{ withdrawCalculations.cash ? formatter(withdrawCalculations.cash , 2) : 0 }}</p> <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></li>
                                    <li>{{__('Total Bank Transfer')}} <p class="withdraw_price">{{ withdrawCalculations.bank_transfer ? formatter(withdrawCalculations.bank_transfer , 2) : 0 }}</p> <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></li>
                                    <li>{{__('Total Mada')}} <p class="withdraw_price">{{ withdrawCalculations.mada ? formatter(withdrawCalculations.mada , 2) : 0 }}</p> <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></li>
                                    <li>{{__('Total Credit Card')}} <p class="withdraw_price">{{ withdrawCalculations.credit ? formatter(withdrawCalculations.credit , 2) : 0 }}</p> <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></li>
                                    <li>{{__('Total Funds Transferred To Management')}} <p class="withdraw_price">{{ withdrawCalculations.total_funds_to_management ?  Math.abs(formatter(withdrawCalculations.total_funds_to_management)) : 0 }}</p> <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></li>
                                    <li>{{__('Total insurance')}} <p class="withdraw_price">{{Math.abs(withdrawCalculations.total_retrieval_insurance).toFixed(2)}}</p> <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></li>
                                </ul>
                            </div>
                        </div>
                    </div>


                 <form id="report_report_statistics" target="_blank" method="post"  style="display: none" action="/home/print/receiptReport">
                    <input type="hidden" name="depositCalculations" :value="JSON.stringify(depositCalculations)">
                    <input type="hidden" name="withdrawCalculations" :value="JSON.stringify(withdrawCalculations)">
                    <input type="hidden" name="dateFrom" :value="dateFrom">
                    <input type="hidden" name="dateTo" :value="dateTo">
                    <input type="hidden" name="employeeId" :value="employeeId">
                    <input type="hidden" name="receiptEmployee" :value="receiptEmployeeName">
                </form>

    </sweet-modal>
  </div>
</template>

<script>
import moment from 'moment';
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css';
import flatpickr from 'flatpickr'
import {Arabic} from "flatpickr/dist/l10n/ar.js"
import momenttimezone from 'moment-timezone'
export default {
    name: 'receipt-report',
    components: {
        Loading
    },
    data(){
        return{
            dateFrom: momenttimezone().tz("Asia/Riyadh").format('YYYY-MM-DD HH:mm'),
            dateTo: null,
            employeeId: '',
            resetDisabled : true,
            locale: Nova.config.local,
            team_id : Nova.config.user.current_team_id,
            employees : [],
            receiptEmployees : [],
            showAlert : false,
            depositCalculations : {},
            withdrawCalculations : {},
            isLoading : false,
            hours : null,
            receiptEmployeeName : null,
            employeeName : '',
            currency :Nova.app.currentTeam.currency,

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
        }

    },
    methods: {
        filterEmployees(){
            this.receiptEmployees =   this.employees.filter(employee => employee.id !=  this.employeeId);
        },
        printStatisticsOnly(){
            $('#report_report_statistics').submit();
        },
        formatter:  (value, amount) => {
              const split = value.toString().split('.');
              if (split.length > 1) {
                  split[split.length-1] = split[split.length-1].substring(0, amount);
              }
              return amount > 0 ? split.join('.') : split[0];
        },
         resetFilters(){
                // this.dateFrom = '';
                // this.dateTo = '';
                // this.employeeId = Nova.config.user.id;
                // this.depositCalculations = {};
                // this.withdrawCalculations = {};
                // this.getEmployees();
                // this.filterEmployees();
                 this.clear();
            },
        clear(){
            this.dateFrom = null
            this.dateTo = null;
            this.employeeId = '';
            this.resetDisabled = true;
            this.depositCalculations = {};
            this.withdrawCalculations = {};
            this.employeeId = Nova.config.user.id;
            this.receiptEmployeeName = null;
            this.showAlert = false;
            this.getEmployees();
        },
        getEmployees(){
            axios.get(window.FANDAQAH_API_URL + `/users/dropDown?team_id=${this.team_id}`)
            .then((response) => {
                this.employees = response.data.data;

                this.filterEmployees();
            })
        },

        getDepositCalculations(){
            let params = {
                        'type' : 'deposit',
                        'filter[by_date_from]' : this.dateFrom,
                        'filter[by_date_to]' : this.dateTo,
                        'filter[by_creator]' : this.employeeId,
                };
            let config = {
                headers : {
                    'x-team' : this.team_id,
                    'x-localization' : this.locale
                },
                params : params
            }

            this.depositQuery = params;

            axios.get(window.FANDAQAH_API_URL + `/reports/receipt-report` , config)
                .then( res => {
                    this.depositCalculations = res.data.calculations;
                    this.isLoading = false;
                })

        },
        getWitdhrawCalculations(){
            let params = {
                        'type' : 'withdraw',
                        'filter[by_date_from]' : this.dateFrom,
                        'filter[by_date_to]' : this.dateTo,
                        'filter[by_creator]' : this.employeeId,
                };

            let config = {
                headers : {
                    'x-team' : this.team_id,
                    'x-localization' : this.locale
                },
                params : params
            }
            axios.get(window.FANDAQAH_API_URL + `/reports/receipt-report` , config)
                .then( res => {
                    this.withdrawCalculations = res.data.calculations;
                    this.isLoading = false;
                })
        },

    },
    watch: {
         dateValues: {
                handler: function (val) {

                    if ((val.dateFrom != null && val.dateFrom != '') && (val.dateTo != null && val.dateTo != '')) {

                        const dateOneObj = new Date(val.dateFrom);
                        const dateTwoObj = new Date(val.dateTo);
                        const milliseconds = Math.abs(dateTwoObj - dateOneObj);
                        this.hours = milliseconds / 36e5;

                      if(this.hours > 24){
                           this.showAlert = true;
                           this.resetDisabled = true;
                           this.isLoading = false;
                      }else{

                          this.showAlert = false;
                          this.resetDisabled = false;
                          this.isLoading = true;
                          this.getDepositCalculations();
                          this.getWitdhrawCalculations();
                      }

                    }

                },
                deep: true
            },
        employeeId: {
            handler : function (val) {

                //  if ((this.dateFrom != null && this.dateFrom != '') && (this.dateTo != null && this.dateTo != '') && this.receiptEmployeeName != null) {
                 if ((this.dateFrom != null && this.dateFrom != '') && (this.dateTo != null && this.dateTo != '')) {
                        this.resetDisabled = false;
                        this.isLoading = true;

                        if(val == '' || val == null){
                            this.receiptEmployeeName = null;
                        }

                        this.getDepositCalculations();
                        this.getWitdhrawCalculations();

                }


            },
            deep : true
        },
    },
    mounted(){
        const self = this;
        this.$nextTick(() => {
            this.flatpickrFrom = flatpickr(this.$refs.datePickerFrom, {
                enableTime: true,
                enableSeconds: false,
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
                onClose(){
                    // self.dateFrom = null
                },
                locale : self.locale
            })

            this.flatpickrTo = flatpickr(this.$refs.datePickerTo, {
                enableTime: true,
                enableSeconds: false,
                // onClose: this.getTransactions(),
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
                 onClose(){
                    if(self.calculateHours > 24) {
                        self.dateTo = null;
                    }
                },
                locale : self.locale
            })

        })


    }
}
</script>

<style lang="scss">
    .alert-error {
            color: red;
    font-size: 15px;
    font-weight: 600;
    }
    .receipt_report_modal {
        .sweet-modal {
            min-width: 60%;
            max-width: 100%;
            width: auto !important;
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
                    margin: 15px auto;
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
                    @media (min-width: 320px) and (max-width: 480px) {
                        width: 50%;
                        margin: 5px 0;
                    } /* media */
                    @media (min-width: 481px) and (max-width: 767px) {
                        width: 33.33333%;
                        margin: 5px 0;
                    } /* media */
                    @media (min-width: 768px) and (max-width: 991px) {
                        width: 25%;
                        margin: 5px 0;
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
                        line-height: 1.2;
                        &.totalDebtor {
                            color: #f56565;
                        } /* totalDebtor */
                        &.totalCreditor {
                            color: #48bb78;
                        } /* totalCreditor */
                    } /* p */
                } /* li */
            } /* ul */
        } /* statistics_area */
         .action_buttons {
                display: flex;
                align-items: center;
                justify-content: flex-end;
                margin: 0 auto 15px;
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
                                &:last-child {
                                    width: 50%;
                                    margin: 0;
                                } /* last-child */
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
            } /* sweet-content */
        } /* sweet-modal */
    } /* shifts modal */


#receipt_report_modal_div {
    margin: 10px auto 0;
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

        .table_area {
            .no_data_show {
                text-align: center;
                padding: 50px 15px 40px;
                svg {
                    display: block;
                    margin: 0 auto 15px;
                } /* svg */
                span {
                    display: block;
                    font-size: 15px;
                    text-align: center;
                    color: #000;
                } /* span */
            } /* no_data_show */
            .table-responsive {
                width: 100%;
                margin: 0 auto 20px;
                position: relative;
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
                                text-align: center !important;
                                color: #ffffff;
                                background: #4a5568;
                            } /* th */
                        } /* tr */
                    } /* thead */
                    tbody {
                        tr {
                            td {
                                text-align: center !important;
                                padding: 15px 5px;
                                vertical-align: middle;
                                line-height: 20px;
                                font-size: 15px;
                                border: 1px solid #ced4dc;
                                color: #000000;
                                font-weight: normal;
                                background: #ffffff;
                                height: auto;
                                &.td-fit {
                                    display: flex;
                                    align-items: center;
                                    justify-content: center;
                                    border-right: none;
                                    border-bottom: none;
                                    a, button {
                                        color: #b3b9bf;
                                        margin: 0 5px !important;
                                        outline: none;
                                        svg {
                                            path {
                                                fill: #b3b9bf;
                                                &:hover {fill: #3d92d4;}
                                            }
                                        }
                                        &:hover {color: #3d92d4;}
                                    } /* a */
                                } /* td-fit */
                                .text-left {
                                    text-align: center !important;
                                } /* text-left */
                            } /* td */
                        } /* tr */
                    } /* tbody */
                } /* table */
            } /* table-responsive */
        } /* table_area */
    } /* content_page */
} /* deposit_management_page */
.d-flex{
  display: flex !important;
}
</style>
