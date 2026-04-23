<script setup>
import Layout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    transactions: Object,
    filters: Object,
    metrics: Object
});

const showModal = ref(false);
const form = useForm({
    type: 'deposit',
    amount: '',
    category: '',
    transaction_date: new Date().toISOString().split('T')[0],
    description: ''
});

const categories = [
    'Cash Drop', 'Bank Transfer', 'Petty Cash Replenishment', 
    'Salary Payout', 'Vendor Payment', 'Other'
];

const submit = () => {
    form.post(route('analytics.safe.store'), {
        onSuccess: () => {
            showModal.value = false;
            form.reset();
        }
    });
};

const deleteTransaction = (id) => {
    if (confirm('Are you sure you want to delete this record?')) {
        router.delete(route('analytics.safe.destroy', id));
    }
};

const formatCurrency = (amt) => {
    return new Intl.NumberFormat('en-SA', { style: 'currency', currency: 'SAR' }).format(amt);
};
</script>

<template>
    <Head title="Safe Movement" />

    <Layout>
        <div class="p-6 bg-[#f2f0eb] min-h-screen">
            <!-- Header -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-[#2a273c]">Safe Movement</h1>
                    <p class="text-[#8f9793]">Physical vault and bank deposit tracking</p>
                </div>
                <button @click="showModal = true" 
                        class="px-6 py-3 bg-[#2a273c] text-white rounded-xl shadow-lg hover:shadow-[#2a273c]/20 transition-all font-bold flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
                    New Transaction
                </button>
            </div>

            <!-- Metrics -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white p-6 rounded-2xl shadow-sm border-t-4 border-[#e95a54]">
                    <div class="flex justify-between items-center mb-4">
                        <span class="text-xs font-bold text-[#8f9793] uppercase tracking-widest">Total Deposits</span>
                        <div class="p-2 bg-[#fbcdab]/20 rounded-lg"><svg class="w-5 h-5 text-[#e95a54]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12" /></svg></div>
                    </div>
                    <h3 class="text-2xl font-black text-[#2a273c]">{{ formatCurrency(metrics.total_deposits) }}</h3>
                </div>
                <div class="bg-white p-6 rounded-2xl shadow-sm border-t-4 border-[#8f9793]">
                    <div class="flex justify-between items-center mb-4">
                        <span class="text-xs font-bold text-[#8f9793] uppercase tracking-widest">Total Withdrawals</span>
                        <div class="p-2 bg-[#f2f0eb] rounded-lg"><svg class="w-5 h-5 text-[#2a273c]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5-5v12" /></svg></div>
                    </div>
                    <h3 class="text-2xl font-black text-[#2a273c]">{{ formatCurrency(metrics.total_withdrawals) }}</h3>
                </div>
                <div class="bg-[#2a273c] p-6 rounded-2xl shadow-xl border-t-4 border-[#fbcdab]">
                    <div class="flex justify-between items-center mb-4 text-white">
                        <span class="text-xs font-bold uppercase tracking-widest opacity-80">Safe Net Balance</span>
                        <div class="p-2 bg-white/10 rounded-lg"><svg class="w-5 h-5 text-[#fbcdab]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg></div>
                    </div>
                    <h3 class="text-2xl font-black text-white">{{ formatCurrency(metrics.net_safe_balance) }}</h3>
                </div>
            </div>

            <!-- List -->
            <div class="bg-white rounded-3xl shadow-sm overflow-hidden border border-[#fbcdab]/20">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-[#f2f0eb]/50">
                            <th class="p-5 text-xs font-bold text-[#8f9793] uppercase tracking-widest">Transaction Ref</th>
                            <th class="p-5 text-xs font-bold text-[#8f9793] uppercase tracking-widest text-center">Type</th>
                            <th class="p-5 text-xs font-bold text-[#8f9793] uppercase tracking-widest">Category</th>
                            <th class="p-5 text-xs font-bold text-[#8f9793] uppercase tracking-widest text-right">Amount</th>
                            <th class="p-5 text-xs font-bold text-[#8f9793] uppercase tracking-widest">Performed By</th>
                            <th class="p-5 text-xs font-bold text-[#8f9793] uppercase tracking-widest text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#f2f0eb]">
                        <tr v-for="tx in transactions.data" :key="tx.id" class="group hover:bg-[#f2f0eb]/20 transition-colors">
                            <td class="p-5">
                                <div class="font-bold text-[#2a273c]">{{ tx.reference_number }}</div>
                                <div class="text-[10px] text-[#8f9793]">{{ tx.transaction_date }}</div>
                            </td>
                            <td class="p-5 text-center">
                                <span :class="[
                                    'px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider',
                                    tx.type === 'deposit' ? 'bg-[#fbcdab]/30 text-[#e95a54]' : 'bg-[#8f9793]/20 text-[#2a273c]'
                                ]">
                                    {{ tx.type }}
                                </span>
                            </td>
                            <td class="p-5 text-sm text-[#2a273c]">{{ tx.category }}</td>
                            <td class="p-5 text-right font-black" :class="tx.type === 'deposit' ? 'text-[#e95a54]' : 'text-[#2a273c]'">
                                {{ tx.type === 'deposit' ? '+' : '-' }}{{ formatCurrency(tx.amount) }}
                            </td>
                            <td class="p-5">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-full bg-[#f2f0eb] flex items-center justify-center text-[10px] font-bold text-[#8f9793]">
                                        {{ tx.performed_by.charAt(0) }}
                                    </div>
                                    <span class="text-sm text-[#8f9793]">{{ tx.performed_by }}</span>
                                </div>
                            </td>
                            <td class="p-5 text-center">
                                <button @click="deleteTransaction(tx.id)" class="p-2 text-[#8f9793] hover:text-[#e95a54] transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Transaction Modal -->
        <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-[#2a273c]/80 backdrop-blur-sm" @click="showModal = false"></div>
            <div class="relative bg-white rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden animate-in zoom-in-95 duration-200">
                <div class="p-8 bg-[#2a273c] text-white">
                    <h3 class="text-2xl font-bold">New Safe Transaction</h3>
                    <p class="text-white/60 text-sm">Record a manual deposit or withdrawal</p>
                </div>
                <form @submit.prevent="submit" class="p-8 space-y-6">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-[#8f9793] uppercase">Transaction Type</label>
                            <select v-model="form.type" class="w-full bg-[#f2f0eb] border-0 rounded-xl focus:ring-2 focus:ring-[#e95a54]">
                                <option value="deposit">Deposit (+)</option>
                                <option value="withdrawal">Withdrawal (-)</option>
                            </select>
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-[#8f9793] uppercase">Amount (SAR)</label>
                            <input v-model="form.amount" type="number" step="0.01" class="w-full bg-[#f2f0eb] border-0 rounded-xl focus:ring-2 focus:ring-[#e95a54]" required />
                        </div>
                    </div>

                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-[#8f9793] uppercase">Category</label>
                        <input v-model="form.category" list="cat-list" class="w-full bg-[#f2f0eb] border-0 rounded-xl focus:ring-2 focus:ring-[#e95a54]" required />
                        <datalist id="cat-list">
                            <option v-for="c in categories" :key="c" :value="c" />
                        </datalist>
                    </div>

                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-[#8f9793] uppercase">Transaction Date</label>
                        <input v-model="form.transaction_date" type="date" class="w-full bg-[#f2f0eb] border-0 rounded-xl focus:ring-2 focus:ring-[#e95a54]" required />
                    </div>

                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-[#8f9793] uppercase">Description / Memo</label>
                        <textarea v-model="form.description" rows="3" class="w-full bg-[#f2f0eb] border-0 rounded-xl focus:ring-2 focus:ring-[#e95a54]"></textarea>
                    </div>

                    <div class="flex gap-4 pt-4">
                        <button type="button" @click="showModal = false" class="flex-1 py-3 px-6 bg-[#f2f0eb] text-[#2a273c] rounded-xl font-bold hover:bg-[#fbcdab]/20 transition-all">Cancel</button>
                        <button type="submit" class="flex-1 py-3 px-6 bg-[#e95a54] text-white rounded-xl font-bold shadow-lg shadow-[#e95a54]/20 hover:scale-[1.02] active:scale-[0.98] transition-all" :disabled="form.processing">
                            Confirm Record
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </Layout>
</template>

<style scoped>
.animate-in {
    animation: zoomIn 0.3s ease-out;
}

@keyframes zoomIn {
    from { opacity: 0; transform: scale(0.95); }
    to { opacity: 1; transform: scale(1); }
}
</style>
