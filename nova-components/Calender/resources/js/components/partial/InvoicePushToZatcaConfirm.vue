<template>
    <sweet-modal
        :enable-mobile-fullscreen="false"
        :pulse-on-block="true"
        :hide-close-button="true"
        overlay-theme="dark"
        ref="confirmPushToZatcaModal"
        class="delete_confirm_modal"
    >
        <div class="delete_confirm_modal_content">
            <loading :active.sync="isLoading" :is-full-page="false"></loading>
            <h1>{{ __("Are you sure to push this invoice to zatca ?") }}</h1>
            <div class="buttons_delete">
                <button
                    id="confirm-delete-button"
                    @click="pushToZatca"
                    class="yes_create_button"
                >
                    {{ __("Yes, Push") }}
                </button>
                <button
                    type="button"
                    @click="stepBack()"
                    class="back_delete_button"
                >
                    {{ __("Step Back") }}
                </button>
            </div>
        </div>
    </sweet-modal>
</template>

<script>
import Loading from "vue-loading-overlay";
// Import stylesheet
import "vue-loading-overlay/dist/vue-loading.css";
export default {
    name: "InvoicePushToZatcaConfirm",
    components: { Loading },
    props: ["invoices", "invoiceType", "invoiceSubType"],
    data() {
        return {
            isLoading: false,
        };
    },
    methods: {
        pushToZatca() {
            this.isLoading = true;
            axios
                .post(
                    `/nova-vendor/calender/reservation/post-invoice-to-zatca/${this.invoices[0].id}/${this.invoiceType}/${this.invoiceSubType}/1`
                )
                .then((response) => {
                    this.isLoading = false;
                    if (
                        response.data.reportingStatus == "REPORTED" ||
                        response.data.clearanceStatus == "CLEARED"
                    ) {
                        this.$toasted.show(
                            this.__(
                                "Invoice has been sent to zatca successfully"
                            ),
                            {
                                duration: 5000,
                                type: "success",
                                position: "top-center",
                            }
                        );
                        this.$refs.confirmPushToZatcaModal.close();
                        Nova.$emit("update");
                        Nova.$emit("pushed-to-zatca");
                    } else {
                        this.handleResError(response);
                        this.$refs.confirmPushToZatcaModal.close();
                        Nova.$emit("update");
                    }
                })
                .catch((error) => {
                    this.isLoading = false;
                    this.$toasted.error(error.message, {
                        duration: 3000,
                    });
                });
        },
        stepBack() {
            this.$refs.confirmPushToZatcaModal.close();
        },
        handleResError(response) {
            this.$toasted.show(this.__("Invoice has not been sent to zatca"), {
                duration: 5000,
                type: "error",
                position: "top-center",
            });
            if (response.data.validationResults.errorMessages.length > 0) {
                response.data.validationResults.errorMessages.forEach(
                    (error) => {
                        this.$toasted.show(error.message, {
                            duration: 5000,
                            type: "error",
                            position: "top-center",
                        });
                    }
                );
            }
        },
    },
};
</script>
