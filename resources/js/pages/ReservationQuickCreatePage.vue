<template>
  <div class="p-6 max-w-4xl mx-auto space-y-6 bg-[#f8f9fa] min-h-full">
    <!-- Header -->
    <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-50 flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-[#2a273c]">Quick Reservation</h1>
        <p class="text-xs text-slate-400 mt-1">Fill in the essential details to quickly book a room.</p>
      </div>
      <router-link to="/reservations/management" class="text-xs font-bold text-slate-400 hover:text-[#e95a54] uppercase tracking-widest transition-colors">
        Back to List
      </router-link>
    </div>

    <!-- Quick Form -->
    <div class="bg-white p-8 rounded-3xl shadow-xl border border-slate-50 space-y-8 relative overflow-hidden">
      <!-- Decor -->
      <div class="absolute top-0 right-0 w-32 h-32 bg-[#e95a54]/5 rounded-bl-full -mr-16 -mt-16"></div>

      <div class="grid md:grid-cols-2 gap-6">
        <!-- Guest Name -->
        <div class="space-y-2">
          <label class="text-xs font-black text-slate-400 uppercase tracking-widest">Guest Full Name</label>
          <input 
            v-model="form.guest_name" 
            class="w-full bg-slate-50 border-none rounded-2xl py-4 px-6 text-sm focus:ring-2 focus:ring-[#e95a54] transition-all" 
            placeholder="John Doe"
          >
        </div>

        <!-- Phone -->
        <div class="space-y-2">
          <label class="text-xs font-black text-slate-400 uppercase tracking-widest">Phone Number</label>
          <input 
            v-model="form.phone" 
            class="w-full bg-slate-50 border-none rounded-2xl py-4 px-6 text-sm focus:ring-2 focus:ring-[#e95a54] transition-all" 
            placeholder="+966 5XX XXX XXX"
          >
        </div>

        <!-- Check In -->
        <div class="space-y-2">
          <label class="text-xs font-black text-slate-400 uppercase tracking-widest">Check-In Date</label>
          <input 
            type="date" 
            v-model="form.check_in" 
            class="w-full bg-slate-50 border-none rounded-2xl py-4 px-6 text-sm focus:ring-2 focus:ring-[#e95a54] transition-all"
          >
        </div>

        <!-- Check Out -->
        <div class="space-y-2">
          <label class="text-xs font-black text-slate-400 uppercase tracking-widest">Check-Out Date</label>
          <input 
            type="date" 
            v-model="form.check_out" 
            class="w-full bg-slate-50 border-none rounded-2xl py-4 px-6 text-sm focus:ring-2 focus:ring-[#e95a54] transition-all"
          >
        </div>

        <!-- Room Type -->
        <div class="space-y-2">
          <label class="text-xs font-black text-slate-400 uppercase tracking-widest">Preferred Room Type</label>
          <select 
            v-model="form.room_type_id" 
            class="w-full bg-slate-50 border-none rounded-2xl py-4 px-6 text-sm focus:ring-2 focus:ring-[#e95a54] transition-all appearance-none"
          >
            <option value="">Select a type</option>
            <option v-for="type in roomTypes" :key="type.id" :value="type.id">{{ type.name }}</option>
          </select>
        </div>

        <!-- Booking Source -->
        <div class="space-y-2">
          <label class="text-xs font-black text-slate-400 uppercase tracking-widest">Booking Source</label>
          <select 
            v-model="form.source_id" 
            class="w-full bg-slate-50 border-none rounded-2xl py-4 px-6 text-sm focus:ring-2 focus:ring-[#e95a54] transition-all appearance-none"
          >
            <option value="">Select a source</option>
            <option v-for="source in sources" :key="source.id" :value="source.id">{{ source.name }}</option>
          </select>
        </div>
      </div>

      <!-- Action Button -->
      <div class="pt-4">
        <button 
          @click="submit"
          :disabled="loading"
          class="w-full bg-[#e95a54] text-white py-4 rounded-2xl text-sm font-bold hover:bg-opacity-90 shadow-xl shadow-rose-100 transition-all flex items-center justify-center gap-3 disabled:opacity-50"
        >
          <span v-if="loading" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
          {{ loading ? 'Processing...' : 'Quick Confirm Reservation' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import api from '../services/api';
import Swal from 'sweetalert2';

const router = useRouter();
const loading = ref(false);
const roomTypes = ref([]);
const sources = ref([]);

const form = reactive({
  guest_name: '',
  phone: '',
  check_in: new Date().toISOString().split('T')[0],
  check_out: new Date(Date.now() + 86400000).toISOString().split('T')[0],
  room_type_id: '',
  source_id: ''
});

const loadLookups = async () => {
  try {
    const [types, src] = await Promise.all([
      api.get('/master-data/room_types'),
      api.get('/sources')
    ]);
    roomTypes.value = types.data.data || [];
    sources.value = src.data.data || [];
  } catch (err) {
    console.error('Failed to load lookups', err);
  }
};

const submit = async () => {
  if (!form.guest_name || !form.phone || !form.room_type_id) {
    Swal.fire('Error', 'Please fill in all required fields.', 'error');
    return;
  }

  loading.value = true;
  try {
    const { data } = await api.post('/reservations/quick-create', form);
    Swal.fire({
      title: 'Success!',
      text: 'Reservation created successfully. Room auto-assigned.',
      icon: 'success',
      confirmButtonText: 'View Reservation'
    }).then((result) => {
      router.push(`/reservations/${data.data.id}`);
    });
  } catch (err) {
    Swal.fire('Error', err.response?.data?.message || 'Failed to create reservation.', 'error');
  } finally {
    loading.value = false;
  }
};

onMounted(loadLookups);
</script>
