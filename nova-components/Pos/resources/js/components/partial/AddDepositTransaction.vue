<template>
  <div>
    <sweet-modal :enable-mobile-fullscreen="false"  @open="open" :pulse-on-block="false" :title="__('New Deposit Transaction') " overlay-theme="dark" ref="deposit_transaction" class="Deposit_Transaction_Modal">
      <div class="input_group" v-if="terms">
        <label>{{__('Type')}}<span>*</span></label>
        <select v-model="kind" @change="updateFor($event)" disabled>
          <option v-for="(term,i) in terms" :key="i" :selected="term.name['ar'] == 'خدمات'" :disabled="term.name['ar'] == 'خدمات'"   :value="term.id">{{term.name[local]}}</option>
        </select>
      </div><!-- input_group -->
      <div class="row_group">
        <div class="col">
          <label>{{__('Date')}}<span>*</span></label>
          <vcc-date-picker
            :input-props='{ readonly: true }'
            mode='single'
            @input="updateDate"
            :value='new Date()'
            v-model='datePicker'
            :max-date='new Date()'
            :locale="locale"
            :masks="{ dayPopover : 'WWW, MMM D, YYYY' , weekdays: 'WWW' }"
            :popover="{ placement: 'bottom', visibility: 'click' }"
          >
          </vcc-date-picker>
        </div><!-- col -->
        <div class="col">
            <label>{{type === 'withdraw' ? __('To') : __('From')}}<span>*</span></label>
          <input type="text" disabled v-model="from" ref="from" :placeHolder="__('From')">
        </div><!-- col -->
      </div><!-- row_group -->
      <div class="row_group">
        <div class="col">
          <label>{{__('Amount')}}<span>*</span></label>
          <input type="tel" disabled v-model="amount" :placeHolder="__('Amount')">
        </div><!-- col -->
        <div class="col">
          <label>{{__('For')}}<span>*</span></label>
          <input type="text" disabled v-model="description" :placeHolder="__('For')">
        </div><!-- col -->
      </div><!-- row_group -->

      <div class="input_group">
        <div class="col" style="width: 100%;">
          <label>{{__('Payment Type')}}<span>*</span></label>
          <select :placeholder="__('Payment Type')" v-model="payment_type" @change="checkPaymentType">
            <option value="" disabled selected>{{__('Select Payment Type')}}</option>
            <option value="cash">{{__('Cash')}}</option>
            <option value="bank-transfer">{{__('Bank Transfer')}}</option>
            <option value="mada">{{__('Mada')}}</option>
            <option value="credit">{{__('Credit Card')}}</option>
          </select>
        </div><!-- col -->

      </div><!-- row_group -->

        <div :class="ref ? 'row_group' : 'input_group'">
            <div class="col"  v-if="ref">
                <label>{{__('Payment Reference')}}</label>
                <input type="text"  v-model="reference" :placeHolder="__('Payment Reference')">
            </div>
            <div class="col">
                <label>{{__('Notes')}}</label>
                <input type="text" disabled v-model="note" :placeHolder="__('Notes')">
            </div><!-- col -->

        </div><!-- row_group -->

      <button :disabled="loading" @click="send">{{__('Save')}}</button>
    </sweet-modal>
  </div>
</template>

<script>
    export default {
        name: "add-deposit-transaction",
        props : ['obj'],
        data: () => {
            return {
                loading: null,
                kind: null,
                local: Nova.config.local,
                date: moment().format('YYYY-MM-DD HH:mm'),
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
                terms : [],
                teamId : Nova.config.user.current_team_id

            }
        },
        watch: {
          amount(num) {
              this.amount = num.replace(/([٠١٢٣٤٥٦٧٨٩])|([۰۱۲۳۴۵۶۷۸۹])/g, (m, $1, $2) => m.charCodeAt(0) - ($1 ? 1632 : 1776))
              this.amount = this.amount.replace(/[^\d.\d]/g,'')
          }
        },
        methods: {
            updateFor(event){
                // if(!this.description)
                    this.description = this.terms[event.target.options.selectedIndex-1].name[this.local]
                    this.termName = this.terms[event.target.options.selectedIndex-1].name['ar'];
            },
            open() {
               console.log(this.obj);
                const self = this;
                this.terms.forEach(function(term){
                    if(term.name['ar'] == 'خدمات'){
                        self.kind = term.id;
                        self.termName = term.name[self.locale];
                    }
                })

                this.from = this.obj.meta.from;
                this.amount = this.obj.meta.total_with_taxes;
                this.payment_type = null;
                if(this.obj.meta.statement == 'various_services'){
                    this.description = this.__('Various services');
                }else{
                    this.description = this.obj.meta.statement;
                }
                this.note = this.obj.meta.note;
                this.reference = null;
                this.ref = false;
                this.received_by = null;

                this.$refs.modal.open()

            },


            checkPaymentType() {
                if (this.payment_type == 'cash') {
                    this.ref = false;
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

                this.loading = true;

                let metaObj = {};
                     metaObj = {
                        category: `deposit-transaction`,
                        statement: this.description,
                        type: this.kind,
                        payment_type: this.payment_type,
                        note: this.note,
                        reference: this.reference,
                        date: this.date ,
                        from: this.from,
                        employee:this.employee
                    };

                axios
                    .post('/nova-vendor/pos/createPostponedTransaction', {

                        team_id : Nova.config.user.current_team_id,
                        type: this.type,
                        amount: this.amount,
                        termName : this.termName,
                        meta: metaObj,
                        oldWithdrawTransactionId : this.obj.id
                    })
                    .then(response => {

                        if(response.data === 'invalid-date'){
                            this.$toasted.show(this.__('Invalid Date Format'), {type: 'error'});
                            this.loading = false;
                            return;
                        }

                        Nova.$emit('transaction-added-pos');
                        this.$refs.deposit_transaction.close();
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
                this.date = moment(String(this.datePicker)).format('YYYY-MM-DD')
            },
            getTerms(){
                this.terms = [];
                axios.get(window.FANDAQAH_API_URL + `/terms/dropDown?type=2&team_id=${this.teamId}`)
                    .then(res => {
                        this.terms = res.data.data;
                    })
            },
        },
        mounted() {
            this.employee = Nova.config.user.name ;
            this.locale =  Nova.config.local ;
            this.getTerms();
        },

    }
</script>

<style lang="scss">
.Deposit_Transaction_Modal {
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
} /* Deposit_Transaction_Modal */
</style>
