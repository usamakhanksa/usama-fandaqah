<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import Layout from '@/Layouts/AuthenticatedLayout.vue';
import { 
    ArrowLeftIcon, 
    PrinterIcon, 
    DownloadIcon, 
    SendIcon, 
    AlertTriangleIcon,
    CheckCircle2Icon,
    FileTextIcon,
    HistoryIcon,
    ExternalLinkIcon,
    XCircleIcon
} from 'lucide-vue-next';

const props = defineProps({
    creditNote: Object,
});

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-SA', { style: 'currency', currency: 'SAR' }).format(amount);
};

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

const submitToZatca = () => {
    if (confirm('Are you sure you want to report this credit note to ZATCA?')) {
        router.post(route('finance.credit-notes.zatca_submit', props.creditNote.id));
    }
};

const cancelCreditNote = () => {
    if (confirm('Are you sure you want to cancel this credit note? This will also reverse the financial transaction.')) {
        router.post(route('finance.credit-notes.cancel', props.creditNote.id));
    }
};

</script>

<template>
    <Head :title="'Credit Note ' + creditNote.credit_note_number" />

    <Layout>
        <template #header>
            <div class="flex justify-between items-center">
                <div class="flex items-center">
                    <button @click="router.get(route('finance.credit-notes.index'))" class="mr-4 text-gray-500 hover:text-gray-700">
                        <ArrowLeftIcon class="w-6 h-6" />
                    </button>
                    <div>
                        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                            Credit Note: {{ creditNote.credit_note_number }}
                        </h2>
                        <p class="text-sm text-gray-500">Linked to Invoice: {{ creditNote.invoice.invoice_number }}</p>
                    </div>
                </div>
                
                <div class="flex space-x-2">
                    <button 
                        @click="cancelCreditNote"
                        v-if="creditNote.status === 'confirmed' && !creditNote.is_zatca_reported"
                        class="inline-flex items-center px-4 py-2 bg-white border border-red-300 rounded-md font-semibold text-xs text-red-700 uppercase tracking-widest hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-500 transition ease-in-out duration-150"
                    >
                        <XCircleIcon class="w-4 h-4 mr-2" />
                        Cancel
                    </button>
                    
                    <button 
                        class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition ease-in-out duration-150"
                    >
                        <PrinterIcon class="w-4 h-4 mr-2" />
                        Print
                    </button>
                    
                    <button 
                        @click="submitToZatca"
                        v-if="!creditNote.is_zatca_reported && creditNote.status === 'confirmed'"
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    >
                        <SendIcon class="w-4 h-4 mr-2" />
                        Submit to ZATCA
                    </button>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Status Banner -->
                <div v-if="creditNote.zatca_status === 'rejected'" class="bg-red-50 border-l-4 border-red-400 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <AlertTriangleIcon class="h-5 w-5 text-red-400" />
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-700">
                                This credit note was rejected by ZATCA. Reason: {{ creditNote.zatca_response?.validationResults?.errors[0]?.message || 'Unknown error' }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Left Column: Details -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Items Table -->
                        <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden border border-gray-200">
                            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                                <h3 class="text-lg font-medium text-gray-900">Items</h3>
                                <Link :href="route('finance.invoices.show', creditNote.invoice_id)" class="text-sm text-indigo-600 hover:underline inline-flex items-center">
                                    Original Invoice: {{ creditNote.invoice.invoice_number }}
                                    <ExternalLinkIcon class="w-3 h-3 ml-1" />
                                </Link>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Price</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Qty</th>
                                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr v-for="item in creditNote.items" :key="item.id">
                                            <td class="px-6 py-4">
                                                <div class="text-sm font-medium text-gray-900">{{ item.product_name }}</div>
                                                <div v-if="item.description" class="text-xs text-gray-500">{{ item.description }}</div>
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-500">
                                                {{ formatCurrency(item.unit_price) }}
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-500">
                                                {{ item.quantity }}
                                            </td>
                                            <td class="px-6 py-4 text-right text-sm font-medium text-gray-900">
                                                {{ formatCurrency(item.sub_total) }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            
                            <div class="px-6 py-6 bg-gray-50 flex justify-end">
                                <div class="w-64 space-y-3">
                                    <div class="flex justify-between text-sm text-gray-600">
                                        <span>Subtotal</span>
                                        <span>{{ formatCurrency(creditNote.sub_total) }}</span>
                                    </div>
                                    <div v-if="creditNote.discount_amount > 0" class="flex justify-between text-sm text-rose-600">
                                        <span>Discount</span>
                                        <span>-{{ formatCurrency(creditNote.discount_amount) }}</span>
                                    </div>
                                    <div class="flex justify-between text-sm text-gray-600">
                                        <span>Taxable Amount</span>
                                        <span>{{ formatCurrency(creditNote.taxable_amount) }}</span>
                                    </div>
                                    <div class="flex justify-between text-sm text-gray-600">
                                        <span>VAT ({{ creditNote.vat_percentage }}%)</span>
                                        <span>{{ formatCurrency(creditNote.vat_amount) }}</span>
                                    </div>
                                    <div class="pt-3 border-t border-gray-300 flex justify-between text-lg font-bold text-gray-900">
                                        <span>Credit Total</span>
                                        <span>{{ formatCurrency(creditNote.total_amount) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Reason & Notes -->
                        <div class="bg-white p-6 shadow-sm sm:rounded-lg border border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Reason & Notes</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Credit Reason</p>
                                    <p class="mt-1 text-sm text-gray-900 capitalize">{{ creditNote.reason.replace('_', ' ') }}</p>
                                    <p v-if="creditNote.reason_description" class="mt-1 text-sm text-gray-600">{{ creditNote.reason_description }}</p>
                                </div>
                                <div>
                                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Additional Notes</p>
                                    <p class="mt-1 text-sm text-gray-900">{{ creditNote.notes || 'None' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Meta & ZATCA -->
                    <div class="space-y-6">
                        <!-- Summary Info -->
                        <div class="bg-white p-6 shadow-sm sm:rounded-lg border border-gray-200">
                            <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-4 border-b border-gray-100 pb-2">Credit Note Info</h3>
                            <dl class="space-y-4">
                                <div>
                                    <dt class="text-xs text-gray-500">Status</dt>
                                    <dd class="mt-1">
                                        <span :class="['px-2.5 py-0.5 rounded-full text-xs font-medium border', getStatusColor(creditNote.status)]">
                                            {{ creditNote.status }}
                                        </span>
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-xs text-gray-500">Date</dt>
                                    <dd class="mt-1 text-sm font-medium text-gray-900">{{ creditNote.credit_note_date }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs text-gray-500">Guest / Company</dt>
                                    <dd class="mt-1 text-sm font-medium text-gray-900">
                                        {{ creditNote.company ? creditNote.company.name : (creditNote.guest ? creditNote.guest.full_name : 'N/A') }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-xs text-gray-500">Created By</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ creditNote.creator?.name || 'System' }}</dd>
                                </div>
                            </dl>
                        </div>

                        <!-- ZATCA Status -->
                        <div class="bg-white p-6 shadow-sm sm:rounded-lg border border-gray-200">
                            <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-4 border-b border-gray-100 pb-2">ZATCA Reporting</h3>
                            <dl class="space-y-4">
                                <div>
                                    <dt class="text-xs text-gray-500">ZATCA Status</dt>
                                    <dd class="mt-1">
                                        <span :class="['px-2.5 py-0.5 rounded-full text-xs font-medium', getZatcaStatusColor(creditNote.zatca_status)]">
                                            {{ creditNote.zatca_status.replace('_', ' ') }}
                                        </span>
                                    </dd>
                                </div>
                                <div v-if="creditNote.is_zatca_reported">
                                    <dt class="text-xs text-gray-500">Reported At</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ creditNote.zatca_submitted_at }}</dd>
                                </div>
                                <div v-if="creditNote.zatca_xml" class="pt-2">
                                    <a :href="route('finance.credit-notes.zatca_download', creditNote.id)" class="inline-flex items-center text-xs font-medium text-indigo-600 hover:text-indigo-900 underline">
                                        <DownloadIcon class="w-3 h-3 mr-1" />
                                        Download ZATCA XML
                                    </a>
                                </div>
                                <div v-if="creditNote.zatca_qr_code" class="mt-4 flex justify-center p-2 bg-white border border-gray-200 rounded">
                                    <div class="text-center">
                                        <p class="text-[10px] text-gray-400 mb-1">ZATCA QR Code</p>
                                        <!-- Placeholder for QR - usually displayed as image or component -->
                                        <div class="w-32 h-32 bg-gray-100 flex items-center justify-center text-gray-300">
                                            <CheckCircle2Icon class="w-12 h-12" />
                                        </div>
                                    </div>
                                </div>
                            </dl>
                        </div>

                        <!-- Financial Impact -->
                        <div v-if="creditNote.transaction" class="bg-white p-6 shadow-sm sm:rounded-lg border border-gray-200">
                            <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-4 border-b border-gray-100 pb-2">Financial Impact</h3>
                            <div class="flex items-start">
                                <HistoryIcon class="w-5 h-5 text-gray-400 mr-2 mt-0.5" />
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Reversal Transaction Created</p>
                                    <p class="text-xs text-gray-500">Number: {{ creditNote.transaction.number }}</p>
                                    <p class="text-xs font-bold text-red-600 mt-1">{{ formatCurrency(creditNote.transaction.amount) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Layout>
</template>
