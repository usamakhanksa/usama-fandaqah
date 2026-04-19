<template>
  <div class="p-4 space-y-4">
    <div class="card p-4 flex items-center justify-between">
      <div>
        <h1 class="text-xl font-bold">Reservations schedule</h1>
        <p class="text-xs text-slate-400">Home &gt; Reservations Schedule</p>
      </div>
      <div class="text-sm text-emerald-600 font-semibold">Today {{ today }}</div>
    </div>

    <div class="card p-4 space-y-4">
      <div class="flex items-center gap-2 flex-wrap">
        <select v-model="view" class="input w-32"><option value="today">Today</option><option value="week">Week</option><option value="month">Month</option></select>
        <select v-model="filters.type" class="input w-40"><option value="all">All Rooms</option></select>
        <select v-model="filters.status" class="input w-32"><option value="all">All</option><option value="paid">Paid</option><option value="pending">Pending</option></select>
        <button class="btn-outline" @click="load">Apply</button>
      </div>

      <div class="overflow-auto border rounded-2xl">
        <div class="min-w-[950px]">
          <div class="grid" :style="gridCols">
            <div class="p-2 text-xs font-semibold bg-slate-50 border-b border-r">All Rooms</div>
            <div v-for="day in days" :key="day" class="p-2 text-xs font-semibold bg-slate-50 border-b border-r">{{ day }}</div>
          </div>

          <div v-for="room in rooms" :key="room.id" class="grid" :style="gridCols">
            <div class="p-2 border-b border-r text-xs">
              <div class="font-semibold">{{ room.name || room.number }}</div>
              <div class="text-slate-400">Room ({{ room.number }})</div>
            </div>
            <div v-for="day in days" :key="room.id+'-'+day" class="p-1 border-b border-r h-14 relative">
              <div v-for="event in eventsByCell(room.id, day)" :key="event.id" class="absolute left-1 right-1 top-1 rounded-full px-2 py-1 text-[10px] text-white flex items-center justify-between" :class="event.status==='paid' ? 'bg-emerald-500':'bg-blue-700'" @click="goDetails(event)">
                <span class="truncate">{{ event.guest }}</span>
                <span>{{ Math.round(event.price) }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <FooterBar />
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import api from '../services/api';
import FooterBar from '../components/FooterBar.vue';

const router = useRouter();
const view = ref('week');
const filters = ref({ type: 'all', status: 'all' });
const rooms = ref([]);
const events = ref([]);
const start = ref('');
const end = ref('');

const today = computed(() => new Date().toLocaleDateString());
const gridCols = computed(() => ({ gridTemplateColumns: `220px repeat(${days.value.length}, minmax(100px,1fr))` }));
const days = computed(() => {
  const arr = [];
  if (!start.value || !end.value) return arr;
  let d = new Date(start.value);
  const e = new Date(end.value);
  while (d <= e) { arr.push(d.toISOString().slice(0, 10)); d.setDate(d.getDate() + 1); }
  return arr;
});

const load = async () => {
  const { data } = await api.get('/reservations/schedule');
  rooms.value = data.rooms;
  events.value = data.events;
  start.value = data.start;
  end.value = data.end;
};

const eventsByCell = (roomId, day) => events.value.filter((e) => e.room_id === roomId && e.start <= day && e.end >= day);
const goDetails = (event) => router.push(`/reservations/management/${event.id}`);

onMounted(load);
</script>
