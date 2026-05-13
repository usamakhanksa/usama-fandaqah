<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import Layout from '@/Layouts/AuthenticatedLayout.vue';
import { 
    SearchIcon, 
    BanknoteIcon,
    ArrowRightIcon,
    FilterIcon,
    DownloadIcon,
    AlertCircleIcon
} from 'lucide-vue-next';

const props = defineProps({
    collections: Object,
    filters: Object,
});

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-SA', { style: 'currency', currency: 'SAR' }).format(amount);
};

const getMethodColor = (method) => {
    switch (method) {
        case 'cash': return 'text-emerald-600 bg-emerald-50';
        case 'card': return 'text-blue-600 bg-blue-50';
        case 'bank_transfer': return 'text-indigo-600 bg-indigo-50';
        case 'cheque': return 'text-amber-600 bg-amber-50';
        default: return 'text-gray-600 bg-gray-50';
    }
};

</script>

<template>
    <Head title="Promissory Collections" />

    <Layout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Promissory Note Collections
                </h2>
                <button class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg font-bold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50 shadow-sm">
                    <DownloadIcon class="w-4 h-4 mr-2" />
                    Export CSV
                </button>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-200">
                    <!-- Filters -->
                    <div class="p-6 border-b border-gray-200 bg-gray-50 flex flex-wrap gap-4 items-center justify-between">
                        <div class="flex gap-4">
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                                    <SearchIcon class="w-4 h-4" />
                                </span>
                                <input 
                                    type="text" 
                                    placeholder="Search PN number..." 
                                    class="block w-64 pl-10 pr-3 py-2 border border-gray-300 rounded-lg leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                >
                            </div>
                            <select class="block w-40 pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-lg">
                                <option value="">All Methods</option>
                                <option value="cash">Cash</option>
                                <option value="card">Card</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="cheque">Cheque</option>
                            </select>
                        </div>
                    </div>

                    <!-- Table -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Promissory Note</th>
                                    <th class="px-6 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Amount</th>
                                    <th class="px-6 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Method</th>
                                    <th class="px-6 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Collected By</th>
                                    <th class="px-6 py-3 text-right text-[10px] font-bold text-gray-400 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="col in collections.data" :key="col.id" class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ col.collection_date }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <Link :href="route('finance.promissory-notes.show', col.promissory_note_id)" class="inline-flex items-center text-sm font-bold text-indigo-600 hover:underline">
                                            {{ col.promissory_note.promissory_number }}
                                            <ArrowRightIcon class="w-3 h-3 ml-1" />
                                        </Link>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-black text-gray-900">
                                        {{ formatCurrency(col.amount) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="['px-2 py-0.5 rounded text-[10px] font-bold uppercase border', getMethodColor(col.payment_method)]">
                                            {{ col.payment_method.replace('_', ' ') }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-600">
                                        {{ col.collector.name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <span :class="['px-2 py-0.5 rounded text-[9px] font-black uppercase border', col.status === 'reversed' ? 'bg-red-50 text-red-700 border-red-100' : 'bg-emerald-50 text-emerald-700 border-emerald-100']">
                                            {{ col.status }}
                                        </span>
                                    </td>
                                </tr>
                                <tr v-if="collections.data.length === 0">
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                        <AlertCircleIcon class="w-12 h-12 text-gray-200 mx-auto mb-2" />
                                        <p class="text-sm">No collections found.</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </Layout>
</template>
