<script setup>
import { ref } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import Layout from '@/Layouts/AuthenticatedLayout.vue';
import { 
    ArrowLeftIcon, 
    BanknoteIcon,
    HistoryIcon,
    ClockIcon,
    CalendarIcon,
    UserIcon,
    BuildingIcon,
    ShieldCheckIcon,
    AlertTriangleIcon,
    RefreshCwIcon,
    XCircleIcon,
    PrinterIcon
} from 'lucide-vue-next';

const props = defineProps({
    note: Object,
});

const collectionForm = useForm({
    amount: props.note.remaining_amount,
    collection_date: new Date().toISOString().split('T')[0],
    payment_method: 'bank_transfer',
    reference_number: '',
    notes: '',
});

const renewForm = useForm({
    due_date: '',
});

const showCollectionModal = ref(false);
const showRenewModal = ref(false);

const recordCollection = () => {
    collectionForm.post(route('finance.promissory-collections.store', props.note.id), {
        onSuccess: () => {
            showCollectionModal.value = false;
            collectionForm.reset();
        }
    });
};

const renewNote = () => {
    renewForm.post(route('finance.promissory-notes.renew', props.note.id), {
        onSuccess: () => {
            showRenewModal.value = false;
        }
    });
};

const cancelNote = () => {
    if (confirm('Are you sure you want to cancel this promissory note?')) {
        router.post(route('finance.promissory-notes.cancel', props.note.id));
    }
};

const reverseCollection = (id) => {
    if (confirm('Reverse this collection? This will create a reversal transaction.')) {
        router.post(route('finance.promissory-collections.reverse', id));
    }
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-SA', { style: 'currency', currency: 'SAR' }).format(amount);
};

const getStatusColor = (status) => {
    switch (status) {
        case 'collected': return 'bg-green-100 text-green-800 border-green-200';
        case 'pending': return 'bg-amber-100 text-amber-800 border-amber-200';
        case 'partially_collected': return 'bg-blue-100 text-blue-800 border-blue-200';
        case 'defaulted': return 'bg-red-100 text-red-800 border-red-200';
        default: return 'bg-gray-100 text-gray-800 border-gray-200';
    }
};

</script>

<template>
    <Head :title="'Note ' + note.promissory_number" />

    <Layout>
        <template #header>
            <div class="flex justify-between items-center">
                <div class="flex items-center">
                    <button @click="router.get(route('finance.promissory-notes.index'))" class="mr-4 text-gray-500 hover:text-gray-700">
                        <ArrowLeftIcon class="w-6 h-6" />
                    </button>
                    <div>
                        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                            {{ note.promissory_number }}
                        </h2>
                        <div class="flex items-center space-x-3 mt-1">
                            <span :class="['px-2 py-0.5 rounded text-[10px] font-bold border uppercase', getStatusColor(note.status)]">
                                {{ note.status }}
                            </span>
                            <span v-if="note.is_overdue" class="text-[10px] font-bold text-red-600 uppercase flex items-center">
                                <AlertTriangleIcon class="w-3 h-3 mr-1" />
                                Overdue by {{ note.overdue_days }} days
                            </span>
                        </div>
                    </div>
                </div>

                <div class="flex space-x-2">
                    <button class="p-2 text-gray-400 hover:text-indigo-600 bg-white border border-gray-200 rounded-lg">
                        <PrinterIcon class="w-5 h-5" />
                    </button>
                    <button 
                        v-if="note.status !== 'collected' && note.status !== 'cancelled'"
                        @click="showRenewModal = true"
                        class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-bold text-gray-700 hover:bg-gray-50"
                    >
                        <RefreshCwIcon class="w-4 h-4 inline mr-1" />
                        Renew
                    </button>
                    <button 
                        v-if="note.status === 'pending' && note.collected_amount <= 0"
                        @click="cancelNote"
                        class="px-4 py-2 bg-white border border-red-200 rounded-lg text-sm font-bold text-red-600 hover:bg-red-50"
                    >
                        <XCircleIcon class="w-4 h-4 inline mr-1" />
                        Cancel
                    </button>
                    <button 
                        v-if="note.remaining_amount > 0"
                        @click="showCollectionModal = true"
                        class="px-4 py-2 bg-indigo-600 rounded-lg text-sm font-bold text-white hover:bg-indigo-700 flex items-center"
                    >
                        <BanknoteIcon class="w-4 h-4 mr-2" />
                        Record Collection
                    </button>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Top Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
                        <p class="text-xs font-bold text-gray-400 uppercase mb-3">Balance Progress</p>
                        <div class="flex justify-between items-end mb-2">
                            <p class="text-2xl font-black text-gray-900">{{ formatCurrency(note.remaining_amount) }}</p>
                            <p class="text-xs text-gray-500">Remaining of {{ formatCurrency(note.amount) }}</p>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-2">
                            <div class="bg-indigo-600 h-2 rounded-full" :style="{ width: (note.collected_amount / note.amount * 100) + '%' }"></div>
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
                        <p class="text-xs font-bold text-gray-400 uppercase mb-3">Dates</p>
                        <div class="flex justify-between">
                            <div>
                                <p class="text-[10px] text-gray-400 font-bold uppercase">Issued</p>
                                <p class="text-sm font-bold">{{ note.issue_date }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-[10px] text-gray-400 font-bold uppercase">Due</p>
                                <p :class="['text-sm font-bold', note.is_overdue ? 'text-red-600' : '']">{{ note.due_date }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
                        <p class="text-xs font-bold text-gray-400 uppercase mb-3">Entity</p>
                        <div class="flex items-center">
                            <div class="p-2 bg-indigo-50 rounded-lg mr-3">
                                <BuildingIcon v-if="note.company" class="w-5 h-5 text-indigo-600" />
                                <UserIcon v-else class="w-5 h-5 text-indigo-600" />
                            </div>
                            <div>
                                <p class="text-sm font-bold">{{ note.company?.name || note.guest?.full_name }}</p>
                                <p class="text-[10px] text-gray-400 font-bold uppercase">{{ note.company ? 'Corporate' : 'Individual' }} Account</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Details & Signatory -->
                    <div class="lg:col-span-1 space-y-6">
                        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
                            <h3 class="text-sm font-bold text-gray-900 uppercase mb-6 flex items-center border-b pb-2">
                                <ShieldCheckIcon class="w-4 h-4 mr-2 text-indigo-500" />
                                Legal & Signatory
                            </h3>
                            <div class="space-y-4">
                                <div>
                                    <p class="text-[10px] text-gray-400 font-bold uppercase">Signatory Name</p>
                                    <p class="text-sm font-bold text-gray-900">{{ note.signatory_name }}</p>
                                </div>
                                <div>
                                    <p class="text-[10px] text-gray-400 font-bold uppercase">ID / Iqama</p>
                                    <p class="text-sm font-bold text-gray-900">{{ note.signatory_id_number }}</p>
                                </div>
                                <div v-if="note.guarantor_name">
                                    <p class="text-[10px] text-gray-400 font-bold uppercase">Guarantor</p>
                                    <p class="text-sm font-bold text-emerald-600">{{ note.guarantor_name }}</p>
                                </div>
                            </div>
                            <div class="mt-6 pt-6 border-t">
                                <p class="text-[10px] text-gray-400 font-bold uppercase mb-2">Notes</p>
                                <p class="text-xs text-gray-600 italic">{{ note.notes || 'No notes provided.' }}</p>
                            </div>
                        </div>

                        <!-- Activity Log -->
                        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
                            <h3 class="text-sm font-bold text-gray-900 uppercase mb-6 flex items-center border-b pb-2">
                                <HistoryIcon class="w-4 h-4 mr-2 text-indigo-500" />
                                Activity Timeline
                            </h3>
                            <div class="space-y-6 relative before:absolute before:inset-y-0 before:left-3 before:w-0.5 before:bg-gray-100">
                                <div v-for="log in note.logs" :key="log.id" class="relative pl-8">
                                    <div class="absolute left-0 top-1 w-6 h-6 bg-white border-2 border-indigo-200 rounded-full flex items-center justify-center z-10">
                                        <div class="w-2 h-2 bg-indigo-500 rounded-full"></div>
                                    </div>
                                    <p class="text-xs font-bold text-gray-900 capitalize">{{ log.action.replace('_', ' ') }}</p>
                                    <p class="text-[10px] text-gray-500 mt-1">{{ log.description }}</p>
                                    <p class="text-[9px] text-gray-400 mt-1 uppercase">{{ log.created_at }} • {{ log.performer?.name }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Collections History -->
                    <div class="lg:col-span-2">
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                            <div class="px-6 py-4 border-b bg-gray-50 flex justify-between items-center">
                                <h3 class="text-sm font-bold text-gray-900 uppercase">Collection History</h3>
                                <span class="text-xs font-bold text-indigo-600">{{ note.collections.length }} Records</span>
                            </div>
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Date</th>
                                        <th class="px-6 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Amount</th>
                                        <th class="px-6 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Method</th>
                                        <th class="px-6 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-right text-[10px] font-bold text-gray-400 uppercase tracking-wider">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="col in note.collections" :key="col.id" :class="{'opacity-50 grayscale bg-gray-50': col.status === 'reversed'}">
                                        <td class="px-6 py-4 whitespace-nowrap text-xs font-bold text-gray-900">{{ col.collection_date }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-xs font-black text-gray-900">{{ formatCurrency(col.amount) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500 capitalize">{{ col.payment_method }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span :class="['px-2 py-0.5 rounded text-[9px] font-bold border uppercase', col.status === 'reversed' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800']">
                                                {{ col.status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right">
                                            <button 
                                                v-if="col.status !== 'reversed'"
                                                @click="reverseCollection(col.id)"
                                                class="text-red-600 hover:text-red-900 text-xs font-bold uppercase"
                                            >
                                                Reverse
                                            </button>
                                        </td>
                                    </tr>
                                    <tr v-if="note.collections.length === 0">
                                        <td colspan="5" class="px-6 py-12 text-center text-gray-400">
                                            <HistoryIcon class="w-8 h-8 mx-auto mb-2 opacity-20" />
                                            <p class="text-xs font-bold uppercase tracking-widest">No collection records yet</p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Collection Modal -->
        <div v-if="showCollectionModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900 bg-opacity-50 backdrop-blur-sm">
            <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full overflow-hidden">
                <div class="px-8 py-6 bg-indigo-600 text-white flex justify-between items-center">
                    <h3 class="text-xl font-bold flex items-center">
                        <BanknoteIcon class="w-6 h-6 mr-2" />
                        Record Collection
                    </h3>
                    <button @click="showCollectionModal = false" class="text-indigo-200 hover:text-white">
                        <XCircleIcon class="w-6 h-6" />
                    </button>
                </div>
                <form @submit.prevent="recordCollection" class="p-8 space-y-6">
                    <div class="bg-indigo-50 p-4 rounded-lg flex justify-between items-center mb-6">
                        <span class="text-xs font-bold text-indigo-400 uppercase">Max Collection</span>
                        <span class="text-lg font-black text-indigo-700">{{ formatCurrency(note.remaining_amount) }}</span>
                    </div>

                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Amount to Collect</label>
                            <input v-model="collectionForm.amount" type="number" step="0.01" :max="note.remaining_amount" required class="block w-full border-gray-300 rounded-lg font-black">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Collection Date</label>
                            <input v-model="collectionForm.collection_date" type="date" required class="block w-full border-gray-300 rounded-lg">
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Payment Method</label>
                        <select v-model="collectionForm.payment_method" required class="block w-full border-gray-300 rounded-lg">
                            <option value="cash">Cash</option>
                            <option value="card">Credit Card</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="cheque">Cheque</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Reference #</label>
                        <input v-model="collectionForm.reference_number" type="text" placeholder="Transaction/Cheque Number" class="block w-full border-gray-300 rounded-lg">
                    </div>

                    <div class="flex space-x-3 pt-4">
                        <button type="button" @click="showCollectionModal = false" class="flex-1 px-4 py-3 border border-gray-200 rounded-xl text-sm font-bold text-gray-600">Cancel</button>
                        <button type="submit" :disabled="collectionForm.processing" class="flex-2 px-8 py-3 bg-indigo-600 text-white rounded-xl text-sm font-bold shadow-lg hover:bg-indigo-700 disabled:opacity-50">
                            Confirm Payment
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Renew Modal -->
        <div v-if="showRenewModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900 bg-opacity-50 backdrop-blur-sm">
            <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden">
                <div class="px-8 py-6 border-b flex justify-between items-center">
                    <h3 class="text-lg font-bold uppercase tracking-tight">Renew / Extend Note</h3>
                    <button @click="showRenewModal = false" class="text-gray-400 hover:text-gray-600">
                        <XCircleIcon class="w-6 h-6" />
                    </button>
                </div>
                <form @submit.prevent="renewNote" class="p-8 space-y-6">
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">New Due Date</label>
                        <input v-model="renewForm.due_date" type="date" required class="block w-full border-gray-300 rounded-lg">
                        <p class="mt-2 text-[10px] text-amber-600 font-medium">Extension will reset overdue status and log the renewal history.</p>
                    </div>
                    <div class="flex space-x-3">
                        <button type="button" @click="showRenewModal = false" class="flex-1 px-4 py-2 border rounded-lg text-xs font-bold">Cancel</button>
                        <button type="submit" :disabled="renewForm.processing" class="flex-1 px-4 py-2 bg-indigo-600 text-white rounded-lg text-xs font-bold hover:bg-indigo-700">Extend Note</button>
                    </div>
                </form>
            </div>
        </div>
    </Layout>
</template>
