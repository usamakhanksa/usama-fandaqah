<template>
  <div v-if="show" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
    <!-- Overlay -->
    <div class="absolute inset-0 bg-[#2a273c]/60 backdrop-blur-sm" @click="close"></div>
    
    <!-- Modal -->
    <div class="relative bg-white w-full max-w-lg rounded-3xl shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-300">
      <div class="p-8">
        <div class="flex justify-between items-center mb-6">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-[#e95a54]/10 flex items-center justify-center text-[#e95a54]">
              <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/></svg>
            </div>
            <h2 class="text-xl font-bold text-[#2a273c]">Contact Our Support</h2>
          </div>
          <button @click="close" class="text-[#8f9793] hover:text-[#e95a54] transition-colors p-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
          </button>
        </div>

        <form @submit.prevent="submitForm" id="quickForm" class="space-y-4">
          <!-- Full Name -->
          <div class="space-y-1.5">
            <label class="text-xs font-bold text-[#8f9793] uppercase tracking-wider">Full Name</label>
            <input 
              v-model="form.full_name" 
              type="text" 
              class="w-full px-4 py-3 rounded-xl border-2 border-[#f2f0eb] bg-[#f2f0eb]/30 focus:border-[#e95a54] focus:bg-white outline-none transition-all"
              placeholder="e.g. John Doe"
              required
            >
          </div>

          <!-- Email -->
          <div class="space-y-1.5">
            <label class="text-xs font-bold text-[#8f9793] uppercase tracking-wider">Email Address</label>
            <input 
              v-model="form.email" 
              type="email" 
              class="w-full px-4 py-3 rounded-xl border-2 border-[#f2f0eb] bg-[#f2f0eb]/30 focus:border-[#e95a54] focus:bg-white outline-none transition-all"
              placeholder="john@example.com"
              required
            >
          </div>

          <!-- Phone -->
          <div class="space-y-1.5">
            <label class="text-xs font-bold text-[#8f9793] uppercase tracking-wider">Phone Number</label>
            <div class="flex gap-2">
              <select v-model="form.country_code" class="px-3 py-3 rounded-xl border-2 border-[#f2f0eb] bg-[#f2f0eb]/30 outline-none">
                <option value="+966">🇸🇦 +966</option>
                <option value="+971">🇦🇪 +971</option>
                <option value="+20">🇪🇬 +20</option>
                <option value="+1">🇺🇸 +1</option>
              </select>
              <input 
                v-model="form.phone" 
                type="tel" 
                class="flex-1 px-4 py-3 rounded-xl border-2 border-[#f2f0eb] bg-[#f2f0eb]/30 focus:border-[#e95a54] focus:bg-white outline-none transition-all"
                placeholder="5XXXXXXXX"
                maxlength="9"
                required
              >
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <!-- Property Type -->
            <div class="space-y-1.5">
              <label class="text-xs font-bold text-[#8f9793] uppercase tracking-wider">Property Type</label>
              <select v-model="form.property_type" class="w-full px-4 py-3 rounded-xl border-2 border-[#f2f0eb] bg-[#f2f0eb]/30 focus:border-[#e95a54] focus:bg-white outline-none transition-all" required>
                <option value="">Select</option>
                <option value="hotel">Hotel</option>
                <option value="serviced-apartments">Apartments</option>
                <option value="resort">Resort</option>
                <option value="boutique-hotel">Boutique</option>
                <option value="other">Other</option>
              </select>
            </div>

            <!-- Product Interest -->
            <div class="space-y-1.5">
              <label class="text-xs font-bold text-[#8f9793] uppercase tracking-wider">Interested In</label>
              <select v-model="form.product_interest" class="w-full px-4 py-3 rounded-xl border-2 border-[#f2f0eb] bg-[#f2f0eb]/30 focus:border-[#e95a54] focus:bg-white outline-none transition-all" required>
                <option value="">Select</option>
                <option value="fastpass">FastPass</option>
                <option value="signstay">SignStay</option>
                <option value="swiftcheckin">SwiftCheckIn</option>
                <option value="both">All Products</option>
              </select>
            </div>
          </div>

          <!-- Submit -->
          <button 
            type="submit" 
            class="w-full !mt-8 py-4 rounded-xl bg-gradient-to-r from-[#e95a54] to-[#d6454a] text-white font-bold text-lg shadow-lg shadow-[#e95a54]/30 hover:scale-[1.02] active:scale-[0.98] transition-all disabled:opacity-50 disabled:scale-100 flex items-center justify-center gap-2"
            :disabled="submitting"
          >
            <span v-if="submitting" class="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
            {{ submitting ? 'Submitting...' : 'Subscribe Now' }}
          </button>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import api from '../services/api';

const props = defineProps({
  show: Boolean
});

const emit = defineEmits(['close', 'success']);

const submitting = ref(false);

const form = ref({
  full_name: '',
  email: '',
  phone: '',
  country_code: '+966',
  property_type: '',
  product_interest: '',
  source: 'feature_slider'
});

const close = () => {
  if (!submitting.value) emit('close');
};

const submitForm = async () => {
  submitting.value = true;
  try {
    await api.post('/leads/submit', form.value);
    emit('success');
    form.value = {
      full_name: '',
      email: '',
      phone: '',
      country_code: '+966',
      property_type: '',
      product_interest: '',
      source: 'feature_slider'
    };
    alert('Thank you! Your request has been submitted.');
    close();
  } catch (e) {
    alert(e.response?.data?.message || 'Submission failed. Please try again.');
  } finally {
    submitting.value = false;
  }
};
</script>
