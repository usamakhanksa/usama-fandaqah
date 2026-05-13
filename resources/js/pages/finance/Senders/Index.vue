<template>
  <div class="p-6 bg-slate-50 min-h-screen">
    <div class="flex justify-between items-center mb-6">
      <div>
        <h1 class="text-2xl font-bold text-slate-800">{{ $t('Payment Senders') }}</h1>
        <p class="text-slate-500 text-sm mt-1">{{ $t('Manage payment remitters and wire transfer senders') }}</p>
      </div>
      <div class="flex gap-3">
        <Link 
          :href="route('finance.senders.create')"
          class="flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-all shadow-md font-medium"
        >
          <Plus class="w-4 h-4" />
          {{ $t('Add Sender') }}
        </Link>
      </div>
    </div>

    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm">
      <table class="w-full text-left border-collapse">
        <thead>
          <tr class="bg-slate-50 border-b border-slate-200">
            <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ $t('Sender Name') }}</th>
            <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ $t('Type') }}</th>
            <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ $t('Contact') }}</th>
            <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ $t('Status') }}</th>
            <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider text-right">{{ $t('Actions') }}</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <tr v-for="sender in senders" :key="sender.id" class="hover:bg-slate-50/50 transition-colors">
            <td class="px-6 py-4">
              <div class="font-medium text-slate-800">{{ sender.name }}</div>
              <div class="text-xs text-slate-400">{{ sender.name_ar }}</div>
            </td>
            <td class="px-6 py-4">
              <span class="px-2 py-1 rounded-full text-xs font-medium uppercase tracking-wider" :class="typeClass(sender.type)">
                {{ $t(sender.type) }}
              </span>
            </td>
            <td class="px-6 py-4">
              <div class="text-sm text-slate-700">{{ sender.phone }}</div>
              <div class="text-xs text-slate-400">{{ sender.email }}</div>
            </td>
            <td class="px-6 py-4">
              <button 
                @click="toggleActive(sender)"
                class="px-2 py-1 rounded-full text-xs font-medium transition-colors"
                :class="sender.is_active ? 'bg-emerald-100 text-emerald-600' : 'bg-slate-100 text-slate-400'"
              >
                {{ sender.is_active ? $t('Active') : $t('Inactive') }}
              </button>
            </td>
            <td class="px-6 py-4 text-right">
              <div class="flex justify-end gap-2">
                <Link 
                  :href="route('finance.senders.show', sender.id)"
                  class="p-1.5 text-slate-400 hover:text-primary transition-colors"
                >
                  <Eye class="w-4 h-4" />
                </Link>
                <Link 
                  :href="route('finance.senders.edit', sender.id)"
                  class="p-1.5 text-slate-400 hover:text-blue-600 transition-colors"
                >
                  <Pencil class="w-4 h-4" />
                </Link>
                <button 
                  @click="deleteSender(sender.id)"
                  class="p-1.5 text-slate-400 hover:text-rose-600 transition-colors"
                >
                  <Trash2 class="w-4 h-4" />
                </button>
              </div>
            </td>
          </tr>
          <tr v-if="senders.length === 0">
            <td colspan="5" class="px-6 py-12 text-center text-slate-400">
              <div class="flex flex-col items-center gap-2">
                <Users2 class="w-12 h-12 text-slate-200" />
                <p>{{ $t('No senders found') }}</p>
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
import { Plus, Eye, Pencil, Trash2, Users2 } from 'lucide-vue-next';

const props = defineProps({
  senders: Array
});

function toggleActive(sender) {
  router.post(route('finance.senders.toggle-active', sender.id), {}, {
    preserveScroll: true
  });
}

function deleteSender(id) {
  if (confirm('Are you sure you want to delete this sender?')) {
    router.delete(route('finance.senders.destroy', id));
  }
}

function typeClass(type) {
  switch (type) {
    case 'individual': return 'bg-blue-100 text-blue-600';
    case 'company': return 'bg-purple-100 text-purple-600';
    case 'government': return 'bg-amber-100 text-amber-600';
    default: return 'bg-slate-100 text-slate-600';
  }
}
</script>

<style scoped>
.text-primary { color: #e95a54; }
.bg-primary { background-color: #e95a54; }
</style>
