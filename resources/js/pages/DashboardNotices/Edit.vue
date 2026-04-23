<script setup>
import { ref, onMounted } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { ChevronLeftIcon, CheckCircleIcon } from '@heroicons/vue/24/outline';
import axios from 'axios';
import { useRouter, useRoute } from 'vue-router';

const router = useRouter();
const route = useRoute();
const loading = ref(false);
const fetching = ref(true);
const errors = ref({});

const form = ref({
  title: '',
  content: '',
  type: 'info',
  is_active: true,
  expires_at: '',
});

const fetchNotice = async () => {
    try {
        const response = await axios.get(`/api/dashboard-notices/${route.params.id}`);
        const notice = response.data;
        form.value = {
            title: notice.title,
            content: notice.content,
            type: notice.type,
            is_active: !!notice.is_active,
            expires_at: notice.expires_at ? notice.expires_at.slice(0, 16) : '',
        };
    } catch (error) {
        alert('Failed to load notice.');
        router.push('/dashboard-notices');
    } finally {
        fetching.value = false;
    }
};

const submit = async () => {
  loading.value = true;
  errors.value = {};
  try {
    await axios.put(`/api/dashboard-notices/${route.params.id}`, form.value);
    router.push('/dashboard-notices');
  } catch (error) {
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors;
    } else {
      alert('An unexpected error occurred.');
    }
  } finally {
    loading.value = false;
  }
};

onMounted(() => fetchNotice());
</script>

<template>
  <AppLayout title="Edit Notice">
    <div class="px-4 sm:px-6 lg:px-8 py-8 max-w-4xl mx-auto space-y-6">
      <div class="flex items-center justify-between">
        <button @click="router.back()" class="group flex items-center text-sm font-bold text-slate-500 hover:text-navy transition-colors">
          <ChevronLeftIcon class="mr-1 h-5 w-5 group-hover:-translate-x-1 transition-transform" />
          Back to List
        </button>
      </div>

      <div v-if="fetching" class="p-20 text-center bg-white shadow-premium rounded-3xl border border-slate-100">
          <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-coral mx-auto"></div>
          <p class="mt-4 text-slate-500">Loading notice data...</p>
      </div>

      <div v-else class="bg-white shadow-premium rounded-3xl overflow-hidden border border-slate-100">
        <div class="px-8 py-6 bg-navy text-white">
          <h2 class="text-xl font-extrabold tracking-tight">Edit Operational Notice</h2>
          <p class="mt-1 text-sm text-premium-beige/70">Update the details of your announcement.</p>
        </div>

        <form @submit.prevent="submit" class="p-8 space-y-8">
          <!-- Title -->
          <div class="space-y-2">
            <label class="block text-sm font-bold text-navy">Notice Title</label>
            <input v-model="form.title" type="text" :class="[errors.title ? 'ring-coral' : 'ring-slate-200', 'block w-full rounded-xl border-0 py-3 text-navy ring-1 ring-inset placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-coral sm:text-sm bg-slate-50/50']" placeholder="e.g., Scheduled Maintenance - West Wing">
            <p v-if="errors.title" class="text-xs font-bold text-coral capitalize">{{ errors.title[0] }}</p>
          </div>

          <!-- Content -->
          <div class="space-y-2">
            <label class="block text-sm font-bold text-navy">Message Content</label>
            <textarea v-model="form.content" rows="4" :class="[errors.content ? 'ring-coral' : 'ring-slate-200', 'block w-full rounded-xl border-0 py-3 text-navy ring-1 ring-inset placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-coral sm:text-sm bg-slate-50/50']" placeholder="Provide detailed information here..."></textarea>
            <p v-if="errors.content" class="text-xs font-bold text-coral capitalize">{{ errors.content[0] }}</p>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Type -->
            <div class="space-y-2">
                <label class="block text-sm font-bold text-navy">Priority Type</label>
                <select v-model="form.type" class="block w-full rounded-xl border-0 py-3 text-navy ring-1 ring-inset ring-slate-200 focus:ring-2 focus:ring-inset focus:ring-coral sm:text-sm bg-slate-50/50">
                    <option value="info">Information (Blue)</option>
                    <option value="warning">Warning (Amber)</option>
                    <option value="urgent">Urgent (Coral)</option>
                </select>
            </div>

            <!-- Expiry -->
            <div class="space-y-2">
                <label class="block text-sm font-bold text-navy">Expiration Date (Optional)</label>
                <input v-model="form.expires_at" type="datetime-local" class="block w-full rounded-xl border-0 py-3 text-navy ring-1 ring-inset ring-slate-200 focus:ring-2 focus:ring-inset focus:ring-coral sm:text-sm bg-slate-50/50">
            </div>
          </div>

          <!-- Is Active -->
          <div class="flex items-center space-x-3 bg-slate-50 p-4 rounded-xl border border-slate-100">
            <input v-model="form.is_active" type="checkbox" class="h-5 w-5 rounded border-slate-300 text-coral focus:ring-coral transition-all">
            <label class="text-sm font-bold text-navy">This notice is currently active</label>
          </div>

          <!-- Actions -->
          <div class="pt-4 flex items-center justify-end space-x-4">
            <button type="button" @click="router.back()" class="px-6 py-3 text-sm font-bold text-slate-500 hover:text-navy transition-colors">Cancel</button>
            <button type="submit" :disabled="loading" class="inline-flex items-center px-8 py-3 rounded-xl bg-navy text-white font-black shadow-premium hover:bg-navy/90 focus:ring-2 focus:ring-offset-2 focus:ring-navy disabled:opacity-50 transition-all">
                <CheckCircleIcon v-if="!loading" class="mr-2 h-5 w-5 text-white/80" />
                <span v-if="loading" class="mr-2 animate-spin rounded-full h-4 w-4 border-t-2 border-white"></span>
                {{ loading ? 'Saving Changes...' : 'Save Changes' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
.shadow-premium {
  box-shadow: 0 10px 25px -5px rgba(42, 39, 60, 0.05), 0 8px 10px -6px rgba(42, 39, 60, 0.05);
}
</style>
