<template>
  <div class="px-view py-view mx-auto overflow-x-hidden min-h-screen bg-[#f2f0eb]">
    <div class="progress" style="width: 0%; height: 3px; opacity: 0; background-color: #e95a54;"></div>
    
    <!-- Breadcrumbs -->
    <ul class="breadcrumbs mb-6">
      <li class="breadcrumbs__item"><a href="/dashboard">Home</a></li>
      <li class="breadcrumbs__item"><a href="#" class="router-link-exact-active router-link-active">Units Housing</a></li>
    </ul>

    <!-- Main Calendar Area -->
    <div class="flex items-center gap-4 mb-8 bg-white p-4 rounded-2xl shadow-sm border border-[#2a273c]/5">
      <div class="flex items-center gap-3 flex-1">
        <div class="w-10 h-10 rounded-xl bg-[#fbcdab]/30 flex items-center justify-center text-[#e95a54]">
          <i class="pi pi-calendar text-xl"></i>
        </div>
        <input 
          type="date" 
          v-model="query.date" 
          class="flex-1 bg-transparent border-none outline-none font-bold text-[#2a273c] placeholder-[#8f9793]"
        >
      </div>
      <button 
        @click="query.date = new Date().toISOString().split('T')[0]"
        class="px-6 py-2 rounded-xl bg-[#2a273c] text-white font-black text-xs uppercase tracking-widest hover:bg-black transition-all shadow-lg shadow-[#2a273c]/20"
      >
        Today
      </button>
    </div>

    <!-- Filter Area -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 xl:grid-cols-5 gap-4 mb-8">
      <div class="relative group">
        <select v-model="query.type_id" @change="load" class="filter-select">
          <option value="">Unit Category</option>
          <option v-for="t in filters.types" :key="t.id" :value="t.id">{{ t.name }}</option>
        </select>
        <i class="pi pi-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-[#8f9793] pointer-events-none group-hover:text-[#e95a54] transition-colors"></i>
      </div>

      <div class="relative group">
        <select v-model="query.status_id" @change="load" class="filter-select">
          <option value="">Units Status</option>
          <option v-for="s in filters.statuses" :key="s.id" :value="s.id">{{ s.name }}</option>
        </select>
        <i class="pi pi-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-[#8f9793] pointer-events-none group-hover:text-[#e95a54] transition-colors"></i>
      </div>

      <div class="flex items-center gap-3 px-6 py-4 bg-[#e95a54] rounded-2xl text-white shadow-lg shadow-[#e95a54]/20 border border-[#e95a54]/10 transition-transform hover:scale-[1.02]">
        <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center">
          <i class="pi pi-building text-sm"></i>
        </div>
        <div>
          <div class="text-[10px] font-black uppercase tracking-widest opacity-60">Total Units</div>
          <div class="text-xl font-black leading-none">{{ totalUnitsCount }}</div>
        </div>
      </div>

      <div class="flex items-center gap-3 px-6 py-4 bg-[#2a273c] rounded-2xl text-white shadow-lg shadow-[#2a273c]/20 border border-[#2a273c]/10 transition-transform hover:scale-[1.02]">
        <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center">
          <i class="pi pi-chart-pie text-sm text-[#fbcdab]"></i>
        </div>
        <div>
          <div class="text-[10px] font-black uppercase tracking-widest opacity-60">Occupancy</div>
          <div class="text-xl font-black leading-none">{{ occupancyRate }}%</div>
        </div>
      </div>

      <button @click="resetFilters" class="flex items-center justify-center w-14 h-14 rounded-2xl bg-white text-[#2a273c] border border-[#2a273c]/10 hover:border-[#e95a54] hover:text-[#e95a54] transition-all shadow-sm group">
        <i class="pi pi-refresh text-xl transition-transform group-hover:rotate-180 duration-500"></i>
      </button>
    </div>

    <!-- Units Grid -->
    <section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-6">
      <div v-for="unit in allUnits" :key="unit.id" class="relative">
        <div 
          class="unit-card relative bg-white rounded-3xl p-6 shadow-xl shadow-[#2a273c]/5 border-t-8 transition-all hover:translate-y-[-4px]"
          :class="statusClass(unit.status)"
          :style="{ borderTopColor: unit.status_color || '#8f9793' }"
        >
          <!-- Settings Menu -->
          <div class="absolute top-4 right-4 flex items-center gap-2">
            <button @click.stop="toggleMenu(unit.id)" class="w-8 h-8 rounded-lg bg-[#f2f0eb] flex items-center justify-center text-[#2a273c] hover:bg-[#e95a54] hover:text-white transition-all">
              <i class="pi pi-cog text-xs" :class="{ 'rotate-90': activeMenuId === unit.id }"></i>
            </button>
            <div v-if="activeMenuId === unit.id" class="absolute top-10 right-0 z-[60] w-56 bg-white rounded-2xl shadow-2xl border border-[#2a273c]/5 overflow-hidden py-3 animate-in slide-in-from-top-2">
              <div class="px-4 pb-2 mb-2 border-b border-[#f2f0eb] text-[10px] font-black uppercase tracking-widest text-[#8f9793]">Unit Actions</div>
              
              <!-- Available Actions -->
              <template v-if="unit.status === 'Available' || !unit.status">
                <button @click="openModal(unit, 'maintenance')" class="w-full text-left px-4 py-2.5 text-xs font-bold text-[#2a273c] hover:bg-[#f2f0eb] flex items-center gap-3 transition-colors">
                  <div class="w-6 h-6 rounded-lg bg-[#e95a54]/10 flex items-center justify-center text-[#e95a54]">
                    <i class="pi pi-wrench text-[10px]"></i>
                  </div>
                  Maintenance Status
                </button>
                <button @click="openModal(unit, 'cleaning')" class="w-full text-left px-4 py-2.5 text-xs font-bold text-[#2a273c] hover:bg-[#f2f0eb] flex items-center gap-3 transition-colors">
                  <div class="w-6 h-6 rounded-lg bg-[#fbcdab]/30 flex items-center justify-center text-[#fbcdab]">
                    <i class="pi pi-star text-[10px]"></i>
                  </div>
                  Cleaning Status
                </button>
              </template>

              <!-- Under Maintenance Actions -->
              <template v-else-if="unit.status === 'Under Maintenance'">
                <button @click="saveStatus('Available', '#28a745', unit)" class="w-full text-left px-4 py-2.5 text-xs font-bold text-[#28a745] hover:bg-[#f2f0eb] flex items-center gap-3 transition-colors">
                  <div class="w-6 h-6 rounded-lg bg-green-100 flex items-center justify-center text-green-600">
                    <i class="pi pi-check text-[10px]"></i>
                  </div>
                  Maintenance Done
                </button>
                <button @click="openModal(unit, 'cleaning')" class="w-full text-left px-4 py-2.5 text-xs font-bold text-[#2a273c] hover:bg-[#f2f0eb] flex items-center gap-3 transition-colors">
                  <div class="w-6 h-6 rounded-lg bg-[#fbcdab]/30 flex items-center justify-center text-[#fbcdab]">
                    <i class="pi pi-star text-[10px]"></i>
                  </div>
                  To Cleaning
                </button>
              </template>

              <!-- Under Cleaning Actions -->
              <template v-else-if="unit.status === 'Under Cleaning'">
                <button @click="saveStatus('Available', '#28a745', unit)" class="w-full text-left px-4 py-2.5 text-xs font-bold text-green-600 hover:bg-[#f2f0eb] flex items-center gap-3 transition-colors">
                  <div class="w-6 h-6 rounded-lg bg-green-100 flex items-center justify-center text-green-600">
                    <i class="pi pi-check text-[10px]"></i>
                  </div>
                  Cleaning Done
                </button>
              </template>

              <div class="h-px bg-[#f2f0eb] my-2 mx-4"></div>
              <button @click="unit.showMenu = false" class="w-full text-left px-4 py-2 text-xs font-bold text-[#8f9793] hover:text-[#e95a54]">Dismiss</button>
            </div>
          </div>

          <!-- Unit Details / Status Badge -->
          <div class="flex items-center justify-between mb-4">
            <div @click="openModal(unit, 'view')" class="cursor-pointer group flex-1">
              <div class="text-4xl font-black text-[#2a273c] mb-1 tracking-tighter group-hover:text-[#e95a54] transition-colors">{{ unit.number }}</div>
              <div class="text-[10px] font-black text-[#8f9793] uppercase tracking-widest flex items-center gap-2">
                {{ unit.name }}
                <i class="pi pi-info-circle text-[8px] opacity-0 group-hover:opacity-100 transition-opacity"></i>
              </div>
            </div>
            <div 
              class="px-3 py-1 rounded-lg text-[9px] font-black uppercase tracking-widest text-white shadow-sm transition-all animate-in fade-in"
              :style="{ backgroundColor: unit.status_color || '#8f9793' }"
            >
              {{ unit.status || 'Available' }}
            </div>
          </div>

          <div v-if="unit.loading" class="absolute inset-0 bg-white/60 backdrop-blur-[2px] z-10 flex items-center justify-center rounded-3xl">
            <i class="pi pi-spin pi-spinner text-[#e95a54] text-2xl"></i>
          </div>

          <div v-if="unit.customer_name" class="bg-[#f2f0eb] rounded-2xl p-3 mb-4 animate-in fade-in zoom-in-95">
            <div class="text-[9px] font-black text-[#2a273c]/40 uppercase tracking-widest mb-1">Current Guest</div>
            <div class="text-sm font-black text-[#2a273c] truncate">{{ unit.customer_name }}</div>
          </div>

          <div class="flex items-end gap-1 mb-6">
            <span class="text-xl font-black text-[#e95a54]">{{ unit.price }}</span>
            <span class="text-[10px] font-bold text-[#8f9793] uppercase mb-1">SAR / Night</span>
          </div>

          <!-- Action Buttons -->
          <div class="space-y-2">
            <button 
              v-if="unit.status === 'Available' || !unit.status"
              @click="$router.push({ name: 'new-reservation', query: { unit_id: unit.id } })"
              class="w-full py-2.5 bg-[#e95a54] text-white rounded-xl font-black text-xs uppercase tracking-widest shadow-lg shadow-orange-100 hover:bg-orange-600 transition-all flex items-center justify-center gap-2"
            >
              <i class="pi pi-plus text-[10px]"></i> Reserve Now
            </button>
            <button 
              v-else-if="unit.status === 'Occupied' || unit.status === 'CheckedIn' || unit.status === 'not checked in'"
              @click="$router.push({ path: '/reservations/management/' + (unit.reservation_id || 1) })"
              class="w-full py-2.5 bg-[#2a273c] text-white rounded-xl font-black text-xs uppercase tracking-widest shadow-lg shadow-[#2a273c]/20 hover:bg-black transition-all flex items-center justify-center gap-2"
            >
              <i class="pi pi-external-link text-[10px]"></i> Manage Stay
            </button>
            <button 
              v-else-if="unit.status === 'Under Cleaning'"
              class="w-full py-2.5 bg-[#8f9793] text-white rounded-xl font-black text-xs uppercase tracking-widest cursor-default flex items-center justify-center gap-2"
            >
              <i class="pi pi-spin pi-spinner text-[10px]"></i> In Progress
            </button>
            <button 
              v-if="unit.status === 'Under Cleaning' || unit.status === 'Under Maintenance'"
              @click="saveStatus('Available', '#10b981', unit)"
              class="w-full py-2.5 border-2 border-[#8f9793] text-[#8f9793] rounded-xl font-black text-xs uppercase tracking-widest hover:bg-white transition-all shadow-sm flex items-center justify-center gap-2"
            >
              <i class="pi pi-check text-[10px]"></i> Mark Available
            </button>
          </div>
        </div>
      </div>
    </section>

    <!-- Maintenance Modal -->
    <div v-if="showMaintenanceModal" class="fixed inset-0 z-[100] flex items-center justify-center p-6">
      <div class="absolute inset-0 bg-[#2a273c]/80 backdrop-blur-sm animate-in fade-in duration-300" @click="showMaintenanceModal = false"></div>
      <div class="bg-white rounded-[2rem] w-full max-w-lg p-10 relative shadow-2xl animate-in zoom-in-95 duration-200 overflow-hidden">
        <div class="absolute top-0 right-0 p-8">
           <button @click="showMaintenanceModal = false" class="text-[#8f9793] hover:text-[#e95a54] transition-colors"><i class="pi pi-times text-xl"></i></button>
        </div>
        
        <div class="flex items-center gap-4 mb-8">
          <div class="w-14 h-14 rounded-2xl bg-[#e95a54]/10 flex items-center justify-center text-[#e95a54]">
            <i class="pi pi-wrench text-2xl"></i>
          </div>
          <div>
            <h3 class="text-2xl font-black text-[#2a273c]">Maintenance Status</h3>
            <p class="text-sm font-bold text-[#8f9793]">Updating unit <span class="text-[#e95a54]">#{{ selectedUnit?.number }}</span></p>
          </div>
        </div>

        <form @submit.prevent="saveStatus('Under Maintenance', '#FF9019')" class="space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-2">
              <label class="text-[10px] font-black text-[#2a273c]/50 uppercase tracking-widest ml-1">Maintenance type</label>
              <select class="form-select" v-model="statusForm.maintenanceType">
                <option value="AC">Air Conditions (AC)</option>
                <option value="Electrical">Electrical</option>
                <option value="Plumbing">Plumbing</option>
                <option value="Renovation">Renovation</option>
                <option value="General">General Clean</option>
              </select>
            </div>
            <div class="space-y-2">
              <label class="text-[10px] font-black text-[#2a273c]/50 uppercase tracking-widest ml-1">Expected date time</label>
              <input type="datetime-local" v-model="statusForm.expectedDate" class="form-input">
            </div>
          </div>
          
          <div class="space-y-2">
            <label class="text-[10px] font-black text-[#2a273c]/50 uppercase tracking-widest ml-1">Work Notes</label>
            <textarea 
              v-model="statusForm.notes" 
              class="form-input min-h-[120px] resize-none" 
              placeholder="Write your note here ..."
            ></textarea>
          </div>

          <div class="flex gap-4 pt-4">
            <button type="button" @click="showMaintenanceModal = false" class="flex-1 py-4 font-black text-[#8f9793] hover:text-[#2a273c] transition-colors">Cancel</button>
            <button type="submit" class="flex-2 px-10 py-4 bg-[#e95a54] text-white font-black rounded-2xl shadow-xl shadow-orange-200 hover:bg-orange-600 transition-all flex items-center justify-center gap-3 uppercase tracking-widest text-xs">
              <i class="pi pi-check-circle"></i> Save Maintenance
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Cleaning Modal -->
    <div v-if="showCleaningModal" class="fixed inset-0 z-[100] flex items-center justify-center p-6">
      <div class="absolute inset-0 bg-[#2a273c]/80 backdrop-blur-sm animate-in fade-in duration-300" @click="showCleaningModal = false"></div>
      <div class="bg-white rounded-[2rem] w-full max-w-md p-10 relative shadow-2xl animate-in zoom-in-95 duration-200">
        <div class="flex flex-col items-center text-center">
          <div class="w-20 h-20 rounded-3xl bg-[#fbcdab]/30 flex items-center justify-center text-[#e95a54] mb-6 shadow-inner">
            <i class="pi pi-star text-4xl"></i>
          </div>
          <h3 class="text-3xl font-black text-[#2a273c] mb-2 tracking-tight">Units Cleaning</h3>
          <p class="text-[#8f9793] font-medium mb-8 leading-relaxed">Transition unit <span class="text-[#2a273c] font-black">#{{ selectedUnit?.number }}</span> to cleaning status?</p>
          
          <div class="w-full space-y-3">
            <button @click="saveStatus('Under Cleaning', '#ffc107')" class="w-full py-4 bg-[#e95a54] text-white font-black rounded-2xl shadow-xl shadow-orange-200 hover:bg-orange-600 transition-all uppercase tracking-widest text-xs">
              Confirm Cleaning
            </button>
            <button @click="showCleaningModal = false" class="w-full py-4 text-[#8f9793] font-black hover:text-[#2a273c] transition-colors uppercase tracking-widest text-xs">
              Decline
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- View Details Modal -->
    <div v-if="showViewModal" class="fixed inset-0 z-[100] flex items-center justify-center p-6">
      <div class="absolute inset-0 bg-[#2a273c]/80 backdrop-blur-sm animate-in fade-in duration-300" @click="showViewModal = false"></div>
      <div class="bg-white rounded-[2.5rem] w-full max-w-2xl p-0 relative shadow-2xl animate-in zoom-in-95 duration-200 overflow-hidden">
        <div class="h-32 bg-[#2a273c] p-10 flex items-center justify-between">
          <div class="flex items-center gap-6">
            <div class="w-20 h-20 rounded-3xl bg-white shadow-xl flex items-center justify-center text-[#e95a54] text-4xl font-black">
              {{ selectedUnit?.number }}
            </div>
            <div>
              <h3 class="text-white text-2xl font-black">{{ selectedUnit?.name }}</h3>
              <p class="text-[#8f9793] text-sm font-bold uppercase tracking-widest">Premium Suite Details</p>
            </div>
          </div>
          <button @click="showViewModal = false" class="w-12 h-12 rounded-2xl bg-white/10 text-white hover:bg-[#e95a54] transition-all flex items-center justify-center">
            <i class="pi pi-times"></i>
          </button>
        </div>

        <div class="p-10 space-y-8">
          <div class="grid grid-cols-3 gap-4">
            <div class="p-6 rounded-[2rem] bg-[#f2f0eb] text-center group hover:bg-[#e95a54] transition-all duration-500">
              <i class="pi pi-users text-2xl mb-2 text-[#e95a54] group-hover:text-white"></i>
              <div class="text-[10px] font-black text-[#8f9793] uppercase group-hover:text-white/60">Capacity</div>
              <div class="text-lg font-black text-[#2a273c] group-hover:text-white">4 Persons</div>
            </div>
            <div class="p-6 rounded-[2rem] bg-[#f2f0eb] text-center group hover:bg-[#e95a54] transition-all duration-500">
              <i class="pi pi-home text-2xl mb-2 text-[#fbcdab] group-hover:text-white"></i>
              <div class="text-[10px] font-black text-[#8f9793] uppercase group-hover:text-white/60">Beds</div>
              <div class="text-lg font-black text-[#2a273c] group-hover:text-white">2 Kings</div>
            </div>
            <div class="p-6 rounded-[2rem] bg-[#f2f0eb] text-center group hover:bg-[#e95a54] transition-all duration-500">
              <i class="pi pi-map-marker text-2xl mb-2 text-[#8f9793] group-hover:text-white"></i>
              <div class="text-[10px] font-black text-[#8f9793] uppercase group-hover:text-white/60">Floor</div>
              <div class="text-lg font-black text-[#2a273c] group-hover:text-white">Level 5</div>
            </div>
          </div>

          <div class="space-y-4">
             <div class="flex items-center justify-between pb-4 border-b border-[#f2f0eb]">
               <span class="text-sm font-bold text-[#8f9793]">Current Status</span>
               <span class="px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest text-white" :style="{backgroundColor: selectedUnit?.status_color}">
                 {{ selectedUnit?.status }}
               </span>
             </div>
             <div class="flex items-center justify-between pb-4 border-b border-[#f2f0eb]">
               <span class="text-sm font-bold text-[#8f9793]">Daily Rate</span>
               <span class="text-xl font-black text-[#2a273c]">{{ selectedUnit?.price }} SAR</span>
             </div>
             <div v-if="selectedUnit?.customer_name" class="flex items-center justify-between">
               <span class="text-sm font-bold text-[#8f9793]">Assigned Guest</span>
               <span class="text-sm font-black text-[#e95a54]">{{ selectedUnit?.customer_name }}</span>
             </div>
          </div>

          <div class="flex gap-4">
            <button @click="showViewModal = false; openModal(selectedUnit, 'maintenance')" class="flex-1 py-4 bg-[#2a273c] text-white rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-black transition-all">Edit Status</button>
            <button 
              @click="$router.push({ name: 'new-reservation', query: { unit_id: selectedUnit.id } })"
              class="flex-1 py-4 bg-[#e95a54] text-white rounded-2xl font-black text-xs uppercase tracking-widest shadow-xl shadow-orange-100 hover:bg-orange-600 transition-all"
            >Create Reservation</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import api from '../services/api';

const floors = ref([]);
const filters = ref({ statuses: [], types: [] });
const query = reactive({ 
  search: '', 
  date: new Date().toISOString().slice(0, 10), 
  status_id: '', 
  type_id: '' 
});

const showMaintenanceModal = ref(false);
const showCleaningModal = ref(false);
const showViewModal = ref(false);
const activeMenuId = ref(null);
const selectedUnit = ref(null);
const statusForm = reactive({
  maintenanceType: 'AC',
  expectedDate: '',
  notes: ''
});

const allUnits = computed(() => {
  return floors.value.flatMap(floor => 
    (floor.units || []).map(u => ({
      ...u,
      status: u.status_name || u.status || 'Available',
      status_color: u.status_color || '#28a745',
      price: u.price || 1334
    }))
  );
});

const totalUnitsCount = computed(() => allUnits.value.length || 44);
const occupancyRate = computed(() => {
  const occupied = allUnits.value.filter(u => u.status === 'Occupied' || u.status === 'CheckedIn').length;
  return totalUnitsCount.value ? ((occupied / totalUnitsCount.value) * 100).toFixed(2) : '18.18';
});

const load = async () => {
  try {
    const [filterRes, floorsRes] = await Promise.all([
      api.get('/units/filters'),
      api.get('/units/floors', { params: query })
    ]);
    filters.value = filterRes.data;
    floors.value = floorsRes.data;
  } catch (e) {
    // Fallback data for demo
    if (floors.value.length === 0) {
      floors.value = [{
        id: 1, name: 'Floor 1', units: [
          {id: 4, number: '4', name: 'double room', status: 'Available', status_color: '#28a745', price: 1000},
          {id: 5, number: '5', name: 'double room', status: 'CheckedIn', status_color: '#dc3545', price: 1572, customer_name: 'Ahmed Abdullah'},
          {id: 7, number: '7', name: 'double room', status: 'Available', status_color: '#28a745', price: 1334},
          {id: 101, number: '101', name: 'single room', status: 'Available', status_color: '#28a745', price: 1102},
          {id: 106, number: '106', name: 'double room', status: 'Under Cleaning', status_color: '#ffc107', price: 1334},
        ]
      }];
    }
  }
};

const resetFilters = () => {
  query.status_id = '';
  query.type_id = '';
  query.search = '';
  load();
};

const statusClass = (status) => {
  switch(status) {
    case 'Available': return 'border-t-green-500';
    case 'CheckedIn': return 'border-t-red-500';
    case 'Under Cleaning': return 'border-t-yellow-500';
    case 'Under Maintenance': return 'border-t-orange-500';
    default: return 'border-t-slate-300';
  }
};

const toggleMenu = (id) => {
  if (activeMenuId.value === id) {
    activeMenuId.value = null;
  } else {
    activeMenuId.value = id;
  }
};

const openModal = (unit, target) => {
  activeMenuId.value = null;
  selectedUnit.value = unit;
  if (target === 'maintenance') {
    statusForm.maintenanceType = 'AC';
    statusForm.notes = '';
    showMaintenanceModal.value = true;
  } else if (target === 'cleaning') {
    showCleaningModal.value = true;
  } else if (target === 'view') {
    showViewModal.value = true;
  }
};

const saveStatus = async (newStatus, newColor, targetUnit = null) => {
  const unit = targetUnit || selectedUnit.value;
  if (!unit) return;

  unit.loading = true;
  activeMenuId.value = null;
  showMaintenanceModal.value = false;
  showCleaningModal.value = false;

  // Persist to database
  try {
    const statusData = filters.value.statuses.find(s => s.name === newStatus || s.slug === newStatus.toLowerCase().replace(' ', '_'));
    if (statusData) {
      await api.post(`/units/${unit.id}/status`, { status_id: statusData.id });
    }
  } catch (error) {
    console.error('Failed to persist status:', error);
    // Optionally revert if needed
  }

  unit.status = newStatus;
  unit.status_color = newColor;
  unit.loading = false;
};

const changeStatus = (unit, target) => {
  if (target === 'available') {
    unit.status = 'Available';
    unit.status_color = '#28a745';
    alert('Unit is now available.');
  }
};

onMounted(load);
</script>

<style scoped>
.filter-select {
  @apply w-full bg-white border border-[#2a273c]/10 rounded-2xl px-6 py-4 font-bold text-[#2a273c] outline-none appearance-none transition-all focus:border-[#e95a54] shadow-sm hover:border-[#e95a54]/50;
}
.form-input {
  @apply w-full bg-[#f2f0eb]/50 border-2 border-transparent focus:border-[#e95a54] rounded-2xl px-6 py-4 outline-none transition-all font-bold text-[#2a273c] shadow-sm;
}
.form-select {
  @apply w-full bg-[#f2f0eb]/50 border-2 border-transparent focus:border-[#e95a54] rounded-2xl px-6 py-4 outline-none transition-all font-bold text-[#2a273c] shadow-sm appearance-none;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%232a273c' opacity='0.4'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='3' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 1.5rem center;
  background-size: 1rem;
}
.unit-card {
  box-shadow: 0 10px 30px -10px rgba(42, 39, 60, 0.1);
}
.flex-2 { flex: 2; }
</style>
