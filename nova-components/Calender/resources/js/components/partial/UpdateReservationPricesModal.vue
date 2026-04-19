<template>
    <sweet-modal :enable-mobile-fullscreen="false"  :pulse-on-block="false" :hide-close-button="true" overlay-theme="dark" ref="updateReservationPricesModal" class="delete_confirm_modal">
    <div class="delete_confirm_modal_content">
      <loading :active.sync="isLoading" :is-full-page="false" />
      <h1>{{__('are you sure ?')}}</h1>
      <h2>{{__('This action will change the specified price on targetted dates, would you like to continue ?')}}</h2>
      <div class="buttons_delete">
        <button id="confirm-delete-button" @click="submit()" class="yes_delete_button">{{__('Yes, confirm !')}}</button>
        <button type="button" @click="stepBack()" class="back_delete_button"> {{__('Do not retreat !')}}</button>
      </div>
    </div>
  </sweet-modal>
</template>

<script>
    export default {
        name: "updateReservationPricesModal",
        methods: {
            stepBack(){
                this.$refs.updateReservationPricesModal.close();
            },
            submit() {
                Nova.$emit('update-reservation-prices');
                this.$refs.updateReservationPricesModal.close();
            }
        }
    }
</script>