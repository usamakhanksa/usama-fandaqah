<template>
    <!-- Save Button -->
    <div class="flex flex justify-center gap-2" v-if="this.transaction">
        <!--Disclaimer: Active Note remains null when zatca phase 2 is not enabled So Update action can be performed-->
        <button
            v-if="
                this.transaction.active_note == null ||
                this.transaction.active_note == 'credit note'
            "
            :disabled="disableUpdate"
            class="addbut shadow mb-2 btn btn-default btn-block btn-primary mt-2"
            @click="updateServices"
        >
            {{ __("Update") }}
            <span
                v-if="spinnerLoadOnEdit"
                class="spinner spinner-light"
                :class="[locale === 'ar' ? 'mr-2' : 'ml-2']"
            ></span>
        </button>
        <button
            v-if="
                this.transaction.active_note == 'invoice' ||
                this.transaction.active_note == 'debit note'
            "
            :disabled="disableUpdateCreditNoteBtn"
            class="addbut shadow mb-2 btn btn-default btn-block btn-primary mt-2"
            @click="issueCreditNote"
        >
            {{ __("Credit Note") }}
        </button>

        <div v-if="zatcaEnabled && transaction.payable_type !== PAYABLE_RESERVATION ">
            <button
                v-if="ifNotIssuedInvoiceZatca()"
                :disabled="disableZatca"
                class="addbut shadow mb-2 btn btn-default btn-block btn-primary mt-2"
                @click="pushToZatca"
            >
                {{ __("Push Invoice To Zatca") }}
                <span
                    v-if="spinnerLoadOnEditZatca"
                    class="spinner spinner-light"
                    :class="[locale === 'ar' ? 'mr-2' : 'ml-2']"
                ></span>
            </button>

            <button
                v-if="ifIssuedInvoiceZatca()"
                :disabled="true"
                class="addbut shadow mb-2 btn btn-default btn-block btn-success mt-2"
            >
                {{ __("Synced with Zatca") }}
                <span
                    v-if="spinnerLoadOnEditZatca"
                    class="spinner spinner-light"
                    :class="[locale === 'ar' ? 'mr-2' : 'ml-2']"
                ></span>
            </button>
        </div>
    </div>
</template>
<script>
export default {
    name: "IntegrationInputActions",
    props: [
        "disableUpdate",
        "updateServices",
        "spinnerLoadOnEdit",
        "spinnerLoadOnEditZatca",
        "locale",
        "zatcaEnabled",
        "pushToZatca",
        "handleModal",
        "handleLoading",
        "transaction",
    ],
    data: () => {
        return {
            disableUpdateCreditNoteBtn: false,
            PAYABLE_RESERVATION: "App\\Reservation"
        };
    },
    methods: {
        ifIssuedInvoiceZatca() {
            return (
                this.transaction.active_note == "invoice" ||
                this.transaction.active_note == "debit note" ||
                this.transaction.active_note == "credit note"
            );
        },
        ifNotIssuedInvoiceZatca() {
            return this.transaction.active_note == null;
        },
        issueCreditNote() {
            this.disableUpdateCreditNoteBtn = true;
            //relaunch parent:loader
            this.handleLoading(true);
            const params = {
                transaction_id: this.transaction.id,
            };
            Nova.request()
                .put("/nova-vendor/pos/service-log/create-credit-note", params)
                .then((response) => {
                    this.disableUpdateCreditNoteBtn = false;
                    //reload table data
                    Nova.$emit("service-transaction-updated", true);
                    //closes current modal
                    this.handleModal(false);
                    this.handleLoading(false);
                })
                .catch((err) => {
                    this.disableUpdateCreditNoteBtn = false;
                    //reload table data
                    Nova.$emit("service-transaction-updated", true);
                    //closes current modal
                    this.handleModal(false);
                    this.handleLoading(false);
                });
        },
        stepBack() {
            this.$refs.confirmCreditNoteModal.close();
        },
        issueCreditNoteModalOpen() {
            Nova.$emit("open-credit-note-confirm-modal", this.transaction);
        }
    },
};
</script>
<style scoped>
button.addbut {
    max-width: 100%;
    min-width: 50%;
    display: inline-block;
    width: auto;
    height: 40px;
    line-height: 40px;
    padding: 0 10px;
    font-size: 16px;
}
.spinner {
    /* Spinner size and color */
    width: 1.5rem;
    height: 1.5rem;
    border-top-color: #444;
    border-left-color: #444;

    /* Additional spinner styles */
    animation: spinner 400ms linear infinite;
    border-bottom-color: transparent;
    border-right-color: transparent;
    border-style: solid;
    border-width: 2px;
    border-radius: 50%;
    box-sizing: border-box;
    display: inline-block;
    vertical-align: middle;
}

/* Animation styles */
@keyframes spinner {
    0% {
        transform: rotate(0deg);
    }
    100% {
        transform: rotate(360deg);
    }
}

/* Optional — create your own variations! */
.spinner-large {
    width: 5rem;
    height: 5rem;
    border-width: 6px;
}

.spinner-slow {
    animation: spinner 1s linear infinite;
}

.spinner-blue {
    border-top-color: #09d;
    border-left-color: #09d;
}
.spinner-light {
    border-top-color: #ffffff;
    border-left-color: #ffffff;
}
</style>
