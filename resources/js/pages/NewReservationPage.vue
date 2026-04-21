<template>
  <div class="min-h-screen bg-[#f2f0eb] p-6 lg:p-8">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
      <div>
        <h1 class="text-[#2a273c] text-3xl font-black tracking-tight">
          {{ isEdit ? 'Update Reservation' : (isView ? 'Reservation Details' : 'New Reservation') }}
        </h1>
        <div class="flex items-center gap-2 mt-1 text-[#8f9793] font-medium uppercase text-xs tracking-widest">
          <span>Home</span>
          <i class="pi pi-chevron-right text-[8px]"></i>
          <span class="text-[#e95a54]">Registration</span>
        </div>
      </div>
      <div class="flex items-center gap-3">
        <button @click="$router.push('/dashboard')" class="px-6 py-2.5 rounded-xl border-2 border-[#2a273c]/10 text-[#2a273c] font-bold hover:bg-white transition-all shadow-sm">
          Cancel
        </button>
        <button v-if="!isView" @click="submitReservation" :disabled="submitting" class="px-8 py-2.5 rounded-xl bg-[#e95a54] text-white font-black hover:bg-orange-600 transition-all shadow-lg shadow-orange-200 flex items-center gap-2">
          <i class="pi pi-check text-xs font-bold"></i>
          {{ submitting ? 'Processing...' : (isEdit ? 'Update Changes' : 'Confirm Registration') }}
        </button>
      </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-12 gap-8">
      <!-- Left Column: Customer & Visit Details -->
      <div class="xl:col-span-7 space-y-8">
        
        <!-- Customer Search & Details -->
        <div class="bg-white rounded-3xl p-8 shadow-xl shadow-[#2a273c]/5 border border-[#2a273c]/5">
          <div class="flex items-center justify-between mb-8">
            <h2 class="flex items-center gap-3 text-xl font-black text-[#2a273c]">
              <span class="w-10 h-10 rounded-xl bg-[#fbcdab] flex items-center justify-center text-[#2a273c] shadow-inner">
                <i class="pi pi-user text-lg"></i>
              </span>
              Guest Information
            </h2>
            <div class="flex gap-2 bg-[#f2f0eb] p-1.5 rounded-xl">
              <button 
                v-for="mode in ['name', 'id', 'email', 'phone']" 
                @click="searchMode = mode"
                :class="searchMode === mode ? 'bg-white text-[#e95a54] shadow-sm' : 'text-[#8f9793] hover:text-[#2a273c]'"
                class="px-4 py-1.5 rounded-lg text-xs font-bold uppercase transition-all"
              >{{ mode }}</button>
            </div>
          </div>

          <div class="relative mb-8">
            <div class="absolute left-4 top-1/2 -translate-y-1/2 text-[#8f9793]">
              <i class="pi pi-search"></i>
            </div>
            <input 
              v-model="searchQuery"
              @input="handleSearch"
              type="text" 
              :placeholder="`Search Guest by ${searchMode}...`"
              class="w-full pl-12 pr-4 py-4 bg-[#f2f0eb]/50 border-2 border-transparent focus:border-[#e95a54] rounded-2xl outline-none transition-all font-medium"
            >
            <!-- Suggestions Dropdown -->
            <div v-if="suggestions.length" class="absolute z-50 top-full left-0 right-0 mt-2 bg-white rounded-2xl shadow-2xl border border-[#2a273c]/5 overflow-hidden">
              <div 
                v-for="item in suggestions" 
                :key="item.id"
                @click="selectCustomer(item)"
                class="px-6 py-4 hover:bg-[#fbcdab]/20 cursor-pointer border-b border-[#2a273c]/5 last:border-0 flex items-center justify-between"
              >
                <div>
                  <div class="font-bold text-[#2a273c]">{{ item.name || item.full_name }}</div>
                  <div class="text-xs text-[#8f9793] font-medium">{{ item.id_number || item.phone }}</div>
                </div>
                <i class="pi pi-plus-circle text-[#e95a54]"></i>
              </div>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-2">
              <label class="text-xs font-black text-[#2a273c]/50 uppercase tracking-widest ml-1">Full Name</label>
              <input v-model="form.customer.name" type="text" class="form-input" :disabled="isSelected" required>
            </div>
            <div class="space-y-2">
              <label class="text-xs font-black text-[#2a273c]/50 uppercase tracking-widest ml-1">Phone Number</label>
              <input v-model="form.customer.phone" type="text" placeholder="+966 5..." class="form-input" :disabled="isSelected" required>
            </div>
            <div class="space-y-2">
              <label class="text-xs font-black text-[#2a273c]/50 uppercase tracking-widest ml-1">Customer Type</label>
              <select v-model="form.customer.type" class="form-select" :disabled="isSelected">
                <option value="1">Citizen</option><option value="2">Gulf Citizen</option><option value="3">Visitor</option><option value="4">Resident</option>
              </select>
            </div>
            <div class="space-y-2">
              <label class="text-xs font-black text-[#2a273c]/50 uppercase tracking-widest ml-1">Nationality</label>
              <select v-model="form.customer.nationality" class="form-select" :disabled="isSelected">
                <option v-for="c in countries" :key="c.id" :value="c.id">{{ c.name }}</option>
              </select>
            </div>
            <div class="space-y-2">
              <label class="text-xs font-black text-[#2a273c]/50 uppercase tracking-widest ml-1">ID Type</label>
              <select v-model="form.customer.id_type" class="form-select" :disabled="isSelected">
                <option value="1">National ID</option><option value="2">Family ID</option><option value="5">Passport</option>
              </select>
            </div>
            <div class="space-y-2">
              <label class="text-xs font-black text-[#2a273c]/50 uppercase tracking-widest ml-1">ID Number</label>
              <input v-model="form.customer.id_number" type="text" class="form-input" :disabled="isSelected">
            </div>
            <div class="space-y-2">
              <label class="text-xs font-black text-[#2a273c]/50 uppercase tracking-widest ml-1">Gender</label>
              <select v-model="form.customer.gender" class="form-select" :disabled="isSelected">
                <option value="male">Male</option><option value="female">Female</option>
              </select>
            </div>
            <div class="space-y-2">
              <label class="text-xs font-black text-[#2a273c]/50 uppercase tracking-widest ml-1">Email Address</label>
              <input v-model="form.customer.email" type="email" class="form-input" :disabled="isSelected">
            </div>
            <div class="space-y-2">
              <label class="text-xs font-black text-[#2a273c]/50 uppercase tracking-widest ml-1">Purpose of Visit</label>
              <select v-model="form.purpose" class="form-select">
                <option value="1">Tourism</option><option value="2">Family/Friends</option><option value="3">Religious</option><option value="4">Work</option><option value="7">Other</option>
              </select>
            </div>
            <div class="space-y-2">
              <label class="text-xs font-black text-[#2a273c]/50 uppercase tracking-widest ml-1">Guest Level</label>
              <select v-model="form.highlight" class="form-select">
                <option value="7659">Regular</option><option value="12931">VIP</option><option value="5730">Blacklist</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Visit Details -->
        <div class="bg-white rounded-3xl p-8 shadow-xl shadow-[#2a273c]/5 border border-[#2a273c]/5">
          <h2 class="flex items-center gap-3 text-xl font-black text-[#2a273c] mb-8">
            <span class="w-10 h-10 rounded-xl bg-[#8f9793]/20 flex items-center justify-center text-[#8f9793] shadow-inner">
              <i class="pi pi-calendar text-lg"></i>
            </span>
            Visit & Stay Details
          </h2>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
            <div class="bg-[#f2f0eb]/30 p-6 rounded-2xl border-2 border-dashed border-[#2a273c]/5">
              <div class="text-[#2a273c]/40 font-black text-[10px] uppercase tracking-widest mb-4">Reservation Category</div>
              <div class="flex gap-4">
                <label v-for="cat in ['Single', 'Group']" class="flex-1 cursor-pointer group">
                  <input type="radio" v-model="form.category" :value="cat.toLowerCase()" class="hidden">
                  <div 
                    :class="form.category === cat.toLowerCase() ? 'bg-[#2a273c] text-white border-[#2a273c]' : 'bg-white text-[#2a273c] border-transparent'"
                    class="py-3 text-center rounded-xl font-bold border-2 transition-all shadow-sm group-hover:scale-105"
                  >{{ cat }}</div>
                </label>
              </div>
            </div>
            <div class="bg-[#f2f0eb]/30 p-6 rounded-2xl border-2 border-dashed border-[#2a273c]/5">
              <div class="text-[#2a273c]/40 font-black text-[10px] uppercase tracking-widest mb-4">Rent Type</div>
              <div class="flex gap-4">
                <label v-for="type in ['Daily', 'Monthly']" class="flex-1 cursor-pointer group">
                  <input type="radio" v-model="form.rent_type" :value="type.toLowerCase()" class="hidden">
                  <div 
                    :class="form.rent_type === type.toLowerCase() ? 'bg-[#8f9793] text-white border-[#8f9793]' : 'bg-white text-[#2a273c] border-transparent'"
                    class="py-3 text-center rounded-xl font-bold border-2 transition-all shadow-sm group-hover:scale-105"
                  >{{ type }}</div>
                </label>
              </div>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-2">
              <label class="text-xs font-black text-[#2a273c]/50 uppercase tracking-widest ml-1">Arrival Date</label>
              <input v-model="form.check_in" type="date" class="form-input">
            </div>
            <div class="space-y-2">
              <label class="text-xs font-black text-[#2a273c]/50 uppercase tracking-widest ml-1">Departure Date</label>
              <input v-model="form.check_out" type="date" class="form-input" @change="calculateNights">
            </div>
            <div class="space-y-2">
              <label class="text-xs font-black text-[#2a273c]/50 uppercase tracking-widest ml-1">Source of Booking</label>
              <select v-model="form.source_id" class="form-select">
                <option v-for="s in sources" :value="s.id">{{ s.name }}</option>
              </select>
            </div>
            <div class="space-y-2">
              <label class="text-xs font-black text-[#2a273c]/50 uppercase tracking-widest ml-1">Voucher/Source No.</label>
              <input v-model="form.source_number" type="text" placeholder="REF-832..." class="form-input">
            </div>
          </div>
        </div>
      </div>

      <!-- Right Column: Unit Selection & Calculations -->
      <div class="xl:col-span-5 space-y-8">
        
        <!-- Unit Selection -->
        <div class="bg-[#2a273c] rounded-3xl p-8 shadow-2xl shadow-[#2a273c]/20 text-white">
          <h2 class="flex items-center gap-3 text-xl font-black mb-8">
            <span class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center text-[#e95a54] shadow-inner">
              <i class="pi pi-home text-lg"></i>
            </span>
            Unit Assignment
          </h2>

          <div class="space-y-6">
            <div class="space-y-2">
              <label class="text-[10px] font-black text-white/40 uppercase tracking-widest ml-1">Select Available Unit</label>
              <select v-model="form.unit_id" @change="handleUnitChange" class="w-full bg-white/10 border-2 border-white/5 focus:border-[#e95a54] rounded-2xl px-6 py-4 outline-none transition-all font-bold text-lg appearance-none">
                <option value="" class="text-slate-800">Choose Unit</option>
                <option v-for="u in (units || [])" :key="u?.id" :value="u?.id" class="text-slate-800">{{ u?.number }} - {{ u?.name }}</option>
              </select>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div class="bg-white/5 rounded-2xl p-4 border border-white/10">
                <div class="text-[10px] font-black text-white/40 uppercase tracking-widest mb-1">Adults</div>
                <div class="flex items-center justify-between">
                  <button @click="form.adults = Math.max(1, form.adults - 1)" class="w-8 h-8 rounded-lg bg-white/10 hover:bg-white/20 transition-colors"><i class="pi pi-minus text-[10px]"></i></button>
                  <span class="text-xl font-black">{{ form.adults }}</span>
                  <button @click="form.adults++" class="w-8 h-8 rounded-lg bg-white/10 hover:bg-white/20 transition-colors"><i class="pi pi-plus text-[10px]"></i></button>
                </div>
              </div>
              <div class="bg-white/5 rounded-2xl p-4 border border-white/10">
                <div class="text-[10px] font-black text-white/40 uppercase tracking-widest mb-1">Children</div>
                <div class="flex items-center justify-between">
                  <button @click="form.children = Math.max(0, form.children - 1)" class="w-8 h-8 rounded-lg bg-white/10 hover:bg-white/20 transition-colors"><i class="pi pi-minus text-[10px]"></i></button>
                  <span class="text-xl font-black">{{ form.children }}</span>
                  <button @click="form.children++" class="w-8 h-8 rounded-lg bg-white/10 hover:bg-white/20 transition-colors"><i class="pi pi-plus text-[10px]"></i></button>
                </div>
              </div>
            </div>

            <div class="bg-gradient-to-br from-[#e95a54] to-orange-600 rounded-3xl p-6 shadow-lg shadow-orange-950/20">
              <div class="flex justify-between items-center mb-1">
                <div class="text-[10px] font-black text-white/60 uppercase tracking-widest">Base Rate / Night</div>
                <div class="bg-white/20 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-tighter">Tax Inc.</div>
              </div>
              <div class="flex items-end gap-1">
                <span class="text-3xl font-black">{{ baseRate }}</span>
                <span class="text-sm font-bold opacity-60 mb-1">SAR</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Add-on Services -->
        <div class="bg-white rounded-3xl p-8 shadow-xl shadow-[#2a273c]/5 border border-[#2a273c]/5">
          <h2 class="flex items-center gap-3 text-xl font-black text-[#2a273c] mb-6">
            <span class="w-10 h-10 rounded-xl bg-[#fbcdab]/50 flex items-center justify-center text-[#e95a54] shadow-inner">
              <i class="pi pi-th-large text-lg"></i>
            </span>
            Add-on Services
          </h2>
          <div class="space-y-3">
            <label v-for="s in (services || [])" :key="s?.id" class="flex items-center justify-between p-4 rounded-2xl border-2 cursor-pointer transition-all hover:bg-[#fbcdab]/10" :class="isSelectedService(s?.id) ? 'border-[#e95a54] bg-[#fbcdab]/5' : 'border-[#2a273c]/5 bg-[#f2f0eb]/20'">
              <div class="flex items-center gap-4">
                <input type="checkbox" :value="s?.id" v-model="form.selected_services" class="w-5 h-5 accent-[#e95a54]">
                <div v-if="s">
                  <div class="font-bold text-[#2a273c]">{{ s.name?.en || s.name }}</div>
                  <div class="text-[10px] text-[#8f9793] uppercase font-bold">Service Add-on</div>
                </div>
              </div>
              <div class="text-[#e95a54] font-black">Free</div>
            </label>
          </div>
        </div>

        <!-- Billing Summary -->
        <div class="bg-white rounded-3xl p-8 shadow-xl shadow-[#2a273c]/5 border border-[#2a273c]/5 overflow-hidden relative">
          <div class="absolute -right-12 -top-12 w-32 h-32 bg-[#fbcdab]/10 rounded-full blur-2xl"></div>
          
          <h2 class="text-xl font-black text-[#2a273c] mb-8 relative">Billing Summary</h2>
          
          <div class="space-y-5 relative">
            <div class="flex justify-between items-center text-sm">
              <span class="font-bold text-[#8f9793]">Accomodation ({{ nights }} nights)</span>
              <span class="font-black text-[#2a273c]">{{ accommodationTotal.toFixed(2) }} SAR</span>
            </div>
            <div class="flex justify-between items-center text-sm">
              <span class="font-bold text-[#8f9793]">Services Total</span>
              <span class="font-black text-muted-green">0.00 SAR</span>
            </div>
            <div class="border-t-2 border-dashed border-[#2a273c]/10 my-4"></div>
            <div class="flex justify-between items-center text-sm">
              <span class="font-bold text-[#8f9793]">Subtotal</span>
              <span class="font-black text-[#2a273c]">{{ subtotal.toFixed(2) }} SAR</span>
            </div>
            <div class="flex justify-between items-center text-sm">
              <span class="font-bold text-[#8f9793]">VAT (15%)</span>
              <span class="font-black text-[#2a273c]">{{ vatTotal.toFixed(2) }} SAR</span>
            </div>
            <div class="flex justify-between items-center text-sm">
              <span class="font-bold text-[#8f9793]">EWA Fees (2.5%)</span>
              <span class="font-black text-[#2a273c]">{{ ewaTotal.toFixed(2) }} SAR</span>
            </div>
            
            <div class="bg-[#2a273c] -mx-8 -mb-8 mt-10 p-8">
              <div class="flex justify-between items-center">
                <div>
                  <div class="text-[10px] font-black text-white/40 uppercase tracking-widest mb-1">Grand Total</div>
                  <div class="text-3xl font-black text-white tracking-tighter">{{ grandTotal.toFixed(2) }} <span class="text-sm opacity-40">SAR</span></div>
                </div>
                <button @click="showTurnawayModal = true" class="w-14 h-14 rounded-2xl bg-white/10 hover:bg-[#e95a54] text-white transition-all shadow-lg flex items-center justify-center group">
                  <i class="pi pi-undo text-xl transition-transform group-hover:-rotate-45"></i>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Turnaway Modal (Simplified) -->
    <div v-if="showTurnawayModal" class="fixed inset-0 z-[100] flex items-center justify-center p-6">
      <div class="absolute inset-0 bg-[#2a273c]/80 backdrop-blur-sm" @click="showTurnawayModal = false"></div>
      <div class="bg-white rounded-3xl w-full max-w-md p-8 relative shadow-2xl animate-in zoom-in-95 duration-200">
        <h3 class="text-2xl font-black text-[#2a273c] mb-6">Guest Turnaway</h3>
        <div class="space-y-4">
          <div class="space-y-1">
            <label class="text-xs font-black text-[#2a273c]/50 uppercase tracking-widest">Reason</label>
            <select class="form-select"><option>Inadequate Price</option><option>Missing Feature</option><option>Fully Booked</option></select>
          </div>
          <div class="space-y-1">
            <label class="text-xs font-black text-[#2a273c]/50 uppercase tracking-widest">Description</label>
            <textarea class="form-input min-h-[100px]" placeholder="Brief notes..."></textarea>
          </div>
          <div class="flex gap-3 pt-4">
            <button @click="showTurnawayModal = false" class="flex-1 py-3 font-bold text-[#8f9793] hover:text-[#2a273c]">Cancel</button>
            <button @click="confirmTurnaway" class="flex-2 px-8 py-3 bg-[#e95a54] text-white font-black rounded-xl shadow-lg shadow-orange-200">Confirm Turnaway</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '../services/api';

const route = useRoute();
const router = useRouter();

const isEdit = computed(() => !!route.params.id && route.query.mode !== 'view');
const isView = computed(() => route.query.mode === 'view');
const isSelected = ref(false);

const countries = ref([]);
const units = ref([]);
const sources = ref([]);
const services = ref([]);
const suggestions = ref([]);
const searchQuery = ref('');
const searchMode = ref('name');
const submitting = ref(false);
const showTurnawayModal = ref(false);

const form = reactive({
  customer: {
    id: null,
    name: '',
    phone: '',
    type: '',
    nationality: '',
    id_type: '',
    id_number: '',
    gender: 'male',
    email: '',
  },
  category: 'single',
  rent_type: 'daily',
  check_in: new Date().toISOString().split('T')[0],
  check_out: new Date(Date.now() + 86400000).toISOString().split('T')[0],
  source_id: '',
  source_number: '',
  unit_id: '',
  adults: 1,
  children: 0,
  purpose: '1',
  highlight: '',
  selected_services: [2] // Default breakfast
});

// Mock pricing logic for demo
const baseRate = ref(0);
const nights = ref(1);

const accommodationTotal = computed(() => baseRate.value * nights.value);
const subtotal = computed(() => accommodationTotal.value);
const vatTotal = computed(() => subtotal.value * 0.15);
const ewaTotal = computed(() => subtotal.value * 0.025);
const grandTotal = computed(() => subtotal.value + vatTotal.value + ewaTotal.value);

function calculateNights() {
  const start = new Date(form.check_in);
  const end = new Date(form.check_out);
  const diff = Math.ceil((end - start) / (1000 * 60 * 60 * 24));
  nights.value = Math.max(1, diff);
}

function handleUnitChange() {
  const unit = units.value.find(u => u.id === form.unit_id);
  baseRate.value = unit?.price || 970;
}

function isSelectedService(id) {
  return form.selected_services.includes(id);
}

async function handleSearch() {
  if (searchQuery.value.length < 2) {
    suggestions.value = [];
    return;
  }
  try {
    const { data } = await api.get(`/search?query=${searchQuery.value}&mode=${searchMode.value}`);
    suggestions.value = data;
  } catch (e) {}
}

function selectCustomer(guest) {
  form.customer.id = guest.id;
  form.customer.name = guest.name || guest.full_name;
  form.customer.phone = guest.phone;
  form.customer.email = guest.email;
  form.customer.id_number = guest.id_number;
  isSelected.value = true;
  suggestions.value = [];
}

async function submitReservation() {
  submitting.value = true;
  try {
    // In real app, we'd post to /api/bookings
    // For demo, we just wait and go back
    await new Promise(r => setTimeout(r, 1500));
    router.push('/reservations/management');
  } catch (e) {
    alert('Failed to save reservation');
  } finally {
    submitting.value = false;
  }
}

function confirmTurnaway() {
  alert('Turnaway recorded successfully');
  showTurnawayModal.value = false;
  router.push('/dashboard');
}

onMounted(async () => {
  try {
    const [cRes, uRes, sRes, svRes] = await Promise.all([
      api.get('/lookups/countries'),
      api.get('/rooms'), // Using rooms as units for now
      api.get('/settings/booking_sources'), // Fallback if no specific sources api
      api.get('/service-categories')
    ]);
    
    countries.value = cRes.data;
    units.value = uRes.data.data || uRes.data;
    sources.value = sRes.data || [
      {id: 31478, name: 'Booking.com'}, 
      {id: 34096, name: 'Direct Sales'},
      {id: 15101, name: 'Reception'}
    ];
    services.value = svRes.data;
  } catch (e) {}
  
  if (route.query.unit_id) {
    form.unit_id = parseInt(route.query.unit_id);
    handleUnitChange();
  }
  
  if (route.params.id) {
    // Load existing reservation data
  }
});
</script>

<style scoped>
.form-input {
  @apply w-full bg-[#f2f0eb]/50 border-2 border-transparent focus:border-[#e95a54] rounded-xl px-4 py-3 outline-none transition-all font-bold text-[#2a273c] shadow-sm disabled:opacity-50;
}
.form-select {
  @apply w-full bg-[#f2f0eb]/50 border-2 border-transparent focus:border-[#e95a54] rounded-xl px-4 py-3 outline-none transition-all font-bold text-[#2a273c] shadow-sm appearance-none disabled:opacity-50;
  background-image: url("data:image/svg+xml,%3Csvg%20xmlns%3D'http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg'%20fill%3D'none'%20viewBox%3D'0%200%2024%2024'%20stroke%3D'%232a273c'%20stroke-width%3D'3'%3E%3Cpath%20stroke-linecap%3D'round'%20stroke-linejoin%3D'round'%20d%3D'M19%209l-7%207-7-7'%2F%3E%3C%2Fsvg%3E");
  background-repeat: no-repeat;
  background-position: right 1rem center;
  background-size: 1.2rem;
}
.text-muted-green { color: #8f9793; }
</style>
