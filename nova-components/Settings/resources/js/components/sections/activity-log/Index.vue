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
          <div id="all_units_page">
    <div class="title">
      <span>{{__('Activity Logs')}}</span>
    </div><!-- title -->
      <div class="content_page">
     <div class="filter_area">

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
             <select v-model="subjectType">
                 <option value="" selected disabled>{{__('Log Type')}}</option>
                 <option v-for="(logType,i) in logTypes"
                        :value="logType.subject_type"
                        :key="i"
                 >
                      {{__(logType.label)}}
                 </option>
             </select>
         </div>


         <div class="item" v-show="subjectType == 'Transaction'">
             <select v-model="transactionType">
                 <option value="" selected disabled>{{__('Transaction Type')}}</option>
                 <option value="deposit_transaction_team">{{__('Deposit Management')}}</option>
                 <option value="withdraw_transaction_team">{{__('Withdraw Management')}}</option>
                 <option value="reservation_transaction">{{__('Transaction')}}</option>
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
      </div>
    <div class="table_content relative">
      <loading :active.sync="isLoading" :can-cancel="true" :loader="'spinner'" :color="'#7e7d7f'" :is-full-page="false"></loading>
      <div class="table_responsive">
        <table>
          <thead>
            <tr>
              <th>{{__('Description')}}</th>
              <th>{{__('Subject Type')}}</th>
              <th>{{__('User')}}</th>
              <th>{{__('The Date')}}</th>
              <th>{{__('Actions')}}</th>
            </tr>
          </thead>
          <tbody>
            <template v-if="collection.length">

               <!-- !(activity.subject && activity.subject_type == 'App\\Transaction' && activity.subject.payable_type == 'App\\Reservation' && !activity.subject.is_public) -->

              <tr v-for="(activity,i)  in collection" :key="i">
                 <td v-if="activity.subject && activity.subject_type == 'App\\Transaction'  &&  activity.subject.payable_type == 'App\\Team' && activity.description == 'created' " >{{__('Create transaction from financial management')}}</td>
                 <td v-else-if="activity.subject && activity.subject_type == 'App\\Transaction'  &&  activity.subject.payable_type == 'App\\Team' && activity.description == 'updated' ">{{__('Update transaction from financial management')}}</td>
                 <td v-else-if="activity.subject && activity.subject_type == 'App\\ReservationTransfer'">
                   <p v-if="activity.description == 'created' && activity.properties && activity.properties.attributes">{{__('Transfer reservation')}} {{activity.properties.attributes['reservation.number']}} {{__('From unit')}} {{activity.properties.attributes['old_unit.unit_number']}} {{__('To unit')}} {{activity.properties.attributes['new_unit.unit_number']}}</p>
                   <p v-else>
                     <template v-if="activity.subject && subject_type == 'Transaction' && activity.subject.payable_type == 'Reservation'"></template>
                     {{activity.description}}
                   </p>
                   </td>
                 <td v-else>{{__(activity.description)}}</td>
                 <td v-if="activity.subject && activity.subject_type == 'App\\Transaction'  &&  activity.subject.payable_type == 'App\\Team' && activity.subject.type == 'deposit'">{{__('Deposit Management')}}</td>
                 <td v-else-if="activity.subject && activity.subject_type == 'App\\Transaction'  &&  activity.subject.payable_type == 'App\\Team' && activity.subject.type == 'withdraw'">{{__('Withdraw Management')}}</td>
                 <td v-else-if="activity.subject == null && activity.subject_type == 'App\\Transaction' && activity.properties && activity.properties.attributes && activity.properties.attributes['type'] == 'deposit'">{{__('Deposit Management')}}</td>
                 <td v-else-if="activity.subject == null && activity.subject_type == 'App\\Transaction' && activity.properties && activity.properties.attributes && activity.properties.attributes['type'] == 'withdraw'">{{__('Withdraw Management')}}</td>
                 <td v-else>{{getSubjectType(activity.subject_type)}}</td>
                 <td v-if="activity.causer"><router-link :to="`/resources/users/${activity.causer.id}`">{{activity.causer.name}}</router-link></td>
                 <td v-else>{{__('System')}}</td>
                 <td>{{activity.created_at}}</td>

                <td>
                  <div class="action">
                    <router-link :to="`/settings/activity-log-info/${activity.id}`" :title="__('View')">
                      <svg xmlns="http://www.w3.org/2000/svg" width="22" height="18" viewBox="0 0 22 16" aria-labelledby="view" role="presentation" class="fill-current"><path d="M16.56 13.66a8 8 0 0 1-11.32 0L.3 8.7a1 1 0 0 1 0-1.42l4.95-4.95a8 8 0 0 1 11.32 0l4.95 4.95a1 1 0 0 1 0 1.42l-4.95 4.95-.01.01zm-9.9-1.42a6 6 0 0 0 8.48 0L19.38 8l-4.24-4.24a6 6 0 0 0-8.48 0L2.4 8l4.25 4.24h.01zM10.9 12a4 4 0 1 1 0-8 4 4 0 0 1 0 8zm0-2a2 2 0 1 0 0-4 2 2 0 0 0 0 4z"></path></svg>
                    </router-link>
                  </div><!-- action -->
                </td>
              </tr>
            </template>
            <template v-else>
              <tr>
                <td colspan="7">{{__('No Activity Logs Found')}}</td>
              </tr>
            </template>
          </tbody>
        </table>
      </div><!-- table_responsive -->
      <div class="w-full flex flex-wrap mt-3 justify-center">
        <pagination
          v-if="paginator.lastPage > 1"
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
      </div><!-- flex -->
      <div class="Results_area" v-if="collection.length">
          <p>{{__('Results')}}  : {{__('From')}} ( {{paginator.from}} ) - {{__('To')}}  ( {{paginator.to}} )</p>
          <p>{{__('Count')}}  : {{paginator.totalResults}}</p>
      </div><!-- Results_area -->
    </div><!-- table_content -->
  </div><!-- all_units_page -->
    </div>

</template>

<script>
    import flatpickr from "flatpickr";
    import { Arabic } from "flatpickr/dist/l10n/ar.js"
    import './airbnb-modified.css'
   import Pagination from './Pagination';
   import Loading from 'vue-loading-overlay';
    export default {
       name : 'index',
       components : {
          Pagination,
          Loading,
       },
       data(){
          return {
            flatpickrFrom: null,
            flatpickrTo: null,
            locale : Nova.config.local,
            collection : [],
            paginator : {},
            baseUrl : null,
            isLoading : false,
            logTypes : [],
            createdFrom : null,
            createdTo : null,
            subjectType : '',
            transactionType : '',
            employeeId : '',
            employees : [],
            team_id : Nova.config.user.current_team_id,
            selectedPage : 1,
            query : {},
            legacyQuery : {},
             crumbs : [],
          }
       },
        computed:{
            dateFormat() {
                return  'Y-m-d H:i'
            },
        },
       methods: {
          getCurrentPage(page){
              this.selectedPage = page;
          },
          getData(){
              let config = {
                headers: {
                    "x-team" : this.team_id ,
                    "x-localization" : this.locale,
                },
                params: this.$route.query
            }

               this.isLoading = true;
               axios.get(window.FANDAQAH_API_URL + `/activity-logs/list` , config)
                  .then(response => {

                     this.collection = response.data.data;
                     this.paginator = {
                        currentPage : response.data.meta.current_page ,
                        lastPage : response.data.meta.last_page ,
                        from : response.data.meta.from,
                        to : response.data.meta.to,
                        totalResults : response.data.meta.total,
                        pathPage : response.data.meta.path + '?page=',
                        firstPageUrl : response.data.links.first ,
                        lastPageUrl : response.data.links.last ,
                        nextPageUrl : response.data.links.next ,
                        prevPageUrl : response.data.links.prev ,
                     };

                    this.query = this.$route.query;
                     this.isLoading = false;
                  })
            },

            getSubjectType(subject_type){
              return this.__(subject_type.replace('App\\',''));
            },
            getEmployees(){
                axios.get(window.FANDAQAH_API_URL + `/users/dropDownForOwnTeams` , {
                    headers : {
                        'x-team' : this.team_id
                    }
                })
                    .then((response) => {
                        this.employees = response.data.data;
                    })
            },

            resetFilters(){
                let opt = {}
                opt["page"] = 1;
                this.$router.push({
                    name : 'settings.activity_logs',
                    query: Object.assign({}, this.legacyQuery, opt)
                } , () => {

                    this.createdFrom = null;
                    this.createdTo = null;
                    this.subjectType = '';
                    this.transactionType = '';
                    this.employeeId = '';
                    this.getData();
                })
            },
        },

        watch : {
            createdFrom: function (val) {
                if(val != null){
                    let opt = {}
                    opt["filter[by_created_from]"] = val;
                    opt["page"] = 1;
                    this.$router.push({
                        name : 'settings.activity_logs',
                        query: Object.assign({}, this.$route.query, opt)
                    } , () => {
                        this.getData();
                    })
                }

                if(val == null){
                    let opt = {}
                    opt["page"] = 1;
                    this.$router.push({
                        name : 'settings.activity_logs',
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
                        name : 'settings.activity_logs',
                        query: Object.assign({}, this.$route.query, opt)
                    } , () => {
                        this.getData();
                    })
                }

                if(val == null){
                    let opt = {}
                    opt["page"] = 1;
                    this.$router.push({
                        name : 'settings.activity_logs',
                        query: Object.assign({}, this.$route.query, opt)
                    } , () => {
                        this.getData();
                    })
                }
            },
            subjectType: function (val) {
                if(val != null){
                    let opt = {}
                    opt["filter[by_subject_type]"] = val;
                    opt["page"] = 1;
                    this.$router.push({
                        name : 'settings.activity_logs',
                        query: Object.assign({}, this.$route.query, opt)
                    } , () => {
                        this.getData();
                    })
                }

                if(val == ''){
                    let opt = {}
                    opt["page"] = 1;
                    this.$router.push({
                        name : 'settings.activity_logs',
                        query: Object.assign({}, this.$route.query, opt)
                    } , () => {
                        this.getData();
                    })
                }
            },
            transactionType: function (val) {
                if(val != null){
                    let opt = {}
                    opt["filter[by_transaction_type]"] = val;
                    opt["page"] = 1;
                    this.$router.push({
                        name : 'settings.activity_logs',
                        query: Object.assign({}, this.$route.query, opt)
                    } , () => {
                        this.getData();
                    })
                }

                if(val == ''){
                    let opt = {}
                    opt["page"] = 1;
                    this.$router.push({
                        name : 'settings.activity_logs',
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
                        name : 'settings.activity_logs',
                        query: Object.assign({}, this.$route.query, opt)
                    } , () => {
                        this.getData();
                    })
                }

                if(val == ''){
                    let opt = {}
                    opt["page"] = 1;
                    this.$router.push({
                        name : 'settings.activity_logs',
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
                        name : 'settings.activity_logs',
                        query: Object.assign({}, this.$route.query, opt)
                    } , () => {
                        this.getData();
                    })
                }
            },

        },

        mounted() {

            const self = this
            let lang = self.locale == 'ar' ? Arabic : 'en';
            this.$nextTick(() => {
                this.flatpickrFrom = flatpickr(this.$refs.datePickerFrom, {
                    enableTime: true,
                    enableSeconds: false,
                    dateFormat: this.dateFormat,
                    allowInput: false,
                    mode: 'single',
                    time_24hr: true,
                    onReady() {
                        self.$refs.datePickerFrom.parentNode.classList.add('date-filter')
                    },
                    onChange(){
                        self.createdFrom = self.$refs.datePickerFrom.value;
                    },
                    locale : lang
                })

                this.flatpickrTo = flatpickr(this.$refs.datePickerTo, {
                    enableTime: true,
                    enableSeconds: false,
                    dateFormat: this.dateFormat,
                    allowInput: false,
                    mode: 'single',
                    time_24hr: true,
                    onReady() {
                        self.$refs.datePickerTo.parentNode.classList.add('date-filter')
                    },
                    onChange(){
                        self.createdTo = self.$refs.datePickerTo.value;
                    },
                    locale : lang
                })

            })

            this.crumbs = [
                {
                    text : 'Home',
                    to : '/dashboards/main'
                },

                {
                    text : 'Settings',
                    to : '/settings'
                },

                {
                    text : 'Activity Logs',
                    to : '#'
                }
            ];
            this.logTypes = [

                {
                    subject_type : 'Transaction',
                    label : 'Transaction Statement'
                },
                {
                     subject_type : 'Customer',
                     label : 'Customer'
                },
                {
                    subject_type : 'Reservation',
                    label : 'Reservation'
                },
                {
                     subject_type : 'Guest',
                     label : 'Guest'
                },
                {
                     subject_type : 'UnitCleaning',
                     label : 'UnitCleaning'
                },
                {
                     subject_type : 'UnitMaintenance',
                     label : 'UnitMaintenance'
                },
                {
                    subject_type : 'Unit',
                    label : 'Unit'
                },
                {
                     subject_type : 'UnitCategory',
                     label : 'UnitCategory'
                },
                {
                     subject_type : 'Highlight',
                     label : 'Highlight'
                },

                {
                     subject_type : 'ServiceLog',
                     label : 'Service Log'
                },

            ];

            this.getEmployees();
            this.query = this.$route.query;
            this.legacyQuery = this.$route.query;

            this.getData();


        }

    }
</script>

<style lang="scss" scoped>

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
}

  #all_units_page {
    border-radius: .5rem;
    overflow: hidden;
    .title {
      background: #f7fafc;
      border-bottom: 1px solid #ddd;
      padding: .75rem;
      color: #000;
      font-size: 1.125rem;
      display: flex;
      align-items: center;
      justify-content: space-between;
      flex-wrap: wrap;
      p {
        display: inline-block;
        font-size: 14px;
        margin: 0 5px 0 0;
      } /* p */
      a {
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
        @media (min-width: 320px) and (max-width: 767px) {
          font-size: 0;
          background-image: url("data:image/svg+xml,%3C%3Fxml version='1.0'%3F%3E%3Csvg xmlns='http://www.w3.org/2000/svg' height='512px' viewBox='0 0 448 448' width='512px'%3E%3Cg%3E%3Cpath d='m408 184h-136c-4.417969 0-8-3.582031-8-8v-136c0-22.089844-17.910156-40-40-40s-40 17.910156-40 40v136c0 4.417969-3.582031 8-8 8h-136c-22.089844 0-40 17.910156-40 40s17.910156 40 40 40h136c4.417969 0 8 3.582031 8 8v136c0 22.089844 17.910156 40 40 40s40-17.910156 40-40v-136c0-4.417969 3.582031-8 8-8h136c22.089844 0 40-17.910156 40-40s-17.910156-40-40-40zm0 0' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%23FFFFFF'/%3E%3C/g%3E%3C/svg%3E%0A");
          background-position: center center;
          background-repeat: no-repeat;
          background-size: 15px;
          width: 30px;
          padding: 0;
          height: 30px;
        } /* media */
        &:hover {
          background-color: #0071C9;
        } /* hover */
      } /* a */
    } /* title */
    .table_content {
      background: #fff;
      padding: 10px;
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
                text-align: center;
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
                text-align: center;
                a {
                  color: #4099de;
                  font-weight: bold;
                  cursor: pointer;
                  &:hover {
                    color: #0071C9;
                  } /* hover */
                } /* a */
                span {
                  display: inline-block;
                  position: relative;
                  &::after {
                    content: "";
                    width: 10px;
                    height: 10px;
                    border-radius: 100%;
                    float: right;
                    margin: 5px 0 0 10px;
                  } /* after */
                  &.enabled {
                    &::after {
                        background: green;
                    } /* after */
                  } /*enabled  */
                  &.maintenance {
                    &::after {
                        background: #aab8c0;
                    } /* after */
                  } /*maintenance  */
                  &.cleaning {
                    &::after {
                        background: #ff9100;
                    } /* after */
                  } /*cleaning  */
                  &.not_enabled {
                    &::after {
                        background: #ff0000;
                    } /* after */
                  } /*not_enabled  */
                } /* span */
                img {
                  margin: 0 auto;
                  display: block;
                } /* img */
                .action {
                  display: flex;
                  align-items: center;
                  justify-content: center;
                  flex-wrap: wrap;
                  a, button {
                    margin: 5px;
                    color: #b3b9bf;
                    svg {
                      display: inline-block;
                    } /* svg */
                    &:hover {
                      color: #4099de;
                    } /* hover */
                  } /* a */
                } /* action */
              } /* td */
            } /* tbody */
          } /* table */
      } /* table_responsive */
      .pagination {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: center;
        margin: 10px auto;
        .page-item {
          .page-link {
              position: relative;
              display: block;
              padding: .5rem .75rem;
              margin-left: -1px;
              line-height: 1.25;
              color: #007bff;
              background-color: #fff;
              border: 1px solid #dee2e6;
              &:hover {
                z-index: 2;
                color: #0056b3;
                text-decoration: none;
                background-color: #e9ecef;
                border-color: #dee2e6;
              } /* hover */
              &:focus {
                z-index: 2;
                outline: 0;
                box-shadow: 0 0 0 .2rem rgba(0, 123, 255, .25);
              } /* focus */
          } /* page-link */
          &:first-child {
              .page-link {
                margin-left: -1px;
                border-top-right-radius: .25rem;
                border-bottom-right-radius: .25rem;
              } /* page-link */
          } /* first-child */
          &:last-child {
              .page-link {
                border-top-left-radius: .25rem;
                border-bottom-left-radius: .25rem;
              } /* page-link */
          } /* first-child */
          &.active {
              .page-link {
                z-index: 1;
                color: #fff;
                background-color: #007bff;
                border-color: #007bff;
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
          } /* active */
        } /* page-item */
      } /* pagination */
    } /* table_content */
  } /* all_units_page */
</style>
