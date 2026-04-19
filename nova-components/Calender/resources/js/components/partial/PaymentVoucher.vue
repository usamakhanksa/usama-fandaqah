<template>
    <div>
        <!-- <button @click="open" v-if="reservation &&  !quick &&  !reservation.checked_out && reservation.status == 'confirmed'" v-permission="'add payments'" class="payment_voucher_button">{{__('Payment Voucher')}}</button> -->
        <button @click="open" v-if="!quick && !reservation.checked_out && (reservation.status == 'confirmed' || reservation.status == 'awaiting-payment' || reservation.status == 'awaiting-confirmation')" v-permission="'add payments'" class="payment_voucher_button">{{__('Payment Voucher')}}</button>
        <button @click="open" v-if="!quick && occ" v-permission="'add payments'" class="payment_voucher_button">{{__('Payment Voucher')}}</button>
        <sweet-modal :enable-mobile-fullscreen="false" class="payment_voucher_modal"  :pulse-on-block="false" :title="__('Payment Voucher')" overlay-theme="dark" ref="paymentVoucherModal">
            <loading :active.sync="loader" :can-cancel="true" :loader="'spinner'" :color="'#7e7d7f'" :is-full-page="false"></loading>

            <div v-if="showHintInsurance" class="alert_insurance">
                {{ __('There is a :amount insurance amount and you must create a retrieval insurance for it' , {amount : insuranceAmountDue}) }}
            </div>
            <div class="formgroup">
                <label>{{__('Type')}} <span>*</span></label>
                <select v-model="kind" @change="updateFor" :disabled="disableFieldsForInsurance">
                    <option value="" disabled selected>{{__('Select Option')}}</option>
                    <template v-if="!termsLoading">
                        <option v-for="(term,index) in terms" :key="index" :value="term.id" v-if="term.deleteable == 1 || term.name['ar'] == 'استرجاع تامين'">{{term.name[local]}}</option>
                    </template>
                </select>
            </div><!-- formgroup-->
            <div class="row_group">
                <div class="col">
                    <label>{{__('Date')}}<span>*</span></label>
                    <flat-pickr 
                        v-model="date"
                        class="form-control" 
                        :placeholder="__('Select Transaction Date')" 
                        :config="config"
                    >
                    </flat-pickr>
                </div><!-- col-->
                <div class="col">
                    <label>{{__('To')}}<span>*</span></label>
                    <input type="text" v-model="from" ref="from" :disabled="disableFields" :placeHolder="__('To')">
                </div><!-- col-->
            </div><!-- row_group-->
            <div class="row_group">
                <div class="col">
                    <label>{{__('Amount')}}<span>*</span></label>
                    <input type="tel" v-model="amount" :disabled="disableFields" :placeHolder="__('Amount')">
                </div><!-- col-->
                <div class="col">
                    <label>{{__('For')}}<span>*</span></label>
                    <input type="text" v-model="description" :disabled="disableFields" :placeHolder="__('For')">
                </div><!-- col-->
            </div><!-- row_group-->
            <div class="row_group">
                <div class="col">
                    <label>{{__('Received By')}}<span>*</span></label>
                    <input type="text" v-model="received_by"  :disabled="disableFields" :placeHolder="__('Received By')">
                </div><!-- col-->
                <div class="col">
                    <label>{{__('Payment Type')}}<span>*</span></label>
                    <select placeholder="Payment Type" v-model="type" @change="checkPaymentType">
                        <option value="" disabled selected>{{__('Select Payment Type')}}</option>
                        <option value="cash">{{__('Cash')}}</option>
                        <option value="bank-transfer">{{__('Bank Transfer')}}</option>
                        <option value="mada">{{__('Mada')}}</option>
                        <option value="credit">{{__('Credit Card')}}</option>
                    </select>
                </div><!-- col-->
            </div><!-- row_group-->
            <div class="row_group">
                <div class="col" v-if="ref">
                    <label>{{__('Payment Reference')}}<span>*</span></label>
                    <input type="text" v-model="reference" :placeHolder="__('Payment Reference')">
                </div><!-- col-->
                <div class="col">
                    <label>{{__('Notes')}}</label>
                    <input type="text" v-model="note" :placeHolder="__('Notes')">
                </div><!-- col-->
            </div><!-- row_group-->
            <button class="shadow mb-2  btn btn-block btn-primary mt-2" v-show="!hideAddBtn" :disabled="loading" @click="send">{{__('Save')}}</button>
        </sweet-modal>
        <reservation-check-out from="payment_voucher" :reservation="$parent.reservation" ref="checkout"></reservation-check-out>

    </div>
</template>

<script>
    import momenttimezone from 'moment-timezone';
    import flatPickr from 'vue-flatpickr-component';
    import 'flatpickr/dist/flatpickr.css';
    import { Arabic } from "flatpickr/dist/l10n/ar.js"
    import Loading from 'vue-loading-overlay';
    import 'vue-loading-overlay/dist/vue-loading.css';
    export default {
        name: "PaymentVoucher",
        props : ['quick','occ' , 'group_balance'],
        components: {
            Loading,
            flatPickr
        },
        data: () => {
            return {
                loading: null,
                kind: null,
                local: Nova.config.local,
                date: null,
                config: {
                  wrap: false, // set wrap to true only when using 'input-group'
                  altFormat: 'Y-m-d H:i',
                  altInput: true,
                  dateFormat: 'Y-m-d H:i',
                  locale: Arabic, // locale for this instance only 
                  enableTime: true,
                  time_24hr: true,
                  disableMobile: true,
                  minuteIncrement : 1,
                  clickOpens : Nova.app.$hasPermission('change transactions date') && Nova.app.$hasPermission('add receipts') ? true : false
                },   
                from: null,
                termsLoading: true,
                terms: {},
                amount: null,
                type: null,
                received_by: null,
                description: null,
                note: null,
                ref: false,
                reference: null,
                locale:'en',
                employee:'',
                loader : false,
                reservation : null,
                quickModal : {
                    from : null ,
                    modal : null
                },
                canChangeTransactionDate : false,
                termName : null,
                hideAddBtn : false,
                disableFields : false,
                disableFieldsForInsurance : false,
                showHintInsurance : false,
                insuranceAmountDue : 0,
                insuranceWithdrawFromCheckout : false,
                openCheckoutForGroupReservation: false,
                data_for_group_reservation_multiple_checkout : null,
                server_date : null
            }
        },
        methods: {
            updateFor(event){  
                let term_id = this.kind ;
                Nova.request().get('/nova-vendor/calender/term?id=' + term_id)
                    .then((res)=>{
                        if(this.$parent.reservation.company){
                            this.description = res.data.name[this.local];
                            if(this.$parent.reservation.group_reservation){
                                this.description +=  ' - '  + this.$parent.reservation.group_reservation.reservations.length + ' ' + this.__('Unit');
                            }
                        }else{
                            this.description = res.data.name[this.local] + ' - ' + this.__('Unit') + ' - ' + this.$parent.reservation.unit.unit_number ;
                        }
                        this.termName  = res.data.name['ar'];

                        if(this.termName == 'استرجاع تامين' || this.termName == 'استرداد تأمين'){

                            if(this.$parent.reservation.reservation_type == 'single' && !this.$parent.reservation.deposit_insurance_transactions.length){
                                this.$toasted.show(this.__('We can not add retrieval insurance transaction cause there is no insurance transaction added'), {type: 'error'});
                                this.hideAddBtn = true;
                                return;
                            }

                            if(this.$parent.reservation.reservation_type == 'single' && this.$parent.reservation.withdraw_insurance_transactions.length){
                                this.$toasted.show(this.__('We can not add new retrieval insurance transaction cause there is already one added'), {type: 'error'});
                                this.hideAddBtn = true;
                                return;
                            }




                            if(this.$parent.reservation.reservation_type == 'single' && this.$parent.reservation.deposit_insurance_transactions.length){
                                this.from =  this.$parent.reservation.deposit_insurance_transactions[0].meta && this.$parent.reservation.deposit_insurance_transactions[0].meta.from ? this.$parent.reservation.deposit_insurance_transactions[0].meta.from :  this.reservation.customer.name;
                                this.received_by = this.$parent.reservation.deposit_insurance_transactions[0].meta && this.$parent.reservation.deposit_insurance_transactions[0].meta.received_by ? this.$parent.reservation.deposit_insurance_transactions[0].meta.received_by :  this.reservation.customer.name;
                                // this.date = this.$parent.reservation.deposit_insurance_transactions[0].meta.date;
                                this.amount = Math.abs(this.reservation.wallet.decimal_places == 3 ? this.$parent.reservation.deposit_insurance_transactions[0].amount / 1000 : this.$parent.reservation.deposit_insurance_transactions[0].amount / 100 );
                                this.type = this.$parent.reservation.deposit_insurance_transactions[0].meta.payment_type;
                                // this.description = this.$parent.reservation.deposit_insurance_transactions[0].meta.statement;
                                this.note = this.$parent.reservation.deposit_insurance_transactions[0].meta.note;
                                this.reference = this.$parent.reservation.deposit_insurance_transactions[0].meta.reference;
                                this.disableFields = true;
                                this.checkPaymentType();

                                return;
                            }

                            // For Group Reservation

                            if(this.$parent.reservation.reservation_type == 'group'){
                                this.loader = true;
                                axios.get(`/nova-vendor/calender/group-reservation/sibling/${this.$parent.reservation.id}/check-insurance-transaction?from=withdraw`)
                                .then((response) => {
                                    if(!response.data.can_add_retrieval_insurance_transaction && response.data.status == 'no_insurance_transaction_found'){
                                        this.$toasted.show(this.__('We can not add retrieval insurance transaction cause there is no insurance transaction added'), {type: 'error'});
                                        this.hideAddBtn = true;
                                        this.loader = false;
                                        return;
                                    }

                                    if(!response.data.can_add_retrieval_insurance_transaction && response.data.status == 'another_retrieval_insurance_found'){
                                        this.$toasted.show(this.__('We can not add new retrieval insurance transaction cause there is already one added'), {type: 'error'});
                                        this.hideAddBtn = true;
                                        this.loader = false;
                                        return;
                                    }

                                    if(response.data.can_add_retrieval_insurance_transaction){
                                        let incomingInsuranceTransaction = response.data.insurance_transaction;
                                        this.loader = false;
                                        this.from = incomingInsuranceTransaction.meta.from;
                                        this.received_by = incomingInsuranceTransaction.meta.received_by ? incomingInsuranceTransaction.meta.received_by : this.reservation.customer.name;
                                        // this.date = incomingInsuranceTransaction.meta.date;
                                        this.amount = Math.abs(this.reservation.wallet.decimal_places == 3 ? incomingInsuranceTransaction.amount / 1000 : incomingInsuranceTransaction.amount / 100 );
                                        this.type = incomingInsuranceTransaction.meta.payment_type;
                                        this.note = incomingInsuranceTransaction.meta.note;
                                        this.reference = incomingInsuranceTransaction.meta.reference;
                                        this.disableFields = true;
                                        this.checkPaymentType();
                                        return;
                                    }
                                    this.loader = false;
                                })

                            }

                            this.disableFields = false;
                            this.hideAddBtn = false;
                        }else{
                            this.disableFields = false;
                            this.hideAddBtn = false;
                            this.from = this.$parent.reservation.company ? this.$parent.reservation.company.name : this.$parent.reservation.customer.name;
                            this.received_by = this.$parent.reservation.company ? this.$parent.reservation.company.name : this.$parent.reservation.customer.name;
                            this.type = null;
                            this.note = null;
                            this.reference = null;
                            this.ref = null;
                            // this.amount = null;
                            // if(this.$parent.reservation.balance > 0){
                            //     this.amount = Math.abs(this.$parent.reservation.balance / (this.$parent.reservation.wallet.decimal_places == 3 ? 1000 : 100)).toFixed(2);
                            // }

                            if(this.$parent.reservation.balance > 0 && this.$parent.reservation.reservation_type == 'single'){
                                this.amount = Math.abs(this.$parent.reservation.balance / (this.$parent.reservation.wallet.decimal_places == 3 ? 1000 : 100)).toFixed(2);
                            }
                            if(this.group_balance > 0 && this.$parent.reservation.reservation_type == 'group'){
                                this.amount = Math.abs(this.group_balance).toFixed(2);
                            }


                        }
                    });




                // this.description = this.terms[event.target.options.selectedIndex-1].name[this.local]
            },
            open() {
                this.getServerDate();      
                this.getTerms();
                this.from = this.$parent.reservation.company ? this.$parent.reservation.company.name : this.$parent.reservation.customer.name;
                this.received_by = this.$parent.reservation.company ? this.$parent.reservation.company.name : this.$parent.reservation.customer.name;
                this.insuranceWithdrawFromCheckout = false;
                this.showHintInsurance = false;
                this.disableFields = false;
                this.disableFieldsForInsurance = false;
                this.hideAddBtn = false;
                this.kind = null;
                this.amount = null;
                if(this.$parent.reservation.balance > 0 && this.$parent.reservation.reservation_type == 'single'){
                    this.amount = Math.abs(this.$parent.reservation.balance / (this.$parent.reservation.wallet.decimal_places == 3 ? 1000 : 100)).toFixed(2);
                }
                if(this.group_balance > 0 && this.$parent.reservation.reservation_type == 'group'){
                    this.amount = Math.abs(this.group_balance).toFixed(2);
                }
                this.type = null;
                this.description = null;
                this.note = null;
                this.reference = null;
                this.ref = false;
                this.ref = false;

                this.quickModal.from = this.$refs.from;
                this.quickModal.paymentVoucherModal = this.$refs.paymentVoucherModal;

                if(this.quick){
                    if(this.$parent.reservation.id === this.reservation.id){
                        this.quickModal.paymentVoucherModal.open();
                    }
                }else{
                    this.quickModal.paymentVoucherModal.open();
                }
            },


            checkPaymentType(){
                if(this.type == 'cash'){
                    this.ref = false;
                } else {
                    this.ref = true;
                }
            },
            send() {

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

                 if (this.amount <= 0) {
                    this.$toasted.show(this.__("Amount is not valid"), {type: 'error'})
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


                if(!this.type){
                    this.$toasted.show(this.__("Please Enter Payment Type!"), {type: 'error'})

                    return
                }


                this.loading = true;
                this.loader = true;
                axios
                    .post('/nova-vendor/calender/reservation/transaction', {
                        id: this.$parent.reservation.id,
                        type: 'withdraw',
                        amount: this.amount,
                        termName : this.termName,
                        meta: {
                            category: 'reservation',
                            statement: this.description,
                            type: this.kind,
                            payment_type: this.type,
                            note: this.note,
                            reference: this.reference,
                            received_by: this.received_by,
                            from: this.from,
                            employee: this.employee,
                        },
                        transaction_date : this.date
                    })
                    .then(response => {

                        this.deposit_type = null;
                        this.amount = 0;
                        this.reference = 0;
                        this.type = null;
                        this.kind = null;
                        this.from = null;
                        this.$emit('update-reservation')


                        if(this.quick){

                            if(this.$parent.reservation.id === response.data.id){
                                this.quickModal.paymentVoucherModal.close();
                            }
                        }else{
                            this.$refs.paymentVoucherModal.close();
                        }

                        this.loading = false;
                        this.loader = false;

                        if(this.insuranceWithdrawFromCheckout){
                            this.$toasted.show(this.__('Retrieval insurance transaction has been created successfully'), {type: 'success'});
                            setTimeout(() => {
                                if(this.openCheckoutForGroupReservation){
                                    if(this.data_for_group_reservation_multiple_checkout){
                                        Nova.$emit('open-checkout-modal-for-group-reservation' , {
                                            reservation : this.data_for_group_reservation_multiple_checkout.reservation,
                                            shared_invoices : this.data_for_group_reservation_multiple_checkout.shared_invoices,
                                            automatic_under_cleaning : this.data_for_group_reservation_multiple_checkout.automatic_under_cleaning
                                        })
                                    }else{
                                        Nova.$emit('open-group-reservation-checkout');
                                    }
                                }else{
                                    this.$refs.checkout.$refs.modal.open();
                                    Nova.$emit('check-liquid-permission' , this.insuranceWithdrawFromCheckout);
                                }

                            }, 0);

                            return;
                        }

                        this.$toasted.show(this.__('Transaction added successfully'), {type: 'success'});

                    }).catch(err => {
                    this.loading = false;
                    this.$toasted.show(this.__(err), {type: 'error'})
                })
            },
            getTerms() {
                Nova.request()
                    .get('/nova-vendor/calender/terms/payment-voucher')
                    .then(response => {

                        this.terms = response.data;
                        if(this.terms.length == 0){
                            this.$toasted.show(this.__('Please Add Terms First '), {type: 'info', action : {
                                    text : 'Settings',
                                    push : {
                                        name : 'settings',
                                        // this will prevent toast from closing
                                        dontClose : true
                                    }
                                },});

                        }
                        // console.log("this.terms", this.terms)
                        this.termsLoading = false;
                    }).catch(err => {
                    // this.serviceLoading = false;
                    // this.$toasted.show(this.__(err), {type: 'error'})
                })
            },
            getServerDate(){
                this.date = new Date( momenttimezone().tz("Asia/Riyadh").format('YYYY-MM-DD HH:mm'));
                Nova.request()
                .get('/nova-vendor/calender/server/current-date')
                .then(response => {
                    this.server_date = response.data;
                    this.date = new Date(this.server_date);      
                })
            },

           async addRetrievalInsurance(transaction){
                const self = this;
                this.termsLoading = true;
                const response = await axios.get('/nova-vendor/calender/terms?type=1');
                this.terms = response.data;
                this.termsLoading = false;
                this.from =  transaction.meta ? transaction.meta.from :  this.reservation.customer.name;
                this.received_by = this.$parent.reservation.deposit_insurance_transactions[0].meta && this.$parent.reservation.deposit_insurance_transactions[0].meta.received_by ? this.$parent.reservation.deposit_insurance_transactions[0].meta.received_by :  this.reservation.customer.name;
                this.date = transaction.meta.date;
                this.amount = Math.abs(this.reservation.wallet.decimal_places == 3 ? transaction.amount / 1000 : transaction.amount / 100 );
                this.type = transaction.meta.payment_type;
                // this.description = this.$parent.reservation.deposit_insurance_transactions[0].meta.statement;
                this.note = transaction.meta.note;
                this.reference = transaction.meta.reference;
                this.disableFields = true;
                this.checkPaymentType();

                $.each(this.terms, function(key , item) {
                    if(item.name['ar'] == "استرجاع تامين"){
                        self.kind = item.id;
                        self.description  = item.name[self.local] + ' - ' + self.__('Unit') + ' - ' + self.$parent.reservation.unit.unit_number ;
                        self.termName = item.name['ar'];
                    }
                });
                this.disableFieldsForInsurance = true;
                this.showHintInsurance = true;
                this.insuranceAmountDue = Math.abs(this.reservation.wallet.decimal_places == 3 ? transaction.amount / 1000 : transaction.amount / 100 );
                this.insuranceWithdrawFromCheckout = true;
                // this.$refs.modal.open();

                if(this.$refs.paymentVoucherModal){

                    this.$refs.paymentVoucherModal.open();
                }



            },
            async addRetrievalInsuranceForGroupReservation(transaction,data=null){
                if(data){
                  this.data_for_group_reservation_multiple_checkout = data;
                }
                 const self = this;
                this.termsLoading = true;
                const response = await axios.get('/nova-vendor/calender/terms?type=1');
                this.terms = response.data;
                this.termsLoading = false;

                  this.from =  transaction.meta ? transaction.meta.from :  this.reservation.customer.name;
                this.received_by = transaction.meta && transaction.meta.received_by ? transaction.meta.received_by :  this.reservation.customer.name;
                this.date = transaction.meta.date;
                this.amount = Math.abs(this.reservation.wallet.decimal_places == 3 ? transaction.amount / 1000 : transaction.amount / 100 );
                this.type = transaction.meta.payment_type;
                this.note = transaction.meta.note;
                this.reference = transaction.meta.reference;
                this.disableFields = true;
                this.checkPaymentType();

                $.each(this.terms, function(key , item) {
                    if(item.name['ar'] == "استرجاع تامين"){
                        self.kind = item.id;
                        self.description  = item.name[self.local];
                        self.termName = item.name['ar'];
                    }
                });
                this.disableFieldsForInsurance = true;
                this.showHintInsurance = true;

                this.insuranceAmountDue = Math.abs(this.reservation.wallet.decimal_places == 3 ? transaction.amount / 1000 : transaction.amount / 100 );
                this.insuranceWithdrawFromCheckout = true;
                if(this.$refs.paymentVoucherModal){
                    this.openCheckoutForGroupReservation = true;
                    this.$refs.paymentVoucherModal.open();
                }

            }
        },
        watch: {
            amount(num) {
                this.amount = num.replace(/([٠١٢٣٤٥٦٧٨٩])|([۰۱۲۳۴۵۶۷۸۹])/g, (m, $1, $2) => m.charCodeAt(0) - ($1 ? 1632 : 1776))
                this.amount = this.amount.replace(/[^\d.\d]/g,'')
            }
        },
        mounted() {
            this.getServerDate();
            const self = this;
            this.termsLoading = true;
            this.locale =  Nova.config.local ;
            this.employee = Nova.config.user.name ;
            this.reservation = this.$parent.reservation;
            Nova.$on('quick-open-payment-voucher-modal' , (reservation) => {
                this.reservation = reservation;
                if(this.$parent.reservation.id === reservation.id){
                    this.open();
                }

            });

            Nova.$on('add-retrieval-insurance' , (transaction) => {
                this.addRetrievalInsurance(transaction);
            })

            Nova.$on('add-retrieval-insurance-for-group-reservation' , (transaction) => {
                this.addRetrievalInsuranceForGroupReservation(transaction);
            })

            Nova.$on('add-retrieval-insurance-for-group-reservation-from-group-checkout' , (data) => {
                this.addRetrievalInsuranceForGroupReservation(data.deposit_transaction,data)
            })
        },
        beforeDestroy(){
            Nova.$off('check-liquid-permission');
        }

    }
</script>

<style lang="scss">
    .payment_voucher_modal {
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
            box-shadow: none !important;
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
            &:disabled{
                background: #d6d6d6 !important;
            }
        } /* select */
        button.save_cash_receipt {
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
        } /* save_cash_receipt */
    } /* payment_voucher_modal */

      .alert_insurance {
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
</style>
