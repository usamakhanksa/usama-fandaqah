<template>
  <div>
    <div class="service_col_inside" v-if="services.length">
      <div class="service_col" v-for="(service,index) in services" :key="index">
        <service-item-component :service="service" />
      </div><!-- end service_col -->
    </div>
    <div v-else>
      <div class="no_services_show">
        <div class="icon"></div>
        <span>{{__('No Results Till Now!')}}</span>
      </div><!-- no_services_show -->
    </div>
    <add-item-to-cart-component :service="service" ref="addModal" />
  </div>
</template>

<script>

    import ServiceItemComponent from './ServiceItemComponent'
    import AddItemToCartComponent from '../cart/AddItemToCartComponent'
    export default {
        name: "ServicesGridComponent",
        components : {ServiceItemComponent,AddItemToCartComponent},
        props: ['services' , 'locale'],
        data(){
            return {
               service : null
            }
        },

        methods: {

        },
        mounted(){

            Nova.$on('open-add-service-product-modal' , (service) => {
                this.service = service;
                this.$nextTick(() => {
                    this.$refs.addModal.$refs.productModal.open();
                });

            })
        }
    }
</script>

<style scoped>

</style>
