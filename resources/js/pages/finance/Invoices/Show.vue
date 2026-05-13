<template>
  <div class="p-6 bg-slate-50 min-h-screen pb-32">
    <div class="max-w-5xl mx-auto">
      <div class="flex justify-between items-center mb-6">
        <div class="flex items-center gap-4">
          <Link :href="route('finance.invoices.index')" class="p-2 bg-white border border-slate-200 rounded-lg text-slate-400 hover:text-slate-600 transition-all shadow-sm">
            <ArrowLeft class="w-5 h-5" />
          </Link>
          <div>
            <div class="flex items-center gap-3">
              <h1 class="text-2xl font-bold text-slate-800">{{ invoice.invoice_number }}</h1>
              <span :class="statusClass(invoice.status)" class="px-2 py-1 rounded text-[10px] font-bold uppercase tracking-wider">
                {{ invoice.status }}
              </span>
            </div>
            <p class="text-slate-500 text-sm flex items-center gap-2">
              <Calendar class="w-3 h-3" />
              {{ formatDate(invoice.invoice_date) }}
              <span class="text-slate-300">|</span>
              <span class="uppercase text-xs font-bold">{{ invoice.zatca_invoice_type }}</span>
            </p>
          </div>
        </div>
        <div class="flex gap-3">
          <button @click="printInvoice" class="px-4 py-2 bg-white border border-slate-200 rounded-lg text-slate-600 hover:bg-slate-50 transition-all shadow-sm font-bold flex items-center gap-2">
            <Printer class="w-4 h-4" />
            {{ $t('Print') }}
          </button>
          <button @click="downloadPdf" class="px-4 py-2 bg-white border border-slate-200 rounded-lg text-slate-600 hover:bg-slate-50 transition-all shadow-sm font-bold flex items-center gap-2">
            <FileText class="w-4 h-4" />
            {{ $t('PDF') }}
          </button>
          <Link v-if="invoice.status === 'draft'" :href="route('finance.invoices.edit', invoice.id)" class="px-4 py-2 bg-slate-800 text-white rounded-lg hover:bg-slate-700 transition-all shadow-sm font-bold flex items-center gap-2">
            <Edit2 class="w-4 h-4" />
            {{ $t('Edit') }}
          </Link>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Items Table -->
          <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
              <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider">{{ $t('Invoice Items') }}</h2>
            </div>
            <table class="w-full text-left">
              <thead class="bg-slate-50/50">
                <tr>
                  <th class="px-6 py-3 text-[10px] font-bold text-slate-400 uppercase">{{ $t('Description') }}</th>
                  <th class="px-6 py-3 text-[10px] font-bold text-slate-400 uppercase text-center">{{ $t('Qty') }}</th>
                  <th class="px-6 py-3 text-[10px] font-bold text-slate-400 uppercase text-right">{{ $t('Unit Price') }}</th>
                  <th class="px-6 py-3 text-[10px] font-bold text-slate-400 uppercase text-right">{{ $t('Total') }}</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100">
                <tr v-for="item in invoice.items" :key="item.id">
                  <td class="px-6 py-4">
                    <p class="text-sm font-bold text-slate-800">{{ item.product_name }}</p>
                    <p class="text-[10px] text-slate-400" v-if="item.description">{{ item.description }}</p>
                  </td>
                  <td class="px-6 py-4 text-sm text-slate-600 text-center">{{ item.quantity }}</td>
                  <td class="px-6 py-4 text-sm text-slate-600 text-right">{{ formatCurrency(item.unit_price) }}</td>
                  <td class="px-6 py-4 text-sm font-bold text-slate-800 text-right">{{ formatCurrency(item.total_amount) }}</td>
                </tr>
              </tbody>
            </table>
            
            <div class="p-6 bg-slate-50/50 border-t border-slate-100">
              <div class="w-64 ml-auto space-y-2">
                <div class="flex justify-between text-sm">
                  <span class="text-slate-500">{{ $t('Subtotal') }}</span>
                  <span class="text-slate-800 font-medium">{{ formatCurrency(invoice.sub_total) }}</span>
                </div>
                <div class="flex justify-between text-sm">
                  <span class="text-slate-500">{{ $t('Taxable Amount') }}</span>
                  <span class="text-slate-800 font-medium">{{ formatCurrency(invoice.taxable_amount) }}</span>
                </div>
                <div class="flex justify-between text-sm">
                  <span class="text-slate-500">{{ $t('VAT (15%)') }}</span>
                  <span class="text-slate-800 font-medium">{{ formatCurrency(invoice.vat_amount) }}</span>
                </div>
                <div class="flex justify-between text-lg font-black border-t border-slate-200 pt-2 mt-2">
                  <span class="text-slate-800">{{ $t('Total') }}</span>
                  <span class="text-primary">{{ formatCurrency(invoice.grand_total) }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- ZATCA Details -->
          <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex justify-between items-center mb-6">
              <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                <ShieldCheck class="w-4 h-4 text-emerald-500" />
                {{ $t('ZATCA Compliance') }}
              </h2>
              <span :class="zatcaStatusClass(invoice.zatca_status)" class="px-2 py-1 rounded text-[10px] font-bold uppercase">
                {{ invoice.zatca_status }}
              </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
              <div v-if="invoice.zatca_qr_code" class="flex flex-col items-center p-4 bg-slate-50 rounded-xl border border-slate-100">
                <p class="text-[10px] font-bold text-slate-400 uppercase mb-4">{{ $t('Verification QR Code') }}</p>
                <div class="bg-white p-4 rounded-lg shadow-sm border border-slate-200">
                  <img :src="`https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=${encodeURIComponent(invoice.zatca_qr_code)}`" alt="ZATCA QR" class="w-32 h-32" />
                </div>
                <p class="mt-4 text-[10px] text-slate-400 font-mono break-all text-center max-w-[200px]">{{ invoice.zatca_uuid }}</p>
              </div>
              <div class="space-y-4">
                <div v-if="invoice.zatca_hash">
                  <p class="text-[10px] font-bold text-slate-400 uppercase mb-1">{{ $t('Invoice Hash (SHA256)') }}</p>
                  <p class="text-xs font-mono text-slate-600 break-all bg-slate-50 p-2 rounded border border-slate-100">{{ invoice.zatca_hash }}</p>
                </div>
                <div class="flex flex-wrap gap-3">
                  <button v-if="invoice.zatca_xml" @click="downloadXml" class="flex-1 px-4 py-2 bg-blue-50 text-blue-600 rounded-lg text-xs font-bold hover:bg-blue-100 transition-all border border-blue-100 flex items-center justify-center gap-2">
                    <FileJson class="w-4 h-4" />
                    {{ $t('Download XML') }}
                  </button>
                  <button v-if="invoice.zatca_status === 'not_reported'" @click="sendToZatca" class="flex-1 px-4 py-2 bg-emerald-500 text-white rounded-lg text-xs font-bold hover:bg-emerald-600 transition-all shadow-sm flex items-center justify-center gap-2">
                    <Send class="w-4 h-4" />
                    {{ $t('Report to ZATCA') }}
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Sidebar Details -->
        <div class="space-y-6">
          <!-- Guest / Company Card -->
          <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
            <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-4">{{ $t('Customer Information') }}</h2>
            
            <div v-if="invoice.guest" class="space-y-4">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-400">
                  <User class="w-5 h-5" />
                </div>
                <div>
                  <p class="font-bold text-slate-800">{{ invoice.guest.name }}</p>
                  <p class="text-xs text-slate-500">{{ $t('Individual Guest') }}</p>
                </div>
              </div>
              <div class="space-y-2 pt-2 border-t border-slate-100">
                <p class="text-xs text-slate-600 flex items-center gap-2">
                  <Phone class="w-3 h-3 text-slate-400" /> {{ invoice.guest.phone }}
                </p>
                <p class="text-xs text-slate-600 flex items-center gap-2">
                  <Mail class="w-3 h-3 text-slate-400" /> {{ invoice.guest.email }}
                </p>
              </div>
            </div>

            <div v-else-if="invoice.company" class="space-y-4">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center text-blue-400">
                  <Building2 class="w-5 h-5" />
                </div>
                <div>
                  <p class="font-bold text-slate-800">{{ invoice.company.name }}</p>
                  <p class="text-xs text-slate-500">{{ $t('Corporate Client') }}</p>
                </div>
              </div>
              <div class="space-y-2 pt-2 border-t border-slate-100">
                <p class="text-xs text-slate-600 flex items-center gap-2 font-bold">
                  <Hash class="w-3 h-3 text-slate-400" /> {{ invoice.company.vat_number || 'No VAT' }}
                </p>
                <p class="text-xs text-slate-600 flex items-center gap-2">
                  <MapPin class="w-3 h-3 text-slate-400" /> {{ invoice.company.address || '-' }}
                </p>
              </div>
            </div>
          </div>

          <!-- Timeline -->
          <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
            <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-6">{{ $t('Invoice History') }}</h2>
            <div class="space-y-6 relative">
              <div class="absolute left-3 top-2 bottom-2 w-px bg-slate-100"></div>
              
              <div class="relative flex items-center gap-4">
                <div class="w-6 h-6 rounded-full bg-emerald-100 flex items-center justify-center z-10">
                  <div class="w-2 h-2 rounded-full bg-emerald-500"></div>
                </div>
                <div>
                  <p class="text-xs font-bold text-slate-700">{{ $t('Invoice Created') }}</p>
                  <p class="text-[10px] text-slate-400">{{ formatDate(invoice.created_at) }}</p>
                </div>
              </div>

              <div v-if="invoice.sent_at" class="relative flex items-center gap-4">
                <div class="w-6 h-6 rounded-full bg-blue-100 flex items-center justify-center z-10">
                  <div class="w-2 h-2 rounded-full bg-blue-500"></div>
                </div>
                <div>
                  <p class="text-xs font-bold text-slate-700">{{ $t('Invoice Issued') }}</p>
                  <p class="text-[10px] text-slate-400">{{ formatDate(invoice.sent_at) }}</p>
                </div>
              </div>

              <div v-if="invoice.zatca_submitted_at" class="relative flex items-center gap-4">
                <div class="w-6 h-6 rounded-full bg-emerald-500 flex items-center justify-center z-10">
                  <Check class="w-3 h-3 text-white" />
                </div>
                <div>
                  <p class="text-xs font-bold text-slate-700">{{ $t('ZATCA Reported') }}</p>
                  <p class="text-[10px] text-slate-400">{{ formatDate(invoice.zatca_submitted_at) }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Quick Actions -->
          <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
            <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-4">{{ $t('Payment Actions') }}</h2>
            <div class="space-y-3">
              <button v-if="invoice.status !== 'paid'" @click="markAsPaid" class="w-full py-2 bg-emerald-500 text-white rounded-xl font-bold text-xs uppercase hover:bg-emerald-600 transition-all shadow-sm">
                {{ $t('Mark as Paid') }}
              </button>
              <button v-if="invoice.status !== 'cancelled' && invoice.status !== 'void'" @click="cancelInvoice" class="w-full py-2 bg-white border border-rose-200 text-rose-500 rounded-xl font-bold text-xs uppercase hover:bg-rose-50 transition-all">
                {{ $t('Cancel Invoice') }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3';
import { ArrowLeft, Calendar, Printer, FileText, Edit2, ShieldCheck, FileJson, Send, User, Building2, Phone, Mail, Hash, MapPin, Check } from 'lucide-vue-next';
import dayjs from 'dayjs';

const props = defineProps({
  invoice: Object,
});

function formatCurrency(amount) {
  return new Intl.NumberFormat('en-SA', { style: 'currency', currency: 'SAR' }).format(amount);
}

function formatDate(date) {
  return dayjs(date).format('DD MMM YYYY HH:mm');
}

function statusClass(status) {
  const classes = {
    draft: 'bg-slate-100 text-slate-600',
    sent: 'bg-blue-100 text-blue-600',
    confirmed: 'bg-emerald-100 text-emerald-600',
    paid: 'bg-emerald-500 text-white',
    cancelled: 'bg-rose-100 text-rose-600',
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
  };
  return classes[status] || 'bg-slate-100 text-slate-400';
}

function sendToZatca() {
  if (confirm('Submit this invoice to ZATCA?')) {
    router.post(route('finance.invoices.zatca_submit', props.invoice.id));
  }
}

function downloadXml() {
  window.open(route('finance.invoices.zatca_download', props.invoice.id), '_blank');
}

function downloadPdf() {
  window.open(route('finance.invoices.download_pdf', props.invoice.id), '_blank');
}

function printInvoice() {
  window.open(route('finance.invoices.print', props.invoice.id), '_blank');
}

function markAsPaid() {
  if (confirm('Mark this invoice as paid?')) {
    router.post(route('finance.invoices.mark_paid', props.invoice.id));
  }
}

function cancelInvoice() {
  const reason = prompt('Reason for cancellation:');
  if (reason) {
    router.post(route('finance.invoices.cancel', props.invoice.id), { reason });
  }
}
</script>

<style scoped>
.text-primary { color: #e95a54; }
.bg-primary { background-color: #e95a54; }
</style>
