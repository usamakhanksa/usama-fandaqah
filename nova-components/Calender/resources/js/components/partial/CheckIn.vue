<template>
    <div class="item_reservation_button">

        <button v-permission="'check-in customer'" class="main_button"  v-if="!quick" @click="open">{{__('Check In')}}</button>
        <sweet-modal :enable-mobile-fullscreen="false"  :pulse-on-block="false" :title="__('Check In')" overlay-theme="dark" ref="modal" class="relative">

            <!-- Loader -->
            <loading :active.sync="loader"
                     :can-cancel="true"
                     :loader="'spinner'"
                     :color="'#7e7d7f'"
                     :is-full-page="false">
            </loading>
            <div class="flex flex-wrap overflow-hidden">
                <div class="w-full justify-center items-center">
                    <div class="input-group p-1">
                        <label class="text-90">{{__('Check-in time')}} </label>
                        <input type="time" v-model="time"
                                :disabled="!canEditCheckinCheckoutTime"
                               class="appearance-none border rounded w-full py-2 px-3 border-gray-300 leading-tight focus:outline-none focus:shadow-outline"
                               :placeholder="__('Name')"/>
                    </div>
                </div>

            </div>
            <button  class="shadow mb-4  btn btn-block btn-primary mt-2" @click="send">{{__('Check in customer')}}</button>

        </sweet-modal>

        <sweet-modal :enable-mobile-fullscreen="false" :pulse-on-block="false" :title="__(Error)" overlay-theme="dark" ref="errorModal" class="relative">

            <loading :active.sync="loader"
                     :can-cancel="true"
                     :loader="'spinner'"
                     :color="'#7e7d7f'"
                     :is-full-page="false">
            </loading>
            <div class="flex flex-wrap overflow-hidden pt-10">
                <div class="w-full justify-center items-center">
                        <p>{{ errorMessage }}</p>

                </div>

            </div>

                <button v-permission="'change to available'" class="shadow mb-4  btn btn-block btn-primary mt-2" @click="changeUnitStatus">{{ __('Change Status to Available') }}</button>
        </sweet-modal>

    </div>
</template>

<script>
    import Loading from 'vue-loading-overlay';
    import 'vue-loading-overlay/dist/vue-loading.css';
    import momenttimezone from 'moment-timezone'
    export default {
        name: "Guests",
        props: ['reservation','quick'],
        components : {
            Loading
        },
        data: () => {
            return {
                loading: null,
                time: momenttimezone().tz("Asia/Riyadh").format('HH:mm'),
                quickReservation : null,
                quickModal : {
                    from : null ,
                    modal : null
                },
                loader : false,
                prReservation : null,
                errorMessage: '',
                hasPermissionToCheckinDebtorCustomer : true,
                total_balance : null,
                canEditCheckinCheckoutTime : true,
                locale : Nova.config.local
            }
        },
        methods: {

            async open() {
                    const fetchBalance = await axios.get(`/nova-vendor/calender/reservation-balance?id=${this.reservation.id}`)
                    this.total_balance = fetchBalance.data.total_balance;


                    if(this.total_balance < 0 && !this.hasPermissionToCheckinDebtorCustomer){
                        if(fetchBalance.data.reservation_type == 'group'){
                            this.$toasted.show(this.__('Can not check-in , please fulfill group reservation balance'), {type: 'error'});
                        }else{
                            this.$toasted.show(this.__('Can not check-in customer cause customer is debtor'), {type: 'error'});
                        }
                        return;
                    }
                    this.loading = false;
                    this.loader = false;
                    this.time =  momenttimezone().tz("Asia/Riyadh").format('HH:mm')
                    this.quickModal.from = this.$refs.from;
                    this.quickModal.modal = this.$refs.modal;

                    if(this.quick){
                        if(this.reservation.id === this.quickReservation.id){
                            this.quickModal.modal.open();
                        }
                    }else{
                        axios.get(`/nova-vendor/calender/check-current-chekedin-reservation?id=${this.reservation.id}&unit_id=${this.reservation.unit_id}`)
                            .then((res) => {

                                if(!_.isEmpty(res.data)){
                                    this.$toasted.show(this.__('Sorry , You can not check-in a customer becuse its has a customer in it for reservation #')+res.data.number, {type: 'info'});
                                }else{

                                    if(this.reservation.unit.status != 1){
                                        this.errorMessage = this.__('We can not perform the check-in because unit :name - number :number is under :status' , {name : this.reservation.unit.name[this.locale] ,number : this.reservation.unit.unit_number , status : this.reservation.unit.status ==2 ? this.__('Under Cleaning') : this.__('Under Maintenance')});
                                        this.$refs.errorModal.open();
                                        return;
                                    }
                                    this.quickModal.modal.open();
                                }
                            })

                    }
                    // this.$refs.modal.open()

            },
            async changeUnitStatus() {
                try {
                    const response = await axios.post(`/nova-vendor/calender/change-unit-status`, {
                        unit_id: this.reservation.unit.id,
                    });
                    this.$toasted.show(this.__('Unit status updated successfully'), { type: 'success' });
                    this.$refs.errorModal.close();
                    this.reservation.unit.status = 1;
                    this.quickModal.modal.open();
                    // this.open(); // Retry opening the check-in modal
                } catch (error) {
                    this.$toasted.show(this.__('Failed to update unit status'), { type: 'error' });
                }
            },
            send() {

                this.loading = true;
                this.loader = true;
                    axios
                        .post('/nova-vendor/calender/reservation/checks', {
                            id: this.reservation.id,
                            time: this.time,
                            type: 'check-in',
                            dateIn : this.reservation.date_in ,
                            dateOut : this.reservation.date_out,
                        })
                        .then(response => {

                            if(response.data.status == 'empty_time'){
                                this.loading = false;
                                this.loader = false;
                                this.$toasted.show(this.__('Please fill check in time'), {type: 'error'})
                                return false;
                            }else if(response.data.status === 'can_not_check_in'){
                                this.loading = false;
                                this.loader = false;
                                this.quickModal.modal.close();
                                this.$toasted.show(this.__('Check-in outside booking dates is not possible'), {type: 'error'})
                                return false;
                            }else if(response.data.status === 'missing_id_number'){
                                this.loading = false;
                                this.loader = false;
                                this.quickModal.modal.close();
                                this.$toasted.show(this.__('Check in is forbidden unless to add customer id number'), {type: 'error'})
                                return false;
                            }else{
                                this.$emit('update-reservation')
                                if(this.quick){
                                    if(this.reservation.id === this.quickReservation.id){
                                        this.quickModal.modal.close();
                                    }
                                }else{
                                    this.$refs.modal.close();
                                }
                                this.$toasted.show(this.__('Customer checked in successfully'), {type: 'success'});
                                this.loading = false;
                                this.loader = false;
                            }


                        }).catch(err => {
                        this.loading = false;
                        this.$toasted.show(this.__(err), {type: 'error'})
                    })


            },
            validation() {
                let day_start = this.reservation.day_start, day_end = this.reservation.day_end;
                var startDate = moment(String(this.reservation.date_in +' '+ day_start));
                var endDate   = moment(String(this.reservation.date_out +' '+ day_end));
                var date  = moment(String(momenttimezone().tz("Asia/Riyadh").format("Y-MM-DD HH:mm")));


                // if(Date.parse(momenttimezone().tz("Asia/Riyadh").format("YYYY-MM-DD HH:mm")) >= Date.parse(startDate) && Date.parse(momenttimezone().tz("Asia/Riyadh").format("YYYY-MM-DD HH:mm")) <= Date.parse(endDate)){
                //     return true;
                // }else{
                //
                //     this.$toasted.show(this.__('Check-in outside booking dates is not possible'), {type: 'error'})
                //     this.quickModal.modal.close();
                //     return false;
                // }
                // if(!date.isBetween(startDate, endDate)) {
                //     this.$toasted.show(this.__('Check-in outside booking dates is not possible'), {type: 'error'})
                //     this.quickModal.modal.close();
                //     return false;
                // }
                if (!this.time ) {
                    this.$toasted.show(this.__('Please fill check in time'), {type: 'error'})
                    return false;
                }
                return true;
            },

        },
        mounted(){
            this.hasPermissionToCheckinDebtorCustomer = Nova.app.$hasPermission('checkin debtor customer');
            this.canEditCheckinCheckoutTime = Nova.app.$hasPermission('edit checkin and checkout time');
            Nova.$on('quick-open-checked-in-modal' , (reservation) => {
                if(this.reservation.id === reservation.id){
                    this.quickReservation = reservation ;
                    this.open();
                }
            })
        }
    }
</script>

<style scoped>
    .btn-md {
        padding: 0px 10px;
    }
</style>
