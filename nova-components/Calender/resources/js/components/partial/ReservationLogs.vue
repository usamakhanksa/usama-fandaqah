<template>
  <div class="reservation_logs_block">
    <ul v-if="reservation">
      <li @click="openLogsModal">
        <span>{{ __('created by')}} :

            <p v-if="reservation.is_online">{{ __('System') }}</p>
            <p v-else>{{ reservation.creator.name}}</p>
        </span>
        <time v-if="reservation">{{ reservation.created_at}}</time>
      </li>

    </ul>

    <div v-if="is_integration_shms" class="shomoos_list">
      <!-- <div class="loading_area" v-if="beginLoader">
        <loader v-if="beginLoader" />
      </div> -->
      <!-- <h3 > {{ __('Shomoos Log') }}</h3> -->
      <button class="shomoos_logs_button" @click="getShomosLogs">{{__('Show Shomoos Log')}}</button>
      <ul style="margin-top: 10px;" v-if="shomos_logs.length">
        <template v-for="(log,i) in shomos_logs">
          <li v-if="log.response" v-for="(message,index) in log.response.Header.ProxyFaults" :key="index" :class="message.FaultCode == 201 ? 'success' : 'danger'">
            <span>{{ message.FaultCode }} : <b v-if="message.FaultCode == 201">{{ message.FaultDescription == 'Saved Successfully' || message.FaultDescription == 'تم الحفظ بنجاح' ? __('Create In Shomoos') : __('Updated In Shomoos') }}</b> <p> - {{ message.FaultDescription }}</p></span>
            <time>{{ log.created_at }}</time>
          </li>
        </template>
      </ul>
    </div>
    <sweet-modal v-if="reservation" :enable-mobile-fullscreen="false" :pulse-on-block="true" class="reservation_logs_Modal" title="سجل الاحداث" overlay-theme="dark" ref="logsModal" width="70%" style="max-width: none;">
      <div v-if="reservation.logs.length">
        <table class="relative">
          <loading :active="reservationLogsLoading" :loader="'spinner'" :color="'#7e7d7f'" :opacity="0.7" :is-full-page="false"></loading>
          <thead>
            <tr>
              <th width="65%">الحدث</th>
              <th width="20%">منشئ الحدث</th>
              <th width="15%">التاريخ</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(log, index) in logs" :key="index">
              <td>
                <div class="attributes" v-if="log.properties.attributes && !log.properties.old">
                    <b>{{  log.description }}</b>
                </div>
                <div class="attributes" v-if="log.properties.old && log.properties.attributes">
                  <b>{{  log.description }}</b>

                  <p v-if="log.properties.old && safeDiff(log.properties.attributes,log.properties.old) && safeDiff(log.properties.attributes,log.properties.old).amount" class="log-data">
                    <template v-if="log.properties.attributes.type === 'deposit'">
                        {{__('Change deposit amount')}}  {{__('From')}} <span class="old">{{formatAmountForDeposit(log.properties.old.amount)}} </span> {{__('To')}} <span class="new">{{formatAmountForDeposit(safeDiff(log.properties.attributes,log.properties.old).amount)}} </span> {{__(currency)}}
                    </template>
                    <template v-else>
                        {{__('Change amount')}}  {{__('From')}} <span class="old">{{formatAmountForDeposit(log.properties.old.amount)}} </span> {{__('To')}} <span class="new">{{formatAmountForDeposit(safeDiff(log.properties.attributes,log.properties.old).amount)}} </span> {{__(currency)}}
                    </template>
                  </p>

                  <p v-if="log.properties.old && safeDiff(log.properties.attributes,log.properties.old) && safeDiff(log.properties.attributes,log.properties.old).total_price" class="log-data">
                       {{__('Change total price for reservation')}}  {{__('From')}} <span class="old">{{formatAmount(log.properties.old.total_price)}} </span> {{__('To')}} <span class="new">{{formatAmount(safeDiff(log.properties.attributes,log.properties.old).total_price)}} </span> {{__(currency)}}
                  </p>

                  <p v-if="log.properties.old && safeDiff(log.properties.attributes,log.properties.old) && safeDiff(log.properties.attributes,log.properties.old).checked_in" class="log-data">
                       {{__('Reservation Checked In At')}}   <span class="new">{{safeDiff(log.properties.attributes,log.properties.old).checked_in | formatDate24}} </span>
                  </p>

                  <p v-if="log.properties.old && safeDiff(log.properties.attributes,log.properties.old) && safeDiff(log.properties.attributes,log.properties.old).checked_out" class="log-data">
                       {{__('Reservation Checked Out At')}}   <span class="new">{{safeDiff(log.properties.attributes,log.properties.old).checked_out | formatDate24}} </span>
                  </p>

                  <p v-if="log.properties.old && safeDiff(log.properties.attributes,log.properties.old) && safeDiff(log.properties.attributes,log.properties.old).status == 'canceled'" class="log-data">
                       {{__('Reservation status changed')}} {{__('From')}} <span class="old">{{__(log.properties.old.status)}}</span> {{__('To')}} {{__('canceled')}}
                  </p>

                  <p v-if="log.properties.old && safeDiff(log.properties.attributes,log.properties.old) && safeDiff(log.properties.attributes,log.properties.old).date_in && !safeDiff(log.properties.attributes , log.properties.old).status" class="log-data">
                       {{__('Change date in for reservation')}}  {{__('From')}} <span class="old">{{ log.properties.old.date_in  | formatDateSpecial}}</span> {{__('To')}} <span class="new"> {{safeDiff(log.properties.attributes,log.properties.old).date_in | formatDateSpecial}} </span>
                  </p>

                  <p v-if="log.properties.old && safeDiff(log.properties.attributes,log.properties.old) && safeDiff(log.properties.attributes,log.properties.old).date_out && !safeDiff(log.properties.attributes , log.properties.old).status" class="log-data">
                       {{__('Change date out for reservation')}}  {{__('From')}} <span class="old">{{ log.properties.old.date_out  | formatDateSpecial}}</span> {{__('To')}} <span class="new"> {{safeDiff(log.properties.attributes,log.properties.old).date_out | formatDateSpecial}} </span>
                  </p>

                  <p v-if="log.properties.old && log.properties.old.checked_out && !log.properties.attributes.checked_out" class="log-data">
                      {{__('Closed Contract Opened')}}
                  </p>

                  <template v-if="log.properties.old && log.log_name && log.log_name == 'change_reservation_customer'">
                    <p>{{__('Customer name changed')}}
                            {{__('From')}} <span class="old">{{log.properties.old['customer.name']}}</span>
                            {{__('To')}} <span class="new"> {{log.properties.attributes['customer.name']}} </span>
                    </p>
                    <p>{{__('Customer phone changed')}}
                            {{__('From')}} <span class="old">{{log.properties.old['customer.phone']}}</span>
                            {{__('To')}} <span class="new"> {{log.properties.attributes['customer.phone']}} </span>
                    </p>

                    <p v-if="safeDiff(log.properties.old , log.properties.attributes)['customer.email']" >{{__('Customer email changed')}}
                            {{__('From')}} <span class="old">{{ log.properties.old['customer.email'] ?  log.properties.old['customer.email'] : '-' }}</span>
                            {{__('To')}} <span class="new"> {{log.properties.attributes['customer.email'] ? log.properties.attributes['customer.email'] : '-'}} </span>
                    </p>

                    <p v-if="safeDiff(log.properties.old , log.properties.attributes)['customer.id_number']" >{{__('Customer id number changed')}}
                            {{__('From')}} <span class="old">{{ log.properties.old['customer.id_number'] ?  log.properties.old['customer.id_number'] : '-' }}</span>
                            {{__('To')}} <span class="new"> {{log.properties.attributes['customer.id_number'] ? log.properties.attributes['customer.id_number'] : '-'}} </span>
                    </p>

                    <p v-if="safeDiff(log.properties.old , log.properties.attributes)['customer.customer_type_string']">{{__('Customer type changed')}}
                            {{__('From')}} <span class="old">{{ log.properties.old['customer.customer_type_string'] ?  log.properties.old['customer.customer_type_string'] : '-' }}</span>
                            {{__('To')}} <span class="new"> {{log.properties.attributes['customer.customer_type_string'] ? log.properties.attributes['customer.customer_type_string'] : '-'}} </span>
                    </p>

                    <p v-if="safeDiff(log.properties.old , log.properties.attributes)['customer.id_type_string']">{{__('Customer id type changed')}}
                            {{__('From')}} <span class="old">{{ log.properties.old['customer.id_type_string'] ?  log.properties.old['customer.id_type_string'] : '-' }}</span>
                            {{__('To')}} <span class="new"> {{log.properties.attributes['customer.id_type_string'] ? log.properties.attributes['customer.id_type_string'] : '-'}} </span>
                    </p>

                    <p v-if="safeDiff(log.properties.old , log.properties.attributes)['customer.nationality_string']">{{__('Customer nationality changed')}}
                            {{__('From')}} <span class="old">{{ log.properties.old['customer.nationality_string'] ?  log.properties.old['customer.nationality_string'] : '-' }}</span>
                            {{__('To')}} <span class="new"> {{log.properties.attributes['customer.nationality_string'] ? log.properties.attributes['customer.nationality_string'] : '-'}} </span>
                    </p>

                  </template>

                </div>

              </td>
              <td>{{log.causer ? log.causer.name : 'النظام' }}</td>
              <td>{{log.created_at}}</td>
            </tr>
          </tbody>
        </table>

        </div>

        <div class="no-messages" v-else>لا يوجد سجل احداث</div>
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
      <div class="Results_area" v-if="logs.length">
          <p>{{__('Results')}}  : {{__('From')}} ( {{paginator.from}} ) - {{__('To')}}  ( {{paginator.to}} )</p>
          <p>{{__('Count')}}  : {{paginator.totalResults}}</p>
      </div><!-- Results_area -->
    </sweet-modal>

  </div>
</template>

<script>
    import Loading from 'vue-loading-overlay';
    import 'vue-loading-overlay/dist/vue-loading.css';
    import moment from 'moment'
    export default {
        name: "ReservationLogs",
        props: ['reservation'],
        components : {
            Loading
        },
        data: () => {
            return {
                is_integration_shms: false,
                loading: true,
                resendLoading: false,
                shomos_logs: [],
                team_id : Nova.config.user.current_team_id,
                locale : Nova.config.local,
                logs : [],
                paginator : {},
                selectedPage : 1,
                reservationLogsLoading : false,
                last_element: null,
                beginLoader : false,
                currency :Nova.app.currentTeam.currency,

            }
        },
        computed: {
            safeDiff() {
                return (a, b) => {
                    try {
                        if (!a || !b || !this.hasValidData(a) || !this.hasValidData(b)) {
                            return {};
                        }
                        return this.diff(a, b);
                    } catch (e) {
                        console.error('Safe diff error:', e);
                        return {};
                    }
                };
            }
        },
        methods: {

            getShomosLogs(){
                if(Spark.state.currentTeam.integration_shms){
                  // this.beginLoader = true;
                  // this.loading = true;
                    this.shomos_logs = [];
                    axios
                        .get('/api/shomoos/reservation/'+ this.reservation.id + '/' + Spark.state.currentTeam.id)
                        .then(response => {
                            if (response.data.length == 0) {
                                return;
                            }
                            this.shomos_logs = response.data;
                        }).catch(err => {
                              this.loading = false;
                              this.beginLoader = false;
                        })
                }

            },
            shomosResend(log){
                this.resendLoading = true;
                axios
                    .post('/api/shomoos/resend/'+ this.reservation.id, {
                      log: log
                    })
                    .then(response => {
                      this.getShomosLogs();
                      this.resendLoading = false;
                    }).catch(err => {
                })
            },
            openLogsModal(){
                this.selectedPage = 1;
                this.getReservationLogs();
                 this.$refs.logsModal.open();

            },
           getReservationLogs(){

            let config = {
                headers: {
                    "x-team" : this.team_id ,
                    "x-localization" : this.locale,
                },
                params : {
                  'id' : this.reservation.id,
                  'page' : this.selectedPage,
                  'per_page' : 10 ,
                }
            }
            this.reservationLogsLoading = true;
            axios.get(window.FANDAQAH_API_URL + `/reservations/logs` , config )
              .then( response => {
                this.logs = response.data.data;
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
                this.reservationLogsLoading = false;


              });
           },
          getCurrentPage(page){
              this.selectedPage = page;
              this.getReservationLogs();
          },
            hasValidData(obj) {
                if (!obj || typeof obj !== 'object') return false;
                return Object.values(obj).some(val => val !== null && val !== undefined);
            },

            diff(a, b) {
                // More thorough checks
                if (!a || !b || typeof a !== 'object' || typeof b !== 'object') {
                    return {};
                }

                // Handle cases where object contains only null values
                const hasNonNullValues = (obj) => {
                    return Object.values(obj).some(val => val !== null && val !== undefined);
                };

                if (!hasNonNullValues(a) || !hasNonNullValues(b)) {
                    return {};
                }

                var r = {};
                var self = this;

                try {
                    _.each(a, function(v, k) {
                        // Skip if values are equal
                        if (b[k] === v) return;

                        // Handle nested objects
                        if (_.isObject(v) && _.isObject(b[k]) && v !== null && b[k] !== null) {
                            try {
                                var nestedDiff = self.diff(v, b[k]);
                                if (nestedDiff && Object.keys(nestedDiff).length > 0) {
                                    r[k] = nestedDiff;
                                }
                            } catch (e) {
                                // If nested diff fails, just use the new value
                                r[k] = v;
                            }
                        } else if (b[k] !== v) {
                            r[k] = v;
                        }
                    });
                } catch (e) {
                    console.error('Error in diff method:', e);
                    return {};
                }

                return r;
            },
             formatter: (value, amount) => {
                if (value === null || value === undefined) return '0';
                const split = value.toString().split('.');
                if (split.length > 1) {
                    split[split.length-1] = split[split.length-1].substring(0, amount);
                }
                return amount > 0 ? split.join('.') : split[0];
            },
            formatAmount(amount) {
                if (!amount && amount !== 0) return '0';
                // Don't divide by 100 - the amount is already in the correct format
                return parseFloat(amount).toFixed(2);
            },
            formatAmountForDeposit(amount) {
                if (!amount && amount !== 0) return '0';
                // For deposits, divide by 100 as they are stored in cents
                return (amount / 100).toFixed(2);
            },
        },

        mounted(){
            // if(Spark.state.currentTeam.integration_shms){
            //     this.getShomosLogs();
            // }
            this.is_integration_shms = Spark.state.currentTeam.integration_shms;
        },

    }
</script>

<style lang="scss">
    .shomoos_logs_button {
        background: #4099de;
        border-radius: 5px;
        border: 1px solid #4099de;
        min-width: 100px;
        height: 35px;
        line-height: 35px;
        font-size: 15px;
        margin: 0 0 0 10px;
        padding: 0 15px;
        color: #ffffff;
        cursor: pointer;
        -webkit-transition: all 0.2s ease-in-out;
        transition: all 0.2s ease-in-out;
        &:hover {
              background: #0071C9;
              border-color: #0071C9;
        }
    }
    button.main_button {
      margin: 0 0 0 10px;
      height: 35px;
      font-size: 15px;
      color: #fff;
      background: #4099de;
      border-radius: 4px;
      padding: 0 15px;
      min-width: 100px;
      -webkit-transition: all 0.2s ease-in-out;
      -moz-transition: all 0.2s ease-in-out;
      -o-transition: all 0.2s ease-in-out;
      transition: all 0.2s ease-in-out;
      [dir="ltr"] & {
        margin: 0 10px 0 0;
      } /* ltr */
      @media (min-width: 320px) and (max-width: 480px) {
        width: 100%;
        margin: 5px auto;
      } /* Mobile */
      &:hover {
        background: #0071C9;
      } /* hover */
      &.cancel {
        background: #e74444;
        &:hover {
          background: #dd3a3a;
        } /* hover */
      } /* cancel */
    } /* main_button */
    .reservation_logs_block {
      width: auto;
      min-width: auto;
      max-width: none;
      background: #ffffff;
      border-radius: 5px;
      border: 1px solid #ddd;
      padding: 10px;
      box-shadow: 0 2px 4px 0 rgba(0,0,0,.05);
      .shomoos_list{
        position:relative;
        margin: 10px 0;
        .loading_area {
          top: 0;
          left: 0;
          right: 0;
          position: absolute;
          width: 100%;
          height: 100%;
          display: flex;
          align-items: center;
          background: #ffffff90;
          svg {
            width: 60px;
            height: auto;
            display: block;
            margin: 0 auto;
          } /* svg */
        } /* loading_area */
      }
      ul {
        li {
          display: flex;
          justify-content: space-between;
          align-items: center;
          width: 100%;
          flex-wrap: wrap;
          border: 1px solid #ddd;
          margin: 0 auto 10px;
          border-radius: 5px;
          padding: 10px;
          background: #fdfdfd;
          cursor: pointer;
          span {
            display: block;
            font-size: 15px;
            color: #666666;
            p {
              display: inline-block;
              color: #000000;
            } /* p */
          } /* span */
          time {
            font-size: 13px;
            color: #777777;
          } /* time */

          &.danger {
              background: #f8d7da;
              border-color: #f5c6cb;
              color: #721c24;
          } /* danger */
          &.success {
              background: #d4edda;
              border-color: #c3e6cb;
              color: #155724;
          } /* success */
          &:last-child {margin: 0 auto;}
        } /* li */
      } /* ul */
    } /* reservation_logs_block */
    .reservation_logs_Modal {
      .sweet-content {
        overflow: auto;
        max-height: 500px;
        display: block;
        position: relative;
        scrollbar-width: thin;
        scrollbar-color: #ccc #f5f5f5;
        &::-webkit-scrollbar {width: 6px;}
        &::-webkit-scrollbar-track {background: #f5f5f5;}
        &::-webkit-scrollbar-thumb {background: #ccc;}
        &::-webkit-scrollbar-thumb:window-inactive {background: #f5f5f5;}
      } /* sweet-content */
      table {
        border: 1px solid #ddd;
        width: 100%;
        margin: 0 auto 10px;
        thead {
          tr {
            th {
              background: #4a5568;
              vertical-align: middle;
              text-align: center;
              padding: 5px 10px;
              color: #ffffff;
              font-weight: normal;
              border: 1px solid #5E697C;
            } /* th */
          } /* tr */
        } /* thead */
        tbody {
          tr {
            td {
              background: #fefefe;
              border: 1px solid #ddd;
              padding: 10px;
              vertical-align: middle;
              text-align: center;
              font-size: 15px;
              .attributes {
                b {
                  display: block;
                  font-family: 'Dubai-Medium';
                  font-weight: normal;
                  font-size: 16px;
                  margin: 0 auto 5px;
                  color: #000000;
                      text-align: initial;
                } /* b */
                p {
                  display: flex;
                  align-items: center;
                  justify-content: flex-start;
                  color: #444;
                  font-size: 15px;
                  margin: 0 auto 5px;
                  flex-wrap: wrap;
                  span {
                    display: block;
                    margin: 0 5px;
                    &.new {color: #28a745;}
                    &.old {color: #d82a3d;}
                  } /* span */
                  &:last-child {margin: 0 auto;}
                } /* p */
              } /* attributes*/
              &:last-child {
                white-space: nowrap;
                direction: ltr;
              } /* last-child */
            } /* td */
          } /* tr */
        } /* tbody */
      } /* table */
      ul.pagination {
        margin: 20px auto 0;
        li {
          width: auto;
          border: none;
          justify-content: center;
          border-radius: initial;
          background: transparent;
        } /* li */
      } /* ul */
      .Results_area {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin: 20px auto 0;
      } /* Results_area */
    } /* reservation_logs_Modal */
</style>
