<template>
    <div class="item Over relative">
        <loading :active="departuresOverdueLoading"
                 :loader="'spinner'"
                 :color="'#7e7d7f'"
                 :opacity="0.7"
                 :is-full-page="false">
        </loading>
        <div class="title">
            <div class="txt">
                <span>{{ __('Over Out Dates Customers')}} ({{departuresOverduePaginator ? departuresOverduePaginator.total : 0}})</span>
            </div><!--end txt -->
            <div class="icon"></div>
        </div><!--end title -->
        <div class="tools" v-if="departuresOverdueCollection.length">
          <a :href="'/home/panels/departure-overdue/' +  date  "  target="_blank" :title="__('Print')" class="print"></a>
        </div><!-- tools -->
        <div class="col_content">
            <div v-if="departuresOverdueCollection.length">
                <div v-for="(departureOverdue,index) in departuresOverdueCollection" :key="index">
                    <router-link :to="'/reservation/' + departureOverdue.rid">
                        <div class="block">
                        <div class="top_row">
                            <div class="wide">
                                <span>{{__('Reservation Number')}} :</span>
                                <p class="res_num">{{departureOverdue.rnum}}</p>
                            </div><!--end wide -->
                            <div class="wide">
                                <span>{{__('Unit Number')}} :</span>
                                <p>{{departureOverdue.unum}}</p>
                            </div><!--end wide -->
                            <div class="wide">
                                <span>{{__('Nights Count')}} :</span>
                                <p>{{nights(departureOverdue.rdo , departureOverdue.rdi)}}</p>
                            </div><!--end wide -->
                        </div><!--end top_row -->
                        <div class="middle_row">
                            <div class="wide">
                                <span>{{__('Customer')}} :</span>

                                <p>{{departureOverdue.cid ? departureOverdue.cname : ''}}</p>
                            </div><!--end wide -->
                            <div class="wide">
                                <span>{{__('Phone')}} :</span>

                                <p>{{departureOverdue.cphone ? departureOverdue.cphone : '-'}}</p>
                            </div><!--end wide -->
                            <div class="wide">
                                <span> {{__('Reservation Status')}} :</span>
                                <div v-if="departureOverdue.rchi == null && departureOverdue.rcho == null">
                                    <p class="text-info">{{__('Pending')}}</p>
                                </div>
                                <div v-else-if="departureOverdue.rchi != null && departureOverdue.rcho == null">
                                    <p class="text-green-500">{{__('Checked In')}}</p>
                                </div>
                                <div v-else>
                                    <p class="text-red-500">{{__('Checked Out')}}</p>
                                </div>
                            </div><!--end wide -->
                        </div><!--end middle_row -->
                        <div class="bottom_row">
                            <div class="wide">
                                <span> {{__('Date In')}} :</span>
                                <p>{{departureOverdue.rdi | formatDateSpecial}}</p>
                            </div><!--end wide -->
                            <div class="wide">
                                <span> {{__('Date Out')}} :</span>
                                <p>{{departureOverdue.rdo | formatDateSpecial}}</p>
                            </div><!--end wide -->
                            <div class="wide">
                                <span>{{__('Total Credit')}} :</span>
                                <div v-if="departureOverdue.rb > 0">
                                    <p class="text-green-500"> ({{__('credit')}}) {{(departureOverdue.rb/ (departureOverdue.decimal_places == 3 ? 1000 : 100)).toFixed(2)}} {{__(currency)}}</p>
                                </div>
                                <div v-else-if="departureOverdue.rb < 0">
                                    <p class="text-red-500"> ({{__('debit')}}) {{(Math.abs(departureOverdue.rb/ (departureOverdue.decimal_places == 3 ? 1000 : 100))).toFixed(2)}} {{__(currency)}}</p>
                                </div>
                                <div v-else>
                                    <p class="text-black-500">0 {{__(currency)}}</p>
                                </div>
                            </div><!--end wide -->
                        </div><!--end bottom_row -->
                    </div><!--end block -->
                    </router-link>
                </div>
            </div>
            <div v-else>
            <div class="not_found_mg">
              <div class="icon"></div>
              <span>{{ __('No Over Out Dates Found') }}</span>
            </div><!-- not_found_mg -->
            </div>
            <!-- Pagination -->
            <div class="w-full flex flex-wrap mb-4 mt-3 justify-center" v-if="departuresOverdueCollection.length && departuresOverduePaginator.total > 4">
                <pagination
                    :page-count="departuresOverduePaginator.lastPage"
                    :page-range="3"
                    :margin-pages="2"
                    :click-handler="getDeparturesOverdue"
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
        name: "panel-departure-overdue",
        props : ['date'],
        components : {
            Loading
        },
        data(){
            return {
                departuresOverdueCollection : [],
                departuresOverduePaginator : null,
                departuresOverdueLoading : false,

            currency :Nova.app.currentTeam.currency,


    
            }
        },
        methods : {
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
            getDeparturesOverdue(dPagi=1){
                this.departuresOverdueLoading = true;
                axios.get('/nova-vendor/DashboardUnits/departures-overdue?date=' + this.date + '&page=' + dPagi)
                    .then((res) => {

                        this.departuresOverdueCollection = res.data.data;
                        this.departuresOverduePaginator = {
                            currentPage : res.data.current_page ,
                            lastPage : res.data.last_page ,
                            from : res.data.from,
                            to : res.data.to,
                            total : res.data.total,
                            pathPage : res.data.path + '?page=',
                            firstPageUrl : res.data.first_page_url ,
                            lastPageUrl : res.data.last_page_url ,
                            nextPageUrl : res.data.next_page_url ,
                            prevPageUrl : res.data.prev_page_url ,
                        };

                        this.departuresOverdueLoading = false;
                        Nova.$off('call-departures-overdue-query');
                    })
            }
        },
        watch: {
            date: function (val) {
                this.getDeparturesOverdue();
            },
            deep : true
        },
        mounted() {
            Nova.$on('call-departures-overdue-query' , () => {
                this.getDeparturesOverdue();
            });
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
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='59.846' height='90.117' viewBox='0 0 59.846 90.117'%3E%3Cg transform='translate(6.442)'%3E%3Cpath d='M163.3,143.755a1.684,1.684,0,0,0-1.521-.89H124.7a1.681,1.681,0,0,0-1.681,1.682v15.287a1.681,1.681,0,1,0,3.362,0V146.228h23.961l-16.133,4.83a1.681,1.681,0,0,0-1.2,1.61v47.459h-6.628V187.118a1.681,1.681,0,0,0-3.362,0v14.691a1.681,1.681,0,0,0,1.681,1.682h8.31v6.441a1.681,1.681,0,0,0,2.164,1.611L162.3,203.42a1.682,1.682,0,0,0,1.2-1.611V144.585A1.692,1.692,0,0,0,163.3,143.755Zm-3.164,56.8-23.767,7.116V153.92l23.767-7.115Zm0,0' transform='translate(-110.095 -121.496)' fill='%23ddd'/%3E%3Cpath d='M229.1,345.431a4.483,4.483,0,1,0-4.484-4.483A4.483,4.483,0,0,0,229.1,345.431Zm0-5.6a1.121,1.121,0,1,1-1.121,1.121A1.121,1.121,0,0,1,229.1,339.827Zm0,0' transform='translate(-196.5 -286.139)' fill='%23ddd'/%3E%3Cpath d='M213.6,17.934a8.919,8.919,0,1,0-6.354-2.612A8.967,8.967,0,0,0,213.6,17.934Zm0-14.945a5.978,5.978,0,1,1-5.978,5.978A5.978,5.978,0,0,1,213.6,2.989Zm0,0' transform='translate(-179.504)' fill='%23ddd'/%3E%3Cpath d='M245.832,39.405l1.391.892v3.326a1.495,1.495,0,0,0,2.989,0V39.479a1.494,1.494,0,0,0-.688-1.258l-2.079-1.333a1.495,1.495,0,1,0-1.612,2.518Zm0,0' transform='translate(-213.955 -31.17)' fill='%23ddd'/%3E%3Cpath d='M-.7,293.767H19.34a1.681,1.681,0,1,0,0-3.363H-.7l4.792-4.792a1.681,1.681,0,0,0-2.378-2.377L-5.95,290.9a1.682,1.682,0,0,0,0,2.378l7.661,7.661a1.681,1.681,0,0,0,2.378-2.377Zm0,0' transform='translate(0 -240.452)' fill='%23ddd'/%3E%3C/g%3E%3C/svg%3E");
      width: 100px;
      height: 100px;
      background-repeat: no-repeat;
      background-position: center center;
      display: block;
      background-size: 65px;
    } /* icon */
    span {
      display: block;
      font-size: 18px;
      color: #DDDDDD;
    } /* span */
  } /* not_found_mg */
</style>
