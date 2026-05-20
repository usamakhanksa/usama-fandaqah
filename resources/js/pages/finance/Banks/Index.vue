<template>
  <div class="p-6 bg-slate-50 min-h-screen">
    <div class="flex justify-between items-center mb-6">
      <div>
        <h1 class="text-2xl font-bold text-slate-800">{{ $t('Banks') }}</h1>
        <p class="text-slate-500 text-sm mt-1">{{ $t('Manage hotel bank accounts') }}</p>
      </div>
      <div class="flex gap-3">
        <Link 
          :href="route('finance.banks.create')"
          class="flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-all shadow-md font-medium"
        >
          <Plus class="w-4 h-4" />
          {{ $t('Add Bank') }}
        </Link>
      </div>
    </div>

    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm">
      <table class="w-full text-left border-collapse">
        <thead>
          <tr class="bg-slate-50 border-b border-slate-200">
            <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ $t('Bank Name') }}</th>
            <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ $t('Account Details') }}</th>
            <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ $t('IBAN') }}</th>
            <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ $t('Status') }}</th>
            <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider text-right">{{ $t('Actions') }}</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <tr v-for="bank in banks" :key="bank.id" class="hover:bg-slate-50/50 transition-colors">
            <td class="px-6 py-4">
              <div class="font-medium text-slate-800">{{ bank.name }}</div>
              <div class="text-xs text-slate-400">{{ bank.name_ar }}</div>
            </td>
            <td class="px-6 py-4">
              <div class="text-sm text-slate-700">{{ bank.account_number }}</div>
              <div class="text-xs text-slate-400">{{ bank.branch }} ({{ bank.branch_code }})</div>
            </td>
            <td class="px-6 py-4">
              <div class="text-sm font-mono text-slate-600">{{ bank.iban }}</div>
              <div class="text-xs text-slate-400">{{ bank.code }} (SWIFT)</div>
            </td>
            <td class="px-6 py-4">
              <button 
                @click="toggleActive(bank)"
                class="px-2 py-1 rounded-full text-xs font-medium transition-colors"
                :class="bank.is_active ? 'bg-emerald-100 text-emerald-600' : 'bg-slate-100 text-slate-400'"
              >
                {{ bank.is_active ? $t('Active') : $t('Inactive') }}
              </button>
            </td>
            <td class="px-6 py-4 text-right">
              <div class="flex justify-end gap-2">
                <Link 
                  :href="route('finance.banks.show', bank.id)"
                  class="p-1.5 text-slate-400 hover:text-primary transition-colors"
                >
                  <Eye class="w-4 h-4" />
                </Link>
                <Link 
                  :href="route('finance.banks.edit', bank.id)"
                  class="p-1.5 text-slate-400 hover:text-blue-600 transition-colors"
                >
                  <Pencil class="w-4 h-4" />
                </Link>
                <button 
                  @click="deleteBank(bank.id)"
                  class="p-1.5 text-slate-400 hover:text-rose-600 transition-colors"
                >
                  <Trash2 class="w-4 h-4" />
                </button>
              </div>
            </td>
          </tr>
           <tr v-if="!banks || banks.length === 0">
            <td colspan="5" class="px-6 py-12 text-center text-slate-400">
              <div class="flex flex-col items-center gap-2">
                <Building2 class="w-12 h-12 text-slate-200" />
                <p>{{ $t('No banks found') }}</p>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3';
import { Plus, Eye, Pencil, Trash2, Building2 } from 'lucide-vue-next';

const props = defineProps({
  banks: Array
});

function toggleActive(bank) {
  router.post(route('finance.banks.toggle-active', bank.id), {}, {
    preserveScroll: true
  });
}

function deleteBank(id) {
  if (confirm('Are you sure you want to delete this bank?')) {
    router.delete(route('finance.banks.destroy', id));
  }
}
</script>

<style scoped>
.text-primary { color: #e95a54; }
.bg-primary { background-color: #e95a54; }
</style>
