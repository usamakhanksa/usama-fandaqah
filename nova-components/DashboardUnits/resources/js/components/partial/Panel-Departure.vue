<template>
    <div class="item Departures relative">
        <loading :active="departuresLoading"
                 :loader="'spinner'"
                 :color="'#7e7d7f'"
                 :opacity="0.7"
                 :is-full-page="false">
        </loading>
        <div class="title">
            <div class="txt">
                <span>{{ __('Departures For Day')}} ({{ departuresPaginator ? departuresPaginator.total : 0 }})</span>
                <p>{{ readableDate() +' '}} {{this.date | formatDateSpecial}}</p>
            </div><!--end txt -->
            <div class="icon"></div>    
        </div><!--end title -->
        <div class="tools_modified" >
        <select v-model="departure_filter" @change="callDepartures">
          <option value="all">{{ __('All') }}</option>
          <option value="checked_in">{{ __('Checked In') }}</option>
          <option value="checked_out">{{ __('Checked Out') }}</option>
        </select>
          <a :href="'/home/panels/departure/' +  date + '?status=' + departure_filter "  target="_blank" :title="__('Print')" class="print"></a>
        </div><!-- tools -->
         <div class="col_content">
            <div v-if="departuresCollection.length">
                <div v-for="(departure,index) in departuresCollection" :key="index">
                    <!-- <template v-if="checkIfCurrentSelectedDateIfGreaterThanActualCheckedOutDate(departure.rcho)"> -->
                        <router-link :to=" departure.cid ? `/reservation/${departure.rid}` : `/reservation-noc/${departure.rid}`">
                            <div class="block">

                                <div class="top_row">
                                    <div class="wide">
                                        <span>{{__('Reservation Number')}} :</span>
                                        <p class="res_num">{{departure.rnum}}</p>
                                    </div><!--end wide -->
                                    <div class="wide">
                                        <span>{{__('Unit Number')}} :</span>
                                        <p>{{departure.unum}}</p>
                                    </div><!--end wide -->
                                    <div class="wide">
                                        <span>{{__('Nights Count')}} :</span>
                                        <p>{{nights(departure.rdo , departure.rdi)}}</p>
                                    </div><!--end wide -->
                                    <div class="wide">
                                        <span>{{__('Customer')}} :</span>
                                        <p v-if="departure.reservation_type == 'single'">{{departure.cid ? departure.cname : ''}}</p>
                                        <p v-if="departure.reservation_type == 'group'">{{departure.company_name ? departure.company_name : ''}}</p>
                                    </div><!--end wide -->

                                </div><!--end top_row -->
                                <div class="middle_row">

                                    <div class="wide">
                                        <span>{{__('Phone')}} :</span>

                                        <p>{{departure.cphone ? departure.cphone : '-'}}</p>
                                    </div><!--end wide -->
                                    <div class="wide">
                                        <span> {{__('Reservation Status')}} :</span>
                                        <div v-if="departure.rchi == null && departure.rcho == null">
                                            <p class="text-info">{{__('Pending')}}</p>
                                        </div>
                                        <div v-else-if="departure.rchi != null && departure.rcho == null">
                                            <p class="text-green-500">{{__('Checked In')}}</p>
                                        </div>
                                        <div v-else>
                                            <p class="text-red-500">{{__('Checked Out')}}</p>
                                        </div>
                                    </div><!--end wide -->
                                    <div class="wide">
                                        <span> {{__('Date In')}} :</span>
                                        <p>{{departure.rdi | formatDateSpecial}}</p>
                                    </div><!--end wide -->
                                    <div class="wide">
                                        <span> {{__('Date Out')}} :</span>
                                        <p>{{departure.rdo | formatDateSpecial}}</p>
                                    </div><!--end wide -->
                                </div><!--end middle_row -->
                                <div class="bottom_row">
                                    <div class="wide">
                                        <span> {{__('Total Reservation')}} :</span>
                                        <p class="d-flex">{{parseFloat(departure.total_price + departure.services_sum).toFixed(2) }} <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></p>
                                    </div><!--end wide -->
                                    <div class="wide" v-if="departure.reservation_type == 'single'">
                                <span>{{__('Total Credit')}} :</span>
                                <div v-if="departure.rb > 0">
                                    <p class="text-green-500 d-flex"> ({{__('credit')}}) {{(departure.rb).toFixed(2)}} <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></p>
                                </div>
                                <div v-else-if="departure.rb < 0">
                                    <p class="text-red-500 d-flex"> ({{__('debit')}}) {{(Math.abs(departure.rb)).toFixed(2)}} <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></p>
                                </div>
                                <div v-else>
                                    <p class="text-black-500 d-flex">0 <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></p>
                                </div>
                            </div><!--end wide -->

                            <div class="wide" v-else>
                                <span>{{__('Total Credit')}} :</span>
                                <div v-if="departure.group_balance > 0">
                                    <p class="text-green-500 d-flex"> ({{__('credit')}}) {{(departure.group_balance).toFixed(2)}} <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></p>
                                </div>
                                <div v-else-if="departure.group_balance < 0">
                                    <p class="text-red-500 d-flex"> ({{__('debit')}}) {{(Math.abs(departure.group_balance)).toFixed(2)}} <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></p>
                                </div>
                                <div v-else>
                                    <p class="text-black-500 d-flex">0 <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></p>
                                </div>
                            </div>

                             

                                    <div class="wide">
                                        <span> {{__('Checkout')}} :</span>
                                        <p v-if="departure.rcho">{{departure.rcho  | formatDateWithAmPm}}</p>
                                        <p v-else>-</p>
                                </div><!--end wide -->
                            </div><!--end bottom_row -->

                                <div v-if="checkout_time <= current_time && (departure.rchi != null && departure.rcho == null)" v-tooltip.top-center="checkout_overdue_msg" class="overdue_icon"></div>

                            </div><!--end block -->
                        </router-link>
                    <!-- </template> -->
                    <!-- <template v-if="!checkIfCurrentSelectedDateIfGreaterThanActualCheckedOutDate(departure.rcho) && departuresCollection.length == 1">
                        <div class="not_found_mg">
                            <div class="icon"></div>
                            <span>{{ __('No Departures Found') }}</span>
                        </div>
                    </template> -->
                </div>
            </div>
            <div v-else>
            <div class="not_found_mg">
                <div v-show="departureQueryCalled">
                    <div class="icon"></div>
                    <span>{{ __('No Departures Found') }}</span>
                </div>  
   
              <button v-show="!departureQueryCalled" class="mt-4 bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded custom_color" @click="getDepartures">{{ __('Show Departures') }}</button>

            </div><!-- not_found_mg -->

            </div>
            <!-- Pagination -->
            <div class="w-full flex flex-wrap mb-4 mt-3 justify-center" v-if="departuresCollection.length && departuresPaginator.total > 4">

                <pagination
                    :page-count="departuresPaginator.lastPage"
                    :page-range="3"
                    :margin-pages="2"
                    :click-handler="getDepartures"
                    :prev-text="'<'"
                    :next-text="'>'"
                    :container-class="'pagination w-full flex justify-center'"
                    :page-class="'page-item'"
                    :page-link-class="'page-link'"
                    :prev-link-class="'page-link'"
                    :next-link-class="'page-link'"
                    :prev-class="'page-item'"
                    :next-class="'page-item'"
                    :first-last-button="true"
                    :first-button-text="'<<'"
                    :last-button-text="'>>'"
                >
                </pagination>

            </div><!-- End Pagination -->
        </div><!--end col_content -->
    </div><!--end item -->
</template>

<script>
    import Loading from 'vue-loading-overlay';
    import 'vue-loading-overlay/dist/vue-loading.css';
    import momenttimezone from 'moment-timezone';
    import moment from 'moment';
    export default {
        name: "panel-departure",
        props : ['date'],
        components : {
            Loading
        },
        data(){
            return {
                departuresCollection : [],
                departuresPaginator : null,
                departuresLoading : false,
                checkout_time : null,
                current_time :  momenttimezone().tz("Asia/Riyadh").format('HH:mm'),
                checkout_overdue_msg : this.__('Customer has passed the checkout time'),
                currency :Nova.app.currentTeam.currency,
                departure_filter : 'all',
                departureQueryCalled : false
            }
        },
        methods : {
            callDepartures(){
                this.getDepartures(1);
            },
            nights(date_out , date_in){
                const start = new Date(date_in)
                const end = new Date(date_out)
                let dayCount = 0

                while (end > start) {
                    dayCount++
                    start.setDate(start.getDate() + 1)
                }
                return dayCount
            },
            readableDate() {
                let date = new Date(this.date);
                switch (date.getDay()) {
                    case 0:
                        return Nova.app.__('Sunday');
                    case 1:
                        return Nova.app.__('Monday');
                    case 2:
                        return Nova.app.__('Tuesday');
                    case 3:
                        return Nova.app.__('Wednesday');
                    case 4:
                        return Nova.app.__('Thursday');
                    case 5:
                        return Nova.app.__('Friday');
                    case 6:
                        return Nova.app.__('Saturday');
                }
            },
            getDepartures(dPagi=1){
                this.departureQueryCalled = true;
                this.departuresLoading = true;
                axios.get('/nova-vendor/DashboardUnits/departures?date=' + this.date + '&page=' + dPagi + '&status=' + this.departure_filter)
                    .then((res) => {
                        this.departuresCollection = res.data.data;
                        this.departuresPaginator = {
                             currentPage : res.data.meta.current_page ,
                            lastPage : res.data.meta.last_page ,
                            from : res.data.meta.from,
                            to : res.data.meta.to,
                            total : res.data.meta.total,
                            pathPage : res.data.meta.path + '?page=',
                            firstPageUrl : res.data.links.first ,
                            lastPageUrl : res.data.links.last ,
                            nextPageUrl : res.data.next ,
                            prevPageUrl : res.data.prev ,
                        };
                        this.checkout_time = res.data.checkout_time;
                        this.departuresLoading = false;
                        Nova.$off('call-departures-query');
                    })
            },
            checkIfCurrentSelectedDateIfGreaterThanActualCheckedOutDate(checkout_date){
                var actual_checkout_date = new Date(checkout_date);
                var currentDate = new Date(this.date);

                if( (currentDate > actual_checkout_date) && checkout_date){
                    return false;
                }else{
                    return true;
                }
            }
        },
        watch: {
            date: function (val) {
                this.departuresCollection = [];
                this.departureQueryCalled = false;
            },
            deep : true
        },
        mounted() {

            // Nova.$on('call-departures-query' , () => {
            //     this.getDepartures();
            // });
        }

    }
</script>

<style lang="scss" scoped>


.overdue_icon {
     background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='59.846' height='90.117' viewBox='0 0 59.846 90.117'%3E%3Cg transform='translate(6.442)'%3E%3Cpath d='M163.3,143.755a1.684,1.684,0,0,0-1.521-.89H124.7a1.681,1.681,0,0,0-1.681,1.682v15.287a1.681,1.681,0,1,0,3.362,0V146.228h23.961l-16.133,4.83a1.681,1.681,0,0,0-1.2,1.61v47.459h-6.628V187.118a1.681,1.681,0,0,0-3.362,0v14.691a1.681,1.681,0,0,0,1.681,1.682h8.31v6.441a1.681,1.681,0,0,0,2.164,1.611L162.3,203.42a1.682,1.682,0,0,0,1.2-1.611V144.585A1.692,1.692,0,0,0,163.3,143.755Zm-3.164,56.8-23.767,7.116V153.92l23.767-7.115Zm0,0' transform='translate(-110.095 -121.496)' fill='%23ff0000'/%3E%3Cpath d='M229.1,345.431a4.483,4.483,0,1,0-4.484-4.483A4.483,4.483,0,0,0,229.1,345.431Zm0-5.6a1.121,1.121,0,1,1-1.121,1.121A1.121,1.121,0,0,1,229.1,339.827Zm0,0' transform='translate(-196.5 -286.139)' fill='%23ff0000'/%3E%3Cpath d='M213.6,17.934a8.919,8.919,0,1,0-6.354-2.612A8.967,8.967,0,0,0,213.6,17.934Zm0-14.945a5.978,5.978,0,1,1-5.978,5.978A5.978,5.978,0,0,1,213.6,2.989Zm0,0' transform='translate(-179.504)' fill='%23ff0000'/%3E%3Cpath d='M245.832,39.405l1.391.892v3.326a1.495,1.495,0,0,0,2.989,0V39.479a1.494,1.494,0,0,0-.688-1.258l-2.079-1.333a1.495,1.495,0,1,0-1.612,2.518Zm0,0' transform='translate(-213.955 -31.17)' fill='%23ff0000'/%3E%3Cpath d='M-.7,293.767H19.34a1.681,1.681,0,1,0,0-3.363H-.7l4.792-4.792a1.681,1.681,0,0,0-2.378-2.377L-5.95,290.9a1.682,1.682,0,0,0,0,2.378l7.661,7.661a1.681,1.681,0,0,0,2.378-2.377Zm0,0' transform='translate(0 -240.452)' fill='%23ff0000'/%3E%3C/g%3E%3C/svg%3E");
       margin: 10px 0 0;
   width: 100%;
    height: 40px;
    background-repeat: no-repeat;
    background-position: center left;
    display: block;
    background-size: 25px;
    } /* icon */
.print_button {
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='19.339' height='19.339' viewBox='0 0 19.339 19.339'%3E%3Cpath d='M17.471,17.471v1.934a1.934,1.934,0,0,1-1.934,1.934H7.8a1.934,1.934,0,0,1-1.934-1.934V17.471H3.934A1.934,1.934,0,0,1,2,15.537v-5.8A1.94,1.94,0,0,1,3.934,7.8H5.868V3.934A1.94,1.94,0,0,1,7.8,2h7.735a1.934,1.934,0,0,1,1.934,1.934V7.8H19.4a1.934,1.934,0,0,1,1.934,1.934v5.8A1.934,1.934,0,0,1,19.4,17.471Zm0-1.934H19.4v-5.8H3.934v5.8H5.868V13.6A1.94,1.94,0,0,1,7.8,11.669h7.735A1.934,1.934,0,0,1,17.471,13.6ZM15.537,7.8V3.934H7.8V7.8ZM7.8,13.6v5.8h7.735V13.6Z' transform='translate(-2 -2)' fill='%23333b45'/%3E%3C/svg%3E");
    } /* print_button */
.not_found_mg {
    padding: 50px 0;
    text-align: center;
    .icon {
      margin: 0 auto 20px;
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='77.656' height='86.09' viewBox='0 0 77.656 86.09'%3E%3Cpath d='M127.572,166.228a7.307,7.307,0,1,0-7.3-7.307A7.3,7.3,0,0,0,127.572,166.228Zm0-11.354a4.047,4.047,0,1,1-4.049,4.047A4.044,4.044,0,0,1,127.572,154.873Zm0,0' transform='translate(-102.607 -129.63)' fill='%23ddd'/%3E%3Cpath d='M74.251,289.319h-4.7v-1.237a9.208,9.208,0,0,0-9.2-9.2h-7.5a9.208,9.208,0,0,0-9.195,9.2v1.237h-7.49v-1.237a9.208,9.208,0,0,0-9.2-9.2H19.466a9.208,9.208,0,0,0-9.195,9.2v1.237H-.15a1.629,1.629,0,0,0-1.627,1.629,1.629,1.629,0,0,0,1.627,1.63H10.272v6.343a1.627,1.627,0,0,0,1.626,1.629h2.5L15.635,323a1.628,1.628,0,0,0,1.625,1.539H29.243a1.627,1.627,0,0,0,1.625-1.544l1.18-22.441h2.493a1.628,1.628,0,0,0,1.627-1.629v-6.343H74.252a1.63,1.63,0,0,0,0-3.26Zm-41.339,7.973H30.5a1.627,1.627,0,0,0-1.624,1.544L27.7,321.277H18.8l-1.24-22.445a1.628,1.628,0,0,0-1.624-1.539h-2.41v-9.21a5.948,5.948,0,0,1,5.941-5.94h7.506a5.947,5.947,0,0,1,5.941,5.94Zm33.385-9.21v1.237H58.132l2.043-7.176h.183a5.947,5.947,0,0,1,5.941,5.939Zm-10.241-3.364-.891-2.575h1.624Zm-9.147,3.364a5.948,5.948,0,0,1,4.848-5.837l2.449,7.073h-7.3Zm0,0' transform='translate(1.777 -238.447)' fill='%23ddd'/%3E%3Cpath d='M357.561,165.625a7.429,7.429,0,0,0,6.8-4.465l1.7-.544a1.6,1.6,0,0,0,.186-.072,4.888,4.888,0,0,0-4.076-8.886l-.624.288a7.422,7.422,0,1,0-3.992,13.679Zm8.132-10.2a1.633,1.633,0,0,1-.724,2.125l-.017.005a7.382,7.382,0,0,0-.968-3.062A1.627,1.627,0,0,1,365.692,155.421Zm-4.047,1.956a6.277,6.277,0,0,1-3.659-3.32A4.176,4.176,0,0,1,361.645,157.376Zm-6.776-2.354a9.412,9.412,0,0,0,6.1,5.573,4.164,4.164,0,1,1-6.1-5.573Zm0,0' transform='translate(-299.21 -128.91)' fill='%23ddd'/%3E%3Cpath d='M215.24,17.385a8.692,8.692,0,1,0-6.307-2.723A8.681,8.681,0,0,0,215.24,17.385ZM210.686,5.123a5.8,5.8,0,1,1-1.23,3.57A5.777,5.777,0,0,1,210.686,5.123Zm0,0' transform='translate(-176.413 0)' fill='%23ddd'/%3E%3Cpath d='M252.74,33.352l2.012,1.291a1.448,1.448,0,1,0,1.56-2.44l-1.346-.864V28.117a1.446,1.446,0,1,0-2.892,0v4.015A1.449,1.449,0,0,0,252.74,33.352Zm0,0' transform='translate(-215.337 -22.801)' fill='%23ddd'/%3E%3Cpath d='M282.687,415.373h-2.832v-1.531a3.261,3.261,0,0,0-3.244-3.271h-4.476a3.261,3.261,0,0,0-3.244,3.271v1.532h-2.832a5.8,5.8,0,0,0-5.784,5.8v9.3a5.8,5.8,0,0,0,3.121,5.142,1.624,1.624,0,0,0,3.057.654h15.7a1.625,1.625,0,0,0,3.05-.578,5.8,5.8,0,0,0,3.269-5.218v-9.3A5.8,5.8,0,0,0,282.687,415.373Zm-10.551-1.543,4.467.012v1.531H272.14Zm-6.078,4.8h16.629a2.537,2.537,0,0,1,2.531,2.536v5.876h-21.69v-5.876A2.537,2.537,0,0,1,266.058,418.632Zm16.629,14.375H266.058a2.536,2.536,0,0,1-2.53-2.536V430.3h21.69v.169A2.536,2.536,0,0,1,282.687,433.007Zm0,0' transform='translate(-222.35 -351.04)' fill='%23ddd'/%3E%3Cpath d='M426.768,318.491h1.265a1.63,1.63,0,0,0,0-3.26h-1.265a1.63,1.63,0,0,0,0,3.26Zm0,0' transform='translate(-363.358 -269.524)' fill='%23ddd'/%3E%3C/svg%3E");
      width: 100px;
      height: 100px;
      background-repeat: no-repeat;
      background-position: center center;
      display: block;
      background-size: 90px;
    } /* icon */
    span {
      display: block;
      font-size: 18px;
      color: #DDDDDD;
    } /* span */
  } /* not_found_mg */

  .tools_modified {
                padding: 5px 10px;
                background: #FDFDFD;
                border-bottom: 1px solid #ddd;
                display: flex;
                align-items: center;
                justify-content: space-between;
              .print {
                background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='19.339' height='19.339' viewBox='0 0 19.339 19.339'%3E%3Cpath d='M17.471,17.471v1.934a1.934,1.934,0,0,1-1.934,1.934H7.8a1.934,1.934,0,0,1-1.934-1.934V17.471H3.934A1.934,1.934,0,0,1,2,15.537v-5.8A1.94,1.94,0,0,1,3.934,7.8H5.868V3.934A1.94,1.94,0,0,1,7.8,2h7.735a1.934,1.934,0,0,1,1.934,1.934V7.8H19.4a1.934,1.934,0,0,1,1.934,1.934v5.8A1.934,1.934,0,0,1,19.4,17.471Zm0-1.934H19.4v-5.8H3.934v5.8H5.868V13.6A1.94,1.94,0,0,1,7.8,11.669h7.735A1.934,1.934,0,0,1,17.471,13.6ZM15.537,7.8V3.934H7.8V7.8ZM7.8,13.6v5.8h7.735V13.6Z' transform='translate(-2 -2)' fill='%23333b45'/%3E%3C/svg%3E");
                display: block;
                height: 25px;
                width: 25px;
                outline: none;
                background-position: center center;
                background-size: 20px 20px;
                background-repeat: no-repeat;
              } /* print */
        } /* tools */

        select {
          background-color: #fafafa;
          background-image: url("data:image/svg+xml,%3C%3Fxml version='1.0'%3F%3E%3Csvg xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' version='1.1' id='Layer_1' x='0px' y='0px' viewBox='0 0 512.011 512.011' style='enable-background:new 0 0 512.011 512.011;' xml:space='preserve' width='512px' height='512px' class=''%3E%3Cg%3E%3Cg%3E%3Cg%3E%3Cpath d='M505.755,123.592c-8.341-8.341-21.824-8.341-30.165,0L256.005,343.176L36.421,123.592c-8.341-8.341-21.824-8.341-30.165,0 s-8.341,21.824,0,30.165l234.667,234.667c4.16,4.16,9.621,6.251,15.083,6.251c5.462,0,10.923-2.091,15.083-6.251l234.667-234.667 C514.096,145.416,514.096,131.933,505.755,123.592z' data-original='%23000000' class='active-path' fill='%23000000'/%3E%3C/g%3E%3C/g%3E%3C/g%3E%3C/svg%3E%0A");
          background-repeat: no-repeat;
          background-size: 14px;
          background-position: 10px center;
          height: 35px;
          padding: 0 10px;
          font-size: 15px;
          border: 1px solid #ddd !important;
          color: #000;
          width: 25%;
          border-radius: 4px !important;
          outline: none;
          -webkit-appearance: none;
          -moz-appearance: none;
          -o-appearance: none;
          appearance: none;
        } /* select */

        .custom_color {
            background-color: #459ade;
            &:hover {
                background-color: #2776b4;
            }
        }

        .d-flex{
            display: flex !important;
        }
</style>
