<template>
    <div class="p-6 max-w-7xl mx-auto space-y-6">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Room Revenue Adjustments</h1>
                <p class="text-gray-500 mt-1 text-sm">Post rebates or extra charges for closed business dates.</p>
            </div>
            <div class="flex items-center gap-3">
                <div class="relative group">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400 group-focus-within:text-indigo-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </span>
                    <input v-model="searchQuery" @input="debounceSearch" type="text" placeholder="Search Reservation Code..." class="pl-10 pr-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition-all w-full md:w-64 shadow-sm" />
                </div>
            </div>
        </div>

        <!-- Results Table -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div v-if="loading" class="p-12 flex justify-center">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
            </div>

            <div v-else-if="reservations.length === 0" class="p-12 text-center">
                <div class="mx-auto w-24 h-24 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                    <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 9.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900">No reservations found</h3>
                <p class="text-gray-500">Try searching for a different reservation code.</p>
            </div>

            <div v-else class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-gray-50 text-gray-600 text-xs font-semibold uppercase tracking-wider">
                            <th class="px-6 py-4">Reservation</th>
                            <th class="px-6 py-4">Guest</th>
                            <th class="px-6 py-4">Room</th>
                            <th class="px-6 py-4">Dates</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="res in reservations" :key="res.id" class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4">
                                <span class="font-mono font-bold text-indigo-600">{{ res.code }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">{{ res.guest?.full_name || 'Walk-in' }}</div>
                                <div class="text-xs text-gray-500">{{ res.guest?.email || 'No email' }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 bg-blue-50 text-blue-700 rounded-md text-xs font-bold">
                                    {{ res.room?.number || res.unit?.number || 'N/A' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                <div>{{ formatDate(res.check_in) }}</div>
                                <div class="text-xs text-gray-400">to {{ formatDate(res.check_out) }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <span :class="statusClass(res.status)" class="px-2.5 py-1 rounded-full text-xs font-medium">
                                    {{ res.status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <button @click="openAdjustmentModal(res)" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors shadow-sm">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                                    </svg>
                                    Adjust
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Adjustment Modal -->
        <AdjustmentModal 
            :show="showModal" 
            :reservation-id="selectedReservation?.id" 
            :initial-date="selectedReservation?.check_in"
            @close="showModal = false"
            @success="handleSuccess"
        />
    </div>
</template>

<script>
import api from '../../services/api';
import AdjustmentModal from '../../components/finance/AdjustmentModal.vue';

/** Native debounce — no lodash dependency needed */
function debounce(fn, delay) {
    let timer;
    return function (...args) {
        clearTimeout(timer);
        timer = setTimeout(() => fn.apply(this, args), delay);
    };
}

export default {
    components: {
        AdjustmentModal
    },
    data() {
        return {
            loading: false,
            searchQuery: '',
            reservations: [],
            showModal: false,
            selectedReservation: null
        };
    },
    methods: {
        debounceSearch: debounce(function() {
            this.fetchReservations();
        }, 500),

        async fetchReservations() {
            if (!this.searchQuery) {
                this.reservations = [];
                return;
            }
            this.loading = true;
            try {
                const response = await api.get(`/reservations?search=${this.searchQuery}`);
                this.reservations = response.data.data;
            } catch (error) {
                console.error('Search failed', error);
            } finally {
                this.loading = false;
            }
        },

        formatDate(date) {
            if (!date) return 'N/A';
            return new Date(date).toLocaleDateString();
        },

        statusClass(status) {
            const classes = {
                'confirmed': 'bg-green-100 text-green-800',
                'checked_in': 'bg-blue-100 text-blue-800',
                'checked_out': 'bg-gray-100 text-gray-800',
                'cancelled': 'bg-red-100 text-red-800'
            };
            return classes[status] || 'bg-gray-100 text-gray-800';
        },

        openAdjustmentModal(res) {
            this.selectedReservation = res;
            this.showModal = true;
        },

        handleSuccess(data) {
            console.log('Adjustment posted', data);
            // Optionally refresh or show success state
        }
    }
};
</script>
