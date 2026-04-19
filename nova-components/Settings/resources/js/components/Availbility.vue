<template>
  <div class="category-table-container p-4 bg-gray-100 rounded-lg shadow-md">
    <heading class="my-4 flex items-center">
      <router-link :to="{ name: 'channel-manager' }" class="back-icon mr-4">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M19 12H5M12 19l-7-7 7-7"/>
        </svg>
      </router-link>
      <h2 class="text-2xl font-bold">Manage Categories</h2>
    </heading>

    <div v-if="isLoading" class="loader-overlay">
        <div class="loader"></div>
      </div>

    <form @submit.prevent="pushAvailability" class="bg-white p-6 rounded-lg shadow-md">
      <div class="flex flex-col mb-4">
        <label class="text-gray-700 font-semibold mb-2" for="category">{{ __("Select Category") }}</label>
        <select id="category" v-model="selectedCategory" @change="fetchCategorySettings" class="border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
          <option v-for="category in categories" :key="category" :value="category">{{ category.name }}</option>
        </select>
      </div>

        <div class="flex flex-col mb-4">
          <label class="text-gray-600 mb-1" for="start_date">{{__("Start Date")}}</label>
          <input type="date" id="start_date" class="border border-gray-300 rounded px-3 py-2" v-model="day_start" :min="getCurrentDate()">
        </div>
        <div class="flex flex-col mb-4">
          <label class="text-gray-600 mb-1" for="end_date">{{__("End Date")}}</label>
          <input type="date" id="end_date" class="border border-gray-300 rounded px-3 py-2" v-model="day_end">
        </div>

        <div class="flex flex-col mb-4 relative">
            <label class="text-gray-600 mb-1" for="unit_count">{{__("Unit Count")}}</label>

            <!-- Input styled similar to others -->
            <input
              id="unit_count"
              type="number"
              class="border border-gray-300 rounded px-3 py-2 w-full"
              v-model="avalible_to_sync"
              :max="count"
              :min="0"
              @input="validateInput()"
              style="text-align: center;"
            />

            <!-- Display the count value below the input for better layout -->
            <div class="text-gray-500 mt-1 text-right">
              Available: {{ count }}
            </div>
          </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="flex flex-col mb-4 relative">
          <label class="text-gray-700 font-semibold mb-2" for="sunday_price">{{ __("Sunday Day Price") }}</label>
          <div class="flex items-center">
            <input type="number" id="sunday_price" class="border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 w-full" v-model="sunday_price" :readonly="sundayPriceReadOnly">
            <svg v-if="sunday_price" @click="applySundayPrice" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600 cursor-pointer ml-2" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M10 18a1 1 0 01-.7-.3l-7-7a1 1 0 111.4-1.4L9 15.59V2a1 1 0 012 0v13.59l5.3-5.3a1 1 0 111.4 1.4l-7 7a1 1 0 01-.7.31z" clip-rule="evenodd" />
            </svg>
          </div>
          <div class="mt-2">
            <input type="checkbox" v-model="sundayPriceReadOnly" @change="toggleUseBasePrice('sunday_price', 'sundayPriceReadOnly')" class="mr-2">
            <span>{{ __("Use Base price") }}</span>
          </div>
        </div>

        <div class="flex flex-col mb-4">
          <label class="text-gray-700 font-semibold mb-2" for="monday_price">{{ __("Monday Day Price") }}</label>
          <input type="number" id="monday_price" class="border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" v-model="monday_price" :readonly="MondayPriceReadOnly">
          <div class="mt-2">
            <input type="checkbox" v-model="MondayPriceReadOnly" @change="toggleUseBasePrice('monday_price', 'MondayPriceReadOnly')" class="mr-2">
            <span>{{ __("Use Base price") }}</span>
          </div>
        </div>

        <div class="flex flex-col mb-4">
          <label class="text-gray-700 font-semibold mb-2" for="tuesday_price">{{ __("Tuesday Day Price") }}</label>
          <input type="number" id="tuesday_price" class="border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" v-model="tuesday_price" :readonly="TuesdayPriceReadOnly">
          <div class="mt-2">
            <input type="checkbox" v-model="TuesdayPriceReadOnly" @change="toggleUseBasePrice('tuesday_price', 'TuesdayPriceReadOnly')" class="mr-2">
            <span>{{ __("Use Base price") }}</span>
          </div>
        </div>

        <div class="flex flex-col mb-4">
          <label class="text-gray-700 font-semibold mb-2" for="wednesday_price">{{ __("Wednesday Day Price") }}</label>
          <input type="number" id="wednesday_price" class="border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" v-model="wednesday_price" :readonly="WednesdayPriceReadOnly">
          <div class="mt-2">
            <input type="checkbox" v-model="WednesdayPriceReadOnly" @change="toggleUseBasePrice('wednesday_price', 'WednesdayPriceReadOnly')" class="mr-2">
            <span>{{ __("Use Base price") }}</span>
          </div>
        </div>

        <div class="flex flex-col mb-4">
          <label class="text-gray-700 font-semibold mb-2" for="thursday_price">{{ __("Thursday Day Price") }}</label>
          <input type="number" id="thursday_price" class="border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" v-model="thursday_price" :readonly="ThursdayPriceReadOnly">
          <div class="mt-2">
            <input type="checkbox" v-model="ThursdayPriceReadOnly" @change="toggleUseBasePrice('thursday_price', 'ThursdayPriceReadOnly')" class="mr-2">
            <span>{{ __("Use Base price") }}</span>
          </div>
        </div>

        <div class="flex flex-col mb-4">
          <label class="text-gray-700 font-semibold mb-2" for="friday_price">{{ __("Friday Day Price") }}</label>
          <input type="number" id="friday_price" class="border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" v-model="friday_price" :readonly="FridayPriceReadOnly">
          <div class="mt-2">
            <input type="checkbox" v-model="FridayPriceReadOnly" @change="toggleUseBasePrice('friday_price', 'FridayPriceReadOnly')" class="mr-2">
            <span>{{ __("Use Base price") }}</span>
          </div>
        </div>
      </div>

        <div class="flex flex-col mb-4">
          <label class="text-gray-700 font-semibold mb-2" for="saturday">{{ __("Saturday Day Price") }}</label>
          <input type="number" id="saturday_price" class="border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" v-model="saturday_price" :readonly="SaturdayPriceReadOnly">
          <div class="mt-2">
            <input type="checkbox" v-model="SaturdayPriceReadOnly" @change="toggleUseBasePrice('saturday_price', 'SaturdayPriceReadOnly')" class="mr-2">
            <span>{{ __("Use Base price") }}</span>
          </div>
      </div>

      <div class="flex flex-col mb-4">
        <label class="text-gray-700 font-semibold mb-2" for="virtual_room">{{ __("Virtual Room") }}</label>
        <input type="number" id="virtual_room" class="border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" v-model="virtual_room">
      </div>

      <div class="flex flex-col mb-4">
        <label class="text-gray-700 font-semibold mb-2">{{ __("Second Rate Plan Formula") }}</label>
        <div class="flex">
          <select v-model="second_rateplan_operator" class="border border-gray-300 rounded-l px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="+">+</option>
            <option value="*">*</option>
          </select>
          <input type="number" class="border border-gray-300 rounded-r px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" v-model.number="second_rateplan_value"
              step="0.01"

          >
        </div>
      </div>

      <div class="flex justify-end" >
        <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
          {{ __("Push") }}
        </button>
      </div>
    </form>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  data() {
    return {
      team_id: Nova.app.currentTeam.id,
      categories: [],
      local: Nova.config.local,
      rooms: [],
      avalible_to_sync: 0,
        count: 0,
      virtual_room: 0,
      day_start: this.getCurrentDate(),
      day_end: null,
      selectedCategory: null,
      category: null,
      saturday_price: null,
      sunday_price: null,
      monday_price: null,
      tuesday_price: null,
      wednesday_price: null,
      thursday_price: null,
      friday_price: null,
      static_price: false,
      SaturdayPriceReadOnly: true,
      sundayPriceReadOnly: true,
      MondayPriceReadOnly: true,
      TuesdayPriceReadOnly: true,
      WednesdayPriceReadOnly: true,
      ThursdayPriceReadOnly: true,
      FridayPriceReadOnly: true,
      second_rateplan_operator: '+',
      second_rateplan_value: 0,
      isLoading: false,  // Flag to show or hide the loader

    };
  },
  mounted() {
    this.fetchCategories();
    this.getRoomListing();
  },
  methods: {
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
    fetchCategories() {
      axios.post(`/nova-vendor/settings/categories`, { team_id: this.team_id, lang: this.local })
        .then(response => {
          this.categories = response.data;
        })
        .catch(error => {
          console.error('Error fetching categories:', error);
        });
    },
    getUnits(){
    axios.get(`/nova-vendor/settings/get-units`, { params: { team_id: this.team_id, category_id: this.selectedCategory } })
      .then(response => {
        this.avalible_to_sync = response.data.synced_units;
        this.count = response.data.units_count;
      })
      .catch(error => {
        console.error('Error fetching units:', error);
      });
    },
    validateInput() {
      if (this.avalible_to_sync > this.count) {
        this.avalible_to_sync = this.count;
      } else if (this.avalible_to_sync < 0) {
        this.avalible_to_sync = 0;
      }
    },

    fetchCategorySettings() {

        // call function get units from the server
        this.getUnits();

      axios.get(`/nova-vendor/settings/get`, { params: { category_id: this.selectedCategory } })
        .then(response => {
          const data = response.data.data;
          if (response.data.status === "success") {
            this.sunday_price = data.sunday_price;
            this.monday_price = data.monday_price;
            this.tuesday_price = data.tuesday_price;
            this.wednesday_price = data.wednesday_price;
            this.thursday_price = data.thursday_price;
            this.saturday_price = data.saturday_price;
            this.friday_price = data.friday_price;
            this.virtual_room = data.virtual_rooms;
            this.second_rateplan_operator = data.second_rateplan_operator;
            this.second_rateplan_value = data.second_rateplan_value;
          } else {
            this.sunday_price = null;
            this.monday_price = null;
            this.tuesday_price = null;
            this.wednesday_price = null;
            this.thursday_price = null;
            this.friday_price = null;
            this.saturday_price = null;
            this.virtual_room = 0;
            this.second_rateplan_operator = '+';
            this.second_rateplan_value = 0;
          }

                 if (this.sunday_price === null) {
                this.sundayPriceReadOnly = true;
            }else{
                this.sundayPriceReadOnly = false;
            }

            if (this.monday_price === null) {
                this.MondayPriceReadOnly = true;
            }else{
                this.MondayPriceReadOnly = false;
            }
            if (this.tuesday_price === null) {
                this.TuesdayPriceReadOnly = true;
            }else{
                this.TuesdayPriceReadOnly = false;
            }
            if (this.wednesday_price === null) {
                this.WednesdayPriceReadOnly = true;
            }else{
                this.WednesdayPriceReadOnly = false;
            }
            if (this.thursday_price === null) {
                this.ThursdayPriceReadOnly = true;
            }else{
                this.ThursdayPriceReadOnly = false;
            }
            if (this.friday_price === null) {
                this.FridayPriceReadOnly = true;
            }else{
                this.FridayPriceReadOnly = false;
            }
            if (this.saturday_price === null) {
                this.SaturdayPriceReadOnly = true;
            }else{
                this.SaturdayPriceReadOnly = false;
            }
            this.category = this.selectedCategory;
        })
        .catch(error => {
          console.error('Error fetching category settings:', error);
        });
    },
    updateReadOnlyState(field) {
      const readOnlyField = field.charAt(0).toUpperCase() + field.slice(1) + 'ReadOnly';
      this[readOnlyField] = this[field] === null;
    },
    toggleUseBasePrice(field, readOnlyField) {
      if (this[readOnlyField]) {
        this[field] = null;
      }
      this.updateReadOnlyState(field);
    },
    applySundayPrice() {
      this.monday_price = this.sunday_price;
      this.tuesday_price = this.sunday_price;
      this.wednesday_price = this.sunday_price;
      this.thursday_price = this.sunday_price;
      this.friday_price = this.sunday_price;
      this.saturday_price = this.sunday_price;

      this.sundayPriceReadOnly = false;
        this.MondayPriceReadOnly = false;
        this.TuesdayPriceReadOnly = false;
        this.WednesdayPriceReadOnly = false;
        this.ThursdayPriceReadOnly = false;
        this.FridayPriceReadOnly = false;
        this.SaturdayPriceReadOnly = false;

    },
    pushAvailability() {
        this.isLoading = true;  // Show loader

      let prices = {
        sunday_price: this.sunday_price,
        monday_price: this.monday_price,
        tuesday_price: this.tuesday_price,
        wednesday_price: this.wednesday_price,
        thursday_price: this.thursday_price,
        friday_price: this.friday_price,
        saturday_price: this.saturday_price,
      };

      this.static_price = Object.values(prices).some(price => price !== null);

      axios.post('/nova-vendor/settings/push-rooms', {
        avalible_to_sync: this.avalible_to_sync,
        category: this.category,
        start_date: this.day_start,
        end_date: this.day_end,
        prices: prices,
        static_price: this.static_price,
        virtual_room: this.virtual_room,
        second_rateplan: {
          operator: this.second_rateplan_operator,
          value: this.second_rateplan_value,
        },
      }).then(response => {
        this.isLoading = false;  // Hide loader when done

          this.$toasted.success(this.__('Pushing availability process went Successfully'), { duration: 3000 });

      }).catch(error => {
        this.isLoading = false;  // Hide loader when done

        console.error('Error pushing availability:', error);
      });
    },
  },
};
</script>

<style scoped>
.back-icon {
  display: inline-block;
  margin-right: 10px;
  vertical-align: middle;
}

.back-icon svg {
  width: 24px;
  height: 24px;
  stroke: #000;
}

.category-table-container {
  padding: 20px;
}

button {
  background-color: #4CAF50;
  border: none;
  color: white;
  padding: 8px 12px;
  text-align: center;
  text-decoration: none;
  display: inline-block;
  font-size: 14px;
  border-radius: 4px;
  cursor: pointer;
}

button:hover {
  background-color: #45a049;
}

.flex {
  display: flex;
}

.flex-col {
  flex-direction: column;
}
.loader-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    display: flex;
    justify-content: center;
    align-items: center;
    background: rgba(0, 0, 0, 0.5);
    z-index: 9999;
  }

  .loader {
    border: 16px solid #f3f3f3; /* Light grey */
    border-top: 16px solid #3498db; /* Blue */
    border-radius: 50%;
    width: 120px;
    height: 120px;
    animation: spin 2s linear infinite;
  }

  @keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
  }

.mb-4 {
  margin-bottom: 1rem;
}

.text-gray-700 {
  color: #4a5568;
}

.border {
  border-width: 1px;
}

.border-gray-300 {
  border-color: #e2e8f0;
}

.rounded {
  border-radius: 0.375rem;
}

.px-3 {
  padding-left: 0.75rem;
  padding-right: 0.75rem;
}

.py-2 {
  padding-top: 0.5rem;
  padding-bottom: 0.5rem;
}

.bg-blue-500 {
  background-color: #4299e1;
}

.hover\:bg-blue-700:hover {
  background-color: #2b6cb0;
}

.text-white {
  color: #fff;
}

.font-bold {
  font-weight: 700;
}

.rounded-l {
  border-top-left-radius: 0.375rem;
  border-bottom-left-radius: 0.375rem;
}

.rounded-r {
  border-top-right-radius: 0.375rem;
  border-bottom-right-radius: 0.375rem;
}

.focus\:outline-none:focus {
  outline: none;
}

.focus\:ring-2:focus {
  box-shadow: 0 0 0 2px rgba(66, 153, 225, 0.6);
}

.focus\:ring-blue-500:focus {
  box-shadow: 0 0 0 2px rgba(66, 153, 225, 0.6);
}

.grid {
  display: grid;
}

.grid-cols-1 {
  grid-template-columns: repeat(1, minmax(0, 1fr));
}

.sm\:grid-cols-2 {
  grid-template-columns: repeat(2, minmax(0, 1fr));
}

.gap-4 {
  gap: 1rem;
}
</style>
