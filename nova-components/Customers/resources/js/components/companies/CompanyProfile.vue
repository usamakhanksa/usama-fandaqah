<template>
  <div>

    <div class="flex w-full mb-4">
        <nav>
            <ul class="breadcrumbs">
                <li class="breadcrumbs__item">
                    <router-link :to="'/'">{{ __('Home') }}</router-link>
                </li>
                <li class="breadcrumbs__item">
                    <router-link :to="'/new/customers'">{{ __('Customers') }}</router-link>
                </li>
                <li class="breadcrumbs__item">
                    <router-link :to="'/companies'">{{ __('Companies Management') }}</router-link>
                </li>
                <li class="breadcrumbs__item" v-if="company">
                    <router-link :to="'#'">{{ company.name }}</router-link>
                </li>
            </ul>
        </nav>
    </div>


    <div class="mb-8 relative">
        <loading :active="isLoading" :loader="'spinner'" :color="'#7e7d7f'" :opacity="0.7"  :is-full-page="false"></loading>
            <div class="flex items-center mb-3">

                <h4 class="text-90 font-normal text-2xl flex-no-shrink">{{__('Company Profile')}}</h4>

                <div class="ml-3 w-full flex items-center">
                    <div class="flex w-full justify-end items-center"></div>
                    <edit-company :company="company" from='profile'/>
                    <!-- <button class="btn btn-default btn-icon bg-primary" :title="__('Edit')" @click="openEditCompany">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" aria-labelledby="edit" role="presentation" class="fill-current text-white" style="margin-top: -2px; margin-left: 3px;">
                            <path d="M4.3 10.3l10-10a1 1 0 0 1 1.4 0l4 4a1 1 0 0 1 0 1.4l-10 10a1 1 0 0 1-.7.3H5a1 1 0 0 1-1-1v-4a1 1 0 0 1 .3-.7zM6 14h2.59l9-9L15 2.41l-9 9V14zm10-2a1 1 0 0 1 2 0v6a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V4c0-1.1.9-2 2-2h6a1 1 0 1 1 0 2H2v14h14v-6z"></path>
                        </svg>
                    </button> -->
                </div>
            </div>
            <div class="card mb-6 py-3 px-6">
                <div class="flex border-b border-40">
                    <div class="w-1/4 py-4">
                        <h4 class="font-normal text-80">{{__('Company Name')}}</h4>
                    </div>
                    <div class="w-3/4 py-4">
                    <p class="text-90" v-if="company">{{company.name}}</p>
                    </div>
                </div>
                <div class="flex border-b border-40">
                    <div class="w-1/4 py-4">
                        <h4 class="font-normal text-80">{{__('Company Phone')}}</h4>
                    </div>
                    <div class="w-3/4 py-4">
                        <p class="text-90 phone-number-dir" v-if="company">{{company.phone}}</p>
                    </div>
                </div>
                <div class="flex border-b border-40">
                    <div class="w-1/4 py-4">
                        <h4 class="font-normal text-80" >{{__('City')}}</h4>
                    </div>
                    <div class="w-3/4 py-4">
                        <p class="text-90" v-if="company">{{company.city ? company.city : '-' }}</p>
                    </div>
                </div>
                <div class="flex border-b border-40">
                    <div class="w-1/4 py-4">
                        <h4 class="font-normal text-80">{{__('Company Address')}}</h4>
                    </div>
                    <div class="w-3/4 py-4">
                        <p class="text-90 phone-number-dir" v-if="company">{{company.address ? company.address : '-' }}</p>
                    </div>
                </div>
                <div class="flex border-b border-40">
                    <div class="w-1/4 py-4">
                        <h4 class="font-normal text-80">{{__('Person In Charge')}}</h4>
                    </div>
                    <div class="w-3/4 py-4">
                        <p class="text-90" v-if="company">{{company.person_incharge_name ? company.person_incharge_name : '-' }}</p>
                    </div>
                </div>
                <div class="flex border-b border-40">
                    <div class="w-1/4 py-4">
                        <h4 class="font-normal text-80">{{__('Person In Charge Phone')}}</h4>
                    </div>
                    <div class="w-3/4 py-4">
                        <p class="text-90 phone-number-dir" v-if="company">{{company.person_incharge_phone ? company.person_incharge_phone : '-' }}</p>
                    </div>
                </div>
                <div class="flex border-b border-40">
                    <div class="w-1/4 py-4">
                        <h4 class="font-normal text-80">{{__('Company Email')}}</h4>
                    </div>
                    <div class="w-3/4 py-4">
                        <p class="text-90" v-if="company">{{company.email ? company.email : '-' }}</p>
                    </div>
                </div>
                <div class="flex border-b border-40">
                    <div class="w-1/4 py-4">
                        <h4 class="font-normal text-80">{{__('Tax number')}}</h4>
                    </div>
                    <div class="w-3/4 py-4">
                        <p class="text-90" v-if="company">{{company.tax_number ? company.tax_number : '-' }}</p>
                    </div>
                </div>
            </div>
    </div>


    <div id="reservations_customer" class="relative">


        <div class="title">{{__('Reservations')}}</div>
        <div class="content_page">


             <div class="overflow-hidden overflow-x-auto relative">
                <loading :active.sync="isLoadingReservations" :can-cancel="true" :loader="'spinner'" :color="'#7e7d7f'" :opacity="0.8" :is-full-page="false"></loading>
                <div class="main_reservations_table rounded overflow-hidden">
                    <table class="table w-full"
                            cellpadding="0"
                            cellspacing="0"
                    >
                        <thead>
                        <tr>
                            <th colspan="4"></th>
                            <th colspan="6">{{ __('Reservation') }}</th>
                            <th colspan="3">{{ __('The Due') }}</th>

                            <th :colspan="is_integration_shms ? '4' : '3'">{{ __('Finance') }}</th>
                            <th></th>
                        </tr>
                        </thead>
                        <thead>
                        <tr>
                            <th>{{__('Reservation Number')}}</th>
                            <th>{{ __('The Unit') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Reservation Status') }}</th>
                            <th>{{ __('Rent Type') }}</th>
                            <th>{{ __('Date In') }}</th>
                            <th>{{ __('Date Out') }}</th>
                            <th>{{ __('Nights Count') }}</th>
                            <th>{{ __('Leasing') }}</th>
                            <th>{{ __('Services') }}</th>
                            <th>{{ __('Amount') }}</th>
                            <th>{{ __('Taxes') }}</th>
                            <th>{{ __('The Total') }}</th>
                            <th>{{ __('Paid') }}</th>
                            <th>{{ __('Creditor') }}</th>
                            <th>{{ __('Debtor') }}</th>
                            <th v-if="is_integration_shms">{{ __('shomos status') }}</th>
                            <th>{{__('Actions')}}</th>
                        </tr>
                        </thead>
                        <tbody>
                            <template v-if="reservations.length">

                                <tr v-for="(reservation,i) in reservations" :key="i">
                                    <td><router-link class="text-primary" :to="{name: 'reservation', params: {id: reservation.id}}">#{{reservation.reservation_number}}</router-link></td>
                                    <td v-if="reservation.unit_id"><router-link class="text-primary" :to="{path: `/resources/units/${reservation.unit_id}`}">{{reservation.unit_number}} - {{reservation.unit_name}} </router-link></td>
                                    <td v-else>-</td>
                                    <td>
                                        <span v-if="reservation.status === 'confirmed'" class="indicators enabled">{{__('confirmed')}}</span>
                                        <span v-if="reservation.status === 'canceled'" class="indicators not_enabled">{{__('canceled')}}</span>
                                        <span v-if="reservation.status === 'timeout'" class="indicators timeout">{{__('timeout')}}</span>
                                        <span v-if="reservation.status === 'awaiting-payment'" class="indicators awaiting_payment">{{__('awaiting-payment')}}</span>

                                    </td>
                                    <td>
                                        <div v-if="!reservation.checked_in" class="pending">{{__('Pending')}}</div>
                                        <div v-if="reservation.checked_in && !reservation.checked_out" class="checked_in">{{__('Checked in')}}</div>
                                        <div v-if="reservation.checked_in && reservation.checked_out" class="checked_out">{{__('Checked out')}}</div>
                                    </td>
                                    <td>{{reservation.rent_type === 1 ?  __('Daily') : __('Monthly')}}</td>
                                    <td>{{reservation.date_in | formatDateSpecial}}</td>
                                    <td>{{reservation.date_out | formatDateSpecial}}</td>
                                    <td>{{reservation.nights}}</td>
                                    <td>{{parseFloat(reservation.leasing_price).toFixed(2)}}</td>
                                    <td>{{parseFloat(reservation.services_price).toFixed(2)}}</td>
                                    <td>{{parseFloat(reservation.amount).toFixed(2)}}</td>
                                    <td>{{ parseFloat(reservation.taxes).toFixed(2)}}</td>
                                    <td>{{parseFloat(reservation.total).toFixed(2)}}</td>
                                    <td>{{parseFloat(reservation.paid).toFixed(2)}}</td>
                                    <td>
                                        <div v-if="reservation.reservation_type == 'single'">
                                            <div v-if="reservation.balance > 0" class="green">{{parseFloat(reservation.balance).toFixed(2)}}</div>
                                            <div v-else>-</div>
                                        </div>
                                        <div v-else>
                                            <div v-if="reservation.groupReservationBalanceMapper.balance > 0" class="green">{{parseFloat(reservation.groupReservationBalanceMapper.balance).toFixed(2)}}</div>
                                            <div v-else>-</div>
                                        </div>
                                    </td>

                                    <td>
                                        <div v-if="reservation.reservation_type == 'single'">
                                            <div v-if="reservation.balance < 0" class="red">{{parseFloat(Math.abs(reservation.balance)).toFixed(2)}}</div>
                                            <div v-else>-</div>
                                        </div>
                                        <div v-else>
                                            <div v-if="reservation.groupReservationBalanceMapper.balance < 0" class="red">{{parseFloat(Math.abs(reservation.groupReservationBalanceMapper.balance)).toFixed(2)}}</div>
                                            <div v-else>-</div>
                                        </div>

                                    </td>


                                    <td v-if="is_integration_shms">
                                        <span v-if="reservation.shomos_status == true" class="indicators enabled" data-toggle="tooltip" data-placement="top" title="Tooltip on top"></span>
                                        <span v-else class="indicators not_enabled" data-toggle="tooltip" data-placement="top" title="Tooltip on top"></span>
                                    </td>
                                    <td class="td-fit text-right pr-6 flex items-center">
                                        <router-link v-if="reservation.customer_id" :to="{path: `/reservation/${reservation.id}`}" :title="__('View')" class="cursor-pointer text-70 hover:text-primary mx-2">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="18" viewBox="0 0 22 16" aria-labelledby="view" role="presentation" class="fill-current"><path d="M16.56 13.66a8 8 0 0 1-11.32 0L.3 8.7a1 1 0 0 1 0-1.42l4.95-4.95a8 8 0 0 1 11.32 0l4.95 4.95a1 1 0 0 1 0 1.42l-4.95 4.95-.01.01zm-9.9-1.42a6 6 0 0 0 8.48 0L19.38 8l-4.24-4.24a6 6 0 0 0-8.48 0L2.4 8l4.25 4.24h.01zM10.9 12a4 4 0 1 1 0-8 4 4 0 0 1 0 8zm0-2a2 2 0 1 0 0-4 2 2 0 0 0 0 4z"></path></svg>
                                        </router-link>

                                        <router-link v-else :to="{path: `/reservation-noc/${reservation.id}`}" :title="__('View')" class="cursor-pointer text-70 hover:text-primary mx-2">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="18" viewBox="0 0 22 16" aria-labelledby="view" role="presentation" class="fill-current"><path d="M16.56 13.66a8 8 0 0 1-11.32 0L.3 8.7a1 1 0 0 1 0-1.42l4.95-4.95a8 8 0 0 1 11.32 0l4.95 4.95a1 1 0 0 1 0 1.42l-4.95 4.95-.01.01zm-9.9-1.42a6 6 0 0 0 8.48 0L19.38 8l-4.24-4.24a6 6 0 0 0-8.48 0L2.4 8l4.25 4.24h.01zM10.9 12a4 4 0 1 1 0-8 4 4 0 0 1 0 8zm0-2a2 2 0 1 0 0-4 2 2 0 0 0 0 4z"></path></svg>
                                        </router-link>

                                    </td>

                                </tr>
                            </template>
                            <template v-else>
                                <tr>
                                    <td colspan="18">{{__('No Reservations Found')}}</td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

            </div>

             <div class="w-full flex flex-wrap mt-3 justify-center">
                <pagination
                    v-if="paginator.lastPage > 1"
                    :page-count="paginator.lastPage"
                    :page-range="3"
                    :margin-pages="2"
                    :click-handler="getReservations"
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
                />
            </div>
            <div class="Results_area" v-if="reservations.length">
                <p>{{__('Results')}}  : {{__('From')}} ( {{paginator.from}} ) - {{__('To')}}  ( {{paginator.to}} )</p>
                <p>{{__('Total Reservations')}}  : {{paginator.totalResults}}</p>
            </div>


        </div>
    </div>

    <notes v-permission="'view companies notes'" type="company" :typeId="id" />

  </div>
</template>

<script>
import Notes from '../Notes.vue';
import Pagination from '../Pagination';
import EditCompany from './EditCompany'
import Loading from 'vue-loading-overlay';
export default {
  name: 'company-profile',
  components: {
      Loading,
      EditCompany,
      Notes,
      Pagination
  },
  props: ['id'],
  data() {
    return {
      company: null,
      reservations: [],
      paginator: {},
      isLoading: false,
      isLoadingReservations: false,
      locale: Nova.config.local,
    };
  },
  methods: {

    getCompany() {
      this.isLoading = true;
      axios.get(`/nova-vendor/new/customers/companies/${this.id}`)
      .then(response => {
        this.company = response.data;
        this.isLoading = false;
      });
    },
    getReservations(page=1){
        this.isLoadingReservations = true;
        axios.get(`/nova-vendor/new/customers/companies/${this.id}/reservations/list?page=${page}`)
        .then(response => {

             this.reservations = response.data.data;
             this.paginator = {
                currentPage : response.data.current_page ,
                lastPage : response.data.last_page ,
                from : response.data.from,
                to : response.data.to,
                totalResults : response.data.total,
                pathPage : response.data.path + '?page=',
                firstPageUrl : response.data.first,
                lastPageUrl : response.data.last,
                nextPageUrl : response.data.next,
                prevPageUrl : response.data.prev,
          };
          this.isLoadingReservations = false;
        })
    },

  },
  mounted() {
    this.getCompany();
    this.getReservations();
    Nova.$on('company-updated' , () => {
        this.getCompany();
    })
  },
};
</script>

<style lang="scss" >
.phone-number-dir{
    direction: ltr;
    text-align: right;
    [lang="en"] & {
        text-align: left;
    }
}
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

   label#customer-label {
    padding: 0 .5rem;
    border-radius: .25rem;
}

  .text-timeout{
        color: #a07930;
    }

    .text-awaiting {
         --text-opacity: 1;
        color: rgba(66,153,225,var(--text-opacity));
    }
    .green{
        --text-opacity: 1;
        color: rgba(72,187,120,var(--text-opacity));
    }

    .red {
        --text-opacity: 1;
        color: rgba(245,101,101,var(--text-opacity));
    }

    .pending {
        --text-opacity: 1;
        color: rgba(66,153,225,var(--text-opacity));
    }
    .checked_in {
        --text-opacity: 1;
        color: rgba(72,187,120,var(--text-opacity));
    }
    .checked_out {
        --text-opacity: 1;
        color: rgba(245,101,101,var(--text-opacity));
    }

    .delete_confirm_reservation {
        h2 {
            line-height: 63px;
        } /* h2 */
        span {
            padding: 30px 20px;
            line-height: normal;
            display: block;
            font-size: 20px;
            color: #000;
        } /* span */
    } /* delete_confirm_slider_image */

    span.indicators {
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
                background: #38c172;
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
        &.timeout {
            &::after {
                background: #a07930;
            } /* after */
        } /*not_enabled  */

        &.awaiting_payment {
            &::after {
                background: #4299e1;
            } /* after */
        } /*not_enabled  */
    } /* span */


  .main_reservations_table {
        overflow: auto;
        width: 100%;
        padding: 0 0 15px 0;
        background: #fff;
    }
    .main_reservations_table .table {border: 1px solid #e2e8f0;}
    .main_reservations_table .table thead tr th {
        padding: 10px 5px;
        line-height: 20px;
        font-weight: normal;
        font-size: 15px;
        border: 1px solid #5E697C;
        vertical-align: middle;
        text-align: center;
        color: #ffffff;
        background: #4a5568;
    }
    .main_reservations_table .table tbody tr {background: #fff;}
    .main_reservations_table .table tbody tr td {
        text-align: center;
        padding: 10px 5px;
        vertical-align: middle;
        line-height: 20px;
        font-size: 15px;
        border: 1px solid #ced4dc;
        color: #000000;
        font-weight: normal;
        background: #ffffff;
    }
    .main_reservations_table .table tbody tr td.td-fit {
      border-bottom: none;
      border-right: dimgray;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .main_reservations_table .table tbody tr td.td-fit a, .main_reservations_table .table tbody tr td.td-fit button {color: #b3b9bf;}
    .main_reservations_table .table tbody tr td.td-fit svg:hover {color: #3d92d4;}
    .main_reservations_table .table tbody tr td .font-bold {font-weight: normal;}
    .main_reservations_table .table tbody tr td a {color: #000000;}
    .main_reservations_table .table tbody tr td a:hover, .main_reservations_table .table tbody tr td button:hover {color: #3d92d4;}
    /* Portrait phones and smaller */
    @media (min-width: 320px) and (max-width: 480px) {
        .main_reservations_table {
            overflow: scroll;
            padding: 0 0 15px 0;
        }
    }

    /* Smart phones and Tablets */
    @media (min-width: 481px) and (max-width: 767px) {
        .main_reservations_table {
            overflow: scroll;
            padding: 0 0 15px 0;
        }
    }

    /* Small Screens */
    @media (min-width: 768px) and (max-width: 991px) {
        .main_reservations_table {
            overflow: scroll;
            padding: 0 0 15px 0;
        }
    }

    /* Medium Screens */
    @media (min-width: 992px) and (max-width: 1000px) {
        .main_reservations_table {
            overflow: scroll;
            padding: 0 0 15px 0;
        }
    }

 #reservations_customer {
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
                            background-color: #5E6D83;
                        } /* hover */
                    } /* button */
                } /* reset_filters */
            } /* filter_area */
            hr {
                margin: 20px auto;
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
    } /* reservations_customer */
</style>
