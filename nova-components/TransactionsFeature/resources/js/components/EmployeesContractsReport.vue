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

        <div id="deposit_management_page">
            <div class="title">{{ __('Employee Contracts') }}</div>
            <div class="content_page">

                <!-- Filter Area -->
                <div class="filter_area">


                    <div class="item" v-show="units.length">
                        <select v-model="unitNumber">
                            <option value="" selected>{{__('The Unit')}}</option>
                            <option v-for="(unit,i) in units" :value="unit.number" :key="i">{{ unit.number + ' - ' + unit.name[locale] }}</option>
                        </select>
                    </div>

                    <div class="item" v-show="employees.length">
                        <select v-model="employeeId">
                            <option value="" selected>{{__('Employee')}}</option>
                            <option v-for="(employee,i) in employees" :value="employee.id" :key="i">{{ employee.name }}</option>
                        </select>
                    </div>

                    <div class="item">
                        <input
                            readonly
                            v-model="createdFrom"
                            ref="datePickerFrom"
                            type="text"
                            :placeholder="__('Date From')"
                        >
                    </div>

                    <div class="item">
                        <input
                            readonly
                            v-model="createdTo"
                            ref="datePickerTo"
                            type="text"
                            :placeholder="__('Date To')"
                        >
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

                <hr>

                <div class="statistics_area relative">
                <loading :active="totalsLoading" :loader="'spinner'" :color="'#7e7d7f'" :opacity="0.7" :height="20" :width="20" :is-full-page="false"></loading>

                    <ul>
                        <li>
                            <span>{{__('Total Reservations')}}</span>
                            <p>{{totals.total_count}}</p>
                        </li>
                        <li>
                            <span>{{__('Total Reservations Amount')}}</span>
                            <p class="d-flex">{{totals.total_cost}} <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></p>
                        </li>
                    </ul>
                </div><!-- statistics_area -->

                <hr>

                <!-- Action Buttons -->
                <div class="action_buttons">
                    <div class="buttons_area" v-if="data.length">
                        <button type="button" class="excel_button" @click="excelExport"></button>
                        <button type="button" class="print_button" @click="printReport"></button>
                    </div><!-- buttons_area -->
                </div><!-- action_buttons -->

                <!-- Table Listing Area -->
                <div class="table_area">
                    <div class="table-responsive relative">
                        <loading :active="isLoading" :loader="'spinner'" :color="'#7e7d7f'" :opacity="0.7" :height="20" :width="20" :is-full-page="false"></loading>
                        <table class="table w-full"
                               cellpadding="0"
                               cellspacing="0"
                        >
                            <thead>
                            <tr>
                                <th>{{__('Reservation Number')}}</th>
                                <th>{{__('Customer')}}</th>
                                <th>{{__('The Unit')}}</th>
                                <th>{{__('Employee Name')}}</th>
                                <th>{{__('Creation Date')}}</th>
                                <th>{{__('Total Price')}}</th>
                                <th>{{__('Balance')}}</th>
                            </tr>
                            </thead>
                            <tbody>
                            <template v-if="data.length">
                                <tr v-for="(obj,index) in data" :key="index">
                                    <td><router-link :to="{name : obj.customer ? 'reservation' : 'reservation-noc' , params : {id : obj.id }}" class="text-info-dark">{{obj.number}}</router-link></td>
                                    <td v-if="obj.reservation_type == 'single'"><router-link :to="{path : `/resources/customers/${obj.customer.id}`}" class="text-info-dark">{{obj.customer.name}}</router-link></td>
                                    <td v-else><router-link :to="{path : `/companies/${obj.company.id}/profile`}" class="text-info-dark">{{obj.company.name}}</router-link></td>

                                    <td v-if="obj.unit"><router-link :to="{path : `/resources/units/${obj.unit.id}`}" class="text-info-dark">{{obj.unit.number + ' - ' + obj.unit.name}}</router-link></td>
                                    <td v-else>-</td>
                                    <td v-if="obj.employee_id"><router-link :to="{path : `/resources/users/${obj.employee_id}`}" class="text-info-dark">{{obj.employee_name}}</router-link></td>
                                    <td v-else>-</td>

                                    <td>{{obj.created_at}}</td>
                                    <td>{{obj.total_with_services}} </td>
                                    <td>
                                        <div v-if="obj.reservation_type == 'single'">
                                            <p v-if="Math.sign(obj.balance) == -1 && obj.balance != 0" class="text-danger">{{parseFloat(Math.abs(obj.balance)).toFixed(2)}} {{__('Debtor')}}</p>
                                            <p v-else-if="Math.sign(obj.balance) == 1 && obj.balance != 0 " class="text-success">{{parseFloat(obj.balance).toFixed(2)}} {{__('Creditor')}}</p>
                                            <p v-else-if="obj.balance == 0">{{obj.balance}}</p>
                                        </div>
                                        <div v-else>
                                            <p v-if="obj.groupReservationBalanceMapper && Math.sign(obj.groupReservationBalanceMapper.balance) == -1 && obj.groupReservationBalanceMapper.balance != 0" class="text-danger">{{parseFloat(Math.abs(obj.groupReservationBalanceMapper.balance)).toFixed(2)}} {{__('Debtor')}}</p>
                                            <p v-else-if="obj.groupReservationBalanceMapper && Math.sign(obj.groupReservationBalanceMapper.balance) == 1 && obj.groupReservationBalanceMapper.balance != 0 " class="text-success">{{parseFloat(obj.groupReservationBalanceMapper.balance).toFixed(2)}} {{__('Creditor')}}</p>
                                            <p v-else-if="obj.groupReservationBalanceMapper && obj.groupReservationBalanceMapper.balance == 0">0.00</p>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                            <template v-else>
                                <tr>
                                    <td colspan="7" class="text-center">{{__('No Reservations Found')}}</td>
                                </tr>
                            </template>

                            </tbody>
                        </table>
                    </div>
                    <!-- Pagination -->
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
                        <p>{{__('Count')}}  : {{paginator.total}}</p>
                    </div><!-- Results_area -->
                </div>

            </div>
        </div>

        <!-- Single Print  -->
        <a :href="'/home/print/contractsReport' + current_print_url + `&total=${totals.total_cost}`" target="_blank" hidden ref="print_link"></a>
    </div>
</template>

<script>

import DeleteConfirm from './DeleteConfirm'
import flatpickr from "flatpickr";
import '../airbnb-modified.css'
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css';
import Pagination from './Pagination'
import XLSX from 'xlsx'

export default {
    name: "employees-contracts-report",
    components:{
        Loading,
        Pagination
    },
    data(){
        return {
            unitNumber : '',
            employeeId : '',
            createdFrom : null,
            createdTo : null,
            flatpickrFrom: null,
            flatpickrTo: null,
            locale : Nova.config.local,
            isLoading : false,
            data : [],
            ids : [],
            paginator : {},
            crumbs : [],
            query : {},
            selectedPage : 1,
            legacyQuery : {},
            team_id : Nova.config.user.current_team_id,
            units : [],
            employees : [],
            per_page : 20,
            totals : {
                total_cost : 0,
                total_count : 0
            },
            totalsLoading : false,
            current_print_url : null,
            currency :Nova.app.currentTeam.currency,

        }
    },
    computed:{
        dateFormat() {
            return  'Y-m-d'
        },
    },
    methods:{

        getCurrentPage(page){
            this.selectedPage = page;
        },
        capitalize(label){
            if (typeof label !== 'string') return ''
            return label.charAt(0).toUpperCase() + label.slice(1)
        },
        excelExport(){

             this.isLoading = true;
             let config = {
                headers : {
                    'x-team' : this.team_id,
                    'x-localization' : this.locale
                },
                params : this.$route.query
            }
            axios.get(`/nova-vendor/transactions-feature/employeesContractsExcel`, config)
            .then(response => {
                this.isLoading = false;
                let Ws = XLSX.utils.json_to_sheet(response.data.data)
                let wb = XLSX.utils.book_new();
                XLSX.utils.book_append_sheet(wb, Ws, response.data.file_name );
                XLSX.writeFile(wb, response.data.file_name + '.xlsx');
                this.$toasted.show(response.data.msg, {type: 'success'});
            });
        },
        printReport(){
            // $('#employees_contract_form').submit();
            this.current_print_url = window.location.search;
            setTimeout(() => {
                this.$refs.print_link.click();
            }, 50);
        },
        resetFilters(){

            let opt = {}
            opt["page"] = 1;
            this.$router.push({
                name : 'employees-contracts-report',
                query: Object.assign({}, this.legacyQuery, opt)
            } , () => {
                this.unitNumber = '',
                this.employeeId = '',
                this.createdFrom = null;
                this.createdTo = null;
                this.getData();
            })
        },
        getTotals() {
            this.totalsLoading = true;
            let config = {
                headers : {
                    'x-team' : this.team_id,
                    'x-localization' : this.locale
                },
                params : this.$route.query
            }

            axios.get(`/nova-vendor/transactions-feature/employeesContractsTotals`, config)
            .then((response) => {
                this.totals = response.data;
                this.totalsLoading = false;
            });

        },
        getData(){

            this.isLoading = true;
            let config = {
                headers : {
                    'x-team' : this.team_id,
                    'x-localization' : this.locale
                },
                params : this.$route.query
            }
            axios.get('/nova-vendor/transactions-feature/employeesContracts' , config)
                .then((response) => {

                    this.data = response.data.data;
                    this.paginator = {
                        currentPage : response.data.meta.current_page || null ,
                        lastPage : response.data.meta.last_page || null ,
                        from : response.data.meta.from || null,
                        to : response.data.meta.to || null,
                        total : response.data.meta.total || null,
                        pathPage : response.data.meta.path + '?page=' || null,
                        firstPageUrl : response.data.links.first || null ,
                        lastPageUrl : response.data.links.last || null ,
                        nextPageUrl : response.data.links.next || null ,
                        prevPageUrl : response.data.links.prev || null ,
                    }

                    this.query = this.$route.query;
                    this.isLoading = false;
                    this.getTotals();
                })
                .catch((err) => {
                    console.log(err);
                });

        },

        getUnits(){
            axios.get(window.FANDAQAH_API_URL + `/units/dropDown?team_id=${this.team_id}`)
                .then((response) => {
                    this.units = response.data.data;
                })
        },
        getEmployees(){
            axios.get(window.FANDAQAH_API_URL + `/users/dropDown?team_id=${this.team_id}`)
                .then((response) => {
                    this.employees = response.data.data;
                })
        }
    },
    watch:{
        unitNumber: function (val) {
            if(val != null){
                let opt = {}
                opt["filter[by_unit_number]"] = val;
                opt["page"] = 1;
                this.$router.push({
                    name : 'employees-contracts-report',
                    query: Object.assign({}, this.$route.query, opt)
                } , () => {
                    this.getData();
                })
            }

            if(val == ''){
                let opt = {}
                opt["page"] = 1;
                this.$router.push({
                    name : 'employees-contracts-report',
                    query: Object.assign({}, this.$route.query, opt)
                } , () => {
                    this.getData();
                })
            }
        },
        employeeId: function (val) {
            if(val != null){
                let opt = {}
                opt["filter[by_creator]"] = val;
                opt["page"] = 1;
                this.$router.push({
                    name : 'employees-contracts-report',
                    query: Object.assign({}, this.$route.query, opt)
                } , () => {
                    this.getData();
                })
            }

            if(val == ''){
                let opt = {}
                opt["page"] = 1;
                this.$router.push({
                    name : 'employees-contracts-report',
                    query: Object.assign({}, this.$route.query, opt)
                } , () => {
                    this.getData();
                })
            }
        },
        createdFrom: function (val) {
            if(val != null){
                let opt = {}
                opt["filter[by_created_from]"] = val;
                opt["page"] = 1;
                this.$router.push({
                    name : 'employees-contracts-report',
                    query: Object.assign({}, this.$route.query, opt)
                } , () => {
                    this.getData();
                })
            }

            if(val == null){
                let opt = {}
                opt["page"] = 1;
                this.$router.push({
                    name : 'employees-contracts-report',
                    query: Object.assign({}, this.$route.query, opt)
                } , () => {
                    this.getData();
                })
            }
        },
        createdTo: function (val) {
            if(val != null){
                let opt = {}
                opt["filter[by_created_to]"] = val;
                opt["page"] = 1;
                this.$router.push({
                    name : 'employees-contracts-report',
                    query: Object.assign({}, this.$route.query, opt)
                } , () => {
                    this.getData();
                })
            }

            if(val == null){
                let opt = {}
                opt["page"] = 1;
                this.$router.push({
                    name : 'employees-contracts-report',
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
                    name : 'employees-contracts-report',
                    query: Object.assign({}, this.$route.query, opt)
                } , () => {
                    this.getData();
                })
            }
        }
    },
    mounted() {


        const self = this
        this.$nextTick(() => {
            this.flatpickrFrom = flatpickr(this.$refs.datePickerFrom, {
                enableTime: false,
                enableSeconds: false,
                disableMobile: "true",
                dateFormat: this.dateFormat,
                allowInput: false,
                mode: 'single',
                time_24hr: false,
                onReady() {
                    self.$refs.datePickerFrom.parentNode.classList.add('date-filter')
                },
                onChange(){
                    self.createdFrom = self.$refs.datePickerFrom.value;
                },
                locale : self.locale
            })

            this.flatpickrTo = flatpickr(this.$refs.datePickerTo, {
                enableTime: false,
                enableSeconds: false,
                disableMobile: "true",
                dateFormat: this.dateFormat,
                allowInput: false,
                mode: 'single',
                time_24hr: false,
                onReady() {
                    self.$refs.datePickerTo.parentNode.classList.add('date-filter')
                },
                onChange(){
                    self.createdTo = self.$refs.datePickerTo.value;
                },
                locale : self.locale
            })

        })


        this.per_page = this.$route.query.per_page;
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
                text : 'Employee Contracts',
                to : '#'
            }
        ];
        this.query = this.$route.query;
        this.legacyQuery = this.$route.query;
        this.getUnits();
        this.getEmployees();
        this.getData();

    },

}
</script>

<style lang="scss">

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
.customer-label {
    display: inline-block;
    font-size: 14px;
    border-radius: 4px;
    padding: 3px 10px;
    min-width: 60px;
}
.Edit_Transaction_Modal {
    .sweet-content {
        overflow: auto;
        max-height: 500px;
        display: block;
        scrollbar-width: thin;
        scrollbar-color: #ccc #f5f5f5;
        &::-webkit-scrollbar {width: 6px;}
        &::-webkit-scrollbar-track {background: #f5f5f5;}
        &::-webkit-scrollbar-thumb {background: #ccc;}
        &::-webkit-scrollbar-thumb:window-inactive {background: #f5f5f5;}
    } /* sweet-content */
    .formgroup {
        display: block;
        margin: 0 auto 10px;
    } /* formgroup */
    .row_group {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        flex-wrap: wrap;
        margin: 0 -10px;
        @media (min-width: 320px) and (max-width: 767px) {
            margin: 0;
        } /* Mobile */
        .col {
            width: 50%;
            padding: 0 10px;
            margin: 0 0 10px;
            @media (min-width: 320px) and (max-width: 767px) {
                width: 100%;
                padding: 0;
            } /* Mobile */
        } /* col */
    } /* row_group */
    label {
        display: block;
        margin: 0 auto 5px;
        font-size: 15px;
        span {
            display: inline-block;
            margin: 0 5px 0 0;
            color: #f00;
            [dir="ltr"] & {
                margin: 0 0 0 5px;
            } /* ltr */
        } /* span */
    } /* label */
    input {
        height: 40px !important;
        padding: 0 10px !important;
        color: #000 !important;
        font-size: 15px !important;
        border: 1px solid #dddddd !important;
        background: #fafafa !important;
        width: 100% !important;
        &[readonly="readonly"] {
            cursor: pointer;
        } /* readonly */
    } /* input */
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
    } /* select */
    button {
        background: #4099de;
        border-radius: 5px;
        border: 1px solid #4099de;
        min-width: 100px;
        height: 35px;
        line-height: 35px;
        font-size: 15px;
        padding: 0 15px;
        color: #ffffff;
        width: 100%;
        margin: 0 auto 10px;
        -webkit-transition: all 0.2s ease-in-out;
        -moz-transition: all 0.2s ease-in-out;
        -o-transition: all 0.2s ease-in-out;
        transition: all 0.2s ease-in-out;
        &:hover {
            background: #0071C9;
            border-color: #0071C9;
        } /* hover */
    } /* button */
} /* Edit_Transaction_Modal */
.Results_area {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    p {
        display: block;
        margin: 10px 0 0;
        font-size: 15px;
        color: #000;
    } /* p */
} /* Results_area */
#deposit_management_page {
    margin: 10px auto 0;
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
        hr {
            margin: 15px auto;
            border-color: #ddd;
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
                    @media (min-width: 320px) and (max-width: 480px) {
                        width: 50%;
                        margin: 5px 0;
                    } /* media */
                    @media (min-width: 481px) and (max-width: 767px) {
                        width: 33.33333%;
                        margin: 5px 0;
                    } /* media */
                    @media (min-width: 768px) and (max-width: 991px) {
                        width: 25%;
                        margin: 5px 0;
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
                        line-height: 1.2;
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
            margin: 0 0 10px;
            button.add_receipts {
                display: block;
                background: #4099de;
                border: none;
                border-radius: 4px;
                color: #fff;
                font-size: 15px;
                padding: 5px 15px;
                &:hover {
                    background: #0071C9;
                } /* hover */
            } /* add_receipts */
            .buttons_area {
                display: flex;
                align-items: center;
                justify-content: flex-end;
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
                    &.print_button {
                        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='19.339' height='19.339' viewBox='0 0 19.339 19.339'%3E%3Cpath d='M17.471,17.471v1.934a1.934,1.934,0,0,1-1.934,1.934H7.8a1.934,1.934,0,0,1-1.934-1.934V17.471H3.934A1.934,1.934,0,0,1,2,15.537v-5.8A1.94,1.94,0,0,1,3.934,7.8H5.868V3.934A1.94,1.94,0,0,1,7.8,2h7.735a1.934,1.934,0,0,1,1.934,1.934V7.8H19.4a1.934,1.934,0,0,1,1.934,1.934v5.8A1.934,1.934,0,0,1,19.4,17.471Zm0-1.934H19.4v-5.8H3.934v5.8H5.868V13.6A1.94,1.94,0,0,1,7.8,11.669h7.735A1.934,1.934,0,0,1,17.471,13.6ZM15.537,7.8V3.934H7.8V7.8ZM7.8,13.6v5.8h7.735V13.6Z' transform='translate(-2 -2)' fill='%23333b45'/%3E%3C/svg%3E");
                    } /* print_button */
                } /* button */
            } /* buttons_area */
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
            .table-responsive {
                width: 100%;
                margin: 0 auto 20px;
                position: relative;
                @media (min-width: 320px) and (max-width: 991px) {
                    overflow: auto;
                } /* media */
                table {
                    width: 100%;
                    border: 1px solid #e2e8f0;
                    display: table;
                    thead {
                        tr {
                            th {
                                padding: 10px 5px;
                                line-height: 20px;
                                font-weight: normal;
                                font-size: 15px;
                                border: 1px solid #5E697C;
                                vertical-align: middle;
                                text-align: center !important;
                                color: #ffffff;
                                background: #4a5568;
                            } /* th */
                        } /* tr */
                    } /* thead */
                    tbody {
                        tr {
                            td {
                                text-align: center !important;
                                padding: 15px 5px;
                                vertical-align: middle;
                                line-height: 20px;
                                font-size: 15px;
                                border: 1px solid #ced4dc;
                                color: #000000;
                                font-weight: normal;
                                background: #ffffff;
                                height: auto;
                                &.td-fit {
                                    display: flex;
                                    align-items: center;
                                    justify-content: center;
                                    border-right: none;
                                    border-bottom: none;
                                    a, button {
                                        color: #b3b9bf;
                                        margin: 0 5px !important;
                                        outline: none;
                                        svg {
                                            path {
                                                fill: #b3b9bf;
                                                &:hover {fill: #3d92d4;}
                                            }
                                        }
                                        &:hover {color: #3d92d4;}
                                    } /* a */
                                } /* td-fit */
                                .text-left {
                                    text-align: center !important;
                                } /* text-left */
                            } /* td */
                        } /* tr */
                    } /* tbody */
                } /* table */
            } /* table-responsive */
        } /* table_area */
    } /* content_page */
} /* deposit_management_page */
.d-flex{
      display: flex !important;
  }
</style>
