<template>
  <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
    <div class="bg-[#f2f0eb] w-full max-w-2xl rounded-2xl shadow-2xl overflow-hidden border border-[#fbcdab]">
      <!-- Header -->
      <div class="bg-[#2a273c] px-8 py-6 flex items-center justify-between">
        <h1 class="text-white text-xl font-bold">
          {{ isView ? 'View Service Category' : (isEdit ? 'Edit Service Category' : 'Add New Service Category') }}
        </h1>
        <button @click="$emit('close')" class="text-white/60 hover:text-white transition-colors">
          <i class="pi pi-times text-lg"></i>
        </button>
      </div>

      <!-- Form -->
      <form @submit.prevent="submit" class="p-8 space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Name EN -->
          <div class="space-y-2">
            <label class="text-[#2a273c] font-bold text-sm">Category Name (en)</label>
            <input 
              v-model="form.name_en" 
              type="text" 
              placeholder="e.g. Food & Beverage"
              class="w-full bg-white border-2 border-transparent focus:border-[#e95a54] rounded-xl px-4 py-3 outline-none transition-all shadow-sm disabled:opacity-75 disabled:cursor-not-allowed"
              required
              :disabled="isView"
            >
          </div>

          <!-- Name AR -->
          <div class="space-y-2">
            <label class="text-[#2a273c] font-bold text-sm">Category Name (ar)</label>
            <input 
              v-model="form.name_ar" 
              type="text" 
              placeholder="مثال: الخدمات الغذائية"
              class="w-full bg-white border-2 border-transparent focus:border-[#e95a54] rounded-xl px-4 py-3 outline-none transition-all shadow-sm dir-rtl text-right disabled:opacity-75 disabled:cursor-not-allowed"
              required
              :disabled="isView"
            >
          </div>
        </div>

        <!-- Toggles Section -->
        <div class="bg-white rounded-2xl p-6 grid grid-cols-1 md:grid-cols-3 gap-6 shadow-sm border border-[#fbcdab]/30">
          <div class="flex flex-col items-center gap-3">
            <label class="text-[#2a273c] text-xs font-bold uppercase tracking-wider">Status</label>
            <button 
              type="button"
              @click="!isView && (form.status = !form.status)"
              :class="form.status ? 'bg-[#8f9793]' : 'bg-slate-200'"
              class="w-14 h-7 rounded-full p-1 transition-all relative flex items-center disabled:cursor-not-allowed"
              :disabled="isView"
            >
              <div :class="form.status ? 'translate-x-7' : 'translate-x-0'" class="w-5 h-5 bg-white rounded-full shadow-md transition-transform transform"></div>
            </button>
          </div>

          <div class="flex flex-col items-center gap-3 border-x border-slate-100">
            <label class="text-[#2a273c] text-xs font-bold uppercase tracking-wider text-center px-2 leading-tight">Show in Reservation</label>
            <button 
              type="button"
              @click="!isView && (form.show_in_reservation = !form.show_in_reservation)"
              :class="form.show_in_reservation ? 'bg-[#8f9793]' : 'bg-slate-200'"
              class="w-14 h-7 rounded-full p-1 transition-all relative flex items-center disabled:cursor-not-allowed"
              :disabled="isView"
            >
              <div :class="form.show_in_reservation ? 'translate-x-7' : 'translate-x-0'" class="w-5 h-5 bg-white rounded-full shadow-md transition-transform transform"></div>
            </button>
          </div>

          <div class="flex flex-col items-center gap-3">
            <label class="text-[#2a273c] text-xs font-bold uppercase tracking-wider">Show in POS</label>
            <button 
              type="button"
              @click="!isView && (form.show_in_pos = !form.show_in_pos)"
              :class="form.show_in_pos ? 'bg-[#8f9793]' : 'bg-slate-200'"
              class="w-14 h-7 rounded-full p-1 transition-all relative flex items-center disabled:cursor-not-allowed"
              :disabled="isView"
            >
              <div :class="form.show_in_pos ? 'translate-x-7' : 'translate-x-0'" class="w-5 h-5 bg-white rounded-full shadow-md transition-transform transform"></div>
            </button>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Display Order -->
          <div class="space-y-2">
            <label class="text-[#2a273c] font-bold text-sm">Display Order</label>
            <input 
              v-model="form.order" 
              type="number" 
              class="w-full bg-white border-2 border-transparent focus:border-[#e95a54] rounded-xl px-4 py-3 outline-none transition-all shadow-sm disabled:opacity-75 disabled:cursor-not-allowed"
              :disabled="isView"
            >
          </div>

          <!-- Users Select (Mocked for now since multiselect needs specific setup) -->
          <div class="space-y-2">
            <label class="text-[#2a273c] font-bold text-sm">Users Access</label>
            <select v-model="form.users" multiple class="w-full bg-white border-2 border-transparent focus:border-[#e95a54] rounded-xl px-4 py-3 shadow-sm h-[48px] disabled:opacity-75 disabled:cursor-not-allowed" :disabled="isView">
              <option v-for="user in userOptions" :key="user.id" :value="user.id">{{ user.name }}</option>
            </select>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-4 pt-4 border-t border-[#fbcdab]">
          <button 
            type="button" 
            @click="$emit('close')"
            class="px-6 py-3 rounded-xl font-bold text-[#2a273c] hover:bg-white transition-all border border-transparent hover:border-[#fbcdab]"
          >
            Cancel
          </button>
          <button 
            v-if="!isView"
            type="submit"
            :disabled="loading"
            class="bg-[#e95a54] hover:bg-orange-600 text-white px-8 py-3 rounded-xl font-bold transition-all shadow-lg shadow-orange-200 disabled:opacity-50"
          >
            <span v-if="loading">Saving...</span>
            <span v-else>{{ isEdit ? 'Update Category' : 'Save Category' }}</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { reactive, onMounted, ref, watch } from 'vue';
import api from '../services/api';

const props = defineProps({
  show: Boolean,
  category: Object,
  isEdit: Boolean,
  isView: Boolean
});

const emit = defineEmits(['close', 'saved']);

const loading = ref(false);
const userOptions = ref([]);

const form = reactive({
  name_en: '',
  name_ar: '',
  status: true,
  show_in_reservation: true,
  show_in_pos: true,
  order: 0,
  users: []
});

watch(() => props.category, (val) => {
  if (val) {
    form.name_en = val.name?.en || val.name;
    form.name_ar = val.name?.ar || val.name;
    form.status = val.status == 1;
    form.show_in_reservation = !!val.show_in_reservation;
    form.show_in_pos = !!val.show_in_pos;
    form.order = val.order || 0;
    form.users = val.users || [];
  } else {
    resetForm();
  }
}, { immediate: true });

function resetForm() {
    form.name_en = '';
    form.name_ar = '';
    form.status = true;
    form.show_in_reservation = true;
    form.show_in_pos = true;
    form.order = 0;
    form.users = [];
}

onMounted(async () => {
  try {
    const res = await api.get('/service-categories/users');
    userOptions.value = res.data;
  } catch (e) {
    console.error('Failed to load users');
  }
});

async function submit() {
  loading.value = true;
  try {
    if (props.isEdit) {
      await api.put(`/service-categories/${props.category.id}`, form);
    } else {
      await api.post('/service-categories', form);
    }
    emit('saved');
    emit('close');
  } catch (e) {
    alert('Something went wrong');
  } finally {
    loading.value = false;
  }
}
</script>

<style scoped>
.dir-rtl { direction: rtl; }
</style>
