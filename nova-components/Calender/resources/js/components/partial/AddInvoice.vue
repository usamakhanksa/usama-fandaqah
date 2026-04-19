<template>
  <div class="special_invoice">
    <!-- <loading :active.sync="isLoading" :is-full-page="false"></loading> -->
    <div class="title">{{ __('Invoices') }}</div>
    <div class="content">
      <div v-if="reservation.reservation_type == 'group' && reservation.shared_invoices.length">
             <company-invoices :reservation="reservation" :invoices="reservation.shared_invoices" :total_invoice="reservation.shared_invoice_total_price" :occ="occ" />
      </div>
      <div class="all_invoices_items" v-if="reservation.reservation_type == 'single' && invoices.length">
        <template v-if="invoices.length">
          <div v-for="(invoice , i) in invoices.slice(0,3)" :key="i">
            <div class="invoice_item">
              <div class="col_right" style="cursor:pointer;" @click="invoiceDivClicked(invoice,i)">
                <span>{{__('Invoice Number')}} : <p>{{invoice.number}}</p></span>
                <span>{{__('Date')}} : <p>{{__('From')}} {{ invoice.from | formatDateSpecial }} {{ __('To')}} {{ invoice.to | formatDateSpecial }}</p></span>
                <span>{{__('Amount')}} : <p class="d-inline-flex">{{ (invoice.data.amount).toFixed(2) }}  <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></p></span>
              </div><!-- col_right -->
              <div class="col_left">
                <time>{{invoice.created_at | formatDate}}</time>
                <div class="name">
                    <p style="cursor:pointer;" v-if="invoice.invoice_credit_note" @click="openCreditNoteModalDirect(invoice)">{{__('Credit Note')}}</p>
                    <label style="cursor:pointer;" @click="invoiceDivClicked(invoice,i)">{{__('invoice')}}</label>
                </div>
              </div><!-- col_left -->
            </div><!-- invoice_item -->
          </div>
        </template>
        <div v-if="invoices.length > 3" @click="openInvoicesListModal" class="more_invoice"></div>
      </div><!-- all_invoices_items -->
      <div v-if="reservation.reservation_type == 'group' &&!reservation.shared_invoices.length" class="no_invoices" style="min-height:345px;">
        <div>
          <div class="icon"></div>
          <span>{{__('No Invoices Found')}}</span>
        </div>
      </div><!-- no_invoices -->
      <div v-if="reservation.reservation_type == 'single' &&!invoices.length" class="no_invoices">
        <div>
          <div class="icon"></div>
          <span>{{__('No Invoices Found')}}</span>
        </div>
      </div><!-- no_invoices -->
      <div class="block_bottom" v-if="reservation.reservation_type == 'single'">
        <button :disabled="!can_add_invoice" @click="openAddInvoiceModal" v-if="(!reservation.checked_out || occ) && reservation.status == 'confirmed'" class="add_invoice">{{ __('Add Invoice') }}</button>
        <!-- <button @click="openAddInvoiceModal" v-else class="add_invoice">{{ __('Add Invoice') }}</button> -->
        <button v-if="invoices.length > 3"  @click="openInvoicesListModal" class="more">{{__('more')}} ({{invoices.length}}) ..</button>
      </div><!-- block_bottom -->
    </div><!-- content -->
    <sweet-modal :enable-mobile-fullscreen="false" :pulse-on-block="true" :title="__('Add Invoice')" overlay-theme="dark" ref="addInvoiceModal" class="add_invoice_modal">
      <loading :active.sync="isLoading" :is-full-page="false"></loading>
      <div class="inputs_row">
        <div class="col">
          <label>{{__('Date From')}} <span>*</span></label>
          <vcc-date-picker
          :input-props='{
            class: "readonly",
            readonly: true
          }'
          mode='single'
          :value='dateFrom'
          show-caps
          v-model='dateFrom'
          :locale="locale"
          :min-date="dateFrom"
          :max-date='dateFrom'
          :masks="{ dayPopover : 'WWW, MMM D, YYYY' , weekdays: 'WWW' }"
          :popover="{ placement: 'bottom', visibility: 'none' }"
          @input="dateChanged"
        >
        </vcc-date-picker>
        </div><!-- col -->
        <div class="col">
          <label>{{__('Date To')}} <span>*</span></label>
          <vcc-date-picker
          :input-props='{readonly: true}'
          mode='single'
          show-caps
          :value="dateTo"
          v-model="dateTo"
          :locale="locale"
          :min-date="dateFrom"
          :max-date='maxDate'
          :masks="{ dayPopover : 'WWW, MMM D, YYYY' , weekdays: 'WWW' }"
          :popover="{ placement: 'bottom', visibility: 'click' }"
          @input="dateChanged"
        >
        </vcc-date-picker>
        </div><!-- col -->
        <div class="big_col">
          <textarea name="notes" id="" cols="30" rows="3" v-model="noteOnInvoice"></textarea>
        </div>
        <div class="col">
          <label>{{__('Nights Count')}} : <span>{{ nights }}</span></label>
        </div>
        <div class="col">
         
        </div>
      </div><!-- inputs_row -->
      <button class="save_button" @click="saveInvoice()">{{__('Save')}}</button>
    </sweet-modal>
    <invoices-list  ref="invoiceListRef" />
    <invoice ref="invoiceAddComponent" :occ="occ"  :invoice.sync="invoice" :overlay-index="overlayIndex" :isApplicableToAddCreditNote="isApplicableToAddCreditNote" :reservation="reservation" />
    <invoice-credit-note v-if="targetInvoice" :reservation="reservation" :invoice="targetInvoice" :credit_note="targetInvoice.invoice_credit_note" :locale="locale" ref="creditNote" />

  </div><!-- special_invoice -->
</template>

<script>
    import InvoiceCreditNote from "./InvoiceCreditNote";
    import CompanyInvoices from './CompanyInvoices';
    import InvoicesList from "./InvoicesList";
    import Invoice from "./Invoice";
    import Loading from 'vue-loading-overlay';
    import moment from "moment";
    // Import stylesheet
    import 'vue-loading-overlay/dist/vue-loading.css';
import { disable } from "xhook";

    export default {

        name: "AddInvoice",
        components: {InvoicesList,Invoice,Loading,CompanyInvoices,InvoiceCreditNote},
        props : ['reservation','occ'],
        computed : {
            // max date for the calendar
            maxDate(){
                return moment(this.reservation.date_out).subtract(1 , 'days').format('YYYY/MM/DD');
            },
            minDate(){
                return moment(this.reservation.date_out).format('YYYY/MM/DD');
            },
        },
        data(){
            return {
                dateFrom:  new Date(moment(this.reservation.date_in)),
                dateTo : new Date(moment(this.reservation.date_out).subtract(1,'days')),
                locale: Nova.config.local,
                noteOnInvoice : this.__('Invoice on reservation number : ') + this.reservation.number,
                invoices : [],
                invoice: {},
                isLoading : false,
                overlayIndex : null,
                isApplicableToAddCreditNote : null,
                holder_invoices : [],
                targetInvoice : null,
                currency :Nova.app.currentTeam.currency,
                can_add_invoice : true,
                nights : null
            }
        },
        methods:{
            dateChanged(){
              this.nights = moment(this.dateTo).diff(moment(this.dateFrom),'days') + 1;
            },
            openCreditNoteModalDirect(invoice){
                this.targetInvoice = invoice;
                setTimeout(() => {
                    this.$refs.creditNote.$refs.invoiceCreditNoteModal.open();
                }, 50);
            },
            openAddInvoiceModal(){
                this.noteOnInvoice = this.__('Invoice on reservation number : ') + this.reservation.number;
                if(this.invoices.length){
                    let holder_invoices = _.filter(this.invoices, function(invoice) {
                        return invoice.invoice_credit_note === null;
                    });
    
                    if(holder_invoices.length){
                        let lastInvoice =   holder_invoices[0];
                        let lastInvoiceDate = new Date(moment(lastInvoice.to)) ;
                        let reservationLastDate = new Date(moment(this.reservation.date_out).subtract(1,'days'));
                        if(lastInvoiceDate.getTime() >= reservationLastDate.getTime()){
                            this.$toasted.show(this.__('You can\'t add any more invoices cause there are invoices already added for reservation period'), {
                                duration : 5000,
                                type: 'error',
                                position: "top-center",
                            });
                            return false;
                        }
                        this.dateFrom =  new Date(moment(lastInvoice.to).add(1,'days'));
                        this.dateTo = new Date(moment(this.reservation.date_out).subtract(1,'days'));
                    }else{
                        this.dateFrom = new Date(moment(this.reservation.date_in)),
                        this.dateTo = new Date(moment(this.reservation.date_out).subtract(1,'days'));
                    }

                }else{
                    this.dateFrom = new Date(moment(this.reservation.date_in)),
                    this.dateTo = new Date(moment(this.reservation.date_out).subtract(1,'days'));
                }
                this.$refs.addInvoiceModal.open();
            },
            closeAddInvoiceModal(){
                this.$refs.addInvoiceModal.close();
            },
            saveInvoice(){
                this.isLoading = true;
                if(!this.dateTo){
                    this.$toasted.show(this.__('Please Select date'), {
                        duration : 2000,
                        type: 'error',
                        position: "top-center",
                    });
                    this.isLoading = false;
                    return false;
                }


                axios
                    .post('/nova-vendor/calender/reservation/add-invoice', {
                        id: this.reservation.id,
                        from_date: this.dateFrom,
                        to_date: this.dateTo,
                        note : this.noteOnInvoice
                    })
                    .then(response => {

                        this.invoices.push(response.data);
                        this.invoices = _.orderBy(this.invoices, 'number', 'desc');
                        this.reservation.invoices = this.invoices;
                        this.$toasted.show(this.__('Invoice add successfully'), {
                            duration : 2000,
                            type: 'success',
                            position : 'top-center',
                        });
                        this.closeAddInvoiceModal();
                        this.isLoading = false;

                        // this is important to update services is attached to invoices value cause upon it
                        // a validation is required
                        this.reservation.services = response.data.reservation.services;

                        // Nova.$emit('invoice-added' , this.invoices);

                    }).catch(err => {
                        this.$toasted.show(err, {type: 'error'})
                    });

            },
            openInvoicesListModal(){
                Nova.$emit('open-invoices-list' ,  {'invoices' : this.invoices , 'reservation' : this.reservation });
            },
            invoiceDivClicked(invoice , index){
                if(this.invoices.length){

                    this.holder_invoices = _.filter(this.invoices, function(invoice) {
                        return invoice.invoice_credit_note === null;
                    });

                    if(this.holder_invoices.length){
                        let firstPotentialInvoice = this.holder_invoices[0];
                        if(firstPotentialInvoice.id == invoice.id){
                            this.isApplicableToAddCreditNote = true;
                        }else{
                            this.isApplicableToAddCreditNote = false;
                        }
                    }else{
                        this.isApplicableToAddCreditNote = false;
                    }

                }else{
                    this.isApplicableToAddCreditNote = false;
                }

                this.invoice = invoice ;
                this.overlayIndex = index;
                this.$refs.invoiceAddComponent.$refs.invoiceModal.open();
            },
            getInvoices(){
                axios.get(`/nova-vendor/calender/reservation/${this.reservation.id}/get-invoices`)
                .then((response) => {
                    this.invoices = response.data;
                })
            }

        },
        mounted() {
          this.nights = moment(this.dateTo).diff(moment(this.dateFrom),'days') + 1 ;
            this.invoices = this.reservation.invoices;

            Nova.$on('invoice-deleted' , () => {
                this.isLoading = true;
                axios.get('/nova-vendor/calender/reservationInvoices?id=' + this.reservation.id)
                    .then((res) => {
                        this.invoices = _.orderBy(res.data, 'number', 'desc');
                        this.isLoading = false;
                    })
                    .catch((err) => {
                        console.log(err);
                    })
            })

            // Nova.$on('reservation_checked_out' , (reservation) => {
            //     this.reservation = reservation ;
            //     this.invoices = _.orderBy(this.reservation.invoices, 'number', 'desc');
            // })



            Nova.$on('invoice-added' , (invoices) => {
                this.getInvoices();
                // this.invoices = invoices;
            })
            Nova.$on('credit-note-added' , (invoices) => {
                this.getInvoices();
            })

            Nova.$on('single_reservation_checked_out' , (invoices) => {
                this.invoices = _.orderBy(invoices, 'number', 'desc');
            })

            Nova.$on('set_invoices_from_history_modal' , (invoices) => {
                this.invoices = _.orderBy(invoices, 'number', 'desc');
            })

            this.can_add_invoice = Nova.app.$hasPermission('add invoice');
        }
    }
</script>

<style lang="scss">
.special_invoice {

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
    box-shadow: 0 2px 4px 0 rgba(0,0,0,.05);
    min-height: 296px;
    .no_invoices {
      min-height: 310px;
      text-align: center;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-wrap: wrap;
      .icon {
        background-image: url("data:image/svg+xml,%3C%3Fxml version='1.0'%3F%3E%3Csvg xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' version='1.1' id='Capa_1' x='0px' y='0px' viewBox='0 0 512 512' style='enable-background:new 0 0 512 512;' xml:space='preserve' width='512px' height='512px'%3E%3Cg%3E%3Cg%3E%3Cg%3E%3Cpath d='M474.994,246.001h-74.012v-236c0-4.044-2.437-7.691-6.173-9.239c-3.738-1.549-8.039-0.691-10.898,2.167l-28.329,28.328 L327.255,2.93c-3.905-3.905-10.237-3.905-14.143,0l-28.328,28.328L256.459,2.93c-3.905-3.905-10.237-3.905-14.143,0 l-28.328,28.328L185.659,2.93c-3.905-3.905-10.237-3.905-14.142,0l-28.328,28.328L114.863,2.93 c-3.905-3.905-10.237-3.905-14.143,0L72.392,31.258L44.065,2.93c-3.881-3.881-10.158-3.901-14.073-0.055 c-2.056,2.021-3.053,4.714-2.984,7.389v449.738c0,28.672,23.326,51.998,51.998,51.998h353.976c0.002,0,0.003,0,0.005,0 c0.003,0,0.006,0,0.009,0c28.672,0,51.998-23.326,51.998-51.998V256C484.994,250.477,480.517,246.001,474.994,246.001z M79.006,492c-17.645,0-31.999-14.355-31.999-31.999V34.156L65.321,52.47c3.905,3.905,10.237,3.905,14.143,0l28.328-28.328 L136.12,52.47c3.905,3.905,10.237,3.905,14.143,0l28.328-28.328L206.92,52.47c3.905,3.905,10.237,3.905,14.142,0l28.328-28.328 l28.328,28.328c3.905,3.905,10.237,3.905,14.143,0l28.328-28.328l28.328,28.328c3.906,3.905,10.238,3.905,14.142,0l18.329-18.328 V256c0,0.091,0.011,0.18,0.014,0.271v203.73c0,12.062,4.14,23.168,11.057,31.999H79.006z M464.994,460.001 c0,17.644-14.355,31.999-31.999,31.999s-31.999-14.355-31.999-31.999V266h63.998V460.001z' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%23DDDDDD'/%3E%3C/g%3E%3C/g%3E%3Cg%3E%3Cg%3E%3Cpath d='M213.995,158.855c-9.925,0-17.999-6.914-17.999-15.411c0-8.498,8.075-15.412,17.999-15.412 c9.925,0,17.999,6.914,17.999,15.412c0,5.523,4.477,10,10,10c5.523,0,10-4.477,10-10c0-16.301-11.884-30.055-27.999-34.157v-6.774 c0-5.523-4.477-10-10-10s-10,4.477-10,10v6.774c-16.116,4.102-27.999,17.857-27.999,34.157c0,19.525,17.047,35.41,37.999,35.41 c9.925,0,17.999,6.914,17.999,15.412s-8.074,15.411-17.999,15.411c-9.925,0-17.999-6.914-17.999-15.411c0-5.523-4.477-10-10-10 s-10,4.477-10,10c0,16.301,11.884,30.055,27.999,34.157v8.16c0,5.523,4.477,10,10,10s10-4.477,10-10v-8.16 c16.116-4.102,27.999-17.856,27.999-34.157C251.994,174.741,234.947,158.855,213.995,158.855z' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%23DDDDDD'/%3E%3C/g%3E%3C/g%3E%3Cg%3E%3Cg%3E%3Cpath d='M314.461,158.856h-19.49c-5.523,0-10,4.477-10,10s4.477,10,10,10h19.49c5.523,0,10-4.477,10-10 S319.984,158.856,314.461,158.856z' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%23DDDDDD'/%3E%3C/g%3E%3C/g%3E%3Cg%3E%3Cg%3E%3Cpath d='M133.02,158.856h-19.49c-5.523,0-10,4.477-10,10s4.477,10,10,10h19.49c5.523,0,10-4.477,10-10 S138.543,158.856,133.02,158.856z' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%23DDDDDD'/%3E%3C/g%3E%3C/g%3E%3Cg%3E%3Cg%3E%3Cpath d='M319.52,270.007H161.097c-5.523,0-10,4.477-10,10c0,5.523,4.477,10,10,10H319.52c5.523,0,10-4.477,10-10 C329.519,274.484,325.043,270.007,319.52,270.007z' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%23DDDDDD'/%3E%3C/g%3E%3C/g%3E%3Cg%3E%3Cg%3E%3Cpath d='M115.538,272.937c-1.86-1.86-4.44-2.93-7.07-2.93c-2.63,0-5.21,1.07-7.07,2.93c-1.86,1.86-2.93,4.44-2.93,7.07 c0,2.63,1.07,5.21,2.93,7.07c1.86,1.86,4.44,2.93,7.07,2.93c2.63,0,5.21-1.07,7.07-2.93c1.86-1.86,2.93-4.44,2.93-7.07 C118.468,277.377,117.398,274.797,115.538,272.937z' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%23DDDDDD'/%3E%3C/g%3E%3C/g%3E%3Cg%3E%3Cg%3E%3Cpath d='M319.52,336.005H161.097c-5.523,0-10,4.477-10,10c0,5.523,4.477,10,10,10H319.52c5.523,0,10-4.477,10-10 C329.519,340.482,325.043,336.005,319.52,336.005z' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%23DDDDDD'/%3E%3C/g%3E%3C/g%3E%3Cg%3E%3Cg%3E%3Cpath d='M115.538,338.935c-1.86-1.86-4.44-2.93-7.07-2.93c-2.63,0-5.21,1.07-7.07,2.93c-1.86,1.86-2.93,4.44-2.93,7.07 c0,2.63,1.07,5.21,2.93,7.07c1.86,1.86,4.44,2.93,7.07,2.93c2.63,0,5.21-1.07,7.07-2.93c1.86-1.86,2.93-4.44,2.93-7.07 C118.468,343.375,117.398,340.795,115.538,338.935z' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%23DDDDDD'/%3E%3C/g%3E%3C/g%3E%3Cg%3E%3Cg%3E%3Cpath d='M319.52,402.003H161.097c-5.523,0-10,4.477-10,10s4.477,10,10,10H319.52c5.523,0,10-4.477,10-10 S325.043,402.003,319.52,402.003z' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%23DDDDDD'/%3E%3C/g%3E%3C/g%3E%3Cg%3E%3Cg%3E%3Cpath d='M115.538,404.933c-1.86-1.86-4.44-2.93-7.07-2.93c-2.63,0-5.21,1.07-7.07,2.93c-1.86,1.86-2.93,4.44-2.93,7.07 s1.07,5.21,2.93,7.07c1.86,1.86,4.44,2.93,7.07,2.93c2.63,0,5.21-1.07,7.07-2.93c1.86-1.86,2.93-4.44,2.93-7.07 S117.398,406.793,115.538,404.933z' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%23DDDDDD'/%3E%3C/g%3E%3C/g%3E%3C/g%3E%3C/svg%3E%0A");
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
    } /* no_invoices */
    .all_invoices_items {min-height: 310px;}
    .invoice_item {
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
          width: 100%;
        } /* Mobile */
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
        width: 30%;
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        align-self: stretch;
        @media (min-width: 320px) and (max-width: 767px) {
          margin: 5px 0 0;
          width: 100%;
          align-self: normal;
        } /* Mobile */
        time {
          display: flex;
          justify-content: flex-end;
          width: 100%;
          font-size: 13px;
          color: #777777;
          @media (min-width: 320px) and (max-width: 767px) {
            width: 50%;
            justify-content: flex-start;
          } /* Mobile */
        } /* time */
        .name {
          display: flex;
          align-self: flex-end;
          justify-content: flex-end;
          width: 100%;
          @media (min-width: 320px) and (max-width: 767px) {
            width: 50%;
          } /* Mobile */
         p {
            border-radius: 100px;
            border: 1px solid #dcd56d;
            padding: 0 10px;
            min-width: 50px;
            font-size: 14px;
            height: 20px;
            color: #676523;
            margin: 0 0 0 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #fff56d;
            white-space: nowrap;
        }
          label {
            display: block;
            border-radius: 100px;
            border: 1px solid #777777;
            padding: 0 15px;
            min-width: 60px;
            font-size: 14px;
            height: 20px;
            line-height: 18px;
            color: #777777;
          } /* label */
        } /* name */
      } /* col_left */
    } /* invoice_item */
    .more_invoice {
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
    } /* more_invoice */
    .block_bottom {
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      width: 100%;
      align-self: end;
      button.add_invoice {
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
      } /* add_invoice */
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
} /* special_invoice */
.add_invoice_modal {
  .sweet-modal .sweet-content .sweet-content-content {
    max-height: none !important;
    overflow: visible;
  } /* sweet-content-content */
  .inputs_row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    margin: 0 -15px;
    @media (min-width: 320px) and (max-width: 767px) {
      margin: 0 auto;
    } /* Mobile */
    .col {
      width: 50%;
      padding: 0 15px;
      margin: 0 auto 10px;
      @media (min-width: 320px) and (max-width: 767px) {
        width: 100%;
        padding: 0;
      } /* Mobile */
      label {
        display: block;
        margin: 0 auto 5px;
        font-size: 15px;
        span {
          display: inline-block;
          margin: 0 5px 0 0;
          color: #f00;
        } /* span */
      } /* label */
      input {
        height: 40px;
        padding: 0 10px !important;
        color: #000 !important;
        font-size: 15px !important;
        border: 1px solid #dddddd !important;
        background: #fafafa;
        width: 100%;
        cursor: pointer;
        &.readonly {
          background: #ddd;
          border-color: #c4c4c4 !important;
          cursor: not-allowed;
        } /* readonly */
      } /* input */
    } /* col */
    .big_col {
      margin: 0 auto 10px;
      width: 100%;
      padding: 0 15px;
      @media (min-width: 320px) and (max-width: 767px) {
        padding: 0;
      } /* Mobile */
      textarea {
        width: 100%;
        padding: 10px;
        border-radius: 5px;
        background: #fafafa;
        border: 1px solid #ddd;
        font-size: 15px;
        color: #000;
      } /* textarea */
    } /* big_col */
  } /* inputs_row */
  button.save_button {
    height: 35px;
    background: #4099de;
    width: 100%;
    border-radius: 5px;
    font-size: 15px;
    color: #fff;
    &:hover {background: #0071C9;}
  } /* button */
} /* add_invoice_modal */

.d-inline-flex{
  display: inline-flex !important;
}
</style>
