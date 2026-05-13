<template>
  <div class="space-y-6">
    <!-- Header with Toggle -->
    <div class="flex items-center justify-between">
      <div>
        <h3 class="text-lg font-bold text-slate-800">Early Check-in & Late Checkout Configs</h3>
        <p class="text-sm text-slate-500">Configure charge tiers for early arrivals and late departures.</p>
      </div>
      <div class="flex gap-2">
        <button @click="showAddModal('early_checkin')" class="btn-primary flex items-center gap-2">
          <span class="text-lg">+</span> Early Check-in Tier
        </button>
        <button @click="showAddModal('late_checkout')" class="btn-outline flex items-center gap-2 border-rose-200 text-rose-600">
          <span class="text-lg">+</span> Late Checkout Tier
        </button>
      </div>
    </div>

    <!-- 24-Hour Timeline Visualization -->
    <div class="card p-6 bg-slate-50/50">
      <div class="flex justify-between items-center mb-4">
        <h4 class="font-semibold text-slate-700">24-Hour Timeline Preview</h4>
        <div class="flex gap-4 text-xs">
          <div class="flex items-center gap-1">
            <div class="w-3 h-3 rounded-full bg-emerald-400"></div>
            <span>Early Check-in</span>
          </div>
          <div class="flex items-center gap-1">
            <div class="w-3 h-3 rounded-full bg-orange-400"></div>
            <span>Late Checkout</span>
          </div>
        </div>
      </div>
      
      <div class="relative h-24 bg-white rounded-xl border border-slate-200 overflow-hidden group">
        <!-- Hour markers -->
        <div class="absolute inset-0 flex justify-between px-2 pointer-events-none">
          <div v-for="h in 25" :key="h" class="h-full border-l border-slate-100 relative">
            <span v-if="(h-1) % 4 === 0" class="absolute -bottom-1 left-1 text-[10px] text-slate-400">{{ h-1 }}:00</span>
          </div>
        </div>

        <!-- Tiers -->
        <div 
          v-for="tier in configs" 
          :key="tier.id"
          class="absolute top-4 bottom-8 rounded-lg cursor-pointer transition-all hover:brightness-95 hover:shadow-sm"
          :style="getTierStyle(tier)"
          @click="editTier(tier)"
        >
          <div class="px-2 py-1 text-[10px] font-bold text-white truncate h-full flex items-center justify-center">
             {{ tier.rate_amount }}{{ tier.rate_type === 'fixed' ? ' SAR' : '%' }}
          </div>
        </div>
      </div>
    </div>

    <!-- Tiers List -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <!-- Early Check-in -->
      <div class="space-y-4">
        <h4 class="font-bold text-slate-700 flex items-center gap-2">
          <div class="w-2 h-6 bg-emerald-400 rounded-full"></div>
          Early Check-in Tiers
        </h4>
        <div v-if="earlyConfigs.length === 0" class="p-8 border-2 border-dashed border-slate-200 rounded-2xl text-center text-slate-400 italic">
          No early check-in tiers defined.
        </div>
        <div v-for="tier in earlyConfigs" :key="tier.id" class="card p-4 flex justify-between items-center hover:shadow-md transition-shadow">
          <div>
            <div class="flex items-center gap-2">
              <span class="font-bold text-slate-800">{{ formatTime(tier.tier_from_hour) }} - {{ formatTime(tier.tier_to_hour) }}</span>
              <span class="px-2 py-0.5 rounded-full text-[10px] bg-emerald-50 text-emerald-600 font-bold uppercase tracking-wider">Active</span>
            </div>
            <div class="text-xs text-slate-500 mt-1">
              Rate: {{ tier.rate_amount }} {{ formatRateType(tier.rate_type) }} • Applies to: {{ tier.applies_to }}
            </div>
          </div>
          <div class="flex gap-2">
            <button @click="editTier(tier)" class="p-2 text-slate-400 hover:text-blue-500 transition-colors">✏️</button>
            <button @click="deleteTier(tier.id)" class="p-2 text-slate-400 hover:text-rose-500 transition-colors">🗑️</button>
          </div>
        </div>
      </div>

      <!-- Late Checkout -->
      <div class="space-y-4">
        <h4 class="font-bold text-slate-700 flex items-center gap-2">
          <div class="w-2 h-6 bg-orange-400 rounded-full"></div>
          Late Checkout Tiers
        </h4>
        <div v-if="lateConfigs.length === 0" class="p-8 border-2 border-dashed border-slate-200 rounded-2xl text-center text-slate-400 italic">
          No late checkout tiers defined.
        </div>
        <div v-for="tier in lateConfigs" :key="tier.id" class="card p-4 flex justify-between items-center hover:shadow-md transition-shadow">
          <div>
            <div class="flex items-center gap-2">
              <span class="font-bold text-slate-800">{{ formatTime(tier.tier_from_hour) }} - {{ formatTime(tier.tier_to_hour) }}</span>
              <span class="px-2 py-0.5 rounded-full text-[10px] bg-orange-50 text-orange-600 font-bold uppercase tracking-wider">Active</span>
            </div>
            <div class="text-xs text-slate-500 mt-1">
              Rate: {{ tier.rate_amount }} {{ formatRateType(tier.rate_type) }} • Applies to: {{ tier.applies_to }}
            </div>
          </div>
          <div class="flex gap-2">
            <button @click="editTier(tier)" class="p-2 text-slate-400 hover:text-blue-500 transition-colors">✏️</button>
            <button @click="deleteTier(tier.id)" class="p-2 text-slate-400 hover:text-rose-500 transition-colors">🗑️</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Add/Edit Modal -->
    <BaseModal v-if="showModal" @close="showModal = false" :title="form.id ? 'Edit Tier' : 'Add New Tier'">
      <div class="space-y-4 p-2">
        <div class="grid grid-cols-2 gap-4">
          <div class="col-span-2">
            <label class="block text-sm font-medium text-slate-700 mb-1">Charge Type</label>
            <select v-model="form.charge_type" class="w-full rounded-xl border-slate-200 focus:ring-rose-500 focus:border-rose-500">
              <option value="early_checkin">Early Check-in</option>
              <option value="late_checkout">Late Checkout</option>
            </select>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">From Hour</label>
            <input type="time" v-model="form.tier_from_hour" class="w-full rounded-xl border-slate-200 focus:ring-rose-500 focus:border-rose-500" />
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">To Hour</label>
            <input type="time" v-model="form.tier_to_hour" class="w-full rounded-xl border-slate-200 focus:ring-rose-500 focus:border-rose-500" />
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Rate Type</label>
            <select v-model="form.rate_type" class="w-full rounded-xl border-slate-200 focus:ring-rose-500 focus:border-rose-500">
              <option value="fixed">Fixed (SAR)</option>
              <option value="percentage_first_night">% of First Night</option>
              <option value="percentage_nightly_rate">% of Nightly Rate</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Amount</label>
            <input type="number" v-model="form.rate_amount" step="0.01" class="w-full rounded-xl border-slate-200 focus:ring-rose-500 focus:border-rose-500" />
          </div>

          <div class="col-span-2">
            <label class="block text-sm font-medium text-slate-700 mb-1">Applies To (Room Types)</label>
            <select v-model="form.applies_to" class="w-full rounded-xl border-slate-200 focus:ring-rose-500 focus:border-rose-500">
              <option value="all">All Room Types</option>
              <option v-for="type in unitTypes" :key="type.id" :value="type.id">{{ type.name }}</option>
            </select>
          </div>

          <div class="col-span-2 flex items-center gap-2">
            <input type="checkbox" v-model="form.is_active" id="is_active" class="rounded border-slate-300 text-rose-600 focus:ring-rose-500" />
            <label for="is_active" class="text-sm text-slate-700">Active and Enabled</label>
          </div>
        </div>

        <div class="flex gap-2 pt-4">
          <button @click="showModal = false" class="btn-outline flex-1">Cancel</button>
          <button @click="saveTier" class="btn-primary flex-1" :disabled="saving">
            {{ saving ? 'Saving...' : 'Save Configuration' }}
          </button>
        </div>
      </div>
    </BaseModal>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import api from '../services/api';
import BaseModal from './BaseModal.vue';

const toast = {
  success: (msg) => alert(msg),
  error: (msg) => alert(msg)
};
const configs = ref([]);
const unitTypes = ref([]);
const showModal = ref(false);
const saving = ref(false);
const loading = ref(false);

const form = ref({
  id: null,
  charge_type: 'early_checkin',
  tier_from_hour: '08:00',
  tier_to_hour: '12:00',
  rate_type: 'fixed',
  rate_amount: 0,
  applies_to: 'all',
  is_active: true
});

const earlyConfigs = computed(() => configs.value.filter(c => c.charge_type === 'early_checkin'));
const lateConfigs = computed(() => configs.value.filter(c => c.charge_type === 'late_checkout'));

const fetchData = async () => {
  loading.value = true;
  try {
    const [configRes, typeRes] = await Promise.all([
      api.get('/stay-charge-configs'),
      api.get('/units/filters')
    ]);
    configs.value = configRes.data;
    unitTypes.value = typeRes.data.types;
  } catch (error) {
    toast.error('Failed to load configurations');
  } finally {
    loading.value = false;
  }
};

const showAddModal = (type) => {
  form.value = {
    id: null,
    charge_type: type,
    tier_from_hour: type === 'early_checkin' ? '08:00' : '14:00',
    tier_to_hour: type === 'early_checkin' ? '12:00' : '18:00',
    rate_type: 'fixed',
    rate_amount: 0,
    applies_to: 'all',
    is_active: true
  };
  showModal.value = true;
};

const editTier = (tier) => {
  form.value = { ...tier };
  // Ensure time format is HH:mm for input
  form.value.tier_from_hour = tier.tier_from_hour.substring(0, 5);
  form.value.tier_to_hour = tier.tier_to_hour.substring(0, 5);
  showModal.value = true;
};

const saveTier = async () => {
  saving.value = true;
  try {
    if (form.value.id) {
      await api.put(`/stay-charge-configs/${form.value.id}`, form.value);
      toast.success('Configuration updated');
    } else {
      await api.post('/stay-charge-configs', form.value);
      toast.success('Configuration created');
    }
    showModal.value = false;
    fetchData();
  } catch (error) {
    toast.error(error.response?.data?.message || 'Failed to save configuration');
  } finally {
    saving.value = false;
  }
};

const deleteTier = async (id) => {
  if (!confirm('Are you sure you want to delete this configuration?')) return;
  try {
    await api.delete(`/stay-charge-configs/${id}`);
    toast.success('Configuration deleted');
    fetchData();
  } catch (error) {
    toast.error('Failed to delete configuration');
  }
};

const formatTime = (time) => {
  return time.substring(0, 5);
};

const formatRateType = (type) => {
  const map = {
    fixed: 'Fixed (SAR)',
    percentage_first_night: '% of First Night',
    percentage_nightly_rate: '% of Nightly Rate'
  };
  return map[type] || type;
};

const getTierStyle = (tier) => {
  const start = timeToPercent(tier.tier_from_hour);
  const end = timeToPercent(tier.tier_to_hour);
  const width = end - start;
  
  return {
    left: `${start}%`,
    width: `${width}%`,
    backgroundColor: tier.charge_type === 'early_checkin' ? '#34d399' : '#fb923c'
  };
};

const timeToPercent = (time) => {
  const [h, m] = time.split(':').map(Number);
  return ((h * 60 + m) / (24 * 60)) * 100;
};

onMounted(fetchData);
</script>
