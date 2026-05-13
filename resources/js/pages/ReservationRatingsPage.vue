<template>
  <div class="p-6 space-y-6 bg-[#f8f9fa] min-h-full flex flex-col">
    <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-50 flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-[#2a273c]">Reservation Ratings</h1>
        <p class="text-xs font-black text-[#e95a54] uppercase tracking-widest mt-1">Guest Feedback & Reviews</p>
      </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
      <div class="bg-white rounded-2xl p-5 border border-slate-50 shadow-sm text-center">
        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Avg Rating</p>
        <p class="text-3xl font-bold text-amber-500 mt-1">{{ avgRating }}</p>
        <div class="flex justify-center gap-0.5 mt-1">
          <span v-for="i in 5" :key="i" :class="i <= Math.round(avgRating) ? 'text-amber-400' : 'text-slate-200'" class="text-lg">★</span>
        </div>
      </div>
      <div class="bg-white rounded-2xl p-5 border border-slate-50 shadow-sm text-center">
        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Total Reviews</p>
        <p class="text-3xl font-bold text-[#2a273c] mt-1">{{ ratings.length }}</p>
      </div>
      <div class="bg-white rounded-2xl p-5 border border-slate-50 shadow-sm text-center">
        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">5 Stars</p>
        <p class="text-3xl font-bold text-emerald-600 mt-1">{{ ratings.filter(r => r.rating === 5).length }}</p>
      </div>
      <div class="bg-white rounded-2xl p-5 border border-slate-50 shadow-sm text-center">
        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">1-2 Stars</p>
        <p class="text-3xl font-bold text-rose-600 mt-1">{{ ratings.filter(r => r.rating <= 2).length }}</p>
      </div>
    </div>

    <!-- Filters -->
    <div class="bg-white p-5 rounded-3xl shadow-sm border border-slate-50 flex flex-wrap gap-3">
      <input v-model="filters.date_from" type="date" class="bg-slate-50 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-[#e95a54]">
      <input v-model="filters.date_to" type="date" class="bg-slate-50 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-[#e95a54]">
      <select v-model="filters.rating" class="bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-medium text-slate-600 focus:ring-2 focus:ring-[#e95a54]">
        <option value="">All Ratings</option>
        <option value="5">5 Stars</option>
        <option value="4">4 Stars</option>
        <option value="3">3 Stars</option>
        <option value="2">2 Stars</option>
        <option value="1">1 Star</option>
      </select>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-3xl shadow-sm border border-slate-50 overflow-hidden flex-1">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="bg-slate-50 text-slate-400 font-bold uppercase text-[10px] tracking-widest border-b border-slate-100">
              <th class="px-6 py-4 text-start">Reservation</th>
              <th class="px-6 py-4 text-start">Guest</th>
              <th class="px-6 py-4 text-start">Rating</th>
              <th class="px-6 py-4 text-start">Feedback</th>
              <th class="px-6 py-4 text-start">Date</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-50">
            <tr v-for="r in ratings" :key="r.id" class="hover:bg-slate-50/50 transition-colors">
              <td class="px-6 py-5">
                <span class="bg-indigo-50 text-indigo-600 px-2 py-1 rounded text-[10px] font-bold">{{ r.reservation?.code || '#—' }}</span>
              </td>
              <td class="px-6 py-5 font-bold text-[#2a273c]">{{ r.reservation?.guest?.name || 'Guest' }}</td>
              <td class="px-6 py-5">
                <div class="flex gap-0.5">
                  <span v-for="i in 5" :key="i" :class="i <= r.rating ? 'text-amber-400' : 'text-slate-200'" class="text-lg">★</span>
                </div>
              </td>
              <td class="px-6 py-5 text-slate-600 max-w-xs truncate">{{ r.feedback || '—' }}</td>
              <td class="px-6 py-5 text-slate-500 text-xs">{{ formatDate(r.created_at) }}</td>
            </tr>
            <tr v-if="!ratings.length">
              <td colspan="5" class="p-16 text-center text-slate-400 text-sm">No ratings found.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, reactive, ref, watch, onMounted } from 'vue';
import api from '../services/api';

const ratings = ref([]);
const filters = reactive({ date_from: '', date_to: '', rating: '' });

const avgRating = computed(() => {
  if (!ratings.value.length) return 0;
  return (ratings.value.reduce((s, r) => s + r.rating, 0) / ratings.value.length).toFixed(1);
});

const load = async () => {
  const { data } = await api.get('/reservations/ratings', { params: filters });
  ratings.value = data.data || [];
};

const formatDate = (d) => d ? new Date(d).toLocaleDateString() : '—';

watch(filters, load, { deep: true });
onMounted(load);
</script>
