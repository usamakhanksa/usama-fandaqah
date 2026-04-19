<template>
  <div>
    <button v-if="type"  v-permission="'create financial'" class="add_receipts" @click="open">{{type === 'deposit' ? __('Add deposit transaction') : __('Add withdraw transaction') }}</button>
    <sweet-modal :enable-mobile-fullscreen="false"  :pulse-on-block="false" :title="type === 'deposit' ? __('New Deposit Transaction') : __('New Withdraw Transaction') " overlay-theme="dark" ref="modal" class="Deposit_Transaction_Modal">

      <div class="input_group" v-if="terms">
        <label>{{__('Type')}}<span>*</span></label>
        <select v-model="kind" @change="updateFor($event)">
          <option  value="" disabled selected> {{__('Select Option')}}</option>
          <option v-for="term in terms"  :value="term.id">{{term.name[local]}}</option>
        </select>
      </div><!-- input_group -->
      <div class="row_group">
        <div class="col">
          <label>{{__('Date')}}<span>*</span></label>
          <flat-pickr 
            v-model="date"
            class="form-control" 
            :placeholder="__('Select Transaction Date')" 
            :config="config"
            :disabled="!canChangeTransactionDate"
          >
          </flat-pickr>
          <!-- <vcc-date-picker
          :input-props='{ readonly: true }'
          mode='single'
          @input="updateDate"
          :value='new Date(date)'
          v-model='datePicker'
          :max-date='new Date(date)'
          :locale="locale"
          :masks="{ dayPopover : 'WWW, MMM D, YYYY' , weekdays: 'WWW' }"
          :popover="{ placement: 'bottom', visibility: !canChangeTransactionDate ? 'hide' : 'click' }"
          :disabled="!canChangeTransactionDate"
        >
        </vcc-date-picker> -->
        </div><!-- col -->
        <div class="col">
            <label>{{type === 'withdraw' ? __('To') : __('From')}}<span>*</span></label>
          <input type="text" v-model="from" ref="from" :placeHolder="__('From')">
        </div><!-- col -->
      </div><!-- row_group -->
      <div class="row_group">
        <div class="col">
          <label>{{__('Amount')}}<span>*</span></label>
          <input type="tel" v-model="amount" :placeHolder="__('Amount')">
        </div><!-- col -->
        <div class="col">
          <label>{{__('For')}}<span>*</span></label>
          <input type="text" v-model="description" :placeHolder="__('For')">
        </div><!-- col -->
      </div><!-- row_group -->

      <div class="row_group" v-if="type == 'withdraw' && tax_percentage">
        <div class="col">
          <label>{{__('Amount Include Tax')}}<span v-if="!disable_amount_include_tax">*</span></label>
          <input type="tel" :disabled="disable_amount_include_tax" v-model="amount_include_tax" :placeHolder="__('Amount Include Tax')">
        </div>
        <div class="col">
          <label>{{__('Tax Amount')}}</label>
          <input type="tel" disabled v-model="tax_amount" :placeHolder="__('Tax Amount')">
        </div>
      </div>


      <div class="row_group" v-if="type == 'withdraw' && tax_percentage">
          <div class="col">
              <label>{{__('Supplier tax number')}}</label>
              <input type="text" :disabled="disable_supplier_tax_number" v-model="supplier_tax_number" :placeHolder="__('Supplier tax number')">
          </div>
          <div class="col">
              <label>{{__('Invoice number')}}</label>
              <input type="text" :disabled="disable_invoice_number" v-model="invoice_number" :placeHolder="__('Invoice number')">
          </div>
      </div>

        <div class="input_group" v-if="type === 'withdraw'">
            <div class="danger_msg" role="alert" v-show="noBalance">
                <span>{{__('Transfer from the safe to management')}}</span>
                <p>{{__('Sorry, there is not enough balance in the fund to complete the transfer')}}</p>
            </div><!-- danger_msg -->
            <div class="success_msg" role="alert" v-show="balanceFound">
                <span>{{__('Transfer from the safe to management')}}</span>
                <p>{{__('Please note that the maximum transferable amount is')}} <b>{{balance}}</b> {{__(currency)}}</p>
            </div><!-- success_msg -->
        </div><!-- input_group -->
      <div :class="this.type != 'deposit' ? 'row_group' : 'input_group'">
          <div class="col" v-if="type === 'withdraw'">
              <label>{{__('Received By')}}<span>*</span></label>
              <input type="text" v-model="received_by" :placeHolder="__('Received By')">
          </div><!-- col -->
        <div class="col">
          <label>{{__('Payment Type')}}<span>*</span></label>
          <select :placeholder="__('Payment Type')" v-model="payment_type" @change="checkPaymentType">
            <option value="" disabled selected>{{__('Select Payment Type')}}</option>
            <option value="cash">{{__('Cash')}}</option>
            <option value="bank-transfer">{{__('Bank Transfer')}}</option>
            <option value="mada">{{__('Mada')}}</option>
            <option value="credit">{{__('Credit Card')}}</option>
            <option v-if="type == 'deposit'" value="credit-payment">{{__('Credit Payment')}}</option>
          </select>
        </div><!-- col -->

      </div><!-- row_group -->


      <div>
        <div class="col">
          <label>{{__('Unit Category')}}</label>
          <select v-model="unit_category_id">
              <option value="" selected>{{__('Unit Category')}}</option>
              <option v-for="(category,i) in unit_categories" :value="category.value" :key="i">{{ category.name }}</option>
          </select>
        </div><!-- col -->

      </div><!-- row_group -->
        <div :class="ref || payment_type == 'credit-payment' ? 'row_group' : 'input_group'">
            <div class="col"  v-if="ref">
                <label>{{__('Payment Reference')}}</label>
                <input type="text" v-model="reference" :placeHolder="__('Payment Reference')">
            </div>

            <div class="col"  v-if="payment_type == 'credit-payment'">
              <label>{{__('Person In Charge')}}</label>
              <select v-model="person_in_charge">
                  <option value="" selected>{{__('Person In Charge')}}</option>
                  <option v-for="(employee,i) in employees"  :key="i">{{ employee.name }}</option>
              </select>
            </div>
            <div class="col">
                <label>{{__('Notes')}}</label>
                <input type="text" v-model="note" :placeHolder="__('Notes')">
            </div><!-- col -->

        </div><!-- row_group -->

      <div class="options_choose" v-if="type == 'withdraw' && tax_percentage">
        <p>{{__('Enable Vat On Withdraw')}} ({{tax_percentage}}%)</p>
        <div class="switch_label">
            <p></p>
            <label class="switch">
                <input type="checkbox" v-model="enable_tax_on_withdraw" @change="enable_tax_on_withdraw_changed">
                <span class="slider round"></span>
            </label>
        </div>
      </div>

      <button :disabled="loading" @click="send">{{__('Save')}}</button>
    </sweet-modal>
  </div>
</template>

<script>
   import flatPickr from 'vue-flatpickr-component';
   import 'flatpickr/dist/flatpickr.css';
   import { Arabic } from "flatpickr/dist/l10n/ar.js"

    export default {
        name: "add-modal",
        props : ['type','terms','unit_categories'],
        components : {
          flatPickr
        },
        data: () => {
            return {
                loading: null,
                kind: null,
                local: Nova.config.local,
                date: moment().format('YYYY-MM-DD'),
                datePicker: moment().toDate(),
                from: null,
                amount: null,
                payment_type: null,
                description: null,
                note: null,
                ref: false,
                reference: null,
                employee : '',
                locale:'en',
                canChangeTransactionDate : false,
                noBalance : false,
                balanceFound : false,
                received_by : null,
                termName : null,
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
                team_id : Nova.config.user.current_team_id,
                currency :Nova.app.currentTeam.currency,
                server_date : null,
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
                  clickOpens : Nova.app.$hasPermission('change transactions date') && Nova.app.$hasPermission('add receipts') ? true : false,
                  maxDate : new Date(),
                },   
                unit_category_id : ''
            }
        },

        watch: {
          amount(num) {

              if(!num){
                this.amount_include_tax = null;
                this.tax_amount = null;
              }else{
                this.amount = num.replace(/([٠١٢٣٤٥٦٧٨٩])|([۰۱۲۳۴۵۶۷۸۹])/g, (m, $1, $2) => m.charCodeAt(0) - ($1 ? 1632 : 1776))
                this.amount = this.amount.replace(/[^\d.\d]/g,'');
                if(this.enable_tax_on_withdraw){
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
                let v = this.tax_percentage / 100;
                let y = parseFloat(x / (1 + v)).toFixed(2);
                this.amount = parseFloat(y);
                this.tax_amount = parseFloat( parseFloat(this.amount_include_tax - this.amount).toFixed(2));
              }else{
                if(this.enable_tax_on_withdraw){
                  this.amount = null;
                }
                this.tax_amount = null;
              }

          }
        },
        methods: {
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
              this.amount_include_tax = parseFloat(this.amount) + parseFloat((this.amount * (this.tax_percentage / 100)));
              this.tax_amount = parseFloat(parseFloat(this.amount_include_tax - this.amount).toFixed(2));
            }
          },
            getEmployees(){
              this.person_in_charge = '';
                axios.get(window.FANDAQAH_API_URL + `/users/dropDown?team_id=${this.team_id}`)
                    .then((response) => {
                        this.employees = response.data.data;
                    })
            },
            updateFor(event){
                // if(!this.description)
                    this.description = this.terms[event.target.options.selectedIndex-1].name[this.local]
                    this.termName = this.terms[event.target.options.selectedIndex-1].name['ar'];
            },
            open() {
                if(this.type == 'withdraw'){
                  this.getVatSetting();
                }
                this.from = '';
                this.amount = null;
                this.payment_type = null;
                this.description = null;
                this.note = null;
                this.reference = null;
                this.ref = false;
                this.received_by = null;
                this.termName = null;
                this.amount_include_tax = null;
                this.tax_amount = null
                this.supplier_tax_number = null;
                this.invoice_number = null;
                this.loading = false;
                this.unit_category_id = '';
                if(Nova.app.$hasPermission('create financial') && Nova.app.$hasPermission('change transactions date')){
                    this.canChangeTransactionDate = true;
                }

                this.getServerDate();
                this.$refs.modal.open()

            },


            checkPaymentType() {
                if (this.payment_type == 'cash' || this.payment_type == 'credit-payment') {
                    this.ref = false;
                    if(this.payment_type == 'credit-payment'){
                      this.getEmployees();
                    }
                } else {
                    this.ref = true;
                }
            },
            send() {
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

                if(!this.from){

                    this.$toasted.show(this.type === 'withdraw' ? this.__("Please Enter from!") : this.__("Please Enter From!"), {type: 'error'})
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



                if (!this.received_by && this.type === 'withdraw') {
                    this.$toasted.show(this.__("Please Enter Received By!"), {type: 'error'})
                    return
                }


                this.loading = true;


                let metaObj = {};
                if(this.type === 'withdraw'){

                  metaObj = {
                        category: `${this.type}-transaction`,
                        statement: this.description,
                        type: this.kind,
                        payment_type: this.payment_type,
                        note: this.note,
                        reference: this.reference,
                        date: this.date ,
                        received_by: this.received_by,
                        from: this.from,
                        employee:this.employee
                    };
                }else{
                     metaObj = {
                        category: `${this.type}-transaction`,
                        statement: this.description,
                        type: this.kind,
                        payment_type: this.payment_type,
                        note: this.note,
                        reference: this.reference,
                        date: this.date ,
                        from: this.from,
                        employee:this.employee,
                        person_in_charge : this.person_in_charge
                    };
                }

             
                axios
                    .post('/nova-vendor/financial-management/storeTransaction', {

                        team_id : Nova.config.user.current_team_id,
                        type: this.type,
                        amount: this.amount,
                        termName : this.termName,
                        meta: metaObj,
                        amount_include_tax : this.amount_include_tax,
                        tax_amount : this.tax_amount,
                        supplier_tax_number : this.supplier_tax_number,
                        invoice_number : this.invoice_number,
                        enable_tax_on_withdraw : this.enable_tax_on_withdraw,
                        tax_percentage : this.tax_percentage,
                        unit_category_id : this.unit_category_id
                    })
                    .then(response => {

                        if(response.data === 'invalid-date'){
                            this.$toasted.show(this.__('Invalid Date Format'), {type: 'error'});
                            this.loading = false;
                            return;
                        }
                        this.deposit_type = null;
                        this.amount = 0;
                        this.reference = 0;
                        this.type = null;
                        this.kind = null;
                        this.from = null;
                        if(this.type === 'withdraw'){
                            this.received_by = null;
                        }
                        Nova.$emit('add-transaction');
                        this.$refs.modal.close();
                        this.$toasted.show(this.__('Transaction added successfully'), {type: 'success'});
                        this.loading = false;
                    }).catch(err => {
                    this.loading = false;
                    this.$toasted.show(this.__(err), {type: 'error'})
                })

            },

            updateDate(r) {
                if (this.datePicker === null) {
                    this.datePicker = moment(this.date).toDate()
                    return;
                }
                this.date = moment(String(this.datePicker)).format('YYYY-MM-DD H:mm')
            },
            getVatSetting(){
              axios.get('/nova-vendor/financial-management/vat-setting')
              .then(response => {
                this.tax_percentage = response.data.vat;
                this.disable_amount_include_tax = true;
                this.disable_invoice_number = true;
                this.disable_supplier_tax_number = true;
                // if(this.tax_percentage) {
                  this.enable_tax_on_withdraw = false;
                // }
              })
            },
            getServerDate(){
                Nova.request()
                .get('/nova-vendor/calender/server/current-date')
                .then(response => {
                  this.server_date = response.data;
                  this.config.maxDate = new Date(this.server_date);
                  this.date = new Date(this.server_date);
                })
            }
        },
        mounted() {
            this.getServerDate();
            this.employee = Nova.config.user.name ;
            this.locale =  Nova.config.local ;
        },

    }
</script>

<style lang="scss">
.Deposit_Transaction_Modal {
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
} /* Deposit_Transaction_Modal */



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
</style>
