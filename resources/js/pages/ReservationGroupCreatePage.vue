<template>
  <div class="p-6 bg-slate-50 min-h-screen">
    <div class="max-w-4xl mx-auto">
      <div class="flex items-center gap-4 mb-6">
        <button 
          @click="router.back()"
          class="p-2 bg-white border border-slate-200 rounded-lg text-slate-400 hover:text-slate-600 transition-all shadow-sm"
        >
          <ArrowLeft class="w-5 h-5" />
        </button>
        <div>
          <h1 class="text-2xl font-bold text-slate-800">{{ $t('Create Group Reservation') }}</h1>
          <p class="text-slate-500 text-sm mt-1">{{ $t('Book multiple rooms for a company or group event') }}</p>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Main Form -->
        <div class="md:col-span-2 space-y-6">
          <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-800 mb-4 flex items-center gap-2">
              <ClipboardList class="w-5 h-5 text-primary" />
              {{ $t('General Information') }}
            </h2>
            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">{{ $t('Group Name') }}*</label>
                <input 
                  v-model="form.name"
                  type="text" 
                  class="w-full px-4 py-2.5 bg-slate-50 border-slate-200 border rounded-lg focus:ring-2 focus:ring-primary/20 transition-all outline-none"
                  :placeholder="$t('e.g. Saudi Aramco Annual Meeting')"
                  required
                >
              </div>

              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-slate-700 mb-1">{{ $t('Check-in Date') }}*</label>
                  <input 
                    v-model="form.check_in"
                    type="date" 
                    class="w-full px-4 py-2.5 bg-slate-50 border-slate-200 border rounded-lg focus:ring-2 focus:ring-primary/20 transition-all outline-none"
                    required
                  >
                </div>
                <div>
                  <label class="block text-sm font-medium text-slate-700 mb-1">{{ $t('Check-out Date') }}*</label>
                  <input 
                    v-model="form.check_out"
                    type="date" 
                    class="w-full px-4 py-2.5 bg-slate-50 border-slate-200 border rounded-lg focus:ring-2 focus:ring-primary/20 transition-all outline-none"
                    required
                  >
                </div>
              </div>

              <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">{{ $t('Company Selection') }}</label>
                <select 
                  v-model="form.company_id"
                  class="w-full px-4 py-2.5 bg-slate-50 border-slate-200 border rounded-lg focus:ring-2 focus:ring-primary/20 transition-all outline-none"
                >
                  <option :value="null">{{ $t('Individual / No Company') }}</option>
                  <option v-for="company in companies" :key="company.id" :value="company.id">
                    {{ company.name }}
                  </option>
                </select>
              </div>
            </div>
          </div>

          <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-800 mb-4 flex items-center gap-2">
              <LayoutGrid class="w-5 h-5 text-primary" />
              {{ $t('Room Block Selection') }}
            </h2>
            
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
              <div 
                v-for="room in availableRooms" 
                :key="room.id"
                @click="toggleRoom(room.id)"
                class="cursor-pointer p-3 border rounded-lg transition-all flex flex-col items-center justify-center gap-2"
                :class="form.room_ids.includes(room.id) ? 'bg-primary/5 border-primary border-2 shadow-sm' : 'bg-slate-50 border-slate-200 hover:border-slate-300'"
              >
                <span class="text-sm font-bold" :class="form.room_ids.includes(room.id) ? 'text-primary' : 'text-slate-700'">{{ room.number }}</span>
                <span class="text-[10px] uppercase text-slate-400 font-medium">{{ room.room_type?.name?.en || 'Standard' }}</span>
              </div>
            </div>

            <div v-if="availableRooms.length === 0" class="text-center py-8 text-slate-400">
               <AlertCircle class="w-8 h-8 mx-auto mb-2 opacity-20" />
               <p>{{ $t('No available rooms for selected dates') }}</p>
            </div>
          </div>
        </div>

        <!-- Sidebar / Actions -->
        <div class="space-y-6">
          <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-sm sticky top-6">
            <h2 class="text-lg font-semibold text-slate-800 mb-4">{{ $t('Summary') }}</h2>
            
            <div class="space-y-3 mb-6">
              <div class="flex justify-between text-sm">
                <span class="text-slate-500">{{ $t('Rooms Count') }}</span>
                <span class="font-bold text-slate-800">{{ form.room_ids.length }}</span>
              </div>
              <div class="flex justify-between text-sm">
                <span class="text-slate-500">{{ $t('Nights') }}</span>
                <span class="font-bold text-slate-800">{{ calculateNights }}</span>
              </div>
            </div>

            <div class="space-y-4 mb-6">
               <div>
                  <label class="block text-sm font-medium text-slate-700 mb-1">{{ $t('Group Rate (per room/night)') }}</label>
                  <div class="relative">
                    <input 
                      v-model="form.rate"
                      type="number" 
                      class="w-full pl-8 pr-4 py-2 bg-slate-50 border-slate-200 border rounded-lg focus:ring-2 focus:ring-primary/20 outline-none"
                    >
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs font-bold">SAR</span>
                  </div>
               </div>
               <div>
                  <label class="block text-sm font-medium text-slate-700 mb-1">{{ $t('Special Instructions') }}</label>
                  <textarea 
                    v-model="form.instructions"
                    rows="3"
                    class="w-full px-4 py-2 bg-slate-50 border-slate-200 border rounded-lg focus:ring-2 focus:ring-primary/20 outline-none resize-none text-sm"
                    :placeholder="$t('Any special requests or notes...')"
                  ></textarea>
               </div>
            </div>

            <button 
              @click="submitGroup"
              :disabled="loading || form.room_ids.length === 0"
              class="w-full py-3 bg-primary text-white rounded-xl font-bold shadow-lg hover:bg-primary/90 transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
            >
              <Save class="w-5 h-5" v-if="!loading" />
              <RotateCw class="w-5 h-5 animate-spin" v-else />
              {{ $t('Confirm Group Booking') }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed, watch } from 'vue';
import { useRouter } from 'vue-router';
import api from '@/services/api.js';
import dayjs from 'dayjs';
import { 
  ArrowLeft, ClipboardList, LayoutGrid, Save, RotateCw, AlertCircle 
} from 'lucide-vue-next';
import Swal from 'sweetalert2';

const router = useRouter();
const companies = ref([]);
const availableRooms = ref([]);
const loading = ref(false);

const form = reactive({
  name: '',
  company_id: null,
  check_in: dayjs().format('YYYY-MM-DD'),
  check_out: dayjs().add(2, 'day').format('YYYY-MM-DD'),
  room_ids: [],
  rate: 0,
  instructions: ''
});

const calculateNights = computed(() => {
  const start = dayjs(form.check_in);
  const end = dayjs(form.check_out);
  return end.diff(start, 'day') || 1;
});

const fetchCompanies = async () => {
  try {
    const response = await api.get('/companies');
    companies.value = response.data;
  } catch (error) {
    console.error('Error fetching companies:', error);
  }
};

const fetchAvailableRooms = async () => {
  try {
    // In a real app, this should filter by availability on selected dates
    const response = await api.get('/rooms', { 
      params: { status: 'available' } 
    });
    availableRooms.value = response.data.data || response.data;
  } catch (error) {
    console.error('Error fetching available rooms:', error);
  }
};

const toggleRoom = (id) => {
  const index = form.room_ids.indexOf(id);
  if (index === -1) {
    form.room_ids.push(id);
  } else {
    form.room_ids.splice(index, 1);
  }
};

const submitGroup = async () => {
  if (!form.name || form.room_ids.length === 0) {
    Swal.fire('Validation Error', 'Please fill in the group name and select at least one room.', 'error');
    return;
  }

  loading.value = true;
  try {
    await api.post('/reservations/groups', form);
    Swal.fire({
      icon: 'success',
      title: 'Group Created!',
      text: 'The group reservation has been confirmed successfully.',
      timer: 2000,
      showConfirmButton: false
    });
    router.push('/reservations/groups');
  } catch (error) {
    console.error('Error creating group:', error);
    Swal.fire('Error', error.response?.data?.message || 'Failed to create group reservation.', 'error');
  } finally {
    loading.value = false;
  }
};

// Re-fetch rooms if dates change (simulated)
watch(() => [form.check_in, form.check_out], () => {
  fetchAvailableRooms();
});

onMounted(() => {
  fetchCompanies();
  fetchAvailableRooms();
});
</script>

<style scoped>
.text-primary { color: #e95a54; }
.bg-primary { background-color: #e95a54; }
.border-primary { border-color: #e95a54; }
</style>
