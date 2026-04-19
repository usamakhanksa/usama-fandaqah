<template>
    <div v-if="reservation" class="services_statement" v-permission="'view statements'">

        <div class="title" v-if="!quick">{{__('Services')}}</div>
        <div class="content" v-if="!quick">
            <div v-if="reservation.reservation_type == 'single' && reservation.services.length === 0" class="no_services">
                <div>
                    <div class="icon"></div>
                    <span>{{__('No services found!')}}</span>
                </div>
            </div>

            <div v-if="reservation.reservation_type == 'group' && reservation.group_reservation_services.length === 0" class="no_services">
                <div>
                    <div class="icon"></div>
                    <span>{{__('No services found!')}}</span>
                </div>
            </div>
            <!-- no_services -->
            <div class="all_services_items" v-if="reservation.reservation_type == 'single' && reservation.services.length">
                <div class="service_item" v-for="(service,index) in reservation.services.slice(0,4)" :key="index" @click="openServiceModal(service)">
                    <div class="col_right">
                        <span>{{__('Number')}} : <p>{{service.service_log.number}}</p></span>

                        <span>{{__('Amount')}} : <p class="d-inline-flex">{{Math.abs(reservation.wallet.decimal_places == 3 ? service.amount / 1000 : service.amount / 100 )}} <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span>  </p></span>
                    </div><!-- col_right -->
                    <div class="col_left">
                        <time>{{service.created_at}}</time>
                        <div class="name">
                            <!-- {{service.meta}} -->
                            <p v-if=" service.meta && service.meta.pos">POS</p>
                            <!-- <p v-if=" 'payment_type' in  service.meta">POS</p> -->
                            <label>{{__('Service Transaction')}}</label>
                            </div>
                    </div><!-- col_left -->
                </div><!-- service_item -->
                <div v-if="reservation.services.length > 4" @click="openAllModal" class="more_services"></div>
            </div><!-- all_services_items -->

            <!-- Group Services  -->
            <div class="all_services_items" v-if="reservation.reservation_type == 'group' && reservation.group_reservation_services.length">
                <div class="service_item" v-for="(service,index) in reservation.group_reservation_services.slice(0,4)" :key="index" @click="openServiceModal(service)">
                    <div class="col_right">
                        <span>{{__('Number')}} : <p>{{service.service_log.number}}</p></span>

                        <span>{{__('Amount')}} : <p class="d-inline-flex">{{Math.abs(reservation.wallet.decimal_places == 3 ? service.amount / 1000 : service.amount / 100 )}} <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></p></span>
                    </div><!-- col_right -->
                    <div class="col_left">
                        <time>{{service.created_at}}</time>
                        <div class="name">
                            <!-- {{service.meta}} -->
                            <p v-if=" service.meta && service.meta.pos">POS</p>
                            <!-- <p v-if=" 'payment_type' in  service.meta">POS</p> -->
                            <label>{{__('Service Transaction')}}</label>
                            </div>
                    </div><!-- col_left -->
                </div><!-- service_item -->
                <div v-if="reservation.group_reservation_services.length > 4" @click="openAllModal" class="more_services"></div>
            </div><!-- Group services -->


            <div class="block_bottom">
                <button class="add_service" v-if="(reservation.status == 'confirmed' && !reservation.checked_out) || occ "  v-permission="'add service transaction from reservation'" @click="openAddModal">{{__('Add Services')}}<span v-if="spinnerLoad" class="spinner spinner-light" :class="[locale === 'ar' ? 'mr-2' : 'ml-2']"></span></button>
                <!-- <button class="add_service" v-else v-permission="'add services'" @click="openAddModal">{{__('Add Services')}}<span v-if="spinnerLoad" class="spinner spinner-light" :class="[locale === 'ar' ? 'mr-2' : 'ml-2']"></span></button> -->
                <button v-if="reservation.reservation_type == 'single' && reservation.services.length > 4" @click="openAllModal" class="more">{{__('more')}}({{reservation.services.length}}) ...</button>
                <button v-if="reservation.reservation_type == 'group' && reservation.group_reservation_services.length > 4" @click="openAllModal" class="more">{{__('more')}}({{reservation.group_reservation_services.length}}) ...</button>
            </div><!-- block_bottom -->
        </div><!-- content -->



        <!-- Start Add Services -->
        <sweet-modal @close="addModalClosed" class="add_service_modal" ref="addServiceModal" :enable-mobile-fullscreen="false" :pulse-on-block="false" :title="__('Add Services')" overlay-theme="dark">
            <div class="choose_category" v-if="this.serviceCategories.length">
                <select v-model="selectedServiceCategory" @change="selectServiceCategory" :disabled="!canSelectCategory">
                    <option v-for="(category,index) in serviceCategories" :key="index" :value="index">{{  category.name[locale]}}</option>
                </select>
                <select v-model="selectedOption" v-if="selectedServiceCategory !== -1" @change="pushToContainerServices">
                    <option v-for="(option,i) in serviceCategories[selectedServiceCategory].services_for_reservation" :key="i" :value="i">{{ option.name[locale] }}</option>
                </select>
            </div><!-- choose_category -->
            <!--      <div v-else>-->
            <!--        <button  class="addbut shadow mb-2  btn btn-default btn-primary mt-2"  @click="gotoServiceCategory">{{__('Add Service Category')}}</button>-->
            <!--      </div>-->
            <div v-if="!items.length" class="select_category_target">
                {{__('You should select service category then the service you target')}}
            </div><!-- select_category_target -->
            <div v-if="items.length" class="table_of_services">
                <template class="relative">
                    <loading :active.sync="isLoading" :is-full-page="false"></loading>
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
                        <th style="width: 20%;" v-if="unitServiceTaxInformation.vat_percentage">{{__('VAT')}} (%{{Number(unitServiceTaxInformation.vat_percentage)}})</th>
                        <th style="width: 20%;" v-if="unitServiceTaxInformation.tourism_percentage">{{__('TTX')}} (%{{Number(unitServiceTaxInformation.tourism_percentage)}})</th>
                        <th colspan="2">{{__('Total With Tax')}}</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="(item,index) in items" :key="index">
                        <td>{{item.text}}</td>
                        <td v-if="canChangeServicePrice"><input @input="selected(item)"  v-model="item.price" type="number"  v-on:keyup="this.value = this.value.replace(/[^0-9.]/g, '')" ></td>
                        <td v-else>{{item.price}}</td>
                        <td><input @input="selected(item)"  v-model="item.qty" type="number"  oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"  value="1"></td>
                        <td><template>{{item.subTotal}}</template></td>
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
                            <button type="button" @click="removeService(items[index])" class="delete_button"></button>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div><!-- table_of_services -->
            <div v-if="items.length" class="w-full">
                <div class="rounded w-full flex flex-wrap my-3">
                    <div class="w-1/2 sm:w-1/1 md:w-1/3 lg:w-1/5 xl:w-1/5 totle_cost">
                        <div class="relative">
                            <h2 class="block capitalize text-md mb-2 w-full">{{__('Sub total')}}</h2>
                            <h4 class="text-base font-normal w-full"><b class="text-black-500">{{ sumSubTotal }}</b></h4>
                        </div>
                    </div>
                    <div class="w-1/2 sm:w-1/1 md:w-1/3 lg:w-1/5 xl:w-1/5 totle_cost" v-if="reservation.unit.tax">
                        <div class="relative">
                            <h2 class="block capitalize text-md mb-2 w-full">{{__('Total Vat')}}</h2>
                            <h4 class="text-base font-normal w-full"><b class="text-black-500">{{ sumVatTotal }}</b></h4>
                        </div>
                    </div>
                    <div class="w-1/2 sm:w-1/1 md:w-1/3 lg:w-1/5 xl:w-1/5 totle_cost" v-if="reservation.unit.tourism_tax">
                        <div class="relative">
                            <h2 class="block capitalize text-md mb-2 w-full">{{__('Total Ttx')}}</h2>
                            <h4 class="text-base font-normal w-full"><b class="text-black-500">{{sumTtxTotal}}</b></h4>
                        </div>
                    </div>
                    <div class="w-1/2 sm:w-1/1 md:w-1/3 lg:w-1/5 xl:w-1/5 totle_cost">
                        <div class="relative">
                            <h2 class="block capitalize text-md mb-2 w-full">{{__('Sum of Taxes')}}</h2>
                            <h4 class="text-base font-normal w-full"><b class="text-black-500">{{ parseFloat(sumTotalTaxes).toFixed(2) }}</b></h4>
                        </div>
                    </div>
                    <div class="w-1/2 sm:w-1/1 md:w-1/3 lg:w-1/5 xl:w-1/5 totle_cost">
                        <div class="relative">
                            <h2 class="block capitalize text-md mb-2 w-full">{{__('Sum of total with tax')}}</h2>
                            <h4 class="text-base font-normal w-full"><b class="text-black-500">{{ sumGeneralTotalWithTaxes }}</b></h4>
                        </div>
                    </div>
                </div>
            </div>
            <div v-if="items.length">
                <button  class="add_service_button" :disabled="disableAdd" @click="addServices">{{__('Save')}} <span v-if="spinnerLoadOnAdd" class="spinner spinner-light"></span></button>
            </div>
        </sweet-modal>
        <!-- End Add Services -->

        <!-- Start More Services -->
        <sweet-modal ref="servicesModal" :enable-mobile-fullscreen="false" :pulse-on-block="false" :title="__('Services')" overlay-theme="dark" class="more_services_modal">
           <div v-if="reservation.reservation_type == 'single' && reservation.services.length ">
               <div class="service_item"  v-for="(service,index) in reservation.services" :key="index" @click="openServiceModal(service)">
                <div class="top_row">
                    <span>{{__('Service')}} : <p>{{__(service.meta.statement)}}</p></span>
                    <time>{{service.created_at}}</time>
                </div><!-- top_row -->
                <div class="bottom_row">
                    <span>{{__('Number')}} : <p>{{service.service_log.number}}</p></span>
                    <span>{{__('Qty')}} : <p>{{service.meta.qty}}</p></span>
                    <span v-if="service.meta['payment_type']">POS</span>
                    <span>{{__('Total Amount')}} : <p>{{Math.abs(reservation.wallet.decimal_places == 3  ? service.amount/ 1000 : service.amount/ 100 )}} <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></p></span>
                </div><!-- bottom_row -->
            </div><!-- service_item -->
           </div>

            <div v-if="reservation.reservation_type == 'group'  &&  reservation.group_reservation_services.length ">
                <div class="service_item"  v-for="(service,index) in reservation.group_reservation_services" :key="index" @click="openServiceModal(service)">
                    <div class="top_row">
                        <span>{{__('Service')}} : <p>{{service.meta.statement}}</p></span>
                        <time>{{service.created_at}}</time>
                    </div><!-- top_row -->
                    <div class="bottom_row">
                        <span>{{__('Number')}} : <p>{{service.service_log.number}}</p></span>
                        <span>{{__('Qty')}} : <p>{{service.meta.qty}}</p></span>
                        <span v-if="service.meta['payment_type']">POS</span>
                        <span>{{__('Total Amount')}} : <p>{{Math.abs(reservation.wallet.decimal_places == 3  ? service.amount/ 1000 : service.amount/ 100 )}} <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></p></span>
                    </div><!-- bottom_row -->
                </div><!-- service_item -->
            </div>

        </sweet-modal>
        <!-- End More Services -->

        <edit-services  :reservation="reservation"></edit-services>

        <!-- Service Show Modal -->
        <sweet-modal ref="serviceModal" v-if="current"  :enable-mobile-fullscreen="false" :pulse-on-block="false"  width="70%" :title="__('Service Details')" overlay-theme="dark" class="service_modal">

            <div class="share_button_reservation">
                <a class="pdf_button" :href="'/home/reservation/pdf/service-transaction/' + current.hash_id  " target="_blank"></a>
                <a v-permission="'print invoices'" class="print_button" :href="'/home/reservation/sub-invoice-service/' + current.hash_id " target="_blank"></a>
                <button   class="edit_button"  @click.prevent="openEditModal(current)" v-show="canEditService && serviceTransactionDoesntHasInvoice" v-if="(!reservation.checked_out && reservation.status != 'canceled') || ( (reservation.status == 'canceled') && occ)"></button>
                <button   class="trash_button"  @click.prevent="openDeleteModal(current)" v-show="canDeleteService && serviceTransactionDoesntHasInvoice" v-if="(!reservation.checked_out && reservation.status != 'canceled') || ( (reservation.status == 'canceled') && occ)"></button>
            </div><!-- share_button_reservation -->
            <div class="embed_area relative" v-if="typeof current.id  !== 'undefined'">
                <iframe id="frameServiceInfo" :src="'/home/reservation/sub-invoice-service/' + current.hash_id + '?type=embed' "></iframe>
            </div>
        </sweet-modal>

    <!-- <delete-resource-modal v-if="deleteModalOpen" @confirm="confirmDelete" @close="closeDeleteModal"  class="delete_confirm_modal">
      <div class="delete_confirm_modal_content">
        <h1>{{__('are you sure ?')}}</h1>
        <h2>{{__('Deletion cannot be undone, would you like to continue ?')}}</h2>
      </div>
    </delete-resource-modal> -->

    <sweet-modal  :enable-mobile-fullscreen="false" :pulse-on-block="false" :hide-close-button="true" overlay-theme="dark" ref="deleteConfirm" class="delete_confirm_modal">
      <div class="delete_confirm_modal_content">
        <loading :active.sync="isLoading" :is-full-page="false" />
        <h1>{{__('are you sure ?')}}</h1>
        <h2>{{__('Deletion cannot be undone, would you like to continue ?')}}</h2>
        <div class="buttons_delete">
          <button id="confirm-delete-button" @click="confirmDelete" class="yes_delete_button">{{__('Yes, delete !')}}</button>
          <button type="button" @click="closeDeleteModal" class="back_delete_button"> {{__('Do not retreat !')}}</button>
        </div>
      </div>
    </sweet-modal>

  </div>
</template>

<script>

    import EditServices from "./EditServices";
    // Import component
    import Loading from 'vue-loading-overlay';
    // Import stylesheet
    import 'vue-loading-overlay/dist/vue-loading.css';

    export default {
        name: "services_statement",
        props: ["reservation","quick","occ"],
        components:{
            Loading ,
            EditServices
        },
        data: () => {
            return {
                data: [],
                locale : null,
                serviceCategories : [],
                containerServicesArray : [],
                current: {},
                loading: false,
                add_loading: false,
                deleteModalOpen: false,
                reservationServices:[] ,
                hashed_id : null,
                selectedServiceCategory:-1,
                selectedOption:'',
                unitServiceTaxInformation : {},
                items: [],
                spinnerLoad : false,
                spinnerLoadOnAdd : false,
                isLoading : false,
                services : null,
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
                qtyError : false,
                serviceCategoryCount : 0,
                invoices : [],
                serviceTransactionDoesntHasInvoice : true,
                loader : false,
                reservation : null,
                quickReservation : null,
                quickModal : null,
                disableAdd : false,
                showValidationMsg : false,
                canChangeServicePrice : false,
                canSelectCategory : true,
                canEditService : false,
                canDeleteService : false,
                currency :Nova.app.currentTeam.currency,

            }
        },
        computed:{
            // get the service selected to be pushed to our container array
            service(){
                return this.serviceCategories[this.selectedServiceCategory].services_for_reservation[this.selectedOption]
            },
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
        watch: {
            items: {
                handler: function(val,oldVal) {
                    this.canSelectCategory = true;
                    /**
                    if(val.length){
                        this.canSelectCategory = false;
                    }else{
                        this.canSelectCategory = true;
                    }
                    */
                },
                deep: true
            }
        },
        methods: {

            gotoServiceCategory(){
                this.$refs.servicesModal.close();
                this.$router.push('/resources/services-categories/new?viaResource=&viaResourceId=&viaRelationship=');
            },
            pushToContainerServices(){
                this.obj = {
                    text: this.service.text,
                    price: Number(this.service.price),
                    qty: Math.floor(1),
                    subTotal: 0 ,
                    vat : Number(0) ,
                    ttx : Number(0),
                    vatIsChecked : false,
                    ttxIsChecked : false ,
                    totalGeneralSum : Number(0),
                    id : this.service.id
                };

                if(this.unitServiceTaxInformation.vat_percentage){
                    this.obj.vatIsChecked = true;
                }

                this.obj.subTotal = this.obj.price *  this.obj.qty;
                this.obj.vat = this.reservation.unit ?  this.obj.subTotal * (this.unitServiceTaxInformation.vat_percentage / 100) : 0;
                this.obj.ttx = this.reservation.unit ?  this.obj.subTotal * (this.unitServiceTaxInformation.tourism_percentage / 100) : 0;
                this.obj.totalGeneralSum = this.obj.subTotal;
                if(this.obj.vatIsChecked){
                    this.obj.totalGeneralSum += this.obj.vat;
                }
                // selected Option became undefined when changing the root category
                if(typeof this.selectedOption  !== 'undefined'){
                    // Determine if object in the array or not to avoid duplicates
                    if(!this.items.some(item => item.id === this.service.id)){
                        this.items.push(this.obj);
                    }
                }
            },
            selected(item) {
                if(!item.price || item.price <= 0  || !item.qty || item.qty <= 0){
                    this.disableAdd = true;
                    this.showValidationMsg = true;
                }else{
                    this.disableAdd = false;
                    this.showValidationMsg = false;
                }


                // Update Specific object with new values after changing the quantity
                let new_sub_total = item.qty * item.price ;
                for (let i = this.items.length - 1; i >= 0; --i) {

                    if (this.items[i].id === item.id) {
                        this.items[i].qty = item.qty;
                        this.items[i].subTotal = new_sub_total;
                        this.items[i].vat = this.reservation.unit ? (this.unitServiceTaxInformation.vat_percentage / 100) * new_sub_total : 0;
                        this.items[i].ttx = this.reservation.unit ?  (this.unitServiceTaxInformation.tourism_percentage / 100) * new_sub_total : 0 ;
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
                            this.items[i].totalGeneralSum += this.reservation.unit ?  (this.unitServiceTaxInformation.vat_percentage / 100) * item.subTotal  : 0 ;
                        }
                    }
                }else{
                    for (let i = this.items.length - 1; i >= 0; --i) {
                        if (this.items[i].id === item.id) {
                            item.vatIsChecked = false ;
                            this.items[i].totalGeneralSum -= this.items[i].vat ;
                            this.items[i].vat  = this.reservation.unit ?  (this.unitServiceTaxInformation.vat_percentage / 100) * item.subTotal  : 0 ;
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
                            this.items[i].totalGeneralSum += this.reservation.unit ?  (this.unitServiceTaxInformation.tourism_percentage / 100) * item.subTotal  : 0 ;
                        }
                    }
                }else{
                    for (let i = this.items.length - 1; i >= 0; --i) {
                        if (this.items[i].id === item.id) {
                            item.ttxIsChecked = false ;
                            this.items[i].totalGeneralSum -= this.items[i].ttx;
                            this.items[i].ttx = this.reservation.unit ?  (this.unitServiceTaxInformation.tourism_percentage / 100) * item.subTotal  : 0 ;
                        }
                    }
                }
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

                if(!this.items.length){
                    this.selectedServiceCategory = -1;
                }

            },
            containsObject(obj, list) {
                let i;
                for (i = 0; i < list.length; i++) {
                    if (list[i] === obj) {
                        return true;
                    }
                }
                return false;
            },
            selectServiceCategory:function() {
                this.selectedOption = '';
            },
            add() {
                if (!this.item.statement || !this.item.price || !this.item.qty) {
                    this.$toasted.show(this.__('Please fill all service info'), {type: 'error'});
                    return;
                }
                this.loading = true;
                axios.post('/nova-vendor/calender/reservation/transaction', {
                    id: this.reservation.id,
                    type: 'service',
                    amount: this.item.price,
                    meta: {
                        category: 'service',
                        statement: this.item.statement,
                        qty: this.item.qty
                    }
                })
                    .then(response => {



                        this.item.original_price = null;
                        this.item.price = 0;
                        this.item.statement = null;
                        this.item.qty = 1;
                        this.$toasted.show(this.__('Service added successfully'), {type: 'success'});

                        Nova.$emit('update-reservation', true)
                        this.loading = false;
                    }).catch(err => {
                    this.loading = false;
                    this.$toasted.show(this.__(err), {type: 'error'})
                })
            },
            openAllModal() {
                this.$refs.servicesModal.open()
            },
            openAddModal() {

                this.getServiceTaxInfo();
                // invoice part
                if(this.invoices.length){

                    axios.get(`/nova-vendor/calender/check-add-service-capability?res_id=${this.reservation.id}`)
                        .then( res => {
                            if(res.data.flag == 'forbidden'){
                                // means that there is an invoice when trying to add a service that matches the date of the invoice
                                this.$toasted.show(Nova.app.__( "Sorry you can't add this service / services cause there is an invoice with number :number" , {number : res.data.invoice_number }),
                                    {
                                            duration : 4000,
                                            type: 'error',
                                            position : 'top-center',

                                    }
                                );

                                return false;
                            }else{
                                this.$refs.addServiceModal.open();
                            }

                        })
                    /**
                    let invoiceFoundForCurrentDate =  this.checkInvoicesFirst();
                    if(invoiceFoundForCurrentDate){

                        this.$toasted.show(this.__('You can\'t add any more service transaction cause there is already invoice with the same date of transaction date and it is :date , to add service transaction please delete the invoice and try again' , { date : moment().startOf('day').format('YYYY/MM/DD') }), {
                            duration : 5000,
                            type: 'error',
                            position: "top-center",
                        });

                        return false;
                    }


                    let lastInvoice =   this.invoices[0];

                    let lastInvoiceDate = new Date(moment(lastInvoice.to)) ;
                    let reservationLastDate = new Date(moment(this.reservation.date_out).subtract(1,'days'));



                    if(lastInvoiceDate.getTime() >= reservationLastDate.getTime()){

                        if(this.quick){
                            if(this.reservation.id === this.quickReservation.id){
                                Nova.$emit('add-service-transaction-error' , this.quickReservation);
                                return false;
                            }
                        }else{
                            this.$toasted.show(this.__('You can\'t add any more service transaction cause there is old invoice and there are no available invoices to add'), {
                                duration : 5000,
                                type: 'error',
                                position: "top-center",
                            });
                            return false;
                        }

                    }
                    */



                }else{
                    // else here the second case while no invoices available , however the reservation is in old state and can't have invoice with todays date
                    let currentDate = new Date(moment().startOf('day').format('Y-m-d')).getTime() ;
                    let reservationLastDate = new Date(moment(this.reservation.date_out).subtract(1,'days').format('Y-m-d'));
                    if(currentDate >= reservationLastDate.getTime()){

                        this.$toasted.show(this.__('You can\'t add any service transaction cause the reservation can\'t have any invoice with todays date unless you updated the reservation first'), {
                            duration : 5000,
                            type: 'error',
                            position: "top-center",
                        });
                        return false;
                    }else{
                        this.$refs.addServiceModal.open();
                    }

                }


                this.spinnerLoad = true;
                this.add_loading = true;

                this.selectedServiceCategory = -1 ;
                this.containerServicesArray = [];
                this.items = [];

                this.add_loading = false;
                this.spinnerLoad = false;



                this.getServicesList();
                // this.getServiceCategories();
                // this.getServiceTaxInfo();

                /**
                if(this.quick){
                    if(this.reservation.id === this.quickReservation.id){
                        this.quickModal = this.$refs.addServiceModal ;
                        // this.quickModal.open();
                        this.$refs.addServiceModal.open() ;
                    }
                }else{

                    this.$refs.addServiceModal.open();
                }

                */




            },

            addServices(){
                this.spinnerLoadOnAdd = true;
                let params = {
                    items : this.items ,
                    category : this.serviceCategories[this.selectedServiceCategory].name[this.locale],
                    reservation_id : this.reservation.id,
                    sumGeneralTotalWithTaxes : this.sumGeneralTotalWithTaxes  ,
                    sumSubTotal : this.sumSubTotal ,
                    sumVatTotal : this.sumVatTotal,
                    sumTtxTotal : this.sumTtxTotal,
                    sumQuantities : this.sumQuantities
                };
                Nova.request().post('/nova-vendor/calender/reservation/addServices' , params)
                    .then((response)=>{


                        if(this.quick){
                            if(this.reservation.id === this.quickReservation.id){
                                this.quickModal.close();
                                Nova.$emit('service-added', this.quickReservation);
                            }
                        }else{
                            this.$refs.addServiceModal.close();
                           Nova.$emit('update-reservation', true)
                        }
                        this.spinnerLoadOnAdd = false ;


                        this.$toasted.show(this.__('Services has been added successfully'), {type: 'success'});

                    })
            },
            checkInvoicesFirst(){

                let invoicesFromDateExtracted =  this.invoices.map( function(obj) { return new Date(moment(obj.from)).getTime(); });
                let invoicesToDateExtracted =  this.invoices.map( function(obj) { return new Date(moment(obj.to)).getTime(); });
                let currentDate = new Date(moment().startOf('day')).getTime();
                let datesArray = this.getDates(invoicesFromDateExtracted[0],invoicesToDateExtracted[0]);


                if(datesArray.includes(currentDate)){
                    return true ;
                }else{
                    return false;
                }

            },
            getDates(start, end) {
                let dateArray = [];
                let currentDate = moment(start);
                let stopDate = moment(end);
                while (currentDate <= stopDate) {
                    dateArray.push( new Date(moment(currentDate)).getTime());
                    currentDate = moment(currentDate).add(1, 'days');
                }
                return dateArray;
            },
            update_price(item) {
                // this.item.price = this.item.original_price * this.item.qty
            },
            checkServiceTransactionDate(service){

                /*--- this function will simply check if service creation date matched a date in our dates array of invoices to disable edit & delete ------*/

                let serviceTransactionDate = new Date(moment(service.created_at).startOf('day')).getTime();
                let invoicesFromDateExtracted =  this.invoices.map( function(obj) { return new Date(moment(obj.from)).getTime(); });
                let invoicesToDateExtracted =  this.invoices.map( function(obj) { return new Date(moment(obj.to)).getTime(); });
                /**
                 * _.concat from loadash to combine the two arrays  ( but it doesnt remove duplicates )
                 * new Set make a unique from the result ( but it returns an object key => value )
                 * Array.from(set) converts set into an array
                 * awesome
                 * @type {any[]}
                 */
                let datesArray = Array.from(new Set(_.concat(invoicesFromDateExtracted,invoicesToDateExtracted)));

                if(datesArray.includes(serviceTransactionDate)){
                    return false;
                }else{
                    return true;
                }

            },
            openServiceModal(service) {

                 $( '#frameServiceInfo' ).attr( 'src', function ( i, val ) { return val; });

                this.current = service

                 if(Nova.app.$hasPermission('edit service transaction from reservation')){
                    this.canEditService = true;
                }else{
                    this.canEditService = false;
                }

                if(Nova.app.$hasPermission('delete service transaction from reservation')){
                    this.canDeleteService = true;
                }else{
                    this.canDeleteService = false;
                }

                // making sure it's a pos service transaction
                if(this.current.meta && this.current.meta.pos){

                     if(Nova.app.$hasPermission('edit service transaction from pos')){
                         this.canEditService = true;
                     }else{
                        this.canEditService = false;
                     }

                     if(Nova.app.$hasPermission('delete service transaction from pos')){
                         this.canDeleteService = true;
                     }else{
                       this.canDeleteService = false;
                     }
                }

                    axios.get(`/nova-vendor/calender/check-add-service-capability?res_id=${this.reservation.id}`)
                        .then( res => {
                            if(res.data.flag == 'forbidden'){
                                 this.serviceTransactionDoesntHasInvoice = false;
                            }else{
                                 this.serviceTransactionDoesntHasInvoice = true;
                            }

                        })

                this.$refs.serviceModal.open()

               /** if(this.invoices.length){
                    this.serviceTransactionDoesntHasInvoice = this.checkServiceTransactionDate(service);
                } */
            },
            isNumberKey(evt){
                var charCode = (evt.which) ? evt.which : event.keyCode
                if (charCode > 31 && (charCode < 48 || charCode > 57))
                    return false;
                return true;
            },
            openDeleteModal(service) {
                if(service.is_attached_to_invoice){
                    this.$toasted.show(this.__('Can not delete this service cause it was attached to an invoice'), {type: 'error'});
                    return;
                }
                // close parent component
                this.$refs.serviceModal.close() ;
                this.deleteModalOpen = true;
                this.$refs.deleteConfirm.open();
                this.service = service ;
            },
            openEditModal(service){
                if(service.is_attached_to_invoice){
                    this.$toasted.show(this.__('Can not edit this service cause it was attached to an invoice'), {type: 'error'});
                    return;
                }
                this.current = service ;
                this.$refs.serviceModal.close();
                Nova.$emit('open-edit-modal' , service);
            },
            confirmDelete() {

                let self = this;
                self.$refs.serviceModal.close() ;
                self.$refs.servicesModal.close() ;
                // sending the request here
                Nova.request().post('/nova-vendor/calender/reservation/delete-service', {
                    id: self.current.id,
                    reservation_id: self.current.payable_id,
                })
                    .then(response => {
                        self.$toasted.show(self.__('Service deleted successfully'), {type: 'success'});
                        self.closeDeleteModal();
                        Nova.$emit('service-transaction-deleted');
                        this.reservation.services = this.reservation.services.filter(function(el) { return el.id != self.current.id; });

                    })
                    .catch((err) => {
                        this.loading = false;
                        if (err.response && err.response.data && err.response.data.message) {
                            this.$toasted.show(this.__(err.response.data.message), { type: 'error' });
                        } else {
                            this.$toasted.show(this.__('An error occurred while updating the transaction'), { type: 'error' });
                        }

                    });


            },
            closeDeleteModal() {
                this.deleteModalOpen = false
                this.$refs.deleteConfirm.close();
                // this.$refs.serviceModal.open() ;
            },

            getServicesList(){
                axios
                    .get('/nova-vendor/calender/reservation/service-list')
                    .then(response => {
                        this.data = response.data
                        this.serviceCategories = response.data ;
                    })
            },
            getServiceCategories(){
                Nova.request('/nova-vendor/calender/check-services-categories')
                    .then((res) => {
                        this.serviceCategoryCount = res.data ;
                    })
            },
            getServiceTaxInfo(){
                // Get taxes on services if available and cache them here into an object
                Nova.request().get('/nova-vendor/calender/services-tax-info')
                    .then((res)=>{
                        this.unitServiceTaxInformation = res.data ;
                    })
            },
            addModalClosed(){
                this.disableAdd = false;
                this.showValidationMsg = false;
            }

        },
        created() {


        },

        beforeDestroy(){
            Nova.$off('update-reservation');
            Nova.$off('service-added');
            Nova.$off('open-edit-modal');
            Nova.$off('service-transaction-deleted');
        },

        mounted() {
            this.reservationServices = this.reservation.services;
            this.locale = Nova.config.local;

            if(Nova.app.$hasPermission('change service price')){
                this.canChangeServicePrice = true;
            }else{
                this.canChangeServicePrice = false;
            }
            // the part of invoices
            this.invoices = this.reservation.invoices ;
            Nova.$on('invoice-deleted' , () => {
                axios.get('/nova-vendor/calender/reservationInvoices?id=' + this.reservation.id)
                    .then((res) => {
                        this.invoices = _.orderBy(res.data, 'number', 'desc');
                    })
                    .catch((err) => {
                        console.log(err);
                    })
            });
            Nova.$on('invoice-added' , ($invoices) => {
                this.invoices = $invoices ;
            });
            Nova.$on('quick-open-service-transaction-modal' , (reservation) => {

                if(this.reservation.id === reservation.id){
                    this.quickReservation = reservation ;
                    this.invoices = reservation.invoices;
                    this.openAddModal();
                }

            });
        },

    }

</script>

<style lang="scss">

.service_modal {
    .sweet-modal {
        @media (min-width: 768px) and (max-width: 991px) {
            width: 95% !important;
        } /* @media */
    } /* sweet-modal */
    .embed_area {
        max-height: 500px;
        height: 100%;
        overflow-y: auto;
        display: block !important;
        scrollbar-width: thin;
        scrollbar-color: #ccc #f5f5f5;
        &::-webkit-scrollbar {width: 6px;}
        &::-webkit-scrollbar-track {background: #f5f5f5;}
        &::-webkit-scrollbar-thumb {background: #ccc;}
        &::-webkit-scrollbar-thumb:window-inactive {background: #f5f5f5;}
        @media (min-width: 320px) and (max-width: 480px) {
            display: none !important;
        } /* @media */
        iframe {
            width: 100%;
            height: 100%;
            min-height: 500px;
        } /* iframe */
    } /* embed_area */
} /* contract_modal */
    .form_has_wrong_inputs{
        text-align: center;
        margin-bottom: 10px;
        color: red;
        font-weight: bold;
    }
    .services_statement {
        margin: 20px auto;
        padding: 0 10px;
        .title {
            display: flex;
            align-items: center;
            justify-content: space-between;
            display: block;
            font-size: 20px;
            color: #000;
        } /* title */
        .content {
            width: auto;
            min-width: auto;
            max-width: none;
            background: #ffffff;
            border-radius: 5px;
            margin: 5px auto 0;
            border: 1px solid #ddd;
            padding: 10px;
            box-shadow: 0 2px 4px 0 rgba(0,0,0,.05);
            display: flex;
            flex-wrap: wrap;
            align-items: stretch;
            min-height: 296px;
            .no_services {
                min-height: 310px;
                text-align: center;
                display: flex;
                align-items: center;
                justify-content: center;
                flex-wrap: wrap;
                width: 100%;
                .icon {
                    background-image: url("data:image/svg+xml,%3C%3Fxml version='1.0'%3F%3E%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 64 64' width='512px' height='512px'%3E%3Cg%3E%3Cg id='Meal'%3E%3Cpath d='M61.525,35.9a4,4,0,0,0-5.439-1.462L38,44.978A5.005,5.005,0,0,0,33,40H13.013a.99.99,0,0,0-.242.03l-2.8.7A2.993,2.993,0,0,0,7,38H5a3,3,0,0,0-3,3V59a3,3,0,0,0,3,3H7a2.994,2.994,0,0,0,2.972-2.72l2.788.691A1.017,1.017,0,0,0,13,60H26.74a5.056,5.056,0,0,0,2.514-.676L60.075,41.363A4.032,4.032,0,0,0,61.525,35.9ZM8,59a1,1,0,0,1-1,1H5a1,1,0,0,1-1-1V41a1,1,0,0,1,1-1H7a1,1,0,0,1,1,1ZM59.066,39.636,28.253,57.593A3.054,3.054,0,0,1,26.74,58H13.122L10,57.227V42.781L13.136,42H33a2.992,2.992,0,0,1,2.573,4.533,3.093,3.093,0,0,1-.445.585A3.018,3.018,0,0,1,33,48H23v2H33a4.927,4.927,0,0,0,4.143-2.208L57.087,36.167a1.967,1.967,0,0,1,2.7.728A2.023,2.023,0,0,1,59.066,39.636Z' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%23DDDDDD'/%3E%3Cpath d='M8.105,31.447A1,1,0,0,0,9,32H55a1,1,0,0,0,.9-.553l2-4A1,1,0,0,0,57,26H54.949A21.011,21.011,0,0,0,36,6.1V6a4,4,0,0,0-8,0v.1A21.011,21.011,0,0,0,9.051,26H7a1,1,0,0,0-.895,1.447ZM32,4a2,2,0,0,1,2,2H30A2,2,0,0,1,32,4ZM30,8h4A19.007,19.007,0,0,1,52.949,26h-41.9A19.007,19.007,0,0,1,30,8ZM55.382,28l-1,2H9.618l-1-2Z' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%23DDDDDD'/%3E%3Cpath d='M34,10v2A15,15,0,0,1,48.526,23.25l1.936-.5A17,17,0,0,0,34,10Z' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%23DDDDDD'/%3E%3Crect x='30' y='10' width='2' height='2' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%23DDDDDD'/%3E%3Cpath d='M2.4,12h2a1.552,1.552,0,0,1,.412-1.083,3.611,3.611,0,0,0,0-4.5A1.558,1.558,0,0,1,4.4,5.33a1.542,1.542,0,0,1,.412-1.08A3.5,3.5,0,0,0,5.6,2h-2a1.553,1.553,0,0,1-.412,1.081A3.487,3.487,0,0,0,2.4,5.33a3.5,3.5,0,0,0,.788,2.252,1.631,1.631,0,0,1,0,2.167A3.493,3.493,0,0,0,2.4,12Z' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%23DDDDDD'/%3E%3Cpath d='M8.4,12h2a1.552,1.552,0,0,1,.412-1.083,3.611,3.611,0,0,0,0-4.5A1.558,1.558,0,0,1,10.4,5.33a1.542,1.542,0,0,1,.412-1.08A3.5,3.5,0,0,0,11.6,2h-2a1.553,1.553,0,0,1-.412,1.081A3.487,3.487,0,0,0,8.4,5.33a3.5,3.5,0,0,0,.788,2.252,1.631,1.631,0,0,1,0,2.167A3.493,3.493,0,0,0,8.4,12Z' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%23DDDDDD'/%3E%3Cpath d='M52.4,12h2a1.552,1.552,0,0,1,.412-1.083,3.611,3.611,0,0,0,0-4.5A1.558,1.558,0,0,1,54.4,5.33a1.542,1.542,0,0,1,.412-1.08A3.5,3.5,0,0,0,55.6,2h-2a1.553,1.553,0,0,1-.412,1.081A3.487,3.487,0,0,0,52.4,5.33a3.5,3.5,0,0,0,.788,2.252,1.631,1.631,0,0,1,0,2.167A3.493,3.493,0,0,0,52.4,12Z' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%23DDDDDD'/%3E%3Cpath d='M58.4,12h2a1.552,1.552,0,0,1,.412-1.083,3.611,3.611,0,0,0,0-4.5A1.558,1.558,0,0,1,60.4,5.33a1.542,1.542,0,0,1,.412-1.08A3.5,3.5,0,0,0,61.6,2h-2a1.553,1.553,0,0,1-.412,1.081A3.487,3.487,0,0,0,58.4,5.33a3.5,3.5,0,0,0,.788,2.252,1.631,1.631,0,0,1,0,2.167A3.493,3.493,0,0,0,58.4,12Z' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%23DDDDDD'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E%0A");
                    background-size: 60px;
                    width: 62px;
                    height: 62px;
                    display: block;
                    margin: 0 auto;
                    background-position: center center;
                    background-repeat: no-repeat;
                } /* icon */
                span {
                    display: block;
                    width: 100%;
                    font-size: 16px;
                    margin: 7px auto 0;
                    color: #dddddd;
                } /* span */
            } /* no_services */
            .all_services_items {
                min-height: 310px;
                width: 100%;
            } /* all_services_items */
            .service_item {
                display: flex;
                justify-content: space-between;
                align-items: center;
                width: 100%;
                flex-wrap: wrap;
                border: 1px solid #ddd;
                margin: 0 auto 10px;
                border-radius: 5px;
                padding: 5px;
                background: #fdfdfd;
                cursor: pointer;
                -webkit-transition: all 0.2s ease-in-out;
                -moz-transition: all 0.2s ease-in-out;
                -o-transition: all 0.2s ease-in-out;
                transition: all 0.2s ease-in-out;
                &:hover {
                    background: #f8f8f8;
                    border-color: #d8d8d8;
                } /* hover */
                .col_right {
                    width: 70%;
                    @media (min-width: 320px) and (max-width: 767px) {
                        width: 50%;
                    } /* Mobile */
                    span {
                        display: block;
                        font-size: 15px;
                        color: #666666;
                        p {
                            display: inline-block;
                            color: #000000;
                        } /* p */
                    } /* span */
                } /* col_right */
                .col_left {
                    width: 30%;
                    display: flex;
                    flex-wrap: wrap;
                    justify-content: space-between;
                    align-self: stretch;
                    @media (min-width: 320px) and (max-width: 767px) {
                        width: 50%;
                    } /* Mobile */
                    time {
                        display: flex;
                        justify-content: flex-end;
                        width: 100%;
                        font-size: 13px;
                        color: #777777;
                    } /* time */
                    .name {
                        display: flex;
                        align-self: flex-end;
                        justify-content: flex-end;
                        width: 100%;
                        label {
                            display: block;
                            border-radius: 100px;
                            border: 1px solid #777777;
                            padding: 0 15px;
                            min-width: 60px;
                            font-size: 14px;
                            height: 20px;
                            line-height: 18px;
                            color: #777777;
                        } /* label */
                        p {
                          border-radius: 100px;
                          border: 1px solid #bbbbbb;
                          padding: 0 10px;
                          min-width: 50px;
                          font-size: 14px;
                          height: 20px;
                          color: #000000;
                          margin: 0 0 0 5px;
                          display: flex;
                          align-items: center;
                          justify-content: center;
                          background: #dddddd;
                        } /* p */
                    } /* name */
                } /* col_left */
            } /* service_item */
            .more_services {
                background-image: url("data:image/svg+xml,%3C%3Fxml version='1.0'%3F%3E%3Csvg xmlns='http://www.w3.org/2000/svg' id='Capa_1' enable-background='new 0 0 515.555 515.555' height='512px' viewBox='0 0 515.555 515.555' width='512px' class=''%3E%3Cg%3E%3Cpath d='m496.679 212.208c25.167 25.167 25.167 65.971 0 91.138s-65.971 25.167-91.138 0-25.167-65.971 0-91.138 65.971-25.167 91.138 0' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%23666666'/%3E%3Cpath d='m303.347 212.208c25.167 25.167 25.167 65.971 0 91.138s-65.971 25.167-91.138 0-25.167-65.971 0-91.138 65.971-25.167 91.138 0' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%23666666'/%3E%3Cpath d='m110.014 212.208c25.167 25.167 25.167 65.971 0 91.138s-65.971 25.167-91.138 0-25.167-65.971 0-91.138 65.971-25.167 91.138 0' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%23666666'/%3E%3C/g%3E%3C/svg%3E%0A");
                background-color: #fafafa;
                border: 1px solid #ddd;
                border-radius: 5px;
                height: 30px;
                margin: 0 auto 10px;
                background-size: 30px;
                background-repeat: no-repeat;
                background-position: center center;
                cursor: pointer;
                &:hover {
                    background-color: #f5f5f5;
                    border-color: #d8d8d8;
                } /* hover */
            } /* more_services */
            .block_bottom {
                display: flex;
                justify-content: space-between;
                align-items: center;
                flex-wrap: wrap;
                width: 100%;
                align-self: end;
                button.add_service {
                    background: #4099de;
                    border-radius: 5px;
                    border: 1px solid #4099de;
                    min-width: 100px;
                    height: 35px;
                    line-height: 35px;
                    font-size: 15px;
                    color: #ffffff;
                    padding: 0 15px;
                    -webkit-transition: all 0.2s ease-in-out;
                    -moz-transition: all 0.2s ease-in-out;
                    -o-transition: all 0.2s ease-in-out;
                    transition: all 0.2s ease-in-out;
                    &:hover {
                        background: #0071C9;
                        border-color: #0071C9;
                    } /* hover */
                } /* add_service */
                button.more {
                    background: #ffffff;
                    border-radius: 5px;
                    border: 1px solid #4099de;
                    min-width: 100px;
                    height: 35px;
                    line-height: 35px;
                    font-size: 15px;
                    color: #4099de;
                    padding: 0 15px;
                    -webkit-transition: all 0.2s ease-in-out;
                    -moz-transition: all 0.2s ease-in-out;
                    -o-transition: all 0.2s ease-in-out;
                    transition: all 0.2s ease-in-out;
                    &:hover {
                        background: #4099de;
                        color: #ffffff;
                    } /* hover */
                } /* more */
            } /* block_bottom */
        } /* content */
    } /* services_statement */
    .add_service_modal {
        .sweet-modal {
            min-width: 60%;
            max-width: 100%;
            width: auto !important;
            @media (min-width: 320px) and (max-width: 767px) {
                min-width: 90%;
            } /* Mobile */
        } /* sweet-modal */
        .choose_category {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            select {
                background-image: url("data:image/svg+xml,%3C%3Fxml version='1.0' encoding='iso-8859-1'%3F%3E%3C!-- Generator: Adobe Illustrator 19.0.0, SVG Export Plug-In . SVG Version: 6.00 Build 0) --%3E%3Csvg version='1.1' id='Layer_1' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' x='0px' y='0px' viewBox='0 0 491.996 491.996' style='enable-background:new 0 0 491.996 491.996;' xml:space='preserve'%3E%3Cg%3E%3Cg%3E%3Cpath d='M484.132,124.986l-16.116-16.228c-5.072-5.068-11.82-7.86-19.032-7.86c-7.208,0-13.964,2.792-19.036,7.86l-183.84,183.848 L62.056,108.554c-5.064-5.068-11.82-7.856-19.028-7.856s-13.968,2.788-19.036,7.856l-16.12,16.128 c-10.496,10.488-10.496,27.572,0,38.06l219.136,219.924c5.064,5.064,11.812,8.632,19.084,8.632h0.084 c7.212,0,13.96-3.572,19.024-8.632l218.932-219.328c5.072-5.064,7.856-12.016,7.864-19.224 C491.996,136.902,489.204,130.046,484.132,124.986z'/%3E%3C/g%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3C/svg%3E%0A");
                width: 48%;
                height: 40px !important;
                padding: 0 10px !important;
                background-color: #fafafa !important;
                border: 1px solid #ddd !important;
                color: #000;
                font-size: 15px;
                margin: 0 0 10px;
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
                &[disabled="disabled"] {
                    background-color: #ddd !important;
                    border-color: #c4c4c4;
                    cursor: not-allowed;
                } /* disabled */
                @media (min-width: 320px) and (max-width: 767px) {
                    width: 100%;
                } /* Mobile */
            } /* select */
        } /* choose_category */
        .select_category_target {
            text-align: center;
            font-size: 15px;
            min-height: 150px;
            display: flex;
            justify-content: center;
            align-items: center;
            color: #555555;
        } /* select_category_target */
        .table_of_services {
            overflow-x: auto;
            max-width: 100%;
            margin: 5px auto 10px;
            max-height: 377px;
            scrollbar-width: thin;
            scrollbar-color: #ccc #f5f5f5;
            &::-webkit-scrollbar {width: 6px;}
            &::-webkit-scrollbar-track {background: #f5f5f5;}
            &::-webkit-scrollbar-thumb {background: #ccc;}
            &::-webkit-scrollbar-thumb:window-inactive {background: #f5f5f5;}
            table {
                width: 100%;
                th {
                    background: #4a5568;
                    text-align: center;
                    color: #ffffff;
                    font-weight: normal;
                    font-size: 15px;
                    padding: 10px 5px;
                    border: 1px solid #5E697C;
                    vertical-align: middle;
                    white-space: nowrap;
                    &:first-child {text-align: right;}
                } /* th */
                td {
                    background: #ffffff;
                    border: 1px solid #dddddd;
                    text-align: center;
                    vertical-align: middle;
                    padding: 5px;
                    font-size: 15px;
                    color: #000;
                    &:first-child {text-align: right;}
                    input[type="number"] {
                        min-width: 90px;
                        border: 1px solid #ddd !important;
                        background: #fafafa;
                        text-align: center;
                        height: 40px;
                        font-size: 18px;
                        width: 100px;
                        max-width: 100%;
                        color: #000;
                    } /* number */
                    .checkbox {
                        p {
                            display: inline-block;
                            margin: 0 0 0 5px;
                        } /* p */
                        .switch {
                            position: relative;
                            display: inline-block;
                            width: 60px;
                            height: 26px;
                            margin: 5px auto;
                            input {
                                opacity: 0;
                                width: 0;
                                height: 0;
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
                                &::before {
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
                                    &::before {border-radius: 50%;}
                                } /* round */
                            } /* slider */
                            input:checked + .slider {
                                background-color: #21b978;
                            }
                            input:checked + .slider::before {
                                -webkit-transform: translateX(33px);
                                transform: translateX(33px);
                            }
                        } /* switch */
                    } /* checkbox */
                    button.delete_button {
                        background-image: url("data:image/svg+xml,%3C%3Fxml version='1.0'%3F%3E%3Csvg xmlns='http://www.w3.org/2000/svg' height='512px' viewBox='-40 0 427 427.00131' width='512px'%3E%3Cg%3E%3Cpath d='m232.398438 154.703125c-5.523438 0-10 4.476563-10 10v189c0 5.519531 4.476562 10 10 10 5.523437 0 10-4.480469 10-10v-189c0-5.523437-4.476563-10-10-10zm0 0' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%23C12020'/%3E%3Cpath d='m114.398438 154.703125c-5.523438 0-10 4.476563-10 10v189c0 5.519531 4.476562 10 10 10 5.523437 0 10-4.480469 10-10v-189c0-5.523437-4.476563-10-10-10zm0 0' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%23C12020'/%3E%3Cpath d='m28.398438 127.121094v246.378906c0 14.5625 5.339843 28.238281 14.667968 38.050781 9.285156 9.839844 22.207032 15.425781 35.730469 15.449219h189.203125c13.527344-.023438 26.449219-5.609375 35.730469-15.449219 9.328125-9.8125 14.667969-23.488281 14.667969-38.050781v-246.378906c18.542968-4.921875 30.558593-22.835938 28.078124-41.863282-2.484374-19.023437-18.691406-33.253906-37.878906-33.257812h-51.199218v-12.5c.058593-10.511719-4.097657-20.605469-11.539063-28.03125-7.441406-7.421875-17.550781-11.5546875-28.0625-11.46875h-88.796875c-10.511719-.0859375-20.621094 4.046875-28.0625 11.46875-7.441406 7.425781-11.597656 17.519531-11.539062 28.03125v12.5h-51.199219c-19.1875.003906-35.394531 14.234375-37.878907 33.257812-2.480468 19.027344 9.535157 36.941407 28.078126 41.863282zm239.601562 279.878906h-189.203125c-17.097656 0-30.398437-14.6875-30.398437-33.5v-245.5h250v245.5c0 18.8125-13.300782 33.5-30.398438 33.5zm-158.601562-367.5c-.066407-5.207031 1.980468-10.21875 5.675781-13.894531 3.691406-3.675781 8.714843-5.695313 13.925781-5.605469h88.796875c5.210937-.089844 10.234375 1.929688 13.925781 5.605469 3.695313 3.671875 5.742188 8.6875 5.675782 13.894531v12.5h-128zm-71.199219 32.5h270.398437c9.941406 0 18 8.058594 18 18s-8.058594 18-18 18h-270.398437c-9.941407 0-18-8.058594-18-18s8.058593-18 18-18zm0 0' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%23C12020'/%3E%3Cpath d='m173.398438 154.703125c-5.523438 0-10 4.476563-10 10v189c0 5.519531 4.476562 10 10 10 5.523437 0 10-4.480469 10-10v-189c0-5.523437-4.476563-10-10-10zm0 0' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%23C12020'/%3E%3C/g%3E%3C/svg%3E%0A");
                        background-repeat: no-repeat;
                        background-position: center center;
                        background-size: 20px;
                        width: 30px;
                        height: 30px;
                    } /* delete_button */
                } /* td */
            } /* table */
        } /* table_of_services */
        button.add_service_button {
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
            -webkit-transition: all 0.2s ease-in-out;
            -moz-transition: all 0.2s ease-in-out;
            -o-transition: all 0.2s ease-in-out;
            transition: all 0.2s ease-in-out;
            &:hover {
                background: #0071C9;
                border-color: #0071C9;
            } /* hover */
        } /* add_service_button */
    } /* add_service_modal */
    .more_services_modal {
        .sweet-content {
            display: block !important;
            max-height: 500px;
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: #ccc #f5f5f5;
            &::-webkit-scrollbar {width: 6px;}
            &::-webkit-scrollbar-track {background: #f5f5f5;}
            &::-webkit-scrollbar-thumb {background: #ccc;}
            &::-webkit-scrollbar-thumb:window-inactive {background: #f5f5f5;}
        } /* sweet-content */
        .service_item {
            border: 1px solid #ddd;
            margin: 0 auto 10px;
            border-radius: 5px;
            padding: 5px;
            background: #fdfdfd;
            cursor: pointer;
            -webkit-transition: all 0.2s ease-in-out;
            -moz-transition: all 0.2s ease-in-out;
            -o-transition: all 0.2s ease-in-out;
            transition: all 0.2s ease-in-out;
            .top_row {
                display: flex;
                justify-content: space-between;
                align-items: center;
            } /* top_row */
            .bottom_row {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin: 5px auto 0;
            } /* bottom_row */
            span {
                display: block;
                font-size: 15px;
                color: #666666;
                white-space: nowrap;
                p {
                    display: inline-block;
                    color: #000000;
                } /* p */
            } /* span */
            time {
                display: flex;
                justify-content: flex-end;
                width: 100%;
                font-size: 13px;
                color: #777777;
            } /* time */
            &:hover {
                background: #f8f8f8;
                border-color: #d8d8d8;
            } /* hover */
        } /* service_item */
    } /* more_services_modal */

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
    .d-inline-flex{
        display: inline-flex !important;
    }
</style>
