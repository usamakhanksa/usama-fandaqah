<template>
    <div>
        <button class="Update_Reservation_Button"   @click="open"></button>
        <sweet-modal  :enable-mobile-fullscreen="false" :pulse-on-block="false" :title="__('Update Reservation')" overlay-theme="dark" ref="modal" @open="handelData" class="update_reservation_modal relative">
            <div v-if="loading" class="loding_spinner"><loader class="text-60" width="40"/></div>
            <loading :active.sync="price_loading" :is-full-page="false"></loading>


                <!-- :disabled-dates='{ weekdays: [1,2,3,4,5,6, 7] }'-->
                <div class="select_alert" v-if="hasInvoices">
                    {{__('Some inputs have been disabled and can not be edited cause reservation already have invoices')}}
                </div><!-- select_alert -->
                <div class="select_alert" v-if="unitHasChanged">
                    {{__('Please update your reservation first on the new unit , to be able to update other informations')}}
                </div><!-- select_alert -->

                <div class="rent_type">
                    <div class="name">{{__('Rent Type')}} :</div>
                    <div class="choose">
                        <label for="Daily" v-show="showDaily">
                            <input
                                type="radio"
                                id="daily"
                                value="1"
                                v-model="rent_type"
                                @change="updateRentType"
                                :disabled="hasInvoices || unitHasChanged ||
                                    ( !reservation.checked_in &&  !hasPermission('change reservation rent type before checkin'))
                                    || ( reservation.reservation_type == 'group' && reservation.all_grouped_reservations_ids.length > 1)
                                    || !can_edit_rent_after_checked_in
                                    "
                            >
                            <p>{{__('Daily')}}</p>
                        </label>
                        <label for="Monthly" v-if="this.unit && this.unit.prices.month">
                            <input
                                type="radio"
                                id="monthly"
                                value="2"
                                v-model="rent_type"
                                @change="updateRentType"
                                :disabled="hasInvoices || unitHasChanged ||
                                    ( !reservation.checked_in &&  !hasPermission('change reservation rent type before checkin'))
                                    || (reservation.reservation_type == 'group' && reservation.all_grouped_reservations_ids.length > 1)
                                    || !can_edit_rent_after_checked_in
                                    "
                            >
                            <p>{{__('Monthly')}}</p>
                        </label>
                    </div><!-- choose -->
                </div><!-- rent_type -->
                <div class="date_range">
                    <div class="name">{{ __('Select date range')}} :</div>
                    <vcc-date-picker
                        class='v-date-picker'
                        :locale="vcc_local"
                        mode='range'
                        v-model='selectedDate'
                        show-caps
                        is-expanded
                        :columns="$screens({ default: 1  })"
                        :popoverExpanded="true"
                        :disabled-dates='calendarDates'
                        @input="sendRange"
                        :popover="{
                            placement: 'bottom',
                            visibility:
                                hasInvoices ||
                                (!reservation.checked_in && !hasPermission('change reservation calendar date before checkin'))
                                || !can_edit_calendar_after_checked_in ? 'hide' : 'click' }"

                        :input-props="{disabled : hasInvoices ||
                            ( !reservation.checked_in &&  !hasPermission('change reservation calendar date before checkin'))
                            || !can_edit_calendar_after_checked_in
                         , readonly: true}"

                        ref="vcalendar"
                    >
                    </vcc-date-picker>
                    <!-- <vcc-date-picker
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
                    </vcc-date-picker> -->
                </div><!-- date_range -->
                <div class="change_unit">
                    <div class="name">{{ __('Unit')}} :</div>
                    <div class="choose">
                        <select :disabled="
                            ( !reservation.checked_in &&  !hasPermission('change reservation unit before checkin'))
                            || !can_edit_unit_after_checked_in
                        "
                        v-model="unit_id"
                        @change="onUnitChange($event.target.value)">
                            <!--  <option value="" selected="selected">{{ __('Choose a unit')}}</option> -->
                            <option :value="reservation.unit.id">{{reservation.unit.unit_number}} {{reservation.unit.name[locale]}}</option>
                            <option :value="unit.id" v-for="(unit , i) in units" :key="i" v-if="!unit.has_reservation">{{unit.unit_number}} {{unit.name}}</option>
                        </select>
                        <p>( {{ __('You can transfer the customer ...')}} )</p>
                    </div><!-- choose -->
                </div><!-- change_unit -->
                <div class="source_reservation">
                    <div class="name">{{__('Source')}} :</div>
                    <select v-model="source_id"
                    :disabled="( !reservation.checked_in &&  !hasPermission('change reservation source before checkin'))
                            || !can_edit_source_after_checked_in
                        ">
                        <option :value="source.id" :disabled="!source.status" v-for="(source, index) in sources" :key="index">{{source.name}}</option>
                    </select>
                </div><!-- source_reservation -->
                <div class="price_reservation" v-if="source_is_deleteable">
                     <div class="name">{{__('Source number')}} :</div>
                    <input style="cursor:text" type="text" v-model="source_num" :placeholder="__('Source number')">

                </div>
                <div class="price_reservation">
                    <div class="name">{{ __('Price Reservation')}} :</div>
                    <!-- <input type="number"  :disabled="hasInvoices || unitHasChanged" :readonly="priceFieldIsReadOnly"  v-model="price" min="1" @change="updateTotals" :placeholder="__('Price Reservation')"> -->
                    <div class="flex-1">
                        <input
                            type="text"
                            @keypress="isNumberKey"
                            :disabled="hasInvoices || unitHasChanged
                                        || ( !reservation.checked_in &&  !hasPermission('change reservation price before checkin'))
                                        || !can_edit_price_after_checked_in
                                        "

                            v-model="price"
                            @change="handlePriceChange"
                            :placeholder="__('Price Reservation')"
                            :min="totalLockedAmount"
                        >
                        <p class="helper-warning" v-if="this.totalLockedAmount">{{ __('Price reservation amount must not be lower or equal to') }} &nbsp; {{ totalLockedAmount }}</p>
                    </div>
                </div><!-- price_reservation -->
                <div class="transfer_reason" v-if="show_reason">
                    <div class="name">{{ __('Transfer Reason') }} :</div>
                    <div class="text_area">
                        <textarea v-model="reason" rows="2" cols="50" :placeholder="__('Transfer Reason')"></textarea>
                        <p>( {{__('All booking records ..')}} )</p>
                    </div><!-- text_area -->
                </div><!-- transfer_reason -->
                <div v-if="rent_type == '2'">
                    <div class="counter_days_month">
                        <div class="col">
                            <div class="counter_items">
                                <!-- <button @click="monthCountStepDown" :disabled="month_count <= 0 || (hasInvoices || unitHasChanged || !canChangeInputs || !canEditReservation || disableMinus)"></button> -->
                                <button @click="monthCountStepDown"
                                :disabled="
                                    (currentSubscription == 'monthly-yearly' ? month_count == 1 : month_count <= 0)
                                    ||  ( !reservation.checked_in &&  !hasPermission('change reservation calendar date before checkin'))
                                    || disableMonthMinus
                                    || !can_edit_calendar_after_checked_in
                                    "></button>


                                <input
                                    type="text"
                                    class="quantity"
                                    name="quantity"
                                    v-model="month_count"
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '').replace(/(\..*)\./g, '$1');"
                                    :disabled=" ( !reservation.checked_in &&  !hasPermission('extend reservation before checkin'))
                                    || !canExtendReservations
                                    || !can_edit_extend_after_checked_in
                                    "
                                    @change="updateRentType"
                                >

                                <button @click="monthCountStepUp" class="plus"
                                :disabled="( !reservation.checked_in &&  !hasPermission('extend reservation before checkin'))
                                    || !canExtendReservations
                                    || !can_edit_extend_after_checked_in
                                    "></button>
                            </div><!-- counter_items -->
                            <p>{{ __('month')}}</p>
                        </div><!-- col -->
                        <div class="col">
                            <div class="counter_items">
                                <button @click="nightCountStepDown"
                                :disabled="night_count <= 0
                                    || ( !reservation.checked_in &&  !hasPermission('change reservation calendar date before checkin'))
                                    || disableMinus
                                    || !canExtendReservations
                                    || !can_edit_calendar_after_checked_in
                                    "></button>

                                <input
                                    type="text"
                                    class="quantity"
                                    name="quantity"
                                    v-model="night_count"
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '').replace(/(\..*)\./g, '$1');"
                                    :disabled="( !reservation.checked_in &&  !hasPermission('change reservation calendar date before checkin'))
                                    || !canChangeInputs
                                    || !canExtendReservations
                                    || !can_edit_calendar_after_checked_in
                                    "
                                    @change="updateRentType"
                                >
                                <button @click="nightCountStepUp" class="plus"
                                :disabled="( !reservation.checked_in &&  !hasPermission('extend reservation before checkin'))
                                    || !canExtendReservations
                                    || !can_edit_extend_after_checked_in
                                    "></button>
                            </div><!-- counter_items -->
                            <p>{{ __('night')}}</p>
                        </div><!-- col -->
                    </div><!-- counter_days_month -->
                </div>
                <div class="counter_days" v-if="rent_type == '2'">
                    <div claRss="name">{{__('Nights')}} :</div>
                    <div class="night_number" v-if="unit && unit.reservation"
                    :disabled="( !reservation.checked_in &&  !hasPermission('change reservation calendar date before checkin'))
                        || !can_edit_calendar_after_checked_in
                        ">{{ unit.reservation.nights }} {{__('Night')}}</div>
                </div><!-- counter_days -->
                <div class="counter_days" v-else>
                    <div class="name">{{__('Nights')}} :</div>
                    <div class="counter_area">
                        <div class="counter_items">
                            <button @click="nightCountStepDown"
                            :disabled="night_count <= 0
                                || ( !reservation.checked_in &&  !hasPermission('change reservation calendar date before checkin'))
                                || disableMinus
                                || !can_edit_calendar_after_checked_in
                                "></button>

                            <input
                                type="text"
                                class="quantity"
                                name="quantity"
                                v-model="night_count"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '').replace(/(\..*)\./g, '$1');"
                                :disabled="
                                    hasInvoices
                                    || ( !reservation.checked_in &&  !hasPermission('change reservation calendar date before checkin'))
                                    || !canChangeInputs
                                    || !canExtendReservations
                                    || !can_edit_calendar_after_checked_in
                                    "
                                @change="nightsChanged"
                            >

                            <button
                                :disabled="( !reservation.checked_in &&  !hasPermission('extend reservation before checkin'))
                                    || !canExtendReservations
                                    || !can_edit_extend_after_checked_in
                                    " @click="nightCountStepUp" class="plus"></button>
                        </div>
                        <p>{{ __('night')}} </p>
                    </div><!-- counter_area -->
                </div><!-- number_month -->

                <!-- <div class="source_reservation">
                    <div class="name">{{__('Source')}} :</div>
                    <select v-model="source_id"
                    :disabled="( !reservation.checked_in &&  !hasPermission('change reservation source before checkin'))
                            || !can_edit_source_after_checked_in
                        ">
                        <option :value="source.id" :disabled="!source.status" v-for="(source, index) in sources" :key="index">{{source.name}}</option>
                    </select>
                </div> -->

                <div class="multi-select-services">
                     <!-- Reservation Services selector must be here  -->
                        <label>{{__('Services included in price')}} :</label>
                       <!-- select is here  -->
                       <vueMultiSelect
                        v-model="reservation_services_selected"
                        search
                        historyButton
                        :options="options"
                        :filters="filters"
                        :btnLabel="multi_select_btn_label"
                        :selectOptions="multi_select_data"/>
                </div>

                <button :disabled="disable_reservation" @click="send" class="update_reservation_button">{{__('Update Reservation')}}</button>

        </sweet-modal>
    </div>
</template>

<script>
    import vueMultiSelect from 'vue-multi-select';
    import Loading from 'vue-loading-overlay';
    import momenttimezone from 'moment-timezone'
    import 'vue-loading-overlay/dist/vue-loading.css';
    import Datetime from 'vue-datetime';
import { isBuffer } from 'lodash';

    export default {
        name: "EditReservation",
        props: ['reservation', 'quick' , 'occ'],
        components : {
            Loading,
            vueMultiSelect
        },
        data: () => {
            return {
                locale : Nova.config.local,
                rent_type: '1',
                month_count: 0,
                edit_price: false,
                specialPricesCollector: [],
                night_count: 1,
                old_night_count: 0,
                old_month_count: 0,
                calender_changed: 0,
                change_rate: 0,
                old_sub_total: 0,
                sources: [],
                unit_id: null,
                unit: null,
                source_id: null,
                source_num: null,
                source_is_deleteable : false,
                loading: false,
                disable_reservation: false,
                units: null,
                reason: '',
                disableDates: [],
                show_reason: false,
                selectedDate: [],
                date_in: null,
                date_out: null,
                price: null,
                prices: {
                    total_price: null,
                    total_price_raw: null,
                    total_ewa: null,
                    total_vat: null,
                    total_ttx : null ,
                    subtotal : null
                },
                new_price:{
                    price:null,
                    ewa:null,
                    vat:null,
                    total:null,
                },
                vat_parentage: 0,
                ewa_parentage: 0,
                vcc_local: {
                    id: Nova.config.local,
                    firstDayOfWeek: 1,
                    masks: {
                        weekdays: 'WWW',
                        input: ['WWWW YYYY/MM/DD', 'L'],
                        data: ['WWWW YYYY/MM/DD', 'L'],
                    }
                },
                invoices : [],
                hasInvoices : false,
                quickReservation : null,
                quickModal : {
                    from : null ,
                    modal : null
                },
                loader : false,
                priceFieldIsReadOnly : true,
                price_loading : false,
                last_reservation_id : null,
                hack_date_out : null,
                disable_plus : false,
                last_reservation_date_in : null,
                canChangeInputs : true,
                reservations_date : [],
                settingStartTime : null,
                cachedSelectedDate : {},
                showYesterdayNotification : false,
                unitHasChanged : false,
                canChangeUnit : true,
                showEditIcon : true,
                canEditReservation : false,
                calendarDates : [],
                disableMinus : false,
                invoicesWithoutCreditNotes : [],
                invalidPrice : false,
                canExtendReservations : true,
                transfer_with_same_price : 0,
                disableMonthMinus : false,
                showDaily : true,
                currentSubscription: null,
                can_edit_calendar_after_checked_in : true,
                can_edit_price_after_checked_in : true,
                can_edit_unit_after_checked_in : true,
                can_edit_source_after_checked_in : true,
                can_edit_extend_after_checked_in : true,
                can_edit_rent_after_checked_in : true,
                reservation_services_selected : [],
                multi_select_btn_label: reservation_services_selected => `${Nova.app.__('Selected Services')} (${reservation_services_selected.length})`,
                multi_select_data: [
                    {
                        reservation_services: [],
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
                    labelName: 'reservationServiceIdentifier',
                    labelList: 'reservation_services',
                    groupName: 'title',

                    cssSelected: option => (option.selected ? { 'background-color': '#4099de' , 'color': '#fff' } : ''),
                },
                change_night_loading: false,
                change_night_mode: '',
                team: Nova.app.currentTeam,
                totalLockedAmount: 0,
                priceChangeMode: '', //if night disabled total amount if empty keep it as edited from input field
                lockedDays: []

            }
        },
        methods: {
            hasPermission(permission){
               return Nova.app.$hasPermission(permission) ? true : false
            },
            monthCountStepDown(){
                this.price_loading = true;
                this.disable_reservation = true;



                if(this.month_count > 0){
                    this.old_month_count = this.month_count
                    this.month_count--;
                    this.calender_changed++
                    this.updateRentType()

                    if(this.reservation.reservation_type == 'group' && this.invoicesWithoutCreditNotes.length){

                        let lastInvoice =   this.invoicesWithoutCreditNotes[0];
                        let lastInvoiceDate = moment(lastInvoice.to).format('YYYY-MM-DD');
                        let lastSelectedEnd = moment(this.selectedDate.end).subtract(30 , 'days').format('YYYY-MM-DD');
                        if(lastInvoiceDate >= lastSelectedEnd){
                            this.disableMonthMinus = true;

                            var available_to_add_from = moment(lastInvoiceDate).add(1,'days').format('YYYY/MM/DD');
                            this.$toasted.show(this.__('Can not edit this reservation cause last invoice date is greater than the selected date out' , {available_to_add_from : available_to_add_from }), {type: 'error'});


                        }else{
                            this.disableMonthMinus = false;
                        }
                    }
                }
                if (this.currentSubscription == 'monthly-yearly' && this.month_count <= 0) {
                    this.month_count = 1;
                }
            },
            monthCountStepUp(){
                this.price_loading = true;
                this.disable_reservation = true;
                this.old_month_count = this.month_count
                this.month_count++;
                this.calender_changed++
                this.updateRentType()

                if(this.reservation.reservation_type == 'group' && this.invoicesWithoutCreditNotes.length){
                        if(this.reservation.reservation_type == 'group'){
                            let lastInvoice =   this.invoicesWithoutCreditNotes[0];
                            let lastInvoiceDate = moment(lastInvoice.to).add(1 , 'days').format('YYYY-MM-DD');
                            let lastSelectedEnd = moment(this.selectedDate.end).add(30, 'days').format('YYYY-MM-DD');

                            this.disableMonthMinus = false;
                            if(lastInvoiceDate >= lastSelectedEnd){
                                this.disableMinus = true;
                            }else{
                                this.disableMinus = false;
                            }
                        }
                }
            },
            async nightCountStepDown(){

                this.price_loading = true;
                this.change_night_loading = true;
                this.change_night_mode = 'reduce';
                this.priceChangeMode = 'nightCounter';
                this.disable_reservation = true;
                if(this.night_count > 0){

                    this.old_night_count = this.night_count
                    this.night_count--;
                    this.calender_changed++
                   await this.updateRentType();
                    // The part of invoices i will disable the minus button based on the following scenario
                    if(this.invoicesWithoutCreditNotes.length){
                        let lastInvoice =   this.invoicesWithoutCreditNotes[0];
                        let lastInvoiceDate = new Date(moment(lastInvoice.to).add(1 , 'days')).getDate();
                        let lastSelectedEnd = new Date(moment(this.selectedDate.end).subtract(1 , 'days')).getDate();
                        if(lastInvoiceDate === lastSelectedEnd){
                            this.disableMinus = true;
                        }else{
                            this.disableMinus = false;
                        }
                    }
                }
            },
            nightCountStepUp(){
                // const self = this;
                // i need a pre-caution step here before incrementing if the full period of the reservation lies inisde an invoice then i will disable plus
                // if(this.reservation.reservation_type == 'group'){
                //     // its a group reservation
                //     var lastGroupInvoice =   this.invoicesWithoutCreditNotes[0];
                //     var theEndDateOfReservation  = moment(self.selectedDate.end).subtract(1,'days').format('YYYY-MM-DD');
                //     console.log(lastGroupInvoice);
                //     console.log(theEndDateOfReservation);
                //     if(lastGroupInvoice.to > theEndDateOfReservation ){
                //         self.disableMinus = true;
                //         self.disable_plus = true;
                //     }else{
                //         self.disableMinus = false;
                //         self.disable_plus = false;
                //     }
                // }

                this.price_loading = true;
                this.change_night_loading = true;
                this.change_night_mode = 'addition';
                this.priceChangeMode = 'nightCounter'
                this.disable_reservation = true;
                this.old_night_count = this.night_count
                this.night_count++;
                this.calender_changed++
                this.updateRentType()

                // if(this.reservation.reservation_type == 'single'){
                    if(this.invoicesWithoutCreditNotes.length){
                        if(this.reservation.reservation_type == 'group'){
                            this.disableMinus = false;
                        }
                        if(this.reservation.reservation_type == 'single'){
                            let lastInvoice =   this.invoicesWithoutCreditNotes[0];
                            let lastInvoiceDate = new Date(moment(lastInvoice.to)).getDate();
                            let lastSelectedEnd = new Date(moment(this.selectedDate.end).subtract(1 , 'days')).getDate();
                            if(lastInvoiceDate != lastSelectedEnd){
                                this.disableMinus = false;
                            }else{
                                this.disableMinus = true;
                            }
                        }

                    }
                // }

            },
            nightsChanged(){
                this.price_loading = true;
                this.disable_reservation = true;
                this.old_night_count = this.night_count
                this.calender_changed++
                this.updateRentType();
            },
            open() {
                this.unitHasChanged = false;
                this.show_reason = false;
                // if(Nova.app.$hasPermission('reservation price')){
                //     this.priceFieldIsReadOnly = false;
                // }
                // if(Nova.app.$hasPermission('edit reservations')){
                //     this.canEditReservation = true;
                // }
                // if(!Nova.app.$hasPermission('change unit')){
                //     this.canChangeUnit = false;
                // }
                let arr = [];
                const self = this;

                if(this.reservation.reservation_free_services.length){
                    this.reservation.reservation_free_services.map(function(mapperService, key) {
                        let obj = {};
                        obj.reservationServiceIdentifier = self.locale == 'ar' ?  mapperService.service.name_ar  : mapperService.service.name_en;
                        obj.id = mapperService.service.id;
                        arr.push(obj);
                    });

                    this.reservation_services_selected = arr;

                }


                this.getSources();
                this.rent_type = this.reservation.rent_type;
                this.old_sub_total = this.reservation.prices.sub_total;
                this.invoices = this.reservation.invoices ;
                this.getReservationsDate();
                Nova.request().get('/nova-vendor/calender/unit/getTeamSettingDayStart?unit_id=' + this.unit_id)
                .then(res => {

                    // waiting unit to be loaded - task in basecamp ( day start based on day start coming from settings )
                        let currentDate  =momenttimezone().tz("Asia/Riyadh").format("YYYY-MM-DD");
                        let startDate =  moment(String(this.$route.params.date)).format("YYYY-MM-DD") ;
                        let currentTime  =   momenttimezone().tz("Asia/Riyadh").format('HH:mm');
                        let settingDayStartTime = res.data.day_start ;

                        this.transfer_with_same_price  = res.data.transfer_with_same_price ? res.data.transfer_with_same_price : 0 ;

                        if( (settingDayStartTime >  currentTime) ){


                            this.showYesterdayNotification = true;
                            this.selectedDate = {
                                start:  new Date(moment(String(this.reservation.date_in)).toISOString()),
                                end: new Date(moment(moment(String(this.reservation.date_out)).toISOString()))
                            };

                            this.cachedSelectedDate = {
                                start:  new Date(moment(String(this.reservation.date_in)).toISOString()),
                                end: new Date(moment(moment(String(this.reservation.date_out)).toISOString()))
                            };
                        }
                })

                this.rent_type = this.reservation.rent_type;
                this.quickModal.from = this.$refs.from;
                this.quickModal.modal = this.$refs.modal;

                if(this.quick){
                    if(this.reservation.id === this.quickReservation.id){
                        this.quickModal.modal.open();
                    }
                }else{
                    this.quickModal.modal.open();
                }

                this.getLockedAmount()

                // this.$refs.modal.open()
            },
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
                        this.multi_select_data[0].reservation_services = arr;
                    }
                })

            },

            getUnitsAvailable() {
                this.disable_reservation = false;
                Nova.request()
                    .get('/nova-vendor/calender/units/available',{
                        params: {
                            date_in: this.date_in,
                            date_out: this.date_out,
                            plus: [this.reservation.unit.id],
                        }
                    })
                    .then(response => {
                        this.units = response.data

                        this.loading = false;
                    }).catch(err => {
                    this.loading = false;
                    this.$toasted.show(this.__(err), {type: 'error'})
                })
            },
            sendRange(date) {

                this.date_in = moment(String(date.start)).format('YYYY-MM-DD');
                this.date_out = moment(String(date.end)).format('YYYY-MM-DD');

                if (this.date_in == this.date_out) {
                    this.selectedDate = {
                        start: new Date(moment(String(this.reservation.date_in)).toISOString()),
                        end: new Date(moment(String(this.reservation.date_out)).toISOString())
                    };
                    return;
                }


                let startDate = new Date(this.selectedDate.start);
                let today = new Date().setHours(0,0,0,0);

                let currentTime  =   momenttimezone().tz("Asia/Riyadh").format('HH:mm');

                let selectedStart = momenttimezone(new Date(this.selectedDate.start).setHours(0,0,0,0)).tz("Asia/Riyadh").format("YYYY-MM-DD")
                let currentDate = momenttimezone().tz("Asia/Riyadh").format("YYYY-MM-DD");
                if(new Date(this.selectedDate.start).setHours(0,0,0,0) < new Date(this.reservation.date_in).setHours(0,0,0,0) && !(selectedStart >= currentDate)  && !Nova.app.$hasPermission('booking past') && !this.showYesterdayNotification){

                            this.$toasted.show(this.__('You can not book in the past'), {type: 'error'})
                            this.selectedDate =  {
                                start: new Date(moment(String(this.reservation.date_in)).toISOString()),
                                end: new Date(moment(String(this.reservation.date_out)).toISOString())
                            }




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


                // if(startDate < today && !Nova.app.$hasPermission('booking past')){
                //         this.$toasted.show(this.__('You can not book in the past'), {type: 'error'})
                //         this.selectedDate = {
                //         start: new Date(moment(String(this.reservation.date_in)).toISOString()),
                //         end: new Date(moment(String(this.reservation.date_out)).toISOString())
                //     };
                //     return ;
                // }
                if(this.invoicesWithoutCreditNotes.length && this.reservation.reservation_type == 'group'){
                    var theStartDateOfReservation  = moment(this.selectedDate.start).format('YYYY-MM-DD');
                    var theEndDateOfReservation  = moment(this.selectedDate.end).format('YYYY-MM-DD');
                    var invoices_from_to_holder = [];
                    this.invoicesWithoutCreditNotes.forEach(function(invoice) {
                        invoices_from_to_holder.push(invoice.from);
                        invoices_from_to_holder.push(invoice.to);
                    });
                    // sort the array to be able to fetch min and max
                    invoices_from_to_holder =  _.sortBy(invoices_from_to_holder);
                    var min_invoice_from_date = invoices_from_to_holder[0];
                    var max_invoice_from_date = invoices_from_to_holder[invoices_from_to_holder.length -1];



                    var rangeOfDates =  this.getDatesBetweenDates(min_invoice_from_date,max_invoice_from_date);

                    if(theStartDateOfReservation < min_invoice_from_date){
                        this.$toasted.show(Nova.app.__( "The Start Date Must Be greater than the first invoice"),
                            {
                                    duration : 4000,
                                    type: 'error',
                                    position : 'top-center',

                            }
                        );
                        this.handelData();
                        return;
                    }

                    if(rangeOfDates.includes(theStartDateOfReservation) && rangeOfDates.includes(theEndDateOfReservation)){
                        this.$toasted.show(Nova.app.__( "The Selected Period Intersects with an invoice"),
                            {
                                    duration : 4000,
                                    type: 'error',
                                    position : 'top-center',

                            }
                        );
                        this.handelData();
                        return;
                    }


                }

                this.getUnitsAvailable();
                this.$emit('setRange', this.date);
                this.onUnitChange(this.reservation.unit.id);
            },
            getDatesBetweenDates(startDate, endDate){
                let dates = []
                //to avoid modifying the original date
                const theDate = new Date(startDate)
                while (theDate < new Date(endDate)) {
                    dates = [...dates, moment(new Date(theDate)).format('YYYY-MM-DD')]
                    theDate.setDate(theDate.getDate() + 1)
                }
                dates = [...dates, moment(new Date(endDate)).format('YYYY-MM-DD')]
                return dates
            },
            getPricingChangeRate(newPrice, oldPrice) {
                return ((newPrice / oldPrice) - 1) * 100
            },
            send() {
                if(parseFloat(this.price) < parseFloat(this.totalLockedAmount)) {
                    this.$toasted.show(this.__('Amount must not be subceeded from the locked amount'), {type: 'error'});
                    return;
                }
                if(this.checkIfDateHasInvoice()) {
                    this.$toasted.show(this.__('Selected dates already have an invoice'), {type: 'error'});
                    return;
                }
                if (this.show_reason) {
                    if(this.reason == 0 || this.reason == '0' || !this.reason){
                        this.$toasted.show(this.__('Please fill Transfer Reason'), {type: 'error'})
                        return;
                    }

                }
                if(this.reservation.reservation_type == 'group' && this.invoicesWithoutCreditNotes.length){
                    let lastInvoice =   this.invoicesWithoutCreditNotes[0];
                        let lastInvoiceDate = moment(lastInvoice.to).format('YYYY-MM-DD');
                        let lastSelectedEnd = moment(this.selectedDate.end).format('YYYY-MM-DD');

                        if(lastInvoiceDate >= lastSelectedEnd){
                            this.disableMonthMinus = true;
                            this.$toasted.show(this.__('Can not edit this reservation cause last invoice date is greater than the selected date out'), {type: 'error'});
                            return;
                        }
                }

                this.price = this.fixNumbers(this.price);
                this.price = Number.parseFloat(this.price);

                if(this.invalidPrice || isNaN(this.price)){
                    this.$toasted.show(this.__('Invalid Price, Please Enter Valid Number'), {type: 'error'})
                    this.price = Number.parseFloat(this.reservation.total_price);
                    return;
                }

                this.updateTotals();

                if (!Nova.app.$hasPermission('booking without min price')) {

                    let change_rate = this.getPricingChangeRate(this.prices.subtotal, this.unit.reservation.prices.min_sub_total);
                    if (change_rate < 0) {
                        this.$toasted.show(this.__('Less than the allowed price'), {type: 'error'});
                        return;
                    }
                }

                // if(this.source_num){
                //     if(!this.alphanumeric(this.source_num)){
                //        this.$toasted.show(this.__('Source Num , Please input alphanumeric characters only'), {type: 'error'})
                //         return ;

                //     }
                // }

                if (!Nova.app.$hasPermission('booking without min price')) {
                    let change_rate = this.getPricingChangeRate(this.subtotal_price, this.unit.reservation.prices.min_sub_total);
                    ;
                    if (change_rate < 0) {
                        this.$toasted.show(this.__('Less than the allowed price'), {type: 'error'});
                        return;
                    }
                }



                this.loading = true;

                axios
                    .put('/nova-vendor/calender/reservation/update_reservation', {
                        id: this.reservation.id,
                        date_in: this.date_in,
                        specialPricesCollector : this.specialPricesCollector,

                        date_out: this.date_out,
                        source_id: this.source_id,
                        source_num : this.source_num,
                        unit_id: this.unit_id,
                        prices: this.prices,
                        reason: this.reason,
                        rent_type: this.rent_type,
                        reservation_services_selected : this.reservation_services_selected,
                        price_change_mode: this.priceChangeMode,  //input or nightCounter,
                        total_locked_amount: this.totalLockedAmount,
                        locked_days: this.lockedDays
                    })
                    .then(response => {
                        Nova.$emit('update-reservation')
                        Nova.$emit('set-invoice-calendar');
                        if(this.quick){
                            if(this.reservation.id === this.quickReservation.id){
                                this.quickModal.modal.close();
                            }
                        }else{
                            this.quickModal.modal.close();
                        }
                        // this.$refs.modal.close();
                        this.$toasted.show(this.__('Reservation updated successfully'), {
                            type: 'success',
                            duration : 2000
                        });
                        this.loading = false;
                    }).catch(err => {
                    this.loading = false;
                    this.$toasted.show(this.__(err), {type: 'error'})
                })
                this.reason = ""
                this.show_reason = false
            },
            // TODO edit modal work
            handleCalendarDates(unit,current_reservation){

                this.calendarDates = unit.reservations_date.map(function(x) {
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

                // This step is needed to remove disable from the current reservation date_in so that i can extend it
                if(this.unit.id == current_reservation.unit_id){
                    // i will exclude current date_in from the calendarDates
                    this.calendarDates = this.calendarDates.filter(date => Date.parse(date.start) != Date.parse(current_reservation.date_in));
                }

                // This step helps me open other reservations start date so that i can select them as end date for current reservation
                axios.get(`/nova-vendor/calender/getLastReservationId?unit_id=${this.unit.id}&date_out=${moment(this.selectedDate.end).format('Y-MM-DD')}&date_in=${moment(this.selectedDate.start).format('Y-MM-DD')}`)
                .then((res) => {

                    this.last_reservation_id = res.data.id;
                    this.last_reservation_date_in = res.data.date_in;

                    if(this.last_reservation_date_in){

                        let next_date_in = new Date(this.last_reservation_date_in);

                        for( var i = 0; i < this.calendarDates.length; i++){
                            if ( Date.parse(this.calendarDates[i].start) === Date.parse(next_date_in) ) {
                                this.calendarDates.splice(i, 1);
                            }
                        }
                    }

                })

            },
            onUnitChange(unit_id) {

                this.disable_reservation = false;
                this.price_loading = true;
                this.show_reason = (unit_id != this.reservation.unit.id)

                this.disableDates = [];
                axios.get('/nova-vendor/calender/unit/' + unit_id + '/' + this.date_in + '/' + this.date_out, {
                    params: {
                        rent_type: this.rent_type,
                        reservation_id: this.reservation.id,
                        transfer_with_same_price : this.transfer_with_same_price
                    }
                })
                    .then(response => {
                        if(response.data.status == 'new_unit_still_has_checked_in_reservation'){
                            this.price_loading = false;
                            this.unitHasChanged = false;
                            this.unit_id = this.reservation.unit_id;
                            this.show_reason = false;
                             this.$toasted.show(this.__('There is a checked-in reservation on this unit'), {type: 'error'});
                             return;
                        }
                        this.unit = response.data;
                        if(this.reservation.unit.id != this.unit.id){
                            this.unitHasChanged = true;
                            this.price = this.unit.reservation.prices.total_price_raw
                            this.prices = this.unit.reservation.prices
                            // this.disableUnitDate(this.reservations_date)
                        } else {
                            this.unitHasChanged = false;
                            this.old_sub_total = this.unit.reservation.prices.sub_total
                            // this.disableUnitDate(this.reservation.unit.reservations_date, this.reservation.date_in)

                            // this.disableUnitDate(this.reservations_date, this.reservation.date_in)
                        }


                        // this.handleCalendarDates(this.unit,this.reservation);


                        this.loading = false;

                        // check if overlapped
                        if(this.reservation.unit.id != this.unit.id){
                            if(this.unit.has_reservation > 0){
                                this.disable_reservation = true;
                                this.$toasted.show(this.__('This unit has a reservation that intersect with selected dates !'), {type: 'error'})
                            } else {
                                this.disable_reservation = false;
                            }

                             setTimeout(() => {
                                     this.price_loading = false;
                                }, 200);

                        }else{

                            let start = moment(String(this.selectedDate['start'])).format('YYYY-MM-DD') ;
                            let end = moment(String(this.selectedDate['end'])).format('YYYY-MM-DD') ;



                             axios.get(`/nova-vendor/calender/overlap-check?start=${start}&end=${end}&reservation_id=${this.reservation.id}&unit_id=${this.unit.id}`)
                            .then(response => {
                                if(!response.data.has_reservation){
                                    let price = this.reservation.total_price / this.reservation.nights;
                                    //!settings.calculateWithRespectToChangeRate ->
                                    if(this.team.check_calculate_price_by_day_enable) {
                                        //will only execute when night increased
                                        //total_nights contains updated price
                                        if(this.change_night_loading) {
                                            if(this.change_night_mode == 'reduce') {
                                                let insertedDate = moment(this.selectedDate.end).format('YYYY/MM/DD');
                                                let currentDay = this.reservation.prices.days.find((day) => {
                                                    if(day.date === insertedDate) {
                                                        return day;
                                                    }
                                                })

                                                if(!currentDay) {
                                                    currentDay = {
                                                        price: 0
                                                    };
                                                    let insertedDate = moment(this.selectedDate.end).format('dddd');
                                                    insertedDate = insertedDate.toLowerCase() + '_day_price';
                                                    currentDay.price = this.reservation.old_prices.prices.day[insertedDate];
                                                }

                                                const ewa_price = (Number(currentDay.price) / 100) * Number(this.reservation.old_prices.ewa_parentage);
                                                const sub_total_with_ewa_day = ewa_price + Number(currentDay.price);
                                                const vat_price = (sub_total_with_ewa_day / 100) * Number(this.reservation.old_prices.vat_parentage);
                                                const total_day = sub_total_with_ewa_day + vat_price;

                                                const total_price = (Number(this.reservation.prices.total_price) - total_day) / this.night_count;

                                                price = total_price;
                                            } else {
                                                let insertedDate = moment(this.selectedDate.end).format('dddd');
                                                insertedDate = insertedDate.toLowerCase() + '_day_price';
                                                const ewa_price = (Number(this.reservation.old_prices.prices.day[insertedDate]) / 100) * Number(this.reservation.old_prices.ewa_parentage);
                                                const sub_total_with_ewa_day = ewa_price + Number(this.reservation.old_prices.prices.day[insertedDate]);
                                                const vat_price = (sub_total_with_ewa_day / 100) * Number(this.reservation.old_prices.vat_parentage);
                                                const total_day = sub_total_with_ewa_day + vat_price;

                                                const total_price = (Number(this.reservation.prices.total_price) + total_day) / this.night_count;

                                                price = total_price;
                                            }

                                            this.change_night_loading = false;
                                        }
                                    }
                                    this.total_nights = price * this.night_count ;
                                    if(start === this.reservation.date_in && end === this.reservation.date_out && this.reservation.rent_type == this.rent_type){
                                        this.price = Number(this.reservation.total_price).toFixed(2);
                                        this.prices.total_price = Number(this.reservation.total_price).toFixed(2);
                                    }else{
                                        if(this.rent_type == 2){
                                          this.price = this.unit.reservation.prices.total_price_raw;
                                        this.prices.total_ttx = 0 ;
                                        this.updateTotals();

                                    }else{

                                        this.price = Number(this.total_nights).toFixed(2);
                                        this.prices.total_ttx = 0 ;
                                        this.updateTotals();
                                    }

                                    }
                                }else{
                                    let nights = 0,
                                    new_date = new Date(this.reservation.date_in),
                                    start = new Date(this.reservation.date_in);

                                    if(this.rent_type == 2){
                                        if (this.month_count == 0) {
                                            this.month_count = 1;
                                            this.night_count = 0;
                                        }
                                        nights = (this.month_count *30) + Number(this.night_count);
                                    } else{
                                        this.night_count = this.reservation.nights;
                                        nights = (this.month_count *0) + Number(this.night_count);
                                    }
                                    let end_data = new Date(new_date.setDate(new_date.getDate() + nights));

                                    let date = {
                                        start: start,
                                        end: end_data
                                    };
                                    this.selectedDate = date;
                                    this.$toasted.show(this.__('This unit has a reservation that intersect with selected dates !'), {type: 'error'})
                                }



                            let start = moment(String(this.selectedDate['start'])).format('YYYY-MM-DD') ;
                            let end = moment(String(this.selectedDate['end'])).format('YYYY-MM-DD') ;

                            let specialPricesSum = 0 ;
                            // console.log(this.reservation);
                            if(this.reservation.special_prices && this.reservation.special_prices != null){
                                let specialPrices = JSON.parse(this.reservation.special_prices);
                                if(specialPrices != null){
                                     specialPrices.forEach(function(specialPrice) {
                                        specialPricesSum += Number(specialPrice.specialPrice);
                                    });
                                }

                            }

                            if(Math.abs(specialPricesSum - this.reservation.sub_total) < 1){
                                this.edit_price = true;
                            }else{
                                this.edit_price = false;
                            }
                            axios.get(`/nova-vendor/calender/unit/${this.unit.id}/get-special-prices/${start}/${end}`)
                            .then(response => {
                                if(response.data.status === 'special_prices_found' && this.edit_price){

                                    this.specialPrices = response.data.special_prices;

                                    this.datesHasSpecialPrice = response.data.datesHasSpecialPrice;
                                    this.datesDoesntHaveSpecialPrice = response.data.datesDoesntHaveSpecialPrice;
                                        // console.log(response.data)
                                        const uniqueDates = new Set();
                                        for (let i = 0; i < this.datesHasSpecialPrice.length; i++) {
                                            const specialDate = this.datesHasSpecialPrice[i].date;

                                            for (let j = 0; j < response.data.incomingDates.length; j++) {
                                                const incomingDate = response.data.incomingDates[j];

                                                // Compare dates and check if the date hasn't been added already
                                                if (specialDate === incomingDate && !uniqueDates.has(incomingDate)) {
                                                    // Add special price to incoming date
                                                    this.specialPricesCollector.push({
                                                        date: incomingDate,
                                                        specialPrice: this.datesHasSpecialPrice[i].price
                                                    });

                                                    // Add the date to the set of unique dates
                                                    uniqueDates.add(incomingDate);
                                                }
                                            }
                                        }

                                        // Add dates from datesDoesntHaveSpecialPrice
                                        for (let i = 0; i < this.datesDoesntHaveSpecialPrice.length; i++) {
                                            const date = this.datesDoesntHaveSpecialPrice[i].date;

                                            for(let j = 0; j < response.data.incomingDates.length; j++) {
                                            const incomingDate = response.data.incomingDates[j];

                                            if (date === incomingDate && !uniqueDates.has(incomingDate)) {
                                                this.specialPricesCollector.push({
                                                date: incomingDate,
                                                specialPrice: this.datesDoesntHaveSpecialPrice[i].price
                                                });

                                                uniqueDates.add(incomingDate);
                                            }
                                            }
                                        }

                                        // remove the repeated dates records in the special prices collector
                                        this.specialPricesCollector = this.specialPricesCollector.filter((thing, index, self) =>
                                            index === self.findIndex((t) => (
                                                t.date === thing.date
                                            ))
                                        )


                                        this.total_price =  (response.data.total_price).toFixed(2);
                                        this.subtotal_price = response.data.subtotal;
                                        this.total_ewa =  response.data.ewaTotal;
                                        this.total_vat =  response.data.vatTotal;
                                        this.total_ttx =  response.data.ttxTotal;


                                        this.unit.reservation.prices.sub_total = response.data.subtotal;
                                        this.unit.reservation.prices.total_ewa = response.data.ewaTotal;
                                        this.unit.reservation.prices.total_vat = response.data.vatTotal;
                                        this.unit.reservation.prices.total_tourism = response.data.ttxTotal;
                                        this.unit.reservation.prices.total_price = this.total_price;
                                        this.unit.reservation.prices.total_price_raw = this.total_price;

                                        this.unit.reservation.prices.price = response.data.subtotal
                                        this.price = this.total_price;

                                }else{
                                        let unitCategoryDaysPrices = response.data.unitCategoryDaysPrices;
                                        this.specialPricesCollector = [];
                                        for(let i = 0 ; i < unitCategoryDaysPrices.length ; i++){
                                            this.specialPricesCollector.push({
                                                date : unitCategoryDaysPrices[i].date ,
                                                specialPrice : unitCategoryDaysPrices[i].price
                                            });
                                        }


                                }
                            });
                            setTimeout(() => {
                                    this.price_loading = false;
                            }, 200);


                            });

                            return;
                        }
                    })
            },
            updateRentType() {

                if(!this.canChangeInputs){
                    this.night_count = this.old_night_count;
                    this.month_count = this.old_month_count;
                    this.$toasted.show(this.__('This unit has a reservation that intersect with selected dates !'), {type: 'error'})
                    return false;
                }

                let nights = 0,
                    new_date = new Date(moment(this.selectedDate.start).format('YYYY-MM-DD')),
                    start = new Date(moment(this.selectedDate.start).format('YYYY-MM-DD'))
                ;


                if(this.rent_type == 2){
                    if (this.month_count == 0) {
                        this.month_count = 1;
                        this.night_count = 0;
                    }
                    nights = (this.month_count *30) + Number(this.night_count);
                } else{
                    nights = (this.month_count *0) + Number(this.night_count);
                }
                let end_data = new Date(new_date.setDate(new_date.getDate() + nights));

                let date = {
                    start: start,
                    end: end_data
                };

                //   this.selectedDate = date;
                  axios.get(`/nova-vendor/calender/overlap-check?start=${moment(start).format('YYYY-MM-DD')}&end=${moment(end_data).format('YYYY-MM-DD')}&reservation_id=${this.reservation.id}&unit_id=${this.unit.id}`)
                    .then(response => {
                        if(!response.data.has_reservation){
                            this.selectedDate = date;
                        }else{
                            this.night_count = this.old_night_count;
                            this.month_count = this.old_month_count;
                            this.rent_type = this.reservation.rent_type;
                            this.$toasted.show(this.__('This unit has a reservation that intersect with selected dates !'), {type: 'error'})
                        }
                        this.disable_reservation = false;
                    });


                // if(!this.dateCheck(start, end_data)){
                //     this.selectedDate = date;

                // }else{
                //     this.night_count = this.old_night_count;
                //     this.month_count = this.old_month_count;

                //     this.$toasted.show(this.__('This unit has a reservation that intersect with selected dates !'), {type: 'error'})
                // }

                // this.selectedDate = date;
            },
            dateCheck(start, end) {
                this.disable_plus = false;
                var ccc = false;
                // let self = this;
                var last    = this.disableDates.pop();

                this.disableDates.forEach(function(element) {
                    if((start <= element.end) && (element.start <= end) ) {
                        ccc = true;
                    }
                });

                if(this.last_reservation_id && this.last_reservation_id != this.reservation.id){
                    // console.log(this.last_reservation_id)
                    // console.log(this.last_reservation_date_in)

                    if(Date.parse(moment(end).format('Y-MM-DD')) > Date.parse(this.last_reservation_date_in)){

                        this.disable_plus = true
                        ccc = true;
                    }else{
                        return false;
                    }

                    if(last){
                        if(last.start == 1){
                            return ccc ;
                        }
                    }
                }


                return ccc;
            },
            disableUnitDate(reservations_date, date_in) {

                if(date_in){
                    reservations_date = reservations_date.filter(reservations => reservations.date_out < date_in);
                }
                let self = this;
                this.disableDates = reservations_date.map(function(x) {
                    if(x.checked_out == null){
                        let start = new Date(x.date_in);
                        let end = new Date(x.date_out);
                        return {
                            start: start,
                            end: end
                        }

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
                // console.log(this.disableDates);
            },
            checkInvoicesFirst(){
                let main_reservation_id = null;
                if(this.reservation.reservation_type =='group'){
                    if(this.reservation.attachable_id) {
                        main_reservation_id = this.reservation.attachable_id;
                    }else{
                        main_reservation_id = this.reservation.id;
                    }
                }
                axios.get(`/nova-vendor/calender/reservation/${this.reservation.id}/get-invoices?type=${this.reservation.reservation_type}&main_reservation_id=${main_reservation_id}`)
                .then((response) => {
                    if(response.data.length){
                        let holder_invoices = _.filter(response.data, function(invoice) {
                            return invoice.invoice_credit_note === null;
                        });

                        if(holder_invoices.length){
                            this.invoicesWithoutCreditNotes = holder_invoices;
                            // this.hasInvoices = true;
                            var lastGroupInvoice = this.invoicesWithoutCreditNotes[0];
                            var theStartDateOfReservation  = moment(this.selectedDate.start).format('YYYY-MM-DD');
                            var theEndDateOfReservation  = moment(this.selectedDate.end).subtract(1,'days').format('YYYY-MM-DD');

                            const self = this;
                            if(this.reservation.reservation_type == 'group'){

                                // For saftey minus logic must not depend on the first potential invoices cause its an include
                                // so i need to ierate the invoices , get periods push dates then check includes
                                var invoices_from_to_holder = [];
                                this.invoicesWithoutCreditNotes.forEach(function(invoice) {
                                    invoices_from_to_holder.push(invoice.from);
                                    invoices_from_to_holder.push(invoice.to);
                                });
                                // sort the array to be able to fetch min and max
                                invoices_from_to_holder =  _.sortBy(invoices_from_to_holder);
                                var min_invoice_from_date = invoices_from_to_holder[0];
                                var max_invoice_from_date = invoices_from_to_holder[invoices_from_to_holder.length -1];

                                var rangeOfDates =  this.getDatesBetweenDates(min_invoice_from_date,max_invoice_from_date);

                                if(rangeOfDates.includes(theStartDateOfReservation)){
                                    this.hasInvoices = true;
                                }else{
                                    this.hasInvoices = false;
                                }

                                if(max_invoice_from_date >= theEndDateOfReservation){
                                    self.disableMinus = true;
                                }else{
                                    self.disableMinus = false;
                                }
                                // if(lastGroupInvoice.data.periods[self.reservation.id] && lastGroupInvoice.data.periods[self.reservation.id].includes(theEndDateOfReservation)){
                                //     self.disableMinus = true;
                                // }else{
                                //     self.disableMinus = false;
                                // }
                                // we can disable the plus button also if the full period lies in an invoice
                                if(lastGroupInvoice.to > theEndDateOfReservation ){

                                    self.canExtendReservations = false;
                                }else{
                                    self.canExtendReservations = true;
                                }

                            }

                            if(this.reservation.reservation_type == 'single'){
                                this.hasInvoices = true;
                                let lastInvoice =   holder_invoices[0];
                                let lastInvoiceDate = new Date(moment(lastInvoice.to)).getDate();
                                let lastSelectedEnd = new Date(moment(this.selectedDate.end).subtract(1 , 'days')).getDate();

                                if(lastInvoiceDate === lastSelectedEnd){
                                    this.disableMinus = true;
                                }else{
                                    this.disableMinus = false;
                                }
                            }

                        }else{
                            this.hasInvoices = false;
                            this.disableMinus = false;
                        }
                    }

                })

            },
            parseNumber(numberText) {
                return Number(
                    // Convert Persian (and Arabic) digits to Latin digits
                    this.normalizeDigits(numberText)
                    // Convert Persian/Arabic decimal separator to English decimal separator (dot)
                    .replace(/٫/g, ".")
                    // Remove other characters such as thousands separators
                    .replace(/,(?=\d*[\.,])/g, "")
                );
            },
            normalizeDigits(text) {
                const persianDigitsRegex = [/۰/g, /۱/g, /۲/g, /۳/g, /۴/g, /۵/g, /۶/g, /۷/g, /۸/g, /۹/g];
                const arabicDigitsRegex = [/٠/g, /١/g, /٢/g, /٣/g, /٤/g, /٥/g, /٦/g, /٧/g, /٨/g, /٩/g];
                for (let i = 0; i < 10; i++) {
                    text = text
                            .replace(persianDigitsRegex[i], i.toString())
                            .replace(arabicDigitsRegex[i], i.toString());
                }
                return text;
            },
            isNumberKey(event)
            {
                let keyCode = (event.keyCode ? event.keyCode : event.which);
                if ((keyCode < 48 || keyCode > 57) && keyCode !== 46) { // 46 is dot
                    event.preventDefault();
                }
            },

            fixNumbers(str)
            {
                var
                    persianNumbers = [/۰/g, /۱/g, /۲/g, /۳/g, /۴/g, /۵/g, /۶/g, /۷/g, /۸/g, /۹/g],
                    arabicNumbers  = [/٠/g, /١/g, /٢/g, /٣/g, /٤/g, /٥/g, /٦/g, /٧/g, /٨/g, /٩/g],
                    oldNativeNumber = this.price;
                if(typeof str === 'string')
                {
                    for(var i=0; i<10; i++)
                    {
                    str = str.replace(persianNumbers[i], i).replace(arabicNumbers[i], i);
                    }
                }

                let countCommaArabic = (str.split("٫").length - 1);
                let countCommaEnglish = (str.split(".").length - 1);


                if(countCommaArabic + countCommaEnglish > 1) {
                     this.invalidPrice = true;
                }else{
                     this.invalidPrice = false;
                }


                // let number = this.parseNumber(str);
                // let englishNumber = number.toLocaleString("en"); // OR "en-US" for USA
                // let numberWithoutThousandSeparator = englishNumber.replace(/,(?=\d*[\.,])/g, "");
                return str;
            },
            async handelData() {

                this.disable_plus = false;
                this.disableMinus = false;
                await this.checkInvoicesFirst();

                this.price = Number(this.reservation.total_price).toFixed(2);
                this.prices = this.reservation.prices;
                this.prices.price = this.reservation.total_price;
                this.prices.total_price = this.reservation.total_price;
                this.prices.total_price_raw = this.reservation.total_price;
                this.prices.total_ewa = this.reservation.ewa_total;
                this.prices.total_vat = this.reservation.vat_total;
                this.prices.total_ttx = this.reservation.ttx_total


                if(this.occ){
                    axios.get(`/nova-vendor/calender/getLastReservationId?unit_id=${this.reservation.unit_id}&date_out=${this.reservation.date_out}&date_in=${this.reservation.date_in}&id=${this.reservation.id}`)
                        .then((res) => {
                            if(res.data && res.data.id){
                                this.canChangeInputs = false;
                                this.disable_plus = true;
                                this.$toasted.show(this.__('This unit has a reservation that intersect with selected dates !'), {type: 'error'})
                            }else{
                                this.canChangeInputs = true;
                            }
                        });
                }
                axios.get(`/nova-vendor/calender/getLastReservationId?unit_id=${this.reservation.unit_id}&date_out=${this.reservation.date_out}&date_in=${this.reservation.date_in}`)
                    .then((res) => {
                        this.last_reservation_id = res.data.id;
                        this.last_reservation_date_in = res.data.date_in;
                    })

                /**
                 * @todo : Stopped Here in the progress of quick access
                 * stop reason , key $reservation['reservations_date'] in line 246 Reservation Controller
                 */

                // console.log(this.reservation.unit.reservations_date);
                // return false;

                this.disableUnitDate(this.reservations_date, this.reservation.date_in)

                this.unit_id = this.reservation.unit.id;
                this.source_id = this.reservation.source_id;
                this.source_num = this.reservation.source_num;

                this.selectedDate = {
                    start: new Date(moment(String(this.reservation.date_in)).toISOString()),
                    end: new Date(moment(String(this.reservation.date_out)).toISOString())
                }

                this.date_in = this.reservation.date_in;
                this.date_out = this.reservation.date_out;


                // if(this.reservation.reservation_type == 'group'){
                //     let main_reservation_id = null;
                //     if(this.reservation.attachable_id){
                //         main_reservation_id = this.reservation.attachable_id;
                //     }else{
                //         main_reservation_id = this.reservation.id;
                //     }
                //     const response = await axios.get(`/nova-vendor/calender/reservation/${this.reservation.id}/get-invoices?type=${this.reservation.reservation_type}&main_reservation_id=${main_reservation_id}`);
                //     var responseInvoices = response.data;


                //         if(responseInvoices.length){
                //             // this.canEditReservation = false;
                //             let holder_invoices = _.filter(responseInvoices, function(invoice) {
                //                 return invoice.invoice_credit_note === null;
                //             });
                //             if(holder_invoices.length){
                //                 const self = this;
                //                 if(this.reservation.reservation_type == 'group'){
                //                     holder_invoices.forEach(function(invoice) {
                //                         if(invoice.data.periods[self.reservation.id] && invoice.data.periods[self.reservation.id].includes(moment(self.selectedDate.end).subtract(1,'days').format('YYYY-MM-DD'))){
                //                             self.disableMinus = true;
                //                         }else{
                //                             self.disableMinus = false;
                //                         }
                //                     })
                //                 }
                //                 if(this.reservation.reservation_type == 'single'){
                //                     let lastInvoice =   holder_invoices[0];
                //                     let lastInvoiceDate = new Date(moment(lastInvoice.to)).getDate();
                //                     let lastSelectedEnd = new Date(moment(this.selectedDate.end).subtract(1 , 'days')).getDate();

                //                     if(lastInvoiceDate === lastSelectedEnd){
                //                         this.disableMinus = true;
                //                     }else{
                //                         this.disableMinus = false;
                //                     }
                //                 }

                //             }else{
                //                 this.disableMinus = false;
                //             }
                //         }

                // }

                // As After Checking And Night run share the same comdition pf reservation checked_in
                if(this.reservation.checked_in){
                    if(!this.reservation.checking_in){
                        if(this.hasPermission('change reservation calendar date before night run')){
                            this.can_edit_calendar_after_checked_in = true;
                        }else{
                            this.can_edit_calendar_after_checked_in = false;
                        }

                        if(this.hasPermission('change reservation price before night run')){
                            this.can_edit_price_after_checked_in = true;
                        }else{
                            this.can_edit_price_after_checked_in = false;
                        }


                        if(this.hasPermission('change reservation unit before night run')){
                            this.can_edit_unit_after_checked_in = true;
                        }else{
                            this.can_edit_unit_after_checked_in = false;
                        }

                        if(this.hasPermission('change reservation source before night run')){
                            this.can_edit_source_after_checked_in = true;
                        }else{
                            this.can_edit_source_after_checked_in = false;
                        }


                        if(this.hasPermission('extend reservation before night run')){
                            this.can_edit_extend_after_checked_in = true;
                        }else{
                            this.can_edit_extend_after_checked_in = false;
                        }


                        if(this.hasPermission('change reservation rent type before night run')){
                            this.can_edit_rent_after_checked_in = true;
                        }else{
                            this.can_edit_rent_after_checked_in = false;
                        }


                    }else{

                        if(this.hasPermission('change reservation calendar date after checkin')){
                            this.can_edit_calendar_after_checked_in = true;
                        }else{
                            this.can_edit_calendar_after_checked_in = false;
                        }


                        if(this.hasPermission('change reservation price after checkin')){
                            this.can_edit_price_after_checked_in = true;
                        }else{
                            this.can_edit_price_after_checked_in = false;
                        }


                        if(this.hasPermission('change reservation unit after checkin')){
                            this.can_edit_unit_after_checked_in = true;
                        }else{
                            this.can_edit_unit_after_checked_in = false;
                        }

                        if(this.hasPermission('change reservation source after checkin')){
                            this.can_edit_source_after_checked_in = true;
                        }else{
                            this.can_edit_source_after_checked_in = false;
                        }


                        if(this.hasPermission('extend reservation after checkin')){
                            this.can_edit_extend_after_checked_in = true;
                        }else{
                            this.can_edit_extend_after_checked_in = false;
                        }


                        if(this.hasPermission('change reservation rent type after checkin')){
                            this.can_edit_rent_after_checked_in = true;
                        }else{
                            this.can_edit_rent_after_checked_in = false;
                        }

                    }
                }

                this.getUnitsAvailable();


            },
            alphanumeric(source_num)
            {
                var letters = /^[0-9a-zA-Z]+$/;
                if(source_num.match(letters))
                {
                    return true;
                }
                else
                {
                    return false;
                }
            },

            checkSourceDeleteable(source_id){

                const self = this;
                this.sources.forEach(function(source){
                    if(source.id == source_id){
                        self.source_is_deleteable = source.deleteable;
                        if(!self.source_is_deleteable){
                            self.source_num = null;
                        }
                    }
                })
            },
            updateTotals(){
                if(parseFloat(this.price) < parseFloat(this.totalLockedAmount)) {
                    this.$toasted.show(this.__('Amount must not be subceeded from the locked amount'), {type: 'error'});
                    return;
                }

                this.prices.total_price_raw = this.price
                this.prices.total_price = this.price

                let x = this.price;
                let e = this.reservation.prices.ewa_parentage / 100;
                let v = this.reservation.prices.vat_parentage / 100 ;
                let t = this.reservation.prices.tourism_percentage / 100 ;
                let y  =   x / ( 1 + e + t + v + (v*e) );

                this.prices.subtotal = y ;
                this.prices.total_ttx = (this.prices.subtotal * t);
                this.prices.total_ewa = (this.prices.subtotal * e);
                this.prices.total_vat = ((Number(this.prices.subtotal) + Number(this.prices.total_ewa) ) * v);
                this.prices.price = (this.prices.total_price - this.prices.total_vat - this.prices.total_ewa - this.prices.total_ttx );

            },
            getVatOrEwaFromTotalPrice(price, percentage, other) {
                let vatDivisor = 1 + (percentage / 100);
                let priceBeforeVat = price / vatDivisor;
                return price - priceBeforeVat - other
            },
            getSources() {
                axios.get('/apidata/sources')
                    .then(response => {
                        this.sources = response.data.data;
                    }).catch(err => {
                    this.loading = false;
                })
            },
            getReservationsDate(){
                axios.get(`/nova-vendor/calender/reservations-dates?unit_id=${this.reservation.unit_id}`)
                    .then((res) => {
                        this.reservations_date = res.data;
                    })
            },
            async getLockedAmount() {
                if(!Nova.app.currentTeam.check_calculate_price_by_day_enable) {
                    return;
                }
                try {
                this.loading = true;
                this.payload = {
                  days: this.reservation.prices.days,
                  reservationId: this.reservation.id,
                  checkDatesOnly: true
                }
                const response = await axios.post(`/nova-vendor/calender/reservations/update-reservation-prices`,this.payload);
                const ewa_percentage = parseFloat(this.reservation.prices.ewa_parentage);
                const vat_percentage = parseFloat(this.reservation.prices.vat_parentage);
                let amount = 0;
                response.data.data.forEach(day => {
                    const day_ewa = (Number(day.price) / 100) * ewa_percentage;
                    const day_wit_ewa = Number(day.price) + day_ewa;
                    const day_vat = (day_wit_ewa / 100) * vat_percentage;
                    const day_total = day_wit_ewa + day_vat;
                    amount = amount + day_total;
                });
                this.lockedDays = response.data.data
                this.totalLockedAmount = this.truncateToTwoDecimals(amount);
                } catch (e) {
                    if(e.response.status == 401) {
                        return;
                    };
                    this.$toasted.show(e, {type: 'error'});
                } finally {
                    this.loading = false;
                }
            },
            handlePriceChange () {
                this.priceChangeMode = 'input';
                this.updateTotals();
            },

            checkIfDateHasInvoice() {
                let today = moment(this.date_out);
                today = today.format("YYYY/MM/DD");
                return this.lockedDays.some(day => day.date === today && day.hasInvoice && day.hasInvoice === true);
            },

            truncateToTwoDecimals(num) {
                return Math.floor(num * 100) / 100;
            }

        },
        watch: {
            unit(newVal, oldVal) {
                if(this.rent_type == 2){
                    this.month_count = Math.floor(newVal.reservation.nights/30);
                    this.night_count = newVal.reservation.nights%30;
                    if(this.currentSubscription == 'monthly-yearly' && this.month_count < 1){
                        this.month_count = 1;
                        this.night_count = 0;
                        this.handelData();
                    }
                }else{
                    this.month_count = 0;
                    this.night_count = newVal.reservation.nights
                }
            },

            source_id(newVal){
                this.checkSourceDeleteable(newVal);
            }
        },
        mounted() {
            this.getReservationServices();
            if (Nova.app.currentTeam.last_subscription && Nova.app.currentTeam.last_subscription.stripe_plan == 'monthly-yearly') {
                this.showDaily = false;
                this.currentSubscription = 'monthly-yearly';
            }
            // if(Nova.app.$hasPermission('edit reservations') || Nova.app.$hasPermission('change unit')){
            //     if(!this.reservation.checked_in || (this.reservation.checked_in && Nova.app.$hasPermission('edit reservations after checkin'))){
            //         this.showEditIcon = true;
            //     }
            // }
            // if(Nova.app.$hasPermission('extend reservations')){
            //         this.canExtendReservations = true;
            // }
            Nova.$on('update-reservation-day-price', () => {
                Nova.$emit('update-reservation');
            })
            Nova.$on('invoice-deleted' , () => {
                this.isLoading = true;

                axios.get('/nova-vendor/calender/reservationInvoices?id=' + this.reservation.id)
                    .then((res) => {
                        this.invoices = _.orderBy(res.data, 'number', 'desc');
                        if(!this.invoices.length){
                            this.hasInvoices = false;
                        }
                        this.isLoading = false;
                    })
                    .catch((err) => {
                        console.log(err);
                    })
            });
            Nova.$on('quick-open-edit-reservation-modal' , (reservation) => {

                if(this.reservation.id === reservation.id){
                    this.quickReservation = reservation ;
                    this.invoices = reservation.invoices;
                    this.open();
                }

            });

            Nova.$on('set_invoices_from_history_modal' , (invoices) => {
                this.invoicesWithoutCreditNotes = _.filter(invoices, function(invoice) {
                            return invoice.invoice_credit_note === null;
                });

                if(this.invoicesWithoutCreditNotes.length){
                    this.hasInvoices = true;
                    var lastGroupInvoice = this.invoicesWithoutCreditNotes[0];
                    var theEndDateOfReservation  = moment(this.selectedDate.end).subtract(1,'days').format('YYYY-MM-DD');
                    const self = this;
                    if(this.reservation.reservation_type == 'group'){

                        // For saftey minus logic must not depend on the first potential invoices cause its an include
                        // so i need to ierate the invoices , get periods push dates then check includes
                        var invoices_from_to_holder = [];
                        this.invoicesWithoutCreditNotes.forEach(function(invoice) {
                            invoices_from_to_holder.push(invoice.from);
                            invoices_from_to_holder.push(invoice.to);
                        });
                        // sort the array to be able to fetch min and max
                        invoices_from_to_holder =  _.sortBy(invoices_from_to_holder);
                        var min_invoice_from_date = invoices_from_to_holder[0];
                        var max_invoice_from_date = invoices_from_to_holder[invoices_from_to_holder.length -1];

                        if(max_invoice_from_date >= theEndDateOfReservation){
                            self.disableMinus = true;
                        }else{
                            self.disableMinus = false;
                        }
                        // if(lastGroupInvoice.data.periods[self.reservation.id] && lastGroupInvoice.data.periods[self.reservation.id].includes(theEndDateOfReservation)){
                        //     self.disableMinus = true;
                        // }else{
                        //     self.disableMinus = false;
                        // }
                        // we can disable the plus button also if the full period lies in an invoice
                        if(lastGroupInvoice.to > theEndDateOfReservation ){

                            self.canExtendReservations = false;
                        }else{
                            self.canExtendReservations = true;
                        }

                    }

                }else{
                    this.hasInvoices = false;
                    this.disableMinus = false;
                }
            });


           // this.getLockedAmount();


        },
        destroyed() {
            Nova.$off('update-reservation-day-price');
        }
    }
</script>

<style lang="scss">
    .update_reservation_modal {
        .sweet-content {
            max-height: 500px;
            overflow-y: auto;
            display: block;
            scrollbar-width: thin;
            scrollbar-color: #ccc #f5f5f5;
            &::-webkit-scrollbar {width: 6px;}
            &::-webkit-scrollbar-track {background: #f5f5f5;}
            &::-webkit-scrollbar-thumb {background: #ccc;}
            &::-webkit-scrollbar-thumb:window-inactive {background: #f5f5f5;}
        } /* sweet-content */
        .sweet-content-content {
            position: relative;
            .loding_spinner {
                position: absolute;
                display: flex;
                align-content: center;
                align-items: center;
                width: 100%;
                height: 100%;
                background: #ffffff99;
                z-index: 9;
            } /* loding_spinner */
            .select_alert {
                text-align: center;
                background: #fffaf0;
                border: 1px solid #fbd38d;
                color: #b7791f;
                font-size: 15px;
                border-radius: 5px;
                padding: 10px 43px 10px 10px;
                margin: 0 auto 15px;
                position: relative;
                width: 100%;
                [dir="ltr"] & {
                    padding: 10px 10px 10px 43px;
                } /* ltr */
                &::after {
                    content: "";
                    position: absolute;
                    right: 0;
                    top: 0;
                    display: block;
                    background-image: url("data:image/svg+xml,%3C%3Fxml version='1.0'%3F%3E%3Csvg xmlns='http://www.w3.org/2000/svg' id='Capa_1' enable-background='new 0 0 524.235 524.235' height='512px' viewBox='0 0 524.235 524.235' width='512px' class=''%3E%3Cg%3E%3Cpath d='m262.118 0c-144.53 0-262.118 117.588-262.118 262.118s117.588 262.118 262.118 262.118 262.118-117.588 262.118-262.118-117.589-262.118-262.118-262.118zm17.05 417.639c-12.453 2.076-37.232 7.261-49.815 8.303-10.651.882-20.702-5.215-26.829-13.967-6.143-8.751-7.615-19.95-3.968-29.997l49.547-136.242h-51.515c-.044-28.389 21.25-49.263 48.485-57.274 12.997-3.824 37.212-9.057 49.809-8.255 7.547.48 20.702 5.215 26.829 13.967 6.143 8.751 7.615 19.95 3.968 29.997l-49.547 136.242h51.499c.01 28.356-20.49 52.564-48.463 57.226zm15.714-253.815c-18.096 0-32.765-14.671-32.765-32.765 0-18.096 14.669-32.765 32.765-32.765s32.765 14.669 32.765 32.765c0 18.095-14.668 32.765-32.765 32.765z' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%23B7791F'/%3E%3C/g%3E%3C/svg%3E%0A");
                    height: 43px;
                    width: 43px;
                    background-repeat: no-repeat;
                    background-size: 22px;
                    background-position: center center;
                    [dir="ltr"] & {
                        left: 0;
                        right: auto;
                    } /* ltr */
                } /* after */
            } /* select_alert */
            .rent_type {
                display: flex;
                align-items: center;
                justify-content: space-between;
                flex-wrap: wrap;
                margin: 0 auto 15px;
                .name {
                    display: block;
                    width: 25%;
                    font-size: 15px;
                    color: #000;
                    @media (min-width: 320px) and (max-width: 767px) {
                        width: 100%;
                        margin: 0 auto 5px;
                    } /* Mobile */
                } /* name */
                .choose {
                    display: flex;
                    align-items: center;
                    justify-content: flex-start;
                    width: 75%;
                    @media (min-width: 320px) and (max-width: 767px) {
                        width: 100%;
                    } /* Mobile */
                    label {
                        display: block;
                        min-width: 150px;
                        margin: 0 0 0 30px;
                        position: relative;
                        overflow: hidden;
                        [dir="ltr"] & {
                            margin: 0 30px 0 0;
                        } /* ltr */
                        p {
                            display: block;
                            font-size: 15px;
                            color: #000;
                            position: relative;
                            padding: 0 21px 0 0;
                            cursor: pointer;
                            [dir="ltr"] & {
                                padding: 0 0 0 21px;
                            } /* ltr */
                            &::after {
                                content: "";
                                width: 15px;
                                height: 15px;
                                border: 2px solid #ddd;
                                border-radius: 100%;
                                display: block;
                                position: absolute;
                                right: 0;
                                top: 6px;
                                background: #ffffff;
                                [dir="ltr"] & {
                                    left: 0;
                                    right: auto;
                                } /* ltr */
                            } /* after */
                        } /* p */
                        input {
                            position: absolute;
                            opacity: 0;
                            width: 100%;
                            height: 100%;
                            cursor: pointer;
                            top: 0;
                            right: 0;
                            z-index: 9;
                            &:checked ~ {
                                p {
                                    color: #4099de;
                                    &::after {
                                        background: #4099de;
                                    } /* after */
                                } /* p */
                            } /* checked */
                            &[disabled="disabled"] {
                                background-color: #ddd !important;
                                border-color: #c4c4c4;
                                cursor: not-allowed;
                            } /* disabled */
                        } /* input */
                    } /* label */
                } /* choose */
            } /* rent_type */
            .date_range {
                display: flex;
                align-items: center;
                justify-content: space-between;
                flex-wrap: wrap;
                margin: 0 auto 15px;
                .name {
                    display: block;
                    width: 25%;
                    font-size: 15px;
                    color: #000;
                    @media (min-width: 320px) and (max-width: 767px) {
                        width: 100%;
                        margin: 0 auto 5px;
                    } /* Mobile */
                } /* name */
                .v-date-picker {
                    width: 75%;
                    position: relative;
                    .vc-title {
                        font-size: 15px;
                        font-weight: normal;
                    } /* vc-title */
                    .vc-weekday {
                        font-size: 14px;
                        font-weight: normal;
                    } /* vc-weekday */
                    span {
                        text-align: center;
                        font-size: 14px !important;
                        // font-weight: normal;
                        line-height: 28px;
                    } /* span */
                    @media (min-width: 320px) and (max-width: 767px) {
                        width: 100%;
                    } /* Mobile */
                    input {
                        height: 40px;
                        border-radius: 5px;
                        padding: 0 10px;
                        background: #fafafa;
                        border: 1px solid #ddd;
                        font-size: 15px;
                        color: #000;
                        cursor: pointer;
                         &[disabled="disabled"] {
                            background-color: #ddd !important;
                            border-color: #c4c4c4;
                            cursor: not-allowed;
                        } /* disabled */
                    } /* input */
                } /* v-date-picker */
            } /* date_range */
            .change_unit {
                display: flex;
                align-items: flex-start;
                justify-content: space-between;
                flex-wrap: wrap;
                margin: 0 auto 15px;
                .name {
                    display: block;
                    width: 25%;
                    font-size: 15px;
                    color: #000;
                    line-height: 40px;
                    @media (min-width: 320px) and (max-width: 767px) {
                        width: 100%;
                        line-height: normal;
                        margin: 0 auto 5px;
                    } /* Mobile */
                } /* name */
                .choose {
                    width: 75%;
                    @media (min-width: 320px) and (max-width: 767px) {
                        width: 100%;
                    } /* Mobile */
                    select {
                        background-image: url("data:image/svg+xml,%3C%3Fxml version='1.0' encoding='iso-8859-1'%3F%3E%3C!-- Generator: Adobe Illustrator 19.0.0, SVG Export Plug-In . SVG Version: 6.00 Build 0) --%3E%3Csvg version='1.1' id='Layer_1' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' x='0px' y='0px' viewBox='0 0 491.996 491.996' style='enable-background:new 0 0 491.996 491.996;' xml:space='preserve'%3E%3Cg%3E%3Cg%3E%3Cpath d='M484.132,124.986l-16.116-16.228c-5.072-5.068-11.82-7.86-19.032-7.86c-7.208,0-13.964,2.792-19.036,7.86l-183.84,183.848 L62.056,108.554c-5.064-5.068-11.82-7.856-19.028-7.856s-13.968,2.788-19.036,7.856l-16.12,16.128 c-10.496,10.488-10.496,27.572,0,38.06l219.136,219.924c5.064,5.064,11.812,8.632,19.084,8.632h0.084 c7.212,0,13.96-3.572,19.024-8.632l218.932-219.328c5.072-5.064,7.856-12.016,7.864-19.224 C491.996,136.902,489.204,130.046,484.132,124.986z'/%3E%3C/g%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3C/svg%3E%0A");
                        width: 100%;
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
                        &[disabled="disabled"] {
                            background-color: #ddd !important;
                            border-color: #c4c4c4;
                            cursor: not-allowed;
                        } /* disabled */
                        [dir="ltr"] & {
                            background-position: 97% center;
                        } /* ltr */
                    } /* select */
                    p {
                        display: block;
                        font-size: 14px;
                        color: #555;
                        margin: 5px auto 0;
                    } /* p */
                } /* choose */
            } /* change_unit */
            .source_reservation {
                display: flex;
                align-items: center;
                justify-content: space-between;
                flex-wrap: wrap;
                margin: 0 auto 15px;
                .name {
                    display: block;
                    width: 25%;
                    font-size: 15px;
                    color: #000;
                    @media (min-width: 320px) and (max-width: 767px) {
                        width: 100%;
                        margin: 0 auto 5px;
                    } /* Mobile */
                } /* name */
                select {
                    background-image: url("data:image/svg+xml,%3C%3Fxml version='1.0' encoding='iso-8859-1'%3F%3E%3C!-- Generator: Adobe Illustrator 19.0.0, SVG Export Plug-In . SVG Version: 6.00 Build 0) --%3E%3Csvg version='1.1' id='Layer_1' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' x='0px' y='0px' viewBox='0 0 491.996 491.996' style='enable-background:new 0 0 491.996 491.996;' xml:space='preserve'%3E%3Cg%3E%3Cg%3E%3Cpath d='M484.132,124.986l-16.116-16.228c-5.072-5.068-11.82-7.86-19.032-7.86c-7.208,0-13.964,2.792-19.036,7.86l-183.84,183.848 L62.056,108.554c-5.064-5.068-11.82-7.856-19.028-7.856s-13.968,2.788-19.036,7.856l-16.12,16.128 c-10.496,10.488-10.496,27.572,0,38.06l219.136,219.924c5.064,5.064,11.812,8.632,19.084,8.632h0.084 c7.212,0,13.96-3.572,19.024-8.632l218.932-219.328c5.072-5.064,7.856-12.016,7.864-19.224 C491.996,136.902,489.204,130.046,484.132,124.986z'/%3E%3C/g%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3C/svg%3E%0A");
                    width: 75%;
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
                    &[disabled="disabled"] {
                        background-color: #ddd !important;
                        border-color: #c4c4c4;
                        cursor: not-allowed;
                    } /* disabled */
                    [dir="ltr"] & {
                        background-position: 97% center;
                    } /* ltr */
                    @media (min-width: 320px) and (max-width: 767px) {
                        width: 100%;
                    } /* Mobile */
                } /* select */
            } /* source_reservation */
            .price_reservation {
                display: flex;
                align-items: baseline;
                justify-content: space-between;
                flex-wrap: wrap;
                margin: 0 auto 15px;
                .name {
                    display: block;
                    width: 25%;
                    font-size: 15px;
                    color: #000;
                    @media (min-width: 320px) and (max-width: 767px) {
                        width: 100%;
                        margin: 0 auto 5px;
                    } /* Mobile */
                } /* name */
                input {
                    width: 75%;
                    height: 40px;
                    border-radius: 5px;
                    padding: 0 10px;
                    background: #fafafa;
                    border: 1px solid #ddd;
                    font-size: 15px;
                    color: #000;
                    &[disabled="disabled"] {
                        background-color: #ddd !important;
                        border-color: #c4c4c4;
                        cursor: not-allowed;
                    } /* disabled */
                    @media (min-width: 320px) and (max-width: 767px) {
                        width: 100%;
                    } /* Mobile */
                } /* input */
            } /* price_reservation */
            .transfer_reason {
                display: flex;
                align-items: flex-start;
                justify-content: space-between;
                flex-wrap: wrap;
                margin: 0 auto 15px;
                .name {
                    display: block;
                    width: 25%;
                    font-size: 15px;
                    color: #000;
                    line-height: 40px;
                    @media (min-width: 320px) and (max-width: 767px) {
                        width: 100%;
                        margin: 0 auto 5px;
                    } /* Mobile */
                } /* name */
                .text_area {
                    width: 75%;
                    @media (min-width: 320px) and (max-width: 767px) {
                        width: 100%;
                    } /* Mobile */
                    textarea {
                        padding: 10px;
                        background: #fafafa;
                        border: 1px solid #ddd;
                        font-size: 15px;
                        color: #000;
                        width: 100%;
                        border-radius: 5px;
                        &[disabled="disabled"] {
                            background-color: #ddd !important;
                            border-color: #c4c4c4;
                            cursor: not-allowed;
                        } /* disabled */
                    } /* input */
                    p {
                        display: block;
                        font-size: 14px;
                        color: #555;
                        margin: 5px auto 0;
                    } /* p */
                } /* text_area */
            } /* transfer_reason */
            .counter_days {
                display: flex;
                align-items: flex-start;
                justify-content: space-between;
                flex-wrap: wrap;
                margin: 0 auto 15px;
                .name {
                    display: block;
                    width: 25%;
                    font-size: 15px;
                    color: #000;
                    line-height: 40px;
                } /* name */
                .night_number {
                    font-size: 20px;
                    width: 75%;
                    line-height: 40px;
                } /* night_number */
                .counter_area {
                    width: 75%;
                    display: flex;
                    align-items: center;
                    justify-content: flex-start;
                    flex-wrap: wrap;
                    .counter_items {
                        display: flex;
                        align-items: center;
                        justify-content: space-between;
                        flex-wrap: nowrap;
                        button {
                            height: 30px;
                            width: 30px;
                            background-color: #fafafa;
                            border: 1px solid #ddd;
                            background-image: url("data:image/svg+xml,%3C%3Fxml version='1.0'%3F%3E%3Csvg xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' version='1.1' id='Capa_1' x='0px' y='0px' viewBox='0 0 298.667 298.667' style='enable-background:new 0 0 298.667 298.667;' xml:space='preserve' width='512px' height='512px'%3E%3Cg%3E%3Cg%3E%3Cg%3E%3Crect y='128' width='298.667' height='42.667' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%23555555'/%3E%3C/g%3E%3C/g%3E%3C/g%3E%3C/svg%3E%0A");
                            background-size: 10px;
                            background-repeat: no-repeat;
                            background-position: center center;
                            order: 3;
                            &.plus {
                                background-image: url("data:image/svg+xml,%3C%3Fxml version='1.0'%3F%3E%3Csvg xmlns='http://www.w3.org/2000/svg' height='512px' viewBox='0 0 448 448' width='512px'%3E%3Cg%3E%3Cpath d='m272 184c-4.417969 0-8-3.582031-8-8v-176h-80v176c0 4.417969-3.582031 8-8 8h-176v80h176c4.417969 0 8 3.582031 8 8v176h80v-176c0-4.417969 3.582031-8 8-8h176v-80zm0 0' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%23444444'/%3E%3C/g%3E%3C/svg%3E%0A");
                                order: 0;
                            } /* plus */
                        } /* button */
                        input {
                            height: 30px;
                            line-height: 30px;
                            background: #fafafa;
                            border-radius: 0 !important;
                            text-align: center;
                            font-size: 20px;
                            width: auto;
                            border-top: 1px solid #ddd !important;
                            border-bottom: 1px solid #ddd !important;
                            order: 2;
                            color: #000;
                            max-width: 50px;
                            padding: 0 10px;
                        } /* input */
                    } /* counter_items */
                    p {
                        margin: 0 10px 0 0;
                        font-size: 17px;
                        [dir="ltr"] & {
                            margin: 0 0 0 10px;
                        } /* ltr */
                    } /* p */
                } /* counter_area */
            } /* counter_days */
            .counter_days_month {
                display: flex;
                align-items: center;
                justify-content: space-between;
                flex-wrap: wrap;
                margin: 0 auto 15px;
                .col {
                    width: 47%;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    margin: 0 0 15px;
                    .counter_items {
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        flex-wrap: nowrap;
                        button {
                            height: 30px;
                            width: 30px;
                            background-color: #fafafa;
                            border: 1px solid #ddd;
                            background-image: url("data:image/svg+xml,%3C%3Fxml version='1.0'%3F%3E%3Csvg xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' version='1.1' id='Capa_1' x='0px' y='0px' viewBox='0 0 298.667 298.667' style='enable-background:new 0 0 298.667 298.667;' xml:space='preserve' width='512px' height='512px'%3E%3Cg%3E%3Cg%3E%3Cg%3E%3Crect y='128' width='298.667' height='42.667' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%23555555'/%3E%3C/g%3E%3C/g%3E%3C/g%3E%3C/svg%3E%0A");
                            background-size: 10px;
                            background-repeat: no-repeat;
                            background-position: center center;
                            order: 3;
                            &.plus {
                                background-image: url("data:image/svg+xml,%3C%3Fxml version='1.0'%3F%3E%3Csvg xmlns='http://www.w3.org/2000/svg' height='512px' viewBox='0 0 448 448' width='512px'%3E%3Cg%3E%3Cpath d='m272 184c-4.417969 0-8-3.582031-8-8v-176h-80v176c0 4.417969-3.582031 8-8 8h-176v80h176c4.417969 0 8 3.582031 8 8v176h80v-176c0-4.417969 3.582031-8 8-8h176v-80zm0 0' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%23444444'/%3E%3C/g%3E%3C/svg%3E%0A");
                                order: 0;
                            } /* plus */
                        } /* button */
                        input {
                            height: 30px;
                            line-height: 30px;
                            background: #fafafa;
                            border-radius: 0 !important;
                            text-align: center;
                            font-size: 20px;
                            width: auto;
                            border-top: 1px solid #ddd !important;
                            border-bottom: 1px solid #ddd !important;
                            order: 2;
                            color: #000;
                            max-width: 50px;
                            padding: 0 10px;
                        } /* input */
                    } /* counter_items */
                    p {
                        margin: 0 10px 0 0;
                        font-size: 17px;
                        [dir="ltr"] & {
                            margin: 0 0 0 10px;
                        } /* ltr */
                    } /* p */
                } /* col */
            } /* counter_days_month */
            button.update_reservation_button {
                background: #4099de;
                border-radius: 5px;
                border: 1px solid #4099de;
                min-width: 100px;
                height: 35px;
                line-height: 35px;
                font-size: 15px;
                padding: 0 15px;
                margin: 0 auto 10px;
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
            } /* update_reservation_button */
        } /* sweet-content-content */
        .helper-warning {
            margin-top: 5px;
        }
        .flex-1 {
            flex: 1
        }
    } /* update_reservation_modal */

    .multi-select-services {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        margin: 0 auto 15px;

        label {
            width: 25%;
        }
    }
    .select {
        width: 75%;
        span {
            font-size: 15px !important;
        }


        // display: block !important;
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

</style>

