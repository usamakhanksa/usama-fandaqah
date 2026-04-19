<template>
  <div class="bg-white rounded-xl border overflow-hidden"><table class="w-full text-sm"><thead class="bg-slate-50"><tr><th class="p-3 text-left">#</th><th class="p-3 text-left">Product Name</th><th class="p-3 text-left">Price</th><th class="p-3 text-left">Category</th><th class="p-3 text-left">Brand</th><th class="p-3 text-left">Status</th></tr></thead><tbody><tr v-for="p in products.data" :key="p.id" class="border-t"><td class="p-3">{{ p.id }}</td><td class="p-3 flex items-center gap-2"><img :src="p.thumbnail || '/assets/avatars/guest1.svg'" class="w-6 h-6"/>{{ p.name }}</td><td class="p-3">{{ p.price }} SAR</td><td class="p-3">{{ p.category?.name }}</td><td class="p-3">{{ p.brand?.name }}</td><td class="p-3"><span class="px-2 py-1 rounded bg-emerald-50 text-emerald-600 text-xs">{{ p.status }}</span></td></tr></tbody></table></div>
</template>
<script setup>
defineProps({ products: Object });
</script>
