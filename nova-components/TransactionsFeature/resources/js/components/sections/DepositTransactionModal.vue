<template>
    <sweet-modal :enable-mobile-fullscreen="false"  :pulse-on-block="false" :title="__('Edit Transaction')"  overlay-theme="dark" ref="editDepositTransactionModal" class="Edit_Transaction_Modal relative">

        <loading :active.sync="loading" :can-cancel="true" :loader="'spinner'" :color="'#7e7d7f'" :is-full-page="fullPage"></loading>

        <div class="formgroup" v-if="!termsLoading">

                <label>{{__('Type')}}<span>*</span></label>
                <select :disabled="true" v-model="kind" @change="updateFor($event)">
                    <option  value="" disabled selected> {{__('Select Option')}}</option>
                    <option v-for="term in terms"  :value="term.id">{{term.name[locale]}}</option>
                </select>
            </div><!-- formgroup-->
            <div class="row_group">
                <div class="col">
                    <label>{{__('Date')}}<span>*</span></label>
                    <vcc-date-picker
                        :input-props='{ readonly: true }'
                        mode='single'
                        @input="updateDate"
                        :value='new Date(date)'
                        show-caps
                        :popover="{ placement: 'bottom', visibility: 'click' }"
                        :locale="vcc_local"
                    >
                    </vcc-date-picker>
                </div><!-- col-->

                <div class="col">
                    <label>{{__('From')}}<span>*</span></label>
                    <input type="text" v-model="from" ref="from" :placeHolder="__('From')">
                </div><!-- col-->

            </div><!-- row_group-->
            <div class="row_group">
                <div class="col">
                    <label>{{__('Amount')}}<span>*</span></label>
                    <input type="tel" min="0" v-model="amount" :placeHolder="__('Amount')">
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

        <button @click="send()">{{__('Save')}}</button>
    </sweet-modal>
</template>

<script>
    import Loading from 'vue-loading-overlay';
    import 'vue-loading-overlay/dist/vue-loading.css';

    export default {
        name: "DepositTransactionModal",
        components : {
            Loading
        },
        data(){
            return {
                terms : {},
                termsLoading : true,
                kind: null,
                date: moment().format('YYYY-MM-DD'),
                datePicker: moment().toDate(),
                from: null,
                type: null,
                description: null,
                note: null,
                reference: null,
                received_by:null,
                ref: false,
                employee : null,
                transaction : null,
                locale: null,
                vcc_local: {
                    id: Nova.config.local,
                    firstDayOfWeek: 1,
                    masks: {
                        weekdays: 'WWW',
                        input: ['WWWW YYYY/MM/DD', 'L'],
                        data: ['WWWW YYYY/MM/DD', 'L'],
                    }
                },
                loading : false,

            }
        },
        methods : {
            getDepositTerms() {
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
                        this.termsLoading = false;
                    }).catch(err => {

                    this.$toasted.show(this.__(err), {type: 'error'})
                })
            },
            updateFor(event){

                // console.log("val",event.target.options.selectedIndex-1)
                let term_id = this.kind ;
                Nova.request().post('/nova-vendor/calender/term/' , {term_id: term_id})
                    .then((res)=>{
                        console.log(res.data);
                        this.description = res.data.name[this.locale]
                    });
                // if(!this.description)
                //     this.description = this.terms[event.target.options.selectedIndex-1].name[this.local]

            },
            updateDate(r) {
                if (!r) {
                    this.datePicker = moment().toDate()
                    this.date = moment(String(r)).format('YYYY-MM-DD')
                }
                this.date = moment(String(r)).format('YYYY-MM-DD')
            },
            checkPaymentType() {
                if (this.type == 'cash') {
                    this.ref = false;
                } else {
                    this.ref = true;
                }
            },
            capitalize(label){
                if (typeof label !== 'string') return ''
                return label.charAt(0).toUpperCase() + label.slice(1)
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

                    if (!this.description) {
                        this.$toasted.show(this.__("Please Enter From!"), {type: 'error'})
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
                    this.loading = true;

                    if(this.transaction.payable_type == 'App\\Team'){
                        // it's a transaction not related to any reservation
                        axios
                            .post('/nova-vendor/financial-management/updateTransaction', {
                                id: this.transaction.id,
                                amount: this.amount,
                                type: 'deposit',
                                meta: {
                                    pos : true,
                                    qty : this.transaction.qty,
                                    category: 'service',
                                    statement: this.description,
                                    type: this.kind,
                                    payment_type: this.type,
                                    note: this.note,
                                    reference: this.reference,
                                    date: this.date + ' ' + new moment().format("HH:mm") ,
                                    from: this.from,
                                    employee : Nova.config.user.name,
                                    services : this.transaction.services,
                                    type : this.transaction.term_id,
                                    sub_total : this.transaction.sub_total,
                                    ttx_total : this.transaction.ttx_total,
                                    vat_total : this.transaction.vat_total,
                                    total_with_taxes : this.amount
                                }
                            })
                            .then(response => {
                                Nova.$emit('transaction-updated')
                                this.$refs.editDepositTransactionModal.close();
                                this.$toasted.show(this.__('POS Transaction updated successfully'), {type: 'success'});
                                this.loading = false;
                            }).catch(err => {
                            this.loading = false;
                            this.$toasted.show(this.__(err), {type: 'error'})
                        })
                    }else{
                        // it's normal reservation transaction
                        axios
                            .put('/nova-vendor/calender/reservation/transaction', {
                                id: this.transaction.id,
                                amount: this.amount,
                                type: 'deposit',
                                meta: {
                                    category: 'reservation',
                                    statement: this.description,
                                    type: this.kind,
                                    payment_type: this.type,
                                    note: this.note,
                                    reference: this.reference,
                                    date: this.date + ' ' + new moment().format("HH:mm") ,
                                    from: this.from,
                                    employee : Nova.config.user.name
                                }
                            })
                            .then(response => {
                                Nova.$emit('transaction-updated');
                                this.$refs.editModal.close();
                                this.$toasted.show(this.__('Transaction updated successfully'), {type: 'success'});
                                this.loading = false;
                            }).catch(err => {
                            this.loading = false;
                            this.$toasted.show(this.__(err), {type: 'error'})
                        })
                    }


            },

        },
        mounted(){


            Nova.$on('open-deposit-modal' , (transaction) => {
                this.transaction = transaction;
                this.getDepositTerms();

                // fill v-model props
                this.kind = this.transaction.term_id;
                this.date = this.transaction.transaction_date;
                this.amount = this.transaction.amount;
                this.type = this.transaction.payment_method;
                this.description = this.transaction.for;
                this.note = this.transaction.note;
                this.reference = this.transaction.reference;
                this.from = this.transaction.from ? this.transaction.from :  this.__('Loaded On Services Revenue')
                if (this.type == 'cash') {
                    this.ref = false;
                } else {
                    this.ref = true;
                }
                this.$refs.editDepositTransactionModal.open();
            })
        },
        created() {
            this.locale = Nova.config.local ;
        }
    }
</script>

<style scoped>

</style>
