<template>
  <div class="p-6 bg-slate-50 min-h-screen">
    <div class="max-w-6xl mx-auto">
      <div class="flex items-center gap-4 mb-6">
        <button 
          @click="router.back()"
          class="p-2 bg-white border border-slate-200 rounded-lg text-slate-400 hover:text-slate-600 transition-all shadow-sm"
        >
          <ArrowLeft class="w-5 h-5" />
        </button>
        <div>
          <h1 class="text-2xl font-bold text-slate-800">{{ $t('Reserved Rooms') }}</h1>
          <p class="text-slate-500 text-sm mt-1">{{ $t('Manage multiple rooms assigned to this reservation group') }}</p>
        </div>
        <div class="ml-auto">
           <button 
            @click="showAddModal = true"
            class="flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-all shadow-md font-medium"
           >
            <Plus class="w-4 h-4" />
            {{ $t('Add Room') }}
           </button>
        </div>
      </div>

      <!-- Rooms Table -->
      <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-slate-50 border-bottom border-slate-200">
              <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ $t('Room Number') }}</th>
              <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ $t('Room Type') }}</th>
              <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ $t('Guest') }}</th>
              <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ $t('Dates') }}</th>
              <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ $t('Rate') }}</th>
              <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider text-right">{{ $t('Actions') }}</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-if="loading" v-for="i in 3" :key="i" class="animate-pulse">
              <td colspan="6" class="px-6 py-4"><div class="h-4 bg-slate-100 rounded w-full"></div></td>
            </tr>
            <tr v-for="res in rooms" :key="res.id" class="hover:bg-slate-50/50 transition-colors">
              <td class="px-6 py-4">
                <div class="font-bold text-slate-700">{{ res.room?.number || 'N/A' }}</div>
                <div class="text-[10px] text-slate-400 uppercase tracking-wider">{{ res.code }}</div>
              </td>
              <td class="px-6 py-4 text-slate-600">
                {{ res.room?.room_type?.name || 'Standard' }}
              </td>
              <td class="px-6 py-4">
                <div class="text-sm font-medium text-slate-700">{{ res.guest?.full_name || 'No Guest' }}</div>
              </td>
              <td class="px-6 py-4 text-xs text-slate-500">
                <div class="flex items-center gap-1">
                  <Calendar class="w-3 h-3" />
                  {{ formatDate(res.check_in) }} - {{ formatDate(res.check_out) }}
                </div>
              </td>
              <td class="px-6 py-4 font-medium text-slate-700">
                {{ res.total_price || 0 }} SAR
              </td>
              <td class="px-6 py-4 text-right">
                <div class="flex justify-end gap-2">
                  <button 
                    @click="viewDetails(res.id)"
                    class="p-2 text-slate-400 hover:bg-slate-100 rounded-lg transition-colors"
                  >
                    <Eye class="w-4 h-4" />
                  </button>
                  <button 
                    v-if="res.id != reservationId"
                    @click="removeRoom(res.id)"
                    class="p-2 text-rose-400 hover:bg-rose-50 rounded-lg transition-colors"
                  >
                    <Trash2 class="w-4 h-4" />
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Add Room Modal -->
    <div v-if="showAddModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4">
      <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex justify-between items-center">
          <h3 class="text-lg font-bold text-slate-800">{{ $t('Add Room to Reservation') }}</h3>
          <button @click="showAddModal = false" class="text-slate-400 hover:text-slate-600">
            <X class="w-5 h-5" />
          </button>
        </div>
        <div class="p-6 space-y-4">
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">{{ $t('Select Available Room') }}</label>
            <div class="grid grid-cols-3 gap-2 max-h-60 overflow-y-auto p-1">
               <div 
                v-for="room in availableRooms" 
                :key="room.id"
                @click="selectedRoomId = room.id"
                class="p-3 border rounded-lg text-center cursor-pointer transition-all"
                :class="selectedRoomId === room.id ? 'bg-primary/5 border-primary shadow-sm' : 'bg-slate-50 border-slate-200 hover:border-slate-300'"
               >
                  <div class="font-bold" :class="selectedRoomId === room.id ? 'text-primary' : 'text-slate-700'">{{ room.number }}</div>
                  <div class="text-[9px] uppercase text-slate-400">{{ room.room_type?.name || 'Room' }}</div>
               </div>
            </div>
            <div v-if="availableRooms.length === 0" class="text-center py-8 text-slate-400 text-sm">
               {{ $t('No available rooms found') }}
            </div>
          </div>
        </div>
        <div class="p-6 bg-slate-50 flex gap-3">
          <button @click="showAddModal = false" class="flex-1 py-2 text-slate-600 font-medium hover:bg-slate-100 rounded-lg transition-all">{{ $t('Cancel') }}</button>
          <button 
            @click="submitAddRoom"
            :disabled="!selectedRoomId || submitting"
            class="flex-1 py-2 bg-primary text-white font-bold rounded-lg hover:bg-primary/90 transition-all shadow-md disabled:opacity-50"
          >
            {{ submitting ? $t('Adding...') : $t('Add Room') }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import api from '@/services/api.js';
import dayjs from 'dayjs';
import { 
  ArrowLeft, Plus, Trash2, X, Eye, Calendar
} from 'lucide-vue-next';
import Swal from 'sweetalert2';

const router = useRouter();
const route = useRoute();
const reservationId = route.params.reservation;

const rooms = ref([]);
const availableRooms = ref([]);
const loading = ref(false);
const showAddModal = ref(false);
const submitting = ref(false);
const selectedRoomId = ref(null);

const fetchRooms = async () => {
  loading.value = true;
  try {
    const response = await api.get(`/reservations/${reservationId}/rooms`);
    rooms.value = response.data.data;
  } catch (error) {
    console.error('Error fetching reservation rooms:', error);
  } finally {
    loading.value = false;
  }
};

const fetchAvailableRooms = async () => {
  try {
    const response = await api.get('/rooms', { params: { status: 'available' } });
    availableRooms.value = response.data.data || response.data;
  } catch (error) {
    console.error('Error fetching available rooms:', error);
  }
};

const submitAddRoom = async () => {
  submitting.value = true;
  try {
    await api.post(`/reservations/${reservationId}/rooms`, { room_id: selectedRoomId.value });
    showAddModal.value = false;
    selectedRoomId.value = null;
    await fetchRooms();
    Swal.fire({
      icon: 'success',
      title: 'Room Added',
      timer: 1500,
      showConfirmButton: false
    });
  } catch (error) {
    Swal.fire('Error', error.response?.data?.message || 'Failed to add room', 'error');
  } finally {
    submitting.value = false;
  }
};

const removeRoom = async (subResId) => {
  const result = await Swal.fire({
    title: 'Remove Room?',
    text: "This room booking will be deleted.",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#e95a54',
    confirmButtonText: 'Yes, remove'
  });

  if (result.isConfirmed) {
    try {
      await api.delete(`/reservations/${reservationId}/rooms/${subResId}`);
      await fetchRooms();
      Swal.fire('Removed!', 'Room has been removed.', 'success');
    } catch (error) {
      Swal.fire('Error!', error.response?.data?.message || 'Failed to remove room.', 'error');
    }
  }
};

const viewDetails = (id) => {
  router.push(`/reservations/management/${id}`);
};

const formatDate = (date) => {
  return dayjs(date).format('MMM DD');
};

onMounted(() => {
  fetchRooms();
  fetchAvailableRooms();
});
</script>

<style scoped>
.text-primary { color: #e95a54; }
.bg-primary { background-color: #e95a54; }
.border-primary { border-color: #e95a54; }
</style>
