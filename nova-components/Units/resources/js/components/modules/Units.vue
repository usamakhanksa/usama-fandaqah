<template>
  <div id="all_units_page">
    <div class="title">
      <span>{{__('Units')}}<p>( {{paginator ? paginator.totalResults : 0}} )</p></span>
      <a
          type="button"
          target="_blank"
          class="print_button"
          href="/home/print/units-report"
        ></a>

      <!-- Print Form -->
    <form
      id="reservations"
      target="_blank"
      method="post"
      style="display: none"
      action="/home/print/units-report"
    >
      <input
        type="hidden"
        :value="JSON.stringify(units)"
        name="ids"
      />
      <input
        type="hidden"
        :value="JSON.stringify(statistics)"
        name="statistics"
      />
      <input
        type="hidden"
        :value="JSON.stringify(canShowStatistics)"
        name="canShowStatistics"
      />
    </form>   
     <!-- <a onclick="Nova.app.$router.push('/resources/units/new?viaResource=&viaResourceId=&viaRelationship=')">{{__('Add Unit')}}</a> -->
    </div><!-- title -->
    <div class="table_content relative">
      <loading :active.sync="isLoading" :can-cancel="true" :loader="'spinner'" :color="'#7e7d7f'" :is-full-page="false"></loading>
      <div class="table_responsive">
        <table>
          <thead>
            <tr>
              <th>{{ __('Unit Number') }}</th>
              <th>{{ __('Name') }}</th>
              <th>{{ __('Category') }}</th>
              <th>{{ __('Enabled') }}</th>
              <th>{{ __('Unit Status') }}</th>
              <th>{{ __('Available To Sync') }}</th>
              <th>{{ __('Main Image') }}</th>
              <th>{{ __('Actions') }}</th>
            </tr>
          </thead>
          <tbody>
            <template v-if="collection.length">
              <tr v-for="(unit , i) in collection" :key="i">
                <td>{{unit.unit_number}}</td>
                <td>{{unit.name}}</td>
                <td><router-link :to="`/resources/unit-categories/${unit.unit_category_id}`">{{unit.unit_category_name}}</router-link></td>
                <td>
                  <span v-if="unit.enabled" class="enabled">{{__('Active')}}</span>
                  <span v-else class="not_enabled">{{__('Inactive')}}</span>
                </td>
                <td>
                  <span v-if="unit.status === 1"  class="enabled">{{__('Available')}}</span>
                  <span v-else-if="unit.status === 2"  class="cleaning">{{__('Under Cleaning')}}</span>
                  <span v-else  class="maintenance">{{__('Under Maintenance')}}</span>
                </td>
                <td>
                  <span v-if="unit.available_to_sync" class="enabled">{{ __('Available') }}</span>
                  <span v-else class="not_enabled">{{ __('Not Available') }}</span>
                </td>
                <td>
                  <div v-if="env === 'local'">
                    <img  :src="baseUrl + unit.main_image" class="rounded-full w-8 h-8" style="object-fit: cover;">
                  </div>
                  <div v-else>
                    <img  :src="unit.main_image != null ? unit.main_image : baseUrl + unit.main_image" class="rounded-full w-8 h-8" style="object-fit: cover;">
                  </div>
                </td>
                <td>
                  <div class="action">
                    <router-link :to="`/resources/units/${unit.id}`" :title="__('View')">
                      <svg xmlns="http://www.w3.org/2000/svg" width="22" height="18" viewBox="0 0 22 16" aria-labelledby="view" role="presentation" class="fill-current"><path d="M16.56 13.66a8 8 0 0 1-11.32 0L.3 8.7a1 1 0 0 1 0-1.42l4.95-4.95a8 8 0 0 1 11.32 0l4.95 4.95a1 1 0 0 1 0 1.42l-4.95 4.95-.01.01zm-9.9-1.42a6 6 0 0 0 8.48 0L19.38 8l-4.24-4.24a6 6 0 0 0-8.48 0L2.4 8l4.25 4.24h.01zM10.9 12a4 4 0 1 1 0-8 4 4 0 0 1 0 8zm0-2a2 2 0 1 0 0-4 2 2 0 0 0 0 4z"></path></svg>
                    </router-link>
                    <router-link :to="`/resources/units/${unit.id}/edit?viaResource=&viaResourceId=&viaRelationship=`" :title="__('Edit')">
                      <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" aria-labelledby="edit" role="presentation" class="fill-current"><path d="M4.3 10.3l10-10a1 1 0 0 1 1.4 0l4 4a1 1 0 0 1 0 1.4l-10 10a1 1 0 0 1-.7.3H5a1 1 0 0 1-1-1v-4a1 1 0 0 1 .3-.7zM6 14h2.59l9-9L15 2.41l-9 9V14zm10-2a1 1 0 0 1 2 0v6a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V4c0-1.1.9-2 2-2h6a1 1 0 1 1 0 2H2v14h14v-6z"></path></svg>
                    </router-link>
                    <button type="button" :title="__('Delete')" @click="initDeleteConfirm(unit.id)" v-if="unit.status == 1 && unit.reservations_count == 0">
                      <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" aria-labelledby="delete" role="presentation" class="fill-current"><path fill-rule="nonzero" d="M6 4V2a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2h5a1 1 0 0 1 0 2h-1v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6H1a1 1 0 1 1 0-2h5zM4 6v12h12V6H4zm8-2V2H8v2h4zM8 8a1 1 0 0 1 1 1v6a1 1 0 0 1-2 0V9a1 1 0 0 1 1-1zm4 0a1 1 0 0 1 1 1v6a1 1 0 0 1-2 0V9a1 1 0 0 1 1-1z"></path></svg>
                    </button>
                    <a
                      v-if="enable_guest_qrcode"
                      :href="`/qr/unit/${unit.id}/generate`"
                      target="_blank"
                      title="Guest QRCode"  
                    >
                      <svg
                        class="qr-icon"
                        height="28"
                        fill="#b3b9bf"
                        viewBox="0 0 32 32"
                        xmlns="http://www.w3.org/2000/svg"
                        style="transition: fill 0.3s ease;"
                      >
                        <path d="m5 5v8h2v2h2v-2h4v-8zm8 8v2h2v2h-4v2h-6v8h8v-8h6v-2h-2v-2h4v-2h2v2h2v-2h2v-8h-8v8zm12 2v2h2v-2zm0 2h-2v2h2zm0 2v2h2v-2zm0 2h-2v-2h-2v2h-5v6h2v-4h4v2h2v-2h1zm-3 4h-2v2h2zm1-8v-2h-2v2zm-12 0v-2h-2v2zm-4-2h-2v2h2zm8-10v4h-1v2h1v1h2v-3h1v-2h-1v-2zm-8 2h4v4h-4zm14 0h4v4h-4zm-13 1v2h2v-2zm14 0v2h2v-2zm-15 13h4v4h-4zm1 1v2h2v-2zm17 3v2h2v-2z"/>
                      </svg>
                    </a>

                  
                  </div><!-- action -->
                </td>
              </tr>
            </template>
            <template v-else>
              <tr>
                <td colspan="7">{{__('No Units Matches Your Criteria')}}</td>
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
          :click-handler="getData"
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
      </div><!-- flex -->
    </div><!-- table_content -->
    <delete-confirm ref="deleteShared" :id="targetToDelete" :targetModel="model" />
  </div><!-- all_units_page -->
</template>

<script>
   import Pagination from '../Pagination';
   import Loading from 'vue-loading-overlay';
   import DeleteConfirm from '../DeleteConfirm';
    export default {
       name : 'units',
       components : {
          Pagination,
          Loading,
          DeleteConfirm
       },
       data(){
          return {
             collection : [],
             paginator : {},
             baseUrl : null,
             isLoading : false,
             targetToDelete : null,
             model : 'Unit',
             env : null,
             enable_guest_qrcode : Nova.app.currentTeam.enable_unit_qrcode
          }
       },
       methods: {
          getData(page=1){
               this.isLoading = true;
               let url  = `/nova-vendor/units/get-resources/${this.model}?page=`+ page;
               axios.get(url)
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

                     this.baseUrl = response.data.general.url;
                     this.env = response.data.general.env;
                     this.isLoading = false;
                  })
            },
          initDeleteConfirm(id){
             this.targetToDelete = id;
             this.$refs.deleteShared.$refs.deleteConfirm.open();
          },
          printReport(){
            alert('www')
          }
        },
      mounted(){

          Nova.$on('Unit-destroyed' , () => {
             this.getData(1);
          });
      },
       created(){
          this.getData();
       }

    }
</script>

<style lang="scss" scoped>

.qr-icon:hover {
  fill: #2196f3; /* Blue on hover */
  cursor: pointer;
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

  a.print_button {
        display: block;
        background-color : #f7fafc !important;
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
</style>
