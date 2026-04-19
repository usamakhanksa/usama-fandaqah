<template>
    <div>
        <div class="invoice_details">
            <ul>
                <li>
                    <span>{{__('Subtotal Price')}}</span>
                    <p> {{subtotal_price}} <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></p>
                </li>
                <li>
                    <span>{{__('VAT')}}</span>
                    <p>{{vat_total_price}} <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></p>
                </li>
                <li>
                    <span>{{__('TTX')}}</span>
                    <p>{{ttx_total_price}} <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></p>
                </li>
            </ul>
        </div><!-- end invoice_details -->
        <div class="last_total">
            <span>{{__('Total')}}</span>
            <p>{{total_price}}</p>
        </div><!-- end last_total -->
        <button v-show="canAddTransactionFromPos" :disabled="items.length === 0" @click="storeTransaction()" class="shadow btn btn-block btn-primary mt-2">{{__(btnText) }} ({{total_price}} <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span>)</button>
        <button v-show="canAddTransactionFromPos && btnText == 'Pay' " :disabled="items.length === 0" @click="storeTransaction(true)" class="shadow btn btn-block btn-primary mt-2">{{__('Pay Later')}}</button>

        <cart-payment-selector-component
                :reservation="reservation"
                :items="items"
                :date_picker="date_picker"
                :total_price="total_price"
                :subtotal_price="subtotal_price"
                :vat_total_price="vat_total_price"
                :ttx_total_price="ttx_total_price"
                :qty_total="qty_total"
                :categories_names="categories_names"
                :item_multiply_quantitiy="item_multiply_quantitiy"
                :payment_method_prop="null"
                :pay_later="pay_later"
                ref="paymentSelector"/>


    </div>

</template>

<script>
    import momenttimezone from 'moment-timezone';
    import CartPaymentSelectorComponent from './CartPaymentSelectorComponent';
    export default {
        name: "CartStatisticsComponent",
        components: {CartPaymentSelectorComponent,momenttimezone},
        props : ['items' , 'date_picker', 'categories_names' , 'item_multiply_quantitiy'],
        data(){
          return {
              reservation : null,
              open_payment_selector_modal : false,
              btnText : 'Pay',
              canAddTransactionFromPos : true,
              pay_later : false,
              currency :Nova.app.currentTeam.currency,

          }
        },
        computed: {

            qty_total(){
                let qty = 0;
                _.forEach(this.items , function(item){
                    qty += item.qty ;
                });
                return qty;
            },

            subtotal_price(){
                let subtotal_price = 0;
                _.forEach(this.items , function(item){
                    subtotal_price += item.qty * item.subtotal_price;
                });
                return parseFloat(subtotal_price).toFixed(2);
            },
            vat_total_price(){
                let vat_total_price = 0;
                _.forEach(this.items , function(item){
                    if(item.vat_checked){
                        vat_total_price += parseFloat(item.vat_total);
                    }
                });
                return parseFloat(vat_total_price).toFixed(2);
            },
            ttx_total_price(){
                let ttx_total_price = 0;
                _.forEach(this.items , function(item){
                    if(item.ttx_checked){
                        ttx_total_price += parseFloat(item.ttx_total);
                    }
                });
                return parseFloat(ttx_total_price).toFixed(2);
            },
            total_price(){
                let total_price = 0;
                _.forEach(this.items , function(item){
                    total_price += item.total_price;
                });
                return parseFloat(total_price).toFixed(2);
            },
        },
        methods : {
          storeTransaction(pay_later = false){
              const self = this;
              if(this.reservation){
                  let params = {
                      items : this.items ,
                      reservation_id : this.reservation.reservation_type == 'group' ? this.reservation.attachable_id : this.reservation.id,
                      sumGeneralTotalWithTaxes : this.total_price  ,
                      sumSubTotal : this.subtotal_price ,
                      sumVatTotal : this.vat_total_price,
                      sumTtxTotal : this.ttx_total_price,
                      sumQuantities : this.qty_total,
                      date : this.date_picker ? momenttimezone(this.date_picker).tz("Asia/Riyadh").format('YYYY-MM-DD H:mm') : momenttimezone().tz("Asia/Riyadh").format('YYYY-MM-DD HH:mm'),
                  };
                  Nova.$emit('begin-store-transaction');
                  Nova.request().post('/nova-vendor/pos/add-services' , params)
                      .then( response => {
                          var cached_reservation = this.reservation;
                          if(response.data.flag == 'forbidden'){
                                this.$toasted.show(Nova.app.__('Sorry you can\'t add this service / services cause there is an invoice with number :number - reservation number :reservation_number' , {number : response.data.invoice_number , reservation_number : response.data.reservation['number'] }),
                                    {
                                            duration : 4000,
                                            type: 'error',
                                            position : 'top-center',
                                            action: {
                                            text: Nova.app.__('Show Reservation'),
                                            onClick:(e, toast) => {
                                                this.$router.push({ name: 'reservation', params: { id: cached_reservation.id } });
                                                toast.goAway(0);
                                            }
                                        }
                                    }
                            );
                          }else{

                               this.$toasted.show(this.__('Items has been added to customer account :customer on unit number :number successfully' , {customer : this.reservation.customer.name , number : this.reservation.unit.number}), {
                              duration : 4000,
                              type: 'success',
                              position : 'top-center',
                            });

                          }

                           this.reservation = null;
                            Nova.$emit('empty-cart');

                      })

              }else{
                  this.pay_later = pay_later;
                  this.$refs.paymentSelector.$refs.paymentModal.open();
              }
          }
        },
        mounted(){

            if(Nova.app.$hasPermission('add service transaction from pos')){
                this.canAddTransactionFromPos = true;
            }else{
                this.canAddTransactionFromPos = false;
            }

            Nova.$on('current_reservation' , (reservation) => {
                this.reservation = reservation;
                this.btnText = 'Load on reservation'
            });

            Nova.$on('process-aborted' , () => {
                this.btnText = 'Pay'
                this.reservation = null;
            })

            Nova.$on('empty-cart' , () => {
                this.btnText = 'Pay'
            })
        }
    }
</script>

<style scoped>

</style>
