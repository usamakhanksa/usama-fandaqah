<template>
    <sweet-modal v-if="transaction" :enable-mobile-fullscreen="false"  :pulse-on-block="false" :title="__('Edit Transaction')"  @open="fillData" @close="clearData"  overlay-theme="dark" ref="editModal" class="Edit_Transaction_Modal">
        <!-- deposit -->
        <div v-if="transaction.transaction_type == 'deposit'">
            <div class="formgroup" v-if="filteredTerms">
                <label>{{__('Type')}}<span>*</span></label>

                <select v-model="kind" @change="updateFor($event)" :disabled="transaction.transaction_category == 'service-deposit' || transaction.transaction_is_promissory  || disableFields">
                    <option  value="" disabled selected> {{__('Select Option')}}</option>
                    <option v-for="(term,i) in filteredTerms"  :value="term.id" :key="i">
                        {{term.name[local]}}
                    </option>
                </select>
            </div><!-- formgroup-->
            <div class="row_group">
                <div class="col">
                    <label>{{__('Date')}}<span>*</span></label>

                    <input
                        :disabled="!canChangeTransactionDate"
                        readonly
                        v-model="date"
                        ref="datePicker"
                        type="text"
                        :placeholder="__('Select Transaction Date')"
                    >
                </div><!-- col-->
                <div class="col">
                    <label>{{__('From')}}<span>*</span></label>
                    <input type="text" v-model="from" ref="from" :disabled="disableFields" :placeHolder="__('From')">
                </div><!-- col-->
            </div><!-- row_group-->
            <div class="row_group">
                <div class="col">
                    <label>{{__('Amount')}}<span>*</span></label>
                    <input type="tel" min="0" v-model="amount" :disabled="disableFields" :placeHolder="__('Amount')">
                </div><!-- col-->
                <div class="col">
                    <label>{{__('For')}}<span>*</span></label>
                    <input type="text" v-model="description" :disabled="disableFields" :placeHolder="__('For')">
                </div><!-- col-->
            </div><!-- row_group-->
            <div class="row_group">
                <div class="col">
                    <label>{{__('Payment Type')}}<span>*</span></label>
                    <select :placeholder="__('Payment Type')" v-model="payment_type" @change="checkPaymentType">
                        <option value="" disabled selected>{{__('Select Payment Type')}}</option>
                        <option value="cash">{{__('Cash')}}</option>
                        <option value="bank-transfer">{{__('Bank Transfer')}}</option>
                        <option value="mada">{{__('Mada')}}</option>
                        <option value="credit">{{__('Credit Card')}}</option>
                        <option value="credit-payment">{{__('Credit Payment')}}</option>
                    </select>
                </div><!-- col-->

                <div class="col">
                    <label>{{__('Notes')}}</label>
                    <input type="text" v-model="note" :placeHolder="__('Notes')">
                </div><!-- col-->
            </div><!-- row_group-->
            <div v-if="transaction && transaction.transaction_payable_type == 'App\\Team'" class="mb-3">
                <div class="col">
                <label>{{__('Unit Category')}}</label>
                <select v-model="unit_category_id">
                    <option value="" selected>{{__('Unit Category')}}</option>
                    <option v-for="(category,i) in unit_categories" :value="category.value" :key="i">{{ category.name }}</option>
                </select>
                </div><!-- col -->

            </div><!-- row_group -->
            <div class="formgroup" v-if="ref">
                <label>{{__('Payment Reference')}}</label>
                <input type="text" v-model="reference" :placeHolder="__('Payment Reference')">
            </div><!-- formgroup-->
            <div class="formgroup" v-if="payment_type == 'credit-payment'">
                <label>{{__('Person In Charge')}}</label>
                <select v-model="person_in_charge">
                    <option value="" selected>{{__('Person In Charge')}}</option>
                    <option v-for="(employee,i) in employees"  :key="i">{{ employee.name }}</option>
                </select>
            </div>
        </div>
        <!-- withdraw -->
        <div v-else>
            <div class="formgroup" v-if="filteredTerms">
                <label>{{__('Type')}}<span>*</span></label>
                <select v-model="kind" @change="updateFor" :disabled="disableFields">
                    <option value="" disabled selected>{{__('Select Option')}}</option>
                    <option v-for="term in filteredTerms" :value="term.id">
                        {{term.name[local]}}
                    </option>
                </select>
            </div><!-- formgroup-->
            <div class="row_group">
                <div class="col">
                    <label>{{__('Date')}}<span>*</span></label>
                    <input
                        :disabled="!canChangeTransactionDate && disableFields"
                        readonly
                        v-model="date"
                        ref="datePicker"
                        type="text"
                        :placeholder="__('Select Transaction Date')"
                    >
                </div><!-- col-->
                <div class="col">
                    <label>{{__('To')}}<span>*</span></label>
                    <input type="text" v-model="from" ref="from" :disabled="disableFields" :placeHolder="__('To')">
                </div><!-- col-->
            </div><!-- row_group-->
            <div class="row_group">
                <div class="col">
                    <label>{{__('Amount')}}<span>*</span></label>
                    <input type="tel" min="0" v-model="amount" :disabled="disableFields" :placeHolder="__('Amount')">
                </div><!-- col-->
                <div class="col">
                    <label>{{__('For')}}<span>*</span></label>
                    <input type="text" v-model="description" :disabled="disableFields" :placeHolder="__('For')">
                </div><!-- col-->
            </div><!-- row_group-->

            <div class="row_group" v-if="transaction.transaction_type == 'withdraw' && transaction.transaction_payable_type == 'App\\Team' && (transaction.transaction_tax_percentage || tax_percentage)">
                <div class="col">
                <label>{{__('Amount Include Tax')}}<span v-if="!disable_amount_include_tax">*</span></label>
                <input type="tel" :disabled="disable_amount_include_tax" v-model="amount_include_tax" :placeHolder="__('Amount Include Tax')">
                </div>
                <div class="col">
                <label>{{__('Tax Amount')}}</label>
                <input type="tel" disabled v-model="tax_amount" :placeHolder="__('Tax Amount')">
                </div>
            </div>

            <div class="row_group" v-if="transaction.transaction_type == 'withdraw' && transaction.transaction_payable_type == 'App\\Team' && (transaction.transaction_tax_percentage || tax_percentage)">
                <div class="col">
                    <label>{{__('Supplier tax number')}}</label>
                    <input type="text" :disabled="disable_supplier_tax_number" v-model="supplier_tax_number" :placeHolder="__('Supplier tax number')">
                </div>
                <div class="col">
                    <label>{{__('Invoice number')}}</label>
                    <input type="text" :disabled="disable_invoice_number" v-model="invoice_number" :placeHolder="__('Invoice number')">
                </div>
            </div>


            <div class="row_group">
                <div class="col">
                    <label>{{__('Received By')}}<span>*</span></label>
                    <input type="text" v-model="received_by" :disabled="disableFields" :placeHolder="__('Received By')">
                </div><!-- col-->
                <div class="col">
                    <label>{{__('Payment Type')}}<span>*</span></label>
                    <select placeholder="Payment Type" v-model="payment_type" @change="checkPaymentType">
                        <option value="" disabled selected>{{__('Select Payment Type')}}</option>
                        <option value="cash">{{__('Cash')}}</option>
                        <option value="bank-transfer">{{__('Bank Transfer')}}</option>
                        <option value="mada">{{__('Mada')}}</option>
                        <option value="credit">{{__('Credit Card')}}</option>
                    </select>
                </div><!-- col-->
            </div><!-- row_group-->

            <div v-if="transaction && transaction.transaction_payable_type == 'App\\Team'"  class="mb-3">
                <div class="col">
                <label>{{__('Unit Category')}}</label>
                <select v-model="unit_category_id">
                    <option value="" selected>{{__('Unit Category')}}</option>
                    <option v-for="(category,i) in unit_categories" :value="category.value" :key="i">{{ category.name }}</option>
                </select>
                </div><!-- col -->

            </div><!-- row_group -->

            <template v-if="!ref">
                <div class="formgroup">
                    <label>{{__('Notes')}}</label>
                    <input type="text" v-model="note" :placeHolder="__('Notes')">
                </div>
            </template>
            <template v-else>
                <div class="row_group">
                    <div class="col">
                        <label>{{__('Notes')}}</label>
                        <input type="text" v-model="note" :placeHolder="__('Notes')">
                    </div><!-- col-->
                    <div class="col" v-if="ref">
                        <label>{{__('Payment Reference')}}</label>
                        <input type="text" v-model="reference" :placeHolder="__('Payment Reference')">
                    </div><!-- col-->
                </div><!-- row_group-->
            </template>

            <div class="options_choose" v-if="transaction.transaction_type == 'withdraw' && transaction.transaction_payable_type == 'App\\Team' && (transaction.transaction_tax_percentage || tax_percentage)">
                <p>{{__('Enable Vat On Withdraw')}} ({{ tax_percentage }}%)</p>
                <div class="switch_label">
                    <p></p>
                    <label class="switch">
                        <input type="checkbox" v-model="enable_tax_on_withdraw" @change="enable_tax_on_withdraw_changed">
                        <span class="slider round"></span>
                    </label>
                </div>
            </div>
        </div>

        <button @click="send(transaction)" v-show="!hideAddBtn">{{__('Save')}}</button>
    </sweet-modal>
</template>

<script>

import flatpickr from "flatpickr";
import { Arabic } from "flatpickr/dist/l10n/ar.js"

export default {
    name: "EditModal",
    props : ['transaction','terms','unit_categories'],
    components:{
        // DateTimePickerFrom
    },
    data(){
        return {
            flatpickr: null,
            kind: null,
            local: 'en',
            date: moment().format('YYYY-MM-DD HH:mm'),
            datePicker: moment().toDate(),
            from: null,
            terms: [],
            amount: null,
            payment_type: null,
            description: null,
            note: null,
            ref: false,
            reference: null,
            received_by:null,
            employee : '',
            calendarLocale : 'en',
            canChangeTransactionDate : false,
            teamId : Nova.config.user.current_team_id,
            filteredTerms : [],
            termName : null,
            disableFields : false,
            hideAddBtn : false,
            withdraw_insurance_transactions : null,
            deposit_insurance_transactions : null,
            tax_percentage : null,
            enable_tax_on_withdraw : false,
            amount_include_tax : null,
            tax_amount : null,
            supplier_tax_number : null,
            invoice_number : null,
            disable_amount_include_tax : false,
            disable_supplier_tax_number : false,
            disable_invoice_number : false,
            person_in_charge : '',
            employees : [],
            unit_category_id : ''
        }
    },
    computed:{
        dateFormat() {
            return  'Y-m-d H:i'
        },

    },
    watch : {
        amount(num) {

            if(!num){
            this.amount_include_tax = null;
            this.tax_amount = null;
            }else{
            this.amount = num.replace(/([٠١٢٣٤٥٦٧٨٩])|([۰۱۲۳۴۵۶۷۸۹])/g, (m, $1, $2) => m.charCodeAt(0) - ($1 ? 1632 : 1776))
            this.amount = this.amount.replace(/[^\d.\d]/g,'');
            if(this.enable_tax_on_withdraw && parseFloat(this.transaction.transaction_tax_percentage)){
                this.amount_include_tax = parseFloat(this.amount) + parseFloat((this.amount * (this.transaction.transaction_tax_percentage / 100)));
                this.tax_amount = parseFloat( parseFloat(this.amount_include_tax - this.amount).toFixed(2));
            }

            if(this.tax_percentage && this.enable_tax_on_withdraw && !parseFloat(this.transaction.transaction_tax_percentage)){
                this.amount_include_tax = parseFloat(this.amount) + parseFloat((this.amount * (this.tax_percentage / 100)));
                this.tax_amount = parseFloat( parseFloat(this.amount_include_tax - this.amount).toFixed(2));
            }
            }
        },
        amount_include_tax(num) {
            if(num){
                this.amount_include_tax = num.replace(/([٠١٢٣٤٥٦٧٨٩])|([۰۱۲۳۴۵۶۷۸۹])/g, (m, $1, $2) => m.charCodeAt(0) - ($1 ? 1632 : 1776));
                this.amount_include_tax = this.amount_include_tax.replace(/[^\d.\d]/g,'');

                let x = this.amount_include_tax;
                let v;
                if(this.enable_tax_on_withdraw && parseFloat(this.transaction.transaction_tax_percentage)){
                    v = this.transaction.transaction_tax_percentage / 100;
                }

                if(this.tax_percentage && this.enable_tax_on_withdraw && !parseFloat(this.transaction.transaction_tax_percentage)){
                    v = this.tax_percentage / 100;
                }

                let y = parseFloat(x / (1 + v)).toFixed(2);
                this.amount = parseFloat(y);
                this.tax_amount = parseFloat( parseFloat(this.amount_include_tax - this.amount).toFixed(2));
            }else{
                if(this.enable_tax_on_withdraw){
                    this.amount = null;
                }
                this.tax_amount = null;
            }
        },
        payment_type: {
            handler(val,oldVal){

                if(val == 'cash' || val == 'credit-payment'){
                    this.reference = null;
                }
            },
            deep: true
        }
    },
    methods:{
        enable_tax_on_withdraw_changed(){
            this.disable_amount_include_tax = !this.enable_tax_on_withdraw ? true : false;
            this.disable_supplier_tax_number = !this.enable_tax_on_withdraw ? true : false;
            this.disable_invoice_number = !this.enable_tax_on_withdraw ? true : false;

            if(!this.enable_tax_on_withdraw){
                this.amount_include_tax = null;
                this.tax_amount = null
                this.supplier_tax_number = null;
                this.invoice_number = null;
            }

            if(this.enable_tax_on_withdraw && this.amount){
              if(parseFloat(this.transaction.transaction_tax_percentage)){
                  this.amount_include_tax = parseFloat(this.amount) + parseFloat((this.amount * (this.transaction.transaction_tax_percentage / 100)));
                  this.tax_amount = parseFloat(parseFloat(this.amount_include_tax - this.amount).toFixed(2));
                  this.supplier_tax_number = this.transaction.transaction_supplier_tax_number;
                  this.invoice_number = this.transaction.transaction_invoice_number;
              }
              if(this.tax_percentage && !parseFloat(this.transaction.transaction_tax_percentage)){
                // it's a legacy transaction before any tax applied
                  this.amount_include_tax = parseFloat(this.amount) + parseFloat((this.amount * (this.tax_percentage / 100)));
                  this.tax_amount = parseFloat(parseFloat(this.amount_include_tax - this.amount).toFixed(2));

              }
            }
        },
        getEmployees(){
            this.person_in_charge = '';
            axios.get(window.FANDAQAH_API_URL + `/users/dropDown?team_id=${this.teamId}`)
                .then((response) => {
                    this.employees = response.data.data;
                    this.person_in_charge = this.transaction.transaction_meta ? JSON.parse(this.transaction.transaction_meta).person_in_charge : '';
                })
        },
        async updateFor(event){
                let config = {
            headers : {
                'x-team' : this.teamId,
                'x-res' : this.transaction.reservation_id,
                'x-localization' : this.local
            },
            params : this.$route.query
        };
        const self = this;
        await axios.get(window.FANDAQAH_API_URL + `/transactions/insurance-transactions` , config)
        .then((response) => {
            self.withdraw_insurance_transactions = response.data.withdraw_insurance_transactions;
            self.deposit_insurance_transactions = response.data.deposit_insurance_transactions;
        });

            axios.get(`/nova-vendor/calender/term?id=${this.kind}`)
                .then((res)=>{
                    this.description = res.data.name[this.local]
                    this.termName = res.data.name['ar']

                    this.hideAddBtn = false;
                    this.disableFields = false;
                    if(this.termName == 'تامين'){

                        if(this.transaction.transaction_type == 'deposit'){
                            if(this.deposit_insurance_transactions.length && this.withdraw_insurance_transactions.length){
                                this.$toasted.show(this.__('We can not add new insurance transaction cause there is already one added'), {type: 'error'});
                                this.hideAddBtn = true;
                                return;
                            }
                        }
                    }

                    if(this.termName == 'استرجاع تامين'){

                        if(!this.deposit_insurance_transactions.length){
                            this.$toasted.show(this.__('We can not add retrieval insurance transaction cause there is no insurance transaction added'), {type: 'error'});
                            this.hideAddBtn = true;
                            return;
                        }

                        if(this.withdraw_insurance_transactions.length && this.deposit_insurance_transactions.length){

                            this.$toasted.show(this.__('We can not add new retrieval insurance transaction cause there is already one added'), {type: 'error'});
                            this.hideAddBtn = true;
                            return;
                        }

                        if(this.deposit_insurance_transactions.length){

                            if(Math.abs(this.amount) != this.deposit_insurance_transactions[0].amount / (this.transaction.wallet_decimal_places == 2 ? 100 : 1000)){
                                this.$toasted.show(this.__('We can not change this transaction to retrieval insurance transaction'), {type: 'error'});
                                this.hideAddBtn = true;
                                return;
                            }

                        }
                    }

                });
        },
        async fillTermName(){
           let config = {
            headers : {
                'x-team' : this.teamId,
                'x-res' : this.transaction.reservation_id,
                'x-localization' : this.local
            },
            params : this.$route.query
        };
        const self = this;
        await axios.get(window.FANDAQAH_API_URL + `/transactions/insurance-transactions` , config)
        .then((response) => {
            self.withdraw_insurance_transactions = response.data.withdraw_insurance_transactions;
            self.deposit_insurance_transactions = response.data.deposit_insurance_transactions;
        });
        Nova.request().get(`/nova-vendor/calender/term?id=${this.kind}`)
                    .then(res =>{

                        this.termName = res.data.name['ar'];
                        this.disableFields = false;
                        this.hideAddBtn = false;

                        if(this.transaction.transaction_type == 'deposit'){
                            // hit an endpoint to get withdraw_insurance_transactions for current reservation transaction


                            if(this.termName == 'تامين'  && this.transaction.reservation_id &&  this.withdraw_insurance_transactions.length){
                                    this.disableFields = true;
                                    return;
                            }
                        }
                        if(this.transaction.transaction_type == 'withdraw'){
                            if(this.termName == 'استرجاع تامين' && this.transaction.transaction_is_insurance && this.deposit_insurance_transactions.length){
                                 this.disableFields = true;
                            }
                        }




                    });
        },
        checkPaymentType() {
            if (this.payment_type == 'cash' || this.payment_type == 'credit-payment') {
                this.ref = false;
                if(this.payment_type == 'credit-payment' && this.transaction.transaction_type == 'deposit'){
                    this.getEmployees();
                }
            } else {
                this.ref = true;
            }
        },
        clearData(){

        },
        async fillData(){


            if(this.transaction.transaction_type == 'withdraw'){
               await this.getVatSetting();
            }
            this.hideAddBtn = false;
            this.disableFields = false;
            this.getTerms(this.transaction.transaction_type == 'deposit' ? 2 : 1);
            if(Nova.app.$hasPermission('edit financial') && Nova.app.$hasPermission('change transactions date')){
                this.canChangeTransactionDate = true;
            }

            if(this.transaction.transaction_type == 'deposit'){
                this.getEmployees();
                this.kind = this.transaction.transaction_term;
                this.from = this.transaction.transaction_received_from;
                this.date = this.transaction.transaction_date_receipt;
                this.amount = Math.abs(this.transaction.transaction_amount / (this.transaction.wallet_decimal_places == 2 ? 100 : 1000)).toFixed(2);
                this.payment_type = this.toLower(this.transaction.transaction_payment_method);
                if(this.transaction.transaction_statement == 'various_services'){
                    this.description = this.__('Various services')
                }else{
                    this.description = this.transaction.transaction_statement;
                }
                this.note = this.transaction.transaction_note != 'null' ? this.transaction.transaction_note : null;
                this.reference = this.transaction.transaction_reference != 'null' ? this.transaction.transaction_reference : null;
                this.ref = false;
            }



            if(this.transaction.transaction_type == 'withdraw'){



                if(parseFloat(this.transaction.transaction_enable_tax_on_withdraw)){
                    this.enable_tax_on_withdraw = parseFloat(this.transaction.transaction_enable_tax_on_withdraw);
                }else{
                    // by default disable the vat on transaction with no previous vat
                    this.enable_tax_on_withdraw = false;
                    this.amount = Math.abs(this.transaction.transaction_amount / ( parseInt(this.transaction.wallet_decimal_places) == 2 ? 100 : 1000));

                    this.amount_include_tax = null;
                    this.tax_amount = null;
                }

                this.disable_amount_include_tax = !this.enable_tax_on_withdraw ? true : false;
                this.disable_supplier_tax_number = !this.enable_tax_on_withdraw ? true : false;
                this.disable_invoice_number = !this.enable_tax_on_withdraw ? true : false;

                this.supplier_tax_number = this.transaction.transaction_supplier_tax_number;
                this.invoice_number = this.transaction.transaction_invoice_number;
                this.kind = this.transaction.transaction_term;
                this.from = this.transaction.transaction_received_from;
                this.date = this.transaction.transaction_date_receipt;
                if(parseFloat(this.transaction.transaction_tax_percentage)){
                    this.amount = Math.abs(parseFloat(this.transaction.transaction_amount_without_tax) / (parseInt(this.transaction.wallet_decimal_places) == 2 ? 100 : 1000));
                    this.amount_include_tax = Math.abs(parseFloat(this.transaction.transaction_amount) / (parseInt(this.transaction.wallet_decimal_places) == 2 ? 100 : 1000));
                    this.tax_amount = Math.abs(parseFloat(this.transaction.transaction_tax_amount) / (parseInt(this.transaction.wallet_decimal_places) == 2 ? 100 : 1000));
                }


                if(this.tax_percentage && this.enable_tax_on_withdraw && !parseFloat(this.transaction.transaction_tax_percentage)){
                    this.amount = Math.abs(parseFloat(this.transaction.transaction_amount) / (parseInt(this.transaction.wallet_decimal_places) == 2 ? 100 : 1000));
                    this.amount_include_tax = parseFloat(this.amount) + parseFloat((this.amount * (this.tax_percentage / 100)));
                    this.tax_amount = parseFloat( parseFloat(this.amount_include_tax - this.amount).toFixed(2));
                }



                this.payment_type = this.toLower(this.transaction.transaction_payment_method);
                this.description = this.transaction.transaction_statement;
                this.note = this.transaction.transaction_note != 'null' ? this.transaction.transaction_note : null;
                this.reference = this.transaction.transaction_reference != 'null' ? this.transaction.transaction_reference : null;
                this.received_by = this.transaction.transaction_received_by;
                this.ref = false;

            }

            this.unit_category_id = this.transaction.unit_category_id ? this.transaction.unit_category_id : '';
            this.fillTermName();

            if (this.payment_type == 'cash' || this.payment_type == 'credit-payment') {
                this.ref = false;
            }else{
                this.ref = true ;
            }

            let self = this;
            this.$nextTick(() => {
                this.flatpickr = flatpickr(this.$refs.datePicker, {
                    enableTime: true,
                    enableSeconds: false,
                    onClose: this.handleChange,
                    dateFormat: this.dateFormat,
                    allowInput: false,
                    maxDate : new Date(),
                    mode: 'single',
                    time_24hr: true,
                    onReady() {
                        self.$refs.datePicker.parentNode.classList.add('date-filter')
                    },
                    "locale": self.local == 'en' ? 'en' :  Arabic
                })

            })

        },
        getTerms(type){
             this.terms = [];
             const self = this;
            axios.get(`/nova-vendor/financial-management/get-terms?type=${type}`)
                .then(res => {;
                    this.terms = res.data;


                    this.filteredTerms = this.terms.filter(function (term) {
                        if(self.transaction.transaction_payable_type == 'App\\Reservation' && self.transaction.transaction_type == 'deposit'){
                            return term.name['ar'] != 'تحويل من الادارة الى الصندوق';
                        }

                        if(self.transaction.transaction_payable_type == 'App\\Reservation' && self.transaction.transaction_type == 'withdraw'){
                            return term.name['ar'] != 'تحويل من الصندوق الى الادارة';
                        }
                        return term;
                    });

                })
        },

        send() {


            if(this.transaction.transaction_type == 'deposit'){

                if (!this.kind) {
                    this.$toasted.show(this.__("Please Enter Type!"), {type: 'error'})
                    return
                }
                if (!this.date) {
                    this.$toasted.show(this.__("Please Enter Date!"), {type: 'error'})
                    return
                }
                if (!this.amount) {
                    this.$toasted.show(this.__("Please Enter Amount!"), {type: 'error'})
                    return
                }

                if (this.amount <= 0) {
                    this.$toasted.show(this.__("Amount is not valid"), {type: 'error'})
                    return
                }

                if (!this.description) {
                    this.$toasted.show(this.__("Please Enter For!"), {type: 'error'})
                    return
                }
                if (!this.payment_type) {
                    this.$toasted.show(this.__("Please Enter Payment Type!"), {type: 'error'})
                    return
                }
                this.loading = true;

                if(this.transaction.transaction_payable_type == 'App\\Team'){
                    // it's a transaction not related to any reservation
                    axios
                        .post('/nova-vendor/financial-management/updateTransaction', {
                            id: this.transaction.transaction_id,
                            amount: this.amount,
                            type: 'deposit',
                            termName :  this.termName,
                            meta: {
                                category: this.transaction.transaction_category,
                                statement: this.description,
                                type: this.kind,
                                payment_type: this.payment_type,
                                note: this.note,
                                reference: this.reference,
                                date: this.date ,
                                from: this.from,
                                employee : Nova.config.user.name,
                                person_in_charge : this.person_in_charge
                            },
                            unit_category_id : this.unit_category_id
                        })
                        .then(response => {
                            Nova.$emit('transaction-updated')
                            this.$refs.editModal.close();
                            this.$toasted.show(this.__('Transaction updated successfully'), {type: 'success'});
                            this.loading = false;
                        }).catch(err => {
                        this.loading = false;
                        // Check if the response exists and has a message
                        if (err.response && err.response.data && err.response.data.message) {
                            this.$toasted.show(this.__(err.response.data.message), { type: 'error' });
                        } else {
                            this.$toasted.show(this.__('An error occurred while updating the transaction'), { type: 'error' });
                        }
                    })
                }else{
                    // it's normal reservation transaction
                    axios
                        .put('/nova-vendor/calender/reservation/transaction', {
                            id: this.transaction.transaction_id,
                            amount: this.amount,
                            type: 'deposit',
                            termName :  this.termName,
                            meta: {
                                category: 'reservation',
                                statement: this.description,
                                type: this.kind,
                                payment_type: this.payment_type,
                                note: this.note,
                                reference: this.reference,
                                date: this.date ,
                                from: this.from,
                                employee : Nova.config.user.name
                            }
                        })
                        .then(response => {
                            if(response.data && response.data.status == 'no-fulfill-available'){
                                this.$toasted.show(this.__('Can not modify this transaction because it is a promissory transaction the due amount is zero'), {type: 'error'});
                                return false;
                            }

                            if(response.data && response.data.status == 'error'){
                                this.$toasted.show(this.__('Amount Exceeded the maximum remaining amount to add to current amount is  :amount SAR' , {amount : response.data.remainingMaxFulfillAmount}), {type: 'error'});
                                return false;
                            }

                            Nova.$emit('transaction-updated');
                            this.$refs.editModal.close();
                            this.$toasted.show(this.__('Transaction updated successfully'), {type: 'success'});
                            this.loading = false;
                        }).catch(err => {
                        this.loading = false;
                        if (err.response && err.response.data && err.response.data.message) {
                            this.$toasted.show(this.__(err.response.data.message), { type: 'error' });
                        } else {
                            this.$toasted.show(this.__('An error occurred while updating the transaction'), { type: 'error' });
                        }
                    })
                }

            }else{
                // this is withdraw
                if(!this.kind){
                    this.$toasted.show(this.__("Please Enter Type!"), {type: 'error'})
                    return
                }
                if(!this.date){
                    this.$toasted.show(this.__("Please Enter Date!"), {type: 'error'})
                    return
                }
                if(!this.from){
                    this.$toasted.show(this.__("Please Enter from!"), {type: 'error'})
                    return
                }
                if(!this.amount){
                    this.$toasted.show(this.__("Please Enter Amount!"), {type: 'error'})
                    return
                }
                if(!this.description){
                    this.$toasted.show(this.__("Please Enter For!"), {type: 'error'})
                    return
                }
                if(!this.received_by){
                    this.$toasted.show(this.__("Please Enter Received By!"), {type: 'error'})
                    return
                }
                if(!this.payment_type){
                    this.$toasted.show(this.__("Please Enter Payment Type!"), {type: 'error'})
                    return
                }

                this.loading = true;

                if(this.transaction.transaction_payable_type == 'App\\Team'){
                    // transaction without reservation
                    axios
                        .post('/nova-vendor/financial-management/updateTransaction', {
                            id: this.transaction.transaction_id,
                            amount: this.amount,
                            type: 'withdraw',
                            termName :  this.termName,
                            meta: {
                                category: 'team',
                                statement: this.description,
                                type: this.kind,
                                payment_type: this.payment_type,
                                note: this.note,
                                reference: this.reference,
                                date: this.date ,
                                from: this.from,
                                received_by: this.received_by,
                                employee : Nova.config.user.name
                            },
                            amount_include_tax : this.amount_include_tax,
                            tax_amount : this.tax_amount,
                            supplier_tax_number : this.supplier_tax_number,
                            invoice_number : this.invoice_number,
                            enable_tax_on_withdraw : this.enable_tax_on_withdraw,
                            tax_percentage : parseFloat(this.transaction.transaction_tax_percentage) ? parseFloat(this.transaction.transaction_tax_percentage) : this.tax_percentage,
                            unit_category_id : this.unit_category_id
                        })
                        .then(response => {
                            Nova.$emit('transaction-updated')
                            this.$refs.editModal.close();
                            this.$toasted.show(this.__('Transaction updated successfully'), {type: 'success'});
                            this.loading = false;
                        }).catch(err => {
                        this.loading = false;
                        if (err.response && err.response.data && err.response.data.message) {
                            this.$toasted.show(this.__(err.response.data.message), { type: 'error' });
                        } else {
                            this.$toasted.show(this.__('An error occurred while updating the transaction'), { type: 'error' });
                        }
                    })
                }else{

                    // it's normal reservation transaction
                    axios
                        .put('/nova-vendor/calender/reservation/transaction', {
                            id: this.transaction.transaction_id,
                            amount: this.amount,
                            type: 'withdraw',
                            termName :  this.termName,
                            meta: {
                                category: 'reservation',
                                statement: this.description,
                                type: this.kind,
                                payment_type: this.payment_type,
                                note: this.note,
                                reference: this.reference,
                                date: this.date,
                                received_by: this.received_by,
                                from: this.from,
                                employee : Nova.config.user.name
                            }
                        })
                        .then(response => {
                            Nova.$emit('transaction-updated')
                            this.$refs.editModal.close();
                            this.$toasted.show(this.__('Transaction updated successfully'), {type: 'success'});
                            this.loading = false;
                        }).catch(err => {
                        this.loading = false;
                        if (err.response && err.response.data && err.response.data.message) {
                            this.$toasted.show(this.__(err.response.data.message), { type: 'error' });
                        } else {
                            this.$toasted.show(this.__('An error occurred while updating the transaction'), { type: 'error' });
                        }
                    })
                }


            }




        },
        capitalize(label){
            if (typeof label !== 'string') return ''
            return label.charAt(0).toUpperCase() + label.slice(1)
        },
        toLower(label){
            if (typeof label !== 'string') return ''
            return label.charAt(0).toLowerCase() + label.slice(1)
        },
        getVatSetting(){
            axios.get('/nova-vendor/financial-management/vat-setting')
            .then(response => {
                this.tax_percentage = response.data.vat;
                if(this.tax_percentage) {
                    // this.enable_tax_on_withdraw = true;
                    if(this.enable_tax_on_withdraw && !this.transaction.transaction_tax_percentage){
                        this.amount = Math.abs(this.transaction.transaction_amount / (this.transaction.wallet_decimal_places == 2 ? 100 : 1000));
                        this.amount_include_tax = parseFloat(this.amount) + parseFloat((this.amount * (this.tax_percentage / 100)));
                        this.tax_amount = parseFloat( parseFloat(this.amount_include_tax - this.amount).toFixed(2));
                    }
                }
            })
        }
    },
    mounted() {
        this.calendarLocale = Nova.config.local;
        this.local = Nova.config.local;

        Nova.$on('from-change' , (val) => {
                this.date = val ;
        });
    }
}
</script>

<style lang="scss">

.switch {
        position: relative;
        display: inline-block;
        width: 65px;
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

    .options_choose {
        display: flex;
        align-items: center;
        justify-content: flex-start;
        flex-wrap: wrap;

        b {
            display: block;
            font-weight: normal;
            font-size: 15px;
            margin: 0 0 10px;
        }

        .switch_label {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            margin: 5px;

            p {
                display: block;
                margin: 0 0 0 10px;
                font-size: 15px;
                color: #000;
            }
        }
    }

.Edit_Transaction_Modal {
  .sweet-content {
    overflow: auto;
    // max-height: calc(100vh - 6rem);
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
    &:disabled{
        background: #d6d6d6 !important;
    }
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
