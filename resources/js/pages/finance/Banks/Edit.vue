<template>
  <div class="p-6 bg-slate-50 min-h-screen">
    <div class="max-w-3xl mx-auto">
      <div class="flex items-center gap-4 mb-6">
        <Link :href="route('finance.banks.index')" class="p-2 bg-white border border-slate-200 rounded-lg text-slate-400 hover:text-slate-600 transition-all">
          <ArrowLeft class="w-5 h-5" />
        </Link>
        <div>
          <h1 class="text-2xl font-bold text-slate-800">{{ $t('Edit Bank') }}</h1>
          <p class="text-slate-500 text-sm">{{ $t('Update bank account information') }}</p>
        </div>
      </div>

      <form @submit.prevent="submit" class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="md:col-span-2">
            <label class="block text-sm font-medium text-slate-700 mb-1">{{ $t('Bank Name') }} *</label>
            <input v-model="form.name" type="text" required class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-primary/20 outline-none transition-all">
            <div v-if="form.errors.name" class="text-xs text-rose-500 mt-1">{{ form.errors.name }}</div>
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">{{ $t('Bank Name (Arabic)') }}</label>
            <input v-model="form.name_ar" type="text" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-primary/20 outline-none transition-all text-right">
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">{{ $t('SWIFT / Bank Code') }}</label>
            <input v-model="form.code" type="text" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-primary/20 outline-none transition-all">
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">{{ $t('Branch Name') }}</label>
            <input v-model="form.branch" type="text" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-primary/20 outline-none transition-all">
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">{{ $t('Branch Code') }}</label>
            <input v-model="form.branch_code" type="text" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-primary/20 outline-none transition-all">
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">{{ $t('Account Number') }}</label>
            <input v-model="form.account_number" type="text" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-primary/20 outline-none transition-all">
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">{{ $t('IBAN') }}</label>
            <input v-model="form.iban" type="text" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-primary/20 outline-none transition-all">
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">{{ $t('Account Name') }}</label>
            <input v-model="form.account_name" type="text" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-primary/20 outline-none transition-all">
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">{{ $t('Currency') }} *</label>
            <select v-model="form.currency" required class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-primary/20 outline-none transition-all">
              <option value="SAR">SAR</option>
              <option value="USD">USD</option>
              <option value="EUR">EUR</option>
            </select>
          </div>

          <div class="md:col-span-2">
            <label class="block text-sm font-medium text-slate-700 mb-1">{{ $t('Notes') }}</label>
            <textarea v-model="form.notes" rows="3" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-primary/20 outline-none transition-all"></textarea>
          </div>

          <div class="flex items-center gap-2">
            <input v-model="form.is_active" type="checkbox" id="is_active" class="w-4 h-4 text-primary focus:ring-primary border-slate-300 rounded">
            <label for="is_active" class="text-sm font-medium text-slate-700">{{ $t('Mark as Active') }}</label>
          </div>
        </div>

        <div class="p-6 bg-slate-50 border-t border-slate-200 flex justify-end gap-3">
          <Link :href="route('finance.banks.index')" class="px-6 py-2 border border-slate-200 rounded-lg text-slate-600 hover:bg-white transition-all font-medium">
            {{ $t('Cancel') }}
          </Link>
          <button type="submit" :disabled="form.processing" class="px-6 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-all shadow-md font-medium disabled:opacity-50">
            {{ $t('Update Bank') }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';

const props = defineProps({
  bank: Object
});

const form = useForm({
  name: props.bank.name,
  name_ar: props.bank.name_ar,
  code: props.bank.code,
  branch: props.bank.branch,
  branch_code: props.bank.branch_code,
  account_number: props.bank.account_number,
  iban: props.bank.iban,
  account_name: props.bank.account_name,
  currency: props.bank.currency,
  is_active: props.bank.is_active,
  notes: props.bank.notes,
});

function submit() {
  form.put(route('finance.banks.update', props.bank.id));
}
</script>

<style scoped>
.text-primary { color: #e95a54; }
.bg-primary { background-color: #e95a54; }
</style>
