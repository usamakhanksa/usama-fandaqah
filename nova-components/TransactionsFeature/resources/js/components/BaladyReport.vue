<template>
    <div>
        <div class="flex w-full mb-4">
            <nav v-if="crumbs.length">
                <ul class="breadcrumbs">
                    <li class="breadcrumbs__item" v-for="(crumb,i) in crumbs" :key="i">
                        <router-link :to="crumb.to">{{ __(crumb.text) }}</router-link>
                    </li>
                </ul>
            </nav>
        </div>

        <div id="monthly_total_report_page">
            <div class="title">{{__('Balady Report')}}</div>
            <div class="content_page">
                <div class="belady_alert">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512.001 512.001" width="512" height="512" fill="#3362cc"><path d="M503.84 395.38L308.14 56.417C297.257 37.57 277.766 26.315 256 26.315s-41.257 11.254-52.14 30.102L8.16 395.377c-10.883 18.85-10.883 41.356 0 60.205s30.373 30.102 52.14 30.102h391.4c21.765 0 41.256-11.254 52.14-30.1s10.883-41.356 0-60.205zm-25.978 45.207c-5.46 9.458-15.24 15.104-26.162 15.104H60.3c-10.922 0-20.702-5.646-26.162-15.104s-5.46-20.75 0-30.208l195.7-338.962c5.46-9.458 15.24-15.104 26.16-15.104s20.7 5.646 26.16 15.104l195.7 338.962c5.46 9.458 5.46 20.75-.001 30.208zM241 176h29.996v149.982H241zm15 180c-11.027 0-19.998 8.97-19.998 19.998s8.97 19.998 19.998 19.998 19.998-8.97 19.998-19.998S267.027 356 256 356z"/></svg>{{__('We made it easy for you to create my disclosure file, select the required month, download the excel file, and then upload it in my portal')}}
                </div><!-- belady_alert -->
                <div class="filter_area">
                    <div class="item">
                        <select @change="getYear($event)" v-model="year">
                            <option value="" :disabled="true">{{__('Year')}}</option>
                            <option :selected="y == currentYear" v-for="(y,index) in fetchYears" :value="y" :key="index" >{{y}}</option>
                        </select>
                    </div><!-- item -->

                    <div class="item">
                        <select @change="getMonth($event)" v-model="month">
                            <option value="" :disabled="true">{{__('Month')}}</option>
                            <option :selected="m == currentMonth" v-for="(m,index) in this.fetchMonths"  :value="m" :key="index">{{months[index]}}</option>
                        </select>
                    </div><!-- item -->
                    <!-- Reset Button -->
                    <div class="item_button reset">
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
                    </div>
                </div><!-- filter_area -->

                <hr>


                <div class="action_buttons" v-if="data.length">
                    <button type="button" class="excel_button" @click="excelExport()"></button>
                    <button type="button" class="print_button" @click="printReport"></button>
<!--                    <a onclick="$('#balady_form').submit();" target="_blank">-->
<!--                        <svg  class="cursor-pointer" xmlns="http://www.w3.org/2000/svg" width="19.339" height="19.339" viewBox="0 0 19.339 19.339"><path d="M17.471,17.471v1.934a1.934,1.934,0,0,1-1.934,1.934H7.8a1.934,1.934,0,0,1-1.934-1.934V17.471H3.934A1.934,1.934,0,0,1,2,15.537v-5.8A1.94,1.94,0,0,1,3.934,7.8H5.868V3.934A1.94,1.94,0,0,1,7.8,2h7.735a1.934,1.934,0,0,1,1.934,1.934V7.8H19.4a1.934,1.934,0,0,1,1.934,1.934v5.8A1.934,1.934,0,0,1,19.4,17.471Zm0-1.934H19.4v-5.8H3.934v5.8H5.868V13.6A1.94,1.94,0,0,1,7.8,11.669h7.735A1.934,1.934,0,0,1,17.471,13.6ZM15.537,7.8V3.934H7.8V7.8ZM7.8,13.6v5.8h7.735V13.6Z" transform="translate(-2 -2)" fill="#333b45"/></svg>-->
<!--                    </a>-->
                </div><!-- action_buttons -->

                <div class="table_area">
                    <div class="title" v-if="data.length">
                        <span>{{__('Reservations')}} <p>({{paginator.total}})</p></span>
                        <span>{{__('Total Amount')}} <p>({{total}} <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span>)</p></span>
                    </div><!-- title -->
                    <div class="table_responsive relative">
                        <loading
                            :active="loading"
                            :loader="'spinner'"
                            :color="'#7e7d7f'"
                            :opacity="0.7"
                            :is-full-page="false"
                        />
                        <table>
                            <thead>
                            <tr>
                                <th class="text-center">{{__('Unit number')}}</th>
                                <th class="text-center">{{__('Date In')}}</th>
                                <th class="text-center">{{__('Date Out')}}</th>
                                <th class="text-center">{{__('Amount') }}</th>
                                <th class="text-center">{{__('Customer Name') }}</th>
                                <th class="text-center">{{__('Reservation Number') }}</th>
                                <th class="text-center">{{__('Unit Type') }}</th>
                                <th class="text-center">{{__('Notes') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            <template v-if="data.length">
                                <tr v-for="(reservation,index) in data" :key="index">
                                    <td>{{ reservation.unumber ? reservation.unumber : '-' }}</td>
                                    <td>{{ reservation.rdi }}</td>
                                    <td>{{ reservation.rdo }}</td>
                                    <td>{{ parseFloat(reservation.amount).toFixed(2) }}</td>
                                    <td>{{ reservation.cname }}</td>
                                    <td>{{ reservation.rnumber }}</td>
                                    <td>{{ reservation.ucname ? reservation.ucname[locale] : '-' }}</td>
                                    <td>-</td>
                                </tr>
                            </template>
                            <template v-else>
                                <tr>
                                    <td colspan="8" class="text-center">{{__('No Reservations Found')}}</td>
                                </tr>
                            </template>
                            </tbody>
                        </table>
                        <div class="w-full flex flex-wrap mt-3 justify-center" v-if="data.length">
                            <pagination
                                v-if="paginator.total > per_page"
                                :page-count="paginator.lastPage"
                                :page-range="3"
                                :margin-pages="2"
                                :value="paginator.currentPage"
                                :prev-text="__('Previous')"
                                :next-text="__('Next')"
                                :container-class="'pagination  w-full flex justify-center'"
                                :page-class="'page-item'"
                                :page-link-class="'page-link'"
                                :prev-link-class="'page-link'"
                                :next-link-class="'page-link'"
                                :prev-class="'page-item'"
                                :next-class="'page-item'"
                                :first-last-button="true"
                                :first-button-text="__('First')"
                                :last-button-text="__('Last')"
                                @input="getCurrentPage($event)"
                            />
                        </div><!-- Pagination -->
                        <div class="Results_area" v-if="data.length">
                            <p>{{__('Results')}}  : {{__('From')}} ( {{paginator.from}} ) - {{__('To')}}  ( {{paginator.to}} )</p>
                        </div><!-- Results_area -->
                    </div><!-- table_responsive -->
                </div><!-- table_area -->
            </div><!-- content_page -->
        </div><!-- monthly_total_report_page -->

        <form  id="balady_form" target="_blank" method="post"  style="display: none" action="/home/print/baladyReport">
            <input type="hidden" :value="JSON.stringify(all_data)" name="all_data">
            <input type="hidden" :value="JSON.stringify(total)" name="total">
        </form>
    </div>

</template>

<script>
    import Pagination from './Pagination';
    import moment from 'moment';
    import XLSX from 'xlsx'
    import axios from 'axios'
    import Loading from 'vue-loading-overlay';
    import 'vue-loading-overlay/dist/vue-loading.css';

    export default {
        name : 'balady-report',
        components : {
            Pagination,
            Loading,
        },
        data(){
            return {

                initialLoading : true,
                loading: true,
                data : [] ,
                days : {},
                months : [
                    this.__('January'),
                    this.__('February'),
                    this.__('March'),
                    this.__('April'),
                    this.__('May'),
                    this.__('June'),
                    this.__('July'),
                    this.__('August'),
                    this.__('September'),
                    this.__('October'),
                    this.__('November'),
                    this.__('December')
                ],
                month: moment(new Date()).format('MM') ,
                year : new Date().getFullYear(),
               locale : Nova.config.local,
                paginator : {},
                all_data : [],
                total_amount : '',
                per_page : 20,
                crumbs : [],
                query : {},
                selectedPage : 1,
                legacyQuery : {},
                team_id : Nova.config.user.current_team_id,
                total : 0,
                currency :Nova.app.currentTeam.currency,


            }
        },
        methods:{
            getCurrentPage(page){
                this.selectedPage = page;
            },

            resetFilters(){
                this.year =  new Date().getFullYear();
                this.month = moment(new Date()).format('MM');
                this.getResources(1);
            },
            getData() {
                this.loading = true ;
                let config = {
                headers : {
                    'x-team' : this.team_id,
                    'x-localization' : this.locale
                },
                params : this.$route.query
            }

                axios.get(window.FANDAQAH_API_URL + `/reservations/balady-report`, config)
                .then((response) => {
                    if(response.data.status == 'success'){
                        this.data = response.data.data.data;
                        this.paginator = {
                            currentPage : response.data.data.current_page || null ,
                            lastPage : response.data.data.last_page || null ,
                            from : response.data.data.from || null,
                            to : response.data.data.to || null,
                            total : response.data.data.total || null,
                            pathPage : response.data.data.path + '?page=' || null,
                            firstPageUrl : response.data.data.links.first || null ,
                            lastPageUrl : response.data.data.links.last || null ,
                            nextPageUrl : response.data.data.links.next || null ,
                            prevPageUrl : response.data.data.links.prev || null ,
                        }

                        this.all_data = response.data.all_data;
                        this.query = this.$route.query;
                        this.total = response.data.total;
                    }else{
                        this.data = [];
                        this.paginator = {};
                        this.ids = [];
                        this.total = 0;
                    }

                    this.loading = false;
                })

            },
            printReport(){
                $('#balady_form').submit();
            },
            excelExport () {
                axios.post(window.FANDAQAH_API_URL + '/reservations/balady-report-excel' , {
                    'all_data' : this.all_data ,
                    'lang' : Nova.config.local
                }).then((response) => {
                    this.isLoading = false;
                    let Ws = XLSX.utils.json_to_sheet(response.data.data)
                    let wb = XLSX.utils.book_new();
                    XLSX.utils.book_append_sheet(wb, Ws, response.data.file_name );
                    XLSX.writeFile(wb, response.data.file_name + '.xlsx');
                    this.$toasted.show(response.data.msg, {type: 'success'});
                })
            },
            getMonth(event){
                let month  = event.target.value ;
                this.month = month ;
            },
            getYear(event){
                let year = event.target.value ;
                this.year = year ;
            },

        },
        watch:{
            month: function (val) {
                if(val){
                    let opt = {}
                    opt["month"] = val;
                    this.$router.push({
                        name : 'balady-report',
                        query: Object.assign({}, this.$route.query, opt)
                    } , () => {
                        this.getData();
                    })
                }
            },
            year: function (val) {
                if(val){
                    let opt = {}
                    opt["year"] = val;
                    this.$router.push({
                        name : 'balady-report',
                        query: Object.assign({}, this.$route.query, opt)
                    } , () => {
                        this.getData();
                    })
                }
            },
            selectedPage: function (val) {
                if(val){
                    let opt = {}
                    opt["page"] = val;
                    this.$router.push({
                        name : 'balady-report',
                        query: Object.assign({}, this.$route.query, opt)
                    } , () => {
                        this.getData();
                    })
                }
            }

        },
        computed:{
            fetchMonths(){
                let months = ['01','02','03','04','05','06','07','08','09','10','11','12'];

                for (let i = 0; i < months.length; i++) {
                    if( (moment(new Date()).format('MM') < months[i])  && (this.year >=new Date().getFullYear())){
                        months.splice(i,months.length - i);
                    }
                }
                return months;
                return ['01','02','03','04','05','06','07','08','09','10','11','12'];
            },
            fetchYears(){
                // return ['2019' , '2020'];
                const year = new Date().getFullYear()
                return Array.from({length: year - 2019}, (value, index) => 2020 + index)
            },
            currentYear(){
                return new Date().getFullYear();
            },
            currentMonth(){
                return this.fetchMonths[new Date().getMonth()];
            }
        },
        mounted() {

            this.per_page = 5;
            this.query = this.$route.query;
            this.legacyQuery = this.$route.query;

            this.crumbs = [
                {
                    text : 'Home',
                    to : '/dashboards/main'
                },

                {
                    text : 'Reports',
                    to : '/reports'
                },

                {
                    text : 'Balady Report',
                    to : '#'
                }
            ];

            this.getData();
        }

    }


</script>

<style lang="scss">
    .print_button {
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='19.339' height='19.339' viewBox='0 0 19.339 19.339'%3E%3Cpath d='M17.471,17.471v1.934a1.934,1.934,0,0,1-1.934,1.934H7.8a1.934,1.934,0,0,1-1.934-1.934V17.471H3.934A1.934,1.934,0,0,1,2,15.537v-5.8A1.94,1.94,0,0,1,3.934,7.8H5.868V3.934A1.94,1.94,0,0,1,7.8,2h7.735a1.934,1.934,0,0,1,1.934,1.934V7.8H19.4a1.934,1.934,0,0,1,1.934,1.934v5.8A1.934,1.934,0,0,1,19.4,17.471Zm0-1.934H19.4v-5.8H3.934v5.8H5.868V13.6A1.94,1.94,0,0,1,7.8,11.669h7.735A1.934,1.934,0,0,1,17.471,13.6ZM15.537,7.8V3.934H7.8V7.8ZM7.8,13.6v5.8h7.735V13.6Z' transform='translate(-2 -2)' fill='%23333b45'/%3E%3C/svg%3E");
    }
    #monthly_total_report_page {
        margin: 0 auto;
        border: 1px solid #ddd;
        border-radius: .5rem;
        box-shadow: 0 2px 4px 0 rgba(0, 0, 0, 0.05);
        overflow: hidden;
        .title {
            background: #f7fafc;
            border-bottom: 1px solid #ddd;
            padding: .75rem;
            color: #000;
            font-size: 1.125rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            p {
                display: inline-block;
                font-size: 14px;
                margin: 0 5px 0 0;
            } /* p */
            button {
                display: block;
                background-color: #4099de;
                font-size: 15px;
                padding: 0 20px;
                height: 35px;
                border-radius: 4px;
                line-height: 35px;
                color: #fff;
                cursor: pointer;
                outline: none;
                &:hover {
                    background-color: #0071C9;
                } /* hover */
            } /* button */
        } /* title */
        .content_page {
            background: #fff;
            padding: 10px;
            .belady_alert {
              background: #F2F6FF;
              border-radius: 4px;
              padding: 10px;
              display: flex;
              margin: 0 auto 15px;
              border: 1px solid #D6E2FD;
              color: #3362CC;
              font-size: 15px;
              align-items: center;
              justify-content: flex-start;
              svg {
                margin: 0 0 0 15px;
                width: 25px;
                height: auto;
                [dir="ltr"] & {
                  margin: 0 15px 0 0;
                } /* ltr */
                @media (min-width: 320px) and (max-width: 480px) {
                  width: 25px;
                } /* Mobile */
              } /* svg */
              @media (min-width: 320px) and (max-width: 480px) {
                padding: 10px;
                font-size: 15px;
              } /* Mobile */
            } /* belady_alert */
            .filter_area {
                display: flex;
                align-items: center;
                flex-wrap: wrap;
                justify-content: flex-start;
                margin: 0 -10px;
                .item {
                    width: 25%;
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
                        cursor: pointer;
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
                .item_button {
                    width: auto;
                    padding: 0 10px;
                    margin: 0 0 10px;
                    display: flex;
                    align-items: center;
                    &.search {
                        button {
                            background-image: url("data:image/svg+xml,%3C%3Fxml version='1.0'%3F%3E%3Csvg xmlns='http://www.w3.org/2000/svg' height='512px' version='1.1' viewBox='-1 0 136 136.21852' width='512px' class=''%3E%3Cg%3E%3Cg id='surface1'%3E%3Cpath d='M 93.148438 80.832031 C 109.5 57.742188 104.03125 25.769531 80.941406 9.421875 C 57.851562 -6.925781 25.878906 -1.460938 9.53125 21.632812 C -6.816406 44.722656 -1.351562 76.691406 21.742188 93.039062 C 38.222656 104.707031 60.011719 105.605469 77.394531 95.339844 L 115.164062 132.882812 C 119.242188 137.175781 126.027344 137.347656 130.320312 133.269531 C 134.613281 129.195312 134.785156 122.410156 130.710938 118.117188 C 130.582031 117.980469 130.457031 117.855469 130.320312 117.726562 Z M 51.308594 84.332031 C 33.0625 84.335938 18.269531 69.554688 18.257812 51.308594 C 18.253906 33.0625 33.035156 18.269531 51.285156 18.261719 C 69.507812 18.253906 84.292969 33.011719 84.328125 51.234375 C 84.359375 69.484375 69.585938 84.300781 51.332031 84.332031 C 51.324219 84.332031 51.320312 84.332031 51.308594 84.332031 Z M 51.308594 84.332031 ' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%23FFFFFF'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E%0A");
                            height: 40px;
                            background-color: #0A80D8;
                            padding: 0;
                            border-radius: 4px;
                            font-size: 0px;
                            color: #fff;
                            width: 40px;
                            outline: none;
                            background-position: center center;
                            background-repeat: no-repeat;
                            background-size: 20px;
                            -webkit-transition: all 0.2s ease-in-out;
                            -moz-transition: all 0.2s ease-in-out;
                            -o-transition: all 0.2s ease-in-out;
                            transition: all 0.2s ease-in-out;
                            &[disabled="disabled"] {
                                opacity: 0.6;
                                cursor: not-allowed;
                            } /* disabled */
                            &:hover {
                                background-color: #0071C9;
                            } /* hover */
                        } /* button */
                    } /* search */
                    &.reset {
                        button {
                            height: 40px;
                            width: 40px;
                            background-color: #718096;
                            border-radius: 4px;
                            outline: none;
                            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16.866' height='18.447' viewBox='0 0 16.866 18.447'%3E%3Cg transform='translate(0 0)'%3E%3Cpath d='M24.417,3.658a7.354,7.354,0,0,1,9.56-.252l-2.189.083a.509.509,0,0,0,.019,1.017h.019l3.36-.124a.508.508,0,0,0,.49-.509v-.06h0L35.552.49a.509.509,0,1,0-1.017.038l.079,2.083A8.364,8.364,0,0,0,23.735,2.9a8.367,8.367,0,0,0-2.516,8.178.506.506,0,0,0,.493.388.441.441,0,0,0,.121-.015.509.509,0,0,0,.373-.614A7.349,7.349,0,0,1,24.417,3.658Z' transform='translate(-20.982 0)' fill='%23ffffff'/%3E%3Cpath d='M91.8,185.6a.508.508,0,1,0-.987.241,7.348,7.348,0,0,1-11.832,7.387l2.215-.2a.509.509,0,1,0-.094-1.013l-3.349.3a.508.508,0,0,0-.46.554l.3,3.349a.508.508,0,0,0,.5.463.183.183,0,0,0,.045,0,.508.508,0,0,0,.46-.554l-.181-2.038a8.308,8.308,0,0,0,4.833,1.842c.143.008.286.011.426.011A8.365,8.365,0,0,0,91.8,185.6Z' transform='translate(-75.175 -178.237)' fill='%23ffffff'/%3E%3C/g%3E%3C/svg%3E");
                            background-repeat: no-repeat;
                            background-position: center center;
                            background-size: 20px;
                            -webkit-transition: all 0.2s ease-in-out;
                            -moz-transition: all 0.2s ease-in-out;
                            -o-transition: all 0.2s ease-in-out;
                            transition: all 0.2s ease-in-out;
                            &[disabled="disabled"] {
                                opacity: 0.6;
                                cursor: not-allowed;
                            } /* disabled */
                            &:hover {
                                background-color: #5E6D83;
                            } /* hover */
                        } /* button */
                    } /* reset */
                } /* item_button */
            } /* filter_area */
            hr {
                margin: 10px auto 20px;
                border-color: #ddd;
                &:last-of-type {
                    margin: 0 0 20px;
                } /* last-of-type */
            } /* hr */
            .statistics_area {
                ul {
                    display: flex;
                    align-items: flex-start;
                    justify-content: flex-start;
                    flex-wrap: wrap;
                    margin: 0 -10px;
                    li {
                        width: 20%;
                        padding: 0 10px;
                        margin: 0 0 20px;
                        @media (min-width: 320px) and (max-width: 480px) {
                            width: 50%;
                        } /* media */
                        @media (min-width: 481px) and (max-width: 767px) {
                            width: 33.33333%;
                        } /* media */
                        @media (min-width: 768px) and (max-width: 991px) {
                            width: 25%;
                        } /* media */
                        span {
                            display: block;
                            font-size: 15px;
                            color: #000;
                            margin: 0 0 5px;
                        } /* span */
                        p {
                            display: block;
                            font-size: 16px;
                            font-weight: bold;
                            &.totalDebtor {
                                color: #f56565;
                            } /* totalDebtor */
                            &.totalCreditor {
                                color: #48bb78;
                            } /* totalCreditor */
                        } /* p */
                    } /* li */
                } /* ul */
            } /* statistics_area */
            .action_buttons {
                display: flex;
                align-items: center;
                justify-content: flex-end;
                margin: 0 auto 10px;
                button {
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
                    } /* excel_button */
                } /* button */
            } /* action_buttons */

            .table_area {
                .no_data_show {
                    text-align: center;
                    padding: 50px 15px 40px;
                    svg {
                        display: block;
                        margin: 0 auto 15px;
                    } /* svg */
                    span {
                        display: block;
                        font-size: 15px;
                        text-align: center;
                        color: #000;
                    } /* span */
                } /* no_data_show */
                .table_responsive {
                    @media (min-width: 320px) and (max-width: 767px) {
                        overflow: auto;
                    } /* media */
                    table {
                        width: 100%;
                        @media (min-width: 320px) and (max-width: 767px) {
                            margin: 0 auto 15px;
                        } /* media */
                        thead {
                            th {
                                background: #4a5568;
                                border: 1px solid #5E697C;
                                font-size: 15px;
                                padding: 10px;
                                vertical-align: middle;
                                color: #fff;
                                font-weight: normal;
                                text-align: center !important;
                            } /* th */
                        } /* thead */
                        tbody {
                            td {
                                background: #fafafa;
                                border: 1px solid #d3d3d3;
                                color: #000;
                                vertical-align: middle;
                                padding: 10px;
                                font-size: 15px;
                                line-height: 20px;
                                text-align: center !important;
                                height: auto;
                                .text-left {
                                    text-align: inherit !important;
                                } /* text-left */
                                label#customer-label {
                                    display: inline-block;
                                    font-size: 14px;
                                    border-radius: 4px;
                                    padding: 3px 10px;
                                    min-width: 60px;
                                } /* customer-label */
                                a {
                                    color: #4099de;
                                    font-weight: bold;
                                    cursor: pointer;
                                    &:hover {
                                        color: #0071C9;
                                    } /* hover */
                                } /* a */
                                &.td-fit {
                                    display: flex;
                                    align-items: center;
                                    justify-content: center;
                                    flex-wrap: wrap;
                                    button {
                                        margin: 5px !important;
                                        color: #b3b9bf;
                                        svg {
                                            display: block;
                                            path {
                                                fill: #b3b9bf;
                                                &:hover {
                                                    fill: #4099de;
                                                } /* hover */
                                            } /*  path*/
                                        } /* svg */
                                        &:hover {
                                            color: #4099de;
                                        } /* hover */
                                    } /* button */
                                } /* action */
                            } /* td */
                        } /* tbody */
                    } /* table */
                } /* table_responsive */
            } /* table_area */
        } /* content_page */
    } /* monthly_total_report_page */
</style>
