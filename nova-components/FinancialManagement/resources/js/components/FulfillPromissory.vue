<template>

    <sweet-modal :enable-mobile-fullscreen="false"  :pulse-on-block="false" :title="__('Fulfill Promissory')" overlay-theme="dark" ref="fulfillComponent" class="cancel_checkout">
         <div class="cash_receipt relative">
            <loading :active="loading" :loader="'spinner'" :color="'#7e7d7f'" :opacity="0.7" :height="20" :width="20" :is-full-page="false"></loading>
            <div class="formgroup">
                <label>{{__('Type')}}<span>*</span></label>
                <select v-model="term_id" @change="updateFor($event)" disabled>
                    <option value="" disabled selected> {{__('Select Option')}}</option>
                    <option v-for="(t,index) in terms" :key="index"  :value="t.id" :selected="term_id == t.id">{{t.name[locale]}}</option>
                </select>
            </div><!-- formgroup -->
             <div class="row_group">
                <div class="col">
                    <label>{{__('Date')}}<span>*</span></label>
                    <vcc-date-picker
                    :input-props='{readonly: true}'
                    mode='single'
                    @input="updateDate"
                    :value="new Date()"
                    show-caps
                    v-model="datePicker"
                    :locale="locale"
                    :max-date="new Date()"
                    :masks="{ dayPopover : 'WWW, MMM D, YYYY' , weekdays: 'WWW' }"
                    :popover="{ placement: 'bottom', visibility: 'click' }"
                    >
                    </vcc-date-picker>
                </div><!-- col -->
                <div class="col">
                    <label>{{__('From')}}<span>*</span></label>
                    <input type="text" v-model="from"  :placeHolder="__('From')">
                </div><!-- col -->
                </div><!-- row_group -->
            <div class="row_group">
                <div class="col">
                    <label>{{__('Amount')}}<span>*</span></label>
                    <input type="number" v-model="due_amount"  :placeHolder="__('Amount')">
                </div><!-- col -->
                <div class="col">
                    <label>{{__('For')}}<span>*</span></label>
                    <input type="text" v-model="description" :placeHolder="__('For')">
                </div><!-- col -->
                <span v-show="showAlert" class="amount-alert">{{__('Maximum fulfill amount is :amount SAR' , {amount : max_fulfill_amount }) }}</span>

            </div><!-- row_group -->
            <div :class=" !ref ? 'formgroup' : 'row_group'">
                <div :class="ref ? 'col' : ''">
                    <label>{{__('Payment Type')}}<span>*</span></label>
                    <select :placeholder="__('Payment Type')" v-model="payment_type" @change="checkPaymentType">
                    <option value="" disabled selected>{{__('Select Payment Type')}}</option>
                    <option value="cash">{{__('Cash')}}</option>
                    <option value="bank-transfer">{{__('Bank Transfer')}}</option>
                    <option value="mada">{{__('Mada')}}</option>
                    <option value="credit">{{__('Credit Card')}}</option>
                    <option value="rebate">{{__('Rebate')}}</option>

                    </select>
                </div><!-- col -->
                <div class="col" v-if="ref">
                    <label>{{__('Payment Reference')}}</label>
                    <input type="text" v-model="reference" :placeHolder="__('Payment Reference')">
                </div><!-- col -->
            </div><!-- row_group -->
            <div class="formgroup">
                <label>{{__('Notes')}}</label>
                <input type="text" v-model="note" :placeHolder="__('Notes')">
            </div><!-- formgroup -->
            <div class="row_group">
                <button :disabled="disabled" @click="fulFill"   class="shadow mb-4  btn btn-block btn-primary mt-2 text-base">{{__('Fulfill Promissory')}}</button>
            </div>
        </div>
    </sweet-modal>

</template>

<script>
    import momenttimezone from 'moment-timezone';
    import Loading from 'vue-loading-overlay';
    import 'vue-loading-overlay/dist/vue-loading.css';
    export default {
        name: "fulfill-promissory",
        components:{
            Loading
        },
        data(){
            return {
                loading: false,
                due_amount : 0,
                date: momenttimezone().tz("Asia/Riyadh").format('YYYY-MM-DD HH:mm'),
                datePicker: moment().toDate(),
                max_fulfill_amount : 0,
                payment_type : '',
                description : '',
                from: '',
                locale:'en',
                currentUser : Nova.config.user,
                currentTeamId : Nova.config.user.current_team_id,
                disabled : false,
                showAlert : true,
                promissory : null,
                date : momenttimezone().tz("Asia/Riyadh").format('YYYY-MM-DD HH:mm'),
                team_id : Nova.config.user.current_team_id,
                terms : [],
                term_id : null,
                locale : Nova.config.local,
                ref : false,
                reference : '',
                note : ''
            }
        },
        methods: {
            fulFill(){

                if (!this.term_id)
                {
                        this.$toasted.show(this.__("Please Enter Type!"), {type: 'error'})
                        this.disabled = false;
                        return false;
                }

                if (!this.due_amount)
                {
                        this.$toasted.show(this.__("Please Enter Fulfill Amount"), {type: 'error'})
                        this.disabled = false;
                        return false;
                }

                if (this.due_amount <= 0)
                {
                        this.$toasted.show(this.__("Amount is not valid"), {type: 'error'})
                        this.disabled = false;
                        return false;
                }

                if (!this.date)
                {
                        this.$toasted.show(this.__("Please Enter Date!"), {type: 'error'})
                        this.disabled = false;
                        return false;
                }

                if (!this.from)
                {
                        this.$toasted.show(this.__("Please Enter From!"), {type: 'error'})
                        this.disabled = false;
                        return false;
                }

                if (!this.description)
                {
                        this.$toasted.show(this.__("Please Enter For!"), {type: 'error'})
                        this.disabled = false;
                        return false;
                }

                if (!this.payment_type)
                {
                        this.$toasted.show(this.__("Please Enter Payment Type!"), {type: 'error'})
                        this.disabled = false;
                        return false;
                }



                let metaObj = {
                        category: 'reservation-promissory',
                        for : this.description,
                        statement : this.description,
                        type : this.term_id,
                        reference : this.reference,
                        payment_type : this.payment_type,
                        from : this.from,
                        employee: this.currentUser.name,
                        date : this.date,
                        note : this.note
                };

                this.loading = true;
                axios
                    .post('/nova-vendor/financial-management/promissory/add-transaction', {
                        promissory_id : this.promissory.id,
                        reservation_id : this.promissory.reservation.id,
                        amount: this.due_amount,
                        max_amount : this.max_fulfill_amount,
                        meta: metaObj
                    })
                    .then(response => {
                        Nova.$emit('reservation-promissory-transaction-added');
                        this.loading = false;
                        this.$refs.fulfillComponent.close();
                    });


            },
            getDefaultPromissoryTerm(){
                axios.get(window.FANDAQAH_API_URL + `/terms/getPromissoryTerm?team_id=${this.team_id}`)
                    .then(res => {
                        this.term_id = res.data.data.id;
                    })
            },
            getTerms(){
                this.terms = [];
                let type = 2 ;
                axios.get(window.FANDAQAH_API_URL + `/terms/dropDown?type=${type}&team_id=${this.team_id}`)
                    .then(res => {
                        this.terms = res.data.data;
                    })
            },
            updateFor(){
                Nova.request().get('/nova-vendor/calender/term?id=' + this.term_id)
                    .then(res =>{
                        if(res.data.name['ar'] == 'تحصيل كمبيالة'){
                            this.description = Nova.app.__('Fulfill promissory number :number' , {number : this.promissory.serial})
                        }else{
                            this.description = res.data.name[this.locale] + ' - ' + this.__('Unit number :number' , {number : this.promissory.reservation.unit_number}) ;
                        }
                    });
            },
             updateDate() {
                if (this.datePicker === null) {
                    this.datePicker = moment(this.date).toDate()
                    return;
                }
                this.date = moment(String(this.datePicker)).format('YYYY-MM-DD')
            },
             checkPaymentType() {
                if (this.payment_type == 'cash') {
                    this.ref = false;
                } else {
                    this.ref = true;
                }
            },
        },
        watch:{
            due_amount: function (val) {
                if(val > this.max_fulfill_amount){
                    this.disabled = true;
                    this.showAlert = true;
                }else{
                    this.disabled = false;
                    this.showAlert = false;
                }
            }
        },
        mounted() {
            Nova.$on('fulfill-promissory' , (id) => {
                this.loading = true;
                this.getTerms();
                this.getDefaultPromissoryTerm();
                axios.get(window.FANDAQAH_API_URL + `/promissories/show?id=${id}`)
                .then(response => {
                    this.promissory = response.data.data;
                    this.due_amount = response.data.data.amount_remaining;
                    this.max_fulfill_amount = response.data.data.amount_remaining;
                    this.description = Nova.app.__('Fulfill promissory number :number' , {number : response.data.data.serial})
                    this.loading = false;
                    this.payment_type = '';
                    this.$refs.fulfillComponent.open();
                })
            });
        }
    }
</script>

<style lang="scss">
.amount-alert {
    color: red;
    margin-bottom: 10px;
}
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
    &:disabled{
        background: #d6d6d6 !important;
    }
  } /* select */
} /* cash_receipt */
</style>
