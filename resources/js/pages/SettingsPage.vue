<template>
<div class="p-6 space-y-6">
  <!-- Page Header -->
  <div class="card p-4 flex justify-between items-center">
    <h1 class="text-2xl font-bold">Settings</h1>
    <p class="text-sm text-slate-400">Home &gt; Settings</p>
  </div>

  <!-- Category Grid -->
  <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
    <button
      v-for="sc in categories"
      :key="sc.slug"
      @click="selected = sc"
      :class="[
        'card p-6 flex flex-col items-center justify-center gap-3 hover:shadow-md transition-all active:scale-95 group',
        selected?.slug === sc.slug ? 'ring-2 ring-rose-400' : ''
      ]"
    >
      <div class="w-12 h-12 rounded-2xl bg-slate-50 flex items-center justify-center text-2xl group-hover:bg-rose-50 transition-colors">
        {{ sc.icon }}
      </div>
      <span class="font-semibold text-center text-sm">{{ sc.title }}</span>
    </button>
  </div>

  <!-- ── Slider & Header inline panel ── -->
  <Transition name="panel">
    <div v-if="selected?.slug === 'header-slider'" class="card p-6">
      <div class="flex items-center justify-between mb-6">
        <h2 class="text-lg font-bold">{{ selected.title }}</h2>
        <button @click="selected = null" class="text-slate-400 hover:text-slate-600 text-xl leading-none">✕</button>
      </div>
      <SliderSettingsPanel />
    </div>
  </Transition>

  <!-- ── Modal for all other categories ── -->
  <BaseModal v-if="selected && selected.slug !== 'header-slider'" @close="selected = null" :title="selected.title">
    <div class="space-y-4">
      <div v-if="loading" class="text-center py-10">Loading settings…</div>
      <div v-else class="space-y-2">
        <label v-for="item in data" :key="item.id" class="p-3 border rounded-xl flex items-center justify-between">
          <span>{{ item.name || item.code }}</span>
          <span v-if="item.group" class="text-xs text-slate-400">{{ item.group }}</span>
          <input type="checkbox" class="accent-rose-500" checked />
        </label>
        <div v-if="!data.length" class="text-slate-400 text-center py-4">No settings found in this category.</div>
      </div>
      <div class="flex gap-2 pt-4">
        <button @click="selected = null" class="btn-outline flex-1">Reset</button>
        <button @click="selected = null" class="btn-primary flex-1">Save Changes</button>
      </div>
    </div>
  </BaseModal>
</div>
</template>

<script setup>
import { ref, watch } from 'vue';
import api from '../services/api';
import BaseModal from '../components/BaseModal.vue';
import SliderSettingsPanel from '../components/SliderSettingsPanel.vue';

const selected = ref(null);
const loading = ref(false);
const data = ref([]);

const categories = [
  { title: 'Header & Slider', slug: 'header-slider', icon: '🎠' },
  { title: 'General Settings', slug: 'general', icon: '⚙️' },
  { title: 'Facility Settings', slug: 'facility', icon: '🏢' },
  { title: 'Hotel Amenities', slug: 'amenities', icon: '🏨' },
  { title: 'Integration Settings', slug: 'integrations', icon: '🔌' },
  { title: 'Users And Roles', slug: 'users-roles', icon: '👥' },
  { title: 'Document Settings', slug: 'documents', icon: '🧾' },
  { title: 'Notifications Settings', slug: 'notifications', icon: '🔔' },
  { title: 'Finance Settings', slug: 'finance', icon: '💰' },
  { title: 'Activity Logs', slug: 'activity-logs', icon: '📑' },
  { title: 'Ledger Numbers', slug: 'ledger-numbers', icon: '🔢' },
  { title: 'Reservation Resource Settings', slug: 'reservation-resources', icon: '📅' },
  { title: 'Customer Groups Settings', slug: 'customer-groups', icon: '🔗' },
  { title: 'Website Settings', slug: 'website', icon: '🌐' },
  { title: 'Rating Settings', slug: 'rating', icon: '⭐' },
  { title: 'Services Included In The Price', slug: 'included-services', icon: '🛎️' },
  { title: 'Maintenance Settings', slug: 'maintenance-categories', icon: '🛠️' },
];

watch(selected, async (val) => {
  if (val && val.slug !== 'header-slider') {
    loading.value = true;
    try {
      const res = await api.get(`/settings/${val.slug}`);
      data.value = res.data;
    } catch {
      data.value = [];
    }
    loading.value = false;
  }
});
</script>

<style scoped>
.panel-enter-active, .panel-leave-active {
  transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
}
.panel-enter-from, .panel-leave-to {
  opacity: 0;
  transform: translateY(12px);
}
</style>
