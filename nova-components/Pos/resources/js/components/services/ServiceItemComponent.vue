<template>
    <div>
        <div v-if="service" class="service_item" @click="openProductModal()">
            <div class="imgthumb">
                <img :src="service.image">
            </div><!-- end imgthumb -->
            <div class="desc">
                <span>{{service.name}}</span>
                <p class="d-inline-flex">{{service.price}}  <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></p>
            </div><!-- end desc -->
        </div><!-- end service_item -->

    </div>

</template>

<script>
    export default {
        name: "ServiceItemComponent",
        props : ['service'],
        data(){
        return{
            currency :Nova.app.currentTeam.currency,

        }
    },
        methods: {
            openProductModal(){
                Nova.$emit('open-add-service-product-modal', this.service);
            }
        }
    }
</script>

<style lang="scss" scoped>
.d-inline-flex{
    display: inline-flex !important;
}
</style>
