<script setup>
import { ref } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import Layout from '@/Layouts/AuthenticatedLayout.vue';
import { 
    ArrowLeftIcon, 
    CheckCircle2Icon, 
    XCircleIcon, 
    ClockIcon,
    ArrowRightLeftIcon,
    ArrowRightIcon,
    AlertTriangleIcon,
    FileTextIcon,
    UserIcon,
    BuildingIcon
} from 'lucide-vue-next';

const props = defineProps({
    transfer: Object,
});

const rejectForm = useForm({
    reason: '',
});

const showRejectModal = ref(false);

const approve = () => {
    if (confirm('Are you sure you want to approve this transfer? This will create a credit note on the source invoice and charges on the destination.')) {
        router.post(route('finance.invoice-transfers.approve', props.transfer.id));
    }
};

const reject = () => {
    rejectForm.post(route('finance.invoice-transfers.reject', props.transfer.id), {
        onSuccess: () => {
            showRejectModal.value = false;
            rejectForm.reset();
        }
    });
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-SA', { style: 'currency', currency: 'SAR' }).format(amount);
};

const getStatusColor = (status) => {
    switch (status) {
        case 'completed': return 'bg-green-100 text-green-800 border-green-200';
        case 'pending': return 'bg-amber-100 text-amber-800 border-amber-200';
        case 'approved': return 'bg-blue-100 text-blue-800 border-blue-200';
        case 'rejected': return 'bg-red-100 text-red-800 border-red-200';
        default: return 'bg-gray-100 text-gray-800 border-gray-200';
    }
};

</script>

<template>
    <Head :title="'Transfer ' + transfer.transfer_number" />

    <Layout>
        <template #header>
            <div class="flex justify-between items-center">
                <div class="flex items-center">
                    <button @click="router.get(route('finance.invoice-transfers.index'))" class="mr-4 text-gray-500 hover:text-gray-700">
                        <ArrowLeftIcon class="w-6 h-6" />
                    </button>
                    <div>
                        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                            Transfer: {{ transfer.transfer_number }}
                        </h2>
                        <p class="text-sm text-gray-500">Date: {{ transfer.transfer_date }}</p>
                    </div>
                </div>

                <div v-if="transfer.status === 'pending'" class="flex space-x-2">
                    <button 
                        @click="showRejectModal = true"
                        class="px-4 py-2 bg-white border border-red-300 rounded-md font-semibold text-xs text-red-700 uppercase tracking-widest hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-500 transition ease-in-out duration-150"
                    >
                        Reject
                    </button>
                    <button 
                        @click="approve"
                        class="px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition ease-in-out duration-150"
                    >
                        Approve & Complete
                    </button>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Status & Summary -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 flex items-center">
                        <div :class="['p-3 rounded-full mr-4', getStatusColor(transfer.status)]">
                            <ClockIcon v-if="transfer.status === 'pending'" class="w-6 h-6" />
                            <CheckCircle2Icon v-else-if="transfer.status === 'completed'" class="w-6 h-6" />
                            <XCircleIcon v-else class="w-6 h-6" />
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase font-bold tracking-wider">Status</p>
                            <p class="text-lg font-bold text-gray-900 capitalize">{{ transfer.status }}</p>
                        </div>
                    </div>
                    
                    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 flex items-center">
                        <div class="p-3 bg-indigo-100 rounded-full mr-4">
                            <FileTextIcon class="w-6 h-6 text-indigo-600" />
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase font-bold tracking-wider">Total Amount</p>
                            <p class="text-lg font-bold text-gray-900">{{ formatCurrency(transfer.total_amount) }}</p>
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 flex items-center">
                        <div class="p-3 bg-emerald-100 rounded-full mr-4">
                            <UserIcon class="w-6 h-6 text-emerald-600" />
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase font-bold tracking-wider">Created By</p>
                            <p class="text-lg font-bold text-gray-900">{{ transfer.creator.name }}</p>
                        </div>
                    </div>
                </div>

                <!-- Transfer Path -->
                <div class="bg-white p-8 rounded-lg shadow-sm border border-gray-200">
                    <div class="flex flex-col md:flex-row items-center justify-between space-y-6 md:space-y-0">
                        <!-- From -->
                        <div class="flex-1 w-full max-w-sm">
                            <div class="bg-gray-50 p-6 rounded-xl border border-gray-200">
                                <p class="text-xs font-bold text-gray-400 uppercase mb-3">From Source</p>
                                <Link :href="route('finance.invoices.show', transfer.from_invoice_id)" class="text-lg font-bold text-indigo-600 hover:underline flex items-center">
                                    {{ transfer.from_invoice.invoice_number }}
                                    <ArrowRightLeftIcon class="w-4 h-4 ml-2" />
                                </Link>
                                <div class="mt-4 space-y-2">
                                    <div class="flex items-center text-sm text-gray-600">
                                        <UserIcon v-if="transfer.from_guest" class="w-4 h-4 mr-2" />
                                        <BuildingIcon v-else class="w-4 h-4 mr-2" />
                                        {{ transfer.from_guest?.full_name || transfer.from_company?.name }}
                                    </div>
                                    <p class="text-xs text-gray-400">Reservation: #{{ transfer.from_reservation_id }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Arrow -->
                        <div class="flex flex-col items-center mx-4">
                            <div class="bg-indigo-600 p-3 rounded-full text-white shadow-lg">
                                <ArrowRightIcon class="w-6 h-6" />
                            </div>
                            <span class="text-xs font-bold text-indigo-600 mt-2 uppercase tracking-tighter">Transferring Charges</span>
                        </div>

                        <!-- To -->
                        <div class="flex-1 w-full max-w-sm">
                            <div class="bg-indigo-50 p-6 rounded-xl border border-indigo-100">
                                <p class="text-xs font-bold text-indigo-400 uppercase mb-3">To Destination</p>
                                <Link v-if="transfer.to_invoice" :href="route('finance.invoices.show', transfer.to_invoice_id)" class="text-lg font-bold text-indigo-700 hover:underline flex items-center">
                                    {{ transfer.to_invoice.invoice_number }}
                                    <ArrowRightLeftIcon class="w-4 h-4 ml-2" />
                                </Link>
                                <p v-else class="text-lg font-bold text-indigo-800 italic">New Invoice/Folio</p>
                                <div class="mt-4 space-y-2">
                                    <div class="flex items-center text-sm text-indigo-900">
                                        <UserIcon v-if="transfer.to_guest" class="w-4 h-4 mr-2" />
                                        <BuildingIcon v-else class="w-4 h-4 mr-2" />
                                        {{ transfer.to_guest?.full_name || transfer.to_company?.name }}
                                    </div>
                                    <p class="text-xs text-indigo-400">Reservation: #{{ transfer.to_reservation_id }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Items & Reason -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                        <div class="px-6 py-4 border-b bg-gray-50">
                            <h3 class="text-lg font-medium text-gray-900">Items Transferred</h3>
                        </div>
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Amount</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="item in transfer.items" :key="item.id">
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900">{{ item.from_item.product_name }}</div>
                                        <div v-if="item.to_item" class="text-xs text-emerald-600">New Item ID: {{ item.to_item.id }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm font-medium text-gray-900">
                                        {{ formatCurrency(item.amount) }}
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot class="bg-gray-50">
                                <tr>
                                    <td class="px-6 py-4 text-sm font-bold text-gray-900">Total</td>
                                    <td class="px-6 py-4 text-right text-sm font-bold text-indigo-600">
                                        {{ formatCurrency(transfer.total_amount) }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="space-y-6">
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Reason for Transfer</h3>
                            <p class="text-sm text-gray-600 bg-gray-50 p-4 rounded-lg italic border-l-4 border-indigo-400">
                                "{{ transfer.reason }}"
                            </p>
                        </div>

                        <div v-if="transfer.status === 'rejected'" class="bg-red-50 p-6 rounded-lg shadow-sm border border-red-200">
                            <h3 class="text-lg font-medium text-red-900 mb-4 flex items-center">
                                <AlertTriangleIcon class="w-5 h-5 mr-2" />
                                Rejection Reason
                            </h3>
                            <p class="text-sm text-red-600">
                                {{ transfer.rejection_reason }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reject Modal -->
        <div v-if="showRejectModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="showRejectModal = false"></div>
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Reject Transfer</h3>
                        <div class="mt-4">
                            <textarea 
                                v-model="rejectForm.reason"
                                rows="3"
                                class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                placeholder="Please provide a reason for rejection..."
                            ></textarea>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button 
                            @click="reject"
                            :disabled="rejectForm.processing || !rejectForm.reason"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm"
                        >
                            Confirm Rejection
                        </button>
                        <button 
                            @click="showRejectModal = false"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                        >
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </Layout>
</template>
