<template>
  <div class="item_reservation_button">
    <button v-if="from != 'payment_voucher'" v-permission="'check-out customer'" class="main_button" @click="open">
        {{__('Check Out')}}
    </button>
    <sweet-modal :enable-mobile-fullscreen="false" :pulse-on-block="false" :title="__('Check Out')" overlay-theme="dark" ref="modal" class="checkout_customer_modal" @close="modalClosed" @open="modalOpened">
      <loading :active.sync="isLoading" :is-full-page="false"></loading>
      <div class="alert_balance" v-if="reservation.balance != 0">
        <div v-if="reservation.balance != 0">
          {{ __('You have not filtered for this booking - Balance') }} :
          <span :class="{ 'text-success': reservation.balance > 0 ,'text-danger': reservation.balance < 0 }">
            {{ (Math.abs(reservation.balance / (reservation.wallet.decimal_places == 3 ?  1000 : 100)).toFixed(2) )}} <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span>
            <p :class="{ 'text-success': reservation.balance > 0 ,'text-danger': reservation.balance < 0 }">{{ (reservation.balance  > 0) ? '('+__('credit')+')'  : ''}} {{ (reservation.balance  < 0) ? '('+__('debit')+')' : ''}}</p></span>
        </div>

      </div><!-- alert_balance -->
      <div v-if="reservation.balance < 0">
        <label class="transaction_title">سند قبض - تصفية حساب</label>
        <!-- <checkout-cash-receipt :terms="terms" :reservation="reservation" :request_type="'checkout'" :balance="balance" ref="receipt"></checkout-cash-receipt> -->

        <!-- There will be a deposit transaction -->
        <div class="transaction_form">
            <div class="formgroup">
            <label>{{__('Liquidation Type')}}<span>*</span></label>
            <select v-model="liquidationType">
                <option value="transaction"  :selected="liquidationType == 'transaction'"> {{__('Deposit Transaction')}}</option>
                <option v-if="can_add_promissory" value="promissory"> {{__('Promissory')}}</option>
            </select>
            </div><!-- formgroup -->
            <div class="row_group" v-if="liquidationType == 'promissory'">
            <div class="col">
                <label>{{__('Due Location')}}<span>*</span></label>
                <input type="text" v-model="due_location"  :placeHolder="__('Due Location')">
            </div><!-- col -->
            <div class="col">
                <label>{{__('Amount Due')}}<span>*</span></label>
                <input type="text" v-model="due_owner"  :placeHolder="__('Amount Due')">
            </div><!-- col -->
            </div>

            <div class="formgroup" v-if="liquidationType != 'promissory'">
            <label>{{__('Type')}}<span>*</span></label>
            <select v-model="selected_term">
                <option value="" disabled selected> {{__('Select Option')}}</option>
                <option v-for="(term,index) in reservation.team.terms"
                    :key="index"
                    :value="term.id"
                    v-if="term.deleteable == 1 && term.type == 2"
                >{{term.name[locale]}}
                </option>
            </select>
            </div><!-- formgroup -->
            <div class="row_group">
            <div class="col">
                <label v-if="liquidationType == 'promissory'">{{__('Due Date')}}<span>*</span></label>
                <label v-else>{{__('Date')}}<span>*</span></label>
                <vcc-date-picker
                :input-props='{readonly: true}'
                mode='single'
                :value="new Date()"
                show-caps
                v-model="selected_date"
                :locale="locale"
                :max-date="liquidationType == 'promissory' ? null : new Date()"
                :min-date="liquidationType == 'promissory' ? new Date() : null"
                :masks="{ dayPopover : 'WWW, MMM D, YYYY' , weekdays: 'WWW' }"
                :popover="{ placement: 'bottom', visibility: liquidationType == 'promissory' ? 'click' : 'hidden' }"
                >
                </vcc-date-picker>
            </div><!-- col -->
            <div class="col" v-if="liquidationType != 'promissory'">
                <label>{{__('From')}}<span>*</span></label>
                <input type="text" v-model="transaction_from" ref="from" :placeHolder="__('From')">
            </div><!-- col -->
            <div class="col" v-if="liquidationType == 'promissory'">
                <label>{{__('Amount')}}<span>*</span></label>
                <input
                    type="text"
                    oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"
                    v-model="amount"
                    disabled="disabled"
                    :placeHolder="__('Amount')"
                >
            </div><!-- col -->
            </div><!-- row_group -->

            <div class="row_group mb-3" v-if="liquidationType == 'promissory'">
                <label>{{__('For')}}<span>*</span></label>
                <input type="text" v-model="due_for"  :placeHolder="__('And That For')">
            </div>
            <div class="row_group">
            <div class="col" v-if="liquidationType != 'promissory'">
                <label>{{__('Amount')}}<span>*</span></label>
                <input
                    type="text"
                    oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"
                    v-model="amount"
                    disabled="disabled"
                    :placeHolder="__('Amount')"
                >
            </div><!-- col -->
            <div class="col" v-if="liquidationType != 'promissory'">
                <label>{{__('For')}}<span>*</span></label>
                <input type="text" v-model="description" :placeHolder="__('For')">
            </div><!-- col -->
            </div><!-- row_group -->
            <div class="row_group">
            <div class="col" v-if="liquidationType != 'promissory'">
                <label>{{__('Payment Type')}}<span>*</span></label>
                <select :placeholder="__('Payment Type')" v-model="payment_type">
                <option value="" disabled selected>{{__('Select Payment Type')}}</option>
                <option value="cash">{{__('Cash')}}</option>
                <option value="bank-transfer">{{__('Bank Transfer')}}</option>
                <option value="mada">{{__('Mada')}}</option>
                <option value="credit">{{__('Credit Card')}}</option>
                </select>
            </div><!-- col -->
            <div class="col" v-if="liquidationType != 'promissory' && payment_type && payment_type != 'cash'">
                <label>{{__('Payment Reference')}}</label>
                <input type="text" v-model="reference" :placeHolder="__('Payment Reference')">
            </div><!-- col -->
            </div><!-- row_group -->
            <div class="formgroup">
            <label>{{__('Notes')}}</label>
            <textarea type="text" class="textarea" v-model="note" :placeHolder="__('Notes')"> </textarea>
            </div><!-- formgroup -->
        </div>

      </div>
      <div v-if="reservation.balance > 0">
        <label class="transaction_title">سند صرف - تصفية حساب</label>
        <!-- <checkout-payment-voucher :reservation="reservation" :terms="terms" :request_type="'checkout'" :balance="balance" ref="payment"></checkout-payment-voucher> -->

        <!-- There will be a withdraw transaction  -->
        <div class="transaction_form">
            <div class="formgroup">
            <label>{{__('Type')}}<span>*</span></label>
            <select v-model="selected_term">
                <option value="" disabled selected>{{__('Select Option')}}</option>
                <option
                        v-for="(term,index) in reservation.team.terms"
                        :key="index"
                        :value="term.id"
                        v-if="term.deleteable == 1 && term.type == 1"
                >{{term.name[locale]}}
                </option>
            </select>
            </div><!-- formgroup -->
            <div class="row_group">
            <div class="col">
                <label>{{__('Date')}} <span>*</span></label>
                <vcc-date-picker
                :input-props='{ readonly: true }'
                mode='single'
                :value='new Date()'
                show-caps
                v-model="selected_date"
                :max-date='new Date()'
                :locale="locale"
                :masks="{ dayPopover : 'WWW, MMM D, YYYY' , weekdays: 'WWW' }"
                :popover="{ placement: 'bottom', visibility: 'hidden' }"
                >
                </vcc-date-picker>
            </div><!-- col -->
            <div class="col">
                <label>{{__('To')}} <span>*</span></label>
                <input type="text" v-model="transaction_to" ref="to" :placeHolder="__('To')">
            </div><!-- col -->
            </div><!-- row_group -->
            <div class="row_group">
            <div class="col">
                <label>{{__('Amount')}} <span>*</span></label>
                <input type="number" v-model="amount" disabled="disabled" :placeHolder="__('Amount')">
            </div><!-- col -->
            <div class="col">
                <label>{{__('For')}} <span>*</span></label>
                <input type="text" v-model="description" :placeHolder="__('For')">
            </div><!-- col -->
            </div><!-- row_group -->
            <div class="row_group" style="margin-bottom:10px;">
                <label>{{__('Received By')}}<span>*</span></label>
                <input type="text" v-model="received_by" :placeHolder="__('Received By')">
            </div><!-- row_group -->
            <div class="row_group">
                <div class="col">
                    <label>{{__('Payment Type')}} <span>*</span></label>
                    <select placeholder="Payment Type" v-model="payment_type">
                    <option value="" disabled selected>{{__('Select Payment Type')}}</option>
                    <option value="cash">{{__('Cash')}}</option>
                    <option value="bank-transfer">{{__('Bank Transfer')}}</option>
                    <option value="mada">{{__('Mada')}}</option>
                    <option value="credit">{{__('Credit Card')}}</option>
                    </select>
                </div><!-- col -->
                <div class="col" v-if="liquidationType != 'promissory' && payment_type && payment_type != 'cash'">
                    <label>{{__('Payment Reference')}}</label>
                    <input type="text" v-model="reference" :placeHolder="__('Payment Reference')">
                </div><!-- col -->
            </div><!-- row_group -->
            <div class="row_group">
                <label>{{__('Notes')}}</label>
                <textarea type="text" class="textarea" v-model="note" :placeHolder="__('Notes')"> </textarea>
            </div>
        </div>

      </div>


      <!-- <label v-if="canConvertUnitToUnderCleaning" class="cleaning_msg">
        <div v-if="automatic_under_cleaning">
            <span>{{__('Unit status will be changed to under cleaning automatically')}}</span>
        </div>
        <div v-else>
             <input type="checkbox" v-model="cleaning">
            <span>{{__('Unit conversion to condition: Under cleaning when recording customer leaving')}}</span>
        </div>

      </label>cleaning_msg -->
      <div class="form_group">
        <label>{{__('Check-out time')}} </label>
        <input type="time" :disabled="!canEditCheckinCheckoutTime" v-model="time" :placeholder="__('Name')"/>
      </div><!-- form_group -->

      <div class="liquidation_receivables" v-if="reservation.balance != 0">
        <!-- Here we will show create promissory button or ( checkout buttons with or without liquidations based on can_make_simple_checkout data value ) -->
        <template v-if="liquidationType == 'transaction'">
            <button
                @click="createTransactionAndCheckout"
                :disabled="disable_action_btn"
                class="shadow mb-4 mt-2 text-base"
                :class="!can_make_simple_checkout ? 'fullWidthBtn' : ''"
            >
                {{__('Checkout and Liquidation')}}
            </button>
            <button
                v-if="can_make_simple_checkout"
                :disabled="disable_action_btn"
                @click="createSimpleCheckout"
                class="shadow mb-4 mt-2 text-base"
            >
                {{__('Checkout without Liquidation')}}
            </button>
        </template>
        <!-- Create a promissory if liquidation type is promissory  -->
        <template v-else>
            <button
                @click="createPromissoryAndCheckout"
                :disabled="disable_action_btn"
                class="shadow mb-4 mt-2 text-base fullWidthBtn"
            >
                {{__('Checkout & Add Promissory')}}
            </button>
        </template>
      </div><!-- liquidation_receivables -->
      <div v-else>
            <button
                @click="createSimpleCheckout"
                :disabled="disable_action_btn"
                class="shadow mb-4 mt-2 text-base fullWidthBtn actionBtn"
            >
                {{__('Make Checkout')}}
            </button>
      </div>
    </sweet-modal>

    <!-- Insurance Retrieval Modal -->
    <sweet-modal :enable-mobile-fullscreen="false" :pulse-on-block="false" :title="__('Insurance retrieval')" overlay-theme="dark" ref="insuranceRetrievalModal" class="comments_modal">
        <loading :active.sync="isAddingInsuranceRetrieval" :is-full-page="false"></loading>
        <div class="transaction_form">
             <div class="alert_insurance">
                {{ __('There is a :amount insurance amount and you must create a retrieval insurance for it' , {amount : insuranceAmountDue}) }}
            </div>
            <div class="formgroup">
            <label>{{__('Type')}}<span>*</span></label>
            <select
                v-model="selected_term"
                :disabled="disableFieldsForInsurance"
            >
                <option value="" disabled selected>{{__('Select Option')}}</option>
                <option
                        v-for="(term,index) in reservation.team.terms"
                        :key="index"
                        :value="term.id"
                        v-if="term.type == 1"
                >{{term.name[locale]}}
                </option>
            </select>
            </div><!-- formgroup -->
            <div class="row_group">
            <div class="col">
                <label>{{__('Date')}} <span>*</span></label>
                <input type="text" :disabled="!canChangeTransactionDate || disableFields"
                    v-model="selected_date"
                    :placeHolder="__('To')"
                />

            </div><!-- col -->
            <div class="col">
                <label>{{__('To')}} <span>*</span></label>
                <input type="text" :disabled="disableFields" v-model="transaction_to" ref="to" :placeHolder="__('To')">
            </div><!-- col -->
            </div><!-- row_group -->
            <div class="row_group">
            <div class="col">
                <label>{{__('Amount')}} <span>*</span></label>
                <input type="number" :disabled="disableFields" v-model="amount" disabled="disabled" :placeHolder="__('Amount')">
            </div><!-- col -->
            <div class="col">
                <label>{{__('For')}} <span>*</span></label>
                <input type="text" :disabled="disableFields" v-model="description" :placeHolder="__('For')">
            </div><!-- col -->
            </div><!-- row_group -->
            <div class="row_group" style="margin-bottom:10px;">
                <label>{{__('Received By')}}<span>*</span></label>
                <input type="text" :disabled="disableFields" v-model="received_by" :placeHolder="__('Received By')">
            </div><!-- row_group -->
            <div class="row_group">
                <div class="col">
                    <label>{{__('Payment Type')}} <span>*</span></label>
                    <select placeholder="Payment Type" v-model="payment_type">
                    <option value="" disabled selected>{{__('Select Payment Type')}}</option>
                    <option value="cash">{{__('Cash')}}</option>
                    <option value="bank-transfer">{{__('Bank Transfer')}}</option>
                    <option value="mada">{{__('Mada')}}</option>
                    <option value="credit">{{__('Credit Card')}}</option>
                    </select>
                </div><!-- col -->
                <div class="col" v-if="liquidationType != 'promissory' && payment_type && payment_type != 'cash'">
                    <label>{{__('Payment Reference')}}</label>
                    <input type="number" v-model="reference" :placeHolder="__('Payment Reference')">
                </div><!-- col -->
            </div><!-- row_group -->
            <div class="row_group">
                <label>{{__('Notes')}}</label>
                <textarea type="text" class="textarea" v-model="note" :placeHolder="__('Notes')"> </textarea>
            </div>

             <button class="shadow mb-2  btn btn-block btn-primary mt-2"  @click="createRetrievalTransaction">{{__('Save')}}</button>
        </div>
    </sweet-modal>
  </div>
</template>

<script>
    import Loading from 'vue-loading-overlay';
    import momenttimezone from 'moment-timezone';
    import 'vue-loading-overlay/dist/vue-loading.css';
    export default {
        name: "check-out",
        props : ['reservation','from'],
        components : {Loading},
        data: () => {
            return {
                locale: Nova.config.local,
                time: momenttimezone().tz("Asia/Riyadh").format('HH:mm'),
                cleaning: false,
                settings: null,
                invoices : [],
                showAutomatedInvoiceNotification : false,
                isLoading : false,
                spinnerLoad : false,
                canConvertUnitToUnderCleaning : true,
                can_make_simple_checkout : false,
                liquidationType : 'transaction',
                automatic_under_cleaning : null,
                canEditCheckinCheckoutTime : true,
                due_location :  null,
                due_owner : null,
                due_for : null,
                transaction_from: null,
                transaction_to: null,
                amount: null,
                payment_type: null,
                description: null,
                note: null,
                reference: null,
                currency :Nova.app.currentTeam.currency,
                selected_term: null,
                selected_term_type: null,
                selected_date :  moment().toDate(),
                received_by: null,
                disableFields : false,
                disableFieldsForInsurance : false,
                insuranceAmountDue : 0,
                employee : Nova.config.user.name,
                isAddingInsuranceRetrieval : false,
                currentUser : Nova.config.user,
                currentTeamId : Nova.config.user.current_team_id,
                disable_action_btn: false,
                can_add_promissory : false,
                hasPermissionToAddDeposit: false,
                hasPermissionToAddWithdraw: false
            }
        },
        methods: {
            preCheckFutureReservation(){
                axios.get(`/nova-vendor/calender/check-convert-unit-to-under-cleaning?unit_id=${this.reservation.unit_id}&reservation_id=${this.reservation.id}`)
                    .then(response => {

                        // if(response.data.count > 0){
                        //     this.canConvertUnitToUnderCleaning = false;
                        // }else{
                        //     this.canConvertUnitToUnderCleaning = true;
                        this.automatic_under_cleaning = response.data.automatic_under_cleaning;
                        if(this.automatic_under_cleaning){
                            this.cleaning = true;
                        }
                        // }

                    })
            },
            open() {
                const self = this;
                if(Nova.app.$hasPermission('liquidation of dues before departure')){
                   this.can_make_simple_checkout = true;
                }else{
                    this.can_make_simple_checkout = false;
                }

                if (Nova.app.$hasPermission('liquid reservation with promissory')) {
                    this.can_add_promissory = true;
                }
                 /**
                 * Before openning checkout modal , we must check if there is any insurance transaction or not and if so , i should give him the modal of retrieving the insurrance
                 * so instead of openning the checkout modal , i will open the retrieval inssurance modal  -_-
                 *
                 */
                if(this.reservation.deposit_insurance_transactions.length && !this.reservation.withdraw_insurance_transactions.length){
                    // this means that there is an insurance transaction

                    this.transaction_from = this.reservation.deposit_insurance_transactions[0].meta && this.reservation.deposit_insurance_transactions[0].meta.from ? this.reservation.deposit_insurance_transactions[0].meta.from :  this.reservation.customer.name;

                    this.transaction_to = this.reservation.deposit_insurance_transactions[0].meta && this.reservation.deposit_insurance_transactions[0].meta.from ? this.reservation.deposit_insurance_transactions[0].meta.from :  this.reservation.customer.name;

                    this.received_by = this.reservation.deposit_insurance_transactions[0].meta && this.reservation.deposit_insurance_transactions[0].meta.received_by ? this.reservation.deposit_insurance_transactions[0].meta.received_by :  this.reservation.customer.name;
                    // this.date = this.reservation.deposit_insurance_transactions[0].meta.date;
                    this.amount = Math.abs(this.reservation.wallet.decimal_places == 3 ? this.reservation.deposit_insurance_transactions[0].amount / 1000 : this.reservation.deposit_insurance_transactions[0].amount / 100 );
                    this.payment_type = this.reservation.deposit_insurance_transactions[0].meta.payment_type;
                    // this.description = this.$parent.reservation.deposit_insurance_transactions[0].meta.statement;
                    this.note = this.reservation.deposit_insurance_transactions[0].meta.note;
                    this.reference = this.reservation.deposit_insurance_transactions[0].meta.reference;
                    this.disableFields = true;

                    $.each(this.reservation.team.terms, function(key , item) {
                        if(item.name['ar'] == "استرجاع تامين"){
                            self.kind = item.id;
                            self.description  = item.name[self.locale] + ' - ' + self.__('Unit') + ' - ' + self.reservation.unit.unit_number ;
                            self.termName = item.name['ar'];
                            self.selected_term = item.id;
                        }
                    });
                    this.disableFieldsForInsurance = true;
                    this.insuranceAmountDue = Math.abs(this.reservation.wallet.decimal_places == 3 ? this.reservation.deposit_insurance_transactions[0].amount / 1000 : this.reservation.deposit_insurance_transactions[0].amount / 100 );
                    this.insuranceWithdrawFromCheckout = true;
                    this.selected_date = moment().format('YYYY-MM-DD');
                    this.$refs.insuranceRetrievalModal.open();
                    return;
                }

                this.preCheckFutureReservation();
                this.time =  momenttimezone().tz("Asia/Riyadh").format('HH:mm');
                this.$refs.modal.open();
            },
            modalOpened(){
                this.due_location =  null;
                this.due_owner = null;
                this.due_for = null;
                this.transaction_from =   this.reservation.customer.name;
                this.transaction_to =   this.reservation.customer.name;
                this.received_by = this.reservation.customer.name;
                this.amount =  Math.abs(this.reservation.balance/ (this.reservation.wallet.decimal_places == 3 ? 1000 : 100)).toFixed(2);
                this.payment_type= null;
                this.description = null;
                this.note = null;
                this.reference = null;
                this.selected_term = null;
                this.selected_date = moment().toDate();
                this.cleaning = false;
                this.liquidationType = 'transaction';
                this.disable_action_btn = false;
            },
            createRetrievalTransaction(){
                this.isAddingInsuranceRetrieval = true;
                axios
                    .post('/nova-vendor/calender/reservation/transaction', {
                        id: this.reservation.id,
                        type: 'withdraw',
                        coming_from: 'retrieval-insurance',
                        amount: this.amount,
                        termName : this.termName,
                        meta: {
                            category: 'reservation',
                            statement: this.description,
                            type: this.selected_term,
                            payment_type: this.payment_type,
                            note: this.note,
                            reference: this.reference,
                            received_by: this.received_by,
                            from: this.transaction_from,
                            to: this.transaction_to,
                            employee: this.employee,
                        },
                        transaction_date : this.date
                    })
                    .then(response => {
                        this.reservation.transactions = response.data.reservationWithLoadedRelations.transactions;
                        this.reservation.withdraw_insurance_transactions = response.data.reservationWithLoadedRelations.withdraw_insurance_transactions;
                        this.$toasted.show(this.__('Retrieval insurance transaction has been created successfully'), {type: 'success'});
                        this.$refs.insuranceRetrievalModal.close();
                        this.isAddingInsuranceRetrieval = false;
                        this.open();
                        return;
                    })
            },
            createPromissoryAndCheckout(){
                if (!this.due_location)
                {
                    this.$toasted.show(this.__("Please Enter Due Location"), {type: 'error'})
                    return false;
                }
                if (!this.due_owner)
                {
                    this.$toasted.show(this.__("Please Enter Due Owner"), {type: 'error'})
                    return false;
                }

                if (!this.due_for)
                {
                    this.$toasted.show(this.__("Please Enter Due For"), {type: 'error'})
                    return false;
                }

                if (!this.time) {
                    this.$toasted.show(this.__('Please fill check out time'), {type: 'error'})
                    return;
                }
                // prepare promissory data alongside with checkout data
                let obj = {
                    due_location : this.due_location,
                    due_owner : this.due_owner,
                    due_date : moment(this.selected_date).format('YYYY-MM-DD'),
                    due_for : this.due_for,
                    total_amount : this.amount,
                    notes : this.note,
                    team_id : this.reservation.team_id,
                    user_id : this.currentUser.id,
                    customer_id : this.reservation.customer_id,
                    reservation_id : this.reservation.id,
                    time: this.time,
                    type: 'check-out',
                    reservation_type: this.reservation.reservation_type,
                    cleaning: this.cleaning,
                    liquidation_type : this.liquidationType,
                    from_date: this.reservation.date_in,
                    to_date: this.reservation.date_out,
                    services: this.reservation.services,
                    noteOnInvoice : this.__('Invoice on reservation number : ') + this.reservation.number
                };
                this.isLoading = true;
                this.disable_action_btn = true;
                Nova.request()
                    .post('/nova-vendor/calender/create-entity-and-checkout' , obj)
                    .then(response => {
                        this.isLoading = false;
                        this.disable_action_btn = false;
                        this.reservation.promissory = response.data.refilled_reservation.promissory;
                        this.reservation.invoices = response.data.refilled_reservation.invoices;
                        this.reservation.checked_out = response.data.refilled_reservation.checked_out;
                        this.reservation.services = response.data.refilled_reservation.services;
                        // to just refresh invoices
                        Nova.$emit('single_reservation_checked_out' , this.reservation.invoices);
                        this.$toasted.show(this.__('Checkout process & promissory creation went successfully'), {type: 'success'});
                        this.$refs.modal.close();
                        return;
                    })
            },
            createTransactionAndCheckout(){
                if(this.reservation.balance > 0){
                    if(!this.hasPermissionToAddWithdraw){
                        this.$toasted.show(this.__("No permission found to add withdraw transaction"), {type: 'error'})
                        return;
                    }
                }

                if(this.reservation.balance < 0) {
                    if(!this.hasPermissionToAddDeposit){
                        this.$toasted.show(this.__("No permission found to add deposit transaction"), {type: 'error'})
                        return;
                    }
                }
              
                if (!this.selected_term) {
                    this.$toasted.show(this.__("Please Enter Type!"), {type: 'error'})
                    return false;
                }
                if (!this.selected_date) {
                    this.$toasted.show(this.__("Please Enter Date!"), {type: 'error'})
                    return false;
                }
                if (!this.amount) {
                    this.$toasted.show(this.__("Please Enter Amount!"), {type: 'error'});
                    return false;
                }
                if(!this.transaction_from && this.selected_term_type == 'deposit'){
                    this.$toasted.show(this.__("Please Enter From!"), {type: 'error'})
                    return false;
                }

                 if(!this.transaction_to && this.selected_term_type == 'withdraw'){
                    this.$toasted.show(this.__("Please Enter To!"), {type: 'error'})
                    return false;
                }

                if(!this.received_by && this.selected_term_type == 'withdraw'){
                    this.$toasted.show(this.__("Please Enter Received By!"), {type: 'error'})
                    return false;
                }

                if (!this.description) {
                    this.$toasted.show(this.__("Please Enter For!"), {type: 'error'});
                    return false;
                }
                if (!this.payment_type) {
                    this.$toasted.show(this.__("Please Enter Payment Type!"), {type: 'error'});
                    return false;
                }
                if (!this.time) {
                    this.$toasted.show(this.__('Please fill check out time'), {type: 'error'})
                    return;
                }

                // prepare transaction data alongside with checkout data
                let obj = {
                    transaction_type:  this.selected_term_type,
                    amount: this.amount,
                    meta: {
                        category: 'reservation',
                        statement: this.description,
                        type: this.selected_term,
                        payment_type: this.payment_type,
                        note: this.note,
                        reference: this.reference,
                        date: this.selected_date + ' ' + new moment().format("HH:mm") ,
                        from: this.selected_term_type == 'deposit' ?  this.transaction_from : null,
                        to: this.selected_term_type == 'withdraw' ? this.transaction_to : null,
                        received_by: this.selected_term_type == 'withdraw' ? this.received_by : null,
                        employee:this.employee
                    },
                    team_id : this.currentTeamId,
                    user_id : this.currentUser.id,
                    customer_id : this.reservation.customer_id,
                    reservation_id : this.reservation.id,
                    time: this.time,
                    checkout_type: 'transaction',
                    type: 'check-out',
                    reservation_type: this.reservation.reservation_type,
                    cleaning: this.cleaning,
                    liquidation_type : this.liquidationType,
                    from_date: this.reservation.date_in,
                    to_date: this.reservation.date_out,
                    services: this.reservation.services,
                    noteOnInvoice : this.__('Invoice on reservation number : ') + this.reservation.number
                };

                this.isLoading = true;
                this.disable_action_btn = true;
                Nova.request()
                    .post('/nova-vendor/calender/create-entity-and-checkout' , obj)
                    .then(response => {
                        this.isLoading = false;
                        this.disable_action_btn = false;
                        this.reservation.invoices = response.data.refilled_reservation.invoices;
                        this.reservation.transactions = response.data.refilled_reservation.transactions;
                        this.reservation.checked_out = response.data.refilled_reservation.checked_out;
                        this.reservation.balance = parseFloat(response.data.refilled_reservation.balance).toFixed(2);
                        this.reservation.services = response.data.refilled_reservation.services;
                        // to just refresh invoices
                        Nova.$emit('single_reservation_checked_out' , this.reservation.invoices);
                        this.$toasted.show(this.__('Checkout process & transaction creation went successfully'), {type: 'success'});
                        this.$refs.modal.close();
                        return;
                    })

            },
            createSimpleCheckout(){
                if (!this.time) {
                    this.$toasted.show(this.__('Please fill check out time'), {type: 'error'})
                    return;
                }

                // prepare transaction data alongside with checkout data
                let obj = {
                    transaction_type:  this.selected_term_type,
                    amount: this.amount,
                    team_id : this.currentTeamId,
                    user_id : this.currentUser.id,
                    customer_id : this.reservation.customer_id,
                    reservation_id : this.reservation.id,
                    time: this.time,
                    checkout_type: 'simple',
                    type: 'check-out',
                    reservation_type: this.reservation.reservation_type,
                    cleaning: this.cleaning,
                    liquidation_type : this.liquidationType,
                    from_date: this.reservation.date_in,
                    to_date: this.reservation.date_out,
                    services: this.reservation.services,
                    noteOnInvoice : this.__('Invoice on reservation number : ') + this.reservation.number
                };
                this.isLoading = true;
                this.disable_action_btn = true;
                Nova.request()
                    .post('/nova-vendor/calender/create-entity-and-checkout' , obj)
                    .then(response => {
                        this.isLoading = false;
                        this.disable_action_btn = false;
                        this.reservation.invoices = response.data.refilled_reservation.invoices;
                        this.reservation.transactions = response.data.refilled_reservation.transactions;
                        this.reservation.checked_out = response.data.refilled_reservation.checked_out;
                        this.reservation.balance = parseFloat(response.data.refilled_reservation.balance).toFixed(2);
                        this.reservation.services = response.data.refilled_reservation.services;
                        // to just refresh invoices
                        Nova.$emit('single_reservation_checked_out' , this.reservation.invoices);
                        this.$toasted.show(this.__('Checkout process went successfully'), {type: 'success'});
                        this.$refs.modal.close();
                        return;
                    })

            },


            /**
             * -------- all the above of this line has been refactored and covered in all cases  --------------
             */




            performCheckout(){
                // Automated invoice
                if(this.invoices.length){
                    this.checkInvoicesFirst();
                }else{
                    // else here we will generate the automated invoice automatically
                    let params = {
                        id : this.reservation.id,
                        from_date : new Date(moment(this.reservation.date_in).startOf('day')),
                        to_date : new Date(moment(this.reservation.date_out).startOf('day').subtract(1,'days')),
                        note : this.__('Automated Invoice')
                    };
                    this.automatedInvoice(params,'firstInvoice');
                }
                axios
                    .post('/nova-vendor/calender/reservation/checks', {
                        id: this.reservation.id,
                        time: this.time,
                        type: 'check-out',
                        cleaning: this.cleaning
                    })
                    .then(response => {

                        this.$emit('update-reservation');
                        this.quickModal.modal.close();
                        this.$toasted.show(this.__('Customer checked out successfully'), {
                            duration : 1000,
                            type: 'success'
                        });
                        this.isLoading = false ;
                    }).catch(err => {
                        this.$toasted.show(this.__(err), {type: 'error'})
                    })
            },

            checkOutAndLiquidation() {
                this.disabled = true;
                this.spinnerLoad = true ;
                if (!this.time) {
                    this.$toasted.show(this.__('Please fill check out time'), {type: 'error'})
                    return;
                }
                let element = null;

                if (this.$refs.receipt) {
                    element = this.$refs.receipt;
                }
                else {
                    element = this.$refs.payment;
                }




                try {
                    element.send().then(response => {
                        this.send();
                    }).catch(error => {

                    });

                } catch(error) {



                }
            },

            automatedInvoice(data,flag = null){
                axios
                    .post('/nova-vendor/calender/reservation/add-invoice', {
                        id: data.id,
                        from_date: data.from_date,
                        to_date: data.to_date,
                        note : data.note
                    })
                    .then(response => {

                        this.invoices.push(response.data);
                        this.invoices = _.orderBy(this.invoices, 'number', 'desc');

                        Nova.$emit('invoice-added' , this.invoices);
                        this.$toasted.show(this.__('Automated Invoice has been generated successfully'), {
                            duration : 3000,
                            type: 'success',
                            position : 'top-center'
                        });
                        this.showAutomatedInvoiceNotification = true;

                    }).catch(err => {
                    this.$toasted.show(err, {type: 'error'})
                });

            },
            /**
             * transactions_ids : list of transactions ids that were not attached to any invoice
             */
            createInvoiceForFreeServicesTransactions(transactions_ids){
                axios.post('/nova-vendor/calender/reservation/create-invoice-for-free-services', {
                    transactions_ids : transactions_ids,
                    reservation_id : this.reservation.id,
                    noteOnInvoice : this.__('Invoice on reservation number : ') + this.reservation.number
                })
                .then(response => {
                    if(response.data.success){
                        this.invoices.push(response.data);
                        this.invoices = _.orderBy(this.invoices, 'number', 'desc');
                        Nova.$emit('invoice-added' , this.invoices);
                    }
                })
                .catch(error => {
                    console.log(error);
                })
            },
            checkInvoicesFirst(){
                axios.get(`/nova-vendor/calender/reservation/${this.reservation.id}/get-invoices`)
                    .then((response) => {
                        if(response.data.length){
                            let holder_invoices = _.filter(response.data, function(invoice) {
                                return invoice.invoice_credit_note === null;
                            });
                            if(holder_invoices.length){
                                let lastInvoice =   holder_invoices[0];
                                let lastInvoiceDate = moment(lastInvoice.to);
                                let reservationLastDate = moment(this.reservation.date_out).subtract(1 , 'days');
                                let diffInDays = reservationLastDate.diff(lastInvoiceDate , 'days');
                                if(diffInDays > 0){
                                    // this means we have an available date that needs automated invoice to be generated
                                    let params = {
                                        id : this.reservation.id,
                                        from_date : new Date(lastInvoiceDate.startOf('day').add(1,'days')),
                                        to_date : new Date(reservationLastDate.startOf('day')),
                                        note : this.__('Automated Invoice')
                                    };
                                    this.automatedInvoice(params , 'invoicesFound');
                                }else{
                                    /**
                                     * Our start here
                                     * 1- else here will mean that full period completed as invoices and no available dates
                                     * 2- then after that i will fetch reservation service transactions
                                     * 3- get the filtered services transactions that are not attached to invoices
                                     * 4- create a new invoice with start & end date as the checkout date
                                     * 5- this invoice will include those not attached service transactions
                                     */

                                    // there are some service transactions
                                    if(this.reservation && this.reservation.services.length){
                                        // filter services and get only the free ones
                                        let free_services_transactions = _.filter(this.reservation.services, function(transaction) {
                                            return transaction.is_attached_to_invoice === 0;
                                        });

                                        if(free_services_transactions.length){
                                            var transactions_ids = [];
                                            free_services_transactions.forEach(function(transaction){
                                                transactions_ids.push(transaction.id);
                                            });
                                            this.createInvoiceForFreeServicesTransactions(transactions_ids);
                                        }
                                    }
                                }

                            }else{
                                let params = {
                                    id : this.reservation.id,
                                    from_date : new Date(moment(this.reservation.date_in).startOf('day')),
                                    to_date : new Date(moment(this.reservation.date_out).startOf('day').subtract(1,'days')),
                                    note : this.__('Automated Invoice')
                                };

                                this.automatedInvoice(params,'firstInvoice');
                            }
                        }

                    });
            },
            send() {
                this.isLoading = true;
                this.disabled = true;
                // Invoice Part , we need to generate automated invoice if there are days don't have invoices yet
                if(this.invoices.length){
                    // call the function to calculate dates and handle other issues
                    this.checkInvoicesFirst();
                }else{

                    // else here we will generate the automated invoice automatically

                    let params = {
                        id : this.reservation.id,
                        from_date : new Date(moment(this.reservation.date_in).startOf('day')),
                        to_date : new Date(moment(this.reservation.date_out).startOf('day').subtract(1,'days')),
                        note : this.__('Automated Invoice')
                    };

                    this.automatedInvoice(params,'firstInvoice');

                }


                if (!this.time) {
                    this.$toasted.show(this.__('Please fill check out time'), {type: 'error'})
                    return;
                }

                axios
                    .post('/nova-vendor/calender/reservation/checks', {
                        id: this.reservation.id,
                        time: this.time,
                        type: 'check-out',
                        cleaning: this.cleaning
                    })
                    .then(response => {
                        Nova.$emit('update-reservation');

                        if(this.fromPaymentVoucher){
                            this.$refs.modal.close();
                        }

                        this.$refs.modal.close();
                        this.$toasted.show(this.__('Customer checked out successfully'), {
                            duration : 1000,
                            type: 'success'
                        });
                        this.isLoading = false ;
                    }).catch(err => {
                    })

            },

            modalClosed(){
                Nova.$emit('liquidation-type-closed' , 'transaction')
            }
        },
        mounted() {
            this.locale = Nova.config.local;
            this.invoices = this.reservation.invoices;
            this.canEditCheckinCheckoutTime = Nova.app.$hasPermission('edit checkin and checkout time');
            this.hasPermissionToAddDeposit = Nova.app.$hasPermission('add receipts');
            this.hasPermissionToAddWithdraw = Nova.app.$hasPermission('add payments');


            Nova.$on('invoice-deleted' , () => {
                this.isLoading = true;
                axios.get('/nova-vendor/calender/reservationInvoices?id=' + this.reservation.id)
                    .then((res) => {
                        this.invoices = _.orderBy(res.data, 'number', 'desc');
                        this.isLoading = false;
                    })
                    .catch((err) => {
                        console.log(err);
                    })
            })
            Nova.$on('invoice-added' , ($invoices) => {
                this.invoices = $invoices ;
            });
            Nova.$on('hide-spinner' , () => {
                setTimeout(()=>{ this.spinnerLoad = false; }, 800);
            })
            Nova.$on('remove-disabled' , () => {
                this.disabled = false ;
                this.disableAddPromissory = false;
            })
            Nova.$on('liquidation-type' , (val) => {
                this.liquidationType = val;
            })
        },
        watch: {
            selected_term: function(newTermId, oldTermId) {
                var chosenTerm;
                var filteredTerms = this.reservation.team.terms.filter(term => term.id == newTermId);
                if(filteredTerms.length){
                   chosenTerm =  filteredTerms[0];
                    if(this.reservation.reservation_type == 'single'){
                        this.description = chosenTerm.name[this.locale] + ' - ' + this.__('Unit') + ' - ' + this.reservation.unit.unit_number ;
                    }else{
                        this.description = chosenTerm.name[this.locale];
                    }
                    this.selected_term_type = chosenTerm.type == 1 ? 'withdraw' : 'deposit';
                }

            },
            liquidationType(newVal,oldVal){
              if(newVal == 'promissory'){
                this.due_for = Nova.app.__('Rent Due On') + ' - ' + this.reservation.unit.name[this.locale]  + ' - ' + this.reservation.unit.unit_number;
              }else{
                this.due_for = null;
              }
            }
        },
        beforeDestroy(){
            Nova.$off('remove-disabled');
        }
    }
</script>

<style lang="scss">

 textarea {
    width: 100%;
    padding: 10px;
    border-radius: 5px;
    background: #fafafa;
    border: 1px solid #ddd;
    font-size: 15px;
    color: #000;
    margin-bottom: 10px;
} /* textarea */
.transaction_form {
  .formgroup {
    display: block;
    margin: 0 0 15px;
  } /* formgroup */
  .row_group {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    .col {
      width: 48%;
      margin: 0 0 15px;
      @media (min-width: 320px) and (max-width: 767px) {
        width: 100%
      } /* Mobile */
    } /* col */
  } /* row_group */
  label {
    display: block;
    margin: 0 0 5px;
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
    width: 100%;
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
    [dir="ltr"] & {
      background-position: 97% center;
    } /* ltr */
    &:disabled{
        background: #d6d6d6 !important;
    }
  } /* select */
} /* transaction_form */
.full {width: 100%;}
.checkout_customer_modal {
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
  .alert_balance {
    margin: 0 auto 15px;
    border-radius: 5px;
    padding: 10px;
    text-align: center;
    color: #b7791f;
    border: 1px solid #fbd38d;
    background: #fffaf0;
    font-size: 15px;
    display: block;
    span, p {display: inline-block;}
  } /* alert_balance */
  .transaction_title {
    display: block;
    text-align: center;
    font-size: 20px;
    margin: 0 auto 15px;
    border-bottom: 1px solid #ddd;
    padding: 0 0 15px;
  } /* transaction_title */
  .cleaning_msg {
    margin: 0 auto 15px;
    border-radius: 5px;
    padding: 10px;
    color: #b7791f;
    border: 1px solid #fbd38d;
    background: #fffaf0;
    font-size: 15px;
    display: table;
    width: 100%;
    clear: both;
    input {
      float: right;
      margin: 5px 0 0 10px;
      [dir="ltr"] & {
        float: left;
        margin: 5px 10px 0 0;
      } /* ltr */
    } /* input */
  } /* cleaning_msg */
  .form_group {
    display: block;
    margin: 0 0 15px;
    label {
      display: block;
      margin: 0 0 5px;
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
      width: 100%;
    } /* input */
  } /* form_group */
  .liquidation_receivables{
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    button {
      background: #4099de;
      border-radius: 5px;
      border: 1px solid #4099de;
      height: 35px;
      line-height: 35px;
      font-size: 15px;
      margin: 10px 0;
      padding: 0 15px;
      width: 48%;
      color: #ffffff;
      cursor: pointer;
      -webkit-transition: all 0.2s ease-in-out;
      -moz-transition: all 0.2s ease-in-out;
      -o-transition: all 0.2s ease-in-out;
      transition: all 0.2s ease-in-out;
      @media (min-width: 320px) and (max-width: 767px) {
        width: 100%;
      } /* Mobile */
      &:hover {
        background: #0071C9;
        border-color: #0071C9;
      } /* hover */
    } /* button */
  } /* liquidation_receivables */
  .actionBtn{
      background: #4099de;
      border-radius: 5px;
      border: 1px solid #4099de;
      height: 35px;
      line-height: 35px;
      font-size: 15px;
      margin: 10px 0;
      padding: 0 15px;
      width: 48%;
      color: #ffffff;
      cursor: pointer;
      -webkit-transition: all 0.2s ease-in-out;
      -moz-transition: all 0.2s ease-in-out;
      -o-transition: all 0.2s ease-in-out;
      transition: all 0.2s ease-in-out;
      @media (min-width: 320px) and (max-width: 767px) {
        width: 100%;
      } /* Mobile */
      &:hover {
        background: #0071C9;
        border-color: #0071C9;
      } /* hover */
  }
  .fullWidthBtn {
        width: 100% !important;
    }
} /* checkout_customer_modal */
.spinner {
  width: 1.5rem;
  height: 1.5rem;
  border-top-color: #444;
  border-left-color: #444;
  animation: spinner 400ms linear infinite;
  border-bottom-color: transparent;
  border-right-color: transparent;
  border-style: solid;
  border-width: 2px;
  border-radius: 50%;
  box-sizing: border-box;
  display: inline-block;
  vertical-align: middle;
}
@keyframes spinner {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}
.spinner-large {
  width: 5rem;
  height: 5rem;
  border-width: 6px;
}
.spinner-slow {
  animation: spinner 1s linear infinite;
}
.spinner-blue {
  border-top-color: #09d;
  border-left-color: #09d;
}
.spinner-light {
  border-top-color: #ffffff;
  border-left-color: #ffffff;
}
</style>
