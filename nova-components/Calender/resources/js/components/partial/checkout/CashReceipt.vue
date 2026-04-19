<template>
  <div class="cash_receipt">
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

    <div class="formgroup" v-if="!termsLoading && liquidationType != 'promissory'">
      <label>{{__('Type')}}<span>*</span></label>
      <select v-model="kind" @change="updateFor($event)">
        <option value="" disabled selected> {{__('Select Option')}}</option>
        <option v-for="(term,index) in terms" :key="index"  :value="term.id" v-if="term.deleteable == 1" >{{term.name[local]}}</option>
      </select>
    </div><!-- formgroup -->
    <div class="row_group">
      <div class="col">
        <label v-if="liquidationType == 'promissory'">{{__('Due Date')}}<span>*</span></label>
        <label v-else>{{__('Date')}}<span>*</span></label>
        <vcc-date-picker
          :input-props='{readonly: true}'
          mode='single'
          @input="updateDate"
          :value="new Date()"
          show-caps
          v-model="datePicker"
          :locale="locale"
          :max-date="liquidationType == 'promissory' ? null : new Date()"
          :min-date="liquidationType == 'promissory' ? new Date() : null"
          :masks="{ dayPopover : 'WWW, MMM D, YYYY' , weekdays: 'WWW' }"
        :popover="{ placement: 'bottom', visibility: 'click' }"
        >
        </vcc-date-picker>
      </div><!-- col -->
      <div class="col" v-if="liquidationType != 'promissory'">
        <label>{{__('From')}}<span>*</span></label>
        <input type="text" v-model="from" ref="from" :placeHolder="__('From')">
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
        <select :placeholder="__('Payment Type')" v-model="type" @change="checkPaymentType">
          <option value="" disabled selected>{{__('Select Payment Type')}}</option>
          <option value="cash">{{__('Cash')}}</option>
          <option value="bank-transfer">{{__('Bank Transfer')}}</option>
          <option value="mada">{{__('Mada')}}</option>
          <option value="credit">{{__('Credit Card')}}</option>
        </select>
      </div><!-- col -->
      <div class="col" v-if="ref && liquidationType != 'promissory'">
        <label>{{__('Payment Reference')}}</label>
        <input type="text" v-model="reference" :placeHolder="__('Payment Reference')">
      </div><!-- col -->
    </div><!-- row_group -->
    <div class="formgroup" v-if="!termsLoading">
      <label>{{__('Notes')}}</label>
      <input type="text" v-model="note" :placeHolder="__('Notes')">
    </div><!-- formgroup -->
  </div>
</template>

<script>
    import momenttimezone from 'moment-timezone'
    export default {
        name: "CheckoutCashReceipt",
        props: {
            reservation: Object,
            balance: null,
            terms : [],
            can_add_promissory : Boolean
        },
        data: () => {
            return {
                loading: false,
                kind: null,
                local: Nova.config.local,
                date: momenttimezone().tz("Asia/Riyadh").format('YYYY-MM-DD HH:mm'),
                datePicker: moment().toDate(),
                from: null,
                termsLoading: true,
                amount: null,
                type: null,
                description: null,
                note: null,
                ref: false,
                reference: null,
                locale:'en',
                employee : '',
                liquidationType : 'transaction',
                due_location :  null,
                due_owner : null,
                currentUser : Nova.config.user,
                currentTeamId : Nova.config.user.current_team_id,
                due_for : null
            }
        },
        methods: {

            updateFor(event){
                let term_id = this.kind ;
                Nova.request().get('/nova-vendor/calender/term?id=' + term_id)
                    .then((res)=>{
                        if(this.reservation.reservation_type == 'single'){
                            this.description = res.data.name[this.local] + ' - ' + this.__('Unit') + ' - ' + this.reservation.unit.unit_number ;
                        }else{
                            this.description = res.data.name[this.local];
                        }
                    });
            },
            checkPaymentType() {
                if (this.type == 'cash') {
                    this.ref = false;
                } else {
                    this.ref = true;
                }
            },
            addPromissory(){
              if (!this.due_location)
              {
                    this.$toasted.show(this.__("Please Enter Due Location"), {type: 'error'})
                    Nova.$emit('remove-disabled');
                    return false;
              }
              if (!this.due_owner)
              {
                    this.$toasted.show(this.__("Please Enter Due Owner"), {type: 'error'})
                    Nova.$emit('remove-disabled');
                    return false;
              }

              if (!this.due_for)
              {
                    this.$toasted.show(this.__("Please Enter Due For"), {type: 'error'})
                    Nova.$emit('remove-disabled');
                    return false;
              }

              let obj = {
                due_location : this.due_location,
                due_owner : this.due_owner,
                due_date : this.date,
                due_for : this.due_for,
                total_amount : this.amount,
                notes : this.note,
                team_id : this.reservation.team_id,
                user_id : this.currentUser.id,
                customer_id : this.reservation.customer_id,
                reservation_id : this.reservation.id,
              };

              return  axios.post(window.FANDAQAH_API_URL+ '/promissories/create' , obj)
                    .then(response => {
                        Nova.$emit('update-reservation');
                        this.$toasted.show(this.__('Promissory has been added successfully'), {type: 'success'});
                        this.due_location = null;
                        this.due_owner = null;
                        this.note = null;
                        this.date = new Date();
                        this.loading = false;
                    })
            },
            send() {

                Nova.$emit('hide-spinner');

                if (!this.kind) {
                    this.$toasted.show(this.__("Please Enter Type!"), {type: 'error'})
                    Nova.$emit('remove-disabled');
                    return false;
                }
                if (!this.date) {
                    this.$toasted.show(this.__("Please Enter Date!"), {type: 'error'})
                    Nova.$emit('remove-disabled');
                    return false;
                }
                if (!this.amount) {
                    this.$toasted.show(this.__("Please Enter Amount!"), {type: 'error'})
                    Nova.$emit('remove-disabled');
                    return false;
                }
                if (!this.description) {
                    this.$toasted.show(this.__("Please Enter For!"), {type: 'error'})
                    Nova.$emit('remove-disabled');
                    return false;
                }
                if (!this.type) {
                    this.$toasted.show(this.__("Please Enter Payment Type!"), {type: 'error'})
                    Nova.$emit('remove-disabled');
                    return false;
                }

                Nova.$off('remove-disabled');
                return axios
                    .post('/nova-vendor/calender/reservation/transaction', {
                        id: this.reservation.id,
                        type: 'deposit',
                        amount: this.amount,
                        meta: {
                            category: 'reservation',
                            statement: this.description,
                            type: this.kind,
                            payment_type: this.type,
                            note: this.note,
                            reference: this.reference,
                            date: this.date + ' ' + new moment().format("HH:mm") ,
                            from: this.from,
                            employee:this.employee
                        }
                    })
                    .then(response => {
                        this.deposit_type = null;
                        this.amount = 0;
                        this.reference = 0;
                        this.type = null;
                        this.kind = null;
                        this.from = null;
                        Nova.$emit('update-reservation');

                        this.$toasted.show(this.__('Transaction added successfully'), {type: 'success'});
                        this.loading = false;
                        return true;
                    }).catch(err => {
                    this.loading = false;
                    this.$toasted.show(this.__(err), {type: 'error'})
                    return false;
                })

            },
            updateDate() {
                if (this.datePicker === null) {
                    this.datePicker = moment(this.date).toDate()
                    return;
                }
                this.date = moment(String(this.datePicker)).format('YYYY-MM-DD')
            }
        },
        mounted() {
            this.termsLoading = true;
            this.termsLoading = false;
            this.from =  this.reservation.reservation_type == 'single' ? this.reservation.customer.name : this.reservation.company.name;
            this.amount = this.reservation.reservation_type == 'single' ?  Math.abs(this.reservation.balance/ (this.reservation.wallet.decimal_places == 3 ? 1000 : 100)).toFixed(2) : Math.abs(this.balance);
            this.locale =  Nova.config.local ;
            this.employee = Nova.config.user.name ;
            if(this.amount < 0) {
                this.amount = this.amount * -1;
            }

            this.liquidationType = 'transaction';

            Nova.$on('liquidation-type-closed' , (val) => {
              this.liquidationType = val;
            })
        },
        watch: {
            balance(newVal) {
                this.amount = Math.abs(newVal/ (this.reservation.wallet.decimal_places == 3 ? 1000 : 100)).toFixed(2);
                if(this.amount < 0) {
                    this.amount = this.amount * -1;
                }
            },
            liquidationType(newVal){
              if(newVal == 'promissory'){
                if(this.reservation.reservation_type == 'single'){
                    this.due_for = Nova.app.__('Rent Due On') + ' - ' + this.reservation.unit.name[this.locale]  + ' - ' + this.reservation.unit.unit_number;
                }else{
                    this.due_for = Nova.app.__('Rent Due On Group Reservation')
                }
              }else{
                this.due_for = null;
              }
              Nova.$emit('liquidation-type' , newVal)
            }

        }
    }
</script>

<style lang="scss">
.cash_receipt {
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
