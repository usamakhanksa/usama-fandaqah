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

        <div id="monthly_total_report_page" class="relative">
            <loading :active="isLoading" :loader="'spinner'" :color="'#7e7d7f'" :opacity="0.7"  :is-full-page="false"></loading>
            <div class="title">{{__('Monthly Report')}}</div>
            <div class="content_page">
                <div class="filter_area">
                    <div class="item">
                        <select @change="setMonth($event)">
                            <option value="" :selected="!month">{{__('Filter By Month')}}</option>
                            <option v-for="(month,index) in this.fetchMonths"  :value="month" :key="index" >{{month}}</option>
                        </select>
                    </div><!-- item -->
                    <div class="item">
                        <select @change="setYear($event)">
                            <option value="" :selected="!year">{{__('Filter By Year')}}</option>
                            <option v-for="(year,index) in this.fetchYears" :value="year" :key="index" >{{year}}</option>
                        </select>
                    </div><!-- item -->
                    <div class="item" v-show="employees.length">
                        <select @change="setEmployee($event)">
                            <option value="" :selected="!employee_id">{{__('Filter By Employee')}}</option>
                            <option v-for="(employee,index) in this.employees" :value="employee.id" :key="index" >{{employee.name}}</option>
                        </select>
                    </div><!-- item -->
                    <div class="item_button reset">
                        <button
                            @click="resetFilters()"
                            :disabled="isDisabledSearchBtn"
                            v-tooltip="{
                            targetClasses: ['it-has-a-tooltip'],
                            placement: 'top',
                            content: __('Reset Filters'),
                            classes: ['tooltip_reset']
                          }"
                        >
                        </button>
                    </div><!-- item_button -->
                </div><!-- filter_area -->

                <hr>

                <div class="statistics_area" v-if="resources.length">

                    <ul>
                        <li>
                            <span>{{__('Total Deposit') }}</span>
                            <p class="d-flex">{{ Math.abs(Number(total_deposit_widget)).toFixed(2)  }} <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></p>
                        </li>
                        <li>
                            <span>{{__('Total Withdraw') }}</span>
                            <p class="d-flex">{{ Math.abs(Number(total_withdraw_widget)).toFixed(2)  }} <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></p>
                        </li>
                        <li>
                            <span>{{__('Total Credit') }}</span>
                            <p class="d-flex">{{ (credit).toFixed(2) }} <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></p>
                        </li>
                    </ul>
                </div>

                <hr>

                <div class="action_buttons" v-if="days.length">
                    <button type="button" class="excel_button" @click="excelExport()"></button>
                </div><!-- action_buttons -->

                <div class="table_area">
                    <div v-if="!resources.length" class="no_data_show">
                        <svg xmlns="http://www.w3.org/2000/svg" width="65" height="51"><path d="M56 40h2a1 1 0 1 1 0 2h-2v2a1 1 0 1 1-2 0v-2h-2a1 1 0 1 1 0-2h2v-2a1 1 0 1 1 2 0v2zm-5.364-8H38v8h7.05c.35-3.53 2.535-6.517 5.587-8zM45.05 42H6a6 6 0 0 1-6-6V6a6 6 0 0 1 6-6h44a6 6 0 0 1 6 6v25.05c5.053.502 9 4.765 9 9.95 0 5.523-4.477 10-10 10-5.185 0-9.45-3.947-9.95-9zM20 30h16v-8H20v8zm0 2v8h16v-8H20zm34-2v-8H38v8h16zM2 30h16v-8H2v8zm0 2v4a4 4 0 0 0 4 4h12v-8H2zm18-12h16v-8H20v8zm34 0v-8H38v8h16zM2 20h16v-8H2v8zm52-10V6a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v4h52zm1 39a8 8 0 1 0 0-16 8 8 0 1 0 0 16z" fill="#a8b9c5" fill-rule="nonzero"/></svg>
                        <span>{{__('Please start to select month and year to initiate monthly report')}}</span>
                    </div><!-- no_data_show -->
                    <div v-if="resources.length" class="table_responsive">
                        <table>
                            <thead>
                            <tr>
                                <th class="text-center">{{__('Day')}}</th>
                                <th class="text-center">{{__('Total Deposit Transactions')}}</th>
                                <th class="text-center">{{__('Total Withdraw Transactions')}}</th>
                                <th class="text-center">{{__('Total Credit') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="(day,index) in this.days" :key="index">
                                <td>{{ day }}</td>
                                <td>{{ Math.abs(Number(deposit_dt[index])).toFixed(2) }}  </td>
                                <td>{{ Math.abs(Number(withdraw_dt[index])).toFixed(2)  }} </td>
                                <td>{{ (Number(deposit_dt[index]) +  (Number(withdraw_dt[index]))).toFixed(2) }} </td>
                            </tr>
                            </tbody>
                        </table>
                    </div><!-- table_responsive -->

                </div><!-- table_area -->
            </div><!-- content_page -->
        </div><!-- monthly_total_report_page -->
    </div>

</template>

<script>
    import Loading from 'vue-loading-overlay';
    import 'vue-loading-overlay/dist/vue-loading.css';
    import XLSX from 'xlsx'
    import axios from 'axios'

    export default {
        name : 'monthly-report',
        components : {
            Loading
        },
        data(){
            return {
                isLoading: false,
                employees : [],
                currency :Nova.app.currentTeam.currency,

                employee_id : null ,
                days : [],
                resources : [],
                month: moment(new Date()).format('MM') ,
                year : new Date().getFullYear(),
                withdraw_dt : {},
                deposit_dt : {},
                total_withdraw_widget : 0 ,
                total_deposit_widget : 0 ,
                withdraw_has_values : false ,
                deposit_has_values : false ,
                credit : 0 ,
                isNegative :  false,
                crumbs : [],
                team_id : Nova.config.user.current_team_id,
                locale : Nova.config.local,
                legacyQuery : null
            }
        },
        methods:{
            setMonth(event){
                let month  = event.target.value ;
                this.month = month ;

                if(this.month != null){
                    let opt = {}
                    opt["month"] = this.month;
                    this.$router.push({
                        name : 'monthly-report',
                        query: Object.assign({}, this.$route.query, opt)
                    } , () => {
                        this.getData();
                    })
                }

                if(this.month == null){
                    let opt = {}
                    opt["month"] = this.month;
                    this.$router.push({
                        name : 'monthly-report',
                        query: Object.assign({}, this.$route.query, opt)
                    } , () => {
                        this.getData();
                    })
                }
            },
            setYear(event){
                let year = event.target.value ;
                this.year = year ;
            },
            setEmployee(event){
                this.employee_id = event.target.value
            },
            resetFilters(){

                let opt = {}
                this.$router.push({
                    name : 'monthly-report',
                    query: Object.assign({}, this.legacyQuery, opt)
                } , () => {
                    this.employee_id = null ;
                    this.year = 0 ;
                    this.month = 0 ;
                    this.getData();
                })
            },
            getData(){
                const self = this;
                this.isLoading = true;
                let config = {
                    headers : {
                        'x-team' : this.team_id,
                        'x-localization' : this.locale
                    },
                    params : this.$route.query
                }
                axios.post(`/nova-vendor/transactions-feature/monthlyReport`, {
                  'team_id' : this.team_id,
                  'month' : this.$route.query.month,
                  'year' : this.$route.query.year,
                  'employee_id' : this.$route.query.employee_id
                })
                // axios.post(window.FANDAQAH_API_URL + `/reports/monthly` ,  {
                //   'team_id' : this.team_id,
                //   'lang' : this.locale,
                //   'month' : this.$route.query.month,
                //   'year' : this.$route.query.year,
                //   'employee_id' : this.$route.query.employee_id
                // })
                    .then(function(response){
                        if(response.data.status == 'invalid_month_or_year'){
                            self.days = [];
                            self.resources = [];
                            self.withdraw_dt = {};
                            self.deposit_dt = {};
                            self.credit = 0 ;
                            self.isLoading = false;
                            return ;
                        }

                        self.days = response.data.days;
                        self.resources = response.data.result;
                        self.withdraw_dt = response.data.result[0]
                        self.deposit_dt = response.data.result[1]
                        self.total_withdraw_widget = self.handleNegativeNumber(response.data.total_withdraw_widget) ;
                        self.total_deposit_widget = self.handleNegativeNumber(response.data.total_deposit_widget);

                        if(self.total_withdraw_widget > self.total_deposit_widget){
                            self.isNegative = true ;
                        }else{
                            self.isNegative = false ;
                        }

                        self.credit = response.data.credit ;
                        self.query = self.$route.query;
                        self.isLoading = false;
                    })

            },
            excelExport () {
                let self = this ;
                let params = {
                    'resources' : this.resources ,
                    'days' : this.days,
                    'lang' : this.locale
                };

                axios.post(window.FANDAQAH_API_URL + `/reports/monthly-excel-report` , params)
                    .then(response => {
                        let data = response.data.data
                        let footer = response.data.footer
                        let monthlyData = XLSX.utils.json_to_sheet(data)
                        let monthlyFooter = XLSX.utils.json_to_sheet(footer)
                        let wb = XLSX.utils.book_new()
                        XLSX.utils.book_append_sheet(wb, monthlyData, response.data.monthly_details)
                        XLSX.utils.book_append_sheet(wb, monthlyFooter, response.data.monthly_statistics)
                        XLSX.writeFile(wb, response.data.file_name + '.xlsx')
                        this.$toasted.show(response.data.msg , {type: 'success'});
                })
            },
            handleNegativeNumber(number){
                let checkNumber =  Math.sign(number) ; // return 1 , 0 , -1 it's Ecma Script Way
                return Math.abs(number) ;
            },
            getEmployees(){
                axios.get(window.FANDAQAH_API_URL + `/users/dropDown?team_id=${this.team_id}`)
                    .then((response) => {
                        this.employees = response.data.data;
                    })
            },
        },
        watch:{
            month: function (val) {

            },
            year: function (val) {
                if(val != null){
                    let opt = {}
                    opt["year"] = val;
                    this.$router.push({
                        name : 'monthly-report',
                        query: Object.assign({}, this.$route.query, opt)
                    } , () => {
                        this.getData();
                    })
                }

                if(val == null){
                    let opt = {}
                    opt["year"] = val;
                    this.$router.push({
                        name : 'monthly-report',
                        query: Object.assign({}, this.$route.query, opt)
                    } , () => {
                        this.getData();
                    })
                }
            },
            employee_id: function (val) {
                if(val != null){
                    let opt = {}
                    opt["employee_id"] = val;
                    this.$router.push({
                        name : 'monthly-report',
                        query: Object.assign({}, this.$route.query, opt)
                    } , () => {
                        this.getData();
                    })
                }

                if(val == null){
                    let opt = {}
                    opt["page"] = 1;
                    this.$router.push({
                        name : 'monthly-report',
                        query: Object.assign({}, this.$route.query, opt)
                    } , () => {
                        this.getData();
                    })
                }
            },
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

            },
            fetchYears(){

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
            this.days = [];
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
                    text : 'Monthly Report',
                    to : '#'
                }
            ];

            this.getEmployees();
            this.getData();
        }

    }


</script>

<style lang="scss">
  #monthly_total_report_page {
    margin: 0 auto;
    border: 1px solid #ddd;
    border-radius: .5rem;
    box-shadow: 0 2px 4px 0 rgba(0, 0, 0, 0.05);
    overflow: hidden;
    .title{
      background: #f7fafc;
      border-bottom: 1px solid #ddd;
      padding: .75rem;
      color: #000;
      font-size: 1.125rem;
      display: block;
    } /* title */
    .content_page {
      background: #fff;
      padding: 10px;
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
                padding: 15px 5px;
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
  .d-flex{
      display: flex !important;
  }
</style>
