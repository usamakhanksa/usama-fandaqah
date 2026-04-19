<template>
    <div>
        <sweet-modal
            :enable-mobile-fullscreen="false"
            :pulse-on-block="false"
            width="70%"
            :title="__('Invoice')"
            overlay-theme="dark"
            ref="invoiceModal"
            @open="refreshContent"
            class="invoice_modal"
        >
            <div class="share_button_reservation relative">
                <loading
                    :active.sync="isLoading"
                    :is-full-page="false"
                ></loading>
                <social-sharing
                    :url="invoice.public_url + '?lang=' + local"
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
                <!-- <a class="trash_button" v-if="overlayIndex === 0" @click="deleteInvoice(invoice)"></a> -->

                <a
                    @click="can_add_credit_note ? openConfirmAddCreditNote() : '#'"
                    v-if="
                        !invoice.invoice_credit_note &&
                        isApplicableToAddCreditNote &&
                        (occ || !reservation.checked_out)
                    "
                    class="add_credit_note_button"
                    >{{ __("Credit Note") }}</a
                >
                   <!-- @click="!can_add_credit_note ? '#' : openConfirmAddCreditNote" -->
                <a
                    v-if="invoice.invoice_credit_note"
                    class="add_credit_note_button"
                    @click="openShowCreditNoteModal"
                    >{{ __("Credit Note") }}</a
                >
                <a
                    v-if="ifIntegrationEnabled('ZatcaPhaseTwo')"
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

                <sms-component
                :entity_id="reservation.id"
                :document_url="invoice.public_url"
                :document_type="'invoice'"
                :sms_base_title="sms_base_title"
                />

            </div>
            <!-- share_button_reservation -->
            <div class="embed_area">
                <iframe
                    id="manualInvoice"
                    v-if="
                        invoice.print_url &&
                        reservation.reservation_type == 'group'
                    "
                    :src="invoice.print_url + '&type=embed'"
                ></iframe>
                <iframe
                    id="manualInvoice"
                    v-if="
                        invoice.print_url &&
                        reservation.reservation_type == 'single'
                    "
                    :src="invoice.print_url + '?type=embed'"
                ></iframe>
                <!--        <embed :src="invoice.pdf_url" type="application/pdf">-->
            </div>
            <!-- embed_area -->
        </sweet-modal>
        <invoice-delete-confirm
            :invoice="invoice"
            ref="deleteButton"
            :ref-parent="'invoiceModal'"
        />
        <invoice-credit-note-confirm
            :invoice="invoice"
            ref="addCreditNoteButton"
        />
        <invoice-credit-note
            :reservation="reservation"
            :invoice="invoice"
            :credit_note="invoice.invoice_credit_note"
            :locale="local"
            ref="creditNote"
        />
        <invoice-push-to-zatca-confirm
            v-if="ifIntegrationEnabled('ZatcaPhaseTwo')"
            :invoices="[...invoice]"
            invoiceType="simplified tax invoice"
            invoiceSubType="invoice"
            :locale="local"
            ref="pushToZatca"
        />
    </div>
</template>

<script>
import SmsComponent from "../SmsComponent";
import InvoiceCreditNote from "./InvoiceCreditNote";
import InvoiceCreditNoteConfirm from "./InvoiceCreditNoteConfirm";
import InvoiceDeleteConfirm from "./InvoiceDeleteConfirm";
import InvoicePushToZatcaConfirm from "./InvoicePushToZatcaConfirm";
import InvoiceAttachments from "./InvoiceAttachments.vue";
import Loading from "vue-loading-overlay";
import "vue-loading-overlay/dist/vue-loading.css";
export default {
    name: "Invoice",
    components: {
        InvoiceDeleteConfirm,
        InvoiceCreditNoteConfirm,
        InvoiceCreditNote,
        InvoicePushToZatcaConfirm,
        InvoiceAttachments,
        Loading,
        SmsComponent
    },
    props: [
        "invoice",
        "overlayIndex",
        "reservation",
        "occ",
        "isApplicableToAddCreditNote",
    ],
    data() {
        return {
            invoice: {},
            reservation: {},
            local: null,
            credit_note: null,
            isLoading: false,
            integrations: [],
            can_add_credit_note : Nova.app.$hasPermission('add credit note'),
            sms_base_title : null
        };
    },
    methods: {
        deleteInvoice(invoice) {
            this.invoice = invoice;
            this.$refs.deleteButton.$refs.deleteInvoiceModal.open();
        },
        openShowCreditNoteModal() {
            this.$refs.creditNote.$refs.invoiceCreditNoteModal.open();
        },
        openShowPushToZatcaModal() {
            this.$refs.pushToZatca.$refs.confirmPushToZatcaModal.open();
        },
        checkCreditNote() {
            axios
                .get(
                    `/nova-vendor/calender/reservation/invoice/${this.invoice.id}/credit-note`
                )
                .then((response) => {
                    if (response.data.success) {
                        this.credit_note = response.data.invoiceCreditNote;
                    } else {
                        this.credit_note = null;
                    }
                    this.isLoading = false;
                });
        },
        refreshContent() {
            this.sms_base_title = this.reservation && this.reservation.vat_total ? Nova.app.__('Simple Tax Invoice') : Nova.app.__('Invoice');
            // this.isLoading = true;
            // setTimeout(() => {
            //     this.checkCreditNote();
            // }, 0);
            $("#manualInvoice").attr("src", function (i, val) {
                return val;
            });
        },
        openConfirmAddCreditNote() {
            this.$refs.addCreditNoteButton.$refs.confirmCreditNoteModal.open();
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
        }
    },
    mounted() {
        this.checkIntegration("ZatcaPhaseTwo");
        this.local = Nova.config.local;
        Nova.$on("reservation_checked_out", (reservation) => {
            this.reservation = reservation;
        });

        Nova.$on("credit-note-added", () => {
            this.$refs.invoiceModal.close();
        });

        Nova.$on("pushed-to-zatca", () => {
            this.invoice.is_reported_to_zatca = {};
        });
    },
};
</script>

<style lang="scss">

.invoice_modal {
    .zatca_button {
        min-width: 170px !important;
    }
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
        @media (min-width: 320px) and (max-width: 480px) {
            display: none !important;
        } /* @media */
        iframe {
            width: 100%;
            height: 100%;
            min-height: 500px;
        } /* iframe */
    } /* embed_area */
} /* contract_modal */

</style>
