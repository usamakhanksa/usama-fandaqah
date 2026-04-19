<template>
  <div class="item_reservation_button">
    <button v-permission="'check-out customer'" class="main_button" @click="open">{{__('Check Out')}}</button>
    <sweet-modal :enable-mobile-fullscreen="false" :pulse-on-block="false" :title="__('Check Out')" overlay-theme="dark" ref="checkOutGroupReservationModal" class="checkout_customer_modal" @close="modalClosed">
      <loading :active.sync="isLoading" :is-full-page="false"></loading>
    <div class="alert_balance" v-if="group_balance != 0">
        <div>
          {{ __('You have not filtered for this booking - Balance') }} :
          <span :class="{ 'text-success': group_balance > 0 ,'text-danger': group_balance < 0 }">
            {{ Math.abs(group_balance).toFixed(2) + ' ' + __(currency)}}
            <p :class="{ 'text-success': group_balance > 0 ,'text-danger': group_balance < 0 }">{{ (group_balance  > 0) ? '('+__('credit')+')'  : ''}} {{ (group_balance  < 0) ? '('+__('debit')+')' : ''}}</p></span>
        </div>
    </div>
      <div v-if="group_balance < 0">
        <label class="transaction_title">سند قبض - تصفية حساب</label>
        <checkout-cash-receipt :terms="terms" :can_add_promissory="can_add_promissory" :reservation="reservation" :request_type="'checkout'" :balance="Math.abs(group_balance).toFixed(2)" ref="receipt"></checkout-cash-receipt>
      </div>
      <div v-if="group_balance > 0">
        <label class="transaction_title">سند صرف - تصفية حساب</label>
        <checkout-payment-voucher :reservation="reservation" :terms="terms" :request_type="'checkout'" :balance="Math.abs(group_balance).toFixed(2)" ref="payment"></checkout-payment-voucher>
      </div>

    <label v-if="canConvertUnitToUnderCleaning" class="cleaning_msg">
        <div v-if="automatic_under_cleaning">
            <span>{{__('Unit status will be changed to under cleaning automatically')}}</span>
        </div>
        <div v-else>
             <input type="checkbox" v-model="cleaning">
            <span>{{__('Unit conversion to condition: Under cleaning when recording customer leaving')}}</span>
        </div>

      </label><!-- cleaning_msg -->
      <!-- Time Banner   -->
      <div class="form_group">
        <label>{{__('Check-out time')}} </label>
        <input type="time" v-model="time" :placeholder="__('Name')"/>
      </div>

    <!-- Simply Show Checkout Button if the reservation is not the last in group reservation -->
      <button
            v-if="simpleCheckout"
            @click="makeSimpleCheckout"
            :disabled="disabled"
            class="shadow mb-4  btn btn-block btn-primary mt-2 text-base"
      >
        {{__('Checked out')}}
      </button>

     <div class="liquidation_receivables" v-if="group_balance != 0">
        <template v-if="hasPermssionToLiquid">
            <template v-if="liquidationType == 'transaction'">
                <button  :disabled="disabled" @click="checkOutAndLiquidation"  class="shadow mb-4  btn btn-block btn-primary mt-2 text-base">{{__('Checkout and Liquidation')}}<span v-if="spinnerLoad" class="spinner spinner-light" :class="[locale === 'ar' ? 'mr-2' : 'ml-2']"></span></button>
                <button :disabled="disabled" @click="send"   class="shadow mb-4  btn btn-block btn-primary mt-2 text-base">{{__('Checkout without Liquidation')}}</button>
            </template>
            <template v-else>
                <button @click="createPromissory" :disabled="disableAddPromissory"  class="shadow mb-4  btn btn-block btn-primary mt-2 text-base full">{{__('Checkout & Add Promissory')}}</button>
            </template>
        </template>
        <template v-else>
            <template v-if="liquidationType == 'transaction'">
                <button :disabled="disabled" @click="checkOutAndLiquidation"  class="shadow mb-4  btn btn-block btn-primary mt-2 text-base">{{__('Checkout and Liquidation')}}<span v-if="spinnerLoad" class="spinner spinner-light" :class="[locale === 'ar' ? 'mr-2' : 'ml-2']"></span></button>
            </template>
            <template v-else>
                <button  @click="createPromissory"   class="shadow mb-4  btn btn-block btn-primary mt-2 text-base full">{{__('Checkout & Add Promissory')}}</button>
            </template>
        </template>

      </div>
      <div v-else>
           <button
                    v-if="!simpleCheckout"
                    @click="makeSimpleCheckout"
                    :disabled="disabled"
                    class="shadow mb-4  btn btn-block btn-primary mt-2 text-base"
            >
                {{__('Checked out')}}
            </button>
      </div>


    </sweet-modal>
  </div>
</template>

<script>
    import Loading from 'vue-loading-overlay';
    import momenttimezone from 'moment-timezone'
    import 'vue-loading-overlay/dist/vue-loading.css';
    export default {
        name: "company-reservation-checkout",
        props : ['reservation','invoicesList'],
        components : {Loading},
        data: () => {
            return {
                loading: false,
                time: momenttimezone().tz("Asia/Riyadh").format('HH:mm'),
                cleaning: false,
                settings: null,
                invoices : [],
                showAutomatedInvoiceNotification : false,
                isLoading : false,
                quickReservation : null,
                currency :Nova.app.currentTeam.currency,

                quickModal : {
                    from : null ,
                    modal : null
                },
                spinnerLoad : false,
                locale : null,
                terms : null,
                canConvertUnitToUnderCleaning : true,
                disabled : false ,
                hasPermssionToLiquid : false,
                liquidationType : 'transaction',
                disableAddPromissory : false,
                fromPaymentVoucher : false,
                automatic_under_cleaning : null,
                simpleCheckout : true,
                group_balance : 0,
                term_type : null,
                is_last_checkout : false,
                dateFrom:  null,
                dateTo : null,
                can_add_promissory : false,
                hasPermissionToAddDeposit: false,
                hasPermissionToAddWithdraw: false
            }
        },
        methods: {
            getTimeBanner() {
                return moment(this.reservation.checked_in).format("DD/MM/YYYY hh:mm A");
            },
            open() {
                this.time =  momenttimezone().tz("Asia/Riyadh").format('HH:mm');
                if(Nova.app.$hasPermission('liquidation of dues before departure')){
                   this.hasPermssionToLiquid = true;
                }else{
                    this.hasPermssionToLiquid = false;
                }

                if (Nova.app.$hasPermission('liquid reservation with promissory')) {
                    this.can_add_promissory = true;
                }

                this.preCheckFutureReservation();
                this.checkIfCurrentReservationIsTheLastOneToCheckout();
            },
            makeSimpleCheckout(){
                let modalRef = this.$refs.checkOutGroupReservationModal;
                this.isLoading = true;
                this.disabled = true;

                if (!this.time) {
                    this.$toasted.show(this.__('Please fill check out time'), {type: 'error'})
                    return;
                }

                if(this.is_last_checkout){
                    this.generateAutomatedInvoiceForGroupReservation();
                }
                axios
                    .post('/nova-vendor/calender/reservation/checks', {
                        id: this.reservation.id,
                        time: this.time,
                        type: 'check-out',
                        cleaning: this.cleaning
                    })
                    .then((response) => {
                        Nova.$emit('update');
                        this.isLoading = false ;
                        modalRef.close();
                        this.$toasted.show(this.__('Customer checked out successfully'), {
                            duration : 1000,
                            type: 'success'
                        });

                    });
            },
            checkOutAndLiquidation() {

                if(this.group_balance > 0){
                    if(!this.hasPermissionToAddWithdraw){
                        this.$toasted.show(this.__("No permission found to add withdraw transaction"), {type: 'error'})
                        return;
                    }
                }

                if(this.group_balance < 0) {
                    if(!this.hasPermissionToAddDeposit){
                        this.$toasted.show(this.__("No permission found to add deposit transaction"), {type: 'error'})
                        return;
                    }
                }

                this.disabled = true;
                this.spinnerLoad = true ;
                if (!this.time) {
                    this.$toasted.show(this.__('Please fill check out time'), {type: 'error'})
                    return;
                }
                // this.loading = true;
                let element = null;
                if (this.$refs.receipt) {
                    element = this.$refs.receipt;
                }
                else {
                    element = this.$refs.payment;
                }
                try {
                    element.send().then((response) => {
                        this.send();
                    }).catch((error) => {
                        this.spinnerLoad = false;
                    });
                } catch (error) {
                    this.spinnerLoad = false;
                }
            },
            createInvoiceForFreeServicesTransactions(transactions_ids){
                axios.post('/nova-vendor/calender/reservation/create-invoice-for-free-services', {
                    transactions_ids : transactions_ids,
                    reservation_id : this.reservation.id,
                    is_group_invoice : true,
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
            generateAutomatedInvoiceForGroupReservation(){

                if(this.invoicesList.length){
                    var holder_invoices = _.filter(this.invoicesList, function(invoice) {
                        return invoice.invoice_credit_note === null;
                    });
                    // if(holder_invoices.length){
                    //     console.log(holder_invoices);
                    //     // return false;
                    // }

                        if(holder_invoices.length){
                            this.currentInOrderCreditNoteId = holder_invoices[0].id;
                            let lastInvoice =   holder_invoices[0];
                            let lastInvoiceDate = new Date(moment(lastInvoice.to)) ;
                            let reservationLastDate = new Date(moment(this.reservation.dates_calculations.end_date).subtract(1,'days'));
                            let diff_in_days = lastInvoiceDate.getTime() - reservationLastDate.getTime();

                            if(!diff_in_days){
                                 // filter services and get only the free ones
                                let free_services_transactions = _.filter(this.reservation.group_reservation_services, function(transaction) {
                                    return transaction.is_attached_to_invoice === 0;
                                });


                                if(free_services_transactions.length){
                                    var transactions_ids = [];
                                    free_services_transactions.forEach(function(transaction){
                                        transactions_ids.push(transaction.id);
                                    });
                                    this.createInvoiceForFreeServicesTransactions(transactions_ids);
                                    return;
                                }else{
                                    // else here means that silence is gold and dont do anything
                                    return;
                                }

                            }

                            this.dateFrom =  new Date(moment(lastInvoice.to).add(1,'days'));
                            this.dateTo = new Date(moment(this.reservation.dates_calculations.end_date).subtract(1,'days'));

                        }else{
                            this.dateFrom = new Date(moment(this.reservation.dates_calculations.start_date));
                            this.dateTo = new Date(moment(this.reservation.dates_calculations.end_date).subtract(1,'days'));
                        }
                }else{
                    this.dateFrom = new Date(moment(this.reservation.dates_calculations.start_date));
                    this.dateTo = new Date(moment(this.reservation.dates_calculations.end_date).subtract(1,'days'));
                }
                // axios
                //     .post(`/nova-vendor/calender/reservation/${this.reservation.id}/automated-group-invoice`)
                //     .then((response) => {
                //         this.invoices.push(response.data.invoice);
                //         Nova.$emit('group-invoice-added-automatic');
                //         this.$toasted.show(this.__('Automated Invoice has been generated successfully'), {
                //             duration : 3000,
                //             type: 'success',
                //             position : 'top-center'
                //         });
                //     })

                axios
                    .post('/nova-vendor/calender/reservation/create-group-invoice', {
                        id: this.reservation.id,
                        attachable_id: this.reservation.attachable_id,
                        all_grouped_reservations_ids: this.reservation.all_grouped_reservations_ids,
                        dates_calculations: this.reservation.dates_calculations,
                        from_date: this.dateFrom,
                        to_date: this.dateTo,
                        note : 'Automated Invoice',
                        company_id : this.reservation.company_id
                    })
                    .then(response => {
                        if(response && !response.success){
                            Nova.$emit('group-invoice-added');
                            return;
                        }
                        this.$toasted.show(this.__('Automated Invoice has been generated successfully'), {
                            duration : 3000,
                            type: 'success',
                            position : 'top-center'
                        });
                        Nova.$emit('group-invoice-added');
                        Nova.$emit('group-invoice-added-automatic');
                    });
            },
            send() {
                let modalRef = this.$refs.checkOutGroupReservationModal;
                this.isLoading = true;
                this.disabled = true;
                if (!this.time) {
                    this.$toasted.show(this.__('Please fill check out time'), {type: 'error'})
                    return;
                }

                if(this.is_last_checkout){
                    this.generateAutomatedInvoiceForGroupReservation();
                }
                axios
                    .post('/nova-vendor/calender/reservation/checks', {
                        id: this.reservation.id,
                        time: this.time,
                        type: 'check-out',
                        cleaning: this.cleaning
                    })
                    .then(response => {
                         Nova.$emit('update');
                        this.isLoading = false ;
                        modalRef.close();
                        this.$toasted.show(this.__('Customer checked out successfully'), {
                            duration : 1000,
                            type: 'success'
                        });
                    });

            },
            performCheckout(){
                let modalRef = this.$refs.checkOutGroupReservationModal;
                this.loading = true;

                if(this.is_last_checkout){
                    this.generateAutomatedInvoiceForGroupReservation();
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
                        modalRef.close();
                        this.$toasted.show(this.__('Customer checked out successfully'), {
                            duration : 1000,
                            type: 'success'
                        });
                        this.loading = false;
                        this.isLoading = false ;


                    }).catch(err => {
                    this.loading = false;
                    this.$toasted.show(this.__(err), {type: 'error'})
                })
            },
            getSettings() {
                this.loading = true;
                Nova.request()
                    .get('/nova-vendor/settings/get/general')
                    .then(response => {
                        this.settings = response.data.items;
                        this.loading = false;
                    })

            },
            getTerms() {

                Nova.request()
                    .get('/nova-vendor/calender/terms?type=' + this.term_type)
                    .then(response => {

                        this.terms = response.data;
                        if (this.terms.length == 0) {
                            this.$toasted.show(this.__('Please Add Terms First '), {
                                type: 'info', action: {
                                    text: 'Settings',
                                    push: {
                                        name: 'settings',
                                        // this will prevent toast from closing
                                        dontClose: true
                                    }
                                },
                            });

                        }

                    }).catch(err => {
                });
            },
            modalClosed(){
                Nova.$emit('liquidation-type-closed' , 'transaction')
            },
            createPromissory(){
                this.disableAddPromissory = true;
                this.spinnerLoad = true ;
                if (!this.time) {
                    this.$toasted.show(this.__('Please fill check out time'), {type: 'error'})
                    return;
                }
                let element = this.$refs.receipt;
                element.addPromissory()
                .then(res => {
                    this.disableAddPromissory = false;
                    this.performCheckout();
                })
            },
            checkInvoicesFirst(){
                let lastInvoice = this.invoices[0];
                let lastInvoiceDate = moment(lastInvoice.to) ;
                let reservationLastDate = moment(this.reservation.date_out).subtract(1,'days');

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

                        this.showAutomatedInvoiceNotification = true;

                    }).catch(err => {
                    this.$toasted.show(err, {type: 'error'})
                });

            },
            checkIfCurrentReservationIsTheLastOneToCheckout(){
                this.isLoading = true;
                axios.get(`/nova-vendor/calender/group-reservation/${this.reservation.id}/is-last`)
                .then((response) => {
                    // note if it wasn't the last reservation , then it will be a simple checkout
                    if(!response.data.is_last){
                        this.simpleCheckout = true;
                        this.is_last_checkout = response.data.is_last;
                    }else{
                        this.simpleCheckout = false;
                        if(response.data.deposit_insurance_transactions & !response.data.withdraw_insurance_transactions){
                            Nova.$emit('add-retrieval-insurance-for-group-reservation' , response.data.deposit_insurance_transaction);
                            return;
                        }
                        this.group_balance = response.data.group_balance;
                        this.is_last_checkout = response.data.is_last;
                        if(this.group_balance > 0 ){
                            this.term_type = 1;
                             this.getTerms();
                        }
                        if(this.group_balance < 0 ){
                            this.term_type = 2;
                             this.getTerms();
                        }


                    }
                    this.$refs.checkOutGroupReservationModal.open();
                    this.isLoading = false;
                })
            },
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
        },
        mounted() {
            this.getSettings();
            this.locale = Nova.config.local;

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
                    })
            })

            Nova.$on('invoice-added' , ($invoices) => {
                this.invoices = $invoices ;
            });

            Nova.$on('hide-spinner' , () => {
                setTimeout(()=>{ this.spinnerLoad = false; }, 800);
            })

            Nova.$on('open-group-reservation-checkout' , () => {
                Nova.$emit('update-reservation');
                setTimeout(() => {
                    this.checkIfCurrentReservationIsTheLastOneToCheckout();
                }, 500);
            });

            Nova.$on('liquidation-type' , (val) => {
                this.liquidationType = val;
            })

            Nova.$on('remove-disabled' , () => {
                this.disabled = false ;
                this.disableAddPromissory = false;
            })

            Nova.$on('check-liquid-permission' , (val) => {
                this.fromPaymentVoucher = val;
                if(Nova.app.$hasPermission('liquidation of dues before departure')){
                   this.hasPermssionToLiquid = true;
                }else{
                    this.hasPermssionToLiquid = false;
                }
            })

            Nova.$on('open-checkout-modal-for-group-reservation' , (obj) => {
                this.reservation = obj.reservation;
                this.invoicesList = obj.shared_invoices;
                this.automatic_under_cleaning = obj.automatic_under_cleaning
                setTimeout(() => {
                    // this.checkIfCurrentReservationIsTheLastOneToCheckout();
                    this.open();
                }, 500);
            });
        },
        beforeDestroy(){
            Nova.$off('remove-disabled');
        }

    }
</script>

<style lang="scss">
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
