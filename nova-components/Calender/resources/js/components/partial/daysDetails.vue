<template>
  <div>
    <div class="price_day_link" @click="open" :title="__('Details of the days price')">{{ __('Details of the days price')}}</div>
    <sweet-modal class="price_days_modal" :enable-mobile-fullscreen="false" :pulse-on-block="true" :title="__('Details of the days price')" overlay-theme="dark" ref="daysPricesDetailsModal">
      <loading :active.sync="isLoading" :is-full-page="false"></loading>
      <div v-if="reservation.rent_type == 1">
        <div :class="!hasPermission || dateHasInvoice(day.date) ? 'price_day_item disabled_input' : 'price_day_item'"  v-for="(day, index) in reservation.prices.days" :key="index">
          <div class="date-div">
            <svg fill="#ababab" width="30px" height="30px" viewBox="-2.4 -2.4 28.80 28.80" xmlns="http://www.w3.org/2000/svg" stroke="#ababab" stroke-width="0.00024000000000000003"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"><path d="M3,22H21a1,1,0,0,0,1-1V6a1,1,0,0,0-1-1H17V3a1,1,0,0,0-2,0V5H9V3A1,1,0,0,0,7,3V5H3A1,1,0,0,0,2,6V21A1,1,0,0,0,3,22ZM4,7H20v3H4Zm0,5H20v8H4Z"></path></g></svg>
            <div class="text">
              <b class="day">{{ day.date_name}}</b>
              <p class="date">{{ day.date}}</p>
            </div>
          </div>
          <template v-if="reservation.change_rate && !team.check_calculate_price_by_day_enable">
            <PriceInput :disabled="dateHasInvoice(day.date)" :value="(reservation.sub_total / reservation.nights).toFixed(2)" :identifier="day.date" @updatePrice="handleUpdate"  />
          </template>
          <template v-else>
            <div class="d-flex align-items-center gap-2">
              <div class="text-center">
                <label class="price-input-label">{{ __('Old Price (Ewa + Vat)') }}</label>
                <PriceInput  :disabled="true" :value="getOldPrice(day.date_name)"   />
              </div>
              <div class="text-center">
                <label class="price-input-label">{{ __('Price') }}</label>
                <PriceInput  :disabled="true" :value="day.mutatedValue ? getCalculateBasePrice(day.mutatedValue).toFixed(2) : day.price.toFixed(2)"  />
              </div>
              <div class="text-center">
                <label class="price-input-label">{{ __('Price (Ewa + Vat)') }}</label>
                <PriceInput  :disabled="!hasPermission || dateHasInvoice(day.date)" :value="day.mutatedValue ? day.mutatedValue : getCalculatePrice(day.price).toFixed(2)" :identifier="day.date" @updatePrice="handleUpdate"   />
              </div> 
            </div> 
          </template>
          <!-- 
          <br> -->
          <!-- {{ change_rate}}
          <p>{{ (day.price + (day.price * change_rate) /100).toFixed(2) }}</p> -->
        </div>
        <div class="price_day_item">
          <span>{{ __('Grand Total For All Nights') }}</span>
          <p>{{ grandTotal.toFixed(2) }}</p>
        </div>
        <div v-if="hasPermission" class="d-flex justify-content-end">
          <button  @click="save()" class="btn btn-primary">{{ __("Update") }}</button>
        </div>
      </div>
      <div v-if="reservation.rent_type == 2">
        <div class="price_day_item">
          <span>{{ __('Month Price') }}</span>
          <p v-if="change_rate">{{(reservation.sub_total).toFixed(2)}}</p>
          <p v-else>{{ (reservation.prices.sub_total).toFixed(2) }}</p>
        </div>
      </div>
    </sweet-modal>
    <update-reservation-prices-modal ref="saveUpdateReservationPrices" />
  </div>
</template>

<script>
    import PriceInput from '../block_helpers/PriceInput.vue';
    import UpdateReservationPricesModal from './UpdateReservationPricesModal.vue';
    import Loading from 'vue-loading-overlay';

    export default {
        name: "days-details",
        props: ['reservation'],
        components: {
          Loading,
          PriceInput,
          UpdateReservationPricesModal
        },
        data: () => {
            return {
                sources: [],
                change_rate : 0,
                grandTotal: 0,
                payload: {
                  checkDatesOnly: false,
                  days: null,
                  reservationId: null
                },
                baseUrl: "",
                datesHaveInvoice: [],
                hasPermission:  Nova.app.$hasPermission('can edit reservation day price') && Nova.app.currentTeam.check_calculate_price_by_day_enable,
                isLoading: false,
                team: Nova.app.currentTeam,
            }
        },
        
        methods: {

            open(){
              // i will use change rate as a sensor for price change 
              this.change_rate = this.reservation.change_rate ;
              this.validateDatesOnMount();
            },
            handleUpdate(val) {
              const {identifier, value} = val;
              const daysCopy = this.reservation.prices.days;
              const date = daysCopy.find((day) => day.date == identifier);
              date.mutatedValue =  parseFloat(value);
              date.mutatedValueBase = this.getCalculateBasePrice(parseFloat(value));
              this.reservation.prices.days = daysCopy;
              this.calculateGrandTotal();
            },

            calculateGrandTotal () {
              if(this.reservation) {
                this.grandTotal = 0;
                const reservation = this.reservation;
                reservation.prices.days.forEach(day => {
                  let amount = 0;
                  amount  = day.mutatedValue ? day.mutatedValue : this.getCalculatePrice(parseFloat(day.price));
                  this.grandTotal = this.grandTotal + amount;
                });
              }
            },

            save() {
              this.$refs.saveUpdateReservationPrices.$refs.updateReservationPricesModal.open();
            },

            async updateReservationPricesReq() {
              try {
                this.isLoading = true;
                this.payload = {
                  days: this.reservation.prices.days,
                  reservationId: this.reservation.id,
                  checkDatesOnly: false
                }

                //replace base value
                this.reservation.prices.days.forEach((value, index) => {
                  if(value.mutatedValue && value.mutatedValueBase) {
                    this.reservation.prices.days[index].mutatedValue = value.mutatedValueBase;
                  }
                });

                const response = await axios.post(`${this.baseUrl}/update-reservation-prices`,this.payload);
                if(response.data.success) {
                  this.$toasted.show(response.data.message, {type: 'success'});
                  // this.$refs.saveUpdateReservationPrices.$refs.updateReservationPricesModal.close();
                  // Nova.$emit('update-reservation');
                  Nova.$emit('update-reservation-day-price');
                }

              } catch (e) {
                this.$toasted.show(e, {type: 'error'});
              } finally {
                this.isLoading = false;
                this.calculateGrandTotal();
              }
            },

            async validateDatesOnMount () {
              try {
                this.isLoading = true;
                this.payload = {
                  days: this.reservation.prices.days,
                  reservationId: this.reservation.id,
                  checkDatesOnly: true
                }
                const response = await axios.post(`${this.baseUrl}/update-reservation-prices`,this.payload);
                this.datesHaveInvoice = response.data.data;
              } catch (e) {
                if(e.response.status == 401) {
                        return;
                };
                this.$toasted.show(e, {type: 'error'});
              } finally {
                this.isLoading = false;
                this.$refs.daysPricesDetailsModal.open();
                this.calculateGrandTotal();
              }
            },

            dateHasInvoice(date) {
              return this.datesHaveInvoice.find((dateInvoice) => dateInvoice.date === date);
            },

            getOldPrice(dayName) {
              const day = this.translateDayName(dayName);
              const price = this.reservation.old_prices.prices.day[day];
              const ewaPercentage = parseFloat(this.reservation.prices.ewa_parentage);
              const vatPercentage = parseFloat(this.reservation.prices.vat_parentage);
              const ewaAmount = parseFloat(price) / 100 * ewaPercentage;
              const subTotal = parseFloat(price) + ewaAmount;
              const vatTotal = subTotal / 100 * vatPercentage;
              const total = subTotal + vatTotal;
              return total.toFixed(2);
            },

            isArabicDay(dayName) {
                const arabicDays = ["الاحد", "الاثنين", "الثلاثاء", "الاربعاء", "الخميس", "الجمعة", "السبت"];
                return arabicDays.includes(dayName);
            },

            translateDayName(dayName) {
              const daysMap = {
                  "الاحد": "Sunday",
                  "الاثنين": "Monday",
                  "الثلاثاء": "Tuesday",
                  "الاربعاء": "Wednesday",
                  "الخميس": "Thursday",
                  "الجمعة": "Friday",
                  "السبت": "Saturday"
              };
              if (this.isArabicDay(dayName)) {
                return daysMap[dayName].toLowerCase() + '_day_price' || "invalid day name";
              }
              return dayName.toLowerCase() + '_day_price';
            },

            getCalculatePrice(price) {
              const ewaPercentage = parseFloat(this.reservation.prices.ewa_parentage);
              const vatPercentage = parseFloat(this.reservation.prices.vat_parentage);
              const ewaAmount = parseFloat(price) / 100 * ewaPercentage;
              const subTotal = parseFloat(price) + ewaAmount;
              const vatTotal = subTotal / 100 * vatPercentage;
              const total = subTotal + vatTotal;
              return total;
            },
            getCalculateBasePrice(price) {
              const ewaPercentage = parseFloat(this.reservation.prices.ewa_parentage);
              const vatPercentage = parseFloat(this.reservation.prices.vat_parentage);
              let distributableAmountWithoutVat = parseFloat(price) / (1 + (vatPercentage / 100));  
              let distributableAmountWithoutEwa =  parseFloat(distributableAmountWithoutVat) / (1 + (ewaPercentage / 100));
              const total = distributableAmountWithoutEwa;
              return total;
            }
        },

        mounted(){
          this.baseUrl = "/nova-vendor/calender/reservations";
          Nova.$on('update-reservation-prices', (val) => {
            this.updateReservationPricesReq();
          })
          this.calculateGrandTotal();
        },
        destroyed() {
          Nova.$off('update-reservation-prices');
        }



    }
</script>

<style lang="scss" scoped>
  .price_day_link {
    color: #8C8C8C;
    font-size: 14px;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' version='1.1' id='Capa_1' x='0px' y='0px' viewBox='0 0 23.625 23.625' style='enable-background:new 0 0 23.625 23.625;' xml:space='preserve' width='15' height='15' class=''%3E%3Cg%3E%3Cg%3E%3Cpath d='M11.812,0C5.289,0,0,5.289,0,11.812s5.289,11.813,11.812,11.813s11.813-5.29,11.813-11.813 S18.335,0,11.812,0z M14.271,18.307c-0.608,0.24-1.092,0.422-1.455,0.548c-0.362,0.126-0.783,0.189-1.262,0.189 c-0.736,0-1.309-0.18-1.717-0.539s-0.611-0.814-0.611-1.367c0-0.215,0.015-0.435,0.045-0.659c0.031-0.224,0.08-0.476,0.147-0.759 l0.761-2.688c0.067-0.258,0.125-0.503,0.171-0.731c0.046-0.23,0.068-0.441,0.068-0.633c0-0.342-0.071-0.582-0.212-0.717 c-0.143-0.135-0.412-0.201-0.813-0.201c-0.196,0-0.398,0.029-0.605,0.09c-0.205,0.063-0.383,0.12-0.529,0.176l0.201-0.828 c0.498-0.203,0.975-0.377,1.43-0.521c0.455-0.146,0.885-0.218,1.29-0.218c0.731,0,1.295,0.178,1.692,0.53 c0.395,0.353,0.594,0.812,0.594,1.376c0,0.117-0.014,0.323-0.041,0.617c-0.027,0.295-0.078,0.564-0.152,0.811l-0.757,2.68 c-0.062,0.215-0.117,0.461-0.167,0.736c-0.049,0.275-0.073,0.485-0.073,0.626c0,0.356,0.079,0.599,0.239,0.728 c0.158,0.129,0.435,0.194,0.827,0.194c0.185,0,0.392-0.033,0.626-0.097c0.232-0.064,0.4-0.121,0.506-0.17L14.271,18.307z M14.137,7.429c-0.353,0.328-0.778,0.492-1.275,0.492c-0.496,0-0.924-0.164-1.28-0.492c-0.354-0.328-0.533-0.727-0.533-1.193 c0-0.465,0.18-0.865,0.533-1.196c0.356-0.332,0.784-0.497,1.28-0.497c0.497,0,0.923,0.165,1.275,0.497 c0.353,0.331,0.53,0.731,0.53,1.196C14.667,6.703,14.49,7.101,14.137,7.429z' data-original='%23030104' class='active-path' data-old_color='%23030104' fill='%237C858E'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    background-position: left center;
    background-repeat: no-repeat;
    background-size: 15px;
    padding: 0 0 0 20px;
    cursor: pointer;
    [dir="ltr"] & {
      background-position: right center;
      padding: 0 20px 0 0;
    } /* ltr */
    &:hover {color: #555555;}
  } /* price_day_link */
  .price_day_item {
    background: #fdfdfd;
    border: 1px solid #ddd;
    padding: 10px;
    border-radius: 5px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    font-size: 15px;
    color: #000;
    margin: 0 auto 10px;
    p {direction: ltr;}
    
  } /* price_day_item */
  .disabled_input {
      background: #f3f3f3;
    }
  .price_days_modal {
    .sweet-content {
      max-height: 500px;
      overflow-y: auto;
      display: block !important;
      scrollbar-width: thin;
      scrollbar-color: #ccc #f5f5f5;
      &::-webkit-scrollbar {width: 6px;}
      &::-webkit-scrollbar-track {background: #f5f5f5;}
      &::-webkit-scrollbar-thumb {background: #ccc;}
      &::-webkit-scrollbar-thumb:window-inactive {background: #f5f5f5;}
    } /* sweet-content */
  } /* price_days_modal */
  .price_days_modal  .btn {
    padding: 10px;
    margin-bottom: 10px;
  }
  .price_days_modal  .d-flex {
    display: flex;
  }
  .price_days_modal  .justify-content-end {
    justify-content: end;
  }
  .price_days_modal  .align-items-center {
    align-items: center;
  }
  .date-div {
    display: flex;
    gap: 2px;
  }
  .date-div .day {
    color: #555555;
    font-weight: 600;
  }
  .date-div .date {
    color: #8C8C8C;
  }
  .date-div .text {
    line-height: 17px;
    margin-top: 1px;
  }
  .price_days_modal .gap-2 {
    gap: 10px;
  }
  .price_days_modal .price-input-label {
    font-weight: 600;
    font-size: 12px;
    color: #838383;
  }
  .price_days_modal .text-center {
    text-align: center;
  }
</style>