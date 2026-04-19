<template>
  <div>
    <div class="flex w-full mb-4">
      <nav v-if="crumbs.length">
        <ul class="breadcrumbs">
          <li
            class="breadcrumbs__item"
            v-for="(crumb,i) in crumbs"
            :key="i"
          >
            <router-link :to="crumb.to">{{ __(crumb.text) }}</router-link>
          </li>
        </ul>
      </nav>
    </div>

    <div class="search_criteria">
      <div class="search_criteria_radios">
          <div class="title">{{__('Search Date Criteria')}} : </div>
          <div class="radios_area">
              <label class="custom_radio" for="date_in">
                  <input type="radio" id="date_in" value="date_in" v-model="search_criteria">
                  <span class="checkmark"></span>
                  <p>{{__('Date in')}}</p>
              </label><!-- custom_radio -->
              <label class="custom_radio" for="date_out">
                  <input type="radio" id="date_out" value="date_out" v-model="search_criteria">
                  <span class="checkmark"></span>
                  <p>{{__('Date out')}}</p>
              </label><!-- custom_radio -->
              <label class="custom_radio" for="all">
                  <input type="radio" id="all" value="all" v-model="search_criteria">
                  <span class="checkmark"></span>
                  <p>{{__('All')}}</p>
              </label><!-- custom_radio -->
            
  
          </div><!-- radios_area -->
      </div>
    </div>
    <div id="reservations_management_page" class="relative">
      <div class="title">{{ __("Reservations Management") }}</div>
      <div class="content_page">
        <!-- Filters -->
        <div class="filter_area">
          <div class="item">
            <date-picker-from
              :enable-seconds="false"
              :enable-time="false"
              :date-format="'Y-m-d'"
              :twelve-hour-time="false"
              :locale="locale"
              :value="dateFrom"
              :placeholder="__('Date From')"
            />
          </div>
          <!-- item -->
          <div class="item">
            <date-picker-to
              :enable-seconds="false"
              :enable-time="false"
              :date-format="'Y-m-d'"
              :twelve-hour-time="false"
              :locale="locale"
              :value="dateTo"
              :placeholder="__('Date To')"
            />
          </div>
          <!-- item -->
          <div class="item">
            <input
              type="text"
              autocomplete="off"
              v-model="reservationNumber"
              @keydown.enter="getReservations"
              :placeholder="__('Reservation Number')"
            />
          </div>
          <!-- item -->
          <div class="item">
            <select v-model="reservationStatus" @change="getReservations">
              <option value="null" disabled>
                {{ __("Reservation Status") }}
              </option>

              <option value="open_all">
                {{ __("Open Reservations (All)") }}
              </option>


              <option value="closed_all">
                {{ __("Closed Reservations (All)") }}
              </option>

              <option value="checked_in">
                {{ __("Checked In Reservation") }}
              </option>
              <option value="checked_out">
                {{ __("Checked Out Reservation") }}
              </option>
              <option value="pending">{{ __("Pending Reservation") }}</option>
                <option value="all">
                {{ __("Confirmed Reservations (All)") }}
              </option>
              <option value="canceled">{{ __("Canceled Reservation") }}</option>
              <option
                v-if="payment_preprocessor == 'sure-bills'"
                value="timeout"
              >
                {{ __("Timeout Reservations") }}
              </option>
              <option
                v-if="payment_preprocessor == 'sure-bills'"
                value="awaiting-payment"
              >
                {{ __("Awaiting Payment Reservations") }}
              </option>
              <option
                v-if="payment_preprocessor == 'fandaqah'"
                value="awaiting-confirmation"
              >
                {{ __("Awaiting confirmation") }}
              </option>
            </select>
          </div>
          <!-- item -->
          <div class="item">
            <input
              type="text"
              autocomplete="off"
              v-model="customerName"
              @input="getReservations"
              :placeholder="__('Customer Name')"
            />
          </div>
          <!-- item -->
          <div class="item">
            <select
              v-model="unitId"
              v-if="unitsOptions.length"
              @change="getReservations"
            >
              <option value="null" :selected="true">
                {{ __("Unit Number") }}
              </option>
              <option
                v-for="(unit, i) in unitsOptions"
                :key="i"
                :value="unit.value"
              >
                {{ unit.name }}
              </option>
            </select>
          </div>
          <!-- item -->
            <!-- item -->
          <div class="item">
            <select
              v-model="unitCategoryId"
              v-if="unitCategories.length"
              @change="getReservations"
            >
              <option value="null" :selected="true">
                {{ __("Unit Category") }}
              </option>
              <option
                v-for="(category, i) in unitCategories"
                :key="i"
                :value="category.value"
              >
                {{ category.name }}
              </option>
            </select>
          </div>
          <!-- item -->
          <!-- <div class="item">
                        <input
                            type="text"
                            autocomplete="off"
                            v-model="unitName"
                            @input="getReservations"
                            :placeholder="__('Unit Name')"
                        >
                    </div> -->
          <div class="item">
            <select v-model="reservationType" @change="getReservations">
              <option value="all" :selected="true">
                {{ __("Type of reservation") }}
              </option>
              <option value="single">{{ __("Single") }}</option>
              <option value="group">{{ __("Group") }}</option>
            </select>
          </div>
          <!-- item -->
          <div class="item">
            <select
              v-model="indebtednessType"
              @change="indebtednessTypeChanged"
            >
              <option value="null" :selected="true">
                {{ __("Type of indebtedness") }}
              </option>
              <option value="creditor">{{ __("Creditor") }}</option>
              <option value="debtor">{{ __("Debtor") }}</option>
            </select>
          </div>
          <!-- item -->
          <div class="item" v-if="showRentTypeFilter">
            <select v-model="rentType" @change="getReservations">
              <option value="null" :selected="true">
                {{ __("Rent Type") }}
              </option>
              <option value="daily">{{ __("Daily") }}</option>
              <option value="monthly">{{ __("Monthly") }}</option>
            </select>
          </div>
          <!-- item -->
          <div class="item">
            <select
              v-model="customerHighlight"
              v-if="customerCategories.length"
              @change="getReservations"
            >
              <option value="null" :selected="true">
                {{ __("Customer Category") }}
              </option>
              <option
                v-for="(category, i) in customerCategories"
                :key="i"
                :value="category.value"
              >
                {{ category.name }}
              </option>
            </select>
          </div>

           <div class="item">
            <select
              v-model="reservationSource"
              v-if="reservationSources.length"
              @change="getReservations"
            >
              <option value="null" :selected="true">
                {{ __("Source") }}
              </option>
              <option
                v-for="(source, i) in reservationSources"
                :key="i"
                :value="source.id"
              >
                {{ source.name[locale] }}
              </option>
            </select>
          </div>

          <div class="item">
              <input
                  type="text"
                  v-model="reservationSourceNumber"
                  :placeholder="__('Source number')"
                  @keydown.enter="getReservations"
              >
          </div>


          <div class="item">
            <select
              v-model="reservationService"
              v-if="reservationServices.length"
              @change="getReservations"
            >
              <option value="null" :selected="true">
                {{ __("Services included in the price") }}
              </option>
              <option
                v-for="(service, i) in reservationServices"
                :key="i"
                :value="service.id"
              >
                {{ locale == 'ar' ? service.name_ar : service.name_en }}
              </option>
            </select>
          </div>


          <!-- item -->
          <div class="reset_filters">
            <button
              @click="resetFilters"
              v-tooltip="{
                targetClasses: ['it-has-a-tooltip'],
                placement: 'top',
                content: __('Reset Filters'),
                classes: ['tooltip_reset'],
              }"
            ></button>
          </div>
          <!-- reset_filters -->
        </div>
        <!-- Filters Area -->

        <hr />

        <!-- Statistics Area -->
        <div class="statistics_area relative" v-if="canShowStatistics">
          <loading
            :active.sync="statisticsLoading"
            :can-cancel="true"
            :loader="'spinner'"
            :color="'#7e7d7f'"
            :opacity="0.8"
            :is-full-page="false"
          ></loading>
          <ul>
            <li>
              <span>{{ __("Total Reservation Amount") }}</span>
              <p class="d-flex">{{ statistics.total_amount }} <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></p>
            </li>
            <li>
              <span>{{ __("Total Income") }}</span>
              <p class="d-flex">{{ statistics.total_income }} <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></p>
            </li>
            <li>
              <span>{{ __("Total Rent") }}</span>
              <p class="d-flex">{{ statistics.total_rent }} <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></p>
            </li>
            <li>
              <span>{{ __("Total Services") }}</span>
              <p class="d-flex">{{ statistics.total_services }} <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></p>
            </li>
            <li>
              <span>{{ __("Total Taxes") }}</span>
              <p class="d-flex">{{ statistics.total_taxes }} <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></p>
            </li>
            <li>
              <span>{{ __("Total Cost") }}</span>
              <p class="d-flex">{{ statistics.total_cost }} <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></p>
            </li>
            <li>
              <span>{{ __("Total Receipts") }}</span>
              <p class="d-flex">{{ statistics.total_receipts }} <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></p>
            </li>
            <li>
              <span>{{ __("Balance") }}</span>
              <p class="d-flex">{{ statistics.the_total_credit }} <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></p>
            </li>
            <li>
              <span>{{ __("Total Debtor") }}</span>
              <p class="totalDebtor d-flex">
                {{ Math.abs(statistics.total_debtor) }} <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span>
              </p>
            </li>
            <li>
              <span>{{ __("Total Creditor") }}</span>
              <p class="totalCreditor d-flex">
                {{ statistics.total_creditor }} <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span>
              </p>
            </li>
          </ul>
          <hr />
        </div>
        <!-- Statistics Area -->

        <!-- Export Options -->
        <div v-if="reservations_ids.length" class="action_buttons">
          <button
            type="button"
            class="excel_button"
            @click="excelExport"
          ></button>
          <button
            type="button"
            class="print_button"
            @click="printReport"
          ></button>
        </div>
        <!-- Export Options -->

        <!-- Reservations Table -->
        <div class="overflow-hidden overflow-x-auto relative">
          <loading
            :active.sync="isLoading"
            :can-cancel="true"
            :loader="'spinner'"
            :color="'#7e7d7f'"
            :opacity="0.8"
            :is-full-page="false"
          ></loading>
          <div class="main_reservations_table rounded overflow-hidden">
            <table class="table w-full" cellpadding="0" cellspacing="0">
              <thead>
                <tr>
                  <th colspan="6"></th>
                  <th colspan="6">{{ __("Reservation") }}</th>
                  <th colspan="3">{{ __("The Due") }}</th>

                  <th :colspan="is_integration_shms ? '4' : '3'">
                    {{ __("Finance") }}
                  </th>
                  <th></th>
                </tr>
              </thead>
              <thead>
                <tr>
                  <th>{{ __("Reservation Number") }}</th>
                  <th>{{ __("Customer") }}</th>
                  <th>{{ __("The Unit") }}</th>
                  <th>{{ __("Status") }}</th>
                  <th>{{ __("Reservation Status") }}</th>
                  <th>{{ __("Source") }}</th>
                  <th>{{ __("Rent Type") }}</th>
                  <th>{{ __("Date In") }}</th>
                  <th>{{ __("Date Out") }}</th>
                  <th>{{ __("Nights Count") }}</th>
                  <th>{{ __("Leasing") }}</th>
                  <th>{{ __("Services") }}</th>
                  <th>{{ __("Amount") }}</th>
                  <th>{{ __("Taxes") }}</th>
                  <th>{{ __("The Total") }}</th>
                  <th>{{ __("Paid") }}</th>
                  <th>{{ __("Creditor") }}</th>
                  <th>{{ __("Debtor") }}</th>
                  <th v-if="is_integration_shms">{{ __("shomos status") }}</th>
                  <th>{{ __("Actions") }}</th>
                </tr>
              </thead>
              <tbody>
                <template v-if="collection.length">
                  <!-- loop through collection of reservations and draw your tr -->
                  <tr v-for="(reservation, i) in collection" :key="i">
                    <td>
                      <div class="res_number">
                        <router-link
                          style="direction: ltr"
                          class="text-primary"
                          :to="{
                            name: reservation.customer_id
                              ? 'reservation'
                              : 'reservation-noc',
                            params: { id: reservation.id },
                          }"
                          >#{{ reservation.reservation_number }}</router-link
                        >
                        <svg
                          v-if="
                            reservation.reservation_type == 'group' &&
                            reservation.attachable_id == null
                          "
                          v-tooltip="{
                            targetClasses: ['it-has-a-tooltip'],
                            placement: 'top',
                            content: __('Main Reservation'),
                            classes: ['tooltip_reset'],
                          }"
                          xmlns="http://www.w3.org/2000/svg"
                          width="30"
                          height="19.05"
                          viewBox="0 0 39.554 25.114"
                        >
                          <path
                            d="M34.208 11.051a4.516 4.516 0 1 0-5.181 0 7.824 7.824 0 0 0-2.669 1.565 10.125 10.125 0 0 0-3.663-2 5.721 5.721 0 1 0-5.917 0 10.209 10.209 0 0 0-3.624 1.972 7.888 7.888 0 0 0-2.637-1.534 4.516 4.516 0 1 0-5.181 0A7.934 7.934 0 0 0 0 18.548v.517a.034.034 0 0 0 .031.031H9.6a10.526 10.526 0 0 0-.086 1.323v.532a4.162 4.162 0 0 0 4.164 4.164h12.133a4.162 4.162 0 0 0 4.164-4.164v-.532a10.525 10.525 0 0 0-.086-1.323h9.634a.034.034 0 0 0 .031-.031v-.517a7.965 7.965 0 0 0-5.346-7.497Zm-5.854-3.7a3.264 3.264 0 1 1 3.326 3.264h-.125a3.259 3.259 0 0 1-3.201-3.266Zm-13.1-1.62a4.469 4.469 0 1 1 4.727 4.461h-.517a4.481 4.481 0 0 1-4.211-4.463ZM4.641 7.349a3.264 3.264 0 1 1 3.326 3.264h-.125a3.264 3.264 0 0 1-3.201-3.264Zm5.181 10.487H1.268a6.687 6.687 0 0 1 6.59-5.971h.094a6.617 6.617 0 0 1 4.265 1.589 10.368 10.368 0 0 0-2.395 4.382Zm18.885 3.123A2.916 2.916 0 0 1 25.8 23.87H13.665a2.916 2.916 0 0 1-2.911-2.911v-.532a8.99 8.99 0 0 1 8.711-8.977c.086.008.18.008.266.008s.18 0 .266-.008a8.99 8.99 0 0 1 8.711 8.977Zm.931-3.123a10.249 10.249 0 0 0-2.371-4.351 6.649 6.649 0 0 1 4.3-1.62h.094a6.687 6.687 0 0 1 6.59 5.971Z"
                            fill="#4099de"
                          />
                        </svg>
                      </div>
                    </td>
                    <td
                      v-if="
                        reservation.customer_id &&
                        reservation.reservation_type == 'single'
                      "
                    >
                      <router-link
                        class="text-primary"
                        :to="{
                          path: `/new/customers/${reservation.customer_id}`,
                        }"
                        >{{ reservation.customer_name }}</router-link
                      >
                    </td>
                    <td
                      v-if="
                        reservation.company &&
                        reservation.reservation_type == 'group'
                      "
                    >
                      <router-link
                        class="text-primary"
                        :to="{
                          path: `/companies/${reservation.company.id}/profile`,
                        }"
                        >{{ reservation.company.name }}</router-link
                      >
                    </td>
                    <td v-if="reservation.unit_id">
                      <router-link
                        class="text-primary"
                        :to="{
                          path: `/resources/units/${reservation.unit_id}`,
                        }"
                        >{{ reservation.unit_number }} -
                        {{ reservation.unit_name }}
                      </router-link>
                    </td>
                    <td v-else>-</td>
                    <td>
                      <span
                        v-if="reservation.status === 'confirmed'"
                        class="indicators enabled"
                        >{{ __("confirmed") }}</span
                      >
                      <span
                        v-if="reservation.status === 'canceled'"
                        class="indicators not_enabled"
                        >{{ __("canceled") }}</span
                      >
                      <span
                        v-if="reservation.status === 'timeout'"
                        class="indicators timeout"
                        >{{ __("timeout") }}</span
                      >
                      <span
                        v-if="reservation.status === 'awaiting-payment'"
                        class="indicators awaiting_payment"
                        >{{ __("awaiting-payment") }}</span
                      >
                      <span
                        v-if="reservation.status === 'awaiting-confirmation'"
                        class="indicators awaiting_payment"
                        >{{ __("Awaiting confirmation") }}</span
                      >
                    </td>
                    <td>
                      <div v-if="!reservation.checked_in" class="pending">
                        {{ __("Pending") }}
                      </div>
                      <div
                        v-if="
                          reservation.checked_in && !reservation.checked_out
                        "
                        class="checked_in"
                      >
                        {{ __("Checked in") }}
                      </div>
                      <div
                        v-if="reservation.checked_in && reservation.checked_out"
                        class="checked_out"
                      >
                        {{ __("Checked out") }}
                      </div>
                    </td>
                    <td>
                      {{reservation.source ? reservation.source : '-'}}

                      <span v-if="reservation.source_num">({{ reservation.source_num }})</span>
                    </td>
                    <td>
                      {{
                        reservation.rent_type === 1
                          ? __("Daily")
                          : __("Monthly")
                      }}
                    </td>
                    <td>{{ reservation.date_in | formatDateSpecial }}</td>
                    <td>{{ reservation.date_out | formatDateSpecial }}</td>
                    <td>{{ reservation.nights }}</td>
                    <td>{{ reservation.leasing_price.toFixed(2) }}</td>
                    <td>{{ reservation.services_price.toFixed(2) }}</td>
                    <td>{{ reservation.amount.toFixed(2) }}</td>
                    <td>{{ reservation.taxes }}</td>
                    <td>{{ reservation.total.toFixed(2) }}</td>
                    <td>{{ reservation.paid.toFixed(2) }}</td>

                    <td>
                      <div v-if="reservation.reservation_type == 'single'">
                        <div v-if="reservation.balance > 0" class="green">
                          {{ parseFloat(reservation.balance).toFixed(2) }}
                        </div>
                        <div v-else>-</div>
                      </div>
                      <div v-else>
                        <div
                          v-if="
                            reservation.groupReservationBalanceMapper &&
                            reservation.groupReservationBalanceMapper.balance >
                              0
                          "
                          class="green"
                        >
                          {{
                            parseFloat(
                              reservation.groupReservationBalanceMapper.balance
                            ).toFixed(2)
                          }}
                        </div>
                        <div v-else>-</div>
                      </div>
                    </td>

                    <td>
                      <div v-if="reservation.reservation_type == 'single'">
                        <div v-if="reservation.balance < 0" class="red">
                          {{
                            parseFloat(Math.abs(reservation.balance)).toFixed(2)
                          }}
                        </div>
                        <div v-else>-</div>
                      </div>
                      <div v-else>
                        <div
                          v-if="
                            reservation.groupReservationBalanceMapper &&
                            reservation.groupReservationBalanceMapper.balance <
                              0
                          "
                          class="red"
                        >
                          {{
                            parseFloat(
                              Math.abs(
                                reservation.groupReservationBalanceMapper
                                  .balance
                              )
                            ).toFixed(2)
                          }}
                        </div>
                        <div v-else>-</div>
                      </div>
                    </td>

                    <td v-if="is_integration_shms">
                      <span
                        v-if="reservation.shomos_id"
                        class="indicators enabled"
                        data-toggle="tooltip"
                        data-placement="top"
                        title="Tooltip on top"
                      ></span>
                      <span
                        v-else
                        class="indicators not_enabled"
                        data-toggle="tooltip"
                        data-placement="top"
                        title="Tooltip on top"
                      ></span>
                    </td>
                    <td class="td-fit text-right pr-6 flex items-center">
                      <router-link
                        v-if="reservation.customer_id"
                        :to="{ path: `/reservation/${reservation.id}` }"
                        :title="__('View')"
                        class="cursor-pointer text-70 hover:text-primary mx-2"
                      >
                        <svg
                          xmlns="http://www.w3.org/2000/svg"
                          width="22"
                          height="18"
                          viewBox="0 0 22 16"
                          aria-labelledby="view"
                          role="presentation"
                          class="fill-current"
                        >
                          <path
                            d="M16.56 13.66a8 8 0 0 1-11.32 0L.3 8.7a1 1 0 0 1 0-1.42l4.95-4.95a8 8 0 0 1 11.32 0l4.95 4.95a1 1 0 0 1 0 1.42l-4.95 4.95-.01.01zm-9.9-1.42a6 6 0 0 0 8.48 0L19.38 8l-4.24-4.24a6 6 0 0 0-8.48 0L2.4 8l4.25 4.24h.01zM10.9 12a4 4 0 1 1 0-8 4 4 0 0 1 0 8zm0-2a2 2 0 1 0 0-4 2 2 0 0 0 0 4z"
                          ></path>
                        </svg>
                      </router-link>

                      <router-link
                        v-else
                        :to="{ path: `/reservation-noc/${reservation.id}` }"
                        :title="__('View')"
                        class="cursor-pointer text-70 hover:text-primary mx-2"
                      >
                        <svg
                          xmlns="http://www.w3.org/2000/svg"
                          width="22"
                          height="18"
                          viewBox="0 0 22 16"
                          aria-labelledby="view"
                          role="presentation"
                          class="fill-current"
                        >
                          <path
                            d="M16.56 13.66a8 8 0 0 1-11.32 0L.3 8.7a1 1 0 0 1 0-1.42l4.95-4.95a8 8 0 0 1 11.32 0l4.95 4.95a1 1 0 0 1 0 1.42l-4.95 4.95-.01.01zm-9.9-1.42a6 6 0 0 0 8.48 0L19.38 8l-4.24-4.24a6 6 0 0 0-8.48 0L2.4 8l4.25 4.24h.01zM10.9 12a4 4 0 1 1 0-8 4 4 0 0 1 0 8zm0-2a2 2 0 1 0 0-4 2 2 0 0 0 0 4z"
                          ></path>
                        </svg>
                      </router-link>

                      <button
                        @click="openClosedContractConfirmation(reservation)"
                        v-permission="'open closed contract'"
                        v-if="
                          ((reservation.checked_out && reservation.status == 'confirmed')
                          ||
                          (reservation.status == 'canceled')
                          )
                          && reservation.unit_id

                        "
                        :to="{
                          path: reservation.customer ?  `/reservation/${reservation.id}?occ=true` : `/reservation-noc/${reservation.id}?occ=true`,
                        }"
                        :title="__('Open Closed Contract')"
                        class="cursor-pointer text-70 hover:text-primary mx-2"
                      >
                        <svg
                          xmlns="http://www.w3.org/2000/svg"
                          viewBox="0 0 341.333 341.333"
                          width="20"
                          height="20"
                          xmlns:v="https://vecta.io/nano"
                        >
                          <path
                            d="M341.227 149.333V0l-50.133 50.133C260.267 19.2 217.707 0 170.56 0 76.267 0 .107 76.373.107 170.667s76.16 170.667 170.453 170.667c79.467 0 146.027-54.4 164.907-128h-44.373c-17.6 49.707-64.747 85.333-120.533 85.333-70.72 0-128-57.28-128-128s57.28-128 128-128c35.307 0 66.987 14.72 90.133 37.867l-68.8 68.8h149.333z"
                            fill="#b3b9bf"
                          />
                        </svg>
                      </button>

                      <!-- <button v-if="reservation.group_reservation" class="cursor-pointer text-70 hover:text-primary mx-2"
                                                @click="openUnlinkModal(reservation.id)"
                                            >
                                                <svg viewBox="0 0 512 512" width="20" height="20" xmlns="http://www.w3.org/2000/svg" class="fill-current"
                                                    v-tooltip="{
                                                            targetClasses: ['it-has-a-tooltip'],
                                                            placement: 'top',
                                                            content: __('Unlink Reservation'),
                                                            classes: ['tooltip_reset']
                                                    }"
                                                >
                                                    <path d="m304.08 405.91c4.686 4.686 4.686 12.284 0 16.971l-44.674 44.674c-59.263 59.262-155.69 59.266-214.96 0-59.264-59.265-59.264-155.7 0-214.96l44.675-44.675c4.686-4.686 12.284-4.686 16.971 0l39.598 39.598c4.686 4.686 4.686 12.284 0 16.971l-44.675 44.674c-28.072 28.073-28.072 73.75 0 101.82 28.072 28.072 73.75 28.073 101.82 0l44.674-44.674c4.686-4.686 12.284-4.686 16.971 0l39.597 39.598zm-56.568-260.22c4.686 4.686 12.284 4.686 16.971 0l44.674-44.674c28.072-28.075 73.75-28.073 101.82 0 28.072 28.073 28.072 73.75 0 101.82l-44.675 44.674c-4.686 4.686-4.686 12.284 0 16.971l39.598 39.598c4.686 4.686 12.284 4.686 16.971 0l44.675-44.675c59.265-59.265 59.265-155.7 0-214.96-59.266-59.264-155.7-59.264-214.96 0l-44.674 44.674c-4.686 4.686-4.686 12.284 0 16.971l39.597 39.598zm234.83 359.28 22.627-22.627c9.373-9.373 9.373-24.569 0-33.941l-441.37-441.37c-9.373-9.373-24.569-9.373-33.941 0l-22.628 22.628c-9.373 9.373-9.373 24.569 0 33.941l441.37 441.37c9.373 9.372 24.569 9.372 33.941 0z"/>
                                                </svg>
                                            </button> -->

                      <!-- <button type="button" @click="openDeleteConfirm(reservation)" :title="__('Delete')" class="appearance-none cursor-pointer text-70 hover:text-primary mx-2" v-permission="'delete reservations'">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" aria-labelledby="delete" role="presentation" class="fill-current"><path fill-rule="nonzero" d="M6 4V2a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2h5a1 1 0 0 1 0 2h-1v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6H1a1 1 0 1 1 0-2h5zM4 6v12h12V6H4zm8-2V2H8v2h4zM8 8a1 1 0 0 1 1 1v6a1 1 0 0 1-2 0V9a1 1 0 0 1 1-1zm4 0a1 1 0 0 1 1 1v6a1 1 0 0 1-2 0V9a1 1 0 0 1 1-1z"></path></svg>
                                            </button> -->
                    </td>
                  </tr>
                </template>
                <template v-else>
                  <tr>
                    <td colspan="18">{{ __("No Reservations Found") }}</td>
                  </tr>
                </template>
              </tbody>
            </table>
          </div>
        </div>
        <!-- Reservations Table -->
        <!-- Pagination -->
        <div class="w-full flex flex-wrap mt-3 justify-center">
          <pagination
            v-if="paginator.lastPage > 1"
            :page-count="paginator.lastPage"
            :page-range="3"
            :margin-pages="2"
            :click-handler="getReservations"
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
        </div>
        <!-- Pagination -->
        <div class="Results_area" v-if="collection.length">
          <p>
            {{ __("Results") }} : {{ __("From") }} ( {{ paginator.from }} ) -
            {{ __("To") }} ( {{ paginator.to }} )
          </p>
          <p>{{ __("Count") }} : {{ paginator.totalResults }}</p>
        </div>
        <!-- Results_area -->
      </div>
    </div>

    <!-- Print Form -->
    <form
      id="reservations"
      target="_blank"
      method="post"
      style="display: none"
      action="/home/print/reservations-print-report"
    >
      <input
        type="hidden"
        :value="JSON.stringify(reservations_ids)"
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

    <!-- Delete Reservation Modal -->
    <sweet-modal
      :enable-mobile-fullscreen="false"
      :pulse-on-block="false"
      :hide-close-button="true"
      overlay-theme="dark"
      ref="deleteReservation"
      class="delete_confirm_modal"
    >
      <div class="delete_confirm_modal_content">
        <loading :active.sync="isLoadingDelete" :is-full-page="false"></loading>
        <h1>{{ __("are you sure ?") }}</h1>
        <h2>
          {{ __("Deletion cannot be undone, would you like to continue ?") }}
        </h2>
        <!-- <h2 v-if="showUnlinkingAlert">{{__("The unlink process for this reservation will be made first")}}</h2> -->
        <div class="buttons_delete">
          <button
            id="confirm-delete-button"
            @click="deleteReservation"
            class="yes_delete_button"
          >
            {{ __("Yes, delete !") }}
          </button>
          <button type="button" @click="stepBack()" class="back_delete_button">
            {{ __("Do not retreat !") }}
          </button>
        </div>
        <!-- buttons_delete -->
      </div>
      <!-- delete_confirm_modal_content -->
    </sweet-modal>

    <!-- Unlink reservation  -->
    <sweet-modal
      :enable-mobile-fullscreen="false"
      :pulse-on-block="false"
      :hide-close-button="true"
      overlay-theme="dark"
      ref="unlinkReservation"
      class="delete_confirm_modal"
    >
      <div class="delete_confirm_modal_content">
        <loading :active.sync="isLoadingUnlink" :is-full-page="false"></loading>
        <h1>{{ __("are you sure ?") }}</h1>
        <h2>{{ __("Would you like to unlink this reservation?") }}</h2>
        <div class="buttons_delete">
          <button
            id="confirm-delete-button"
            @click="unlinkReservation"
            class="yes_delete_button"
          >
            {{ __("Yes, Unlink !") }}
          </button>
          <button
            type="button"
            @click="stepBackUnlink()"
            class="back_delete_button"
          >
            {{ __("Do not retreat !") }}
          </button>
        </div>
        <!-- buttons_delete -->
      </div>
      <!-- delete_confirm_modal_content -->
    </sweet-modal>

    <open-closed-contract-confirm :reservation="targetReservation" />
  </div>
</template>

<script>
import OpenClosedContractConfirm from "./OpenClosedContractConfirm";
import Pagination from "./Pagination";
import Loading from "vue-loading-overlay";
import DatePickerFrom from "./reservation_filters/DatePickerFrom";
import DatePickerTo from "./reservation_filters/DatePickerTo";
import XLSX from "xlsx";
export default {
  name: "ReservationManagementComponent",
  components: {
    Pagination,
    Loading,
    DatePickerFrom,
    DatePickerTo,
    OpenClosedContractConfirm,
  },
  data() {
    return {
      is_integration_shms: false,
      locale: null,
      crumbs: [],
      unitsOptions: [],
      customerCategories: [],
      reservationSources : [],
      reservationSource : null,
      dateFrom: null,
      dateTo: null,
      reservationNumber: null,
      reservationStatus: 'open_all',
      customerName: null,
      unitId: null,
      unitName: null,
      indebtednessType: null,
      rentType: null,
      customerHighlight: null,
      collection: [],
      paginator: {},
      isLoading: true,
      reservations_ids: [],
      target_to_delete: null,
      isLoadingDelete: false,
      statistics: {
        total_amount: parseFloat(0).toFixed(2),
        total_income: parseFloat(0).toFixed(2),
        total_rent: parseFloat(0).toFixed(2),
        total_services: parseFloat(0).toFixed(2),
        total_taxes: parseFloat(0).toFixed(2),
        total_cost: parseFloat(0).toFixed(2),
        total_receipts: parseFloat(0).toFixed(2),
        the_total_credit: parseFloat(0).toFixed(2),
        total_debtor: parseFloat(0).toFixed(2),
        total_creditor: parseFloat(0).toFixed(2),
      },
      reservationsData: [],
      statisticsLoading: false,
      targetContract: null,
      canShowStatistics: false,
      defaultStatus: "confirmed",
      payment_preprocessor: Spark.state.currentTeam.payment_preprocessor,
      isLoadingUnlink: false,
      target_unlink_id: null,
      showUnlinkingAlert: false,
      reservationType: "all",
      currency :Nova.app.currentTeam.currency,
      showRentTypeFilter : true,
      reservationService : null,
      reservationServices : [],
      reservationSourceNumber : null,
      search_criteria : 'all',
      unitCategories : [],
      unitCategoryId : null,
      targetReservation : null
    };
  },
  methods: {
    getReservationServices(){
        axios.get('/nova-vendor/settings/getReservationServices?all=true')
        .then(response => {
            this.reservationServices = response.data;
        })

    },
            
    indebtednessTypeChanged() {
      if (this.reservationType == "all") {
        this.$toasted.show(Nova.app.__("Please select reservation type"), {
          duration: 4000,
          type: "error",
          position: "top-center",
        });
        return;
      } else {
        this.getReservations();
      }
    },
    formatter: (value, amount) => {
      const split = value.toString().split(".");
      if (split.length > 1) {
        split[split.length - 1] = split[split.length - 1].substring(0, amount);
      }
      return amount > 0 ? split.join(".") : split[0];
    },
    openClosedContractConfirmation(reservation) {
      this.targetReservation = reservation;
      this.targetContract = reservation.id;
      // if(reservation.unit_status != 1){
      //     this.$toasted.show(this.__('Contract can not be opened because unit :name - number :number is under :status' , {name : reservation.unit_name ,number : reservation.unit_number , status : reservation.unit_status ==2 ? this.__('Under Cleaning') : this.__('Under Maintenance')}), {type: 'error'});
      //     return;
      // }
      Nova.$emit("open-closed-contract");
    },
    resetFilters() {
      Nova.$emit("reset-dates");
      this.dateFrom = null;
      this.dateTo = null;
      this.reservationNumber = null;
      this.reservationStatus = 'open_all';
      this.customerName = null;
      this.unitId = null;
      this.unitName = null;
      this.indebtednessType = null;
      this.reservationType = "all";
      this.rentType = null;
      this.customerHighlight = null;
      this.reservationSource = null;
      this.reservationService = null;
      this.defaultStatus = "confirmed";
      this.reservationSourceNumber = null;
      this.unitCategoryId = null;
      this.getReservations();
    },

    getStatistics() {
      this.statisticsLoading = true;
      Nova.request()
        .post("/nova-vendor/calender/reservations-data-statistics", {
          params: this.reservations_ids,
        })
        .then((response) => {
          this.statistics = response.data;
          this.statisticsLoading = false;
        });
    },
    getReservations(page = 1) {
      let url = `/nova-vendor/calender/reservations-data?page=${page}
                                    &dateFrom=${this.dateFrom}
                                    &dateTo=${this.dateTo}
                                    &reservationNumber=${this.reservationNumber}
                                    &reservationStatus=${this.reservationStatus}
                                    &customerName=${this.customerName}
                                    &unitId=${this.unitId}
                                    &unitName=${this.unitName}
                                    &reservationType=${this.reservationType}
                                    &indebtednessType=${this.indebtednessType}
                                    &rentType=${this.rentType}
                                    &customerHighlight=${this.customerHighlight}
                                    &reservationSource=${this.reservationSource}
                                    &reservationService=${this.reservationService}
                                    &reservationSourceNumber=${this.reservationSourceNumber}
                                    &search_criteria=${this.search_criteria}
                                    &unitCategoryId=${this.unitCategoryId}
                                    `;

      this.isLoading = true;
      this.reservationsData = [];
      Nova.request()
        .get(url)
        .then((response) => {
          this.collection = response.data.data;
          this.reservations_ids = response.data.ids;

          this.reservationsData.push(
            JSON.stringify(this.reservations_ids),
            JSON.stringify(this.statistics)
          );
          this.paginator = {
            currentPage: response.data.meta.current_page,
            lastPage: response.data.meta.last_page,
            from: response.data.meta.from,
            to: response.data.meta.to,
            totalResults: response.data.meta.total,
            pathPage: response.data.meta.path + "?page=",
            firstPageUrl: response.data.links.first,
            lastPageUrl: response.data.links.last,
            nextPageUrl: response.data.links.next,
            prevPageUrl: response.data.links.prev,
          };

          this.isLoading = false;
          this.getStatistics();
        });
    },
    getUnitsFilterValues() {
      Nova.request()
        .get("/nova-vendor/calender/reservations/units-filter-values")
        .then((response) => {
          this.unitsOptions = response.data;
        });
    },
    getCustomerCategories() {
      Nova.request()
        .get("/nova-vendor/calender/reservations/customer-categories")
        .then((response) => {
          this.customerCategories = response.data;
        });
    },
    getReservationsSources() {
      Nova.request()
        .get("/nova-vendor/calender/reservations/sources")
        .then(response => {
          this.reservationSources = response.data;
        });
    },
    stepBack() {
      this.$refs.deleteReservation.close();
    },
    openDeleteConfirm(reservation) {
      this.showUnlinkingAlert =
        reservation.reservation_type == "group" ? true : false;
      if (
        reservation.reservation_type == "group" &&
        reservation.attachable_reservations_count
      ) {
        this.$toasted.show(this.__("You can not delete main reservation"), {
          type: "error",
        });
        return false;
      }
      this.target_to_delete = reservation.id;
      this.$refs.deleteReservation.open();
    },
    deleteReservation() {
      this.isLoadingDelete = true;
      Nova.request()
        .delete(
          `/nova-vendor/calender/reservations/${this.target_to_delete}/delete`
        )
        .then((response) => {
          this.isLoadingDelete = false;
          this.$refs.deleteReservation.close();
          this.getReservations();
        });
    },
    printReport() {
      $("#reservations").submit();
    },
    excelExport() {
      this.isLoading = true;
      Nova.request()
        .post("/nova-vendor/calender/reservations/management/excel-report", {
          params: this.reservationsData,
        })
        .then((response) => {
          this.isLoading = false;
          let defaultCellStyle = {
            font: { name: "Verdana", sz: 11, color: "FF00FF88" },
            fill: { fgColor: { rgb: "FFFFAA00" } },
          };
          // Data coming from my tool controller
          let dataToBeExported = response.data.data;
          // Export Json Data as worksheet
          let transactionsWs = XLSX.utils.json_to_sheet(dataToBeExported);
          // New workbook instance
          let wb = XLSX.utils.book_new(); // make Workbook of Excel
          // Adding worksheet to workbook
          XLSX.utils.book_append_sheet(
            wb,
            transactionsWs,
            response.data.filename
          ); // sheetAName is name of Worksheet

          // Export file
          XLSX.writeFile(wb, response.data.filename + ".xlsx", {
            defaultCellStyle: defaultCellStyle,
          });
          // fire success toast
          this.$toasted.show(this.__("Report was exported successfully"), {
            type: "success",
          });
        });
    },
    openUnlinkModal(id) {
      this.target_unlink_id = id;
      this.$refs.unlinkReservation.open();
    },
    stepBackUnlink() {
      this.$refs.unlinkReservation.close();
    },
    unlinkReservation() {
      this.isLoadingUnlink = true;
      axios
        .post(
          `/nova-vendor/calender/unlink-reservation/${this.target_unlink_id}`
        )
        .then((response) => {
          this.isLoadingUnlink = false;
          this.getReservations();
          this.stepBackUnlink();
          this.$toasted.show(this.__("Unlinking process went successfully"), {
            type: "success",
          });
        });
    },
    handleRadio(){
      if((this.dateFrom != null && this.dateFrom != "") ||
      (this.dateTo != null && this.dateTo != "")){
        this.getReservations();
      }
    },
    getUnitCategoryFilterValues() {
      Nova.request()
        .get("/nova-vendor/calender/reservations/unit-category-filter-values")
        .then((response) => {
          this.unitCategories = response.data;
        });
    },
  },
  computed: {
    dateValues() {
      const { dateFrom, dateTo } = this;
      return {
        dateFrom,
        dateTo,
      };
    },
  },
  watch: {

    search_criteria: {
        handler : function(val){
          if(val && ( (this.dateFrom != null && this.dateFrom != "") || (this.dateTo != null && this.dateTo != "") )){
            this.search_criteria = val;
            this.getReservations();
          }
        },
        deep: true,
        immediate: true
    },
    // reservationStatus: {
    //     handler : function(old , newVal){

    //         if(newVal == 'checked_in' || newVal == 'checked_out' || newVal == 'pending'){
    //             this.defaultStatus = 'confirmed';
    //         }else{
    //             if(newVal == 'canceled'){
    //                 this.defaultStatus = 'canceled';
    //             }

    //             if(newVal == 'timeout'){
    //                 this.defaultStatus = 'timeout';
    //             }

    //             if(newVal == 'awaiting-payment'){
    //                 this.defaultStatus = 'awaiting-payment';
    //             }
    //         }
    //     },
    //     deep: true,
    //     immediate: true
    // },
    dateValues: {
      handler: function (val) {
        if (
          (val.dateFrom != null && val.dateFrom != "") ||
          (val.dateTo != null && val.dateTo != "")
        ) {
          this.getReservations();
        }
      },
      deep: true,
    },
  },
  mounted() {
    this.crumbs = [
      {
        text: "Home",
        to: "/dashboards/main",
      },
      {
        text: "Reservations Management",
        to: "#",
      },
    ];
    this.getReservations();
    this.getUnitsFilterValues();
    this.getCustomerCategories();
    this.getReservationsSources();
    this.getUnitCategoryFilterValues();

    Nova.$on("to-change", (val) => {
      this.dateTo = val;
    });
    Nova.$on("from-change", (val) => {
      this.dateFrom = val;
    });

    if (Nova.app.$hasPermission("show statistics in reservation management")) {
      this.canShowStatistics = true;
    }

    if(Nova.app.currentTeam.last_subscription && Nova.app.currentTeam.last_subscription.stripe_plan == 'monthly-yearly'){
      this.showRentTypeFilter = false;
    }

    this.getReservationServices();
  },
  created() {
    this.is_integration_shms = Spark.state.currentTeam.integration_shms;
    this.locale = Nova.config.local;
  },
};
</script>

<style lang="scss" scoped>

.search_criteria {
    border-radius: 5px;
    border: 1px solid #ddd;
    padding: 10px 10px 0;
    background: #fff;
    box-shadow: 0 2px 4px 0 rgba(0, 0, 0, 0.05);
    height: 100%;
    margin-bottom: 5px;
  .search_criteria_radios{
    display: flex;
    align-items: center;
    justify-content: flex-start;
    padding: 0 0 10px;
    // border-bottom: 1px solid #ddd;
    margin-bottom: 10px;

    .title {
        margin: 0 0 0 20px;
          @media (min-width: 320px) and (max-width: 767px) {
            margin-bottom: 5px;
        }
    }
    .radios_area {
    display: flex;
    align-items: center;
    flex-wrap: wrap;

    label.custom_radio {
        display: block;
        position: relative;
        padding: 0 30px 0 0;
        cursor: pointer;
        color: #7E8790;
        line-height: 30px;
        margin: 0 0 0 50px;
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;
        -webkit-transition: all 0.2s ease-in-out;
        -moz-transition: all 0.2s ease-in-out;
        -o-transition: all 0.2s ease-in-out;
        transition: all 0.2s ease-in-out;
        [dir="ltr"] & {
            padding: 0 0 0 30px;
            margin: 0 50px 0  0;
        } /* rtl */
        &:hover {
            .checkmark {background: #fafafa;}
            p {color: #444444;}
        } /* hover */
        input {
            position: absolute;
            opacity: 0;
            cursor: pointer;
            height: 0;
            width: 0;
            &:checked ~ {
                .checkmark {
                    background: #fafafa;
                    &::after {
                        opacity: 1;
                        visibility: visible;
                        -webkit-transform: scale(1);
                        -moz-transform: scale(1);
                        -o-transform: scale(1);
                        transform: scale(1);
                    } /* after */
                } /* checkmark */
                p {color: #0A80D8;}
            } /* checked */
        } /* input */
        .checkmark {
            position: absolute;
            top: 0;
            right: 0;
            height: 20px;
            width: 20px;
            background-color: #fcfcfc;
            border: 1px solid #e8e8e8;
            border-radius: 100%;
            -webkit-transition: all 0.2s ease-in-out;
            -moz-transition: all 0.2s ease-in-out;
            -o-transition: all 0.2s ease-in-out;
            transition: all 0.2s ease-in-out;
            [dir="ltr"] & {
                right: auto;
                left: 0;
            } /* rtl */
            &::after {
                content: "";
                background: #0A80D8;
                position: absolute;
                top: 4px;
                right: 4px;
                width: 10px;
                height: 10px;
                opacity: 0;
                visibility: hidden;
                border-radius: 100%;
                -webkit-transform: scale(0);
                -moz-transform: scale(0);
                -o-transform: scale(0);
                transform: scale(0);
                -webkit-transition: all 0.2s ease-in-out;
                -moz-transition: all 0.2s ease-in-out;
                -o-transition: all 0.2s ease-in-out;
                transition: all 0.2s ease-in-out;
            } /* after */
        } /* checkmark */
        p {
            display: block;
            line-height: 20px;
            font-size: 16px;
            color: #000;
            -webkit-transition: all 0.2s ease-in-out;
            -moz-transition: all 0.2s ease-in-out;
            -o-transition: all 0.2s ease-in-out;
            transition: all 0.2s ease-in-out;
        } /* p */
        &:last-of-type{
            margin: 0;
        }
          @media (min-width: 320px) and (max-width: 767px) {
            margin: 0;
        }
    } /* label */
    @media (min-width: 320px) and (max-width: 767px) {
        display: grid;
        gap: 5px;
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
  } /* radios_area */
    @media (min-width: 320px) and (max-width: 767px) {
        flex-direction: column;
    }
  } /* search_criteria_radios */                
}


.text-timeout {
  color: #a07930;
}

.text-awaiting {
  --text-opacity: 1;
  color: rgba(66, 153, 225, var(--text-opacity));
}
.green {
  --text-opacity: 1;
  color: rgba(72, 187, 120, var(--text-opacity));
}

.red {
  --text-opacity: 1;
  color: rgba(245, 101, 101, var(--text-opacity));
}

.pending {
  --text-opacity: 1;
  color: rgba(66, 153, 225, var(--text-opacity));
}
.checked_in {
  --text-opacity: 1;
  color: rgba(72, 187, 120, var(--text-opacity));
}
.checked_out {
  --text-opacity: 1;
  color: rgba(245, 101, 101, var(--text-opacity));
}

.delete_confirm_reservation {
  h2 {
    line-height: 63px;
  } /* h2 */
  span {
    padding: 30px 20px;
    line-height: normal;
    display: block;
    font-size: 20px;
    color: #000;
  } /* span */
} /* delete_confirm_slider_image */

span.indicators {
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
      background: #38c172;
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
  &.timeout {
    &::after {
      background: #a07930;
    } /* after */
  } /*not_enabled  */

  &.awaiting_payment {
    &::after {
      background: #4299e1;
    } /* after */
  } /*not_enabled  */
} /* span */
#reservations_management_page {
  margin: 0 auto;
  border: 1px solid #ddd;
  border-radius: 0.5rem;
  box-shadow: 0 2px 4px 0 rgba(0, 0, 0, 0.05);
  overflow: hidden;
  .title {
    background: #f7fafc;
    border-bottom: 1px solid #ddd;
    padding: 0.75rem;
    color: #000;
    font-size: 1.125rem;
    display: block;
  } /* title */
  .content_page {
    background: #fff;
    padding: 10px;
    .filter_area {
      display: flex;
      align-items: center;
      flex-wrap: wrap;
      justify-content: flex-start;
      margin: 0 -10px;
      .item {
        width: 20%;
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
            background-color: #5e6d83;
          } /* hover */
        } /* button */
      } /* reset_filters */
    } /* filter_area */
    hr {
      margin: 20px auto;
      border-color: #ddd;
      &:last-of-type {
        margin: 0 0 20px;
      } /* last-of-type */
    } /* hr */
    .statistics_area {
      ul {
        display: flex;
        align-items: flex-start;
        justify-content: flex-start;
        flex-wrap: wrap;
        margin: 0 -10px;
        li {
          width: 20%;
          padding: 0 10px;
          margin: 0 0 20px;
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
            color: #000;
            margin: 0 0 5px;
          } /* span */
          p {
            display: block;
            font-size: 16px;
            font-weight: bold;
            &.totalDebtor {
              color: #f56565;
            } /* totalDebtor */
            &.totalCreditor {
              color: #48bb78;
            } /* totalCreditor */
          } /* p */
        } /* li */
      } /* ul */
    } /* statistics_area */
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
} /* reservations_management_page */
</style>
<style lang="css" scoped>
.cursor-pointer.inline-flex.items-center svg {
  display: none;
}
.main_reservations_table {
  overflow: auto;
  width: 100%;
  padding: 0 0 15px 0;
  background: #fff;
}
.main_reservations_table .table {
  border: 1px solid #e2e8f0;
}
.main_reservations_table .table thead tr th {
  padding: 10px 5px;
  line-height: 20px;
  font-weight: normal;
  font-size: 15px;
  border: 1px solid #5e697c;
  vertical-align: middle;
  text-align: center;
  color: #ffffff;
  background: #4a5568;
}
.main_reservations_table .table tbody tr {
  background: #fff;
}
.main_reservations_table .table tbody tr td {
  text-align: center;
  padding: 10px 5px;
  vertical-align: middle;
  line-height: 20px;
  font-size: 15px;
  border: 1px solid #ced4dc;
  color: #000000;
  font-weight: normal;
  background: #ffffff;
}

.main_reservations_table .table tbody tr td .res_number {
  display: flex;
  align-items: center;
  justify-content: flex-start;
}

.main_reservations_table .table tbody tr td svg {
  margin: 0 10px 0 0;
}
.main_reservations_table .table tbody tr td svg path {
  fill: #555;
}

.main_reservations_table .table tbody tr td.td-fit {
  border-bottom: none;
  border-right: dimgray;
  display: flex;
  align-items: center;
  justify-content: center;
}
.main_reservations_table .table tbody tr td.td-fit a,
.main_reservations_table .table tbody tr td.td-fit button {
  color: #b3b9bf;
}
.main_reservations_table .table tbody tr td.td-fit svg:hover {
  color: #3d92d4;
}
.main_reservations_table .table tbody tr td .font-bold {
  font-weight: normal;
}
.main_reservations_table .table tbody tr td a {
  color: #000000;
}
.main_reservations_table .table tbody tr td a:hover,
.main_reservations_table .table tbody tr td button:hover {
  color: #3d92d4;
}
/* Portrait phones and smaller */
@media (min-width: 320px) and (max-width: 480px) {
  .main_reservations_table {
    overflow: scroll;
    padding: 0 0 15px 0;
  }
}

/* Smart phones and Tablets */
@media (min-width: 481px) and (max-width: 767px) {
  .main_reservations_table {
    overflow: scroll;
    padding: 0 0 15px 0;
  }
}

/* Small Screens */
@media (min-width: 768px) and (max-width: 991px) {
  .main_reservations_table {
    overflow: scroll;
    padding: 0 0 15px 0;
  }
}

/* Medium Screens */
@media (min-width: 992px) and (max-width: 1000px) {
  .main_reservations_table {
    overflow: scroll;
    padding: 0 0 15px 0;
  }
}

.d-flex{
  display: flex !important;
}
</style>
