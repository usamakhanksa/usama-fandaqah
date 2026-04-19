<template>
    <div class="reservation_details relative">
         <loading :active.sync="isLoadingMonthlyYearly"
             :can-cancel="true"
             :is-full-page="false"
             :opacity="0.9"
        >
        </loading>
        <div class="rent_type">
            <div class="title">{{__('Rent Type')}} :</div>
            <div class="radios_area">
                <label v-show="showDaily" class="custom_radio" for="daily" v-if="!main_reservation || (main_reservation && main_reservation.rent_type == 1)">
                    <input type="radio" id="daily" value="1" v-model="rent_type" @change="updateDate">
                    <span class="checkmark"></span>
                    <p>{{__('Daily')}}</p>
                </label><!-- custom_radio -->
                <!-- <label class="custom_radio" for="monthly" v-if="unit && unit.prices.month && (!main_reservation || (main_reservation && main_reservation.rent_type == 2))"> -->
                <label class="custom_radio" for="monthly">
                    <input type="radio" id="monthly" value="2" v-model="rent_type" @change="updateDate">
                    <span class="checkmark"></span>
                    <p>{{__('Monthly')}}</p>
                </label><!-- custom_radio -->

                <button ref="changeRentBtn" v-show="false" @click="changeRentType">Change to Monthly</button>
            </div><!-- radios_area -->
        </div><!-- rent_type -->
        <div  class="input_group">
            <label>{{__('The Unit')}} :</label>
            <select v-model="unit_id">
                <option disabled selected value="">{{__('Select Unit')}}</option>
                <option :value="unit.id" v-for="(unit, index) in units" :key="index" :selected="room_id(unit.id)">
                    #{{ unit.unit_number }} - {{ unit.name }}
                </option>
            </select>
        </div><!-- input_group -->
        <div  class="date_range">
            <label>{{__('Select date range')}} :</label>
            <div class="date_picker_area">
                <vcc-date-picker
                    class='v-date-picker'
                    :locale="vcc_local"
                    mode='range'
                    v-model='selectedDate'
                    show-caps
                    is-expanded
                    :columns="$screens({ default: 1, lg: 2 })"
                    :popoverExpanded="true"
                    :disabled-dates='disableDates'
                    :min-date='minDate'
                    @input="dateChanged"
                    :popover="{ placement: 'bottom', visibility: 'click' }"
                    ref="vcalendar"
                    :input-props='{readonly: true}'
                >
                </vcc-date-picker>
            </div><!-- date_picker_area -->
            <div class="date_picker_alert" role="alert" v-if="showYesterdayNotification">
                <span>{{__('Warning')}}</span>
                <p>{{__('The check-in time is not due for the new day, this reservation will be added to the previous day, or you can adjust the date for the desired day')}}</p>
            </div><!-- date_picker_alert -->
        </div><!-- date_range -->
        <div class="sourceId">
            <label>{{__('Source')}} :</label>
            <select v-model="sourceId">
                <option :value="source.id" :disabled="!source.status" v-for="(source, index) in sources" :key="index">{{source.name}}</option>
            </select>
        </div><!-- sourceId -->
        <div class="date_range border-bottom-none margin-top-10" v-if="sourceId != receptionSourceId">
             <label>{{__('Source number')}} :</label>
             <div class="date_picker_area">
             <input style="cursor:text" type="text" v-model="sourceNum" :placeholder="__('Source number')">
             </div>
        </div>
        <div class="loader_item" v-if="loading">
            <loader />
        </div><!-- loader_item -->
        <ul v-if="unit && !loading">
            <li>
                <div class="name">{{__('Unit')}} :</div>
                <div class="desc">#{{ unit.unit_number }} - {{ unit.name }}</div>
            </li>
            <li>
                <div class="name">{{__('Reservation Date')}} :</div>
                <div class="reservation_dates">
                    <div class="date_area">
                        <span>{{__('From')}} : {{ formatDate(this.selectedDate.start) }}</span>
                        <span>{{__('To')}} : {{ formatDate(this.selectedDate.end) }}</span>
                    </div><!-- date_area -->
                    <div v-if="rent_type == '2'">
                        <div class="counts_area">
                            <div class="rent_type_month">
                                <div class="block_one">
                                    <div class="number_input">
                                        <button @click="monthCountStepDown" :disabled="currentSubscription == 'monthly-yearly' ? month_count <= 1 : month_count <= 0">
                                            <i class="fa fa-minus" aria-hidden="true"></i>
                                        </button>
                                        <input v-model="month_count" class="quantity" min="0" name="quantity" type="number" @change="updateDate">
                                        <button @click="monthCountStepUp" class="plus">
                                            <i class="fa fa-plus" aria-hidden="true"></i>
                                        </button>
                                    </div><!-- number_input -->
                                    <span>{{ __('month')}}</span>
                                </div><!-- block_one -->
                                <div class="block_one">
                                    <div class="number_input">
                                        <button @click="nightCountStepDown" :disabled="night_count <= 0">
                                            <i class="fa fa-minus" aria-hidden="true"></i>
                                        </button>
                                        <input class="quantity" v-model="night_count" min="0" name="quantity" type="number" @change="updateDate">
                                        <button @click="nightCountStepUp" class="plus">
                                            <i class="fa fa-plus" aria-hidden="true"></i>
                                        </button>
                                    </div><!-- number_input -->
                                    <span>{{ __('night')}} </span>
                                </div><!-- block_one -->
                            </div>
                        </div>
                    </div>
                </div><!-- reservation_dates -->
            </li>
            <li v-if="rent_type == '2'">
                <div class="name">{{__('Nights')}} :</div>
                <div class="night_count">{{ unit.reservation.nights }} {{__('Night')}}</div>
            </li>
            <li v-else>
                <div class="name">{{__('Nights')}} :</div>
                <div class="reservation_dates">
                    <div class="rent_type_day">
                        <div class="block_one">
                            <div class="number_input">
                                <button @click="nightCountStepDown" :disabled="night_count <= 0">
                                    <i class="fa fa-minus" aria-hidden="true"></i>
                                </button>
                                <input class="quantity" v-model="night_count" min="0" name="quantity" type="number" @change="nightsChanged">
                                <button @click="nightCountStepUp" class="plus" :disabled="disable_plus">
                                    <i class="fa fa-plus" aria-hidden="true"></i>
                                </button>
                            </div><!-- number_input -->
                            <span>{{ __('night')}} </span>
                        </div><!-- block_one -->
                    </div><!-- rent_type_day -->
                </div><!-- reservation_dates -->
            </li>
            <li>
                <div class="name">{{__('Reservation Cost')}} :</div>
                <!--        <div class="total_price">{{ unit.reservation.prices.total_price }} {{unit.reservation.prices.currency }}</div>-->
                <!--        <div class="total_price">{{ totalPriceRaw }} {{unit.reservation.prices.currency }}</div>-->

                <span v-if="customUnit">{{parseFloat(customUnit.reservation.prices.total_price_raw).toFixed(2)}}</span>

            </li>
        </ul>
    </div><!-- reservation_details -->
</template>

<script>
    import momenttimezone from 'moment-timezone'
    import moment from "moment";
    import Loading from 'vue-loading-overlay';
    // Import stylesheet
    import 'vue-loading-overlay/dist/vue-loading.css';
    export default {
        name: "reservation-details",
        props: ['units' , 'customUnit','calendar_changed_from_unit_housing'],
        components: {
            Loading
        },
        data: () => {
            return {
                selectedDate:{},
                sources: [],
                sourceId: null,
                sourceNum : null,
                unit: null,
                unit_id: null,
                rent_type: '1',
                disableDates:[],
                month_count: 0,
                night_count: 1,
                old_night_count: 0,
                old_month_count: 0,
                calender_changed: 0,
                vcc_local: {
                    id: Nova.config.local,
                    firstDayOfWeek: 1,
                    masks: {
                        weekdays: 'WWW',
                        input: ['WWWW YYYY/MM/DD', 'L'],
                        data: ['WWWW YYYY/MM/DD', 'L'],
                    }
                },
                showYesterdayNotification : false,
                last_reservation_id: null,
                last_reservation_date_in : null,
                settingDayStartTimeFromSettings : null,
                currentTime : null,
                cachedSelectedDate : {},
                receptionSourceId : null,
                main_reservation : null,
                showDaily : true,
                isLoadingMonthlyYearly: false,
                currentSubscription : null,
                server_date : null
            }
        },
        created() {
            this.start_date = this.$route.params.date;
            this.getSources();
        },
        watch: {
            sourceId: function(val) {
                if(this.receptionSourceId == val){
                    this.sourceNum = null;
                }

                this.$parent.source_id = val;
            },
            sourceNum: function(val){
                this.$parent.source_num = val;
            },
            selectedDate: function (val) {

            },
            unit_id: function (newVal) {
                this.last_reservation_date_in = null;
                this.$emit('setunit', newVal);
            },
            unit: function(newVal, oldVal) {
                if(this.rent_type == 2){
                    this.month_count = Math.floor(newVal.reservation.nights/30)
                    this.night_count = newVal.reservation.nights%30
                    if(this.currentSubscription == 'monthly-yearly' && this.month_count < 1){
                        this.month_count = 1; 
                        this.night_count = 0; 
                        this.updateDate();
                    }
                }else{
                    this.month_count = 0
                    this.night_count = newVal.reservation.nights
                }
                this.disableDates = newVal.reservations_date.map(function(x) {
                    if(x.checked_out == null){
                        let start = new Date(x.date_in);
                        let end = new Date(x.date_out);
                        var n = end.getTime();
                        n -= 86400000;
                        return {
                            start: start,
                            end: new Date(n)
                        }
                    }else{
                        return {
                            start: 1,
                            end:  1
                        }
                    }
                });


                if(this.last_reservation_date_in === null){

                    axios.get(`/nova-vendor/calender/getLastReservationId?unit_id=${newVal.id}&date_out=${moment(this.selectedDate.end).format('Y-MM-DD')}&date_in=${moment(this.selectedDate.start).format('Y-MM-DD')}`)
                        .then((res) => {

                            this.last_reservation_id = res.data.id;
                            this.last_reservation_date_in = res.data.date_in;

                            if(this.last_reservation_date_in){

                                let next_date_in = new Date(this.last_reservation_date_in);

                                for( var i = 0; i < this.disableDates.length; i++){
                                    if ( Date.parse(this.disableDates[i].start) === Date.parse(next_date_in) ) {
                                        this.disableDates.splice(i, 1);
                                    }
                                }
                            }

                        })
                }else{

                    let next_date_in = new Date(this.last_reservation_date_in);
                    for( var i = 0; i < this.disableDates.length; i++){
                        if ( Date.parse(this.disableDates[i].start) === Date.parse(next_date_in) ) {
                            this.disableDates.splice(i, 1);
                        }
                    }
                }

                setTimeout(() => {
                       if (Nova.app.currentTeam.last_subscription && Nova.app.currentTeam.last_subscription.stripe_plan == 'monthly-yearly'){
                            this.isLoadingMonthlyYearly = false;
                        }
                }, 2001);
               
            },
            units(val) {
                let unit = val.filter(unit => {
                    return this.room_id(unit.id);
                })
                if (unit[0]) {
                    this.unit = unit[0];
                }else{
                    if (Nova.app.currentTeam.last_subscription && Nova.app.currentTeam.last_subscription.stripe_plan == 'monthly-yearly'){
                        setTimeout(() => {
                                    this.isLoadingMonthlyYearly = false;
                        }, 1000);
                    }
                }
            }
        },
        methods: {
            changeRentType(){
                this.rent_type = 2;
                this.updateDate();
            },
            room_id (room_id) {
                return this.$route.params.room_id == room_id;
            },
            getSources() {
                const self = this;
                axios.get('/apidata/sources')
                    .then(response => {
                        this.sources = response.data.data;
                        this.sourceId = (response.data.data[0].status) ? response.data.data[0].id:null;
                        this.receptionSourceId
                        $.each(response.data.data, function(key, source) {
                            if(!source.deleteable){
                                self.receptionSourceId = source.id;
                            }
                        });
                    }).catch(err => {
                    this.loading = false;
                })
            },
            monthCountStepDown(){
                if(this.month_count > 0){
                    this.old_month_count = this.month_count
                    this.month_count--;
                    this.calender_changed++
                    this.updateDate()
                }
            },
            monthCountStepUp(){
                this.old_month_count = this.month_count
                this.month_count++;
                this.calender_changed++
                this.updateDate()
            },
            nightCountStepDown(){
                if(this.night_count > 0){
                    this.old_night_count = this.night_count
                    this.night_count--;
                    this.calender_changed++
                    this.updateDate()
                }
            },
            nightCountStepUp(){
                this.old_night_count = this.night_count
                this.night_count++;
                this.calender_changed++
                this.updateDate()
            },
            nightsChanged(){
                this.old_night_count = this.night_count
                this.calender_changed++
                this.updateDate()
            },
            updateDate(){
                if(this.currentSubscription == 'monthly-yearly' && this.month_count <= 0) {
                    this.month_count = 1;
                }
                let new_date = new Date(this.selectedDate.start),
                    nights = 0
                ;

                if(this.rent_type == 2){
                    if(this.calender_changed == 1){
                        nights = 30;
                    }else{
                        nights = (this.month_count *30) + Number(this.night_count);
                    }
                } else{
                    nights = (this.month_count *30) + Number(this.night_count);
                }

                let end_data = new Date(new_date.setDate(new_date.getDate() + nights));

                if(!this.dateCheck(end_data, this.selectedDate.start)){
                    this.selectedDate = {
                        start: this.selectedDate.start,
                        end: end_data
                    };
                    this.$emit('setrange', [this.selectedDate, this.rent_type]);
                }else{
                    this.night_count = this.old_night_count
                    this.month_count = this.old_month_count
                    this.$toasted.show(this.__('This unit has a reservation that intersect with selected dates !'), {type: 'error'})
                }
            },
            formatDate(date) {
                return Nova.app.__formatDateWithHumanDate(date);
            },
            dateCheck(end, start) {
                var ccc = false;
                this.disableDates.forEach(function(element) {
                    if((start <= element.end) && (element.start <= end)) {
                        ccc = true;
                    }
                });

                return ccc;
            },
            dateChanged(check) {
                
                // alert('am changed');
                // console.log(check);

                if(this.selectedDate.start.getTime() == this.selectedDate.end.getTime()) {
                    let date = this.selectedDate;
                    this.selectedDate = {
                        start: new Date(moment(String(this.$route.params.date)).toISOString()),
                        end: new Date(moment(String(this.$route.params.date)).add('1', 'days').toISOString())
                    };
                    return;
                }

                let startDate = new Date(this.selectedDate.start);
                let today = new Date(this.server_date).setHours(0,0,0,0);

                var target_date = moment(startDate);
                var comparing_date = moment(today);
                
                var diff_in_days =  comparing_date.diff(target_date, 'days');

                if((startDate < today && diff_in_days > 1) && !Nova.app.$hasPermission('booking past')){
                    Nova.$emit('disable-reservation-button-book-in-past');
                    this.$toasted.show(this.__('You can not book in the past'), {type: 'error'});
                    return;
                }else{
                    Nova.$emit('enable-reservation-button-book-in-past');
                }
                if(startDate < today && !Nova.app.$hasPermission('booking past') && !this.showYesterdayNotification){
                        this.$toasted.show(this.__('You can not book in the past'), {type: 'error'})
                        let new_date = new Date(this.selectedDate.start),  nights = 0;
                        if(this.rent_type == 2){
                            if(this.calender_changed == 1){
                                nights = 30;
                            }else{
                                nights = (this.month_count *30) + Number(this.night_count);
                            }
                        } else{
                            nights = (this.month_count *30) + Number(this.night_count);
                        }
                        let end_date = new Date(new_date.setDate(new_date.getDate() + nights));
                        this.selectedDate = {
                            start : new_date,
                            end : end_date
                        };

                }

                if(this.cachedSelectedDate.start > this.selectedDate.start && !Nova.app.$hasPermission('booking past')){

                    this.$toasted.show(this.__('You can not book in the past'), {type: 'error'})
                        let new_date = new Date(this.cachedSelectedDate.start),  nights = 0;
                        if(this.rent_type == 2){
                            if(this.calender_changed == 1){
                                nights = 30;
                            }else{
                                nights = (this.month_count *30) + Number(this.night_count);
                            }
                        } else{
                            nights = (this.month_count *30) + Number(this.night_count);
                        }
                        let end_date = new Date(moment(this.cachedSelectedDate.start).add(1,"days"));
                        this.selectedDate = {
                            start : new_date,
                            end : end_date
                        };

                }
                // if(this.showYesterdayNotification){
                //     console.log(this.selectedDate);
                //     console.log(this.cachedSelectedDate);
                // }

                if(this.last_reservation_date_in){
                    if(Date.parse(this.selectedDate.end) > Date.parse(this.last_reservation_date_in) && Date.parse(this.selectedDate.start) < Date.parse(this.last_reservation_date_in)){
                        this.selectedDate.end = new Date(this.last_reservation_date_in);
                        this.$emit('setrange', [this.selectedDate, this.rent_type]);
                        // Nova.$emit('selectedDateFilterUnits', this.selectedDate);
                        this.$toasted.show(this.__('This unit has a reservation that intersect with selected dates !'), {type: 'error'});
                        return false;
                    }else{
                        this.calender_changed++;
                        this.$emit('setrange', [this.selectedDate, this.rent_type]);
                        // Nova.$emit('selectedDateFilterUnits', this.selectedDate);
                    }
                }else{
                    this.calender_changed++;
                    this.$emit('setrange', [this.selectedDate, this.rent_type]);
                    // Nova.$emit('selectedDateFilterUnits', this.selectedDate);
                }

             
            },
            updateUnit(newUnit) {
                if (newUnit && newUnit.id) {
                    this.unit_id = newUnit.id;
                    this.unit = newUnit;
                }
            }
        },
        mounted() {
            Nova.request().get('/nova-vendor/calender/unit/getTeamSettingDayStart?unit_id=' + this.unit_id)
                .then((res) => {
                    // waiting unit to be loaded - task in basecamp ( day start based on day start coming from settings )
                    let currentDate  =momenttimezone().tz("Asia/Riyadh").format("YYYY-MM-DD");
                    let startDate =  moment(String(this.$route.params.date)).format("YYYY-MM-DD") ;
                    let currentTime  =   momenttimezone().tz("Asia/Riyadh").format('HH:mm');
                    let settingDayStartTime = res.data.day_start ;
                    this.settingDayStartTimeFromSettings = res.data.day_start
                    this.currentTime = currentTime;
                    this.server_date = res.data.server_date;
                    // if( (currentTime > settingDayStartTime) && ( currentDate < startDate ) ){
                    if( (settingDayStartTime >  currentTime) ){
                        // this.selectedDate.start = new Date(moment(String(this.$route.params.date)).subtract(1,'d').toISOString());
                        // this.selectedDate.end = new Date(moment(String(this.$route.params.date)).toISOString());

                        if(!this.$route.params.changed){
                            this.showYesterdayNotification = true;
                            this.selectedDate = {
                                start:  new Date(moment(String(this.$route.params.date)).subtract(1,'d').toISOString()),
                                end: new Date(moment(String(this.$route.params.date)).toISOString())
                            };

                            this.cachedSelectedDate = {
                                start:  new Date(moment(String(this.$route.params.date)).subtract(1,'d').toISOString()),
                                end: new Date(moment(String(this.$route.params.date)).toISOString())
                            };
                        }else{
                            this.selectedDate = {
                                start: new Date(moment(String(this.$route.params.date)).toISOString()),
                                end: new Date(moment(String(this.$route.params.date)).add('1', 'days').toISOString())
                            };
                        }
                       
                    }else{
                        this.selectedDate = {
                            start: new Date(moment(String(this.$route.params.date)).toISOString()),
                            end: new Date(moment(String(this.$route.params.date)).add('1', 'days').toISOString())
                        };
                    }
                }).catch((err) => {
                console.log(err);
            });

            Nova.$on('attachable_reservation' , (reservation) => {
              this.main_reservation = reservation;
              this.rent_type =  this.main_reservation.rent_type;
              this.updateDate();
              if(this.main_reservation){
                    if(this.main_reservation.invoices.length){
                        let invoices_without_credit_notes = _.filter(this.main_reservation.invoices, function(invoice) {
                            return invoice.invoice_credit_note === null;
                        });
                        if(invoices_without_credit_notes.length){
                            // invoices found according to the selected reservation
                            var lastGroupInvoice = invoices_without_credit_notes[0];
                            var theStartDateOfTheNewReservation  = moment(this.selectedDate.start).format('YYYY-MM-DD'); 
                        
                            // if the new start date for our new reservation not greater than the last invoice date to 
                            // we must be not able to add this reservation and we should force user to change the dates 
                            if(!(theStartDateOfTheNewReservation > lastGroupInvoice.to)){
                                var available_to_add_from = moment(lastGroupInvoice.to).add(1,'days').format('YYYY/MM/DD'); 
                                this.$toasted.show(this.__('Can not add this new reservation with those dates becuase of invoices intersection, you can add starting from :available_to_add_from' , {available_to_add_from : available_to_add_from }), {type: 'error'});
                                // Nova.$emit('disable-add-reservatoin-btn-cause-of-group-reservation-invoices-intersection');
                                return;
                            }
                        }
                        
                        
                    }
                
                
              }
          })

            if (Nova.app.currentTeam.last_subscription && Nova.app.currentTeam.last_subscription.stripe_plan == 'monthly-yearly'){
                  this.isLoadingMonthlyYearly = true;
                   this.showDaily = false;
                   this.currentSubscription = 'monthly-yearly';
            }
            setTimeout(() => {
                if (Nova.app.currentTeam.last_subscription && Nova.app.currentTeam.last_subscription.stripe_plan == 'monthly-yearly'){
                    const elem = this.$refs.changeRentBtn
                    elem.click()
                }  
            }, 2000);
         
        }
    }
</script>

<style lang="scss">
    .margin-top-10 {
        margin-top: 10px !important;
    }
    .border-bottom-none{
        border-bottom: none !important;
    }
    .reservation_details {
        .rent_type {
            display: flex;
            border-bottom: 1px solid #ddd;
            margin: 0 0 10px;
            padding: 0 0 10px;
            justify-content: flex-start;
            font-size: 15px;
            color: #000;
            flex-wrap: wrap;
            align-items: center;
            .title {
                width: 25%;
                [dir="ltr"] & {
                    width: 30%;
                } /* rtl */
            } /* title */
            .radios_area {
                width: 75%;
                display: flex;
                align-items: center;
                flex-wrap: wrap;
                [dir="ltr"] & {
                    width: 70%;
                } /* rtl */
                label.custom_radio {
                    display: block;
                    position: relative;
                    padding: 0 30px 0 0;
                    cursor: pointer;
                    color: #7E8790;
                    line-height: 30px;
                    margin: 0 0 0 50px;
                    -webkit-user-select: none;
                    -moz-user-select: none;
                    -ms-user-select: none;
                    user-select: none;
                    -webkit-transition: all 0.2s ease-in-out;
                    -moz-transition: all 0.2s ease-in-out;
                    -o-transition: all 0.2s ease-in-out;
                    transition: all 0.2s ease-in-out;
                    [dir="ltr"] & {
                        padding: 0 0 0 30px;
                        margin: 0 50px 0  0;
                    } /* rtl */
                    &:hover {
                        .checkmark {background: #fafafa;}
                        p {color: #444444;}
                    } /* hover */
                    input {
                        position: absolute;
                        opacity: 0;
                        cursor: pointer;
                        height: 0;
                        width: 0;
                        &:checked ~ {
                            .checkmark {
                                background: #fafafa;
                                &::after {
                                    opacity: 1;
                                    visibility: visible;
                                    -webkit-transform: scale(1);
                                    -moz-transform: scale(1);
                                    -o-transform: scale(1);
                                    transform: scale(1);
                                } /* after */
                            } /* checkmark */
                            p {color: #0A80D8;}
                        } /* checked */
                    } /* input */
                    .checkmark {
                        position: absolute;
                        top: 0;
                        right: 0;
                        height: 20px;
                        width: 20px;
                        background-color: #fcfcfc;
                        border: 1px solid #e8e8e8;
                        border-radius: 100%;
                        -webkit-transition: all 0.2s ease-in-out;
                        -moz-transition: all 0.2s ease-in-out;
                        -o-transition: all 0.2s ease-in-out;
                        transition: all 0.2s ease-in-out;
                        [dir="ltr"] & {
                            right: auto;
                            left: 0;
                        } /* rtl */
                        &::after {
                            content: "";
                            background: #0A80D8;
                            position: absolute;
                            top: 4px;
                            right: 4px;
                            width: 10px;
                            height: 10px;
                            opacity: 0;
                            visibility: hidden;
                            border-radius: 100%;
                            -webkit-transform: scale(0);
                            -moz-transform: scale(0);
                            -o-transform: scale(0);
                            transform: scale(0);
                            -webkit-transition: all 0.2s ease-in-out;
                            -moz-transition: all 0.2s ease-in-out;
                            -o-transition: all 0.2s ease-in-out;
                            transition: all 0.2s ease-in-out;
                        } /* after */
                    } /* checkmark */
                    p {
                        display: block;
                        line-height: 20px;
                        font-size: 16px;
                        color: #000;
                        -webkit-transition: all 0.2s ease-in-out;
                        -moz-transition: all 0.2s ease-in-out;
                        -o-transition: all 0.2s ease-in-out;
                        transition: all 0.2s ease-in-out;
                    } /* p */
                } /* label */
            } /* radios_area */
        } /* rent_type */
        .input_group {
            border-bottom: 1px solid #ddd;
            padding: 0 0 10px;
            margin: 0 0 10px;
            label {
                display: block;
                font-size: 15px;
                color: #000;
                margin: 0 auto 5px;
            } /* label */
            select {
                width: 100%;
                background-image: url("data:image/svg+xml,%3C%3Fxml version='1.0' encoding='iso-8859-1'%3F%3E%3C!-- Generator: Adobe Illustrator 19.0.0, SVG Export Plug-In . SVG Version: 6.00 Build 0) --%3E%3Csvg version='1.1' id='Layer_1' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' x='0px' y='0px' viewBox='0 0 491.996 491.996' style='enable-background:new 0 0 491.996 491.996;' xml:space='preserve'%3E%3Cg%3E%3Cg%3E%3Cpath d='M484.132,124.986l-16.116-16.228c-5.072-5.068-11.82-7.86-19.032-7.86c-7.208,0-13.964,2.792-19.036,7.86l-183.84,183.848 L62.056,108.554c-5.064-5.068-11.82-7.856-19.028-7.856s-13.968,2.788-19.036,7.856l-16.12,16.128 c-10.496,10.488-10.496,27.572,0,38.06l219.136,219.924c5.064,5.064,11.812,8.632,19.084,8.632h0.084 c7.212,0,13.96-3.572,19.024-8.632l218.932-219.328c5.072-5.064,7.856-12.016,7.864-19.224 C491.996,136.902,489.204,130.046,484.132,124.986z'/%3E%3C/g%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3C/svg%3E%0A");
                height: 40px !important;
                padding: 0 10px !important;
                background-color: #fafafa !important;
                border: 1px solid #ddd !important;
                color: #000;
                font-size: 15px;
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
                [dir="ltr"] & {
                    background-position: 97% center;
                } /* ltr */
            } /* select */
        } /* input_group */
        .date_range {
            border-bottom: 1px solid #ddd;
            margin: 0 0 10px;
            padding: 0 0 10px;
            label {
                display: block;
                font-size: 15px;
                color: #000;
                margin: 0 auto 5px;
            } /* label */
            .date_picker_area {
                position: relative;
                input {
                    width: 100%;
                    height: 40px !important;
                    padding: 0 10px !important;
                    background-color: #fafafa !important;
                    border: 1px solid #ddd !important;
                    color: #000;
                    font-size: 15px;
                    cursor: pointer;
                    -webkit-box-sizing: border-box;
                    box-sizing: border-box;
                    -webkit-appearance: none;
                    -moz-appearance: none;
                    -o-appearance: none;
                    appearance: none;
                    border-radius: 5px !important;
                } /* input */
                .vc-popover-content-wrapper {
                    .vc-popover-content {
                        border: 1px solid #dddddd !important;
                        border-radius: 5px !important;
                        .vc-container {
                            background: #fafafa;
                            .vc-title-wrapper {
                                text-align: center;
                                width: 100%;
                                .vc-title {
                                    font-family: 'Dubai-Medium';
                                    font-weight: normal;
                                    font-size: 20px;
                                    line-height: 30px;
                                    height: 30px;
                                    padding: 0 30px;
                                } /* vc-title */
                            } /* vc-title-wrapper */
                            .vc-arrows-container {
                                position: absolute;
                                top: 0;
                                left: 0;
                                right: 0;
                                width: 100%;
                                display: flex;
                                justify-content: space-between !important;
                                align-items: center;
                            } /* vc-arrows-container */
                            .vc-weekday {
                                color: #444444;
                                font-weight: normal;
                                font-size: 13px;
                                margin: 0 5px;
                                @media (min-width: 320px) and (max-width: 480px) {
                                    margin: 0 1px;
                                } /* Mobile */
                                @media (min-width: 481px) and (max-width: 767px) {
                                    margin: 0 3px;
                                } /* Mobile */
                            } /* vc-weekday */
                        } /* vc-container */
                    } /* vc-popover-content */
                } /* vc-popover-content-wrapper */
            } /* date_picker_area */
            .date_picker_alert {
                display: block;
                margin: 10px 0 0;
                background: #fff3cd;
                border: 1px solid #ffeeba;
                color: #856404;
                border-radius: 4px;
                padding: 10px;
                font-size: 15px;
                span {
                    display: block;
                    font-family: 'Dubai-Bold';
                } /* span */
            } /* date_picker_alert */
        } /* date_range */
        .sourceId {
            label {
                display: block;
                font-size: 15px;
                color: #000;
                margin: 0 auto 5px;
            } /* label */
            select {
                width: 100%;
                background-image: url("data:image/svg+xml,%3C%3Fxml version='1.0' encoding='iso-8859-1'%3F%3E%3C!-- Generator: Adobe Illustrator 19.0.0, SVG Export Plug-In . SVG Version: 6.00 Build 0) --%3E%3Csvg version='1.1' id='Layer_1' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' x='0px' y='0px' viewBox='0 0 491.996 491.996' style='enable-background:new 0 0 491.996 491.996;' xml:space='preserve'%3E%3Cg%3E%3Cg%3E%3Cpath d='M484.132,124.986l-16.116-16.228c-5.072-5.068-11.82-7.86-19.032-7.86c-7.208,0-13.964,2.792-19.036,7.86l-183.84,183.848 L62.056,108.554c-5.064-5.068-11.82-7.856-19.028-7.856s-13.968,2.788-19.036,7.856l-16.12,16.128 c-10.496,10.488-10.496,27.572,0,38.06l219.136,219.924c5.064,5.064,11.812,8.632,19.084,8.632h0.084 c7.212,0,13.96-3.572,19.024-8.632l218.932-219.328c5.072-5.064,7.856-12.016,7.864-19.224 C491.996,136.902,489.204,130.046,484.132,124.986z'/%3E%3C/g%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3C/svg%3E%0A");
                height: 40px !important;
                padding: 0 10px !important;
                background-color: #fafafa !important;
                border: 1px solid #ddd !important;
                color: #000;
                font-size: 15px;
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
                [dir="ltr"] & {
                    background-position: 97% center;
                } /* ltr */
            } /* select */
        } /* sourceId */
        .loader_item {
            padding: 50px;
        } /* loader_item */
        ul {
            li {
                border-top: 1px solid #dddddd;
                margin: 10px 0 0;
                padding: 10px 0 0;
                display: flex;
                justify-content: flex-start;
                flex-wrap: wrap;
                align-items: center;
                font-size: 15px;
                color: #000000;
                .name {
                    width: 25%;
                    [dir="ltr"] & {
                        width: 30%;
                    } /* rtl */
                } /* name */
                .desc {
                    width: 75%;
                    [dir="ltr"] & {
                        width: 70%;
                    } /* rtl */
                } /* desc */
                .reservation_dates {
                    width: 75%;
                    [dir="ltr"] & {
                        width: 70%;
                    } /* rtl */
                    .date_area {
                        span {
                            display: block;
                            margin: 0 auto 5px;
                            &:last-of-type {margin: 0 auto;}
                        } /* span */
                    } /* date_area */
                    .rent_type_month {
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        border-top: 1px solid #dddddd;
                        margin: 10px 0 0;
                        padding: 10px 0 0;
                        .block_one {
                            display: flex;
                            flex-wrap: wrap;
                            align-items: center;
                            justify-content: center;
                            .number_input {
                                width: 80px;
                                border: 1px solid #ddd;
                                border-radius: 4px;
                                height: 40px;
                                display: block;
                                overflow: hidden;
                                padding: 0 30px 0 0;
                                position: relative;
                                margin: 0 0 0 10px;
                                [dir="ltr"] & {
                                    margin: 0 10px 0 0;
                                } /* rtl */
                                button {
                                    height: 19px;
                                    line-height: 19px;
                                    font-size: 10px;
                                    padding: 0;
                                    width: 30px;
                                    border-top: 1px solid #ddd;
                                    border-left: 1px solid #ddd;
                                    background: #fafafa;
                                    display: block;
                                    bottom: 0;
                                    right: 0;
                                    position: absolute;
                                    &.plus {
                                        top: 0;
                                        bottom: auto;
                                        height: 19px;
                                        border-top: none;
                                        i {
                                            position: relative;
                                            top: 1px;
                                        } /* ه */
                                    } /* plus */
                                } /* button */
                                input {
                                    width: 100%;
                                    height: 38px;
                                    text-align: center;
                                    font-size: 19px;
                                    background: #fafafa;
                                    -webkit-appearance: textfield;
                                    -moz-appearance: textfield;
                                    appearance: textfield;
                                } /* input */
                                span {
                                    float: right;
                                    line-height: 40px;
                                    margin: 0 10px 0 0 !important;
                                    color: #111 !important;
                                    [dir="ltr"] & {
                                        margin: 0 0 0 10px !important;
                                    } /* rtl */
                                } /* span */
                            } /* number_input */
                        } /* block_one */
                    } /* rent_type_month */
                    .rent_type_day {
                        .block_one {
                            display: flex;
                            flex-wrap: wrap;
                            align-items: center;
                            justify-content: flex-start;
                            .number_input {
                                width: 80px;
                                border: 1px solid #ddd;
                                border-radius: 4px;
                                height: 40px;
                                display: block;
                                overflow: hidden;
                                padding: 0 30px 0 0;
                                position: relative;
                                margin: 0 0 0 10px;
                                [dir="ltr"] & {
                                    margin: 0 10px 0 0;
                                } /* rtl */
                                button {
                                    height: 19px;
                                    line-height: 19px;
                                    font-size: 10px;
                                    padding: 0;
                                    width: 30px;
                                    border-top: 1px solid #ddd;
                                    border-left: 1px solid #ddd;
                                    background: #fafafa;
                                    display: block;
                                    bottom: 0;
                                    right: 0;
                                    position: absolute;
                                    &.plus {
                                        top: 0;
                                        bottom: auto;
                                        height: 19px;
                                        border-top: none;
                                        i {
                                            position: relative;
                                            top: 1px;
                                        } /* ه */
                                    } /* plus */
                                } /* button */
                                input {
                                    width: 100%;
                                    height: 38px;
                                    text-align: center;
                                    font-size: 19px;
                                    background: #fafafa;
                                    -webkit-appearance: textfield;
                                    -moz-appearance: textfield;
                                    appearance: textfield;
                                } /* input */
                                span {
                                    float: right;
                                    line-height: 40px;
                                    margin: 0 10px 0 0 !important;
                                    color: #111 !important;
                                    [dir="ltr"] & {
                                        margin: 0 0 0 10px !important;
                                    } /* rtl */
                                } /* span */
                            } /* number_input */
                        } /* block_one */
                    } /* rent_type_day */
                } /* reservation_dates */
                .total_price {
                    width: 75%;
                    direction: ltr;
                    font-size: 20px;
                    color: #000;
                    [dir="ltr"] & {
                        width: 70%;
                    } /* rtl */
                } /* total_price */
                .night_count {
                    width: 75%;
                    font-size: 20px;
                    color: #000;
                    [dir="ltr"] & {
                        width: 70%;
                    } /* rtl */
                } /* night_count */
            } /* li */
        } /* ul */
    } /* reservation_details */
    input[type=number]::-webkit-inner-spin-button,
    input[type=number]::-webkit-outer-spin-button {
        -webkit-appearance: none;
    }
</style>
