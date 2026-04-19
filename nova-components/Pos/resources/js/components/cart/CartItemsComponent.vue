<template>
        <table>
            <thead>
            <tr>
                <th></th>
                <th>{{__('Name')}}</th>
                <th>{{__('Qty')}}</th>
                <th>{{__('Price')}}</th>
            </tr>
            </thead>
            <tbody>
                <template v-if="items.length">
                    <cart-item-component v-for="(item , index) in items" :key="index" :item="item" />
                </template>
                <template v-else>
                    <tr>
                        <td colspan="4">
                          <div class="no_cart_items">
                            <div class="icon"></div>
                            <span>{{__('No item added to the cart yet')}}</span>
                          </div><!-- no_cart_items -->
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
</template>

<script>
    import CartItemComponent from './CartItemComponent'
    export default {
        name: "CartItemsComponent",
        props : ['items'],
        components : {CartItemComponent}
    }
</script>

<style scoped>

</style>
