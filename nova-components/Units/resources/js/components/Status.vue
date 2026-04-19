<template>
    <loading-view :loading="initialLoading">
        <div v-if="!initialLoading">
            <heading class="mb-1 text-90 font-normal text-2xl">{{__('Units Status')}}</heading>
            <div id="units_status_page">
                <div class="title_page">
                    <div class="title_right">
                        <h2><i class="far fa-calendar-alt"></i> {{ days[0].number}} - {{ days[13].number}}
                        </h2>
                    </div><!-- title_right -->
                    <div class="title_left">
                        <div class="fc-button-group">
                            <button type="button" class="fc-next-button fc-button fc-button-primary" aria-label="next" @click="getUnits('old')">
                                <span class="fc-icon fa fa-chevron-right"></span>
                            </button>
                            <button type="button" class="fc-prev-button fc-button fc-button-primary" aria-label="prev" @click="getUnits('new')"><span class="fc-icon fa fa-chevron-left"></span></button>
                        </div><!-- fc-button-group -->
                        <button @click="getUnits('today')" type="button" class="fc-today-button fc-button fc-button-primary" :disabled="start_date.isSame(TODAY, 'd')">
                            {{__('Today')}}
                        </button>
                    </div><!-- title_left -->
                </div><!-- title_page -->
                <div class="table_area">
                    <table class="units_row">
                        <thead>
                        <tr><th>{{__('Units')}}</th></tr>
                        </thead>
                        <tbody>
                        <tr v-for="(unit, index) in units" :key="index">
                            <td>
                                <div class="unit_number">{{ unit.unit_number}}</div>
                                <div class="unit_name"> {{ unit.name}}</div>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                    <div class="another_table">
                        <table>
                            <thead>
                            <tr>
                                <th :class="{ time_today: day.is_today }" v-for="(day, index) in days" :key="index" >
                                    <p>{{day.name}}</p>
                                    <span>{{day.number}}</span>
                                </th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="(unit, index) in units" :key="index">
                                <td v-for="(day, index) in unit.days" :key="index" :colspan="day.colspan">
                                    <div v-if="day.reservations != null && day.reservations.length > 0 && day.reservations[0].checked_in != null" class="Is_checked_in">
                                        <router-link :to="'/reservation/'+day.reservations[0].id">
                                            <div class="customer_label" v-if="day.reservations[0].customer.label" v-html="day.reservations[0].customer.label"></div>
                                            {{ day.reservations[0].customer.name}}
                                        </router-link>
                                    </div>
                                    <div v-if="day.reservations != null && day.reservations.length > 0 && day.reservations[0].checked_in == null" class="Is_waiting">
                                        <router-link :to="'/reservation/'+day.reservations[0].id">
                                            <div class="customer_label" v-if="day.reservations[0].customer.label" v-html="day.reservations[0].customer.label"></div>
                                            {{ day.reservations[0].customer.name}}
                                        </router-link>
                                    </div>
                                    <div v-if="unit.status == 2" class="price">
                                        {{__('Under Cleaning')}}
                                    </div>
                                    <div v-if="unit.status == 3" class="price">
                                        {{__('Under Maintenance')}}
                                    </div>
                                    <div v-if="unit.status == 1 && day.reservations != null && day.reservations.length == 0" class="price">
                                        <a href="#" @click="newReservation(day.number, unit.id)">
                                            {{ day.prices.day}} <i>{{__(currency)}}</i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div><!-- units_status_page -->
            <div class="clrearfix" style="clear: both;"></div>
            <div class="status_block">
                <span class="Is_waiting"><i></i>{{__('Pending')}}</span>
                <span class="Is_checked_in"><i></i>{{__('Login Now')}}</span>
            </div><!-- status_block -->
        </div>






        <section id="units_status_table">

            <div class="units_names">
                <div class="title">{{__('Units')}}</div>
                <div class="unit_name">
                    <span>1</span>
                    <p>غرفة مفردة</p>
                </div><!-- unit_name -->
                <div class="unit_name">
                    <span>2</span>
                    <p>غرفة سوبيريور كوين</p>
                </div><!-- unit_name -->
                <div class="unit_name">
                    <span>3</span>
                    <p>اندلس</p>
                </div><!-- unit_name -->
                <div class="unit_name">
                    <span>4</span>
                    <p>غرفة ستوديو</p>
                </div><!-- unit_name -->
            </div><!-- units_names -->

            <div class="all_units_inside">

                <div class="dayes_head">
                    <div class="day time_today">
                        <span>الاحد</span>
                        <p>2020-08-09</p>
                    </div><!-- day -->
                    <div class="day">
                        <span>الاثنين</span>
                        <p>2020-08-10</p>
                    </div><!-- day -->
                    <div class="day">
                        <span>الثلاثاء</span>
                        <p>2020-08-09</p>
                    </div><!-- day -->
                    <div class="day">
                        <span>الاربعاء</span>
                        <p>2020-08-09</p>
                    </div><!-- day -->
                    <div class="day">
                        <span>الخميس</span>
                        <p>2020-08-09</p>
                    </div><!-- day -->
                    <div class="day">
                        <span>الجمعة</span>
                        <p>2020-08-09</p>
                    </div><!-- day -->
                    <div class="day">
                        <span>السبت</span>
                        <p>2020-08-09</p>
                    </div><!-- day -->
                    <div class="day">
                        <span>الاحد</span>
                        <p>2020-08-09</p>
                    </div><!-- day -->
                    <div class="day">
                        <span>الاثنين</span>
                        <p>2020-08-09</p>
                    </div><!-- day -->
                    <div class="day">
                        <span>الثلاثاء</span>
                        <p>2020-08-09</p>
                    </div><!-- day -->
                    <div class="day">
                        <span>الاربعاء</span>
                        <p>2020-08-09</p>
                    </div><!-- day -->
                    <div class="day">
                        <span>الخميس</span>
                        <p>2020-08-09</p>
                    </div><!-- day -->
                    <div class="day">
                        <span>الجمعة</span>
                        <p>2020-08-09</p>
                    </div><!-- day -->
                    <div class="day">
                        <span>السبت</span>
                        <p>2020-08-09</p>
                    </div><!-- day -->
                    <div class="day">
                        <span>الاحد</span>
                        <p>2020-08-09</p>
                    </div><!-- day -->
                    <div class="day">
                        <span>الاثنين</span>
                        <p>2020-08-09</p>
                    </div><!-- day -->
                </div><!-- dayes_head -->

                <div class="unit_item">
                    <div class="unit_price">
                        100 <i>ريال</i>
                    </div><!-- unit_price -->
                    <div class="unit_price">
                        100 <i>ريال</i>
                        <div class="customer_item not_logged">
                            سعيد احمد علي
                        </div><!-- customer_item -->
                    </div><!-- unit_price -->
                    <div class="unit_price">
                        100 <i>ريال</i>
                        <div class="customer_item reserved">
                            محمد محمود احمد
                        </div><!-- customer_item -->
                    </div><!-- unit_price -->
                    <div class="unit_price">
                        100 <i>ريال</i>
                        <div class="customer_item not_logged day-3">
                            عبد السلام النابلسى محمود
                        </div><!-- customer_item -->
                    </div><!-- unit_price -->
                    <div class="unit_price">
                        100 <i>ريال</i>
                    </div><!-- unit_price -->
                    <div class="unit_price">
                        100 <i>ريال</i>
                    </div><!-- unit_price -->
                    <div class="unit_price">
                        100 <i>ريال</i>
                    </div><!-- unit_price -->
                    <div class="unit_price">
                        100 <i>ريال</i>
                    </div><!-- unit_price -->
                    <div class="unit_price">
                        100 <i>ريال</i>
                    </div><!-- unit_price -->
                    <div class="unit_price">
                        100 <i>ريال</i>
                    </div><!-- unit_price -->
                    <div class="unit_price">
                        100 <i>ريال</i>
                    </div><!-- unit_price -->
                    <div class="unit_price">
                        100 <i>ريال</i>
                    </div><!-- unit_price -->
                    <div class="unit_price">
                        100 <i>ريال</i>
                    </div><!-- unit_price -->
                    <div class="unit_price">
                        100 <i>ريال</i>
                    </div><!-- unit_price -->
                    <div class="unit_price">
                        100 <i>ريال</i>
                    </div><!-- unit_price -->
                    <div class="unit_price">
                        100 <i>ريال</i>
                    </div><!-- unit_price -->
                </div><!-- unit_item -->

                <div class="unit_item">
                    <div class="unit_price">
                        100 <i>ريال</i>
                    </div><!-- unit_price -->
                    <div class="unit_price">
                        100 <i>ريال</i>
                    </div><!-- unit_price -->
                    <div class="unit_price">
                        100 <i>ريال</i>
                    </div><!-- unit_price -->
                    <div class="unit_price">
                        100 <i>ريال</i>
                    </div><!-- unit_price -->
                    <div class="unit_price">
                        100 <i>ريال</i>
                    </div><!-- unit_price -->
                    <div class="unit_price">
                        100 <i>ريال</i>
                    </div><!-- unit_price -->
                    <div class="unit_price">
                        100 <i>ريال</i>
                    </div><!-- unit_price -->
                    <div class="unit_price">
                        100 <i>ريال</i>
                    </div><!-- unit_price -->
                    <div class="unit_price">
                        100 <i>ريال</i>
                    </div><!-- unit_price -->
                    <div class="unit_price">
                        100 <i>ريال</i>
                    </div><!-- unit_price -->
                    <div class="unit_price">
                        100 <i>ريال</i>
                    </div><!-- unit_price -->
                    <div class="unit_price">
                        100 <i>ريال</i>
                    </div><!-- unit_price -->
                    <div class="unit_price">
                        100 <i>ريال</i>
                    </div><!-- unit_price -->
                    <div class="unit_price">
                        100 <i>ريال</i>
                    </div><!-- unit_price -->
                    <div class="unit_price">
                        100 <i>ريال</i>
                    </div><!-- unit_price -->
                    <div class="unit_price">
                        100 <i>ريال</i>
                    </div><!-- unit_price -->
                </div><!-- unit_item -->

                <div class="unit_item">
                    <div class="unit_price">
                        100 <i>ريال</i>
                        <div class="customer_item reserved day-2">
                            محمد محمود احمد
                        </div><!-- customer_item -->
                    </div><!-- unit_price -->
                    <div class="unit_price">
                        100 <i>ريال</i>
                    </div><!-- unit_price -->
                    <div class="unit_price">
                        100 <i>ريال</i>
                        <div class="customer_item not_logged day-3">
                            عبد السلام النابلسى محمود
                        </div><!-- customer_item -->
                    </div><!-- unit_price -->
                    <div class="unit_price">
                        100 <i>ريال</i>
                    </div><!-- unit_price -->
                    <div class="unit_price">
                        100 <i>ريال</i>
                    </div><!-- unit_price -->
                    <div class="unit_price">
                        100 <i>ريال</i>
                    </div><!-- unit_price -->
                    <div class="unit_price">
                        100 <i>ريال</i>
                    </div><!-- unit_price -->
                    <div class="unit_price">
                        100 <i>ريال</i>
                    </div><!-- unit_price -->
                    <div class="unit_price">
                        100 <i>ريال</i>
                    </div><!-- unit_price -->
                    <div class="unit_price">
                        100 <i>ريال</i>
                    </div><!-- unit_price -->
                    <div class="unit_price">
                        100 <i>ريال</i>
                    </div><!-- unit_price -->
                    <div class="unit_price">
                        100 <i>ريال</i>
                    </div><!-- unit_price -->
                    <div class="unit_price">
                        100 <i>ريال</i>
                    </div><!-- unit_price -->
                    <div class="unit_price">
                        100 <i>ريال</i>
                    </div><!-- unit_price -->
                    <div class="unit_price">
                        100 <i>ريال</i>
                    </div><!-- unit_price -->
                    <div class="unit_price">
                        100 <i>ريال</i>
                    </div><!-- unit_price -->
                </div><!-- unit_item -->

                <div class="unit_item">
                    <div class="unit_price">
                        100 <i>ريال</i>
                        <div class="customer_item reserved day-4">
                            محمد محمود احمد
                        </div><!-- customer_item -->
                    </div><!-- unit_price -->
                    <div class="unit_price">
                        100 <i>ريال</i>
                    </div><!-- unit_price -->
                    <div class="unit_price">
                        100 <i>ريال</i>
                    </div><!-- unit_price -->
                    <div class="unit_price">
                        100 <i>ريال</i>
                    </div><!-- unit_price -->
                    <div class="unit_price">
                        100 <i>ريال</i>
                    </div><!-- unit_price -->
                    <div class="unit_price">
                        100 <i>ريال</i>
                        <div class="customer_item not_logged day-5">
                            عبد السلام النابلسى محمود
                        </div><!-- customer_item -->
                    </div><!-- unit_price -->
                    <div class="unit_price">
                        100 <i>ريال</i>
                    </div><!-- unit_price -->
                    <div class="unit_price">
                        100 <i>ريال</i>
                    </div><!-- unit_price -->
                    <div class="unit_price">
                        100 <i>ريال</i>
                    </div><!-- unit_price -->
                    <div class="unit_price">
                        100 <i>ريال</i>
                    </div><!-- unit_price -->
                    <div class="unit_price">
                        100 <i>ريال</i>
                    </div><!-- unit_price -->
                    <div class="unit_price">
                        100 <i>ريال</i>
                    </div><!-- unit_price -->
                    <div class="unit_price">
                        100 <i>ريال</i>
                    </div><!-- unit_price -->
                    <div class="unit_price">
                        100 <i>ريال</i>
                    </div><!-- unit_price -->
                    <div class="unit_price">
                        100 <i>ريال</i>
                    </div><!-- unit_price -->
                    <div class="unit_price">
                        100 <i>ريال</i>
                    </div><!-- unit_price -->
                </div><!-- unit_item -->

            </div><!-- all_units_inside -->

        </section><!-- units_status_table -->

        <div class="units_status">
            <span class="not_logged"><i></i>{{__('Reserved not logged in')}}</span>
            <span class="reserved"><i></i>{{__('Reserved')}}</span>
        </div><!-- units_status -->



    </loading-view>
</template>

<script>
    export default {
        props: {
            textAlign : {
                type : String ,
                default : "text-left"
            }
        },
        data: () => {
            return {
                loading: false,
                currency :Nova.app.currentTeam.currency,
                units: [],
                days: [],
                start_date: moment(new Date()),
                initialLoading : true
            }
        },
        mounted() {
            this.initialLoading = true;
            this.getUnits();
            let lang = Nova.config.local ;
            lang == 'en' ? this.textAlign = 'text-left' : this.textAlign = 'text-right' ;

        },
        methods: {
            async getUnits(status){
                // this.units = []
                // this.days = []

                if(status == 'old'){
                    this.start_date = this.start_date.subtract(14, "days");
                }else if(status == 'new'){
                    this.start_date = this.start_date.add(14, 'days');
                }else if(status == 'today'){
                    this.start_date = moment(new Date());
                }

                await axios.get('/nova-vendor/units/status',{
                    params: {
                        date_in: this.start_date.format('YYYY-MM-DD'),
                    }
                })
                    .then(response => {
                        this.units = response.data.data
                        this.days = response.data.meta.days
                        this.initialLoading = false;
                    })
                    .catch(err => {
                        this.loading = false;
                        this.$toasted.show(this.__(err), {type: 'error'})
                    })
            },
            newReservation(day, unit_id) {
                this.$router.replace({
                    name: 'new-reservation',
                    params: {date: day, room_id: unit_id}
                })
            },
        },

    }
</script>

<style lang="scss">
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
                        border-radius: 0.25em;
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
            &::-webkit-scrollbar {width: 5px;height: 5px;}
            &::-webkit-scrollbar-track {background: #f1f1f1;}
            &::-webkit-scrollbar-thumb {background: #999999;}
            &::-webkit-scrollbar-thumb:hover {background: #777777;}
        } /* mobile */
        [dir="ltr"] & {
            padding: 0 0 0 140px;
            @media (min-width: 320px) and (max-width: 767px) {
                padding:  0;
            } /* mobile */
        } /* ltr */
        .all_units_inside {
            overflow-x: auto;
            overflow-y: hidden;
            &::-webkit-scrollbar {width: 5px;height: 5px;}
            &::-webkit-scrollbar-track {background: #f1f1f1;}
            &::-webkit-scrollbar-thumb {background: #999999;}
            &::-webkit-scrollbar-thumb:hover {background: #777777;}
            @media (min-width: 320px) and (max-width: 767px) {
                overflow: initial;
            } /* mobile */
        } /* all_units_inside */
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
                min-width: 140px;
                text-align: center;
                font-size: 17px;
                font-weight: normal;
                line-height: 22px;
                height: 60px;
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
            } /* title */
            .unit_name {
                background: #F4F7FA;
                white-space: nowrap;
                border: 1px solid #707070;
                padding: 0 5px;
                font-size: 17px;
                height: 60px;
                min-width: 140px;
                border-top: none;
                position: sticky;
                right: 0;
                top: 0;
                display: flex;
                align-items: flex-start;
                justify-content: center;
                flex-direction: column;
                z-index: 99;
                [dir="ltr"] & {
                    left: 0;
                    right: auto;
                } /* ltr */
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
                font-size: 17px;
                font-weight: normal;
                line-height: 22px;
                height: 60px;
                display: flex;
                align-items: center;
                justify-content: center;
                flex-direction: column;
                border-right: 0;
                [dir="ltr"] & {
                    border-left: 0;
                    border-right: 1px solid #707070;
                } /* ltr */
                &.time_today {
                    background: #FCF8E3;
                } /* time_today */
            } /* day */
        } /* dayes_head */
        .unit_item {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            flex-wrap: nowrap;
            position: relative;
            height: 60px;
            overflow: hidden;
            width: 1960px;
            .unit_price {
                background: #ffffff;
                white-space: nowrap;
                border: 1px solid #707070;
                padding: 0 5px;
                font-size: 16px;
                height: 60px;
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
                    font-size: 16px;
                    text-align: center;
                    border-radius: 100px;
                    height: 40px;
                    line-height: 40px;
                    z-index: 9;
                    position: absolute;
                    right: 35px;
                    top: 10px;
                    width: 135px;
                    overflow: hidden;
                    [dir="ltr"] & {
                        left: 35px;
                        right: auto;
                    } /* ltr */
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
</style>
