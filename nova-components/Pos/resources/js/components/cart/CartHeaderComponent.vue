<template>
    <div class="top_row">
        <span>{{__('Order')}}</span>
        <p class="cursor-pointer" @click="selectUnitModal">
            <label class="cursor-pointer" v-if="!selected_reservation">{{__('Select Customer')}}</label>
            <label class="cursor-pointer" v-else>{{__('The customer')}} : {{selected_reservation.customer.name}}</label>
        </p>
        <sweet-modal  :enable-mobile-fullscreen="false" :pulse-on-block="false" :title="__('Select Unit')" overlay-theme="dark" ref="unitsModal" class="UnitsModal">
            <div class="serach_units">
                <input type="text"  @input="filterReservations($event)" v-model="reservations_search_query" :placeholder="__('search by unit name / number or customer name / facility name')" autofocus="">
            </div><!-- serach_units -->

            <div class="relative" style="min-height: 100px;">
                <!-- Loader -->
                <loading :active.sync="isLoading"
                         :can-cancel="true"
                         :loader="'spinner'"
                         :color="'#7e7d7f'"
                         :is-full-page="false">
                </loading>
                <div class="all_units_services" v-if="reservations.length">

                    <label v-for="(reservation , i) in reservations" :for="reservation.id" :key="i">
                        <input type="radio" :id="reservation.id" v-bind:checked="reservation.id == checked_id" :name="'unit_item_' + reservation.id" :value="reservation.id" @click="selectCurrent(reservation)" >
                        <div class="unit_info">
                            <div class="col">
                                <div class="col_inside">
                                    <div class="checkbox"></div>
                                    <b>#{{reservation.unit ? reservation.unit.number : ''}}</b>
                                    <span>{{reservation.unit ? reservation.unit.name : ''}}</span>
                                </div><!-- col_inside -->
                            </div><!-- col -->
                            <p v-if="reservation.customer && reservation.reservation_type == 'single'">{{reservation.customer.name}}</p>
                            <p v-else>{{reservation.company.name}}</p>
                        </div><!-- unit_deti -->
                    </label>
                </div><!-- all_units_services -->
                <div v-else>
                    <div class="no_units_to_show">
                        <div class="icon"></div>
                        <span> {{__('No occupied units found to select from')}}</span>
                    </div><!-- no_services_show -->
                </div>
            </div>


<!--            <button @click="selectUnitAndClose()" class="Select_Unit">{{__('Select')}}</button>-->
        </sweet-modal>

        <!--    <button class="cursor-pointer" @click="selectPaymentModal">Open Modal</button>-->


  </div><!-- end top_row -->
</template>

<script>

    import Loading from 'vue-loading-overlay';
    import 'vue-loading-overlay/dist/vue-loading.css';
    export default {
        name: "CartHeaderComponent",
        components: {Loading},
        data(){
            return {
                isLoading: false,
                customer : null ,
                reservations : [],
                selected_reservation : null,
                reservations_search_query : null,
                hasClickedSelectBtn : false,
                checked_id : null
            }
        },
        methods:{

            getOccupiedUnits(){
                this.isLoading = true;
                axios.get('/nova-vendor/pos/get-occupied-units')
                    .then(response => {
                        this.reservations = response.data;
                        this.isLoading = false;
                    });
            },
           async selectUnitModal(){
              // this.selected_reservation = null;
              this.reservations_search_query = null;
              await  this.getOccupiedUnits();
              this.$refs.unitsModal.open();
            },
            selectCurrent(reservation){
                this.selected_reservation = reservation ;
                this.checked_id = reservation.id;
                Nova.$emit('current_reservation' , this.selected_reservation);
                this.$refs.unitsModal.close();
            },
            selectPaymentModal(){
              this.$refs.paymentModal.open();
            },
            filterReservations(event){

                if(event.inputType === 'deleteContentBackward'){
                    this.getOccupiedUnits();
                    this.isLoading = true;
                }

                this.isLoading = true;
                let self = this;
                _.debounce(() => {

                    self.reservations = self.reservations.filter(function(obj){
                        if (obj.reservation_type == 'group'){
                            obj.customer.name = obj.company.name;
                        }
                       let unit_number = String(obj.unit.number);
                       return unit_number.indexOf(self.reservations_search_query) > -1 ||
                              obj.unit.name.indexOf(self.reservations_search_query) > -1 ||
                              obj.customer.name.indexOf(self.reservations_search_query) > -1  ;
                    });
                    self.isLoading = false;
                }, 500)();


            }
        },
        mounted(){
            Nova.$on('process-aborted' , () => {
                this.selected_reservation = null;
                this.checked_id = null;
                this.getOccupiedUnits();
            });

            Nova.$on('empty-cart' , () => {
                this.selected_reservation = null;
                this.checked_id = null;
            });
        }
    }
</script>

<style scoped>

</style>
