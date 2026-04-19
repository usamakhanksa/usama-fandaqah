<template>
  <div class="p-6">
    <div class="card p-4 mb-4"><h1 class="text-2xl font-bold">{{ modeLabel }} Success</h1></div>
    <div class="card p-10 text-center">
      <img :src="'/assets/banners/banner2.svg'" class="mx-auto w-56 opacity-80" alt="success"/>
      <h2 class="text-4xl font-semibold mt-6">{{ modeLabel }} Has Been Successfull</h2>
      <p class="text-slate-600 mt-3">You Have Successfully Checked Out From The Room Number (504)</p>
      <div class="mt-8 flex flex-col items-center gap-2">
        <router-link to="/dashboard" class="btn-primary w-64 text-center">Back To Home</router-link>
        <button class="btn-outline w-64">Print Receipt</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useRoute } from 'vue-router';
const route = useRoute();
const modeLabel = computed(() => route.path.includes('/expenses/') ? 'Expense' : 'Receipt');
</script>
