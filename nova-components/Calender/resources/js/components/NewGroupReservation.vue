<template>
    <div id="new_reservation_page">
        <loading
                :active.sync="loading"
                :can-cancel="false"
                :is-full-page="true"
        />

        <div class="row_cols">
            <div class="right_col">
                <companies-component-for-group-feature />
            
                <!-- Sidebar details start -->

                 <div class="reservation_details">

                     <!-- rent_type -->
                    <div class="rent_type">
                        <div class="title">{{__('Rent Type')}} :</div>
                        <div class="radios_area">
                            <label class="custom_radio" for="daily" v-if="showDaily">
                                <input type="radio" id="daily" value="daily" v-model="rent_type" @change="changeRentType" :disabled="main_reservation && main_reservation.rent_type == 2">
                                <span class="checkmark"></span>
                                <p>{{__('Daily')}}</p>
                            </label>
                            <label class="custom_radio" for="monthly">
                                <input type="radio" id="monthly" value="monthly" v-model="rent_type" @change="changeRentType" :disabled="main_reservation && main_reservation.rent_type == 1">
                                <span class="checkmark"></span>
                                <p>{{__('Monthly')}}</p>
                            </label>
                        </div><!-- radios_area -->
                    </div>

                    <div  class="date_range">
                        <label>{{__('Select date range')}} :</label>
                        <div class="date_picker_area">
                            <vcc-date-picker
                                class='v-date-picker'
                                :locale="vcc_local"
                                mode='range'
                                v-model="calendarDateObj"
                                show-caps
                                is-expanded
                                :columns="$screens({ default: 1, lg: 2 })"
                                :popoverExpanded="true"
                                :popover="{ placement: 'bottom', visibility: 'click' }"
                                @input="calendarChanged($event)"
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


                    <div class="input_group">
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

                     <!-- Unit selector must be here  -->
                    <div  class="multi-select-services input_group">
                        <label>{{__('Units Selection')}} :</label>
                       <!-- select is here  -->
                       <vueMultiSelect
                        v-model="units_selected"
                        :options="options"
                        :filters="filters"
                        :btnLabel="multi_select_btn_label"
                        :selectOptions="multi_select_data"/>
                    </div>


                    <ul>
                        <!-- Monthly Reservations  -->
                        <li>
                            <div class="name">{{__('Reservation Date')}} :</div>
                            <div class="reservation_dates">
                                <div class="date_area">
                                    <span>{{__('From')}} : {{ formatDate(this.calendarDateObj.start) }}</span>
                                    <span>{{__('To')}} : {{ formatDate(this.calendarDateObj.end) }}</span>
                                </div><!-- date_area -->

                                <!-- switcher for monthly  -->

                                 <div v-if="rent_type == 'monthly'">
                                    <div class="counts_area">
                                        <div class="rent_type_month">
                                            <div class="block_one">
                                                <div class="number_input">
                                                    <button @click="decrementMonthCount">
                                                        <i class="fa fa-minus" aria-hidden="true"></i>
                                                    </button>
                                                    <input  
                                                        v-model="month_count"
                                                        class="quantity" 
                                                        min="0" 
                                                        name="quantity" 
                                                        type="number"
                                                        @change="monthCountInputChanged"
                                                    />
                                                    <button class="plus" @click="incrementMonthCount">
                                                        <i class="fa fa-plus" aria-hidden="true"></i>
                                                    </button>
                                                </div><!-- number_input -->
                                                <span>{{ __('month')}}</span>
                                            </div><!-- block_one -->
                                            <div class="block_one">
                                                <div class="number_input">
                                                    <button @click="decrementMonthNightCount">
                                                        <i class="fa fa-minus" aria-hidden="true"></i>
                                                    </button>
                                                    <input
                                                        v-model="month_night_count"
                                                        class="quantity"
                                                        name="quantity"
                                                        type="number"
                                                        @change="monthNightCountInputChanged"
                                                    />
                                                    <button class="plus" @click="incrementMonthNightCount">
                                                        <i class="fa fa-plus" aria-hidden="true"></i>
                                                    </button>
                                                </div><!-- number_input -->
                                                <span>{{ __('night')}} </span>
                                            </div><!-- block_one -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                        <li v-if="rent_type == 'monthly'">
                            <div class="name">{{__('Nights')}} :</div>
                            <div class="night_count">{{ night_count }} {{__('Night')}}</div>
                        </li>

                        <!-- Daily Reservations  -->
                        <li v-else>
                            <div class="name">{{__('Nights')}} :</div>
                            <div class="reservation_dates">
                                <div class="rent_type_day">
                                    <div class="block_one">
                                        <div class="number_input">
                                            <button @click="decrementNightCount">
                                                <i class="fa fa-minus" aria-hidden="true"></i>
                                            </button>
                                            <input 
                                                    v-model="night_count" 
                                                    class="quantity" 
                                                    min="1" 
                                                    name="quantity" 
                                                    type="number"
                                                    @change="nightCountInputChanged"
                                            />
                                            <button  class="plus" @click="incrementNightCount">
                                                <i class="fa fa-plus" aria-hidden="true"></i>
                                            </button>
                                        </div><!-- number_input -->
                                        <span>{{ __('night')}} </span>
                                    </div><!-- block_one -->
                                </div><!-- rent_type_day -->
                            </div><!-- reservation_dates -->
                        </li>
                    </ul>

                </div><!-- reservation_details -->

                <br>

                <div class="reservation_details relative">


                    <!-- Reservation Services selector must be here  -->
                    <div  class="multi-select-services input_group">
                        <label>{{__('Services included in price')}} :</label>
                    <!-- select is here  -->
                    <vueMultiSelect
                        v-model="reservation_services_selected"
                        :options="options_services"
                        :filters="filters_services"
                        :btnLabel="multi_select_services_btn_label"
                        :selectOptions="multi_select_services_data"/>
                    </div>

                </div><!-- Reservation Services  -->


                <!-- Sidebar details end -->
            </div><!-- right_col -->

            <!-- Units Selected & Star Unit -->
            <div class="left_col" >
                <div class="units_selection" v-if="units_selected.length">
                    <div class="title d-block mb-3">{{__('Total units selected')}} : {{units_selected.length}}</div>
                    <div class="allUnitItems">
                        <div class="unitItem" v-for="(unit,i) in units_selected" :key="i">
                            <div class="action">
                                <div class="unitStar relative">
                                    <input type="radio" name="star_radio" disabled :id="unit.id" :value="unit.id" v-model="star_unit_id"  class="w-full h-full absolute top-0 start-0">
                                    <div class="starUnit"></div>
                                </div>
                                <button type="button" class="clearUnit" @click="removeUnitFromSelection(unit)"></button>
                            </div>
                            <span>{{unit.unitIdentifier}}</span>

                        </div>
                    </div>
                    <!-- <h1>welcome emad</h1> -->
                </div><!-- units_selection -->
                <div class="units_selection" v-else>
                    <div class="noUnits flex align-items-center justify-content-center flex-col">
                        <div class="icon mb-3"></div>
                        <b>{{__('Please select units at max of 40 to proceed')}}</b>
                    </div>
                </div>
            </div><!-- left_col -->
        </div><!-- row_cols -->

        <div class="total_result">
                    <div class="top_rows">
                        <div class="col_right">
                            <span>{{__('From')}} : {{ formatDate(this.calendarDateObj.start) }}</span>
                            <span>{{__('To')}} : {{ formatDate(this.calendarDateObj.end) }}</span>
                        </div><!-- col_right -->
                        <div class="col_left">
                            <p> <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span> {{ parseFloat(totals.sub_total).toFixed(2) }}</p>
                        </div><!-- col_left -->
                    </div><!-- top_rows -->
                    <div class="bottom_rows">
                        <ul>
                            <li>
                               <span>{{__('Subtotal')}}</span>
                               <p> <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span> {{ parseFloat(totals.sub_total).toFixed(2) }}</p>
                        
                            </li>
                            <li>
                                <span>{{__('Ewa')}} ({{  }}%)</span>
                                <p> <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span> {{ parseFloat(totals.total_ewa).toFixed(2) }}</p>
                        
                            </li>
                            <li >
                                <span>{{__('VAT')}} ({{  }}%)</span>
                                <p> <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span> {{ parseFloat(totals.total_vat).toFixed(2) }}</p>

                            </li>
                          
                            <li>
                               <span>{{__('Total')}}</span>
                               <p> <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span> {{ parseFloat(totals.sub_total + totals.total_ewa + totals.total_vat).toFixed(2) }}</p>
                        
                            </li>
                        </ul>
                    </div><!-- top_rows -->
                </div><!-- total_result -->
        <div class="button_area">
            <button @click="createReservations" :disabled="disableCreationProcess">{{__('Create reservations')}}</button>
        </div><!-- button_area -->

    </div><!-- new_reservation_page -->
</template>

<script>
    import momenttimezone from 'moment-timezone';
    import vueMultiSelect from 'vue-multi-select';
    import 'vue-multi-select/dist/lib/vue-multi-select.css';
    import CompaniesComponentForGroupFeature from './partial/CompaniesComponentForGroupFeature.vue';
    import HijrahDate from 'hijrah-date';
    import momentHijri from 'moment-hijri';
    import moment from 'moment';
    import Loading from 'vue-loading-overlay';
    import 'vue-loading-overlay/dist/vue-loading.css';
    import flatPickr from 'vue-flatpickr-component';
    import 'flatpickr/dist/flatpickr.css';
    import VueDatetimeJs from 'vue-datetime-js'

    export default {
        name: "new-group-reservation",
        components: {
            Loading,
            flatPickr,
            datePicker: VueDatetimeJs,
            CompaniesComponentForGroupFeature,
            vueMultiSelect
        },
        data: () => {
            return {
                locale : Nova.config.local,
                reservationPeriod : [],
                loading: false,
                isLoading: false,
                SCTH: false,
                SHMS: false,
                source_id: null,
                source_num : null,
                unit_id: null,
                unit: null,
                units: null,
                units_selected : [],
                multi_select_btn_label: units_selected => `${Nova.app.__('Selected Units')} (${units_selected.length})`,
                multi_select_data: [
                    {
                        units: [],
                    },
                ],
                filters: [{
                    nameAll: Nova.app.__('Select all'),
                    nameNotAll: Nova.app.__('Do not select any'),
                    func() {
                    return true;
                    },
                }],
                options: {
                    multi: true,
                    groups: true,
                    labelName: 'unitIdentifier',
                    labelList: 'units',
                    groupName: 'title',

                    cssSelected: option => (option.selected ? { 'background-color': '#4099de' , 'color': '#fff' } : ''),
                },
                price_type: 'Day',
                total_price: 0,
                subtotal_price: 0,
                total_ewa: 0,
                total_vat: 0,
                total_ttx: 0,
                change_rate: 0,
                start_date: null,
                crumbs: [],
                calendarDateObj: {
                    start: null,
                    end: null
                },
                rent_type: 'daily',
                calendarLocale: Nova.config.local,
                current_team_id : Nova.config.user.current_team_id,
                company_id : null,
                vcc_local: {
                    id: Nova.config.local,
                    firstDayOfWeek: 1,
                    masks: {
                        weekdays: 'WWW',
                        input: ['WWWW YYYY/MM/DD', 'L'],
                        data: ['WWWW YYYY/MM/DD', 'L'],
                    }
                },
                sourceId: null,
                sourceNum : null,
                receptionSourceId: null,
                sources : [],
                star_unit_id : null,
                month_count: 0,
                month_night_count:0,
                night_count: 1,
                reservation_nights : 0,
                calender_changed: 0,
                old_night_count: 0,
                old_month_count: 0,
                cachedSelectedDate : {},
                showYesterdayNotification : false,
                init_month_nights : 30,
                main_reservation : null,
                filtered_units_selected : [],
                disableCreationProcess : false,
                showDaily: true,
                reservation_services_selected : [],
                multi_select_services_btn_label: reservation_services_selected => `${Nova.app.__('Selected Services')} (${reservation_services_selected.length})`,
                multi_select_services_data: [
                    {
                        reservation_services: [],
                    },
                ],
                filters_services: [{
                    nameAll: Nova.app.__('Select all'),
                    nameNotAll: Nova.app.__('Do not select any'),
                    func() {
                    return true;
                    },
                }],
                options_services: {
                    multi: true,
                    groups: true,
                    labelName: 'reservationServiceIdentifier',
                    labelList: 'reservation_services',
                    groupName: 'title',

                    cssSelected: option => (option.selected ? { 'background-color': '#4099de' , 'color': '#fff' } : ''),
                },    
                customer_id : null,
                server_date : null,
                totals : {
                    total_price : 0,
                    total_ewa : 0,
                    total_vat : 0,
                    sub_total : 0
                },
                groupedSpecialPricesArray : []
            }
        },
        computed: {
            // a computed getter
            minDate() {
                let user = Nova.config.user
                let permisions = user.roles.find(x => x.team_id == user.current_team_id)['permissions']
                return permisions.includes('booking past') ? false : new Date();
            },
            maxDate(){
                return moment().format('YYYY-MM-DD');
            },
            maxDateHijriDate(){
                return momentHijri().format('iYYYY-iMM-iDD');
            },
        },
        methods: {
            getReservationServices(){
              
                axios.get('/nova-vendor/settings/getReservationServices?all=true')
                .then(response => {
                    const self = this;
                    let arr = [];
                    var reservation_services = response.data;
                    if(reservation_services.length){
                        reservation_services.map(function(service, key) {
                                let obj = {};
                                obj.reservationServiceIdentifier = self.locale == 'ar' ?  service.name_ar  : service.name_en;
                                obj.id = service.id;
                                arr.push(obj);
                        });
                        this.multi_select_services_data[0].reservation_services = arr;
                    }
                })

            },
            monthCountInputChanged(){
                this.setStartAndEndDate();
            },
            monthNightCountInputChanged(){
                this.setStartAndEndDate();
            },
            nightCountInputChanged(){
                this.setStartAndEndDate();
            },
            changeRentType(){
                if(this.rent_type == 'daily'){
                    this.night_count = 1;
                    this.month_count = 0;
                    this.month_night_count = 0;
                }else{
                    this.night_count = 30;
                    this.month_count = 1;
                    this.month_night_count = 0;
                }
                // set the start & end date for the calendar 
                this.setStartAndEndDate();
            },
            incrementNightCount(){
                this.night_count++;
                this.setStartAndEndDate();
            },
            decrementNightCount(){
                if(this.night_count == 1){
                    return;
                }
                this.night_count--;
                this.setStartAndEndDate();
            },

            incrementMonthNightCount(){
                if(this.month_night_count < 30){
                    // normal increment
                    this.month_night_count++;
                }
                if(this.month_night_count == 30){
                    // if month night count reached 30 then convert it to a month  
                    this.month_count++;
                    // then set month_night_count to zero
                    this.month_night_count = 0;
                }
                
                // then set the start & end date for calendar date object by calling it's function 
                this.setStartAndEndDate();

            },
            decrementMonthNightCount(){
                // stop decrement if month_night_count is zero
                if(this.month_night_count == 0){
                    return;
                }
                // normal decrement
                this.month_night_count--;
                // then set the start & end date for calendar date object by calling it's function 
                this.setStartAndEndDate();

            },
            incrementMonthCount(){
                this.month_count++;
                // then set the start & end date for calendar date object by calling it's function 
                this.setStartAndEndDate();
            },
            decrementMonthCount(){
                // stop decrement if month_count is 1
                if(this.month_count == 1){
                    return;
                }
                this.month_count--;
                // then set the start & end date for calendar date object by calling it's function 
                this.setStartAndEndDate();
            },
            /**
             * set the start and the end date for the calendar date object according to the night_count variable
             */
            setStartAndEndDate(){
                if(this.rent_type == 'monthly'){
                    // then we need to calculate the night_count again after the new values settled 
                    this.night_count = parseInt(this.month_night_count) + parseInt(this.month_count*30);
                }
                this.calendarDateObj = {
                    start: new Date(moment(String(this.calendarDateObj.start)).toISOString()),
                    end: new Date(moment(String(this.calendarDateObj.start)).add(this.night_count, 'days').toISOString())
                };
                
            },

            createReservations(){

                 if(!this.company_id){
                    this.$toasted.show(this.__('Please select company to proceed'), {type: 'error'})
                    return;
                }
                
                if(!this.units_selected.length){
                    this.$toasted.show(this.__('Please select units to proceed'), {type: 'error'})
                    return;
                }

                this.loading = true;
                if(this.star_unit_id && !this.main_reservation && this.units_selected.length > 1){
                    this.filtered_units_selected = this.units_selected.filter(unit => unit.id != this.star_unit_id);
                }
                
                if(this.units_selected.length > 40){
                    this.loading = false;
                    this.$toasted.show(this.__('Please select units at max of 40 to proceed'), {type: 'error'})
                    return;
                }
                
                 // Transform groupedSpecialPricesArray into the desired format
                const transformedGroupedSpecialPrices = this.groupedSpecialPricesArray.reduce((acc, item) => {
                    acc[item.unit_id] = item.specialPrices;
                    return acc;
                }, {});
                Nova.request().post('/nova-vendor/calender/units/create-reservations',{
                    company_id : this.company_id,
                    units_selected : this.units_selected,
                    filtered_units_selected : this.filtered_units_selected,
                    main_reservation  :this.main_reservation,
                    star_unit_id : this.star_unit_id,
                    date_start : moment(String(this.calendarDateObj.start)).format('YYYY-MM-DD'),
                    date_end : moment(String(this.calendarDateObj.end)).format('YYYY-MM-DD'),
                    rent_type : this.rent_type,
                    source_id : this.sourceId,
                    source_number : this.sourceNum,
                    reservation_services_selected : this.reservation_services_selected,
                    customer_id : this.customer_id,
                    groupedSpecialPricesArray : transformedGroupedSpecialPrices
                })
                .then(response => {
                    if (response.data.success) {
                        this.$toasted.show(this.__('Reservations has been created successfuly !'), {type: 'success'})
                        if(this.customer_id || response.data.customer_id){
                            this.$router.push({
                                name: 'reservation', params: {
                                    id: response.data.reservation.id
                                }
                            })
                        }else{
                            this.$router.push({
                                name: 'reservation-noc', params: {
                                    id: response.data.reservation.id
                                }
                            })
                        }
                      
                    }

                    this.loading = false;
                })
                
            },

            /**
             * Remove unit from the selection
             */
            removeUnitFromSelection(unit){
                let remainingArr = [];

                 if((this.star_unit_id == unit.id) && this.main_reservation){
                    this.star_unit_id = null;
                }

                remainingArr = this.units_selected.filter(data => data.id != unit.id);
                this.units_selected = remainingArr;

            },

            formatDate(date) {
                return Nova.app.__formatDateWithHumanDate(date);
            },

            calculateTotalNights(){
                // To calculate the time difference of two dates
                // var Difference_In_Time = this.calendarDateObj.end.getTime() - this.calendarDateObj.start.getTime();
                // To calculate the no. of days between two dates
                // var Difference_In_Days = Difference_In_Time / (1000 * 3600 * 24);

                var start = moment(this.calendarDateObj.start);
                var end = moment(this.calendarDateObj.end);
                
                return end.diff(start, 'days');
            },
            /**
             * The user opened the calendar and select the start and end dates
             * we are calculating the night_count from here
             */
            calendarChanged(date){
                this.getServerDate();
                this.night_count = this.calculateTotalNights();
                if(this.rent_type == 'monthly'){
                        // reset date as the minimum nights must be 30
                        if(this.night_count < 30){
                            this.calendarDateObj = {
                                start: new Date(moment(String(this.calendarDateObj.start)).toISOString()),
                                end: new Date(moment(String(this.calendarDateObj.start)).add(30, 'days').toISOString())
                            };
                        }
                        var the_reminder = this.night_count % 30 ;
                        var pure_months_days = parseInt(this.night_count) - parseInt(the_reminder);  
                        var pure_months = parseInt(pure_months_days / 30);
                        this.month_count = pure_months;
                        this.month_night_count = the_reminder;     
            
                }

                if(this.main_reservation && !this.checkValidToAttachReservationOrNot()){
                    this.disableCreationProcess = true;
                }else{
                    this.disableCreationProcess = false;
                }
            },
            /**
             * Get a list of reservation sources
             */
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

            /**
             * Check if the day start for the hotel started or not
             */
            dayStartSettings(){
                Nova.request().get('/nova-vendor/calender/unit/getTeamSettingDayStart?unit_id=' + this.unit_id)
                    .then((res) => {
                        
                        let currentTime  =   momenttimezone().tz("Asia/Riyadh").format('HH:mm');
                        let settingDayStartTime = res.data.day_start ;
                      
                      
                        if( (settingDayStartTime >  currentTime) ){
                             
                            this.showYesterdayNotification = true;
                            this.calendarDateObj = {
                                start:  new Date(moment(String(this.$route.query.date)).subtract(1,'d').toISOString()),
                                end: new Date(moment(String(this.$route.query.date)).toISOString())
                            };

                            this.cachedSelectedDate = {
                                start:  new Date(moment(String(this.$route.query.date)).subtract(1,'d').toISOString()),
                                end: new Date(moment(String(this.$route.query.date)).toISOString())
                            };
                        }else{
                            this.calendarDateObj = {
                                start: new Date(moment(String(this.$route.query.date)).toISOString()),
                                end: new Date(moment(String(this.$route.query.date)).add('1', 'days').toISOString())
                            };


                        }
                        
                    }).catch((err) => {
                    console.log(err);
                });
            },
            /**
             * Check units availablility based on the selected dates 
             * Units retrieved has restrictions to the availability & the rent type ( if the unit support monthly reservations or not )
             */
            checkAvailability(){
                this.loading = true;
                let start = moment(String(this.calendarDateObj.start)).format('YYYY-MM-DD');
                let end = moment(String(this.calendarDateObj.end)).format('YYYY-MM-DD');
                Nova.request()
                .post(`/nova-vendor/calender/units/get-available-units` , {
                    
                        start : start,
                        end : end,
                        rent_type : this.rent_type,
                        units_selected : this.units_selected
                    
                })
                .then(response => {
                    this.SCTH = response.data.utility.SCTH;
                    this.SHMS = response.data.utility.SHMS;
                    this.units = response.data.units;
                
                    const selectedIds = new Set(this.units_selected.map(unit => unit.id));

                    const filteredUnits = this.units.filter(unit => selectedIds.has(unit.id));


                    // Sum up prices
                    this.totals = filteredUnits.reduce((acc, unit) => {
                        acc.total_price += parseFloat(unit.prices.total_price);
                        acc.total_ewa += parseFloat(unit.prices.total_ewa);
                        acc.total_vat += parseFloat(unit.prices.total_vat);
                        acc.sub_total += parseFloat(unit.prices.sub_total);
                        return acc;
                    }, { total_price: 0, total_ewa: 0, total_vat: 0, sub_total: 0 });


                    if(this.units){
                        let arr = [];
                        this.units.map(function(unit, key) {
                            if(!unit.has_reservation){
                                let obj = {};
                                obj.unitIdentifier = unit.unit_number + ' - ' + unit.name;
                                obj.id = unit.id;
                                arr.push(obj);
                            }
                        });
                        this.multi_select_data[0].units = arr;
                    }
                    this.loading = false;
                })
                .catch(err => {
                    this.loading = false;
                    this.$toasted.show(this.__(err), {type: 'error'})
                })
            },

            checkValidToAttachReservationOrNot(){
                if(this.main_reservation.invoices.length){
                    let invoices_without_credit_notes = _.filter(this.main_reservation.invoices, function(invoice) {
                        return invoice.invoice_credit_note === null;
                    });
                    if(invoices_without_credit_notes.length){
                        // invoices found according to the selected reservation
                        var lastGroupInvoice = invoices_without_credit_notes[0];
                        var theStartDateOfTheNewReservation  = moment(this.calendarDateObj.start).format('YYYY-MM-DD'); 
                    
                        // if the new start date for our new reservation not greater than the last invoice date to 
                        // we must be not able to add this reservation and we should force user to change the dates 
                        if(!(theStartDateOfTheNewReservation > lastGroupInvoice.to)){
                            var available_to_add_from = moment(lastGroupInvoice.to).add(1,'days').format('YYYY/MM/DD'); 
                            this.$toasted.show(this.__('Can not add this new reservation with those dates becuase of invoices intersection, you can add starting from :available_to_add_from' , {available_to_add_from : available_to_add_from }), {type: 'error'});
                            return false;
                        }else{
                            return true;
                        }
                    }else{
                        return  true;
                    }
                    
                }else{
                    return  true;
                }
            },
            getServerDate(){
                Nova.request()
                .get('/nova-vendor/calender/server/current-date')
                .then(response => {
                    this.server_date = response.data;

                    let startDate = new Date(this.calendarDateObj.start);
                    let today = new Date(this.server_date).setHours(0,0,0,0);
                    

                    var target_date = moment(startDate);
                    var comparing_date = moment(today);
                    
                    var diff_in_days =  comparing_date.diff(target_date, 'days');

                    if( (startDate < today && diff_in_days > 1) && !Nova.app.$hasPermission('booking past')){
                        this.disableCreationProcess = true;
                        this.$toasted.show(this.__('You can not book in the past'), {type: 'error'});
                        return;
                    }else{
                        this.disableCreationProcess = false;
                    }


                    if(startDate < today && !Nova.app.$hasPermission('booking past') && !this.showYesterdayNotification){
                        this.disableCreationProcess = true;
                        this.$toasted.show(this.__('You can not book in the past'), {type: 'error'});
                        return;
                    }else{
                        this.disableCreationProcess = false;
                    }
                })
            },
            getSpecialPrices(unitIds) {

                 // Ensure unitIds is an array
                if (!Array.isArray(unitIds)) {
                    console.error('unitIds is not an array:', unitIds);
                    unitIds = [unitIds]; // Convert to an array if it's a single value
                }

                const start_date = moment(String(this.calendarDateObj.start)).format('YYYY-MM-DD');
                const end_date = moment(String(this.calendarDateObj.end)).format('YYYY-MM-DD');
                const groupedSpecialPrices = {};

                // Iterate over all unit IDs and fetch special prices for each
                const requests = unitIds.map(id =>
                    axios.get(`/nova-vendor/calender/unit/${id}/get-special-prices/${start_date}/${end_date}`)
                        .then(response => {
                            const specialPricesCollector = [];
                            const uniqueDates = new Set();

                            if (response.data.status === 'special_prices_found') {
                                // Process dates with special prices
                                this.datesHasSpecialPrice = response.data.datesHasSpecialPrice;
                                this.datesDoesntHaveSpecialPrice = response.data.datesDoesntHaveSpecialPrice;

                                this.datesHasSpecialPrice.forEach(specialPrice => {
                                    response.data.incomingDates.forEach(incomingDate => {
                                        if (specialPrice.date === incomingDate && !uniqueDates.has(incomingDate)) {
                                            specialPricesCollector.push({
                                                date: incomingDate,
                                                specialPrice: specialPrice.price
                                            });
                                            uniqueDates.add(incomingDate);
                                        }
                                    });
                                });

                                // Process dates without special prices
                                this.datesDoesntHaveSpecialPrice.forEach(noSpecialPrice => {
                                    response.data.incomingDates.forEach(incomingDate => {
                                        if (noSpecialPrice.date === incomingDate && !uniqueDates.has(incomingDate)) {
                                            specialPricesCollector.push({
                                                date: incomingDate,
                                                specialPrice: noSpecialPrice.price
                                            });
                                            uniqueDates.add(incomingDate);
                                        }
                                    });
                                });
                            } else {
                                // Process unit category days prices
                                response.data.unitCategoryDaysPrices.forEach(priceData => {
                                    specialPricesCollector.push({
                                        date: priceData.date,
                                        specialPrice: priceData.price
                                    });
                                });
                            }

                            // Remove duplicate dates
                            const uniqueSpecialPrices = specialPricesCollector.filter((thing, index, self) =>
                                index === self.findIndex(t => t.date === thing.date)
                            );

                            // Group special prices by unit ID
                            groupedSpecialPrices[id] = uniqueSpecialPrices;
                        })
                );

                // Wait for all requests to complete
                Promise.all(requests).then(() => {
                    // Convert grouped special prices into an array
                    this.groupedSpecialPricesArray = Object.keys(groupedSpecialPrices).map(id => ({
                        unit_id: parseInt(id),
                        specialPrices: groupedSpecialPrices[id]
                    }));
                });
            }
        },
        created() {
            this.getSources();
            this.crumbs = [
                {
                    text: 'Home',
                    to: '/dashboards/main',
                },
                {
                    text: 'Add Group Reservation',
                    to: '#',
                },

            ];
        },
        mounted() {
            // day start settings -> am setting the calendar date object there as the initial date  
            this.dayStartSettings();
            this.start_date = this.$route.query.date;
            this.calendarLocale = 'en' ? this.calendarLocale = 'en' : this.calendarLocale = 'ar-sa';
            Nova.$on('attachable_reservation' , (main_reservation) => {
                if(main_reservation){
                    this.main_reservation = main_reservation
                    this.star_unit_id = null;  
                    this.rent_type = main_reservation.rent_type == 1 ? 'daily' : 'monthly';
                    if(!this.checkValidToAttachReservationOrNot()){
                        this.disableCreationProcess = true;
                        return;
                    }else{
                        this.disableCreationProcess = false;
                    }
                    this.changeRentType();
                }else{
                    this.main_reservation = null;
                    if(this.units_selected.length){
                        this.star_unit_id = this.units_selected[0].id;
                    }
                    this.disableCreationProcess = false;
                }
            });
            Nova.$on('companyIdSelected' , (companyIdSelected) => {
                this.company_id = companyIdSelected;
            });


            Nova.$on('individual-created-from-customer' , (customer_id) => {
                this.customer_id = customer_id;
            });

            if (Nova.app.currentTeam.last_subscription && Nova.app.currentTeam.last_subscription.stripe_plan == 'monthly-yearly'){
               this.rent_type = 'monthly';
               this.showDaily = false;
            }

            this.getReservationServices();
        },
        beforeDestroy() {
            Nova.$off('attachable_reservation');
        },
        watch: {
            night_count: {
                    handler(newValue, oldValue) {
                        // Note: `newValue` will be equal to `oldValue` here
                        // on nested mutations as long as the object itself
                        // hasn't been replaced.
                        /**
                         * if the new value is less than 1 i will set the old value and return 
                         */
                        if(newValue <= 0){
                            this.night_count = oldValue;
                            return;
                        }
                        
                        // Setting the start and end date according the night_count variable
                        // this.setStartAndEndDate();

                    },
                    deep: true
            },
            month_count: {
                    handler(newValue, oldValue) {
                        /**
                         * if the new value is less than 1 i will set the old value and return 
                         */
                        if(newValue <= 0){
                            this.month_count = oldValue;
                            return;
                        }
                        // Setting the start and end date according the night_count variable
                        // this.setStartAndEndDate();
                    },
                    deep: true
            },

            calendarDateObj: {
                    handler(newValue, oldValue) {
                         /**
                          * Calling the availability function to get units based on selected dates -_- 
                          */
                       this.checkAvailability();
                    },
                    deep: true
            },
            units_selected: {
                    handler(newUnitsSelected, oldUnitsSelected) {
                        /**
                         * Checking if there are some units selected && the user stared a unit before to store the main reservation on it 
                         */

                         
                        if(newUnitsSelected.length){

                            // Default selection for star unit if no main reservation selected
                            if(!this.main_reservation){
                                this.star_unit_id = newUnitsSelected[0].id;
                            }

                            if(this.star_unit_id){
                                    var units_ids_selected = [];
                                    // the the stared unit is not included in the new selection , i should set it free and d-star it so that the user select a new stared unit
                                    newUnitsSelected.map(function(selectedUnit, key) {
                                        units_ids_selected.push(selectedUnit.id);
                                    });

                                    // set free the stared unit before -_-
                                    if(!units_ids_selected.includes(this.star_unit_id)){
                                        this.star_unit_id = null;
                                    }
                            }

                        }else{
                            this.star_unit_id = null;
                        }

                        const selectedIds = new Set(this.units_selected.map(unit => unit.id));

                        const filteredUnits = this.units.filter(unit => selectedIds.has(unit.id));


                        // Sum up prices
                        this.totals = filteredUnits.reduce((acc, unit) => {
                            acc.total_price += parseFloat(unit.prices.total_price);
                            acc.total_ewa += parseFloat(unit.prices.total_ewa);
                            acc.total_vat += parseFloat(unit.prices.total_vat);
                            acc.sub_total += parseFloat(unit.prices.sub_total);
                            return acc;
                        }, { total_price: 0, total_ewa: 0, total_vat: 0, sub_total: 0 });

                        this.getSpecialPrices([...selectedIds]);
                    
                    }
            },

        }
    }
</script>

<style lang="scss">
    @import '~vue2-autocomplete-js/dist/style/vue2-autocomplete.css';
    @import '~vue-tel-input/dist/vue-tel-input.css';

    .select {
        display: block !important;
        button.btn-select {
            width: 100%;
            height: 40px;
            border: 1px solid #ddd;
            background-color: #fafafa;
            outline: none;
            box-shadow: none;
            padding : 0;
            background-image: url("data:image/svg+xml,%3C%3Fxml version='1.0' encoding='iso-8859-1'%3F%3E%3C!-- Generator: Adobe Illustrator 19.0.0, SVG Export Plug-In . SVG Version: 6.00 Build 0) --%3E%3Csvg version='1.1' id='Layer_1' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' x='0px' y='0px' viewBox='0 0 491.996 491.996' style='enable-background:new 0 0 491.996 491.996;' xml:space='preserve'%3E%3Cg%3E%3Cg%3E%3Cpath d='M484.132,124.986l-16.116-16.228c-5.072-5.068-11.82-7.86-19.032-7.86c-7.208,0-13.964,2.792-19.036,7.86l-183.84,183.848 L62.056,108.554c-5.064-5.068-11.82-7.856-19.028-7.856s-13.968,2.788-19.036,7.856l-16.12,16.128 c-10.496,10.488-10.496,27.572,0,38.06l219.136,219.924c5.064,5.064,11.812,8.632,19.084,8.632h0.084 c7.212,0,13.96-3.572,19.024-8.632l218.932-219.328c5.072-5.064,7.856-12.016,7.864-19.224 C491.996,136.902,489.204,130.046,484.132,124.986z'/%3E%3C/g%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3C/svg%3E%0A");
            background-position: 15px center;
            background-repeat: no-repeat;
            background-size: 14px;
            border-radius: 5px;

            .buttonLabel {
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 0 10px;
                color: #000;
                font-size: 15px;
                span {
                    color: #000;
                    &.caret {
                        display:none;


                    }
                }
            }
        }
        .checkboxLayer {
                margin: 0 auto 5px;
                border-radius: 5px !important;
                border: 1px solid #ddd !important;
                min-width: 339px !important;
                bottom: 100%;
                box-shadow: 0 -6px 12px rgba(0,0,0,0.18) !important;

                div {
                     &:nth-child(2) {
                        display: none;
                    }
                }


                .helperContainer{
                    padding: 8px !important;
                    border-bottom: 1px solid #ddd;
                    margin-bottom: 8px;
                    button.helperButton {
                        min-width: 100px;
                        padding: 0 10px !important;
                        border-radius: 4px !important;
                        margin: 0 !important;
                    }
                }

                .checkBoxContainer{
                    padding: 0 !important;
                    max-height: 250px !important;

                    ul{
                        li{
                            padding: 10px !important;
                            direction: rtl;
                            font-size: 15px;
                            color: #000;
                            height: 45px;

                            span {
                                margin: 0 !important;
                                display: block;
                                &.right {
                                    float: left !important;
                                }
                                &.margin-left-20 {
                                    float: right !important;
                                }
                            }
                        }
                    }
                }
        }


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
    .alert-warning {
        margin: 0 auto 15px;
        border-radius: 5px;
        padding: 10px;
        text-align: center;
        color: #b7791f;
        border: 1px solid #fbd38d;
        background: #fffaf0;
        font-size: 15px;
        cursor: pointer;
    }


    #new_reservation_page {
        .row_cols {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: flex-start;
            margin: 0 -10px;

            .right_col {
                width: 33.3333%;
                padding: 0 10px;
                align-self: stretch;
                margin: 0 0 20px;
                @media (min-width: 320px) and (max-width: 480px) {
                    width: 100%;
                }
                /* Mobile */
                @media (min-width: 481px) and (max-width: 767px) {
                    width: 50%;
                }
                /* Mobile */
                @media (min-width: 768px) and (max-width: 991px) {
                    width: 50%;
                }
                /* Mobile */
                .reservation_details {
                    border-radius: 5px;
                    border: 1px solid #ddd;
                    padding: 10px;
                    background: #fff;
                    box-shadow: 0 2px 4px 0 rgba(0, 0, 0, 0.05);
                    height: auto;
                }

                /* reservation_details */
            }

            /* right_col */
            .left_col {
                width: 66.66666%;
                padding: 0 10px;
                align-self: stretch;
                margin: 0 0 20px;
                @media (min-width: 320px) and (max-width: 480px) {
                    width: 100%;
                }
                /* Mobile */
                @media (min-width: 481px) and (max-width: 767px) {
                    width: 50%;
                }
                /* Mobile */
                @media (min-width: 768px) and (max-width: 991px) {
                    width: 50%;
                }
                /* Mobile */
                .units_selection {
                    border-radius: 5px;
                    border: 1px solid #ddd;
                    padding: 10px;
                    background: #fff;
                    box-shadow: 0 2px 4px 0 rgba(0, 0, 0, 0.05);
                    height: 100%;
                    .title {
                        font-size: 17px;
                        color: #000;
                    }

                    .allUnitItems {
                        display: grid;
                        gap: 15px;
                        grid-template-columns: repeat(3, minmax(0, 1fr));
                        .unitItem {
                            border-radius: .25rem;
                            border: 1px solid #ddd;
                            padding: .25rem;
                            .action {
                                display: flex;
                                align-items: center;
                                justify-content: space-between;
                                .unitStar{
                                    input {
                                        opacity: 0;
                                        // cursor: pointer;
                                        z-index: 9;
                                        &:checked ~ {
                                            .starUnit{
                                                opacity: 1;
                                                visibility: visible;
                                                background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 24 24' xml:space='preserve' xmlns='http://www.w3.org/2000/svg' enable-background='new 0 0 24 24'%3E%3Cpath d='m12.9 2.6 2.3 5c.1.3.4.5.7.6l5.2.8c.9 0 1.2 1 .6 1.6l-3.8 3.9c-.2.2-.3.6-.3.9l.9 5.4c.1.8-.7 1.5-1.4 1.1l-4.7-2.6c-.3-.2-.6-.2-.9 0l-4.7 2.6c-.7.4-1.6-.2-1.4-1.1l.9-5.4c.1-.3-.1-.7-.3-.9l-3.8-3.9C1.7 10 2 9 2.8 8.9L8 8.1c.3 0 .6-.3.7-.6l2.3-5c.5-.7 1.5-.7 1.9.1z' fill='%23ff912c' class='fill-000000 fill-aaaaaa'%3E%3C/path%3E%3C/svg%3E");

                                            }
                                        }
                                    }
                                    .starUnit{
                                        width: 25px;
                                        height: 25px;
                                        background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 24 24' xml:space='preserve' xmlns='http://www.w3.org/2000/svg' enable-background='new 0 0 24 24'%3E%3Cpath d='m12.9 2.6 2.3 5c.1.3.4.5.7.6l5.2.8c.9 0 1.2 1 .6 1.6l-3.8 3.9c-.2.2-.3.6-.3.9l.9 5.4c.1.8-.7 1.5-1.4 1.1l-4.7-2.6c-.3-.2-.6-.2-.9 0l-4.7 2.6c-.7.4-1.6-.2-1.4-1.1l.9-5.4c.1-.3-.1-.7-.3-.9l-3.8-3.9C1.7 10 2 9 2.8 8.9L8 8.1c.3 0 .6-.3.7-.6l2.3-5c.5-.7 1.5-.7 1.9.1z' fill='%23aaaaaa' class='fill-000000'%3E%3C/path%3E%3C/svg%3E");
                                        background-position: center center;
                                        background-repeat: no-repeat;
                                        background-size: contain;
                                        opacity: 0;
                                        visibility: hidden;
                                    }
                                }
                                button {
                                    width: 25px;
                                    height: 25px;
                                    border-radius: .25rem;
                                    &.clearUnit{
                                       outline: none !important;
                                       box-shadow: none !important;
                                       background-image: url('data:image/svg+xml;base64,PHN2ZyB2aWV3Qm94PSIwIDAgMjAgMjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PHBhdGggZD0iTTIuOTMgMTcuMDdBMTAgMTAgMCAxIDEgMTcuMDcgMi45MyAxMCAxMCAwIDAgMSAyLjkzIDE3LjA3em0xLjQxLTEuNDFBOCA4IDAgMSAwIDE1LjY2IDQuMzQgOCA4IDAgMCAwIDQuMzQgMTUuNjZ6bTkuOS04LjQ5TDExLjQxIDEwbDIuODMgMi44My0xLjQxIDEuNDFMMTAgMTEuNDFsLTIuODMgMi44My0xLjQxLTEuNDFMOC41OSAxMCA1Ljc2IDcuMTdsMS40MS0xLjQxTDEwIDguNTlsMi44My0yLjgzIDEuNDEgMS40MXoiIGZpbGw9IiNlYTU0NTUiIGNsYXNzPSJmaWxsLTAwMDAwMCI+PC9wYXRoPjwvc3ZnPg==');
                                       background-position: center center;
                                       background-repeat: no-repeat;
                                       background-size: contain;
                                    }


                                }
                            }

                            span {
                                text-align: center;
                                display: block;
                                margin: 0 auto 10px;
                                font-size: 18px;
                            }

                            &:hover{
                                .action{
                                    .unitStar{
                                        input {

                                        &:checked ~ {
                                            .starUnit{
                                                background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 24 24' xml:space='preserve' xmlns='http://www.w3.org/2000/svg' enable-background='new 0 0 24 24'%3E%3Cpath d='m12.9 2.6 2.3 5c.1.3.4.5.7.6l5.2.8c.9 0 1.2 1 .6 1.6l-3.8 3.9c-.2.2-.3.6-.3.9l.9 5.4c.1.8-.7 1.5-1.4 1.1l-4.7-2.6c-.3-.2-.6-.2-.9 0l-4.7 2.6c-.7.4-1.6-.2-1.4-1.1l.9-5.4c.1-.3-.1-.7-.3-.9l-3.8-3.9C1.7 10 2 9 2.8 8.9L8 8.1c.3 0 .6-.3.7-.6l2.3-5c.5-.7 1.5-.7 1.9.1z' fill='%23ff912c' class='fill-000000 fill-aaaaaa'%3E%3C/path%3E%3C/svg%3E");
                                            }
                                        }
                                    }
                                        // .starUnit{
                                        //     opacity: 1;
                                        //     visibility: visible;
                                        // }
                                    }
                                }
                            }

                        }
                    }

                    .noUnits {
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        align-self: stretch;
                        height: 100%;
                        color: #999;
                        font-size: 16px;

                        .icon {
                            background-image: url("data:image/svg+xml, %3Csvg fill='%23999' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 480 480' xmlns:v='https://vecta.io/nano'%3E%3Cpath d='M304 304.808V232c0-13.232-10.768-24-24-24H56c-13.232 0-24 10.768-24 24v72.808C13.768 308.528 0 324.688 0 344v128c0 4.416 3.584 8 8 8h32c4.416 0 8-3.584 8-8v-24h240v24c0 4.416 3.584 8 8 8h32c4.416 0 8-3.584 8-8V344c0-19.312-13.768-35.472-32-39.192zM48 232c0-4.408 3.592-8 8-8h224c4.408 0 8 3.592 8 8v72h-16v-32c0-13.232-10.768-24-24-24h-48c-13.232 0-24 10.768-24 24v32h-16v-32c0-13.232-10.768-24-24-24H88c-13.232 0-24 10.768-24 24v32H48v-72zm208 40v32h-64v-32c0-4.408 3.592-8 8-8h48c4.408 0 8 3.592 8 8zm-112 0v32H80v-32c0-4.408 3.592-8 8-8h48c4.408 0 8 3.592 8 8zm176 192h-16v-24c0-4.416-3.584-8-8-8H40c-4.416 0-8 3.584-8 8v24H16v-64h304v64zm0-80H16v-40c0-13.232 10.768-24 24-24h256c13.232 0 24 10.768 24 24v40zm152-80H360c-4.416 0-8 3.584-8 8v120h16v-48h96v48h16V312c0-4.416-3.584-8-8-8zm-8 64h-96v-48h96v48zm-64-32h32v16h-32zm55.592-98.536l-16-48C438.496 186.2 435.448 184 432 184h-32a8 8 0 0 0-7.592 5.472l-16 48c-.816 2.44-.4 5.12 1.104 7.208S381.432 248 384 248h24v24h-16v16h48v-16h-16v-24h24a7.98 7.98 0 0 0 6.488-3.328c1.504-2.088 1.912-4.768 1.104-7.208zM395.096 232l10.672-32h20.472l10.664 32h-41.808zm80.176-127.304l-232-104a8.04 8.04 0 0 0-6.544 0l-232 104C1.848 105.992 0 108.848 0 112v96h16v-90.824L240 16.768l224 100.416V208h16v-96c0-3.152-1.848-6.008-4.728-7.304zm-180.048 5.976l-15.784 2.656A39.39 39.39 0 0 1 280 120c0 22.056-17.944 40-40 40s-40-17.944-40-40 17.944-40 40-40c4.576 0 9.072.768 13.344 2.28l5.328-15.088A55.82 55.82 0 0 0 240 64c-30.88 0-56 25.12-56 56s25.12 56 56 56 56-25.12 56-56a56.6 56.6 0 0 0-.776-9.328zm-4.88-44.328L240 116.688l-10.344-10.344-11.312 11.312 16 16a7.98 7.98 0 0 0 11.312 0l56-56-11.312-11.312z'/%3E%3C/svg%3E%0A");
                            width: 80px;
                            height: 80px;
                            background-position: center center;
                            background-repeat: no-repeat;
                            background-size: contain;
                        }
                    }

                }

                /* units_selection */
            }

            /* left_col */
        }

        /* row_cols */
        .total_result {
            border-radius: 5px;
            border: 1px solid #ddd;
            padding: 10px;
            background: #fff;
            box-shadow: 0 2px 4px 0 rgba(0, 0, 0, 0.05);
            margin: 0 auto 20px;

            .loader_item {
                padding: 50px;
            }

            /* loader_item */
            .top_rows {
                display: flex;
                flex-wrap: wrap;
                justify-content: space-between;
                align-items: center;

                .col_right {
                    span {
                        display: block;
                        font-size: 15px;
                        margin: 0 0 5px;
                        color: #000;
                    }

                    /* span */
                }

                /* col_right */
                .col_left {
                    font-size: 20px;
                    color: #000;
                    display: flex;
                    align-items: flex-end;
                    justify-content: flex-end;
                    direction: ltr;

                    p {
                        font-size: 35px;
                        margin: 0 7px 0 0;
                        line-height: 1.1;
                    }

                    /* p */
                }

                /* col_left */
            }

            /* top_rows */
            .bottom_rows {
                padding: 10px 0 0;
                margin: 10px 0 0;
                border-top: 1px solid #dddddd;
                display: flex;
                justify-content: flex-end;

                ul {
                    min-width: 33.3333%;
                    @media (min-width: 320px) and (max-width: 480px) {
                        min-width: 100%;
                        width: 100%;
                    }
                    /* Mobile */
                    @media (min-width: 481px) and (max-width: 767px) {
                        min-width: 100%;
                        width: 100%;
                    }
                    /* Mobile */
                    @media (min-width: 768px) and (max-width: 991px) {
                        min-width: 50%;
                        width: 50%;
                    }
                    /* Mobile */
                    li {
                        border-bottom: 1px solid #dddddd;
                        margin: 0 auto 10px;
                        padding: 0 0 10px;
                        display: flex;
                        justify-content: space-between;
                        align-items: center;

                        span {
                            display: block;
                            font-size: 15px;
                            color: #000;
                        }

                        /* span */
                        p {
                            display: flex;
                            align-items: center;
                            justify-content: flex-end;
                            font-size: 20px;
                            color: #000;
                            direction: ltr;

                            input {
                                background: #fafafa;
                                border: 1px solid #ddd !important;
                                color: #000;
                                margin: 0 8px 0 0;
                                min-width: 130px;
                                max-width: 100%;
                                text-align: center;
                                font-size: 20px;
                                display: block;
                                width: 130px;

                                &.total {
                                    margin: 0;
                                }
                            }

                            /* input */
                        }

                        /* p */
                        .total_ewa {
                            display: flex;
                            align-items: center;
                            justify-content: flex-end;
                            font-size: 20px;
                            color: #000;
                            direction: ltr;
                        }

                        /* total_ewa */
                        &:last-child {
                            margin: 0;
                            padding: 0;
                            border-bottom: none;
                        }

                        /* last-child  */
                    }

                    /* li */
                }

                /* ul */
            }

            /* bottom_rows */
        }

        /* total_result */
        .button_area {
            display: flex;
            justify-content: flex-end;

            button {
                background: #4099de;
                border-radius: 5px;
                border: 1px solid #4099de;
                min-width: 33.3333%;
                height: 35px;
                line-height: 35px;
                font-size: 15px;
                padding: 0 15px;
                color: #ffffff;
                -webkit-transition: all 0.2s ease-in-out;
                -moz-transition: all 0.2s ease-in-out;
                -o-transition: all 0.2s ease-in-out;
                transition: all 0.2s ease-in-out;
                @media (min-width: 320px) and (max-width: 480px) {
                    min-width: 100%;
                    width: 100%;
                }
                /* Mobile */
                @media (min-width: 481px) and (max-width: 767px) {
                    min-width: 100%;
                    width: 100%;
                }
                /* Mobile */
                @media (min-width: 768px) and (max-width: 991px) {
                    min-width: 50%;
                    width: 50%;
                }
                /* Mobile */
                &:hover {
                    background: #0071C9;
                    border-color: #0071C9;
                }

                /* hover */
            }

            /* button */
        }

        /* button_area */
    }

    /* new_reservation_page */

    .date_picker_alert {
        display: block;
        background: #fff3cd;
        border: 1px solid #ffeeba;
        color: #856404;
        padding: 10px;
        font-size: 15px;

        span {
            display: block;
            font-family: 'Dubai-Bold';
        }

        /* span */
    }

    /* date_picker_alert */

    .green_alert {
        display: block;
        background: #d4edda;
        border: 1px solid #c3e6cb;
        color: #155724;
        padding: 10px;
        font-size: 15px;

        span {
            display: block;
            font-family: 'Dubai-Bold';
        }

        /* span */
    }


    .gray_alert{

        display: block;
        background: #e6e6e6;
        border: 1px solid #c5c4c4;
        color: #767776;
        padding: 10px;
        font-size: 15px;

        span {
            display: block;
            font-family: 'Dubai-Bold';
        }

    }

    .switch_group {
      margin: 0 auto 15px;
      display: flex;
      align-items: center;
      justify-content: flex-start;
      flex-wrap: wrap;
      label {
        font-weight: bold;
        margin: 0;
        min-width: 130px;
      } /* label */
      .switch {
        position: relative;
        display: inline-block;
        width: 60px;
        height: 26px;
        input {
          opacity: 0;
          width: 100% !important;
          height: 100% !important;
          z-index: 99;
          position: relative;
          cursor: pointer;
          &:checked ~ {
            .slider {
              background-color: #21b978;
              &:before {
                -webkit-transform: translateX(33px);
                -ms-transform: translateX(33px);
                transform: translateX(33px);
              } /* before */
            } /* slider */
          } /* checked */
          &:focus {
            .slider {
              box-shadow: 0 0 1px #21b978;
            } /* slider */
          } /* focus */
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
          &:before {
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
            &:before {
              border-radius: 50%;
            } /* before */
          } /* round */
        } /* slider */
      } /* switch */
    } /* switch_group */

  
</style>
