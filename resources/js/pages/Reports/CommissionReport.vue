<template>
  <div class="p-6 bg-[#f8f9fa] min-h-full space-y-6">
    <!-- Header/Filter -->
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-3xl font-bold text-[#2a273c]">Commission Report</h1>
        <p class="text-slate-500">Commission owed by travel agents & OTAs</p>
      </div>
      <div class="flex items-center gap-4 bg-white p-2 rounded-2xl shadow-sm border border-slate-100">
        <div class="flex items-center gap-2 px-3 border-r border-slate-100">
          <span class="text-xs font-bold text-slate-400 uppercase">From</span>
          <input type="date" v-model="filters.start_date" @change="fetchData" class="border-none p-0 text-sm font-bold text-[#2a273c] focus:ring-0" />
        </div>
        <div class="flex items-center gap-2 px-3">
          <span class="text-xs font-bold text-slate-400 uppercase">To</span>
          <input type="date" v-model="filters.end_date" @change="fetchData" class="border-none p-0 text-sm font-bold text-[#2a273c] focus:ring-0" />
        </div>
        <select v-model="filters.source_id" @change="fetchData" class="border-none p-0 text-sm font-bold text-[#2a273c] focus:ring-0 bg-transparent">
          <option value="">All Sources</option>
          <option v-for="src in sources" :key="src.id" :value="src.id">{{ src.name }}</option>
        </select>
        <button @click="exportCSV" class="px-4 py-1.5 bg-emerald-500 text-white text-sm font-bold rounded-xl hover:bg-emerald-600">Export</button>
      </div>
    </div>

    <div v-if="loading" class="flex justify-center py-20">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-emerald-500"></div>
    </div>

    <div v-else class="space-y-6">
      <!-- Summary Cards -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
          <div class="text-slate-400 text-xs font-bold uppercase">Total Commission</div>
          <div class="text-3xl font-bold text-blue-600 mt-2">{{ formatCurrency(paidUnpaid.total_commission) }}</div>
          <div class="text-xs text-slate-500 mt-1">Period total</div>
        </div>
        <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
          <div class="text-slate-400 text-xs font-bold uppercase">Paid</div>
          <div class="text-3xl font-bold text-emerald-500 mt-2">{{ formatCurrency(paidUnpaid.paid_commission) }}</div>
          <div class="text-xs text-slate-500 mt-1">{{ paidUnpaid.paid_percentage }}% settled</div>
        </div>
        <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
          <div class="text-slate-400 text-xs font-bold uppercase">Unpaid</div>
          <div class="text-3xl font-bold text-orange-500 mt-2">{{ formatCurrency(paidUnpaid.unpaid_commission) }}</div>
          <div class="text-xs text-slate-500 mt-1">awaiting payment</div>
        </div>
        <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
          <div class="text-slate-400 text-xs font-bold uppercase">Agents</div>
          <div class="text-3xl font-bold text-purple-500 mt-2">{{ summary.length }}</div>
          <div class="text-xs text-slate-500 mt-1">active partners</div>
        </div>
      </div>

      <!-- Commission by Source -->
      <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100">
        <h3 class="text-lg font-bold text-[#2a273c] mb-6 flex items-center gap-2">
          <BarChart3Icon class="w-5 h-5 text-blue-500" /> Commission by Source
        </h3>
        <div class="h-96 overflow-auto">
          <table class="w-full">
            <thead class="bg-slate-50 text-slate-400 text-xs font-bold uppercase sticky top-0">
              <tr>
                <th class="px-6 py-4 text-left">Source</th>
                <th class="px-6 py-4 text-right">Commission Rate</th>
                <th class="px-6 py-4 text-center">Reservations</th>
                <th class="px-6 py-4 text-right">Revenue</th>
                <th class="px-6 py-4 text-right">Amount</th>
                <th class="px-6 py-4 text-right">Paid</th>
                <th class="px-6 py-4 text-right">Unpaid</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
              <tr v-for="item in summary" :key="item.source_id">
                <td class="px-6 py-4 text-sm font-medium text-[#2a273c]">{{ item.source_name }}</td>
                <td class="px-6 py-4 text-sm text-right text-blue-600">{{ item.commission_rate }}%</td>
                <td class="px-6 py-4 text-sm text-center">{{ item.reservation_count }}</td>
                <td class="px-6 py-4 text-sm text-right">{{ formatCurrency(item.total_revenue) }}</td>
                <td class="px-6 py-4 text-sm text-right font-bold text-purple-600">{{ formatCurrency(item.commission_amount) }}</td>
                <td class="px-6 py-4 text-sm text-right text-emerald-600">{{ formatCurrency(item.paid_amount) }}</td>
                <td class="px-6 py-4 text-sm text-right text-orange-600">{{ formatCurrency(item.unpaid_amount) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Reservation Details -->
      <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="p-6 border-b border-slate-50">
          <h3 class="font-bold text-[#2a273c]">Reservation Commission Detail</h3>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-slate-50 text-slate-400 text-xs font-bold uppercase">
              <tr>
                <th class="px-6 py-4 text-left">Code</th>
                <th class="px-6 py-4 text-left">Guest</th>
                <th class="px-6 py-4 text-left">Check-in</th>
                <th class="px-6 py-4 text-left">Source</th>
                <th class="px-6 py-4 text-right">Revenue</th>
                <th class="px-6 py-4 text-right">Rate %</th>
                <th class="px-6 py-4 text-right">Commission</th>
                <th class="px-6 py-4 text-right">Paid</th>
                <th class="px-6 py-4 text-center">Status</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
              <tr v-for="res in byReservation" :key="res.reservation_id">
                <td class="px-6 py-4 text-sm font-medium text-[#2a273c]">{{ res.reservation_code }}</td>
                <td class="px-6 py-4 text-sm text-slate-500">{{ res.guest_name }}</td>
                <td class="px-6 py-4 text-sm text-slate-500">{{ res.check_in }}</td>
                <td class="px-6 py-4 text-sm text-slate-500">{{ res.source_name }}</td>
                <td class="px-6 py-4 text-sm text-right">{{ formatCurrency(res.room_revenue) }}</td>
                <td class="px-6 py-4 text-sm text-right">{{ res.commission_rate }}%</td>
                <td class="px-6 py-4 text-sm text-right font-bold text-purple-600">{{ formatCurrency(res.commission_amount) }}</td>
                <td class="px-6 py-4 text-sm text-right" :class="res.paid_amount > 0 ? 'text-emerald-600' : 'text-slate-400'">
                  {{ formatCurrency(res.paid_amount) }}
                </td>
                <td class="px-6 py-4 text-sm text-center">
                  <span class="px-2 py-1 text-xs font-bold rounded-full" :class="res.status === 'Paid' ? 'bg-emerald-100 text-emerald-700' : 'bg-orange-100 text-orange-700'">
                    {{ res.status }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import { BarChart3 as BarChart3Icon } from 'lucide-vue-next';

const props = defineProps(['startDate', 'endDate']);
const filters = ref({
  start_date: props.startDate,
  end_date: props.endDate,
  source_id: ''
});
const loading = ref(true);
const summary = ref([]);
const byReservation = ref([]);
const paidUnpaid = ref({});
const rateComparison = ref([]);
const sources = ref([]);

const fetchData = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/reports/commission/generate', { params: filters.value });
    summary.value = response.data.summary;
    byReservation.value = response.data.by_reservation;
    paidUnpaid.value = response.data.paid_unpaid;
    rateComparison.value = response.data.rate_comparison;
    sources.value = response.data.rate_comparison;
  } catch (error) {
    console.error('Error fetching commission report:', error);
  } finally {
    loading.value = false;
  }
};

onMounted(fetchData);

const formatCurrency = (value) => {
  return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'SAR' }).format(value || 0);
};

const exportCSV = () => {
  const params = new URLSearchParams(filters.value);
  window.location.href = `/reports/commission/export?${params.toString()}&format=csv`;
};
</script>

<style scoped>
.shadow-sm { box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05); }
</style>
