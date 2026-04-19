<template>

    <div v-if="service">
        <!-- Product Modal -->
        <sweet-modal
            :enable-mobile-fullscreen="false"
            :pulse-on-block="false"
            :title="service.name"
            overlay-theme="dark"
            ref="productModal"
            class="productModal"
            @open="modalOpened"
        >
            <div class="productModal_content">
                <div class="imgthumb">
                    <img :src="service.image">
                </div><!-- end imgthumb -->
                <div class="desc">
                    <span>{{service.name}}</span>
                  
                    <input v-permission="'edit service price pos'"  name="service_price" v-model="service_price" @input="handleServicePriceChange" type="number">
                </div><!-- end desc -->
                <div class="number_input">
                    <button @click="incrementQty"  class="plus button-flex-center">+</button>
                    <input class="quantity" min="1" name="quantity" v-model="qty" type="number">
                    <button @click="decrementQty" class="button-flex-center" >-</button>
                </div><!-- end number_input -->
                <hr>
                <ul>
                    <li v-if="vat_tax">
                        <div class="choose">
                            <label class="switch">
                                <input type="checkbox" v-model="vat_checked" :checked="vat_checked">
                                <span class="slider round"></span>
                            </label>
                            {{__('VAT')}} <i>{{vat_tax}}%</i>
                        </div>
                        <div class="price"><span>{{vatCalculator}}</span>  <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span> </div>
                    </li>
                    <li v-if="ttx_tax">
                        <div class="choose">
                            <label class="switch">
                                <input type="checkbox" v-model="ttx_checked" :checked="ttx_checked">
                                <span class="slider round"></span>
                            </label>
                            {{__('TTX')}} <i>{{ttx_tax}}%</i>
                        </div>
                        <div class="price"><span>{{ttxCalculator}}</span>  <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span> </div>
                    </li>
                </ul>
                <button @click="addToCart()" v-permission="'add service transaction from pos'" class="shadow btn btn-block btn-primary mt-2">{{__('Add')}}  (  <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span> {{totalPrice}} )</button>

            </div><!-- end productModal_content -->
        </sweet-modal>
        <!-- Product Modal -->
    </div>


</template>

<script>
    export default {
        name: "AddItemToCartComponent",
        props: ['service'],
        data(){
            return {
                vat_tax : 0,
                ttx_tax : 0,
                vat_total : null,
                ttx_total : null ,
                vat_checked : false,
                ttx_checked : false,
                subtotal_price : 0,
                total_price : 0,
                qty : 1,
                id : null,
                currency :Nova.app.currentTeam.currency,
                service_price : 0

            }
        },
        computed : {
            vatCalculator(){
               return  this.vat_total =  parseFloat(( this.service_price / 100 ) * this.vat_tax * this.qty).toFixed(2);
            },
            ttxCalculator(){
               return this.ttx_total =  parseFloat(( this.service_price / 100 )  * this.ttx_tax * this.qty).toFixed(2) ;
            },
            totalPrice(){

                this.total_price = parseFloat(this.service_price * this.qty) ;

                if(this.vat_checked){
                    this.total_price += parseFloat(this.vat_total);
                }

                if(this.ttx_checked){
                    this.total_price += parseFloat(this.ttx_total);
                }

                return parseFloat(this.total_price).toFixed(2) ;
            }
        },
        methods: {
            incrementQty(){
                this.qty++;
            },
            decrementQty(){
                this.qty === 1 ? 1 : this.qty--;
            },
            getTaxesSettingsInformation(){
                axios.get(`/nova-vendor/pos/get-taxes-settings-information`)
                    .then(response => {
                        this.vat_tax = response.data.vat_tax;
                        this.ttx_tax = response.data.ttx_tax;
                    })
            },
            addToCart(){

                if(!this.service_price){
                    this.$toasted.show(this.__('Service price is required'), {
                        duration : 4000,
                        type: 'error',
                        position : 'top-center',
                    });
                    return;
                }
                if(!this.qty || this.qty <= 0){
                    this.$toasted.show(this.__('Minimum quantity required is one'), {
                        duration : 4000,
                        type: 'error',
                        position : 'top-center',
                    });

                    this.qty = 1;
                    return false;
                }

                this.item = {
                    id : this.id ,
                    uuid : this.$uuid.v1(),
                    name : this.service.name,
                    category_name : this.service.category_name,
                    vat_total : this.vat_total,
                    ttx_total : this.ttx_total,
                    vat_checked : this.vat_checked,
                    ttx_checked : this.ttx_checked,
                    subtotal_price : this.service_price,
                    total_price : this.total_price,
                    qty : this.qty,
                    vat_tax : this.vat_tax,
                    ttx_tax : this.ttx_tax,
                };

                Nova.$emit('add-item-to-cart' , this.item);
                this.$refs.productModal.close();

            },
            modalOpened(){
                this.vat_checked = this.vat_tax ? true : false;
            },
            handleServicePriceChange(){
                
            }
        },
        mounted(){
            Nova.$on('open-add-service-product-modal' , (service) => {
                this.qty = 1;
                this.id = service.id;
                this.service_price = parseFloat(service.price);
                this.subtotal_price = this.service_price;
                this.vat_checked = false;
                this.ttx_checked = false;
                this.item = {};
            });
        },
        created(){
            this.getTaxesSettingsInformation();
        }
    }
</script>

<style scoped>
    .button-flex-center{
        display: flex;
        justify-content: center;
        align-items: center;
    }
</style>
