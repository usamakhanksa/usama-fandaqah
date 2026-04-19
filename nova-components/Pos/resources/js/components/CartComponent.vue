<template>
    <div class="relative">

        <!-- Loader -->
        <loading :active.sync="isLoading"
                 :can-cancel="true"
                 :loader="'spinner'"
                 :color="'#7e7d7f'"
                 :is-full-page="fullPage">
        </loading>

        <cart-header-component />
        <div class="row_group date-container" v-if="canEditDate">
                <div class="col">
                    <label style="color: #555;">{{__('Date')}}<span style="color:red;font-size: 15px;">*</span></label>
                    <input
                        style="margin-top: 10px;"
                        readonly
                        v-model="date_picker"
                        ref="datePicker"
                        type="text"
                        :placeholder="__('Date')"
                    />
                    <!-- <vcc-date-picker
                        :input-props='{ readonly: true }'
                        mode="time"
                        v-model="date_picker"
                        @input="updateDate"
                        @change="updateDate"
                        show-caps
                        :popover="{ placement: 'bottom', visibility: 'click' }"
                        :locale="vcc_local"
                    >
                    </vcc-date-picker> -->
                </div><!-- col-->
        </div><!-- row_group-->
        <div class="orders_table">
            <cart-items-component :items="items" />
        </div><!-- end orders_table -->
        <cart-statistics-component :items="items" :date_picker="date_picker" :categories_names="categories_names" :item_multiply_quantitiy="item_multiply_quantitiy" />
    </div>
</template>

<script>
    import momenttimezone from 'moment-timezone';
    import CartItemsComponent from './cart/CartItemsComponent';
    import CartStatisticsComponent from './cart/CartStatisticsComponent';
    import CartHeaderComponent from './cart/CartHeaderComponent';
    import Loading from 'vue-loading-overlay';
    import 'vue-loading-overlay/dist/vue-loading.css';
    import flatpickr from "flatpickr";
    import { Arabic } from "flatpickr/dist/l10n/ar.js"
    export default {
        name: "CartComponent",
        data(){
            return {
                items : [],
                isLoading: false,
                fullPage: false ,
                categories_names : [],
                item_multiply_quantitiy : [],
                date_picker:null,
                canEditDate:false,
                vcc_local: Nova.config.local
            }
        },
        components : {CartItemsComponent , CartStatisticsComponent , Loading , CartHeaderComponent},
        computed:{
            dateFormat() {
                return  'Y-m-d H:i'
            },
        },
        mounted(){
            this.date_picker =  momenttimezone().tz("Asia/Riyadh").format('YYYY-MM-DD HH:mm');
            if(Nova.app.$hasPermission('add pos date')){
                this.canEditDate = true
            } else{
                this.date_picker = null;
            }

            Nova.$on('add-item-to-cart' , (item) => {

                this.isLoading = true;
                this.items.push(item);

                let self = this;
                setTimeout(function(){
                    self.isLoading = false;
                }, 500);

                this.categories_names = [];
                this.item_multiply_quantitiy  = [];
                _.forEach(this.items , function(item){
                    self.categories_names.push(item.category_name);
                    self.item_multiply_quantitiy.push(item.qty + ' × ' + item.name );
                });

                this.categories_names = _.uniq(this.categories_names);

                Nova.$emit('cart-items' , this.items);

            });

            Nova.$on('remove-item-from-cart-confirmed' , (uuid) => {
                this.categories_names = [];
                this.item_multiply_quantitiy  = [];
                this.items = _.reject(this.items, function(obj) { return obj.uuid === uuid; });
                let self = this;
                _.forEach(this.items , function(item){
                    self.categories_names.push(item.category_name);
                    self.item_multiply_quantitiy.push(item.qty + ' × ' + item.name );
                });

                this.categories_names = _.uniq(this.categories_names);
            });


            Nova.$on('update-cart-item-after-increment-or-decrement' , (item) => {

              let general_total_price = 0;
              this.items =   _.forEach(this.items , function(obj){
                    if(obj.uuid === item.uuid){
                        obj.qty = item.qty;
                        obj.vat_total = obj.vat_checked ? parseFloat(( obj.subtotal_price / 100 )  * obj.vat_tax * obj.qty).toFixed(2) : 0;
                        obj.ttx_total = obj.ttx_checked ? parseFloat(( obj.subtotal_price / 100 )  * obj.ttx_tax * obj.qty).toFixed(2) : 0;
                        obj.total_price =  parseFloat(obj.subtotal_price * obj.qty) + parseFloat(obj.vat_total) + parseFloat(obj.ttx_total);
                    }


                });


                this.item_multiply_quantitiy  = [];
                let self = this;
                _.forEach(this.items , function(item){
                    self.item_multiply_quantitiy.push(item.qty + ' × ' + item.name);
                });


            });

            Nova.$on('process-aborted' , () => {
               this.items = [];
            });

            Nova.$on('empty-cart' , () => {
               this.items = [];
               this.isLoading = false;
               this.categories_names = [];
               this.item_multiply_quantitiy  = [];
            });

            Nova.$on('begin-store-transaction' , ()=> {
                this.isLoading = true;
            })

            const self = this;
            this.$nextTick(() => {
                flatpickr(this.$refs.datePicker, {
                    enableTime: true,
                    enableSeconds: false,
                    dateFormat: this.dateFormat,
                    allowInput: false,
                    mode: 'single',
                    time_24hr: false,
                    onReady() {
                        self.$refs.datePicker.parentNode.classList.add('date-filter')
                    },
                    onChange(){
                        self.datePicker = self.$refs.datePicker.value;
                    },
                    "locale" : self.vcc_local == 'ar' ?  Arabic : 'en'
                })
            })
        }
    }
</script>

<style scoped>

.date-container{
    padding:10px
}

input {
                    background: #fafafa;
                    height: 40px;
                    padding: 0 10px;
                    font-size: 15px;
                    border: 1px solid #ddd !important;
                    color: #000;
                    width: 100%;
                    border-radius: 4px !important;
                    outline: none;
                } /* input */
</style>
