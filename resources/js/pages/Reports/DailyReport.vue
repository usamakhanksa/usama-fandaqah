<template>
  <div class="p-6 bg-[#f8f9fa] min-h-full space-y-6 print:p-0 print:bg-white">
    <!-- Header/Filter -->
    <div class="flex justify-between items-center print:hidden">
      <div>
        <h1 class="text-3xl font-bold text-[#2a273c]">Daily Business Report</h1>
        <p class="text-slate-500">Summary for {{ selectedDate }}</p>
      </div>
      <div class="flex items-center gap-4">
        <button @click="previousDay" class="bg-white border border-slate-200 p-2 rounded-xl hover:bg-slate-50 transition-all">
          <ChevronLeftIcon class="w-5 h-5" />
        </button>
        <input 
          type="date" 
          v-model="selectedDate" 
          @change="fetchData"
          class="bg-white border border-slate-200 rounded-xl px-4 py-2 text-slate-700 focus:ring-2 focus:ring-[#e95a54] transition-all"
        />
        <button @click="nextDay" class="bg-white border border-slate-200 p-2 rounded-xl hover:bg-slate-50 transition-all">
          <ChevronRightIcon class="w-5 h-5" />
        </button>
        <button v-if="permissions.can_export" @click="window.print()" class="bg-[#2a273c] text-white px-6 py-2 rounded-xl font-semibold flex items-center gap-2 hover:bg-slate-800 transition-all">
          <PrinterIcon class="w-4 h-4" /> Print
        </button>
        <div v-if="permissions.can_export" class="relative">
          <button @click="toggleExportMenu" class="bg-white border border-slate-200 text-[#2a273c] px-6 py-2 rounded-xl font-semibold flex items-center gap-2 hover:bg-slate-50 transition-all">
            <DownloadIcon class="w-4 h-4" /> Export
          </button>
          <div v-if="showExportMenu" class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-xl border border-slate-100 z-50">
            <button @click="exportReport('pdf')" class="w-full text-left px-4 py-3 hover:bg-slate-50 text-sm font-medium border-b border-slate-50">Export as PDF</button>
            <button @click="exportReport('excel')" class="w-full text-left px-4 py-3 hover:bg-slate-50 text-sm font-medium">Export as Excel</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center py-20">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-[#e95a54]"></div>
    </div>

    <!-- Main Report Content -->
    <div v-else-if="report" class="space-y-6">
      <!-- Top KPIs Row -->
      <div class="grid grid-cols-12 gap-6">
        <!-- Occupancy Summary -->
        <div class="col-span-12 lg:col-span-4 bg-white p-8 rounded-3xl shadow-sm border border-slate-100 print:shadow-none print:border-slate-300">
          <h3 class="text-lg font-bold text-[#2a273c] mb-6 flex items-center gap-2">
            <HomeIcon class="w-5 h-5 text-[#e95a54]" /> Occupancy
          </h3>
          <div class="space-y-4">
            <div class="flex justify-between items-center">
              <span class="text-slate-500 font-medium">Total Rooms</span>
              <span class="font-bold text-[#2a273c]">{{ report.occupancy.total_rooms }}</span>
            </div>
            <div class="flex justify-between items-center">
              <span class="text-slate-500 font-medium">Occupied</span>
              <span class="font-bold text-emerald-600">{{ report.occupancy.occupied }}</span>
            </div>
            <div class="flex justify-between items-center">
              <span class="text-slate-500 font-medium">Available</span>
              <span class="font-bold text-blue-600">{{ report.occupancy.available }}</span>
            </div>
            <div class="flex justify-between items-center">
              <span class="text-slate-500 font-medium">Out of Order</span>
              <span class="font-bold text-red-500">{{ report.occupancy.ooo }}</span>
            </div>
            <div class="pt-4 border-t border-slate-50 flex justify-between items-center">
              <span class="font-bold text-[#2a273c]">Occupancy Rate</span>
              <span class="text-2xl font-black text-[#e95a54]">{{ report.occupancy.occupancy_rate }}%</span>
            </div>
          </div>
        </div>

        <!-- Revenue Summary -->
        <div class="col-span-12 lg:col-span-8 bg-white p-8 rounded-3xl shadow-sm border border-slate-100 print:shadow-none print:border-slate-300">
          <h3 class="text-lg font-bold text-[#2a273c] mb-6 flex items-center gap-2">
            <DollarSignIcon class="w-5 h-5 text-emerald-500" /> Revenue Summary
          </h3>
          <div class="grid grid-cols-3 gap-8">
            <div>
              <p class="text-slate-400 text-sm">Room Revenue</p>
              <p class="text-2xl font-bold text-[#2a273c]">{{ formatCurrency(report.room_revenue.total) }}</p>
            </div>
            <div>
              <p class="text-slate-400 text-sm">F&B Revenue</p>
              <p class="text-2xl font-bold text-[#2a273c]">{{ formatCurrency(report.fb_revenue.total) }}</p>
            </div>
            <div>
              <p class="text-slate-400 text-sm">Total Revenue</p>
              <p class="text-2xl font-bold text-emerald-600">{{ formatCurrency(report.total_revenue.total) }}</p>
            </div>
          </div>
          <div class="mt-8 grid grid-cols-2 gap-8 pt-8 border-t border-slate-50">
            <div class="flex justify-between items-center bg-slate-50 p-4 rounded-2xl">
              <span class="text-slate-600 font-medium">ADR</span>
              <span class="text-xl font-bold text-[#2a273c]">{{ formatCurrency(report.adr_revpar.adr) }}</span>
            </div>
            <div class="flex justify-between items-center bg-slate-50 p-4 rounded-2xl">
              <span class="text-slate-600 font-medium">RevPAR</span>
              <span class="text-xl font-bold text-[#2a273c]">{{ formatCurrency(report.adr_revpar.revpar) }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Arrivals & Departures -->
      <div class="grid grid-cols-12 gap-6">
        <!-- Arrivals -->
        <ReportSection
          title="Arrivals"
          :count="report.arrivals.count"
          icon-color="emerald"
          class="col-span-12 lg:col-span-6"
        >
          <table class="w-full">
            <thead class="bg-slate-50 text-slate-400 text-xs font-bold uppercase">
              <tr>
                <th class="px-6 py-4 text-left">Guest</th>
                <th class="px-6 py-4 text-left">Room</th>
                <th class="px-6 py-4 text-left">Nights</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
              <tr v-for="arrival in report.arrivals.list" :key="arrival.id">
                <td class="px-6 py-4 text-sm font-medium text-[#2a273c]">{{ arrival.guest_name }}</td>
                <td class="px-6 py-4 text-sm text-slate-500">{{ arrival.room_number }}</td>
                <td class="px-6 py-4 text-sm text-slate-500">{{ arrival.nights }}</td>
              </tr>
              <tr v-if="report.arrivals.count === 0">
                <td colspan="3" class="px-6 py-8 text-center text-slate-400">No arrivals</td>
              </tr>
            </tbody>
          </table>
        </ReportSection>

        <!-- Departures -->
        <ReportSection
          title="Departures"
          :count="report.departures.count"
          icon-color="red"
          class="col-span-12 lg:col-span-6"
        >
          <table class="w-full">
            <thead class="bg-slate-50 text-slate-400 text-xs font-bold uppercase">
              <tr>
                <th class="px-6 py-4 text-left">Guest</th>
                <th class="px-6 py-4 text-left">Room</th>
                <th class="px-6 py-4 text-left">Nights</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
              <tr v-for="departure in report.departures.list" :key="departure.id">
                <td class="px-6 py-4 text-sm font-medium text-[#2a273c]">{{ departure.guest_name }}</td>
                <td class="px-6 py-4 text-sm text-slate-500">{{ departure.room_number }}</td>
                <td class="px-6 py-4 text-sm text-slate-500">{{ departure.nights }}</td>
              </tr>
              <tr v-if="report.departures.count === 0">
                <td colspan="3" class="px-6 py-8 text-center text-slate-400">No departures</td>
              </tr>
            </tbody>
          </table>
        </ReportSection>
      </div>

      <!-- In-House Guests -->
      <ReportSection
        title="In-House Guests"
        :count="report.in_house.count"
        icon-color="blue"
        :collapsible="true"
      >
        <div class="p-6">
          <p class="text-lg">Total Room Revenue: <span class="font-bold text-emerald-600">{{ formatCurrency(report.in_house.room_revenue) }}</span></p>
        </div>
      </ReportSection>

      <!-- Payments by Method -->
      <ReportSection
        title="Payments Received"
        :count="report.payments.count"
        icon-color="purple"
      >
        <div class="p-6 grid grid-cols-2 md:grid-cols-4 gap-6">
          <div v-for="payment in report.payments.by_method" :key="payment.method" class="bg-slate-50 p-4 rounded-2xl">
            <p class="text-slate-400 text-xs font-bold uppercase mb-1">{{ payment.method }}</p>
            <p class="text-xl font-bold text-[#2a273c]">{{ formatCurrency(payment.total) }}</p>
            <p class="text-xs text-slate-400">{{ payment.count }} transactions</p>
          </div>
          <div v-if="report.payments.by_method.length === 0" class="col-span-4 text-center py-4 text-slate-400">
            No payments recorded
          </div>
        </div>
      </ReportSection>

      <!-- Taxes Section -->
      <ReportSection
        title="Taxes Collected"
        icon-color="indigo"
        :collapsible="true"
      >
        <div class="p-6 grid grid-cols-4 gap-6">
          <div class="bg-slate-50 p-4 rounded-2xl">
            <p class="text-slate-400 text-xs font-bold uppercase mb-1">VAT</p>
            <p class="text-xl font-bold text-[#2a273c]">{{ formatCurrency(report.taxes.vat_collected) }}</p>
          </div>
          <div class="bg-slate-50 p-4 rounded-2xl">
            <p class="text-slate-400 text-xs font-bold uppercase mb-1">Municipality Fee</p>
            <p class="text-xl font-bold text-[#2a273c]">{{ formatCurrency(report.taxes.municipality_fee) }}</p>
          </div>
          <div class="bg-slate-50 p-4 rounded-2xl">
            <p class="text-slate-400 text-xs font-bold uppercase mb-1">Tourism Levy</p>
            <p class="text-xl font-bold text-[#2a273c]">{{ formatCurrency(report.taxes.tourism_levy) }}</p>
          </div>
          <div class="bg-emerald-50 p-4 rounded-2xl">
            <p class="text-emerald-600 text-xs font-bold uppercase mb-1">Total Taxes</p>
            <p class="text-xl font-bold text-emerald-600">{{ formatCurrency(report.taxes.total_taxes) }}</p>
          </div>
        </div>
      </ReportSection>

      <!-- Comparison Section -->
      <div v-if="report.comparison" class="grid grid-cols-2 gap-6">
        <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100">
          <h3 class="text-lg font-bold text-[#2a273c] mb-6">vs Yesterday</h3>
          <div class="space-y-4">
            <ComparisonMetric 
              label="Revenue" 
              :current="report.comparison.vs_yesterday.revenue.current"
              :compare="report.comparison.vs_yesterday.revenue.compare"
              :percent="report.comparison.vs_yesterday.revenue.percent_change"
            />
            <ComparisonMetric 
              label="Occupancy" 
              :current="report.comparison.vs_yesterday.occupancy.current"
              :compare="report.comparison.vs_yesterday.occupancy.compare"
              :percent="report.comparison.vs_yesterday.occupancy.percent_change"
              unit="rooms"
            />
          </div>
        </div>
        <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100">
          <h3 class="text-lg font-bold text-[#2a273c] mb-6">vs Last Year</h3>
          <div class="space-y-4">
            <ComparisonMetric 
              label="Revenue" 
              :current="report.comparison.vs_last_year.revenue.current"
              :compare="report.comparison.vs_last_year.revenue.compare"
              :percent="report.comparison.vs_last_year.revenue.percent_change"
            />
            <ComparisonMetric 
              label="Occupancy" 
              :current="report.comparison.vs_last_year.occupancy.current"
              :compare="report.comparison.vs_last_year.occupancy.compare"
              :percent="report.comparison.vs_last_year.occupancy.percent_change"
              unit="rooms"
            />
          </div>
        </div>
      </div>

      <!-- Cashier Summary (if exists) -->
      <ReportSection
        v-if="report.cashier_summary && report.cashier_summary.shifts.length > 0"
        title="Cashier Summary"
        :count="report.cashier_summary.shifts.length"
        icon-color="amber"
        :collapsible="true"
      >
        <table class="w-full">
          <thead class="bg-slate-50 text-slate-400 text-xs font-bold uppercase">
            <tr>
              <th class="px-6 py-4 text-left">Cashier</th>
              <th class="px-6 py-4 text-left">Deposits</th>
              <th class="px-6 py-4 text-left">Withdrawals</th>
              <th class="px-6 py-4 text-left">Variance</th>
              <th class="px-6 py-4 text-left">Status</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-50">
            <tr v-for="shift in report.cashier_summary.shifts" :key="shift.cashier_name">
              <td class="px-6 py-4 text-sm font-medium text-[#2a273c]">{{ shift.cashier_name }}</td>
              <td class="px-6 py-4 text-sm text-emerald-600">{{ formatCurrency(shift.deposits) }}</td>
              <td class="px-6 py-4 text-sm text-red-600">{{ formatCurrency(shift.withdrawals) }}</td>
              <td class="px-6 py-4 text-sm" :class="shift.variance >= 0 ? 'text-emerald-600' : 'text-red-600'">
                {{ formatCurrency(shift.variance) }}
              </td>
              <td class="px-6 py-4 text-sm">
                <span class="px-2 py-1 rounded-full text-xs font-bold" :class="getStatusClass(shift.status)">
                  {{ shift.status }}
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </ReportSection>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { 
  Printer as PrinterIcon, 
  Download as DownloadIcon,
  Home as HomeIcon,
  DollarSign as DollarSignIcon,
  ChevronLeft as ChevronLeftIcon,
  ChevronRight as ChevronRightIcon
} from 'lucide-vue-next';

const props = defineProps({
  initialDate: String,
  permissions: {
    type: Object,
    default: () => ({ can_export: false, can_email: false })
  }
});

const selectedDate = ref(props.initialDate || new Date().toISOString().substr(0, 10));
const loading = ref(true);
const report = ref(null);
const showExportMenu = ref(false);

const fetchData = async () => {
  loading.value = true;
  try {
    const response = await axios.get(`/reports/daily/generate?date=${selectedDate.value}`);
    report.value = response.data;
  } catch (error) {
    console.error('Error fetching daily report:', error);
  } finally {
    loading.value = false;
  }
};

const previousDay = () => {
  const date = new Date(selectedDate.value);
  date.setDate(date.getDate() - 1);
  selectedDate.value = date.toISOString().substr(0, 10);
  fetchData();
};

const nextDay = () => {
  const date = new Date(selectedDate.value);
  date.setDate(date.getDate() + 1);
  selectedDate.value = date.toISOString().substr(0, 10);
  fetchData();
};

const toggleExportMenu = () => {
  showExportMenu.value = !showExportMenu.value;
};

const exportReport = (format) => {
  window.location.href = `/reports/daily/export?date=${selectedDate.value}&format=${format}`;
  showExportMenu.value = false;
};

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-SA', {
    style: 'currency',
    currency: 'SAR',
    minimumFractionDigits: 2
  }).format(amount || 0);
};

const getStatusClass = (status) => {
  const classes = {
    'open': 'bg-blue-50 text-blue-600',
    'closed': 'bg-slate-100 text-slate-600',
    'approved': 'bg-emerald-50 text-emerald-600',
  };
  return classes[status?.toLowerCase()] || 'bg-slate-50 text-slate-500';
};

onMounted(fetchData);
</script>

<script>
// Component definitions
export default {
  components: {
    ReportSection: {
      props: ['title', 'count', 'iconColor', 'collapsible'],
      data() {
        return { isCollapsed: false };
      },
      template: `
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden print:shadow-none print:border-slate-300">
          <div class="p-6 border-b border-slate-50 flex justify-between items-center" :class="collapsible ? 'cursor-pointer' : ''" @click="collapsible && (isCollapsed = !isCollapsed)">
            <h3 class="font-bold text-[#2a273c] flex items-center gap-2">
              {{ title }} <span v-if="count !== undefined" class="text-sm font-normal text-slate-400">({{ count }})</span>
            </h3>
            <button v-if="collapsible" class="text-slate-400 hover:text-slate-600">
              {{ isCollapsed ? '▼' : '▲' }}
            </button>
          </div>
          <div v-show="!isCollapsed">
            <slot></slot>
          </div>
        </div>
      `
    },
    ComparisonMetric: {
      props: ['label', 'current', 'compare', 'percent', 'unit'],
      template: `
        <div class="flex justify-between items-center p-4 bg-slate-50 rounded-xl">
          <div>
            <p class="text-sm text-slate-500">{{ label }}</p>
            <p class="text-xl font-bold text-[#2a273c]">
              {{ unit === 'rooms' ? current : formatMoney(current) }}
            </p>
          </div>
          <div class="text-right">
            <p class="text-xs text-slate-400">Change</p>
            <p class="text-lg font-bold" :class="percent >= 0 ? 'text-emerald-600' : 'text-red-600'">
              {{ percent >= 0 ? '+' : '' }}{{ percent }}%
            </p>
          </div>
        </div>
      `,
      methods: {
        formatMoney(val) {
          return new Intl.NumberFormat('en-SA', {
            style: 'currency',
            currency: 'SAR',
            minimumFractionDigits: 0
          }).format(val || 0);
        }
      }
    }
  }
};
</script>

<style scoped>
@media print {
  body { background: white; }
  .print\:hidden { display: none !important; }
  .print\:p-0 { padding: 0 !important; }
  .print\:bg-white { background: white !important; }
  .print\:shadow-none { box-shadow: none !important; }
  .print\:border-slate-300 { border-color: #cbd5e1 !important; }
}
.shadow-sm { box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05); }
</style>
