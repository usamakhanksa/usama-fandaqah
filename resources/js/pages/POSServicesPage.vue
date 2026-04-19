<template>
  <main class="bg-slate-100 min-h-[calc(100vh-64px)] p-4 space-y-4">
    <div class="bg-white rounded-xl border p-4 flex justify-between items-center"><h1 class="font-bold">POS <span class="text-slate-400 text-sm">Home > POS > Services</span></h1><router-link to="/pos/services/create" class="bg-rose-500 text-white px-4 py-2 rounded text-xs">Add New Service</router-link></div>
    <POSServicesTable :services="services"/>
  </main>
</template>
<script setup>
import { onMounted, ref } from 'vue';
import api from '../services/api';
import POSServicesTable from '../components/POSServicesTable.vue';
const services = ref({ data: [] });
onMounted(async () => { services.value = (await api.get('/pos/services')).data; });
</script>
