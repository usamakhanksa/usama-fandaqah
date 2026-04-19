<template>
    <!-- <div> -->
      <div class="item_reservation_button" v-if="!reservation.checked_in">
          <button v-if="!reservation.checked_in" v-permission="'check-in customer'" class="main_button action-btn" @click="openGroupReservationCheckinModal">{{__('Check in Group Reservation')}}</button>
     
      <sweet-modal 
          :enable-mobile-fullscreen="false" 
          :pulse-on-block="false"  
          width="70%" 
          :title="__('Check in Group Reservation')" 
          overlay-theme="dark" 
          ref="groupReservationCheckinRef" 
          class="summary_modal"
      >
          <div id="update_prices_div" class="relative">
              <loading
                  :active.sync="isLoading"
                  :can-cancel="false"
              />
              <div class="title">
                  {{__('Available Reservations To Make Checkin')}}
              </div>

            
  
              <div class="content_page relative">
                  <div class="overflow-hidden overflow-x-auto relative">
                      <div class="group_reservations_tbl rounded overflow-hidden">
                        <div class="input-group p-1">
                            <label class="text-90">{{__('Check-in time')}} </label>
                            <input type="time" v-model="time"
                                    :disabled="!canEditCheckinCheckoutTime"
                                class="appearance-none border rounded w-full py-2 px-3 border-gray-300 leading-tight focus:outline-none focus:shadow-outline"
                                :placeholder="__('Name')"/>
                        </div>
                          <table class="table w-full"
                                  cellpadding="0"
                                  cellspacing="0"
                          >
  
                              <thead>
                              <tr>
                                  <th>{{__('Statement')}}</th>
                                  <th @click="checkAllReservations">{{ __('Check All') }} <input v-if="reservations.length" type="checkbox" v-model="checkAll"> </th>
                              </tr>
                              </thead>
                              <tbody>
                                    <template v-if="reservations.length">
                                    <tr v-for="(reservation,key) in reservations" :key="key">
                                        <td>
                                            <span>
                                                {{ __('Reservation with number') }} {{ reservation.number }}
                                                -
                                                {{reservation.unit.name[locale]}}
                                                -
                                                {{reservation.unit.unit_number}}
                                                -
                                                {{ __('From',[],'ar') }}
                                                {{ reservation.date_in | formatDateSpecial}}
                                                -
                                                {{ __('To',[],'ar') }}
                                                {{ reservation.date_out | formatDateSpecial}}
                                            </span>
                                        </td>
                                        <td>
                                            <input type="checkbox" :value="reservation.id" v-model="checked_reservations">
                                        </td>
                                     
                                    </tr>
                                    </template>
                                    <template v-else>
                                        <tr>
                                            <td colspan="2">{{ __('No Reservations Found') }}</td>
                                        </tr>
                                    </template>
                              </tbody>
                          </table>
                      </div>
                      <button  class="shadow mb-4  btn btn-block btn-primary mt-2" @click="doCheckin">{{__('Check In')}}</button>
                  </div>
              </div>
          </div>
      </sweet-modal>
  </div>
    <!-- </div>   -->
   
  </template>
  
  <script>
      import Loading from 'vue-loading-overlay';
      import 'vue-loading-overlay/dist/vue-loading.css';
      import momenttimezone from 'moment-timezone'
      export default {
          name : 'group-reservation-multiple-checkin',
          props: ["reservation"],
          components: {
              Loading
          },
          data() {
              return {
                  isLoading : false,
                  locale: Nova.config.local,
                  canEditCheckinCheckoutTime : true,
                  time: momenttimezone().tz("Asia/Riyadh").format('HH:mm'),
                  total_balance : null,
                  reservations : [],
                  checkAll : false,
                  checked_reservations : []
              }
          },
          methods: {
         
           async openGroupReservationCheckinModal() {
                    this.checkAll = false;
                    this.isLoading = true;
                    this.checked_reservations = [];
                    this.time = momenttimezone().tz("Asia/Riyadh").format('HH:mm');
                    const fetchBalance = await axios.get(`/nova-vendor/calender/reservation-balance?id=${this.reservation.id}`)
                    this.total_balance = fetchBalance.data.total_balance;

                    if(this.total_balance < 0 && !this.hasPermissionToCheckinDebtorCustomer && fetchBalance.data.reservation_type == 'group'){
                        this.isLoading = false;
                        this.$toasted.show(this.__('Can not check-in , please fulfill group reservation balance'), {type: 'error'});
                        return;
                
                    }  
                  this.getQualifiedForCheckinReservations();  
                  this.$refs.groupReservationCheckinRef.open()
            },
            getQualifiedForCheckinReservations(){
                this.isLoading = true;
                axios.get(`/nova-vendor/calender/get-qualified-for-checkin-reservations?main_id=${this.reservation.attachable_id ? this.reservation.attachable_id  : this.reservation.id}&time=${this.time}`)
                .then( response => {
                    this.reservations = response.data;
                    this.isLoading = false;
                })
            },
            doCheckin(){
                this.isLoading = true;
                if(!this.checked_reservations.length){
                    this.isLoading = false;
                    this.$toasted.show(this.__('Can not check-in , please select at least one reservation'), {type: 'error'});
                    return;
                }

                axios.post(`/nova-vendor/calender/doCheckinGroupReservations`, {
                    checked_reservations : this.checked_reservations,
                    time : this.time
                })
                .then(response => {
                    this.isLoading = false;
                    if(response.data.success){
                        this.$toasted.show(this.__('Checkin for selected reservations went successfully'), {type: 'success'});
                        this.$refs.groupReservationCheckinRef.close();
                        Nova.$emit('close-toggle-group-buttons');
                        this.checked_reservations = [];
                        Nova.$emit('refresh-reservation');
                    }else{
                        this.$toasted.show(this.__('Something went wrong!'), {type: 'false'});
                    }
                   
                })

            },
            checkAllReservations(){
                this.checkAll = !this.checkAll;
                if(!this.checkAll){
                    this.checked_reservations = [];
                }
                const self = this;
                this.reservations.forEach(reservation => {
                    if(self.checkAll){
                        this.checked_reservations.push(reservation.id);
                    }
                });
                
            }
           
          },
          mounted(){
            this.hasPermissionToCheckinDebtorCustomer = Nova.app.$hasPermission('checkin debtor customer');
            this.canEditCheckinCheckoutTime = Nova.app.$hasPermission('edit checkin and checkout time');
          }
  
      }
  </script>
  
  <style lang="scss" scoped>
   .select_alert {
                  text-align: center;
                  background: #fffaf0;
                  border: 1px solid #fbd38d;
                  color: #b7791f;
                  font-size: 15px;
                  border-radius: 5px;
                  padding: 10px 43px 10px 10px;
                  margin: 0 auto 15px;
                  position: relative;
                  width: 100%;
                  [dir="ltr"] & {
                      padding: 10px 10px 10px 43px;
                  } /* ltr */
                  &::after {
                      content: "";
                      position: absolute;
                      right: 0;
                      top: 0;
                      display: block;
                      background-image: url("data:image/svg+xml,%3C%3Fxml version='1.0'%3F%3E%3Csvg xmlns='http://www.w3.org/2000/svg' id='Capa_1' enable-background='new 0 0 524.235 524.235' height='512px' viewBox='0 0 524.235 524.235' width='512px' class=''%3E%3Cg%3E%3Cpath d='m262.118 0c-144.53 0-262.118 117.588-262.118 262.118s117.588 262.118 262.118 262.118 262.118-117.588 262.118-262.118-117.589-262.118-262.118-262.118zm17.05 417.639c-12.453 2.076-37.232 7.261-49.815 8.303-10.651.882-20.702-5.215-26.829-13.967-6.143-8.751-7.615-19.95-3.968-29.997l49.547-136.242h-51.515c-.044-28.389 21.25-49.263 48.485-57.274 12.997-3.824 37.212-9.057 49.809-8.255 7.547.48 20.702 5.215 26.829 13.967 6.143 8.751 7.615 19.95 3.968 29.997l-49.547 136.242h51.499c.01 28.356-20.49 52.564-48.463 57.226zm15.714-253.815c-18.096 0-32.765-14.671-32.765-32.765 0-18.096 14.669-32.765 32.765-32.765s32.765 14.669 32.765 32.765c0 18.095-14.668 32.765-32.765 32.765z' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%23B7791F'/%3E%3C/g%3E%3C/svg%3E%0A");
                      height: 43px;
                      width: 43px;
                      background-repeat: no-repeat;
                      background-size: 22px;
                      background-position: center center;
                      [dir="ltr"] & {
                          left: 0;
                          right: auto;
                      } /* ltr */
                  } /* after */
              } /* select_alert */
  input[type="number"] {
      min-width: 90px;
      border: 1px solid #ddd !important;
      background: #fafafa;
      text-align: center;
      height: 40px;
      font-size: 18px;
      width: 100px;
      max-width: 100%;
      color: #000;
      &:disabled{
          background: #d6d6d6 !important;
      }
  } /* number */
  .group_reservations_tbl {
          overflow: auto;
          width: 100%;
          padding: 0 0 15px 0;
          background: #fff;
      }
      .group_reservations_tbl .table {border: 1px solid #e2e8f0;}
      .group_reservations_tbl .table thead tr th {
          padding: 10px 5px;
          line-height: 20px;
          font-weight: normal;
          font-size: 15px;
          border: 1px solid #5E697C;
          vertical-align: middle;
          text-align: center;
          color: #ffffff;
          background: #4a5568;
  
           label {
              display: flex;
              align-items: center;
              justify-content: center;
              position: relative;
              overflow: hidden;
              cursor: pointer;
  
              input {
                  width: 100%;
                  height: 100%;
                  position: absolute;
                  right: 0;
                  top: 0;
                  opacity: 0;
                  z-index: 9;
                  &:checked ~ {
                      .checkmark {
                          &::before{
                              display: block;
                          }
                      }
                  }
              }
  
              .checkmark {
  
                  position: relative;
                  width: 18px;
                  height: 18px;
                  border: 1px solid #ced4dc;
                  background-color: #fff;
                  margin: 0 0 0 10px;
                  border-radius: .25rem;
  
                  &::before{
                      display: none;
                      content: "";
                      background-color: #4599dd;
                      background-position: center;
                      background-size: 13px 13px;
                      background-repeat: no-repeat;
                      position: absolute;
                      right: -1px;
                      top: -1px;
                      border: 1px solid #4599dd;
                      width: 18px;
                      height: 18px;
                      border-radius: .25rem;
                      background-image: url("data:image/svg+xml,%3Csvg fill='none' height='24' stroke='%23fff' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' viewBox='0 0 24 24' width='24' xmlns='http://www.w3.org/2000/svg'%3E%3Cpolyline points='20 6 9 17 4 12'/%3E%3C/svg%3E");
                  }
              }
          }
      }
      .group_reservations_tbl .table tbody tr {background: #fff;}
      .group_reservations_tbl .table tbody tr td {
          text-align: center;
          padding: 10px 5px;
          vertical-align: middle;
          line-height: 20px;
          font-size: 15px;
          border: 1px solid #ced4dc;
          color: #000000;
          font-weight: normal;
          background: #fafafa;
  
          &.linked_reservations{
              display: flex;
              align-items: center;
              justify-content: flex-start;
              &.clickable{
                  cursor: pointer;
  
                  span{
                      font-weight: bold;
                      font-size: 15px;
                      color: #4599dd;
                      padding: 0;
                  }
  
                  svg{
                      margin: 0 0 0 5px;
                      path {
                          fill: #4599dd;
                      }
                  }
              }
  
              span{
                  font-weight: bold;
                  font-size: 15px;
                  color: #aaa;
                  padding: 0;
              }
  
              svg{
                  margin: 0 0 0 5px;
                  path {
                      fill: #aaa;
                  }
              }
  
          }
  
          label {
              position: relative;
              overflow: hidden;
  
              input {
                  width: 100%;
                  height: 100%;
                  position: absolute;
                  right: 0;
                  top: 0;
                  opacity: 0;
  
                  z-index: 9;
                  &:checked ~ {
                      .checkmark {
                          &::before{
                              display: block;
                          }
                      }
                  }
  
                  &:disabled ~ {
                      .checkmark {
                          &::before{
                              background-color: #aaa;
                              border-color: #aaa;
                              cursor: not-allowed;
                          }
                      }
                  }
              }
  
              .checkmark {
                  cursor: pointer;
                  position: relative;
                  width: 22px;
                  height: 22px;
                  border: 1px solid #ced4dc;
                  background-color: #fff;
                  margin: 0 auto;
                  border-radius: .25rem;
  
                  &::before{
                      display: none;
                      content: "";
                      background-color: #4599dd;
                      background-position: center;
                      background-size: 15px 15px;
                      background-repeat: no-repeat;
                      position: absolute;
                      right: -1px;
                      top: -1px;
                      border: 1px solid #4599dd;
                      width: 22px;
                      height: 22px;
                      border-radius: .25rem;
                      background-image: url("data:image/svg+xml,%3Csvg fill='none' height='24' stroke='%23fff' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' viewBox='0 0 24 24' width='24' xmlns='http://www.w3.org/2000/svg'%3E%3Cpolyline points='20 6 9 17 4 12'/%3E%3C/svg%3E");
                  }
              }
          }
           svg{
               cursor: pointer;
               margin: 0 auto;
              width:30px;
              height:30px;
              // path {
              //     fill: #495568;
              // }
          }
      }
      .group_reservations_tbl .table tbody tr td.td-fit {
        border-bottom: none;
        border-right: dimgray;
        display: flex;
        align-items: center;
        justify-content: center;
      }
      .group_reservations_tbl .table tbody tr td.td-fit a, .group_reservations_tbl .table tbody tr td.td-fit button {color: #b3b9bf;}
      .group_reservations_tbl .table tbody tr td.td-fit svg:hover {color: #3d92d4;}
      .group_reservations_tbl .table tbody tr td .font-bold {font-weight: normal;}
      .group_reservations_tbl .table tbody tr td a {color: #000000;}
      .group_reservations_tbl .table tbody tr td a:hover, .group_reservations_tbl .table tbody tr td button:hover {color: #3d92d4;}
      /* Portrait phones and smaller */
      @media (min-width: 320px) and (max-width: 480px) {
          .group_reservations_tbl {
              overflow: scroll;
              padding: 0 0 15px 0;
          }
      }
  
      /* Smart phones and Tablets */
      @media (min-width: 481px) and (max-width: 767px) {
          .group_reservations_tbl {
              overflow: scroll;
              padding: 0 0 15px 0;
          }
      }
  
      /* Small Screens */
      @media (min-width: 768px) and (max-width: 991px) {
          .group_reservations_tbl {
              overflow: scroll;
              padding: 0 0 15px 0;
          }
      }
  
      /* Medium Screens */
      @media (min-width: 992px) and (max-width: 1000px) {
          .group_reservations_tbl {
              overflow: scroll;
              padding: 0 0 15px 0;
          }
      }
  .summary_modal {
      .sweet-modal {
          @media (min-width: 768px) and (max-width: 991px) {
              width: 95% !important;
          } /* @media */
      } /* sweet-modal */
      .embed_area {
          max-height: 500px;
          height: 100%;
          overflow-y: auto;
          display: block !important;
          scrollbar-width: thin;
          scrollbar-color: #ccc #f5f5f5;
          &::-webkit-scrollbar {width: 6px;}
          &::-webkit-scrollbar-track {background: #f5f5f5;}
          &::-webkit-scrollbar-thumb {background: #ccc;}
          &::-webkit-scrollbar-thumb:window-inactive {background: #f5f5f5;}
          @media (min-width: 320px) and (max-width: 480px) {
              display: none !important;
          } /* @media */
          iframe {
              width: 100%;
              height: 100%;
              min-height: 500px;
          } /* iframe */
      } /* embed_area */
  } /* contract_modal */
  
  #update_prices_div {
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
  
              p{
                  font-size: 15px;
              }
          } /* title */
          .content_page {
              background: #fff;
              padding: 10px;
              .options {
                  margin: 15px 0;
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
                      outline: none !important;
                      } /* select */
              }
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
                      &.print_button {
                          background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='19.339' height='19.339' viewBox='0 0 19.339 19.339'%3E%3Cpath d='M17.471,17.471v1.934a1.934,1.934,0,0,1-1.934,1.934H7.8a1.934,1.934,0,0,1-1.934-1.934V17.471H3.934A1.934,1.934,0,0,1,2,15.537v-5.8A1.94,1.94,0,0,1,3.934,7.8H5.868V3.934A1.94,1.94,0,0,1,7.8,2h7.735a1.934,1.934,0,0,1,1.934,1.934V7.8H19.4a1.934,1.934,0,0,1,1.934,1.934v5.8A1.934,1.934,0,0,1,19.4,17.471Zm0-1.934H19.4v-5.8H3.934v5.8H5.868V13.6A1.94,1.94,0,0,1,7.8,11.669h7.735A1.934,1.934,0,0,1,17.471,13.6ZM15.537,7.8V3.934H7.8V7.8ZM7.8,13.6v5.8h7.735V13.6Z' transform='translate(-2 -2)' fill='%23333b45'/%3E%3C/svg%3E");
                      } /* print_button */
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
              } /* table_area */
          } /* content_page */
      } /* update_prices_div */
  </style>
  