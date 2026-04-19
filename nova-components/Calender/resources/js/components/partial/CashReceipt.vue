<template>
  <div>
    <!-- <button v-if="!quick && !reservation.checked_out && reservation.status == 'confirmed'" v-permission="'add receipts'" @click="open" class="cash_receipt_button">{{__('Cash Receipt')}}</button> -->
    <button v-if="!quick && !reservation.checked_out && (reservation.status == 'confirmed' || reservation.status == 'awaiting-payment' || reservation.status == 'awaiting-confirmation')" v-permission="'add receipts'" @click="open" class="cash_receipt_button">{{__('Cash Receipt')}}</button>
    <button v-if="!quick && occ" v-permission="'add receipts'" @click="open" class="cash_receipt_button">{{__('Cash Receipt')}}</button>
    <sweet-modal :enable-mobile-fullscreen="false" class="cash_receipt_modal" :pulse-on-block="false" :title="__('Cash Receipt')" overlay-theme="dark" ref="modal">
      <loading :active.sync="loader" :can-cancel="true" :loader="'spinner'" :color="'#7e7d7f'" :is-full-page="false"></loading>
      <div class="formgroup">
        <label>{{__('Type')}} <span>*</span></label>
        <select v-model="kind" @change="updateFor($event)">
          <option  value="" disabled selected> {{__('Select Option')}}</option>
          <template v-if="!termsLoading">
            <option v-for="(term,index) in terms" :key="index"  :value="term.id" v-if="term.deleteable == 1 || term.name['ar'] == 'تامين'" >{{term.name[local]}}</option>
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
          <label>{{__('From')}}<span>*</span></label>
          <input type="text" v-model="from" ref="from" :placeHolder="__('From')">
        </div><!-- col-->
      </div><!-- row_group-->
      <div class="row_group">
        <div class="col">
          <label>{{__('Amount')}}<span>*</span></label>
          <input type="tel" v-model="amount" :placeHolder="__('Amount')">
        </div><!-- col-->
        <div class="col">
          <label>{{__('For')}}<span>*</span></label>
          <input type="text" v-model="description" :placeHolder="__('For')">
        </div><!-- col-->
      </div><!-- row_group-->
      <div class="row_group">
        <div class="col">
          <label>{{__('Payment Type')}}<span>*</span></label>
          <select :placeholder="__('Payment Type')" v-model="type" @change="checkPaymentType">
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
      <div class="formgroup" v-if="ref">
        <label>{{__('Payment Reference')}}</label>
        <input type="text" v-model="reference" :placeHolder="__('Payment Reference')">
      </div><!-- formgroup-->
      <div class="formgroup" v-if="type == 'credit-payment'">
        <label>{{__('Person In Charge')}}</label>
        <select v-model="person_in_charge">
            <option value="" selected>{{__('Person In Charge')}}</option>
            <option v-for="(employee,i) in employees"  :key="i">{{ employee.name }}</option>
        </select>
      </div><!-- formgroup-->
      <button class="save_cash_receipt" v-show="!hideAddBtn" :disabled="btnDisabled" @click="send">{{__('Save')}}</button>
    </sweet-modal>
  </div>
</template>

<script>
    import momenttimezone from 'moment-timezone';
    // flatpicker 
    import flatPickr from 'vue-flatpickr-component';
    import 'flatpickr/dist/flatpickr.css';

    import { Arabic } from "flatpickr/dist/l10n/ar.js"

    import Loading from 'vue-loading-overlay';
    import 'vue-loading-overlay/dist/vue-loading.css'; 
  
    export default {
        name: "CashReceipt",
        props : ['quick','occ','group_balance' , 'reservation'],
        components: {
            Loading,
            flatPickr
        },
        computed:{
            dateFormat() {
                return  'Y-m-d H:i'
            },
        },
        data: () => {
            return {
                loading: null,
                kind: null,
                local: Nova.config.local,
                date: new Date( momenttimezone().tz("Asia/Riyadh").format('YYYY-MM-DD HH:mm')),
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
                description: null,
                note: null,
                ref: false,
                reference: null,
                locale:'en',
                employee : '',
                loader : false,
                reservation : null,
                quickModal : {
                    from : null ,
                    modal : null
                },
                canChangeTransactionDate : false,
                termName : null,
                hideAddBtn : false,
                btnDisabled : false,
                person_in_charge : '',
                employees : [],
                team_id : Nova.config.user.current_team_id,
                server_date : null
            }
        },
        methods: {
          getEmployees(){
            this.person_in_charge = '';
              axios.get(window.FANDAQAH_API_URL + `/users/dropDown?team_id=${this.team_id}`)
                  .then((response) => {
                      this.employees = response.data.data;
                  })
          },
            updateFor(event){

                let term_id = this.kind ;
                Nova.request().get('/nova-vendor/calender/term?id=' + term_id)
                    .then((res)=>{
                        if(this.reservation.company){
                            this.description = res.data.name[this.local];
                            // if(this.reservation.group_reservation){
                            //     this.description +=  ' - '  + this.reservation.group_reservation.reservations.length + ' ' + this.__('Unit');
                            // }
                        }else{
                            this.description = res.data.name[this.local] + ' - ' + this.__('Unit') + ' - ' + this.reservation.unit.unit_number ;
                        }
                        this.termName = res.data.name['ar'];

                        if(this.termName == 'تامين'){

                            if(this.reservation.reservation_type == 'single' && this.reservation.deposit_insurance_transactions.length){

                                this.$toasted.show(this.__('We can not add new insurance transaction cause there is already one added'), {type: 'error'});
                                this.hideAddBtn = true;
                                return;
                            }

                            if(this.reservation.reservation_type == 'group'){
                                this.loader = true;
                                axios.get(`/nova-vendor/calender/group-reservation/sibling/${this.reservation.id}/check-insurance-transaction?from=deposit`)
                                .then((response) => {
                                    if(!response.data.can_add_insurance_transaction){
                                        this.$toasted.show(this.__('We can not add new insurance transaction cause there is already one added'), {type: 'error'});
                                        this.hideAddBtn = true;
                                        this.loader = false;
                                        return;
                                    }
                                    this.loader = false;
                                })

                            }

                            this.hideAddBtn = false;
                        }else{
                             this.hideAddBtn = false;
                        }
                    });
                // this.description = this.terms[event.target.options.selectedIndex-1].name[this.local]
            },
            open() {
              this.getServerDate();
              this.btnDisabled = false;
              this.getTerms();
                this.hideAddBtn = false;
                this.kind = null;
                this.from = this.reservation.company ? this.reservation.company.name :  this.reservation.customer.name;
                this.amount = null;

                if(this.reservation.balance < 0 && this.reservation.reservation_type == 'single'){
                    this.amount = Math.abs(this.reservation.balance / (this.reservation.wallet.decimal_places == 3 ? 1000 : 100)).toFixed(2);
                }

                if(this.group_balance < 0 && this.reservation.reservation_type == 'group'){
                    this.amount = Math.abs(this.group_balance).toFixed(2);
                }

                this.type = null;
                this.description = null;
                this.note = null;
                this.reference = null;
                this.ref = false;
                this.quickModal.from = this.$refs.from;
                this.quickModal.modal = this.$refs.modal;

                if(this.quick){
                    if(this.reservation.id === this.reservation.id){
                            this.quickModal.modal.open();
                    }
                }else{
                    this.quickModal.modal.open();
                }
            },


            checkPaymentType() {
                if (this.type == 'cash' || this.type == 'credit-payment') {
                    this.ref = false;
                    if(this.type == 'credit-payment'){
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

                if (!this.description) {
                    this.$toasted.show(this.__("Please Enter For!"), {type: 'error'})
                    return
                }
                if (!this.type) {
                    this.$toasted.show(this.__("Please Enter Payment Type!"), {type: 'error'})
                    return
                }

                this.btnDisabled = true;

                 
                this.loading = true;
                this.loader = true;
                axios
                    .post('/nova-vendor/calender/reservation/transaction', {
                        id: this.reservation.id,
                        type: 'deposit',
                        amount: this.amount,
                        termName :  this.termName,
                        meta: {
                            category: 'reservation',
                            statement: this.description,
                            type: this.kind,
                            payment_type: this.type,
                            note: this.note,
                            reference: this.reference,
                            from: this.from,
                            employee:this.employee,
                            person_in_charge : this.person_in_charge
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

                            if(this.reservation.id === response.data.id){
                                this.quickModal.modal.close();
                            }
                        }else{
                            this.$refs.modal.close();
                        }


                            this.$toasted.show(this.__('Transaction added successfully'), {type: 'success'});


                        this.loading = false;
                        this.loader = false;
                    }).catch(err => {
                      this.loading = false;
                      this.$toasted.show(this.__(err), {type: 'error'})
                    })

            },
            updateDate() {
                this.date = new Date(moment(String(this.date)).format('YYYY-MM-DD'))
            },
            getTerms() {
                Nova.request()
                    .get('/nova-vendor/calender/terms/cash-receipt')
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
                        // console.log("this.terms", this.terms)
                        this.termsLoading = false;
                    }).catch(err => {
                    this.serviceLoading = false;
                    this.$toasted.show(this.__(err), {type: 'error'})
                })
            },
            getServerDate(){
              this.date = new Date( momenttimezone().tz("Asia/Riyadh").format('YYYY-MM-DD HH:mm'));
            
              Nova.request()
              .get('/nova-vendor/calender/server/current-date')
              .then(response => {
                this.server_date = response.data;
                this.date = new Date(this.server_date)
              })
          }
        },
        watch: {
          amount(num) {
              if(num){
                   this.amount = num.replace(/([٠١٢٣٤٥٦٧٨٩])|([۰۱۲۳۴۵۶۷۸۹])/g, (m, $1, $2) => m.charCodeAt(0) - ($1 ? 1632 : 1776))
                   this.amount = this.amount.replace(/[^\d.\d]/g,'')
              }

          }
        },
        mounted() {
              this.getServerDate();
                this.termsLoading = true;
                this.reservation = this.$parent.reservation;
                this.from = this.reservation.company ? this.reservation.company.name : this.reservation.customer.name;
                this.amount = 0;
                this.locale =  Nova.config.local ;
                this.employee = Nova.config.user.name ;
                Nova.$on('quick-open-cash-reciept-modal' , (reservation) => {
                    // this.reservation = reservation;
                    if(this.reservation.id === reservation.id){
                        this.open();
                    }

                });

        },

    }
</script>

<style lang="scss">
.cash_receipt_modal {
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
} /* cash_receipt_modal */
</style>
