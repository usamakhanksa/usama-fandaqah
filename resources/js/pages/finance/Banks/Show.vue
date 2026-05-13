<template>
  <div class="p-6 bg-slate-50 min-h-screen">
    <div class="max-w-3xl mx-auto">
      <div class="flex items-center gap-4 mb-6">
        <Link :href="route('finance.banks.index')" class="p-2 bg-white border border-slate-200 rounded-lg text-slate-400 hover:text-slate-600 transition-all">
          <ArrowLeft class="w-5 h-5" />
        </Link>
        <div class="flex-1">
          <h1 class="text-2xl font-bold text-slate-800">{{ bank.name }}</h1>
          <p class="text-slate-500 text-sm">{{ bank.name_ar }}</p>
        </div>
        <Link :href="route('finance.banks.edit', bank.id)" class="px-4 py-2 bg-white border border-slate-200 rounded-lg text-slate-600 hover:bg-slate-50 transition-all flex items-center gap-2">
          <Pencil class="w-4 h-4" />
          {{ $t('Edit') }}
        </Link>
      </div>

      <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
        <div class="p-6">
          <div class="grid grid-cols-2 gap-8">
            <div>
              <p class="text-xs text-slate-400 uppercase tracking-wider mb-1">{{ $t('Bank Code (SWIFT)') }}</p>
              <p class="text-slate-700 font-medium">{{ bank.code || '-' }}</p>
            </div>
            <div>
              <p class="text-xs text-slate-400 uppercase tracking-wider mb-1">{{ $t('Currency') }}</p>
              <p class="text-slate-700 font-medium">{{ bank.currency }}</p>
            </div>
            <div>
              <p class="text-xs text-slate-400 uppercase tracking-wider mb-1">{{ $t('Branch') }}</p>
              <p class="text-slate-700 font-medium">{{ bank.branch || '-' }} ({{ bank.branch_code || '-' }})</p>
            </div>
            <div>
              <p class="text-xs text-slate-400 uppercase tracking-wider mb-1">{{ $t('Status') }}</p>
              <span class="px-2 py-1 rounded-full text-xs font-medium" :class="bank.is_active ? 'bg-emerald-100 text-emerald-600' : 'bg-slate-100 text-slate-400'">
                {{ bank.is_active ? $t('Active') : $t('Inactive') }}
              </span>
            </div>
            <div class="col-span-2 pt-4 border-t border-slate-100">
              <p class="text-xs text-slate-400 uppercase tracking-wider mb-1">{{ $t('Account Name') }}</p>
              <p class="text-slate-700 font-medium text-lg">{{ bank.account_name || '-' }}</p>
            </div>
            <div>
              <p class="text-xs text-slate-400 uppercase tracking-wider mb-1">{{ $t('Account Number') }}</p>
              <p class="text-slate-700 font-medium text-lg">{{ bank.account_number || '-' }}</p>
            </div>
            <div>
              <p class="text-xs text-slate-400 uppercase tracking-wider mb-1">{{ $t('IBAN') }}</p>
              <p class="text-slate-700 font-medium text-lg font-mono">{{ bank.iban || '-' }}</p>
            </div>
            <div class="col-span-2 pt-4 border-t border-slate-100">
              <p class="text-xs text-slate-400 uppercase tracking-wider mb-1">{{ $t('Notes') }}</p>
              <p class="text-slate-600 whitespace-pre-wrap">{{ bank.notes || '-' }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import { ArrowLeft, Pencil } from 'lucide-vue-next';

const props = defineProps({
  bank: Object
});
</script>
