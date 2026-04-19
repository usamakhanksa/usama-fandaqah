<template>
  <div class="flex gap-2 py-2 border-b">
    <img :src="item.thumbnail || '/assets/avatars/guest3.svg'" class="w-10 h-10 rounded" />
    <div class="flex-1">
      <div class="text-xs font-semibold line-clamp-1">{{ item.name }}</div>
      <div class="text-xs">{{ item.price }} SR <span v-if="item.old_price" class="line-through text-slate-400">{{ item.old_price }} SR</span></div>
      <button class="text-[11px] text-rose-500" @click="$emit('remove', item)"># Remove Product</button>
    </div>
    <div class="flex flex-col items-center gap-1">
      <button class="w-5 h-5 bg-emerald-500 text-white rounded" @click="$emit('quantity', item, item.quantity+1)">+</button>
      <span class="text-xs">{{ item.quantity }}</span>
      <button class="w-5 h-5 bg-slate-100 rounded" @click="$emit('quantity', item, item.quantity-1)">-</button>
    </div>
  </div>
</template>
<script setup>
defineProps({ item: Object });
defineEmits(['remove', 'quantity']);
</script>
