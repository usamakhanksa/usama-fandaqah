<template>
  <div class="bg-white rounded-xl border overflow-hidden"><table class="w-full text-sm"><thead class="bg-slate-50"><tr><th class="p-3 text-left">#</th><th class="p-3 text-left">Sub Category Name</th><th class="p-3 text-left">Parent Category</th><th class="p-3 text-left">Products</th><th class="p-3 text-left">Status</th></tr></thead><tbody><tr v-for="c in subCategories" :key="c.id" class="border-t"><td class="p-3">{{ c.id }}</td><td class="p-3">{{ c.name }}</td><td class="p-3">{{ c.category?.name }}</td><td class="p-3">{{ c.products_count }}</td><td class="p-3"><span class="px-2 py-1 rounded bg-emerald-50 text-emerald-600 text-xs">{{ c.status }}</span></td></tr></tbody></table></div>
</template>
<script setup>
defineProps({ subCategories: Array });
</script>
