<template>
    <!-- <div> -->
      <div class="item_reservation_button">
          <button class="main_button action-btn" @click="openModal">{{__('Group Reservation Edit')}}</button>

      <sweet-modal
          :enable-mobile-fullscreen="false"
          :pulse-on-block="false"
          width="70%"
          :title="__('Group Reservation Edit')"
          overlay-theme="dark"
          ref="groupReservationExtenderModalRef"
          class="summary_modal"
      >
          <div id="update_prices_div" class="relative">
              <loading
                  :active.sync="isLoading"
                  :can-cancel="false"
              />
           

              <div class="content_page">
               
                  <div class="overflow-hidden overflow-x-auto relative">
                      <div class="group_reservations_tbl rounded overflow-hidden">
                          <table class="table w-full"
                                  cellpadding="0"
                                  cellspacing="0"
                          >

                              <thead>
                              <tr>
                                  <th>{{__('Reservation Number')}}</th>
                                  <th>{{__('The Unit')}}</th>
                                  <th>{{ __('Reservation Date In & Out') }}</th>
                                  <th>{{ __('Nights') }}</th>
                                  <th>{{ __('Action') }}</th>
                              </tr>
                              </thead>
                              <tbody>
                                    <template v-if="confirmedReservations.length">
                                       
                                        <tr v-for="(obj, i) in confirmedReservations" :key="i">
                                            <td>{{ obj.number }}</td>
                                           
                                            <td>{{ obj.unit_number }} - {{ obj.unit_category_name }}</td>
                                           
                                            <td>
                                                <div class="relative">
                                                <vcc-date-picker
                                                class="v-date-picker"
                                                :class="{ 'disabled-datepicker': calendarDisabledMap[i] || isSaving || hasInvoices }"
                                                :locale="vcc_local"
                                                mode="range"
                                                v-model="selectedDates[i]"
                                                show-caps
                                                is-expanded
                                                :columns="$screens({ default: 1, lg: 2 })"
                                                :popover-expanded="true"
                                                :popover="{
                                                    placement: 'bottom',
                                                    visibility: calendarDisabledMap[i] ? 'hide' : 'click'
                                                }"
                                                :input-props="{
                                                    disabled: calendarDisabledMap[i] || isSaving || hasInvoices,
                                                    readonly: true
                                                }"
                                                />
                                                </div>
                                            </td>
                                            <td class="flex items-center justify-center gap-2 h-full">
                                               
                                                <button 
                                                    :disabled="extendDisabledMap[i] || isSaving || !canExtendReservationsMap[i]"
                                                    @click="increaseNights(i)" 
                                                    type="button" 
                                                    class="px-2 py-1 rounded bg-gray-200"
                                                    >
                                                +
                                                </button>
                                                <input
                                                    type="number"
                                                    min="1"
                                                    :readonly="true"
                                                    :value="nightsValues[i]"
                                                    @input="onNightsInput($event, i)"
                                                    @blur="onNightsBlur(i)"
                                                    class="w-16 text-center border rounded"
                                                />
                                                <button 
                                                        type="button" 
                                                        :disabled="calendarDisabledMap[i] || isSaving || disableMinusMap[i]" 
                                                        @click="decreaseNights(i)" 
                                                        class="px-2 py-1 rounded bg-gray-200"
                                                >
                                                    -
                                                </button>
                                            </td>

                                            <td>
                                                <button
                                                    @click="updateReservation(i)"
                                                    class="main_button"
                                                    :disabled="isSaving"
                                                    >
                                                    <template v-if="isSaving">
                                                        <template v-if="savingIndex === i">
                                                        <!-- Spinner + Saving... -->
                                                        <svg class="animate-spin h-4 w-4 mr-2 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z" />
                                                        </svg>
                                                        
                                                        </template>
                                                        <template v-else>
                                                        <!-- Lock icon only -->
                                                        🔒
                                                        </template>
                                                    </template>

                                                    <template v-else>
                                                        <!-- Normal Save -->
                                                        {{ __('Save') }}
                                                    </template>
                                                </button>


                                            </td>

                                        </tr>

                                    </template>
                                    <template v-else>
                                        <tr>
                                            <td colspan="5" class="text-center">
                                                <div class="no_data_show">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 17h-2v-2h2v2zm0-4h-2V7h2v8z"/></svg>
                                                    <span>{{__('No reservations applicable for edit')}}</span>
                                                </div>
                                            </td>
                                        </tr>
                                    </template>
                              </tbody>
                          </table>
                      </div>
                  </div>
              </div>
          </div>
      </sweet-modal>
  </div>
    <!-- </div>   -->

  </template>

<script>
// GroupReservationExtenderModal.vue
// This component allows editing and extending group reservations with date and night control per reservation.

import moment from 'moment';
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css';
export default {
    name : 'group-reservation-extender-modal',
    props: ["reservation"],
    components: {
        Loading
    },
    data() {
        return {
            isLoading : false,
            locale: Nova.config.local,
            categories: [],
            pricesMap: new Map(),
            disableActions : false,
            hasInvoices : false,
            confirmedReservations: [],
            selectedDates: {},
            nightsValues: {},
            vcc_local: {
              id: Nova.config.local,
              firstDayOfWeek: 1,
              masks: {
                  weekdays: 'WWW',
                  input: ['L'],
                  data: ['L']
              }
          },
          // Permissions and control flags
          can_edit_calendar_before_checkin : false,
          can_edit_calendar_after_checked_in: false,
          can_edit_calendar_before_night_run : false,
          calendarDisabledMap: {},

          can_extend_before_checkin: false,
          can_extend_before_night_run: false,
          can_extend_after_checkin: false,
          extendDisabledMap: {},

          isSaving: false,
          savingIndex: null,

          invoicesWithoutCreditNotes : [],
          hasInvoices : false,

          disableMinusMap: {},
          canExtendReservationsMap: {},

          pricesMapper : null,

          specialPricesCollector: [],

          initialSelectedDates: {},
          initialNightsValues: {}, 

        }
    },
    methods: {
        // Checks if current user has a given permission - don't count on it
        hasPermission(permission){
            return Nova.app.$hasPermission(permission) ? true : false
        },
        // Adds one night to reservation and updates the end date
        increaseNights(i) {
            const selected = this.selectedDates[i];
            if (!selected || !(selected.start instanceof Date)) return;

            this.nightsValues[i]++;
            const newEnd = this.addDays(selected.start, this.nightsValues[i]);
            this.$set(this.selectedDates, i, {
                start: selected.start,
                end: newEnd
            });

            this.updateInvoiceBoundariesForAll();
        },
        // Decreases one night if > 1 and updates the end date
        decreaseNights(i) {
            const selected = this.selectedDates[i];
            if (!selected || !(selected.start instanceof Date) || this.nightsValues[i] <= 1) return;

            this.nightsValues[i]--;
            const newEnd = this.addDays(selected.start, this.nightsValues[i]);
            this.$set(this.selectedDates, i, {
                start: selected.start,
                end: newEnd
            });

            this.updateInvoiceBoundariesForAll();
        },
        // Utility to add N days to a date object
        addDays(dateObj, days) {
            const result = new Date(dateObj);
            result.setDate(result.getDate() + days);
            return result;
        },  
        formatDate(date) {
            if (!date) return null;
            return new Date(date).toISOString().slice(0, 10);
        },
        // On direct input of night count
        onNightsInput(event, i) {
            let value = event.target.value;
            value = value.replace(/[^\d]/g, '');
            const parsed = Math.max(1, parseInt(value || '1', 10));
            this.nightsValues[i] = parsed;
            this.updateDateRangeFromNights(i);
        },
        onNightsBlur(i) {
            if (!this.nightsValues[i] || isNaN(this.nightsValues[i]) || this.nightsValues[i] < 1) {
                this.nightsValues[i] = 1;
                this.updateDateRangeFromNights(i);
            }
        },
        updateDateRangeFromNights(i) {
            const selected = this.selectedDates[i];
            const nights = this.nightsValues[i];
            if (!selected || !(selected.start instanceof Date) || !nights || nights < 1) return;
            const newEnd = this.addDays(selected.start, nights);
            this.$set(this.selectedDates, i, {
                start: selected.start,
                end: newEnd
            });
        },
        // Update disabling rules based on invoice data
        updateInvoiceBoundariesForAll() {
                if (!this.invoicesWithoutCreditNotes.length) {
                    // ✅ No invoices → both buttons enabled
                    this.confirmedReservations.forEach((res, i) => {
                        this.$set(this.disableMinusMap, i, false);
                        this.$set(this.canExtendReservationsMap, i, true);
                    });
                    return;
                }

                const allDates = this.invoicesWithoutCreditNotes.flatMap(inv => [inv.from, inv.to]);
                const sorted = _.sortBy(allDates);
                const maxInvoiceDate = sorted.length ? sorted[sorted.length - 1] : null;

                const lastInvoice = this.invoicesWithoutCreditNotes[0];
                const lastInvoiceTo = lastInvoice && lastInvoice.to ? lastInvoice.to : null;

                this.confirmedReservations.forEach((res, i) => {
                    const selected = this.selectedDates[i];

                    if (!selected || !(selected.end instanceof Date)) {
                        this.$set(this.disableMinusMap, i, true);
                        this.$set(this.canExtendReservationsMap, i, false);
                        return;
                    }

                    const theEndDate = moment(selected.end).subtract(1, 'days').format('YYYY-MM-DD');

                    // 🔒 Disable minus if invoice covers current date range
                    const disableMinus = maxInvoiceDate && maxInvoiceDate >= theEndDate;

                    // 🔒 Disable extend if invoice already extends beyond
                    const disableExtend = lastInvoiceTo && lastInvoiceTo > theEndDate;

                    this.$set(this.disableMinusMap, i, disableMinus);
                    this.$set(this.canExtendReservationsMap, i, !disableExtend);
                });
        },
        // Reload updated balance and reservation list
        callGroupReservationBalanceMapper(reservation_id){
            axios
            .get(`/nova-vendor/calender/reservation/call-grp-mapper/${reservation_id}`)
            .then(response => {
                if(response.data.success){
                    Nova.$emit('set-group-balance', response.data.group_balance);
                    Nova.$emit('reload-grp-reservation');
                }
            })
        },
        // Open modal and populate reservation data
        async openModal() {
            await this.handleInvoices();
            await this.fetchPermissions();
            if (
                this.reservation &&
                this.reservation.all_grouped_reservations &&
                this.reservation.all_grouped_reservations.length
            ) {

                this.initialSelectedDates = {};
                this.initialNightsValues = {};

                const selectedDates = {};
                const nightsValues = {};

                this.disableMinusMap = {};
                this.canExtendReservationsMap = {};

                this.confirmedReservations = this.reservation.all_grouped_reservations
                .filter(res => res.status === 'confirmed' && res.checked_out === null)
                // .sort((a, b) => b.number.localeCompare(a.number))
                .sort((a, b) => {
                    function splitCode(code) {
                        const match = code.match(/^([A-Z]*)(\d+)$/i);
                        return {
                            prefix: match ? match[1].toUpperCase() : '',
                            number: match ? parseInt(match[2], 10) : 0
                        };
                    }

                    const aParts = splitCode(a.number || '');
                    const bParts = splitCode(b.number || '');

                    // Compare prefix length DESC
                    if (aParts.prefix.length !== bParts.prefix.length) {
                        return bParts.prefix.length - aParts.prefix.length;
                    }

                    // Compare prefix alphabetically DESC
                    if (aParts.prefix !== bParts.prefix) {
                        return bParts.prefix.localeCompare(aParts.prefix);
                    }

                    // Compare numeric part DESC
                    return bParts.number - aParts.number;
                })

                .map((res, i) => {
                    const unit = res.unit || {};
                    const category = unit.unit_category || {};
                    const nameKey = this.locale === 'ar' ? 'name_ar' : 'name_en';

                    let isDisabled = false;
                    if (!res.checked_in) {
                        isDisabled = !this.can_edit_calendar_before_checkin;
                    } else {
                        if (res.checking_in == 0) {
                            isDisabled = !this.can_edit_calendar_before_night_run;
                        } else {
                            isDisabled = !this.can_edit_calendar_after_checked_in;
                        }
                    }
                    this.$set(this.calendarDisabledMap, i, isDisabled);

                    let canExtend = false;
                    if (!res.checked_in) {
                        canExtend = this.can_extend_before_checkin;
                    } else {
                        if (res.checking_in == 0) {
                            canExtend = this.can_extend_before_night_run;
                        } else {
                            canExtend = this.can_extend_after_checkin;
                        }
                    }
                    this.$set(this.extendDisabledMap, i, !canExtend);

                    nightsValues[i] = res.nights;
                    selectedDates[i] = {
                        start: new Date(res.date_in),
                        end: new Date(res.date_out)
                    };

                    this.initialSelectedDates[i] = {
                        start: new Date(res.date_in),
                        end: new Date(res.date_out)
                    };
                    this.initialNightsValues[i] = res.nights;


                    return {
                        id: res.id || null,
                        unit_number: unit.unit_number || null,
                        unit_category_name: category[nameKey] || null,
                        number: res.number || null,
                        date_in: res.date_in || null,
                        date_out: res.date_out || null,
                        checked_in: res.checked_in || null,
                        checking_in: res.checking_in || null,
                        nights: res.nights || 0,
                        unit_id: res.unit_id || null,
                        rent_type: res.rent_type || null,
                        special_prices: res.special_prices || null,
                        sub_total: res.sub_total || 0,
                        source_id: res.source_id || null,
                        source_num: res.source_num || null
                    };
                });

                this.selectedDates = selectedDates;
                this.nightsValues = nightsValues;
                this.updateInvoiceBoundariesForAll();

                this.$nextTick(() => {
                    this.$refs.groupReservationExtenderModalRef.open();
                });
            }
        },  
        // Reset edited reservation to original state
        resetReservationToInitial(i) {
            this.$set(this.selectedDates, i, { 
                start: new Date(this.initialSelectedDates[i].start),
                end: new Date(this.initialSelectedDates[i].end)
            });

            this.$set(this.nightsValues, i, this.initialNightsValues[i]);
        },
        // Load invoice data
        async handleInvoices() {
            const invoices = this.reservation.shared_invoices;
            this.invoicesWithoutCreditNotes = _.filter(invoices, inv => inv.invoice_credit_note === null);
            this.hasInvoices = !!this.invoicesWithoutCreditNotes.length;
        },
        // Load user permission flags
        async fetchPermissions() {
            const response = await axios.get('/nova-vendor/calender/check-permissions-for-user-in-multiple-roles-for-team', {
                params: {
                    user_id: Nova.app.user.id,
                    team_id: Nova.app.currentTeam.id,
                    permissions: [
                        'change reservation calendar date before checkin',
                        'change reservation calendar date after checkin',
                        'change reservation calendar date before night run',
                        'extend reservation before checkin',
                        'extend reservation after checkin',
                        'extend reservation before night run'
                    ]
                }
            });
            const perms = response.data;
            this.can_edit_calendar_before_checkin = perms['change reservation calendar date before checkin'];
            this.can_edit_calendar_after_checked_in = perms['change reservation calendar date after checkin'];
            this.can_edit_calendar_before_night_run = perms['change reservation calendar date before night run'];
            this.can_extend_before_checkin = perms['extend reservation before checkin'];
            this.can_extend_after_checkin = perms['extend reservation after checkin'];
            this.can_extend_before_night_run = perms['extend reservation before night run'];
        },
        // To Get Actual Prices after changing calendar
        async getFreshUpdatedResrvatoinBaseOnStartAndEndDate(reservation_id, unit_id, rent_type, date_in, date_out) {
            try {
                const response = await axios.get(`/nova-vendor/calender/unit/${unit_id}/${date_in}/${date_out}`, {
                    params: {
                        rent_type: rent_type,
                        reservation_id: reservation_id
                    }
                });

                // ✅ Guard clause if status indicates an issue
                if (response.data.status === 'new_unit_still_has_checked_in_reservation') {
                    this.$toasted.show(this.__('There is a checked-in reservation on this unit'), { type: 'error' });
                    return false; // ❌ Stop update flow
                }

                this.pricesMapper = response.data.reservation.prices || null;
                if (this.pricesMapper) {
                    this.pricesMapper.subtotal = this.pricesMapper.sub_total;
                }

                return true; // ✅ continue

            } catch (error) {
                console.error('Error fetching reservation:', error);
                this.pricesMapper = null;
                return false; // ❌ Stop update flow
            }
        },
        // To set specialPricesCollector
        async unitSpecialPrices(unit_id, special_prices, sub_total, start, end) {
            let specialPricesSum = 0;

            if (special_prices) {
                let specialPrices = JSON.parse(special_prices);
                if (specialPrices) {
                    specialPrices.forEach(specialPrice => {
                        specialPricesSum += Number(specialPrice.specialPrice);
                    });
                }
            }

            let edit_price = Math.abs(specialPricesSum - sub_total) < 1;

            try {
                const response = await axios.get(`/nova-vendor/calender/unit/${unit_id}/get-special-prices/${start}/${end}`);

                if (response.data.status === 'special_prices_found' && edit_price) {
                    this.specialPrices = response.data.special_prices;

                    const datesHasSpecialPrice = response.data.datesHasSpecialPrice || [];
                    const datesDoesntHaveSpecialPrice = response.data.datesDoesntHaveSpecialPrice || [];
                    const incomingDates = response.data.incomingDates || [];

                    const uniqueDates = new Set();
                    const collector = [];

                    for (let i = 0; i < datesHasSpecialPrice.length; i++) {
                        const specialDate = datesHasSpecialPrice[i].date;
                        const price = datesHasSpecialPrice[i].price;

                        incomingDates.forEach(incomingDate => {
                            if (specialDate === incomingDate && !uniqueDates.has(incomingDate)) {
                                collector.push({ date: incomingDate, specialPrice: price });
                                uniqueDates.add(incomingDate);
                            }
                        });
                    }

                    for (let i = 0; i < datesDoesntHaveSpecialPrice.length; i++) {
                        const date = datesDoesntHaveSpecialPrice[i].date;
                        const price = datesDoesntHaveSpecialPrice[i].price;

                        incomingDates.forEach(incomingDate => {
                            if (date === incomingDate && !uniqueDates.has(incomingDate)) {
                                collector.push({ date: incomingDate, specialPrice: price });
                                uniqueDates.add(incomingDate);
                            }
                        });
                    }

                    // Remove duplicates
                    this.specialPricesCollector = collector.filter((thing, index, self) =>
                        index === self.findIndex(t => t.date === thing.date)
                    );
                } else {
                    const unitCategoryDaysPrices = response.data.unitCategoryDaysPrices || [];
                    this.specialPricesCollector = unitCategoryDaysPrices.map(day => ({
                        date: day.date,
                        specialPrice: day.price
                    }));
                }

            } catch (error) {
                console.error('Failed to fetch special prices:', error);
                this.specialPricesCollector = [];
            }
        },
        // To Avoid reservations overlapping
        async checkOverlap(i,reservation_id, unit_id, start, end) {
            try {
                const response = await axios.get(`/nova-vendor/calender/overlap-check?start=${start}&end=${end}&reservation_id=${reservation_id}&unit_id=${unit_id}`);

                if (response.data.has_reservation) {
                    // ✅ Reset to original state
                    this.resetReservationToInitial(i);
                    this.$toasted.show(this.__('This unit has a reservation that intersect with selected dates !'), { type: 'error' });
                    return true; // ❗ overlap exists
                }

                return false; // ✅ no overlap

            } catch (error) {
                console.error('Overlap check failed:', error);
                return true; // block just in case of error
            }
        },
        // Update reservation action
        async updateReservation(i) {
            this.isSaving = true;
            this.savingIndex = i;
            const reservation_id = this.confirmedReservations[i].id;
            const unit_id = this.confirmedReservations[i].unit_id;
            const rent_type = this.confirmedReservations[i].rent_type;
            const date_in = moment(this.selectedDates[i].start).format('YYYY-MM-DD');
            const date_out = moment(this.selectedDates[i].end).format('YYYY-MM-DD');
            const special_prices = this.confirmedReservations[i].special_prices;
            const sub_total =  this.confirmedReservations[i].sub_total;
            const source_id =  this.confirmedReservations[i].source_id;
            const source_num =  this.confirmedReservations[i].source_num;

             
            await this.unitSpecialPrices(unit_id,special_prices,sub_total,date_in,date_out); 
            
            const canProceed = await this.getFreshUpdatedResrvatoinBaseOnStartAndEndDate(
                reservation_id, unit_id, rent_type, date_in, date_out
            );

            if (!canProceed) {
                this.isSaving = false;
                this.savingIndex = null;
                return;
            }
            
            const hasOverlap = await this.checkOverlap(i,reservation_id, unit_id, date_in, date_out);
            if (hasOverlap) {
                this.isSaving = false;
                this.savingIndex = null;
                return;
            }

            var updateObject = {
                id: reservation_id,
                date_in: date_in,
                date_out: date_out,
                unit_id: unit_id,
                rent_type: rent_type,
                prices: this.pricesMapper,
                specialPricesCollector : this.specialPricesCollector,
                reservation_services_selected : [],
                source_id: source_id,
                source_num: source_num
            }
            
            axios
            .put('/nova-vendor/calender/reservation/update_reservation', updateObject)
            .then(response => {
            
                if(response.data){
                    this.isSaving = false;
                    this.savingIndex = null;
                    this.$toasted.show(this.__('Reservation updated successfully'), { type: 'success' });
                    this.callGroupReservationBalanceMapper(reservation_id);
                }
            })
            
        }
    },
}
</script>


  <style lang="scss" scoped>

    .content_page {
                background: #fff;
                padding: 10px;
                .options {
                    margin: 15px 0;
                    select {
                        background-image: url("data:image/svg+xml,%3C%3Fxml version='1.0' encoding='iso-8859-1'%3F%3E%3C!-- Generator: Adobe Illustrator 19.0.0, SVG Export Plug-In . SVG Version: 6.00 Build 0) --%3E%3Csvg version='1.1' id='Layer_1' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' x='0px' y='0px' viewBox='0 0 491.996 491.996' style='enable-background:new 0 0 491.996 491.996;' xml:space='preserve'%3E%3Cg%3E%3Cg%3E%3Cpath d='M484.132,124.986l-16.116-16.228c-5.072-5.068-11.82-7.86-19.032-7.86c-7.208,0-13.964,2.792-19.036,7.86l-183.84,183.848 L62.056,108.554c-5.064-5.068-11.82-7.856-19.028-7.856s-13.968,2.788-19.036,7.856l-16.12,16.128 c-10.496,10.488-10.496,27.572,0,38.06l219.136,219.924c5.064,5.064,11.812,8.632,19.084,8.632h0.084 c7.212,0,13.96-3.572,19.024-8.632l218.932-219.328c5.072-5.064,7.856-12.016,7.864-19.224 C491.996,136.902,489.204,130.046,484.132,124.986z'/%3E%3C/g%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3C/svg%3E%0A");
                        width: 100%;
                        height: 40px !important;
                        padding: 0 10px !important;
                        background-color: #fafafa !important;
                        border: 1px solid #ddd !important;
                        color: #000;
                        font-size: 15px;
                        -webkit-box-sizing: border-box;
                        box-sizing: border-box;
                        -webkit-appearance: none;
                        -moz-appearance: none;
                        -o-appearance: none;
                        appearance: none;
                        border-radius: 5px !important;
                        background-position: 15px center;
                        background-repeat: no-repeat;
                        background-size: 14px;
                        outline: none !important;
                        } /* select */
                }
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

    .group_reservations_tbl {
          overflow: auto;
          width: 100%;
          height: 85vh;
          padding: 0 0 15px 0;
          background: #fff;
      }
      .group_reservations_tbl .table {border: 1px solid #e2e8f0;}
      .group_reservations_tbl .table thead tr th {
          padding: 10px 5px;
          line-height: 20px;
          font-weight: normal;
          font-size: 15px;
          border: 1px solid #5E697C;
          vertical-align: middle;
          text-align: center;
          color: #ffffff;
          background: #4a5568;

           label {
              display: flex;
              align-items: center;
              justify-content: center;
              position: relative;
              overflow: hidden;
              cursor: pointer;

              input {
                  width: 100%;
                  height: 100%;
                  position: absolute;
                  right: 0;
                  top: 0;
                  opacity: 0;
                  z-index: 9;
                  &:checked ~ {
                      .checkmark {
                          &::before{
                              display: block;
                          }
                      }
                  }
              }

              .checkmark {

                  position: relative;
                  width: 18px;
                  height: 18px;
                  border: 1px solid #ced4dc;
                  background-color: #fff;
                  margin: 0 0 0 10px;
                  border-radius: .25rem;

                  &::before{
                      display: none;
                      content: "";
                      background-color: #4599dd;
                      background-position: center;
                      background-size: 13px 13px;
                      background-repeat: no-repeat;
                      position: absolute;
                      right: -1px;
                      top: -1px;
                      border: 1px solid #4599dd;
                      width: 18px;
                      height: 18px;
                      border-radius: .25rem;
                      background-image: url("data:image/svg+xml,%3Csvg fill='none' height='24' stroke='%23fff' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' viewBox='0 0 24 24' width='24' xmlns='http://www.w3.org/2000/svg'%3E%3Cpolyline points='20 6 9 17 4 12'/%3E%3C/svg%3E");
                  }
              }
          }
      }
      .group_reservations_tbl .table tbody tr {background: #fff;}
      .group_reservations_tbl .table tbody tr td {
          text-align: center;
          padding: 10px 5px;
          vertical-align: middle;
          line-height: 20px;
          font-size: 15px;
          border: 1px solid #ced4dc;
          color: #000000;
          font-weight: normal;
          background: #fafafa;

          &.linked_reservations{
              display: flex;
              align-items: center;
              justify-content: flex-start;
              &.clickable{
                  cursor: pointer;

                  span{
                      font-weight: bold;
                      font-size: 15px;
                      color: #4599dd;
                      padding: 0;
                  }

                  svg{
                      margin: 0 0 0 5px;
                      path {
                          fill: #4599dd;
                      }
                  }
              }

              span{
                  font-weight: bold;
                  font-size: 15px;
                  color: #aaa;
                  padding: 0;
              }

              svg{
                  margin: 0 0 0 5px;
                  path {
                      fill: #aaa;
                  }
              }

          }

          label {
              position: relative;
              overflow: hidden;

              input {
                  width: 100%;
                  height: 100%;
                  position: absolute;
                  right: 0;
                  top: 0;
                  opacity: 0;

                  z-index: 9;
                  &:checked ~ {
                      .checkmark {
                          &::before{
                              display: block;
                          }
                      }
                  }

                  &:disabled ~ {
                      .checkmark {
                          &::before{
                              background-color: #aaa;
                              border-color: #aaa;
                              cursor: not-allowed;
                          }
                      }
                  }
              }

              .checkmark {
                  cursor: pointer;
                  position: relative;
                  width: 22px;
                  height: 22px;
                  border: 1px solid #ced4dc;
                  background-color: #fff;
                  margin: 0 auto;
                  border-radius: .25rem;

                  &::before{
                      display: none;
                      content: "";
                      background-color: #4599dd;
                      background-position: center;
                      background-size: 15px 15px;
                      background-repeat: no-repeat;
                      position: absolute;
                      right: -1px;
                      top: -1px;
                      border: 1px solid #4599dd;
                      width: 22px;
                      height: 22px;
                      border-radius: .25rem;
                      background-image: url("data:image/svg+xml,%3Csvg fill='none' height='24' stroke='%23fff' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' viewBox='0 0 24 24' width='24' xmlns='http://www.w3.org/2000/svg'%3E%3Cpolyline points='20 6 9 17 4 12'/%3E%3C/svg%3E");
                  }
              }
          }
           svg{
               cursor: pointer;
               margin: 0 auto;
              width:30px;
              height:30px;
              // path {
              //     fill: #495568;
              // }
          }
      }
      .group_reservations_tbl .table tbody tr td.td-fit {
        border-bottom: none;
        border-right: dimgray;
        display: flex;
        align-items: center;
        justify-content: center;
      }
      .group_reservations_tbl .table tbody tr td.td-fit a, .group_reservations_tbl .table tbody tr td.td-fit button {color: #b3b9bf;}
      .group_reservations_tbl .table tbody tr td.td-fit svg:hover {color: #3d92d4;}
      .group_reservations_tbl .table tbody tr td .font-bold {font-weight: normal;}
      .group_reservations_tbl .table tbody tr td a {color: #000000;}
      .group_reservations_tbl .table tbody tr td a:hover, .group_reservations_tbl .table tbody tr td button:hover {color: #3d92d4;}
      /* Portrait phones and smaller */
      @media (min-width: 320px) and (max-width: 480px) {
          .group_reservations_tbl {
              overflow: scroll;
              padding: 0 0 15px 0;
          }
      }

      /* Smart phones and Tablets */
      @media (min-width: 481px) and (max-width: 767px) {
          .group_reservations_tbl {
              overflow: scroll;
              padding: 0 0 15px 0;
          }
      }

      /* Small Screens */
      @media (min-width: 768px) and (max-width: 991px) {
          .group_reservations_tbl {
              overflow: scroll;
              padding: 0 0 15px 0;
          }
      }

      /* Medium Screens */
      @media (min-width: 992px) and (max-width: 1000px) {
          .group_reservations_tbl {
              overflow: scroll;
              padding: 0 0 15px 0;
          }
      }

    button[disabled] {
        opacity: 0.8;
        cursor: not-allowed !important;
    }

    /* Alternative scoped syntax */
    .disabled-datepicker >>> input {
    background-color: #ddd !important;
    cursor: not-allowed !important;
    }

    .v-date-picker >>> input {
        text-align: center !important;
    }

  </style>
