<template>
  <div class="p-6 bg-[#f8f9fa] min-h-full space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
      <div>
        <h1 class="text-3xl font-bold text-[#2a273c]">Reports Dashboard</h1>
        <p class="text-slate-500">Comprehensive overview of your hotel performance</p>
      </div>
    </div>

    <!-- Stats Preview -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
      <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
        <p class="text-slate-500 text-sm font-medium">Today's Occupancy</p>
        <h3 class="text-3xl font-bold text-[#2a273c]">{{ stats.occupancy }}%</h3>
        <div class="mt-2 w-full bg-slate-100 h-1.5 rounded-full">
          <div class="bg-[#e95a54] h-full rounded-full" :style="{ width: stats.occupancy + '%' }"></div>
        </div>
      </div>
      <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
        <p class="text-slate-500 text-sm font-medium">Today's Revenue</p>
        <h3 class="text-3xl font-bold text-emerald-600">{{ stats.revenue }} <span class="text-sm font-normal text-slate-400">SR</span></h3>
      </div>
      <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
        <p class="text-slate-500 text-sm font-medium">Month-to-Date</p>
        <h3 class="text-3xl font-bold text-[#2a273c]">{{ stats.mtd_revenue }} <span class="text-sm font-normal text-slate-400">SR</span></h3>
      </div>
      <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
        <p class="text-slate-500 text-sm font-medium">ADR (Today)</p>
        <h3 class="text-3xl font-bold text-orange-500">{{ stats.adr }} <span class="text-sm font-normal text-slate-400">SR</span></h3>
      </div>
    </div>

    <!-- Report Menu Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
      <!-- Daily Report Card -->
      <Link href="/reports/daily" class="group bg-white p-8 rounded-3xl shadow-sm border border-slate-100 hover:border-[#e95a54] transition-all flex flex-col items-center text-center space-y-4">
        <div class="w-16 h-16 rounded-2xl bg-orange-50 flex items-center justify-center text-[#e95a54] group-hover:scale-110 transition-transform">
          <TodayIcon class="w-8 h-8" />
        </div>
        <h3 class="text-xl font-bold text-[#2a273c]">Daily Report</h3>
        <p class="text-slate-500 text-sm">Review today's arrivals, departures, revenue breakdown and room statistics.</p>
        <span class="text-[#e95a54] font-semibold flex items-center">
          Open Report <ChevronRightIcon class="w-4 h-4 ml-1" />
        </span>
      </Link>

      <!-- Occupancy Report Card -->
      <Link href="/reports/occupancy" class="group bg-white p-8 rounded-3xl shadow-sm border border-slate-100 hover:border-emerald-400 transition-all flex flex-col items-center text-center space-y-4">
        <div class="w-16 h-16 rounded-2xl bg-emerald-50 flex items-center justify-center text-emerald-500 group-hover:scale-110 transition-transform">
          <HotelIcon class="w-8 h-8" />
        </div>
        <h3 class="text-xl font-bold text-[#2a273c]">Occupancy Analysis</h3>
        <p class="text-slate-500 text-sm">Analyze occupancy trends, room type performance, and floor-wise distribution.</p>
        <span class="text-emerald-500 font-semibold flex items-center">
          Open Report <ChevronRightIcon class="w-4 h-4 ml-1" />
        </span>
      </Link>

      <!-- Revenue Report Card -->
      <Link href="/reports/revenue" class="group bg-white p-8 rounded-3xl shadow-sm border border-slate-100 hover:border-blue-400 transition-all flex flex-col items-center text-center space-y-4">
        <div class="w-16 h-16 rounded-2xl bg-blue-50 flex items-center justify-center text-blue-500 group-hover:scale-110 transition-transform">
          <AttachMoneyIcon class="w-8 h-8" />
        </div>
        <h3 class="text-xl font-bold text-[#2a273c]">Revenue & ADR</h3>
        <p class="text-slate-500 text-sm">Track revenue by source, market segments, and monitor ADR/RevPAR performance.</p>
        <span class="text-blue-500 font-semibold flex items-center">
          Open Report <ChevronRightIcon class="w-4 h-4 ml-1" />
        </span>
      </Link>
    </div>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import { 
  CalendarDays as TodayIcon, 
  Hotel as HotelIcon, 
  CircleDollarSign as AttachMoneyIcon,
  ChevronRight as ChevronRightIcon 
} from 'lucide-vue-next';

const props = defineProps({
  stats: {
    type: Object,
    default: () => ({
      occupancy: 0,
      revenue: 0,
      mtd_revenue: 0,
      adr: 0
    })
  }
});
</script>

<style scoped>
.shadow-sm { box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05); }
.transition-all { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
</style>
