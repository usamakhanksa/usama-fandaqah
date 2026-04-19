<template>
  <sweet-modal :enable-mobile-fullscreen="false" :pulse-on-block="true" :title="__('Invoices')" overlay-theme="dark" ref="modal" class="ReservationInvoices_Modal">
    <div class="input-group mb-5">
      <label for="date_picker">{{ __('Select date range')}} :</label>
        <div class="input_group">
          <div class="vcc-datepicker">
            <vcc-date-picker
              :columns="1"
              v-if="start && end"
              :disabled-dates='disableDates'
              :min-date="start"
              :max-date="end"
              :locale="vcc_local"
              :popover="{ placement: 'bottom', visibility: 'click' }"
              :popoverExpanded="true"
              class='v-date-picker'
              disabled
              is-expanded
              mode='range'
              show-caps
              v-model='selectedDate'
            >
            </vcc-date-picker>
          </div>
          <div class="add_button">
            <button @click="addInvoice" class="bg-blue-500 hover:bg-blue-700 text-white rounded" :disabled="loading">{{ __('Add')}}</button>
          </div>
        </div>
      </div>
      <div class="table_ReservationInvoices_Modal">
        <table>
          <thead>
            <tr>
              <th>#</th>
              <th>{{__('From')}}</th>
              <th>{{__('To')}}</th>
              <th width="15%"></th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="invoice in invoices" :key="invoice.id">
              <td>{{invoice.number}}</td>
              <td>{{invoice.from}}</td>
              <td>{{invoice.to}}</td>
              <td>
                <a href="#" class="appearance-none cursor-pointer text-70 hover:text-primary"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" aria-labelledby="delete" role="presentation" class="fill-current"><path fill-rule="nonzero" d="M6 4V2a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2h5a1 1 0 0 1 0 2h-1v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6H1a1 1 0 1 1 0-2h5zM4 6v12h12V6H4zm8-2V2H8v2h4zM8 8a1 1 0 0 1 1 1v6a1 1 0 0 1-2 0V9a1 1 0 0 1 1-1zm4 0a1 1 0 0 1 1 1v6a1 1 0 0 1-2 0V9a1 1 0 0 1 1-1z"></path></svg></a>
                <a :href="invoice.pdf_url" target="_blank" class="cursor-pointer text-70 hover:text-primary"><svg xmlns="http://www.w3.org/2000/svg" width="22" height="18" viewBox="0 0 22 16" aria-labelledby="view" role="presentation" class="fill-current"><path d="M16.56 13.66a8 8 0 0 1-11.32 0L.3 8.7a1 1 0 0 1 0-1.42l4.95-4.95a8 8 0 0 1 11.32 0l4.95 4.95a1 1 0 0 1 0 1.42l-4.95 4.95-.01.01zm-9.9-1.42a6 6 0 0 0 8.48 0L19.38 8l-4.24-4.24a6 6 0 0 0-8.48 0L2.4 8l4.25 4.24h.01zM10.9 12a4 4 0 1 1 0-8 4 4 0 0 1 0 8zm0-2a2 2 0 1 0 0-4 2 2 0 0 0 0 4z"></path></svg></a>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </sweet-modal>
</template>

<script>
    export default {
        name: "Invoices",
        props: {
            invoices: Array,
            start: null,
            reservation_id: null,
            end: null
        },
        data() {
            return {
                loading: false,
                selectedDate: {
                    start: null,
                    end: null
                },
                disableDates: [],
                vcc_local: {
                    id: Nova.config.local,
                    firstDayOfWeek: 1,
                    masks: {
                        weekdays: 'WWW',
                        input: ['WWWW YYYY/MM/DD', 'L'],
                        data: ['WWWW YYYY/MM/DD', 'L'],
                    }
                },
            }
        },
        methods: {
            addInvoice() {
                if (!this.selectedDate.start || !this.selectedDate.end) {
                    this.$toasted.show(this.__('Please select date'), {type: 'error'});
                    return;
                }
                let from_date = moment(String(this.selectedDate.start)).format('YYYY-MM-DD'),
                    to_date = moment(String(this.selectedDate.end)).format('YYYY-MM-DD');
                this.loading = true;
                axios
                    .post('/nova-vendor/calender/reservation/add-invoice', {
                        id: this.reservation_id,
                        from_date: from_date,
                        to_date: to_date,
                    })
                    .then(response => {
                        this.selectedDate = {
                            start: null,
                            end: null
                        };
                        this.invoices = response.data;
                        this.updateDisableDates();
                        this.$toasted.show(this.__('Invoice add successfully'), {type: 'success'});
                        this.loading = false;
                    }).catch(err => {
                    this.loading = false;
                    this.$toasted.show(this.__(err), {type: 'error'})
                })
            },
            updateDisableDates() {
                this.disableDates = this.invoices.map(function (inv) {
                    return {
                        start: new Date(inv.from),
                        end: new Date(inv.to)
                    };
                })
            }
        },
        mounted() {
            this.updateDisableDates();
            let end = new Date(this.end)
            this.end = end.setDate(end.getDate() - 1);
        }
    }
</script>

<style scoped>
.ReservationInvoices_Modal .sweet-modal.theme-light.has-title.has-content.is-visible {overflow: inherit;}
label[for="date_picker"] {
  font-size: 1rem;
  margin: 0 auto 5px;
  display: block;
}
.input_group {
  display: flex;
  justify-content: space-between;
}
.input_group .vcc-datepicker {
  width: 80%;
}
.input_group .add_button {
  width: 18%;
}
.input_group .add_button button {
  height: 40px;
  width: 100%;
  padding: 0;
  line-height: 40px;
  font-size: 17px;
  font-family: Dubai-Medium;
}
.table_ReservationInvoices_Modal {
  width: 100%;
  overflow: auto;
  max-height: 500px;
}
.table_ReservationInvoices_Modal table {
  border: 1px solid #e2e8f0;
  width: 100%;
}
.table_ReservationInvoices_Modal table thead tr th {
  padding:  5px;
  line-height: normal;
  font-weight: normal;
  font-size: 15px;
  border: 1px solid #5E697C;
  vertical-align: middle;
  text-align: center;
  color: #ffffff;
  background: #4a5568;
}
.table_ReservationInvoices_Modal table tbody tr {background: #fff;}
.table_ReservationInvoices_Modal table tbody tr td {
  text-align: center;
  padding: 5px;
  vertical-align: middle;
  line-height: normal;
  font-size: 15px;
  border: 1px solid #ced4dc;
  color: #000000;
  font-weight: normal;
  background: #ffffff;
}
.table_ReservationInvoices_Modal table tbody tr td a {
  display: inline-block;
  margin: 5px;
}
</style>
