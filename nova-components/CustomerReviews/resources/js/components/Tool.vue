<template>
  <div id="customer_reviews_page">

    <div class="flex w-full mb-4">
      <nav v-if="crumbs.length">
        <ul class="breadcrumbs">
          <li class="breadcrumbs__item" v-for="crumb in crumbs" v-if="crumb.text != false">
            <router-link :to="crumb.to">{{ __(crumb.text) }}</router-link>
          </li>
        </ul>
      </nav>
    </div><!-- Breadcrumb -->

    <div class="filter_area">
      <div class="item">
        <date-picker-from :enable-seconds="false" :enable-time="true" :date-format="'Y-m-d H:i'" :twelve-hour-time="false" :locale="locale" :placeholder="__('Date From')" />
      </div><!-- item -->
      <div class="item">
        <date-picker-to  :enable-seconds="false" :enable-time="true" :date-format="'Y-m-d H:i'" :twelve-hour-time="false" :locale="locale" :placeholder="__('Date To')" />
      </div><!-- item -->
      <div class="item">
        <select
              v-model="rating_status"
              @change="getReviews"
            >
              <option value="null">
                {{ __("Status") }}
              </option>
              <option value="0" :selected="true">{{ __('Waiting Approval') }}</option>
              <option value="1">{{ __('Approved') }}</option>
          </select>
      </div><!-- item -->

      <div class="item" v-if="teams_data.length">
        <select
              v-model="team_id_filter"
              @change="getReviews"
            >
              <option value="null">
                {{ __("Hotel") }}
              </option>
              <option v-for="(team,i) in teams_data" :key="i" :value="team.id">{{ team.name }}</option>
          </select>
      </div><!-- item -->

      <div class="reset_filters">
        <button
          @click="resetFilters"
          v-tooltip="{
            targetClasses: ['it-has-a-tooltip'],
            placement: 'top',
            content: __('Reset Filters'),
            classes: ['tooltip_reset']
          }"
        >
        </button>
      </div><!-- reset_filters -->
    </div><!-- filter_area -->

    <div v-if="collection.length" class="totla_reviews_block">
      <div class="col_1">
        <div class="evaluation_number" v-if="total">{{total.total}}</div>
        <div class="evaluation_txt">
          <span>{{total.label}}</span>
          <p>{{__('Ratings Count')}} : {{total.count}}</p>
          <p>{{__('Approved Ratings Count')}} : {{total.approved_ratings}}</p>
        </div><!-- evaluation_txt -->
      </div><!-- col_1 -->
      <div class="col_2" v-show="settings && settings.items">
        <div class="progress_item">
          <span>{{settings.items.text_of_the_first_question.value}}</span>
          <div class="progress_bar">
            <p>{{total.q_one}}</p>
            <div class="pro_bar"><div :style="{width: total.q_one*10 +'%'}" class="bar_inside"></div></div>
          </div><!-- progress_bar -->
        </div><!-- progress_item -->
        <div class="progress_item">
          <span>{{settings.items.text_of_the_second_question.value}}</span>
          <div class="progress_bar">
            <p>{{total.q_two}}</p>
            <div class="pro_bar"><div :style="{width: total.q_two*10 +'%'}" class="bar_inside"></div></div>
          </div><!-- progress_bar -->
        </div><!-- progress_item -->
        <div class="progress_item">
          <span>{{settings.items.text_of_question_three.value}}</span>
          <div class="progress_bar">
            <p>{{total.q_three}}</p>
            <div class="pro_bar"><div :style="{width: total.q_three*10 +'%'}" class="bar_inside"></div></div>
          </div><!-- progress_bar -->
        </div><!-- progress_item -->
        <div class="progress_item" v-if="settings.items.enable_ninth_question.value">
          <span>{{settings.items.the_text_of_the_ninth_question.value}}</span>
          <div class="progress_bar">
            <p>{{total.q_eleven}}</p>
            <div class="pro_bar"><div :style="{width: total.q_eleven*10 +'%'}" class="bar_inside"></div></div>
          </div><!-- progress_bar -->
        </div><!-- progress_item -->
        <div v-if="settings.items.enable_tenth_question.value" class="progress_item">
          <span>{{settings.items.the_text_of_the_tenth_question.value}}</span>
          <div class="progress_bar">
            <p>{{total.q_twelve}}</p>
            <div class="pro_bar"><div :style="{width: total.q_twelve*10 +'%'}" class="bar_inside"></div></div>
          </div><!-- progress_bar -->
        </div><!-- progress_item -->
      </div><!-- col_2 -->
      <div class="col_2" v-show="settings && settings.items">
        <div class="progress_item">
          <span>{{settings.items.text_of_question_four.value}}</span>
          <div class="progress_bar">
            <p>{{total.q_four}}</p>
            <div class="pro_bar"><div :style="{width: total.q_four*10 +'%'}" class="bar_inside"></div></div>
          </div><!-- progress_bar -->
        </div><!-- progress_item -->
        <div class="progress_item">
          <span>{{settings.items.text_of_question_five.value}}</span>
          <div class="progress_bar">
            <p>{{total.q_five}}</p>
            <div class="pro_bar"><div :style="{width: total.q_five*10 +'%'}" class="bar_inside"></div></div>
          </div><!-- progress_bar -->
        </div><!-- progress_item -->
        <div class="progress_item">
          <span>{{settings.items.the_text_of_the_sixth_question.value}}</span>
          <div class="progress_bar">
            <p>{{total.q_six}}</p>
            <div class="pro_bar"><div :style="{width: total.q_six*10 +'%'}" class="bar_inside"></div></div>
          </div><!-- progress_bar -->
        </div><!-- progress_item -->
        <div v-if="settings.items.enable_seventh_question.value" class="progress_item">
          <span>{{settings.items.the_text_of_the_seventh_question.value}}</span>
          <div class="progress_bar">
            <p>{{total.q_nine}}</p>
            <div class="pro_bar"><div :style="{width: total.q_nine*10 +'%'}" class="bar_inside"></div></div>
          </div><!-- progress_bar -->
        </div><!-- progress_item -->

        <div v-if="settings.items.enable_eighth_question.value" class="progress_item">
          <span>{{settings.items.the_text_of_the_eighth_question.value}}</span>
          <div class="progress_bar">
            <p>{{total.q_ten}}</p>
            <div class="pro_bar"><div :style="{width: total.q_ten*10 +'%'}" class="bar_inside"></div></div>
          </div><!-- progress_bar -->
        </div><!-- progress_item -->
      </div><!-- col_2 -->
    </div><!-- totla_reviews_block -->

    <div class="reviews_users">
      <loading :active.sync="isLoading" :can-cancel="true" :color="'#7e7d7f'" :is-full-page="false" :loader="'spinner'"></loading>
      <div :key="i" class="progress_user rounded-lg" v-for="(review , i) in collection" v-if="collection.length">
        <div  class="all_details">
          <div  class="details">
            <span>{{__('Review Status')}}</span>
            <p :class="[review.status == 0 ? 'pending' : 'approved']">{{review.status == 0 ?  __('Waiting Approval') : __('Approved') }} </p>
          </div><!-- details -->
          <div  class="details">
            <span>{{__('Reservation Number')}}</span>
            <p  v-if="review.team_id == shared_team_id"><router-link :to="`/reservation/${review.reservation_id}`">{{review.reservation_number}}</router-link></p>
            <p v-else>{{review.reservation_number}}</p>
          </div><!-- details -->
          <div  class="details">
            <span>{{__('Customer')}}</span>
            <p class="mb-3"><router-link  :to="`/resources/customers/${review.customer_id}`" class="">{{review.customer_name}}</router-link></p>
            <a :href="'tel:' + review.customer_phone">{{ review.customer_phone }}</a>
          </div><!-- details -->
          <div  class="details" v-if="shared_team_id == current_team_id">
            <span>{{__('Hotel')}}</span>
            <span v-if="locale == 'ar'" class="bg-gray-100 text-gray-800 text-xs font-medium me-2 px-2.5 py-0.5 rounded dark:bg-gray-700 dark:text-gray-300">{{ review.hotel_name.ar }}</span>
            <span v-else class="bg-gray-100 text-gray-800 text-xs font-medium me-2 px-2.5 py-0.5 rounded dark:bg-gray-700 dark:text-gray-300">{{ review.hotel_name.en }}</span>

          </div>
          <div  class="details">
            <span>{{__('Reservation Date')}}</span>
            <p>{{dateFormat(review.reservation_checked_in)}}</p>
          </div><!-- details -->
          <div  class="details">
            <span>{{__('Nights')}}</span>
            <p>{{review.nights}}</p>
          </div><!-- details -->
          <div  class="details">
            <span>{{__('Evaluation')}}</span>
            <p>{{review.overall_rating}}</p>
          </div><!-- details -->
          <div  class="details">
            <span>{{__('Review Date')}}</span>
            <p>{{dateFormat(review.created_at)}}</p>
          </div><!-- details -->
          <div  class="details">
            <span >{{__('Actions')}}</span>
            <button
              @click="handleApproveClick(review.id)"
              class="cursor-pointer text-70 hover:text-primary mr-3"
              type="button"
              v-if="!review.status"
              v-tooltip="{
                targetClasses: ['it-has-a-tooltip'],
                placement: 'top',
                content: __('Approve Review'),
                classes: ['tooltip_reset']
              }"
            >
              <svg version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="20px" height="20px" class="fill-current" viewBox="0 0 459 459" style="enable-background:new 0 0 459 459;" xml:space="preserve">
                <g>
                  <g id="check-box-outline">
                    <path d="M124.95,181.05l-35.7,35.7L204,331.5l255-255l-35.7-35.7L204,260.1L124.95,181.05z M408,408H51V51h255V0H51
                      C22.95,0,0,22.95,0,51v357c0,28.05,22.95,51,51,51h357c28.05,0,51-22.95,51-51V204h-51V408z"/>
                  </g>
                </g>
                <g>
                </g>
                <g>
                </g>
                <g>
                </g>
                <g>
                </g>
                <g>
                </g>
                <g>
                </g>
                <g>
                </g>
                <g>
                </g>
                <g>
                </g>
                <g>
                </g>
                <g>
                </g>
                <g>
                </g>
                <g>
                </g>
                <g>
                </g>
                <g>
                </g>
              </svg>
            </button>
            <button @click="handleEditClick(review.id)" class="cursor-pointer text-70 hover:text-primary mr-3" type="button">
              <svg aria-labelledby="edit" class="fill-current" height="20" role="presentation" viewBox="0 0 20 20" width="20" xmlns="http://www.w3.org/2000/svg"><path d="M4.3 10.3l10-10a1 1 0 0 1 1.4 0l4 4a1 1 0 0 1 0 1.4l-10 10a1 1 0 0 1-.7.3H5a1 1 0 0 1-1-1v-4a1 1 0 0 1 .3-.7zM6 14h2.59l9-9L15 2.41l-9 9V14zm10-2a1 1 0 0 1 2 0v6a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V4c0-1.1.9-2 2-2h6a1 1 0 1 1 0 2H2v14h14v-6z"></path></svg>
            </button>
          </div><!-- details -->
        </div><!-- progress_user -->
        <div class="comments_user">
          <div class="evaluation_comment">
            <div v-if="review.positive_comment" class="comment_item">
              <span>{{__('Comments - positive')}}</span>
              <p>{{review.positive_comment}}</p>
            </div>
            <div v-if="review.negative_comment" class="comment_item">
              <span>{{__('Comments - negative')}}</span>
              <p>{{review.negative_comment}}</p>
            </div>
            <div v-if="review.q_custom" class="comment_item">
              <span>{{__('Feedback & Suggestions')}}</span>
              <p>{{review.q_custom}}</p>
            </div>
          </div><!-- evaluation_comment -->
          <div class="evaluation_comment_bars">
            <div class="progress_item">
              <div class="flex justify-between">
                <span>{{settings.items.text_of_the_first_question.value}}</span>
                <p>{{review.q_one}}</p>
              </div>
              <div class="pro_bar"><div  :style="{width: review.q_one*10 +'%'}" class="bar_inside"></div></div>
            </div><!-- progress_item -->
            <div class="progress_item">
              <div class="flex justify-between">
                <span>{{settings.items.text_of_the_second_question.value}}</span>
                <p>{{review.q_two}}</p>
              </div>
              <div class="pro_bar"><div  :style="{width: review.q_two*10 +'%'}" class="bar_inside"></div></div>
            </div><!-- progress_item -->
            <div class="progress_item">
              <div class="flex justify-between">
                <span>{{settings.items.text_of_question_three.value}}</span>
                <p>{{review.q_three}}</p>
              </div>
              <div  class="pro_bar"><div  :style="{width: review.q_three*10 +'%'}" class="bar_inside"></div></div>
            </div><!-- progress_item -->
            <div v-if="settings.items.enable_ninth_question.value" class="progress_item">
              <div class="flex justify-between">
                <span>{{settings.items.the_text_of_the_ninth_question.value}}</span>
                <p>{{review.q_eleven}}</p>
              </div>
              <div  class="pro_bar"><div  :style="{width: review.q_eleven*10 +'%'}" class="bar_inside"></div></div>
            </div><!-- progress_item -->
            <div v-if="settings.items.enable_tenth_question.value" class="progress_item">
              <div class="flex justify-between">
                <span>{{settings.items.the_text_of_the_tenth_question.value}}</span>
                <p>{{review.q_twelve}}</p>
              </div>
              <div  class="pro_bar"><div  :style="{width: review.q_twelve*10 +'%'}" class="bar_inside"></div></div>
            </div><!-- progress_item -->


          </div><!-- evaluation_comment_bars -->
          <div class="evaluation_comment_bars">
            <div class="progress_item">
              <div class="flex justify-between">
                <span>{{settings.items.text_of_question_four.value}}</span>
                <p>{{review.q_four}}</p>
              </div>
              <div  class="pro_bar"><div  :style="{width: review.q_four*10 +'%'}" class="bar_inside"></div></div>
            </div><!-- progress_item -->
            <div class="progress_item">
              <div class="flex justify-between">
                <span>{{settings.items.text_of_question_five.value}}</span>
                <p>{{review.q_five}}</p>
              </div>
              <div class="pro_bar"><div  :style="{width: review.q_five*10 +'%'}" class="bar_inside"></div></div>
            </div><!-- progress_item -->
            <div class="progress_item">
              <div class="flex justify-between">
                <span>{{settings.items.the_text_of_the_sixth_question.value}}</span>
                <p>{{review.q_six}}</p>
              </div>
              <div class="pro_bar"><div  :style="{width: review.q_six*10 +'%'}" class="bar_inside"></div></div>
            </div><!-- progress_item -->
            <div v-if="settings.items.enable_seventh_question.value" class="progress_item">
              <div class="flex justify-between">
                <span>{{settings.items.the_text_of_the_seventh_question.value}}</span>
                <p>{{review.q_nine}}</p>
              </div>
              <div class="pro_bar"><div  :style="{width: review.q_nine*10 +'%'}" class="bar_inside"></div></div>
            </div><!-- progress_item -->
            <div v-if="settings.items.enable_eighth_question.value" class="progress_item">
              <div class="flex justify-between">
                <span>{{settings.items.the_text_of_the_eighth_question.value}}</span>
                <p>{{review.q_ten}}</p>
              </div>
              <div class="pro_bar"><div  :style="{width: review.q_ten*10 +'%'}" class="bar_inside"></div></div>
            </div><!-- progress_item -->

          </div><!-- evaluation_comment_bars -->
        </div><!-- comments_user -->
      </div><!-- progress_user -->
      <div class="pagination_area">
        <div  v-if="collection.length && paginator.totalPages > 1">
          <pagination
            :click-handler="getReviews"
            :container-class="'pagination  w-full flex justify-center'"
            :first-button-text="__('First')"
            :first-last-button="true"
            :last-button-text="__('Last')"
            :value="paginator.currentPage"
            :margin-pages="2"
            :next-class="'page-item'"
            :next-link-class="'page-link'"
            :next-text="__('Next')"
            :page-class="'page-item'"
            :page-count="paginator.lastPage"
            :page-link-class="'page-link'"
            :page-range="3"
            :prev-class="'page-item'"
            :prev-link-class="'page-link'"
            :prev-text="__('Previous')"
          >
          </pagination>
          <div class="w-full flex justify-between mt-4 mb-2">
            <p>{{__('Results')}}  : {{__('From')}} ( {{paginator.from}} ) - {{__('To')}}  ( {{paginator.to}} )</p>
            <p>{{__('Count')}}  : {{paginator.totalPages}}</p>
          </div>
        </div>
        <div class="progress_user rounded-lg" v-else>
          <div class="text-center mt-10 mb-10 text-gray-700">{{__('No Reviews Found')}}</div>
        </div>
      </div><!-- pagination_area -->

    </div><!-- reviews_users -->

    <!-- Approve Review Modal -->
    <approve-review  />

    <!-- Edit Review Modal -->
    <edit-review />

  </div><!-- customer_reviews_page -->
</template>

<script>
    import DatePickerFrom from './DatePickerFrom';
    import DatePickerTo from './DatePickerTo';
    import Pagination from './Pagination';
    import Loading from 'vue-loading-overlay';
    import 'vue-loading-overlay/dist/vue-loading.css';
    import ApproveReview from './ApproveReview';
    import EditReview from './EditReview';
    export default {
        name: 'Customers Rerviews',
        components: {
            Loading,
            Pagination,
            ApproveReview,
            EditReview,
            DatePickerFrom,
            DatePickerTo
        },
        data() {
            return {
                crumbs : [],
                settings: [],
                total: null,
                isLoading : false,
                collection : [],
                paginator : {},
                locale : null,
                dateFrom:null,
                dateTo:null,
                rating_status : 0,
                shared_team_id : window.ALLOWED_TEAM_TO_GET_ALL_TEAMS_REVIEWS,
                current_team_id : Nova.config.user.current_team_id,
                teams_data : [],
                team_id_filter : null,
                getAllReviews : 0
            }
        },
        methods: {

            handleApproveClick(id){
              Nova.$emit('open-approve-modal' , id);
            },
            handleEditClick(id){
                Nova.$emit('open-edit-modal' , id);
            },
            dateFormat(date) {
                date = moment(date);

                return Nova.app.__(date.format('dddd')) + ' ' + date.format('YYYY/MM/DD h:mm a');
            },
            resetFilters() {
                Nova.$emit('reset-dates') ;
                this.dateFrom = null;
                this.dateTo = null;
                this.status = 0;
                this.team_id_filter = null;
                this.getReviews();
            },
            getSettings() {
                Nova.request().get('/nova-vendor/settings/get/ratings')
                .then(response => {
                    this.settings = response.data
                }).catch(error => {
                });
            },
            getTotal() {

              if(this.shared_team_id == Nova.config.user.current_team_id){
                this.getAllReviews = 1;
              }
                Nova.request().get(`/nova-vendor/customer-reviews/get-total?all_reviews=${this.getAllReviews}`)
                .then(response => {
                    this.total = response.data
                }).catch(error => {
                });
            },
            getReviews(page = 1){
              if(this.shared_team_id == Nova.config.user.current_team_id){
                this.getAllReviews = 1;
              }
                this.isLoading = true;
                Nova.request().get(`/nova-vendor/customer-reviews/get-reviews?page=${page}&dateFrom=${this.dateFrom}&dateTo=${this.dateTo}&status=${this.rating_status}&team_id_filter=${this.team_id_filter}&all_reviews=${this.getAllReviews}`)
                .then((res) => {
                    this.collection = res.data.data
                    this.paginator = {
                        currentPage : res.data.meta.current_page ,
                        lastPage : res.data.meta.last_page ,
                        from : res.data.meta.from,
                        to : res.data.meta.to,
                        totalPages : res.data.meta.total,
                        pathPage : res.data.meta.path + '?page=',
                        firstPageUrl : res.data.links.first ,
                        lastPageUrl : res.data.links.last ,
                        nextPageUrl : res.data.links.next ,
                        prevPageUrl : res.data.links.prev,
                    };
                    this.isLoading = false;
                })
            },
            getTeams(){
              axios.get(`/nova-vendor/customer-reviews/get-teams`)
                .then( response  => {
                    this.teams_data = response.data;
                })
            },
        },
        computed: {
            dateValues() {
                const { dateFrom, dateTo } = this
                return {
                    dateFrom,
                    dateTo
                }
            }
        },
        watch:{
            dateValues: {
                handler: function (val) {

                    if ((val.dateFrom != null && val.dateFrom != '') && (val.dateTo != null && val.dateTo != '')) {
                        this.getReviews();
                    }

                },
                deep: true
            },
        },
        beforeDestroy() {
          Nova.$off('open-edit-modal');
          Nova.$off('open-approve-modal');
          Nova.$off('reset-dates');
        },
        mounted() {
            this.crumbs = [
                {
                    text: 'Home',
                    to: '/dashboards/main',
                },
                {
                    text: 'Evaluations',
                    to: '#',
                }
            ];
            this.getReviews();

            Nova.$on('review-approved' , () => {
               this.getReviews();
               this.getTotal();
            });

            Nova.$on('review-updated' , () => {
               this.getReviews();
            });

            Nova.$on('to-change' , (val) => {
                this.dateTo = val ;
            });
            Nova.$on('from-change' , (val) => {
                this.dateFrom = val ;
            });


            var current_team_id = Nova.config.user.current_team_id;
            if(this.shared_team_id == current_team_id){
              this.getTeams();
            }
        },
        created() {
            this.locale = Nova.config.local;
            this.getSettings();
            this.getTotal();
        }
    }
</script>


<style scoped lang="scss">
  #customer_reviews_page {
    .filter_area {
      display: flex;
      align-items: center;
      flex-wrap: wrap;
      justify-content: flex-start;
      background: #fff;
      border-radius: 5px;
      padding: 10px 0;
      margin: 0 auto 15px;
      border: 1px solid #ddd;
      .item {
        width: 25%;
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
    .totla_reviews_block {
      background: #fff;
      border-radius: 4px;
      border: 1px solid #ddd;
      margin: 0 auto 15px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      flex-wrap: wrap;
      .col_1 {
        padding: 15px;
        min-width: 33.333%;
        display: flex;
        align-items: center;
        justify-content: flex-start;
        flex-direction: column;
        @media (min-width: 320px) and (max-width: 767px) {
          width: 100%;
        }
        @media (min-width: 768px) and (max-width: 991px) {
          width: 50%;
        }
        .evaluation_number {
          background: #4099de;
          border-radius: 4px;
          padding: 10px;
          text-align: center;
          line-height: 55px;
          color: #fff;
          font-size: 45px;
          width: 100%;
          margin: 0 auto 10px;
        } /* evaluation_number */
        .evaluation_txt {
          display: flex;
          align-items: center;
          justify-content: space-between;
          width: 100%;
           span {
            display: block;
            font-size: 20px;
            color: #000;
          } /* span */
          p {
            display: block;
            font-size: 17px;
            color: #AAAAAA;
          } /* p */
        } /* evaluation_txt */
      } /* col_1 */
      .col_2 {
        padding: 15px;
        min-width: 33.333%;
        @media (min-width: 320px) and (max-width: 767px) {
          width: 100%;
        }
        @media (min-width: 768px) and (max-width: 991px) {
          width: 50%;
        }
        .progress_item {
          margin: 0 auto 15px;
          display: flex;
          align-items: center;
          justify-content: space-between;
          span {
            display: block;
            line-height: 25px;
            color: #000;
            font-size: 15px;
          } /* span */
          .progress_bar {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            flex-wrap: nowrap;
            flex-direction: row-reverse;
            p {
              display: block;
              min-width: 35px;
              color: #4099de;
              font-size: 15px;
              line-height: 1;
              text-align: left;
              font-weight: bold;
            } /* p */
            .pro_bar {
              margin: 0 0 0 5px;
              height: 4px;
              box-shadow: 0 0 5px 1px #C2CDD5 inset;
              border-radius: 100px;
              background: #E5F0F8;
              position: relative;
              overflow: hidden;
              width: 140px;
              .bar_inside {
                background: #4099de;
                border-radius: 100px;
                height: 100%;
              } /* bar_inside */
            } /* pro_bar */
          } /* progress_bar */
          &:last-of-type {
            margin: 0 auto;
          } /* last-of-type */
        }/* progress_item */
      } /* col_2 */
    } /* totla_reviews_block */
    .reviews_users {
      background: #fff;
      border-radius: 4px;
      margin: 0 auto 15px;
      border: 1px solid #ddd;
      position: relative;
      .progress_user {
        margin: 10px 10px 15px;
        background: #FCFCFC;
        border: 1px solid #EAEAEA;
        .all_details {
          display: flex;
          flex-wrap: nowrap;
          @media (min-width: 320px) and (max-width: 991px) {
            flex-wrap: wrap;
          } /* media */
          .details {
            padding: 10px 15px;
            width: 100%;
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
              color: #777777;
              margin: 0 auto 5px;
            } /* span */
            p {
              display: block;
              font-size: 15px;
              color: #000000;
              &.pending {
                color: #ed8936;
              } /* pending */
              &.approved {
                color: #54bf45;
              } /* approved */
              a {
                color: #4099DE;
                &:hover {
                  color: #2C85CA;
                } /* hover */
              } /* a */
            } /* p */
            button {
              display: inline-block;
              margin: 0 3px 10px !important;
              width: 25px;
              height: 25px;
              text-align: center;
              line-height: 25px;
              outline: none;
              svg {
                display: block;
                width: 20px;
                height: 25px;
                margin: 0 auto;
              } /* svg */
            } /* button */
          } /* details */
        } /* all_details */
        .comments_user {
          display: flex;
          flex-wrap: nowrap;
          @media (min-width: 320px) and (max-width: 991px) {
            flex-wrap: wrap;
          } /* media */
          .evaluation_comment {
            padding: 5px 15px 10px;
            width: 66.6666%;
            @media (min-width: 320px) and (max-width: 767px) {
              width: 1000%;
            } /* media */
            @media (min-width: 768px) and (max-width: 991px) {
              width: 50%;
            } /* media */
            .comment_item {
              margin: 0 auto 15px;
              span {
                display: block;
                font-size: 15px;
                color: #777777;
                margin: 0 auto 5px;
              } /* span */
              p {
                display: block;
                font-size: 14px;
                color: #000000;
              } /* p */
              &:last-of-type {
                margin: 0 auto;
              } /* last-of-type */
            } /* comment_item */
          } /* evaluation_comment */
          .evaluation_comment_bars {
            padding: 5px 15px 10px;
            width: 33.3333%;
            @media (min-width: 320px) and (max-width: 767px) {
              width: 1000%;
            } /* media */
            @media (min-width: 768px) and (max-width: 991px) {
              width: 50%;
            } /* media */
            .progress_item {
              margin: 5px 0 0 0;
              span {
                display: block;
                line-height: 25px;
                color: #000;
                font-size: 15px;
              } /* span */
              p {
                display: block;
                line-height: 25px;
                color: #4099de;
                font-size: 15px;
                font-weight: bold;
              } /* p */
            } /* progress_item */
            .pro_bar {
              margin: 3px auto 0;
              height: 4px;
              box-shadow: 0 0 5px 1px #C2CDD5 inset;
              border-radius: 100px;
              background: #E5F0F8;
              position: relative;
              overflow: hidden;
              .bar_inside {
                background: #4099de;
                border-radius: 100px;
                height: 100%;
              } /* bar_inside */
            } /* pro_bar */
          } /* evaluation_comment_bars */
        } /* comments_user */
        &:last-child {
          margin: 10px;
        } /* last-child */
      } /* progress_user */
      .pagination_area {
        padding: 15px 15px 5px;
      } /* pagination_area */
    } /* reviews_users */
  } /* customer_reviews_page */
</style>
