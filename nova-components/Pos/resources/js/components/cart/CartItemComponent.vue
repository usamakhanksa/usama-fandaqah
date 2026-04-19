<template>
    <tr v-if="item">
        <td><button @click="handleRemoveItemFromCart(item.uuid)"></button></td>
        <td>
            <b>{{item.name}}</b>
            <span v-if="item.vat_checked || item.ttx_checked">
                <template v-if="item.vat_checked"><br> {{__('VAT')}} : {{item.vat_total}} <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></template>
                <template v-if="item.ttx_checked"><br> {{__('TTX')}} : {{item.ttx_total}} <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></template>
            </span>
        </td>
        <td>
            <div class="number_input" v-on:mouseover="hover = true"
                 v-on:mouseleave="hover = false">
                <a @click="increment" v-if="hover" class="custom-additions-controls cursor-pointer button-flex-center">+</a>
                <div class="qty">{{qty}}</div>
                <a @click="decrement" v-if="hover" class="custom-additions-controls cursor-pointer button-flex-center">-</a>
            </div>

        </td>
        <td>{{item.subtotal_price}}</td>
    </tr>
</template>

<script>
    export default {
        name: "CartItemComponent",
        props : ['item'],
        data(){
            return {
                hover : false,
                qty : this.item.qty,
                currency :Nova.app.currentTeam.currency,

            }
        },
        computed:{
          // qtyItem(){
          //     this.qty = this.item.qty;
          //     return this.qty;
          // }
        },
        methods: {
            handleRemoveItemFromCart(uuid){
                Nova.$emit('remove-item-from-cart-confirm' , uuid);
            },

            increment(){
                this.qty++;
                this.item.qty = this.qty;
                Nova.$emit('update-cart-item-after-increment-or-decrement' , this.item);
            },

            decrement(){
                if(this.qty > 1){
                    this.qty--;
                    this.item.qty = this.qty;
                    Nova.$emit('update-cart-item-after-increment-or-decrement' , this.item);
                }



            }
        }
    }
</script>

<style scoped>

    .qty{
        width: 55%;
        text-align: center;
        font-size: 20px;
        height: 35px;
        line-height: 35px;
        outline: none;
    }

    .custom-additions-controls{
        width: 20px;
        height: 20px;
        display: block;
        font-size: 20px;
        color: #4099de;
        outline: none;
        text-align: center;
    }

    .number_input{
        display: -webkit-box;
        display: -ms-flexbox;
        display: flex;
        width: 100px;
        margin: 20px auto 15px;
        -webkit-box-pack: justify;
        -ms-flex-pack: justify;
        justify-content: center;
        -webkit-box-align: center;
        -ms-flex-align: center;
        align-items: center;
        -ms-flex-wrap: nowrap;
        flex-wrap: nowrap;
    }

    .button-flex-center{
        display: flex;
        justify-content: center;
        align-items: center;
    }
</style>
