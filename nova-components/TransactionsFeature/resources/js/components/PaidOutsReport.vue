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
            <div class="title">{{ __('Paid Outs Report') }}</div>
            <div class="content_page">

                <!-- Filter Area -->
                <div class="filter_area">

                    <div class="item">
                        <input
                            readonly
                            v-model="dateFrom"
                            ref="datePickerFrom"
                            type="text"
                            :placeholder="__('Period From')"
                        >
                    </div>

                    <div class="item">
                        <input
                            readonly
                            v-model="dateTo"
                            ref="datePickerTo"
                            type="text"
                            :placeholder="__('Period To')"
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

                <!-- Action Buttons -->
                <div class="action_buttons">
                    <div class="buttons_area" v-if="data">
                        <button type="button" class="excel_button" @click="excelExport"></button>
                        <button type="button" class="print_button" @click="printReport"></button>
                    </div><!-- buttons_area -->
                </div><!-- action_buttons -->

                <!-- Table Listing Area -->
              
                    <div class="table_area" v-if="data" >
                        <div class="table-responsive relative">
                            <loading :active="isLoading" :loader="'spinner'" :color="'#7e7d7f'" :opacity="0.7" :height="20" :width="20" :is-full-page="false"></loading>
                            <table class="table w-full"
                                cellpadding="0"
                                cellspacing="0"
                            >
                                <thead>
                                    <tr>
                                        <th>{{__('Date Time')       }}</th>
                                        <th>{{__('Room')            }}</th>
                                        <th>{{__('Room Number')     }}</th>
                                        <th>{{__('Customer')        }}</th>
                                        <th>{{__('TRN Group')       }}</th>
                                        <th>{{__('TRN Code')        }}</th>
                                        <th>{{__('Description')     }}</th>
                                        <th>{{__('Credit')          }}</th>
                                        <th>{{__('Debit')           }}</th>
                                        <th>{{__('Payment Type')    }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template v-if="data">
                                        <tr v-for="data in data.data">
                                            <td>{{ data.created_at              }}</td>
                                            <td>{{ locale == 'ar' ? 
                                                    JSON.parse(data.room).ar : 
                                                    JSON.parse(data.room).en    }}</td>
                                            <td>{{ data.room_number             }}</td>
                                            <td>{{ data.customer                }}</td>
                                            <td>{{ data.trn_group               }}</td>
                                            <td>{{ data.trn_code                }}</td>
                                            <td>{{ data.description             }}</td>
                                            <td>{{ Math.abs(data.credit)        }}</td>
                                            <td>{{ Math.abs(data.debit)         }}</td>
                                            <td>{{ data.meta_payment_type       }}</td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div v-else class="table_area">
                        <div class="no_data_show">
                            <svg xmlns="http://www.w3.org/2000/svg" width="65" height="51"><path d="M56 40h2a1 1 0 1 1 0 2h-2v2a1 1 0 1 1-2 0v-2h-2a1 1 0 1 1 0-2h2v-2a1 1 0 1 1 2 0v2zm-5.364-8H38v8h7.05c.35-3.53 2.535-6.517 5.587-8zM45.05 42H6a6 6 0 0 1-6-6V6a6 6 0 0 1 6-6h44a6 6 0 0 1 6 6v25.05c5.053.502 9 4.765 9 9.95 0 5.523-4.477 10-10 10-5.185 0-9.45-3.947-9.95-9zM20 30h16v-8H20v8zm0 2v8h16v-8H20zm34-2v-8H38v8h16zM2 30h16v-8H2v8zm0 2v4a4 4 0 0 0 4 4h12v-8H2zm18-12h16v-8H20v8zm34 0v-8H38v8h16zM2 20h16v-8H2v8zm52-10V6a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v4h52zm1 39a8 8 0 1 0 0-16 8 8 0 1 0 0 16z" fill="#a8b9c5" fill-rule="nonzero"/></svg>
                            <span>{{__('Please start to select month and year to initiate paid outs report')}}</span>
                        </div><!-- no_data_show -->
                    </div>
            </div>
        </div>

        <!-- Single Print  -->
        <form id="invoices_form" target="_blank" method="post"  style="display: none" action="/home/print/paidOutsReportPrint">
            <input type="hidden" :value="JSON.stringify(data)" name="data">
        </form>
    </div>
</template>

<script>

import DeleteConfirm from './DeleteConfirm'
import '../airbnb-modified.css'
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css';
import Pagination from './Pagination'
import XLSX from 'xlsx'

export default {
    name: "paid-outs-report",
    components:{
        Loading,
        Pagination
    },
    data(){
        return {
            dateFrom : null,
            dateTo : null,
            locale : Nova.config.local,
            isLoading : false,
            data : null,
            team_id : Nova.config.user.current_team_id,
            currency :Nova.app.currentTeam.currency,
            years: [],
            crumbs: [],
            flatpickrFrom: null,
            flatpickrTo: null
        }
    },
    computed:{
        dateFormat() {
            return  'Y-m-d'
        },
        getYears() {
            const currentYear = new Date().getFullYear();
            let years = [];
            for (let i = 0; i < 5; i++) {
                years.push(currentYear - i);
            }
            return years;
        }
    },
    methods:{
        capitalize(label){
            if (typeof label !== 'string') return ''
            return label.charAt(0).toUpperCase() + label.slice(1)
        },
        async excelExport(){
            try {
                this.isLoading = true;
                const response = await axios.post(window.FANDAQAH_API_URL + '/reports/paid-outs-excel' , {
                    data: this.data,
                }, {
                    headers: {
                        lang: this.locale
                    }
                })
                let Ws = XLSX.utils.json_to_sheet(response.data.data)
                let wb = XLSX.utils.book_new();
                XLSX.utils.book_append_sheet(wb, Ws, response.data.file_name );
                XLSX.writeFile(wb, response.data.file_name + '.xlsx');
                this.$toasted.show(response.data.msg, {type: 'success'});
            } catch (e) {
            } finally {
                this.isLoading = false;
            }
        },
        printReport(){
            $('#invoices_form').submit();
        },
        resetFilters(){
            let opt = {}
            this.$router.push({
                name : 'paid-outs-report',
                query: Object.assign({}, this.legacyQuery, opt)
            } , () => {
                this.dateFrom = null;
                this.dateTo = null;
                this.getData();
            })
        },
        async getData(){
            try {
                this.isLoading = true;
                let config = {
                    headers : {
                        'x-team' : this.team_id,
                        'x-localization' : this.locale
                    },
                    params : this.$route.query
                }
                const response = await axios.get(window.FANDAQAH_API_URL + `/reports/paid-outs`, config);
                this.data = response.data;
            } catch (e) {
                console.log(err);
            } finally {
                this.isLoading = false;
            }
        },
        
    },
    watch:{
        dateFrom: function (val) {
            if(val != null){
                let opt = {}
                opt["dateFrom"] = val;
                opt["page"] = 1;
                this.$router.push({
                    name : 'paid-outs',
                    query: Object.assign({}, this.$route.query, opt)
                } , () => {
                    this.getData();
                })
            }

            if(val == null){
                let opt = {}
                opt["page"] = 1;
                this.$router.push({
                    name : 'paid-outs',
                    query: Object.assign({}, this.$route.query, opt)
                } , () => {
                    this.getData();
                })
            }
        },
        dateTo: function (val) {
            if(val != null){
                let opt = {}
                opt["dateTo"] = val;
                opt["page"] = 1;
                this.$router.push({
                    name : 'paid-outs',
                    query: Object.assign({}, this.$route.query, opt)
                } , () => {
                    this.getData();
                })
            }

            if(val == null){
                let opt = {}
                opt["page"] = 1;
                this.$router.push({
                    name : 'paid-outs',
                    query: Object.assign({}, this.$route.query, opt)
                } , () => {
                    this.getData();
                })
            }
        },
    },
    mounted() {
        const self = this

        this.$nextTick(() => {
            this.flatpickrFrom = flatpickr(this.$refs.datePickerFrom, {
                enableTime: false,
                enableSeconds: false,
                disableMobile: "true",
                // onClose: this.getTransactions(),
                dateFormat: this.dateFormat,
                allowInput: false,
                mode: 'single',
                time_24hr: false,
                onReady() {
                    self.$refs.datePickerFrom.parentNode.classList.add('date-filter')
                },
                onChange(){
                    self.dateFrom = self.$refs.datePickerFrom.value;
                },
                locale : self.locale
            })

            this.flatpickrTo = flatpickr(this.$refs.datePickerTo, {
                enableTime: false,
                enableSeconds: false,
                disableMobile: "true",
                // onClose: this.getTransactions(),
                dateFormat: this.dateFormat,
                allowInput: false,
                mode: 'single',
                time_24hr: false,
                onReady() {
                    self.$refs.datePickerTo.parentNode.classList.add('date-filter')
                },
                onChange(){
                    self.dateTo = self.$refs.datePickerTo.value;
                },
                locale : self.locale
            })

        })

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
                text : 'Paid outs report',
                to : '#'
            }
        ];
        this.query = this.$route.query;
        this.legacyQuery = this.$route.query;
        
        
        //this.getData();
        

    },

}
</script>

<style lang="scss">

.show_credit_note_button {
    background-image: url("data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiA/PjxzdmcgZGF0YS1uYW1lPSIxLURvY3VtZW50IiBpZD0iXzEtRG9jdW1lbnQiIHZpZXdCb3g9IjAgMCA0OCA0OCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48dGl0bGUvPjxwYXRoIGQ9Ik00Mi43MSw4LjI5bC04LThBMSwxLDAsMCwwLDM0LDBIOEEzLDMsMCwwLDAsNSwzVjQ1YTMsMywwLDAsMCwzLDNINDBhMywzLDAsMCwwLDMtM1Y5QTEsMSwwLDAsMCw0Mi43MSw4LjI5Wk0zNSwzLjQxLDM5LjU5LDhIMzZhMSwxLDAsMCwxLTEtMVpNNDEsNDVhMSwxLDAsMCwxLTEsMUg4YTEsMSwwLDAsMS0xLTFWM0ExLDEsMCwwLDEsOCwySDMzVjdhMywzLDAsMCwwLDMsM2g1WiIvPjxyZWN0IGhlaWdodD0iMiIgd2lkdGg9IjIwIiB4PSIxNiIgeT0iMTgiLz48cmVjdCBoZWlnaHQ9IjIiIHdpZHRoPSIyNCIgeD0iMTIiIHk9IjI0Ii8+PHJlY3QgaGVpZ2h0PSIyIiB3aWR0aD0iMjQiIHg9IjEyIiB5PSIzMCIvPjxyZWN0IGhlaWdodD0iMiIgd2lkdGg9IjE2IiB4PSIxMiIgeT0iMzYiLz48cmVjdCBoZWlnaHQ9IjIiIHdpZHRoPSIyIiB4PSIzNCIgeT0iMzYiLz48cmVjdCBoZWlnaHQ9IjIiIHdpZHRoPSIyIiB4PSIzMCIgeT0iMzYiLz48L3N2Zz4=");
    background-color: #d5d5d5;
    &:hover {
        background-color: #d0d0d0;
    }
}
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
                    padding: 0 30px;
                    font-size: 15px;
                    border: 1px solid #ddd;
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
            scrollbar-color: #b3b3b3 #e8e8e8;
            scrollbar-width: thin;
            overflow-x: auto;
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
                                             width: 20px;
                                             height: 20px;
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
    .error_field {
        border: 1px solid #ff8686 !important
    }
} /* deposit_management_page */
</style>
