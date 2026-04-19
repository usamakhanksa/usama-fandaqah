<template>
    <div v-bind:class="{ disabled: ifIssuedInvoiceZatca() && enabled }">
        <slot></slot>
    </div>
</template>

<script>
export default {
    name: "IntegrationInputGroup",
    props: ["transaction", "enabled"],
    methods: {
        ifIssuedInvoiceZatca() {
            if(this.transaction) {
                return (
                    this.transaction.active_note == "invoice" ||
                    this.transaction.active_note == "debit note"
                );
            }
        },
    },
};
</script>

<style>
.disabled {
    pointer-events: none;
    opacity: 0.7;
}
</style>
