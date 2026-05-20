<template>
  <div class="p-6 bg-slate-50 min-h-screen">
    <div class="max-w-7xl mx-auto">
      <div class="flex justify-between items-center mb-6">
        <div>
          <h1 class="text-2xl font-bold text-slate-800">{{ $t('Invoices') }}</h1>
          <p class="text-slate-500 text-sm">{{ $t('Manage and report your tax invoices to ZATCA.') }}</p>
        </div>
        <div class="flex gap-3">
          <Link :href="route('finance.invoices.create')" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-all shadow-sm font-medium flex items-center gap-2">
            <Plus class="w-4 h-4" />
            {{ $t('Create Invoice') }}
          </Link>
          <button @click="exportInvoices" class="px-4 py-2 bg-white border border-slate-200 rounded-lg text-slate-600 hover:bg-slate-50 transition-all shadow-sm font-medium flex items-center gap-2">
            <Download class="w-4 h-4" />
            {{ $t('Export') }}
          </button>
        </div>
      </div>

      <!-- Stats Summary -->
      <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
          <p class="text-slate-400 text-[10px] font-bold uppercase tracking-wider mb-1">{{ $t('Total Invoices') }}</p>
          <p class="text-xl font-bold text-slate-800">{{ stats.total_count || 0 }}</p>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
          <p class="text-slate-400 text-[10px] font-bold uppercase tracking-wider mb-1">{{ $t('Total Amount') }}</p>
          <p class="text-xl font-bold text-emerald-600">{{ formatCurrency(stats.total_amount) }}</p>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
          <p class="text-slate-400 text-[10px] font-bold uppercase tracking-wider mb-1">{{ $t('Outstanding') }}</p>
          <p class="text-xl font-bold text-amber-600">{{ formatCurrency(stats.outstanding) }}</p>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm border-l-4 border-l-rose-500">
          <p class="text-slate-400 text-[10px] font-bold uppercase tracking-wider mb-1">{{ $t('Overdue') }}</p>
          <p class="text-xl font-bold text-rose-600">{{ formatCurrency(stats.overdue || 0) }}</p>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm border-l-4 border-l-blue-500">
          <p class="text-slate-400 text-[10px] font-bold uppercase tracking-wider mb-1">{{ $t('ZATCA Pending') }}</p>
          <p class="text-xl font-bold text-blue-600">{{ stats.zatca_pending || 0 }}</p>
        </div>
      </div>

      <!-- Filters -->
      <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm mb-6 flex flex-wrap gap-4 items-end">
        <div class="flex-1 min-w-[200px]">
          <label class="block text-xs font-bold text-slate-400 uppercase mb-1">{{ $t('Search') }}</label>
          <input v-model="filters.search" type="text" :placeholder="$t('Invoice #, Guest...')" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:ring-primary focus:border-primary" />
        </div>
        <div>
          <label class="block text-xs font-bold text-slate-400 uppercase mb-1">{{ $t('Status') }}</label>
          <select v-model="filters.status" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm">
            <option value="">{{ $t('All Statuses') }}</option>
            <option value="draft">{{ $t('Draft') }}</option>
            <option value="sent">{{ $t('Sent') }}</option>
            <option value="paid">{{ $t('Paid') }}</option>
            <option value="cancelled">{{ $t('Cancelled') }}</option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-bold text-slate-400 uppercase mb-1">{{ $t('ZATCA Status') }}</label>
          <select v-model="filters.zatca_status" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm">
            <option value="">{{ $t('All') }}</option>
            <option value="reported">{{ $t('Reported') }}</option>
            <option value="accepted">{{ $t('Accepted') }}</option>
            <option value="rejected">{{ $t('Rejected') }}</option>
            <option value="not_reported">{{ $t('Not Reported') }}</option>
          </select>
        </div>
        <div class="flex gap-2">
          <button @click="applyFilters" class="px-4 py-2 bg-slate-800 text-white rounded-lg text-sm font-bold">{{ $t('Apply') }}</button>
          <button @click="resetFilters" class="px-4 py-2 bg-slate-200 text-slate-600 rounded-lg text-sm font-bold">{{ $t('Reset') }}</button>
        </div>
      </div>

      <!-- Data Table -->
      <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <table class="w-full text-left">
          <thead class="bg-slate-50 border-b border-slate-200">
            <tr>
              <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase">{{ $t('Invoice #') }}</th>
              <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase">{{ $t('Date') }}</th>
              <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase">{{ $t('Guest / Company') }}</th>
              <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase text-right">{{ $t('Total') }}</th>
              <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase text-center">{{ $t('Status') }}</th>
              <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase text-center">{{ $t('ZATCA') }}</th>
              <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase text-right">{{ $t('Actions') }}</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-for="invoice in invoices?.data" :key="invoice.id" class="hover:bg-slate-50/50 transition-colors">
              <td class="px-6 py-4">
                <Link :href="route('finance.invoices.show', invoice.id)" class="font-bold text-primary hover:underline">
                  {{ invoice.invoice_number }}
                </Link>
                <p class="text-[10px] text-slate-400 uppercase font-bold">{{ invoice.zatca_invoice_type }}</p>
              </td>
              <td class="px-6 py-4 text-sm text-slate-600">
                {{ formatDate(invoice.invoice_date) }}
              </td>
              <td class="px-6 py-4">
                <div v-if="invoice.guest" class="flex items-center gap-2">
                  <User class="w-3 h-3 text-slate-400" />
                  <span class="text-sm font-medium text-slate-700">{{ invoice.guest.name }}</span>
                </div>
                <div v-else-if="invoice.company" class="flex items-center gap-2">
                  <Building2 class="w-3 h-3 text-slate-400" />
                  <span class="text-sm font-medium text-slate-700">{{ invoice.company.name }}</span>
                </div>
                <span v-else class="text-slate-300">-</span>
              </td>
              <td class="px-6 py-4 text-right font-bold text-slate-800">
                {{ formatCurrency(invoice.grand_total) }}
                <p class="text-[10px] text-slate-400 font-normal">VAT: {{ formatCurrency(invoice.vat_amount) }}</p>
              </td>
              <td class="px-6 py-4 text-center">
                <span :class="statusClass(invoice.status)" class="px-2 py-1 rounded text-[10px] font-bold uppercase">
                  {{ invoice.status }}
                </span>
              </td>
              <td class="px-6 py-4 text-center">
                <span :class="zatcaStatusClass(invoice.zatca_status)" class="px-2 py-1 rounded text-[10px] font-bold uppercase inline-flex items-center gap-1">
                  <CheckCircle2 v-if="invoice.zatca_status === 'accepted' || invoice.zatca_status === 'reported'" class="w-3 h-3" />
                  <XCircle v-if="invoice.zatca_status === 'rejected'" class="w-3 h-3" />
                  {{ invoice.zatca_status }}
                </span>
              </td>
              <td class="px-6 py-4 text-right">
                <div class="flex justify-end gap-2">
                  <button @click="printInvoice(invoice.id)" class="p-1.5 text-slate-400 hover:text-slate-600 transition-colors" v-tooltip="$t('Print')">
                    <Printer class="w-4 h-4" />
                  </button>
                  <button v-if="invoice.zatca_status === 'accepted' || invoice.zatca_status === 'reported'" @click="downloadXml(invoice.id)" class="p-1.5 text-blue-400 hover:text-blue-600 transition-colors" v-tooltip="$t('XML')">
                    <FileJson class="w-4 h-4" />
                  </button>
                  <Link v-if="invoice.status === 'draft'" :href="route('finance.invoices.edit', invoice.id)" class="p-1.5 text-amber-400 hover:text-amber-600 transition-colors" v-tooltip="$t('Edit')">
                    <Edit2 class="w-4 h-4" />
                  </Link>
                  <button v-if="invoice.zatca_status === 'not_reported' && invoice.status !== 'draft'" @click="sendToZatca(invoice.id)" class="p-1.5 text-emerald-400 hover:text-emerald-600 transition-colors" v-tooltip="$t('Submit to ZATCA')">
                    <Send class="w-4 h-4" />
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="!invoices?.data || invoices?.data.length === 0">
               <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                  {{ $t('No invoices found') }}
               </td>
            </tr>
          </tbody>
        </table>

        <!-- Pagination -->
        <div v-if="invoices?.data?.length > 0" class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex justify-between items-center">
          <p class="text-sm text-slate-500">
            Showing {{ invoices.from }} to {{ invoices.to }} of {{ invoices.total }} invoices
          </p>
          <div class="flex gap-2">
            <Link v-for="link in invoices.links" :key="link.label" :href="link.url" v-html="link.label" class="px-3 py-1 rounded border text-sm" :class="link.active ? 'bg-primary text-white border-primary' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'" />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { Plus, Download, Printer, Edit2, Send, FileJson, CheckCircle2, XCircle, User, Building2 } from 'lucide-vue-next';
import dayjs from 'dayjs';

const props = defineProps({
  invoices: {
    type: Object,
    default: () => ({ data: [] })
  },
  filters: {
    type: Object,
    default: () => ({})
  },
  stats: {
    type: Object,
    default: () => ({})
  },
});

const filters = reactive({ ...props.filters });

function formatCurrency(amount) {
  return new Intl.NumberFormat('en-SA', { style: 'currency', currency: 'SAR' }).format(amount);
}

function formatDate(date) {
  return dayjs(date).format('DD MMM YYYY');
}

function statusClass(status) {
  const classes = {
    draft: 'bg-slate-100 text-slate-600',
    sent: 'bg-blue-100 text-blue-600',
    confirmed: 'bg-emerald-100 text-emerald-600',
    paid: 'bg-emerald-500 text-white',
    cancelled: 'bg-rose-100 text-rose-600',
    void: 'bg-slate-800 text-white',
  };
  return classes[status] || 'bg-slate-100 text-slate-600';
}

function zatcaStatusClass(status) {
  const classes = {
    not_reported: 'bg-slate-100 text-slate-400',
    pending: 'bg-amber-100 text-amber-600',
    reported: 'bg-emerald-100 text-emerald-600',
    accepted: 'bg-emerald-500 text-white',
    rejected: 'bg-rose-500 text-white',
    error: 'bg-rose-100 text-rose-600',
  };
  return classes[status] || 'bg-slate-100 text-slate-400';
}

function applyFilters() {
  router.get(route('finance.invoices.index'), filters, { preserveState: true });
}

function resetFilters() {
  Object.keys(filters).forEach(k => filters[k] = '');
  applyFilters();
}

function sendToZatca(id) {
  if (confirm('Submit this invoice to ZATCA?')) {
    router.post(route('finance.invoices.zatca_submit', id));
  }
}

function downloadXml(id) {
  window.open(route('finance.invoices.zatca_download', id), '_blank');
}

function printInvoice(id) {
  window.open(route('finance.invoices.print', id), '_blank');
}

function exportInvoices() {
  window.open(route('finance.invoices.export', filters), '_blank');
}
</script>

<style scoped>
.text-primary { color: #e95a54; }
.bg-primary { background-color: #e95a54; }
.border-primary { border-color: #e95a54; }
</style>
