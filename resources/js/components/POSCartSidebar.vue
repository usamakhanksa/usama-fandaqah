<template>
  <aside class="bg-white rounded-xl border border-slate-200 p-4 h-full flex flex-col">
    <div class="flex gap-2 mb-2"><input v-model="localCustomer" @change="$emit('customer', localCustomer)" placeholder="Type customer name" class="flex-1 border rounded px-3 py-2 text-xs"/><button class="w-8 h-8 rounded bg-slate-800 text-white">+</button></div>
    <div class="flex justify-between text-xs mb-2"><span>Items ({{ cart.items.length }})</span><button class="text-rose-500" @click="$emit('clear')">Remove All</button></div>
    <div class="flex-1 overflow-auto">
      <POSCartItem v-for="item in cart.items" :key="item.id" :item="item" @remove="$emit('remove', $event)" @quantity="(item, qty) => $emit('quantity', item, qty)"/>
    </div>
    <div class="text-sm space-y-1 mt-2 border-t pt-2">
      <div class="flex justify-between"><span>Subtotal</span><span>{{ cart.subtotal.toFixed(2) }} SAR</span></div>
      <div class="flex justify-between"><span>Tax (12%)</span><span>{{ cart.tax.toFixed(2) }} SAR</span></div>
      <div class="flex justify-between"><span>Discount</span><span class="text-rose-500">-{{ cart.discount.toFixed(2) }} SAR</span></div>
      <div class="flex justify-between font-bold text-base"><span>Total</span><span>{{ cart.total.toFixed(2) }} SAR</span></div>
    </div>
    <button class="mt-3 w-full bg-rose-500 text-white rounded-lg py-2 font-semibold" @click="$emit('checkout')">Pay {{ cart.total.toFixed(2) }} SR</button>
    <button class="text-xs underline mt-2">Pay Letter</button>
  </aside>
</template>
<script setup>
import { ref, watch } from 'vue';
import POSCartItem from './POSCartItem.vue';
const props = defineProps({ cart: Object });
const localCustomer = ref(props.cart.customer_name || '');
watch(() => props.cart.customer_name, (v) => (localCustomer.value = v || ''));
defineEmits(['customer', 'clear', 'remove', 'quantity', 'checkout']);
</script>
