<template>
    <div class="invoice__attachments" v-if="invoiceXML">
        <a class="flex__center p-2" @click="printAttachment()">
            <svg
                width="20px"
                height="20px"
                viewBox="0 -2 30 30"
                version="1.1"
                xmlns="http://www.w3.org/2000/svg"
                xmlns:xlink="http://www.w3.org/1999/xlink"
                xmlns:sketch="http://www.bohemiancoding.com/sketch/ns"
                fill="#858585"
                stroke="#858585"
            >
                <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                <g
                    id="SVGRepo_tracerCarrier"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke="#CCCCCC"
                    stroke-width="0.6"
                ></g>
                <g id="SVGRepo_iconCarrier">
                    <title>download</title>
                    <desc>Created with Sketch Beta.</desc>
                    <defs></defs>
                    <g
                        id="Page-1"
                        stroke-width="0.00030000000000000003"
                        fill="none"
                        fill-rule="evenodd"
                        sketch:type="MSPage"
                    >
                        <g
                            id="Icon-Set-Filled"
                            sketch:type="MSLayerGroup"
                            transform="translate(-103.000000, -728.000000)"
                            fill="#ababab"
                        >
                            <path
                                d="M132,728 L104,728 C103.521,728 103,728.521 103,729 L103,736 C103,737.104 103.896,738 105,738 C106.104,738 107,737.104 107,736 L107,732 L129,732 L129,736 C129,737.104 129.896,738 131,738 C132.104,738 133,737.104 133,736 L133,729 C133,728.458 132.604,728 132,728 L132,728 Z M122,746 L120,746 L120,737 C120,735.896 119.104,735 118,735 C116.896,735 116,735.896 116,737 L116,746 L114,746 C113.608,746 112.705,745.905 112.313,746.3 C111.921,746.693 111.921,747.332 112.313,747.726 L117.255,753.717 C117.464,753.927 117.742,754.017 118.016,754.002 C118.29,754.017 118.567,753.927 118.776,753.717 L123.718,747.726 C124.11,747.332 124.11,746.693 123.718,746.3 C123.326,745.905 122.705,746 122,746 L122,746 Z"
                                id="download"
                                sketch:type="MSShapeGroup"
                            ></path>
                        </g>
                    </g>
                </g>
            </svg>
        </a>
    </div>
</template>

<script>
import axios from "axios";
import "vue-loading-overlay/dist/vue-loading.css";
export default {
    name: "InvoiceAttachments",
    components: {},
    props: ["invoiceXML", "meta"],
    data() {},
    methods: {
        async printAttachment() {
            try {
                this.$emit("loading", true);
                const postData = {
                    invoiceXML: this.invoiceXML,
                    meta: {
                        ...(this.meta && {
                            credit_note_number: this.meta.credit_note_number
                                ? this.meta.credit_note_number
                                : null,
                            debit_note_number: this.meta.debit_note_number
                                ? this.meta.debit_note_number
                                : null,
                            invoice_type: this.meta.invoice_type
                                ? this.meta.invoice_type
                                : null,
                            invoice_reference_number: this.meta.invoiceNumber
                                ? this.meta.invoiceNumber
                                : null
                        }),
                    },
                };
                await Nova.app.__printReceipt(postData.invoiceXML, postData.meta);

            } catch (err) {
                this.$emit("loading", false);
                return err;
            } finally {
                this.$emit("loading", false);
            }
        },
    },
};
</script>

<style>
.invoice__attachments {
    .flex__center {
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
    }
}
</style>
