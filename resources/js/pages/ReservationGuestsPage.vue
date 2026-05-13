<template>
  <div class="p-6 bg-slate-50 min-h-screen">
    <div class="max-w-5xl mx-auto">
      <div class="flex items-center gap-4 mb-6">
        <button 
          @click="router.back()"
          class="p-2 bg-white border border-slate-200 rounded-lg text-slate-400 hover:text-slate-600 transition-all shadow-sm"
        >
          <ArrowLeft class="w-5 h-5" />
        </button>
        <div>
          <h1 class="text-2xl font-bold text-slate-800">{{ $t('Reservation Guests') }}</h1>
          <p class="text-slate-500 text-sm mt-1">{{ $t('Manage all guests staying in this reservation') }}</p>
        </div>
        <div class="ml-auto">
           <button 
            @click="showAddModal = true"
            class="flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-all shadow-md font-medium"
           >
            <UserPlus class="w-4 h-4" />
            {{ $t('Add Guest') }}
           </button>
        </div>
      </div>

      <!-- Guests Table -->
      <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-slate-50 border-bottom border-slate-200">
              <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ $t('Guest Name') }}</th>
              <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ $t('ID Type') }}</th>
              <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ $t('ID Number') }}</th>
              <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider text-center">{{ $t('Is Primary') }}</th>
              <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider text-right">{{ $t('Actions') }}</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-if="loading" v-for="i in 3" :key="i" class="animate-pulse">
              <td colspan="5" class="px-6 py-4"><div class="h-4 bg-slate-100 rounded w-full"></div></td>
            </tr>
            <tr v-else-if="guests.length === 0">
              <td colspan="5" class="px-6 py-12 text-center text-slate-400">
                <div class="flex flex-col items-center gap-2">
                  <Users class="w-12 h-12 text-slate-200" />
                  <p>{{ $t('No additional guests found') }}</p>
                </div>
              </td>
            </tr>
            <tr v-for="guest in guests" :key="guest.id" class="hover:bg-slate-50/50 transition-colors">
              <td class="px-6 py-4">
                <div class="font-medium text-slate-700">{{ guest.full_name || guest.name }}</div>
                <div class="text-xs text-slate-400">{{ guest.email || guest.phone }}</div>
              </td>
              <td class="px-6 py-4 text-slate-600">
                {{ guest.id_type || 'N/A' }}
              </td>
              <td class="px-6 py-4 text-slate-600">
                {{ guest.id_number || 'N/A' }}
              </td>
              <td class="px-6 py-4 text-center">
                <CheckCircle v-if="guest.is_primary" class="w-5 h-5 text-emerald-500 mx-auto" />
                <span v-else class="text-slate-300">-</span>
              </td>
              <td class="px-6 py-4 text-right">
                <div class="flex justify-end gap-2">
                  <button 
                    @click="removeGuest(guest.id)"
                    class="p-2 text-rose-400 hover:bg-rose-50 rounded-lg transition-colors"
                    :title="$t('Remove')"
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

    <!-- Add Guest Modal -->
    <div v-if="showAddModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4">
      <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex justify-between items-center">
          <h3 class="text-lg font-bold text-slate-800">{{ $t('Add Guest to Reservation') }}</h3>
          <button @click="showAddModal = false" class="text-slate-400 hover:text-slate-600">
            <X class="w-5 h-5" />
          </button>
        </div>
        <div class="p-6 space-y-4">
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">{{ $t('Search Existing Guest') }}</label>
            <div class="relative">
              <Search class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
              <input 
                v-model="guestSearch"
                @input="searchGuests"
                type="text" 
                class="w-full pl-10 pr-4 py-2 bg-slate-50 border-slate-200 border rounded-lg focus:ring-2 focus:ring-primary/20 outline-none"
                placeholder="Name, ID, or Phone..."
              >
            </div>
            <!-- Search Results -->
            <div v-if="searchResults.length > 0" class="mt-2 bg-white border border-slate-200 rounded-lg shadow-lg max-h-48 overflow-y-auto">
               <div 
                v-for="res in searchResults" 
                :key="res.id"
                @click="selectGuest(res)"
                class="p-3 hover:bg-slate-50 cursor-pointer flex items-center justify-between border-b border-slate-50 last:border-0"
               >
                  <div>
                    <div class="text-sm font-medium text-slate-700">{{ res.full_name }}</div>
                    <div class="text-xs text-slate-400">{{ res.id_number }}</div>
                  </div>
                  <Plus class="w-4 h-4 text-primary" />
               </div>
            </div>
          </div>

          <div v-if="selectedGuest" class="p-4 bg-emerald-50 rounded-xl border border-emerald-100">
            <div class="text-emerald-700 text-sm font-bold">{{ selectedGuest.full_name }}</div>
            <div class="text-emerald-600 text-xs">{{ selectedGuest.id_type }}: {{ selectedGuest.id_number }}</div>
          </div>

          <div class="flex items-center gap-2">
            <input type="checkbox" v-model="addForm.is_primary" id="is_primary" class="rounded text-primary focus:ring-primary">
            <label for="is_primary" class="text-sm text-slate-600">{{ $t('Set as Primary Guest') }}</label>
          </div>
        </div>
        <div class="p-6 bg-slate-50 flex gap-3">
          <button @click="showAddModal = false" class="flex-1 py-2 text-slate-600 font-medium hover:bg-slate-100 rounded-lg transition-all">{{ $t('Cancel') }}</button>
          <button 
            @click="submitAddGuest"
            :disabled="!selectedGuest || submitting"
            class="flex-1 py-2 bg-primary text-white font-bold rounded-lg hover:bg-primary/90 transition-all shadow-md disabled:opacity-50"
          >
            {{ submitting ? $t('Adding...') : $t('Confirm') }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import api from '@/services/api.js';
import { 
  ArrowLeft, UserPlus, Users, CheckCircle, Trash2, X, Search, Plus 
} from 'lucide-vue-next';
import Swal from 'sweetalert2';
// Native debounce implementation
const debounce = (fn, delay) => {
  let timeout;
  return (...args) => {
    clearTimeout(timeout);
    timeout = setTimeout(() => fn(...args), delay);
  };
};

const router = useRouter();
const route = useRoute();
const reservationId = route.params.reservation;

const guests = ref([]);
const loading = ref(false);
const showAddModal = ref(false);
const submitting = ref(false);

const guestSearch = ref('');
const searchResults = ref([]);
const selectedGuest = ref(null);

const addForm = reactive({
  guest_id: null,
  is_primary: false,
  relation: ''
});

const fetchGuests = async () => {
  loading.value = true;
  try {
    const response = await api.get(`/reservations/${reservationId}/guests`);
    guests.value = response.data;
  } catch (error) {
    console.error('Error fetching guests:', error);
  } finally {
    loading.value = false;
  }
};

const searchGuests = debounce(async () => {
  if (guestSearch.value.length < 2) {
    searchResults.value = [];
    return;
  }
  try {
    const response = await api.get('/guests', { params: { search: guestSearch.value } });
    searchResults.value = response.data.data || response.data;
  } catch (error) {
    console.error('Error searching guests:', error);
  }
}, 300);

const selectGuest = (guest) => {
  selectedGuest.value = guest;
  addForm.guest_id = guest.id;
  searchResults.value = [];
  guestSearch.value = '';
};

const submitAddGuest = async () => {
  submitting.value = true;
  try {
    await api.post(`/reservations/${reservationId}/guests`, addForm);
    showAddModal.value = false;
    selectedGuest.value = null;
    addForm.guest_id = null;
    await fetchGuests();
    Swal.fire({
      icon: 'success',
      title: 'Guest Added',
      timer: 1500,
      showConfirmButton: false
    });
  } catch (error) {
    Swal.fire('Error', error.response?.data?.message || 'Failed to add guest', 'error');
  } finally {
    submitting.value = false;
  }
};

const removeGuest = async (guestId) => {
  const result = await Swal.fire({
    title: 'Remove Guest?',
    text: "This guest will be removed from the reservation.",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#e95a54',
    confirmButtonText: 'Yes, remove'
  });

  if (result.isConfirmed) {
    try {
      await api.delete(`/reservations/${reservationId}/guests/${guestId}`);
      await fetchGuests();
      Swal.fire('Removed!', 'Guest has been removed.', 'success');
    } catch (error) {
      Swal.fire('Error!', 'Failed to remove guest.', 'error');
    }
  }
};

onMounted(() => {
  fetchGuests();
});
</script>

<style scoped>
.text-primary { color: #e95a54; }
.bg-primary { background-color: #e95a54; }
</style>
