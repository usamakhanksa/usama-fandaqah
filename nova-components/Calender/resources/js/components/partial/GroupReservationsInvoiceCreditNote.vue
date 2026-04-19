<template>
    <sweet-modal
        v-if="reservation && invoice && credit_note"
        :enable-mobile-fullscreen="false"
        :pulse-on-block="false"
        width="70%"
        :title="__('Credit Note')"
        overlay-theme="dark"
        ref="groupInvoiceCreditNoteModal"
        class="credit_note_modal"
    >
        <div
            class="share_button_reservation"
            v-if="reservation.reservation_type"
        >
            <social-sharing
                :url="`${base_url}/home/group_reservation/invoice-credit-note-public?cnid=${credit_note.hash_id}&locale=${locale}`"
                inline-template
            >
                <network network="whatsapp">
                    <a href="#" class="whatsapp_button"></a>
                </network>
            </social-sharing>
            <a
                v-permission = "'print invoices'"
                class="print_button"
                :href="`/home/group_reservation/invoice-credit-note?cnid=${credit_note.hash_id}&locale=${locale}`"
                target="_blank"
            ></a>
            <!-- <a v-if="ifIntegrationEnabled('ZatcaPhaseTwo')" class="add_credit_note_button zatca_button" @click="openShowPushToZatcaModal">{{__('Push Invoice To Zatca')}}</a>
        <a v-if="invoice.is_reported_to_zatca !== null" class="add_pushed_success_zatca_button zatca_button">{{__('Synced with Zatca')}}</a> -->
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
                :src="`/home/group_reservation/invoice-credit-note?cnid=${credit_note.hash_id}&locale=${locale}&type=embed`"
            ></iframe>
        </div>
        <!-- embed_area -->
    </sweet-modal>
</template>

<script>
import InvoicePushToZatcaConfirm from "./InvoicePushToZatcaConfirm";
import InvoiceAttachments from "./InvoiceAttachments.vue";

export default {
    name: "group-reservations-invoice-credit-note",
    components: { InvoicePushToZatcaConfirm, InvoiceAttachments },
    props: ["invoice", "credit_note", "reservation", "locale"],
    data() {
        return {
            base_url: null,
        };
    },
    methods: {
        refreshContent() {
            $("#invoiceCreditNote").attr("src", function (i, val) {
                return val;
            });
        },
        handleLoading($loading) {
            this.isLoading = $loading;
        },
    },
    mounted() {
        this.local = Nova.config.local;
        this.base_url = window.location.origin;
        Nova.$on("open-credit-note-modal-from-invoice-list", (obj) => {});
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
