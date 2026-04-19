<template>
  <div class="payment_voucher">
    <div class="formgroup" v-if="!termsLoading">
      <label>{{__('Type')}}<span>*</span></label>
      <select v-model="kind" @change="updateFor">
        <option value="" disabled selected>{{__('Select Option')}}</option>
        <option v-for="(term,index) in terms" :key="index" :value="term.id" v-if="term.deleteable == 1">{{term.name[local]}}</option>
      </select>
    </div><!-- formgroup -->
    <div class="row_group">
      <div class="col">
        <label>{{__('Date')}} <span>*</span></label>
        <vcc-date-picker
          :input-props='{ readonly: true }'
          mode='single'
          @input="updateDate"
          @change="updateDate"
          :value='new Date()'
          show-caps
          v-model='datePicker'
          :max-date='new Date()'
          :locale="locale"
          :masks="{ dayPopover : 'WWW, MMM D, YYYY' , weekdays: 'WWW' }"
        >
        </vcc-date-picker>
      </div><!-- col -->
      <div class="col">
        <label>{{__('To')}} <span>*</span></label>
        <input type="text" v-model="from" ref="from" :placeHolder="__('To')">
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
    <div class="row_group">
      <div class="col">
        <label>{{__('Received By')}}<span>*</span></label>
        <input type="text" v-model="received_by" :placeHolder="__('Received By')">
      </div><!-- col -->
      <div class="col">
        <label>{{__('Payment Type')}} <span>*</span></label>
        <select placeholder="Payment Type" v-model="type" @change="checkPaymentType">
          <option value="" disabled selected>{{__('Select Payment Type')}}</option>
          <option value="cash">{{__('Cash')}}</option>
          <option value="bank-transfer">{{__('Bank Transfer')}}</option>
          <option value="mada">{{__('Mada')}}</option>
          <option value="credit">{{__('Credit Card')}}</option>
        </select>
      </div><!-- col -->
    </div><!-- row_group -->
    <div class="row_group">
      <div class="col" v-if="ref">
        <label>{{__('Payment Reference')}}</label>
        <input type="number" v-model="reference" :placeHolder="__('Payment Reference')">
      </div><!-- col -->
      <div class="col">
        <label>{{__('Notes')}}</label>
        <input type="text" v-model="note" :placeHolder="__('Notes')">
      </div><!-- col -->
    </div><!-- row_group -->
  </div>
</template>

<script>
    import momenttimezone from 'moment-timezone'
    export default {
        name: "CheckoutCashReceipt",
        props: {
            reservation: Object,
            balance: null,
            terms : []
        },
        data: () => {
            return {
                formValidate: {
                    type: '',
                },
                ruleValidate: {
                    type: [
                        { required: true, message: 'Please select the city', trigger: 'change' }
                    ],
                },
                loading: false,
                kind: null,
                local: Nova.config.local,
                date: momenttimezone().tz("Asia/Riyadh").format('YYYY-MM-DD HH:mm'),
                datePicker: moment().toDate(),
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
                employee:''
            }
        },
        methods: {
            updateFor(event) {
                let term_id = this.kind;
                Nova.request().get('/nova-vendor/calender/term?id=' + term_id)
                    .then((res) => {
                        if(this.reservation.reservation_type == 'single'){
                            this.description = res.data.name[this.local] + ' - ' + this.__('Unit') + ' - ' + this.reservation.unit.unit_number;
                        }else{
                            this.description = res.data.name[this.local];
                        }
                    });
            },
            checkPaymentType(){
                if(this.type == 'cash'){
                    this.ref = false;
                } else {
                    this.ref = true;
                }
            },
            send() {


                Nova.$emit('hide-spinner');


                if(!this.kind){
                    this.$toasted.show(this.__("Please Enter Type!"), {type: 'error'})
                    Nova.$emit('remove-disabled');
                    return false;
                }

                if(!this.date){
                    this.$toasted.show(this.__("Please Enter Date!"), {type: 'error'})
                    Nova.$emit('remove-disabled');
                    return false;
                }

                if(!this.from){
                    this.$toasted.show(this.__("Please Enter from!"), {type: 'error'})
                    Nova.$emit('remove-disabled');
                    return false;
                }

                if(!this.amount){
                    this.$toasted.show(this.__("Please Enter Amount!"), {type: 'error'})
                    Nova.$emit('remove-disabled');
                    return false;
                }


                if(!this.description){
                    this.$toasted.show(this.__("Please Enter For!"), {type: 'error'})
                    Nova.$emit('remove-disabled');
                    return false;
                }
                if(!this.received_by){
                    this.$toasted.show(this.__("Please Enter Received By!"), {type: 'error'})
                    Nova.$emit('remove-disabled');
                    return false;
                }


                if(!this.type){
                    this.$toasted.show(this.__("Please Enter Payment Type!"), {type: 'error'})
                    Nova.$emit('remove-disabled');
                    return false;
                }

                Nova.$off('remove-disabled');
                this.loading = true;
                return axios
                    .post('/nova-vendor/calender/reservation/transaction', {
                        id: this.reservation.id,
                        type: 'withdraw',
                        amount: this.amount,
                        meta: {
                            category: 'reservation',
                            statement: this.description,
                            type: this.kind,
                            payment_type: this.type,
                            note: this.note,
                            reference: this.reference,
                            date: this.date + ' ' + new moment().format("HH:mm") ,
                            received_by: this.received_by,
                            from: this.from,
                            employee: this.employee,
                        }
                    })
                    .then(response => {
                        this.deposit_type = null;
                        this.amount = 0;
                        this.reference = 0;
                        this.type = null;
                        this.kind = null;
                        this.from = null;
                        this.$emit('update-reservation')
                        this.$toasted.show(this.__('Transaction added successfully'), {type: 'success'});
                        this.loading = false;
                    }).catch(err => {
                    this.loading = false;
                    this.$toasted.show(this.__(err), {type: 'error'})
                })
            },
            updateDate() {
                if (this.datePicker === null) {
                    this.datePicker = moment(this.date).toDate()
                    return;
                }
                this.date = moment(String(this.datePicker)).format('YYYY-MM-DD')
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
                        this.termsLoading = false;
                    }).catch(err => {
                    this.serviceLoading = false;
                    this.$toasted.show(this.__(err), {type: 'error'})
                })
            }
        },
        mounted() {
            this.termsLoading = true;
            this.getTerms();
            this.termsLoading = false;
            this.locale =  Nova.config.local ;
            this.from = this.reservation.reservation_type == 'single' ?  this.reservation.customer.name : this.reservation.company.name;
            this.received_by = this.reservation.customer.name;
            this.employee = Nova.config.user.name ;
            this.amount = this.reservation.reservation_type == 'single' ?   Math.abs(this.reservation.balance / (this.reservation.wallet.decimal_places == 3 ? 1000 : 100)).toFixed(2) : Math.abs(this.balance);
            if(this.amount < 0) {
                this.amount = this.amount * -1;
            }
        },
        watch: {
            balance(newVal) {
                this.amount = Math.abs(newVal/ (this.reservation.wallet.decimal_places == 3 ? 1000 : 100)).toFixed(2);
                if(this.amount < 0) {
                    this.amount = this.amount * -1;
                }
            }
        }
    }
</script>

<style lang="scss">
.payment_voucher {
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
  } /* select */
} /* cash_receipt */
</style>
