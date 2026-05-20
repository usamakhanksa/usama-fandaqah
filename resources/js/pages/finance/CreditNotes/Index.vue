<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import Layout from '@/Layouts/AuthenticatedLayout.vue';
import { 
    SearchIcon, 
    FilterIcon, 
    PlusIcon, 
    MoreHorizontalIcon,
    FileTextIcon,
    CheckCircleIcon,
    AlertCircleIcon,
    DownloadIcon,
    ExternalLinkIcon
} from 'lucide-vue-next';
import Pagination from '@/Components/Pagination.vue';
import { debounce } from 'lodash';

const props = defineProps({
    creditNotes: {
        type: Object,
        default: () => ({ data: [] })
    },
    filters: {
        type: Object,
        default: () => ({})
    },
});

const search = ref(props.filters?.search || '');
const status = ref(props.filters?.status || '');

watch([search, status], debounce(([s, st]) => {
    router.get(route('finance.credit-notes.index'), { search: s, status: st }, { preserveState: true, replace: true });
}, 300));

const getStatusColor = (s) => {
    switch (s) {
        case 'confirmed': return 'bg-green-100 text-green-800 border-green-200';
        case 'draft': return 'bg-amber-100 text-amber-800 border-amber-200';
        case 'cancelled': return 'bg-red-100 text-red-800 border-red-200';
        default: return 'bg-gray-100 text-gray-800 border-gray-200';
    }
};

const getZatcaStatusColor = (s) => {
    switch (s) {
        case 'reported':
        case 'accepted': return 'bg-emerald-100 text-emerald-800';
        case 'pending': return 'bg-blue-100 text-blue-800';
        case 'rejected':
        case 'error': return 'bg-rose-100 text-rose-800';
        default: return 'bg-slate-100 text-slate-800';
    }
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-SA', { style: 'currency', currency: 'SAR' }).format(amount);
};

</script>

<template>
    <Head title="Credit Notes" />

    <Layout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Credit Notes
                </h2>
                <Link 
                    :href="route('finance.credit-notes.create')"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                >
                    <PlusIcon class="w-4 h-4 mr-2" />
                    New Credit Note
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Filters -->
                <div class="bg-white p-4 rounded-lg shadow-sm mb-6 flex flex-wrap gap-4 items-center border border-gray-100">
                    <div class="relative flex-grow max-w-md">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <SearchIcon class="h-5 w-5 text-gray-400" />
                        </div>
                        <input 
                            v-model="search"
                            type="text" 
                            class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" 
                            placeholder="Search by CN number or Invoice..."
                        >
                    </div>
                    
                    <select 
                        v-model="status"
                        class="block pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                    >
                        <option value="">All Statuses</option>
                        <option value="draft">Draft</option>
                        <option value="confirmed">Confirmed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>

                <!-- Table -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Number</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Original Invoice</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Guest / Company</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ZATCA</th>
                                    <th scope="col" class="relative px-6 py-3">
                                        <span class="sr-only">Actions</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="note in creditNotes?.data" :key="note.id" class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <Link :href="route('finance.credit-notes.show', note.id)" class="text-sm font-semibold text-indigo-600 hover:text-indigo-900">
                                            {{ note.credit_note_number }}
                                        </Link>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <Link :href="route('finance.invoices.show', note.invoice_id)" class="text-sm text-gray-600 hover:underline inline-flex items-center">
                                            {{ note.invoice?.invoice_number }}
                                            <ExternalLinkIcon class="w-3 h-3 ml-1" />
                                        </Link>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            {{ note.company ? note.company.name : (note.guest ? note.guest.full_name : 'N/A') }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{ note.company ? 'Corporate' : 'Individual' }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ note.credit_note_date }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ formatCurrency(note.total_amount) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <span :class="['px-2.5 py-0.5 rounded-full text-xs font-medium border', getStatusColor(note.status)]">
                                            {{ note.status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <span :class="['px-2.5 py-0.5 rounded-full text-xs font-medium', getZatcaStatusColor(note.zatca_status)]">
                                            {{ note.zatca_status?.replace('_', ' ') }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <Link :href="route('finance.credit-notes.show', note.id)" class="text-indigo-600 hover:text-indigo-900 p-1 rounded-full hover:bg-indigo-50">
                                            <MoreHorizontalIcon class="w-5 h-5" />
                                        </Link>
                                    </td>
                                </tr>
                                <tr v-if="!creditNotes?.data || creditNotes?.data.length === 0">
                                    <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                                        <div class="flex flex-col items-center">
                                            <FileTextIcon class="w-12 h-12 text-gray-300 mb-2" />
                                            <p>No credit notes found.</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <div v-if="creditNotes?.data?.length > 0" class="bg-gray-50 px-6 py-4 border-t border-gray-200">
                        <Pagination :links="creditNotes.links" />
                    </div>
                </div>
            </div>
        </div>
    </Layout>
</template>
