<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import Layout from '@/Layouts/AuthenticatedLayout.vue';
import { 
    PlusIcon, 
    SearchIcon, 
    FileTextIcon, 
    ArrowRightLeftIcon,
    ChevronRightIcon,
    CheckCircle2Icon,
    XCircleIcon,
    ClockIcon
} from 'lucide-vue-next';

const props = defineProps({
    transfers: Object,
    filters: Object,
});

const getStatusColor = (status) => {
    switch (status) {
        case 'completed': return 'bg-green-100 text-green-800 border-green-200';
        case 'pending': return 'bg-amber-100 text-amber-800 border-amber-200';
        case 'approved': return 'bg-blue-100 text-blue-800 border-blue-200';
        case 'rejected': return 'bg-red-100 text-red-800 border-red-200';
        default: return 'bg-gray-100 text-gray-800 border-gray-200';
    }
};

const getStatusIcon = (status) => {
    switch (status) {
        case 'completed': return CheckCircle2Icon;
        case 'pending': return ClockIcon;
        case 'approved': return CheckCircle2Icon;
        case 'rejected': return XCircleIcon;
        default: return ClockIcon;
    }
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-SA', { style: 'currency', currency: 'SAR' }).format(amount);
};

</script>

<template>
    <Head title="Invoice Transfers" />

    <Layout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Invoice Transfers
                </h2>
                <Link 
                    :href="route('finance.invoice-transfers.create')"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                >
                    <PlusIcon class="w-4 h-4 mr-2" />
                    New Transfer
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                    <!-- Filters -->
                    <div class="p-6 border-b border-gray-200 bg-gray-50 flex flex-wrap gap-4 items-center justify-between">
                        <div class="flex gap-4">
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                                    <SearchIcon class="w-4 h-4" />
                                </span>
                                <input 
                                    type="text" 
                                    placeholder="Search transfers..." 
                                    class="block w-64 pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                >
                            </div>
                            <select class="block w-40 pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                <option value="">All Statuses</option>
                                <option value="pending">Pending</option>
                                <option value="completed">Completed</option>
                                <option value="rejected">Rejected</option>
                            </select>
                        </div>
                    </div>

                    <!-- Table -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Number</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Source</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Target</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="transfer in transfers.data" :key="transfer.id" class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="p-2 bg-indigo-50 rounded-lg mr-3">
                                                <ArrowRightLeftIcon class="w-4 h-4 text-indigo-600" />
                                            </div>
                                            <span class="text-sm font-bold text-gray-900">{{ transfer.transfer_number }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900">{{ transfer.from_invoice.invoice_number }}</div>
                                        <div class="text-xs text-gray-500">{{ transfer.from_guest?.full_name || transfer.from_company?.name }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div v-if="transfer.to_invoice" class="text-sm text-gray-900">{{ transfer.to_invoice.invoice_number }}</div>
                                        <div v-else class="text-sm text-amber-600 italic">New Invoice</div>
                                        <div class="text-xs text-gray-500">{{ transfer.to_guest?.full_name || transfer.to_company?.name }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                        {{ formatCurrency(transfer.total_amount) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="['px-2.5 py-0.5 rounded-full text-xs font-medium border inline-flex items-center', getStatusColor(transfer.status)]">
                                            <component :is="getStatusIcon(transfer.status)" class="w-3 h-3 mr-1" />
                                            {{ transfer.status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ transfer.transfer_date }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <Link :href="route('finance.invoice-transfers.show', transfer.id)" class="text-indigo-600 hover:text-indigo-900">
                                            View Details
                                        </Link>
                                    </td>
                                </tr>
                                <tr v-if="transfers.data.length === 0">
                                    <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                        <div class="flex flex-col items-center">
                                            <FileTextIcon class="w-12 h-12 text-gray-200 mb-2" />
                                            <p>No transfers found.</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="transfers.data.length > 0" class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                        <!-- Pagination logic here -->
                    </div>
                </div>
            </div>
        </div>
    </Layout>
</template>
