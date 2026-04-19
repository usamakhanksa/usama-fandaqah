<template>
  <div>
    <sweet-modal ref="shomoosLogModal" class="shomoos_log_modal" blocking :enable-mobile-fullscreen="false" overlay-theme="dark" :title="__('Shomoos Log')" @close="closeLog()">
      <div class="loading_area" v-show="loading"><loader /></div>
      <ol v-if="laravelData && laravelData.data && laravelData.data.length">
        <li v-for="log in laravelData.data" :class="log.class">
          <div class="col_right">
            <span>{{ __('Reservation Number') }} : <p><router-link :to="'/reservation/'+log.reservation.id">#{{ log.reservation.number }} </router-link></p></span>
            <span>{{ __(log.type) }}</span>
          </div><!-- col_right -->
          <div class="col_left">
            <time>{{ log.created_at }}</time>
            <span>{{ log.message }}</span>
          </div><!-- col_right -->
        </li>
      </ol>
      <paginationnew :data="laravelData" @pagination-change-page="getResults">
        <span slot="prev-nav">&laquo;</span>
        <span slot="next-nav">&raquo;</span>
      </paginationnew>
      <div v-if="loading == false && laravelData && laravelData.data && laravelData.data.length <= 0">
        <div class="w-full border border-gray-400 rounded bg-gray-300 text-gray-800 text-base p-3 my-3">{{__('No Data')}}</div>
      </div>
    </sweet-modal>
  </div>
</template>

<script>

    export default {
        name: "shomoos-log",
        data() {
            return {
                loading: false,
                laravelData: {},
                loading: true,
            }
        },
        methods: {
            openLog() {
                this.$refs.shomoosLogModal.open()
            },
            closeLog() {
                this.$refs.shomoosLogModal.close()
            },
            viewReservation(reservation) {
                this.$router.push({name: 'reservation', params: {
                        id: reservation.split(':')[1]}
                });
            },
            getResults(page = 1) {
                this.loading = true;
                Nova.request()
                    .get('/api/shomoos/team/'+Nova.app.currentTeam.id +'?page='+page).then(response => {
                    this.laravelData = response.data;
                    this.loading = false;
                })
                .catch(error => {
                    this.$toasted.error(error, {
                        duration: 3000
                    });
                    this.loading = false;
                });
            }
        },
        mounted() {
            this.getResults()
        }
    }
</script>

<style lang="scss">
.shomoos_log_modal {
  h2 {text-align: initial;}
  .sweet-content {
    display: block !important;
    max-height: 500px;
    overflow-y: auto;
    scrollbar-width: thin;
    scrollbar-color: #ccc #f5f5f5;
    &::-webkit-scrollbar {width: 6px;}
    &::-webkit-scrollbar-track {background: #f5f5f5;}
    &::-webkit-scrollbar-thumb {background: #ccc;}
    &::-webkit-scrollbar-thumb:window-inactive {background: #f5f5f5;}
  } /* sweet-content */
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
  ol {
    li {
      margin: 0 auto 10px;
      background: #fdfdfd;
      border-radius: 5px;
      padding: 5px;
      border: 1px solid #ddd;
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      span {
        display: block;
        font-size: 15px;
        margin: 0 0 5px;
        text-align: initial;
        width: 100%;
        &:last-child {margin: 0;}
        p {
          display: inline-block;
          a {
            display: block;
            font-size: inherit;
            font-family: inherit;
            font-weight: inherit;
            color: #000;
          } /* a */
        } /* p */
      } /* span */
      .col_right {
      } /* col_right */
      .col_left {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        align-self: stretch;
        width: 70%;
        @media (min-width: 320px) and (max-width: 767px) {
          width: 100%;
        } /* Mobile */
        span {
          display: flex;
          width: 100%;
          justify-content: flex-end;
          @media (min-width: 320px) and (max-width: 767px) {
            justify-content: flex-start;
          } /* Mobile */
        } /* span */
      } /* col_left */
      time {
        display: flex;
        justify-content: flex-end;
        width: 100%;
        @media (min-width: 320px) and (max-width: 767px) {
          justify-content: flex-start;
        } /* Mobile */
      } /* time */
      &.success {
        border-color: #c3e6cb;
        background: #d4edda;
        color: #155724;
      } /* success */
      &.danger {
        border-color: #f5c6cb;
        background: #f8d7da;
        color: #721c24;
      } /* danger */
    } /* li */
  } /* ol */
  ul.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    margin: 20px auto;
    flex-wrap: wrap;
    li { 
      a {
        position: relative;
        display: block;
        padding: .5rem .75rem;
        margin-right: -1px;
        line-height: 1.25;
        color: #007bff;
        background-color: #fff;
        border: 1px solid #dee2e6;
        font-size: 1rem;
      } /* a */
      &.active {
        a {
          background-color: #007bff;
          border-color: #006cf0;
          color: #ffffff;
        } /* a */
      } /* active */
      &:first-child { 
        a {
          margin-right: 0;
          border-top-right-radius: .25rem;
          border-bottom-right-radius: .25rem;
        } /* a */
      } /* first-child */
      &:last-child { 
        a {
          border-top-left-radius: .25rem;
          border-bottom-left-radius: .25rem;
        } /* a */
      } /* last-child */
    } /* li */
  } /* pagination */
} /* shomoos_log_modal */
</style>
