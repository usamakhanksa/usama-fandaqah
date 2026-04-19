<template>
  <div class="bg-white rounded-xl border border-slate-200 p-3">
    <img :src="product.thumbnail || '/assets/avatars/guest2.svg'" class="h-20 mx-auto object-contain"/>
    <div class="mt-2 text-sm font-semibold line-clamp-1">{{ product.name }}</div>
    <div class="text-xs text-slate-500">{{ product.size }}</div>
    <div class="text-rose-500 font-bold text-sm">{{ product.price }} SR <span v-if="product.old_price" class="text-slate-400 line-through font-normal ml-1">{{ product.old_price }} SR</span></div>
    <div class="mt-2">
      <button v-if="!qty" class="w-full bg-rose-500 text-white rounded py-1.5 text-xs" @click="$emit('add', product)">Add To Card</button>
      <div v-else class="flex items-center gap-2">
        <button class="w-8 h-8 rounded bg-slate-100" @click="$emit('quantity', product, qty-1)">-</button>
        <div class="flex-1 text-center text-sm">{{ qty }}</div>
        <button class="w-8 h-8 rounded bg-emerald-500 text-white" @click="$emit('quantity', product, qty+1)">+</button>
      </div>
    </div>
  </div>
</template>
<script setup>
defineProps({ product: Object, qty: Number });
defineEmits(['add', 'quantity']);
</script>
