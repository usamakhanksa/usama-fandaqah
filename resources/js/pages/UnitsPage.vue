<template>
  <div class="p-4 space-y-4">
    <UnitsHeaderBar v-model:search="query.search" v-model:date="query.date" />
    <div class="card p-3 space-y-3">
      <div class="flex items-center justify-between gap-3 flex-wrap">
        <UnitsFilterBar :filters="filters" v-model:query="query" @change="load" />
        <UnitsModeSwitch :mode="mode" @change="switchMode" />
      </div>

      <template v-if="mode==='units'">
        <FloorSection v-for="floor in floors" :key="floor.id" :floor="floor" @action="onUnitAction"/>
      </template>
      <template v-else>
        <DailyStatusSection title="ARRIVALS" tab="CHECKED-IN" :rows="daily.arrivals" type="checkin" @action="openCheckInFromRow"/>
        <DailyStatusSection title="DEPARTURES" tab="CHECKED-OUT" :rows="daily.departures" type="checkout" @action="openCheckOutFromRow"/>
      </template>
    </div>

    <div class="grid md:grid-cols-4 gap-3">
      <QuickModeCard label="Units" :active="mode==='units'" @click="switchMode('units')" />
      <QuickModeCard label="Daily Status" :active="mode==='daily'" @click="switchMode('daily')" />
      <SmallFilterWidget :filters="filters" v-model:query="query" @change="load" />
      <QuickActionCard @check-in="checkInOpen=true" @check-out="checkOutOpen=true" />
    </div>

    <ReservationStepper :current="2" />
    <FooterBar/>

    <CheckInModal v-model="checkInOpen" :rooms="unitOptions" :reservation-id="selected.reservation_id" :unit-id="selected.unit_id" @submitted="submitCheckIn"/>
    <CheckOutModal v-model="checkOutOpen" :rooms="unitOptions" :reservation-id="selected.reservation_id" :unit-id="selected.unit_id" @submitted="submitCheckOut"/>
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import api from '../services/api';
import UnitsHeaderBar from '../components/UnitsHeaderBar.vue';
import UnitsFilterBar from '../components/UnitsFilterBar.vue';
import UnitsModeSwitch from '../components/UnitsModeSwitch.vue';
import FloorSection from '../components/FloorSection.vue';
import DailyStatusSection from '../components/DailyStatusSection.vue';
import CheckInModal from '../components/CheckInModal.vue';
import CheckOutModal from '../components/CheckOutModal.vue';
import QuickModeCard from '../components/QuickModeCard.vue';
import SmallFilterWidget from '../components/SmallFilterWidget.vue';
import QuickActionCard from '../components/QuickActionCard.vue';
import ReservationStepper from '../components/ReservationStepper.vue';
import FooterBar from '../components/FooterBar.vue';

const mode = ref('units');
const floors = ref([]);
const daily = ref({ arrivals: [], departures: [] });
const filters = ref({ statuses: [], types: [] });
const checkInOpen = ref(false);
const checkOutOpen = ref(false);
const selected = reactive({ reservation_id: null, unit_id: null });
const query = reactive({ search: '', date: new Date().toISOString().slice(0, 10), status_id: '', type_id: '' });

const unitOptions = computed(() => floors.value.flatMap((f) => f.units || []).map((u) => ({ value: u.id, label: `${u.number}` })));

const load = async () => {
  const [filterRes, floorsRes, dailyRes] = await Promise.all([
    api.get('/units/filters'),
    api.get('/units/floors', { params: query }),
    api.get('/units/daily-status'),
  ]);
  filters.value = filterRes.data;
  floors.value = floorsRes.data;
  daily.value = dailyRes.data;
};

const switchMode = (next) => { mode.value = next; };
const onUnitAction = (payload) => {
  selected.unit_id = payload.unit_id;
  selected.reservation_id = payload.reservation_id;
  if (payload.action === 'Check IN') checkInOpen.value = true;
  if (payload.action === 'Check Out') checkOutOpen.value = true;
};
const openCheckInFromRow = (row) => { selected.unit_id = row.unit_id; selected.reservation_id = row.reservation_id; checkInOpen.value = true; };
const openCheckOutFromRow = (row) => { selected.unit_id = row.unit_id; selected.reservation_id = row.reservation_id; checkOutOpen.value = true; };

const submitCheckIn = async (payload) => { await api.post('/units/check-in', payload); checkInOpen.value = false; await load(); };
const submitCheckOut = async (payload) => { await api.post('/units/check-out', payload); checkOutOpen.value = false; await load(); };

onMounted(load);
</script>
