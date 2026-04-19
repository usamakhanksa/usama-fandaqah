<template>

        <div v-permission="'view statements'">
            <!--        <h1 class="mb-1 text-90 font-normal text-2xl">{{__('Services')}}</h1>-->


            <!-- Edit Service Transaction -->
            <sweet-modal class="addServiceModal" ref="editServiceModal" @close="editModalClosed"  :enable-mobile-fullscreen="false" :pulse-on-block="false" :title="__('Edit Services')" overlay-theme="dark">

                <div  class="addServiceinside overflowautoforsweet">

                    <div v-if="!items.length && !isLoading" class="nodata">{{__('All services has been removed temporarily , by clicking the update btn the actual remove will be triggered')}}</div>
                    <!-- Action Table for Playing with services -->
                    <div v-if="items.length" class="table_of_services relative">
                        <!-- Loader -->
                        <template class="relative">
                            <loading :active.sync="isLoading"
                                     :is-full-page="false">
                            </loading>
                        </template>

                        <div class="form_has_wrong_inputs" v-if="showValidationMsg">
                            {{__('Please make sure to add valid price & quantity for each service')}}
                        </div>
                        <table>
                            <thead>
                            <tr>
                                <th>{{__('Service')}}</th>
                                <th>{{__('Price')}}</th>
                                <th>{{__('Qty')}}</th>
                                <th>{{__('Sub total')}}</th>
                                <th style="width: 20%;" v-if="unitServiceTaxInformation.vat_percentage">{{__('VAT')}} (%{{unitServiceTaxInformation.vat_percentage}})</th>
                                <th style="width: 20%;" v-if="unitServiceTaxInformation.tourism_percentage">{{__('TTX')}} (%{{unitServiceTaxInformation.tourism_percentage}})</th>
                                <th colspan="2">{{__('Total With Tax')}}</th>
                            </tr>
                            </thead>
                            <tbody>


                            <tr v-for="(item,index) in items" :key="index">
                                <td>{{item.text}}</td>

                                <td v-if="canChangeServicePrice"><input @input="selected(item)"  v-model="item.price" type="number"  v-on:keyup="this.value = this.value.replace(/[^0-9.]/g, '')"></td>
                                <td v-else>{{item.price}}</td>
                                <td><input @input="selected(item)"  v-model="item.qty" type="number"  oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"></td>
                                <td><template>{{parseFloat(item.subTotal).toFixed(2)}}</template></td>

                                <td v-if="unitServiceTaxInformation.vat_percentage">
                                    <div class="checkbox">
                                        <p>{{ parseFloat(items[index].vat).toFixed(2)}}</p>
                                        <label class="switch"><input @change="vatSliderButtonChanged($event,item)"  v-model="item.vatIsChecked" type="checkbox"> <span class="slider round"></span></label>
                                    </div>
                                </td>
                                <td v-if="unitServiceTaxInformation.tourism_percentage">
                                    <div class="checkbox">
                                        <p>{{ parseFloat(items[index].ttx).toFixed(2)}}</p>
                                        <label class="switch"><input type="checkbox" @change="ttxSliderButtonChanged($event,item)" v-model="item.ttxIsChecked"> <span class="slider round"></span></label>
                                    </div>
                                </td>

                                <td>{{ parseFloat(items[index].totalGeneralSum).toFixed(2)}}</td>
                                <td>
                                    <button type="button" @click="removeService(items[index])">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18.688" height="20.049" viewBox="0 0 18.688 20.049"><g transform="translate(-17.379)"><g transform="translate(17.379 3.01)"><g transform="translate(0)"><path d="M306.717,175.114l-1.569-.058-.34,9.292,1.569.057Z" transform="translate(-293.552 -171.211)" fill="#ff2626"/><rect width="1.57" height="9.292" transform="translate(8.559 3.874)" fill="#ff2626"/><path d="M160.329,184.341l-.34-9.292-1.569.058.34,9.292Z" transform="translate(-152.896 -171.204)" fill="#ff2626"/><path d="M17.379,76.867v1.57h1.636l1.3,14.752a.785.785,0,0,0,.782.716H32.324a.785.785,0,0,0,.782-.717l1.3-14.752h1.663v-1.57ZM31.6,92.335h-9.79l-1.223-13.9H32.828Z" transform="translate(-17.379 -76.867)" fill="#ff2626"/></g></g><g transform="translate(22.849)"><g transform="translate(0)"><path d="M163.515,0h-5.13a1.31,1.31,0,0,0-1.309,1.309V3.8h1.57V1.57h4.607V3.8h1.57V1.309A1.31,1.31,0,0,0,163.515,0Z" transform="translate(-157.076)" fill="#ff2626"/></g></g></g></svg>
                                    </button>
                                </td>
                            </tr>

                            </tbody>

                        </table>
                    </div>

                    <div v-if="items.length" class="w-full">
                        <div class="rounded w-full flex flex-wrap my-3">
                            <div class="sm:w-1/1 md:w-1/3 lg:w-1/5 xl:w-1/5 totle_cost">
                                <div class="relative">
                                    <h2 class="block capitalize text-md mb-2 w-full">{{__('Sub total')}}</h2>
                                    <h4 class="text-base font-normal w-full"><b class="text-black-500">{{ sumSubTotal }}</b></h4>
                                </div>
                            </div>
                            <div class="sm:w-1/1 md:w-1/3 lg:w-1/5 xl:w-1/5 totle_cost" v-if="unitServiceTaxInformation.vat_percentage">
                                <div class="relative">
                                    <h2 class="block capitalize text-md mb-2 w-full" >{{__('Total Vat')}}</h2>
                                    <h4 class="text-base font-normal w-full"><b class="text-black-500">{{ sumVatTotal }}</b></h4>
                                </div>
                            </div>
                            <div class="sm:w-1/1 md:w-1/3 lg:w-1/5 xl:w-1/5 totle_cost" v-if="unitServiceTaxInformation.tourism_percentage">
                                <div class="relative">
                                    <h2 class="block capitalize text-md mb-2 w-full">{{__('Total Ttx')}}</h2>
                                    <h4 class="text-base font-normal w-full"><b class="text-black-500">{{sumTtxTotal}}</b></h4>
                                </div>
                            </div>
                            <div class="sm:w-1/1 md:w-1/3 lg:w-1/5 xl:w-1/5 totle_cost">
                                <div class="relative">
                                    <h2 class="block capitalize text-md mb-2 w-full">{{__('Sum of Taxes')}}</h2>
                                    <h4 class="text-base font-normal w-full"><b class="text-black-500">{{ parseFloat(sumTotalTaxes).toFixed(2) }}</b></h4>
                                </div>
                            </div>
                            <div class="sm:w-1/1 md:w-1/3 lg:w-1/5 xl:w-1/5 totle_cost">
                                <div class="relative">
                                    <h2 class="block capitalize text-md mb-2 w-full">{{__('Sum of total with tax')}}</h2>
                                    <h4 class="text-base font-normal w-full"><b class="text-black-500">{{ sumGeneralTotalWithTaxes }}</b></h4>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Save Button -->
                    <div   class="flex flex justify-center">
                        <button :disabled="disableUpdate"  class="addbut shadow mb-2  btn btn-default btn-block btn-primary mt-2" @click="updateServices">{{__('Update')}}
                            <span v-if="spinnerLoadOnEdit" class="spinner spinner-light" :class="[locale === 'ar' ? 'mr-2' : 'ml-2']"></span>
                        </button>

                    </div>
                </div>

            </sweet-modal>
    </div>
</template>

<script>
    // Import component
    import Loading from 'vue-loading-overlay';
    // Import stylesheet
    import 'vue-loading-overlay/dist/vue-loading.css';
    export default {
        props: ["reservation"],
        components:{
            Loading
        },
        data: () => {
            return {
                unitServiceTaxInformation : null ,
                reservationServices : null ,
                locale : null ,
                items : [] ,
                obj : {
                    text: null,
                    price: 0,
                    qty: Math.floor(0),
                    subTotal: 0 ,
                    vat : 0 ,
                    ttx : 0,
                    vatIsChecked : false,
                    ttxIsChecked : false ,
                    totalGeneralSum : Number(0),
                    id : null
                },
                isLoading : false,
                spinnerLoadOnEdit : false ,
                transaction : null,
                disableUpdate : false,
                showValidationMsg : false,
                canChangeServicePrice : false
            }
        },
        computed:{
            sumSubTotal() {
                let sumSubTotal = 0  ;
                for (let i = this.items.length - 1; i >= 0; --i) {
                    sumSubTotal +=   this.items[i].subTotal ;
                }
                return sumSubTotal ;
            },
            sumVatTotal() {
                let vatTotal = 0  ;
                for (let i = this.items.length - 1; i >= 0; --i) {
                    if(this.items[i].vatIsChecked){
                        vatTotal +=   this.items[i].vat ;
                    }
                }
                return parseFloat(vatTotal).toFixed(2) ;
            },
            sumTtxTotal() {
                let ttxTotal = 0  ;
                for (let i = this.items.length - 1; i >= 0; --i) {
                    if(this.items[i].ttxIsChecked){
                        ttxTotal +=   this.items[i].ttx ;
                    }
                }
                return parseFloat(ttxTotal).toFixed(2) ;
            },
            sumTotalTaxes(){
                let vatTotal = 0  ;
                for (let i = this.items.length - 1; i >= 0; --i) {
                    if(this.items[i].vatIsChecked){
                        vatTotal +=   this.items[i].vat ;
                    }
                }


                let ttxTotal = 0  ;
                for (let i = this.items.length - 1; i >= 0; --i) {
                    if(this.items[i].ttxIsChecked){
                        ttxTotal +=   this.items[i].ttx ;
                    }
                }
                return ttxTotal + vatTotal ;
            },
            sumGeneralTotalWithTaxes() {
                let sumGeneralTotal = 0  ;
                for (let i = this.items.length - 1; i >= 0; --i) {
                    sumGeneralTotal +=   this.items[i].totalGeneralSum ;
                }
                return parseFloat(sumGeneralTotal).toFixed(2) ;
            },
            sumQuantities() {
                let sumQuantities = Math.floor(0)  ;
                for (let i = this.items.length - 1; i >= 0; --i) {
                    sumQuantities +=   Math.floor(this.items[i].qty) ;
                }
                return sumQuantities ;
            },
        },
        methods: {

            selected(item) {
                if(!item.price || item.price <= 0  || !item.qty || item.qty <= 0){
                    this.disableUpdate = true;
                    this.showValidationMsg = true;
                }else{
                    this.disableUpdate = false;
                    this.showValidationMsg = false;
                }
                // Update Specific object with new values after changing the quantity
                let new_sub_total = item.qty * item.price ;
                for (let i = this.items.length - 1; i >= 0; --i) {

                    if (this.items[i].id === item.id) {
                        this.items[i].qty = item.qty;
                        this.items[i].subTotal = new_sub_total;
                        this.items[i].vat = this.unitServiceTaxInformation.vat_percentage ? (this.unitServiceTaxInformation.vat_percentage / 100) * new_sub_total : 0;
                        this.items[i].ttx = this.unitServiceTaxInformation.tourism_percentage ?  (this.unitServiceTaxInformation.tourism_percentage / 100) * new_sub_total : 0 ;
                        this.items[i].totalGeneralSum = new_sub_total  ;

                        if(this.items[i].vatIsChecked){
                            this.items[i].totalGeneralSum +=  this.items[i].vat
                        }

                        if(this.items[i].ttxIsChecked){
                            this.items[i].totalGeneralSum +=  this.items[i].ttx
                        }

                    }
                }
            },

            vatSliderButtonChanged(event,item){
                let checked = event.target.checked ;
                if(checked){
                    for (let i = this.items.length - 1; i >= 0; --i) {
                        if (this.items[i].id === item.id) {
                            item.vatIsChecked = true ;
                            this.items[i].totalGeneralSum += this.unitServiceTaxInformation.vat_percentage ?  (this.unitServiceTaxInformation.vat_percentage / 100) * item.subTotal  : 0 ;
                        }
                    }
                }else{
                    for (let i = this.items.length - 1; i >= 0; --i) {
                        if (this.items[i].id === item.id) {
                            item.vatIsChecked = false ;
                            this.items[i].totalGeneralSum -= this.items[i].vat ;
                            this.items[i].vat  = this.unitServiceTaxInformation.vat_percentage ?  (this.unitServiceTaxInformation.vat_percentage / 100) * item.subTotal  : 0 ;
                        }
                    }
                }
            },
            ttxSliderButtonChanged(event,item){
                let checked = event.target.checked ;
                if(checked){
                    for (let i = this.items.length - 1; i >= 0; --i) {
                        if (this.items[i].id === item.id) {
                            item.ttxIsChecked = true ;
                            this.items[i].totalGeneralSum += this.unitServiceTaxInformation.tourism_percentage ?  (this.unitServiceTaxInformation.tourism_percentage / 100) * item.subTotal  : 0 ;
                        }
                    }
                }else{
                    for (let i = this.items.length - 1; i >= 0; --i) {
                        if (this.items[i].id === item.id) {
                            item.ttxIsChecked = false ;
                            this.items[i].totalGeneralSum -= this.items[i].ttx;
                            this.items[i].ttx = this.unitServiceTaxInformation.tourism_percentage ?  (this.unitServiceTaxInformation.tourism_percentage / 100) * item.subTotal  : 0 ;
                        }
                    }
                }
            },
            editModalClosed(){
                this.disableUpdate = false;
                this.showValidationMsg = false;
                this.items = [] ;
            },
            removeService(item){
                this.isLoading = true ;
                for (let i = this.items.length - 1; i >= 0; --i) {
                    if (this.items[i].id === item.id) {
                        setTimeout(() => {
                            this.isLoading = false
                        },500);
                        this.$delete(this.items , i) ;
                    }
                }

            },
            updateServices(){
                this.spinnerLoadOnEdit = true;
                let params = {
                    items : this.items ,
                    reservation_id : this.reservation.id,
                    transaction_id : this.transaction.id,
                    sumGeneralTotalWithTaxes : this.sumGeneralTotalWithTaxes  ,
                    sumSubTotal : this.sumSubTotal ,
                    sumVatTotal : this.sumVatTotal,
                    sumTtxTotal : this.sumTtxTotal,
                    sumQuantities : this.sumQuantities
                };
                Nova.request().post('/nova-vendor/calender/reservation/updateServices' , params)
                    .then((response)=>{

                        if(response.data.status === 'services-updated'){
                            this.$refs.editServiceModal.close();
                            this.spinnerLoadOnEdit = false
                            Nova.$emit('update-reservation', true)
                            this.$toasted.show(this.__('Services has been updated successfully'), {type: 'success'});
                        }

                        if(response.data.status === 'services-deleted'){
                            this.$refs.editServiceModal.close();
                            this.spinnerLoadOnEdit = false
                            Nova.$emit('update-reservation', true)
                            this.$toasted.show(this.__('Services Transaction has been deleted successfully'), {type: 'success'});
                        }




                    })
                    .catch((err) => {
                        this.spinnerLoadOnEdit = false
                        if (err.response && err.response.data && err.response.data.message) {
                            this.$toasted.show(this.__(err.response.data.message), { type: 'error' });
                        } else {
                            this.$toasted.show(this.__('An error occurred while updating the transaction'), { type: 'error' });
                        }

                    })
            }
        },
        created() {

            this.reservationServices = this.reservation.services;

            this.locale = Nova.config.local;
        },
        mounted() {

            if(Nova.app.$hasPermission('change service price')){
                this.canChangeServicePrice = true;
            }else{
                this.canChangeServicePrice = false;
            }

            Nova.$on('open-edit-modal' , (transaction) => {

                this.isLoading = true;
                // Get taxes on services if available and cache them here into an object
                Nova.request().get('/nova-vendor/calender/services-tax-info')
                    .then((res)=>{
                        this.unitServiceTaxInformation = res.data ;

                        this.transaction = transaction ;

                        for (let i = transaction.meta.services.length - 1; i >= 0; --i) {
                            this.obj = {
                                text: transaction.meta.services[i].statement,
                                price: Number(transaction.meta.services[i].price),
                                qty: Math.floor(transaction.meta.services[i].qty),
                                subTotal: transaction.meta.services[i].sub_total ,
                                vat : Number(transaction.meta.services[i].vat) ,
                                ttx : Number(transaction.meta.services[i].ttx),
                                vatIsChecked : transaction.meta.services[i].vatIsChecked,
                                ttxIsChecked : transaction.meta.services[i].ttxIsChecked ,
                                totalGeneralSum : Number(transaction.meta.services[i].totalGeneralSum),
                                id : transaction.meta.services[i].id
                            };


                            this.obj.subTotal = this.obj.price *  this.obj.qty;
                            this.obj.vat = this.unitServiceTaxInformation.vat_percentage ?  this.obj.subTotal * (this.unitServiceTaxInformation.vat_percentage / 100) : 0;
                            this.obj.ttx = this.unitServiceTaxInformation.tourism_percentage ?  this.obj.subTotal * (this.unitServiceTaxInformation.tourism_percentage / 100) : 0;
                            // this.obj.totalGeneralSum = this.obj.subTotal + this.obj.vat + this.obj.ttx  ;

                            if(!this.items.some(item => item.id === this.obj.id)){
                                this.items.push(this.obj);
                            }

                            this.isLoading = false;

                        }
                    })
                this.$refs.editServiceModal.open();
            });


        }

    }

</script>

<style scoped>

    .form_has_wrong_inputs{
        text-align: center;
        margin-bottom: 10px;
        color: red;
        font-weight: bold;
    }

    .custom-edit-btn{
        background : #0a7df3;
    }

    .addServiceModal .addServiceinside .super-select-container label.super-select-input {
        height: 40px;
        padding: 0 10px;
    }
    .addServiceModal .addServiceinside .super-select-container label.super-select-input input {
        padding: 0;
        font-size: 16px;
        font-weight: normal;
        line-height: 40px !important;
    }
    .addServiceModal .addServiceinside .nodata {
        font-size: 16px;
        text-align: center;
        line-height: 100px;
        color: #000;
    }
    .addServiceModal .addServiceinside .table_of_services {margin: 20px auto 10px;}
    .addServiceModal .addServiceinside .table_of_services table {
        width: 100%;
        border: 1px solid #ddd;
    }
    .addServiceModal .addServiceinside .table_of_services table thead tr th {
        background: #fafafa;
        border: 1px solid #ddd;
        padding: 10px;
        font-weight: normal;
        font-family: 'Dubai-Bold';
        font-size: 14px;
        vertical-align: middle;
    }
    .addServiceModal .addServiceinside .table_of_services table tbody tr td {
        border: 1px solid #ddd;
        text-align: center;
        padding: 10px;
        font-size: 16px;
        vertical-align: middle;
        color: #000;
    }
    .addServiceModal .addServiceinside .table_of_services table tbody tr td input[type="number"] {
        border: 1px solid #ddd;
        border-radius: 5px !important;
        text-align: center;
        padding: 0;
        height: 36px;
        width: 70px;
        font-size: 20px;
        line-height: 36px;
    }
    .addServiceModal .addServiceinside .table_of_services table tbody tr td:first-child {text-align: right;}
    .addServiceModal .addServiceinside .table_of_services table tbody tr td .checkbox p {
        display: inline-block;
        margin: 0 0 0 5px;
    }
    .addServiceModal .addServiceinside .table_of_services table tfoot tr td {
        padding: 10px;
        text-align: center;
        border: 1px solid #ddd;
        background: #fafafa;
        color: #000;
        font-size: 16px;
        vertical-align: middle;
    }
    .addServiceModal .addServiceinside .table_of_services table tfoot tr td p {
        display: block;
        margin: 5px auto 0;
    }
    .addServiceModal .addServiceinside .table_of_services table tfoot tr td table{border: none !important;}
    .addServiceModal .addServiceinside .table_of_services table tfoot tr td table tr td {
        text-align: center !important;
        border-right: none !important;
        border-top: none !important;
    }
    .addServiceModal .addServiceinside .table_of_services table tfoot tr td table tr td:last-child {border-left: none;}
    .switch {
        position: relative;
        display: inline-block;
        width: 60px;
        height: 26px;
        margin: 5px auto;
    }
    .switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }
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
    }
    .slider.round {
        border-radius: 34px;
    }
    input:checked + .slider {
        background-color: #21b978;
    }
    .slider::before {
        position: absolute;
        content: "";
        height: 20px;
        width: 20px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        -webkit-transition: .4s;
        transition: .4s;
    }
    .slider.round::before {
        border-radius: 50%;
    }
    input:checked + .slider::before {
        -webkit-transform: translateX(33px);
        transform: translateX(33px);
    }
    .addServiceModal .addServiceinside button.addbut {
        max-width: 100%;
        min-width: 50%;
        display: inline-block;
        width: auto;
        height: 40px;
        line-height: 40px;
        padding: 0 10px;
        font-size: 16px;
    }

    .custom-select{
        font-weight: bold;
        font-size: 16px;
        color: black;
    }
    .totle_cost h2 {
        font-size: 14px;
        margin: 0 auto 5px;
    }
    .totle_cost h4 b {
        font-weight: normal;
        font-family: Dubai-Bold;
    }
    /* Portrait phones and smaller */
    @media (min-width: 320px) and (max-width: 480px) {
        .addServiceModal .addServiceinside .table_of_services {
            max-width: 100%;
            overflow: auto;
        }
        .addServiceModal .addServiceinside .nodata {font-size: 14px;}
    }

    /* Smart phones and Tablets */
    @media (min-width: 481px) and (max-width: 767px) {
        .addServiceModal .addServiceinside .table_of_services {
            max-width: 100%;
            overflow: auto;
        }
    }

    /* Small Screens */
    @media (min-width: 768px) and (max-width: 991px) {
        .addServiceModal .addServiceinside .table_of_services {
            max-width: 100%;
            overflow: auto;
        }
    }

    .spinner {
        /* Spinner size and color */
        width: 1.5rem;
        height: 1.5rem;
        border-top-color: #444;
        border-left-color: #444;

        /* Additional spinner styles */
        animation: spinner 400ms linear infinite;
        border-bottom-color: transparent;
        border-right-color: transparent;
        border-style: solid;
        border-width: 2px;
        border-radius: 50%;
        box-sizing: border-box;
        display: inline-block;
        vertical-align: middle;
    }

    /* Animation styles */
    @keyframes spinner {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }


    /* Optional — create your own variations! */
    .spinner-large {
        width: 5rem;
        height: 5rem;
        border-width: 6px;
    }

    .spinner-slow {
        animation: spinner 1s linear infinite;
    }

    .spinner-blue {
        border-top-color: #09d;
        border-left-color: #09d;
    }
    .spinner-light {
        border-top-color: #ffffff;
        border-left-color: #ffffff;
    }
</style>

