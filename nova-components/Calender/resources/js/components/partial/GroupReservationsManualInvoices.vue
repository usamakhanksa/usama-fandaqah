<template>
    <div class="group_invoices">
        <loading :active.sync="isLoading" :is-full-page="false"></loading>
        <div class="title">{{ __("Invoices") }}</div>
        <div class="content">
            <!-- Invoice Listing -->
            <div class="all_invoices_items" v-if="invoices.length">
                <div
                    class="invoice_item"
                    v-for="(invoice, i) in invoices.slice(0, 4)"
                    :key="i"
                >
                    <div
                        class="col_right"
                        style="cursor: pointer"
                        @click="invoiceDivClicked(invoice, i)"
                    >
                        <span
                            >{{ __("Invoice Number") }} :
                            <p>{{ invoice.number }}</p></span
                        >
                        <span
                            >{{ __("Amount") }} :
                            <p class="d-inline-flex">
                                {{ invoice.data.amount.toFixed(2) }}
                                <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span>
                            </p></span
                        >
                    </div>
                    <!-- col_right -->
                    <div class="col_left">
                        <time>{{ invoice.created_at | formatDate }}</time>
                        <div class="name">
                            <p
                                style="cursor: pointer"
                                v-if="invoice.invoice_credit_note"
                                @click="openCreditNoteModalDirect(invoice)"
                            >
                                {{ __("Credit Note") }}
                            </p>
                            <label
                                style="cursor: pointer"
                                @click="invoiceDivClicked(invoice, i)"
                                >{{ __("invoice") }}</label
                            >
                        </div>
                    </div>
                    <!-- col_left -->
                </div>
                <!-- invoice_item -->
                <div
                    v-if="invoices.length > 4"
                    class="more_invoice"
                    @click="openGroupReservationsInvoicesListModal"
                ></div>
            </div>
            <!-- all_invoices_items -->
            <!-- No invoices found -->
            <div v-if="!invoices.length" class="no_invoices">
                <div>
                    <div class="icon"></div>
                    <span>{{ __("No Invoices Found") }}</span>
                </div>
            </div>
            <!-- no_invoices -->
            <!-- Create new invoices call to action button -->
            <div class="block_bottom">
                <button v-if="(!reservation.checked_out || occ) && reservation.status == 'confirmed'" :disabled="!can_add_invoice" class="add_invoice" @click="openAddInvoiceModal">
                    {{ __("Add Invoice") }}
                </button>
                <button
                    v-if="invoices.length > 4"
                    class="more"
                    @click="openGroupReservationsInvoicesListModal"
                >
                    {{ __("more") }} ({{ invoices.length }}) ..
                </button>
            </div>
            <!-- block_bottom -->
        </div>
        <!-- content -->

        <!-- Create new Invoice Modal -->
        <sweet-modal
            :enable-mobile-fullscreen="false"
            :pulse-on-block="true"
            :title="__('Add Invoice')"
            overlay-theme="dark"
            ref="add_manual_group_invoice"
            class="add_invoice_modal"
        >
            <loading
                :active.sync="isAddingInvoice"
                :is-full-page="false"
            ></loading>
            <div class="inputs_row">
                <div class="col">
                    <label>{{ __("Date From") }} <span>*</span></label>
                    <vcc-date-picker
                        :input-props="{
                            class: 'readonly',
                            readonly: true,
                        }"
                        mode="single"
                        show-caps
                        :value="dateFrom"
                        v-model="dateFrom"
                        :locale="locale"
                        :min-date="dateFrom"
                        :max-date="dateFrom"
                        :masks="{
                            dayPopover: 'WWW, MMM D, YYYY',
                            weekdays: 'WWW',
                        }"
                        :popover="{ placement: 'bottom', visibility: 'none' }"
                        @input="dateChanged"
                    >
                    </vcc-date-picker>
                </div>
                <!-- col -->
                <div class="col">
                    <label>{{ __("Date To") }} <span>*</span></label>
                    <vcc-date-picker
                        :input-props="{ readonly: true }"
                        mode="single"
                        show-caps
                        :value="dateTo"
                        v-model="dateTo"
                        :locale="locale"
                        :min-date="dateFrom"
                        :max-date="maxDate"
                        :masks="{
                            dayPopover: 'WWW, MMM D, YYYY',
                            weekdays: 'WWW',
                        }"
                        :popover="{ placement: 'bottom', visibility: 'click' }"
                        @input="dateChanged"
                    >
                    </vcc-date-picker>
                </div>
                <!-- col -->
                <div class="big_col">
                    <textarea
                        name="notes"
                        id=""
                        cols="30"
                        rows="3"
                        v-model="noteOnInvoice"
                    ></textarea>
                </div>

                <div class="col">
                    <label>{{__('Nights Count')}} : <span>{{ nights }}</span></label>
                </div>
                <div class="col">

                </div>
            </div>
            <!-- inputs_row -->
            <button class="save_button" @click="createInvoice">
                {{ __("Save") }}
            </button>
        </sweet-modal>
        <!-- Invoices Listing -->
        <group-reservations-manual-invoices-list
            ref="groupInvoicesRef"
            :invoices="invoices"
            :reservation="reservation"
            :occ="occ"
        />
        <!-- Invoice Single Display Component -->
        <group-reservations-single-invoice
            ref="openDisplayInvoice"
            v-if="invoice && invoice.is_group_reservation"
            :invoice="invoice"
            :isApplicableToAddCreditNote="isApplicableToAddCreditNote"
            :reservation="reservation"
            :occ="occ"
        />

        <group-reservations-invoice-credit-note
            ref="showDirectCreditNote"
            v-if="targetInvoice"
            :reservation="reservation"
            :invoice="targetInvoice"
            :credit_note="targetInvoice.invoice_credit_note"
            :locale="locale"
        />

        <sweet-modal
            :enable-mobile-fullscreen="false"
            :pulse-on-block="false"
            width="70%"
            :title="__('Invoice')"
            overlay-theme="dark"
            ref="invoiceImageModal"
            class="invoice_modal"
            @open="setIframe"
        >
            <div class="share_button_reservation relative">
                <!-- <loading :active.sync="isLoading" :is-full-page="false"></loading> -->
                <social-sharing
                    :url="invoice.public_url + '?lang=' + locale"
                    inline-template
                >
                    <network network="whatsapp">
                        <a href="#" class="whatsapp_button"></a>
                    </network>
                </social-sharing>
                <a
                    class="pdf_button"
                    :href="invoice.pdf_url"
                    target="_blank"
                ></a>
                <a
                    v-permission="'print invoices'"
                    class="print_button"
                    :href="invoice.print_url"
                    target="_blank"
                ></a>

                <a
                    v-if="ifIntegrationEnabled('ZatcaPhaseTwo')"
                    class="add_credit_note_button"
                    @click="openShowPushToZatcaModal"
                    >{{ __("Push Invoice To Zatca") }}</a
                >

                <a v-if="invoice.invoice_credit_note" class="add_credit_note_button" @click="openShowCreditNoteModal(invoice.invoice_credit_note)">{{__('Credit Note')}}</a>
            </div>
            <!-- share_button_reservation -->
            <div class="embed_area">
                <iframe
                    id="invoiceImageModal"
                    v-if="
                        invoice &&
                        invoice.print_url &&
                        !invoice.is_group_reservation
                    "
                    :src="invoice.print_url + '?type=embed'"
                ></iframe>
            </div>
            <!-- embed_area -->
        </sweet-modal>

        <sweet-modal
            v-if="reservation && invoice && singleInvoiceCreditNote"
            :enable-mobile-fullscreen="false"
            :pulse-on-block="false"
            width="70%"
            :title="__('Credit Note')"
            overlay-theme="dark"
            ref="invoiceCreditNoteImageModal"
            class="invoice_modal"
        >
            <div class="share_button_reservation">
                <social-sharing
                    :url="
                        singleInvoiceCreditNote.public_url + '?lang=' + locale
                    "
                    inline-template
                >
                    <network network="whatsapp">
                        <a href="#" class="whatsapp_button"></a>
                    </network>
                </social-sharing>
                <a
                    v-permission="'print invoices'"
                    class="print_button"
                    :href="
                        singleInvoiceCreditNote.print_url + '?lang=' + locale
                    "
                    target="_blank"
                ></a>
                <a
                    class="add_credit_note_button zatca_button"
                    @click="openShowPushToZatcaModal"
                    >{{ __("Push Invoice To Zatca") }}</a
                >
                <a
                    v-if="invoice.is_reported_to_zatca"
                    class="add_pushed_success_zatca_button zatca_button"
                    >{{ __("Synced with Zatca") }}</a
                >
                <invoice-attachments
                    v-permission="'print zatca invoices'"
                    v-if="invoice.is_reported_to_zatca"
                    :invoiceXML="invoice.is_reported_to_zatca.invoice"
                    :creditNote="null"
                    :invoiceNumber="invoice.number"
                    @loading="handleLoading"
                />
            </div>
            <!-- share_button_reservation -->
            <div class="embed_area">
                <iframe
                    id="invoiceCreditNote"
                    v-if="singleInvoiceCreditNote.print_url"
                    :src="
                        singleInvoiceCreditNote.print_url +
                        `?type=embed&lang=${locale}`
                    "
                ></iframe>
            </div>
            <!-- embed_area -->
        </sweet-modal>

        <invoice-push-to-zatca-confirm
            :invoices="[...invoices]"
            invoiceType="tax invoice"
            invoiceSubType="invoice"
            :locale="local"
            ref="pushToZatca"
        />
    </div>
    <!-- group_invoices -->
</template>

<script>
import GroupReservationsInvoiceCreditNote from "./GroupReservationsInvoiceCreditNote";
import GroupReservationsManualInvoicesList from "./GroupReservationsManualInvoicesList";
import GroupReservationsSingleInvoice from "./GroupReservationsSingleInvoice.vue";
import InvoicePushToZatcaConfirm from "./InvoicePushToZatcaConfirm";
import InvoiceAttachments from "./InvoiceAttachments.vue";
import moment from "moment";
import Loading from "vue-loading-overlay";
import "vue-loading-overlay/dist/vue-loading.css";
export default {
    name: "group-reservations-manual-invoices",
    components: {
        Loading,
        GroupReservationsManualInvoicesList,
        GroupReservationsSingleInvoice,
        GroupReservationsInvoiceCreditNote,
        InvoicePushToZatcaConfirm,
        InvoiceAttachments,
    },
    props: ["reservation", "occ"],
    computed: {},
    data() {
        return {
            isLoading: false,
            locale: Nova.config.local,
            isAddingInvoice: false,
            invoices: [],
            noteOnInvoice: null,
            dateFrom: null,
            dateTo: null,
            maxDate: null,
            main_reservation_id: null,
            isApplicableToAddCreditNote: false,
            holder_invoices: [],
            targetInvoice: null,
            invoice: {},
            overlayIndex: null,
            currentInOrderCreditNoteId: null,
            singleInvoiceCreditNote: null,
            currency: Nova.app.currentTeam.currency,
            integrations: [],
            can_add_invoice : true,
            nights : null
        };
    },
    methods: {
        dateChanged(){
            this.nights = moment(this.dateTo).diff(moment(this.dateFrom),'days') + 1;
        },
        openShowCreditNoteModal(singleTargetCreditNote) {
            this.singleInvoiceCreditNote = singleTargetCreditNote;
            setTimeout(() => {
                this.$refs.invoiceCreditNoteImageModal.open();
            }, 50);
        },
        setIframe() {
            $("#invoiceImageModal").attr("src", function (i, val) {
                return val;
            });
        },
        openCreditNoteModalDirect(invoice) {
            if (!invoice.is_group_reservation) {
                this.singleInvoiceCreditNote = invoice.invoice_credit_note;
                setTimeout(() => {
                    this.$refs.invoiceCreditNoteImageModal.open();
                }, 50);
            } else {
                this.targetInvoice = invoice;
                setTimeout(() => {
                    this.$refs.showDirectCreditNote.$refs.groupInvoiceCreditNoteModal.open();
                }, 50);
            }
        },
        openShowPushToZatcaModal() {
            this.$refs.pushToZatca.$refs.confirmPushToZatcaModal.open();
        },
        openGroupReservationsInvoicesListModal() {
            this.$refs.groupInvoicesRef.$refs.groupInvoicesListModal.open();
        },
        openAddInvoiceModal() {
            this.noteOnInvoice = null;
            if (this.invoices.length) {
                let holder_invoices = _.filter(
                    this.invoices,
                    function (invoice) {
                        return invoice.invoice_credit_note === null;
                    }
                );
                if (holder_invoices.length) {
                    this.currentInOrderCreditNoteId = holder_invoices[0].id;
                    let lastInvoice = holder_invoices[0];
                    let lastInvoiceDate = new Date(moment(lastInvoice.to));
                    let reservationLastDate = new Date(
                        moment(
                            this.reservation.dates_calculations.end_date
                        ).subtract(1, "days")
                    );
                    if (
                        lastInvoiceDate.getTime() >=
                        reservationLastDate.getTime()
                    ) {
                        this.$toasted.show(
                            this.__(
                                "You can't add any more invoices cause there are invoices already added for reservation period"
                            ),
                            {
                                duration: 5000,
                                type: "error",
                                position: "top-center",
                            }
                        );
                        return;
                    }

                    this.dateFrom = new Date(
                        moment(lastInvoice.to).add(1, "days")
                    );
                    this.dateTo = new Date(
                        moment(
                            this.reservation.dates_calculations.end_date
                        ).subtract(1, "days")
                    );
                } else {
                    this.dateFrom = new Date(
                        moment(this.reservation.dates_calculations.start_date)
                    );
                    this.dateTo = new Date(
                        moment(
                            this.reservation.dates_calculations.end_date
                        ).subtract(1, "days")
                    );
                }
            } else {
                this.dateFrom = new Date(
                    moment(this.reservation.dates_calculations.start_date)
                );
                this.dateTo = new Date(
                    moment(
                        this.reservation.dates_calculations.end_date
                    ).subtract(1, "days")
                );
            }
            // Setting max here as the user may update the reservation period
            this.maxDate = this.dateTo;
            this.$refs.add_manual_group_invoice.open();
        },
        closeCreateInvoiceModal() {
            this.$refs.add_manual_group_invoice.close();
        },
        getInvoices() {
            axios
                .get(
                    `/nova-vendor/calender/reservation/${this.main_reservation_id}/get-invoices`
                )
                .then((response) => {
                    this.invoices = response.data;
                    this.invoices = _.orderBy(this.invoices, "number", "desc");
                });
        },
        createInvoice() {
            this.isAddingInvoice = true;
            if (!this.dateTo) {
                this.$toasted.show(this.__("Please Select date"), {
                    duration: 2000,
                    type: "error",
                    position: "top-center",
                });
                this.isAddingInvoice = false;
                return false;
            }
            axios
                .post(
                    "/nova-vendor/calender/reservation/create-group-invoice",
                    {
                        id: this.reservation.id,
                        attachable_id: this.reservation.attachable_id,
                        all_grouped_reservations_ids:
                            this.reservation.all_grouped_reservations_ids,
                        dates_calculations: this.reservation.dates_calculations,
                        from_date: this.dateFrom,
                        to_date: this.dateTo,
                        note: this.noteOnInvoice,
                        company_id: this.reservation.company_id,
                    }
                )
                .then((response) => {
                    this.isAddingInvoice = false;
                    this.invoices.push(response.data.invoice);
                    this.invoices = _.orderBy(this.invoices, "number", "desc");
                    // Sorting invoices based on number
                    // this.getInvoices();
                    this.$parent.reservation.shared_invoices = this.invoices;
                    this.$toasted.show(this.__("Invoice add successfully"), {
                        duration: 2000,
                        type: "success",
                        position: "top-center",
                    });
                    this.closeCreateInvoiceModal();
                    Nova.$emit("group-invoice-added");
                });

            return;
        },
        invoiceDivClicked(invoice, index) {
            if (this.invoices.length) {
                this.holder_invoices = _.filter(
                    this.invoices,
                    function (invoice) {
                        return invoice.invoice_credit_note === null;
                    }
                );

                if (this.holder_invoices.length) {
                    this.currentInOrderCreditNoteId =
                        this.holder_invoices[0].id;
                    let firstPotentialInvoice = this.holder_invoices[0];
                    if (firstPotentialInvoice.id == invoice.id) {
                        this.isApplicableToAddCreditNote = true;
                    } else {
                        this.isApplicableToAddCreditNote = false;
                    }
                } else {
                    this.isApplicableToAddCreditNote = false;
                }
            } else {
                this.isApplicableToAddCreditNote = false;
            }
            this.invoice = invoice;
            this.overlayIndex = index;
            if (!invoice.is_group_reservation) {
                setTimeout(() => {
                    this.$refs.invoiceImageModal.open();
                }, 50);
            } else {
                setTimeout(() => {
                    this.$refs.openDisplayInvoice.$refs.groupReservationsSingleInvoiceModal.open();
                }, 50);
            }
            // this.$refs.groupInvoiceModal.open();
        },
        checkIntegration(key) {
            this.isLoading = true;
            Nova.request()
                .get(`/nova-vendor/settings/integrations/${key}`)
                .then((response) => {
                    this.isLoading = false;
                    this.integrations = [
                        ...this.integrations,
                        {
                            key: key,
                            ...response.data,
                        },
                    ];
                })
                .catch((error) => {
                    this.isLoading = false;
                    this.$toasted.error(error, {
                        duration: 3000,
                    });
                });
        },
        ifIntegrationEnabled(key) {
            return (
                this.integrations.find(
                    (integration) =>
                        integration.key === key &&
                        integration.integration !== null
                ) && this.invoice.is_reported_to_zatca == null
            );
        },
        handleLoading($loading) {
            this.isLoading = $loading;
        },
    },
    mounted() {
        this.nights = moment(this.dateTo).diff(moment(this.dateFrom),'days') + 1 ;
        if (this.reservation) {
            this.main_reservation_id = this.reservation.main_reservation_id;
            this.invoices = this.reservation.shared_invoices;
            if (this.invoices.length) {
                this.holder_invoices = _.filter(
                    this.invoices,
                    function (invoice) {
                        return invoice.invoice_credit_note === null;
                    }
                );

                if (this.holder_invoices.length) {
                    this.currentInOrderCreditNoteId =
                        this.holder_invoices[0].id;
                }
            }
            this.dateFrom = new Date(
                moment(this.reservation.dates_calculations.start_date)
            );
            this.dateTo = new Date(
                moment(this.reservation.dates_calculations.end_date).subtract(
                    1,
                    "days"
                )
            );
            this.maxDate = new Date(
                moment(this.reservation.dates_calculations.end_date)
                    .subtract(1, "days")
                    .format("YYYY/MM/DD")
            );

            Nova.$on("group-invoice-credit-note-added", () => {
                this.getInvoices();
            });

            Nova.$on("group-invoice-added-automatic", () => {
                this.getInvoices();
            });

            Nova.$on("set_invoices_from_history_modal", (invoices) => {
                this.invoices = _.orderBy(invoices, "number", "desc");
            });

            Nova.$on("pushed-to-zatca", () => {
                this.invoice.is_reported_to_zatca = {};
            });

            this.checkIntegration("ZatcaPhaseTwo");

            this.can_add_invoice = Nova.app.$hasPermission('add invoice');
        }
    },
};
</script>

<style lang="scss">
.group_invoices {
    margin: 20px auto;
    padding: 0 10px;
    .title {
        display: block;
        font-size: 20px;
        color: #000;
    } /* title */
    .zatca_button {
        min-width: 170px !important;
    }
    .content {
        width: auto;
        min-width: auto;
        max-width: none;
        background: #ffffff;
        border-radius: 5px;
        margin: 5px auto 0;
        border: 1px solid #ddd;
        padding: 10px;
        box-shadow: 0 2px 4px 0 rgba(0, 0, 0, 0.05);
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
        .all_invoices_items {
            min-height: 310px;
        }
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
                    background: #0071c9;
                    border-color: #0071c9;
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
} /* group_invoices */
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
        &:hover {
            background: #0071c9;
        }
    } /* button */
} /* add_invoice_modal */

.invoices_list_modal {
    .sweet-content {
        max-height: 500px;
        overflow-y: auto;
        display: block;
        scrollbar-width: thin;
        scrollbar-color: #ccc #f5f5f5;
        &::-webkit-scrollbar {
            width: 6px;
        }
        &::-webkit-scrollbar-track {
            background: #f5f5f5;
        }
        &::-webkit-scrollbar-thumb {
            background: #ccc;
        }
        &::-webkit-scrollbar-thumb:window-inactive {
            background: #f5f5f5;
        }
    } /* sweet-content */
    .table_invoices {
        width: 100%;
        overflow: auto;
        margin: 0 auto 10px;
        scrollbar-width: thin;
        scrollbar-color: #ccc #f5f5f5;
        &::-webkit-scrollbar {
            width: 6px;
        }
        &::-webkit-scrollbar-track {
            background: #f5f5f5;
        }
        &::-webkit-scrollbar-thumb {
            background: #ccc;
        }
        &::-webkit-scrollbar-thumb:window-inactive {
            background: #f5f5f5;
        }
        table {
            border: 1px solid #e2e8f0;
            width: 100%;
            th {
                padding: 5px;
                line-height: normal;
                font-weight: normal;
                font-size: 15px;
                border: 1px solid #5e697c;
                vertical-align: middle;
                text-align: center;
                color: #ffffff;
                background: #4a5568;
            } /* th */
            tbody {
                tr {
                    background: #fff;
                    td {
                        text-align: center;
                        padding: 5px;
                        vertical-align: middle;
                        line-height: normal;
                        font-size: 15px;
                        border: 1px solid #ced4dc;
                        color: #000000;
                        font-weight: normal;
                        background: #ffffff;
                        &.actions {
                            display: flex;
                            justify-content: center;
                            align-items: center;

                            a {
                                display: block;
                                height: 20px;
                                width: 20px;
                                background-position: center center;
                                background-size: contain;
                                background-repeat: no-repeat;
                                margin: 5px;
                                cursor: pointer;

                                &.show_credit_note_button {
                                    background-image: url("data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiA/PjxzdmcgZGF0YS1uYW1lPSIxLURvY3VtZW50IiBpZD0iXzEtRG9jdW1lbnQiIHZpZXdCb3g9IjAgMCA0OCA0OCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48dGl0bGUvPjxwYXRoIGQ9Ik00Mi43MSw4LjI5bC04LThBMSwxLDAsMCwwLDM0LDBIOEEzLDMsMCwwLDAsNSwzVjQ1YTMsMywwLDAsMCwzLDNINDBhMywzLDAsMCwwLDMtM1Y5QTEsMSwwLDAsMCw0Mi43MSw4LjI5Wk0zNSwzLjQxLDM5LjU5LDhIMzZhMSwxLDAsMCwxLTEtMVpNNDEsNDVhMSwxLDAsMCwxLTEsMUg4YTEsMSwwLDAsMS0xLTFWM0ExLDEsMCwwLDEsOCwySDMzVjdhMywzLDAsMCwwLDMsM2g1WiIvPjxyZWN0IGhlaWdodD0iMiIgd2lkdGg9IjIwIiB4PSIxNiIgeT0iMTgiLz48cmVjdCBoZWlnaHQ9IjIiIHdpZHRoPSIyNCIgeD0iMTIiIHk9IjI0Ii8+PHJlY3QgaGVpZ2h0PSIyIiB3aWR0aD0iMjQiIHg9IjEyIiB5PSIzMCIvPjxyZWN0IGhlaWdodD0iMiIgd2lkdGg9IjE2IiB4PSIxMiIgeT0iMzYiLz48cmVjdCBoZWlnaHQ9IjIiIHdpZHRoPSIyIiB4PSIzNCIgeT0iMzYiLz48cmVjdCBoZWlnaHQ9IjIiIHdpZHRoPSIyIiB4PSIzMCIgeT0iMzYiLz48L3N2Zz4=");
                                }
                                &.add_credit_note_button {
                                    background-image: url("data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiA/Pjxzdmcgdmlld0JveD0iMCAwIDM2MCA0MTAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGRlZnM+PHN0eWxlPi5jbHMtMXtmaWxsOiNjNWM2ZWY7fS5jbHMtMntmaWxsOiMzODU4Njg7fS5jbHMtM3tmaWxsOiM0MjY1NzI7fS5jbHMtNHtmaWxsOiM2YWUxODQ7fS5jbHMtNXtmaWxsOiNhOGZmOGM7fS5jbHMtNntmaWxsOiMzODlkOWM7fTwvc3R5bGU+PC9kZWZzPjx0aXRsZS8+PGcgZGF0YS1uYW1lPSJMYXllciAyIiBpZD0iTGF5ZXJfMiI+PGcgZGF0YS1uYW1lPSJMYXllciAxIiBpZD0iTGF5ZXJfMS0yIj48cGF0aCBjbGFzcz0iY2xzLTEiIGQ9Ik0yODcsMzk5bDktNmExMywxMywwLDAsMS0xMywxM0gxOUExMywxMywwLDAsMSw2LDM5M1YyMUExMywxMywwLDAsMSwxOSw4SDEzTDM1LjUsMzYzLjVhMTMsMTMsMCwwLDAsMTMsMTNaIi8+PHJlY3QgY2xhc3M9ImNscy0yIiBoZWlnaHQ9IjE0LjAzIiB3aWR0aD0iODQuNzIiIHg9IjY2LjI4IiB5PSI3Mi40MiIvPjxyZWN0IGNsYXNzPSJjbHMtMiIgaGVpZ2h0PSIxNC4wMyIgd2lkdGg9Ijg0LjczIiB4PSI2Ni4yOCIgeT0iMTQ2Ljg4Ii8+PHJlY3QgY2xhc3M9ImNscy0yIiBoZWlnaHQ9IjE0LjAzIiB3aWR0aD0iMTM2LjA3IiB4PSI2Ni4yOCIgeT0iMTA4LjM3Ii8+PHJlY3QgY2xhc3M9ImNscy0yIiBoZWlnaHQ9IjE0LjAzIiB3aWR0aD0iODQuNzIiIHg9IjY2LjI4IiB5PSIyMzcuNDIiLz48cmVjdCBjbGFzcz0iY2xzLTIiIGhlaWdodD0iMTQuMDMiIHdpZHRoPSI4NC43MyIgeD0iNjYuMjgiIHk9IjMxMS44OCIvPjxyZWN0IGNsYXNzPSJjbHMtMiIgaGVpZ2h0PSIxNC4wMyIgd2lkdGg9IjEzNi4wNyIgeD0iNjYuMjgiIHk9IjI3My4zNyIvPjxwYXRoIGNsYXNzPSJjbHMtMyIgZD0iTTI4Myw0MTBIMTlBMTksMTksMCwwLDEsMCwzOTFWMTlBMTksMTksMCwwLDEsMTksMEgyODNhMTksMTksMCwwLDEsMTksMTlWMzkxQTE5LDE5LDAsMCwxLDI4Myw0MTBaTTE5LDEyYTcsNywwLDAsMC03LDdWMzkxYTcsNywwLDAsMCw3LDdIMjgzYTcsNywwLDAsMCw3LTdWMTlhNyw3LDAsMCwwLTctN1oiLz48Y2lyY2xlIGNsYXNzPSJjbHMtNCIgY3g9IjI5NS41IiBjeT0iMjI5LjUiIHI9IjU4LjUiLz48cGF0aCBjbGFzcz0iY2xzLTUiIGQ9Ik0zNDcsMjI2LjVhNTguNTIsNTguNTIsMCwwLDEtNjUsNTguMTQsNTguNSw1OC41LDAsMCwwLDAtMTE2LjI4LDU4LjUyLDU4LjUyLDAsMCwxLDY1LDU4LjE0WiIvPjxwYXRoIGNsYXNzPSJjbHMtNiIgZD0iTTMwNiwyODQuNTJhNTguNSw1OC41LDAsMSwxLDAtMTE2LDU4LjUsNTguNSwwLDAsMCwwLDExNloiLz48cGF0aCBjbGFzcz0iY2xzLTMiIGQ9Ik0yOTUuNSwyOTFBNjQuNSw2NC41LDAsMSwxLDM2MCwyMjYuNSw2NC41Nyw2NC41NywwLDAsMSwyOTUuNSwyOTFabTAtMTE3QTUyLjUsNTIuNSwwLDEsMCwzNDgsMjI2LjUsNTIuNTYsNTIuNTYsMCwwLDAsMjk1LjUsMTc0WiIvPjxwYXRoIGNsYXNzPSJjbHMtMyIgZD0iTTI4NC42MywyMTF2MzcuNzVjMCw5LjY1LDE1LDkuNjcsMTUsMFYyMTFjMC05LjY1LTE1LTkuNjctMTUsMFoiLz48cGF0aCBjbGFzcz0iY2xzLTMiIGQ9Ik0zMTEsMjIyLjM4SDI3My4yNWMtOS42NSwwLTkuNjcsMTUsMCwxNUgzMTFjOS42NSwwLDkuNjctMTUsMC0xNVoiLz48L2c+PC9nPjwvc3ZnPg==");
                                }
                            } /* a */
                        }
                    } /* td */
                } /* tr */
            } /* tbody */
        } /* table */
    } /* table_invoices */
} /* invoices_list_modal */
.d-inline-flex{
  display: inline-flex !important;
}
</style>
