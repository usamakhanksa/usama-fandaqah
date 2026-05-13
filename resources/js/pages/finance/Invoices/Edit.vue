<template>
  <div class="p-6 bg-slate-50 min-h-screen pb-32">
    <div class="max-w-5xl mx-auto">
      <div class="flex justify-between items-center mb-6">
        <div>
          <h1 class="text-2xl font-bold text-slate-800">{{ $t('Edit Invoice') }}</h1>
          <p class="text-slate-500 text-sm">{{ invoice.invoice_number }}</p>
        </div>
        <Link :href="route('finance.invoices.show', invoice.id)" class="text-slate-500 hover:text-slate-800 flex items-center gap-2 font-medium">
          <ArrowLeft class="w-4 h-4" />
          {{ $t('Back to Invoice') }}
        </Link>
      </div>

      <form @submit.prevent="submit">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <div class="lg:col-span-2 space-y-6">
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
              <div class="grid grid-cols-2 gap-4 mb-6">
                <div>
                  <label class="block text-xs font-bold text-slate-400 uppercase mb-1">{{ $t('Invoice Type') }}</label>
                  <select v-model="form.zatca_invoice_type" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:ring-primary focus:border-primary">
                    <option value="simplified">{{ $t('Simplified Tax Invoice (B2C)') }}</option>
                    <option value="standard">{{ $t('Standard Tax Invoice (B2B)') }}</option>
                  </select>
                </div>
                <div>
                  <label class="block text-xs font-bold text-slate-400 uppercase mb-1">{{ $t('Invoice Date') }}</label>
                  <input v-model="form.invoice_date" type="datetime-local" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:ring-primary focus:border-primary" />
                </div>
              </div>

              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-xs font-bold text-slate-400 uppercase mb-1">{{ $t('Guest') }}</label>
                  <select v-model="form.guest_id" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm">
                    <option :value="null">{{ $t('Select Guest') }}</option>
                    <option v-for="guest in guests" :key="guest.id" :value="guest.id">{{ guest.name }}</option>
                  </select>
                </div>
                <div>
                  <label class="block text-xs font-bold text-slate-400 uppercase mb-1">{{ $t('Company') }}</label>
                  <select v-model="form.company_id" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm">
                    <option :value="null">{{ $t('Select Company') }}</option>
                    <option v-for="company in companies" :key="company.id" :value="company.id">{{ company.name }}</option>
                  </select>
                </div>
              </div>
            </div>

            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
              <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-4 flex items-center gap-2">
                <Receipt class="w-4 h-4 text-primary" />
                {{ $t('Invoice Items') }}
              </h2>

              <div class="space-y-4">
                <div v-for="(item, index) in form.items" :key="index" class="p-4 bg-slate-50 rounded-xl border border-slate-100 relative group">
                  <button @click="removeItem(index)" type="button" class="absolute -top-2 -right-2 bg-rose-500 text-white p-1 rounded-full opacity-0 group-hover:opacity-100 transition-opacity">
                    <X class="w-3 h-3" />
                  </button>
                  
                  <div class="grid grid-cols-12 gap-3">
                    <div class="col-span-6">
                      <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">{{ $t('Product / Service') }}</label>
                      <input v-model="item.product_name" type="text" class="w-full px-3 py-1.5 bg-white border border-slate-200 rounded text-sm" />
                    </div>
                    <div class="col-span-2">
                      <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">{{ $t('Qty') }}</label>
                      <input v-model.number="item.quantity" type="number" class="w-full px-3 py-1.5 bg-white border border-slate-200 rounded text-sm text-center" />
                    </div>
                    <div class="col-span-2">
                      <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">{{ $t('Price') }}</label>
                      <input v-model.number="item.unit_price" type="number" class="w-full px-3 py-1.5 bg-white border border-slate-200 rounded text-sm text-right" />
                    </div>
                    <div class="col-span-2 text-right">
                      <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">{{ $t('Total') }}</label>
                      <p class="py-1.5 text-sm font-bold text-slate-800">{{ formatCurrency(item.quantity * item.unit_price) }}</p>
                    </div>
                  </div>
                </div>
              </div>

              <button @click="addItem" type="button" class="mt-4 w-full py-2 border-2 border-dashed border-slate-200 rounded-xl text-slate-400 hover:text-primary hover:border-primary transition-all flex items-center justify-center gap-2 font-bold text-xs uppercase">
                <Plus class="w-4 h-4" />
                {{ $t('Add Item') }}
              </button>
            </div>
          </div>

          <div class="space-y-6">
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm sticky top-6">
              <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-4">{{ $t('Summary') }}</h2>
              
              <div class="space-y-3 border-b border-slate-100 pb-4 mb-4">
                <div class="flex justify-between text-sm">
                  <span class="text-slate-500">{{ $t('Subtotal') }}</span>
                  <span class="font-medium text-slate-700">{{ formatCurrency(totals.sub_total) }}</span>
                </div>
                <div class="flex justify-between text-sm">
                  <span class="text-slate-500">{{ $t('VAT (15%)') }}</span>
                  <span class="font-medium text-slate-700">{{ formatCurrency(totals.vat_amount) }}</span>
                </div>
              </div>

              <div class="flex justify-between items-end mb-6">
                <span class="text-slate-400 text-xs font-bold uppercase">{{ $t('Grand Total') }}</span>
                <span class="text-2xl font-black text-slate-800">{{ formatCurrency(totals.grand_total) }}</span>
              </div>

              <div class="space-y-3">
                <button type="submit" class="w-full py-3 bg-slate-800 text-white rounded-xl font-bold hover:bg-slate-700 transition-all shadow-md">
                  {{ $t('Update Invoice') }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { reactive, computed } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import { ArrowLeft, Plus, X, Receipt } from 'lucide-vue-next';
import dayjs from 'dayjs';

const props = defineProps({
  invoice: Object,
  guests: Array,
  companies: Array,
});

const form = useForm({
  zatca_invoice_type: props.invoice.zatca_invoice_type,
  invoice_date: dayjs(props.invoice.invoice_date).format('YYYY-MM-DDTHH:mm'),
  guest_id: props.invoice.guest_id,
  company_id: props.invoice.company_id,
  items: props.invoice.items.map(item => ({ ...item })),
});

const totals = computed(() => {
  let sub = 0;
  form.items.forEach(item => sub += item.quantity * item.unit_price);
  const vat = sub * 0.15;
  return { sub_total: sub, vat_amount: vat, grand_total: sub + vat };
});

function addItem() {
  form.items.push({ product_name: '', quantity: 1, unit_price: 0, discount_amount: 0, vat_percentage: 15 });
}

function removeItem(index) {
  if (form.items.length > 1) form.items.splice(index, 1);
}

function formatCurrency(amount) {
  return new Intl.NumberFormat('en-SA', { style: 'currency', currency: 'SAR' }).format(amount);
}

function submit() {
  form.put(route('finance.invoices.update', props.invoice.id));
}
</script>
