<template>
  <div class="p-6 space-y-6 bg-[#f8f9fa] min-h-full">
    <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-50">
      <h1 class="text-2xl font-bold text-[#2a273c]">Guest Registration</h1>
      <p class="text-xs font-black text-[#e95a54] uppercase tracking-widest mt-1">Guest Personal Details & ID Capture</p>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-slate-50 p-6">
      <div class="mb-4">
        <label class="label">Reservation ID</label>
        <input v-model="reservationId" type="number" class="input w-64" placeholder="Enter reservation ID">
      </div>
      <div class="grid grid-cols-2 gap-4">
        <div><label class="label">Full Name *</label><input v-model="form.full_name" class="input" placeholder="Full name"></div>
        <div><label class="label">ID Type *</label>
          <select v-model="form.id_type" class="input">
            <option value="national_id">National ID</option>
            <option value="passport">Passport</option>
            <option value="iqama">Iqama</option>
          </select>
        </div>
        <div><label class="label">ID Number *</label><input v-model="form.id_number" class="input" placeholder="ID Number"></div>
        <div><label class="label">Nationality</label><input v-model="form.nationality" class="input" placeholder="Nationality"></div>
        <div><label class="label">Date of Birth</label><input v-model="form.date_of_birth" type="date" class="input"></div>
        <div><label class="label">Gender</label>
          <select v-model="form.gender" class="input"><option value="male">Male</option><option value="female">Female</option></select>
        </div>
        <div><label class="label">Phone</label><input v-model="form.phone" class="input" placeholder="+966..."></div>
        <div><label class="label">Email</label><input v-model="form.email" type="email" class="input" placeholder="email@example.com"></div>
      </div>

      <!-- Signature -->
      <div class="mt-6">
        <h3 class="text-sm font-black text-[#2a273c] uppercase tracking-widest mb-3">Digital Signature</h3>
        <div class="border-2 border-dashed border-slate-200 rounded-2xl h-32 flex items-center justify-center bg-slate-50 cursor-pointer" @click="form.signature_data = 'signed'">
          <p v-if="!form.signature_data" class="text-slate-400 text-sm">Click to capture signature</p>
          <p v-else class="text-emerald-600 font-bold text-sm">✓ Signature captured</p>
        </div>
      </div>
    </div>

    <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-50 flex justify-end gap-3">
      <button @click="$router.back()" class="bg-slate-100 text-[#2a273c] px-6 py-3 rounded-xl text-xs font-black uppercase tracking-widest">Cancel</button>
      <button @click="save" :disabled="processing" class="bg-[#e95a54] text-white px-6 py-3 rounded-xl text-xs font-black uppercase tracking-widest disabled:opacity-40">
        {{ processing ? 'Saving...' : 'Save Registration' }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue';
import api from '../services/api';

const reservationId = ref('');
const processing = ref(false);
const form = reactive({ full_name: '', id_type: 'national_id', id_number: '', nationality: '', date_of_birth: '', gender: 'male', phone: '', email: '', signature_data: '' });

const save = async () => {
  if (!reservationId.value || !form.full_name || !form.id_number) return;
  processing.value = true;
  try {
    await api.post(`/front-desk/registration/${reservationId.value}`, form);
  } finally {
    processing.value = false;
  }
};
</script>

<style scoped>
.label { @apply block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2; }
.input { @apply w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-bold text-[#2a273c] focus:ring-2 focus:ring-[#e95a54]; }
</style>
