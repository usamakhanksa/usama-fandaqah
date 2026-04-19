<template>
    <div id="PaymentForm">
        <form :action="resultUrl" method="POST" class="paymentWidgets" data-brands="VISA MASTER MADA"></form>
    </div>
</template>

<script>
    export default {
        name: "PaymentForm",
        props: [
            "checkoutId",
            "resultUrl"
        ],
        mounted() {


        },
        computed: {
            wpwlOptions() {
                return {
                    locale: Nova.config.local,
                }
            }
        }
    }
</script>

<style scoped>
    #PaymentForm {
        direction: rtl;
        text-align: right;
        font-family: 'Dubai-Medium';
    }
    .wpwl-group.wpwl-group-submit.wpwl-clearfix {margin: 0 auto;}
    .wpwl-wrapper.wpwl-wrapper-submit button {
        padding: 5px 30px;
        font-family: 'Dubai-Bold';
    }
</style>
