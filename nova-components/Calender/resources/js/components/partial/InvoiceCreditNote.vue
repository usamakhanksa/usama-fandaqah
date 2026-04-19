<template>
    <div>
        <sweet-modal
            v-if="reservation && invoice && credit_note"
            :enable-mobile-fullscreen="false"
            :pulse-on-block="false"
            width="70%"
            :title="__('Credit Note')"
            overlay-theme="dark"
            ref="invoiceCreditNoteModal"
            class="credit_note_modal"
        >
            <div
                class="share_button_reservation"
                v-if="reservation.reservation_type"
            >
                <loading
                    :active.sync="isLoading"
                    :is-full-page="false"
                ></loading>
                <social-sharing
                    :url="
                        reservation.reservation_type == 'group'
                            ? `${base_url}/home/group_reservation/company_invoice_credit_note/${credit_note.hash_id}?locale=${locale}`
                            : credit_note.public_url + '?lang=' + locale
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
                        reservation.reservation_type == 'group'
                            ? `/home/group_reservation/company_invoice_credit_note/${credit_note.hash_id}?locale=${locale}`
                            : credit_note.print_url + '?lang=' + locale
                    "
                    target="_blank"
                ></a>
                <!-- <a v-if="ifIntegrationEnabled('ZatcaPhaseTwo')" class="add_credit_note_button zatca_button" @click="openShowPushToZatcaModal">{{__('Push Invoice To Zatca')}}</a> -->
                <a
                    v-if="credit_note.is_reported_to_zatca"
                    class="add_pushed_success_zatca_button zatca_button"
                    >{{ __("Synced with Zatca") }}</a
                >
                <invoice-attachments
                    v-permission="'print zatca invoices'"
                    :invoiceXML="
                       credit_note.is_reported_to_zatca != undefined
                       || credit_note.is_reported_to_zatca != null
                        ? credit_note.is_reported_to_zatca.invoice
                         : null
                    "
                    :creditNote="credit_note"
                    :invoiceNumber="invoice.number"
                    @loading="handleLoading"
                />
            </div>
            <!-- share_button_reservation -->
            <div class="embed_area">
                <iframe
                    id="invoiceCreditNote"
                    v-if="credit_note.print_url"
                    :src="
                        reservation.reservation_type == 'group'
                            ? `/home/group_reservation/company_invoice_credit_note/${credit_note.hash_id}?locale=${locale}&type=embed`
                            : credit_note.print_url +
                              `?type=embed&lang=${locale}`
                    "
                ></iframe>
            </div>
            <!-- embed_area -->
        </sweet-modal>
        <!-- <invoice-push-to-zatca-confirm  :invoices="[...invoice]" invoiceType="simplified tax invoice" invoiceSubType="credit note" :locale="local" ref="pushToZatca" /> -->
    </div>
</template>

<script>
import InvoiceDeleteConfirm from "./InvoiceDeleteConfirm";
import InvoiceCreditNoteConfirm from "./InvoiceCreditNoteConfirm";
import InvoicePushToZatcaConfirm from "./InvoicePushToZatcaConfirm";
import Loading from "vue-loading-overlay";
import InvoiceAttachments from "./InvoiceAttachments.vue";

export default {
    name: "invoice-credit-note",
    components: {
        InvoiceDeleteConfirm,
        InvoiceCreditNoteConfirm,
        InvoicePushToZatcaConfirm,
        Loading,
        InvoiceAttachments,
    },
    props: ["invoice", "credit_note", "reservation", "locale"],
    data() {
        return {
            base_url: null,
            integrations: [],
            isLoading: false,
        };
    },
    methods: {
        refreshContent() {
            $("#invoiceCreditNote").attr("src", function (i, val) {
                return val;
            });
        },
        openShowPushToZatcaModal() {
            this.$refs.pushToZatca.$refs.confirmPushToZatcaModal.open();
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
        this.base_url = window.location.origin;
        Nova.$on("open-credit-note-modal-from-invoice-list", (obj) => {});
        this.checkIntegration("ZatcaPhaseTwo");

        Nova.$on("pushed-to-zatca", () => {
            this.invoice.is_reported_to_zatca = {};
        });

        if (this.credit_note != null) {
            this.credit_note = JSON.parse(this.credit_note);
            console.log(credit_note);
        }
    },
};
</script>

<style lang="scss">
.credit_note_modal {
    .sweet-modal {
        @media (min-width: 768px) and (max-width: 991px) {
            width: 95% !important;
        } /* @media */
    } /* sweet-modal */
    .zatca_button {
        min-width: 170px !important;
    }
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
