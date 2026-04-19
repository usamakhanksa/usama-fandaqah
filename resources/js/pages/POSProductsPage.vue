<template>
  <main class="bg-slate-100 min-h-[calc(100vh-64px)] p-4 space-y-4">
    <div class="bg-white rounded-xl border p-4"><h1 class="font-bold">POS <span class="text-slate-400 text-sm">Home > POS > Products</span></h1></div>
    <div class="grid md:grid-cols-3 gap-3"><router-link class="bg-white border rounded-xl p-4 font-semibold" to="/pos/products/categories">Categories</router-link><router-link class="bg-white border rounded-xl p-4 font-semibold" to="/pos/products/sub-categories">Sub Categories</router-link><router-link class="bg-white border rounded-xl p-4 font-semibold" to="/pos/products/brands">Brands</router-link></div>
    <POSProductsTable :products="products"/>
  </main>
</template>
<script setup>
import { onMounted, ref } from 'vue';
import api from '../services/api';
import POSProductsTable from '../components/POSProductsTable.vue';
const products = ref({ data: [] });
onMounted(async () => { products.value = (await api.get('/pos/products')).data; });
</script>
