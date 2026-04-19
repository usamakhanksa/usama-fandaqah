<template>

  <div id="units_categories_page">
  <sweet-modal title="Select Date Range" ref="daterange" style=" background: rgb(255 255 255 / 0%);">
  <div class="flex flex-col p-4">

    <div class="flex flex-col mb-4">
      <label class="text-gray-600 mb-1" for="start_date">{{__("Start Date")}}</label>
      <input type="date" id="start_date" class="border border-gray-300 rounded px-3 py-2" v-model="day_start" :min="getCurrentDate()">
    </div>
    <div class="flex flex-col mb-4">
      <label class="text-gray-600 mb-1" for="end_date">{{__("End Date")}}</label>
      <input type="date" id="end_date" class="border border-gray-300 rounded px-3 py-2" v-model="day_end">
    </div>

<div class="flex flex-col mb-4">
  <label class="text-gray-600 mb-1" for="sunday_price">{{__("Sunday Day Price")}}</label>
  <input type="number" id="sunday_price" class="border border-gray-300 rounded px-3 py-2" v-model="sunday_price" :readonly="sundayPriceReadOnly" >
  <div>
  <input type="checkbox" v-model="sundayPriceReadOnly" @click="clearIfReadOnly('sunday_price', sundayPriceReadOnly)">
  <span >{{__("Use Base price")}}</span>
  </div>
</div>
<div class="flex flex-col mb-4">
  <label class="text-gray-600 mb-1" for="monday_price">{{__("Monday Day Price")}}</label>
  <input type="number" id="monday_price" class="border border-gray-300 rounded px-3 py-2" v-model="monday_price" :readonly="MondayPriceReadOnly">
  <div>
    <input type="checkbox" v-model="MondayPriceReadOnly" @click="clearIfReadOnly('monday_price', MondayPriceReadOnly)">
    <span >{{__("Use Base price")}}</span>
    </div>
</div>
<div class="flex flex-col mb-4">
  <label class="text-gray-600 mb-1" for="tuesday_price">{{__("Tuesday Day Price")}}</label>
  <input type="number" id="tuesday_price" class="border border-gray-300 rounded px-3 py-2" v-model="tuesday_price" :readonly="TuesdayPriceReadOnly">
  <div>
    <input type="checkbox" v-model="TuesdayPriceReadOnly" @click="clearIfReadOnly('tuesday_price', TuesdayPriceReadOnly)">
    <span >{{__("Use Base price")}}</span>
    </div>
</div>
<div class="flex flex-col mb-4">
  <label class="text-gray-600 mb-1" for="wednesday_price">{{__("Wednesday Day Price")}}</label>
  <input type="number" id="wednesday_price" class="border border-gray-300 rounded px-3 py-2" v-model="wednesday_price" :readonly="WednesdayPriceReadOnly">
    <div>
        <input type="checkbox" v-model="WednesdayPriceReadOnly" @click="clearIfReadOnly('wednesday_price', WednesdayPriceReadOnly)">
        <span >{{__("Use Base price")}}</span>
        </div>
</div>
<div class="flex flex-col mb-4">
  <label class="text-gray-600 mb-1" for="thursday_price">{{__("Thursday Day Price")}}</label>
  <input type="number" id="thursday_price" class="border border-gray-300 rounded px-3 py-2" v-model="thursday_price" :readonly="ThursdayPriceReadOnly">
    <div>
        <input type="checkbox" v-model="ThursdayPriceReadOnly" @click="clearIfReadOnly('thursday_price', ThursdayPriceReadOnly)">
        <span >{{__("Use Base price")}}</span>
        </div>
</div>
<div class="flex flex-col mb-4">
  <label class="text-gray-600 mb-1" for="friday_price">{{__("Friday Day Price")}}</label>
  <input type="number" id="friday_price" class="border border-gray-300 rounded px-3 py-2" v-model="friday_price" :readonly="FridayPriceReadOnly">
    <div>
        <input type="checkbox" v-model="FridayPriceReadOnly" @click="clearIfReadOnly('friday_price', FridayPriceReadOnly)">
        <span >{{__("Use Base price")}}</span>
        </div>
</div>
<div class="flex flex-col mb-4">
  <label class="text-gray-600 mb-1" for="virtual_room">{{__("virtual room")}}</label>
  <input type="number" id="virtual_room" class="border border-gray-300 rounded px-3 py-2" v-model="virtual_room">
</div>
    <div class="flex justify-end">
      <button @click="pushAvailability(category)" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
        {{__("Push")}}
      </button>
    </div>
  </div>
</sweet-modal>
    <div class="title">
      <span>{{ __('Unit Categories') }}<p>( {{ paginator? paginator.totalResults : 0}} )</p></span>
      <button @click="syncAll" style="padding:6px;" class="btn btn-default btn-primary btn-sync">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 50 50" width="20px" height="20px" class="fill-current"
          :class="spinLoading ? 'spin' : ''">
          <path
            d="M 25 5 C 14.351563 5 5.632813 13.378906 5.054688 23.890625 C 5.007813 24.609375 5.347656 25.296875 5.949219 25.695313 C 6.550781 26.089844 7.320313 26.132813 7.960938 25.804688 C 8.601563 25.476563 9.019531 24.828125 9.046875 24.109375 C 9.511719 15.675781 16.441406 9 25 9 C 29.585938 9 33.699219 10.925781 36.609375 14 L 34 14 C 33.277344 13.988281 32.609375 14.367188 32.246094 14.992188 C 31.878906 15.613281 31.878906 16.386719 32.246094 17.007813 C 32.609375 17.632813 33.277344 18.011719 34 18 L 40.261719 18 C 40.488281 18.039063 40.71875 18.039063 40.949219 18 L 44 18 L 44 8 C 44.007813 7.460938 43.796875 6.941406 43.414063 6.558594 C 43.03125 6.175781 42.511719 5.964844 41.96875 5.972656 C 40.867188 5.988281 39.984375 6.894531 40 8 L 40 11.777344 C 36.332031 7.621094 30.964844 5 25 5 Z M 43.03125 23.972656 C 41.925781 23.925781 40.996094 24.785156 40.953125 25.890625 C 40.488281 34.324219 33.558594 41 25 41 C 20.414063 41 16.304688 39.074219 13.390625 36 L 16 36 C 16.722656 36.011719 17.390625 35.632813 17.753906 35.007813 C 18.121094 34.386719 18.121094 33.613281 17.753906 32.992188 C 17.390625 32.367188 16.722656 31.988281 16 32 L 9.71875 32 C 9.507813 31.96875 9.296875 31.96875 9.085938 32 L 6 32 L 6 42 C 5.988281 42.722656 6.367188 43.390625 6.992188 43.753906 C 7.613281 44.121094 8.386719 44.121094 9.007813 43.753906 C 9.632813 43.390625 10.011719 42.722656 10 42 L 10 38.222656 C 13.667969 42.378906 19.035156 45 25 45 C 35.648438 45 44.367188 36.621094 44.945313 26.109375 C 44.984375 25.570313 44.800781 25.039063 44.441406 24.636719 C 44.078125 24.234375 43.570313 23.996094 43.03125 23.972656 Z" />
        </svg>
        <span>{{ __('Sync All') }}</span>
      </button>
    </div><!-- title -->
    <div class="table_content relative">
      <loading :active.sync="isLoading" :can-cancel="true" :loader="'spinner'" :color="'#7e7d7f'" :is-full-page="false">
      </loading>
      <div class="table_responsive">
        <table>
          <thead>
            <tr>
              <th>{{ __('Name') }}</th>
              <th>{{ __('Status') }}</th>
              <th>{{ __('Show in website') }}</th>
              <th>{{ __('Main Image') }}</th>
              <th>{{ __('Actions') }}</th>
            </tr>
          </thead>
          <tbody>
            <template v-if="collection.length">
              <tr v-for="(category, i) in collection" :key="i">
                <td>{{ category.name }}</td>
                <td>
                  <span v-if="category.status" class="enabled">{{ __('Active') }}</span>
                  <span v-else class="not_enabled">{{ __('Inactive') }}</span>
                </td>
                <td>
                  <span v-if="category.show_in_website" class="enabled">{{ __('Shown') }}</span>
                  <span v-else class="not_enabled">{{ __('Not Shown') }}</span>
                </td>
                <td>
                  <div v-if="env === 'local'">
                    <img :src="baseUrl + category.main_image" class="rounded-full w-8 h-8" style="object-fit: cover;">
                  </div>
                  <div v-else>
                    <img :src="category.main_image != null ? category.main_image : baseUrl + category.main_image"
                      class="rounded-full w-8 h-8" style="object-fit: cover;">
                  </div>
                </td>
                <td>
                  <div class="action">
                    <button
                      v-if="(staahRoomTypeListingIDS.length && staahRoomTypeListingIDS.includes(category.id)) && category.status == 1"
                      @click="selectDate(category)" v-tooltip.top-center="__('Push Availability')">

                      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20px" height="20px"
                        class="fill-current">
                        <path fill="none" d="M0 0h24v24H0V0z" />
                        <path d="M5 5h14v2H5z" opacity=".3" />
                        <path
                          d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V9h14v10zm0-12H5V5h14v2zm-2.51 4.53l-1.06-1.06-4.87 4.87-2.11-2.11-1.06 1.06 3.17 3.17z" />
                      </svg>
                    </button>
                    <router-link :to="`/resources/unit-categories/${category.id}`" :title="__('View')">
                      <svg xmlns="http://www.w3.org/2000/svg" width="22" height="18" viewBox="0 0 22 16"
                        aria-labelledby="view" role="presentation" class="fill-current">
                        <path
                          d="M16.56 13.66a8 8 0 0 1-11.32 0L.3 8.7a1 1 0 0 1 0-1.42l4.95-4.95a8 8 0 0 1 11.32 0l4.95 4.95a1 1 0 0 1 0 1.42l-4.95 4.95-.01.01zm-9.9-1.42a6 6 0 0 0 8.48 0L19.38 8l-4.24-4.24a6 6 0 0 0-8.48 0L2.4 8l4.25 4.24h.01zM10.9 12a4 4 0 1 1 0-8 4 4 0 0 1 0 8zm0-2a2 2 0 1 0 0-4 2 2 0 0 0 0 4z">
                        </path>
                      </svg>
                    </router-link>
                    <router-link
                      :to="`/resources/unit-categories/${category.id}/edit?viaResource=&viaResourceId=&viaRelationship=`"
                      :title="__('Edit')">
                      <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20"
                        aria-labelledby="edit" role="presentation" class="fill-current">
                        <path
                          d="M4.3 10.3l10-10a1 1 0 0 1 1.4 0l4 4a1 1 0 0 1 0 1.4l-10 10a1 1 0 0 1-.7.3H5a1 1 0 0 1-1-1v-4a1 1 0 0 1 .3-.7zM6 14h2.59l9-9L15 2.41l-9 9V14zm10-2a1 1 0 0 1 2 0v6a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V4c0-1.1.9-2 2-2h6a1 1 0 1 1 0 2H2v14h14v-6z">
                        </path>
                      </svg>
                    </router-link>
                    <button type="button" :title="__('Delete')" @click="initDeleteConfirm(category.id)"
                      v-if="category.units_count == 0">
                      <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20"
                        aria-labelledby="delete" role="presentation" class="fill-current">
                        <path fill-rule="nonzero"
                          d="M6 4V2a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2h5a1 1 0 0 1 0 2h-1v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6H1a1 1 0 1 1 0-2h5zM4 6v12h12V6H4zm8-2V2H8v2h4zM8 8a1 1 0 0 1 1 1v6a1 1 0 0 1-2 0V9a1 1 0 0 1 1-1zm4 0a1 1 0 0 1 1 1v6a1 1 0 0 1-2 0V9a1 1 0 0 1 1-1z">
                        </path>
                      </svg>
                    </button>
                  </div><!-- action -->
                </td>
              </tr>
            </template>
            <template v-else>
              <tr>
                <td colspan="7">{{ __('No Unit Categories Matches Your Criteria') }}</td>
              </tr>
            </template>
          </tbody>
        </table>
      </div><!-- table_responsive -->
      <!-- I will hide the pagination if there is only one page -->
      <div class="w-full flex flex-wrap mt-3 justify-center">
        <pagination v-if="paginator.lastPage > 1" :page-count="paginator.lastPage" :page-range="3" :margin-pages="2"
          :click-handler="getData" :value="paginator.currentPage" :prev-text="__('Previous')" :next-text="__('Next')"
          :container-class="'pagination  w-full flex justify-center'" :page-class="'page-item'"
          :page-link-class="'page-link'" :prev-link-class="'page-link'" :next-link-class="'page-link'"
          :prev-class="'page-item'" :next-class="'page-item'" :first-last-button="true" :first-button-text="__('First')"
          :last-button-text="__('Last')" />
      </div><!-- flex -->
    </div><!-- table_content -->
    <!-- Delete Confirm Component -->
    <delete-confirm ref="deleteShared" :id="targetToDelete" :targetModel="model" />
  </div><!-- units_categories_page -->
</template>

<script>
import Pagination from '../Pagination';
import Loading from 'vue-loading-overlay';
import DeleteConfirm from '../DeleteConfirm';
export default {
  name: 'unit-categories',
  components: {
    Pagination,
    Loading,
    DeleteConfirm
  },
  data() {
    return {
      collection: [],
      paginator: {},
      baseUrl: null,
      isLoading: false,
      spinLoading: false,
      targetToDelete: null,
      model: 'UnitCategory',
      env: null,
      rooms: [],
        // put day_start to now date
        day_start: this.getCurrentDate(),
        day_end: null,
        category: null,
        saturday_price: null,
        sunday_price: null,
        monday_price: null,
        tuesday_price: null,
        wednesday_price: null,
        thursday_price: null,
        friday_price: null,
        static_price: false,
        virtual_room: null,
        sundayPriceReadOnly: true,
        MondayPriceReadOnly: true,
        TuesdayPriceReadOnly: true,
        WednesdayPriceReadOnly: true,
        ThursdayPriceReadOnly: true,
        FridayPriceReadOnly: true,




    }
  },
  methods: {
    getData(page = 1) {
      this.isLoading = true;
      let url = `/nova-vendor/units/get-resources/${this.model}?page=` + page;
      axios.get(url)
        .then(response => {
          this.collection = response.data.data;
          this.paginator = {
            currentPage: response.data.meta.current_page,
            lastPage: response.data.meta.last_page,
            from: response.data.meta.from,
            to: response.data.meta.to,
            totalResults: response.data.meta.total,
            pathPage: response.data.meta.path + '?page=',
            firstPageUrl: response.data.links.first,
            lastPageUrl: response.data.links.last,
            nextPageUrl: response.data.links.next,
            prevPageUrl: response.data.links.prev,
          };

          this.baseUrl = response.data.general.url;
          this.env = response.data.general.env;
          this.isLoading = false;
        })
    },
    initDeleteConfirm(id) {
      this.targetToDelete = id;
      this.$refs.deleteShared.$refs.deleteConfirm.open();
    },
     clearIfReadOnly(field, event) {
    if (event == false) {
      this[field] = null;
      console.log(this.sunday_price)
    }
  },
    syncAll() {
      const self = this;
      this.spinLoading = true;
      if (!this.unitCategoriesWithRoomsToSell.length){
        this.$toasted.error('No new unit categories with available to sync units ready to be synced to staah, please make sure unit category has some units with available to sync option enabled', {
          duration: 5000
        });
        this.spinLoading = false
        return;
      }
      console.log(this.unitCategoriesWithRoomsToSell);
      axios.post(window.STAAH_MEDIATOR_API_URL + '/api/v1/roomType/create', {
        unit_categories: this.unitCategoriesWithRoomsToSell,
        team_id: Nova.app.user.current_team_id
      }).then(response => {
        if (response.data.Status == "Success") {
          this.$toasted.success(this.__('Syncing Process went Successfully'), {
            duration: 3000
          });
          this.getRoomListing();
          this.unitCategoriesWithRoomsToSell.forEach(function(cat){
            self.pushAvailability(cat)
          })
          // pushAvailability
          this.spinLoading = false
        } else {
          if (response.data.Errors) {
            if (response.data.Errors.length){
              response.data.Errors.forEach(function (error) {
                self.$toasted.error(error.ShortText, {
                  duration: 5000
                });
              })
            }else{
              self.$toasted.error(response.data.Errors.ShortText, {
                duration: 5000
              });
            }
          }
          this.spinLoading = false
        }
      })
      // this.spinLoading = false
    },
    getRoomListing() {
      this.isLoading = true;
      axios.post(window.STAAH_MEDIATOR_API_URL + '/api/v1/roomType/list', {
        team_id: Nova.app.user.current_team_id
      }).then(response => {
        if (response.data.rooms.length) {
          this.rooms = response.data.rooms
        } else {
          this.rooms = [];
        }
        this.isLoading = false;
      })
    },selectDate(category){
     // open ref daterange
     this.category = category;
     this.virtual_room = category.virtual_rooms;
    //  this.sunday_price = category.sunday_price;
     this.$refs.daterange.open();

    },
     getCurrentDate() {
      const today = new Date();
      const year = today.getFullYear();
      let month = today.getMonth() + 1;
      let day = today.getDate();

      if (month < 10) {
        month = '0' + month;
      }
      if (day < 10) {
        day = '0' + day;
      }

      return `${year}-${month}-${day}`;
    },
    pushAvailability(category) {
        let prices = {
            saturday_price: this.saturday_price,
            sunday_price: this.sunday_price,
            monday_price: this.monday_price,
            tuesday_price: this.tuesday_price,
            wednesday_price: this.wednesday_price,
            thursday_price: this.thursday_price,
            friday_price: this.friday_price
        }
        // if all prices are null then return
        if (!prices.saturday_price && !prices.sunday_price && !prices.monday_price && !prices.tuesday_price && !prices.wednesday_price && !prices.thursday_price && !prices.friday_price) {
             this.static_price = false;

        }else{
            this.static_price = true;
        }
        console.log(prices)

      axios.post('/nova-vendor/units/push-rooms', {
        category: this.category,
        start_date: this.day_start,
        end_date: this.day_end,
        prices: prices,
        static_price: this.static_price ,
        virtual_room: this.virtual_room

      }).then(response => {
        if (response.data.Status == "Success") {
          this.$toasted.success(this.__('Pushing availability process went Successfully'), {
            duration: 3000
          });

        } else {
          if (response.data.Errors) {
            if (response.data.Errors.length) {
              response.data.Errors.forEach(function (error) {
                self.$toasted.error(error.ShortText, {
                  duration: 5000
                });
              })
            } else {
              self.$toasted.error(response.data.Errors.ShortText, {
                duration: 5000
              });
            }

          }
        }

      })
          this.$refs.daterange.close();

    }
  },
  computed: {
    unitCategoriesWithRoomsToSell() {
      var activeCategories = this.collection.filter(category => category.status = 1);
      return activeCategories.filter(category => (category.rooms_to_sell >= 1 && !this.staahRoomTypeListingIDS.includes(category.id)));
    },
    staahRoomTypeListingIDS() {
      var ids = [];
      if (this.rooms.length) {
        this.rooms.forEach(function (room) {
          ids.push(parseInt(room.roomid))
        })
      }
      return ids;
    }
  },
  mounted() {
    this.getData(1);
    this.getRoomListing();
  }
}
</script>

<style lang="scss" scoped>
.btn-sync {
  padding: 6px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  width: 100px;
}

#units_categories_page {
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
    }

    /* p */
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
      }

      /* media */
      &:hover {
        background-color: #0071C9;
      }

      /* hover */
    }

    /* a */
  }

  /* title */
  .table_content {
    background: #fff;
    padding: 10px;

    .table_responsive {
      @media (min-width: 320px) and (max-width: 767px) {
        overflow: auto;
      }

      /* media */
      table {
        width: 100%;

        @media (min-width: 320px) and (max-width: 767px) {
          margin: 0 auto 15px;
        }

        /* media */
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
          }

          /* th */
        }

        /* thead */
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
              }

              /* hover */
            }

            /* a */
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
              }

              /* after */
              &.enabled {
                &::after {
                  background: green;
                }

                /* after */
              }

              /*enabled  */
              &.maintenance {
                &::after {
                  background: #aab8c0;
                }

                /* after */
              }

              /*maintenance  */
              &.cleaning {
                &::after {
                  background: #ff9100;
                }

                /* after */
              }

              /*cleaning  */
              &.not_enabled {
                &::after {
                  background: #ff0000;
                }

                /* after */
              }

              /*not_enabled  */
            }

            /* span */
            img {
              margin: 0 auto;
              display: block;
            }

            /* img */
            .action {
              display: flex;
              align-items: center;
              justify-content: center;
              flex-wrap: wrap;

              a,
              button {
                margin: 5px;
                color: #b3b9bf;

                svg {
                  display: inline-block;
                }

                /* svg */
                &:hover {
                  color: #4099de;
                }

                /* hover */
              }

              /* a */
            }

            /* action */
          }

          /* td */
        }

        /* tbody */
      }

      /* table */
    }

    /* table_responsive */
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
          }

          /* hover */
          &:focus {
            z-index: 2;
            outline: 0;
            box-shadow: 0 0 0 .2rem rgba(0, 123, 255, .25);
          }

          /* focus */
        }

        /* page-link */
        &:first-child {
          .page-link {
            margin-left: -1px;
            border-top-right-radius: .25rem;
            border-bottom-right-radius: .25rem;
          }

          /* page-link */
        }

        /* first-child */
        &:last-child {
          .page-link {
            border-top-left-radius: .25rem;
            border-bottom-left-radius: .25rem;
          }

          /* page-link */
        }

        /* first-child */
        &.active {
          .page-link {
            z-index: 1;
            color: #fff;
            background-color: #007bff;
            border-color: #007bff;
          }

          /* page-link */
        }

        /* active */
        &.disabled {
          .page-link {
            color: #6c757d;
            pointer-events: none;
            cursor: auto;
            background-color: #fff;
            border-color: #dee2e6;
          }

          /* page-link */
        }

        /* active */
      }

      /* page-item */
    }

    /* pagination */
  }

  /* table_content */
}

/* units_categories_page */
</style>
