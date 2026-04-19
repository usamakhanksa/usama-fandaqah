<template>
    <div ref='scrollTop'>

        <div class="filter_search_homapage" v-if="payment_preprocessor == 'fandaqah'">
            <div class="waiting_reservations relative" >
                <template >
                    <span @click="gotoMainScreen">{{awaitingConfirmationCount}}  {{__('Awaiting Confirmation Reservations')}}</span>
                </template>
            </div><!-- waiting_reservations -->
        </div><!-- filter_search_homapag -->

        <ul class="breadcrumbs">
            <li class="breadcrumbs__item"><router-link to="/" class="router-link-active">{{__('Home')}}</router-link></li>
            <li class="breadcrumbs__item">
                <a class="router-link-exact-active router-link-active" href="#">{{__('Units Housing')}}</a>
            </li>
        </ul>


        <div class="main_calendar_area">
            <div class="col_date">
                <i class="far fa-calendar-alt"></i>
                <vcuh-date-picker
                    class='v-date-picker'
                    :locale="locale"
                    :masks="{ dayPopover : 'WWW, MMM D, YYYY' , weekdays: 'WWW' }"
                    :is-dark="false"
                    is-expanded
                    @input="update"
                    :input-props='{
                      id: "date-input",
                      readonly: true ,
                    }'
                    mode='single'
                    v-model='datePicker'
                    show-caps
                    :popover="{ placement: 'bottom', visibility: 'click' }"
                >
                </vcuh-date-picker>
            </div><!-- col_date -->

            <div class="col_today">
                <button type="button" @click="resetDatetoToday">{{__('Today')}}</button>
            </div><!-- col_today -->


        </div><!-- main_calendar_area -->
        <div class="filter_area">
            <div class="item" v-if="unitCategories.length">
                <select v-model="unit_category_id" @change="unitFilter">
                    <option value="" selected>{{__('Unit Category')}}</option>
                    <option v-for="(cat,i) in unitCategories" :key="i" :value="cat.id">{{cat.name[locale]}}</option>
                </select>
            </div>


            <!-- Hide statuses filter for now  -->
            <div class="item" v-if="unitStatuses.length">
                <select v-model="unit_status" @change="unitFilter">
                    <option v-for="(status,i) in unitStatuses" :key="i" :value="status.id" :disabled="status.disabled" :selected="status.selected">{{status.label}}</option>
                </select>
            </div>

            <div class="item">
                <div class="units_counter" :style="{'background' : unitsCounterBackground}" >{{ __('Units count') }} : {{ unit_count }}</div>
            </div>

            <div class="item">
                <div class="units_counter" :style="{'background' : '#4099de'}" >{{ __('Occupancy Now') }} : {{ occupancyNow }}%</div>
            </div>

            <div class="buttons_area_action" v-if="units.length && unit_status != ''">
                <button type="button" class="excel_button" @click="exportExcelUnitsStatuses"></button>
                <button type="button" class="print_button" @click="printUnitsStatuses"></button>
                <form id="units_statuses_form" target="_blank" method="post" style="display: none" action="/home/unit-housing-print">
                    <input type="hidden" name="units" :value="JSON.stringify(units)" >
                    <input type="hidden" name="unit_status" :value="unit_status">
                    <input type="hidden" name="selected_date" :value="date">
                </form>
                <!-- <button type="button" class="print_button" @click="printUnitStatuses"></button> -->
            </div>

            <div class="reset_filters" slot="reset-btn">
                <button
                    @click="resetFilters()"
                    v-tooltip="{
                        targetClasses: ['it-has-a-tooltip'],
                        placement: 'top',
                        content: __('Reset Filters'),
                        classes: ['tooltip_reset']
                    }"
                >
                </button>
            </div><!-- reset_filters -->
        </div>


        <div class="row">

            <div class="scroll_up">
                <button @click="scrollTop"
                        type="button"  v-tooltip="{
                        targetClasses: ['it-has-a-tooltip'],
                        placement: 'top',
                        content: __('Scroll To Top'),
                        classes: ['tooltip_reset']
                    }">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="M6.25753788,13.2424621 C5.84748737,12.8324116 5.84748737,12.1675884 6.25753788,11.7575379 C6.66758839,11.3474874 7.33241161,11.3474874 7.74246212,11.7575379 L12,16.0150758 L16.2575379,11.7575379 C16.6675884,11.3474874 17.3324116,11.3474874 17.7424621,11.7575379 C18.1525126,12.1675884 18.1525126,12.8324116 17.7424621,13.2424621 L12.7424621,18.2424621 C12.3324116,18.6525126 11.6675884,18.6525126 11.2575379,18.2424621 L6.25753788,13.2424621 Z M6.25753788,7.24246212 C5.84748737,6.83241161 5.84748737,6.16758839 6.25753788,5.75753788 C6.66758839,5.34748737 7.33241161,5.34748737 7.74246212,5.75753788 L12,10.0150758 L16.2575379,5.75753788 C16.6675884,5.34748737 17.3324116,5.34748737 17.7424621,5.75753788 C18.1525126,6.16758839 18.1525126,6.83241161 17.7424621,7.24246212 L12.7424621,12.2424621 C12.3324116,12.6525126 11.6675884,12.6525126 11.2575379,12.2424621 L6.25753788,7.24246212 Z"/></svg>

                </button>
            </div><!-- scroll_up -->

            <div class="scroll_down">
                <button @click="scrollBottom"
                        type="button"  v-tooltip="{
                        targetClasses: ['it-has-a-tooltip'],
                        placement: 'top',
                        content: __('Scroll To Bottom'),
                        classes: ['tooltip_reset']
                    }">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="M6.25753788,13.2424621 C5.84748737,12.8324116 5.84748737,12.1675884 6.25753788,11.7575379 C6.66758839,11.3474874 7.33241161,11.3474874 7.74246212,11.7575379 L12,16.0150758 L16.2575379,11.7575379 C16.6675884,11.3474874 17.3324116,11.3474874 17.7424621,11.7575379 C18.1525126,12.1675884 18.1525126,12.8324116 17.7424621,13.2424621 L12.7424621,18.2424621 C12.3324116,18.6525126 11.6675884,18.6525126 11.2575379,18.2424621 L6.25753788,13.2424621 Z M6.25753788,7.24246212 C5.84748737,6.83241161 5.84748737,6.16758839 6.25753788,5.75753788 C6.66758839,5.34748737 7.33241161,5.34748737 7.74246212,5.75753788 L12,10.0150758 L16.2575379,5.75753788 C16.6675884,5.34748737 17.3324116,5.34748737 17.7424621,5.75753788 C18.1525126,6.16758839 18.1525126,6.83241161 17.7424621,7.24246212 L12.7424621,12.2424621 C12.3324116,12.6525126 11.6675884,12.6525126 11.2575379,12.2424621 L6.25753788,7.24246212 Z"/></svg>
                </button>
            </div><!-- scroll_down -->

            <div class="m-11" v-if="loading">
                <loader class="text-60" width="40"/>
            </div>

            <section v-if="!loading" class="units-dash-home">
                <template v-if="units.length && day_start && day_end">
                    <unit :main_calendar_date_was_changed="main_calendar_date_was_changed" v-for="unit in units" v-bind:key="unit.id"  :unit="unit" :date="date" :price_type="price_type" :day_start="day_start" :day_end="day_end" :maintenanceActionTypes="maintenanceActionTypes"></unit>
                </template>
            </section>

            <div v-if="show_no_results" class="alert_insurance">
                {{__('No results found')}}
            </div>
            <div class="add_notes_popup" id="showaddnotes">
                <button type="button" @click="btnNotes()">
                    <svg xmlns="http://www.w3.org/2000/svg" width="23.2" height="26.889"><path d="M16.606 6.642h2.73a2.64 2.64 0 0 0-1.818-2.729h3.64c1.003.003 1.815.815 1.818 1.818v19.106c-.003 1.003-.815 1.815-1.818 1.818H6.6V21.65a1.34 1.34 0 0 0-1.365-1.365H.23V5.73c.003-1.003.815-1.815 1.818-1.818h13.647a2.64 2.64 0 0 0-1.818 2.729z" fill="#fdfb8d"/><path d="M19.335 6.643h-5.46a2.64 2.64 0 0 1 1.818-2.729v-1.82s-.912 0-.912-.453V.272h3.64v1.365c0 .453-.912.453-.912.453v1.818c1.167.38 1.92 1.5 1.823 2.735" fill="#ff7474"/><path d="M6.6 21.655v5.006L.23 20.3h5.006A1.34 1.34 0 0 1 6.6 21.655" fill="#aab1ba"/><path d="M6.6 26.884c-.048 0-.14-.048-.18-.048L.048 20.47C0 20.423 0 20.38 0 20.3V5.73a2.04 2.04 0 0 1 2.047-2.047h10.465a.23.23 0 0 1 .198.344.23.23 0 0 1-.198.114H2.052C1.177 4.154.47 4.86.458 5.736V20.2l5.912 5.907v-4.453c.002-.302-.116-.592-.33-.805s-.503-.332-.805-.33H2.96a.23.23 0 0 1-.198-.344.23.23 0 0 1 .198-.114h2.276c.875.012 1.582.72 1.594 1.594v5.005a.26.26 0 0 1-.139.229.55.55 0 0 0-.09-.005zm14.56 0H8.418a.23.23 0 0 1-.229-.229.23.23 0 0 1 .229-.229h12.735c.875-.012 1.582-.72 1.594-1.594V5.73c-.012-.875-.72-1.582-1.594-1.594H20.7a.23.23 0 0 1-.198-.344.23.23 0 0 1 .198-.114h.453A2.04 2.04 0 0 1 23.2 5.725v19.107a2.03 2.03 0 0 1-2.041 2.052zm-3.64-8.184h-6.37a.23.23 0 0 1-.198-.344.23.23 0 0 1 .198-.114h6.37a.23.23 0 0 1 .198.344.23.23 0 0 1-.198.114zm-.912-2.73H12.06a.23.23 0 0 1-.229-.229.23.23 0 0 1 .229-.229h4.547a.23.23 0 0 1 .198.344.23.23 0 0 1-.198.114zm-6.365 0h-5.46a.23.23 0 0 1-.229-.229.23.23 0 0 1 .229-.229h5.46c.062-.004.123.02.167.062s.067.105.062.167c.002.06-.022.12-.065.162s-.103.065-.164.062zm8.188-2.73H15.7a.23.23 0 0 1-.198-.344.23.23 0 0 1 .198-.114h2.73c.062-.004.123.02.167.062s.067.105.062.167c.002.06-.022.12-.065.162s-.103.065-.164.062zm-4.553 0H8.418a.23.23 0 0 1-.198-.344.23.23 0 0 1 .198-.114h5.46a.23.23 0 0 1 .229.229.23.23 0 0 1-.229.229zm-7.277 0H4.782a.23.23 0 0 1-.229-.229.23.23 0 0 1 .229-.229H6.6a.23.23 0 0 1 .229.229.23.23 0 0 1-.229.229zm5.46-2.73H4.782a.23.23 0 0 1-.229-.229.23.23 0 0 1 .229-.229h7.277a.23.23 0 0 1 .198.344.23.23 0 0 1-.198.114zm4.548-.913c-.062.004-.123-.02-.167-.062s-.067-.105-.062-.167v-2.73c-.004-.062.02-.123.062-.167s.105-.067.167-.062h2.506a2.45 2.45 0 0 0-1.637-2.276.31.31 0 0 1-.181-.229V2.1c-.004-.062.02-.123.062-.167s.105-.067.167-.062c.272 0 .682-.09.682-.23V.5h-3.182v1.137c0 .14.4.23.682.23.062-.004.123.02.167.062s.067.105.062.167v1.818a.31.31 0 0 1-.181.229c-.99.3-1.66 1.238-1.637 2.276h.684a.23.23 0 0 1 .229.229.23.23 0 0 1-.229.229h-.912c-.062.004-.123-.02-.167-.062s-.067-.105-.062-.167c-.075-1.258.655-2.426 1.818-2.91v-1.46c-.453-.048-.912-.23-.912-.682V.23c-.004-.062.018-.122.062-.166s.104-.067.166-.063h3.64c.062-.004.123.02.167.062s.067.105.062.167v1.365c0 .4-.453.634-.912.682v1.456a2.91 2.91 0 0 1 1.819 2.91c.004.062-.02.123-.062.167s-.105.067-.167.062h-2.5v2.5c.001.062-.024.122-.068.165s-.105.066-.167.064z" fill="#51565f"/></svg>
                </button>
                <div class="add_notes_inside">
                    <div class="title">{{__('Staff feedback')}}</div>
                    <div class="content_area">
                        <div class="icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="26.199" height="29.197"><g fill="#ee4e71"><path d="M15.907 5.478l-.04-.03-7.454 8.548 6.3 4.874 6.325-9.43-4.113-3.17z"/><path d="M24.803 6.824L17.54 1.207c-.333-.258-.786-.3-1.16-.107a1.09 1.09 0 0 0-.588 1.007l.123 3.376 1.018.784 2.364 1.825 1.784 1.38 3.3-.73c.4-.094.728-.415.818-.826s-.065-.836-.397-1.092zm-10.488 19.63a7.57 7.57 0 0 0 .386-7.582l-6.3-4.868a7.59 7.59 0 0 0-7.243 2.282.76.76 0 0 0 .094 1.112l11.953 9.244c.17.13.385.184.596.15a.76.76 0 0 0 .514-.337z"/></g><path d="M4.433 27.326L8.36 22.88l-1.562-1.205-.667-.515-.35.515-2.972 4.4z" fill="#d6effb"/><path d="M25.397 6.052l-2.832-2.194-4.43-3.422A2.06 2.06 0 0 0 15.934.23a2.06 2.06 0 0 0-1.121 1.906l.105 2.92-6.874 7.88c-2.818-.432-5.664.576-7.582 2.685a1.73 1.73 0 0 0 .211 2.545l3.27 2.527a2.43 2.43 0 0 1 .374.269c.165.122.3.292.357.486.06.234-.11.415-.257.58a.59.59 0 0 0-.076.094c-.012.012-.053.064-.06.07-.023.03-.045.06-.064.094l-.275.38-1.35 2.007-.35-.275a.98.98 0 0 0-1.372.176.98.98 0 0 0 .176 1.372l3.95 3.048c.173.13.383.2.6.2.303.001.588-.14.772-.38a.98.98 0 0 0-.176-1.369l-.333-.257 2.633-2.984 4.136 3.2a1.1 1.1 0 0 0 .135.094c.014.01.03.02.047.023a.82.82 0 0 0 .1.053c.018.006.035.018.053.023.035.018.07.03.105.047l.047.018c.052.018.105.034.158.047.065.018.112.024.158.035.018 0 .035.006.053.006.035.006.076.006.11.012h.085c.025.001.05-.001.076-.006h.053l.164-.018.176-.04a1.74 1.74 0 0 0 .983-.731l.176-.287.053-.094c.04-.065.077-.132.11-.2l.06-.105.1-.2.053-.1.11-.24c.007-.02.017-.04.03-.06l.123-.3a.46.46 0 0 0 .029-.082l.082-.222.035-.105.064-.2.035-.105.064-.228c.006-.03.018-.06.023-.082l.07-.3c.006-.018.006-.035.012-.053l.047-.263.018-.105.03-.216c.007-.037.01-.074.012-.11l.023-.228a.57.57 0 0 0 .006-.1l.018-.322h.012v-.012l-.088-1.03c.001-.3-.03-.62-.094-.924a.2.2 0 0 1-.006-.082 8.6 8.6 0 0 0-.509-1.638l5.77-8.6 2.982-.666a2.06 2.06 0 0 0 .813-3.645zM4.305 25.997l-.176-.135 2.17-3.216h.164l.474.37zm10.2-2.7a6.55 6.55 0 0 1-.878 2.422L2.053 16.77a6.59 6.59 0 0 1 5.933-1.872l5.956 4.6a6.69 6.69 0 0 1 .6 2.247l.018.234a6.48 6.48 0 0 1-.057 1.314zm-.018-5.827l-4.657-3.6 6.178-7.08 3.72 2.87zm9.76-9.76c-.008.045-.043.08-.088.088l-2.85.632-4.44-3.434-.105-2.92c-.003-.045.023-.087.064-.105a.1.1 0 0 1 .123.012l7.26 5.617a.1.1 0 0 1 .033.106z"/></svg>
                        </div><!-- icon -->
                        <textarea cols="30" rows="6" :placeholder="__('Write your note here ..')" @change="noteChanged()" ref="noteInput"></textarea>
                        <div style="text-align: left;">
                            <button @click="addNote" type="submit" class="shadow mb-2  btn btn-block btn-primary mt-2">{{__('Save')}}</button>
                        </div>
                    </div><!-- content_area -->
                </div><!-- add_notes_inside -->
            </div><!-- add_notes_popup -->
        </div>
<!-- add pages pagination buttons with pages variable counts
        <div class="pagination">
            <button type="button" class="prev" :disabled="page == 1" @click="prevPage()"></button>
            <button type="button" class="next" :disabled="page == pages" @click="nextPage()"></button>
        </div>
 -->
 <div class="pagination" style="overflow: scroll;">
    <button type="button" class="prev" :disabled="page === 1" @click="prevPage()">Previous</button>
    <button
      type="button"
      v-for="pageNumber in visiblePages"
      :key="pageNumber"
      :class="{ active: pageNumber === page }"
      @click="goToPage(pageNumber)"
    >
      {{ pageNumber }}
    </button>
    <button type="button" class="next" :disabled="page === pages" @click="nextPage()">Next</button>
  </div>
        <div class="chart_labels_balance">
          <div class="labels relative">
            <loading :active="isCallingOccupied" :loader="'spinner'" :color="'#7e7d7f'" :opacity="0.7" :height="20" :width="20" :is-full-page="false"></loading>
            <ul>
              <li class="empty"><i></i>{{ __('Available')}} ({{this.counters.available_units}})</li>
              <li class="checkedin"><i></i>{{ __('CheckedIn')}} ({{this.counters.occupied_units}})</li>
              <li class="reserved"><i></i>{{ __('Reserved')}} ({{this.counters.reservedUnits}})</li>
              <li class="onlinewaiting"><i></i>{{ this.counters.paymentPreprocessor == 'fandaqah' ? __('Awaiting confirmation') : __('Awaiting Payment')}} ({{this.counters.awaitingReservations}})</li>
              <li class="cleanliness"><i></i>{{ __('Cleanliness')}} ({{this.counters.under_cleaning_units}})</li>
              <li class="maintenance"><i></i>{{ __('Maintenance')}} ({{this.counters.under_maintenace_units}})</li>
            </ul>
          </div><!--end labels -->
          <div class="safe_balance_text relative">
            <loading :active="calculationsActive" :loader="'spinner'" :color="'#7e7d7f'" :opacity="0.7" :height="20" :width="20" :is-full-page="false"></loading>
            <button v-show="!showBalanceAndHideCallToActionButton" :disabled="!original_safe_balance_btn_text"  v-permission="'show safe balance'"  class="receipt_report balance_btn" @click="getSafeBalance">
                <span v-show="original_safe_balance_btn_text">{{__('Show Safe Balance')}}</span>
                <span v-show="!original_safe_balance_btn_text">{{__('Please wait')}}</span>
            </button>
            <span v-show="showBalanceAndHideCallToActionButton" :class="showOrOpenReceiptModal ? 'shifts-click' : ''" @click="OpenReceiptReport" v-permission="'show safe balance'">{{__('Safe Balance')}} : {{safe_balance}} <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></span>
            <div v-show="showOrOpenReceiptModal"  class="receipt_report" @click="OpenReceiptReport">{{__('Receipt Report')}}</div>
          </div><!-- safe_balance_text -->
        </div><!--end chart_labels_balance -->
        <div class="arrivals_and_departures" ref='scrollBottom'>
            <div class="col">
                <!-- Arrivals Today -->
                <panel-arrival :date="date"/>
            </div><!--end col -->
            <div class="col">
                <!-- Departure Today -->
                <panel-departure :date="date" />
            </div><!--end col -->
            <!-- <div class="col">
                <panel-departure-overdue :date="date" />
            </div> -->
        </div><!--end arrivals_and_departures -->

        <receipt-report  ref="initReceipt"/>
    </div>
</template>

<script>
import XLSX from 'xlsx';
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css';
import ReceiptReport from './ReceiptReport';
export default {
    name: "UnitHousing",
    components:{
        Loading,
        ReceiptReport
    },
    computed: {
        unitsCounterBackground(){
            if(this.unit_status == 1){
                return '#28a745';
            }else if(this.unit_status == 2){
                return '#ff9100'
            }else if (this.unit_status == 3){
                return '#aab8c0'
            }else{
                return '#4099de'
            }
            console.log(this.counters.under_maintenace_units);
        },

    visiblePages() {
    //   const startPage = Math.max(1, this.page - 2);
      // make the start page always 1 if you want to display 10 pages
      const startPage = 1;
      // make endPage 10 if you want to display 10 pages
      const endPage = Math.min(startPage + 15, this.pages);
      return Array.from({ length: endPage - startPage + 1 }, (_, i) => startPage + i);
    },

    },
    data(){
        return {
            unit_count : 0,
            page : 1,
            pages : 0,
            locale: 'en',
            date: moment().format('YYYY-MM-DD'),
            datePicker: moment(moment().format('YYYY-MM-DD')).toDate(),
            loading : false,
            units: [],
            counters : {},
            isCallingOccupied : false,
            safe_balance : 0,
            price_type: 'Day',
            noteBody: null,
            showOrOpenReceiptModal : false,
            unit_category_id : '',
            unitCategories : [],
            unit_status : '',
            unitStatuses : [],
            show_no_results : false,
            day_start : null,
            day_end : null,
            payment_preprocessor : Spark.state.currentTeam.payment_preprocessor,
            awaitingConfirmationCount : 0,
            occupancyNow : 0,
            currency :Nova.app.currentTeam.currency,
            maintenanceActionTypes: [],
            main_calendar_date_was_changed : false,
            showBalanceAndHideCallToActionButton : false,
            original_safe_balance_btn_text : true
        }
    },
    methods:{

        scrollTop() {
            const el = this.$refs.scrollTop;

            if (el) {
            // Use el.scrollIntoView() to instantly scroll to the element
            el.scrollIntoView({behavior: 'smooth'});
            }
        },
        scrollBottom() {
            const el = this.$refs.scrollBottom;

            if (el) {
            // Use el.scrollIntoView() to instantly scroll to the element
            el.scrollIntoView({behavior: 'smooth'});
            }
        },
        exportExcelUnitsStatuses(){

            axios.post('/home/unit-housing-excel' , this.units).then(response => {
                let unitStatusWs = XLSX.utils.json_to_sheet(response.data.data)
                let wb = XLSX.utils.book_new();
                XLSX.utils.book_append_sheet(wb, unitStatusWs, response.data.file_name);
                let file_name = '';
                if(this.unit_status == 1) {
                    file_name = this.date + '_' + 'the_available_units';
                }else if(this.unit_status == 2){
                    file_name = this.date + '_' + 'the_under_cleaning_units';
                }else{
                    file_name = this.date + '_' + 'the_under_maintenance_units';
                }
                XLSX.writeFile(wb, file_name + '.xlsx');
                this.$toasted.show(response.data.msg, { type: 'success' });
            });

        },
        printUnitsStatuses(){
            $('#units_statuses_form').submit();
        },
        gotoMainScreen(){
            this.$router.push('/');
        },
        OpenReceiptReport(){
            if(this.showOrOpenReceiptModal){
                this.$refs.initReceipt.$refs.ReceiptReportModal.open();
            }
        },
        resetDatetoToday(){
            this.datePicker =  moment(moment().format('YYYY-MM-DD')).toDate();
            this.update();
        },
        btnNotes() {
            $('#showaddnotes').toggleClass('active');
        },
        noteChanged() {
            this.noteBody = this.$refs.noteInput.value;
        },
        addNote() {
            axios.post('/apidata/notes', {
                body: this.noteBody
            }).then(res => {
                this.btnNotes()
                this.noteBody = null;
                this.$refs.noteInput.value = null;
                this.$toasted.success(this.__('Note added successfully'), {
                    duration: 3000
                })
            })
        },
        unitFilter(){
            this.page = 1;
            this.getUnits();
            console.log(this.page);
        },
        getUnits() {
            const self = this;
            var filtered_units_based_on_status_available = [];
            this.date = moment(String(this.datePicker)).format('YYYY-MM-DD');
            this.loading = true;
            this.units = [];
            axios
                .get(`/nova-vendor/calender/units?date=${this.date}&cat_id=${this.unit_category_id}&unit_status=${this.unit_status}&page=${this.page}`)
                .then(response => {
                    this.units = response.data.items;
                    this.loading = false;
                    this.getOccupied();
                    if(this.payment_preprocessor == 'fandaqah'){
                        this.getAwaitingConfirmationReservations();
                    }
                    if(this.units.length){

                        if (this.unit_status == 1){
                            this.units.forEach(function (unit) {

                                var unit_can_be_pushed_to_array = false;
                                /**
                                 * We have two factors here
                                 * @1 the incoming date match the todays date
                                 * @2 the incoming date doesnt match the todays date
                                 */
                                if (unit.today == self.date) {
                                    // prev reservation
                                    if (unit.previous_reservation) {

                                        if (unit.currentTime < self.day_end) {
                                            unit_can_be_pushed_to_array = false;
                                            if (unit.legacy_checkedin_reservation && !unit.previous_reservation.checked_in) {
                                                unit_can_be_pushed_to_array = false;
                                            }

                                            if (!unit.previous_reservation.checked_in) {
                                                unit_can_be_pushed_to_array = false;
                                            }

                                        }else{
                                            // console.log('harby')
                                            unit_can_be_pushed_to_array = false;
                                            if (unit.current_reservation) {
                                                unit_can_be_pushed_to_array = false;
                                            }

                                            if (unit.previous_reservation.checked_in) {
                                                unit_can_be_pushed_to_array = false;
                                            }

                                            if (unit.legacy_checkedin_reservation) {
                                                unit_can_be_pushed_to_array = false;
                                            }

                                        }

                                        // console.log('am here');
                                        // console.log(unit_can_be_pushed_to_array);
                                    }


                                    // current reservation
                                    if (unit.current_reservation) {
                                        unit_can_be_pushed_to_array = false;
                                        if (unit.previous_reservation && unit.previous_reservation.checked_in && unit.previous_reservation.date_out == unit.today) {
                                            unit_can_be_pushed_to_array = false;
                                        }

                                        if (unit.current_reservation.status === 'awaiting-payment' || unit.current_reservation.status === 'awaiting-confirmation') {
                                            unit_can_be_pushed_to_array = false;
                                        }

                                        if (unit.legacy_checkedin_reservation && !unit.current_reservation.checked_in) {
                                            unit_can_be_pushed_to_array = false;
                                        }

                                        if (!unit.current_reservation.checked_in && unit.current_reservation.date_in == unit.today) {

                                            if (unit.currentTime < self.day_start && (unit.date == moment(new Date(unit.current_reservation.date_in)).format('YYYY-MM-DD'))) {
                                                unit_can_be_pushed_to_array = false;
                                            }
                                            if (unit.currentTime < self.day_start && (unit.date == moment(new Date(unit.current_reservation.date_out)).subtract(1, "days").format('YYYY-MM-DD'))) {
                                                unit_can_be_pushed_to_array = false;
                                            }


                                        }

                                        if (!unit.current_reservation.checked_in) {
                                            unit_can_be_pushed_to_array = false;
                                        }


                                        if (unit.current_reservation.checked_in) {
                                            if (unit.currentTime < self.day_start && ( unit.date == moment(new Date(unit.current_reservation.date_out)).format('YYYY-MM-DD'))) {
                                                unit_can_be_pushed_to_array = false;
                                            }
                                        }
                                    }

                                    if (!unit.current_reservation && !unit.previous_reservation && !unit.legacy_checkedin_reservation) {
                                        unit_can_be_pushed_to_array = true;
                                    }

                                    if (unit.legacy_checkedin_reservation && !unit.current_reservation && !unit.previous_reservation) {
                                        unit_can_be_pushed_to_array = true;
                                    }

                                     if (unit.current_reservation == null && unit.currentTime > self.day_end) {
                                    unit_can_be_pushed_to_array = true;
                                    }


                                    // console.log(unit_can_be_pushed_to_array);

                                    if (unit_can_be_pushed_to_array) {
                                        filtered_units_based_on_status_available.push(unit);
                                    }

                                }else{

                                    if (unit.previous_reservation) {
                                        if (unit.previous_reservation.checked_in) {
                                            unit_can_be_pushed_to_array = true;
                                            if (unit.current_reservation) {
                                                unit_can_be_pushed_to_array = false;
                                            }
                                        }
                                    }

                                    if (unit.current_reservation) {
                                        unit_can_be_pushed_to_array = false;
                                    }else{
                                        unit_can_be_pushed_to_array = true;
                                    }

                                    // console.log(unit_can_be_pushed_to_array);

                                    if (unit_can_be_pushed_to_array) {
                                        filtered_units_based_on_status_available.push(unit);
                                    }
                                }

                            });

                            this.units = filtered_units_based_on_status_available;
                        }
this.pages = response.data.next;

// scroll to top
this.scrollTop();
                        this.show_no_results = false;
                        // Nova.$emit('call-arrivals-query');
                        // Nova.$emit('call-departures-query');
                        // Nova.$emit('call-departures-overdue-query');
                        this.countUsers();
                    }else{
                        this.show_no_results = true;
                    }
                }).catch(err => {
                this.loading = false;
                this.$router.go(-1)
                this.$toasted.show(this.__(err), {type: 'error'})
            })


        },
        goToPage(pageNumber) {
            this.page = pageNumber;
            this.getUnits();
        },
        nextPage() {
            this.page++;
            this.getUnits();
        },
        prevPage() {
            this.page--;
            this.getUnits();
        },
        getAwaitingConfirmationReservations(){
            axios
                .get(`/nova-vendor/calender/get-awaiting-confirmation-reservations-count`)
                .then(response => {
                    this.awaitingConfirmationCount = response.data.count;
                });
        },
        getOccupied(){

            this.isCallingOccupied = true;
            axios.get(`/nova-vendor/calender/get-occupied-data?date=${this.date}&cat_id=${this.unit_category_id}&unit_status=${this.unit_status}`)
                .then((res) => {
                    this.counters = res.data;
                    this.unit_count = this.counters.unit_count;
                    this.isCallingOccupied = false;

                        if(this.unit_status == ''){
                        this.occupancyNow = parseFloat(((this.counters.occupied_units + this.counters.reservedUnits) / this.unit_count ) * 100).toFixed(2);
                    }
                })


        },
        getSafeBalance(){
            this.original_safe_balance_btn_text = false;
            Nova.request().get('/nova-vendor/calender/safe-balance')
            .then(res => {

                    this.showBalanceAndHideCallToActionButton = !this.showBalanceAndHideCallToActionButton;
                    this.safe_balance = res.data.safe_balance;
              
            })

        },
        // fetchCaclculations(){

        //     Nova.request().get('/nova-vendor/calender/calculations?date=' + this.date)
        //         .then((res) => {
        //             if(res.data.gte){
        //                 this.available_units = res.data.available_units;
        //                 this.occupied_percentage = res.data.occupied_percentage;
        //                 this.occupied_percentage_today = res.data.occupied_percentage;
        //             }else{
        //                 this.available_units = res.data.available_units;
        //                 this.occupied_percentage = res.data.occupied_percentage;
        //             }
        //             this.safe_balance = res.data.safe_balance;
        //             this.calculationsActive = false;

        //             if(this.units.length){
        //                 Nova.$emit('call-arrivals-query');
        //                 Nova.$emit('call-departures-query');
        //                 Nova.$emit('call-departures-overdue-query');
        //             }

        //         })
        // },
        update() {
            this.loading = true;
            if (this.datePicker === null) {
                this.datePicker = moment(this.date).toDate()
                return;
            }

            this.date = moment(String(this.datePicker)).format('YYYY-MM-DD')

            const date_is_today = moment(this.date).isSame(moment(), 'day');
            if(!date_is_today){
                this.main_calendar_date_was_changed = true;
            }else{
                this.main_calendar_date_was_changed = false;
            }
            this.getUnits();

            this.$el.querySelector('#date-input').value = Nova.app.__formatDateWithHijriDate(this.date);
        },
        countUsers(){
            axios.get(`/nova-vendor/DashboardUnits/countUsers`)
                .then(res => {
                    if(res.data > 1){
                        this.showOrOpenReceiptModal = true;
                    }else{
                        this.showOrOpenReceiptModal = false;
                    }

                })
        },
        getUnitCategories(){
            axios.get('/nova-vendor/DashboardUnits/getUnitCategories')
            .then(res => {
                this.unitCategories = res.data;
            })
        },
        resetFilters(){
            this.unit_category_id = '';
            this.unit_status = '';
            this.getUnits();
        },
        getSettings(){
            axios.get('/nova-vendor/DashboardUnits/get-day-start-and-day-end-settings')
            .then(res => {
                this.day_start = res.data.day_start;
                this.day_end = res.data.day_end;
            })
        },
        async getActionModel(type) {
              try {
              const payload = {
                type
              };
              const response = await axios.post('/nova-vendor/settings/getActionTypes', payload);
              this.maintenanceActionTypes = response.data.actionTypes;
              } catch (e) {
                this.$toasted.error(e.message, {type: 'danger'});
              }
        }
    },
    created() {

        this.locale = Nova.config.local;
        this.crumbs = [
            {
                text: 'Home',
                to: 'home',
            },
            {
                text: 'Units Housing',
                to: '#',
            }
        ]

        this.unitStatuses = [
                {
                    id : '',
                    label : this.__('Units Status'),
                    selected : true,
                    disabled: false
                },
                {
                    id : 1,
                    label : this.__('Available')
                },
                // {
                //     id : 'checked_in',
                //     label : this.__('CheckedIn'),
                //     selected : false,
                // },
                // {
                //     id : 'booked',
                //     label : this.__('Booked'),
                //     selected : false,
                // },
                // {
                //     id : 'awaiting-payment',
                //     label : this.__('Awaiting Payment'),
                //     selected : false,
                // },
                {
                    id : 2,
                    label : this.__('Under Cleaning'),
                    selected : false,
                },
                {
                    id : 3,
                    label : this.__('Under Maintenance'),
                    selected : false,
                },
                {
                    id : 4,
                    label : this.__('CheckedIn'),
                    selected : false,
                },
                {
                    id : 5,
                    label : this.__('not checked in'),
                    selected : false,
                }

        ]

        this.getSettings();
    },
    mounted() {
        const self = this;
        self.datePicker = moment(moment().format('YYYY-MM-DD')).toDate();
        // this.update();
        this.getUnitCategories();
        Nova.$on('unit-status-setting-changed' ,(changed_unit) => {
            this.getOccupied();
            if(this.units.length && this.unit_status != ''){
                var filtered_units = [];
                // means there is a filter selected and units also
                this.units.forEach(function(item){
                    if(item.status == self.unit_status){
                        filtered_units.push(item);
                    }
                })
                this.units = filtered_units;
            }

            if(!this.units.length){
                this.show_no_results = true;
            }else{
                this.show_no_results = false;
            }

            // this.getUnits();
        });
        this.getActionModel('maintenance');


    },
    // watch:{
    //     unit_category_id: function (val) {
    //         this.getUnits();
    //     },
    // }
}
</script>

<style lang="scss">
.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
}

button {
  margin: 0 5px;
  padding: 5px 10px;
  cursor: pointer;
  background-color: #f0f0f0;
}

button:disabled {
  background-color: #ddd;
  cursor: not-allowed;
}

button.active {
  background-color: #007bff;
  color: #fff;
}
.buttons_area_action {
    width: 100%;
     display: flex;
     align-items: center;
     justify-content: flex-end;
    padding: 0 10px;
    margin-bottom: 10px;
     button,a {
         display: block;
         height: 30px;
         width: 30px;
         margin: 0 10px 0 0;
         outline: none;
         background-position: center center;
         background-size: 25px;
         background-repeat: no-repeat;

         &.excel_button {
             background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='23.308' height='23.308' viewBox='0 0 23.308 23.308'%3E%3Cpath d='M24.213,3H16V5.675h2.717V7.5H16V9.275h2.689v1.793H16v1.793h2.689v1.793H16v1.793h2.689V18.24H16v2.689h8.213a.768.768,0,0,0,.751-.78V3.78A.768.768,0,0,0,24.213,3ZM23.172,18.24H19.586V16.447h3.586Zm0-3.586H19.586V12.861h3.586Zm0-3.586H19.586V9.275h3.586Zm0-3.586H19.586V5.689h3.586Z' transform='translate(-1.657 -0.311)' fill='%23333b45'/%3E%3Cpath d='M0,2.59V20.719l13.447,2.589V0ZM8.505,16.208,6.941,13.25a2.623,2.623,0,0,1-.184-.608H6.733a4.6,4.6,0,0,1-.21.634l-1.57,2.931H2.516l2.894-4.54L2.763,7.128H5.251l1.3,2.723a4.756,4.756,0,0,1,.273.766h.025q.077-.266.285-.792l1.443-2.7h2.279l-2.723,4.5,2.8,4.578Z' fill='%23333b45'/%3E%3C/svg%3E");
         }

         /* excel_button */
         &.print_button {
             background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='19.339' height='19.339' viewBox='0 0 19.339 19.339'%3E%3Cpath d='M17.471,17.471v1.934a1.934,1.934,0,0,1-1.934,1.934H7.8a1.934,1.934,0,0,1-1.934-1.934V17.471H3.934A1.934,1.934,0,0,1,2,15.537v-5.8A1.94,1.94,0,0,1,3.934,7.8H5.868V3.934A1.94,1.94,0,0,1,7.8,2h7.735a1.934,1.934,0,0,1,1.934,1.934V7.8H19.4a1.934,1.934,0,0,1,1.934,1.934v5.8A1.934,1.934,0,0,1,19.4,17.471Zm0-1.934H19.4v-5.8H3.934v5.8H5.868V13.6A1.94,1.94,0,0,1,7.8,11.669h7.735A1.934,1.934,0,0,1,17.471,13.6ZM15.537,7.8V3.934H7.8V7.8ZM7.8,13.6v5.8h7.735V13.6Z' transform='translate(-2 -2)' fill='%23333b45'/%3E%3C/svg%3E");
         }

         /* print_button */
     }

     /* button */
 }

 /* buttons_area */
button.action_btn {

    display: block;
    background: #0A80D8;
    border-radius: 4px;
    font-size: 15px;
    padding: 5px 20px;
    color: #fff;
    margin: 0 auto;

    @media (min-width: 320px) and (max-width: 767px) {
        display: inline-block;
    }

    @media (min-width: 768px) and (max-width: 991px) {
        display: inline-block;
    }

     &.excel_button {
         background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='23.308' height='23.308' viewBox='0 0 23.308 23.308'%3E%3Cpath d='M24.213,3H16V5.675h2.717V7.5H16V9.275h2.689v1.793H16v1.793h2.689v1.793H16v1.793h2.689V18.24H16v2.689h8.213a.768.768,0,0,0,.751-.78V3.78A.768.768,0,0,0,24.213,3ZM23.172,18.24H19.586V16.447h3.586Zm0-3.586H19.586V12.861h3.586Zm0-3.586H19.586V9.275h3.586Zm0-3.586H19.586V5.689h3.586Z' transform='translate(-1.657 -0.311)' fill='%23333b45'/%3E%3Cpath d='M0,2.59V20.719l13.447,2.589V0ZM8.505,16.208,6.941,13.25a2.623,2.623,0,0,1-.184-.608H6.733a4.6,4.6,0,0,1-.21.634l-1.57,2.931H2.516l2.894-4.54L2.763,7.128H5.251l1.3,2.723a4.756,4.756,0,0,1,.273.766h.025q.077-.266.285-.792l1.443-2.7h2.279l-2.723,4.5,2.8,4.578Z' fill='%23333b45'/%3E%3C/svg%3E");
     }

     /* excel_button */
     &.print_button {
         background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='19.339' height='19.339' viewBox='0 0 19.339 19.339'%3E%3Cpath d='M17.471,17.471v1.934a1.934,1.934,0,0,1-1.934,1.934H7.8a1.934,1.934,0,0,1-1.934-1.934V17.471H3.934A1.934,1.934,0,0,1,2,15.537v-5.8A1.94,1.94,0,0,1,3.934,7.8H5.868V3.934A1.94,1.94,0,0,1,7.8,2h7.735a1.934,1.934,0,0,1,1.934,1.934V7.8H19.4a1.934,1.934,0,0,1,1.934,1.934v5.8A1.934,1.934,0,0,1,19.4,17.471Zm0-1.934H19.4v-5.8H3.934v5.8H5.868V13.6A1.94,1.94,0,0,1,7.8,11.669h7.735A1.934,1.934,0,0,1,17.471,13.6ZM15.537,7.8V3.934H7.8V7.8ZM7.8,13.6v5.8h7.735V13.6Z' transform='translate(-2 -2)' fill='%23333b45'/%3E%3C/svg%3E");
     }

     /* print_button */
 }

.units_counter {
    float: right;
    background: #4099de;
    padding: 0 20px;
    border-radius: 9px;
    height: 40px;
    display: -webkit-box;
    display: -ms-flexbox;
    display: flex;
    -webkit-box-align: center;
    -ms-flex-align: center;
    align-items: center;
    -webkit-box-pack: center;
    -ms-flex-pack: center;
    justify-content: center;
    font-size: 15px;
    color: #fff;
    margin: 0 5px;
    cursor: auto;
}
.alert_insurance {
        margin: 10px auto 15px;
        border-radius: 5px;
        padding: 10px;
        text-align: center;
        color: #b7791f;
        border: 1px solid #fbd38d;
        background: #fffaf0;
        font-size: 15px;
        display: block;
        span, p {display: inline-block;}
    } /* alert_balance */
.filter_area {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            justify-content: flex-start;
            margin: 0 -10px;
            .item {
                width: 16.66666%;
                padding: 0 10px;
                margin: 0 0 10px;
                @media (min-width: 320px) and (max-width: 480px) {
                    width: 50%;
                } /* media */
                @media (min-width: 481px) and (max-width: 767px) {
                    width: 33.33333%;
                } /* media */
                @media (min-width: 768px) and (max-width: 991px) {
                    width: 25%;
                } /* media */
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
                select {
                    background-color: #fafafa;
                    background-image: url("data:image/svg+xml,%3C%3Fxml version='1.0'%3F%3E%3Csvg xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' version='1.1' id='Layer_1' x='0px' y='0px' viewBox='0 0 512.011 512.011' style='enable-background:new 0 0 512.011 512.011;' xml:space='preserve' width='512px' height='512px' class=''%3E%3Cg%3E%3Cg%3E%3Cg%3E%3Cpath d='M505.755,123.592c-8.341-8.341-21.824-8.341-30.165,0L256.005,343.176L36.421,123.592c-8.341-8.341-21.824-8.341-30.165,0 s-8.341,21.824,0,30.165l234.667,234.667c4.16,4.16,9.621,6.251,15.083,6.251c5.462,0,10.923-2.091,15.083-6.251l234.667-234.667 C514.096,145.416,514.096,131.933,505.755,123.592z' data-original='%23000000' class='active-path' fill='%23000000'/%3E%3C/g%3E%3C/g%3E%3C/g%3E%3C/svg%3E%0A");
                    background-repeat: no-repeat;
                    background-size: 14px;
                    background-position: 10px center;
                    height: 40px;
                    padding: 0 10px;
                    font-size: 15px;
                    border: 1px solid #ddd !important;
                    color: #000;
                    width: 100%;
                    border-radius: 4px !important;
                    outline: none;
                    -webkit-appearance: none;
                    -moz-appearance: none;
                    -o-appearance: none;
                    appearance: none;
                } /* select */
            } /* item */
            .reset_filters {
                width: 100%;
                display: flex;
                padding: 0 10px;
                justify-content: flex-end;
                button {
                    height: 40px;
                    width: 40px;
                    background-color: #718096;
                    border-radius: 4px;
                    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16.866' height='18.447' viewBox='0 0 16.866 18.447'%3E%3Cg transform='translate(0 0)'%3E%3Cpath d='M24.417,3.658a7.354,7.354,0,0,1,9.56-.252l-2.189.083a.509.509,0,0,0,.019,1.017h.019l3.36-.124a.508.508,0,0,0,.49-.509v-.06h0L35.552.49a.509.509,0,1,0-1.017.038l.079,2.083A8.364,8.364,0,0,0,23.735,2.9a8.367,8.367,0,0,0-2.516,8.178.506.506,0,0,0,.493.388.441.441,0,0,0,.121-.015.509.509,0,0,0,.373-.614A7.349,7.349,0,0,1,24.417,3.658Z' transform='translate(-20.982 0)' fill='%23ffffff'/%3E%3Cpath d='M91.8,185.6a.508.508,0,1,0-.987.241,7.348,7.348,0,0,1-11.832,7.387l2.215-.2a.509.509,0,1,0-.094-1.013l-3.349.3a.508.508,0,0,0-.46.554l.3,3.349a.508.508,0,0,0,.5.463.183.183,0,0,0,.045,0,.508.508,0,0,0,.46-.554l-.181-2.038a8.308,8.308,0,0,0,4.833,1.842c.143.008.286.011.426.011A8.365,8.365,0,0,0,91.8,185.6Z' transform='translate(-75.175 -178.237)' fill='%23ffffff'/%3E%3C/g%3E%3C/svg%3E");
                    background-repeat: no-repeat;
                    background-position: center center;
                    background-size: 20px;
                    -webkit-transition: all 0.2s ease-in-out;
                    -moz-transition: all 0.2s ease-in-out;
                    -o-transition: all 0.2s ease-in-out;
                    transition: all 0.2s ease-in-out;
                    &:hover {
                        background-color: #5E6D83;
                    } /* hover */
                } /* button */
            } /* reset_filters */
        } /* filter_area */
.receipt_report {
  float: right;
  background: #4099de;
  padding: 0 12px;
  border-radius: 100px;
  height: 26px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 15px;
  color: #fff;
  margin: 0 5px;
  cursor: pointer;
}
.receipt_report:hover {background: #318acf;}
.shifts-click{
    cursor: pointer;
}
.filter_search_homapage {
    background: #ffffff;
    padding: 5px;
    margin: -1.125rem -1.125rem 1.125rem -1.125rem;
    box-shadow: 0 1px 3px 0 rgba(0,0,0,.1),0 1px 2px 0 rgba(0,0,0,.06);
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    .choose_area {
        display: flex;
        flex-wrap: wrap;
        @media (min-width: 320px) and (max-width: 767px) {
            width: 100%;
        }
        .multiselect {
            height: 35px;
            margin: 0 0 0 10px;
            color: #000;
            min-width: 145px;
            width: auto;
            min-height: 35px;
            font-size: 15px;
            [dir="ltr"] & {
                margin: 0 10px 0 0;
            } /* ltr */
            @media (min-width: 320px) and (max-width: 767px) {
                width: 48%;
                margin: 0 auto 10px;
                [dir="ltr"] & {
                    margin: 0 auto 10px;
                } /* ltr */
            }
            .multiselect__select {
                height: 35px;
                width: 35px;
                top: 0;
                padding: 0;
                line-height: 35px;
                &::before {
                    margin: 0;
                    top: 40%;
                } /* before */
            } /* multiselect__select */
            .multiselect__tags {
                border: 1px solid #ddd;
                box-shadow: none;
                border-radius: 4px;
                padding: 0 10px;
                line-height: 35px;
                height: 35px;
                min-height: 35px;
                font-size: 15px;
                cursor: pointer;
                span.multiselect__placeholder, span.multiselect__single {
                    display: block;
                    line-height: 33px;
                    margin: 0 auto;
                    padding: 0;
                    color: #000;
                    background: transparent;
                } /* multiselect__placeholder */
            } /* multiselect__tags */
            .multiselect__content-wrapper {
                max-width: none !important;
                width: 100% !important;
                margin: 3px 0 0 0;
                border: 1px solid #ddd;
                border-radius: 4px;
                display: table;
                overflow: hidden;
                @media (min-width: 320px) and (max-width: 767px) {
                    display: block;
                    text-align: center;
                    display: block;
                    overflow: auto;
                }
                li {
                    &.multiselect__element {
                        span.multiselect__option {
                            padding: 0 10px;
                            height: 35px;
                            line-height: 35px;
                            font-size: 15px;
                            min-height: 35px;
                            border-top: 1px solid #eee;
                            color: #000;
                            text-align: initial;
                            @media (min-width: 320px) and (max-width: 767px) {
                                position: relative;
                                height: auto;
                                line-height: 17px;
                                white-space: break-spaces;
                                padding: 7px 25px 7px 10px;
                                font-size: 13px;
                                [dir="ltr"] & {
                                    padding: 7px 10px 7px 25px;
                                } /* ltr */
                            }
                            &::before {
                                content: "";
                                height: 14px;
                                background: #fff;
                                word-wrap: 14px;
                                display: inline-block;
                                width: 14px;
                                margin: 0 0 0 5px;
                                position: relative;
                                top: 3px;
                                border-radius: 2px;
                                border: 1px solid #ddd;
                                [dir="ltr"] & {
                                    margin: 0 5px 0 0;
                                } /* ltr */
                                @media (min-width: 320px) and (max-width: 767px) {
                                    position: absolute;
                                    right: 5px;
                                    top: 9px;
                                    margin: 0 auto;
                                    [dir="ltr"] & {
                                        left: 5px;
                                        right: auto;
                                    } /* ltr */
                                }
                            } /* before */
                            &::after {display: none;}
                            &.multiselect__option--highlight {
                                background: #fafafa;
                                &::after{display: none;}
                            } /* multiselect__option--highlight */
                            &.multiselect__option--selected {
                                font-weight: normal;
                                color: #0A80D8;
                                &::before {
                                    background: #0A80D8;
                                    border-color: #0A80D8;
                                } /* before */
                            } /* multiselect__option--selected */
                        } /* multiselect__option */
                        &:first-child {
                            span.multiselect__option {border-top: none;}
                        } /* first-child */
                    } /* multiselect__element */
                } /* li */
            } /* multiselect__content-wrapper */
        } /* multiselect */
        .actionButton {
            @media (min-width: 320px) and (max-width: 767px) {
                display: flex;
                width: 100%;
                justify-content: center;
            }
            button[type="submit"] {
                float: right;
                height: 35px;
                background: #0A80D8;
                padding: 0;
                border-radius: 3px;
                font-size: 0px;
                color: #fff;
                margin: 0 0 0 10px;
                width: 35px;
                -webkit-transition: all 0.2s ease-in-out;
                -moz-transition: all 0.2s ease-in-out;
                -o-transition: all 0.2s ease-in-out;
                transition: all 0.2s ease-in-out;
                [dir="ltr"] & {
                    float: left;
                    margin: 0 10px 0 0;
                } /* ltr */
                @media (min-width: 320px) and (max-width: 767px) {
                    float: none;
                }
                &:hover {background: #0071C9;}
                svg {
                    height: 20px;
                    margin: 0 auto;
                    fill: #fff;
                } /* svg */
            } /* button */
            button[type="reset"] {
                float: right;
                height: 35px;
                background: #718096;
                padding: 0;
                border-radius: 3px;
                font-size: 17px;
                border: 1px solid #718096;
                margin: 0;
                width: 35px;
                text-align: center;
                -webkit-transition: all 0.2s ease-in-out;
                -moz-transition: all 0.2s ease-in-out;
                -o-transition: all 0.2s ease-in-out;
                transition: all 0.2s ease-in-out;
                [dir="ltr"] & {
                    float: left;
                } /* ltr */
                @media (min-width: 320px) and (max-width: 767px) {
                    float: none;
                }
                &:hover {
                    background: #627187;
                    border-color: #627187;
                } /* hover */
                svg {
                    display: block;
                    margin: 0 auto;
                    fill: #ffffff;
                } /* svg */
            } /* button */
        } /* actionButton */
    } /* choose_area */
    .waiting_reservations {
        span {
            display: block;
            cursor: pointer;
            background-size: 21px;
            background-repeat: no-repeat;
            background-position: center right;
            font-size: 15px;
            padding: 0 28px 0 0;
            background-image: url("/images/alert_icon.gif");
            line-height: 30px;
        } /* span */
    } /* waiting_reservations */
    .units_info {
        @media (min-width: 320px) and (max-width: 767px) {
            margin: 10px 0;
            display: flex;
            justify-content: space-between;
            width: 100%;
        }
        span {
            display: inline-block;
            font-size: 15px;
            margin: 0 0 0 40px;
            [dir="ltr"] & {
                margin: 0 40px 0 0;
            } /* ltr */
            @media (min-width: 320px) and (max-width: 767px) {
                width: auto;
                margin: 0 auto;
                [dir="ltr"] & {
                    margin: 0 auto;
                } /* ltr */
            }
            &:last-of-type {margin: 0 auto;}
        } /* span */
    } /* units_info */
} /* filter_search_homapage */
#registration_steps {
    margin: 0 auto 15px;
    background: #F2F6FF;
    border: 1px solid #D6E2FD;
    border-radius: 5px;
    padding: 10px 15px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    position: relative;
    .txt {
        margin: 10px 0;
        h1 {
            font-size: 15px;
            color: #3362CC;
            margin: 0 auto 10px;
        } /* h1 */
        ul {
            li {
                position: relative;
                margin: 0 auto 10px;
                line-height: 25px;
                font-size: 14px;
                cursor: pointer;
                color: #777777;
                padding: 0 32px 0 0;
                [dir="ltr"] & {
                    padding: 0 0 0 32px;
                } /* ltr */
                &:last-child {margin: 0;}
                &::after {
                    position: absolute;
                    right: 0;
                    top: 0;
                    background-color: #fff;
                    border: 1px solid #ddd;
                    border-radius: 100%;
                    height: 25px;
                    width: 25px;
                    text-align: center;
                    line-height: 25px;
                    font-size: 14px;
                    color: #777777;
                    [dir="ltr"] & {
                        left: 0;
                        right: auto;
                    } /* ltr */
                } /* after */
                &:nth-child(1n) {&::after {content: "1";}}
                &:nth-child(2n) {&::after {content: "2";}}
                &:nth-child(3n) {&::after {content: "3";}}
                &:nth-child(4n) {&::after {content: "4";}}
                i {
                    font-style: normal;
                    margin: 0 5px 0 0;
                    display: inline-block;
                    [dir="ltr"] & {
                        margin: 0 0 0 5px;
                    } /* ltr */
                } /* i */
                &.complete {
                    cursor: auto;
                    &::after {
                        content: "";
                        background-image: url("data:image/svg+xml,%3Csvg id='success' xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24'%3E%3Ccircle id='Ellipse_39' data-name='Ellipse 39' cx='12' cy='12' r='12' fill='%2325ae88'/%3E%3Cpath id='Path_959' data-name='Path 959' d='M24.48,15,16.8,23.64,12,19.8' transform='translate(-6.24 -7.8)' fill='none' stroke='%23fff' stroke-linecap='round' stroke-linejoin='round' stroke-miterlimit='10' stroke-width='2'/%3E%3C/svg%3E%0A");
                        border-color: #25AE88;
                        background-color: #25AE88;
                        background-size: 25px 25px;
                    } /* after */
                    i {
                        display: none;
                    } /* i */
                } /* complete */
            } /* li */
        } /* ul */
    } /* txt */
    .imgthumb {
        margin: 10px 0;
        overflow: hidden;
        img {
            max-width: 100%;
            height: auto;
        } /* img */
    } /* imgthumb */
    button.close_item {
        position: absolute;
        top: 10px;
        left: 10px;
        background-image: url("data:image/svg+xml,%3Csvg id='error' xmlns='http://www.w3.org/2000/svg' width='22' height='22' viewBox='0 0 22 22'%3E%3Cg id='Group_991' data-name='Group 991'%3E%3Cpath id='Path_960' data-name='Path 960' d='M11,0A11,11,0,1,0,22,11,11.012,11.012,0,0,0,11,0Zm0,21.154A10.154,10.154,0,1,1,21.154,11,10.166,10.166,0,0,1,11,21.154Z' fill='%23d6e2fd'/%3E%3Cpath id='Path_961' data-name='Path 961' d='M24.337,16.124a.423.423,0,0,0-.6,0l-3.509,3.509-3.509-3.509a.423.423,0,1,0-.6.6l3.509,3.509-3.509,3.509a.423.423,0,1,0,.6.6l3.509-3.509,3.509,3.509a.423.423,0,0,0,.6-.6l-3.509-3.509,3.509-3.509A.423.423,0,0,0,24.337,16.124Z' transform='translate(-9.231 -9.231)' fill='%23d6e2fd'/%3E%3C/g%3E%3C/svg%3E%0A");
        width: 22px;
        height: 22px;
        [dir="ltr"] & {
            left: auto;
            right: 10px;
        } /* ltr */
    } /* close_item */
} /* registration_steps */
.main_calendar_area {
    position: relative;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    .col_date {
        display: flex;
        align-items: center;
        justify-content: flex-start;
        flex-wrap: wrap;
        margin: 15px 0;
        i {
            font-size: 21px;
            margin: 0 0 0 10px;
        } /* i */
        input#date-input {
            background: transparent;
            cursor: pointer;
            border: none;
            padding: 0;
            font-size: 20px;
            min-width: 400px;
            color: #000;
            @media (min-width: 320px) and (max-width: 767px) {
                font-size: 15px;
                min-width: 290px;
            }
        } /* input */
    } /* col_date */
    .col_occupancy {
        margin: 15px 0;
        @media (min-width: 320px) and (max-width: 767px) {
            margin: 0 auto;
        }
        font-weight: 400;
        font-size: 1.2em;
        border: 1px solid;
        border-radius: 20px;
        padding: 4px;
    }
    .col_today {
        margin: 15px 0;
        @media (min-width: 320px) and (max-width: 767px) {
            margin: 0 auto;
        }
        button {
            color: #fff;
            background-color: #2C3E50;
            opacity: 0.65;
            display: inline-block;
            font-weight: 400;
            text-align: center;
            vertical-align: middle;
            user-select: none;
            border: 1px solid transparent;
            padding: 0.4em 0.65em;
            font-size: 1em;
            line-height: 1.5;
            border-radius: 0.25em;
            &:hover {
                background: #1D2F41;
            } /* hover */
        } /* button */
    } /* col_today */
} /* main_calendar_area */
.chart_labels_balance {
    background: #fff;
    padding: 15px;
    margin: 15px auto;
    border-radius: 4px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 1px 3px 0 rgba(0,0,0,.1),0 1px 2px 0 rgba(0,0,0,.06);
    @media (min-width: 320px) and (max-width: 767px) {
        display: block;
        text-align: center;
        padding: 10px;
    }
    .labels {
        @media (min-width: 320px) and (max-width: 767px) {
            width: 100%;
        }
        ul {
            display: flex;
            flex-wrap: wrap;
            li {
                margin: 0 0 0 20px;
                font-size: 15px;
                line-height: 15px;
                [dir="ltr"] & {
                    margin: 0 20px 0 0;
                } /* ltr */
                @media (min-width: 320px) and (max-width: 767px) {
                    width: 33.333%;
                    margin: 0 auto 15px;
                    text-align: initial;
                }
                i {
                    float: right;
                    height: 15px;
                    width: 15px;
                    margin: 0 0 0 5px;
                    [dir="ltr"] & {
                        float: left;
                        margin: 0 5px 0 0;
                    } /* ltr */
                } /* i */
                &.empty i {background: #50c669;}
                &.checkedin i {background: #f6574b;}
                &.reserved i {background: #126ee8;}
                &.onlinewaiting i {background: #9B59B6;}
                &.cleanliness i {background: #ff9019;}
                &.maintenance i {background: #b3c0c7;}
            } /* li */
        } /* ul */
    } /* labels */
    .safe_balance_text {
        font-size: 15px;
        display: flex;
        align-items: center;
        justify-content: flex-end;
        @media (min-width: 320px) and (max-width: 767px) {
            width: 100%;
            justify-content: center;
        }
        .receipt_report {
          margin: 0 10px 0 0;
        } /* receipt_report */
    } /* safe_balance_text */
} /* main_calendar_area */
.units-dash-home {
    margin: 0 -.5rem;
    display: grid;
    grid-template-columns: repeat(auto-fill,minmax(195px, 1fr));
    @media (min-width: 320px) and (max-width: 480px) {
        margin: .5rem -.5rem;
        grid-template-columns: repeat(auto-fill,minmax(170px, 1fr))
    }
    @media (min-width: 481px) and (max-width: 767px) {
        margin: .5rem -.5rem;
        grid-template-columns: repeat(auto-fill,minmax(155px, 1fr));
    }
    @media (min-width: 768px) and (max-width: 991px) {
        margin: .5rem -.5rem;
        grid-template-columns: repeat(auto-fill,minmax(187px, 1fr));
    }
} /* units-dash-home */
.arrivals_and_departures {
    display: flex;
    flex-wrap: wrap;
    align-content: space-between;
    margin: 0 -10px;
    .col {
        width: 50%;
        padding: 0 10px;
        @media (min-width: 320px) and (max-width: 480px) {
            width: 100%;
            padding: 10px;
        }
        @media (min-width: 481px) and (max-width: 767px) {
            width: 50%;
            padding: 10px;
        }
        .item {
            height: 100%;
            border-radius: 5px;
            background: #ffffff;
            border-left: 1px solid #DDDDDD;
            border-right: 1px solid #DDDDDD;
            border-bottom: 1px solid #DDDDDD;
            .title {
                display: flex;
                padding: 10px;
                border-radius: 5px 5px 0 0;
                align-items: center;
                justify-content: space-between;
                .txt {
                    width: 80%;
                    color: #fff;
                    span {
                        display: block;
                        font-size: 18px;
                        margin: 0 auto 5px;
                    } /* span */
                    p {
                        display: block;
                        font-size: 15px;
                    } /* p */
                } /* txt */
                .icon {
                    width: 15%;
                    height: 60px;
                    background-repeat: no-repeat;
                    background-size: 70px;
                    background-position: center center;
                } /* icon */
            } /* title */
            .tools {
                padding: 5px 10px;
                background: #FDFDFD;
                border-bottom: 1px solid #ddd;
                display: flex;
                align-items: center;
                justify-content: flex-end;
              .print {
                background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='19.339' height='19.339' viewBox='0 0 19.339 19.339'%3E%3Cpath d='M17.471,17.471v1.934a1.934,1.934,0,0,1-1.934,1.934H7.8a1.934,1.934,0,0,1-1.934-1.934V17.471H3.934A1.934,1.934,0,0,1,2,15.537v-5.8A1.94,1.94,0,0,1,3.934,7.8H5.868V3.934A1.94,1.94,0,0,1,7.8,2h7.735a1.934,1.934,0,0,1,1.934,1.934V7.8H19.4a1.934,1.934,0,0,1,1.934,1.934v5.8A1.934,1.934,0,0,1,19.4,17.471Zm0-1.934H19.4v-5.8H3.934v5.8H5.868V13.6A1.94,1.94,0,0,1,7.8,11.669h7.735A1.934,1.934,0,0,1,17.471,13.6ZM15.537,7.8V3.934H7.8V7.8ZM7.8,13.6v5.8h7.735V13.6Z' transform='translate(-2 -2)' fill='%23333b45'/%3E%3C/svg%3E");
                display: block;
                height: 25px;
                width: 25px;
                outline: none;
                background-position: center center;
                background-size: 20px 20px;
                background-repeat: no-repeat;
              } /* print */
            } /* tools */
            .col_content {
                padding: 10px;
                .block {
                    background: #FDFDFD;
                    border-radius: 5px;
                    border: 1px solid #ddd;
                    margin: 0 auto 10px;
                    padding: 5px;
                    color: #000;
                    cursor: pointer;
                    -webkit-transition: all 0.2s ease-in-out;
                    -moz-transition: all 0.2s ease-in-out;
                    -o-transition: all 0.2s ease-in-out;
                    transition: all 0.2s ease-in-out;
                    .top_row {
                        display: flex;
                        flex-wrap: wrap;
                        align-items: flex-start;
                        justify-content: space-between;
                        .wide {
                            width: 25%;
                             margin: 5px 0;
                            @media (max-width: 575.98px) {
                                width: 50%;
                             }
                            @media (min-width: 576px) and (max-width: 767.98px) {
                                width: 33.333%;
                             }
                            span {
                                display: block;
                                font-size: 13px;
                                color: #7D858E;
                            } /* span */
                            p {
                                display: block;
                                color: #000;
                                font-size: 13px;
                                &.res_num {font-size: 18px;}
                            } /* p */
                        } /* wide */
                    } /* top_row */
                    .middle_row {
                        display: flex;
                        flex-wrap: wrap;
                        align-items: flex-start;
                        justify-content: space-between;
                        .wide {
                            margin: 5px 0;
                            width: 25%;
                            @media (max-width: 575.98px) {
                                width: 50%;
                             }
                            @media (min-width: 576px) and (max-width: 767.98px) {
                                width: 33.333%;
                             }
                            span {
                                display: block;
                                font-size: 13px;
                                color: #7D858E;
                            } /* span */
                            p {
                                display: block;
                                font-size: 13px;
                            } /* p */

                        } /* wide */
                    } /* middle_row */
                    .bottom_row {
                        display: flex;
                        flex-wrap: wrap;
                        align-items: flex-start;
                        justify-content: flex-start;
                        .wide {
                            width: 25%;
                             margin: 5px 0;
                            @media (max-width: 575.98px) {
                                width: 50%;
                             }
                            @media (min-width: 576px) and (max-width: 767.98px) {
                                width: 33.333%;
                             }
                            span {
                                display: block;
                                font-size: 13px;
                                color: #7D858E;
                            } /* span */
                            p {
                                display: block;
                                font-size: 13px;
                            } /* p */

                        } /* wide */
                    } /* bottom_row */
                    &:hover {
                        background: #f8f8f8;
                        border-color: #d8d8d8;
                    } /* hover */
                } /* block */
                .pagination {
                    display: flex;
                    padding-right: 0;
                    list-style: none;
                    border-radius: .25rem;
                    .page-item {
                        .page-link {
                            position: relative;
                            display: block;
                            padding: .5rem .75rem;
                            margin-right: -1px;
                            line-height: 1.25;
                            color: #007bff;
                            cursor: pointer;
                            background-color: #fff;
                            border: 1px solid #dee2e6;
                            -webkit-transition: all 0.2s ease-in-out;
                            -moz-transition: all 0.2s ease-in-out;
                            -o-transition: all 0.2s ease-in-out;
                            transition: all 0.2s ease-in-out;
                            &:hover {
                                z-index: 2;
                                color: #0056b3;
                                text-decoration: none;
                                background-color: #e9ecef;
                                border-color: #dee2e6
                            } /* hover */
                            &:focus {
                                z-index: 2;
                                outline: 0;
                                box-shadow: 0 0 0 .2rem rgba(0, 123, 255, .25)
                            } /* focus */
                        } /* page-link */
                        &:first-child {
                            .page-link {
                                margin-right: 0;
                                border-top-right-radius: .25rem;
                                border-bottom-right-radius: .25rem
                            } /* page-link */
                        } /* first-child */
                        &:last-child {
                            .page-link {
                                border-top-left-radius: .25rem;
                                border-bottom-left-radius: .25rem
                            } /* page-link */
                        } /* last-child */
                        &.active {
                            .page-link {
                                z-index: 1;
                                color: #fff;
                                background-color: #007bff;
                                border-color: #007bff
                            } /* page-link */
                        } /* active */
                        &.disabled {
                            .page-link {
                                color: #6c757d;
                                pointer-events: none;
                                cursor: auto;
                                background-color: #fff;
                                border-color: #dee2e6;
                            } /* page-link */
                        } /* disabled */
                    } /* page-item */
                } /* pagination */
            } /* col_content */
            &.Arrivals {
                .title {
                    background: #51C769;
                    .icon {
                        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='46.82' height='47.491' viewBox='0 0 46.82 47.491'%3E%3Cg transform='translate(0.125)'%3E%3Cpath d='M45.767,37.951H40.714V12.118a9.189,9.189,0,0,0-9.178-9.178H23.7V.928a.928.928,0,1,0-1.856,0V2.939H15.035a9.189,9.189,0,0,0-9.178,9.178V37.951H.8a.928.928,0,1,0,0,1.856H45.767a.928.928,0,0,0,0-1.856ZM7.713,12.118A7.33,7.33,0,0,1,15.035,4.8h6.806V7.631a.928.928,0,0,0,1.856,0V4.8h7.838a7.33,7.33,0,0,1,7.322,7.322V37.951H7.713Zm0,0' fill='%23fff'/%3E%3Cpath d='M145.549,151.129H140.01v-1.37a2.118,2.118,0,0,0-2.107-2.122H132.01a2.118,2.118,0,0,0-2.107,2.122v1.37h-4.361a3.3,3.3,0,0,0-3.3,3.3v13.341a3.305,3.305,0,0,0,2.269,3.135v.839a.928.928,0,0,0,1.856,0v-.673h18.254v.673a.928.928,0,1,0,1.856,0v-.807a3.306,3.306,0,0,0,2.372-3.167V154.429A3.3,3.3,0,0,0,145.549,151.129Zm-13.79-1.37a.259.259,0,0,1,.251-.266H137.9a.259.259,0,0,1,.251.266v1.37h-1.845l-.072-.081-.09.081h-4.387Zm-6.217,3.226h9.939l2.337,2.6v2.368a.929.929,0,0,0,.268.652l3.871,3.921a.928.928,0,0,0,1.321,0l2.58-2.614a.928.928,0,0,0,0-1.3l-3.871-3.921a.927.927,0,0,0-.66-.276h-2.069l-1.28-1.423h7.57a1.446,1.446,0,0,1,1.444,1.444v10.406H124.1V154.429A1.446,1.446,0,0,1,125.542,152.985Zm14.132,4.584v-1.305h1.265l2.955,2.993-1.276,1.292Zm5.875,11.645H125.542a1.445,1.445,0,0,1-1.444-1.444v-1.08h22.895v1.08A1.445,1.445,0,0,1,145.549,169.215Zm0,0' transform='translate(-112.261 -135.443)' fill='%23fff'/%3E%3Cpath d='M400.212,493.6a3.506,3.506,0,1,0-3.506,3.506A3.506,3.506,0,0,0,400.212,493.6Zm-5.157,0a1.65,1.65,0,1,1,1.65,1.65A1.65,1.65,0,0,1,395.055,493.6Zm0,0' transform='translate(-360.839 -449.616)' fill='%23fff'/%3E%3Cpath d='M100.536,493.6a3.506,3.506,0,1,0-3.506,3.506A3.506,3.506,0,0,0,100.536,493.6Zm-5.156,0a1.65,1.65,0,1,1,1.65,1.65A1.65,1.65,0,0,1,95.38,493.6Zm0,0' transform='translate(-85.914 -449.616)' fill='%23fff'/%3E%3C/g%3E%3C/svg%3E");
                        background-size: 50px;
                    } /* icon */
                } /* title */
            } /* Arrivals */
            &.Departures {
                .title {
                    background: #126EE8;
                    .icon {
                        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='48.12' height='53.245' viewBox='0 0 48.12 53.245'%3E%3Cg transform='translate(1.777 0)'%3E%3Cpath d='M124.795,160.652a4.519,4.519,0,1,0-4.525-4.519A4.52,4.52,0,0,0,124.795,160.652Zm0-7.022a2.5,2.5,0,1,1-2.509,2.5A2.5,2.5,0,0,1,124.795,153.63Zm0,0' transform='translate(-111.102 -138.017)' fill='%23fff'/%3E%3Cpath d='M45.334,285.338H42.422v-.765a5.7,5.7,0,0,0-5.7-5.69h-4.65a5.7,5.7,0,0,0-5.7,5.69v.765H21.735v-.765a5.7,5.7,0,0,0-5.7-5.69H11.386a5.7,5.7,0,0,0-5.7,5.69v.765H-.769a1.008,1.008,0,0,0-1.008,1.008,1.008,1.008,0,0,0,1.008,1.008H5.689v3.923A1.008,1.008,0,0,0,6.7,292.285H8.244l.768,13.883a1.008,1.008,0,0,0,1.007.952h7.426a1.008,1.008,0,0,0,1.007-.955l.731-13.879h1.545a1.008,1.008,0,0,0,1.008-1.008v-3.923h23.6a1.008,1.008,0,1,0,0-2.016Zm-25.616,4.931H18.226a1.008,1.008,0,0,0-1.006.955L16.488,305.1H10.973L10.2,291.221a1.008,1.008,0,0,0-1.006-.952H7.7v-5.7a3.682,3.682,0,0,1,3.682-3.674h4.651a3.682,3.682,0,0,1,3.682,3.674Zm20.687-5.7v.765h-5.06l1.266-4.438h.113a3.682,3.682,0,0,1,3.682,3.673Zm-6.346-2.081-.552-1.593h1.006Zm-5.668,2.081a3.681,3.681,0,0,1,3-3.61l1.517,4.375H28.392Zm0,0' transform='translate(0 -253.874)' fill='%23fff'/%3E%3Cpath d='M354.738,159.958a4.6,4.6,0,0,0,4.216-2.762l1.055-.336a.989.989,0,0,0,.115-.044,3.024,3.024,0,0,0-2.526-5.5l-.387.178a4.593,4.593,0,1,0-2.473,8.46Zm5.039-6.311a1.009,1.009,0,0,1-.449,1.314l-.01,0a4.56,4.56,0,0,0-.6-1.894A1.009,1.009,0,0,1,359.777,153.647Zm-2.508,1.21A3.887,3.887,0,0,1,355,152.8,2.586,2.586,0,0,1,357.269,154.856Zm-4.2-1.456a5.827,5.827,0,0,0,3.777,3.447,2.578,2.578,0,1,1-3.777-3.447Zm0,0' transform='translate(-320.358 -137.251)' fill='%23fff'/%3E%3Cpath d='M211.94,10.753a5.376,5.376,0,1,0-3.908-1.684A5.377,5.377,0,0,0,211.94,10.753Zm-2.822-7.584a3.584,3.584,0,1,1-.762,2.208A3.584,3.584,0,0,1,209.118,3.168Zm0,0' transform='translate(-189.658 0)' fill='%23fff'/%3E%3Cpath d='M252.487,30.8l1.247.8a.9.9,0,1,0,.967-1.509l-.834-.535V27.564a.9.9,0,0,0-1.792,0v2.483A.9.9,0,0,0,252.487,30.8Zm0,0' transform='translate(-231.087 -24.277)' fill='%23fff'/%3E%3Cpath d='M274.162,413.54h-1.755v-.947a2.018,2.018,0,0,0-2.01-2.023h-2.774a2.019,2.019,0,0,0-2.01,2.023v.947h-1.755a3.589,3.589,0,0,0-3.584,3.584v5.753a3.587,3.587,0,0,0,1.934,3.18,1.007,1.007,0,0,0,1.894.4h9.729a1.008,1.008,0,0,0,1.89-.357,3.589,3.589,0,0,0,2.025-3.227v-5.754A3.589,3.589,0,0,0,274.162,413.54Zm-6.538-.955,2.768.007v.947h-2.765Zm-3.766,2.971h10.3a1.571,1.571,0,0,1,1.568,1.568v3.634H262.29v-3.634A1.57,1.57,0,0,1,263.858,415.556Zm10.3,8.891h-10.3a1.57,1.57,0,0,1-1.568-1.568v-.1h13.44v.1A1.57,1.57,0,0,1,274.162,424.447Zm0,0' transform='translate(-238.551 -373.752)' fill='%23fff'/%3E%3Cpath d='M426.149,317.247h.784a1.008,1.008,0,1,0,0-2.016h-.784a1.008,1.008,0,1,0,0,2.016Zm0,0' transform='translate(-388.634 -286.962)' fill='%23fff'/%3E%3C/g%3E%3C/svg%3E");
                        background-size: 50px;
                    } /* icon */
                } /* title */
            } /* Departures */
            &.Over {
                .title {
                    background: #D82A3D;
                    .txt {
                        span {
                            margin: 0 auto;
                        } /* span */
                    } /* txt */
                    .icon {
                        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='36.193' height='54.5' viewBox='0 0 36.193 54.5'%3E%3Cg transform='translate(6.442)'%3E%3Cpath d='M147.379,143.4a1.018,1.018,0,0,0-.92-.538H124.032a1.017,1.017,0,0,0-1.017,1.017v9.245a1.017,1.017,0,1,0,2.034,0V144.9H139.54l-9.757,2.921a1.017,1.017,0,0,0-.725.974v28.7h-4.009v-7.868a1.017,1.017,0,0,0-2.034,0v8.885a1.017,1.017,0,0,0,1.017,1.017h5.026v3.9a1.017,1.017,0,0,0,1.308.974l16.407-4.912a1.017,1.017,0,0,0,.725-.975V143.905A1.023,1.023,0,0,0,147.379,143.4Zm-1.914,34.353-14.374,4.3V149.551l14.374-4.3Zm0,0' transform='translate(-117.748 -129.942)' fill='%23fff'/%3E%3Cpath d='M227.329,341.888a2.711,2.711,0,1,0-2.712-2.711A2.711,2.711,0,0,0,227.329,341.888Zm0-3.389a.678.678,0,1,1-.678.678A.678.678,0,0,1,227.329,338.5Zm0,0' transform='translate(-210.159 -306.029)' fill='%23fff'/%3E%3Cpath d='M210.055,10.846a5.394,5.394,0,1,0-3.843-1.579A5.423,5.423,0,0,0,210.055,10.846Zm0-9.038a3.615,3.615,0,1,1-3.615,3.615A3.615,3.615,0,0,1,210.055,1.808Zm0,0' transform='translate(-191.981)' fill='%23fff'/%3E%3Cpath d='M245.56,38.317l.841.539v2.011a.9.9,0,0,0,1.808,0V38.362a.9.9,0,0,0-.416-.761l-1.258-.806a.9.9,0,1,0-.975,1.523Zm0,0' transform='translate(-228.828 -33.336)' fill='%23fff'/%3E%3Cpath d='M-2.971,289.41H9.15a1.017,1.017,0,0,0,0-2.034H-2.971l2.9-2.9a1.017,1.017,0,0,0,0-1.438,1.017,1.017,0,0,0-1.438,0l-4.633,4.633a1.018,1.018,0,0,0,0,1.438l4.633,4.633a1.017,1.017,0,0,0,1.438,0,1.017,1.017,0,0,0,0-1.438Zm0,0' transform='translate(0 -257.166)' fill='%23fff'/%3E%3C/g%3E%3C/svg%3E");
                        background-size: 40px;
                    } /* icon */
                } /* title */
            } /* Over */
        } /* item */
    } /* col */
} /* arrivals_and_departures */

.scroll_up {
    position: fixed;
    left: -300px;
    top: 25%;
    z-index: 99999999;
    background: #fff;
    border-radius: 0 0 5px 0;
    border: 1px solid #EAEAEA;
    width: 300px;
    -webkit-transition: all 0.2s ease-in-out;
    -moz-transition: all 0.2s ease-in-out;
    -o-transition: all 0.2s ease-in-out;
    transition: all 0.2s ease-in-out;
    [dir="ltr"] & {
        right: -300px;
        left: auto;
        border-radius: 0 0 0 5px;
    } /* ltr */

    button[type="button"] {
        background: #fff;
        height: 45px;
        width: 45px;
        text-align: center;
        line-height: 45px;
        border-radius: 0 5px 5px 0;
        border: 1px solid #EAEAEA;
        border-left: none;
        position: absolute;
        top: -1px;
        right: -45px;
        outline: none;
        [dir="ltr"] & {
            left: -45px;
            right: auto;
            border-left: 1px solid #EAEAEA;
            border-right: none;
            border-radius: 5px 0 0 5px;
        } /* ltr */
        &::before {
            content: "";
            width: 100%;
            height: 100%;
            position: fixed;
            top: 0;
            right: 0;
            opacity: 0;
            display: none;
            -webkit-transition: all 0.2s ease-in-out;
            -moz-transition: all 0.2s ease-in-out;
            -o-transition: all 0.2s ease-in-out;
            transition: all 0.2s ease-in-out;
        } /* before */
         &:hover {
            background: #000;
            color: #fff;
            border: 1px solid #000;
            svg {
                fill : #fff !important;
            }
        }
        svg {
            display: block;
            margin: 0 auto;
            position: relative;
	       transform: rotate(180deg)
        } /* svg */
    } /* button */
} /* scroll up */

.scroll_down {
    position: fixed;
    left: -300px;
    top: 35%;
    z-index: 99999999;
    background: #fff;
    border-radius: 0 0 5px 0;
    border: 1px solid #EAEAEA;
    width: 300px;
    -webkit-transition: all 0.2s ease-in-out;
    -moz-transition: all 0.2s ease-in-out;
    -o-transition: all 0.2s ease-in-out;
    transition: all 0.2s ease-in-out;
    [dir="ltr"] & {
        right: -300px;
        left: auto;
        border-radius: 0 0 0 5px;
    } /* ltr */

    button[type="button"] {
        background: #fff;
        height: 45px;
        width: 45px;
        text-align: center;
        line-height: 45px;
        border-radius: 0 5px 5px 0;
        border: 1px solid #EAEAEA;
        border-left: none;
        position: absolute;
        top: -1px;
        right: -45px;
        outline: none;
        [dir="ltr"] & {
            left: -45px;
            right: auto;
            border-left: 1px solid #EAEAEA;
            border-right: none;
            border-radius: 5px 0 0 5px;
        } /* ltr */
        &::before {
            content: "";
            width: 100%;
            height: 100%;
            position: fixed;
            top: 0;
            right: 0;
            opacity: 0;
            display: none;
            -webkit-transition: all 0.2s ease-in-out;
            -moz-transition: all 0.2s ease-in-out;
            -o-transition: all 0.2s ease-in-out;
            transition: all 0.2s ease-in-out;
        } /* before */
        &:hover {
            background: #000;
            color: #fff;
            border: 1px solid #000;
            svg {
                fill : #fff !important;
            }
        }
        svg {
            display: block;
            margin: 0 auto;
            position: relative;
            &::before {
                content: '';
                position: absolute;
                top: 15px;
                left: 18px;
                width: 18px;
                height: 18px;
                border-left: 2px solid #333;
            border-bottom: 2px solid #333;
                transform: rotate(-45deg);
            }
        } /* svg */
    } /* button */
} /* scroll down */
.add_notes_popup {
    position: fixed;
    left: -300px;
    top: 45%;
    z-index: 9;
    background: #fff;
    border-radius: 0 0 5px 0;
    border: 1px solid #EAEAEA;
    width: 300px;
    -webkit-transition: all 0.2s ease-in-out;
    -moz-transition: all 0.2s ease-in-out;
    -o-transition: all 0.2s ease-in-out;
    transition: all 0.2s ease-in-out;
    [dir="ltr"] & {
        right: -300px;
        left: auto;
        border-radius: 0 0 0 5px;
    } /* ltr */
    &.active {
        left: 0;
        [dir="ltr"] & {
            right: 0;
            left: auto;
        } /* ltr */
        button[type="button"] {
            &::before {
                display: block;
                opacity: 1;
            } /* before */
        } /* button */
    } /* active */
    button[type="button"] {
        background: #fff;
        height: 45px;
        width: 45px;
        text-align: center;
        line-height: 45px;
        border-radius: 0 5px 5px 0;
        border: 1px solid #EAEAEA;
        border-left: none;
        position: absolute;
        top: -1px;
        right: -45px;
        outline: none;
        [dir="ltr"] & {
            left: -45px;
            right: auto;
            border-left: 1px solid #EAEAEA;
            border-right: none;
            border-radius: 5px 0 0 5px;
        } /* ltr */
        &::before {
            content: "";
            width: 100%;
            height: 100%;
            position: fixed;
            top: 0;
            right: 0;
            opacity: 0;
            display: none;
            -webkit-transition: all 0.2s ease-in-out;
            -moz-transition: all 0.2s ease-in-out;
            -o-transition: all 0.2s ease-in-out;
            transition: all 0.2s ease-in-out;
        } /* before */
        svg {
            display: block;
            margin: 0 auto;
        } /* svg */
    } /* button */
    .add_notes_inside {
        padding: 10px;
        .title {
            font-size: 17px;
            line-height: 25px;
        } /* title */
        .content_area {
            margin: 30px auto 0;
            position: relative;
            .icon{
                overflow: auto;
                position: absolute;
                margin: 0 auto;
                display: table;
                top: -15px;
                svg {
                    [dir="ltr"] & {
                        -webkit-transform: rotatey(180deg);
                        -moz-transform: rotatey(180deg);
                        -o-transform: rotatey(180deg);
                        transform: rotatey(180deg);
                    } /* ltr */
                } /* svg */
            } /* icon */
            textarea {
                background: #FDFB8D;
                width: 100%;
                border: 1px solid #E3E15C;
                border-radius: 5px;
                padding: 25px 10px 10px 10px;
                color: #000;
                font-size: 16px;
                margin: 0 auto;
            } /* textarea */
            button {
                background: #EE4E71;
                -webkit-box-shadow: none;
                box-shadow: none;
                border: none;
                display: inline-block;
                width: auto;
                padding: 0 20px;
                width: 100%;
                font-size: 17px;
                height: 40px;
                line-height: 40px;
                outline: none;
                &:hover {background: #DF3F62;}
            } /* button */
        } /* content_area */
    } /* add_notes_inside */
} /* add_notes_popup */
::-webkit-input-placeholder {color: #000000;}
:-moz-placeholder {color: #000000;opacity:  1;}
::-moz-placeholder {color: #000000;opacity:  1;}
:-ms-input-placeholder {color: #000000;}
::-ms-input-placeholder {color: #000000;}
::placeholder {color:    #000000;}
.reservations_awaiting_confirmation {
    border-radius: 5px;
    margin: 20px auto;
    background: #ffffff;
    border-left: 1px solid #DDDDDD;
    border-right: 1px solid #DDDDDD;
    border-bottom: 1px solid #DDDDDD;
    .title {
        display: flex;
        padding: 10px;
        border-radius: 5px 5px 0 0;
        align-items: center;
        justify-content: space-between;
        background: #9B59B6;
        span {
            display: block;
            font-size: 18px;
            color: #ffffff;
        } /* span */
        .icon {
            width: 45px;
            height: 45px;
            background-repeat: no-repeat;
            background-size: 45px;
            background-position: center center;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='43.461' height='43.461' viewBox='0 0 43.461 43.461'%3E%3Cpath d='M39.839,0H3.622A3.626,3.626,0,0,0,0,3.622V32.6a3.626,3.626,0,0,0,3.622,3.622H17.384v2.9H13.763a3.626,3.626,0,0,0-3.622,3.622.725.725,0,0,0,.724.724H32.6a.725.725,0,0,0,.724-.724A3.626,3.626,0,0,0,29.7,39.115H26.077v-2.9H39.839A3.626,3.626,0,0,0,43.461,32.6V3.622A3.626,3.626,0,0,0,39.839,0ZM29.7,40.564a2.177,2.177,0,0,1,2.049,1.449H11.713a2.177,2.177,0,0,1,2.049-1.449H29.7ZM18.833,39.115v-2.9h5.795v2.9ZM42.012,32.6a2.176,2.176,0,0,1-2.173,2.173H3.622A2.176,2.176,0,0,1,1.449,32.6v-.724H42.012Zm0-2.173H1.449V3.622A2.176,2.176,0,0,1,3.622,1.449H39.839a2.176,2.176,0,0,1,2.173,2.173Z' fill='%23fff'/%3E%3Cg transform='translate(11.59 6.519)'%3E%3Cpath d='M147.557,76.346h-1.449a1.45,1.45,0,0,0-1.449,1.449v.724h-7.968V77.07a1.45,1.45,0,0,0-1.449-1.449h-2.9V73.449A1.45,1.45,0,0,0,130.9,72h-1.449A1.45,1.45,0,0,0,128,73.449V84.314a.725.725,0,0,0,.724.724h2.9a.725.725,0,0,0,.724-.724V82.865H144.66v1.449a.725.725,0,0,0,.724.724h2.9a.725.725,0,0,0,.724-.724V77.795A1.45,1.45,0,0,0,147.557,76.346Zm-15.211.724h2.9v1.449h-2.9Zm15.211,6.519h-1.449V82.141a.725.725,0,0,0-.724-.724H131.622a.725.725,0,0,0-.724.724V83.59h-1.449V73.449H130.9v5.795a.725.725,0,0,0,.724.724h13.763a.725.725,0,0,0,.724-.724V77.795h1.449Z' transform='translate(-128 -72)' fill='%23fff'/%3E%3C/g%3E%3Cg transform='translate(15.936 21.006)'%3E%3Cpath d='M185.417,232h-7.243A2.176,2.176,0,0,0,176,234.173v1.449a2.176,2.176,0,0,0,2.173,2.173h7.243a2.176,2.176,0,0,0,2.173-2.173v-1.449A2.176,2.176,0,0,0,185.417,232Zm.724,3.622a.726.726,0,0,1-.724.724h-7.243a.726.726,0,0,1-.724-.724v-1.449a.726.726,0,0,1,.724-.724h7.243a.726.726,0,0,1,.724.724Z' transform='translate(-176 -232)' fill='%23fff'/%3E%3C/g%3E%3Cg transform='translate(28.974 21.006)'%3E%3Cpath d='M325.371,236.347l1.661-1.661a.724.724,0,0,0-.336-1.215l-5.795-1.449a.724.724,0,0,0-.879.879l1.449,5.795a.723.723,0,0,0,.509.522.736.736,0,0,0,.193.026.724.724,0,0,0,.512-.212l1.661-1.661,3.11,3.11,1.024-1.024Zm-2.8.756-.845-3.382,3.382.845Z' transform='translate(-320.001 -232.001)' fill='%23fff'/%3E%3C/g%3E%3Cg transform='translate(20.8 8.699)'%3E%3Cg transform='translate(0 0)'%3E%3Crect width='2' height='1' transform='translate(-0.069 0.031)' fill='%23fff'/%3E%3C/g%3E%3C/g%3E%3Cg transform='translate(19.123 5.795)'%3E%3Cpath d='M213.808,64a4.384,4.384,0,0,0-2.608.869l.869,1.159a2.9,2.9,0,0,1,3.477,0l.869-1.159A4.382,4.382,0,0,0,213.808,64Z' transform='translate(-211.2 -64)' fill='%23fff'/%3E%3C/g%3E%3Cg transform='translate(17.609 2.897)'%3E%3Cpath d='M198.6,32a6.058,6.058,0,0,0-4.122,1.649l1,1.049a4.528,4.528,0,0,1,6.245,0l1-1.049A6.058,6.058,0,0,0,198.6,32Z' transform='translate(-194.48 -32)' fill='%23fff'/%3E%3C/g%3E%3Cg transform='translate(36.158 13.819)'%3E%3Cg transform='translate(0 0)'%3E%3Crect width='2' height='1' transform='translate(-0.428 -0.088)' fill='%23fff'/%3E%3C/g%3E%3C/g%3E%3Cg transform='translate(36.158 9.63)'%3E%3Cg transform='translate(0 0)'%3E%3Crect width='2' height='3' transform='translate(-0.428 0.101)' fill='%23fff'/%3E%3C/g%3E%3C/g%3E%3Cg transform='translate(36.158 4.51)'%3E%3Cg transform='translate(0 0)'%3E%3Crect width='2' height='3' transform='translate(-0.428 0.22)' fill='%23fff'/%3E%3C/g%3E%3C/g%3E%3Cg transform='translate(5.907 13.819)'%3E%3Cg transform='translate(0 0)'%3E%3Crect width='2' height='1' transform='translate(-0.176 -0.088)' fill='%23fff'/%3E%3C/g%3E%3C/g%3E%3Cg transform='translate(5.907 9.63)'%3E%3Cg transform='translate(0 0)'%3E%3Crect width='2' height='3' transform='translate(-0.176 0.101)' fill='%23fff'/%3E%3C/g%3E%3C/g%3E%3Cg transform='translate(5.907 4.51)'%3E%3Cg transform='translate(0 0)'%3E%3Crect width='2' height='3' transform='translate(-0.176 0.22)' fill='%23fff'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        } /* icon */
    } /* title */
    .content_area {
        padding: 10px;
        .no_data_show {
            min-height: 250px;
            padding: 50px 0;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-wrap: wrap;
            flex-direction: column;
            .icon {
                background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='78.258' height='78.258' viewBox='0 0 78.258 78.258'%3E%3Cpath d='M71.736,0H6.521A6.529,6.529,0,0,0,0,6.521V58.693a6.529,6.529,0,0,0,6.521,6.521H31.3v5.217H24.782a6.529,6.529,0,0,0-6.521,6.521,1.3,1.3,0,0,0,1.3,1.3H58.693a1.3,1.3,0,0,0,1.3-1.3,6.529,6.529,0,0,0-6.521-6.521H46.955V65.215H71.736a6.529,6.529,0,0,0,6.521-6.521V6.521A6.529,6.529,0,0,0,71.736,0ZM53.476,73.041a3.92,3.92,0,0,1,3.69,2.609H21.092a3.92,3.92,0,0,1,3.69-2.609H53.476ZM33.912,70.432V65.215H44.346v5.217ZM75.649,58.693a3.918,3.918,0,0,1-3.913,3.913H6.521a3.918,3.918,0,0,1-3.913-3.913v-1.3H75.649Zm0-3.913H2.609V6.521A3.918,3.918,0,0,1,6.521,2.609H71.736a3.918,3.918,0,0,1,3.913,3.913Z' fill='%23ddd'/%3E%3Cg transform='translate(20.869 11.739)'%3E%3Cpath d='M163.216,79.826h-2.609A2.611,2.611,0,0,0,158,82.434v1.3H143.652V81.13a2.611,2.611,0,0,0-2.609-2.609h-5.217V74.609A2.611,2.611,0,0,0,133.217,72h-2.609A2.611,2.611,0,0,0,128,74.609V94.173a1.3,1.3,0,0,0,1.3,1.3h5.217a1.3,1.3,0,0,0,1.3-1.3V91.564H158v2.609a1.3,1.3,0,0,0,1.3,1.3h5.217a1.3,1.3,0,0,0,1.3-1.3V82.434A2.611,2.611,0,0,0,163.216,79.826Zm-27.39,1.3h5.217v2.609h-5.217Zm27.39,11.739h-2.609V90.26a1.3,1.3,0,0,0-1.3-1.3H134.521a1.3,1.3,0,0,0-1.3,1.3v2.609h-2.609V74.609h2.609V85.043a1.3,1.3,0,0,0,1.3,1.3H159.3a1.3,1.3,0,0,0,1.3-1.3V82.434h2.609Z' transform='translate(-128 -72)' fill='%23ddd'/%3E%3C/g%3E%3Cg transform='translate(28.695 37.825)'%3E%3Cpath d='M192.956,232H179.913A3.918,3.918,0,0,0,176,235.913v2.609a3.918,3.918,0,0,0,3.913,3.913h13.043a3.918,3.918,0,0,0,3.913-3.913v-2.609A3.918,3.918,0,0,0,192.956,232Zm1.3,6.521a1.306,1.306,0,0,1-1.3,1.3H179.913a1.306,1.306,0,0,1-1.3-1.3v-2.609a1.306,1.306,0,0,1,1.3-1.3h13.043a1.306,1.306,0,0,1,1.3,1.3Z' transform='translate(-176 -232)' fill='%23ddd'/%3E%3C/g%3E%3Cg transform='translate(52.172 37.825)'%3E%3Cpath d='M329.671,239.827l2.991-2.991a1.3,1.3,0,0,0-.605-2.187l-10.434-2.609a1.3,1.3,0,0,0-1.582,1.582l2.609,10.434a1.3,1.3,0,0,0,.917.94,1.326,1.326,0,0,0,.348.047,1.3,1.3,0,0,0,.922-.382l2.991-2.991,5.6,5.6,1.844-1.844Zm-5.05,1.362L323.1,235.1l6.09,1.522Z' transform='translate(-320.001 -232.001)' fill='%23ddd'/%3E%3C/g%3E%3Cg transform='translate(37.328 15.72)'%3E%3Cg transform='translate(0 0)'%3E%3Crect width='3.601' height='1.801' transform='translate(0)' fill='%23ddd'/%3E%3C/g%3E%3C/g%3E%3Cg transform='translate(34.433 10.434)'%3E%3Cpath d='M215.9,64a7.893,7.893,0,0,0-4.7,1.565l1.565,2.087a5.217,5.217,0,0,1,6.261,0l1.565-2.087A7.89,7.89,0,0,0,215.9,64Z' transform='translate(-211.2 -64)' fill='%23ddd'/%3E%3C/g%3E%3Cg transform='translate(31.707 5.217)'%3E%3Cpath d='M201.9,32a10.908,10.908,0,0,0-7.421,2.969l1.8,1.889a8.153,8.153,0,0,1,11.244,0l1.8-1.889A10.908,10.908,0,0,0,201.9,32Z' transform='translate(-194.48 -32)' fill='%23ddd'/%3E%3C/g%3E%3Cg transform='translate(64.338 24.724)'%3E%3Cg transform='translate(0 0)'%3E%3Crect width='3.601' height='1.801' fill='%23ddd'/%3E%3C/g%3E%3C/g%3E%3Cg transform='translate(64.338 17.521)'%3E%3Cg transform='translate(0 0)'%3E%3Crect width='3.601' height='5.402' fill='%23ddd'/%3E%3C/g%3E%3C/g%3E%3Cg transform='translate(64.338 8.518)'%3E%3Cg transform='translate(0 0)'%3E%3Crect width='3.601' height='5.402' fill='%23ddd'/%3E%3C/g%3E%3C/g%3E%3Cg transform='translate(10.319 24.724)'%3E%3Cg transform='translate(0 0)'%3E%3Crect width='3.601' height='1.801' fill='%23ddd'/%3E%3C/g%3E%3C/g%3E%3Cg transform='translate(10.319 17.521)'%3E%3Cg transform='translate(0 0)'%3E%3Crect width='3.601' height='5.402' fill='%23ddd'/%3E%3C/g%3E%3C/g%3E%3Cg transform='translate(10.319 8.518)'%3E%3Cg transform='translate(0 0)'%3E%3Crect width='3.601' height='5.402' fill='%23ddd'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
                display: block;
                width: 90px;
                height: 90px;
                background-position: center center;
                background-repeat: no-repeat;
                background-size: 90px;
                margin: 0 auto 20px;
            } /* icon */
            span {
                display: block;
                font-size: 18px;
                color: #DDDDDD;
            } /* span */
        } /* no_data_show */
        .block {
            background: #FDFDFD;
            border-radius: 5px;
            border: 1px solid #ddd;
            margin: 0 auto 10px;
            padding: 5px 0;
            color: #000;
            display: flex;
            align-items: center;
            justify-content: space-around;
            cursor: pointer;
            -webkit-transition: all 0.2s ease-in-out;
            -moz-transition: all 0.2s ease-in-out;
            -o-transition: all 0.2s ease-in-out;
            transition: all 0.2s ease-in-out;
            @media (min-width: 320px) and (max-width: 767px) {
                flex-wrap: wrap;
            }
            @media (min-width: 768px) and (max-width: 991px) {
                flex-wrap: wrap;
            }
            .txt {
                flex: 1 1 0;
                padding: 0 5px;
                @media (min-width: 320px) and (max-width: 767px) {
                    width: 100%;
                    flex: auto;
                }
                @media (min-width: 768px) and (max-width: 991px) {
                    width: 100%;
                    flex: auto;
                }
                span {
                    display: block;
                    font-size: 15px;
                    color: #000000;
                } /* span */
                ul {
                    display: flex;
                    align-items: center;
                    justify-content: flex-start;
                    flex-wrap: wrap;
                    margin: 0 -5px;
                    li {
                        padding: 0 5px;
                        min-width: 25%;
                        margin: 5px 0 0;
                        font-size: 13px;
                        color: #000;
                        @media (min-width: 320px) and (max-width: 767px) {
                            width: 100%;
                        }
                        @media (min-width: 768px) and (max-width: 991px) {
                            width: 50%;
                        }
                        p {
                            display: inline-block;
                            margin: 0 0 0 5px;
                            color: #7C858E;
                        } /* p */
                        .price {
                            display: inline-block;
                            color: #0A80D8;
                            font-weight: bold;
                        } /* price */
                    } /* li */
                } /* ul */
            } /* txt */
            .buttons_area {
                text-align: center;
                padding: 0 5px;
                @media (min-width: 320px) and (max-width: 767px) {
                    margin: 10px auto 0;
                    width: 100%;
                }
                @media (min-width: 768px) and (max-width: 991px) {
                    margin: 10px auto 0;
                    width: 100%;
                }
                button {
                    display: block;
                    height: 30px;
                    width: 30px;
                    margin: 0 10px 0 0;
                    outline: none;
                    background-position: center center;
                    background-size: 25px;
                    background-repeat: no-repeat;

                    /* print_button */
                }
                button.confirmation {
                    display: block;
                    background: #0A80D8;
                    border-radius: 4px;
                    font-size: 15px;
                    padding: 5px 20px;
                    color: #fff;
                    margin: 0 auto;
                    @media (min-width: 320px) and (max-width: 767px) {
                        display: inline-block;
                    }
                    @media (min-width: 768px) and (max-width: 991px) {
                        display: inline-block;
                    }
                } /* confirmation */
                button.cancellation {
                    display: block;
                    background: transparent;
                    border-radius: 4px;
                    font-size: 15px;
                    padding: 5px 20px;
                    color: #ff0000;
                    margin: 0 auto;
                    @media (min-width: 320px) and (max-width: 767px) {
                        display: inline-block;
                    }
                    @media (min-width: 768px) and (max-width: 991px) {
                        display: inline-block;
                    }
                } /* cancellation */
            } /* buttons_area */
            &:hover {
                background: #f8f8f8;
                border-color: #d8d8d8;
            } /* hover */
        } /* block */
    } /* content_area */
} /* reservations_awaiting_confirmation */

.latest_updates {
    border-radius: 5px;
    margin: 20px auto;
    background: #ffffff;
    border-left: 1px solid #DDDDDD;
    border-right: 1px solid #DDDDDD;
    border-bottom: 1px solid #DDDDDD;
    .title {
        display: flex;
        padding: 10px;
        border-radius: 5px 5px 0 0;
        align-items: center;
        justify-content: space-between;
        background: #4099de;
        span {
            display: block;
            font-size: 18px;
            color: #ffffff;
        } /* span */
        .icon {
            width: 45px;
            height: 45px;
            background-repeat: no-repeat;
            background-size: 45px;
            background-position: center center;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' version='1.1' id='Capa_1' x='0px' y='0px' viewBox='0 0 477.867 477.867' style='enable-background:new 0 0 477.867 477.867;' xml:space='preserve' width='512px' height='512px'%3E%3Cg%3E%3Cg%3E%3Cg%3E%3Cpath d='M409.6,0c-9.426,0-17.067,7.641-17.067,17.067v62.344C304.667-5.656,164.478-3.386,79.411,84.479 c-40.09,41.409-62.455,96.818-62.344,154.454c0,9.426,7.641,17.067,17.067,17.067S51.2,248.359,51.2,238.933 c0.021-103.682,84.088-187.717,187.771-187.696c52.657,0.01,102.888,22.135,138.442,60.976l-75.605,25.207 c-8.954,2.979-13.799,12.652-10.82,21.606s12.652,13.799,21.606,10.82l102.4-34.133c6.99-2.328,11.697-8.88,11.674-16.247v-102.4 C426.667,7.641,419.026,0,409.6,0z' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%23FFFFFF'/%3E%3C/g%3E%3C/g%3E%3Cg%3E%3Cg%3E%3Cpath d='M443.733,221.867c-9.426,0-17.067,7.641-17.067,17.067c-0.021,103.682-84.088,187.717-187.771,187.696 c-52.657-0.01-102.888-22.135-138.442-60.976l75.605-25.207c8.954-2.979,13.799-12.652,10.82-21.606 c-2.979-8.954-12.652-13.799-21.606-10.82l-102.4,34.133c-6.99,2.328-11.697,8.88-11.674,16.247v102.4 c0,9.426,7.641,17.067,17.067,17.067s17.067-7.641,17.067-17.067v-62.345c87.866,85.067,228.056,82.798,313.122-5.068 c40.09-41.409,62.455-96.818,62.344-154.454C460.8,229.508,453.159,221.867,443.733,221.867z' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%23FFFFFF'/%3E%3C/g%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        } /* icon */
    } /* title */
    .content_area {
        padding: 10px;
        .no_data_show {
            min-height: 250px;
            padding: 50px 0;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-wrap: wrap;
            flex-direction: column;
            .icon {
                background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' version='1.1' id='Capa_1' x='0px' y='0px' viewBox='0 0 477.867 477.867' style='enable-background:new 0 0 477.867 477.867;' xml:space='preserve' width='512px' height='512px'%3E%3Cg%3E%3Cg%3E%3Cg%3E%3Cpath d='M409.6,0c-9.426,0-17.067,7.641-17.067,17.067v62.344C304.667-5.656,164.478-3.386,79.411,84.479 c-40.09,41.409-62.455,96.818-62.344,154.454c0,9.426,7.641,17.067,17.067,17.067S51.2,248.359,51.2,238.933 c0.021-103.682,84.088-187.717,187.771-187.696c52.657,0.01,102.888,22.135,138.442,60.976l-75.605,25.207 c-8.954,2.979-13.799,12.652-10.82,21.606s12.652,13.799,21.606,10.82l102.4-34.133c6.99-2.328,11.697-8.88,11.674-16.247v-102.4 C426.667,7.641,419.026,0,409.6,0z' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%23ddd'/%3E%3C/g%3E%3C/g%3E%3Cg%3E%3Cg%3E%3Cpath d='M443.733,221.867c-9.426,0-17.067,7.641-17.067,17.067c-0.021,103.682-84.088,187.717-187.771,187.696 c-52.657-0.01-102.888-22.135-138.442-60.976l75.605-25.207c8.954-2.979,13.799-12.652,10.82-21.606 c-2.979-8.954-12.652-13.799-21.606-10.82l-102.4,34.133c-6.99,2.328-11.697,8.88-11.674,16.247v102.4 c0,9.426,7.641,17.067,17.067,17.067s17.067-7.641,17.067-17.067v-62.345c87.866,85.067,228.056,82.798,313.122-5.068 c40.09-41.409,62.455-96.818,62.344-154.454C460.8,229.508,453.159,221.867,443.733,221.867z' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%23ddd'/%3E%3C/g%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
                display: block;
                width: 90px;
                height: 90px;
                background-position: center center;
                background-repeat: no-repeat;
                background-size: 90px;
                margin: 0 auto 20px;
            } /* icon */
            span {
                display: block;
                font-size: 18px;
                color: #DDDDDD;
            } /* span */
        } /* no_data_show */
        .update_items {
            .item {
                background: #FDFDFD;
                border: 1px solid #ddd;
                padding: 10px;
                border-radius: 5px;
                display: flex;
                align-items: center;
                flex-wrap: wrap;
                justify-content: flex-start;
                margin: 0 auto 10px;
                cursor: pointer;
                -webkit-transition: all 0.2s ease-in-out;
                -moz-transition: all 0.2s ease-in-out;
                -o-transition: all 0.2s ease-in-out;
                transition: all 0.2s ease-in-out;
                @media (min-width: 320px) and (max-width: 767px) {
                    flex-wrap: wrap;
                }
                time {
                    display: block;
                    margin: 0;
                    font-size: 15px;
                    color: #444;
                    @media (min-width: 320px) and (max-width: 767px) {
                        width: 100%;
                        margin: 0 auto 10px;
                    }
                } /* time */
                .desc {
                    flex: 1 1 0;
                    padding: 0 60px 0 0;
                    @media (min-width: 320px) and (max-width: 767px) {
                        padding: 0;
                    }
                    span {
                        display: block;
                        color: #000;
                        font-size: 15px;
                        font-weight: bold;
                    } /* span */
                    p {
                        display: block;
                        font-size: 14px;
                        color: #444;
                        margin: 3px auto 0;
                    } /* p */
                } /* desc */
                &:last-child {
                    margin: 0;
                } /* last-child */
                &:hover {
                    background: #f8f8f8;
                    border-color: #d8d8d8;
                } /* hover */
            } /* item */
        } /* update_items */
    } /* content_area */
} /* latest_updates */
</style>
