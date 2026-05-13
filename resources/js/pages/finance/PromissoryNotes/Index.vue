<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import Layout from '@/Layouts/AuthenticatedLayout.vue';
import { 
    PlusIcon, 
    SearchIcon, 
    FileTextIcon, 
    AlertCircleIcon,
    ChevronRightIcon,
    CalendarIcon,
    TrendingUpIcon,
    AlertTriangleIcon,
    CheckCircle2Icon
} from 'lucide-vue-next';

const props = defineProps({
    notes: Object,
    aging: Object,
    stats: Object,
    filters: Object,
});

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
    <Head title="Promissory Notes" />

    <Layout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Promissory Notes & AR Aging
                </h2>
                <Link 
                    :href="route('finance.promissory-notes.create')"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                >
                    <PlusIcon class="w-4 h-4 mr-2" />
                    New Promissory Note
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Summary Stats -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Total Outstanding</p>
                        <p class="text-2xl font-black text-gray-900">{{ formatCurrency(stats.total_outstanding) }}</p>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                        <p class="text-xs font-bold text-red-400 uppercase tracking-wider mb-1">Overdue Count</p>
                        <p class="text-2xl font-black text-red-600">{{ stats.overdue_count }}</p>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                        <p class="text-xs font-bold text-emerald-400 uppercase tracking-wider mb-1">Collected MTD</p>
                        <p class="text-2xl font-black text-emerald-600">{{ formatCurrency(stats.collected_this_month) }}</p>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                        <p class="text-xs font-bold text-indigo-400 uppercase tracking-wider mb-1">Collection Efficiency</p>
                        <p class="text-2xl font-black text-indigo-600">92.4%</p>
                    </div>
                </div>

                <!-- Aging Buckets -->
                <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                    <h3 class="text-sm font-bold text-gray-900 uppercase mb-4 flex items-center">
                        <TrendingUpIcon class="w-4 h-4 mr-2 text-indigo-500" />
                        AR Aging Report (SAR)
                    </h3>
                    <div class="grid grid-cols-5 gap-4">
                        <div v-for="(val, key) in aging" :key="key" class="p-4 rounded-lg bg-gray-50 border border-gray-100 text-center">
                            <p class="text-[10px] text-gray-400 uppercase font-bold">{{ key.replace('_', '-') }} Days</p>
                            <p class="text-sm font-bold text-gray-700 mt-1">{{ formatCurrency(val) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Table Container -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                    <div class="p-6 border-b border-gray-200 bg-gray-50 flex flex-wrap gap-4 items-center justify-between">
                        <div class="flex gap-4">
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                                    <SearchIcon class="w-4 h-4" />
                                </span>
                                <input 
                                    type="text" 
                                    placeholder="Search notes or signatories..." 
                                    class="block w-80 pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                >
                            </div>
                            <select class="block w-40 pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                <option value="">All Statuses</option>
                                <option value="pending">Pending</option>
                                <option value="partially_collected">Partial</option>
                                <option value="collected">Collected</option>
                                <option value="defaulted">Defaulted</option>
                            </select>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Note Info</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Beneficiary</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Due Date</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="note in notes.data" :key="note.id" :class="{'bg-red-50': note.is_overdue && note.status !== 'collected'}">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="p-2 bg-indigo-50 rounded-lg mr-3">
                                                <FileTextIcon class="w-4 h-4 text-indigo-600" />
                                            </div>
                                            <div>
                                                <div class="text-sm font-bold text-gray-900">{{ note.promissory_number }}</div>
                                                <div class="text-[10px] text-gray-500">Signatory: {{ note.signatory_name }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900 font-medium">{{ note.company?.name || note.guest?.full_name || 'N/A' }}</div>
                                        <div class="text-xs text-gray-400">{{ note.company ? 'Corporate' : 'Individual' }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-bold text-gray-900">{{ formatCurrency(note.amount) }}</div>
                                        <div class="text-xs text-indigo-600">Rem: {{ formatCurrency(note.remaining_amount) }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="['px-2.5 py-0.5 rounded-full text-[10px] font-bold border uppercase tracking-wider inline-flex items-center', getStatusColor(note.status)]">
                                            <AlertTriangleIcon v-if="note.is_overdue && note.status !== 'collected'" class="w-3 h-3 mr-1" />
                                            {{ note.status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div :class="['text-sm font-medium', note.is_overdue ? 'text-red-600' : 'text-gray-900']">
                                            {{ note.due_date }}
                                        </div>
                                        <div v-if="note.is_overdue" class="text-[10px] text-red-400 font-bold uppercase">{{ note.overdue_days }} Days Overdue</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <Link :href="route('finance.promissory-notes.show', note.id)" class="text-indigo-600 hover:text-indigo-900 flex items-center justify-end">
                                            Manage
                                            <ChevronRightIcon class="w-4 h-4 ml-1" />
                                        </Link>
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
