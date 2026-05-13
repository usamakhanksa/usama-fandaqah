<template>
  <div class="p-6 space-y-6 bg-[#f8f9fa] min-h-full flex flex-col">
    <!-- Header & Filters -->
    <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-50 flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-[#2a273c]">Availability Grid</h1>
        <div class="flex items-center gap-4 mt-2">
           <button @click="prevMonth" class="p-2 hover:bg-slate-50 rounded-xl transition-colors"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M15 19l-7-7 7-7" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg></button>
           <span class="text-sm font-black uppercase tracking-widest text-[#e95a54]">{{ currentMonthLabel }}</span>
           <button @click="nextMonth" class="p-2 hover:bg-slate-50 rounded-xl transition-colors"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg></button>
        </div>
      </div>
      
      <div class="flex items-center gap-3">
        <select v-model="filters.room_type_id" class="bg-slate-50 border-none rounded-xl py-3 px-4 text-xs font-bold text-slate-600 focus:ring-2 focus:ring-[#e95a54]">
          <option value="">All Room Types</option>
          <option v-for="t in roomTypes" :key="t.id" :value="t.id">{{ t.name }}</option>
        </select>
        <select v-model="filters.room_floor_id" class="bg-slate-50 border-none rounded-xl py-3 px-4 text-xs font-bold text-slate-600 focus:ring-2 focus:ring-[#e95a54]">
          <option value="">All Floors</option>
          <option v-for="f in floors" :key="f.id" :value="f.id">{{ f.name }}</option>
        </select>
        <router-link to="/reservations/quick-create" class="bg-[#e95a54] text-white px-4 py-3 rounded-xl text-xs font-bold shadow-lg shadow-rose-100 transition-all hover:bg-opacity-90">
          + Quick Book
        </router-link>
      </div>
    </div>

    <!-- Calendar Grid -->
    <div class="bg-white rounded-3xl shadow-sm border border-slate-50 overflow-hidden flex-1 flex flex-col min-h-0">
      <div class="overflow-auto flex-1 relative custom-scrollbar" ref="gridContainer">
        <table class="w-full border-collapse table-fixed min-w-[1200px]">
          <thead class="sticky top-0 z-20 bg-white">
            <tr class="border-b border-slate-100">
              <th class="w-[200px] sticky left-0 z-30 bg-white p-4 text-start border-r border-slate-50">
                 <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Rooms / Dates</span>
              </th>
              <th v-for="day in daysInMonth" :key="day.date" class="w-[50px] p-2 text-center border-r border-slate-50" :class="{ 'bg-slate-50': isWeekend(day.date) }">
                 <div class="flex flex-col">
                   <span class="text-[10px] font-black text-slate-400 uppercase">{{ day.dayName }}</span>
                   <span :class="['text-xs font-bold', isToday(day.date) ? 'text-[#e95a54]' : 'text-[#2a273c]']">{{ day.dayNumber }}</span>
                 </div>
              </th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="room in rooms" :key="room.id" class="border-b border-slate-50 hover:bg-slate-50/30 transition-colors">
              <td class="sticky left-0 z-10 bg-white p-4 border-r border-slate-50 shadow-[4px_0_10px_-4px_rgba(0,0,0,0.05)]">
                 <div class="flex flex-col">
                   <span class="text-sm font-bold text-[#2a273c]">{{ room.number }}</span>
                   <span class="text-[10px] text-slate-400 font-medium">{{ room.room_type?.name }}</span>
                 </div>
              </td>
              <td 
                v-for="day in daysInMonth" :key="day.date" 
                class="relative border-r border-slate-50 h-16 group"
                @click="onCellClick(room, day.date)"
              >
                <!-- Existing Reservation Block -->
                <div 
                  v-if="getReservationForDay(room.id, day.date)"
                  @click.stop="viewReservation(getReservationForDay(room.id, day.date).id)"
                  :class="[
                    'absolute inset-y-1 left-0 right-0 z-10 rounded-lg mx-0.5 p-2 overflow-hidden cursor-pointer shadow-sm transition-all hover:scale-[1.02]',
                    getStatusColor(getReservationForDay(room.id, day.date).status)
                  ]"
                  :style="getReservationStyle(getReservationForDay(room.id, day.date), day.date)"
                >
                  <div class="flex flex-col h-full justify-center">
                    <span class="text-[9px] font-black text-white uppercase truncate leading-none">{{ getReservationForDay(room.id, day.date).guest_name || 'Guest' }}</span>
                  </div>
                </div>

                <!-- Empty Cell Hover State -->
                <div class="absolute inset-0 bg-[#e95a54]/5 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center pointer-events-none">
                  <span class="text-[8px] font-black text-[#e95a54] uppercase tracking-widest">+ Book</span>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Legend -->
      <div class="bg-slate-50 p-6 border-t border-slate-100 flex items-center justify-between">
         <div class="flex items-center gap-6">
            <div class="flex items-center gap-2">
               <div class="w-3 h-3 rounded-full bg-blue-500"></div>
               <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Confirmed</span>
            </div>
            <div class="flex items-center gap-2">
               <div class="w-3 h-3 rounded-full bg-emerald-500"></div>
               <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Checked In</span>
            </div>
            <div class="flex items-center gap-2">
               <div class="w-3 h-3 rounded-full bg-slate-400"></div>
               <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Checked Out</span>
            </div>
            <div class="flex items-center gap-2">
               <div class="w-3 h-3 rounded-full bg-rose-500"></div>
               <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Cancelled</span>
            </div>
         </div>
         <div class="text-[10px] font-black text-slate-300 uppercase tracking-widest">
            Total {{ rooms.length }} Rooms
         </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, reactive, ref, computed, watch } from 'vue';
import { useRouter } from 'vue-router';
import api from '../services/api';
import dayjs from 'dayjs';
import isBetween from 'dayjs/plugin/isBetween';

dayjs.extend(isBetween);

const router = useRouter();
const currentMonth = ref(dayjs().startOf('month'));
const rooms = ref([]);
const reservations = ref({});
const roomTypes = ref([]);
const floors = ref([]);
const filters = reactive({ room_type_id: '', room_floor_id: '' });

const currentMonthLabel = computed(() => currentMonth.value.format('MMMM YYYY'));

const daysInMonth = computed(() => {
  const days = [];
  const count = currentMonth.value.daysInMonth();
  for (let i = 1; i <= count; i++) {
    const d = currentMonth.value.date(i);
    days.push({
      date: d.format('YYYY-MM-DD'),
      dayNumber: i,
      dayName: d.format('ddd')
    });
  }
  return days;
});

const load = async () => {
  try {
    const { data } = await api.get('/reservations/calendar', {
      params: {
        ...filters,
        start_date: currentMonth.value.startOf('month').format('YYYY-MM-DD'),
        end_date: currentMonth.value.endOf('month').format('YYYY-MM-DD')
      }
    });
    rooms.value = data.data.rooms || [];
    reservations.value = data.data.reservations || {};
  } catch (err) {
    console.error('Failed to load calendar data', err);
  }
};

const loadLookups = async () => {
  try {
    const [types, flrs] = await Promise.all([
      api.get('/master-data/room_types'),
      api.get('/master-data/floors') // Assuming this endpoint exists
    ]);
    roomTypes.value = types.data.data || [];
    floors.value = flrs.data.data || [];
  } catch (err) {
    console.error('Failed to load calendar lookups', err);
  }
};

const prevMonth = () => { currentMonth.value = currentMonth.value.subtract(1, 'month'); };
const nextMonth = () => { currentMonth.value = currentMonth.value.add(1, 'month'); };

const isWeekend = (date) => [0, 6].includes(dayjs(date).day());
const isToday = (date) => dayjs(date).isSame(dayjs(), 'day');

const getReservationForDay = (roomId, date) => {
  const roomRes = reservations.value[roomId] || [];
  return roomRes.find(r => dayjs(date).isBetween(dayjs(r.check_in), dayjs(r.check_out), 'day', '[)'));
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

const getReservationStyle = (res, date) => {
  const isStart = dayjs(date).isSame(dayjs(res.check_in), 'day');
  return {
    width: '100%',
    marginLeft: isStart ? '0px' : '-2px',
    borderRadius: isStart ? '8px' : '0px',
  };
};

const onCellClick = (room, date) => {
  if (getReservationForDay(room.id, date)) return;
  router.push({
    path: '/reservations/quick-create',
    query: { room_id: room.id, date_in: date, room_type_id: room.room_type_id }
  });
};

const viewReservation = (id) => {
  router.push(`/reservations/${id}`);
};

watch(() => [filters.room_type_id, filters.room_floor_id, currentMonth.value], load);

onMounted(() => {
  load();
  loadLookups();
});
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }
</style>
