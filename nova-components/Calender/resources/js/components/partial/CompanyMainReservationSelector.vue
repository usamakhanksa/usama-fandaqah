<template>
  <div class="item_reservation_button">
    <div class="label_with_button mb-2">
        <label class="custom_company_label">{{__('Main Reservation')}}</label>
        <button v-if="!selectedReservation" @click="open">{{__('Select Main Reservation')}}</button>
        <svg v-else @click="clearSelectedReservation" width="30" height="30" enable-background="new 0 0 32 32" id="Editable-line" version="1.1" viewBox="0 0 32 32" xml:space="preserve" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><path d="  M25,10H7v17c0,1.105,0.895,2,2,2h14c1.105,0,2-0.895,2-2V10z" fill="none" id="XMLID_194_" stroke="#000000" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" stroke-width="2"/><path d="  M20,7h-8V5c0-1.105,0.895-2,2-2h4c1.105,0,2,0.895,2,2V7z" fill="none" id="XMLID_193_" stroke="#000000" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" stroke-width="2"/><path d="  M28,10H4V8c0-0.552,0.448-1,1-1h22c0.552,0,1,0.448,1,1V10z" fill="none" id="XMLID_192_" stroke="#000000" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" stroke-width="2"/><line fill="none" id="XMLID_191_" stroke="#000000" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" stroke-width="2" x1="13" x2="19" y1="16" y2="22"/><line fill="none" id="XMLID_190_" stroke="#000000" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" stroke-width="2" x1="13" x2="19" y1="22" y2="16"/></svg>
    </div>

     <div v-if="selectedReservation" class="selected_reservation_alert mt-2">
        <p><b>{{__('Selected Main Reservation Info')}} </b></p>
        <p>{{__('Reservation Number')}} : {{selectedReservation.reservation_number}}</p>
        <p>{{__('Rent Type')}} : {{selectedReservation.rent_type == 1 ? __('Daily') : __('Monthly')}}</p>
        <p>{{__('From')}} : {{selectedReservation.date_in}} -  {{__('To')}} : {{selectedReservation.date_out}}</p>
    </div>

    <sweet-modal  :enable-mobile-fullscreen="false" :pulse-on-block="false" :title="__('Select Main Reservation')" overlay-theme="dark" ref="mainReservationSelector" class="link_reservations">
      <div id="link_reservations_div">

        <div class="title">
            {{__('Reservations')}}
        </div>

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
                            <th>{{ __('Unit Number') }}</th>
                            <th>{{ __('Customer Name') }}</th>
                            <th>{{ __('Rent Type') }}</th>
                            <th>{{ __('Reservation Status') }}</th>
                            <th>{{__('Select')}}</th>
                        </tr>
                        </thead>
                        <tbody>
                            <template v-if="attachable_reservations.length">
                                <tr v-for="(reservation,i) in attachable_reservations" :key="i">
                                    <td>{{reservation.reservation_number}}</td>
                                    <td>{{reservation.unit_number}}</td>
                                    <td>{{reservation.customer_name}}</td>
                                    <td>{{ reservation.rent_type == 1 ? __('Daily') : __('Monthly')}}</td>
                                    <td>
                                        <div v-if="!reservation.checked_in" class="pending">{{__('Pending')}}</div>
                                        <div v-if="reservation.checked_in && !reservation.checked_out" class="checked_in">{{__('Checked in')}}</div>
                                        <div v-if="reservation.checked_out" class="checked_out">{{__('Checked out')}}</div>
                                    </td>
                                    <td>
                                        <svg @click="selectReservation(reservation)" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 256" width="256" height="256" xml:space="preserve"><g><g style="stroke:none;stroke-width:0;stroke-dasharray:none;stroke-linecap:butt;stroke-linejoin:miter;stroke-miterlimit:10;fill:none;fill-rule:nonzero;opacity:1"><path d="M71.656 37.966h-.503a7.131 7.131 0 0 0-3.282.795c-.575-3.389-3.532-5.978-7.082-5.978h-.503a7.144 7.144 0 0 0-3.637.99 7.195 7.195 0 0 0-6.729-4.668h-.502a7.144 7.144 0 0 0-3.183.744v-11.03c0-3.96-3.222-7.183-7.183-7.183h-.502c-3.96 0-7.183 3.222-7.183 7.183v27.414l-3.479 2.641a12.041 12.041 0 0 0-4.71 8.205 12.043 12.043 0 0 0 2.612 9.094l10.604 13.109v5.273a5.45 5.45 0 0 0 5.444 5.444h26.334a5.45 5.45 0 0 0 5.444-5.444l.001-5.009a23.7 23.7 0 0 0 5.218-14.809v-19.59c.004-3.959-3.218-7.181-7.179-7.181zm3.183 26.772c0 4.674-1.683 9.215-4.738 12.787-.311.362-.48.823-.48 1.3v5.73c0 .797-.647 1.444-1.444 1.444H41.841a1.445 1.445 0 0 1-1.444-1.444v-5.981a2 2 0 0 0-.445-1.258l-11.05-13.659a8.072 8.072 0 0 1-1.751-6.097 8.074 8.074 0 0 1 3.157-5.501l1.06-.804v6.684a2 2 0 0 0 4 0V18.819a3.187 3.187 0 0 1 3.183-3.183h.502a3.187 3.187 0 0 1 3.183 3.183V36.29l.011 14.531a2 2 0 0 0 2 1.998h.001a2 2 0 0 0 1.999-2.002l-.011-14.528a3.187 3.187 0 0 1 3.183-3.182h.502a3.187 3.187 0 0 1 3.183 3.182V39.969l.011 10.853a2 2 0 0 0 2 1.998h.002a2 2 0 0 0 1.998-2.002l-.011-10.851a3.187 3.187 0 0 1 3.183-3.182h.503a3.187 3.187 0 0 1 3.183 3.182v4.602c-.008.072-.022.143-.021.217l.011 6.037a2 2 0 0 0 2 1.996h.004a2 2 0 0 0 1.996-2.004l-.01-5.463c.007-.068.02-.133.02-.203a3.187 3.187 0 0 1 3.182-3.183h.503a3.187 3.187 0 0 1 3.183 3.183v19.589z" style="stroke:none;stroke-width:1;stroke-dasharray:none;stroke-linecap:butt;stroke-linejoin:miter;stroke-miterlimit:10;fill:#000;fill-rule:nonzero;opacity:1" transform="translate(1.964 1.964) scale(2.8008)"/><path d="M25.719 26.934a2 2 0 0 1-1.7-.944 16.968 16.968 0 0 1-2.56-8.979C21.459 7.631 29.09 0 38.47 0c9.38 0 17.011 7.631 17.011 17.011 0 2.438-.505 4.792-1.502 6.998a2 2 0 1 1-3.645-1.646 12.911 12.911 0 0 0 1.146-5.351C51.481 9.837 45.645 4 38.47 4S25.459 9.837 25.459 17.011c0 2.436.676 4.81 1.956 6.865a1.998 1.998 0 0 1-.641 2.754 1.981 1.981 0 0 1-1.055.304zM61.144 28.176a2 2 0 0 1-1.702-3.047c1.529-2.491 2.338-5.461 2.338-8.588 0-3.105-.8-6.06-2.312-8.544a2 2 0 1 1 3.416-2.081c1.895 3.111 2.896 6.785 2.896 10.625 0 3.866-1.013 7.56-2.93 10.681a2 2 0 0 1-1.706.954zM15.801 28.176a1.998 1.998 0 0 1-1.706-.954c-1.919-3.125-2.934-6.819-2.934-10.681 0-3.839 1.001-7.513 2.896-10.625a2 2 0 1 1 3.416 2.08c-1.513 2.485-2.313 5.439-2.313 8.544 0 3.123.81 6.092 2.342 8.588a1.998 1.998 0 0 1-.658 2.75 1.958 1.958 0 0 1-1.043.298z" style="stroke:none;stroke-width:1;stroke-dasharray:none;stroke-linecap:butt;stroke-linejoin:miter;stroke-miterlimit:10;fill:#000;fill-rule:nonzero;opacity:1" transform="translate(1.964 1.964) scale(2.8008)"/></g></g></svg>
                                    </td>
                                </tr>
                            </template>
                            <template v-else>
                                <tr>
                                    <td colspan="18">{{__('No Reservations Found')}}</td>
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
</template>

<script>

    import Loading from 'vue-loading-overlay';
    import 'vue-loading-overlay/dist/vue-loading.css';
    export default {
        name: "company-main-reservation-selector",
        components : {
            Loading,
        },
        props : ['attachable_reservations'],
        data: () => {
            return {
                loading: false,
                selectedReservation : null
            }
        },

        methods: {
            open(){
                this.$refs.mainReservationSelector.open();
            },
            clearSelectedReservation(){
                this.selectedReservation = null;
                 Nova.$emit('attachable_reservation' , this.selectedReservation);
            },
            selectReservation(reservation){
                this.selectedReservation = reservation;
                Nova.$emit('attachable_reservation' , this.selectedReservation);
                this.$refs.mainReservationSelector.close();
            }
        },


    }
</script>
<style lang="scss" scoped>
 button {
    background: #4099de;
    border-radius: 5px;
    border: 1px solid #4099de;
    min-width: 33.3333%;
    height: 35px;
    line-height: 35px;
    font-size: 15px;
    padding: 0 15px;
    color: #ffffff;
    -webkit-transition: all 0.2s ease-in-out;
    -moz-transition: all 0.2s ease-in-out;
    -o-transition: all 0.2s ease-in-out;
    transition: all 0.2s ease-in-out;
    @media (min-width: 320px) and (max-width: 480px) {
        min-width: 100%;
        width: 100%;
    }
    /* Mobile */
    @media (min-width: 481px) and (max-width: 767px) {
        min-width: 100%;
        width: 100%;
    }
    /* Mobile */
    @media (min-width: 768px) and (max-width: 991px) {
        min-width: 50%;
        width: 50%;
    }
    /* Mobile */
    &:hover {
        background: #0071C9;
        border-color: #0071C9;
    }


    /* hover */
} /* button */

.selected_reservation_alert{
    background: #fff3cd;
    border: 1px solid #ffeeba;
    padding: 10px;
    border-radius: 4px;
    font-size: 15px;
    color: #856404;
    margin: 0 auto 15px;
}
.label_with_button {
                display: flex;
                align-items: center;
                justify-content: space-between;
                    label.custom_company_label {
                        font-size: 14px;
                        font-weight: 500;
                        color: #000;
                    }
                     button {
                        min-width: 130px;
                        padding: 0 10px;
                        width: auto;
                        max-width: none;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                    }
                   svg{
                       cursor: pointer;
                   }
            }
.link_reservations {

 span {
    padding: 30px 20px;
    line-height: normal;
    display: block;
    font-size: 20px;
    color: #000;
  } /* span */
  button.confirm_button {
    height: 35px;
    background: #4599dd;
    width: 100%;
    border-radius: 5px;
    font-size: 15px;
    color: #fff;
    &:hover {
        background: #0071C9;
        border-color: #0071C9;
    }
  } /* confirm_button */
} /* add_comment_modal */

 #link_reservations_div {
        margin: 0 auto;
        border: 1px solid #ddd;
        border-radius: .5rem;
        box-shadow: 0 2px 4px 0 rgba(0, 0, 0, 0.05);
        overflow: hidden;
        .title{
            background: #f7fafc;
            border-bottom: 1px solid #ddd;
            padding: .75rem;
            color: #000;
            font-size: 1.125rem;
            display: block;

            p{
                font-size: 15px;
            }
        } /* title */
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
    } /* reservations_customer */
</style>

<style lang="scss" scoped>

  .text-timeout{
        color: #a07930;
    }

    .text-awaiting {
         --text-opacity: 1;
        color: rgba(66,153,225,var(--text-opacity));
    }
    .green{
        --text-opacity: 1;
        color: rgba(72,187,120,var(--text-opacity));
    }

    .red {
        --text-opacity: 1;
        color: rgba(245,101,101,var(--text-opacity));
    }

    .pending {
        --text-opacity: 1;
        color: rgba(66,153,225,var(--text-opacity));
    }
    .checked_in {
        --text-opacity: 1;
        color: rgba(72,187,120,var(--text-opacity));
    }
    .checked_out {
        --text-opacity: 1;
        color: rgba(245,101,101,var(--text-opacity));
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


  .group_reservations_tbl {
        overflow: auto;
        width: 100%;
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
</style>
