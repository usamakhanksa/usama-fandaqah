<script setup>
import { ref, onMounted } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { 
  PlusIcon, 
  PencilSquareIcon, 
  TrashIcon, 
  MagnifyingGlassIcon,
  ChevronLeftIcon,
  ChevronRightIcon
} from '@heroicons/vue/24/outline';
import axios from 'axios';
import { useRouter } from 'vue-router';

const router = useRouter();
const notices = ref([]);
const pagination = ref({});
const search = ref('');
const loading = ref(true);

const fetchNotices = async (page = 1) => {
  loading.value = true;
  try {
    const response = await axios.get('/api/dashboard-notices', {
      params: { 
        page, 
        search: search.value,
        per_page: 10 
      }
    });
    notices.value = response.data.data;
    pagination.value = response.data;
  } catch (error) {
    console.error('Failed to fetch notices:', error);
  } finally {
    loading.value = false;
  }
};

const deleteNotice = async (id) => {
  if (!confirm('Are you sure you want to delete this notice?')) return;
  
  try {
    await axios.delete(`/api/dashboard-notices/${id}`);
    fetchNotices(pagination.value.current_page);
  } catch (error) {
    alert('Failed to delete notice.');
  }
};

onMounted(() => fetchNotices());
</script>

<template>
  <AppLayout title="Manage Notices">
    <div class="px-4 sm:px-6 lg:px-8 py-8 space-y-6">
      <div class="sm:flex sm:items-center sm:justify-between">
        <div>
          <h1 class="text-2xl font-bold text-navy">Dashboard Notices</h1>
          <p class="mt-2 text-sm text-slate-500">Manage critical announcements and operational updates for all staff.</p>
        </div>
        <div class="mt-4 sm:mt-0">
          <button @click="router.push('/dashboard-notices/create')" class="inline-flex items-center rounded-xl bg-coral px-4 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-coral/90 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-coral transition-all">
            <PlusIcon class="-ml-0.5 mr-2 h-5 w-5" aria-hidden="true" />
            Create Notice
          </button>
        </div>
      </div>

      <!-- Filters -->
      <div class="flex items-center space-x-4 bg-white p-4 rounded-2xl shadow-premium border border-slate-100">
        <div class="relative flex-1">
          <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
            <MagnifyingGlassIcon class="h-5 w-5 text-slate-400" aria-hidden="true" />
          </div>
          <input v-model="search" @input="fetchNotices(1)" type="text" class="block w-full rounded-xl border-0 py-2.5 pl-10 text-navy ring-1 ring-inset ring-slate-200 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-coral sm:text-sm sm:leading-6 bg-slate-50/50" placeholder="Search notices by title or content...">
        </div>
      </div>

      <!-- Table -->
      <div class="overflow-hidden bg-white shadow-premium rounded-2xl border border-slate-100">
        <table class="min-w-full divide-y divide-slate-100">
          <thead class="bg-navy">
            <tr>
              <th scope="col" class="py-4 pl-6 pr-3 text-left text-xs font-bold uppercase tracking-wider text-white">Title</th>
              <th scope="col" class="px-3 py-4 text-left text-xs font-bold uppercase tracking-wider text-white">Type</th>
              <th scope="col" class="px-3 py-4 text-left text-xs font-bold uppercase tracking-wider text-white">Status</th>
              <th scope="col" class="px-3 py-4 text-left text-xs font-bold uppercase tracking-wider text-white">Expires At</th>
              <th scope="col" class="relative py-4 pl-3 pr-6 text-right">
                <span class="sr-only">Actions</span>
              </th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 bg-white">
            <tr v-if="loading">
                <td colspan="5" class="py-12 text-center">
                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-coral"></div>
                </td>
            </tr>
            <tr v-else-if="notices.length === 0">
                <td colspan="5" class="py-20 text-center">
                    <div class="flex flex-col items-center">
                        <InformationCircleIcon class="h-12 w-12 text-slate-200" />
                        <p class="mt-4 text-lg font-medium text-navy">No data yet</p>
                        <p class="text-slate-500">Start by creating your first notice.</p>
                        <button @click="router.push('/dashboard-notices/create')" class="mt-6 text-coral font-bold hover:underline">Add New →</button>
                    </div>
                </td>
            </tr>
            <tr v-for="notice in notices" :key="notice.id" class="hover:bg-slate-50 transition-colors">
              <td class="whitespace-nowrap py-4 pl-6 pr-3">
                <div class="text-sm font-bold text-navy">{{ notice.title }}</div>
              </td>
              <td class="whitespace-nowrap px-3 py-4">
                <span :class="[
                  notice.type === 'urgent' ? 'bg-coral/10 text-coral' : 
                  notice.type === 'warning' ? 'bg-amber-100 text-amber-700' : 
                  'bg-navy/10 text-navy',
                  'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-bold capitalize'
                ]">
                  {{ notice.type }}
                </span>
              </td>
              <td class="whitespace-nowrap px-3 py-4">
                <span :class="[notice.is_active ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-500', 'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-bold']">
                  {{ notice.is_active ? 'Active' : 'Inactive' }}
                </span>
              </td>
              <td class="whitespace-nowrap px-3 py-4 text-sm text-slate-500">
                {{ notice.expires_at ? new Date(notice.expires_at).toLocaleString() : 'Never' }}
              </td>
              <td class="relative whitespace-nowrap py-4 pl-3 pr-6 text-right text-sm font-medium">
                <div class="flex justify-end space-x-2">
                    <button @click="router.push(`/dashboard-notices/${notice.id}/edit`)" class="p-2 text-slate-400 hover:text-navy transition-colors">
                        <PencilSquareIcon class="h-5 w-5" />
                    </button>
                    <button @click="deleteNotice(notice.id)" class="p-2 text-slate-400 hover:text-coral transition-colors">
                        <TrashIcon class="h-5 w-5" />
                    </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
        
        <!-- Pagination -->
        <div v-if="pagination.total > 0" class="flex items-center justify-between border-t border-slate-100 bg-white px-6 py-4">
          <div class="flex flex-1 justify-between sm:hidden">
            <button @click="fetchNotices(pagination.current_page - 1)" :disabled="pagination.current_page === 1" class="relative inline-flex items-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 disabled:opacity-50">Previous</button>
            <button @click="fetchNotices(pagination.current_page + 1)" :disabled="pagination.current_page === pagination.last_page" class="relative ml-3 inline-flex items-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 disabled:opacity-50">Next</button>
          </div>
          <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
            <div>
              <p class="text-sm text-slate-700">
                Showing <span class="font-bold text-navy">{{ pagination.from }}</span> to <span class="font-bold text-navy">{{ pagination.to }}</span> of <span class="font-bold text-navy">{{ pagination.total }}</span> results
              </p>
            </div>
            <div>
              <nav class="isolate inline-flex -space-x-px rounded-xl shadow-sm" aria-label="Pagination">
                <button @click="fetchNotices(pagination.current_page - 1)" :disabled="pagination.current_page === 1" class="relative inline-flex items-center rounded-l-xl px-2 py-2 text-slate-400 ring-1 ring-inset ring-slate-200 hover:bg-slate-50 focus:z-20 focus:outline-offset-0 disabled:opacity-50">
                  <ChevronLeftIcon class="h-5 w-5" aria-hidden="true" />
                </button>
                <button @click="fetchNotices(pagination.current_page + 1)" :disabled="pagination.current_page === pagination.last_page" class="relative inline-flex items-center rounded-r-xl px-2 py-2 text-slate-400 ring-1 ring-inset ring-slate-200 hover:bg-slate-50 focus:z-20 focus:outline-offset-0 disabled:opacity-50">
                  <ChevronRightIcon class="h-5 w-5" aria-hidden="true" />
                </button>
              </nav>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
.shadow-premium {
  box-shadow: 0 10px 25px -5px rgba(42, 39, 60, 0.05), 0 8px 10px -6px rgba(42, 39, 60, 0.05);
}
</style>
