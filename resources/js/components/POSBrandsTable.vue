<template>
  <div class="bg-white rounded-xl border overflow-hidden"><table class="w-full text-sm"><thead class="bg-slate-50"><tr><th class="p-3 text-left">#</th><th class="p-3 text-left">Brand Name</th><th class="p-3 text-left">Number Of Products</th><th class="p-3 text-left">Status</th></tr></thead><tbody><tr v-for="b in brands.data" :key="b.id" class="border-t"><td class="p-3">{{ b.id }}</td><td class="p-3">{{ b.name }}</td><td class="p-3">{{ b.products_count }}</td><td class="p-3"><span class="px-2 py-1 rounded bg-emerald-50 text-emerald-600 text-xs">{{ b.status }}</span></td></tr></tbody></table></div>
</template>
<script setup>
defineProps({ brands: Object });
</script>
