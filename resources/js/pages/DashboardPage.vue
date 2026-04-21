<template>
  <div class="p-6 bg-[#f8f9fa] min-h-full space-y-6">
    <!-- Top Row: Welcome Slider & Quick Stats -->
    <div class="grid grid-cols-12 gap-6">
      <!-- Welcome Slider -->
      <div class="col-span-12 lg:col-span-8">
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-[#2a273c] to-[#3a3654] text-white p-8 h-full min-h-[300px] flex items-center shadow-lg">
          <div class="relative z-10 max-w-lg space-y-4">
            <h1 class="text-4xl font-bold leading-tight">{{ $t('dashboard.welcome') }}</h1>
            <p class="text-slate-300 text-lg">{{ $t('dashboard.slider_desc') }}</p>
            <button class="bg-[#e95a54] text-white px-8 py-3 rounded-xl font-semibold hover:bg-opacity-90 transition-all shadow-lg">
              {{ $t('dashboard.explore') }}
            </button>
          </div>
          <!-- Decorative Illustration -->
          <div class="absolute right-0 bottom-0 top-0 w-1/2 flex items-center justify-center p-4">
            <img :src="'/dashboard_hero.png'" alt="Hero Illustration" class="max-h-full w-auto object-contain transform translate-x-10 translate-y-4" />
          </div>
        </div>
      </div>

      <!-- Side Stats (Active Users & Revenue) -->
      <div class="col-span-12 lg:col-span-4 flex flex-col gap-6">
        <div class="flex-1 bg-white rounded-3xl p-6 shadow-sm border border-slate-100 flex items-center justify-between group hover:border-[#e95a54] transition-colors cursor-pointer" @click="$router.push('/leads')">
          <div class="space-y-1">
            <p class="text-slate-500 font-medium">{{ $t('leads.new') }} {{ $t('leads.title') }}</p>
            <h3 class="text-3xl font-bold text-[#2a273c]">{{ store.summary?.stats?.new_leads || '0' }}</h3>
          </div>
          <div class="w-16 h-16 rounded-2xl bg-orange-50 flex items-center justify-center text-[#e95a54]">
             <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path></svg>
          </div>
        </div>
        <div class="flex-1 bg-white rounded-3xl p-6 shadow-sm border border-slate-100 flex items-center justify-between group hover:border-emerald-400 transition-colors">
          <div class="space-y-1">
            <p class="text-slate-500 font-medium">{{ $t('dashboard.total_revenue') }}</p>
            <h3 class="text-3xl font-bold text-[#2a273c]">{{ store.summary?.stats?.profit || '0' }} <span class="text-sm font-normal text-slate-400">SR</span></h3>
          </div>
          <div class="w-16 h-16 rounded-2xl bg-emerald-50 flex items-center justify-center text-emerald-500">
             <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
          </div>
        </div>
      </div>
    </div>

    <!-- Recent Status Row -->
    <div class="space-y-4">
      <div class="flex justify-between items-center">
        <h2 class="text-xl font-bold text-[#2a273c]">{{ $t('dashboard.recent_status') }}</h2>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <StatusMetricCard 
          :title="$t('dashboard.booked_rooms')" 
          :value="store.summary?.recent_status?.new_booked || 0" 
          color="#e95a54" 
        />
        <StatusMetricCard 
          :title="$t('dashboard.check_in_today')" 
          :value="store.summary?.recent_status?.check_in || 0" 
          color="#fbcdab"
          textColor="#2a273c"
        />
        <StatusMetricCard 
          :title="$t('dashboard.check_out_today')" 
          :value="store.summary?.recent_status?.check_out || 0" 
          color="#8f9793" 
        />
      </div>
    </div>

    <!-- Analytics Section -->
    <div class="grid grid-cols-12 gap-6">
      <!-- Customers Chart -->
      <div class="col-span-12 lg:col-span-8 bg-white rounded-3xl p-8 shadow-sm border border-slate-100">
        <div class="flex justify-between items-center mb-8">
          <div>
            <h3 class="text-xl font-bold text-[#2a273c]">{{ $t('dashboard.customers') }}</h3>
            <p class="text-slate-400 text-sm">{{ $t('dashboard.customer_desc') }}</p>
          </div>
          <div class="flex bg-slate-50 rounded-xl p-1">
            <button class="px-4 py-2 text-sm font-medium rounded-lg text-[#2a273c] bg-white shadow-sm">{{ $t('dashboard.monthly') }}</button>
            <button class="px-4 py-2 text-sm font-medium rounded-lg text-slate-400 hover:text-[#2a273c]">{{ $t('dashboard.weekly_new') }}</button>
          </div>
        </div>
        <CustomerTrendChart />
      </div>

      <!-- Hotel Summary -->
      <div class="col-span-12 lg:col-span-4 bg-white rounded-3xl p-8 shadow-sm border border-slate-100">
        <h3 class="text-xl font-bold text-[#2a273c] mb-8">{{ $t('dashboard.hotel_summary') }}</h3>
        <div class="space-y-6">
          <SummaryRow :label="$t('dashboard.total_rooms')" :value="store.summary?.stats?.total_rooms || 0" icon="rooms" />
          <SummaryRow :label="$t('dashboard.total_guests')" :value="store.summary?.stats?.total_guests || 0" icon="guests" />
          <SummaryRow :label="$t('dashboard.total_profit')" :value="(store.summary?.stats?.profit || 0) + ' SR'" icon="profit" color="#e95a54" />
          
          <div class="pt-6 border-t border-slate-100 space-y-4">
            <div class="flex justify-between items-center">
              <span class="text-slate-500 font-medium">{{ $t('dashboard.reservation_rate') }}</span>
              <span class="text-[#e95a54] font-bold">85%</span>
            </div>
            <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
              <div class="bg-[#e95a54] h-full" style="width: 85%"></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Bottom Section: Reservations & Units -->
    <div class="grid grid-cols-12 gap-6">
      <div class="col-span-12 lg:col-span-8 bg-white rounded-3xl p-8 shadow-sm border border-slate-100">
        <div class="flex justify-between items-center mb-6">
          <h3 class="text-xl font-bold text-[#2a273c]">{{ $t('dashboard.upcoming') }}</h3>
          <button class="text-[#e95a54] font-semibold text-sm hover:underline">{{ $t('dashboard.see_all') }}</button>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full text-left">
            <thead>
              <tr class="text-slate-400 font-medium text-sm border-b border-slate-50">
                <th class="pb-4 font-normal">{{ $t('dashboard.check_in') }}</th>
                <th class="pb-4 font-normal">{{ $t('dashboard.check_out') }}</th>
                <th class="pb-4 font-normal">{{ $t('dashboard.registration') }}</th>
                <th class="pb-4 font-normal text-right">Status</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
              <tr v-for="res in store.reservations.data?.slice(0, 5)" :key="res.id" class="text-sm">
                <td class="py-4 font-medium text-[#2a273c]">{{ res.check_in_date }}</td>
                <td class="py-4 text-slate-500">{{ res.check_out_date }}</td>
                <td class="py-4 text-slate-500">#{{ res.id }}</td>
                <td class="py-4 text-right">
                  <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-600">CONFIRMED</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div class="col-span-12 lg:col-span-4 bg-white rounded-3xl p-8 shadow-sm border border-slate-100">
        <div class="flex justify-between items-center mb-8">
          <h3 class="text-xl font-bold text-[#2a273c]">{{ $t('dashboard.unit_status') }}</h3>
          <select class="text-sm bg-slate-50 border-none rounded-lg font-medium text-slate-500">
            <option>All Units</option>
          </select>
        </div>
        <UnitStatusDonut />
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted } from 'vue';
import { useDashboardStore } from '../stores/dashboard';
import StatusMetricCard from '../components/dashboard/StatusMetricCard.vue';
import CustomerTrendChart from '../components/dashboard/CustomerTrendChart.vue';
import SummaryRow from '../components/dashboard/SummaryRow.vue';
import UnitStatusDonut from '../components/dashboard/UnitStatusDonut.vue';

const store = useDashboardStore();

onMounted(() => {
  store.load();
});
</script>

<style scoped>
/* Custom shadows and transitions for the corporate feel */
.shadow-sm { box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05); }
.transition-all { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }

[dir="rtl"] .text-left { text-align: right; }
[dir="rtl"] .text-right { text-align: left; }
</style>
