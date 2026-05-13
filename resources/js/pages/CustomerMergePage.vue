<template>
  <div class="p-6 space-y-6 bg-[#f8f9fa] min-h-full">
    <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-50">
      <h1 class="text-2xl font-bold text-[#2a273c]">Duplicate Merge</h1>
      <p class="text-xs font-black text-[#e95a54] uppercase tracking-widest mt-1">Merge Duplicate Customer Records</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Primary Customer -->
      <div class="bg-white rounded-3xl shadow-sm border border-slate-50 p-6 space-y-4">
        <h2 class="text-sm font-black text-[#2a273c] uppercase tracking-widest">Primary Customer (Keep)</h2>
        <div><label class="label">Customer ID</label><input v-model="form.primary_id" type="number" class="input" placeholder="Primary customer ID" @change="loadCustomer('primary')"></div>
        <div v-if="primary" class="bg-emerald-50 border border-emerald-100 rounded-2xl p-4">
          <p class="text-sm font-bold text-[#2a273c]">{{ primary.name }}</p>
          <p class="text-xs text-slate-500">{{ primary.phone }} · {{ primary.email }}</p>
          <p class="text-xs text-slate-500">ID: {{ primary.id_number }}</p>
        </div>
      </div>

      <!-- Duplicate Customer -->
      <div class="bg-white rounded-3xl shadow-sm border border-slate-50 p-6 space-y-4">
        <h2 class="text-sm font-black text-[#2a273c] uppercase tracking-widest">Duplicate Customer (Merge & Delete)</h2>
        <div><label class="label">Customer ID</label><input v-model="form.duplicate_id" type="number" class="input" placeholder="Duplicate customer ID" @change="loadCustomer('duplicate')"></div>
        <div v-if="duplicate" class="bg-red-50 border border-red-100 rounded-2xl p-4">
          <p class="text-sm font-bold text-[#2a273c]">{{ duplicate.name }}</p>
          <p class="text-xs text-slate-500">{{ duplicate.phone }} · {{ duplicate.email }}</p>
          <p class="text-xs text-slate-500">ID: {{ duplicate.id_number }}</p>
        </div>
      </div>
    </div>

    <!-- Preview -->
    <div v-if="primary && duplicate" class="bg-white rounded-3xl shadow-sm border border-slate-50 p-6">
      <h2 class="text-sm font-black text-[#2a273c] uppercase tracking-widest mb-4">Merge Preview</h2>
      <div class="bg-amber-50 border border-amber-100 rounded-2xl p-4">
        <p class="text-sm font-bold text-amber-700">⚠ This will merge all reservations from <strong>{{ duplicate.name }}</strong> into <strong>{{ primary.name }}</strong> and delete the duplicate record.</p>
      </div>
    </div>

    <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-50 flex justify-end gap-3">
      <button @click="$router.back()" class="bg-slate-100 text-[#2a273c] px-6 py-3 rounded-xl text-xs font-black uppercase tracking-widest">Cancel</button>
      <button @click="showConfirm = true" :disabled="!primary || !duplicate" class="bg-[#e95a54] text-white px-6 py-3 rounded-xl text-xs font-black uppercase tracking-widest disabled:opacity-40">
        Proceed Merge
      </button>
    </div>

    <!-- Confirm Modal -->
    <div v-if="showConfirm" class="fixed inset-0 bg-black/60 flex items-center justify-center z-50">
      <div class="bg-white rounded-3xl p-8 w-full max-w-md space-y-4">
        <h3 class="text-xl font-bold text-[#2a273c]">Confirm Merge</h3>
        <p class="text-sm text-slate-600">Merge <strong>{{ duplicate?.name }}</strong> into <strong>{{ primary?.name }}</strong>? This cannot be undone.</p>
        <div class="flex gap-3">
          <button @click="showConfirm = false" class="flex-1 bg-slate-100 text-[#2a273c] py-3 rounded-xl text-xs font-black uppercase tracking-widest">Cancel</button>
          <button @click="merge" :disabled="processing" class="flex-1 bg-[#e95a54] text-white py-3 rounded-xl text-xs font-black uppercase tracking-widest disabled:opacity-40">
            {{ processing ? 'Merging...' : 'Confirm Merge' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import api from '../services/api';

const router = useRouter();
const primary = ref(null);
const duplicate = ref(null);
const showConfirm = ref(false);
const processing = ref(false);
const form = reactive({ primary_id: '', duplicate_id: '' });

const loadCustomer = async (type) => {
  const id = type === 'primary' ? form.primary_id : form.duplicate_id;
  if (!id) return;
  try {
    const { data } = await api.get(`/guests-module/customers/${id}`);
    if (type === 'primary') primary.value = data.data;
    else duplicate.value = data.data;
  } catch { if (type === 'primary') primary.value = null; else duplicate.value = null; }
};

const merge = async () => {
  processing.value = true;
  try {
    await api.post('/guests-module/customers/merge', { primary_id: form.primary_id, duplicate_id: form.duplicate_id });
    showConfirm.value = false;
    router.push('/customers');
  } finally { processing.value = false; }
};
</script>

<style scoped>
.label { @apply block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2; }
.input { @apply w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-bold text-[#2a273c] focus:ring-2 focus:ring-[#e95a54]; }
</style>
