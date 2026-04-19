<template>
    <div>

        <div class="flex w-full mb-4">
            <nav v-if="crumbs.length">
                <ul class="breadcrumbs">
                    <li class="breadcrumbs__item" v-for="(crumb,i) in crumbs" :key="i ">
                        <router-link :to="crumb.to">{{ __(crumb.text) }}</router-link>
                    </li>
                </ul>
            </nav>
        </div>

        <div id="deposit_management_page">
            <div class="title">{{ dynamicType === 'deposit' ?  __('Deposit Management') : __('Withdraw Management') }}</div>
            <div class="content_page">

                <!-- Filter Area -->
                <div class="filter_area">
                    <div class="item">
                        <input
                            type="text"
                            v-model.lazy="transactionNumber"
                            :placeholder="__('Transaction Number')"
                        >
                    </div>

                    <div class="item">
                        <input
                            type="text"
                            v-model.lazy="reservationNumber"
                            oninput="this.value = this.value.replace(/[^0-9]/g, '').replace(/(\..*)\./g, '$1');"
                            :placeholder="__('Reservation Number')"
                        >
                    </div>

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
                            <option value="" selected>{{__('Payment Method')}}</option>
                            <option value="cash">{{__('Cash')}}</option>
                            <option value="bank-transfer">{{__('Bank Transfer')}}</option>
                            <option value="mada">{{__('Mada')}}</option>
                            <option value="credit">{{__('Credit Card')}}</option>
                            <option value="credit-payment">{{__('Credit Payment')}}</option>
                            <option value="rebate">{{__('Rebate')}}</option>

                        </select>
                    </div>

                    <div class="item">
                        <select v-model="transactionTerm">
                            <option value="" selected>{{__('Deposit Type')}}</option>
                            <option v-for="(term,i) in terms" :key="i" :value="term.id">{{term.name[locale]}}</option>
                        </select>
                    </div>

                    <div class="item" v-show="units.length">
                        <select v-model="unitId">
                            <option value="" selected>{{__('The Unit')}}</option>
                            <option v-for="(unit,i) in units" :value="unit.id" :key="i">{{ unit.number + ' - ' + unit.name[locale] }}</option>
                        </select>
                    </div>

                    <div class="item">
                        <select v-model="unitCategoryId">
                            <option value="" selected>{{__('Unit Category')}}</option>
                            <option v-for="(category,i) in unit_categories" :value="category.value" :key="i">{{ category.name }}</option>
                        </select>
                    </div>



                    <div class="item" v-show="employees.length">
                        <select v-model="employeeId">
                            <option value="" selected>{{__('Employee')}}</option>
                            <option v-for="(employee,i) in employees" :value="employee.id" :key="i">{{ employee.name }}</option>
                        </select>
                    </div>

                    <div class="item" v-if="dynamicType == 'withdraw'">
                        <select v-model="taxable">
                            <option value="" selected>{{__('VAT')}}</option>
                            <option value="not_taxable" selected>{{__('Not Taxable')}}</option>
                            <option value="taxable" selected>{{__('Taxable')}}</option>
                        </select>
                    </div>

                    <div class="reset_filters" slot="reset-btn">
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
                    </div><!-- reset_filters -->

                </div>

                <hr>

                <!-- Statistics -->
                <div class="statistics_area relative">
                    <loading :active="calculationsLoading" :loader="'spinner'" :color="'#7e7d7f'" :opacity="0.7" :height="20" :width="20" :is-full-page="false"></loading>
                    <ul>
                        <li>
                            <span>{{__('Total Cash')}}</span>
                            <p class="d-flex">{{calculations.cash}} <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></p>
                        </li>
                        <li>
                            <span>{{__('Total Bank Transfer')}}</span>
                            <p class="d-flex">{{calculations.bank_transfer}} <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></p>
                        </li>
                        <li>
                            <span>{{__('Total Mada')}}</span>
                            <p class="d-flex">{{calculations.mada}} <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></p>
                        </li>
                        <li>
                            <span>{{__('Total Credit Card')}}</span>
                            <p class="d-flex">{{calculations.credit_card}} <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></p>
                        </li>

                        <li>
                            <span>{{__('Credit Payment')}}</span>
                            <p class="d-flex">{{calculations.credit_payment}} <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></p>
                        </li>
                        <li>
                            <span>{{__('Total Rebate')}}</span>
                            <p class="d-flex">{{calculations.rebate}} <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></p>
                        </li>
                        <li>
                            <span>{{__('Total without insurance')}}</span>

                            <p class="d-flex" v-if="dynamicType == 'deposit'">
                                {{(calculations.total_without_format - calculations.total_insurance).toFixed(2)}} <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span>
                            </p>
                            <p class="d-flex" v-else>
                                {{(calculations.total_without_format - calculations.total_retrieval_insurance).toFixed(2)}} <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span>
                            </p>
                        </li>

                        <li>
                            <span>{{__('Total insurance')}}</span>
                            <p class="d-flex" v-if="dynamicType == 'deposit'">
                                {{(calculations.total_insurance).toFixed(2)}} <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span>
                            </p>
                            <p class="d-flex" v-else>
                                {{(calculations.total_retrieval_insurance).toFixed(2)}} <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span>
                            </p>
                        </li>
                        <template v-if="dynamicType == 'deposit'">
                            <li>
                                <span>{{__('Total')}}</span>
                                <p class="d-flex">{{calculations.total}} <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></p>
                            </li>
                        </template>
                        <template v-else>
                            <li>
                                <span>{{__('Total')}}</span>
                                <p class="d-flex">{{calculations.total_without_taxes}} <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></p>
                            </li>
                            <li>
                                <span>{{__('Total VAT')}}</span>
                                <p class="d-flex">{{calculations.total_vat}} <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></p>
                            </li>
                            <li>
                                <span>{{__('Total Includes VAT')}}</span>
                                <p class="d-flex">{{calculations.total}} <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></p>
                            </li>
                        </template>

                    </ul>
                </div>

                <hr>

                <!-- Action Buttons -->
                <div class="action_buttons">

                    <add-modal :type="dynamicType" :terms="terms" :unit_categories="unit_categories" />
                    <div class="buttons_area" v-if="paginator && paginator.total < 500">
                    <!-- <div class="buttons_area" v-if="paginator"> -->
                        <button type="button" class="excel_button" @click="excelExport"></button>
                        <button type="button" class="print_button" @click="printReport"></button>
                    </div><!-- buttons_area -->
                </div><!-- action_buttons -->

                <!-- Table Listing Area -->
                <div class="table_area">
                    <div class="table-responsive relative">
                        <loading :active="transactionsLoading" :loader="'spinner'" :color="'#7e7d7f'" :opacity="0.7" :height="20" :width="20" :is-full-page="false"></loading>
                        <table class="table w-full"
                               cellpadding="0"
                               cellspacing="0"
                        >
                            <thead>
                            <tr>
                                <th>{{__('Transaction Number')}}</th>
                                <th>{{ __('Reservation Num') }}</th>
                                <th>{{ __('Unit Number') }}</th>
                                <th>{{ __('Amount') }}</th>
                                <th v-if="dynamicType === 'withdraw'">{{ __('VAT') }}</th>
                                <th v-if="dynamicType === 'withdraw'">{{ __('Amount Include Tax') }}</th>
                                <th>{{ dynamicType === 'deposit' ? __('Received From') : __('Exchange to') }}</th>
                                <th>{{ __('For') }}</th>
                                <th>{{ __('Date Receipt') }}</th>
                                <th>{{ __('Payment Method') }}</th>
                                <th>{{ __('Reference Number') }}</th>
                                <th>{{ __('Employee') }}</th>
                                <th>{{__('Actions')}}</th>
                            </tr>
                            </thead>
                            <tbody>
                            <template v-if="transactions.length">

                                <tr v-for="(obj,i) in transactions" :key="i">
                                    <td>{{obj.transaction_number}}</td>
                                    <td v-if="obj.transaction_payable_type === 'App\\Team'">-</td>
                                    <td v-else>
                                        <router-link v-if="obj.customer_id" class="text-primary" :to="{name: 'reservation', params: {id: obj.transaction_payable_id}}">{{obj.reservation_number}}</router-link>
                                        <router-link v-else class="text-primary" :to="{name: 'reservation-noc', params: {id: obj.transaction_payable_id}}">{{obj.reservation_number}}</router-link>
                                    </td>
                                    <td v-if="obj.transaction_payable_type === 'App\\Team'">-</td>
                                    <td v-else>{{obj.unit_id ? obj.unit_number : '-'}}  {{  obj.unit_id  ? ' - ' + JSON.parse(obj.unit_name)[locale] : '' }}</td>
                                    <template v-if="dynamicType === 'withdraw' && parseInt(obj.transaction_enable_tax_on_withdraw)">
                                        <td>{{Math.abs(obj.transaction_amount_without_tax / (obj.wallet_decimal_places == 2 ? 100 : 1000) ).toFixed(2) }} </td>
                                    </template>
                                    <template v-else>
                                        <td>{{Math.abs(obj.transaction_amount / (obj.wallet_decimal_places == 2 ? 100 : 1000) ).toFixed(2) }} </td>
                                    </template>

                                    <template v-if="dynamicType === 'withdraw'">
                                        <template v-if="obj.transaction_enable_tax_on_withdraw">
                                            <td>{{Math.abs(obj.transaction_tax_amount / (obj.wallet_decimal_places == 2 ? 100 : 1000) ).toFixed(2) }} </td>
                                        </template>
                                        <template v-else>
                                            <td>0</td>
                                        </template>
                                            <td>{{Math.abs(obj.transaction_amount / (obj.wallet_decimal_places == 2 ? 100 : 1000) ).toFixed(2) }} </td>
                                    </template>

                                    <td>{{obj.transaction_received_from ? obj.transaction_received_from : '-'}}</td>
                                    <td v-if="obj.transaction_statement && obj.transaction_statement == 'Billed Online' ">{{__('Billed Online')}}</td>
                                    <td v-else-if="obj.transaction_statement && obj.transaction_statement == 'various_services'">{{__('Various services')}}</td>
                                    <td v-else>{{obj.transaction_statement ? obj.transaction_statement : '-'}}</td>
                                    <td>{{obj.transaction_date_receipt ? obj.transaction_date_receipt : '-'}}</td>
                                    <td>{{ obj.transaction_payment_method == 'credit' ? __('Credit Card') : __(capitalize(obj.transaction_payment_method)) }}</td>
                                    <td >{{obj.transaction_reference != 'null' ? obj.transaction_reference : '-'}}</td>
                                    <td>{{obj.transaction_employee ? obj.transaction_employee : '-' }}</td>
                                    <td>
                                        <!-- Actions Here -->

                                        <a class="display:none;"  :href="'/home/reservation/sub-invoice/' + hash_id  " ref="reservation_href" target="_blank"></a>
                                        <a class="display:none;"  :href="'/home/team/sub-invoice/' +  hash_id  " ref="team_href" target="_blank"></a>

                                        <button v-permission = "'print transactions'" class="cursor-pointer text-70 hover:text-primary mr-3" @click="hashTransactionId(obj,i)"  target="_blank"  :title="__('Print Transaction')" >
                                            <svg xmlns="http://www.w3.org/2000/svg" width="19.339" height="19.339" viewBox="0 0 19.339 19.339"><path d="M17.471,17.471v1.934a1.934,1.934,0,0,1-1.934,1.934H7.8a1.934,1.934,0,0,1-1.934-1.934V17.471H3.934A1.934,1.934,0,0,1,2,15.537v-5.8A1.94,1.94,0,0,1,3.934,7.8H5.868V3.934A1.94,1.94,0,0,1,7.8,2h7.735a1.934,1.934,0,0,1,1.934,1.934V7.8H19.4a1.934,1.934,0,0,1,1.934,1.934v5.8A1.934,1.934,0,0,1,19.4,17.471Zm0-1.934H19.4v-5.8H3.934v5.8H5.868V13.6A1.94,1.94,0,0,1,7.8,11.669h7.735A1.934,1.934,0,0,1,17.471,13.6ZM15.537,7.8V3.934H7.8V7.8ZM7.8,13.6v5.8h7.735V13.6Z" transform="translate(-2 -2)" class="fill-current"/></svg>
                                        </button>
                                        <!-- View Resource Link -->
                                        <button
                                            class="cursor-pointer text-70 hover:text-primary mr-3"
                                            :title="__('View')"
                                            @click="detailsModal(obj)"
                                        >
                                            <icon v-if="obj.transaction_created_by == obj.transaction_updated_by" type="view" width="22" height="18" view-box="0 0 22 16" />
                                            <svg v-else xmlns="http://www.w3.org/2000/svg" width="22" height="18" viewBox="0 0 22 16" aria-labelledby="view" role="presentation" class="fill-current" style="color:red;"><path d="M16.56 13.66a8 8 0 0 1-11.32 0L.3 8.7a1 1 0 0 1 0-1.42l4.95-4.95a8 8 0 0 1 11.32 0l4.95 4.95a1 1 0 0 1 0 1.42l-4.95 4.95-.01.01zm-9.9-1.42a6 6 0 0 0 8.48 0L19.38 8l-4.24-4.24a6 6 0 0 0-8.48 0L2.4 8l4.25 4.24h.01zM10.9 12a4 4 0 1 1 0-8 4 4 0 0 1 0 8zm0-2a2 2 0 1 0 0-4 2 2 0 0 0 0 4z"></path></svg>

                                        </button>


                                        <button v-permission="'edit financial'"
                                                class="cursor-pointer text-70 hover:text-primary mr-3"
                                                :title="__('Edit')"
                                                @click="editModal(obj)"
                                        >

                                            <icon type="edit" />
                                        </button>

                                        <!-- Delete Resource Link -->
                                        <button v-permission="'delete financial'"
                                                @click="deleteModal(obj)"
                                                class="appearance-none cursor-pointer text-70 hover:text-primary mr-3"
                                                :title="__('Delete')"
                                        >
                                            <icon />
                                        </button>

                                    </td>
                                </tr>
                            </template>
                            <template v-else>
                                <tr>
                                    <td colspan="11">{{__('No transactions found!')}}</td>
                                </tr>
                            </template>
                            </tbody>
                        </table>
                    </div>
                    <!-- Pagination -->
                    <div class="w-full flex flex-wrap mt-3 justify-center" v-if="transactions.length && paginator.total > 20">
                        <pagination
                            v-if="paginator.lastPage > 1"
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
                        />
                    </div><!-- Pagination -->
                    <div class="Results_area" v-if="transactions.length">
                        <p>{{__('Results')}}  : {{__('From')}} ( {{paginator.from}} ) - {{__('To')}}  ( {{paginator.to}} )</p>
                        <p>{{__('Total Transactions')}}  : {{paginator.total}}</p>
                    </div><!-- Results_area -->
                </div>

            </div>
        </div>

        <form id="transactions_form" target="_blank" method="post"  style="display: none" action="/home/print/transactions">
            <input type="hidden" name="type" :value="dynamicType">
            <input type="hidden" name="management" value="financial">
            <input type="hidden" name="query" :value="JSON.stringify(query)">
            <input type="hidden" name="team_id" :value="teamId">
            <input type="hidden" :value="JSON.stringify(calculations)" name="calculations">
        </form>
        <details-modal ref="sharedDetailsModal" :transaction="targetTransaction" />
        <edit-modal ref="sharedEditModal" :transaction="targetTransaction" :unit_categories="unit_categories" />
        <delete-modal ref="sharedDeleteModal" :transaction_id="targetTransactionId" />
    </div>
</template>

<script>
import flatpickr from "flatpickr";
import './airbnb-modified.css'
import moment from "moment";
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css';
import AddModal from "./Helpers/AddModal";
import EditModal from "./Helpers/EditModal";
import DetailsModal from "./Helpers/DetailsModal";
import DeleteModal from "./Helpers/DeleteModal";
import Pagination from './Pagination'
import XLSX from 'xlsx'
export default {
    name: "transactions",
    components:{
      Loading,
      AddModal,
      EditModal,
      DetailsModal,
      DeleteModal,
      Pagination
    },
    data(){
        return {
            transactionNumber : '',
            reservationNumber : '',
            unitId          : '',
            employeeId          : '',
            taxable : '',
            dateFrom : null,
            dateTo : null,
            flatpickrFrom: null,
            flatpickrTo: null,
            locale : 'en',
            paymentType : '',
            transactionTerm : '',
            terms : [],
            transactionsLoading : false,
            transactions : [],
            transactions_ids : [],
            paginator : {},
            range : '',
            dynamicType : '',
            calculations : {
                cash : (0).toFixed(2),
                bank_transfer : (0).toFixed(2),
                mada : (0).toFixed(2),
                credit_card : (0).toFixed(2),
                credit_payment : (0).toFixed(2),
                total : (0).toFixed(2),
                total_insurance : 0,
                total_retrieval_insurance : 0,
                total_without_format : 0,
                total_vat : 0,
                total_without_taxes : 0,
                rebate : (0).toFixed(2),
            },
            crumbs : [],
            hash_id : null,
            targetTransaction : null,
            targetTransactionId : null,
            query : {},
            selectedPage : 1,
            legacyQuery : {},
            teamId : Nova.config.user.current_team_id,
            calculationsLoading : false,
            time_12hrs_enabled : false,
            formedTransaction : {
                number : null,
                amount : null,
                statement : null,
                date_receipt : null,
                received_from : null,
                payment_method : null,
                reference : null,
                employee : null,
                received_by : null,
                note : null,
                lastUpdateEmployee : null,
                created_by : null,
                updated_by : null
            },
            withdraw_insurance_transactions : null,
            units : [],
            employees : [],
            currency :Nova.app.currentTeam.currency,
            unit_categories : [],
            unitCategoryId : ''

        }
    },
    computed:{
        dateFormat() {
            return  'Y-m-d H:i'
        },
    },
    methods:{
        getCurrentPage(page){
            this.selectedPage = page;
        },
        capitalize(label){
            if (typeof label !== 'string') return ''
            return label.charAt(0).toUpperCase() + label.slice(1)
        },
        hashTransactionId(obj,i) {
            axios.post('/nova-vendor/calender/hashTransactionId', {
                transaction_id: obj.transaction_id
            })
            .then((res) => {
                this.hash_id = res.data;
                this.triggerPrintHref(obj,i);
            })
        },
        triggerPrintHref(obj,i){

            this.$nextTick(() => {
                if(obj.transaction_payable_type === 'App\\Reservation'){
                    this.$refs.reservation_href[i].click();
                }else{
                    this.$refs.team_href[i].click();
                }
            });
        },
        fillTransaction(obj){
            this.formedTransaction.number = obj.transaction_number;
            this.formedTransaction.amount = (obj.transaction_amount / (obj.wallet_decimal_places == 2 ? 100 : 1000) ).toFixed(2);
            this.formedTransaction.amount_without_tax = (obj.transaction_amount_without_tax / (obj.wallet_decimal_places == 2 ? 100 : 1000) ).toFixed(2);
            this.formedTransaction.tax_percentage = obj.transaction_tax_percentage;
            this.formedTransaction.enable_tax_on_withdraw = obj.transaction_enable_tax_on_withdraw;
            this.formedTransaction.tax_amount = (obj.transaction_tax_amount / (obj.wallet_decimal_places == 2 ? 100 : 1000) ).toFixed(2);
            this.formedTransaction.supplier_tax_number = obj.transaction_supplier_tax_number;
            this.formedTransaction.invoice_number = obj.transaction_invoice_number;

            let statement_label = null;
            if(obj.transaction_statement && obj.transaction_statement == 'Billed Online'){
                statement_label = this.__('Billed Online');
            }else if(obj.transaction_statement && obj.transaction_statement == 'various_services'){
                statement_label = this.__('Various services');
            }else{
                statement_label = obj.transaction_statement ? obj.transaction_statement : '-'
            }
            this.formedTransaction.statement = statement_label;
            this.formedTransaction.date_receipt = obj.transaction_date_receipt;
            this.formedTransaction.received_from = obj.transaction_received_from != 'null' ? obj.transaction_received_from : '-';
            // this.formedTransaction.payment_method = obj.transaction_payment_method == 'credit' ?  this.__('Credit Card') : this.__(this.capitalize(obj.transaction_payment_method));
            this.formedTransaction.payment_method = obj.transaction_payment_method;
            this.formedTransaction.reference = obj.transaction_reference != 'null' ? obj.transaction_reference : '-' ;
            this.formedTransaction.employee = obj.transaction_employee ? obj.transaction_employee : '-' ;
            this.formedTransaction.received_by = obj.transaction_received_by != 'null' ? obj.transaction_received_by : '-';
            this.formedTransaction.note = obj.transaction_note != 'null' ? obj.transaction_note : '-';
            this.formedTransaction.lastUpdateEmployee = obj.transaction_lastUpdateEmployee ? obj.transaction_lastUpdateEmployee : '-';
            this.formedTransaction.created_by = obj.transaction_created_by;
            this.formedTransaction.updated_by = obj.transaction_updated_by;

            if(obj.transaction_type == 'deposit'){
                this.formedTransaction.person_in_charge = JSON.parse(obj.transaction_meta).person_in_charge;
            }
        },
        detailsModal(obj){
            this.fillTransaction(obj);
            this.targetTransaction = this.formedTransaction;
            this.$nextTick(() => {
                this.$refs.sharedDetailsModal.$refs.transactionModal.open();
            });
        },
        editModal(obj){

            this.targetTransaction = obj;
            this.$nextTick(() => {
                this.$refs.sharedEditModal.$refs.editModal.open();
            });
        },
        async deleteModal(obj){

            if(obj.transaction_payable_type == 'App\\Reservation' && obj.transaction_type == 'deposit'){

                // hit an endpoint to get withdraw_insurance_transactions for current reservation transaction
                let config = {
                    headers : {
                        'x-team' : this.teamId,
                        'x-res' : obj.reservation_id,
                        'x-localization' : this.locale
                    },
                    params : this.$route.query
                };
                const self = this;
                 await axios.get(window.FANDAQAH_API_URL + `/transactions/insurance-transactions` , config)
                .then((response) => {
                    self.withdraw_insurance_transactions = response.data.withdraw_insurance_transactions.length;
                });
                if(obj.transaction_is_insurance && this.withdraw_insurance_transactions){
                    this.$toasted.show(this.__('We can not delete the insurance transaction cause there is retrieval insurance transaction added'), {type: 'error'});
                    return ;
                }
            }

            this.targetTransactionId = obj.transaction_id;
            this.$nextTick(() => {
                this.$refs.sharedDeleteModal.$refs.deleteModal.open();
            });
        },
        excelExport(){

            this.transactionsLoading = true;

             let config = {
                headers : {
                    'x-team' : this.teamId,
                    'x-localization' : this.locale
                },
                params : this.$route.query
            }
            axios.get(window.FANDAQAH_API_URL + '/transactions/excel' , config).then(response => {

                this.transactionsLoading = false;
                let transactionsWs = XLSX.utils.json_to_sheet(response.data.data)
                let wb = XLSX.utils.book_new();
                XLSX.utils.book_append_sheet(wb, transactionsWs, response.data.file_name );
                let file_name = this.dynamicType == 'deposit' ? this.__('Deposit Transactions Management Report') : this.__('Withdraw Transactions Management Report');
                XLSX.writeFile(wb, file_name + '.xlsx');
                this.$toasted.show(response.data.msg, {type: 'success'});
            });
        },
        printReport(){
            $('#transactions_form').submit();
        },
        resetFilters(){


            let opt = {}
            opt["page"] = 1;
            this.$router.push({
                name : 'transactions',
                query: Object.assign({}, this.legacyQuery, opt)
            } , () => {
                this.transactionNumber = '';
                this.reservationNumber = '';
                this.unitId = '';
                this.employeeId = '';
                this.dateFrom = moment(new Date()).subtract(7, 'days').format('YYYY-MM-DD HH:mm');
                this.dateTo = null;
                this.paymentType = '';
                this.transactionTerm = '';
                this.taxable = '';
                this.unitCategoryId = '';
                this.getTransactions();
            })
        },
        toMomentsFormat(format){
            var res=format;
            res = res.replace('Y', 'YYYY');
            res = res.replace('m', 'MM');
            res = res.replace('d', 'DD');
            return res;
        },
        getTransactions(page=1){

            let config = {
                headers : {
                    'x-team' : this.teamId,
                    'x-localization' : this.locale
                },
                params : this.$route.query
            }

            this.transactionsLoading = true;
            axios.get(window.FANDAQAH_API_URL + `/transactions/list` , config)
            // axios.get('/nova-vendor/financial-management/get-transactions' , config)
                .then((response) => {
                    this.transactions = response.data.data;
                    // this.paginator = {
                    //     currentPage : response.data.meta.current_page || null ,
                    //     lastPage : response.data.meta.last_page || null ,
                    //     from : response.data.meta.from || null,
                    //     to : response.data.meta.to || null,
                    //     total : response.data.meta.total || null,
                    //     pathPage : response.data.meta.path + '?page=' || null,
                    //     firstPageUrl : response.data.links.first || null ,
                    //     lastPageUrl : response.data.links.last || null ,
                    //     nextPageUrl : response.data.links.next || null ,
                    //     prevPageUrl : response.data.links.prev || null ,
                    // }

                    this.transactionsLoading = false;
                    this.query = this.$route.query;

                    this.getTotals();
                })
                .catch((err) => {
                    console.log(err);
                });

        },
        getTotals(){
            let config = {
                headers : {
                    'x-team' : this.teamId,
                    'x-localization' : this.locale
                },
                params : this.$route.query
            }
            this.calculationsLoading = true;
            axios.get(window.FANDAQAH_API_URL + `/transactions/totals` , config)
            .then((response) => {

                    this.paginator = {
                        currentPage : response.data.pagination.current_page || null ,
                        lastPage : response.data.pagination.last_page || null ,
                        from : response.data.pagination.from || null,
                        to : response.data.pagination.to || null,
                        total : response.data.pagination.total || null,
                        pathPage : response.data.pagination.path + '?page=' || null,
                        firstPageUrl : response.data.pagination.first_page_url || null ,
                        lastPageUrl : response.data.pagination.last_page_url || null ,
                        nextPageUrl : response.data.pagination.next_page_url || null ,
                        prevPageUrl : response.data.pagination.prev_page_url || null ,
                    }

                   this.calculations = {
                        cash : response.data.calculations.cash ,
                        bank_transfer : response.data.calculations.bank_transfer,
                        mada : response.data.calculations.mada,
                        credit_card : response.data.calculations.credit,
                        credit_payment : response.data.calculations.credit_payment,
                        rebate : response.data.calculations.rebate,
                        total : response.data.calculations.total,
                        total_insurance : response.data.calculations.total_insurance,
                        total_retrieval_insurance : response.data.calculations.total_retrieval_insurance,
                        total_without_format : response.data.calculations.total_without_format,
                        total_vat : response.data.calculations.total_vat,
                        total_without_taxes : response.data.calculations.total_without_taxes,
                    }

                    // this.transactions_ids = response.data.transactions_ids;
                    this.calculationsLoading = false;
            })
        },
        getTerms(){
            this.terms = [];
            let type = this.dynamicType == 'deposit' ? 2 : 1 ;
            axios.get(window.FANDAQAH_API_URL + `/terms/dropDown?type=${type}&team_id=${this.teamId}`)
                .then(res => {
                    this.terms = res.data.data;
                })
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
         getUnits(){
            axios.get(window.FANDAQAH_API_URL + `/units/dropDown?team_id=${this.teamId}`)
            .then((response) => {
                this.units = response.data.data;
            })
        },
        getEmployees(){
            axios.get(window.FANDAQAH_API_URL + `/users/dropDown?team_id=${this.teamId}`)
            .then((response) => {
                this.employees = response.data.data;
            })
        },
        getUnitCategoryFilterValues() {
            Nova.request()
            .get("/nova-vendor/calender/reservations/unit-category-filter-values")
            .then((response) => {
                this.unit_categories = response.data;
            });
        },
    },
    watch:{
        employeeId: function (val) {
            if(val != null){
                let opt = {}
                opt["filter[by_employee_id]"] = val;
                opt["page"] = 1;
                this.$router.push({
                    name : 'transactions',
                    query: Object.assign({}, this.$route.query, opt)
                } , () => {
                    this.getTransactions();
                })
            }

            if(val == null){
                let opt = {}
                opt["page"] = 1;
                this.$router.push({
                    name : 'transactions',
                    query: Object.assign({}, this.$route.query, opt)
                } , () => {
                    this.getTransactions();
                })
            }
        },
        taxable: function (val) {
            if(val != null){
                let opt = {}
                opt["filter[by_taxable]"] = val;
                opt["page"] = 1;
                this.$router.push({
                    name : 'transactions',
                    query: Object.assign({}, this.$route.query, opt)
                } , () => {
                    this.getTransactions();
                })
            }

            if(val == null){
                let opt = {}
                opt["page"] = 1;
                this.$router.push({
                    name : 'transactions',
                    query: Object.assign({}, this.$route.query, opt)
                } , () => {
                    this.getTransactions();
                })
            }
        },
        unitId: function (val) {
            if(val != null){
                let opt = {}
                opt["filter[by_unit_id]"] = val;
                opt["page"] = 1;
                this.$router.push({
                    name : 'transactions',
                    query: Object.assign({}, this.$route.query, opt)
                } , () => {
                    this.getTransactions();
                })
            }

            if(val == null){
                let opt = {}
                opt["page"] = 1;
                this.$router.push({
                    name : 'transactions',
                    query: Object.assign({}, this.$route.query, opt)
                } , () => {
                    this.getTransactions();
                })
            }
        },
        unitCategoryId: function (val) {
            if(val != null){
                let opt = {}
                opt["filter[by_unit_category_id]"] = val;
                opt["page"] = 1;
                this.$router.push({
                    name : 'transactions',
                    query: Object.assign({}, this.$route.query, opt)
                } , () => {
                    this.getTransactions();
                })
            }

            if(val == null){
                let opt = {}
                opt["page"] = 1;
                this.$router.push({
                    name : 'transactions',
                    query: Object.assign({}, this.$route.query, opt)
                } , () => {
                    this.getTransactions();
                })
            }
        },
        paymentType: function (val) {
            if(val != null){
                let opt = {}
                opt["filter[by_payment_type]"] = val;
                opt["page"] = 1;
                this.$router.push({
                    name : 'transactions',
                    query: Object.assign({}, this.$route.query, opt)
                } , () => {
                    this.getTransactions();
                })
            }

            if(val == null){
                let opt = {}
                opt["page"] = 1;
                this.$router.push({
                    name : 'transactions',
                    query: Object.assign({}, this.$route.query, opt)
                } , () => {
                    this.getTransactions();
                })
            }
        },
        transactionNumber: function (val) {
            if(val != null){
                let opt = {}
                opt["filter[by_number]"] = val;
                opt["page"] = 1;
                this.$router.push({
                    name : 'transactions',
                    query: Object.assign({}, this.$route.query, opt)
                } , () => {
                    this.getTransactions();
                })
            }

            if(val == null){
                let opt = {}
                opt["page"] = 1;
                this.$router.push({
                    name : 'transactions',
                    query: Object.assign({}, this.$route.query, opt)
                } , () => {
                    this.getTransactions();
                })
            }
        },
        reservationNumber: function (val) {
            if(val != null){
                let opt = {}
                opt["filter[by_reservation_number]"] = val;
                opt["page"] = 1;
                this.$router.push({
                    name : 'transactions',
                    query: Object.assign({}, this.$route.query, opt)
                } , () => {
                    this.getTransactions();
                })
            }

            if(val == null){
                let opt = {}
                opt["page"] = 1;
                this.$router.push({
                    name : 'transactions',
                    query: Object.assign({}, this.$route.query, opt)
                } , () => {
                    this.getTransactions();
                })
            }
        },
        transactionTerm: function (val) {
            if(val != null){
                let opt = {}
                opt["filter[by_term]"] = val;
                opt["page"] = 1;
                this.$router.push({
                    name : 'transactions',
                    query: Object.assign({}, this.$route.query, opt)
                } , () => {
                    this.getTransactions();
                })
            }

            if(val == null){
                let opt = {}
                opt["page"] = 1;
                this.$router.push({
                    name : 'transactions',
                    query: Object.assign({}, this.$route.query, opt)
                } , () => {
                    this.getTransactions();
                })
            }
        },
        dateFrom: function (val) {
            if(val != null){
                let opt = {}
                opt["filter[by_date_from]"] = val;
                opt["page"] = 1;
                this.$router.push({
                    name : 'transactions',
                    query: Object.assign({}, this.$route.query, opt)
                } , () => {
                    this.getTransactions();
                })
            }

            if(val == null){
                let opt = {}
                opt["page"] = 1;
                this.$router.push({
                    name : 'transactions',
                    query: Object.assign({}, this.$route.query, opt)
                } , () => {
                    this.getTransactions();
                })
            }
        },
        dateTo: function (val) {
            if(val != null){
                let opt = {}
                opt["filter[by_date_to]"] = val;
                opt["page"] = 1;
                this.$router.push({
                    name : 'transactions',
                    query: Object.assign({}, this.$route.query, opt)
                } , () => {
                    this.getTransactions();
                })
            }

            if(val == null){
                let opt = {}
                opt["page"] = 1;
                this.$router.push({
                    name : 'transactions',
                    query: Object.assign({}, this.$route.query, opt)
                } , () => {
                    this.getTransactions();
                })
            }
        },

        selectedPage: function (val) {
            if(val){
                let opt = {}
                opt["page"] = val;
                this.$router.push({
                    name : 'transactions',
                    query: Object.assign({}, this.$route.query, opt)
                } , () => {
                    this.getTransactions();
                })
            }
        }
    },
    mounted() {

        this.getSearchWithTimeSetting();
        this.getUnits();
        this.getEmployees();
        this.getUnitCategoryFilterValues();
        this.locale = Nova.config.local;

        this.dynamicType = this.$route.query.type;
        this.crumbs = [
            {
                text : 'Home',
                to : '/dashboards/main'
            },

            {
                text : 'Financial Management',
                to : '/financial-management'
            },

            {
                text : this.dynamicType === 'deposit' ? 'Deposit Management' : 'Withdraw Management',
                to : '#'
            }
        ];
        this.getTerms();
        this.dateFrom = moment(new Date()).subtract(7, 'days').format('YYYY-MM-DD HH:mm');
        // this.getTransactions()

        Nova.$on('add-transaction' , () => {
            this.getTransactions();
        })
        Nova.$on('transaction-updated' , () => {
            this.getTransactions();
        })
        Nova.$on('delete-transaction' , () => {
            this.getTransactions();
        })
        this.query = this.$route.query;
        this.legacyQuery = this.$route.query;
    },

}
</script>

<style lang="scss">
.flatpickr-time {
    height: auto !important;
}

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
#deposit_management_page {
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
        .filter_area {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            justify-content: flex-start;
            margin: 0 -10px;
            .item {
                width: 16.66666%;
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
                    margin: 10px 0;
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
            justify-content: space-between;
            margin: 0 0 10px;
            button.add_receipts {
                display: block;
                background: #4099de;
                border: none;
                border-radius: 4px;
                color: #fff;
                font-size: 15px;
                padding: 5px 15px;
                &:hover {
                    background: #0071C9;
                } /* hover */
            } /* add_receipts */
            .buttons_area {
                display: flex;
                align-items: center;
                justify-content: flex-end;
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
            } /* buttons_area */
        } /* action_buttons */
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
                                padding: 10px 5px;
                                vertical-align: middle;
                                line-height: 20px;
                                font-size: 15px;
                                border: 1px solid #ced4dc;
                                color: #000000;
                                font-weight: normal;
                                background: #ffffff;
                                &.td-fit {
                                    display: flex;
                                    align-items: center;
                                    justify-content: center;
                                    border-right: none;
                                    border-bottom: none;
                                    a, button {
                                        color: #b3b9bf;
                                        margin: 0 5px !important;
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
