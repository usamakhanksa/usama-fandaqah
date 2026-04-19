<template>
    <loading-view :loading="isLoading">
    <div v-if="!isLoading" >

        <div class="flex w-full mb-4">
            <nav v-if="crumbs.length">
                <ul class="breadcrumbs">
                    <li class="breadcrumbs__item" v-for="(crumb,i) in crumbs" :key="i" v-if="crumb.text != false">
                        <router-link :to="crumb.to">{{ __(crumb.text) }}</router-link>
                    </li>
                </ul>
            </nav>
        </div>


           <!-- Filters -->
           <div class="filter_area">
            
            <div class="item">
                <select
                v-model="unitCategoryId"
                v-if="unitCategories.length"
                @change="getData"
                >
                <option :value="null" :selected="true">
                    {{ __("Unit Category") }}
                </option>
                <option
                    v-for="(category, i) in unitCategories"
                    :key="i"
                    :value="category.value"
                >
                    {{ category.name }}
                </option>
                </select>
            </div>

            <!-- item -->
            <div class="reset_filters">
                <button
                @click="resetFilters"
                v-tooltip="{
                    targetClasses: ['it-has-a-tooltip'],
                    placement: 'top',
                    content: __('Reset Filters'),
                    classes: ['tooltip_reset'],
                }"
                ></button>
            </div>
            <!-- reset_filters -->
        </div>
        <!-- Filters Area -->

        <heading class="mb-1 text-90 font-normal text-2xl">{{__('Reservations Table')}}</heading>
        <div id="units_status_page">
            <div class="title_page">
                <div class="title_right">
                    <h2 v-if="days"><i class="far fa-calendar-alt"></i> {{ first_date_in_period }} - {{ last_date_in_period}}
                    </h2>
                </div><!-- title_right -->
                <div class="title_left">
                    <div class="fc-button-group">
                        <button type="button" class="fc-next-button fc-button fc-button-primary" aria-label="next" @click="getData('old')">
                            <!-- <span class="fc-icon fa fa-chevron-right"></span> --> <
                        </button>
                        <button type="button" class="fc-prev-button fc-button fc-button-primary" aria-label="prev" @click="getData('new')">
                            <!-- <span class="fc-icon fa fa-chevron-left"></span> --> >
                        </button>
                    </div><!-- fc-button-group -->
                    <button @click="getData('today')" type="button" class="fc-today-button fc-button fc-button-primary" :disabled="start_date.isSame(TODAY, 'd')">
                        {{__('Today')}}
                    </button>
                </div><!-- title_left -->
            </div><!-- title_page -->
        </div>


        <section id="units_status_table" class="relative">

            <div class="units_names">
                <div class="title">{{__('Units')}}</div>
                <div v-for="(unit, index) in units" :key="index" class="unit_name">

                     <div  v-if="unit.status == 2"  style="position: absolute;top: 6px;right:10px;z-index: 9;"> <span class="circle-cleaning"></span></div>
                    <p>{{unit.unit_number}} - {{unit.name}}</p>
                </div><!-- unit_name -->
            </div><!-- units_names -->

            <div class="all_units_inside relative">
                <loading :active.sync="tableLoading" :can-cancel="false" :loader="'spinner'" :color="'#7e7d7f'" :opacity="0.8" :is-full-page="true"></loading>

                <div class="dayes_head">
                    <div v-for="(day, index) in days" :key="index"   class="day" :class="[day.is_today ? 'time_today' : '', `day-column-${index}`]">
                        <span>{{day.name}}</span>
                        <p>{{day.number}}</p>
                    </div><!-- day -->
                </div><!-- dayes_head -->

                <div v-for="(unit, index) in units" :key="index" class="unit_item">
                    <div v-for="(day, index) in unit.days" :key="index" class="unit_price">

                            <router-link v-if="day.reservations != null && day.reservations.length > 0 && day.reservations[0].checked_in != null" :to="day.reservations[0].customer ?  `/reservation/${day.reservations[0].id}` : `/reservation-noc/${day.reservations[0].id}`">
                            <div class="customer_item reserved" :class="day.colspan">
                                <!-- the part of customer highlight -->
                                <div v-if="day.reservations[0] && day.reservations[0].reservation_type == 'single'">
                                    <span v-if="day.reservations[0] && day.reservations[0].customer && day.reservations[0].customer.highlight" :style="{'background-color': day.reservations[0].customer.highlight.color}"><p :style="{'background-color': day.reservations[0].customer.highlight.color}">{{day.reservations[0].customer.highlight.name[locale]}}</p></span>
                                    {{ day.reservations[0].customer.name}}
                                </div>
                                <div v-else>
                                    {{ day.reservations[0].company.name}}
                                </div>

                            </div>
                            </router-link>



                            <router-link v-else-if="day.reservations != null && day.reservations.length > 0 && day.reservations[0].checked_in == null" :to=" day.reservations[0].customer ?  `/reservation/${day.reservations[0].id}` : `/reservation-noc/${day.reservations[0].id}`">
                            <div class="customer_item not_logged" :class="day.colspan">
                              <div v-if="day.reservations[0] && day.reservations[0].reservation_type == 'single'">
                                   <span v-if="day.reservations[0] && day.reservations[0].customer && day.reservations[0].customer.highlight" :style="{'background-color': day.reservations[0].customer.highlight.color}"><p :style="{'background-color': day.reservations[0].customer.highlight.color}">{{day.reservations[0].customer.highlight.name[locale]}}</p></span>
                                   {{ day.reservations[0].customer.name}}
                                   <span class="hover-date">{{formatDate(day.number)}}</span>
                              </div>
                              <div v-else>
                                  {{ day.reservations[0].company.name}}
                              </div>

                            </div>
                            </router-link>

                            <template v-if="unit.continues_reservation && unit.continues_reservation.has_reservation && ( day.number ==  unit.continues_reservation.date)">
                               <div class="continues_reservation_parent">
                                   <span class="continues_reservation"></span>
                               </div>
                            </template>
                         <template v-if="!day.reservations.length" >

                            <template v-if="unit.status == 2">
                                <!-- <div style="position: absolute;top: 6px;right:10px;z-index: 9;"> <span class="circle-cleaning"></span></div> -->
                                <a href="#" @click="newReservation(day.number, unit.id)" class="price-wrapper">
                                    <span class="price">{{ day.prices.day}}</span>  <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span>
                                    <span class="hover-date" v-if="day.colspan != 'block-me'">{{formatDate(day.number)}}</span>
                                </a>

                            </template>
                            <template v-else-if="unit.status == 3">
                                {{__('Under Maintenance')}}
                            </template>
                            <template v-else>
                                <a href="#" @click="newReservation(day.number, unit.id)" class="price-wrapper">
                                    <span class="price">{{ day.prices.day}}</span> <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span>
                                    <span class="hover-date" v-if="day.colspan != 'block-me'">{{formatDate(day.number)}}</span>
                                </a>
                            </template>
                         </template>
                    </div>
                </div><!-- unit_item -->
            </div>
        </section><!-- units_status_table -->


        <div class="units_status">
            <span class="not_logged"><i></i>{{__('Reserved not checked in')}}</span>
            <span class="reserved"><i></i>{{__(' Checked In')}}</span>
        </div><!-- units_status -->

    </div>
    </loading-view>
</template>

<script>
    import Loading from 'vue-loading-overlay';
    export default {
        name: "ReservationsTable",
        components : {
            Loading
        },
        computed: {
            allUnitIds() {
                // Use map to extract all ids
                return this.units.map(item => item.id);
            },
        },
        data(){
            return {
                isLoading : false,
                tableLoading : false,
                crumbs : [],
                start_date: moment(new Date()),
                units: [],
                days: [],
                locale : 'ar',
                first_date_in_period : null,
                last_date_in_period : null,
                currency : [],
                hoveredColumn: null,
                unitCategoryId : null,
                unitCategories : [],
            }
        },
        methods:{
            async getData(status = 'today'){
              this.tableLoading = true;
              if(status == 'old'){
                  this.start_date = this.start_date.subtract(14, "days");
              }else if(status == 'new'){
                  this.start_date = this.start_date.add(14, 'days');
              }else if(status == 'today'){
                  this.start_date = moment(new Date());
              }
                await axios.get(`/nova-vendor/units/reservations-table-data?start_date=${this.start_date.format('YYYY-MM-DD')}&unit_category_id=${this.unitCategoryId}`)
                .then(response => {
                    this.units = response.data.data
             
                    this.days = response.data.meta.days
                    this.first_date_in_period = response.data.first_date_in_period
                    this.last_date_in_period = response.data.last_date_in_period
                    this.guessReservations(this.units,this.first_date_in_period);
                    this.isLoading = false;
                    this.tableLoading = false;
                    this.currency = Nova.app.currentTeam.currency
                })
          },
          newReservation(day, unit_id) {

                let startDate = new Date(day);
                let today = new Date().setHours(0,0,0,0);
                if(startDate.getTime() < today && !Nova.app.$hasPermission('booking past')){
                        this.$toasted.show(this.__('You can not book in the past'), {type: 'error'})
                        return ;
                }
                this.$router.replace({
                    name: 'new-reservation',
                    params: {date: day, room_id: unit_id}
                })
          },
            guessReservations(units,date){

                    Nova.request().post('/nova-vendor/units/reservations-table/guess-reservations',{
                        unit_ids : this.allUnitIds,
                        date : date
                    })

                    .then(response => {


                        let units_that_holds_a_reservation = Object.values(response.data)


                        // Convert keyValueArray to a Map
                        const keyValueMap = new Map(units_that_holds_a_reservation.map(kv => [kv.key, kv.value]));


                        let new_units = [];
                        // Check for matches
                        units.forEach(obj => {

                            if (keyValueMap.has(obj.id)) {

                                obj.days = obj.days;
                                obj.id = obj.id;
                                obj.name = obj.name;
                                obj.status = obj.status;
                                obj.unit_number = obj.unit_number;
                                obj.continues_reservation = {
                                    has_reservation : true,
                                    date : date,
                                    res_number : keyValueMap.get(obj.id).res_number,
                                    res_id :  keyValueMap.get(obj.id).res_id,
                                }

                            }else{
                                obj.days = obj.days;
                                obj.id = obj.id;
                                obj.name = obj.name;
                                obj.status = obj.status;
                                obj.unit_number = obj.unit_number;
                                obj.continues_reservation = {
                                    has_reservation : false,
                                    date : date,
                                    res_number : null,
                                    res_id :  null,
                                }
                            }

                            new_units.push(obj);
                        });

                        this.units = new_units;
                    })
            },
            formatDate(date) {
                return Nova.app.__(moment(date).format('dddd')) + ' - ' + moment(date).format('YYYY/MM/DD');
            },
            getUnitCategoryFilterValues() {
                Nova.request()
                .get("/nova-vendor/calender/reservations/unit-category-filter-values")
                .then((response) => {
                    this.unitCategories = response.data;
                });
            },
            resetFilters() {
                this.unitCategoryId = null;
                this.getData();
            },
        },
        mounted(){
            this.crumbs = [
                {
                    text: 'Home',
                    to: '/dashboards/main',
                },
                {
                    text: 'Reservations Table',
                    to: '#',
                }
            ];
            this.isLoading = true;
            this.getData();

            this.locale = Nova.config.local ;
            this.locale == 'en' ? this.textAlign = 'text-left' : this.textAlign = 'text-right' ;

            this.getUnitCategoryFilterValues();

        }
    }
    // console.log(currency);
</script>

<style lang="scss" scoped>
    #units_status_page {
        .title_page {
            margin-bottom: 15px;
            display: flex;
            -webkit-box-pack: justify;
            justify-content: space-between;
            -webkit-box-align: center;
            align-items: center;
            @media (min-width: 320px) and (max-width: 767px) {
                display: block;
            } /* mobile */
            h2 {
                color: #000;
                font-size: 19px;
                i {
                    font-size: 16px;
                } /* i */
            } /* h2 */
            .title_left {
                .fc-button-primary.fc-today-button {
                    color: #fff;
                    background-color:#2C3E50;
                    border-color:#2C3E50;
                    opacity: 0.65;
                    display: inline-block;
                    font-weight: 400;
                    text-align: center;
                    vertical-align: middle;
                    -webkit-user-select: none;
                    -moz-user-select: none;
                    -ms-user-select: none;
                    user-select: none;
                    border: 1px solid transparent;
                    padding: 0.4em 0.65em;
                    font-size: 1em;
                    line-height: 1.5;
                    border-radius: 0.25em;
                } /* fc-button-primary */
                .fc-button-group {
                    position: relative;
                    display: inline-flex;
                    vertical-align: middle;
                    margin: 0 0 0 .75em;
                    [dir="ltr"] & {
                        margin: 0 .75em 0 0;
                    } /* ltr */
                    .fc-button {
                        overflow: visible;
                        text-transform: none;
                        margin: 0;
                        font-family: inherit;
                        padding: 0.4em 0.84em;
                        font-size: 1em;
                        line-height: 1.5;
                        // border-radius: 0.25em;
                        border: 1px solid transparent;
                        user-select: none;
                        display: inline-block;
                        font-weight: 400;
                        text-align: center;
                    } /* fc-button */
                    .fc-button-primary {
                        color: #fff;
                        background-color: #2C3E50;
                        border-color: #2C3E50;
                    } /* fc-button-primary */
                    .fc-button {
                        position: relative;
                        -webkit-box-flex: 1;
                        -ms-flex: 1 1 auto;
                        flex: 1 1 auto;
                        &:not(:disabled) {cursor: pointer;}
                        &:not(:last-child) {
                            border-top-left-radius: 0;
                            border-bottom-left-radius: 0;
                        } /* last-child */
                        &:not(:first-child) {
                            border-top-right-radius: 0;
                            border-bottom-right-radius: 0;
                            margin-left: -1px;
                        } /* first-child */
                        .fc-icon {
                            vertical-align: middle;
                            font-size: 1.1em;
                        } /* fc-icon */
                        &:hover {
                            color: #fff;
                            background-color: #1e2b37;
                            border-color: #1a252f;
                        } /* hover */
                    } /* fc-button */
                } /* fc-button-group */
            } /* title_left */
        } /* title_page */
        .table_area {
            position: relative;
            width: 100%;
            scrollbar-width: thin;
            scrollbar-color: #ccc #f5f5f5;
            &::-webkit-scrollbar {width: 6px;}
            &::-webkit-scrollbar-track {background: #f5f5f5;}
            &::-webkit-scrollbar-thumb {background: #ccc;}
            &::-webkit-scrollbar-thumb:window-inactive {background: #f5f5f5;}
            @media (min-width: 320px) and (max-width: 991px) {
                overflow: auto;
            } /* mobile */
            .another_table {
                overflow: auto;
                width: 100%;
                padding: 0 150px 0 0;
                scrollbar-width: thin;
                scrollbar-color: #ccc #f5f5f5;
                &::-webkit-scrollbar {width: 6px;}
                &::-webkit-scrollbar-track {background: #f5f5f5;}
                &::-webkit-scrollbar-thumb {background: #ccc;}
                &::-webkit-scrollbar-thumb:window-inactive {background: #f5f5f5;}
                @media (min-width: 320px) and (max-width: 991px) {
                    position: absolute;
                    float: right;
                    overflow: hidden;
                    width: auto;
                } /* mobile */
                [dir="ltr"] & {
                    padding: 0 0 0 150px;
                    @media (min-width: 320px) and (max-width: 767px) {
                        float: left;
                    } /* mobile */
                } /* ltr */
            } /* another_table */
            table {
                table-layout: fixed;
                white-space: nowrap;
                width: 100%;
                thead {
                    tr {
                        th {
                            background: #fff;
                            white-space: nowrap;
                            overflow: hidden;
                            border: 1px solid #707070;
                            padding: 5px;
                            width: 140px;
                            text-align: center;
                            vertical-align: middle;
                            font-size: 17px;
                            font-weight: normal;
                            line-height: 22px;
                            height: 55px;
                            &.time_today {background: #FCF8E3;}
                        } /* th */
                    } /* tr */
                } /* thead */
                tbody {
                    tr {
                        td {
                            background: #fff;
                            white-space: nowrap;
                            overflow: hidden;
                            border: 1px solid #707070;
                            padding: 0 5px;
                            text-align: center;
                            font-size: 17px;
                            vertical-align: middle;
                            height: 61px;
                            &.time_today {background: #FCF8E3;}
                            .price {
                                direction: rtl;
                                font-size: 16px;
                                i {
                                    font-style: normal;
                                    font-size: 17px;
                                    font-weight: normal;
                                } /* i */
                            } /* price */
                            .Is_checked_in {
                                background: #E0CFE2;
                                border-radius: 5px;
                                padding: 3px 5px;
                                height: 40px;
                                border: 2px solid #EDE1EF;
                                color: #000000;
                                line-height: 30px;
                                white-space: nowrap;
                                overflow: hidden;
                                font-size: 15px;
                                text-align: right;
                                a {display: block;}
                                img {
                                    float: right;
                                    height: 30px;
                                    width: 30px;
                                    display: block;
                                    margin: 0 0 0 5px;
                                    [dir="ltr"] & {
                                        float: left;
                                        margin: 0 5px 0 0;
                                    } /* ltr */
                                } /* img */
                            } /* Is_checked_in */
                            .Is_waiting {
                                background: #BDDAE3;
                                border-radius: 5px;
                                padding: 3px 5px;
                                height: 40px;
                                border: 2px solid #D8ECF2;
                                color: #000000;
                                line-height: 30px;
                                white-space: nowrap;
                                overflow: hidden;
                                font-size: 15px;
                                text-align: right;
                                [dir="ltr"] & {
                                    text-align: left;
                                } /* ltr */
                                a {display: block;}
                                img {
                                    float: right;
                                    height: 30px;
                                    width: 30px;
                                    display: block;
                                    margin: 0 0 0 5px;
                                    [dir="ltr"] & {
                                        float: left;
                                        margin: 0 5px 0 0;
                                    } /* ltr */
                                } /* img */
                            } /* Is_waiting */
                            span.tag {
                                display: block;
                                float: right;
                                padding: 0 10px;
                                margin: 5px auto 5px 5px;
                                height: 20px;
                                border-radius: 4px;
                                line-height: 20px;
                                font-size: 14px;
                                border: 1px solid;
                                [dir="ltr"] & {
                                    float: left;
                                    margin: 5px 5px 5px auto;
                                } /* ltr */
                            } /* span.tag */
                            a {
                                .customer_label {
                                    float: right;
                                    margin: 0 0 0 5px;
                                    [dir="ltr"] & {
                                        float: left;
                                        margin: 0 5px 0 0;
                                    } /* ltr */
                                    label.customer-label {
                                        display: block !important;
                                        min-width: 50px;
                                        border-radius: 100px;
                                        border-width: 1px;
                                        font-size: 14px;
                                        text-align: center;
                                        padding: 0 5px !important;
                                        border-style: solid;
                                        color: #000000;
                                    } /* label.customer-label */
                                } /* customer_label */
                            } /* a */
                        } /* td */
                    } /* tr */
                } /* tbody */
                &.units_row {
                    position: absolute;
                    right: 0;
                    top: 0;
                    z-index: 9;
                    width: 150px;
                    @media (min-width: 320px) and (max-width: 991px) {
                        position: relative;
                        float: right;
                    } /* mobile */
                    [dir="ltr"] & {
                        right: auto;
                        left: 0;
                    } /* ltr */
                    tbody {
                        tr {
                            td {
                                text-align: right;
                                background: #F4F7FA;
                                line-height: 20px;
                                [dir="ltr"] & {
                                    text-align: left;
                                } /* ltr */
                            } /* td */
                        } /* tr */
                    } /* tbody */
                } /* units_row */
            } /* table */
        } /* table_area */
    } /* units_status_page */
    .status_block {
        text-align: left;
        margin: 20px auto 0;
        [dir="ltr"] & {
            text-align: right;
        } /* ltr */
        span {
            display: inline-block;
            height: 20px;
            line-height: 20px;
            margin: 0 20px 0 0;
            color: #000;
            [dir="ltr"] & {
                margin: 0 0 0 20px;
            } /* ltr */
            i {
                display: block;
                height: 20px;
                width: 20px;
                border-radius: 100%;
                border: 2px solid;
                float: left;
                margin: 0 5px 0 0;
                color: #000;
                [dir="ltr"] & {
                    float: right;
                    margin: 0 0 0 5px;
                } /* ltr */
            } /* i */
            &.Is_checked_in {
                i {
                    background: #E0CFE2;
                    border-color: #EDE1EF;
                } /* i */
            } /* Is_checked_in */
            &.Is_waiting {
                i {
                    background: #BDDAE3;
                    border-color: #D8ECF2;
                } /* i */
            } /* Is_waiting */
        } /* span */
    } /* status_block */


    #units_status_table {
        position: relative;
        padding: 0 140px 0 0;
        @media (min-width: 320px) and (max-width: 767px) {
            padding:  0;
            overflow-x: auto;
            overflow-y: hidden;
            display: flex;
            align-items: flex-start;
            &::-webkit-scrollbar {
                width: 5px;
                height: 12px;
            }
            &::-webkit-scrollbar-track {background: #f1f1f1;}
            &::-webkit-scrollbar-thumb {
                background: #999999;
                border-radius: 6px;
            }
            &::-webkit-scrollbar-thumb:hover {background: #777777;}
        } /* mobile */
        [dir="ltr"] & {
            padding: 0 0 0 140px;
            @media (min-width: 320px) and (max-width: 767px) {
                padding:  0;
            } /* mobile */
        } /* ltr */
        .vld-overlay {
          z-index: 999;
        } /* vld-overlay */

        .all_units_inside {
            overflow-x: auto;
            overflow-y: hidden;
            &::-webkit-scrollbar {
                width: 5px;
                height: 12px;
            }
            &::-webkit-scrollbar-track {background: #f1f1f1;}
            &::-webkit-scrollbar-thumb {
                background: #999999;
                border-radius: 6px;
            }
            &::-webkit-scrollbar-thumb:hover {background: #777777;}
            @media (min-width: 320px) and (max-width: 767px) {
                overflow: initial;
            } /* mobile */
        }

        .units_names {
            position: absolute;
            right: 0;
            top: 0;
            @media (min-width: 320px) and (max-width: 767px) {
                position: relative;
            } /* mobile */
            [dir="ltr"] & {
                left: 0;
                right: auto;
            } /* ltr */

            .title {
                background: #fff;
                white-space: nowrap;
                border: 1px solid #707070;
                padding: 5px;
                width: 140px;
                overflow: hidden;
                text-align: center;
                font-size: 16px;
                font-weight: normal;
                line-height: 22px;
                height: 70px;
                display: flex;
                align-items: center;
                justify-content: center;
                position: sticky;
                right: 0;
                top: 0;
                z-index: 99;
                [dir="ltr"] & {
                    left: 0;
                    right: auto;
                } /* ltr */
            }

            .unit_name {
                position: sticky;
                right: 0;
                top: 0;
                background: #F4F7FA;
                white-space: nowrap;
                border: 1px solid #707070;
                padding: 0 5px;
                font-size: 16px;
                height: 70px;
                width: 140px;
                overflow: hidden;
                border-top: none;
                display: flex;
                align-items: flex-start;
                justify-content: center;
                flex-direction: column;
                z-index: 99;
                [dir="ltr"] & {
                    left: 0;
                    right: auto;
                } /* ltr */
                p {
                  white-space: break-spaces;
                  display: block;
                  line-height: 19px;
                } /* p */
            } /* unit_name */
        } /* units_names */

        .dayes_head {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            flex-wrap: nowrap;
            position: relative;

            .day {
                background: #fff;
                white-space: nowrap;
                border: 1px solid #707070;
                padding: 5px;
                width: 140px;
                min-width: 140px;
                text-align: center;
                font-size: 16px;
                font-weight: normal;
                line-height: 22px;
                height: 70px;
                display: flex;
                align-items: center;
                justify-content: center;
                flex-direction: column;
                border-right: 0;
                [dir="ltr"] & {
                    border-left: 0;
                    border-right: 1px solid #707070;
                }
                &.time_today {
                    background: #FCF8E3;
                }
            } /* day */
        } /* dayes_head */
        .unit_item {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            flex-wrap: nowrap;
            position: relative;
            height: 70px;
            overflow: hidden;
            width: 1960px;
            .unit_price {
                background: #ffffff;
                white-space: nowrap;
                border: 1px solid #707070;
                padding: 0 5px;
                font-size: 16px;
                height: 70px;
                min-width: 140px;
                width: 140px;
                border-right: none;
                border-top: none;
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                position: relative;
                [dir="ltr"] & {
                    border-left: none;
                    border-right: 1px solid #707070;
                } /* ltr */
                i {
                    display: inline-block;
                    margin: 0 5px 0 0;
                    font-style: normal;
                    font-weight: normal;
                    [dir="ltr"] & {
                        margin: 0 0 0 5px;
                    } /* ltr */
                } /* i */
                .customer_item {
                    color: #fff;
                    font-size: 15px;
                    text-align: center;
                    border-radius: 100px;
                    height: 40px;
                    line-height: 40px;
                    z-index: 9;
                    position: absolute;
                    right: 35px;
                    top: 15px;
                    width: 135px;
                    overflow: hidden;
                    display: flex;
                    justify-content: center;
                    [dir="ltr"] & {
                        left: 35px;
                        right: auto;
                    } /* ltr */
                    span {
                      display: block;
                      width: 15px;
                      height: 15px;
                      border-radius: 100%;
                      margin: 0 0 0 5px;
                      position: relative;
                      [dir="ltr"] & {
                        margin: 0 5px 0 0;
                      } /* ltr */
                      p {
                        position: absolute;
                        right: 100%;
                        height: 20px;
                        white-space: nowrap;
                        padding: 0 10px;
                        border-radius: 100px;
                        line-height: 20px;
                        font-size: 14px;
                        top: -2.5px;
                        margin: 0 5px 0 0;
                        display: none;
                        [dir="ltr"] & {
                          left: 100%;
                          right: auto;
                          margin: 0 0 0 5px;
                        } /* ltr */
                      } /* p */
                      &:hover {
                        p {
                          display: block;
                        } /* p */
                      } /* hover */
                    } /* span */
                    &.reserved {
                        background: #2ACB6E;
                    } /* reserved */
                    &.not_logged {
                        background: #06B0FF;
                    } /* not_logged */
                    &.day-2 {
                        width: 275px;
                    } /* day-2 */
                    &.day-3 {
                        width: 415px;
                    } /* day-3 */
                    &.day-4 {
                        width: 555px;
                    } /* day-4 */
                    &.day-5 {
                        width: 695px;
                    } /* day-5 */
                    &.day-6 {
                        width: 835px;
                    } /* day-6 */
                    &.day-7 {
                        width: 975px;
                    } /* day-7 */
                    &.day-8 {
                        width: 1115px;
                    } /* day-8 */
                    &.day-9 {
                        width: 1255px;
                    } /* day-9 */
                    &.day-10 {
                        width: 1395px;
                    } /* day-10 */
                    &.day-11 {
                        width: 1535px;
                    } /* day-11 */
                    &.day-12 {
                        width: 1675px;
                    } /* day-12 */
                    &.day-13 {
                        width: 1815px;
                    } /* day-13 */
                    &.day-14 {
                        width: 1955px;
                    } /* day-14 */
                    &.day-15 {
                        width: 2095px;
                    } /* day-15 */
                } /* customer_item */
            } /* unit_price */
        } /* unit_item */
    } /* units_status_table */

    .units_status {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        flex-wrap: nowrap;
        margin: 20px 0 0;
        span {
            color: #000;
            font-size: 16px;
            margin: 0 0 0 30px;
            padding: 0 0 0 25px;
            line-height: 17px;
            position: relative;
            [dir="ltr"] & {
                margin: 0 30px 0 0;
                padding: 0 25px 0 0;
            } /* ltr */
            &:last-child {
                margin: 0;
            } /* last-child */
            &:after {
                content: "";
                position: absolute;
                left: 0;
                top: 0;
                height: 17px;
                width: 17px;
                border-radius: 100%;
                display: block;
                [dir="ltr"] & {
                    right: 0;
                    left: auto;
                } /* ltr */
            } /* after */
            &.not_logged {
                &:after {
                    background: #06B0FF;
                } /* after */
            } /* not_logged */
            &.reserved {
                &:after {
                    background: #2ACB6E;
                } /* after */
            } /* reserved */
        } /* span */
    } /* units_status */

    .circle-cleaning{
        height: 10px;
        width: 10px;
        background-color: rgb(255, 145, 0) !important;
        border-radius: 50%;
        display: inline-block;
        bottom: 0.23em;
        position: relative;
        margin-left: 0.1em;
        margin-right: 0.1em;
    }

    .continues_reservation_parent{
        display: flex;
        .continues_reservation {
            width: 20px;
            height: 35px;
            background: #2ACB6E;
            position: absolute;
            top: 15px;
            [lang="en"] & {
                left: 0px;
                border-top-right-radius: 100px;
                border-bottom-right-radius: 100px;
            }
            [lang="ar"] & {
                right: 0px;
                border-top-left-radius: 100px;
                border-bottom-left-radius: 100px;
            }



        }
    }

    .unit_price {
        position: relative;

        .day-tooltip {
            display: none;
            position: absolute;
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 14px;
            top: -30px;
            left: 50%;
            transform: translateX(-50%);
            white-space: nowrap;
            z-index: 1000;

            &:after {
                content: '';
                position: absolute;
                bottom: -5px;
                left: 50%;
                transform: translateX(-50%);
                width: 0;
                height: 0;
                border-left: 5px solid transparent;
                border-right: 5px solid transparent;
                border-top: 5px solid rgba(0, 0, 0, 0.8);
            }
        }

        &:hover {
            .day-tooltip {
                display: block;
            }
        }
    }

    .price-wrapper {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        height: 100%;
        text-decoration: none;

        .price {
            display: block;
            transition: opacity 0.2s;
        }

        .hover-date {
            display: none;
            position: absolute;
            left: 50%;
            top: 15%;
            transform: translate(-50%, -50%);
            // background:rgba(255, 0, 0, 0.4);;
            background: #1a1919;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            white-space: nowrap;
            font-size: 12px;
            min-width: 100px;
            text-align: center;
        }

        &:hover {
            .price {
                // opacity: 0;
            }

            .hover-date {
                display: block;
            }
        }
    }

    // Remove the day-tooltip styles since we're not using them anymore
    .unit_price .day-tooltip {
        display: none;
    }

    .filter_area {
      display: flex;
      align-items: center;
      flex-wrap: wrap;
      justify-content: flex-start;
      margin: 0 -10px;
      .item {
        width: 20%;
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
            background-color: #5e6d83;
          } /* hover */
        } /* button */
      } /* reset_filters */
    } /* filter_area */
</style>
