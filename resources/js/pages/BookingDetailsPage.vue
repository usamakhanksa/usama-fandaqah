<template>
  <div class="p-4 space-y-4">
    <div class="card p-4 flex justify-between items-center">
      <div>
        <h1 class="text-xl font-bold">Reservations schedule</h1>
        <p class="text-xs text-slate-400">Home &gt; Reservations Schedule &gt; Booking Details</p>
      </div>
      <button class="btn-primary">Check Out</button>
    </div>

    <div class="card p-3 bg-emerald-50 border-emerald-200 text-sm flex gap-10">
      <div class="font-semibold">Check In</div>
      <div>{{ summaryDate }}</div>
      <div>{{ summaryTime }}</div>
    </div>

    <div class="card p-3 space-y-3">
      <div class="flex gap-2 text-sm">
        <button class="px-4 py-2 rounded-full" :class="tab==='summary'?'bg-slate-900 text-white':'bg-slate-100'" @click="tab='summary'">Reservation summary</button>
        <button class="px-4 py-2 rounded-full" :class="tab==='visitor'?'bg-slate-900 text-white':'bg-slate-100'" @click="tab='visitor'">Visitor Information</button>
        <button class="px-4 py-2 rounded-full" :class="tab==='financial'?'bg-slate-900 text-white':'bg-slate-100'" @click="tab='financial'">Financial Record</button>
        <button class="px-4 py-2 rounded-full" :class="tab==='notes'?'bg-slate-900 text-white':'bg-slate-100'" @click="tab='notes'">Notes On Booking</button>
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

      <div v-if="tab==='financial'" class="space-y-2 text-sm">
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
import { computed, onMounted, ref } from 'vue';
import { useRoute } from 'vue-router';
import api from '../services/api';

const route = useRoute();
const tab = ref('visitor');
const details = ref({ summary: null, visitor: [], financial: [], notes: [] });
const note = ref('');

const summaryDate = computed(() => details.value.summary?.check_in || 'Feb 18, 1998');
const summaryTime = computed(() => '8:00 AM');

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
</script>
