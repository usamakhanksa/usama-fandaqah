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
      @click="openSetting(sc)"
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
  <Transition name="panel" mode="out-in">
    <div v-if="selected?.slug === 'header-slider'" key="slider" class="card p-6">
      <div class="flex items-center justify-between mb-6">
        <h2 class="text-lg font-bold">{{ selected.title }}</h2>
        <button @click="closePanel" class="text-slate-400 hover:text-slate-600 text-xl leading-none">✕</button>
      </div>
      <SliderSettingsPanel />
    </div>
    <div v-else-if="selected?.slug === 'stay-charges'" key="charges" class="card p-6">
      <div class="flex items-center justify-between mb-6">
        <h2 class="text-lg font-bold">{{ selected.title }}</h2>
        <button @click="closePanel" class="text-slate-400 hover:text-slate-600 text-xl leading-none">✕</button>
      </div>
      <StayChargeSettingsPanel />
    </div>
  </Transition>

  <!-- ── Modal for all other categories ── -->
  <BaseModal v-if="selected && selected.slug !== 'header-slider' && selected.slug !== 'stay-charges'" :modelValue="true" @close="closeModal" :title="selected.title">
    <div class="space-y-4">
      <div v-if="loading" class="text-center py-10">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-rose-500"></div>
        <p class="mt-2 text-slate-500">Loading settings…</p>
      </div>
      <div v-else class="space-y-2">
        <label v-for="item in data" :key="item.id" class="p-3 border rounded-xl flex items-center justify-between hover:bg-slate-50 transition-colors">
          <div class="flex-1">
            <span class="font-medium">{{ item.name || item.code }}</span>
            <span v-if="item.group" class="ml-2 text-xs text-slate-400 bg-slate-100 px-2 py-0.5 rounded">{{ item.group }}</span>
          </div>
          <input type="checkbox" class="accent-rose-500 w-5 h-5" :checked="item.is_active !== false" @change="toggleSetting(item)" />
        </label>
        <div v-if="!data.length" class="text-slate-400 text-center py-4">No settings found in this category.</div>
      </div>
      <div class="flex gap-2 pt-4">
        <button @click="resetSettings" class="btn-outline flex-1">Reset</button>
        <button @click="saveSettings" class="btn-primary flex-1">Save Changes</button>
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
import StayChargeSettingsPanel from '../components/StayChargeSettingsPanel.vue';

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
  { title: 'Early Check-in / Late Checkout', slug: 'stay-charges', icon: '⏰' },
];

const openSetting = (setting) => {
  selected.value = setting;
  if (setting.slug !== 'header-slider' && setting.slug !== 'stay-charges') {
    loadSettings();
  }
};

const closePanel = () => {
  selected.value = null;
};

const closeModal = () => {
  selected.value = null;
  data.value = [];
};

const loadSettings = async () => {
  if (!selected.value) return;
  loading.value = true;
  try {
    const res = await api.get(`/settings/${selected.value.slug}`);
    data.value = res.data || [];
  } catch (e) {
    console.error('Error loading settings:', e);
    data.value = [];
  }
  loading.value = false;
};

const toggleSetting = (item) => {
  item.is_active = !item.is_active;
};

const resetSettings = () => {
  loadSettings();
};

const saveSettings = async () => {
  try {
    await api.post('/settings/global', { category: selected.value.slug, data: data.value });
    closeModal();
  } catch (e) {
    console.error('Error saving settings:', e);
  }
};
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
