<template>
<div class="p-4 space-y-4">
  <div class="grid grid-cols-12 gap-4">
    <div class="col-span-12 lg:col-span-8 card"><BannerCarousel :items="store.summary?.banners||[]"/></div>
    <div class="col-span-12 lg:col-span-4 space-y-4"><CalendarWidget/><MetricMiniChartCard title="Active Users" :value="store.summary?.stats.active_users||0" color="pink"/><MetricMiniChartCard title="Total Revenue" :value="store.summary?.stats.profit||0" color="purple"/></div>
  </div>
  <div class="grid md:grid-cols-3 gap-4"><StatusCard title="New Booked Rooms" :value="store.summary?.recent_status.new_booked||0"/><StatusCard title="Check In Today" :value="store.summary?.recent_status.check_in||0"/><StatusCard title="Check Out Today" :value="store.summary?.recent_status.check_out||0"/></div>
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
