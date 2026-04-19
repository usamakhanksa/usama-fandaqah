<template>
  <div id="pos_page">
    <div class="all_col">
      <div class="col_right">
            <services-component />
      </div><!-- end col_right -->
      <div class="col_left">
            <cart-component />
      </div><!-- end col_left -->
    </div><!-- end all_col -->

    <remove-item-from-cart-component />

  </div><!-- End pos_page -->
</template>

<script>

  import ServicesComponent from './ServicesComponent'
  import CartComponent from './CartComponent'
  import RemoveItemFromCartComponent from './cart/RemoveFromCartComponent'

  export default {
    components: { ServicesComponent , CartComponent , RemoveItemFromCartComponent },
    methods: {

    },
    mounted() {
      //
    },
    beforeDestroy(){
        Nova.$off('open-add-service-product-modal');
    }
  }
</script>
