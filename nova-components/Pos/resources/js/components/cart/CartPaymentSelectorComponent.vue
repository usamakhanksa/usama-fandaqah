<template>


        <sweet-modal :enable-mobile-fullscreen="false" :pulse-on-block="false" overlay-theme="dark" ref="paymentModal" class="paymentModal relative" @open="fillNotes">
            <!-- Loader -->
            <loading :active.sync="isLoading"
                     :can-cancel="true"
                     :loader="'spinner'"
                     :color="'#7e7d7f'"
                     :is-full-page="fullPage">
            </loading>

            <div class="title">{{__('Invoice Total')}}</div>
          <div class="price">{{total_price}} <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></div>
          <div class="all_Payment" v-if="!pay_later">

            <label>
              <input type="radio"  name="payments" v-bind:checked="payment_method_prop == paymentMethod"  v-model="paymentMethod" :value="'cash'" @click="setCurrent('cash')">
              <div class="checkbox">{{__('Cash')}}</div>
            </label>
            <label>
              <input type="radio" name="payments" v-bind:checked="payment_method_prop == paymentMethod"  v-model="paymentMethod" :value="'bank-transfer'"  @click="setCurrent('bank-transfer')" >
              <div class="checkbox">{{__('Bank Transfer')}}</div>
            </label>
            <label>
              <input type="radio" name="payments" v-bind:checked="payment_method_prop == paymentMethod"  v-model="paymentMethod" :value="'mada'" @click="setCurrent('mada')">
              <div class="checkbox">{{__('Mada')}}</div>
            </label>
            <label>
              <input type="radio" name="payments" v-bind:checked="payment_method_prop == paymentMethod"  v-model="paymentMethod" :value="'credit'" @click="setCurrent('credit')">
              <div class="checkbox">{{__('Credit Card')}}</div>
            </label>
          </div><!-- all_Payment -->
          <div class="input_group">
            <label for="customer_name">{{__('Customer Name')}}</label>
            <input id="customer_name"  v-model="customer_name" type="text">
          </div><!-- input_group -->


          <div class="input_group">
            <label for="customer_phone">{{__('Customer Phone Number')}}</label>
            <vue-tel-input
                    :defaultCountry="'SA'"
                    @onInput="checkThePhone($event)"
                    :required="true"
                    :enabledFlags="true"
                    name="phone"
                    :placeholder="__('Enter Phone Number')"
                    :inputOptions="{ showDialCode: false, tabindex: 0 }"
                    v-model="customer_phone"
                    class="mb-2"
            >
            </vue-tel-input>
            <p v-if="!customerValidPhone" style="color:#ce1025;text-align: justify;">{{__('Phone number is not valid')}}</p>
          </div><!-- input_group -->

          <div class="input_group">
            <label for="address">{{__('Address')}}</label>
            <input id="address"  v-model="address" type="text">
          </div><!-- input_group -->

          <div class="input_group">
            <label for="tax_number">{{__('Tax number')}}</label>
            <input id="tax_number"  v-model="tax_number" type="text">
          </div><!-- input_group -->

           <div class="input_group">
            <label for="for">{{__('For')}}</label>
            <input id="for" readonly v-model="transaction_for = categoriesNamesComputed" type="text">
          </div><!-- input_group -->
          <div class="input_group">
            <label for="notes">{{__('Notes')}}</label>
              <input id="notes" type="text" v-model="notes" />
          </div><!-- input_group -->
            <div class="options_choose">
                <b>{{__('options')}}</b>
                <div v-permission="'print pos transactions'" class="switch_label">
                    <p>{{__('Auto Print')}}</p>
                    <label class="switch">
                        <input type="checkbox" v-model="openPrint">
                        <span class="slider round"></span>
                    </label>
                </div>
                <div class="switch_label">
                    <p>{{__('Send Invoice To Customer')}}</p>
                    <label class="switch">
                        <input type="checkbox" v-model="sendToCustomer">
                        <span class="slider round"></span>
                    </label>
                </div>
                <!-- <div class="switch_label" v-if="ifIntegrationEnabled('ZatcaPhaseTwo')">
                    <p>{{__('Push Invoice To Zatca')}}</p>
                    <label class="switch">
                        <input type="checkbox" v-model="sendToZatca">
                        <span class="slider round"></span>
                    </label>
                </div> -->
            </div><!-- options_choose -->
            <div class="input_group" v-if="sendToCustomer">
                <label>{{__('Email')}}</label>
                <input  v-model="customerEmail" type="text">
            </div><!-- input_group -->
            <div class="zatca_warning" v-if="sendToZatca">
                {{ __('Warning: Ensure that the details entered in the invoice are correct as you cannot edit or delete the invoice once it has been marked as Sent') }}
            </div>
          <button type="button" @click="addItems()" class="add_payment">{{__('Save')}}</button>

            <!-- <form id="pos_data" target="_blank" method="post"  style="display: none" action="/home/reservation/pos-print">
                <input type="hidden" :value="data" name="data">
                <input type="hidden" :value="id" name="id">
            </form> -->

            <a  v-permission="'print pos transactions'" :href="`/home/pos/print-record/${id}`" target="__blank" ref="printClick"></a>
            <!-- <form id="pos_data" target="_blank" method="get"  style="display: none" :url="`/home/pos/print-record/${id}`">

            </form> -->
        </sweet-modal>

</template>

<script>
    import Loading from 'vue-loading-overlay';
    import 'vue-loading-overlay/dist/vue-loading.css';
    export default {
        name: "CartPaymentSelectorComponent",
        components: {Loading},
        props: ['reservation' , 'items' , 'date_picker' , 'total_price','subtotal_price','vat_total_price','ttx_total_price' , 'qty_total' , 'categories_names' , 'item_multiply_quantitiy' , 'payment_method_prop','pay_later'],
        data(){
          return {
              paymentMethod : null,
              notes : '',
              transaction_for : '',
              isLoading : false,
              openPrint : 0,
              sendToCustomer : 0,
              sendToZatca: 0,
              customerEmail : null,
              teamName : null,
              teamOwnerEmail : null,
              teamPhotoUrl : null,
              data : [],
              id: null,
              customer_name : null,
              address : null,
              tax_number : null,
              integrations: [],
              customerValidPhone: true,
              customerPhoneCountry : null,
              customer_phone : null
          }
        },
        computed:{
            categoriesNamesComputed(){
                let names = '';
                _.forEach(this.categories_names , function(name){
                    names += name + ' , ';
                });

                return names.replace(/,\s*$/, "");
            },

            // notesComputed(){
            //     let notes = '';
            //     _.forEach(this.item_multiply_quantitiy , function(item){
            //         notes += item + ' , ';
            //     });

            //     return notes.replace(/,\s*$/, "");
            // }
        },
        methods:{
            fillNotes(){
                this.notes = '';
                let self = this;
                _.forEach(this.item_multiply_quantitiy , function(item){
                    self.notes += item + ' , ';
                });
                this.notes.replace(/,\s*$/, "");
            },
            setCurrent(type){
                this.paymentMethod = type;
            },
            addItems(){

                let params = {
                    items : this.items ,
                    date : this.date_picker ? moment(this.date_picker).format('YYYY-MM-DD H:mm') : moment().format('YYYY-MM-DD H:mm'),
                    pay_later : this.pay_later,
                    sumGeneralTotalWithTaxes : this.total_price  ,
                    sumSubTotal : this.subtotal_price ,
                    sumVatTotal : this.vat_total_price,
                    sumTtxTotal : this.ttx_total_price,
                    sumQuantities : this.qty_total,
                    paymentMethod : this.paymentMethod,
                    categoriesNames : this.transaction_for,
                    note : this.notes,
                    customerEmail : this.customerEmail,
                    sendToZatca: this.sendToZatca,
                    teamName : this.teamName,
                    teamOwnerEmail : this.teamOwnerEmail,
                    teamPhotoUrl : this.teamPhotoUrl,
                    customer_name : this.customer_name,
                    customer_phone : this.customer_phone,
                    address : this.address,
                    tax_number : this.tax_number
                };

                this.data = JSON.stringify(params);

                if(!this.paymentMethod && !this.pay_later){
                    this.$toasted.show(this.__('Please select payment method'), {
                        duration : 4000,
                        type: 'error',
                        position : 'top-center',
                    });
                    return;
                }

                if(this.transaction_for === '' || this.transaction_for === null){
                    this.$toasted.show(this.__('For field is required'), {
                        duration : 4000,
                        type: 'error',
                        position : 'top-center',
                    });
                    return;
                }

                if(this.sendToCustomer && !this.customerEmail){
                    this.$toasted.show(this.__('Email Is Required'), {
                        duration : 4000,
                        type: 'error',
                        position : 'top-center',
                    });
                    return;
                }

                this.isLoading = true;

                Nova.request().post('/nova-vendor/pos/add-services-general' , params)
                    .then(response =>{


                        this.$toasted.show(this.pay_later ? this.__('Services added successfully')  : this.__('Deposit Transaction has been added successfully'), {
                            duration : 4000,
                            type: 'success',
                            position : 'top-center',
                        });
                        this.isLoading = false;
                        this.$refs.paymentModal.close();
                        this.customer_name = null;
                        this.note = null;
                        this.paymentMethod = null;
                        this.customerEmail = null;
                        this.sendToZatca = 0;
                        this.sendToCustomer = 0;
                        this.id = response.data.id;
                        Nova.$emit('empty-cart');
                        if(this.openPrint){
                            this.printPos();
                            this.openPrint = 0;
                        }

                        //window.location.reload();
                    })

            },
            printPos(){

                setTimeout(() => {
                    this.$refs.printClick.click();
                }, 0);
                // $('#pos_data').submit();
            },
            checkIntegration(key) {
                this.isLoading = true;
                Nova.request()
                .get(`/nova-vendor/settings/integrations/${key}`).then(response => {
                  this.isLoading = false;
                  this.integrations = [
                    ...this.integrations,
                    {
                    key: key,
                    ...response.data
                    }
                ];
            })
                .catch(error => {
                   this.isLoading = false;
                    this.$toasted.error(error, {
                        duration: 3000
                    });

                })
            },
            ifIntegrationEnabled(key) {
                return this.integrations.find((integration) => integration.key === key && integration.integration !== null);
            },
            checkThePhone(phone){
                this.customerValidPhone = phone.isValid;
                this.customerPhoneCountry = phone.country.name;
            },
        },
        mounted() {
            this.teamName = Nova.app.currentTeam.name;
            this.teamOwnerEmail = Nova.app.currentTeam.owner.email;
            this.teamPhotoUrl = Nova.app.currentTeam.photo_url;
            this.checkIntegration('ZatcaPhaseTwo');
        },
        watch: {
            sendToZatca: function () {
                if(this.sendToZatca) {
                    this.$toasted.show(this.__('Warning: Ensure that the details entered in the invoice are correct as you cannot edit or delete the invoice once it has been marked as Sent'), {
                        duration : 4000,
                        type: 'warning',
                        position : 'top-center',
                    });
                }
            }
        }
    }
</script>

<style lang="scss" scoped>
    .switch {
        position: relative;
        display: inline-block;
        width: 60px;
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
        justify-content: space-between;
        flex-wrap: wrap;
        margin: 20px auto;

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
            margin: 0 0 10px;

            p {
                display: block;
                margin: 0 0 0 10px;
                font-size: 15px;
                color: #000;
            }
        }


    }
    .zatca_warning {
            color: #8a6d3b;
            background-color: #fcf8e3;
            border-color: #faebcc;
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 4px;
        }
</style>
