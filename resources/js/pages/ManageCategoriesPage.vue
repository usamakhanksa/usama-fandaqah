<template>
<div class="p-6 space-y-6">
  <div class="card p-4 flex justify-between items-center">
    <div class="flex items-center gap-2">
      <router-link to="/channel-manager" class="text-xl text-slate-400 hover:text-slate-600">←</router-link>
      <h1 class="text-2xl font-bold">Manage Categories</h1>
    </div>
    <p class="text-sm text-slate-400">Home > Channel Manager > Manage Categories</p>
  </div>

  <div class="grid lg:grid-cols-2 gap-6">
    <div class="card p-6 space-y-6">
      <div class="grid md:grid-cols-2 gap-4">
        <div><label class="label">Select Category</label><select v-model="form.category_id" class="input"><option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option></select></div>
        <div><label class="label">Date Range</label><div class="flex gap-2"><input type="date" v-model="form.start" class="input flex-1"><input type="date" v-model="form.end" class="input flex-1"></div></div>
      </div>
      <div class="p-4 bg-slate-50 rounded-2xl flex items-center justify-between">
        <div><p class="text-xs text-slate-500 uppercase font-bold tracking-wider">Unit Count</p><p class="text-3xl font-black">12</p></div>
        <div class="h-10 w-px bg-slate-200"></div>
        <div class="text-right"><p class="text-xs text-slate-500 uppercase font-bold tracking-wider">Available</p><p class="text-3xl font-black text-emerald-500">8</p></div>
      </div>

      <div class="space-y-3">
        <p class="font-bold">Daily Prices</p>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
          <div v-for="day in days" :key="day" class="p-3 border rounded-xl space-y-2">
            <p class="text-[11px] font-bold text-slate-400 uppercase">{{ day }}</p>
            <input type="number" class="w-full font-bold focus:outline-none" placeholder="0.00">
            <label class="flex items-center gap-1 text-[10px] text-slate-500"><input type="checkbox" checked> Base price</label>
          </div>
        </div>
      </div>
    </div>

    <div class="card p-6 space-y-6">
      <div class="grid md:grid-cols-2 gap-4">
        <div><label class="label">Virtual Room</label><input type="number" class="input" placeholder="0"></div>
        <div>
          <label class="label">Second Rate Plan Formula</label>
          <div class="flex gap-2">
            <select class="input w-20"><option>+</option><option>-</option><option>*</option></select>
            <input type="number" class="input flex-1" placeholder="Numeric value">
          </div>
        </div>
      </div>
      
      <div class="p-10 border-2 border-dashed border-slate-100 rounded-3xl flex flex-col items-center justify-center text-center opacity-40">
        <div class="text-4xl mb-2">🏷️</div>
        <p class="font-medium">Pricing rules will be synced across all connected channels automatically.</p>
      </div>

      <div class="flex gap-2 pt-4">
        <button class="btn-outline flex-1">Reset</button>
        <button class="btn-primary flex-1">Save Rules</button>
      </div>
    </div>
  </div>
</div>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import api from '../services/api';

const categories = ref([]);
const form = ref({ category_id: null, start: '', end: '' });
const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

onMounted(async () => {
  const res = await api.get('/rooms/filters');
  categories.value = res.data.room_types || [];
});
</script>
