<template>
  <div class="item Arrivals relative">
    <loading
      :active="arrivalsLoading"
      :loader="'spinner'"
      :color="'#7e7d7f'"
      :opacity="0.7"
      :is-full-page="false"
    >
    </loading>
    <div class="title">

      <div class="txt">
        <span>{{ __('Arrivals For Day')}} ({{arrivalsPaginator ? arrivalsPaginator.total : 0}})</span>
        <p>{{ readableDate() +' '}} {{this.date | formatDateSpecial}}</p>
      </div><!--end txt -->
      <div class="icon"></div>

    </div><!--end title -->
    <div class="tools_modified" >
        <select v-model="arrival_filter" @change="callArrivals">
          <option value="all">{{ __('All') }}</option>
          <option value="pending">{{ __('Pending') }}</option>
          <option value="checked_in">{{ __('Checked In') }}</option>
        </select>
      <a :href="'/home/panels/arrival/' +  date  + '?status=' + arrival_filter "  target="_blank" :title="__('Print')" class="print"></a>
    </div><!-- tools -->
    <div class="col_content">
      <div v-if="arrivalsCollection.length">
        <div v-for="(arrival,index) in arrivalsCollection" :key="index">
            <router-link :to="arrival.cid ? `/reservation/${arrival.rid}` : `/reservation-noc/${arrival.rid}`">
                <div class="block">
                    <div class="top_row">
                        <div class="wide">
                            <span>{{__('Reservation Number')}} :</span>
                            <p class="res_num">{{arrival.rnum}}</p>
                        </div><!--end wide -->
                        <div class="wide">
                            <span>{{__('Unit Number')}} :</span>
                            <p>{{arrival.unum}}</p>
                        </div><!--end wide -->
                        <div class="wide">
                            <span>{{__('Nights Count')}} :</span>
                            <p>{{nights(arrival.rdo , arrival.rdi)}}</p>
                        </div><!--end wide -->
                         <div class="wide">
                            <span>{{__('Customer')}} :</span>

                            <p v-if="arrival.reservation_type == 'single'">{{arrival.cid ? arrival.cname : ''}}</p>
                            <p v-if="arrival.reservation_type == 'group'">{{arrival.company_name ? arrival.company_name : ''}}</p>
                        </div><!--end wide -->
                    </div><!--end top_row -->
                    <div class="middle_row">

                        <div class="wide">
                            <span>{{__('Phone')}} :</span>

                            <p>{{arrival.cphone ? arrival.cphone : '-'}}</p>
                        </div><!--end wide -->
                        <div class="wide">
                            <span> {{__('Reservation Status')}} :</span>
                            <div v-if="arrival.rchi == null && arrival.rcho == null">
                                <p class="text-info">{{__('Pending')}}</p>
                            </div>
                            <div v-else-if="arrival.rchi != null && arrival.rcho == null">
                                <p class="text-green-500">{{__('Checked In')}}</p>
                            </div>
                            <div v-else>
                                <p class="text-green-500">{{__('Checked In')}}</p>
                            </div>
                        </div><!--end wide -->
                         <div class="wide">
                            <span> {{__('Date In')}} :</span>
                            <p>{{arrival.rdi | formatDateSpecial}}</p>
                        </div><!--end wide -->
                        <div class="wide">
                            <span> {{__('Date Out')}} :</span>
                            <p>{{arrival.rdo | formatDateSpecial}}</p>
                        </div><!--end wide -->
                    </div><!--end middle_row -->
                    <div class="bottom_row">
                        <div class="wide">
                            <span> {{__('Total Reservation')}} :</span>
                            <p class="d-flex">{{parseFloat(arrival.total_price + arrival.services_sum).toFixed(2) }} <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></p>
                        </div><!--end wide -->
                        <div class="wide" v-if="arrival.reservation_type == 'single'">
                            <span>{{__('Total Credit')}} :</span>
                            <div v-if="arrival.rb > 0">
                                <p class="text-green-500 d-flex"> ({{__('credit')}}) {{(arrival.rb).toFixed(2)}} <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></p>
                            </div>
                            <div v-else-if="arrival.rb < 0">
                                <p class="text-red-500 d-flex"> ({{__('debit')}}) {{(Math.abs(arrival.rb)).toFixed(2)}} <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></p>
                            </div>
                            <div v-else>
                                <p class="text-black-500 d-flex">0 <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></p>
                            </div>
                        </div><!--end wide -->

                        <div class="wide" v-else>
                            <span>{{__('Total Credit')}} :</span>
                            <div v-if="arrival.group_balance > 0">
                                <p class="text-green-500 d-flex"> ({{__('credit')}}) {{(arrival.group_balance).toFixed(2)}} <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></p>
                            </div>
                            <div v-else-if="arrival.group_balance < 0">
                                <p class="text-red-500 d-flex"> ({{__('debit')}}) {{(Math.abs(arrival.group_balance)).toFixed(2)}} <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></p>
                            </div>
                            <div v-else>
                                <p class="text-black-500 d-flex">0 <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></p>
                            </div>
                        </div>

                        <div class="wide">
                            <span> {{__('Checkin')}} :</span>
                            
                            <p v-if="arrival.rchi">{{arrival.rchi  | formatDateWithAmPm}}</p>
                            <p v-else>-</p>
                        </div><!--end wide -->

                    
                    </div><!--end bottom_row -->
                </div><!--end block -->
            </router-link>
        </div>
      </div>
      <div v-else>
        <div class="not_found_mg">  

          <div v-show="arrivalsQueryCalled">
              <div class="icon"></div>
              <span>{{ __('No Arrivals Found') }}</span>
          </div>  
          <button v-show="!arrivalsQueryCalled" class="mt-4 bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded custom_color" @click="getArrivals">{{ __('Show Arrivals') }}</button>

        </div><!-- not_found_mg -->
      </div>
      <div class="w-full flex flex-wrap mb-4 mt-3 justify-center" v-if="arrivalsCollection.length && arrivalsPaginator.total > 4">
        <pagination
          :page-count="arrivalsPaginator.lastPage"
          :page-range="3"
          :margin-pages="2"
          :click-handler="getArrivals"
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

    export default {
        name: "panel-arrival",
        props : ['date'],
        components : {
            Loading
        },
        data(){
            return {
                arrivalsCollection : [],
                arrivalsPaginator : null,
                arrivalsLoading : false,
                currency :Nova.app.currentTeam.currency,
                arrival_filter : 'all',
                arrivalsQueryCalled : false
            }
        },
        methods : {
            
            callArrivals(){
                this.getArrivals(1);
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
          getArrivals(dPagi=1){
            
              this.arrivalsQueryCalled = true;
              this.arrivalsLoading = true;
              axios.get('/nova-vendor/DashboardUnits/arrivals?date=' + this.date + '&page=' + dPagi + '&status=' + this.arrival_filter)
                  .then((res) => {

                      this.arrivalsCollection = res.data.data
                      this.arrivalsPaginator = {
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
                      this.arrivalsLoading = false;

                      Nova.$off('call-arrivals-query');
                  })
          }
        },
        watch: {
            date: function (val) {
                this.arrivalsCollection = [];
                this.arrivalsQueryCalled = false;
            },
            deep : true
        },
        mounted() {
            // Nova.$on('call-arrivals-query' , () => {
            //     this.getArrivals();
            // });
        }

    }
</script>

<style lang="scss" scoped>

   .print_button {
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='19.339' height='19.339' viewBox='0 0 19.339 19.339'%3E%3Cpath d='M17.471,17.471v1.934a1.934,1.934,0,0,1-1.934,1.934H7.8a1.934,1.934,0,0,1-1.934-1.934V17.471H3.934A1.934,1.934,0,0,1,2,15.537v-5.8A1.94,1.94,0,0,1,3.934,7.8H5.868V3.934A1.94,1.94,0,0,1,7.8,2h7.735a1.934,1.934,0,0,1,1.934,1.934V7.8H19.4a1.934,1.934,0,0,1,1.934,1.934v5.8A1.934,1.934,0,0,1,19.4,17.471Zm0-1.934H19.4v-5.8H3.934v5.8H5.868V13.6A1.94,1.94,0,0,1,7.8,11.669h7.735A1.934,1.934,0,0,1,17.471,13.6ZM15.537,7.8V3.934H7.8V7.8ZM7.8,13.6v5.8h7.735V13.6Z' transform='translate(-2 -2)' fill='%23333b45'/%3E%3C/svg%3E");
    } /* print_button */
  .not_found_mg {
    padding: 50px 0;
    text-align: center;
    .icon {
      margin: 0 auto 20px;
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='88.167' height='89.429' viewBox='0 0 88.167 89.429'%3E%3Cg transform='translate(0.125)'%3E%3Cpath d='M86.294,71.466H76.778V22.818A17.3,17.3,0,0,0,59.494,5.535H44.735V1.748a1.748,1.748,0,0,0-3.5,0V5.535H28.422A17.3,17.3,0,0,0,11.139,22.818V71.466H1.623a1.748,1.748,0,1,0,0,3.5H86.294a1.748,1.748,0,1,0,0-3.5ZM14.634,22.818A13.8,13.8,0,0,1,28.422,9.03H41.24v5.34a1.748,1.748,0,0,0,3.5,0V9.03H59.494A13.8,13.8,0,0,1,73.283,22.818V71.466H14.634Zm0,0' fill='%23ddd'/%3E%3Cpath d='M166.132,154.213H155.7v-2.58a3.988,3.988,0,0,0-3.968-4h-11.1a3.988,3.988,0,0,0-3.968,4v2.58h-8.211a6.222,6.222,0,0,0-6.214,6.214v25.123a6.224,6.224,0,0,0,4.272,5.9v1.58a1.748,1.748,0,1,0,3.5,0v-1.268h34.374v1.268a1.748,1.748,0,1,0,3.5,0v-1.519a6.226,6.226,0,0,0,4.467-5.964V160.428A6.221,6.221,0,0,0,166.132,154.213Zm-25.967-2.58a.488.488,0,0,1,.472-.5h11.1a.488.488,0,0,1,.472.5v2.58H148.73l-.136-.152-.169.152h-8.261Zm-11.708,6.075h18.715l4.4,4.89v4.459a1.75,1.75,0,0,0,.5,1.228l7.289,7.383a1.748,1.748,0,0,0,2.488,0l4.859-4.922a1.747,1.747,0,0,0,0-2.456l-7.289-7.383a1.747,1.747,0,0,0-1.242-.519h-3.9l-2.411-2.68h14.256a2.723,2.723,0,0,1,2.719,2.719v19.6H125.738V160.428A2.722,2.722,0,0,1,128.457,157.708Zm26.611,8.632v-2.457h2.383l5.564,5.636-2.4,2.434Zm11.064,21.929H128.457a2.722,2.722,0,0,1-2.719-2.719v-2.033H168.85v2.033A2.721,2.721,0,0,1,166.132,188.27Zm0,0' transform='translate(-103.336 -124.675)' fill='%23ddd'/%3E%3Cpath d='M406.4,496.7a6.6,6.6,0,1,0-6.6,6.6A6.6,6.6,0,0,0,406.4,496.7Zm-9.71,0A3.107,3.107,0,1,1,399.8,499.8,3.108,3.108,0,0,1,396.694,496.7Zm0,0' transform='translate(-332.151 -413.87)' fill='%23ddd'/%3E%3Cpath d='M106.729,496.7a6.6,6.6,0,1,0-6.6,6.6A6.6,6.6,0,0,0,106.729,496.7Zm-9.71,0a3.107,3.107,0,1,1,3.107,3.107A3.107,3.107,0,0,1,97.019,496.7Zm0,0' transform='translate(-79.083 -413.87)' fill='%23ddd'/%3E%3C/g%3E%3C/svg%3E");
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
