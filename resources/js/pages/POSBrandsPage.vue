<template><main class="bg-slate-100 min-h-[calc(100vh-64px)] p-4 space-y-4"><div class="bg-white rounded-xl border p-4"><h1 class="font-bold">POS <span class="text-slate-400 text-sm">Home > POS > Products > Brands</span></h1></div><POSBrandsTable :brands="brands"/></main></template>
<script setup>
import { onMounted, ref } from 'vue';
import api from '../services/api';
import POSBrandsTable from '../components/POSBrandsTable.vue';
const brands = ref({ data: [] });
onMounted(async () => { brands.value = (await api.get('/pos/brands')).data; });
</script>
