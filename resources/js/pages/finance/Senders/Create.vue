<template>
  <div class="p-6 bg-slate-50 min-h-screen">
    <div class="max-w-3xl mx-auto">
      <div class="flex items-center gap-4 mb-6">
        <Link :href="route('finance.senders.index')" class="p-2 bg-white border border-slate-200 rounded-lg text-slate-400 hover:text-slate-600 transition-all">
          <ArrowLeft class="w-5 h-5" />
        </Link>
        <div>
          <h1 class="text-2xl font-bold text-slate-800">{{ $t('Add New Sender') }}</h1>
          <p class="text-slate-500 text-sm">{{ $t('Register a new payment remitter') }}</p>
        </div>
      </div>

      <form @submit.prevent="submit" class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="md:col-span-2">
            <label class="block text-sm font-medium text-slate-700 mb-1">{{ $t('Sender Name') }} *</label>
            <input v-model="form.name" type="text" required class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-primary/20 outline-none transition-all">
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">{{ $t('Sender Name (Arabic)') }}</label>
            <input v-model="form.name_ar" type="text" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-primary/20 outline-none transition-all text-right">
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">{{ $t('Type') }} *</label>
            <select v-model="form.type" required class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-primary/20 outline-none transition-all">
              <option value="individual">{{ $t('Individual') }}</option>
              <option value="company">{{ $t('Company') }}</option>
              <option value="government">{{ $t('Government') }}</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">{{ $t('ID / CR Number') }}</label>
            <input v-model="form.id_number" type="text" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-primary/20 outline-none transition-all">
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">{{ $t('Phone Number') }}</label>
            <input v-model="form.phone" type="text" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-primary/20 outline-none transition-all">
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">{{ $t('Email Address') }}</label>
            <input v-model="form.email" type="email" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-primary/20 outline-none transition-all">
          </div>

          <div class="md:col-span-2">
            <label class="block text-sm font-medium text-slate-700 mb-1">{{ $t('Default Bank') }}</label>
            <select v-model="form.bank_id" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-primary/20 outline-none transition-all">
              <option :value="null">{{ $t('No Bank') }}</option>
              <option v-for="bank in banks" :key="bank.id" :value="bank.id">{{ bank.name }}</option>
            </select>
          </div>

          <div class="md:col-span-2">
            <label class="block text-sm font-medium text-slate-700 mb-1">{{ $t('Address') }}</label>
            <textarea v-model="form.address" rows="2" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-primary/20 outline-none transition-all"></textarea>
          </div>

          <div class="md:col-span-2">
            <label class="block text-sm font-medium text-slate-700 mb-1">{{ $t('Notes') }}</label>
            <textarea v-model="form.notes" rows="2" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-primary/20 outline-none transition-all"></textarea>
          </div>

          <div class="flex items-center gap-2">
            <input v-model="form.is_active" type="checkbox" id="is_active" class="w-4 h-4 text-primary focus:ring-primary border-slate-300 rounded">
            <label for="is_active" class="text-sm font-medium text-slate-700">{{ $t('Mark as Active') }}</label>
          </div>
        </div>

        <div class="p-6 bg-slate-50 border-t border-slate-200 flex justify-end gap-3">
          <Link :href="route('finance.senders.index')" class="px-6 py-2 border border-slate-200 rounded-lg text-slate-600 hover:bg-white transition-all font-medium">
            {{ $t('Cancel') }}
          </Link>
          <button type="submit" :disabled="form.processing" class="px-6 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-all shadow-md font-medium disabled:opacity-50">
            {{ $t('Save Sender') }}
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
  banks: Array
});

const form = useForm({
  name: '',
  name_ar: '',
  type: 'company',
  id_number: '',
  phone: '',
  email: '',
  address: '',
  bank_id: null,
  is_active: true,
  notes: '',
});

function submit() {
  form.post(route('finance.senders.store'));
}
</script>

<style scoped>
.text-primary { color: #e95a54; }
.bg-primary { background-color: #e95a54; }
</style>
