<template>
<div class="p-4 space-y-4">
  <div class="grid grid-cols-12 gap-4">
    <div class="col-span-12 lg:col-span-8 card"><BannerCarousel :items="store.summary?.banners||[]"/></div>
    <div class="col-span-12 lg:col-span-4 space-y-4"><CalendarWidget/><MetricMiniChartCard title="Active Users" :value="store.summary?.stats?.active_users||0" color="pink"/><MetricMiniChartCard title="Total Revenue" :value="store.summary?.stats?.profit||0" color="purple"/></div>
  </div>
  <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
    <div class="card p-4 bg-orange-50 border-orange-100 flex flex-col justify-between">
      <p class="text-[11px] font-bold text-orange-600 uppercase">ADR</p>
      <p class="text-2xl font-black">{{ store.summary?.metrics?.adr || 0 }} SR</p>
    </div>
    <div class="card p-4 bg-blue-50 border-blue-100 flex flex-col justify-between">
      <p class="text-[11px] font-bold text-blue-600 uppercase">RevPAR</p>
      <p class="text-2xl font-black">{{ store.summary?.metrics?.revpar || 0 }} SR</p>
    </div>
    <div class="card p-4 bg-emerald-50 border-emerald-100 flex flex-col justify-between">
      <p class="text-[11px] font-bold text-emerald-600 uppercase">GOP</p>
      <p class="text-2xl font-black">{{ store.summary?.metrics?.gop || 0 }} SR</p>
    </div>
    <div class="card p-4 bg-purple-50 border-purple-100 flex flex-col justify-between">
      <p class="text-[11px] font-bold text-purple-600 uppercase">Occupancy</p>
      <p class="text-2xl font-black">{{ store.summary?.metrics?.occupancy_rate || 0 }}%</p>
    </div>
  </div>
  <div class="grid md:grid-cols-3 gap-4"><StatusCard title="New Booked Rooms" :value="store.summary?.recent_status?.new_booked||0"/><StatusCard title="Check In Today" :value="store.summary?.recent_status?.check_in||0"/><StatusCard title="Check Out Today" :value="store.summary?.recent_status?.check_out||0"/></div>
  <div class="grid lg:grid-cols-3 gap-4"><div class="lg:col-span-2 card"><CustomerAnalyticsCard/></div><div class="card"><HotelSummaryCard :stats="store.summary?.stats"/></div></div>
  <div class="grid lg:grid-cols-3 gap-4"><div class="lg:col-span-2 card"><ReservationsTable :rows="store.reservations.data"/></div><div class="card"><UnitStatusChart/></div></div>
  <FooterBar/>
</div>
</template>
<script setup>
import { onMounted } from 'vue';
import { useDashboardStore } from '../stores/dashboard';
import BannerCarousel from '../components/BannerCarousel.vue';import CalendarWidget from '../components/CalendarWidget.vue';import MetricMiniChartCard from '../components/MetricMiniChartCard.vue';import StatusCard from '../components/StatusCard.vue';import CustomerAnalyticsCard from '../components/CustomerAnalyticsCard.vue';import HotelSummaryCard from '../components/HotelSummaryCard.vue';import ReservationsTable from '../components/ReservationsTable.vue';import UnitStatusChart from '../components/UnitStatusChart.vue';import FooterBar from '../components/FooterBar.vue';
const store=useDashboardStore(); onMounted(()=>store.load());
</script>
