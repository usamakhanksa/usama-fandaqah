<template>
  <div class="p-6 space-y-6 bg-[#f8f9fa] min-h-full" v-if="res">
    <!-- Top Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-3xl shadow-sm border border-slate-50">
      <div class="flex items-center gap-4">
        <div 
          :class="[
            'w-12 h-12 rounded-2xl flex items-center justify-center text-white font-black text-xl shadow-lg',
            getStatusColor(res.status)
          ]"
        >
          {{ res.code?.charAt(0) }}
        </div>
        <div>
          <div class="flex items-center gap-2">
            <h1 class="text-2xl font-bold text-[#2a273c]">{{ res.code }}</h1>
            <span :class="['px-3 py-0.5 rounded-full text-[10px] font-black uppercase tracking-widest', getStatusClass(res.status)]">
              {{ res.status }}
            </span>
          </div>
          <p class="text-xs text-slate-400 mt-0.5">
            {{ res.guest?.name }} • {{ res.room?.number }} • {{ res.check_in }} - {{ res.check_out }}
          </p>
        </div>
      </div>
      <div class="flex items-center gap-2 flex-wrap">
        <button v-if="res.status === 'confirmed'" @click="checkIn" class="bg-emerald-500 text-white px-4 py-2 rounded-xl text-sm font-bold hover:bg-emerald-600 transition-all shadow-lg shadow-emerald-100">Check-In</button>
        <button v-if="res.status === 'checked-in'" @click="checkOut" class="bg-rose-500 text-white px-4 py-2 rounded-xl text-sm font-bold hover:bg-rose-600 transition-all shadow-lg shadow-rose-100">Check-Out</button>
        <button @click="extendStay" class="bg-white border border-slate-200 text-[#2a273c] px-4 py-2 rounded-xl text-sm font-bold hover:bg-slate-50 transition-all">Extend Stay</button>
        <button @click="transferRoom" class="bg-white border border-slate-200 text-[#2a273c] px-4 py-2 rounded-xl text-sm font-bold hover:bg-slate-50 transition-all">Transfer Room</button>
        <div class="relative group">
          <button class="bg-slate-900 text-white p-2 rounded-xl hover:bg-slate-800 transition-all">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
          </button>
          <div class="absolute right-0 top-full mt-2 bg-white border border-slate-100 rounded-2xl shadow-2xl py-3 w-56 z-20 hidden group-hover:block border-t-4 border-t-[#e95a54]">
            <button class="w-full text-left px-6 py-2 text-xs font-bold text-slate-600 hover:bg-slate-50">Print Contract</button>
            <button class="w-full text-left px-6 py-2 text-xs font-bold text-slate-600 hover:bg-slate-50">Generate Invoice</button>
            <button class="w-full text-left px-6 py-2 text-xs font-bold text-slate-600 hover:bg-slate-50">Send to Email</button>
            <div class="h-px bg-slate-50 my-2"></div>
            <button @click="cancelReservation" class="w-full text-left px-6 py-2 text-xs font-bold text-rose-500 hover:bg-rose-50">Cancel Reservation</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid lg:grid-cols-[300px_1fr] gap-6">
      <!-- Sidebar Info -->
      <div class="space-y-6">
        <!-- Guest Card -->
        <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-50">
          <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Guest Information</h3>
          <div class="flex items-center gap-4 mb-6">
            <div class="w-16 h-16 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600 font-black text-2xl overflow-hidden shadow-inner">
               <img v-if="res.guest?.avatar" :src="res.guest.avatar" class="w-full h-full object-cover">
               <span v-else>{{ res.guest?.name?.charAt(0) }}</span>
            </div>
            <div>
              <h4 class="font-bold text-[#2a273c]">{{ res.guest?.name }}</h4>
              <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">{{ res.guest?.type || 'Standard' }} Member</p>
            </div>
          </div>
          <div class="space-y-4">
            <div class="flex flex-col">
              <span class="text-[10px] font-bold text-slate-400 uppercase">Phone</span>
              <span class="text-sm font-semibold text-[#2a273c]">{{ res.guest?.phone || 'N/A' }}</span>
            </div>
            <div class="flex flex-col">
              <span class="text-[10px] font-bold text-slate-400 uppercase">Email</span>
              <span class="text-sm font-semibold text-[#2a273c]">{{ res.guest?.email || 'N/A' }}</span>
            </div>
          </div>
        </div>

        <!-- Financial Summary Card -->
        <div class="bg-slate-900 p-6 rounded-3xl shadow-xl shadow-slate-200 text-white">
          <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Balance Summary</h3>
          <div class="space-y-6">
            <div>
              <p class="text-xs text-slate-400">Total Amount</p>
              <p class="text-3xl font-black">{{ res.booking?.total_amount || 0 }} <span class="text-xs font-normal opacity-50 uppercase tracking-widest">SAR</span></p>
            </div>
            <div class="h-px bg-white/10"></div>
            <div class="flex justify-between">
              <div>
                <p class="text-[10px] text-slate-400 font-bold uppercase">Paid</p>
                <p class="text-lg font-bold text-emerald-400">{{ getPaidAmount() }}</p>
              </div>
              <div>
                <p class="text-[10px] text-slate-400 font-bold uppercase">Due</p>
                <p class="text-lg font-bold text-rose-400">{{ getDueAmount() }}</p>
              </div>
            </div>
            <button @click="tab = 'financial'" class="w-full py-3 bg-white/10 hover:bg-white/20 rounded-2xl text-xs font-bold transition-all border border-white/5 uppercase tracking-widest">
              View All Transactions
            </button>
          </div>
        </div>
      </div>

      <!-- Tabs Content -->
      <div class="bg-white rounded-3xl shadow-sm border border-slate-50 overflow-hidden flex flex-col">
        <!-- Tab Navigation -->
        <div class="flex border-b border-slate-50 px-4 overflow-x-auto no-scrollbar">
          <button 
            v-for="t in tabs" :key="t.id"
            @click="tab = t.id"
            :class="[
              'px-6 py-4 text-xs font-bold uppercase tracking-widest whitespace-nowrap transition-all border-b-2',
              tab === t.id ? 'border-[#e95a54] text-[#e95a54]' : 'border-transparent text-slate-400 hover:text-slate-600'
            ]"
          >
            {{ t.label }}
          </button>
        </div>

        <!-- Tab Panels -->
        <div class="p-8 flex-1 overflow-y-auto">
          <!-- Details Tab -->
          <div v-if="tab === 'details'" class="space-y-8 animate-fade-in">
            <div class="grid md:grid-cols-2 gap-8">
              <div class="space-y-6">
                <h4 class="text-xs font-black text-[#2a273c] uppercase tracking-widest flex items-center gap-2">
                   <div class="w-1.5 h-1.5 rounded-full bg-[#e95a54]"></div>
                   Stay Details
                </h4>
                <div class="grid grid-cols-2 gap-4">
                   <div class="bg-slate-50 p-4 rounded-2xl">
                     <p class="text-[10px] text-slate-400 font-bold uppercase mb-1">Check-In</p>
                     <p class="text-sm font-bold text-[#2a273c]">{{ res.check_in }}</p>
                   </div>
                   <div class="bg-slate-50 p-4 rounded-2xl">
                     <p class="text-[10px] text-slate-400 font-bold uppercase mb-1">Check-Out</p>
                     <p class="text-sm font-bold text-[#2a273c]">{{ res.check_out }}</p>
                   </div>
                   <div class="bg-slate-50 p-4 rounded-2xl">
                     <p class="text-[10px] text-slate-400 font-bold uppercase mb-1">Nights</p>
                     <p class="text-sm font-bold text-[#2a273c]">{{ calculateNights(res.check_in, res.check_out) }}</p>
                   </div>
                   <div class="bg-slate-50 p-4 rounded-2xl border-2 border-emerald-100">
                     <p class="text-[10px] text-emerald-500 font-bold uppercase mb-1">Category</p>
                     <p class="text-sm font-bold text-[#2a273c]">{{ res.reservation_category_type || 'Normal' }}</p>
                   </div>
                </div>
              </div>
              <div class="space-y-6">
                <h4 class="text-xs font-black text-[#2a273c] uppercase tracking-widest flex items-center gap-2">
                   <div class="w-1.5 h-1.5 rounded-full bg-indigo-500"></div>
                   Attribution
                </h4>
                <div class="grid grid-cols-1 gap-4">
                   <div class="bg-slate-50 p-4 rounded-2xl flex justify-between items-center">
                     <div>
                       <p class="text-[10px] text-slate-400 font-bold uppercase mb-1">Booking Source</p>
                       <p class="text-sm font-bold text-[#2a273c]">{{ res.source?.name || 'Walk-In' }}</p>
                     </div>
                     <div class="w-10 h-10 rounded-xl bg-white border border-slate-100 flex items-center justify-center">
                       <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                     </div>
                   </div>
                   <div class="bg-slate-50 p-4 rounded-2xl flex justify-between items-center">
                     <div>
                       <p class="text-[10px] text-slate-400 font-bold uppercase mb-1">Corporate / Company</p>
                       <p class="text-sm font-bold text-[#2a273c]">{{ res.company?.name || 'Individual' }}</p>
                     </div>
                     <div class="w-10 h-10 rounded-xl bg-white border border-slate-100 flex items-center justify-center">
                       <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                     </div>
                   </div>
                </div>
              </div>
            </div>
            <div class="bg-[#fcf8f1] p-6 rounded-3xl border border-[#f5e6cc]">
               <h5 class="text-[10px] font-black text-[#856404] uppercase tracking-widest mb-2">Special Requests / Notes</h5>
               <p class="text-sm text-[#856404] leading-relaxed">{{ res.special_request || 'No special requests provided for this booking.' }}</p>
            </div>
          </div>

          <!-- Financial Tab -->
          <div v-if="tab === 'financial'" class="space-y-6 animate-fade-in">
             <div class="flex items-center justify-between">
                <h4 class="text-xs font-black text-[#2a273c] uppercase tracking-widest">Financial Records</h4>
                <button @click="addTransaction" class="text-[10px] font-black text-[#e95a54] uppercase tracking-widest hover:underline">+ Add Transaction</button>
             </div>
             <div class="overflow-hidden rounded-2xl border border-slate-100">
               <table class="w-full text-xs">
                 <thead>
                   <tr class="bg-slate-50 text-slate-400 font-bold border-b border-slate-100">
                     <th class="px-6 py-4 text-start">Date</th>
                     <th class="px-6 py-4 text-start">Description</th>
                     <th class="px-6 py-4 text-start">Type</th>
                     <th class="px-6 py-4 text-end">Amount</th>
                   </tr>
                 </thead>
                 <tbody class="divide-y divide-slate-50">
                   <tr v-for="t in res.transactions" :key="t.id" class="hover:bg-slate-50/50 transition-colors">
                     <td class="px-6 py-4 text-slate-500">{{ formatDate(t.created_at) }}</td>
                     <td class="px-6 py-4 font-bold text-[#2a273c]">{{ t.description || 'Service Charge' }}</td>
                     <td class="px-6 py-4">
                       <span :class="['px-2 py-0.5 rounded-full text-[9px] font-black uppercase', t.type === 'credit' ? 'bg-emerald-50 text-emerald-600' : 'bg-indigo-50 text-indigo-600']">
                         {{ t.type }}
                       </span>
                     </td>
                     <td :class="['px-6 py-4 text-end font-bold', t.type === 'credit' ? 'text-emerald-600' : 'text-[#2a273c]']">
                       {{ t.type === 'credit' ? '-' : '+' }} {{ t.amount }} SAR
                     </td>
                   </tr>
                 </tbody>
               </table>
             </div>
          </div>

          <!-- Notes Tab -->
          <div v-if="tab === 'notes'" class="space-y-6 animate-fade-in">
             <div class="flex gap-4">
                <textarea v-model="newNote" class="flex-1 bg-slate-50 border-none rounded-2xl p-4 text-sm focus:ring-2 focus:ring-[#e95a54] placeholder:text-slate-300 min-h-[100px]" placeholder="Type a note about this reservation..."></textarea>
                <button @click="saveNote" class="bg-[#2a273c] text-white px-8 py-2 rounded-2xl font-bold text-xs hover:bg-slate-800 transition-all self-end">Post Note</button>
             </div>
             <div class="space-y-4">
                <div v-for="n in res.notes" :key="n.id" class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm relative group">
                  <div class="flex justify-between items-start mb-2">
                    <div class="flex items-center gap-2">
                      <div class="w-6 h-6 rounded-lg bg-slate-100 flex items-center justify-center text-[10px] font-black text-slate-400">UA</div>
                      <span class="text-xs font-bold text-[#2a273c]">User Admin</span>
                    </div>
                    <span class="text-[10px] text-slate-400 font-medium">{{ formatDate(n.created_at) }}</span>
                  </div>
                  <p class="text-sm text-slate-600 leading-relaxed">{{ n.note }}</p>
                </div>
             </div>
          </div>
          
          <!-- Activity Log Tab -->
          <div v-if="tab === 'activity'" class="space-y-6 animate-fade-in">
            <div class="relative pl-8 space-y-8 before:absolute before:left-[11px] before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-100">
               <div v-for="log in activityLogs" :key="log.id" class="relative">
                  <div class="absolute -left-[31px] top-1 w-6 h-6 rounded-full bg-white border-2 border-slate-100 flex items-center justify-center z-10">
                    <div class="w-2 h-2 rounded-full bg-slate-200"></div>
                  </div>
                  <div class="flex flex-col">
                    <p class="text-xs font-bold text-[#2a273c]">{{ log.action }}</p>
                    <p class="text-[10px] text-slate-400 font-medium">{{ log.user }} • {{ formatDate(log.time) }}</p>
                  </div>
               </div>
            </div>
          </div>

          <!-- Other tabs placeholders -->
          <div v-if="['guests', 'rooms', 'services', 'invoices', 'promissories'].includes(tab)" class="py-20 flex flex-col items-center justify-center text-center opacity-30">
             <div class="w-16 h-16 rounded-full bg-slate-100 flex items-center justify-center mb-4">
               <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
             </div>
             <h3 class="text-sm font-bold text-[#2a273c] uppercase tracking-widest">Coming Soon</h3>
             <p class="text-xs text-slate-400 mt-2">This module section is currently under development.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div v-else class="flex items-center justify-center min-h-[500px]">
    <div class="w-10 h-10 border-4 border-[#e95a54] border-t-transparent rounded-full animate-spin"></div>
  </div>
</template>

<script setup>
import { onMounted, ref, watch, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '../services/api';
import dayjs from 'dayjs';

const route = useRoute();
const router = useRouter();
const res = ref(null);
const tab = ref('details');
const newNote = ref('');

const tabs = [
  { id: 'details', label: 'Details' },
  { id: 'guests', label: 'Guests' },
  { id: 'rooms', label: 'Rooms' },
  { id: 'services', label: 'Services' },
  { id: 'financial', label: 'Financial' },
  { id: 'invoices', label: 'Invoices' },
  { id: 'promissories', label: 'Promissories' },
  { id: 'activity', label: 'Activity Log' },
  { id: 'notes', label: 'Notes' },
];

const activityLogs = [
  { id: 1, action: 'Reservation Created', user: 'Usama Khan', time: '2026-05-01 10:00:00' },
  { id: 2, action: 'Payment Added (Cash)', user: 'Usama Khan', time: '2026-05-01 10:05:00' },
  { id: 3, action: 'Guest Details Updated', user: 'Admin', time: '2026-05-02 14:20:00' },
];

const load = async () => {
  const id = route.params.id || route.params.bookingId;
  try {
    const { data } = await api.get(`/reservations/${id}`);
    res.value = data.data;
  } catch (err) {
    console.error('Failed to load reservation details', err);
  }
};

const getStatusColor = (status) => {
  switch (status?.toLowerCase()) {
    case 'confirmed': return 'bg-blue-500';
    case 'checked-in': return 'bg-emerald-500';
    case 'checked-out': return 'bg-slate-400';
    case 'cancelled': return 'bg-rose-500';
    default: return 'bg-slate-300';
  }
};

const getStatusClass = (status) => {
  switch (status?.toLowerCase()) {
    case 'confirmed': return 'bg-blue-50 text-blue-600';
    case 'checked-in': return 'bg-emerald-50 text-emerald-600';
    case 'checked-out': return 'bg-slate-100 text-slate-500';
    case 'cancelled': return 'bg-rose-50 text-rose-600';
    default: return 'bg-slate-50 text-slate-400';
  }
};

const calculateNights = (start, end) => {
  if (!start || !end) return 0;
  return dayjs(end).diff(dayjs(start), 'day');
};

const formatDate = (date) => dayjs(date).format('MMM D, YYYY HH:mm');

const getPaidAmount = () => {
  if (!res.value?.transactions) return '0.00';
  const total = res.value.transactions
    .filter(t => t.type === 'credit')
    .reduce((sum, t) => sum + Number(t.amount), 0);
  return total.toFixed(2);
};

const getDueAmount = () => {
  const total = Number(res.value?.booking?.total_amount || 0);
  const paid = Number(getPaidAmount());
  return Math.max(0, total - paid).toFixed(2);
};

const checkIn = async () => {
  if (!confirm('Check-in this guest?')) return;
  await api.post(`/reservations/${res.value.id}/check-in`);
  load();
};

const checkOut = async () => {
  if (!confirm('Check-out this guest?')) return;
  await api.post(`/reservations/${res.value.id}/check-out`);
  load();
};

const cancelReservation = async () => {
  const reason = prompt('Reason for cancellation?');
  if (reason === null) return;
  await api.delete(`/reservations/${res.value.id}`, { data: { cancellation_reason: reason } });
  load();
};

const saveNote = async () => {
  if (!newNote.value) return;
  await api.post(`/reservations/management/${res.value.id}/notes`, { note: newNote.value });
  newNote.value = '';
  load();
};

onMounted(load);
watch(() => route.params.id, load);
</script>

<style scoped>
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
.animate-fade-in { animation: fadeIn 0.4s ease-out; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
</style>
