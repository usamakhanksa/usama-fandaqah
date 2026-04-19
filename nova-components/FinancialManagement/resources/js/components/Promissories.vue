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

        <div id="promissories_index_page">
            <div class="title">{{ __('Promissories Management') }}</div>
            <div class="content_page">

                <!-- Filter Area -->
                <div class="filter_area">
                    <div class="item">
                        <input
                            type="text"
                            v-model.lazy="promissoryNumber"
                            :placeholder="__('Promissory Number')"
                        >
                    </div>

                    <div class="item">
                        <input
                            type="text"
                            v-model.lazy="reservationNumber"
                            :placeholder="__('Reservation Number')"
                        >
                    </div>

                    <div class="item">
                        <input
                            type="text"
                            v-model.lazy="customerName"
                            :placeholder="__('Customer Name')"
                        >
                    </div>

                    <div class="item">
                        <input
                            type="text"
                            v-model.lazy="companyName"
                            :placeholder="__('Company Name')"
                        >
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

                    <div class="item">
                        <select v-model="status" :required="true">
                            <option value="" disabled>{{__('Promissory status')}}</option>
                            <option :value="'all'">{{__('All')}}</option>
                            <option :value="'pending'" :selected="true">{{__('Promissory not fulfilled')}}</option>
                            <option :value="'fulfilled'">{{__('Promissory fulfilled')}}</option>

                        </select>
                    </div>

                    <div class="item" v-show="employees.length">
                        <select v-model="employeeId">
                            <option value="" selected>{{__('Employee')}}</option>
                            <option v-for="(employee,i) in employees" :value="employee.id" :key="i">{{ employee.name }}</option>
                        </select>
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
                    <div class="buttons_area" v-if="data.length">
                        <button type="button" class="excel_button" @click="excelExport"></button>
                        <button type="button" class="print_button" @click="printReport"></button>
                    </div><!-- buttons_area -->
                </div><!-- action_buttons -->

                <!-- Table Listing Area -->
                <div class="table_area">
                    <div class="table-responsive relative">
                        <loading :active="loading" :loader="'spinner'" :color="'#7e7d7f'" :opacity="0.7" :height="20" :width="20" :is-full-page="false"></loading>
                        <table class="table w-full"
                               cellpadding="0"
                               cellspacing="0"
                        >
                            <thead>
                            <tr>
                                <th>{{ __('Promissory Number') }}</th>
                                <th>{{ __('Reservation Number') }}</th>
                                <th>{{ __('Creation Date') }}</th>
                                <th>{{ __('Amount Total') }}</th>
                                <th>{{ __('Amount Fulfilled') }}</th>
                                <th>{{ __('Remaining Amount') }}</th>
                                <th>{{ __('Customer Name') }}</th>
                                <th>{{ __('Company Name') }}</th>
                                <th>{{ __('Due Date') }}</th>
                                <th>{{ __('Employee') }}</th>
                                <th>{{  __('Actions') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            <template v-if="data.length">

                                <tr v-for="(promissory,i) in data" :key="i">
                                    <td>{{promissory.serial}}</td>
                                    <td><router-link class="text-primary" :to="{name: 'reservation', params: {id: promissory.reservation.id}}">{{promissory.reservation.number}}</router-link></td>
                                    <td>{{promissory.creation_date }}</td>
                                    <td>{{promissory.amount_total}} </td>
                                    <td>{{promissory.amount_fulfilled}} </td>
                                    <td>{{(promissory.amount_remaining).toFixed(2)}} </td>
                                    <td v-if="promissory.reservation.customer_id"><router-link class="text-primary" :to="{path: `/resources/customers/${promissory.reservation.customer_id}`}">{{promissory.reservation.customer_name}}</router-link></td>
                                    <td v-if="promissory.reservation.company_id"><router-link class="text-primary" :to="{path: `/companies/${promissory.reservation.company_id}/profile`}">{{promissory.reservation.company_name}}</router-link></td>
                                    <td v-else>-</td>
                                    <td>{{promissory.due_date}}</td>
                                    <td>{{promissory.creator.name}}</td>
                                    <td class="td-fit">

                                        <button
                                            v-if="promissory.transactions.length"
                                            class=""
                                            @click="openFulfillmentLog(promissory)"
                                            v-tooltip="{
                                            targetClasses: ['it-has-a-tooltip'],
                                            placement: 'top',
                                            content: __('View fulfillment log'),
                                            classes: ['tooltip_reset']
                                            }"
                                        >
                                            <icon type="menu" width="22" height="18" view-box="0 0 22 16" />
                                        </button>


                                         <button
                                            v-if="promissory.status != 'fulfilled'"
                                            class=""
                                            @click="fulfillPromissory(promissory)"
                                            v-tooltip="{
                                            targetClasses: ['it-has-a-tooltip'],
                                            placement: 'top',
                                            content: __('Fulfil Promissory'),
                                            classes: ['tooltip_reset']
                                            }"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" height="18" viewBox="0 0 496 496" width="22" xmlns:v="https://vecta.io/nano"><path d="M393.053 116H102.947c-26.467 0-48 21.533-48 48v168c0 26.467 21.533 48 48 48h290.105c26.467 0 48-21.533 48-48V164c.001-26.467-21.532-48-48-48zm-55.825 232H158.772c-6.618-36.418-35.406-65.206-71.824-71.824v-56.352c36.42-6.618 65.206-35.406 71.824-71.824H337.23c6.618 36.418 35.406 65.206 71.824 71.824v56.352c-36.42 6.618-65.207 35.406-71.825 71.824zm71.825-184v22.972c-18.73-5.46-33.51-20.243-38.972-38.972h22.972c8.822 0 16 7.178 16 16zm-306.106-16h22.972c-5.46 18.73-20.243 33.51-38.972 38.972V164c0-8.822 7.178-16 16-16zm-16 184v-22.972c18.73 5.46 33.51 20.243 38.972 38.972h-22.972c-8.822 0-16-7.178-16-16zm306.106 16H370.08c5.46-18.73 20.243-33.51 38.972-38.972V332c0 8.822-7.178 16-16 16zM248 177.053c-39.12 0-70.947 31.827-70.947 70.947S208.88 318.947 248 318.947 318.947 287.12 318.947 248 287.12 177.053 248 177.053zm0 109.894c-21.476 0-38.947-17.472-38.947-38.947s17.472-38.947 38.947-38.947 38.947 17.472 38.947 38.947-17.47 38.947-38.947 38.947zM491.314 91.314l-24 24c-6.25 6.248-16.38 6.248-22.628 0l-24-24c-6.248-6.25-6.248-16.38 0-22.628C425.924 63.45 433.88 62.62 440 66.16V48c0-8.822-7.178-16-16-16H296c-8.836 0-16-7.164-16-16s7.164-16 16-16h128c26.467 0 48 21.533 48 48v18.16c6.12-3.542 14.076-2.712 19.314 2.525 6.248 6.25 6.248 16.38 0 22.628zM216 480c0 8.836-7.164 16-16 16H72c-26.467 0-48-21.533-48-48v-18.16c-6.12 3.542-14.076 2.712-19.314-2.525-6.248-6.25-6.248-16.38 0-22.628l24-24c6.25-6.248 16.38-6.248 22.628 0l24 24C85.427 414.8 78.123 432 64 432c-2.77 0-5.53-.732-8-2.16V448c0 8.822 7.178 16 16 16h128c8.836 0 16 7.164 16 16z"/></svg>
                                        </button>




                                        <a v-permission = "'print promissory notes'" :href="`/home/reservation/promissory/${promissory.hash_id}`" target="_blank"  :title="__('Print Promissory')" class="">
                                            <svg xmlns="http://www.w3.org/2000/svg"  width="22" height="18" viewBox="0 0 22 16"><path d="M17.471,17.471v1.934a1.934,1.934,0,0,1-1.934,1.934H7.8a1.934,1.934,0,0,1-1.934-1.934V17.471H3.934A1.934,1.934,0,0,1,2,15.537v-5.8A1.94,1.94,0,0,1,3.934,7.8H5.868V3.934A1.94,1.94,0,0,1,7.8,2h7.735a1.934,1.934,0,0,1,1.934,1.934V7.8H19.4a1.934,1.934,0,0,1,1.934,1.934v5.8A1.934,1.934,0,0,1,19.4,17.471Zm0-1.934H19.4v-5.8H3.934v5.8H5.868V13.6A1.94,1.94,0,0,1,7.8,11.669h7.735A1.934,1.934,0,0,1,17.471,13.6ZM15.537,7.8V3.934H7.8V7.8ZM7.8,13.6v5.8h7.735V13.6Z" transform="translate(-2 -2)" fill="#333b45"/></svg>
                                        </a>

                                        <button type="button" v-if="parseFloat(promissory.amount_remaining) == parseFloat(promissory.amount_total)" @click="openDeleteConfirm(promissory)" :title="__('Delete Promissory')" class="appearance-none cursor-pointer text-70 hover:text-primary mx-2" v-permission="'delete promissory'">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" aria-labelledby="delete" role="presentation" class="fill-current"><path fill-rule="nonzero" d="M6 4V2a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2h5a1 1 0 0 1 0 2h-1v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6H1a1 1 0 1 1 0-2h5zM4 6v12h12V6H4zm8-2V2H8v2h4zM8 8a1 1 0 0 1 1 1v6a1 1 0 0 1-2 0V9a1 1 0 0 1 1-1zm4 0a1 1 0 0 1 1 1v6a1 1 0 0 1-2 0V9a1 1 0 0 1 1-1z"></path></svg>
                                        </button>

                                       

                                        <button
                                        v-if="user.digital_signature"
                                        type="button"
                                        @click="sendSms(promissory)"
                                        :disabled="smsStates[promissory.id] && smsStates[promissory.id].disabled"
                                        >
                                        <svg  v-show="!(smsStates[promissory.id] && smsStates[promissory.id].disabled)" height="18" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg" fill="#000000"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <g data-name="19. Send" id="_19._Send"> <path class="cls-1" d="M21,16.6a3,3,0,0,1-1.66-.51l-4.89-3.26a1,1,0,0,1,1.1-1.66l4.9,3.26a1,1,0,0,0,1.1,0l4.9-3.26a1,1,0,0,1,1.1,1.66l-4.89,3.26A3,3,0,0,1,21,16.6Z"></path> <path class="cls-1" d="M29,25H13a3,3,0,0,1-3-3V10a3,3,0,0,1,3-3H29a3,3,0,0,1,3,3V22A3,3,0,0,1,29,25ZM13,9a1,1,0,0,0-1,1V22a1,1,0,0,0,1,1H29a1,1,0,0,0,1-1V10a1,1,0,0,0-1-1Z"></path> <path class="cls-2" d="M7,19H5a1,1,0,0,1,0-2H7a1,1,0,0,1,0,2Z"></path> <path class="cls-2" d="M7,15H3a1,1,0,0,1,0-2H7a1,1,0,0,1,0,2Z"></path> <path class="cls-2" d="M7,11H1A1,1,0,0,1,1,9H7a1,1,0,0,1,0,2Z"></path> </g> </g></svg>
                            
                                        <p v-show="smsStates[promissory.id] && smsStates[promissory.id].disabled">
                                            {{ smsStates[promissory.id] ? smsStates[promissory.id].text : 'SMS' }}
                                        </p>
                                        </button>


                                    </td>
                                </tr>
                            </template>
                            <template v-else>
                                <tr>
                                    <td colspan="11">{{__('No promissories found')}}</td>
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
                        <p>{{__('Total')}}  : {{paginator.total}}</p>
                    </div><!-- Results_area -->
                </div>

            </div>
        </div>

        <form id="promissories_form" target="_blank" method="post"  style="display: none" action="/home/print/promissoriesPrint">
            <input type="hidden" :value="JSON.stringify(ids)" name="ids">
        </form>

        <fulfill-promissory />
        <promissory-fulfillment-history />
        <promissory-delete ref="deletePromissoryRef" :promissory_id="target_promissory_to_delete" />
    </div>
</template>

<script>
import flatpickr from "flatpickr";
import './airbnb-modified.css'
import moment from "moment";
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css';
import Pagination from './Pagination'
import XLSX from 'xlsx'
import FulfillPromissory from './FulfillPromissory';
import PromissoryFulfillmentHistory from './PromissoryFulfillmentHistory'
import PromissoryDelete from './Helpers/PromissoryDelete'

export default {
    name: "transactions",
    components:{
      Loading,
      Pagination,
      FulfillPromissory,
      PromissoryFulfillmentHistory,
      PromissoryDelete
    },
    data(){
        return {
            promissoryNumber : '',
            reservationNumber : '',
            customerName : '',
            companyName : '',
            employeeId : '',
            createdFrom : null,
            createdTo : null,
            status : 'pending',
            flatpickrFrom: null,
            flatpickrTo: null,
            locale : Nova.config.local,
            loading : false,
            employees : [],
            data : [],
            ids : [],
            paginator : {},
            crumbs : [],
            query : {},
            selectedPage : 1,
            legacyQuery : {},
            teamId : Nova.config.user.current_team_id,
            per_page : 20,
            time_12hrs_enabled : false,
            currency :Nova.app.currentTeam.currency,
            target_promissory_to_delete : null,
            user: {
                digital_signature: null
            },
            smsDisabled: false,
            smsButtonText: 'SMS',
            countdown: 60,
            countdownTimer: null,
            smsStates: {} // Holds state per promissory.id
        }
    },
    computed:{
        dateFormat() {
            return  'Y-m-d H:i'
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
        triggerPrintHref(transaction,i){

            this.$nextTick(() => {
                if(transaction.payable_type === 'App\\Reservation'){
                    this.$refs.reservation_href[i].click();
                }else{
                    this.$refs.team_href[i].click();
                }
            });
        },

        excelExport(){

            this.loading = true;
            axios.post(window.FANDAQAH_API_URL + '/promissories/excel' , {
                'ids' : this.ids ,
                'lang' : Nova.config.local
            }).then(response => {
                this.loading = false;
                let ws = XLSX.utils.json_to_sheet(response.data.data)
                let wb = XLSX.utils.book_new();
                XLSX.utils.book_append_sheet(wb, ws, response.data.file_name );
                XLSX.writeFile(wb, response.data.file_name + '.xlsx');
                this.$toasted.show(response.data.msg, {type: 'success'});
            });
        },
        printReport(){
            $('#promissories_form').submit();
        },
        resetFilters(){
            let opt = {}
            opt["page"] = 1;
            opt["filter[by_status]"] = 'pending';
            this.$router.push({
                name : 'promissories',
                query: Object.assign({}, this.legacyQuery, opt)
            } , () => {
                this.promissoryNumber = '';
                this.reservationNumber = '';
                this.customerName = '';
                this.companyName = '';
                this.employeeId = '';
                this.status = 'pending';
                this.createdFrom = null;
                this.createdTo = null;
                this.getData();
            })
        },
        toMomentsFormat(format){
            var res=format;
            res = res.replace('Y', 'YYYY');
            res = res.replace('m', 'MM');
            res = res.replace('d', 'DD');
            return res;
        },
        getData(page=1){

             let config = {
                headers : {
                    'x-team' : this.teamId,
                    'x-localization' : this.locale
                },
                params : this.$route.query
            }
            this.loading = true;
            axios.get(window.FANDAQAH_API_URL + `/promissories/list` , config)
                .then(response => {

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
                    this.ids = response.data.ids;

                })
                .catch((err) => {
                    console.log(err);
                }).finally(() => {
                    this.loading = false;
                });
        },
        getEmployees(){
            axios.get(window.FANDAQAH_API_URL + `/users/dropDown?team_id=${this.teamId}`)
            .then((response) => {
                this.employees = response.data.data;
            })
        },

        fulfillPromissory(promissory){
            Nova.$emit('fulfill-promissory' , promissory.id);
        },

        openFulfillmentLog(promissory){
            Nova.$emit('open-promissory-fulfilllment-history' , promissory.id);
        },

        drawCalendars(){
                    const self = this;
                    this.flatpickrFrom = flatpickr(this.$refs.datePickerFrom, {
                        enableTime: true,
                        enableSeconds: false,
                        disableMobile: "true",
                        // onClose: this.getTransactions(),
                        dateFormat: this.dateFormat,
                        allowInput: false,
                        mode: 'single',
                        time_24hr: !self.time_12hrs_enabled,
                        onReady() {
                            self.$refs.datePickerFrom.parentNode.classList.add('date-filter')
                        },
                        onChange(){
                            self.dateFrom = self.$refs.datePickerFrom.value;
                        },
                        locale : self.locale
                    })

                    this.flatpickrTo = flatpickr(this.$refs.datePickerTo, {
                        enableTime: true,
                        enableSeconds: false,
                        disableMobile: "true",
                        // onClose: this.getTransactions(),
                        dateFormat: this.dateFormat,
                        allowInput: false,
                        mode: 'single',
                        time_24hr: !self.time_12hrs_enabled,
                        onReady() {
                            self.$refs.datePickerTo.parentNode.classList.add('date-filter')
                        },
                        onChange(){
                            self.dateTo = self.$refs.datePickerTo.value;
                        },
                        locale : self.locale
                    })

        },
        getSearchWithTimeSetting(){
            axios.get('/nova-vendor/transactions-feature/search-with-time')
            .then(res => {
                this.time_12hrs_enabled = res.data.time_12hrs_enabled;
                this.drawCalendars();
            })
        },

        openDeleteConfirm(promissory) {
            this.target_promissory_to_delete = promissory.id;
            this.$refs.deletePromissoryRef.$refs.deletePromissoryModal.open();
        },
        startCountdown(id) {
            this.smsStates[id].timer = setInterval(() => {
            this.smsStates[id].secondsLeft--;

            if (this.smsStates[id].secondsLeft > 0) {
                this.smsStates[id].text = `${this.smsStates[id].secondsLeft}s`;
            } else {
                clearInterval(this.smsStates[id].timer);
                this.$set(this.smsStates, id, {
                disabled: false,
                text: 'SMS',
                timer: null,
                secondsLeft: 60
                });
            }
            }, 1000);
        },
          sendSms(promissory) {
            if(!this.user.digital_signature) {
                this.$toasted.show(this.__('Please add a signature before sending the SMS') + '&nbsp' + `<a href="${window.location.origin}/home/profile" target="_blank">${this.__('Click here to add signature')}</a>`, {type: 'error'})
                return;
            }

            const id = promissory.id;

            // Avoid sending if already disabled
            
            if (this.smsStates[id] && this.smsStates[id].disabled) return;


            this.$set(this.smsStates, id, {
                disabled: true,
                text: '60s',
                timer: null,
                secondsLeft: 60
            });

            this.loading = true
            this.startCountdown(id);
            axios
              .post('/nova-vendor/calender/reservation/send-promissory-via-sms' , {
                    promissory,
              })
              .then(response => {
                this.$toasted.show(this.__('The sending is underway - the message will be received within 1 minute'), {type: 'success'})
                this.loading = false
              })
          },
          getUserInfo() {
            if(!this.user.digital_signature) {
                this.loading = true;
                axios.get('/nova-vendor/settings/getUserObject')
                        .then((res) => {
                            if(res.data.digital_signature) {
                                axios.post('/signature/uncompress', {
                                    signature: res.data.digital_signature.signature_base64,
                                }).then(response => {
                                    this.user.digital_signature = response.data.signature;
                                }).catch(error => {
                                    console.error('Error uncompressing signature:', error);
                                }).finally(() => {
                                    this.loading = false;
                                });
                            }
                    })
             }
          }

    },
    watch:{
          employeeId: function (val) {
            if(val != null){
                let opt = {}
                opt["filter[by_employee]"] = val;
                opt["page"] = 1;
                this.$router.push({
                    name : 'promissories',
                    query: Object.assign({}, this.$route.query, opt)
                } , () => {
                    this.getData();
                })
            }

            if(val == null){
                let opt = {}
                opt["page"] = 1;
                this.$router.push({
                    name : 'promissories',
                    query: Object.assign({}, this.$route.query, opt)
                } , () => {
                    this.promissories();
                })
            }
        },
          status: function (val) {
            if(val != null){
                let opt = {}
                opt["filter[by_status]"] = val;
                opt["page"] = 1;
                this.$router.push({
                    name : 'promissories',
                    query: Object.assign({}, this.$route.query, opt)
                } , () => {
                    this.getData();
                })
            }

            if(val == null){
                let opt = {}
                opt["page"] = 1;
                this.$router.push({
                    name : 'promissories',
                    query: Object.assign({}, this.$route.query, opt)
                } , () => {
                    this.promissories();
                })
            }
        },
        promissoryNumber: function (val) {
            if(val != null){
                let opt = {}
                opt["filter[by_serial]"] = val;
                opt["page"] = 1;
                this.$router.push({
                    name : 'promissories',
                    query: Object.assign({}, this.$route.query, opt)
                } , () => {
                    this.getData();
                })
            }

            if(val == null){
                let opt = {}
                opt["page"] = 1;
                this.$router.push({
                    name : 'promissories',
                    query: Object.assign({}, this.$route.query, opt)
                } , () => {
                    this.getData();
                })
            }
        },
        reservationNumber: function (val) {
            if(val != null){
                let opt = {}
                opt["filter[by_reservation_number]"] = val;
                opt["page"] = 1;
                this.$router.push({
                    name : 'promissories',
                    query: Object.assign({}, this.$route.query, opt)
                } , () => {
                    this.getData();
                })
            }

            if(val == null){
                let opt = {}
                opt["page"] = 1;
                this.$router.push({
                    name : 'promissories',
                    query: Object.assign({}, this.$route.query, opt)
                } , () => {
                    this.getData();
                })
            }
        },
        customerName: function (val) {
            if(val != null){
                let opt = {}
                opt["filter[by_customer_name]"] = val;
                opt["page"] = 1;
                this.$router.push({
                    name : 'promissories',
                    query: Object.assign({}, this.$route.query, opt)
                } , () => {
                    this.getData();
                })
            }

            if(val == null){
                let opt = {}
                opt["page"] = 1;
                this.$router.push({
                    name : 'promissories',
                    query: Object.assign({}, this.$route.query, opt)
                } , () => {
                    this.getData();
                })
            }
        },
        companyName: function (val) {
            if(val != null){
                let opt = {}
                opt["filter[by_company_name]"] = val;
                opt["page"] = 1;
                this.$router.push({
                    name : 'promissories',
                    query: Object.assign({}, this.$route.query, opt)
                } , () => {
                    this.getData();
                })
            }

            if(val == null){
                let opt = {}
                opt["page"] = 1;
                this.$router.push({
                    name : 'promissories',
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
                    name : 'promissories',
                    query: Object.assign({}, this.$route.query, opt)
                } , () => {
                    this.getData();
                })
            }

            if(val == null){
                let opt = {}
                opt["page"] = 1;
                this.$router.push({
                    name : 'promissories',
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
                    name : 'promissories',
                    query: Object.assign({}, this.$route.query, opt)
                } , () => {
                    this.getData();
                })
            }

            if(val == null){
                let opt = {}
                opt["page"] = 1;
                this.$router.push({
                    name : 'promissories',
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
                    name : 'promissories',
                    query: Object.assign({}, this.$route.query, opt)
                } , () => {
                    this.getData();
                })
            }
        }
    },
    mounted() {

        this.getSearchWithTimeSetting();

        this.locale = Nova.config.local;

        this.dynamicType = this.$route.query.type;
        this.crumbs = [
            {
                text : 'Home',
                to : '/dashboards/main'
            },

            {
                text : 'Financial Management',
                to : '/financial-management'
            },

            {
                text : 'Promissories Management',
                to : '#'
            }
        ];

        this.query = this.$route.query;
        this.legacyQuery = this.$route.query;

        // let opt = {}
        // opt["filter[by_status]"] = 'pending';
        // this.$router.push({
        //     name : 'promissories',
        //     query: Object.assign({}, this.$route.query, opt)
        // } , () => {
        //     this.getData();
        // })
        this.getUserInfo();
        this.getData();

        Nova.$on('reservation-promissory-transaction-added' , () => {
            this.getData();
        })

        Nova.$on('delete-promissory' , () => {
            this.getData();
        })

        this.getEmployees();

    },
    beforeDestroy(){
        Nova.$off('fulfill-promissory');
        Nova.$off('reservation-promissory-transaction-added');
        Nova.$off('fulfilllment-history');
    }

}
</script>

<style lang="scss">
.flatpickr-time {
    height: auto !important;
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
#promissories_index_page {
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
                                padding: 10px 5px;
                                vertical-align: middle;
                                line-height: 20px;
                                font-size: 15px;
                                border: 1px solid #ced4dc;
                                color: #000000;
                                font-weight: normal;
                                background: #ffffff;
                                &.td-fit {
                                    display: flex;
                                    align-items: center;
                                    justify-content: center;
                                    border-right: none;
                                    border-bottom: none;
                                        margin: 0;
                                    a, button {
                                        color: #b3b9bf;
                                        margin: 0 5px !important;
                                        outline: none;
                                        svg {
                                          width: 25px;
                                          height: 25px;
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
} /* promissories_index_page */
</style>
