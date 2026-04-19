<template>
  <div id="reservation_page">
    <nav v-if="!loading">
      <ul class="breadcrumbs">
        <li class="breadcrumbs__item"><router-link to="/" class="router-link-active">{{__('Home')}}</router-link></li>
        <li class="breadcrumbs__item">
          <router-link to="/reservations-management" class="router-link-exact-active router-link-active">{{ __('Reservations') }}</router-link>
        </li>
        <li class="breadcrumbs__item">
            <a class="router-link-exact-active router-link-active" href="#">#{{reservation.number}}</a>
        </li>
      </ul>
    </nav>
    <br>
    <div v-if="loading"><loader class="text-70" width="40"/></div>
    <!-- <online-reservation v-if="reservation && reservation.status == 'awaiting-confirmation'"  :reservation="reservation" /> -->
    <div class="main_reservation_buttons" v-if="reservation">
      <!-- <a onclick="$('#transactions_form').submit();" target="_blank" class="btn btn-default btn-primary mx-1 sm:btn-block cursor-pointer">
      {{__('Reservation Summary')}}
      </a> -->

      <div class="action-group-wrapper" v-if="reservation.reservation_type == 'group'">
          <!-- Overlay -->
          <transition name="fade">
            <div v-if="showActions" class="action-backdrop" @click="closeActions"></div>
          </transition>

          <!-- Trigger Button -->
          <button class="main_button gap-8 management-btn" @click="toggleActions">
              <span class="button-text">{{ __('Reservation Management') }}</span>
              <span class="button-icon">☰</span>
          </button>
          <!-- transactionBalance -->

        <button v-permission="'update transactions balance'" class="main_button gap-8" @click="openTransactionBalanceModal(reservation.id)">
            <span class="button-text">{{ __('Transactions Balance') }}</span>
            <span class="button-icon">💰</span>
        </button>
        <TransactionBalance ref="transactionBalanceModal" />

          <!-- Dropdown -->
          <transition name="fade">
            <div v-if="showActions" class="action-dropdown">
              <group-reservation-multiple-checkout
                v-if="reservation.status == 'confirmed'"
                :reservation="reservation" />

              <group-reservation-multiple-checkin
                v-if="reservation.status == 'confirmed'"
                :reservation="reservation" />

              <group-reservation-prices
                v-if="reservation.status == 'confirmed'"
                :reservation="reservation" />

              <group-reservation-extender-modal
                v-if="reservation.status == 'confirmed'"
                :reservation="reservation" />

              <group-reservation-multiple-cancel
                v-if="shouldShowCancel"
                :quick="false"
                :reservation="reservation"
                @update-reservation="updateReservation" />
            </div>
          </transition>
      </div>


      <reservation-summary :reservation="reservation" v-if="(reservation.status == 'confirmed' || reservation.status == 'canceled') && reservation.reservation_type == 'single' "></reservation-summary>
      <company-live-summary v-if="reservation.reservation_type == 'group'" :reservation="reservation" />
      <div class="item_reservation_button" v-if="reservation.reservation_type == 'single'">
        <button class="main_button" @click="openInvoiceModal" v-if="reservation.status == 'confirmed' || reservation.status == 'canceled' ">{{__('Invoice Prototype')}}</button>
        <button v-permission="'update transactions balance'" class="main_button gap-8" @click="openTransactionBalanceModal(reservation.id)">
            <span class="button-text">{{ __('Transactions Balance') }}</span>
            <span class="button-icon">💰</span>
        </button>
        <TransactionBalance ref="transactionBalanceModal" />
      </div><!-- item_reservation_button -->
      <company-live-invoice v-if="reservation.reservation_type == 'group'" :reservation="reservation" />
      <company-live-contract :check_sms="check_sms_getway" v-if="reservation.reservation_type == 'group'" :reservation="reservation" />
      <reservation-contract :check_user_signature="user.digital_signature" :check_sms="check_sms_getway" :reservation="reservation" v-if="(reservation.status == 'confirmed' || reservation.status == 'canceled') &&  reservation.reservation_type == 'single'"></reservation-contract>
      <reservation-guest v-if="reservation.status == 'confirmed'" v-on:update-reservation="updateReservation" :reservation="reservation"></reservation-guest>
      <reservation-check-in v-if="reservation.status == 'confirmed' && !reservation.checked_in" :quick="false" :reservation="reservation" v-on:update-reservation="updateReservation"></reservation-check-in>
      <reservation-check-out  v-if="reservation.status == 'confirmed' &&!reservation.checked_out && reservation.checked_in && reservation.reservation_type == 'single' "
        v-on:update-reservation="updateReservation"
        :reservation="reservation"
      >
      </reservation-check-out>
      <company-reservation-checkout v-if="reservation.status == 'confirmed' &&!reservation.checked_out && reservation.checked_in && reservation.reservation_type == 'group' " v-on:update-reservation="updateReservation" :reservation="reservation" :invoicesList="reservation.shared_invoices"></company-reservation-checkout>
      <reservation-confirm v-if="reservation.status == 'awaiting-confirmation' || reservation.status == 'awaiting-payment'" :reservation_id="reservation.id" :quick="false"  v-on:update-reservation="updateReservation" />


      <reservation-cancel
        v-show="
        ( !reservation.checked_in &&  cancel_reservation_before_checkin ) ||
        ((reservation.checked_in && user_has_permission_to_cancel_reservation_after_checkin_before_night_run && formatDateCustom(reservation.date_in) == today_date) || (reservation.checked_in && user_has_permission_to_cancel_reservation_after_checkin_after_night_run && formatDateCustom(reservation.date_in) != today_date))
       "
       v-if="(reservation.status == 'confirmed' || reservation.status == 'awaiting-payment' || reservation.status == 'awaiting-confirmation')  && !reservation.checked_out" :quick="false" :reservation="reservation"  v-on:update-reservation="updateReservation"></reservation-cancel>
      <cancel-checkout v-if="canCancelCheckout && reservation.checked_out" :quick="false" :unit="reservation.unit"  :reservation_id="reservation.id" v-on:update-reservation="updateReservation"></cancel-checkout>

      <!-- Add the ReservationFees component -->
      <reservation-fees v-if="reservation.status === 'canceled'" :reservation="reservation" v-on:update-reservation="updateReservation"></reservation-fees>
    </div><!-- main_reservation_buttons -->
    <div v-show="!loading">

      <div class="checkin_checkout" v-if="reservation">
        <div v-if="reservation.checked_in" class="reservation_checked checkedin" role="alert">
          <span></span>
          <p> {{ __('Customer checked-in in')  }} <i class="alert_datte">{{ getTimeBanner(reservation.checked_in) }}</i></p>
        </div>
        <div v-if="reservation.checked_out" class="reservation_checked checkedout" role="alert">
          <span></span>
          <p>{{ __('Customer checked-out in')  }} <i class="alert_datte">{{ getTimeBanner(reservation.checked_out) }}</i></p>
        </div>

        <a v-if="hasPermission('print promissory notes') && reservation.reservation_type == 'single' && reservation.promissory && reservation.promissory.status == 'pending'"
            :href="`/home/reservation/promissory/${reservation.promissory.hash_id}`"
            target="_blank"
            class="cursor-pointer reservation_checked promissory"
            role="alert">
                <span>
                    <svg version="1.1" id="Layer_1" width="50" height="40" xmlns="http://www.w3.org/2000/svg" x="0" y="0" viewBox="0 0 512 512" xml:space="preserve"><path class="st0" d="M448.8 225.7c4.2 0 7.6-3.4 7.6-7.6V99.2c0-9.6-7.8-17.4-17.4-17.4h-29.7V58.3c0-9.6-7.8-17.4-17.4-17.4h-30.7V17.4c0-9.6-7.8-17.4-17.4-17.4H134.5c-4.2 0-7.6 3.4-7.6 7.6 0 4.2 3.4 7.6 7.6 7.6h209.1c1.2 0 2.3 1 2.3 2.3v395.3c0 1.2-1 2.3-2.3 2.3H73.4c-1.3 0-2.3-1-2.3-2.3V17.4c0-1.3 1-2.3 2.3-2.3h30.8c4.2 0 7.6-3.4 7.6-7.6 0-4.2-3.4-7.6-7.6-7.6H73.4C63.8 0 56 7.8 56 17.4v395.3c0 9.6 7.8 17.4 17.4 17.4h30.7v23.5c0 9.6 7.8 17.4 17.4 17.4h29.7v23.5c0 9.6 7.8 17.4 17.4 17.4h270.2c9.6 0 17.4-7.8 17.4-17.4V248.4c0-4.2-3.4-7.6-7.6-7.6-4.2 0-7.6 3.4-7.6 7.6v246.2c0 1.3-1 2.3-2.3 2.3h-270c-1.2 0-2.3-1-2.3-2.3v-23.5h225.4c9.6 0 17.4-7.8 17.4-17.4V348.9c0-4.2-3.4-7.6-7.6-7.6-4.2 0-7.6 3.4-7.6 7.6v104.8c0 1.3-1 2.3-2.3 2.3H121.5c-1.3 0-2.3-1-2.3-2.3v-23.5h224.4c9.6 0 17.4-7.8 17.4-17.4V56.1h30.7c1.3 0 2.3 1 2.3 2.3v260.2c0 4.2 3.4 7.6 7.6 7.6 4.2 0 7.6-3.4 7.6-7.6V97h29.7c1.3 0 2.3 1 2.3 2.3v118.8c0 4.2 3.4 7.6 7.6 7.6z"/><path class="st0" d="M251.3 75c0-23.6-19.2-42.7-42.7-42.7S165.8 51.4 165.8 75s19.2 42.7 42.7 42.7 42.8-19.1 42.8-42.7zm-70.4 0c0-15.2 12.4-27.6 27.6-27.6s27.6 12.4 27.6 27.6-12.4 27.6-27.6 27.6-27.6-12.4-27.6-27.6zM311.9 82.6c4.2 0 7.6-3.4 7.6-7.6 0-4.2-3.4-7.6-7.6-7.6h-35.5c-4.2 0-7.6 3.4-7.6 7.6 0 4.2 3.4 7.6 7.6 7.6h35.5zM140.7 67.4h-35.5c-4.2 0-7.6 3.4-7.6 7.6 0 4.2 3.4 7.6 7.6 7.6h35.5c4.2 0 7.6-3.4 7.6-7.6 0-4.2-3.4-7.6-7.6-7.6zM115.1 146.9c0 4.2 3.4 7.6 7.6 7.6h171.7c4.2 0 7.6-3.4 7.6-7.6 0-4.2-3.4-7.6-7.6-7.6H122.7c-4.2 0-7.6 3.4-7.6 7.6zM294.4 176.1H122.7c-4.2 0-7.6 3.4-7.6 7.6s3.4 7.6 7.6 7.6h171.7c4.2 0 7.6-3.4 7.6-7.6s-3.4-7.6-7.6-7.6zM294.4 212.8H122.7c-4.2 0-7.6 3.4-7.6 7.6 0 4.2 3.4 7.6 7.6 7.6h171.7c4.2 0 7.6-3.4 7.6-7.6 0-4.2-3.4-7.6-7.6-7.6zM294.4 249.5H122.7c-4.2 0-7.6 3.4-7.6 7.6 0 4.2 3.4 7.6 7.6 7.6h171.7c4.2 0 7.6-3.4 7.6-7.6 0-4.2-3.4-7.6-7.6-7.6zM294.4 286.3H122.7c-4.2 0-7.6 3.4-7.6 7.6 0 4.2 3.4 7.6 7.6 7.6h171.7c4.2 0 7.6-3.4 7.6-7.6 0-4.2-3.4-7.6-7.6-7.6zM297.7 324.7c-3.2-2.7-8-2.2-10.7 1.1l-36.2 44.1-16.8-16.4c-3-2.9-7.8-2.9-10.7.1-2.9 3-2.9 7.8.1 10.7l18.8 18.4c5.3 5.2 13.8 4.7 18.5-1l38-46.3c2.7-3.2 2.2-8-1-10.7z"/></svg>
                </span>
                <p>
                    {{ __('Reservation has a promissory with number :number with due date' , {number : reservation.promissory.serial})  }}
                    <i class="alert_datte">{{ reservation.promissory.due_date }}</i>
                </p>
        </a>

            <!-- Without permission -->
        <div v-else-if="reservation.reservation_type == 'single' && reservation.promissory && reservation.promissory.status == 'pending'"
            class="reservation_checked promissory"
            role="alert">
            <span>
                <svg version="1.1" id="Layer_1" width="50" height="40" xmlns="http://www.w3.org/2000/svg" x="0" y="0" viewBox="0 0 512 512" xml:space="preserve"><path class="st0" d="M448.8 225.7c4.2 0 7.6-3.4 7.6-7.6V99.2c0-9.6-7.8-17.4-17.4-17.4h-29.7V58.3c0-9.6-7.8-17.4-17.4-17.4h-30.7V17.4c0-9.6-7.8-17.4-17.4-17.4H134.5c-4.2 0-7.6 3.4-7.6 7.6 0 4.2 3.4 7.6 7.6 7.6h209.1c1.2 0 2.3 1 2.3 2.3v395.3c0 1.2-1 2.3-2.3 2.3H73.4c-1.3 0-2.3-1-2.3-2.3V17.4c0-1.3 1-2.3 2.3-2.3h30.8c4.2 0 7.6-3.4 7.6-7.6 0-4.2-3.4-7.6-7.6-7.6H73.4C63.8 0 56 7.8 56 17.4v395.3c0 9.6 7.8 17.4 17.4 17.4h30.7v23.5c0 9.6 7.8 17.4 17.4 17.4h29.7v23.5c0 9.6 7.8 17.4 17.4 17.4h270.2c9.6 0 17.4-7.8 17.4-17.4V248.4c0-4.2-3.4-7.6-7.6-7.6-4.2 0-7.6 3.4-7.6 7.6v246.2c0 1.3-1 2.3-2.3 2.3h-270c-1.2 0-2.3-1-2.3-2.3v-23.5h225.4c9.6 0 17.4-7.8 17.4-17.4V348.9c0-4.2-3.4-7.6-7.6-7.6-4.2 0-7.6 3.4-7.6 7.6v104.8c0 1.3-1 2.3-2.3 2.3H121.5c-1.3 0-2.3-1-2.3-2.3v-23.5h224.4c9.6 0 17.4-7.8 17.4-17.4V56.1h30.7c1.3 0 2.3 1 2.3 2.3v260.2c0 4.2 3.4 7.6 7.6 7.6 4.2 0 7.6-3.4 7.6-7.6V97h29.7c1.3 0 2.3 1 2.3 2.3v118.8c0 4.2 3.4 7.6 7.6 7.6z"/><path class="st0" d="M251.3 75c0-23.6-19.2-42.7-42.7-42.7S165.8 51.4 165.8 75s19.2 42.7 42.7 42.7 42.8-19.1 42.8-42.7zm-70.4 0c0-15.2 12.4-27.6 27.6-27.6s27.6 12.4 27.6 27.6-12.4 27.6-27.6 27.6-27.6-12.4-27.6-27.6zM311.9 82.6c4.2 0 7.6-3.4 7.6-7.6 0-4.2-3.4-7.6-7.6-7.6h-35.5c-4.2 0-7.6 3.4-7.6 7.6 0 4.2 3.4 7.6 7.6 7.6h35.5zM140.7 67.4h-35.5c-4.2 0-7.6 3.4-7.6 7.6 0 4.2 3.4 7.6 7.6 7.6h35.5c4.2 0 7.6-3.4 7.6-7.6 0-4.2-3.4-7.6-7.6-7.6zM115.1 146.9c0 4.2 3.4 7.6 7.6 7.6h171.7c4.2 0 7.6-3.4 7.6-7.6 0-4.2-3.4-7.6-7.6-7.6H122.7c-4.2 0-7.6 3.4-7.6 7.6zM294.4 176.1H122.7c-4.2 0-7.6 3.4-7.6 7.6s3.4 7.6 7.6 7.6h171.7c4.2 0 7.6-3.4 7.6-7.6s-3.4-7.6-7.6-7.6zM294.4 212.8H122.7c-4.2 0-7.6 3.4-7.6 7.6 0 4.2 3.4 7.6 7.6 7.6h171.7c4.2 0 7.6-3.4 7.6-7.6 0-4.2-3.4-7.6-7.6-7.6zM294.4 249.5H122.7c-4.2 0-7.6 3.4-7.6 7.6 0 4.2 3.4 7.6 7.6 7.6h171.7c4.2 0 7.6-3.4 7.6-7.6 0-4.2-3.4-7.6-7.6-7.6zM294.4 286.3H122.7c-4.2 0-7.6 3.4-7.6 7.6 0 4.2 3.4 7.6 7.6 7.6h171.7c4.2 0 7.6-3.4 7.6-7.6 0-4.2-3.4-7.6-7.6-7.6zM297.7 324.7c-3.2-2.7-8-2.2-10.7 1.1l-36.2 44.1-16.8-16.4c-3-2.9-7.8-2.9-10.7.1-2.9 3-2.9 7.8.1 10.7l18.8 18.4c5.3 5.2 13.8 4.7 18.5-1l38-46.3c2.7-3.2 2.2-8-1-10.7z"/></svg>
            </span>
            <p>
                    {{ __('Reservation has a promissory with number :number with due date' , {number : reservation.promissory.serial})  }}
                <i class="alert_datte">{{ reservation.promissory.due_date }}</i>
            </p>
        </div>

        <a :href="`/home/reservation/promissory/${reservation.shared_promissory.hash_id}`" target="_blank" v-if="hasPermission('print promissory notes') && reservation.reservation_type == 'group' && reservation.shared_promissory && reservation.shared_promissory.status == 'pending'" class="cursor-pointer reservation_checked promissory" role="alert">
          <span>
            <svg version="1.1" id="Layer_1" width="50" height="40" xmlns="http://www.w3.org/2000/svg" x="0" y="0" viewBox="0 0 512 512" xml:space="preserve"><path class="st0" d="M448.8 225.7c4.2 0 7.6-3.4 7.6-7.6V99.2c0-9.6-7.8-17.4-17.4-17.4h-29.7V58.3c0-9.6-7.8-17.4-17.4-17.4h-30.7V17.4c0-9.6-7.8-17.4-17.4-17.4H134.5c-4.2 0-7.6 3.4-7.6 7.6 0 4.2 3.4 7.6 7.6 7.6h209.1c1.2 0 2.3 1 2.3 2.3v395.3c0 1.2-1 2.3-2.3 2.3H73.4c-1.3 0-2.3-1-2.3-2.3V17.4c0-1.3 1-2.3 2.3-2.3h30.8c4.2 0 7.6-3.4 7.6-7.6 0-4.2-3.4-7.6-7.6-7.6H73.4C63.8 0 56 7.8 56 17.4v395.3c0 9.6 7.8 17.4 17.4 17.4h30.7v23.5c0 9.6 7.8 17.4 17.4 17.4h29.7v23.5c0 9.6 7.8 17.4 17.4 17.4h270.2c9.6 0 17.4-7.8 17.4-17.4V248.4c0-4.2-3.4-7.6-7.6-7.6-4.2 0-7.6 3.4-7.6 7.6v246.2c0 1.3-1 2.3-2.3 2.3h-270c-1.2 0-2.3-1-2.3-2.3v-23.5h225.4c9.6 0 17.4-7.8 17.4-17.4V348.9c0-4.2-3.4-7.6-7.6-7.6-4.2 0-7.6 3.4-7.6 7.6v104.8c0 1.3-1 2.3-2.3 2.3H121.5c-1.3 0-2.3-1-2.3-2.3v-23.5h224.4c9.6 0 17.4-7.8 17.4-17.4V56.1h30.7c1.3 0 2.3 1 2.3 2.3v260.2c0 4.2 3.4 7.6 7.6 7.6 4.2 0 7.6-3.4 7.6-7.6V97h29.7c1.3 0 2.3 1 2.3 2.3v118.8c0 4.2 3.4 7.6 7.6 7.6z"/><path class="st0" d="M251.3 75c0-23.6-19.2-42.7-42.7-42.7S165.8 51.4 165.8 75s19.2 42.7 42.7 42.7 42.8-19.1 42.8-42.7zm-70.4 0c0-15.2 12.4-27.6 27.6-27.6s27.6 12.4 27.6 27.6-12.4 27.6-27.6 27.6-27.6-12.4-27.6-27.6zM311.9 82.6c4.2 0 7.6-3.4 7.6-7.6 0-4.2-3.4-7.6-7.6-7.6h-35.5c-4.2 0-7.6 3.4-7.6 7.6 0 4.2 3.4 7.6 7.6 7.6h35.5zM140.7 67.4h-35.5c-4.2 0-7.6 3.4-7.6 7.6 0 4.2 3.4 7.6 7.6 7.6h35.5c4.2 0 7.6-3.4 7.6-7.6 0-4.2-3.4-7.6-7.6-7.6zM115.1 146.9c0 4.2 3.4 7.6 7.6 7.6h171.7c4.2 0 7.6-3.4 7.6-7.6 0-4.2-3.4-7.6-7.6-7.6H122.7c-4.2 0-7.6 3.4-7.6 7.6zM294.4 176.1H122.7c-4.2 0-7.6 3.4-7.6 7.6s3.4 7.6 7.6 7.6h171.7c4.2 0 7.6-3.4 7.6-7.6s-3.4-7.6-7.6-7.6zM294.4 212.8H122.7c-4.2 0-7.6 3.4-7.6 7.6 0 4.2 3.4 7.6 7.6 7.6h171.7c4.2 0 7.6-3.4 7.6-7.6 0-4.2-3.4-7.6-7.6-7.6zM294.4 249.5H122.7c-4.2 0-7.6 3.4-7.6 7.6 0 4.2 3.4 7.6 7.6 7.6h171.7c4.2 0 7.6-3.4 7.6-7.6 0-4.2-3.4-7.6-7.6-7.6zM294.4 286.3H122.7c-4.2 0-7.6 3.4-7.6 7.6 0 4.2 3.4 7.6 7.6 7.6h171.7c4.2 0 7.6-3.4 7.6-7.6 0-4.2-3.4-7.6-7.6-7.6zM297.7 324.7c-3.2-2.7-8-2.2-10.7 1.1l-36.2 44.1-16.8-16.4c-3-2.9-7.8-2.9-10.7.1-2.9 3-2.9 7.8.1 10.7l18.8 18.4c5.3 5.2 13.8 4.7 18.5-1l38-46.3c2.7-3.2 2.2-8-1-10.7z"/></svg>
          </span>

          <p>{{ __('Group Reservation has a promissory with number :number with due date' , {number : reservation.shared_promissory.serial})  }} <i class="alert_datte">{{ reservation.shared_promissory.due_date }}</i></p>
        </a>
        <div v-else-if="reservation.reservation_type == 'group' && reservation.shared_promissory && reservation.shared_promissory.status == 'pending'" class="cursor-pointer reservation_checked promissory" role="alert">
            <span>
                <svg version="1.1" id="Layer_1" width="50" height="40" xmlns="http://www.w3.org/2000/svg" x="0" y="0" viewBox="0 0 512 512" xml:space="preserve"><path class="st0" d="M448.8 225.7c4.2 0 7.6-3.4 7.6-7.6V99.2c0-9.6-7.8-17.4-17.4-17.4h-29.7V58.3c0-9.6-7.8-17.4-17.4-17.4h-30.7V17.4c0-9.6-7.8-17.4-17.4-17.4H134.5c-4.2 0-7.6 3.4-7.6 7.6 0 4.2 3.4 7.6 7.6 7.6h209.1c1.2 0 2.3 1 2.3 2.3v395.3c0 1.2-1 2.3-2.3 2.3H73.4c-1.3 0-2.3-1-2.3-2.3V17.4c0-1.3 1-2.3 2.3-2.3h30.8c4.2 0 7.6-3.4 7.6-7.6 0-4.2-3.4-7.6-7.6-7.6H73.4C63.8 0 56 7.8 56 17.4v395.3c0 9.6 7.8 17.4 17.4 17.4h30.7v23.5c0 9.6 7.8 17.4 17.4 17.4h29.7v23.5c0 9.6 7.8 17.4 17.4 17.4h270.2c9.6 0 17.4-7.8 17.4-17.4V248.4c0-4.2-3.4-7.6-7.6-7.6-4.2 0-7.6 3.4-7.6 7.6v246.2c0 1.3-1 2.3-2.3 2.3h-270c-1.2 0-2.3-1-2.3-2.3v-23.5h225.4c9.6 0 17.4-7.8 17.4-17.4V348.9c0-4.2-3.4-7.6-7.6-7.6-4.2 0-7.6 3.4-7.6 7.6v104.8c0 1.3-1 2.3-2.3 2.3H121.5c-1.3 0-2.3-1-2.3-2.3v-23.5h224.4c9.6 0 17.4-7.8 17.4-17.4V56.1h30.7c1.3 0 2.3 1 2.3 2.3v260.2c0 4.2 3.4 7.6 7.6 7.6 4.2 0 7.6-3.4 7.6-7.6V97h29.7c1.3 0 2.3 1 2.3 2.3v118.8c0 4.2 3.4 7.6 7.6 7.6z"/><path class="st0" d="M251.3 75c0-23.6-19.2-42.7-42.7-42.7S165.8 51.4 165.8 75s19.2 42.7 42.7 42.7 42.8-19.1 42.8-42.7zm-70.4 0c0-15.2 12.4-27.6 27.6-27.6s27.6 12.4 27.6 27.6-12.4 27.6-27.6 27.6-27.6-12.4-27.6-27.6zM311.9 82.6c4.2 0 7.6-3.4 7.6-7.6 0-4.2-3.4-7.6-7.6-7.6h-35.5c-4.2 0-7.6 3.4-7.6 7.6 0 4.2 3.4 7.6 7.6 7.6h35.5zM140.7 67.4h-35.5c-4.2 0-7.6 3.4-7.6 7.6 0 4.2 3.4 7.6 7.6 7.6h35.5c4.2 0 7.6-3.4 7.6-7.6 0-4.2-3.4-7.6-7.6-7.6zM115.1 146.9c0 4.2 3.4 7.6 7.6 7.6h171.7c4.2 0 7.6-3.4 7.6-7.6 0-4.2-3.4-7.6-7.6-7.6H122.7c-4.2 0-7.6 3.4-7.6 7.6zM294.4 176.1H122.7c-4.2 0-7.6 3.4-7.6 7.6s3.4 7.6 7.6 7.6h171.7c4.2 0 7.6-3.4 7.6-7.6s-3.4-7.6-7.6-7.6zM294.4 212.8H122.7c-4.2 0-7.6 3.4-7.6 7.6 0 4.2 3.4 7.6 7.6 7.6h171.7c4.2 0 7.6-3.4 7.6-7.6 0-4.2-3.4-7.6-7.6-7.6zM294.4 249.5H122.7c-4.2 0-7.6 3.4-7.6 7.6 0 4.2 3.4 7.6 7.6 7.6h171.7c4.2 0 7.6-3.4 7.6-7.6 0-4.2-3.4-7.6-7.6-7.6zM294.4 286.3H122.7c-4.2 0-7.6 3.4-7.6 7.6 0 4.2 3.4 7.6 7.6 7.6h171.7c4.2 0 7.6-3.4 7.6-7.6 0-4.2-3.4-7.6-7.6-7.6zM297.7 324.7c-3.2-2.7-8-2.2-10.7 1.1l-36.2 44.1-16.8-16.4c-3-2.9-7.8-2.9-10.7.1-2.9 3-2.9 7.8.1 10.7l18.8 18.4c5.3 5.2 13.8 4.7 18.5-1l38-46.3c2.7-3.2 2.2-8-1-10.7z"/></svg>
            </span>

          <p>{{ __('Group Reservation has a promissory with number :number with due date' , {number : reservation.shared_promissory.serial})  }} <i class="alert_datte">{{ reservation.shared_promissory.due_date }}</i></p>
         </div>

      </div><!-- checkin_checkout -->

      <div class="row_cols">

        <div class="right_col">

          <div class="booking_summary" v-if="reservation">
            <div class="title">
              <span>{{__('Summary')}}</span>
              <reservation-edit v-if="!reservation.checked_out && (reservation.status == 'confirmed' || reservation.status == 'canceled' || reservation.status == 'awaiting-payment') && !occ" :occ="occ"  :reservation="reservation" @update-reservation="updateReservation"></reservation-edit>
              <reservation-edit v-if="occ"  :occ="occ" :reservation="reservation" @update-reservation="updateReservation"></reservation-edit>
            </div><!-- title -->
            <div class="content">
              <ul>
                <li class="oneline">
                  <div class="name">{{__('Reservation Number')}} : </div>
                  <div class="col_two">
                    <div class="desc" >
                      {{ reservation.number }}
                      <svg v-if="reservation.group_reservation" xmlns="http://www.w3.org/2000/svg" width="30" height="19.05" viewBox="0 0 39.554 25.114"><path d="M34.208 11.051a4.516 4.516 0 1 0-5.181 0 7.824 7.824 0 0 0-2.669 1.565 10.125 10.125 0 0 0-3.663-2 5.721 5.721 0 1 0-5.917 0 10.209 10.209 0 0 0-3.624 1.972 7.888 7.888 0 0 0-2.637-1.534 4.516 4.516 0 1 0-5.181 0A7.934 7.934 0 0 0 0 18.548v.517a.034.034 0 0 0 .031.031H9.6a10.526 10.526 0 0 0-.086 1.323v.532a4.162 4.162 0 0 0 4.164 4.164h12.133a4.162 4.162 0 0 0 4.164-4.164v-.532a10.525 10.525 0 0 0-.086-1.323h9.634a.034.034 0 0 0 .031-.031v-.517a7.965 7.965 0 0 0-5.346-7.497Zm-5.854-3.7a3.264 3.264 0 1 1 3.326 3.264h-.125a3.259 3.259 0 0 1-3.201-3.266Zm-13.1-1.62a4.469 4.469 0 1 1 4.727 4.461h-.517a4.481 4.481 0 0 1-4.211-4.463ZM4.641 7.349a3.264 3.264 0 1 1 3.326 3.264h-.125a3.264 3.264 0 0 1-3.201-3.264Zm5.181 10.487H1.268a6.687 6.687 0 0 1 6.59-5.971h.094a6.617 6.617 0 0 1 4.265 1.589 10.368 10.368 0 0 0-2.395 4.382Zm18.885 3.123A2.916 2.916 0 0 1 25.8 23.87H13.665a2.916 2.916 0 0 1-2.911-2.911v-.532a8.99 8.99 0 0 1 8.711-8.977c.086.008.18.008.266.008s.18 0 .266-.008a8.99 8.99 0 0 1 8.711 8.977Zm.931-3.123a10.249 10.249 0 0 0-2.371-4.351 6.649 6.649 0 0 1 4.3-1.62h.094a6.687 6.687 0 0 1 6.59 5.971Z" fill="#4099de"/></svg>
                    </div>
                    <div class="status" v-if="reservation.status == 'confirmed' || reservation.status == 'canceled' " v-bind:class="{ 'btn-outline-danger': this.reservation.status == 'canceled' ,'btn-outline-success': this.reservation.status == 'confirmed' }">{{__(reservation.status)}}</div>
                    <div class="status" v-else-if="reservation.status == 'awaiting-payment' || reservation.status == 'timeout'" v-bind:class="{ 'btn-outline-awaiting-payment': this.reservation.status == 'awaiting-payment' ,'btn-outline-timeout': this.reservation.status == 'timeout' }">{{__(reservation.status)}}</div>
                    <div class="status" v-else v-bind:class="{ 'btn-outline-awaiting-confirmation': this.reservation.status == 'awaiting-confirmation'}">{{__(reservation.status)}}</div>
                    <div v-if="reservation.no_show == 1" style="display: inline-flex; align-items: center; margin-left: 8px; padding: 4px 8px; background-color: #FF4D4F; color: white; border-radius: 4px; font-size: 0.85em; font-weight: 500;">{{__('No Show')}}</div>

                  </div><!-- col_two -->
                </li>
                <li class="oneline">
                  <div class="name">{{__('Source')}} :</div>
                  <div class="desc2" v-if="reservation.source">{{ reservation.source.name[this.local] }}
                      <span v-if="source_num"> &nbsp; - &nbsp;
                          <v-popover
                            :placement="'top-center'"
                          >
                            <!-- This will be the popover target (for the events and position) -->
                            <button>{{__('Show number')}}</button>
                            <div slot="popover">{{source_num}}</div>
                            </v-popover>
                       </span>
                  </div>
                </li>
                <li class="oneline">
                  <div class="name">{{__('Unit')}} :</div>
                  <div class="desc">{{reservation.unit ?  reservation.unit.unit_number : '-'}} - {{reservation.unit ? reservation.unit.name[this.local] : '-'}}</div>
                </li>
                <li class="reservation_date_row">
                  <div class="name">{{__('Reservation Date')}} :</div>
                  <div class="desc">
                    <div class="nights" v-if="reservation.rent_type == 1">{{reservation.nights}} {{__('Night')}} </div>
                    <div class="nights" v-if="reservation.rent_type == 2">{{Math.floor(reservation.nights/30) }} {{__('Month')}} - {{reservation.nights%30 }} {{__('Night')}}</div>
                    <div class="from_to">
                      <div class="from">
                        <span>{{__('From')}} : {{formatDate(reservation.date_in)}}</span>
                        <template v-if="day_start"><p>{{__('CheckIn')}} : {{day_start}}</p></template>
                      </div><!-- from -->
                      <div class="to">
                        <span>{{__('To')}} : {{formatDate(reservation.date_out)}}</span>
                        <template v-if="day_end"><p>{{__('CheckOut')}} : {{day_end}}</p></template>
                      </div><!-- to -->
                    </div><!-- from_to -->
                  </div><!-- desc -->
                </li>
                <li class="oneline">
                  <div class="name">{{__('Leasing')}} :</div>
                  <div class="Leasing">
                    <span>{{  (reservation.sub_total).toFixed(2) }} <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></span>
                    <days-details :reservation="reservation"> </days-details>
                  </div><!-- Leasing -->
                </li>
                <li class="oneline">
                  <div class="name">{{__('Services')}} :</div>
                  <div class="desc">{{ (ServicesSum).toFixed(2) }} <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></div>
                </li>
                <li class="oneline">
                  <div class="name">{{__('total')}} :</div>
                  <div class="desc">{{ (grandTotalRounded).toFixed(2) }} <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></div>
                </li>
                <li class="oneline">
                  <div class="name">{{__('Balance')}} :</div>
                  <div class="desc" v-if="reservation.reservation_type == 'single'">
                    <template v-if="reservation.wallet.decimal_places == 3">
                       <span :class="{ 'text-success': reservation.balance / 1000 > 0 ,'text-danger': reservation.balance / 1000 < 0 }"> {{ (Math.abs(reservation.balance / 1000)).toFixed(2)}} <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span> <p :class="{ 'text-success': reservation.balance / 1000 > 0 ,'text-danger': reservation.balance / 1000 < 0 }"> {{ (reservation.balance / 1000  > 0) ? '('+__('credit')+')'  : ''}} {{ (reservation.balance / 1000  < 0) ? '('+__('debit')+')' : ''}}</p>
                    </span>
                    </template>
                    <template v-else>
                       <span :class="{ 'text-success': reservation.balance > 0 ,'text-danger': reservation.balance < 0 }"> {{ (Math.abs(reservation.balance / 100)).toFixed(2)}} <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span> <p :class="{ 'text-success': reservation.balance > 0 ,'text-danger': reservation.balance < 0 }"> {{ (reservation.balance  > 0) ? '('+__('credit')+')'  : ''}} {{ (reservation.balance  < 0) ? '('+__('debit')+')' : ''}}</p>
                    </span>
                    </template>

                  </div>
                  <div class="desc" v-else>
                    <span :class="{ 'text-success': reservation.group_balance > 0 ,'text-danger': reservation.group_balance < 0 }"> {{ (Math.abs(reservation.group_balance)).toFixed(2)}} <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span> <p :class="{ 'text-success': reservation.group_balance > 0 ,'text-danger': reservation.group_balance < 0 }"> {{ (reservation.group_balance  > 0) ? '('+__('credit')+')'  : ''}} {{ (reservation.group_balance < 0) ? '('+__('debit')+')' : ''}}</p>
                    </span>
                  </div>
                </li>

                <li v-if="reservation.reservation_free_services.length" class="oneline">
                  <div class="name">{{__('Services included in the price')}} : </div>
                  <div class="col_two" style="display: contents;">
                    <div class="desc" >
                    </div>
                    <template v-for="(serviceMapper,i) in reservation.reservation_free_services">
                      <label class="reservation_service_label">{{local == 'ar' ? serviceMapper.service.name_ar : serviceMapper.service.name_en }}</label>
                    </template>

                  </div><!-- col_two -->
                </li>

              </ul>
            </div><!-- content -->
          </div><!-- booking_summary -->

          <add-invoice v-if="reservation && reservation.reservation_type == 'single'" :occ="occ" :reservation="reservation" />
          <group-reservations-manual-invoices :reservation="reservation" :occ="occ"  v-else/>
          <div class="financial_statement" v-permission="'view statements'" v-if="reservation && reservation.reservation_type == 'single'">
            <div class="title">{{__('Financial Statement')}}</div>
            <div class="content">
              <div class="all_financial_items" v-if="reservation.transactions.length">
                <div class="financial_item"  v-for="(transaction,index) in reservation.transactions.slice(0,4)" :key="index" @click="openTransactionModal(transaction)">
                  <div class="col_right">
                    <span>{{__('Number')}} : <p>{{transaction.number}}</p></span>
                    <template v-if="reservation.wallet.decimal_places == 3">
                      <span>{{__('Amount')}} : <p class="d-inline-flex" v-bind:class="{ 'text-danger': transaction.type == 'withdraw' ,'text-success': transaction.type == 'deposit' }">{{transaction.amount / 1000}} <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></p></span>
                    </template>
                    <template v-else>
                       <span>{{__('Amount')}} : <p class="d-inline-flex" v-bind:class="{ 'text-danger': transaction.type == 'withdraw' ,'text-success': transaction.type == 'deposit' }">{{transaction.amount / 100}} <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></p></span>
                    </template>
                  </div><!-- col_right -->
                  <div class="col_left">
                    <time>{{transaction.meta.date}}</time>
                    <div class="name">
                      <p
                        class="credit_payment"
                        v-if="transaction.meta && transaction.meta.payment_type == 'credit-payment'"
                      >
                        {{__('Credit Payment')}}
                      </p>
                      <p v-if="transaction.is_insurance">{{__('Insurance')}}</p>
                      <label
                          v-bind:class="{
                                'text-danger': transaction.type == 'withdraw' ,
                                'text-success': transaction.type == 'deposit'
                          }"
                        >
                          {{__(transaction.meta.payment_type)}} | {{__(transaction.type)}}
                      </label>
                    </div>
                  </div><!-- col_left -->
                </div><!-- financial_item -->
                <div class="more_transactions" v-if="reservation.transactions.length > 4" @click="openTransactionsModal"></div>
              </div>
              <div class="no_transactions_found" v-if="reservation.transactions.length === 0">
                <div>
                  <div class="icon"></div>
                  <span>{{__('No transactions found!')}}</span>
                </div>
              </div><!-- no_transactions_found -->
              <div class="block_bottom" v-if="reservation">
                <!-- <div class="add_payments" v-if="reservation.status != 'canceled'"> -->
                <div class="add_payments">
                  <cash-receipt :reservation="reservation" :occ="occ" v-on:update-reservation="updateReservation" :group_balance="reservation.group_balance"></cash-receipt>
                  <payment-voucher :occ="occ" v-on:update-reservation="updateReservation" :group_balance="reservation.group_balance"></payment-voucher>
                  <rebate :reservation="reservation" :occ="occ" v-on:update-reservation="updateReservation" :group_balance="reservation.group_balance"></rebate>

                </div><!-- add_payments -->

                <div class="more">
                  <button v-if="reservation.transactions.length > 4" @click="openTransactionsModal">{{__('more')}} ({{reservation.transactions.length}}) ..</button>
                </div><!-- more -->
              </div><!-- block_bottom -->
            </div><!-- content -->
          </div><!-- financial_statement -->
          <!-- The Part of transactions grouped for group reservation -->
          <div class="financial_statement" v-permission="'view statements'" v-if="reservation && reservation.reservation_type == 'group'">
            <div class="title">{{__('Financial Statement')}}</div>
            <div class="content">
              <div class="all_financial_items" v-if="reservation.reservation_type == 'group' && reservation.group_reservation_transactions.length">
                <div class="financial_item"  v-for="(transaction,index) in reservation.group_reservation_transactions.slice(0,4)" :key="index" @click="openTransactionModal(transaction)">
                  <div class="col_right">
                    <span>{{__('Number')}} : <p>{{transaction.number}}</p></span>
                    <template v-if="reservation.wallet.decimal_places == 3">
                      <span>{{__('Amount')}} : <p class="d-inline-flex" v-bind:class="{ 'text-danger': transaction.type == 'withdraw' ,'text-success': transaction.type == 'deposit' }">{{transaction.amount / 1000}} <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></p></span>
                    </template>
                    <template v-else>
                       <span>{{__('Amount')}} : <p class="d-inline-flex" v-bind:class="{ 'text-danger': transaction.type == 'withdraw' ,'text-success': transaction.type == 'deposit' }">{{transaction.amount / 100}} <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></p></span>
                    </template>
                  </div><!-- col_right -->
                  <div class="col_left">
                    <time>{{transaction.meta.date}}</time>
                    <div class="name">
                      <p v-if="transaction.is_insurance">{{__('Insurance')}}</p>
                      <label v-bind:class="{ 'text-danger': transaction.type == 'withdraw' ,'text-success': transaction.type == 'deposit' }">{{__(transaction.type)}}</label></div>
                  </div><!-- col_left -->
                </div><!-- financial_item -->
                <div class="more_transactions" v-if="reservation.group_reservation_transactions.length > 4" @click="openTransactionsModal"></div>
              </div>
              <div class="no_transactions_found" v-if="reservation.group_reservation_transactions.length === 0">
                <div>
                  <div class="icon"></div>
                  <span>{{__('No transactions found!')}}</span>
                </div>
              </div><!-- no_transactions_found -->
              <div class="block_bottom" v-if="reservation">
                <div class="add_payments" v-if="reservation.status != 'canceled'">
                  <cash-receipt :reservation="reservation" :occ="occ" v-on:update-reservation="updateReservation" :group_balance="reservation.group_balance"></cash-receipt>
                  <payment-voucher :occ="occ" v-on:update-reservation="updateReservation" :group_balance="reservation.group_balance"></payment-voucher>
                  <rebate :reservation="reservation" :occ="occ" v-on:update-reservation="updateReservation" :group_balance="reservation.group_balance"></rebate>

                </div><!-- add_payments -->

                <div class="more">
                  <button v-if="reservation.group_reservation_transactions.length > 3" @click="openTransactionsModal">{{__('more')}} ({{reservation.transactions.length}}) ..</button>
                </div><!-- more -->
              </div><!-- block_bottom -->
            </div><!-- content -->
          </div><!-- financial_statement -->

        </div><!-- right_col -->

        <div class="left_col">

          <div class="customer_information" v-if="reservation">
            <div class="title">
              <span class="spn">{{__('Customer Information')}}</span>
              <reservation-edit-customer @update-reservation="updateReservation" :occ="occ" :reservation="reservation" v-if="(reservation.status == 'confirmed' || reservation.status == 'awaiting-payment') && !reservation.checked_out"></reservation-edit-customer>
              <reservation-edit-customer @update-reservation="updateReservation" :occ="occ" :reservation="reservation" v-if="occ"></reservation-edit-customer>
            </div><!-- title -->
            <div class="content">
              <div class="block_top">
                <ul>
                  <li v-if="reservation.company && reservation.company.entity_type != 'individual'">
                    <div class="name">{{__('Company Name')}} :</div>
                    <div class="col_two">
                      <router-link :to="`/companies/${reservation.company.id}/profile`"><span>{{reservation.company.name}}</span></router-link>
                    </div><!-- col_two -->
                  </li>
                  <li>
                    <div class="name">{{__('Name')}} :</div>
                    <div class="col_two">
                      <spn>{{reservation.customer.name}}</spn>
                      <div v-if="reservation.customer.label" v-html="reservation.customer.label"></div>
                    </div><!-- col_two -->
                  </li>
                  <li>
                    <div class="name">{{__('Phone')}} :</div>
                    <div class="col_two">
                      <span style="direction: ltr !important;display: inline-block;"><a :href="'tel:'+reservation.customer.phone">{{reservation.customer.phone}}</a></span>
                      <div v-if="( reservation.status == 'awaiting-payment' || reservation.status == 'confirmed' ) && reservation.balance < 0 && reservation.team.payment_preprocessor == 'hyperpay' && reservation.team.enabled_payment_link">
                        <button class="main_button" @click="openAddPaymentLinkModal">{{__('Send Payment Link')}}</button>
                      </div>
                    </div>
                  </li>
                  <li>

                    <div class="name">{{reservation.customer.id_type == 2 ? __('Passport Number') :  __('ID No')}} :</div>
                    <div class="desc">{{reservation.customer_id_number}}</div>
                  </li>
                  <li>
                    <div class="name">{{__('Email')}} :</div>
                    <div class="desc">{{reservation.customer.email}}</div>
                  </li>
                  <li>
                    <div class="name">{{__('Nationality')}} :</div>
                    <div class="desc">{{reservation.customer.nationality_string}}</div>
                  </li>
                  <li>
                    <div class="name">{{__('Reservations')}} :</div>
                    <div class="desc">{{reservation.customer.reservations_count}}</div>
                  </li>
                </ul>
                <div class="customerNotes" v-if="customerNotes.length">
                  <span @click="openDisplayNotesModal">{{__('Customer Have Notes', {count: customerNotes.length })}}</span>
                </div><!-- customerNotes -->

                 <div class="customerNotes" v-if="reservation" @click="openCreditorDebitorModal">
                      <span>
                          <!-- <p v-if="total_balance > 0">{{__('Customer')}} <b class="text-green-500">{{__('Creditor')}}</b> - {{__('Show Reservations History')}}</p>
                          <p v-if="total_balance < 0">{{__('Customer')}} <b class="text-red-500">{{__('Debtor')}}</b> - {{__('Show Reservations History')}}</p> -->
                          <p>{{__('Show Reservations History')}}</p>
                      </span>
                  </div>
                  <!-- customer creditor debtor modal -->
                  <customer-creditor-debtor-reservation-scope v-if="reservation && reservation.reservation_type == 'single'" ref="initCustomerCreditorDebitorModal" :reservation="reservation" />
                  <company-creditor-debtor v-if="reservation && reservation.reservation_type == 'group'" ref="initCompanyCreditorDebitorModal" :reservation="reservation" />



              </div><!-- block_top -->
              <div class="block_bottom">
                <button @click="sendMailModal" class="sendMailMsg">{{__('Send a message / email')}}</button>
                <button v-if="((reservation.company && reservation.company.entity_type == 'individual') || !reservation.company) && reservation.status == 'confirmed'" @click="convertReservationToGroupReservation" class="more">{{__('Add Company')}}</button>
                <remove-company v-if="reservation && reservation.company && reservation.company.entity_type == 'company'"  :reservation="reservation" />
                <change-company v-if="reservation && reservation.company && reservation.company.entity_type == 'company'" :reservation="reservation" />
                <!-- <button v-if="reservation.all_grouped_reservations_ids && reservation.all_grouped_reservations_ids.length >= 1 && reservation.reservation_type == 'group' && reservation.status == 'confirmed' && reservation.company" class="main_button">{{__('Change Company')}}</button> -->
                <button @click="reservation.reservation_type == 'single' ? pushToCustomerDetails(reservation.customer.id) : pushToCompanyDetails(reservation)" class="more">{{__('more')}} ..</button>
              </div><!-- block_bottom -->
            </div><!-- content -->
          </div><!-- customer_information -->

          <services-statement v-if="reservation" :quick="false" :occ="occ"  @update="getReservation" :reservation="reservation"></services-statement>

      <div class="comments_block" v-permission="'view comments'" v-if="reservation">
        <div class="title">{{__('Notes on reservation')}}</div>
        <div class="content">
          <div class="all_comments_items" v-if="reservation.comments.length">

            <div class="comment_item"  v-for="(comment,i) in reservation.comments.slice(0,3)" :key="i">

                    <div class="user_info">
                        <div class="user_account" v-if="comment && comment.commenter">
                        <img v-if="comment.commenter.photo_url" alt="" v-bind:src="'https://fandaqah.s3.eu-central-1.amazonaws.com/'+comment.commenter.photo_url"/>
                        {{ comment.commenter.name }}
                        </div><!-- user_account -->


                        <time v-if="comment && comment.commenter">{{comment.created_at | formatDate24 }}</time>
                    </div><!-- user_info -->
                  <div class="desc" v-if="comment && comment.commenter">{{ltrim(comment.comment)}}</div>
                  <div class="actions" v-if="comment.commenter_id == current_user_id">
                        <button class="edit_button" @click="openEditCommentModal(comment)"></button>
                        <button class="trash_button" @click="openNoteDeleteConfirm(comment)"></button>
                   </div>



            </div><!-- comment_item -->
            <div v-if="reservation.comments.length > 3" @click="openCommentsModal" class="more_comments"></div>
          </div><!-- all_comments_items -->
          <div v-if="reservation.comments == 0 " class="no_comments">
            <div>
              <div class="icon"></div>
              <span>{{__('There is no notes')}}</span>
            </div>
          </div><!-- no_comments -->
          <div class="block_bottom">
            <button class="add_comment" @click="openAddCommentModal" v-permission="'add comments'">{{__('Add a note')}}</button>
            <button v-if="reservation.comments.length > 3" @click="openCommentsModal" class="more">{{__('more')}} ({{reservation.comments.length}}) ..</button>
          </div><!-- block_bottom -->
        </div><!-- content -->
      </div><!-- comments_block -->

        </div><!-- left_col -->

      </div><!-- row_cols -->

       <!-- Start Comments -->
      <sweet-modal v-if="reservation" :enable-mobile-fullscreen="false" :pulse-on-block="false" :title="__('Notes on reservation')" overlay-theme="dark" ref="commentsModal" class="comments_modal">
        <div class="comment_item"  v-for="(comment,i) in reservation.comments" :key="i" @click="openEditCommentModal(comment)">

          <div class="user_info">
            <div class="user_account" v-if="comment && comment.commenter">
              <img v-if="comment.commenter.photo_url" alt="" v-bind:src="'https://fandaqah.s3.eu-central-1.amazonaws.com/'+comment.commenter.photo_url"/>
              {{ comment.commenter.name }}
            </div><!-- user_account -->
            <time v-if="comment && comment.commenter">{{comment.created_at | formatDate }}</time>
          </div><!-- user_info -->

          <div class="desc" v-if="comment && comment.commenter">{{ltrim(comment.comment)}}</div>
            <div class="actions" v-if="comment.commenter_id == current_user_id">
                <button class="edit_button" @click="openEditCommentModal(comment)"></button>
                <button class="trash_button" @click="openNoteDeleteConfirm(comment)"></button>
            </div>

        </div><!-- comment_item -->
      </sweet-modal>
      <!-- End Comments -->


      <reservation-logs v-if="reservation" :reservation="reservation"> </reservation-logs>

      <!-- Start Payment Link Modal  -->
      <sweet-modal :enable-mobile-fullscreen="false" :pulse-on-block="false" :title="__('Send Payment Link')" overlay-theme="dark" ref="paymentlinkModal" class="payment_link_modal relative">

        <loading
          :active="sendSmsLoading || sendEmailLoading"
          :loader="'spinner'"
          :color="'#7e7d7f'"
          :opacity="0.6"
          :is-full-page="false"
        >
        </loading>
        <div class="cols">
          <div class="col">
            <label for="customer_mobile">{{__('Phone Number')}}</label>
            <div class="phone">
              <input v-model="payment_link_buyer_phone" name="customer_mobile" type="tel" id="customer_mobile" placeholder="9665XXXXXXXX">
            </div><!-- phone -->
          </div><!-- col -->

          <div class="col">
            <label for="link">{{__('Email')}}</label>
            <div class="phone">
              <input v-model="payment_link_buyer_email" name="customer_email" type="email" id="customer_email" placeholder="you@email.com">
            </div><!-- email -->
          </div><!-- col -->
        </div><!-- cols -->

        <div class="cols">
          <div class="col">

            <label for="link">{{__('Amount to charge')}}</label>
            <div>
              <input  ref="amount_to_charge" v-model="amount_to_charge" name="amount_to_charge" type="tel" id="amount_to_charge" :placeholder="__('Amount to charge')">
            </div>
          </div>

          <div class="col">
            <label for="link">{{__('Payment Link')}}</label>
            <div>
              <button class="copy_btn"  @click="copy">{{__('Copy')}}</button>
            </div><!-- phone -->
          </div><!-- col -->
        </div>

        <div class="share_button_reservation">


          <button @click="sendSms" class="sms_button"></button>
          <button @click="sendEmail" class="email_button"></button>
          <a :href="`https://wa.me/${payment_link_buyer_phone}?text=${whats_app_payment_link}`" target="_blank" class="whatsapp_button"></a>
          <!-- <a class="print_button" :href="'/home/reservation/sub-invoice/'+2" target="_blank"></a> -->


        </div><!-- share_button_reservation -->
        <!-- <sweet-button slot="button" class="add_payment_link_button">{{__('Send')}}</sweet-button> -->
      </sweet-modal>
      <!-- End Payment Link Modal  -->

      <!-- Start Customer Notes Modal -->
      <sweet-modal :enable-mobile-fullscreen="false" :pulse-on-block="false" :title="__('Notes')" overlay-theme="dark" ref="notesModal" class="customer_notes_modal">
        <div class="note_item" v-for="note in customerNotes">
          <div class="desc">{{ltrim(note.comment)}}</div>
          <div class="user_info">
            <div class="user_account">
              <img v-if="note.commenter.photo_url" alt="" v-bind:src="'https://fandaqah.s3.eu-central-1.amazonaws.com/'+note.commenter.photo_url"/>
              {{ note.commenter.name }}
            </div><!-- user_account -->
            <time>{{note.created_at | formatDate }}</time>
          </div><!-- user_info -->
        </div><!-- note_item -->
      </sweet-modal>
      <!-- End Customer Notes Modal -->



      <!-- Start Transactions -->
      <sweet-modal v-if="reservation" :enable-mobile-fullscreen="false" :pulse-on-block="false" :title="__('Transactions')" overlay-theme="dark" ref="transactionsModal" class="transactions_modal">
          <template v-if="reservation.reservation_type == 'single' && reservation.transactions.length">
                <div class="financial_item"  v-for="(transaction,index) in reservation.transactions" :key="index" @click="openTransactionModal(transaction)">
                    <div class="col_right">
                        <span>{{__('Number')}} : <p>{{transaction.number}}</p></span>
                    <template v-if="reservation.wallet.decimal_places == 3">
                        <span>{{__('Amount')}} : <p class="d-inline-flex" v-bind:class="{ 'text-danger': transaction.type == 'withdraw' ,'text-success': transaction.type == 'deposit' }">{{transaction.amount / 1000}} <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></p></span>
                        </template>
                        <template v-else>
                            <span>{{__('Amount')}} : <p class="d-inline-flex" v-bind:class="{ 'text-danger': transaction.type == 'withdraw' ,'text-success': transaction.type == 'deposit' }">{{transaction.amount / 100}} <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></p></span>
                        </template>
                    </div><!-- col_right -->
                    <div class="col_left">
                        <time>{{transaction.meta.date}}</time>
                        <div class="name">
                          <p
                            class="credit_payment"
                            v-if="transaction.meta && transaction.meta.payment_type == 'credit-payment'"
                          >
                            {{__('Credit Payment')}}
                          </p>
                        <p class="insurance" v-if="transaction.is_insurance">{{__('Insurance')}}</p>
                        <label v-bind:class="{ 'text-danger': transaction.type == 'withdraw' ,'text-success': transaction.type == 'deposit' }">{{__(transaction.type)}}</label></div>
                    </div><!-- col_left -->
                </div><!-- financial_item -->
          </template>
          <template v-else>
              <div class="financial_item"  v-for="(transaction,index) in reservation.group_reservation_transactions" :key="index" @click="openTransactionModal(transaction)">
                    <div class="col_right">
                        <span>{{__('Number')}} : <p>{{transaction.number}}</p></span>
                    <template v-if="reservation.wallet.decimal_places == 3">
                        <span>{{__('Amount')}} : <p v-bind:class="{ 'text-danger': transaction.type == 'withdraw' ,'text-success': transaction.type == 'deposit' }">{{transaction.amount / 1000}} <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></p></span>
                        </template>
                        <template v-else>
                            <span>{{__('Amount')}} : <p v-bind:class="{ 'text-danger': transaction.type == 'withdraw' ,'text-success': transaction.type == 'deposit' }">{{transaction.amount / 100}} <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></p></span>
                        </template>
                    </div><!-- col_right -->
                    <div class="col_left">
                        <time>{{transaction.meta.date}}</time>
                        <div class="name">
                        <p class="insurance" v-if="transaction.is_insurance">{{__('Insurance')}}</p>
                        <label v-bind:class="{ 'text-danger': transaction.type == 'withdraw' ,'text-success': transaction.type == 'deposit' }">{{__(transaction.type)}}</label></div>
                    </div><!-- col_left -->
                </div><!-- financial_item -->
          </template>

      </sweet-modal>
      <!-- End Transactions -->

      <!-- Start Edit Custmer  -->
      <sweet-modal v-if="reservation" :enable-mobile-fullscreen="false" :pulse-on-block="false" ref="customerModal" overlay-theme="dark" :title="__('View Customer')">
        <div class="flex flex-wrap -mx-1 overflow-hidden edit_customer_Model">
          <div class="my-1 px-1 w-full overflow-hidden md:w-1/2">
            <div class="list-style p-2 flex justify-between w-full">
              <div class="text-lg">{{__('Name')}} :</div>
              <div class="text-base">{{reservation.customer.name}}</div>
              <div v-if="reservation.customer.label" v-html="reservation.customer.label" class="customer_label"></div>
            </div>
          </div>
          <div class="my-1 px-1 w-full overflow-hidden md:w-1/2">
            <div class="list-style p-2 flex justify-between w-full">
              <div class="text-lg">{{__('Email')}} :</div>
              <div class="text-base">{{reservation.customer.email}}</div>
            </div>
          </div>
          <div class="my-1 px-1 w-full overflow-hidden md:w-1/2">
            <div class="list-style p-2 flex justify-between w-full">
              <div class="text-lg">{{__('Phone')}} :</div>
              <div class="dir-ltr text-base">{{reservation.customer.phone}}</div>
            </div>
          </div>
          <div class="my-1 px-1 w-full overflow-hidden md:w-1/2">
            <div class="list-style p-2 flex justify-between w-full">
              <div class="text-lg">{{__('Gender')}} :</div>
              <div class="text-base">{{reservation.customer.gender}}</div>
            </div>
          </div>
          <div class="my-1 px-1 w-full overflow-hidden md:w-1/2">
            <div class="list-style p-2 flex justify-between w-full">
              <div class="text-lg">{{__('ID No')}} :</div>
              <div class="text-base">{{reservation.customer.id_number}}</div>
            </div>
          </div>
          <div class="my-1 px-1 w-full overflow-hidden md:w-1/2">
            <div class="list-style p-2 flex justify-between w-full">
              <div class="text-lg">{{__('Nationality')}} :</div>
              <div class="text-base">{{reservation.customer.nationality_string}}</div>
            </div>
          </div>
          <div class="my-1 px-1 w-full overflow-hidden md:w-1/2">
            <div class="list-style p-2 flex justify-between w-full">
              <div class="text-lg">{{__('Customer Type')}} :</div>
              <div class="text-base">{{reservation.customer.id_type_string}}</div>
            </div>
          </div>
          <div class="my-1 px-1 w-full overflow-hidden md:w-1/2">
            <div class="list-style p-2 flex justify-between w-full">
              <div class="text-lg">{{__('Work')}} :</div>
              <div class="text-base">{{reservation.customer.id_type_string}}</div>
            </div>
          </div>
          <div class="my-1 px-1 w-full overflow-hidden md:w-1/2">
            <div class="list-style p-2 flex justify-between w-full">
              <div class="text-lg">{{__('Work Phone')}} :</div>
              <div class="text-base">{{reservation.customer.work_phone}}</div>
            </div>
          </div>
          <div class="my-1 px-1 w-full overflow-hidden md:w-1/2">
            <div class="list-style p-2 flex justify-between w-full">
              <div class="text-lg">{{__('Address')}} :</div>
              <div class="text-base">{{reservation.customer.address}}</div>
            </div>
          </div>
          <div class="my-1 px-1 w-full overflow-hidden md:w-1/2">
            <div class="list-style p-2 flex justify-between w-full">
              <div class="text-lg">{{__('Birthday Date')}} :</div>
              <div class="text-base">{{reservation.customer.birthday_date}}</div>
            </div>
          </div>
        </div>
        <div class="edit-customer-button">
          <sweet-button slot="button" @click="editCustomer" class="btn btn-default btn-outline-primary">{{__('Edit Customer')}}</sweet-button>
        </div>
      </sweet-modal>
      <!-- End Edit Custmer  -->



      <!-- Start Add New Comment  -->
      <sweet-modal :enable-mobile-fullscreen="false" :pulse-on-block="false" :title="__('Add a new note')" overlay-theme="dark" ref="addCommentModal" class="add_comment_modal">
        <div class="relative">
            <loading :active="addCommentIsActive"
                     :loader="'spinner'"
                     :color="'#7e7d7f'"
                     :opacity="0.6"
                     :is-full-page="false">
            </loading>
            <textarea wrap="soft" cols="30" :placeholder="__('Write your note here ..')" rows="3" v-model="comment"></textarea>
            <button @click="sendComment" class="save_button"> {{__('Add a note')}}</button>
        </div>
      </sweet-modal>
      <!-- End Add New Comment  -->

      <!-- Start Transaction Modal  -->
      <sweet-modal v-if="current_transaction" :enable-mobile-fullscreen="false" :pulse-on-block="false" width="70%" :title="__('Transaction Details')" overlay-theme="dark" ref="transactionModal" class="transaction_modal">
        <div class="share_button_reservation">
          <social-sharing :url=" current_url +  '/home/reservation/sub-invoice/'+ transaction_hash_id + '/public?lang=' + local" inline-template>
            <network network="whatsapp">
              <a href="#" class="whatsapp_button"></a>
            </network>
          </social-sharing>
          <a class="pdf_button" :href="'/home/reservation/pdf/sub-invoice/' + transaction_hash_id " target="_blank"></a>
          <a v-permission="'print reservation transactions'" class="print_button" :href="'/home/reservation/sub-invoice/'+transaction_hash_id" target="_blank"></a>

          <template v-if="((current_transaction.type === 'deposit' && !reservation.checked_out && reservation.status != 'canceled') && ((current_transaction.meta && current_transaction.meta.statement != 'Billed Online') && (current_transaction.meta && current_transaction.meta.statement != 'Billed Online Fulfill Promissory Number'))) || ( occ )">
              <button class="edit_button" v-permission="'edit receipts'" @click="openEditTransation"></button>
          </template>
          <template v-else-if="(current_transaction.type === 'withdraw' && !reservation.checked_out && reservation.status != 'canceled') || (occ)">
              <button class="edit_button" v-permission="'edit payments'" @click="openEditTransation"></button>
          </template>
          <button v-if="((current_transaction.meta && current_transaction.meta.statement != 'Billed Online') && (current_transaction.meta && current_transaction.meta.statement != 'Billed Online Fulfill Promissory Number')) && (!reservation.checked_out && reservation.status != 'canceled') || (occ)" class="trash_button" @click="deleteTransaction(current_transaction.id)"  v-permission="'delete statements'" v-show="!hideDeleteButton"></button>

          <sms-component
            v-if="current_transaction.type == 'deposit' && current_transaction.meta_payment_type != 'rebate' "
            :entity_id="reservation.attachable_id ? reservation.attachable_id : reservation.id"
            :document_url="`${current_url}/home/reservation/sub-invoice/${transaction_hash_id}/public?lang=${local}`"
            :document_type="'transaction'"
            :sms_base_title="setSmsBaseTitle('transaction')"
          />
        </div><!-- share_button_reservation -->
        <div class="embed_area">
<!--          <embed :src="'/home/reservation/pdf/sub-invoice/' + transaction_hash_id " type="application/pdf">-->
            <iframe v-if="current_transaction.id != undefined" id="callTransactionIframeId" :src="'/home/reservation/sub-invoice/'+current_transaction.id  + '?typeFrame=embed'"></iframe>
        </div><!-- embed_area -->
      </sweet-modal>
      <!-- End Transaction Modal  -->

      <edit-payment-voucher
        v-permission="'edit payments'"
        :transaction="current_transaction"
        :show.sync="edit_payment_show"
        :reservation="reservation"
        v-on:update-reservation="updateReservation"
      >
      </edit-payment-voucher>

      <edit-cash-receipt
        v-permission="'edit receipts'"
        :transaction="current_transaction"
        :show.sync="edit_cash_receipt_show"
        :reservation="reservation"
        v-on:update-reservation="updateReservation"
      >
      </edit-cash-receipt>

      <!-- Start Send Mail Modal -->
      <sweet-modal v-if="reservation" :enable-mobile-fullscreen="false" :pulse-on-block="true" class="sendmailModal" title="إرسال رسالة" overlay-theme="dark" ref="mailModal">
        <div id="tabs">
          <div class="tabs">
            <a v-bind:class="[ activetab === '1' ? 'active' : '' ]" v-on:click="activetab='1'">إرسال بريد إلكتروني</a>
            <a v-bind:class="[ activetab === '2' ? 'active' : '' ]" v-on:click="activetab='2'">إرسال رسالة نصية</a>
            <a v-bind:class="[ activetab === '3' ? 'active' : '' ]" v-on:click="activetab='3'">سجل الرسائل</a>
          </div>
          <div class="content">
            <div class="tabcontent relative" v-if="activetab ==='1'">
                <loading :active="isActive"
                         :loader="'spinner'"
                         :color="'#7e7d7f'"
                         :opacity="0.6"
                         :is-full-page="false">
                </loading>
              <div class="formgroup">
                <label for="customer_email">بريد العميل :</label>
                <input :value="reservation.customer.email" disabled="disabled" id="customer_email" type="text">
              </div><!-- formgroup -->
              <div class="formgroup">
                <label for="email_subject">عنوان الرسالة :</label>
                <input id="email_subject" type="text" value="" v-model="email_subject">
              </div><!-- formgroup -->
              <div class="formgroup">
                <label for="email_message">نص الرسالة :</label>
                <textarea cols="30" id="email_message" name="#" rows="4" v-model="email_message"></textarea>
              </div><!-- formgroup -->
              <div class="block_bottom">
                <button @click="sendMail('mail')" class="send">إرسال</button>
                <button @click="closeSendMailModal" class="close" type="reset">تراجع</button>
              </div><!-- block_bottom -->
            </div><!-- formgroup -->

            <div class="tabcontent relative" v-if="activetab ==='2'">
              <div class="formgroup">
                <label for="customer_phone">رقم العميل :</label>
                <input :value="reservation.customer.phone" disabled="disabled" id="customer_phone" type="text">
              </div><!-- formgroup -->
              <div class="formgroup">
                <label for="message">نص الرسالة :</label>
                <textarea cols="30" id="message" name="#" rows="4" v-model="sms_message"></textarea>
              </div><!-- formgroup -->
              <div class="block_bottom">
                <button @click="sendMail('sms')" class="send">إرسال</button>
                <button @click="closeSendMailModal" class="close" type="reset">تراجع</button>
              </div><!-- block_bottom -->
              <div v-if="!check_sms_getway">
                <div class="sms_not_available">
                  قم بتفعيل الربط مع احد مقدمي خدمة الرسائل النصية من خلال <router-link :to="'/settings/integrations'">إعدادات التكامل</router-link>
                </div>
              </div>
              <div v-if="!no_balance">
                <div class="sms_not_available">
                  قم بمراجعة الرصيد الخاص بك  <router-link :to="'/settings/integrations'">إعدادات التكامل</router-link>
                </div>
              </div>
            </div>
            <div class="tabcontent" v-if="activetab ==='3'">
                <div v-if="reservation.messages_logs.length">
                  <table>
                    <thead>
                      <tr>
                        <th width="65%">نص الرسالة</th>
                        <th width="20%">تاريخ الإرسال</th>
                        <th width="15%">نوع الارسال</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="(log, index) in reservation.messages_logs" :key="index">
                        <td>{{log.message}}</td>
                        <td>{{log.date}}</td>
                        <td>{{__(log.type)}}</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
                <div class="no-messages" v-else>
                    لا يوجد رسائل فى السجل
                </div>
            </div>
          </div>
        </div>
      </sweet-modal>
      <!-- End Send Mail Modal -->

      <!-- Start Invoice -->
      <sweet-modal v-if="reservation" :enable-mobile-fullscreen="false" :pulse-on-block="false"  width="70%" :title="__('Invoice')" overlay-theme="dark" ref="invoiceModal" class="invoice_modal">
        <div class="share_button_reservation">
          <social-sharing :url="current_url + '/home/reservation/generalInvoice/' + hash_id + '/public?lang=' + local" inline-template>
            <network network="whatsapp">
              <a href="#" class="whatsapp_button"></a>
            </network>
          </social-sharing>
          <!-- <a class="pdf_button" :href="'/home/reservation/pdf/generalInvoice/' + reservation.hash_id + '?lang=' + local " target="_blank"></a> -->
          <a v-permission="'print invoices'" class="print_button" :href="'/home/reservation/generalInvoice/' + reservation.hash_id + '?lang=' + local  " target="_blank"></a>
        </div><!-- share_button_reservation -->
        <div class="embed_area">
<!--          <embed :src="'/home/reservation/pdf/generalInvoice/' + reservation.hash_id" type="application/pdf">-->
            <iframe v-if="generalInvoiceSrc" id="invoiceModalSrc" :src="generalInvoiceSrc"></iframe>
        </div><!-- embed_area -->
      </sweet-modal>

      <convert-to-group-reservation ref="convertModal" :reservation="reservation" />
      <!-- End Invoice -->
       <edit-note :comment="targetComment"  ref="editNoteHandler" />
        <delete-confirm-component v-if="targetComment" :id="targetComment.id" ref="deleteNoteHaandlerFromReservation" />


    </div>
  </div>
</template>

<script>
  import RemoveCompany from "./partial/RemoveCompany";
  import ChangeCompany from "./partial/ChangeCompany";
  import GroupReservationMultipleCancel from "./partial/GroupReservationMultipleCancel";
  import GroupReservationMultipleCheckout from "./partial/GroupReservationMultipleCheckout";
  import GroupReservationMultipleCheckin from "./partial/GroupReservationMultipleCheckin";
  import GroupReservationPrices from "./partial/GroupReservationPrices";
  import GroupReservationExtenderModal from "./partial/GroupReservationExtenderModal";

  import ConvertToGroupReservation from "./partial/ConvertToGroupReservation";
  import GroupReservationsManualInvoices from "./partial/GroupReservationsManualInvoices";
  import CompanyCreditorDebtor from '../components/CompanyCreditorDebtor';
  import CompanyLiveInvoice from './partial/CompanyLiveInvoice';

  import CustomerCreditorDebtorReservationScope from './CustomerCreditorDebtorReservationScope';
  import ReservationConfirm from "./partial/ReservationConfirm";
  import AddInvoice from "./partial/AddInvoice";
  import EditNote from './partial/EditNote.vue';
  import CancelCheckout from "./partial/CancelCheckout";
  import Loading from 'vue-loading-overlay';
  import 'vue-loading-overlay/dist/vue-loading.css';
import CompanyLiveContract from './partial/CompanyLiveContract';
import CompanyLiveSummary from './partial/CompanyLiveSummary';
import CompanyReservationCheckout from './partial/CompanyReservationCheckout';
import DeleteConfirmComponent from './partial/DeleteConfirmComponent';
import SmsComponent from "./SmsComponent";
import Rebate from './partial/Rebate';
import ReservationFees from './partial/ReservationFees.vue';
import TransactionBalance from './partial/TransactionBalance.vue';

export default {
    name: "reservation",
    components: {
        AddInvoice,
        Loading,
        CustomerCreditorDebtorReservationScope,
        CancelCheckout,
        EditNote,
        ReservationConfirm,
        CompanyLiveInvoice,
        CompanyLiveContract,
        CompanyLiveSummary,
        CompanyReservationCheckout,
        CompanyCreditorDebtor,
        DeleteConfirmComponent,
        GroupReservationsManualInvoices,
        ConvertToGroupReservation,
        GroupReservationPrices,
        GroupReservationMultipleCheckin,
        GroupReservationMultipleCheckout,
        GroupReservationMultipleCancel,
        RemoveCompany,
        ChangeCompany,
        SmsComponent,
        Rebate,
        ReservationFees,
        GroupReservationExtenderModal,
        TransactionBalance
      },
      data: () => {
            return {
                check_sms_getway: false,
                email_subject: null,
                email_message: null,
                no_balance: true,
                sms_message: null,
                hash_id: null,
                loaded: false,
                local: null,
                activetab: '1',
                cash_receipt: {
                    kind: null,
                    date: moment(moment().format('YYYY-MM-DD')).toDate(),
                    from: null,
                    amount: null,
                    type: null,
                    description: null,
                    note: null,
                    currency :Nova.app.currentTeam.currency,
                    team : Nova.app.currentTeam

                },
                loading: true,
                current_transaction: {},
                deposit_type: 'cash',
                deposit_amount: 0,
                customer: null,
                reservation: null,
                unit: null,
                modal: null,
                comment: null,
                current_url: null,
                transaction_hash_id: null ,
                logout_icon : null ,
                login_icon : null,
                edit_payment_show : false,
                edit_cash_receipt_show : false,
                day_start : null ,
                unifonic : null ,
                day_end : null,
                customerNotes : [],
                isActive: false,
                addCommentIsActive : false,
                activateLoaderInReservation : true,
                occ : false,
                canCancelCheckout : false,
                total_balance : 0 ,
                reservations_count : 0,
                hideDeleteButton : false,
                source_num : null,
                targetComment : null,
                current_user_id : Nova.config.userId,
                generalInvoiceSrc : null,
                currency :Nova.app.currentTeam.currency,
                payment_link_buyer_phone: null,
                payment_link_buyer_email: null,
                payment_link: null,
                amount_to_charge : null,
                sms_text_label : Nova.app.__('Pay your reservation now at hotel :hotel , enjoy your stay' , {hotel : Nova.app.currentTeam.name}),
                whats_app_payment_link : null,
                sendSmsLoading : false,
                sendEmailLoading : false,
                cancel_reservation_after_checkin_after_revenue : false,
                cancel_reservation_after_checkin_before_revenue : false,
                cancel_reservation_before_checkin : false,
                user_has_permission_to_cancel_reservation_before_checkin : false,
                user_has_permission_to_cancel_reservation_after_checkin_before_night_run : false,
                user_has_permission_to_cancel_reservation_after_checkin_after_night_run : false,
                today_date : null,
                cancellationFees: 0,
                noShowFees: 0,
                user: {
                  digital_signature: null
                },
                showActions: false
            }
        },

      computed: {
        shouldShowCancel() {
            const r = this.reservation;
            const isToday = this.formatDateCustom(r.date_in) === this.today_date;

            const beforeCheckin = !r.checked_in && this.cancel_reservation_before_checkin;
            const afterCheckinBeforeNight = r.checked_in && this.user_has_permission_to_cancel_reservation_after_checkin_before_night_run && isToday;
            const afterCheckinAfterNight = r.checked_in && this.user_has_permission_to_cancel_reservation_after_checkin_after_night_run && !isToday;

            return (
              (r.status === 'confirmed' || r.status === 'awaiting-payment' || r.status === 'awaiting-confirmation') &&
              !r.checked_out &&
              (beforeCheckin || afterCheckinBeforeNight || afterCheckinAfterNight)
            );
          },
            grandTotalRounded(){
                // return this.ServicesSum + this.reservation.total_price;
              return  Number(this.ServicesSum + this.reservation.total_price)
            },
            // a computed getter
            ServicesSum: function () {
                let decimals = this.reservation.wallet.decimal_places;
                let sum  = this.reservation.services.reduce(function(prev, cur) {
                  if(decimals == 3){
                       return prev + cur.amount/ 1000;
                  }
                    return prev + cur.amount/ 100;
                }, 0);
                return Math.abs(sum);
            }
        },
      methods: {
        toggleActions() {
          this.showActions = !this.showActions;
        },
        closeActions() {
          this.showActions = false;
        },
        formatDateCustom(date){
                return moment(date).format('YYYY-MM-DD');
          },
          convertReservationToGroupReservation(){
            var invoices;
            if(this.reservation.reservation_type == 'group'){
              invoices = this.reservation.shared_invoices;
            }else{
              invoices = this.reservation.invoices;
            }
            if(invoices.length){
                let invoices_without_credit_notes = _.filter(invoices, function(invoice) {
                      return invoice.invoice_credit_note === null;
                });
                if(invoices_without_credit_notes.length){
                    this.$toasted.show(this.__('Please cancel all invoices on this reservation to proceed'), {type: 'error'});
                    return;
                }
            }

            // all now clear we need to open the modal to convert the reservation to a group reservation
            this.$refs.convertModal.$refs.convertReservationModal.open();
          },
            checkSmsIntegration(){
              Nova.request().get('/api/check_integrate_with_sms_gateway', {
                      params : {
                        team_id: Nova.app.user.current_team_id
                      }
              })
              .then(response => {
                if(response.data.data.type == 'no_balance'){
                    this.no_balance = response.data.data.check;
                }else{
                    this.check_sms_getway = response.data.data.check;

                }

              })
            },
            getTotalBalance(){
               Nova.request().get(`/nova-vendor/calender/customer-total-balance?id=${this.reservation.customer_id}`)
                .then(response => {
                    this.total_balance = parseFloat(response.data.total_balance).toFixed(2);
                    this.reservations_count = response.data.reservations_count;
                    if(this.reservations_count === 1 && this.route_name === 'reservation'){
                        this.reservations_count = 0;
                    }
                })
            },

            openCreditorDebitorModal(){
              if(this.reservation.reservation_type == 'single'){
                this.$refs.initCustomerCreditorDebitorModal.$refs.resScope.open();
              }else{
                this.$refs.initCompanyCreditorDebitorModal.$refs.companyCreditorDebtorModal.open();
              }
            },
            formatter:  (value, amount) => {
              const split = value.toString().split('.');
              if (split.length > 1) {
                  split[split.length-1] = split[split.length-1].substring(0, amount);
              }
              return amount > 0 ? split.join('.') : split[0];
            },

            gotoPromissoryManagement(){
                this.$router.push({ name : 'promissories' , query : {team_id : Nova.config.user.current_team_id , per_page : 20 , 'filter[by_status]' : 'pending'}});
            },
            openDisplayNotesModal(){
                this.$refs.notesModal.open()
            },
            logType(type) {

            },
            sendMail(type) {
                let object = {};
                if (type == 'mail') {
                    if (!this.email_subject || !this.email_message) {
                        this.$toasted.show(this.__('Please fill email info'), {type: 'error'})
                        return;
                    }
                    object = {
                        type: type,
                        email_subject: this.email_subject,
                        email_message: this.email_message,
                    }
                }
                if (type == 'sms') {
                    if (!this.sms_message ) {
                        this.$toasted.show(this.__('Please fill sms info'), {type: 'error'})
                        return;
                    }
                    object = {
                        type: type,
                        sms_message: this.sms_message,
                    }
                }

                this.isActive = true;
                axios
                    .post('/nova-vendor/calender/reservation/' + this.reservation.id + '/messages' , {
                        data: object,
                    })
                    .then(response => {
                        console.log(response.data.data.check);
                        if(response.data.data.check){

                            this.$toasted.show(this.__('message sent successfully !'), {type: 'success'})
                        }
                        if(response.data.data.check == false && response.data.data.type == 'balance'){
                            this.$toasted.show(this.__(' Message Limit has been consumed, Kindly renew it'), {type: 'error'})
                        }

                        if(response.data.data.check == false && response.data.data.type == 'sms'){
                            this.$toasted.show(this.__('Kindly Reconnect with Rasil Service'), {type: 'error'})
                        }
                        this.loading = false;
                        this.email_subject = null;
                        this.email_message = null;
                        this.sms_message = null;
                        this.isActive = false;
                        this.refreshReservation();
                        this.$refs.mailModal.close();
                    }).catch(err => {

                    this.$toasted.show(this.__('Error !'), {type: 'error'})
                })
            },
            pushToCustomerDetails(id){
                Nova.app.$router.push(`/new/customers/${id}`) ;
            },
            pushToCompanyDetails(reservation){
              if(reservation.company && reservation.company.entity_type == 'individual' && reservation.customer_id){
                Nova.app.$router.push(`/new/customers/${reservation.customer_id}`) ;
              }else{
                Nova.app.$router.push(`/companies/${reservation.company.id}/profile`) ;
              }
            },
            openCustomerModal() {
                this.$refs.customerModal.open()
            },
            openAddPaymentLinkModal() {
              this.payment_link_buyer_phone = this.reservation.customer.phone.trim();
              this.payment_link_buyer_email = this.reservation.customer.email;
              // this.payment_link = `${window.APP_URL}/reservation/collect-payments?id=${this.reservation.id}&n=${this.amount_to_charge}&l=${this.local}`;
              var data = {
                id : this.reservation.id,
                n : this.amount_to_charge,
                l : this.local
              };
              var base64cat = btoa(JSON.stringify(data));
              this.payment_link = `${window.APP_URL}/reservation/collect-payments?data=${base64cat}`;
              this.whats_app_payment_link = this.sms_text_label + ' ' + encodeURIComponent(this.payment_link);
              this.amount_to_charge = this.reservation.wallet.decimal_places == 3 ?  (-1 * this.reservation.balance / 1000 ) : (-1 * this.reservation.balance / 100 );
              this.$refs.paymentlinkModal.open()
            },
            copy() {
              const el = document.createElement('textarea');
              el.value = this.payment_link;
              el.setAttribute('readonly', '');
              el.style.position = 'absolute';
              el.style.left = '-9999px';
              document.body.appendChild(el);
              const selected =  document.getSelection().rangeCount > 0  ? document.getSelection().getRangeAt(0) : false;
              el.select();
              document.execCommand('copy');
              document.body.removeChild(el);
              if (selected) {
                document.getSelection().removeAllRanges();
                document.getSelection().addRange(selected);
              }

              this.$toasted.show(this.__('Payment link copied to clipboard'), {type: 'info'});

            },
            sendSms(){
               if(!this.check_sms_getway){
                this.$toasted.show(this.__('Please integrate with sms provider through integration settings'), {
                    type: 'info', action: {
                        text: 'Integrations',
                        push: {
                            name: 'settings.integrations',
                            // this will prevent toast from closing
                            dontClose: true
                        }
                    },
                });

                return;
              }

              if(!this.payment_link_buyer_phone){
                this.$toasted.show(this.__('Phone number is required to perform this action'), {type: 'error'});
                return;
              }

              if(this.amount_to_charge <= 0){
                this.$toasted.show(this.__('Amount to pay is invalid'), {type: 'error'});
                return;
              }

              this.sendSmsLoading = true;
              axios.post('/nova-vendor/calender/send-payment-sms' , {
                phone : this.payment_link_buyer_phone,
                sms_content : this.whats_app_payment_link
              })
              .then(response => {
                if(response.data.success){
                  this.$toasted.show(this.__('Payment link has been sent successfully'), {type: 'success'});
                }else{
                  this.$toasted.show(this.__('Something went wrong. Please try again or contact customer support.'), {type: 'error'});
                }
                this.sendSmsLoading = false;
              })
            },
            sendEmail(){
              if(!this.payment_link_buyer_email){
                this.$toasted.show(this.__('Email is required to perform this action'), {type: 'error'});
                return;
              }

              if(this.amount_to_charge <= 0){
                this.$toasted.show(this.__('Amount to pay is invalid'), {type: 'error'});
                return;
              }

              this.sendEmailLoading = true;
              axios.post('/nova-vendor/calender/send-payment-email' , {
                reservation_id : this.reservation.id,
                email : this.payment_link_buyer_email,
                payment_link : this.payment_link
              })
              .then(response => {
                if(response.data.success){
                  this.$toasted.show(this.__('Payment link has been sent successfully'), {type: 'success'});
                }else{
                  this.$toasted.show(this.__('Something went wrong. Please try again or contact customer support.'), {type: 'error'});
                }
                this.sendEmailLoading = false;
              })

            },
            openTransactionBalanceModal(reservationId) {
              this.$refs.transactionBalanceModal.open(reservationId);
            },
            openInvoiceModal() {
                this.refreshReservation();
                this.generalInvoiceSrc = '/home/reservation/generalInvoice/' + this.reservation.hash_id + '?type=embed&lang=' + this.local;
                 $( '#invoiceModalSrc' ).attr( 'src', function ( i, val ) { return val; });
                this.$refs.invoiceModal.open()
            },
            sendMailModal() {
                this.email_subject = null;
                this.email_message = null;
                // this.checkSmsIntegration();
                this.$refs.mailModal.open()
                //email_message
                const innerhtml = document.getElementById('message');
                console.log(innerhtml);

            },
            closeSendMailModal() {
                this.$refs.mailModal.close()
            },
            openPaymentModal() {
                this.$refs.cashPaymentModal.open()
            },
            openAddCommentModal() {
                this.$refs.addCommentModal.open()
            },
            openEditCommentModal(comment){
                if(comment.commenter_id != this.current_user_id)
                    return false;


                this.targetComment = comment;
                setTimeout(() => {
                    this.$refs.editNoteHandler.$refs.updateNote.open();
                }, 0);
            },

            openNoteDeleteConfirm(comment){

                 this.targetComment = comment

                setTimeout(() => {
                    this.$refs.deleteNoteHaandlerFromReservation.$refs.deleteNote.open();
                }, 0);


            },
            openCashReceiptModal() {
                // this.cash_receipt.from = 1;
                this.$refs.cashReceiptModal.open()
            },
            openTransactionsModal() {

                this.$refs.transactionsModal.open()
            },

            openTransactionModal(transaction) {

                this.current_transaction = transaction
                this.hideDeleteButton  = false;




                this.hashTransactionId(transaction.id);
                setTimeout(() => {
                     $('#callTransactionIframeId').attr( 'src', function ( i, val ) {  return val;  });
                       this.$refs.transactionModal.open()
                }, 0);

                if(this.reservation.reservation_type == 'group'){
                    axios.get(`/nova-vendor/calender/group-reservation/sibling/${this.reservation.id}/check-insurance-transaction?from=reservation`)
                    .then((response) => {
                        if(this.current_transaction.type == 'deposit' && this.current_transaction.is_insurance && response.data.withdraw_insurance_transactions){
                            this.hideDeleteButton = true;
                        }
                    })
                }else{
                    if(this.current_transaction.type == 'deposit' && this.current_transaction.is_insurance && this.reservation.withdraw_insurance_transactions.length){
                        this.hideDeleteButton = true;
                    }
                }

            },
            openCommentsModal() {
                this.$refs.commentsModal.open()
            },
            openServicesModal() {
                this.getServices();
            },
            ltrim(str) {
                if (str == null) return str;
                return str.trim();
            },
            selected_service(a) {
                this.service_original_price = a.price;
                this.service_price = this.service_original_price * this.service_qty;
                this.service_statement = a.text
            },
            update_service_price() {
                this.service_price = this.service_original_price * this.service_qty
            },
            getReservation() {
                if(this.activateLoaderInReservation){
                    this.loading = true;
                }else{
                    this.loading = false;
                }
                var config = {headers: {'Content-Type': 'application/json', 'Cache-Control': 'no-cache'}};
                axios
                    .get('/nova-vendor/calender/reservation/' + this.$route.params.id, config)
                    .then( (response) => {
                        this.reservation = response.data;
                        // Hashing Just like that
                        this.hash_id = this.reservation.hash_id;
                        this.current_url = this.reservation.url_current;
                        this.logout_icon = this.reservation.logout_icon ;
                        this.login_icon = this.reservation.login_icon ;
                        this.source_num = this.reservation.source_num;

                        this.day_start = this.reservation.day_start ;
                        this.day_end = this.reservation.day_end ;
                        this.customerNotes = this.reservation.customerNotes ;
                        this.loading = false;
                        this.activateLoaderInReservation = true;
                        this.amount_to_charge = this.reservation.wallet.decimal_places == 3 ?  (-1 * this.reservation.balance / 1000 ) : (-1 * this.reservation.balance / 100 );
                        // this.getTotalBalance();
                        if(this.reservation.reservation_type == 'single'){
                          Nova.$emit('set_invoices_from_history_modal' , this.reservation.invoices);
                        }else{
                          Nova.$emit('set_invoices_from_history_modal' , this.reservation.shared_invoices);
                        }

                    }).catch(err => {
                        this.loading = false;
                        this.$toasted.show(this.__(err), {type: 'error'})
                    });
            },
            refreshReservation(){
                var config = {headers: {'Content-Type': 'application/json', 'Cache-Control': 'no-cache'}};
                axios
                    .get('/nova-vendor/calender/reservation/' + this.$route.params.id, config)
                    .then(response => {
                        this.reservation = response.data;


                        // Hashing Just like that
                        this.hash_id = this.reservation.hash_id;
                        this.current_url = this.reservation.url_current;
                        this.logout_icon = this.reservation.logout_icon ;
                        this.login_icon = this.reservation.login_icon ;
                        this.source_num = this.reservation.source_num;

                        this.day_start = this.reservation.day_start ;
                        this.day_end = this.reservation.day_end ;
                        this.customerNotes = this.reservation.customerNotes ;
                    })
            },
            updateReservation() {
                var config = {headers: {'Content-Type': 'application/json', 'Cache-Control': 'no-cache'}};
                axios
                    .get('/nova-vendor/calender/reservation/' + this.$route.params.id, config)
                    .then(response => {
                        this.reservation = response.data;
                    }).catch(err => {
                    this.$toasted.show(this.__(err), {type: 'error'})
                })
            },
            sendComment() {
                // this.loading = true;

                if(!this.comment){
                    this.$toasted.show(this.__('Note is required'), {type: 'error'});
                    return ;
                }
                this.addCommentIsActive = true;
                axios
                    .post('/nova-vendor/calender/comments', {
                        commentable_type: "App\\Reservation",
                        commentable_id: this.reservation.id,
                        message: this.comment,
                    })
                    .then(response => {
                        this.comment = null;
                        this.$refs.addCommentModal.close();
                        this.activateLoaderInReservation = false;
                        this.addCommentIsActive = false;
                        this.getReservation();
                        this.$toasted.show(this.__('Reservation Note Added Successfully'), {type: 'success'});
                        // this.loading = false;
                    }).catch(err => {
                    // this.getReservation();
                    // this.loading = false;
                    this.$toasted.show(this.__(err), {type: 'error'})
                })
            },

            getTime(t) {
                // console.log(t)
                return moment(t).format("hh:mm A");
            },
            getTimeBanner(t) {
                return moment(t).format("YYYY-MM-DD hh:mm A");
            },
            deleteTransaction(id) {
                if (confirm('Are you sure you want to delete this item?')) {
                    this.loading = true;
                    axios
                        .post('/nova-vendor/calender/reservation/delete-transaction', {
                            id: id,
                            reservation_id: this.reservation.id,
                        })
                        .then(response => {

                            this.$refs.transactionModal.close()
                            this.getReservation();

                            this.$toasted.show(this.__('Transaction Deleted Successfully'), {type: 'success'});
                            this.loading = false;
                        }).catch(err => {
                        this.loading = false;
                        if (err.response && err.response.data && err.response.data.message) {
                            this.$toasted.show(this.__(err.response.data.message), { type: 'error' });
                        } else {
                            this.$toasted.show(this.__('An error occurred while updating the transaction'), { type: 'error' });
                        }
                    })
                }
            },
            destroyed() {
                this.getReservation()
            },
            editCustomer() {
                window.location = '/home/resources/customers/' + this.reservation.customer.id + '/edit'
            },
            openEditTransation() {
                this.$refs.transactionModal.close();
                if (this.current_transaction.type == "deposit"){
                    this.edit_cash_receipt_show = true
                    this.edit_payment_show = false
                }else{
                    this.edit_payment_show = true
                    this.edit_cash_receipt_show = false
                }
            },
            hashTransactionId(transaction_id) {
                Nova.request().post('/nova-vendor/calender/hashTransactionId', {
                    transaction_id: transaction_id
                })
                    .then(response => {
                        this.transaction_hash_id = response.data;
                    });
            },
            formatDate(date) {
                return Nova.app.__formatDateWithHumanDate(date);
            },
           lastCheckedInReservation(id){
                axios.get(`/nova-vendor/calender/last-checkedin-reservation?id=${id}`)
                    .then((res) => {
                        if(res.data && !res.data.id){
                            // can reset the checkout
                            this.resetReservationCheckOut(id);
                        }

                    })
           },
          resetReservationCheckOut(id){
              // reset process goes here
              axios.get(`/nova-vendor/calender/reset-reservation-checkout?id=${id}`)
                .then((res) => {
                    this.getReservation();

                    if(res.data.status == 'reopen'){
                        this.canCancelCheckout = true;
                    }

                })
          },
          hasPermission(permission) {
            return Nova.app.$hasPermission(permission) ? true : false
          },
          setSmsBaseTitle(type){
            if(type == 'transaction'){
              return Nova.app.__('Deposit Transaction');
            }
          },
          openFeesModal() {
            this.$refs.feesModal.open();
          },
          addCancellationFees() {
            axios.post('/nova-vendor/calender/reservation/cancel', {
              reservation_id: this.reservation.id,
              cancellation_fees: this.cancellationFees,
              no_show_fees: this.noShowFees
            })
            .then(response => {
              this.$refs.feesModal.close();
              this.getReservation();
              this.$toasted.show(this.__('Cancellation fees added successfully'), {type: 'success'});
            })
            .catch(error => {
              this.$toasted.show(this.__('An error occurred while adding cancellation fees'), {type: 'error'});
            });
          },
          getUserInfo(){
                axios.get('/nova-vendor/settings/getUserObject')
                    .then((res) => {
                        if(res.data.digital_signature) {
                            axios.post('/signature/uncompress', {
                                signature: res.data.digital_signature.signature_base64,
                            }).then(response => {
                                this.user.digital_signature = response.data.signature;
                            }).catch(error => {
                                console.error('Error uncompressing signature:', error);
                            });
                        }
                    })
          }
      },
      mounted() {
        this.getUserInfo();
        this.user_has_permission_to_cancel_reservation_before_checkin = Nova.app.$hasPermission('cancel reservation before checkin');
        this.user_has_permission_to_cancel_reservation_after_checkin_before_night_run = Nova.app.$hasPermission('cancel reservation after checkin');
        this.user_has_permission_to_cancel_reservation_after_checkin_after_night_run = Nova.app.$hasPermission('cancel reservation before night run');
        this.today_date = moment().format('YYYY-MM-DD');


        this.cancel_reservation_after_checkin_after_revenue = Nova.app.$hasPermission('cancel reservation before night run');
        this.cancel_reservation_after_checkin_before_revenue = Nova.app.$hasPermission('cancel reservation after checkin');
        this.cancel_reservation_before_checkin = Nova.app.$hasPermission('cancel reservation before checkin');
            this.getReservation();
            this.loaded = true;
            this.local = Nova.config.local;
            Nova.$on('update' , ()=>{
                this.activateLoaderInReservation = false ;
                this.getReservation();
            });

            Nova.$on('set-invoice-calendar' , ()=>{
                this.getReservation();
            });


            Nova.$on('update-reservation' , () => {
                setTimeout(() => {
                    this.refreshReservation();
                }, 50);
            })

            Nova.$on('pusher-reservation-created' , (e) => {
                this.$route.params.id = e.id;
                this.getReservation();
            })

            Nova.$on('get-reservation' , (id) => {
                this.$route.params.id = id;
                this.getReservation();
            })


            Nova.$on('service-transaction-deleted' , () => {
              this.getReservation();

            })
            Nova.$on('note-on-reservation-updated' , () => {
              this.getReservation();
            });

            Nova.$on('note-on-reservation-deleted' , () => {
              this.getReservation();
            });


            Nova.$on('link-reservations-process' , () => {
              this.getReservation();
            })

            Nova.$on('re-call-reservation-with-id' , () => {
                 this.getReservation();
            })

            Nova.$on('refresh-reservation' , () => {
                this.refreshReservation()
            })

            Nova.$on('group-invoice-added' , () => {
                this.getReservation();
            })

            Nova.$on('group-invoice-credit-note-added' , () => {
                this.getReservation();
            })

            Nova.$on('reload-grp-reservation' , () => {
                this.getReservation();
            })


          if(this.$route.query.occ){
            this.occ = this.$route.query.occ;
            this.lastCheckedInReservation(this.$route.params.id);
          }

          Nova.$on('reservation_converted_to_group' , () => {
                this.activateLoaderInReservation = false ;
                this.getReservation();
            });
          // Nova.$on('invoice-added' , ()=>{
          //   this.getReservation();
          // });

          this.checkSmsIntegration();


          Nova.$on('set-group-balance' , (balance) => {
               this.reservation.group_balance = balance;
          });

          Nova.$on('close-toggle-group-buttons' , () => {
            this.showActions = false;
          })
      },
      destroyed() {
          Nova.$off('quick-open-cash-reciept-modal');
          Nova.$off('quick-open-payment-voucher-modal');
          Nova.$off('quick-open-service-transaction-modal');
          Nova.$off('add-service-transaction-error');
          Nova.$off('quick-open-checked-in-modal');
          Nova.$off('quick-open-checked-out-modal');
          Nova.$off('quick-open-edit-reservation-modal');
          Nova.$off('quick-open-cancel-reservation-modal');
      },
      watch: {
        amount_to_charge(newVal, oldVal) {
          if(newVal <= 0){
            // this.$toasted.show(this.__('Amount to charge is not valid'), {type: 'error'});
            // this.amount_to_charge = this.reservation.wallet.decimal_places == 3 ?  (-1 * this.reservation.balance / 1000 ) : (-1 * this.reservation.balance / 100 );
          }else{
            this.amount_to_charge = newVal;
          }

          var data = {
            id : this.reservation.id,
            n : newVal,
            l : this.local
          };

          var base64cat = btoa(JSON.stringify(data));
          this.payment_link = `${window.APP_URL}/reservation/collect-payments?data=${base64cat}`;
          // this.payment_link = `${window.APP_URL}/reservation/collect-payments?id=${this.reservation.id}&n=${newVal}&l=${this.local}`;
          this.whats_app_payment_link = this.sms_text_label + ' ' + encodeURIComponent(this.payment_link);
        }
      }
  }
</script>

<style lang="scss">

 .slice {
         width: 100%;
    display: contents;
 }
 .actions {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    width: 100%;
    button {
        display: block;
        height: 25px;
        width: 25px;
        border-radius: 5px;
        background-position: center center;
        background-size: 14px;
        background-repeat: no-repeat;
        margin: 0 5px 0 0;
        cursor: pointer;
      &.edit_button {
        background-image: url("data:image/svg+xml,%3C%3Fxml version='1.0'%3F%3E%3Csvg xmlns='http://www.w3.org/2000/svg' height='512px' viewBox='0 -1 401.52289 401' width='512px'%3E%3Cg%3E%3Cpath d='m370.589844 250.972656c-5.523438 0-10 4.476563-10 10v88.789063c-.019532 16.5625-13.4375 29.984375-30 30h-280.589844c-16.5625-.015625-29.980469-13.4375-30-30v-260.589844c.019531-16.558594 13.4375-29.980469 30-30h88.789062c5.523438 0 10-4.476563 10-10 0-5.519531-4.476562-10-10-10h-88.789062c-27.601562.03125-49.96875 22.398437-50 50v260.59375c.03125 27.601563 22.398438 49.96875 50 50h280.589844c27.601562-.03125 49.96875-22.398437 50-50v-88.792969c0-5.523437-4.476563-10-10-10zm0 0' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%23FFFFFF'/%3E%3Cpath d='m376.628906 13.441406c-17.574218-17.574218-46.066406-17.574218-63.640625 0l-178.40625 178.40625c-1.222656 1.222656-2.105469 2.738282-2.566406 4.402344l-23.460937 84.699219c-.964844 3.472656.015624 7.191406 2.5625 9.742187 2.550781 2.546875 6.269531 3.527344 9.742187 2.566406l84.699219-23.464843c1.664062-.460938 3.179687-1.34375 4.402344-2.566407l178.402343-178.410156c17.546875-17.585937 17.546875-46.054687 0-63.640625zm-220.257812 184.90625 146.011718-146.015625 47.089844 47.089844-146.015625 146.015625zm-9.40625 18.875 37.621094 37.625-52.039063 14.417969zm227.257812-142.546875-10.605468 10.605469-47.09375-47.09375 10.609374-10.605469c9.761719-9.761719 25.589844-9.761719 35.351563 0l11.738281 11.734375c9.746094 9.773438 9.746094 25.589844 0 35.359375zm0 0' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%23FFFFFF'/%3E%3C/g%3E%3C/svg%3E%0A");
        background-color: #45668e;
        &:hover {background-color: #36577F;}
      } /* trash_button */
      &.trash_button {
        background-image: url("data:image/svg+xml,%3C%3Fxml version='1.0'%3F%3E%3Csvg xmlns='http://www.w3.org/2000/svg' height='512px' viewBox='-40 0 427 427.00131' width='512px'%3E%3Cg%3E%3Cpath d='m232.398438 154.703125c-5.523438 0-10 4.476563-10 10v189c0 5.519531 4.476562 10 10 10 5.523437 0 10-4.480469 10-10v-189c0-5.523437-4.476563-10-10-10zm0 0' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%23FFFFFF'/%3E%3Cpath d='m114.398438 154.703125c-5.523438 0-10 4.476563-10 10v189c0 5.519531 4.476562 10 10 10 5.523437 0 10-4.480469 10-10v-189c0-5.523437-4.476563-10-10-10zm0 0' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%23FFFFFF'/%3E%3Cpath d='m28.398438 127.121094v246.378906c0 14.5625 5.339843 28.238281 14.667968 38.050781 9.285156 9.839844 22.207032 15.425781 35.730469 15.449219h189.203125c13.527344-.023438 26.449219-5.609375 35.730469-15.449219 9.328125-9.8125 14.667969-23.488281 14.667969-38.050781v-246.378906c18.542968-4.921875 30.558593-22.835938 28.078124-41.863282-2.484374-19.023437-18.691406-33.253906-37.878906-33.257812h-51.199218v-12.5c.058593-10.511719-4.097657-20.605469-11.539063-28.03125-7.441406-7.421875-17.550781-11.554687-28.0625-11.46875h-88.796875c-10.511719-.0859375-20.621094 4.046875-28.0625 11.46875-7.441406 7.425781-11.597656 17.519531-11.539062 28.03125v12.5h-51.199219c-19.1875.003906-35.394531 14.234375-37.878907 33.257812-2.480468 19.027344 9.535157 36.941407 28.078126 41.863282zm239.601562 279.878906h-189.203125c-17.097656 0-30.398437-14.6875-30.398437-33.5v-245.5h250v245.5c0 18.8125-13.300782 33.5-30.398438 33.5zm-158.601562-367.5c-.066407-5.207031 1.980468-10.21875 5.675781-13.894531 3.691406-3.675781 8.714843-5.695313 13.925781-5.605469h88.796875c5.210937-.089844 10.234375 1.929688 13.925781 5.605469 3.695313 3.671875 5.742188 8.6875 5.675782 13.894531v12.5h-128zm-71.199219 32.5h270.398437c9.941406 0 18 8.058594 18 18s-8.058594 18-18 18h-270.398437c-9.941407 0-18-8.058594-18-18s8.058593-18 18-18zm0 0' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%23FFFFFF'/%3E%3Cpath d='m173.398438 154.703125c-5.523438 0-10 4.476563-10 10v189c0 5.519531 4.476562 10 10 10 5.523437 0 10-4.480469 10-10v-189c0-5.523437-4.476563-10-10-10zm0 0' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%23FFFFFF'/%3E%3C/g%3E%3C/svg%3E%0A");
        background-color: #222222;
        &:hover {background-color: #333333;}
      } /* trash_button */
    } /* button */
  } /* share_button_reservation */

  .cursor-pointer {
      cursor: pointer;
  }
  .checkin_checkout {
    .reservation_checked {
      border-radius: 5px;
      overflow: hidden;
      color: #fff;
      margin: 10px auto;
      display: table;
      width: 100%;
      border: 1px solid transparent;
      span {
        display: table-cell;
        height: 100%;
        width: 50px;
        text-align: center;
        vertical-align: middle;
        background-position: center center;
        background-repeat: no-repeat;
        background-size: 30px;
      } /* span */
      p {
        display: table-cell;
        font-family: Dubai-Regular;
        font-size: 16px;
        padding: 5px;
        vertical-align: middle;
        line-height: 23px;
        text-align: center;
        @media (min-width: 320px) and (max-width: 480px) {
          font-size: 15px;
          line-height: 20px;
        }
        i {
          display: block;
          font-style: normal;
          margin: 0 auto;
          direction: ltr;
        } /* i */
      } /* p */
      &.checkedin {
        color: #004085;
        background-color: #cce5ff;
        border-color: #b8daff;
        span {
          background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' xmlns:svgjs='http://svgjs.com/svgjs' version='1.1' width='512' height='512' x='0' y='0' viewBox='0 0 512 512' style='enable-background:new 0 0 512 512' xml:space='preserve' class=''%3E%3Cg%3E%3Cpath xmlns='http://www.w3.org/2000/svg' d='m218.667969 240h-202.667969c-8.832031 0-16-7.167969-16-16s7.167969-16 16-16h202.667969c8.832031 0 16 7.167969 16 16s-7.167969 16-16 16zm0 0' fill='%23004085' data-original='%23000000' style='' class=''/%3E%3Cpath xmlns='http://www.w3.org/2000/svg' d='m138.667969 320c-4.097657 0-8.191407-1.558594-11.308594-4.691406-6.25-6.253906-6.25-16.386719 0-22.636719l68.695313-68.691406-68.695313-68.671875c-6.25-6.253906-6.25-16.386719 0-22.636719s16.382813-6.25 22.636719 0l80 80c6.25 6.25 6.25 16.382813 0 22.636719l-80 80c-3.136719 3.132812-7.234375 4.691406-11.328125 4.691406zm0 0' fill='%23004085' data-original='%23000000' style='' class=''/%3E%3Cpath xmlns='http://www.w3.org/2000/svg' d='m341.332031 512c-23.53125 0-42.664062-19.136719-42.664062-42.667969v-384c0-18.238281 11.605469-34.515625 28.882812-40.511719l128.171875-42.730468c28.671875-8.789063 56.277344 12.480468 56.277344 40.578125v384c0 18.21875-11.605469 34.472656-28.863281 40.488281l-128.214844 42.753906c-4.671875 1.449219-9 2.089844-13.589844 2.089844zm128-480c-1.386719 0-2.558593.171875-3.816406.554688l-127.636719 42.558593c-4.183594 1.453125-7.210937 5.675781-7.210937 10.21875v384c0 7.277344 7.890625 12.183594 14.484375 10.113281l127.636718-42.558593c4.160157-1.453125 7.210938-5.675781 7.210938-10.21875v-384c0-5.867188-4.777344-10.667969-10.667969-10.667969zm0 0' fill='%23004085' data-original='%23000000' style='' class=''/%3E%3Cpath xmlns='http://www.w3.org/2000/svg' d='m186.667969 106.667969c-8.832031 0-16-7.167969-16-16v-32c0-32.363281 26.300781-58.667969 58.664062-58.667969h240c8.832031 0 16 7.167969 16 16s-7.167969 16-16 16h-240c-14.699219 0-26.664062 11.96875-26.664062 26.667969v32c0 8.832031-7.167969 16-16 16zm0 0' fill='%23004085' data-original='%23000000' style='' class=''/%3E%3Cpath xmlns='http://www.w3.org/2000/svg' d='m314.667969 448h-85.335938c-32.363281 0-58.664062-26.304688-58.664062-58.667969v-32c0-8.832031 7.167969-16 16-16s16 7.167969 16 16v32c0 14.699219 11.964843 26.667969 26.664062 26.667969h85.335938c8.832031 0 16 7.167969 16 16s-7.167969 16-16 16zm0 0' fill='%23004085' data-original='%23000000' style='' class=''/%3E%3C/g%3E%3C/svg%3E");
          background-color: #b8daff;
        } /* span */
      } /* checkedin */
      &.checkedout {
        color: #721c24;
        background-color: #f8d7da;
        border-color: #f5c6cb;
        span {
          background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' xmlns:svgjs='http://svgjs.com/svgjs' version='1.1' width='512' height='512' x='0' y='0' viewBox='0 0 512.016 512' style='enable-background:new 0 0 512 512' xml:space='preserve' class=''%3E%3Cg%3E%3Cpath xmlns='http://www.w3.org/2000/svg' d='m496 240.007812h-202.667969c-8.832031 0-16-7.167968-16-16 0-8.832031 7.167969-16 16-16h202.667969c8.832031 0 16 7.167969 16 16 0 8.832032-7.167969 16-16 16zm0 0' fill='%23721c24' data-original='%23000000' style='' class=''/%3E%3Cpath xmlns='http://www.w3.org/2000/svg' d='m416 320.007812c-4.097656 0-8.191406-1.558593-11.308594-4.691406-6.25-6.253906-6.25-16.386718 0-22.636718l68.695313-68.691407-68.695313-68.695312c-6.25-6.25-6.25-16.382813 0-22.632813 6.253906-6.253906 16.386719-6.253906 22.636719 0l80 80c6.25 6.25 6.25 16.382813 0 22.632813l-80 80c-3.136719 3.15625-7.230469 4.714843-11.328125 4.714843zm0 0' fill='%23721c24' data-original='%23000000' style='' class=''/%3E%3Cpath xmlns='http://www.w3.org/2000/svg' d='m170.667969 512.007812c-4.566407 0-8.898438-.640624-13.226563-1.984374l-128.386718-42.773438c-17.46875-6.101562-29.054688-22.378906-29.054688-40.574219v-384c0-23.53125 19.136719-42.6679685 42.667969-42.6679685 4.5625 0 8.894531.6406255 13.226562 1.9843755l128.382813 42.773437c17.472656 6.101563 29.054687 22.378906 29.054687 40.574219v384c0 23.53125-19.132812 42.667968-42.664062 42.667968zm-128-480c-5.867188 0-10.667969 4.800782-10.667969 10.667969v384c0 4.542969 3.050781 8.765625 7.402344 10.28125l127.785156 42.582031c.917969.296876 2.113281.46875 3.480469.46875 5.867187 0 10.664062-4.800781 10.664062-10.667968v-384c0-4.542969-3.050781-8.765625-7.402343-10.28125l-127.785157-42.582032c-.917969-.296874-2.113281-.46875-3.476562-.46875zm0 0' fill='%23721c24' data-original='%23000000' style='' class=''/%3E%3Cpath xmlns='http://www.w3.org/2000/svg' d='m325.332031 170.675781c-8.832031 0-16-7.167969-16-16v-96c0-14.699219-11.964843-26.667969-26.664062-26.667969h-240c-8.832031 0-16-7.167968-16-16 0-8.832031 7.167969-15.9999995 16-15.9999995h240c32.363281 0 58.664062 26.3046875 58.664062 58.6679685v96c0 8.832031-7.167969 16-16 16zm0 0' fill='%23721c24' data-original='%23000000' style='' class=''/%3E%3Cpath xmlns='http://www.w3.org/2000/svg' d='m282.667969 448.007812h-85.335938c-8.832031 0-16-7.167968-16-16 0-8.832031 7.167969-16 16-16h85.335938c14.699219 0 26.664062-11.96875 26.664062-26.667968v-96c0-8.832032 7.167969-16 16-16s16 7.167968 16 16v96c0 32.363281-26.300781 58.667968-58.664062 58.667968zm0 0' fill='%23721c24' data-original='%23000000' style='' class=''/%3E%3C/g%3E%3C/svg%3E");
          background-color: #f5c6cb;
        } /* span */
      } /* checkedout */
      &.promissory {
          color: #343333;
          background-color: #dddddd;
          border-color: #dddddd;
        span {

          background-color: #cac6c6;
        } /* span */
      } /* checkedout */
      @media (min-width: 768px) and (max-width: 991px) {
        width: 49%;
        float: right;
      }
    } /* reservation_checked */
  } /* checkin_checkout */

  .transaction_modal {
      .sweet-modal {
          @media (min-width: 768px) and (max-width: 991px) {
              width: 95% !important;
          } /* @media */
      } /* sweet-modal */
      .embed_area {
          max-height: 500px;
          height: 100%;
          overflow-y: auto;
          display: block !important;
          scrollbar-width: thin;
          scrollbar-color: #ccc #f5f5f5;
          &::-webkit-scrollbar {width: 6px;}
          &::-webkit-scrollbar-track {background: #f5f5f5;}
          &::-webkit-scrollbar-thumb {background: #ccc;}
          &::-webkit-scrollbar-thumb:window-inactive {background: #f5f5f5;}
          @media (min-width: 320px) and (max-width: 480px) {
              display: none !important;
          } /* @media */
          iframe {
              width: 100%;
              height: 100%;
              min-height: 500px;
          } /* iframe */
      } /* embed_area */
  } /* transaction_modal */

  .invoice_modal {
    .sweet-modal {
        @media (min-width: 768px) and (max-width: 991px) {
            width: 95% !important;
        } /* @media */
    } /* sweet-modal */
    .embed_area {
      max-height: 500px;
      height: 100%;
      overflow-y: auto;
      display: block !important;
      scrollbar-width: thin;
      scrollbar-color: #ccc #f5f5f5;
      &::-webkit-scrollbar {width: 6px;}
      &::-webkit-scrollbar-track {background: #f5f5f5;}
      &::-webkit-scrollbar-thumb {background: #ccc;}
      &::-webkit-scrollbar-thumb:window-inactive {background: #f5f5f5;}
      @media (min-width: 320px) and (max-width: 480px) {
          display: none !important;
      } /* @media */
      iframe {
          width: 100%;
          height: 100%;
          min-height: 500px;
      } /* iframe */
    } /* embed_area */
  } /* invoice_modal */

  #reservation_page {
    .main_reservation_buttons {
      margin: 20px auto;
      display: flex;
      justify-content: flex-end;
      flex-wrap: wrap;
      @media (min-width: 320px) and (max-width: 480px) {
        justify-content: space-between;
      } /* Mobile */
      div.item_reservation_button {
        @media (min-width: 320px) and (max-width: 480px) {
          width: 50%;
          padding: 0 10px;
        } /* Mobile */
        button {
          @media (min-width: 320px) and (max-width: 480px) {
            width: 100%;
            margin: 5px auto;
          } /* Mobile */
        } /* button */
      } /* item_reservation_button */
      button.main_button {
        margin: 0 0 0 10px;
        height: 35px;
        font-size: 15px;
        color: #fff;
        background: #4099de;
        border-radius: 4px;
        padding: 0 15px;
        min-width: 100px;
        -webkit-transition: all 0.2s ease-in-out;
        -moz-transition: all 0.2s ease-in-out;
        -o-transition: all 0.2s ease-in-out;
        transition: all 0.2s ease-in-out;
        [dir="ltr"] & {
          margin: 0 10px 0 0;
        } /* ltr */
        @media (min-width: 320px) and (max-width: 480px) {
          width: 100%;
          margin: 5px auto;
        } /* Mobile */
        &:hover {
          background: #0071C9;
        } /* hover */
        &.cancel {
          background: #e74444;
          &:hover {
            background: #dd3a3a;
          } /* hover */
        } /* cancel */
      } /* main_button */
    } /* main_reservation_buttons */
    .checkin_checkout {
      display: table;
      width: 100%;
      clear: both;
    } /* checkin_checkout */
    .row_cols {
      display: flex;
      flex-wrap: wrap;
      justify-content: space-between;
      align-items: flex-start;
      margin: 0 -10px;
      .right_col {
        width: 50%;
        @media (min-width: 320px) and (max-width: 767px) {
          width: 100%;
        } /* Mobile */
        .booking_summary {
          margin: 20px auto;
          padding: 0 10px;
          .title {
            display: flex;
            align-items: center;
            justify-content: space-between;
            span {
              display: block;
              font-size: 20px;
            //   color: #000;
            } /* span */
            .Update_Reservation_Button {
              background-image: url("data:image/svg+xml,%3C%3Fxml version='1.0'%3F%3E%3Csvg xmlns='http://www.w3.org/2000/svg' height='512px' viewBox='0 -1 401.52289 401' width='512px' class=''%3E%3Cg%3E%3Cpath d='m370.589844 250.972656c-5.523438 0-10 4.476563-10 10v88.789063c-.019532 16.5625-13.4375 29.984375-30 30h-280.589844c-16.5625-.015625-29.980469-13.4375-30-30v-260.589844c.019531-16.558594 13.4375-29.980469 30-30h88.789062c5.523438 0 10-4.476563 10-10 0-5.519531-4.476562-10-10-10h-88.789062c-27.601562.03125-49.96875 22.398437-50 50v260.59375c.03125 27.601563 22.398438 49.96875 50 50h280.589844c27.601562-.03125 49.96875-22.398437 50-50v-88.792969c0-5.523437-4.476563-10-10-10zm0 0' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%234099DE'/%3E%3Cpath d='m376.628906 13.441406c-17.574218-17.574218-46.066406-17.574218-63.640625 0l-178.40625 178.40625c-1.222656 1.222656-2.105469 2.738282-2.566406 4.402344l-23.460937 84.699219c-.964844 3.472656.015624 7.191406 2.5625 9.742187 2.550781 2.546875 6.269531 3.527344 9.742187 2.566406l84.699219-23.464843c1.664062-.460938 3.179687-1.34375 4.402344-2.566407l178.402343-178.410156c17.546875-17.585937 17.546875-46.054687 0-63.640625zm-220.257812 184.90625 146.011718-146.015625 47.089844 47.089844-146.015625 146.015625zm-9.40625 18.875 37.621094 37.625-52.039063 14.417969zm227.257812-142.546875-10.605468 10.605469-47.09375-47.09375 10.609374-10.605469c9.761719-9.761719 25.589844-9.761719 35.351563 0l11.738281 11.734375c9.746094 9.773438 9.746094 25.589844 0 35.359375zm0 0' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%234099DE'/%3E%3C/g%3E%3C/svg%3E%0A");
              width: 25px;
              height: 25px;
              display: block;
              background-repeat: no-repeat;
              background-position: center center;
              background-size: 20px;
              -webkit-transition: all 0.2s ease-in-out;
              -moz-transition: all 0.2s ease-in-out;
              -o-transition: all 0.2s ease-in-out;
              transition: all 0.2s ease-in-out;
              opacity: 0;
              -webkit-transform: scale(0);
              -moz-transform: scale(0);
              -o-transform: scale(0);
              transform: scale(0);
              @media (min-width: 320px) and (max-width: 767px) {
                opacity: 1;
                -webkit-transform: scale(1);
                -moz-transform: scale(1);
                -o-transform: scale(1);
                transform: scale(1);
              } /* Mobile */
            } /* Update_Reservation_Button */
          } /* title */
          .content {
            width: auto;
            min-width: auto;
            max-width: none;
            background: #ffffff;
            border-radius: 5px;
            margin: 5px auto 0;
            border: 1px solid #ddd;
            padding: 10px;
            box-shadow: 0 2px 4px 0 rgba(0,0,0,.05);
            min-height: 436px;
            ul {
              li {
                display: flex;
                align-items: center;
                justify-content: flex-start;
                flex-wrap: wrap;
                border-bottom: 1px solid #ddd;
                padding: 0 0 10px;
                margin: 0 auto 10px;
                font-size: 15px;
                color: #000;
                .name {
                  width: 20%;
                  color: #666666;
                  [dir="ltr"] & {
                    width: 25%;
                  } /* ltr */
                  @media (min-width: 320px) and (max-width: 480px) {
                    width: 100%;
                    margin: 0 0 5px;
                    [dir="ltr"] & {
                      width: 100%;
                    } /* ltr */
                  } /* Mobile */
                } /* name */
                .col_two {
                  display: flex;
                  align-items: center;
                  justify-content: space-between;
                  width: 80%;
                  [dir="ltr"] & {
                    width: 75%;
                  } /* ltr */
                  @media (min-width: 320px) and (max-width: 480px) {
                    width: 100%;
                    [dir="ltr"] & {
                      width: 100%;
                    } /* ltr */
                  } /* Mobile */
                  .status {
                    display: block;
                    font-size: 14px;
                    font-weight: normal;
                    padding: 0 15px;
                    text-align: center;
                    border-radius: 100px;
                  } /* status */
                } /* col_two */
                .desc {
                  display: flex;
                  align-items: center;
                  justify-content: flex-start;
                  color: #000;
                  width: 80%;
                  [dir="ltr"] & {
                    width: 75%;
                  } /* ltr */
                  @media (min-width: 320px) and (max-width: 480px) {
                    width: 100%;
                    [dir="ltr"] & {
                      width: 100%;
                    } /* ltr */
                  } /* Mobile */
                  svg{
                      margin: 0 10px 0 0;
                      path {
                          fill: #555;
                      }
                  }
                  .from_to {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    margin: 0 -10px;
                    flex-wrap: wrap;
                    width: 100%;
                    .from, .to {
                      width: 50%;
                      margin: 5px 0 0;
                      padding: 0 10px;
                      span {
                        display: block;
                        width: 100%;
                      } /* span */
                      p {
                        display: block;
                        width: 100%;
                      } /* p */
                    } /* from & to */
                  } /* from_to */
                  span {
                    p {
                      display: inline-block;
                    } /* p */
                  } /* span */
                } /* desc */
                .desc2 {
                    display : flex;
                    align-items : center;
                    justify-content: flex-start;
                    color: #000;
                  width: 80%;
                  [dir="ltr"] & {
                    width: 75%;
                  } /* ltr */
                  @media (min-width: 320px) and (max-width: 480px) {
                    width: 100%;
                    [dir="ltr"] & {
                      width: 100%;
                    } /* ltr */
                  } /* Mobile */

                  span {
                      display : flex;
                    align-items : center;
                    justify-content: flex-start;

                    button {
                        outline : none !important;
                    }
                  }
                } /* desc2 */
                .Leasing {
                  color: #000000;
                  width: 80%;
                  display: flex;
                  justify-content: space-between;
                  align-items: center;
                  flex-wrap: wrap;
                  [dir="ltr"] & {
                    width: 75%;
                  } /* ltr */
                  @media (min-width: 320px) and (max-width: 480px) {
                    width: 100%;
                    [dir="ltr"] & {
                      width: 100%;
                    } /* ltr */
                  } /* Mobile */
                } /* Leasing */
                &:last-child {
                  margin: 0;
                  padding: 0;
                  border-bottom: none;
                } /* last-child */
                &.oneline {
                  .name {
                    @media (min-width: 320px) and (max-width: 480px) {
                      width: 30%;
                      margin: 0;
                    } /* Mobile */
                  } /* name */
                  .col_two, .desc, .Leasing {
                    @media (min-width: 320px) and (max-width: 480px) {
                      width: 70%;
                      margin: 0;
                    } /* Mobile */
                  } /* col_two */
                } /* oneline */
              } /* li */
            } /* ul */
          } /* content */
          &:hover {
            .Update_Reservation_Button {
              opacity: 1;
              -webkit-transform: scale(1);
              -moz-transform: scale(1);
              -o-transform: scale(1);
              transform: scale(1);
            } /* Update_Reservation_Button */
          } /* hover */
        } /* booking_summary */
        .financial_statement {
          margin: 20px auto;
          padding: 0 10px;
          .title {
            display: block;
            font-size: 20px;
            color: #000;
          } /* title */
          .content {
            width: auto;
            min-width: auto;
            max-width: none;
            background: #ffffff;
            border-radius: 5px;
            margin: 5px auto 0;
            border: 1px solid #ddd;
            padding: 10px;
            min-height: 296px;
            box-shadow: 0 2px 4px 0 rgba(0,0,0,.05);
            .no_transactions_found {
              min-height: 310px;
              text-align: center;
              display: flex;
              align-items: center;
              justify-content: center;
              flex-wrap: wrap;
              .icon {
                background-image: url("data:image/svg+xml,%3C%3Fxml version='1.0'%3F%3E%3Csvg xmlns='http://www.w3.org/2000/svg' id='Layer_5' enable-background='new 0 0 64 64' height='512px' viewBox='0 0 64 64' width='512px'%3E%3Cg%3E%3Cpath d='m55 53v-47c0-2.757-2.243-5-5-5h-44c-2.757 0-5 2.243-5 5v27h8v25c0 2.757 2.243 5 5 5h44c2.757 0 5-2.243 5-5v-5zm-46-22h-6v-25c0-1.654 1.346-3 3-3s3 1.346 3 3zm8 27c0 1.654-1.346 3-3 3s-3-1.346-3-3v-52c0-1.13-.391-2.162-1.026-3h40.026c1.654 0 3 1.346 3 3v47h-2v-16h-6v16h-2v-12h-6v12h-2v-10h-6v10h-2v-6h-6v6h-4zm32-5h-2v-14h2zm-8 0h-2v-10h2zm-8 0h-2v-8h2zm-8 0h-2v-4h2zm36 5c0 1.654-1.346 3-3 3h-40.002c.629-.836 1.002-1.875 1.002-3v-3h42z' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%23DDDDDD'/%3E%3Cpath d='m17 19c-1.103 0-2-.897-2-2h-2c0 1.859 1.279 3.411 3 3.858v2.142h2v-2h3v-4c0-2.206-1.794-4-4-4-1.103 0-2-.897-2-2v-2h2c1.103 0 2 .897 2 2h2c0-1.859-1.279-3.411-3-3.858v-2.142h-2v2h-3v4c0 2.206 1.794 4 4 4 1.103 0 2 .897 2 2v2z' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%23DDDDDD'/%3E%3Cpath d='m51 5h-28v18h28zm-2 16h-24v-14h24z' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%23DDDDDD'/%3E%3Cpath d='m27 9h2v2h-2z' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%23DDDDDD'/%3E%3Cpath d='m31 9h16v2h-16z' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%23DDDDDD'/%3E%3Cpath d='m27 13h2v2h-2z' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%23DDDDDD'/%3E%3Cpath d='m31 13h16v2h-16z' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%23DDDDDD'/%3E%3Cpath d='m27 17h2v2h-2z' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%23DDDDDD'/%3E%3Cpath d='m31 17h16v2h-16z' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%23DDDDDD'/%3E%3Cpath d='m43.586 31-5.586 5.586-4-4-10.707 10.707 1.414 1.414 9.293-9.293 4 4 7-7 3.542 3.542 2.832-11.33-11.33 2.832zm3.872 1.044-3.502-3.502 4.67-1.168z' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%23DDDDDD'/%3E%3Cpath d='m13 27h2v2h-2z' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%23DDDDDD'/%3E%3Cpath d='m17 27h2v2h-2z' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%23DDDDDD'/%3E%3Cpath d='m21 27h2v2h-2z' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%23DDDDDD'/%3E%3Cpath d='m13 31h10v2h-10z' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%23DDDDDD'/%3E%3Cpath d='m13 35h10v2h-10z' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%23DDDDDD'/%3E%3Cpath d='m57 57h2v2h-2z' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%23DDDDDD'/%3E%3Cpath d='m53 57h2v2h-2z' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%23DDDDDD'/%3E%3Cpath d='m49 57h2v2h-2z' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%23DDDDDD'/%3E%3Cpath d='m5 27h2v2h-2z' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%23DDDDDD'/%3E%3Cpath d='m5 23h2v2h-2z' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%23DDDDDD'/%3E%3Cpath d='m5 19h2v2h-2z' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%23DDDDDD'/%3E%3C/g%3E%3C/svg%3E%0A");
                background-size: 60px;
                width: 62px;
                height: 62px;
                display: block;
                margin: 0 auto;
                background-position: center center;
                background-repeat: no-repeat;
              } /* icon */
              span {
                display: block;
                width: 100%;
                font-size: 16px;
                margin: 7px auto 0;
                color: #dddddd;
              } /* span */
            } /* no_transactions_found */
            .all_financial_items {
              width: 100%;
              min-height: 310px;
            } /* all_financial_items */
            .financial_item {
              display: flex;
              justify-content: space-between;
              align-items: center;
              width: 100%;
              flex-wrap: wrap;
              border: 1px solid #ddd;
              margin: 0 auto 10px;
              border-radius: 5px;
              padding: 5px;
              background: #fdfdfd;
              cursor: pointer;
              -webkit-transition: all 0.2s ease-in-out;
              -moz-transition: all 0.2s ease-in-out;
              -o-transition: all 0.2s ease-in-out;
              transition: all 0.2s ease-in-out;
              &:hover {
                background: #f8f8f8;
                border-color: #d8d8d8;
              } /* hover */
              .col_right {
                width: 70%;
                @media (min-width: 320px) and (max-width: 767px) {
                  width: 50%;
                } /* Mobile */
                span {
                  display: block;
                  font-size: 15px;
                  color: #666666;
                  p {
                    display: inline-block;
                    color: #000000;
                    &.text-success {
                      color: #155724;
                    } /* text-success */
                    &.text-danger {
                      color: #721c24;
                    } /* text-danger */
                  } /* p */
                } /* span */
              } /* col_right */
              .col_left {
                width: 30%;
                display: flex;
                flex-wrap: wrap;
                justify-content: space-between;
                align-self: stretch;
                @media (min-width: 320px) and (max-width: 767px) {
                  width: 50%;
                } /* Mobile */
                time {
                  display: flex;
                  justify-content: flex-end;
                  width: 100%;
                  font-size: 13px;
                  color: #777777;
                } /* time */
                .name {
                  display: flex;
                  align-self: flex-end;
                  justify-content: flex-end;
                  width: 100%;
                  label {
                    display: block;
                    border-radius: 100px;
                    padding: 0 15px;
                    min-width: 60px;
                    font-size: 14px;
                    height: 20px;
                    line-height: 18px;
                    font-weight: normal;
                    border-width: 1px;
                    border-style: solid;
                    &.text-success {
                      color: #155724;
                      background-color: #d4edda;
                      border-color: #c3e6cb;
                    } /* text-success */
                    &.text-danger {
                      color: #721c24;
                      background-color: #f8d7da;
                      border-color: #f5c6cb;
                    } /* text-danger */
                  } /* label */
                  p {
                        border-radius: 100px;
                        border: 1px solid #dcd56d;
                        padding: 0 10px;
                        min-width: 50px;
                        font-size: 14px;
                        height: 20px;
                        color: #676523;
                        margin: 0 0 0 5px;
                        display: -webkit-box;
                        display: -ms-flexbox;
                        display: flex;
                        -webkit-box-align: center;
                        -ms-flex-align: center;
                        align-items: center;
                        -webkit-box-pack: center;
                        -ms-flex-pack: center;
                        justify-content: center;
                        background: #fff56d;
                    } /* p */
                } /* name */
              } /* col_left */
            } /* financial_item */
            .more_transactions {
              background-image: url("data:image/svg+xml,%3C%3Fxml version='1.0'%3F%3E%3Csvg xmlns='http://www.w3.org/2000/svg' id='Capa_1' enable-background='new 0 0 515.555 515.555' height='512px' viewBox='0 0 515.555 515.555' width='512px' class=''%3E%3Cg%3E%3Cpath d='m496.679 212.208c25.167 25.167 25.167 65.971 0 91.138s-65.971 25.167-91.138 0-25.167-65.971 0-91.138 65.971-25.167 91.138 0' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%23666666'/%3E%3Cpath d='m303.347 212.208c25.167 25.167 25.167 65.971 0 91.138s-65.971 25.167-91.138 0-25.167-65.971 0-91.138 65.971-25.167 91.138 0' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%23666666'/%3E%3Cpath d='m110.014 212.208c25.167 25.167 25.167 65.971 0 91.138s-65.971 25.167-91.138 0-25.167-65.971 0-91.138 65.971-25.167 91.138 0' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%23666666'/%3E%3C/g%3E%3C/svg%3E%0A");
              background-color: #fafafa;
              border: 1px solid #ddd;
              border-radius: 5px;
              height: 30px;
              margin: 0 auto 10px;
              background-size: 30px;
              background-repeat: no-repeat;
              background-position: center center;
              cursor: pointer;
              &:hover {
                background-color: #f5f5f5;
                border-color: #d8d8d8;
              } /* hover */
            } /* more_transactions */
            .block_bottom {
              display: flex;
              justify-content: space-between;
              align-items: center;
              flex-wrap: wrap;
              width: 100%;
              .add_payments{
                display: flex;
                button.cash_receipt_button, button.payment_voucher_button {
                  background: #4099de;
                  border-radius: 5px;
                  border: 1px solid #4099de;
                  min-width: 100px;
                  height: 35px;
                  line-height: 35px;
                  font-size: 15px;
                  margin: 0 0 0 10px;
                  padding: 0 15px;
                  color: #ffffff;
                  cursor: pointer;
                  -webkit-transition: all 0.2s ease-in-out;
                  -moz-transition: all 0.2s ease-in-out;
                  -o-transition: all 0.2s ease-in-out;
                  transition: all 0.2s ease-in-out;
                  @media (min-width: 320px) and (max-width: 767px) {
                    min-width: auto;
                  } /* Mobile */
                  &:hover {
                    background: #0071C9;
                    border-color: #0071C9;
                  } /* hover */
                } /* button */
              } /* add_payments */
              .more {
                button {
                  background: #ffffff;
                  border-radius: 5px;
                  border: 1px solid #4099de;
                  min-width: 100px;
                  height: 35px;
                  line-height: 35px;
                  font-size: 15px;
                  color: #4099de;
                  padding: 0 15px;
                  -webkit-transition: all 0.2s ease-in-out;
                  -moz-transition: all 0.2s ease-in-out;
                  -o-transition: all 0.2s ease-in-out;
                  transition: all 0.2s ease-in-out;
                  @media (min-width: 320px) and (max-width: 767px) {
                    min-width: auto;
                  } /* Mobile */
                  &:hover {
                    background: #4099de;
                    color: #ffffff;
                  } /* hover */
                } /* button */
              } /* more */
            } /* block_bottom */
          } /* content */
        } /* financial_statement */
      } /* right_col */
      .left_col {
        width: 50%;
        @media (min-width: 320px) and (max-width: 767px) {
          width: 100%;
        } /* Mobile */
        .customer_information {
          margin: 20px auto;
          padding: 0 10px;
          .title {
            display: flex;
            align-items: center;
            justify-content: space-between;
            span.spn {
              display: block;
              font-size: 20px;
            //   color: #000;
            } /* span */
            .Update_Customer_Button {
              background-image: url("data:image/svg+xml,%3C%3Fxml version='1.0'%3F%3E%3Csvg xmlns='http://www.w3.org/2000/svg' height='512px' viewBox='0 -1 401.52289 401' width='512px' class=''%3E%3Cg%3E%3Cpath d='m370.589844 250.972656c-5.523438 0-10 4.476563-10 10v88.789063c-.019532 16.5625-13.4375 29.984375-30 30h-280.589844c-16.5625-.015625-29.980469-13.4375-30-30v-260.589844c.019531-16.558594 13.4375-29.980469 30-30h88.789062c5.523438 0 10-4.476563 10-10 0-5.519531-4.476562-10-10-10h-88.789062c-27.601562.03125-49.96875 22.398437-50 50v260.59375c.03125 27.601563 22.398438 49.96875 50 50h280.589844c27.601562-.03125 49.96875-22.398437 50-50v-88.792969c0-5.523437-4.476563-10-10-10zm0 0' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%234099DE'/%3E%3Cpath d='m376.628906 13.441406c-17.574218-17.574218-46.066406-17.574218-63.640625 0l-178.40625 178.40625c-1.222656 1.222656-2.105469 2.738282-2.566406 4.402344l-23.460937 84.699219c-.964844 3.472656.015624 7.191406 2.5625 9.742187 2.550781 2.546875 6.269531 3.527344 9.742187 2.566406l84.699219-23.464843c1.664062-.460938 3.179687-1.34375 4.402344-2.566407l178.402343-178.410156c17.546875-17.585937 17.546875-46.054687 0-63.640625zm-220.257812 184.90625 146.011718-146.015625 47.089844 47.089844-146.015625 146.015625zm-9.40625 18.875 37.621094 37.625-52.039063 14.417969zm227.257812-142.546875-10.605468 10.605469-47.09375-47.09375 10.609374-10.605469c9.761719-9.761719 25.589844-9.761719 35.351563 0l11.738281 11.734375c9.746094 9.773438 9.746094 25.589844 0 35.359375zm0 0' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%234099DE'/%3E%3C/g%3E%3C/svg%3E%0A");
              width: 25px;
              height: 25px;
              display: block;
              background-repeat: no-repeat;
              background-position: center center;
              background-size: 20px;
              -webkit-transition: all 0.2s ease-in-out;
              -moz-transition: all 0.2s ease-in-out;
              -o-transition: all 0.2s ease-in-out;
              transition: all 0.2s ease-in-out;
              opacity: 0;
              -webkit-transform: scale(0);
              -moz-transform: scale(0);
              -o-transform: scale(0);
              transform: scale(0);
              @media (min-width: 320px) and (max-width: 767px) {
                opacity: 1;
                -webkit-transform: scale(1);
                -moz-transform: scale(1);
                -o-transform: scale(1);
                transform: scale(1);
              } /* Mobile */
            } /* Update_Customer_Button */
          } /* title */
          .content {
            width: auto;
            min-width: auto;
            max-width: none;
            background: #ffffff;
            border-radius: 5px;
            margin: 5px auto 0;
            border: 1px solid #ddd;
            min-height: 397px;
            padding: 10px;
            box-shadow: 0 2px 4px 0 rgba(0,0,0,.05);
            display: flex;
            flex-wrap: wrap;
            align-items: stretch;
            ul {
              li {
                display: flex;
                align-items: center;
                justify-content: flex-start;
                flex-wrap: wrap;
                border-bottom: 1px solid #ddd;
                padding: 0 0 10px;
                margin: 0 auto 10px;
                font-size: 15px;
                color: #000;
                .name {
                  width: 20%;
                  color: #666666;
                  [dir="ltr"] & {
                    width: 25%;
                  } /* ltr */
                  @media (min-width: 320px) and (max-width: 480px) {
                    width: 35%;
                    margin: 0;
                    [dir="ltr"] & {
                      width: 35%;
                    } /* ltr */
                  } /* Mobile */
                } /* name */
                .col_two {
                  display: flex;
                  align-items: center;
                  justify-content: space-between;
                  width: 80%;
                  [dir="ltr"] & {
                    width: 75%;
                  } /* ltr */
                  @media (min-width: 320px) and (max-width: 480px) {
                    width: 65%;
                    [dir="ltr"] & {
                      width: 65%;
                    } /* ltr */
                  } /* Mobile */
                  .status {
                    display: block;
                    font-size: 14px;
                    font-weight: normal;
                    padding: 0 15px;
                    text-align: center;
                    border-radius: 100px;
                  } /* status */
                  label {
                    display: block;
                    min-width: 70px;
                    border-radius: 100px;
                    border-width: 1px;
                    font-size: 14px;
                    text-align: center;
                    padding: 0 15px;
                    border-style: solid;
                    color: #000000;
                  } /* label */
                } /* col_two */
                .desc {
                  display: block;
                  color: #000;
                  width: 80%;
                  [dir="ltr"] & {
                    width: 75%;
                  } /* ltr */
                  @media (min-width: 320px) and (max-width: 480px) {
                    width: 65%;
                    [dir="ltr"] & {
                      width: 65%;
                    } /* ltr */
                  } /* Mobile */
                } /* desc */
                // .phone {
                //   display: block;
                //   width: 80%;
                //   color: #000;
                //   direction: ltr;
                //   [dir="ltr"] & {
                //     width: 75%;
                //   } /* ltr */
                //   @media (min-width: 320px) and (max-width: 480px) {
                //     width: 65%;
                //     [dir="ltr"] & {
                //       width: 65%;
                //     } /* ltr */
                //   } /* Mobile */
                // } /* phone */
              } /* li */
            } /* ul */
            .customerNotes {
              span {
                margin: 0 auto 15px;
                border-radius: 5px;
                padding: 10px;
                text-align: center;
                color: #b7791f;
                border: 1px solid #fbd38d;
                background: #fffaf0;
                font-size: 15px;
                cursor: pointer;
                display: block;
                -webkit-transition: all 0.2s ease-in-out;
                -moz-transition: all 0.2s ease-in-out;
                -o-transition: all 0.2s ease-in-out;
                transition: all 0.2s ease-in-out;
                &:hover {
                  background: #faf5eb;
                  border-color: #f6ce88;
                  color: #b2741a;
                } /* hover */
              } /* span */
            } /* customerNotes */
            .block_top {
              width: 100%;
              min-height: 379px;
            } /* block_top */
            .block_bottom {
              display: flex;
              justify-content: space-between;
              align-items: center;
              flex-wrap: wrap;
              width: 100%;
              align-self: end;
              button.more {
                background: #ffffff;
                border-radius: 5px;
                border: 1px solid #4099de;
                min-width: 100px;
                height: 35px;
                line-height: 35px;
                font-size: 15px;
                color: #4099de;
                padding: 0 15px;
                -webkit-transition: all 0.2s ease-in-out;
                -moz-transition: all 0.2s ease-in-out;
                -o-transition: all 0.2s ease-in-out;
                transition: all 0.2s ease-in-out;
                &:hover {
                  background: #4099de;
                  color: #ffffff;
                } /* hover */
              } /* more */
              button.sendMailMsg {
                background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' height='512' width='512' fill='%23459bdc'%3E%3Cpath d='M506.955 1.314a10 10 0 0 0-10.045.078L313.656 109.756a10 10 0 0 0-3.518 13.697c2.8 4.753 8.942 6.328 13.697 3.518l131.482-77.75L210.4 303.335 88.603 266.07l158.965-94c4.754-2.812 6.33-8.944 3.518-13.698a10 10 0 0 0-13.697-3.518L58.9 260.392a10 10 0 0 0 2.164 18.171l145.47 44.504L270.72 439.88a2.44 2.44 0 0 0 .207.314c1.07 1.786 2.676 3.245 4.678 4.087a9.99 9.99 0 0 0 3.878.784c2.563 0 5.086-.986 7-2.85l73.794-72.12 138.806 42.466c.96.293 1.945.438 2.925.438A10 10 0 0 0 512 403V10a10 10 0 0 0-5.045-8.686zm-235.7 327.916a10.01 10.01 0 0 0-1.78 5.694v61.17l-43.823-79.765 193.92-201.2-148.32 214.1zm18.22 82.08v-62.867l49 14.988-49 47.88zM492 389.483l-196.5-60.116L492 45.704v343.78zm-327.577-41.906c-3.906-3.905-10.236-3.905-14.143 0L56.928 440.93c-3.905 3.905-3.905 10.237 0 14.143C58.882 457.024 61.44 458 64 458a9.96 9.96 0 0 0 7.07-2.93l93.352-93.352c3.905-3.904 3.905-10.236 0-14.142zM40.07 471.928c-3.906-3.903-10.236-3.903-14.142.001l-23 23c-3.905 3.905-3.905 10.237 0 14.143A9.97 9.97 0 0 0 10 512a9.97 9.97 0 0 0 7.071-2.929l23-23c3.905-3.905 3.905-10.237 0-14.143zm102.58 22.412c-1.86-1.86-4.44-2.93-7.07-2.93-2.64 0-5.2 1.07-7.07 2.93a10.06 10.06 0 0 0-2.93 7.07c0 2.63 1.07 5.2 2.93 7.07s4.44 2.93 7.07 2.93 5.2-1.07 7.07-2.93a10.08 10.08 0 0 0 2.931-7.07c0-2.64-1.07-5.2-2.93-7.07zm74.4-74.405c-3.903-3.905-10.233-3.905-14.142 0l-49.446 49.445c-3.905 3.905-3.905 10.237 0 14.142a9.97 9.97 0 0 0 14.141.001l49.446-49.445c3.905-3.905 3.905-10.237 0-14.142zm170.654-3.795c-3.906-3.904-10.236-3.904-14.142 0l-49.58 49.58c-3.905 3.905-3.905 10.237 0 14.143a9.97 9.97 0 0 0 14.142 0l49.58-49.58c3.905-3.905 3.905-10.237 0-14.143zM283.5 136.3c-1.86-1.86-4.44-2.93-7.07-2.93s-5.2 1.07-7.07 2.93a10.04 10.04 0 0 0-2.93 7.08c0 2.63 1.07 5.2 2.93 7.06 1.86 1.87 4.44 2.93 7.07 2.93a10.05 10.05 0 0 0 7.07-2.93c1.86-1.86 2.93-4.43 2.93-7.06 0-2.64-1.07-5.22-2.93-7.08z'/%3E%3C/svg%3E");
                background-repeat: no-repeat;
                background-position: center right;
                background-size: 20px;
                color: #4099de;
                font-size: 15px;
                padding: 0 25px 0 0;
                [dir="ltr"] & {
                  background-position: center left;
                  padding: 0 0 0 25px;
                } /* ltr */
                &:hover {
                  color: #0071C9;
                } /* hover */
              } /* sendMailMsg */
            } /* block_bottom */
          } /* content */
          &:hover {
            .Update_Customer_Button {
              opacity: 1;
              -webkit-transform: scale(1);
              -moz-transform: scale(1);
              -o-transform: scale(1);
              transform: scale(1);
            } /* Update_Customer_Button */
          } /* hover */
        } /* customer_information */
        .payment_links_block {
          margin: 20px auto;
          padding: 0 10px;
          .title {
            display: flex;
            align-items: center;
            justify-content: space-between;
            display: block;
            font-size: 20px;
            color: #000;
          } /* title */
          .content {
            width: auto;
            min-width: auto;
            max-width: none;
            background: #ffffff;
            border-radius: 5px;
            margin: 5px auto 0;
            border: 1px solid #ddd;
            padding: 10px;
            box-shadow: 0 2px 4px 0 rgba(0,0,0,.05);
            display: flex;
            flex-wrap: wrap;
            align-items: stretch;
            min-height: 296px;
            .no_payment_links {
              min-height: 240px;
              width: 100%;
              text-align: center;
              display: flex;
              align-items: center;
              justify-content: center;
              flex-wrap: wrap;
              flex-direction: column;
              .icon {
                background-image: url("data:image/svg+xml,%3C%3Fxml version='1.0'%3F%3E%3Csvg xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' version='1.1' id='Capa_1' x='0px' y='0px' viewBox='0 0 512 512' style='enable-background:new 0 0 512 512;' xml:space='preserve' width='512px' height='512px'%3E%3Cg%3E%3Cg%3E%3Cg%3E%3Cpath d='M474.994,246.001h-74.012v-236c0-4.044-2.437-7.691-6.173-9.239c-3.738-1.549-8.039-0.691-10.898,2.167l-28.329,28.328 L327.255,2.93c-3.905-3.905-10.237-3.905-14.143,0l-28.328,28.328L256.459,2.93c-3.905-3.905-10.237-3.905-14.143,0 l-28.328,28.328L185.659,2.93c-3.905-3.905-10.237-3.905-14.143,0l-28.328,28.328L114.863,2.93 c-3.905-3.905-10.237-3.905-14.143,0L72.392,31.258L44.065,2.93c-3.881-3.881-10.158-3.901-14.073-0.055 c-2.056,2.021-3.053,4.714-2.984,7.389v449.738c0,28.672,23.326,51.998,51.998,51.998h353.976c0.002,0,0.003,0,0.005,0 c0.003,0,0.006,0,0.009,0c28.672,0,51.998-23.326,51.998-51.998V256C484.994,250.477,480.517,246.001,474.994,246.001z M79.006,492c-17.645,0-31.999-14.355-31.999-31.999V34.156L65.321,52.47c3.905,3.905,10.237,3.905,14.143,0l28.328-28.328 L136.12,52.47c3.905,3.905,10.237,3.905,14.143,0l28.328-28.328L206.92,52.47c3.905,3.905,10.237,3.905,14.142,0l28.328-28.328 l28.328,28.328c3.905,3.905,10.238,3.905,14.142,0l18.329-18.328 V256c0,0.091,0.011,0.18,0.014,0.271v203.73c0,12.062,4.14,23.168,11.057,31.999H79.006z M464.994,460.001 c0,17.644-14.355,31.999-31.999,31.999s-31.999-14.355-31.999-31.999V266h63.998V460.001z' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%23DDDDDD'/%3E%3C/g%3E%3C/g%3E%3Cg%3E%3Cg%3E%3Cpath d='M213.995,158.855c-9.925,0-17.999-6.914-17.999-15.411c0-8.498,8.075-15.412,17.999-15.412 c9.925,0,17.999,6.914,17.999,15.412c0,5.523,4.477,10,10,10c5.523,0,10-4.477,10-10c0-16.301-11.884-30.055-27.999-34.157v-6.774 c0-5.523-4.477-10-10-10s-10,4.477-10,10v6.774c-16.116,4.102-27.999,17.857-27.999,34.157c0,19.525,17.047,35.41,37.999,35.41 c9.925,0,17.999,6.914,17.999,15.412s-8.074,15.411-17.999,15.411c-9.925,0-17.999-6.914-17.999-15.411c0-5.523-4.477-10-10-10 s-10,4.477-10,10c0,16.301,11.884,30.055,27.999,34.157v8.16c0,5.523,4.477,10,10,10s10-4.477,10-10v-8.16 c16.116-4.102,27.999-17.856,27.999-34.157C251.994,174.741,234.947,158.855,213.995,158.855z' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%23DDDDDD'/%3E%3C/g%3E%3C/g%3E%3Cg%3E%3Cg%3E%3Cpath d='M314.461,158.856h-19.49c-5.523,0-10,4.477-10,10s4.477,10,10,10h19.49c5.523,0,10-4.477,10-10 S319.984,158.856,314.461,158.856z' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%23DDDDDD'/%3E%3C/g%3E%3C/g%3E%3Cg%3E%3Cg%3E%3Cpath d='M133.02,158.856h-19.49c-5.523,0-10,4.477-10,10s4.477,10,10,10h19.49c5.523,0,10-4.477,10-10 S138.543,158.856,133.02,158.856z' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%23DDDDDD'/%3E%3C/g%3E%3C/g%3E%3Cg%3E%3Cg%3E%3Cpath d='M319.52,270.007H161.097c-5.523,0-10,4.477-10,10c0,5.523,4.477,10,10,10H319.52c5.523,0,10-4.477,10-10 C329.519,274.484,325.043,270.007,319.52,270.007z' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%23DDDDDD'/%3E%3C/g%3E%3C/g%3E%3Cg%3E%3Cg%3E%3Cpath d='M115.538,272.937c-1.86-1.86-4.44-2.93-7.07-2.93c-2.63,0-5.21,1.07-7.07,2.93c-1.86,1.86-2.93,4.44-2.93,7.07 c0,2.63,1.07,5.21,2.93,7.07c1.86,1.86,4.44,2.93,7.07,2.93c2.63,0,5.21-1.07,7.07-2.93c1.86-1.86,2.93-4.44,2.93-7.07 C118.468,277.377,117.398,274.797,115.538,272.937z' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%23DDDDDD'/%3E%3C/g%3E%3C/g%3E%3Cg%3E%3Cg%3E%3Cpath d='M319.52,336.005H161.097c-5.523,0-10,4.477-10,10c0,5.523,4.477,10,10,10H319.52c5.523,0,10-4.477,10-10 C329.519,340.482,325.043,336.005,319.52,336.005z' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%23DDDDDD'/%3E%3C/g%3E%3C/g%3E%3Cg%3E%3Cg%3E%3Cpath d='M115.538,338.935c-1.86-1.86-4.44-2.93-7.07-2.93c-2.63,0-5.21,1.07-7.07,2.93c-1.86,1.86-2.93,4.44-2.93,7.07 c0,2.63,1.07,5.21,2.93,7.07c1.86,1.86,4.44,2.93,7.07,2.93c2.63,0,5.21-1.07,7.07-2.93c1.86-1.86,2.93-4.44,2.93-7.07 C118.468,343.375,117.398,340.795,115.538,338.935z' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%23DDDDDD'/%3E%3C/g%3E%3C/g%3E%3Cg%3E%3Cg%3E%3Cpath d='M319.52,402.003H161.097c-5.523,0-10,4.477-10,10s4.477,10,10,10H319.52c5.523,0,10-4.477,10-10 S325.043,402.003,319.52,402.003z' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%23DDDDDD'/%3E%3C/g%3E%3C/g%3E%3Cg%3E%3Cg%3E%3Cpath d='M115.538,404.933c-1.86-1.86-4.44-2.93-7.07-2.93c-2.63,0-5.21,1.07-7.07,2.93c-1.86,1.86-2.93,4.44-2.93,7.07 s1.07,5.21,2.93,7.07c1.86,1.86,4.44,2.93,7.07,2.93c2.63,0,5.21-1.07,7.07-2.93c1.86-1.86,2.93-4.44,2.93-7.07 S117.398,406.793,115.538,404.933z' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%23DDDDDD'/%3E%3C/g%3E%3C/g%3E%3C/g%3E%3C/svg%3E%0A");
                background-size: 60px;
                width: 62px;
                height: 62px;
                display: block;
                margin: 0 auto;
                background-position: center center;
                background-repeat: no-repeat;
              } /* icon */
              span {
                display: block;
                width: 100%;
                font-size: 16px;
                margin: 7px auto 0;
                color: #dddddd;
              } /* span */
            } /* no_payment_links */
            .all_payment_links {
              width: 100%;
              min-height: 240px;
              .payment_item {
                display: flex;
                -webkit-box-pack: justify;
                -ms-flex-pack: justify;
                justify-content: space-between;
                -webkit-box-align: center;
                -ms-flex-align: center;
                align-items: center;
                width: 100%;
                -ms-flex-wrap: wrap;
                flex-wrap: wrap;
                border: 1px solid #ddd;
                margin: 0 auto 10px;
                border-radius: 5px;
                padding: 5px;
                background: #fdfdfd;
                -webkit-transition: all 0.2s ease-in-out;
                transition: all 0.2s ease-in-out;
                position: relative;
                .col_right {
                  width: 65%;
                  span {
                    display: block;
                    font-size: 15px;
                    color: #666666;
                    p {
                      display: inline-block;
                      color: #000000;
                    } /* p */
                  } /* span */
                } /* col_right */
                .col_left {
                  width: 35%;
                  display: flex;
                  -ms-flex-wrap: wrap;
                  flex-wrap: wrap;
                  -webkit-box-pack: justify;
                  -ms-flex-pack: justify;
                  justify-content: space-between;
                  -ms-flex-item-align: stretch;
                  align-self: stretch;
                  time {
                    display: flex;
                    -webkit-box-pack: end;
                    -ms-flex-pack: end;
                    justify-content: flex-end;
                    width: 100%;
                    font-size: 13px;
                    color: #777777;
                  } /* time */
                  .name {
                    display: flex;
                    -ms-flex-item-align: end;
                    align-self: flex-end;
                    -webkit-box-pack: end;
                    -ms-flex-pack: end;
                    justify-content: flex-end;
                    width: 100%;
                    label {
                      display: block;
                      border-radius: 100px;
                      padding: 0 15px;
                      min-width: 60px;
                      font-size: 12px;
                      height: 20px;
                      line-height: 20px;
                      font-weight: normal;
                      color: #ffffff;
                      &.badge-success {
                        background-color: #3e884f;
                      } /* badge-success */
                      &.badge-info {
                        background-color: #3195a5;
                      } /* badge-info */
                    } /* label */
                  } /* name */
                } /* col_left */
                .links_area {
                  position: absolute;
                  right: 0;
                  left: 0;
                  width: 100%;
                  height: 100%;
                  background: rgba(250, 250, 250, 0.8);
                  border-radius: 4px;
                  display: flex;
                  align-items: center;
                  justify-content: center;
                  flex-wrap: nowrap;
                  opacity: 0;
                  visibility: hidden;
                  -webkit-transition: all 0.2s ease-in-out;
                  -moz-transition: all 0.2s ease-in-out;
                  -o-transition: all 0.2s ease-in-out;
                  transition: all 0.2s ease-in-out;
                  button {
                    margin: 0 5px;
                    height: 35px;
                    width: 35px;
                    background: #4099de;
                    border-radius: 4px;
                    overflow: hidden;
                    text-align: center;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    -webkit-transition: all 0.4s ease-in-out;
                    -moz-transition: all 0.4s ease-in-out;
                    -o-transition: all 0.4s ease-in-out;
                    transition: all 0.4s ease-in-out;
                    -webkit-transform: scale(0);
                    -moz-transform: scale(0);
                    -o-transform: scale(0);
                    transform: scale(0);
                    svg {
                      width: 20px;
                      height: auto;
                    } /* svg */
                    &:hover {
                      background: #0071C9;
                    } /* hover */
                  } /* button */
                  a {
                    margin: 0 5px;
                    height: 35px;
                    width: 35px;
                    background: #4099de;
                    border-radius: 4px;
                    overflow: hidden;
                    text-align: center;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    -webkit-transition: all 0.4s ease-in-out;
                    -moz-transition: all 0.4s ease-in-out;
                    -o-transition: all 0.4s ease-in-out;
                    transition: all 0.4s ease-in-out;
                    -webkit-transform: scale(0);
                    -moz-transform: scale(0);
                    -o-transform: scale(0);
                    transform: scale(0);
                    svg {
                      width: 20px;
                      height: auto;
                    } /* svg */
                    &:hover {
                      background: #0071C9;
                    } /* hover */
                  } /* a */
                } /* links_area */
                &:hover {
                  .links_area {
                    opacity: 1;
                    visibility: visible;
                    button {
                      -webkit-transform: scale(1);
                      -moz-transform: scale(1);
                      -o-transform: scale(1);
                      transform: scale(1);
                    } /* button */
                    a {
                      -webkit-transform: scale(1);
                      -moz-transform: scale(1);
                      -o-transform: scale(1);
                      transform: scale(1);
                    } /* a */
                  } /* links_area */
                } /* hover */
              } /* payment_item */
            } /* all_payment_links */
            .block_bottom {
              display: flex;
              justify-content: space-between;
              align-items: center;
              flex-wrap: wrap;
              width: 100%;
              align-self: end;
              button.add_payment_link {
                background: #4099de;
                border-radius: 5px;
                border: 1px solid #4099de;
                min-width: 100px;
                height: 35px;
                line-height: 35px;
                font-size: 15px;
                color: #ffffff;
                padding: 0 15px;
                -webkit-transition: all 0.2s ease-in-out;
                -moz-transition: all 0.2s ease-in-out;
                -o-transition: all 0.2s ease-in-out;
                transition: all 0.2s ease-in-out;
                &:hover {
                  background: #0071C9;
                  border-color: #0071C9;
                } /* hover */
              } /* add_payment_link */
            } /* block_bottom */
          } /* content */
        } /* payment_links_block */
      } /* left_col */
    } /* row_cols */
    .comments_block {
      margin: 0 auto 20px;
      padding: 0;
      .title {
        display: flex;
        align-items: center;
        justify-content: space-between;
        display: block;
        font-size: 20px;
        color: #000;
      } /* title */
      .content {
        width: auto;
        min-width: auto;
        max-width: none;
        background: #ffffff;
        border-radius: 5px;
        margin: 5px auto 0;
        border: 1px solid #ddd;
        padding: 10px;
        box-shadow: 0 2px 4px 0 rgba(0,0,0,.05);
        display: flex;
        flex-wrap: wrap;
        align-items: stretch;
        min-height: 296px;
        .all_comments_items {
          width: 100%;
          min-height: 310px;
        } /* all_comments_items */
        .comment_item {
          display: flex;
          justify-content: space-between;
          align-items: center;
          width: 100%;
          flex-wrap: wrap;
          border: 1px solid #ddd;
          margin: 0 auto 10px;
          border-radius: 5px;
          padding: 5px;
          background: #fdfdfd;

          .desc {
            display: block;
            white-space: pre-line;
            font-size: 15px;
            color: #000;
            margin: 10px 0;
            width: 100%;
          } /* desc */
          .user_info {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            .user_account {
              display: flex;
              align-items: center;
              justify-content: flex-start;
              font-size: 15px;
              color: #666666;
              img {
                display: block;
                width: 35px;
                height: 35px;
                border-radius: 100%;
                margin: 0 0 0 5px;
                border: 1px solid #ddd;
                [dir="ltr"] & {
                  margin: 0 5px 0 0;
                } /* ltr */
              } /* img */
            } /* user_account */
            time {
              display: block;
              font-size: 13px;
              color: #777777;
            } /* time */
          } /* user_info */
        } /* comment_item */
        .more_comments {
          background-image: url("data:image/svg+xml,%3C%3Fxml version='1.0'%3F%3E%3Csvg xmlns='http://www.w3.org/2000/svg' id='Capa_1' enable-background='new 0 0 515.555 515.555' height='512px' viewBox='0 0 515.555 515.555' width='512px' class=''%3E%3Cg%3E%3Cpath d='m496.679 212.208c25.167 25.167 25.167 65.971 0 91.138s-65.971 25.167-91.138 0-25.167-65.971 0-91.138 65.971-25.167 91.138 0' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%23666666'/%3E%3Cpath d='m303.347 212.208c25.167 25.167 25.167 65.971 0 91.138s-65.971 25.167-91.138 0-25.167-65.971 0-91.138 65.971-25.167 91.138 0' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%23666666'/%3E%3Cpath d='m110.014 212.208c25.167 25.167 25.167 65.971 0 91.138s-65.971 25.167-91.138 0-25.167-65.971 0-91.138 65.971-25.167 91.138 0' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%23666666'/%3E%3C/g%3E%3C/svg%3E%0A");
          background-color: #fafafa;
          border: 1px solid #ddd;
          border-radius: 5px;
          height: 30px;
          margin: 0 auto 10px;
          background-size: 30px;
          background-repeat: no-repeat;
          background-position: center center;
          cursor: pointer;
          &:hover {
            background-color: #f5f5f5;
            border-color: #d8d8d8;
          } /* hover */
        } /* more_comments */
        .no_comments {
          min-height: 310px;
          width: 100%;
          text-align: center;
          display: flex;
          align-items: center;
          justify-content: center;
          flex-wrap: wrap;
          .icon {
            background-image: url("data:image/svg+xml,%3C%3Fxml version='1.0'%3F%3E%3Csvg xmlns='http://www.w3.org/2000/svg' id='Capa_1' enable-background='new 0 0 512 512' height='512px' viewBox='0 0 512 512' width='512px'%3E%3Cg%3E%3Cg%3E%3Cpath d='m470.05 173.341h-62.573v-66.313c0-28.691-23.147-52.034-51.599-52.034h-192.423c-4.143 0-7.5 3.357-7.5 7.5s3.357 7.5 7.5 7.5h192.423c20.181 0 36.599 16.613 36.599 37.034v179.025c0 20.42-16.418 37.033-36.599 37.033h-271.867c-2.366 0-4.593 1.116-6.009 3.012l-52.655 70.491c-2.235 2.995-5.188 2.42-6.355 2.031-.937-.31-3.992-1.635-3.992-5.615v-285.978c0-20.421 16.418-37.034 36.599-37.034h79.339c4.143 0 7.5-3.357 7.5-7.5s-3.357-7.5-7.5-7.5h-79.339c-28.452 0-51.599 23.343-51.599 52.034v285.978c0 9.183 5.6 16.975 14.265 19.852 2.211.733 4.451 1.09 6.654 1.089 6.365 0 12.409-2.972 16.447-8.383l50.403-67.477h102.671v17.383c0 23.313 18.818 42.278 41.95 42.278h35.999c4.143 0 7.5-3.357 7.5-7.5s-3.357-7.5-7.5-7.5h-35.999c-14.86 0-26.95-12.237-26.95-27.278v-17.383h150.438c28.451 0 51.599-23.342 51.599-52.033v-97.712h62.573c14.86 0 26.95 12.242 26.95 27.29v223.374c0 1.886-1.248 2.582-1.992 2.828-.868.288-2.113.347-3.128-1.017l-41.128-55.058c-1.416-1.896-3.643-3.012-6.009-3.012h-143.593c-4.143 0-7.5 3.357-7.5 7.5s3.357 7.5 7.5 7.5h139.834l38.873 52.04c3.474 4.66 8.674 7.22 14.152 7.22 1.894-.001 3.82-.307 5.72-.937 7.454-2.472 12.271-9.171 12.271-17.065v-223.373c0-23.318-18.818-42.29-41.95-42.29z' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%23DDDDDD'/%3E%3Cpath d='m218.177 130.707h-139.872c-4.143 0-7.5 3.357-7.5 7.5s3.357 7.5 7.5 7.5h139.872c4.143 0 7.5-3.357 7.5-7.5s-3.358-7.5-7.5-7.5z' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%23DDDDDD'/%3E%3Cpath d='m298.828 197.382c0-4.143-3.357-7.5-7.5-7.5h-213.023c-4.143 0-7.5 3.357-7.5 7.5s3.357 7.5 7.5 7.5h213.023c4.143 0 7.5-3.358 7.5-7.5z' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%23DDDDDD'/%3E%3Cpath d='m78.305 249.058c-4.143 0-7.5 3.357-7.5 7.5s3.357 7.5 7.5 7.5h184.085c4.143 0 7.5-3.357 7.5-7.5s-3.357-7.5-7.5-7.5z' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%23DDDDDD'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E%0A");
            background-size: 60px;
            width: 62px;
            height: 62px;
            display: block;
            margin: 0 auto;
            background-position: center center;
            background-repeat: no-repeat;
          } /* icon */
          span {
            display: block;
            width: 100%;
            font-size: 16px;
            margin: 7px auto 0;
            color: #dddddd;
          } /* span */
        } /* no_comments */
        .block_bottom {
          display: flex;
          justify-content: space-between;
          align-items: center;
          flex-wrap: wrap;
          width: 100%;
          align-self: end;
          button.add_comment {
            background: #4099de;
            border-radius: 5px;
            border: 1px solid #4099de;
            min-width: 100px;
            height: 35px;
            line-height: 35px;
            font-size: 15px;
            color: #ffffff;
            padding: 0 15px;
            -webkit-transition: all 0.2s ease-in-out;
            -moz-transition: all 0.2s ease-in-out;
            -o-transition: all 0.2s ease-in-out;
            transition: all 0.2s ease-in-out;
            &:hover {
              background: #0071C9;
              border-color: #0071C9;
            } /* hover */
          } /* add_comment */
          button.more {
            background: #ffffff;
            border-radius: 5px;
            border: 1px solid #4099de;
            min-width: 100px;
            height: 35px;
            line-height: 35px;
            font-size: 15px;
            color: #4099de;
            padding: 0 15px;
            -webkit-transition: all 0.2s ease-in-out;
            -moz-transition: all 0.2s ease-in-out;
            -o-transition: all 0.2s ease-in-out;
            transition: all 0.2s ease-in-out;
            &:hover {
              background: #4099de;
              color: #ffffff;
            } /* hover */
          } /* more */
        } /* block_bottom */
      } /* content */
    } /* comments_block */
    ul.pagination {
      li {
        margin: 0 !important;
        padding: 0 !important;
        border-bottom: none !important;
      } /* li */
    } /* pagination */
  } /* reservation_page */

  .transactions_modal {
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
    .financial_item {
      display: flex;
      justify-content: space-between;
      align-items: center;
      width: 100%;
      flex-wrap: wrap;
      border: 1px solid #ddd;
      margin: 0 auto 10px;
      border-radius: 5px;
      padding: 5px;
      background: #fdfdfd;
      cursor: pointer;
      -webkit-transition: all 0.2s ease-in-out;
      -moz-transition: all 0.2s ease-in-out;
      -o-transition: all 0.2s ease-in-out;
      transition: all 0.2s ease-in-out;
      &:hover {
        background: #f8f8f8;
        border-color: #d8d8d8;
      } /* hover */
      .col_right {
        width: 70%;
        @media (min-width: 320px) and (max-width: 767px) {
          width: 50%;
        } /* Mobile */
        span {
          display: block;
          font-size: 15px;
          color: #666666;
          p {
            display: inline-block;
            color: #000000;
            &.text-success {
              color: #155724;
            } /* text-success */
            &.text-danger {
              color: #721c24;
            } /* text-danger */
          } /* p */
        } /* span */
      } /* col_right */
      .col_left {
        width: 30%;
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        align-self: stretch;
        @media (min-width: 320px) and (max-width: 767px) {
          width: 50%;
        } /* Mobile */
        time {
          display: flex;
          justify-content: flex-end;
          width: 100%;
          font-size: 13px;
          color: #777777;
        } /* time */
        .name {
          display: flex;
          align-self: flex-end;
          justify-content: flex-end;
          width: 100%;
          label {
            display: block;
            border-radius: 100px;
            padding: 0 15px;
            min-width: 60px;
            font-size: 14px;
            height: 20px;
            line-height: 18px;
            font-weight: normal;
            border-width: 1px;
            border-style: solid;
            &.text-success {
              color: #155724;
              background-color: #d4edda;
              border-color: #c3e6cb;
            } /* text-success */
            &.text-danger {
              color: #721c24;
              background-color: #f8d7da;
              border-color: #f5c6cb;
            } /* text-danger */
          } /* label */
          p {
                border-radius: 100px;
                border: 1px solid #dcd56d;
                padding: 0 10px;
                min-width: 50px;
                font-size: 14px;
                height: 20px;
                color: #676523;
                margin: 0 0 0 5px;
                display: -webkit-box;
                display: -ms-flexbox;
                display: flex;
                -webkit-box-align: center;
                -ms-flex-align: center;
                align-items: center;
                -webkit-box-pack: center;
                -ms-flex-pack: center;
                justify-content: center;
                background: #fff56d;
            } /* p */
        } /* name */
      } /* col_left */
    } /* financial_item */
  } /* transactions_modal */
  p.credit_payment {
      color: #004085 !important;
      background-color: #cce5ff !important;
      border-color: #b8daff !important;
  }
  .add_comment_modal {
    textarea {
      width: 100%;
      padding: 10px;
      border-radius: 5px;
      background: #fafafa;
      border: 1px solid #ddd;
      font-size: 15px;
      color: #000;
      margin: 0 auto 10px;
    } /* textarea */
    button.save_button {
      height: 35px;
      background: #4099de;
      width: 100%;
      border-radius: 5px;
      font-size: 15px;
      color: #fff;
      &:hover {background: #0071C9;}
    } /* button */
  } /* add_comment_modal */

  .comments_modal {
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
    .comment_item {
      display: flex;
      justify-content: space-between;
      align-items: center;
      width: 100%;
      flex-wrap: wrap;
      border: 1px solid #ddd;
      margin: 0 auto 10px;
      border-radius: 5px;
      padding: 5px;
      background: #fdfdfd;
      .desc {
        display: block;
        white-space: pre-line;
        font-size: 15px;
        color: #000;
        margin: 0 0 10px;
      } /* desc */
      .user_info {
        display: flex;
        align-items: center;
        justify-content: space-between;
        width: 100%;
        .user_account {
          display: flex;
          align-items: center;
          justify-content: flex-start;
          font-size: 15px;
          color: #666666;
          img {
            display: block;
            width: 35px;
            height: 35px;
            border-radius: 100%;
            margin: 0 0 0 5px;
            border: 1px solid #ddd;
          } /* img */
        } /* user_account */
        time {
          display: block;
          font-size: 13px;
          color: #777777;
        } /* time */
      } /* user_info */
    } /* comment_item */
  } /* comments_modal */

  .customer_notes_modal {
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
    .note_item {
      display: flex;
      justify-content: space-between;
      align-items: center;
      width: 100%;
      flex-wrap: wrap;
      border: 1px solid #ddd;
      margin: 0 auto 10px;
      border-radius: 5px;
      padding: 5px;
      background: #fdfdfd;
      .desc {
        display: block;
        white-space: pre-line;
        font-size: 15px;
        color: #000;
        margin: 0 0 10px;
      } /* desc */
      .user_info {
        display: flex;
        align-items: center;
        justify-content: space-between;
        width: 100%;
        .user_account {
          display: flex;
          align-items: center;
          justify-content: flex-start;
          font-size: 15px;
          color: #666666;
          img {
            display: block;
            width: 35px;
            height: 35px;
            border-radius: 100%;
            margin: 0 0 0 5px;
            border: 1px solid #ddd;
            [dir="ltr"] & {
              margin: 0 5px 0 0;
            } /* ltr */
          } /* img */
        } /* user_account */
        time {
          display: block;
          font-size: 13px;
          color: #777777;
        } /* time */
      } /* user_info */
    } /* note_item */
  } /* customer_notes_modal */

  .sendmailModal {
    .tabs {
      display: flex;
      justify-content: space-between;
      padding: 10px 0;
      a {
        background: #fafafa;
        display: block;
        width: 100%;
        border: 1px solid #ddd;
        padding: 0 10px;
        font-size: 15px;
        border-left: none;
        color: #000;
        cursor: pointer;
        text-align: center;
        position: relative;
        height: 35px;
        line-height: 35px;
        &:after {
          position: absolute;
          top: 100%;
          right: 0;
          left: 0;
          margin:0 auto;
          content: "";
          display: none;
          width: 0;
          height: 0;
          border-style: solid;
          border-width: 10px 7.5px 0 7.5px;
          border-color: #4099de transparent transparent transparent;
          line-height: 0;
          _border-color: #4099de #000000 #000000 #000000;
          _filter: progid:DXImageTransform.Microsoft.Chroma(color='#000000');
        } /* after */
        &:hover {background: #f5f5f5;}
        &:first-child {border-radius: 0 5px 5px 0;}
        &:last-child {
          border-left: 1px solid #ddd;
          border-radius: 5px 0 0 5px;
        } /* last-child */
        &.active {
          background: #4099de;
          color: #fff;
          border-color: #4099de;
          &:after {display: inline-block;}
        } /* active */
      } /* a */
    } /* tabs */
    .content {
      width: auto;
      min-width: auto;
      padding: 20px 0 0;
      .formgroup {
        margin: 0 auto 15px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        label {
          display: block;
          font-size: 15px;
          width: 20%;
        } /* label */
        input {
          background: #fafafa;
          width: 80%;
          border: 1px solid #dddddd !important;
          height: 40px;
          padding: 0 10px;
          font-size: 15px;
          border-radius: 5px !important;
          &[disabled="disabled"] {
            background: #ddd;
            border-color: #c4c4c4 !important;
            cursor: not-allowed;
          } /* disabled */
        } /* input */
        textarea {
          background: #fafafa;
          border: 1px solid #dddddd !important;
          padding: 10px;
          font-size: 15px;
          border-radius: 5px !important;
          width: 80%;
          white-space: normal;
        } /* textarea */
      } /* formgroup */
      table {
        border: 1px solid #ddd;
        width: 100%;
        th {
          background: #4a5568;
          vertical-align: middle;
          text-align: center;
          padding: 5px 10px;
          color: #ffffff;
          font-weight: normal;
          border: 1px solid #5E697C;
          &:first-child {text-align: right;}
        } /* th */
        td {
          background: #fefefe;
          border: 1px solid #ddd;
          padding: 5px;
          vertical-align: middle;
          text-align: center;
          font-size: 15px;
          &:first-child {text-align: right;}
          &:nth-child(2n) {direction: ltr;}
        } /* td */
      } /* table */
      .sms_not_available {
        position: absolute;
        height: 100%;
        top: 0;
        right: 0;
        width: 100%;
        text-align: center;
        background: rgba(255, 255, 255, 0.8);
        display: block;
        font-size: 16px;
        color: #000;
        padding: 13% 0;
        a {
          color: #0A80D8;
        } /* a */
      } /* sms_not_available */
        .no-messages{
            min-height: 200px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 15px;
            color: #000;
        }
      .block_bottom {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        width: 100%;
        align-self: end;
        button.send {
          background: #4099de;
          border-radius: 5px;
          border: 1px solid #4099de;
          min-width: 100px;
          height: 35px;
          line-height: 35px;
          font-size: 15px;
          color: #ffffff;
          padding: 0 15px;
          -webkit-transition: all 0.2s ease-in-out;
          -moz-transition: all 0.2s ease-in-out;
          -o-transition: all 0.2s ease-in-out;
          transition: all 0.2s ease-in-out;
          &:hover {
            background: #0071C9;
            border-color: #0071C9;
          } /* hover */
        } /* send */
        button.close {
          background: #cbd5e0;
          border-radius: 5px;
          border: 1px solid #cbd5e0;
          min-width: 100px;
          height: 35px;
          line-height: 35px;
          font-size: 15px;
          color: #000000;
          padding: 0 15px;
          -webkit-transition: all 0.2s ease-in-out;
          -moz-transition: all 0.2s ease-in-out;
          -o-transition: all 0.2s ease-in-out;
          transition: all 0.2s ease-in-out;
          &:hover {
            background: #a0aec0;
            border-color: #a0aec0;
          } /* hover */
        } /* close */
      } /* block_bottom */
    } /* content */
  } /* sendmailModal */

  .share_button_reservation {
    display: flex;
    justify-content: center;
    align-items: center;
    flex-wrap: wrap;
    margin: 0 auto 15px;
    border-bottom: 1px solid #dddddd;
    padding: 0 0 10px;
    a {
      display: block;
      height: 35px;
      width: 35px;
      border-radius: 5px;
      background-position: center center;
      background-size: 20px;
      background-repeat: no-repeat;
      margin: 5px;
      cursor: pointer;
      &.pdf_button {
        background-image: url("data:image/svg+xml,%3C%3Fxml version='1.0'%3F%3E%3Csvg xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' version='1.1' id='Capa_1' x='0px' y='0px' viewBox='0 0 482.14 482.14' style='enable-background:new 0 0 482.14 482.14;' xml:space='preserve' width='512px' height='512px'%3E%3Cg%3E%3Cg%3E%3Cpath d='M142.024,310.194c0-8.007-5.556-12.782-15.359-12.782c-4.003,0-6.714,0.395-8.132,0.773v25.69 c1.679,0.378,3.743,0.504,6.588,0.504C135.57,324.379,142.024,319.1,142.024,310.194z' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%23FFFFFF'/%3E%3Cpath d='M202.709,297.681c-4.39,0-7.227,0.379-8.905,0.772v56.896c1.679,0.394,4.39,0.394,6.841,0.394 c17.809,0.126,29.424-9.677,29.424-30.449C230.195,307.231,219.611,297.681,202.709,297.681z' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%23FFFFFF'/%3E%3Cpath d='M315.458,0H121.811c-28.29,0-51.315,23.041-51.315,51.315v189.754h-5.012c-11.418,0-20.678,9.251-20.678,20.679v125.404 c0,11.427,9.259,20.677,20.678,20.677h5.012v22.995c0,28.305,23.025,51.315,51.315,51.315h264.223 c28.272,0,51.3-23.011,51.3-51.315V121.449L315.458,0z M99.053,284.379c6.06-1.024,14.578-1.796,26.579-1.796 c12.128,0,20.772,2.315,26.58,6.965c5.548,4.382,9.292,11.615,9.292,20.127c0,8.51-2.837,15.745-7.999,20.646 c-6.714,6.32-16.643,9.157-28.258,9.157c-2.585,0-4.902-0.128-6.714-0.379v31.096H99.053V284.379z M386.034,450.713H121.811 c-10.954,0-19.874-8.92-19.874-19.889v-22.995h246.31c11.42,0,20.679-9.25,20.679-20.677V261.748 c0-11.428-9.259-20.679-20.679-20.679h-246.31V51.315c0-10.938,8.921-19.858,19.874-19.858l181.89-0.19v67.233 c0,19.638,15.934,35.587,35.587,35.587l65.862-0.189l0.741,296.925C405.891,441.793,396.987,450.713,386.034,450.713z M174.065,369.801v-85.422c7.225-1.15,16.642-1.796,26.58-1.796c16.516,0,27.226,2.963,35.618,9.282 c9.031,6.714,14.704,17.416,14.704,32.781c0,16.643-6.06,28.133-14.453,35.224c-9.157,7.612-23.096,11.222-40.125,11.222 C186.191,371.092,178.966,370.446,174.065,369.801z M314.892,319.226v15.996h-31.23v34.973h-19.74v-86.966h53.16v16.122h-33.42 v19.875H314.892z' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%23FFFFFF'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E%0A");
        background-color: #f00000;
        &:hover {background-color: #e10000;}
      } /* pdf_button */
      &.print_button {
        background-image: url("data:image/svg+xml,%3C%3Fxml version='1.0'%3F%3E%3Csvg xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' version='1.1' id='Layer_1' x='0px' y='0px' viewBox='0 0 512 512' style='enable-background:new 0 0 512 512;' xml:space='preserve' width='512px' height='512px'%3E%3Cg%3E%3Cg%3E%3Cg%3E%3Cpath d='M472.178,133.907h-54.303V35.132c0-9.425-7.641-17.067-17.067-17.067H111.192c-9.425,0-17.067,7.641-17.067,17.067v98.775 H39.822C17.864,133.907,0,151.772,0,173.73v171.702c0,21.958,17.864,39.822,39.822,39.822h54.306v91.614 c0,9.425,7.641,17.067,17.067,17.067h289.61c9.425,0,17.067-7.641,17.067-17.067v-91.614h54.306 c21.958,0,39.822-17.864,39.822-39.822V173.73C512,151.773,494.136,133.907,472.178,133.907z M128.258,52.199h255.483v81.708 H128.258V52.199z M383.738,459.801H128.262c0-3.335,0-135.503,0-139.628h255.477C383.738,324.402,383.738,456.594,383.738,459.801 z M477.867,345.433c0,3.137-2.552,5.689-5.689,5.689h-54.306v-48.014c0-9.425-7.641-17.067-17.067-17.067h-289.61 c-9.425,0-17.067,7.641-17.067,17.067v48.014H39.822c-3.137,0-5.689-2.552-5.689-5.689V173.731c0-3.137,2.552-5.689,5.689-5.689 c13.094,0,419.57,0,432.356,0c3.137,0,5.689,2.552,5.689,5.689V345.433z' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%23FFFFFF'/%3E%3C/g%3E%3C/g%3E%3Cg%3E%3Cg%3E%3Cpath d='M400.808,199.988h-43.443c-9.425,0-17.067,7.641-17.067,17.067s7.641,17.067,17.067,17.067h43.443 c9.425,0,17.067-7.641,17.067-17.067S410.234,199.988,400.808,199.988z' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%23FFFFFF'/%3E%3C/g%3E%3C/g%3E%3Cg%3E%3Cg%3E%3Cpath d='M329.956,399.834H182.044c-9.425,0-17.067,7.641-17.067,17.067s7.641,17.067,17.067,17.067h147.911 c9.425,0,17.067-7.641,17.067-17.067S339.381,399.834,329.956,399.834z' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%23FFFFFF'/%3E%3C/g%3E%3C/g%3E%3Cg%3E%3Cg%3E%3Cpath d='M329.956,346.006H182.044c-9.425,0-17.067,7.641-17.067,17.067s7.641,17.067,17.067,17.067h147.911 c9.425,0,17.067-7.641,17.067-17.067S339.381,346.006,329.956,346.006z' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%23FFFFFF'/%3E%3C/g%3E%3C/g%3E%3C/g%3E%3C/svg%3E%0A");
        background-color: #4099de;
        &:hover {background-color: #0071C9;}
      } /* print_button */
      &.trash_button {
        background-image: url("data:image/svg+xml,%3C%3Fxml version='1.0'%3F%3E%3Csvg xmlns='http://www.w3.org/2000/svg' height='512px' viewBox='-40 0 427 427.00131' width='512px'%3E%3Cg%3E%3Cpath d='m232.398438 154.703125c-5.523438 0-10 4.476563-10 10v189c0 5.519531 4.476562 10 10 10 5.523437 0 10-4.480469 10-10v-189c0-5.523437-4.476563-10-10-10zm0 0' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%23FFFFFF'/%3E%3Cpath d='m114.398438 154.703125c-5.523438 0-10 4.476563-10 10v189c0 5.519531 4.476562 10 10 10 5.523437 0 10-4.480469 10-10v-189c0-5.523437-4.476563-10-10-10zm0 0' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%23FFFFFF'/%3E%3Cpath d='m28.398438 127.121094v246.378906c0 14.5625 5.339843 28.238281 14.667968 38.050781 9.285156 9.839844 22.207032 15.425781 35.730469 15.449219h189.203125c13.527344-.023438 26.449219-5.609375 35.730469-15.449219 9.328125-9.8125 14.667969-23.488281 14.667969-38.050781v-246.378906c18.542968-4.921875 30.558593-22.835938 28.078124-41.863282-2.484374-19.023437-18.691406-33.253906-37.878906-33.257812h-51.199218v-12.5c.058593-10.511719-4.097657-20.605469-11.539063-28.03125-7.441406-7.421875-17.550781-11.554687-28.0625-11.46875h-88.796875c-10.511719-.0859375-20.621094 4.046875-28.0625 11.46875-7.441406 7.425781-11.597656 17.519531-11.539062 28.03125v12.5h-51.199219c-19.1875.003906-35.394531 14.234375-37.878907 33.257812-2.480468 19.027344 9.535157 36.941407 28.078126 41.863282zm239.601562 279.878906h-189.203125c-17.097656 0-30.398437-14.6875-30.398437-33.5v-245.5h250v245.5c0 18.8125-13.300782 33.5-30.398438 33.5zm-158.601562-367.5c-.066407-5.207031 1.980468-10.21875 5.675781-13.894531 3.691406-3.675781 8.714843-5.695313 13.925781-5.605469h88.796875c5.210937-.089844 10.234375 1.929688 13.925781 5.605469 3.695313 3.671875 5.742188 8.6875 5.675782 13.894531v12.5h-128zm-71.199219 32.5h270.398437c9.941406 0 18 8.058594 18 18s-8.058594 18-18 18h-270.398437c-9.941407 0-18-8.058594-18-18s8.058593-18 18-18zm0 0' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%23FFFFFF'/%3E%3Cpath d='m173.398438 154.703125c-5.523438 0-10 4.476563-10 10v189c0 5.519531 4.476562 10 10 10 5.523437 0 10-4.480469 10-10v-189c0-5.523437-4.476563-10-10-10zm0 0' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%23FFFFFF'/%3E%3C/g%3E%3C/svg%3E%0A");
        background-color: #222222;
        &:hover {background-color: #333333;}
      } /* trash_button */
      &.whatsapp_button {
        outline: none;
        background-image: url("data:image/svg+xml,%3C%3Fxml version='1.0'%3F%3E%3Csvg xmlns='http://www.w3.org/2000/svg' height='512px' viewBox='-23 -21 682 682.66669' width='512px'%3E%3Cg%3E%3Cpath d='m544.386719 93.007812c-59.875-59.945312-139.503907-92.9726558-224.335938-93.007812-174.804687 0-317.070312 142.261719-317.140625 317.113281-.023437 55.894531 14.578125 110.457031 42.332032 158.550781l-44.992188 164.335938 168.121094-44.101562c46.324218 25.269531 98.476562 38.585937 151.550781 38.601562h.132813c174.785156 0 317.066406-142.273438 317.132812-317.132812.035156-84.742188-32.921875-164.417969-92.800781-224.359376zm-224.335938 487.933594h-.109375c-47.296875-.019531-93.683594-12.730468-134.160156-36.742187l-9.621094-5.714844-99.765625 26.171875 26.628907-97.269531-6.269532-9.972657c-26.386718-41.96875-40.320312-90.476562-40.296875-140.28125.054688-145.332031 118.304688-263.570312 263.699219-263.570312 70.40625.023438 136.589844 27.476562 186.355469 77.300781s77.15625 116.050781 77.132812 186.484375c-.0625 145.34375-118.304687 263.59375-263.59375 263.59375zm144.585938-197.417968c-7.921875-3.96875-46.882813-23.132813-54.148438-25.78125-7.257812-2.644532-12.546875-3.960938-17.824219 3.96875-5.285156 7.929687-20.46875 25.78125-25.09375 31.066406-4.625 5.289062-9.242187 5.953125-17.167968 1.984375-7.925782-3.964844-33.457032-12.335938-63.726563-39.332031-23.554687-21.011719-39.457031-46.960938-44.082031-54.890626-4.617188-7.9375-.039062-11.8125 3.476562-16.171874 8.578126-10.652344 17.167969-21.820313 19.808594-27.105469 2.644532-5.289063 1.320313-9.917969-.664062-13.882813-1.976563-3.964844-17.824219-42.96875-24.425782-58.839844-6.4375-15.445312-12.964843-13.359374-17.832031-13.601562-4.617187-.230469-9.902343-.277344-15.1875-.277344-5.28125 0-13.867187 1.980469-21.132812 9.917969-7.261719 7.933594-27.730469 27.101563-27.730469 66.105469s28.394531 76.683594 32.355469 81.972656c3.960937 5.289062 55.878906 85.328125 135.367187 119.648438 18.90625 8.171874 33.664063 13.042968 45.175782 16.695312 18.984374 6.03125 36.253906 5.179688 49.910156 3.140625 15.226562-2.277344 46.878906-19.171875 53.488281-37.679687 6.601563-18.511719 6.601563-34.375 4.617187-37.683594-1.976562-3.304688-7.261718-5.285156-15.183593-9.253906zm0 0' fill-rule='evenodd' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%23FFFFFF'/%3E%3C/g%3E%3C/svg%3E%0A");
        background-color: #4dc247;
        &:hover {background-color: #3eb338;}
      } /* whatsapp_button */
      &.show_credit_note_button {
        background-image: url("data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiA/PjxzdmcgZGF0YS1uYW1lPSIxLURvY3VtZW50IiBpZD0iXzEtRG9jdW1lbnQiIHZpZXdCb3g9IjAgMCA0OCA0OCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48dGl0bGUvPjxwYXRoIGQ9Ik00Mi43MSw4LjI5bC04LThBMSwxLDAsMCwwLDM0LDBIOEEzLDMsMCwwLDAsNSwzVjQ1YTMsMywwLDAsMCwzLDNINDBhMywzLDAsMCwwLDMtM1Y5QTEsMSwwLDAsMCw0Mi43MSw4LjI5Wk0zNSwzLjQxLDM5LjU5LDhIMzZhMSwxLDAsMCwxLTEtMVpNNDEsNDVhMSwxLDAsMCwxLTEsMUg4YTEsMSwwLDAsMS0xLTFWM0ExLDEsMCwwLDEsOCwySDMzVjdhMywzLDAsMCwwLDMsM2g1WiIvPjxyZWN0IGhlaWdodD0iMiIgd2lkdGg9IjIwIiB4PSIxNiIgeT0iMTgiLz48cmVjdCBoZWlnaHQ9IjIiIHdpZHRoPSIyNCIgeD0iMTIiIHk9IjI0Ii8+PHJlY3QgaGVpZ2h0PSIyIiB3aWR0aD0iMjQiIHg9IjEyIiB5PSIzMCIvPjxyZWN0IGhlaWdodD0iMiIgd2lkdGg9IjE2IiB4PSIxMiIgeT0iMzYiLz48cmVjdCBoZWlnaHQ9IjIiIHdpZHRoPSIyIiB4PSIzNCIgeT0iMzYiLz48cmVjdCBoZWlnaHQ9IjIiIHdpZHRoPSIyIiB4PSIzMCIgeT0iMzYiLz48L3N2Zz4=");
        background-color: #d5d5d5;
        &:hover {background-color: #d0d0d0;}
      } /* whatsapp_button */
      &.add_credit_note_button {
            // background-image: url("data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiA/PjwhRE9DVFlQRSBzdmcgIFBVQkxJQyAnLS8vVzNDLy9EVEQgU1ZHIDEuMS8vRU4nICAnaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkJz48c3ZnIGVuYWJsZS1iYWNrZ3JvdW5kPSJuZXcgMCAwIDMyIDMyIiBoZWlnaHQ9IjMycHgiIGlkPSJMYXllcl8xIiB2ZXJzaW9uPSIxLjEiIHZpZXdCb3g9IjAgMCAzMiAzMiIgd2lkdGg9IjMycHgiIHhtbDpzcGFjZT0icHJlc2VydmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiPjxnPjxwb2x5bGluZSBmaWxsPSJub25lIiBwb2ludHM9IiAgIDY0OSwxMzcuOTk5IDY3NSwxMzcuOTk5IDY3NSwxNTUuOTk5IDY2MSwxNTUuOTk5IDAiIHN0cm9rZT0iI0ZGRkZGRiIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIiBzdHJva2UtbGluZWpvaW49InJvdW5kIiBzdHJva2UtbWl0ZXJsaW1pdD0iMTAiIHN0cm9rZS13aWR0aD0iMiIvPjxwb2x5bGluZSBmaWxsPSJub25lIiBwb2ludHM9IiAgIDY1MywxNTUuOTk5IDY0OSwxNTUuOTk5IDY0OSwxNDEuOTk5IDY0OSwxMzcuOTk5ICAiIHN0cm9rZT0iI0ZGRkZGRiIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIiBzdHJva2UtbGluZWpvaW49InJvdW5kIiBzdHJva2UtbWl0ZXJsaW1pdD0iMTAiIHN0cm9rZS13aWR0aD0iMiIvPjxwb2x5bGluZSBmaWxsPSJub25lIiBwb2ludHM9IiAgIDY2MSwxNTYgNjUzLDE2MiA2NTMsMTU2ICAiIHN0cm9rZT0iI0ZGRkZGRiIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIiBzdHJva2UtbGluZWpvaW49InJvdW5kIiBzdHJva2UtbWl0ZXJsaW1pdD0iMTAiIHN0cm9rZS13aWR0aD0iMiIvPjwvZz48Zz48cGF0aCBkPSJNMTYsMzBjLTMuNzQsMC03LjI1NS0xLjQ1Ni05Ljg5OS00LjEwMUMxLjc3OSwyMS41NzgsMC43NTMsMTUuMDI1LDMuNTQ3LDkuNTk1QzMuOCw5LjEwNCw0LjQwMiw4LjkxMSw0Ljg5NCw5LjE2MyAgIGMwLjQ5MSwwLjI1MiwwLjY4NSwwLjg1NSwwLjQzMiwxLjM0N0MyLjkzMSwxNS4xNjUsMy44MSwyMC43ODEsNy41MTUsMjQuNDg1QzkuNzgxLDI2Ljc1MiwxMi43OTQsMjgsMTYsMjggICBjMy4yMDUsMCw2LjIxOS0xLjI0OCw4LjQ4NS0zLjUxNVMyOCwxOS4yMDUsMjgsMTZjMC0zLjIwNi0xLjI0OC02LjIxOS0zLjUxNS04LjQ4NVMxOS4yMDYsNCwxNiw0ICAgYy0zLjIwNiwwLTYuMjE5LDEuMjQ5LTguNDg1LDMuNTE1Yy0wLjM5MSwwLjM5MS0xLjAyMywwLjM5MS0xLjQxNCwwcy0wLjM5MS0xLjAyMywwLTEuNDE0QzguNzQ1LDMuNDU3LDEyLjI2LDIsMTYsMiAgICBjMy43MzksMCw3LjI1NiwxLjQ1Nyw5Ljg5OSw0LjEwMUMyOC41NDQsOC43NDUsMzAsMTIuMjYsMzAsMTYgICBjMCwzLjczOS0xLjQ1Niw3LjI1NS00LjEwMSw5Ljg5OUMyMy4yNTYsMjguNTQ0LDE5Ljc0LDMwLDE2LDMwWm0wLDExYzAtMC41NTIsMC40NDgtMSwxLTFzMSwwLjQ0OCwxLDF2MTBDMTcsMjEuNTUzLDE2LjU1MiwyMiwxNiwyMnoiLz48L2c+PGc+PHBhdGggZD0iTTIxLDE3SDExYy0wLjU1MiwwLTEtMC40NDgtMS0xczAuNDQ4LTEsMS0xaDEwYzAuNTUzLDAsMSwwLjQ0OCwxLDFTMjEuNTUzLDE3LDIxLDE3eiIvPjwvZz48L3N2Zz4=");
            background-color: #4099de;
            border-radius: 5px;
            border: 1px solid #4099de;
            text-align: center;
            min-width: 100px;
            height: 35px;
            line-height: 35px;
            font-size: 15px;
            color: #ffffff;
            padding: 0 15px;
            -webkit-transition: all 0.2s ease-in-out;
            transition: all 0.2s ease-in-out;
        &:hover {background-color: #0071C9;}
      }
      &.add_pushed_success_zatca_button {
            // background-image: url("data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiA/PjwhRE9DVFlQRSBzdmcgIFBVQkxJQyAnLS8vVzNDLy9EVEQgU1ZHIDEuMS8vRU4nICAnaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkJz48c3ZnIGVuYWJsZS1iYWNrZ3JvdW5kPSJuZXcgMCAwIDMyIDMyIiBoZWlnaHQ9IjMycHgiIGlkPSJMYXllcl8xIiB2ZXJzaW9uPSIxLjEiIHZpZXdCb3g9IjAgMCAzMiAzMiIgd2lkdGg9IjMycHgiIHhtbDpzcGFjZT0icHJlc2VydmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiPjxnPjxwb2x5bGluZSBmaWxsPSJub25lIiBwb2ludHM9IiAgIDY0OSwxMzcuOTk5IDY3NSwxMzcuOTk5IDY3NSwxNTUuOTk5IDY2MSwxNTUuOTk5ICAiIHN0cm9rZT0iI0ZGRkZGRiIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIiBzdHJva2UtbGluZWpvaW49InJvdW5kIiBzdHJva2UtbWl0ZXJsaW1pdD0iMTAiIHN0cm9rZS13aWR0aD0iMiIvPjxwb2x5bGluZSBmaWxsPSJub25lIiBwb2ludHM9IiAgIDY1MywxNTUuOTk5IDY0OSwxNTUuOTk5IDY0OSwxNDEuOTk5ICAiIHN0cm9rZT0iI0ZGRkZGRiIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIiBzdHJva2UtbGluZWpvaW49InJvdW5kIiBzdHJva2UtbWl0ZXJsaW1pdD0iMTAiIHN0cm9rZS13aWR0aD0iMiIvPjxwb2x5bGluZSBmaWxsPSJub25lIiBwb2ludHM9IiAgIDY2MSwxNTYgNjUzLDE2MiA2NTMsMTU2ICAiIHN0cm9rZT0iI0ZGRkZGRiIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIiBzdHJva2UtbGluZWpvaW49InJvdW5kIiBzdHJva2UtbWl0ZXJsaW1pdD0iMTAiIHN0cm9rZS13aWR0aD0iMiIvPjwvZz48Zz48cGF0aCBkPSJNMTYsMzBjLTMuNzQsMC03LjI1NS0xLjQ1Ni05Ljg5OS00LjEwMUMxLjc3OSwyMS41NzgsMC43NTMsMTUuMDI1LDMuNTQ3LDkuNTk1QzMuOCw5LjEwNCw0LjQwMiw4LjkxMSw0Ljg5NCw5LjE2MyAgIGMwLjQ5MSwwLjI1MiwwLjY4NSwwLjg1NSwwLjQzMiwxLjM0N0MyLjkzMSwxNS4xNjUsMy44MSwyMC43ODEsNy41MTUsMjQuNDg1QzkuNzgxLDI2Ljc1MiwxMi43OTQsMjgsMTYsMjggICBjMy4yMDUsMCw2LjIxOS0xLjI0OCw4LjQ4NS0zLjUxNVMyOCwxOS4yMDUsMjgsMTZjMC0zLjIwNi0xLjI0OC02LjIxOS0zLjUxNS04LjQ4NVMxOS4yMDYsNCwxNiw0ICAgYy0zLjIwNiwwLTYuMjE5LDEuMjQ5LTguNDg1LDMuNTE1Yy0wLjM5MSwwLjM5MS0xLjAyMywwLjM5MS0xLjQxNCwwcy0wLjM5MS0xLjAyMywwLTEuNDE0QzguNzQ1LDMuNDU3LDEyLjI2LDIsMTYsMiAgIGMzLjc0LDAsNy4yNTYsMS40NTcsOS44OTksNC4xMDFDMjguNTQ0LDguNzQ1LDMwLDEyLjI2LDMwLDE2YzAsMy43MzktMS40NTYsNy4yNTUtNC4xMDEsOS44OTlDMjMuMjU2LDI4LjU0NCwxOS43NCwzMCwxNiwzMHoiLz48L2c+PGc+PHBhdGggZD0iTTE2LDIyYy0wLjU1MiwwLTEtMC40NDctMS0xVjExYzAtMC41NTIsMC40NDgtMSwxLTFzMSwwLjQ0OCwxLDF2MTBDMTcsMjEuNTUzLDE2LjU1MiwyMiwxNiwyMnoiLz48L2c+PGc+PHBhdGggZD0iTTIxLDE3SDExYy0wLjU1MiwwLTEtMC40NDgtMS0xczAuNDQ4LTEsMS0xaDEwYzAuNTUzLDAsMSwwLjQ0OCwxLDFTMjEuNTUzLDE3LDIxLDE3eiIvPjwvZz48L3N2Zz4=");
            background-color: #42b883;
            border-radius: 5px;
            border: 1px solid #42b883;
            text-align: center;
            min-width: 100px;
            height: 35px;
            line-height: 35px;
            font-size: 15px;
            color: #ffffff;
            padding: 0 15px;
            -webkit-transition: all 0.2s ease-in-out;
            transition: all 0.2s ease-in-out;
        &:hover {background-color: #42b883;}
      } /* whatsapp_button */
    } /* a */
    button {
      display: block;
      height: 35px;
      width: 35px;
      border-radius: 5px;
      background-position: center center;
      background-size: 20px;
      background-repeat: no-repeat;
      margin: 5px;
      cursor: pointer;
      &.edit_button {
        background-image: url("data:image/svg+xml,%3C%3Fxml version='1.0'%3F%3E%3Csvg xmlns='http://www.w3.org/2000/svg' height='512px' viewBox='0 0 401.52289 401' width='512px'%3E%3Cg%3E%3Cpath d='m370.589844 250.972656c-5.523438 0-10 4.476563-10 10v88.789063c-.019532 16.5625-13.4375 29.984375-30 30h-280.589844c-16.5625-.015625-29.980469-13.4375-30-30v-260.589844c.019531-16.558594 13.4375-29.980469 30-30h88.789062c5.523438 0 10-4.476563 10-10 0-5.519531-4.476562-10-10-10h-88.789062c-27.601562.03125-49.96875 22.398437-50 50v260.59375c.03125 27.601563 22.398438 49.96875 50 50h280.589844c27.601562-.03125 49.96875-22.398437 50-50v-88.792969c0-5.523437-4.476563-10-10-10zm0 0' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%23FFFFFF'/%3E%3Cpath d='m376.628906 13.441406c-17.574218-17.574218-46.066406-17.574218-63.640625 0l-178.40625 178.40625c-1.222656 1.222656-2.105469 2.738282-2.566406 4.402344l-23.460937 84.699219c-.964844 3.472656.015624 7.191406 2.5625 9.742187 2.550781 2.546875 6.269531 3.527344 9.742187 2.566406l84.699219-23.464843c1.664062-.460938 3.179687-1.34375 4.402344-2.566407l178.402343-178.410156c17.546875-17.585937 17.546875-46.054687 0-63.640625zm-220.257812 184.90625 146.011718-146.015625 47.089844 47.089844-146.015625 146.015625zm-9.40625 18.875 37.621094 37.625-52.039063 14.417969zm227.257812-142.546875-10.605468 10.605469-47.09375-47.09375 10.609374-10.605469c9.761719-9.761719 25.589844-9.761719 35.351563 0l11.738281 11.734375c9.746094 9.773438 9.746094 25.589844 0 35.359375zm0 0' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%23FFFFFF'/%3E%3C/g%3E%3C/svg%3E%0A");
        background-color: #45668e;
        &:hover {background-color: #36577F;}
      } /* trash_button */
      &.trash_button {
        background-image: url("data:image/svg+xml,%3C%3Fxml version='1.0'%3F%3E%3Csvg xmlns='http://www.w3.org/2000/svg' height='512px' viewBox='-40 0 427 427.00131' width='512px'%3E%3Cg%3E%3Cpath d='m232.398438 154.703125c-5.523438 0-10 4.476563-10 10v189c0 5.519531 4.476562 10 10 10 5.523437 0 10-4.480469 10-10v-189c0-5.523437-4.476563-10-10-10zm0 0' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%23FFFFFF'/%3E%3Cpath d='m114.398438 154.703125c-5.523438 0-10 4.476563-10 10v189c0 5.519531 4.476562 10 10 10 5.523437 0 10-4.480469 10-10v-189c0-5.523437-4.476563-10-10-10zm0 0' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%23FFFFFF'/%3E%3Cpath d='m28.398438 127.121094v246.378906c0 14.5625 5.339843 28.238281 14.667968 38.050781 9.285156 9.839844 22.207032 15.425781 35.730469 15.449219h189.203125c13.527344-.023438 26.449219-5.609375 35.730469-15.449219 9.328125-9.8125 14.667969-23.488281 14.667969-38.050781v-246.378906c18.542968-4.921875 30.558593-22.835938 28.078124-41.863282-2.484374-19.023437-18.691406-33.253906-37.878906-33.257812h-51.199218v-12.5c.058593-10.511719-4.097657-20.605469-11.539063-28.03125-7.441406-7.421875-17.550781-11.554687-28.0625-11.46875h-88.796875c-10.511719-.0859375-20.621094 4.046875-28.0625 11.46875-7.441406 7.425781-11.597656 17.519531-11.539062 28.03125v12.5h-51.199219c-19.1875.003906-35.394531 14.234375-37.878907 33.257812-2.480468 19.027344 9.535157 36.941407 28.078126 41.863282zm239.601562 279.878906h-189.203125c-17.097656 0-30.398437-14.6875-30.398437-33.5v-245.5h250v245.5c0 18.8125-13.300782 33.5-30.398438 33.5zm-158.601562-367.5c-.066407-5.207031 1.980468-10.21875 5.675781-13.894531 3.691406-3.675781 8.714843-5.695313 13.925781-5.605469h88.796875c5.210937-.089844 10.234375 1.929688 13.925781 5.605469 3.695313 3.671875 5.742188 8.6875 5.675782 13.894531v12.5h-128zm-71.199219 32.5h270.398437c9.941406 0 18 8.058594 18 18s-8.058594 18-18 18h-270.398437c-9.941407 0-18-8.058594-18-18s8.058593-18 18-18zm0 0' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%23FFFFFF'/%3E%3Cpath d='m173.398438 154.703125c-5.523438 0-10 4.476563-10 10v189c0 5.519531 4.476562 10 10 10 5.523437 0 10-4.480469 10-10v-189c0-5.523437-4.476563-10-10-10zm0 0' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%23FFFFFF'/%3E%3C/g%3E%3C/svg%3E%0A");
        background-color: #222222;
        &:hover {background-color: #333333;}
      } /* trash_button */
      &.sms_button {
        outline: none;
        background-image: url("data:image/svg+xml,%3C%3Fxml version='1.0' encoding='UTF-8'%3F%3E%3Csvg xmlns='http://www.w3.org/2000/svg' id='Layer_1' data-name='Layer 1' viewBox='0 0 24 24' width='512' height='512'%3E%3Cpath d='m13.5,10.5c0,.828-.672,1.5-1.5,1.5s-1.5-.672-1.5-1.5.672-1.5,1.5-1.5,1.5.672,1.5,1.5Zm3.5-1.5c-.828,0-1.5.672-1.5,1.5s.672,1.5,1.5,1.5,1.5-.672,1.5-1.5-.672-1.5-1.5-1.5Zm-10,0c-.828,0-1.5.672-1.5,1.5s.672,1.5,1.5,1.5,1.5-.672,1.5-1.5-.672-1.5-1.5-1.5Zm17-5v12c0,2.206-1.794,4-4,4h-2.852l-3.848,3.18c-.361.322-.824.484-1.292.484-.476,0-.955-.168-1.337-.507l-3.749-3.157h-2.923c-2.206,0-4-1.794-4-4V4C0,1.794,1.794,0,4,0h16c2.206,0,4,1.794,4,4Zm-2,0c0-1.103-.897-2-2-2H4c-1.103,0-2,.897-2,2v12c0,1.103.897,2,2,2h3.288c.235,0,.464.083.645.235l4.048,3.41,4.171-3.416c.179-.148.404-.229.637-.229h3.212c1.103,0,2-.897,2-2V4Z'/%3E%3C/svg%3E%0A");
        background-color: #dcdcdc;
        &:hover {background-color: #b1aaaa;}
      } /* sms_button */
      &.email_button {
        outline: none;
        background-image: url("data:image/svg+xml,%3C%3Fxml version='1.0' encoding='UTF-8'%3F%3E%3Csvg xmlns='http://www.w3.org/2000/svg' id='Outline' viewBox='0 0 24 24' width='512' height='512'%3E%3Cpath d='M19,1H5A5.006,5.006,0,0,0,0,6V18a5.006,5.006,0,0,0,5,5H19a5.006,5.006,0,0,0,5-5V6A5.006,5.006,0,0,0,19,1ZM5,3H19a3,3,0,0,1,2.78,1.887l-7.658,7.659a3.007,3.007,0,0,1-4.244,0L2.22,4.887A3,3,0,0,1,5,3ZM19,21H5a3,3,0,0,1-3-3V7.5L8.464,13.96a5.007,5.007,0,0,0,7.072,0L22,7.5V18A3,3,0,0,1,19,21Z'/%3E%3C/svg%3E%0A");
        background-color: #dcdcdc;
        &:hover {background-color: #b1aaaa;}
      } /* email_button */
    } /* button */
  } /* share_button_reservation */

  .embed_area {
    overflow: auto;
    height: 550px;
    embed {
      width: 100%;
      height: 100%;
      max-height: 500px;
    } /* embed */
  } /* embed_area */

  .payment_link_modal {
    .sweet-modal {
      width: 90%;
    }
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
    .cols {
      display: flex;
      align-items: flex-start;
      justify-content: space-between;
      margin: 0 -10px;
      @media (min-width: 320px) and (max-width: 480px) {
        flex-wrap: wrap;
        margin: 0 auto;
      } /* media */
      .col {
        width: 50%;
        padding: 0 10px;
        margin: 0 0 10px;
        @media (min-width: 320px) and (max-width: 480px) {
          width: 100%;
          padding: 0;
        } /* media */
        label {
          display: block;
          margin: 0 auto 5px;
          font-size: 15px;
        } /* label */
        input {
          height: 40px !important;
          padding: 0 10px !important;
          color: #000 !important;
          font-size: 15px !important;
          border: 1px solid #dddddd !important;
          background: #fafafa !important;
          width: 100% !important;
          outline: none !important;
          -webkit-appearance: none;
          -moz-appearance: none;
          -o-appearance: none;
          appearance: none;
          &[disabled="disabled"] {
            cursor: not-allowed;
            background: #f5f5f5 !important;
            font-weight: bold;
          } /* disabled */
        } /* input */
        select {
          background-image: url("data:image/svg+xml,%3C%3Fxml version='1.0' encoding='iso-8859-1'%3F%3E%3C!-- Generator: Adobe Illustrator 19.0.0, SVG Export Plug-In . SVG Version: 6.00 Build 0) --%3E%3Csvg version='1.1' id='Layer_1' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' x='0px' y='0px' viewBox='0 0 491.996 491.996' style='enable-background:new 0 0 491.996 491.996;' xml:space='preserve'%3E%3Cg%3E%3Cg%3E%3Cpath d='M484.132,124.986l-16.116-16.228c-5.072-5.068-11.82-7.86-19.032-7.86c-7.208,0-13.964,2.792-19.036,7.86l-183.84,183.848 L62.056,108.554c-5.064-5.068-11.82-7.856-19.028-7.856s-13.968,2.788-19.036,7.856l-16.12,16.128 c-10.496,10.488-10.496,27.572,0,38.06l219.136,219.924c5.064,5.064,11.812,8.632,19.084,8.632h0.084 c7.212,0,13.96-3.572,19.024-8.632l218.932-219.328c5.072-5.064,7.856-12.016,7.864-19.224 C491.996,136.902,489.204,130.046,484.132,124.986z'/%3E%3C/g%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3C/svg%3E%0A");
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
        .phone {
          display: flex;
          align-items: center;
          justify-content: flex-end;
          flex-wrap: nowrap;
          input {
            flex: 1 1 0;
            border-radius: 0 4px 4px 0 !important;
            direction: ltr !important;
            text-align: left !important;
          } /* input */
          span {
            display: block;
            min-width: 60px;
            direction: ltr;
            height: 40px;
            border-radius: 4px 0 0 4px;
            text-align: center;
            line-height: 40px;
            background: #f5f5f5;
            border: 1px solid #ddd;
            margin: 0 -1px 0 0;
            color: #000;
          } /* span */
        } /* phone */
      } /* col */
    } /* cols */
    hr {
      display: block;
      margin: 5px auto;
      border-color: #ddd;
    } /* hr */
    div#Discount_Values {
      width: 50%;
      float: right;
      @media (min-width: 320px) and (max-width: 480px) {
        width: auto;
        float: none;
      } /* media */
    } /* Discount_Values */
    div#Tax_Values {
      width: 50%;
      float: left;
      @media (min-width: 320px) and (max-width: 480px) {
        width: 100%;
        float: none;
      } /* media */
    } /* Tax_Values */
    .name {
      display: block;
      margin: 15px auto;
      font-size: 20px;
      color: #000;
    } /* name */
    .switch_cols {
      display: flex;
      align-items: flex-start;
      justify-content: space-between;
      margin: 0 -10px;
      .col {
        width: 50%;
        padding: 0 10px;
        margin: 0 0 10px;
        label.custom-switch-input {
          position: relative;
          display: block;
          padding: 0 70px 0 0;
          font-size: 15px;
          color: #000;
          height: 26px;
          line-height: 26px;
          @media (min-width: 320px) and (max-width: 480px) {
            padding: 31px 0 0 0;
            margin: 0 auto 25px;
          } /* media */
          cursor: pointer;
          input {
            position: absolute;
            left: 0;
            right: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            top: 0;
            cursor: pointer;
            &:checked ~ {
              .checkmark {
                background: #21b978;
                &::after {
                  -webkit-transform: translate3d(37px, 3px, 0);
                  -moz-transform: translate3d(37px, 3px, 0);
                  -ms-transform: translate3d(37px, 3px, 0);
                  -o-transform: translate3d(37px, 3px, 0);
                  transform: translate3d(37px, 3px, 0);
                } /* after */
              } /* checkmark */
            } /* checked */
          } /* input */
          .checkmark {
            position: absolute;
            right: 0;
            height: 26px;
            width: 60px;
            background: #bacad6;
            border-radius: 13px;
            top: 0;
            &::after {
              content: "";
              position: absolute;
              left: 0;
              top: 0;
              width: 20px;
              height: 20px;
              border-radius: 100%;
              background: #fff;
              -webkit-transition: all 0.2s ease-in-out;
              -moz-transition: all 0.2s ease-in-out;
              -o-transition: all 0.2s ease-in-out;
              transition: all 0.2s ease-in-out;
              -webkit-transform: translate3d(3px,3px,0);
              -moz-transform: translate3d(3px,3px,0);
              -o-transform: translate3d(3px,3px,0);
              transform: translate3d(3px,3px,0);
            } /* after */
          } /* checkmark */
        } /* custom-switch-input */
      } /* col */
    } /* switch_cols */
    .item {
      margin: 0 auto 10px;
      label {
        display: block;
        margin: 0 auto 5px;
        font-size: 15px;
      } /* label */
      input {
        height: 40px !important;
        padding: 0 10px !important;
        color: #000 !important;
        font-size: 15px !important;
        border: 1px solid #dddddd !important;
        background: #fafafa !important;
        width: 100% !important;
        outline: none !important;
        -webkit-appearance: none;
        -moz-appearance: none;
        -o-appearance: none;
        appearance: none;
        &[disabled="disabled"] {
          cursor: not-allowed;
          background: #f5f5f5 !important;
          font-weight: bold;
        } /* disabled */
      } /* input */
    } /* item */
    .add_payment_link_button {
      height: 35px;
      background: #4099de;
      width: 100%;
      border-radius: 5px;
      font-size: 15px;
      color: #fff;
      display: block;
      text-align: center;
      line-height: 35px;
      cursor: pointer;
      outline: none;
      &:hover {
        background: #0071C9;
      } /* hover */
    } /* add_payment_link_button */
  } /* payment_link_modal */

.reservation_checkedin, .reservation_checkedout {
    border-radius: 5px;
    overflow: hidden;
    color: #fff;
    margin: 10px auto;
    display: table;
    width: 100%;
}
.reservation_checkedin span, .reservation_checkedout span {
    display: table-cell;
    height: 100%;
    width: 50px;
    text-align: center;
    vertical-align: middle;
}
.reservation_checkedin span img, .reservation_checkedout span img {
    height: 30px;
    width: 30px;
    margin: 0 auto;
    display: block;
}
.reservation_checkedin p, .reservation_checkedout p {
    display: table-cell;
    font-family: Dubai-Regular;
    font-size: 16px;
    padding: 5px;
    vertical-align: middle;
    line-height: 23px;
    text-align: center;
}
.reservation_checkedin p i, .reservation_checkedout p i {
    display: block;
    font-style: normal;
    margin: 0 auto;
    direction: ltr;
}
/* Portrait phones and smaller */
@media (min-width: 320px) and (max-width: 480px) {
  .reservation_checkedin p, .reservation_checkedout p {
    font-size: 15px;
    line-height: 20px;
  }
}
/* Small Screens */
@media (min-width: 768px) and (max-width: 991px) {
  .reservation_checkedin, .reservation_checkedout {width: 49%;float: right;}
}

.copy_btn{
    border: 1px solid #000;
    height: 40px !important;
    border-radius: 4px;
    width: 100%;
    &:hover {
      background-color: #ededed;
    }
}

.reservation_service_label {
  display: block;
  border-radius: 100px;
  padding: 0 15px;
  min-width: 60px;
  font-size: 14px;
  height: 20px;
  line-height: 18px;
  font-weight: normal;
  border-width: 1px;
  border-style: solid;

  color: #155724;
  background-color: #d4edda;
  border-color: #c3e6cb;
  margin: 3px;
}
.d-inline-flex{
  display: inline-flex !important;
}
.action-group-wrapper {
  position: relative; /* anchor for absolute dropdown */
  display: inline-block;
}

.main-btn {
  background: #007bff;
  color: white;
  border: none;
  padding: 10px 16px;
  border-radius: 6px;
}

.action-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background: rgba(0, 0, 0, 0.5); /* Dimming */
  z-index: 998;
}

.action-dropdown {
  position: absolute;
  top: 100%;
  right: 0;
  margin-top: 8px;
  padding: 12px;
  background: white;
  border-radius: 10px;
  box-shadow: 0 4px 16px rgba(0,0,0,0.1);
  z-index: 999;
  display: flex;
  align-items: center;
  gap: 10px;
  direction: rtl;
  min-width: 340px;
}


.action-btn {
  width: 100%;               /* Fill each grid cell */
  padding: 10px;
  text-align: center;
  background-color: #007bff;
  color: white;
  border: none;
  border-radius: 6px;
  font-size: 14px;
  white-space: nowrap;
  box-sizing: border-box;

}

.action-btn.danger {
  background: #dc3545;
}

.fade-enter-active, .fade-leave-active {
  transition: opacity 0.2s ease;
}
.fade-enter, .fade-leave-to {
  opacity: 0;
}


.action-btn.danger {
  background-color: #dc3545;
}

.gap-8 {
  gap: 8px; /* space between icon and text */
}

.button-text {
  display: inline-block;
}

.button-icon {
  display: inline-block;
  font-size: 18px;
  line-height: 1;
}

.management-btn {
  background-color: #9b59b6 !important;
  color: #fff;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1) !important;
}

.management-btn:hover {
  background-color: #884ea0 !important;
  transform: translateY(-1px) !important;
}

.management-btn:active {
  background-color: #7d3c98 !important;
  transform: scale(0.98) !important;
}

.management-btn:focus {
  outline: none !important;
  box-shadow: 0 0 0 3px rgba(155, 89, 182, 0.4);
}

html[dir="rtl"] .action-dropdown {
  direction: rtl;
  right: 0;
  left: auto;
}

html[dir="ltr"] .action-dropdown {
  direction: ltr;
  left: 0;
  right: auto;
}


</style>
