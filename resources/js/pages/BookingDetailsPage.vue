<template>
  <div class="p-4 space-y-4">
    <div class="card p-4 flex justify-between items-center">
      <div>
        <h1 class="text-xl font-bold">Reservations Management</h1>
        <p class="text-xs text-slate-400">Home &gt; Reservations Management &gt; Booking Details</p>
      </div>
      <div class="flex gap-2">
        <button class="btn-outline">Booking Files</button>
        <button class="btn-primary">Check Out</button>
      </div>
    </div>

    <div class="card p-3 bg-emerald-50 border-emerald-200 text-sm flex gap-10">
      <div class="font-semibold">Check In</div>
      <div>{{ summaryDate }}</div>
      <div>{{ summaryTime }}</div>
    </div>
    <div class="card p-3 bg-rose-50 border-rose-200 text-sm flex gap-10">
      <div class="font-semibold">Check Out</div>
      <div>{{ summaryDate }}</div>
      <div>{{ summaryTime }}</div>
    </div>

    <div class="card p-3 space-y-3">
      <div class="flex gap-2 text-sm">
        <button class="px-4 py-2 rounded-full" :class="tab==='summary'?'bg-slate-900 text-white':'bg-slate-100'" @click="setTab('summary')">Reservation summary</button>
        <button class="px-4 py-2 rounded-full" :class="tab==='visitor'?'bg-slate-900 text-white':'bg-slate-100'" @click="setTab('visitor')">Visitor Information</button>
        <button class="px-4 py-2 rounded-full" :class="tab==='financial'?'bg-slate-900 text-white':'bg-slate-100'" @click="setTab('financial')">Financial Record</button>
        <button class="px-4 py-2 rounded-full" :class="tab==='notes'?'bg-slate-900 text-white':'bg-slate-100'" @click="setTab('notes')">Notes On Booking</button>
      </div>

      <div v-if="tab==='visitor'" class="grid md:grid-cols-2 gap-3 text-sm">
        <div v-for="(row,idx) in details.visitor" :key="idx" class="flex justify-between border-b pb-2"><span class="font-semibold">{{ row.label }}</span><span>{{ row.value }}</span></div>
      </div>

      <div v-if="tab==='summary'" class="text-sm space-y-2">
        <p><span class="font-semibold">Booking #:</span> {{ details.summary?.id }}</p>
        <p><span class="font-semibold">Guest:</span> {{ details.summary?.reservation?.guest?.name }}</p>
        <p><span class="font-semibold">Room:</span> {{ details.summary?.room?.number }}</p>
        <p><span class="font-semibold">Total:</span> {{ details.summary?.total_amount }} SAR</p>
      </div>

      <div v-if="tab==='financial'" class="space-y-4 text-sm">
        <div class="grid md:grid-cols-3 gap-3">
          <div class="card p-3 border"><p class="text-xs text-slate-500">Total Amount</p><p class="text-xl font-bold">{{ totals.total }} SAR</p></div>
          <div class="card p-3 border border-emerald-300"><p class="text-xs text-slate-500">Total Purchased</p><p class="text-xl font-bold text-emerald-600">{{ totals.purchased }} SAR</p></div>
          <div class="card p-3 border border-rose-300"><p class="text-xs text-slate-500">Total Due</p><p class="text-xl font-bold text-rose-600">{{ totals.due }} SAR</p></div>
        </div>
        <div v-for="record in details.financial" :key="record.id" class="flex justify-between border-b pb-2"><span>{{ record.label }}</span><span>{{ record.amount }} SAR</span></div>
      </div>

      <div v-if="tab==='notes'" class="space-y-3">
        <form class="flex gap-2" @submit.prevent="saveNote"><input v-model="note" class="input" placeholder="Add booking note"><button class="btn-primary">Save</button></form>
        <div v-for="n in details.notes" :key="n.id" class="text-sm border rounded-xl p-3">{{ n.note }}</div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '../services/api';

const route = useRoute();
const router = useRouter();
const tab = ref('visitor');
const details = ref({ summary: null, visitor: [], financial: [], notes: [] });
const note = ref('');

const summaryDate = computed(() => details.value.summary?.check_in || 'Feb 18, 1998');
const summaryTime = computed(() => '8:00 AM');
const totals = computed(() => {
  const total = Number(details.value.summary?.total_amount || 0);
  const purchased = details.value.financial
    .filter((item) => item.type === 'debit')
    .reduce((sum, item) => sum + Number(item.amount), 0);
  const due = Math.max(0, purchased - total);
  return {
    total: total.toFixed(2),
    purchased: purchased.toFixed(2),
    due: due.toFixed(2),
  };
});

const setTab = (next) => {
  tab.value = next;
  if (next === 'financial') router.replace(`/reservations/management/${route.params.bookingId}/financial`);
  if (next === 'notes') router.replace(`/reservations/management/${route.params.bookingId}/notes`);
  if (next === 'visitor' || next === 'summary') router.replace(`/reservations/management/${route.params.bookingId}`);
};

const load = async () => {
  const { data } = await api.get(`/reservations/management/${route.params.bookingId}`);
  details.value = data;
};

const saveNote = async () => {
  if (!note.value) return;
  await api.post(`/reservations/management/${route.params.bookingId}/notes`, { note: note.value });
  note.value = '';
  await load();
};

onMounted(load);
watch(
  () => route.path,
  (path) => {
    if (path.endsWith('/financial')) tab.value = 'financial';
    else if (path.endsWith('/notes')) tab.value = 'notes';
    else tab.value = 'visitor';
  },
  { immediate: true },
);
</script>
