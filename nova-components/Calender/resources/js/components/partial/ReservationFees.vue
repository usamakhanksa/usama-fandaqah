<template>
  <div class="item_reservation_button">
    <button class="main_button fees" v-if="!quick" @click="open">{{__('Add Fees')}}</button>
    <sweet-modal :enable-mobile-fullscreen="false" :pulse-on-block="false" :title="__('Add Cancellation Fees')" overlay-theme="dark" ref="feesModal" class="fees_modal">
      <loading :active.sync="loading" :can-cancel="true" :loader="'spinner'" :color="'#7e7d7f'" :is-full-page="false"></loading>
      <div class="form-group">
        <label>{{__('Cancellation Fees')}}</label>
        <input type="number" class="form-control" v-model="cancellationFees" min="0" step="0.01">
      </div>
      <div class="form-group">
        <label>{{__('No Show Fees')}}</label>
        <input type="number" class="form-control" v-model="noShowFees" min="0" step="0.01">
      </div>
      <button class="add_fees_button" @click="submit">{{__('Add Fees')}}</button>
    </sweet-modal>
  </div>
</template>

<script>
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css';

export default {
    name: "ReservationFees",
    components: {
        Loading
    },
    props: ['quick', 'reservation'],
    data: () => {
        return {
            loading: false,
            cancellationFees: 0,
            noShowFees: 0
        }
    },
    methods: {
        open() {
            this.$refs.feesModal.open();
        },
        submit() {
            this.loading = true;
            axios.post('/nova-vendor/calender/reservation/cancel-fees', {
                reservation_id: this.reservation.id,
                cancellation_fees: this.cancellationFees,
                no_show_fees: this.noShowFees
            })
            .then(response => {
                this.$refs.feesModal.close();
                this.$emit('update-reservation');
                this.$toasted.show(this.__('Fees added successfully'), {type: 'success'});
                this.loading = false;
            })
            .catch(error => {
                this.loading = false;
                this.$toasted.show(this.__('Error adding fees'), {type: 'error'});
            });
        }
    }
}
</script>

<style lang="scss" scoped>
.fees_modal {
  .form-group {
    margin-bottom: 20px;

    label {
      display: block;
      margin-bottom: 8px;
      font-size: 14px;
      font-weight: 500;
      color: #374151;
    }

    input {
      width: 100%;
      padding: 10px 12px;
      border-radius: 8px;
      background: #fff;
      border: 2px solid #E5E7EB;
      font-size: 14px;
      color: #1F2937;
      transition: all 0.2s ease;
      box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);

      &:focus {
        border-color: #3B82F6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        outline: none;
      }

      &:hover {
        border-color: #D1D5DB;
      }
    }
  }

  .add_fees_button {
    height: 40px;
    background: #5e00ff;
    width: 100%;
    border-radius: 8px;
    font-size: 15px;
    font-weight: 500;
    color: #fff;
    transition: all 0.2s ease;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);

    &:hover {
      background: #6D28D9;
      transform: translateY(-1px);
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    &:active {
      transform: translateY(0);
    }
  }
}

.main_button.fees {
  background: #e74444  !important;
  color: #fff;
  border: none;
  padding: 10px 24px;
  border-radius: 8px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 500;
  transition: all 0.2s ease;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);

  &:hover {
  background: #e74444  !important;
    transform: translateY(-1px);
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  }

  &:active {
    transform: translateY(0);
  }
}
</style>
