<template>
  <main class="bg-slate-100 min-h-[calc(100vh-64px)] p-4">
    <div class="bg-white rounded-xl border p-4 max-w-4xl mx-auto">
      <h1 class="font-bold mb-4">Add New Service</h1>
      <form class="space-y-4" @submit.prevent="submit">
        <div class="grid md:grid-cols-2 gap-4">
          <label class="text-sm">Service Name In English<input v-model="form.name_en" class="w-full mt-1 border rounded px-3 py-2"/></label>
          <label class="text-sm">Service Name In Arabic<input v-model="form.name_ar" class="w-full mt-1 border rounded px-3 py-2"/></label>
        </div>
        <div class="flex gap-6 text-sm">
          <label class="flex items-center gap-2"><input type="checkbox" v-model="form.show_in_reservation"/>Show In Reservation</label>
          <label class="flex items-center gap-2"><input type="checkbox" v-model="form.show_in_pos"/>Show In POS</label>
          <label class="flex items-center gap-2">Status<select v-model="form.status" class="border rounded px-2 py-1"><option value="active">Active</option><option value="inactive">Inactive</option></select></label>
        </div>
        <div class="flex justify-end gap-2"><router-link to="/pos/services" class="px-4 py-2 border rounded">Cancel</router-link><button class="px-4 py-2 bg-rose-500 text-white rounded">Add Service</button></div>
      </form>
    </div>
  </main>
</template>
<script setup>
import { reactive } from 'vue';
import { useRouter } from 'vue-router';
import api from '../services/api';
const router = useRouter();
const form = reactive({ name_en: '', name_ar: '', show_in_reservation: true, show_in_pos: true, status: 'active' });
const submit = async () => { await api.post('/pos/services', form); router.push('/pos/services'); };
</script>
