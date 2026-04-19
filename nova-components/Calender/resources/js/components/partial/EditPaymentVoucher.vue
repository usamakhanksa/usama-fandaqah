<template>
  <sweet-modal :enable-mobile-fullscreen="false"  :pulse-on-block="false" :title="__('Edit Payment Vouchers') + ' ' + transaction.number " overlay-theme="dark" ref="editpaymentvoucher" @close="close" class="Edit_Payment_Voucher_Modal">
    <loading :active.sync="loader" :can-cancel="true" :loader="'spinner'" :color="'#7e7d7f'" :is-full-page="false"></loading>
    <div class="formgroup" v-if="!termsLoading">
      <label>{{__('Type')}} <span>*</span></label>
      <select v-model="kind" @change="updateFor" :disabled="disableFields">
        <option value="" disabled selected>{{__('Select Option')}}</option>
        <option v-for="(term,index) in terms" :key="index" :value="term.id" v-if="term.deleteable == 1 || term.name['ar'] == 'استرجاع تامين' ">{{term.name[local]}}</option>
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
        <input type="tel" min="0"  v-model="amount" :disabled="disableFields" :placeHolder="__('Amount')">
      </div><!-- col-->
      <div class="col">
        <label>{{__('For')}}<span>*</span></label>
        <input type="text" v-model="description" :disabled="disableFields" :placeHolder="__('For')">
      </div><!-- col-->
    </div><!-- row_group-->
    <div class="row_group">
      <div class="col">
        <label>{{__('Received By')}}<span>*</span></label>
        <input type="text" v-model="received_by" :disabled="disableFields" :placeHolder="__('Received By')">
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
        <label>{{__('Payment Reference')}}</label>
        <input type="text" v-model="reference" :placeHolder="__('Payment Reference')">
      </div><!-- col-->
      <div class="col">
        <label>{{__('Notes')}}</label>
        <input type="text" v-model="note" :placeHolder="__('Notes')">
      </div><!-- col-->
    </div><!-- row_group-->
    <button v-show="!hideAddBtn" :disabled="loading" @click="send">{{__('Save')}}</button>
  </sweet-modal>
</template>

<script>
    import flatPickr from 'vue-flatpickr-component';
    import 'flatpickr/dist/flatpickr.css';
    import { Arabic } from "flatpickr/dist/l10n/ar.js"
    import Loading from 'vue-loading-overlay';
    import 'vue-loading-overlay/dist/vue-loading.css';
    export default {
        name: "edit-payment-voucher",
        props: ["transaction", 'reservation', 'show'],
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
                employee : '',
                calendarLocale : 'en',
                canChangeTransactionDate : false,
                termName : null,
                hideAddBtn : false,
                disableFields : false,
                 loader: false
            }
        },
        watch: {
            show: function (newQuestion, oldQuestion) {
                if(newQuestion){
                    this.open()
                }
            },

          amount(num) {
              this.amount = num.replace(/([٠١٢٣٤٥٦٧٨٩])|([۰۱۲۳۴۵۶۷۸۹])/g, (m, $1, $2) => m.charCodeAt(0) - ($1 ? 1632 : 1776))
              this.amount = this.amount.replace(/[^\d.\d]/g,'')
          }
        },
         computed:{
            dateFormat() {
                return  'Y-m-d H:i'
            },
        },
        methods: {
            updateFor(event){

                    Nova.request().get(`/nova-vendor/calender/term?id=${this.kind}`)
                        .then((res)=>{
                            if(this.$parent.reservation.company){
                                this.description = res.data.name[this.local];
                                if(this.$parent.reservation.group_reservation){
                                    this.description +=  ' - '  + this.$parent.reservation.group_reservation.reservations.length + ' ' + this.__('Unit');
                                }
                            }else{
                                this.description = res.data.name[this.local] + ' - ' + this.__('Unit') + ' - ' + this.$parent.reservation.unit.unit_number ;
                            }
                            this.termName = res.data.name['ar'];
                            if(this.termName == 'استرجاع تامين'){

                                if(this.reservation.reservation_type == 'single'){
                                    if(!this.reservation.deposit_insurance_transactions.length){
                                        this.$toasted.show(this.__('We can not add retrieval insurance transaction cause there is no insurance transaction added'), {type: 'error'});
                                        this.hideAddBtn = true;
                                        return;
                                    }

                                    if(this.reservation.withdraw_insurance_transactions.length && this.reservation.deposit_insurance_transactions.length){

                                        this.$toasted.show(this.__('We can not add new retrieval insurance transaction cause there is already one added'), {type: 'error'});
                                        this.hideAddBtn = true;
                                        return;
                                    }

                                    if(this.reservation.deposit_insurance_transactions.length){

                                        if(Math.abs(this.transaction.amount) !=  this.reservation.deposit_insurance_transactions[0].amount){
                                            this.$toasted.show(this.__('We can not change this transaction to retrieval insurance transaction'), {type: 'error'});
                                            this.hideAddBtn = true;
                                            return;
                                        }

                                    }

                                }


                                if(this.reservation.reservation_type == 'group'){
                                    this.loader = true;
                                    axios.get(`/nova-vendor/calender/group-reservation/sibling/${this.$parent.reservation.id}/check-insurance-transaction?from=edit_withdraw`)
                                    .then((response) => {
                                        if(!response.data.deposit_insurance_transactions){
                                            this.$toasted.show(this.__('We can not add retrieval insurance transaction cause there is no insurance transaction added'), {type: 'error'});
                                            this.hideAddBtn = true;
                                            this.loader = false;
                                            return;
                                        }

                                        if(response.data.withdraw_insurance_transactions && response.data.deposit_insurance_transactions){
                                            this.$toasted.show(this.__('We can not add new retrieval insurance transaction cause there is already one added'), {type: 'error'});
                                            this.hideAddBtn = true;
                                            this.loader = false;
                                            return;
                                        }

                                        if(response.data.deposit_insurance_transactions){
                                            if(Math.abs(this.transaction.amount) !=   response.data.deposit_insurance_transaction.amount ){
                                                this.$toasted.show(this.__('We can not change this transaction to retrieval insurance transaction'), {type: 'error'});
                                                this.hideAddBtn = true;
                                                this.loader = false;
                                                return;
                                            }
                                        }
                                    });
                                }
                                 this.loader = false;
                                this.hideAddBtn = false;
                            }else{
                                this.hideAddBtn = false;
                            }
                        });
            },
            fillTermName(){
                Nova.request().get(`/nova-vendor/calender/term?id=${this.kind}`)
                        .then(res =>{
                            this.termName = res.data.name['ar'];
                            if(this.reservation.reservation_type == 'group'){
                                this.loader = true;
                                axios.get(`/nova-vendor/calender/group-reservation/sibling/${this.$parent.reservation.id}/check-insurance-transaction?from=edit_withdraw`)
                                    .then((response) => {
                                        if(this.termName == 'استرجاع تامين' && this.transaction.is_insurance && response.data.deposit_insurance_transactions){
                                            this.disableFields = true;
                                            this.loader = false;
                                        }else{
                                            this.disableFields = false;
                                            this.loader = false;
                                        }
                                    })
                            }else{
                                if(this.termName == 'استرجاع تامين' && this.transaction.is_insurance && this.reservation.deposit_insurance_transactions.length){
                                    this.disableFields = true;
                                }else{
                                    this.disableFields = false;
                                }
                            }

                        });
            },
            open() {

                this.disableFields = false;
                this.hideAddBtn = false;
                this.kind = this.transaction.meta.type;
                 this.fillTermName();
                this.from =  this.transaction.meta && this.transaction.meta.from ? this.transaction.meta.from :  this.reservation.customer.name;
                this.received_by = this.transaction.meta && this.transaction.meta.received_by ? this.transaction.meta.received_by :  this.reservation.customer.name;
                this.date = this.transaction.meta.date;
                this.amount = Math.abs(this.reservation.wallet.decimal_places == 3 ? this.transaction.amount / 1000 : this.transaction.amount / 100 );
                this.type = this.transaction.meta.payment_type;
                this.description = this.transaction.meta.statement;
                this.note = this.transaction.meta.note;
                this.reference = this.transaction.meta.reference;
                this.checkPaymentType();
                this.getTerms();
                this.$refs.editpaymentvoucher.open();

            },
            checkPaymentType(){
                if(this.type == 'cash'){
                    this.ref = false;
                    this.reference = null
                } else {
                    this.ref = true;
                    this.reference = this.transaction.meta.reference;
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

                // if(this.ref && !this.reference){
                //     this.$toasted.show(this.__("Please Enter Payment Reference!"), {type: 'error'})
                //     return
                // }

                // return ;

                this.loading = true;
                axios
                    .put('/nova-vendor/calender/reservation/transaction', {
                        id: this.transaction.id,
                        amount: this.amount,
                        type: 'withdraw',
                        termName :  this.termName,
                        meta: {
                            category: 'reservation',
                            statement: this.description,
                            type: this.kind,
                            payment_type: this.type,
                            note: this.note,
                            reference: this.reference,
                            date: this.date,
                            received_by: this.received_by,
                            from: this.from,
                            employee:this.employee
                        }
                    })
                    .then(response => {
                         Nova.$emit('update-reservation')
                        this.$refs.editpaymentvoucher.close();
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

            },
            updateDate(r) {
                // console.log(r);
                if (!r) {
                    this.datePicker = moment().toDate()
                    this.date = moment(String(r)).format('YYYY-MM-DD')
                }
                this.date = moment(String(r)).format('YYYY-MM-DD')
            },
            getTerms() {
                Nova.request()
                    .get('/nova-vendor/calender/terms/payment-voucher')
                    .then(response => {
                        // console.log(response.data);
                        this.terms = response.data;
                        if(this.terms.length == 0){
                            this.$toasted.show(this.__('Please Add Terms First '), {type: 'info', action : {
                                    text : 'Settings',
                                    push : {
                                        name : 'settings',
                                        // this will prevent toast from closing
                                        dontClose : true
                                    }
                                },
                            });

                        }
                        this.termsLoading = false;
                    }).catch(err => {
                    this.serviceLoading = false;
                    this.$toasted.show(this.__(err), {type: 'error'})
                })
            },
            close() {
               this.date = this.transaction.meta.date;
               this.checkPaymentType();
               this.type = this.transaction.meta.payment_type;
               this.$emit("update:show", false);
            }
        },
        mounted() {
            this.termsLoading = true;
            this.employee = Nova.config.user.name ;
            // console.log("this.datePicker", this.datePicker)

            this.calendarLocale = Nova.config.local;
        },

    }
</script>

<style lang="scss">
.Edit_Payment_Voucher_Modal {
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
} /* Edit_Payment_Voucher_Modal */
</style>
