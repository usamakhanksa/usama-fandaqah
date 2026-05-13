<template>
  <div class="p-6 max-w-7xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Cashier Shifts</h1>
        <p class="text-gray-500 mt-1 text-sm">
          Manage daily cashier shifts, monitor balances, and approve variances.
        </p>
      </div>
      <div class="flex items-center gap-3">
        <button
          v-if="!activeShift && !loadingActive"
          @click="showOpenModal = true"
          class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl transition-all shadow-md shadow-indigo-100"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Open New Shift
        </button>
        <div v-else-if="activeShift" class="flex items-center gap-3">
          <span class="flex items-center gap-1.5 px-3 py-1.5 bg-green-50 text-green-700 text-xs font-bold rounded-full border border-green-100 animate-pulse">
            <span class="w-2 h-2 bg-green-500 rounded-full"></span>
            ACTIVE SHIFT #{{ activeShift.id }}
          </span>
          <button
            @click="showCloseModal = true"
            class="inline-flex items-center gap-2 px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-sm font-semibold rounded-xl transition-all shadow-md shadow-rose-100"
          >
            Close Shift
          </button>
        </div>
      </div>
    </div>

    <!-- Active Shift Stats -->
    <div v-if="activeShift" class="grid grid-cols-1 md:grid-cols-4 gap-4">
      <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100">
        <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Opening Balance</p>
        <p class="text-2xl font-bold text-gray-900 mt-1">{{ formatMoney(activeShift.opening_balance) }}</p>
      </div>
      <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100">
        <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">System Balance</p>
        <p class="text-2xl font-bold text-indigo-600 mt-1">{{ formatMoney(activeShift.system_balance) }}</p>
      </div>
      <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100">
        <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Opened At</p>
        <p class="text-lg font-semibold text-gray-700 mt-1">{{ formatDateTime(activeShift.opened_at) }}</p>
      </div>
      <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100">
        <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Cashier</p>
        <p class="text-lg font-semibold text-gray-700 mt-1">{{ activeShift.user?.name || 'Me' }}</p>
      </div>
    </div>

    <!-- Filters & History -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <h2 class="text-lg font-semibold text-gray-800">Shift History</h2>
        <div class="flex items-center gap-2">
          <select v-model="filters.status" @change="fetchHistory(1)" class="text-sm border border-gray-200 rounded-lg px-3 py-1.5 focus:ring-2 focus:ring-indigo-500 outline-none">
            <option value="">All Statuses</option>
            <option value="open">Open</option>
            <option value="closed">Closed</option>
            <option value="approved">Approved</option>
          </select>
        </div>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left">
          <thead>
            <tr class="bg-gray-50/50 text-gray-500 text-xs font-semibold uppercase tracking-wider">
              <th class="px-6 py-4">Shift ID</th>
              <th class="px-6 py-4">Cashier</th>
              <th class="px-6 py-4">Period</th>
              <th class="px-6 py-4">Balances (Open/System/Actual)</th>
              <th class="px-6 py-4">Variance</th>
              <th class="px-6 py-4">Status</th>
              <th class="px-6 py-4 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="shift in shifts" :key="shift.id" class="hover:bg-gray-50/50 transition-colors">
              <td class="px-6 py-4 text-sm font-bold text-indigo-600">#{{ shift.id }}</td>
              <td class="px-6 py-4 text-sm text-gray-600">{{ shift.user?.name }}</td>
              <td class="px-6 py-4 text-sm">
                <div class="font-medium text-gray-800">{{ formatDateTime(shift.opened_at) }}</div>
                <div class="text-xs text-gray-400">{{ shift.closed_at ? formatDateTime(shift.closed_at) : 'Active' }}</div>
              </td>
              <td class="px-6 py-4 text-sm">
                <div class="flex items-center gap-2">
                  <span class="text-gray-400">O:</span> {{ formatMoney(shift.opening_balance) }}
                  <span class="text-gray-400">S:</span> {{ formatMoney(shift.system_balance) }}
                  <span class="text-gray-400 font-bold" v-if="shift.closing_balance">A: {{ formatMoney(shift.closing_balance) }}</span>
                </div>
              </td>
              <td class="px-6 py-4 text-sm">
                <span v-if="shift.status !== 'open'" :class="varianceClass(shift.variance)" class="font-bold">
                  {{ formatMoney(shift.variance) }}
                </span>
                <span v-else class="text-gray-300">—</span>
              </td>
              <td class="px-6 py-4">
                <span :class="statusClass(shift.status)" class="px-2.5 py-1 rounded-full text-xs font-bold uppercase tracking-wider">
                  {{ shift.status }}
                </span>
              </td>
              <td class="px-6 py-4 text-right">
                <button
                  v-if="shift.status === 'closed'"
                  @click="openApproveModal(shift)"
                  class="text-indigo-600 hover:text-indigo-800 text-sm font-bold bg-indigo-50 px-3 py-1 rounded-lg transition-colors"
                >
                  Approve
                </button>
                <button
                  v-else
                  @click="viewShift(shift)"
                  class="text-gray-400 hover:text-gray-600"
                >
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                  </svg>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="pagination" class="px-6 py-4 border-t border-gray-100 flex items-center justify-between text-sm text-gray-500 bg-gray-50/30">
        <span>Showing {{ pagination.from }}–{{ pagination.to }} of {{ pagination.total }} shifts</span>
        <div class="flex gap-2">
          <button @click="fetchHistory(pagination.current_page - 1)" :disabled="pagination.current_page === 1" class="px-3 py-1.5 rounded-lg border border-gray-200 bg-white hover:bg-gray-50 disabled:opacity-50 transition-all">Previous</button>
          <button @click="fetchHistory(pagination.current_page + 1)" :disabled="pagination.current_page === pagination.last_page" class="px-3 py-1.5 rounded-lg border border-gray-200 bg-white hover:bg-gray-50 disabled:opacity-50 transition-all">Next</button>
        </div>
      </div>
    </div>

    <!-- Modals (Simple implementation for now) -->
    <div v-if="showOpenModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
      <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden">
        <div class="p-6 border-b border-gray-100 bg-indigo-50">
          <h3 class="text-xl font-bold text-indigo-900">Open New Shift</h3>
          <p class="text-indigo-600 text-sm mt-1">Initialize your cash drawer for today.</p>
        </div>
        <div class="p-6 space-y-4">
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Opening Balance (SAR)</label>
            <input v-model="form.opening_balance" type="number" step="0.01" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none transition-all" placeholder="0.00" />
          </div>
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Notes</label>
            <textarea v-model="form.notes" rows="3" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none transition-all" placeholder="Optional notes..."></textarea>
          </div>
        </div>
        <div class="p-6 bg-gray-50 flex gap-3">
          <button @click="showOpenModal = false" class="flex-1 px-4 py-2.5 text-sm font-semibold text-gray-600 bg-white border border-gray-200 rounded-xl hover:bg-gray-100 transition-all">Cancel</button>
          <button @click="handleOpenShift" :disabled="submitting" class="flex-1 px-4 py-2.5 text-sm font-semibold text-white bg-indigo-600 rounded-xl hover:bg-indigo-700 transition-all disabled:opacity-50">
            {{ submitting ? 'Opening...' : 'Start Shift' }}
          </button>
        </div>
      </div>
    </div>

    <div v-if="showCloseModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
      <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden">
        <div class="p-6 border-b border-gray-100 bg-rose-50">
          <h3 class="text-xl font-bold text-rose-900">Close Shift</h3>
          <p class="text-rose-600 text-sm mt-1">End your session and record actual cash.</p>
        </div>
        <div class="p-6 space-y-4">
          <div class="bg-indigo-50 p-4 rounded-xl border border-indigo-100">
            <div class="flex justify-between items-center text-sm">
              <span class="text-indigo-700 font-medium">Expected (System) Balance</span>
              <span class="text-indigo-900 font-bold text-lg">{{ formatMoney(activeShift?.system_balance) }}</span>
            </div>
          </div>
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Actual Closing Balance (SAR)</label>
            <input v-model="form.closing_balance" type="number" step="0.01" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none transition-all" placeholder="0.00" />
          </div>
          <div v-if="variance !== 0" :class="variance > 0 ? 'bg-emerald-50 text-emerald-700 border-emerald-100' : 'bg-rose-50 text-rose-700 border-rose-100'" class="p-3 rounded-lg text-xs font-bold border">
            Variance: {{ formatMoney(variance) }} ({{ variance > 0 ? 'Surplus' : 'Shortage' }})
          </div>
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Closing Notes</label>
            <textarea v-model="form.notes" rows="3" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none transition-all" placeholder="Explain any variance..."></textarea>
          </div>
        </div>
        <div class="p-6 bg-gray-50 flex gap-3">
          <button @click="showCloseModal = false" class="flex-1 px-4 py-2.5 text-sm font-semibold text-gray-600 bg-white border border-gray-200 rounded-xl hover:bg-gray-100 transition-all">Cancel</button>
          <button @click="handleCloseShift" :disabled="submitting" class="flex-1 px-4 py-2.5 text-sm font-semibold text-white bg-rose-600 rounded-xl hover:bg-rose-700 transition-all disabled:opacity-50">
            {{ submitting ? 'Closing...' : 'Close & Finalize' }}
          </button>
        </div>
      </div>
    </div>

    <div v-if="showApproveModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
      <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden">
        <div class="p-6 border-b border-gray-100 bg-indigo-50">
          <h3 class="text-xl font-bold text-indigo-900">Approve Shift Variance</h3>
          <p class="text-indigo-600 text-sm mt-1">Review and approve the cashier's report.</p>
        </div>
        <div class="p-6 space-y-4 text-sm">
          <div class="grid grid-cols-2 gap-4">
            <div class="bg-gray-50 p-3 rounded-lg">
              <span class="text-gray-500 block">Cashier</span>
              <span class="font-bold">{{ selectedShift?.user?.name }}</span>
            </div>
            <div class="bg-gray-50 p-3 rounded-lg">
              <span class="text-gray-500 block">Variance</span>
              <span :class="varianceClass(selectedShift?.variance)" class="font-bold">{{ formatMoney(selectedShift?.variance) }}</span>
            </div>
          </div>
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Manager Notes / Approval Remarks</label>
            <textarea v-model="form.notes" rows="3" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none transition-all" placeholder="Enter approval comments..."></textarea>
          </div>
        </div>
        <div class="p-6 bg-gray-50 flex gap-3">
          <button @click="showApproveModal = false" class="flex-1 px-4 py-2.5 text-sm font-semibold text-gray-600 bg-white border border-gray-200 rounded-xl hover:bg-gray-100 transition-all">Cancel</button>
          <button @click="handleApproveShift" :disabled="submitting" class="flex-1 px-4 py-2.5 text-sm font-semibold text-white bg-indigo-600 rounded-xl hover:bg-indigo-700 transition-all disabled:opacity-50">
            {{ submitting ? 'Approving...' : 'Confirm Approval' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import api from '../../services/api';

export default {
  data() {
    return {
      loadingActive: true,
      loadingHistory: false,
      submitting: false,
      activeShift: null,
      shifts: [],
      pagination: null,
      filters: {
        status: '',
      },
      showOpenModal: false,
      showCloseModal: false,
      showApproveModal: false,
      selectedShift: null,
      form: {
        opening_balance: 0,
        closing_balance: 0,
        notes: '',
      }
    };
  },

  computed: {
    variance() {
      if (!this.activeShift) return 0;
      return (this.form.closing_balance || 0) - this.activeShift.system_balance;
    }
  },

  mounted() {
    this.fetchActiveShift();
    this.fetchHistory();
  },

  methods: {
    async fetchActiveShift() {
      this.loadingActive = true;
      try {
        const { data } = await api.get('/cashier-shifts/active');
        this.activeShift = data;
        if (data) {
          this.form.closing_balance = data.system_balance;
        }
      } catch (e) {
        console.error('Failed to fetch active shift', e);
      } finally {
        this.loadingActive = false;
      }
    },

    async fetchHistory(page = 1) {
      this.loadingHistory = true;
      try {
        const { data } = await api.get('/cashier-shifts', {
          params: { page, ...this.filters }
        });
        this.shifts = data.data;
        this.pagination = {
          current_page: data.current_page,
          last_page: data.last_page,
          from: data.from,
          to: data.to,
          total: data.total
        };
      } catch (e) {
        console.error('Failed to fetch shift history', e);
      } finally {
        this.loadingHistory = false;
      }
    },

    async handleOpenShift() {
      this.submitting = true;
      try {
        await api.post('/cashier-shifts/open', {
          opening_balance: this.form.opening_balance,
          notes: this.form.notes
        });
        this.showOpenModal = false;
        this.form.notes = '';
        await this.fetchActiveShift();
        await this.fetchHistory(1);
      } catch (e) {
        alert(e.response?.data?.message || 'Failed to open shift');
      } finally {
        this.submitting = false;
      }
    },

    async handleCloseShift() {
      this.submitting = true;
      try {
        await api.post(`/cashier-shifts/${this.activeShift.id}/close`, {
          closing_balance: this.form.closing_balance,
          notes: this.form.notes
        });
        this.showCloseModal = false;
        this.form.notes = '';
        this.activeShift = null;
        await this.fetchActiveShift();
        await this.fetchHistory(1);
      } catch (e) {
        alert(e.response?.data?.message || 'Failed to close shift');
      } finally {
        this.submitting = false;
      }
    },

    openApproveModal(shift) {
      this.selectedShift = shift;
      this.form.notes = '';
      this.showApproveModal = true;
    },

    async handleApproveShift() {
      this.submitting = true;
      try {
        await api.post(`/cashier-shifts/${this.selectedShift.id}/approve`, {
          notes: this.form.notes
        });
        this.showApproveModal = false;
        await this.fetchHistory();
      } catch (e) {
        alert(e.response?.data?.message || 'Failed to approve shift');
      } finally {
        this.submitting = false;
      }
    },

    formatMoney(v) {
      return new Intl.NumberFormat('en-SA', { style: 'currency', currency: 'SAR' }).format(v ?? 0);
    },

    formatDateTime(d) {
      if (!d) return '—';
      return new Date(d).toLocaleString('en-GB', {
        day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit'
      });
    },

    statusClass(s) {
      const map = {
        open: 'bg-green-100 text-green-700',
        closed: 'bg-amber-100 text-amber-700',
        approved: 'bg-indigo-100 text-indigo-700',
      };
      return map[s] || 'bg-gray-100 text-gray-700';
    },

    varianceClass(v) {
      if (!v || v == 0) return 'text-gray-400';
      return v > 0 ? 'text-emerald-600' : 'text-rose-600';
    },

    viewShift(shift) {
      // Implement details view or redirect
    }
  }
};
</script>
