<template>

    <div class="relative">
        <loading
            :active="oRLoading"
            :loader="'spinner'"
            :color="'#7e7d7f'"
            :opacity="0.7"
            :is-full-page="false"
        >
        </loading>
        <div class="title">
            <span>{{__('Awaiting Confirmation Reservations')}} ({{oRPaginator ? oRPaginator.total : 0}})</span>
            <div class="icon"></div>
        </div><!-- title -->
        <div class="content_area">

            <!-- Show only if there are no reservations -->
            <div class="no_data_show" v-if="!oRCollection.length">
                <div class="icon"></div>
                <span>{{__('There are no reservations awaiting confirmation')}}</span>
            </div><!-- no_data_show -->
            <!-- Show only if there are no reservations -->

            <div v-if="oRCollection.length">
                <div v-for="(oReservation , i) in oRCollection" :key="i" class="block">
                    <div class="txt" @click="gotoReservation(oReservation.rid)">
<!--                        <span>{{__('Reservation From Website in')}} {{oReservation.orcat}}</span>-->
                        <ul>
                            <li><p>{{__('Unit')}} :</p><span>{{oReservation.unum}}</span></li>
                            <li><p>{{__('From')}} :</p><span>{{__formatDateWithHumanDate(oReservation.rdi)}}</span></li>
                            <li><p>{{__('Name')}} :</p><span>{{oReservation.cname}}</span></li>
                            <li><p>{{__('Email address')}} :</p><span>{{oReservation.cemail}}</span></li>
                            <li><p>{{__('Duration')}} :</p><span class="days">{{nights(oReservation.rdo , oReservation.rdi)}} {{__('Night')}}</span></li>
                            <li><p>{{__('To')}} :</p><span>{{__formatDateWithHumanDate(oReservation.rdo)}}</span></li>
                            <li><p>{{__('Phone Number')}} :</p><span class="phone">{{oReservation.cphone}}</span></li>
                            <li><p>{{__('Reservation Cost')}} :</p><span class="price">{{oReservation.rtotalprice}} {{__(currency)}}</span></li>
                        </ul>
                    </div><!-- txt -->
                    <div class="buttons_area">
<!--                        <button type="button" class="confirmation" @click="confirm(oReservation.orid , oReservation.ordi , oReservation.ordo , oReservation.uid )">{{__('Confirm Reservation')}}</button>-->
<!--                        <button type="button" class="cancellation" @click="cancel(oReservation.orid , oReservation.uid)">{{__('Cancel Reservation but')}}</button>-->
                    </div><!-- buttons_area -->
                </div><!-- block -->
            </div>
            <div class="w-full flex flex-wrap mb-4 mt-3 justify-center" v-if="oRCollection.length && oRPaginator.total > 10">
                <pagination
                    :page-count="oRPaginator.last_page"
                    :page-range="3"
                    :margin-pages="2"
                    :click-handler="getReservations"
                    :prev-text="'<'"
                    :next-text="'>'"
                    :container-class="'pagination w-full flex justify-center'"
                    :page-class="'page-item'"
                    :page-link-class="'page-link'"
                    :prev-link-class="'page-link'"
                    :next-link-class="'page-link'"
                    :prev-class="'page-item'"
                    :next-class="'page-item'"
                    :first-last-button="true"
                    :first-button-text="'<<'"
                    :last-button-text="'>>'"
                >
                </pagination>
            </div><!-- End Pagination -->




        </div><!-- content_area -->
    </div>

</template>

<script>
    import Loading from 'vue-loading-overlay';
    import 'vue-loading-overlay/dist/vue-loading.css';

    export default {
        name: "panel-awaiting-confirmation-reservations",
        components : {
            Loading
        },
        props: ['payment_preprocessor'],
        data(){
            return {
                oRCollection : [],
                oRPaginator : null,
                oRLoading : false,
                unit_reservation : {},
                locale : null,
                currency :Nova.app.currentTeam.currency,


            }
        },
        methods : {
            nights(date_out , date_in){
                const start = new Date(date_in)
                const end = new Date(date_out)
                let dayCount = 0

                while (end > start) {
                    dayCount++
                    start.setDate(start.getDate() + 1)
                }
                return dayCount
            },
            getReservations(dPagi=1){
                this.oRLoading = true;
                axios.get(`/nova-vendor/DashboardUnits/awaiting-reservations?page=${dPagi}&payment_preprocessor=${this.payment_preprocessor}`)
                    .then(res => {

                        this.oRCollection = res.data.data;

                        this.oRPaginator = {
                            currentPage : res.data.current_page ,
                            lastPage : res.data.last_page ,
                            from : res.data.from,
                            to : res.data.to,
                            total : res.data.total,
                            pathPage : res.data.path + '?page=',
                            firstPageUrl : res.data.first_page_url ,
                            lastPageUrl : res.data.last_page_url ,
                            nextPageUrl : res.data.next_page_url ,
                            prevPageUrl : res.data.prev_page_url ,
                        };
                        this.oRLoading = false;

                        Nova.$emit('count-online-reservations' , this.oRPaginator.total);
                    })
            },
            gotoReservation(id){
                this.$router.replace({
                    name: 'reservation',
                    params: {id: id}
                })
            },
            cancel(id , unit_id) {
                this.oRLoading = true;
                Nova.request().post('/nova-vendor/calender/cancel-online', {
                    reservation_id: id
                })
                    .then(response => {
                        this.$toasted.show(this.__('Online reservation has been canceled successfully !'), {type: 'success'});
                        this.oRLoading = false;
                        Nova.$emit('online-reservation-canceled' , unit_id)
                        this.getReservations();
                    });
            },
            confirm(id , date_in , date_out , unit_id) {
                this.getUnitTotal(id , date_in , date_out , unit_id);
            },
            getUnitTotal(id , date_in , date_out , unit_id) {
                this.oRLoading = true;
                let start = moment(String(date_in)).format('YYYY-MM-DD');
                let end = moment(String(date_out)).format('YYYY-MM-DD');
                axios
                    .get('/nova-vendor/calender/unit/' + unit_id + '/' + start + '/' + end)
                    .then(response => {

                        this.unit_reservation = response.data;
                        this.oRLoading = true;
                        Nova.request().post('/nova-vendor/calender/confirm-online', {
                            reservation_id: id,
                            unit_reservation: this.unit_reservation
                        })
                            .then(res => {
                                this.$toasted.show(this.__('Reservation created successfully !'), {type: 'success'});
                                this.$router.push({name: 'reservation', params: {
                                        id: res.data.reservation.reservation_id
                                    }
                                });

                            });
                    });
            },
        },
        created() {
            this.locale = Nova.config.local;
            Nova.$on('call-awaiting-confirmation-reservations-query' , () => {
                this.getReservations();
            })
        },
        beforeDestroy(){
            Nova.$off('call-awaiting-confirmation-reservations-query')
        }
    }
</script>

<style scoped>

</style>
