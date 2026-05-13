<template>
    <div class="p-6 max-w-7xl mx-auto space-y-6">

        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Payment Correction</h1>
                <p class="text-gray-500 mt-1 text-sm">
                    Correct payment method or amount on frozen transactions. Original transactions are never modified.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <div class="relative group">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400 group-focus-within:text-indigo-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </span>
                    <input
                        v-model="txSearch"
                        @input="onSearch"
                        type="text"
                        placeholder="Search transaction ID or reservation..."
                        class="pl-10 pr-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition-all w-full md:w-72 shadow-sm"
                    />
                </div>
            </div>
        </div>

        <!-- Correction History Table -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h2 class="text-base font-semibold text-gray-800">Correction History</h2>
                <span class="text-xs text-gray-400 bg-gray-50 px-3 py-1 rounded-full">Current team · Open business date</span>
            </div>

            <div v-if="loading" class="p-12 flex justify-center">
                <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-indigo-600"></div>
            </div>

            <div v-else-if="corrections.length === 0" class="p-12 text-center">
                <div class="mx-auto w-20 h-20 bg-indigo-50 rounded-full flex items-center justify-center mb-4">
                    <svg class="w-10 h-10 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 14l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="text-base font-medium text-gray-700">No corrections yet</h3>
                <p class="text-gray-400 text-sm mt-1">Search for a frozen transaction above to start a correction workflow.</p>
            </div>

            <div v-else class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-gray-50 text-gray-500 text-xs font-semibold uppercase tracking-wider">
                            <th class="px-6 py-4">Log #</th>
                            <th class="px-6 py-4">Frozen TX</th>
                            <th class="px-6 py-4">Correction Type</th>
                            <th class="px-6 py-4">Original</th>
                            <th class="px-6 py-4">Corrected</th>
                            <th class="px-6 py-4">Business Date</th>
                            <th class="px-6 py-4">By</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="c in corrections" :key="c.id" class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 font-mono text-indigo-600 font-bold">#{{ c.id }}</td>
                            <td class="px-6 py-4 font-mono text-gray-600">TX#{{ c.frozen_transaction_id }}</td>
                            <td class="px-6 py-4">
                                <span :class="typeClass(c.correction_type)" class="px-2.5 py-1 rounded-full text-xs font-medium whitespace-nowrap">
                                    {{ formatType(c.correction_type) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <div class="font-medium text-gray-800">{{ formatMoney(c.original_amount) }}</div>
                                <div class="text-xs text-gray-400">{{ c.original_payment_type }}</div>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <div class="font-medium text-green-700">{{ formatMoney(c.correct_amount) }}</div>
                                <div class="text-xs text-gray-400">{{ c.correct_payment_type }}</div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ c.posted_business_date }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ c.creator?.name || '—' }}</td>
                        </tr>
                    </tbody>
                </table>

                <!-- Pagination -->
                <div v-if="meta" class="px-6 py-4 border-t border-gray-100 flex items-center justify-between text-sm text-gray-500">
                    <span>Showing {{ meta.from }}–{{ meta.to }} of {{ meta.total }}</span>
                    <div class="flex gap-2">
                        <button
                            v-if="meta.current_page > 1"
                            @click="fetchHistory(meta.current_page - 1)"
                            class="px-3 py-1 rounded-lg border border-gray-200 hover:bg-gray-50 transition-colors"
                        >← Prev</button>
                        <button
                            v-if="meta.current_page < meta.last_page"
                            @click="fetchHistory(meta.current_page + 1)"
                            class="px-3 py-1 rounded-lg border border-gray-200 hover:bg-gray-50 transition-colors"
                        >Next →</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Frozen Transaction Lookup Results -->
        <div v-if="txSearch && frozenResults.length > 0" class="bg-white rounded-2xl shadow-sm border border-indigo-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-indigo-50 bg-indigo-50">
                <h2 class="text-base font-semibold text-indigo-800">Frozen Transactions Found</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-indigo-50/50 text-indigo-500 text-xs font-semibold uppercase tracking-wider">
                            <th class="px-6 py-3">TX ID</th>
                            <th class="px-6 py-3">Amount</th>
                            <th class="px-6 py-3">Method</th>
                            <th class="px-6 py-3">Date</th>
                            <th class="px-6 py-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-indigo-50">
                        <tr v-for="tx in frozenResults" :key="tx.id" class="hover:bg-indigo-50/30 transition-colors">
                            <td class="px-6 py-3 font-mono font-bold text-indigo-600">TX#{{ tx.id }}</td>
                            <td class="px-6 py-3 text-sm font-medium">{{ formatMoney(tx.amount / 100) }}</td>
                            <td class="px-6 py-3 text-sm text-gray-500">{{ tx.meta?.payment_type || '—' }}</td>
                            <td class="px-6 py-3 text-sm text-gray-500">{{ formatDate(tx.created_at) }}</td>
                            <td class="px-6 py-3 text-right">
                                <button
                                    @click="openModal(tx)"
                                    class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors shadow-sm"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                    Correct
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Correction Modal -->
        <PaymentCorrectionModal
            :show="showModal"
            :transaction="selectedTx"
            @close="showModal = false"
            @success="handleSuccess"
        />
    </div>
</template>

<script>
import api from '../../services/api';
import PaymentCorrectionModal from '../../components/finance/PaymentCorrectionModal.vue';

function debounce(fn, delay) {
    let timer;
    return function (...args) {
        clearTimeout(timer);
        timer = setTimeout(() => fn.apply(this, args), delay);
    };
}

export default {
    components: { PaymentCorrectionModal },

    data() {
        return {
            loading: false,
            corrections: [],
            meta: null,
            txSearch: '',
            frozenResults: [],
            showModal: false,
            selectedTx: null,
        };
    },

    mounted() {
        this.fetchHistory();
        this.onSearch = debounce(this.searchFrozen, 450);
    },

    methods: {
        async fetchHistory(page = 1) {
            this.loading = true;
            try {
                const { data } = await api.get('/finance/payment-corrections', { params: { page } });
                this.corrections = data.data;
                this.meta = data.meta;
            } catch (e) {
                console.error('Failed to fetch correction history', e);
            } finally {
                this.loading = false;
            }
        },

        async searchFrozen() {
            if (!this.txSearch) { this.frozenResults = []; return; }
            try {
                const { data } = await api.get('/finance/frozen-transactions', {
                    params: { search: this.txSearch }
                });
                this.frozenResults = data.data || [];
            } catch {
                this.frozenResults = [];
            }
        },

        openModal(tx) {
            this.selectedTx = tx;
            this.showModal = true;
        },

        handleSuccess(log) {
            this.showModal = false;
            this.frozenResults = [];
            this.txSearch = '';
            this.fetchHistory();
        },

        formatMoney(v) {
            return new Intl.NumberFormat('en-SA', { style: 'currency', currency: 'SAR' }).format(v ?? 0);
        },

        formatDate(d) {
            return d ? new Date(d).toLocaleDateString() : '—';
        },

        formatType(t) {
            const map = {
                wrong_payment_method:    'Wrong Method',
                overcharge:              'Overcharge',
                undercharge:             'Undercharge',
                wrong_method_and_amount: 'Method & Amount',
            };
            return map[t] || t;
        },

        typeClass(t) {
            const map = {
                wrong_payment_method:    'bg-orange-100 text-orange-700',
                overcharge:              'bg-red-100 text-red-700',
                undercharge:             'bg-blue-100 text-blue-700',
                wrong_method_and_amount: 'bg-purple-100 text-purple-700',
            };
            return map[t] || 'bg-gray-100 text-gray-700';
        },
    },
};
</script>
