<template>
  <div class="p-6 bg-[#f8f9fa] min-h-full space-y-6">
    <!-- Header/Filter -->
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-3xl font-bold text-[#2a273c]">Trial Balance Report</h1>
        <p class="text-slate-500">General ledger trial balance</p>
      </div>
      <div class="flex items-center gap-4 bg-white p-2 rounded-2xl shadow-sm border border-slate-100">
        <div class="flex items-center gap-2 px-3">
          <span class="text-xs font-bold text-slate-400 uppercase">As of Date</span>
          <input type="date" v-model="asOfDate" @change="fetchData" class="border-none p-0 text-sm font-bold text-[#2a273c] focus:ring-0" />
        </div>
        <button @click="exportCSV" class="px-4 py-1.5 bg-emerald-500 text-white text-sm font-bold rounded-xl hover:bg-emerald-600">Export</button>
      </div>
    </div>

    <div v-if="loading" class="flex justify-center py-20">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-emerald-500"></div>
    </div>

    <div v-else class="space-y-6">
      <!-- Balance Status -->
      <div :class="['p-6 rounded-3xl shadow-sm border', totals.balanced ? 'bg-emerald-50 border-emerald-200' : 'bg-red-50 border-red-200']">
        <div class="flex items-center justify-between">
          <div>
            <h3 class="text-lg font-bold text-[#2a273c]">Trial Balance</h3>
            <p class="text-sm text-slate-500">As of {{ asOfDate }}</p>
          </div>
          <div class="text-right">
            <div class="text-sm text-slate-500">Total Debits</div>
            <div class="text-2xl font-bold text-[#2a273c]">{{ formatCurrency(totals.debits) }}</div>
            <div class="text-sm text-slate-500 mt-1">Total Credits</div>
            <div class="text-2xl font-bold text-[#2a273c]">{{ formatCurrency(totals.credits) }}</div>
          </div>
        </div>
        <div v-if="totals.balanced" class="mt-4 text-emerald-700 font-bold flex items-center gap-2">
          <CheckCircleIcon class="w-5 h-5" /> Balanced
        </div>
        <div v-else class="mt-4 text-red-700 font-bold flex items-center gap-2">
          <AlertTriangleIcon class="w-5 h-5" /> Difference: {{ formatCurrency(Math.abs(totals.debits - totals.credits)) }}
        </div>
      </div>

      <!-- Organized Accounts (by Category) -->
      <div v-for="(categoryAccounts, category) in organized" :key="category" 
           v-if="categoryAccounts && categoryAccounts.length"
           class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="p-6 border-b border-slate-50 bg-slate-50">
          <h3 class="font-bold text-[#2a273c] capitalize">{{ categoryLabel(category) }}</h3>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-slate-50 text-slate-400 text-xs font-bold uppercase">
              <tr>
                <th class="px-6 py-4 text-left">Account</th>
                <th class="px-6 py-4 text-right">Debit</th>
                <th class="px-6 py-4 text-right">Credit</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
              <tr v-for="account in categoryAccounts" :key="account.account_name">
                <td class="px-6 py-4 text-sm">{{ account.account_name }}</td>
                <td class="px-6 py-4 text-sm text-right font-medium text-emerald-600">
                  {{ account.debit ? formatCurrency(account.debit) : '-' }}
                </td>
                <td class="px-6 py-4 text-sm text-right font-medium text-red-600">
                  {{ account.credit ? formatCurrency(account.credit) : '-' }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Full Trial Balance Table -->
      <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="p-6 border-b border-slate-50">
          <h3 class="font-bold text-[#2a273c]">All Accounts</h3>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-slate-50 text-slate-400 text-xs font-bold uppercase">
              <tr>
                <th class="px-6 py-4 text-left">Account Name</th>
                <th class="px-6 py-4 text-right">Debit</th>
                <th class="px-6 py-4 text-right">Credit</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
              <tr v-for="account in trial_balance" :key="account.account_name">
                <td class="px-6 py-4 text-sm font-medium">{{ account.account_name }}</td>
                <td class="px-6 py-4 text-sm text-right text-emerald-600">
                  {{ account.debit ? formatCurrency(account.debit) : '-' }}
                </td>
                <td class="px-6 py-4 text-sm text-right text-red-600">
                  {{ account.credit ? formatCurrency(account.credit) : '-' }}
                </td>
              </tr>
              <tr class="bg-slate-50 font-bold">
                <td class="px-6 py-4 text-sm">TOTALS</td>
                <td class="px-6 py-4 text-right text-emerald-600">{{ formatCurrency(totals.debits) }}</td>
                <td class="px-6 py-4 text-right text-red-600">{{ formatCurrency(totals.credits) }}</td>
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
import { CheckCircle as CheckCircleIcon, AlertTriangle as AlertTriangleIcon } from 'lucide-vue-next';

const asOfDate = ref('2025-05-31');
const loading = ref(true);
const trial_balance = ref([]);
const organized = ref({});
const totals = ref({ debits: 0, credits: 0, balanced: false });

const fetchData = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/reports/trial-balance/generate', { params: { as_of_date: asOfDate.value } });
    trial_balance.value = response.data.trial_balance;
    organized.value = response.data.organized;
    totals.value = response.data.totals;
  } catch (error) {
    console.error('Error fetching trial balance:', error);
  } finally {
    loading.value = false;
  }
};

onMounted(fetchData);

const formatCurrency = (value) => {
  return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'SAR' }).format(value || 0);
};

const categoryLabel = (category) => {
  const labels = {
    'assets': 'Assets',
    'liabilities': 'Liabilities',
    'equity': 'Equity',
    'revenue': 'Revenue',
    'expenses': 'Expenses',
    'uncategorized': 'Uncategorized'
  };
  return labels[category] || category;
};

const exportCSV = () => {
  window.location.href = `/reports/trial-balance/export?as_of_date=${asOfDate.value}&format=csv`;
};
</script>

<style scoped>
.shadow-sm { box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05); }
</style>
