<script setup>
import { ref, computed } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import Layout from '@/Layouts/AuthenticatedLayout.vue';
import { 
    ArrowLeftIcon, 
    SaveIcon, 
    SearchIcon,
    AlertCircleIcon,
    ArrowRightIcon,
    UserIcon,
    BuildingIcon
} from 'lucide-vue-next';

const props = defineProps({
    fromInvoice: Object,
    invoices: Array,
});

const form = useForm({
    from_invoice_id: props.fromInvoice?.id || '',
    to_invoice_id: '',
    reason: '',
    transfer_type: 'item_level',
    items: props.fromInvoice?.items.map(item => ({
        id: item.id,
        product_name: item.product_name,
        quantity: item.quantity,
        max_quantity: item.quantity,
        unit_price: item.unit_price,
        total_amount: item.total_amount,
        selected: true
    })) || []
});

const selectInvoice = (invoiceId) => {
    router.get(route('finance.invoice-transfers.create'), { from_invoice_id: invoiceId }, { preserveState: true });
};

const submit = () => {
    form.post(route('finance.invoice-transfers.store'));
};

const totals = computed(() => {
    let total = 0;
    form.items.forEach(item => {
        if (item.selected) {
            total += (item.quantity * item.unit_price) * 1.15; // Rough VAT estimate for preview
        }
    });
    return total;
});

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-SA', { style: 'currency', currency: 'SAR' }).format(amount);
};

</script>

<template>
    <Head title="New Invoice Transfer" />

    <Layout>
        <template #header>
            <div class="flex items-center">
                <button @click="router.get(route('finance.invoice-transfers.index'))" class="mr-4 text-gray-500 hover:text-gray-700">
                    <ArrowLeftIcon class="w-6 h-6" />
                </button>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    New Invoice Transfer
                </h2>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Step 1: Source Invoice -->
                    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                            <div class="bg-indigo-100 p-2 rounded-full mr-3">
                                <SearchIcon class="w-4 h-4 text-indigo-600" />
                            </div>
                            Step 1: Select Source Invoice
                        </h3>
                        
                        <div v-if="!fromInvoice" class="space-y-4">
                            <select 
                                @change="e => selectInvoice(e.target.value)"
                                class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            >
                                <option value="">Select invoice to transfer from...</option>
                                <option v-for="inv in invoices" :key="inv.id" :value="inv.id">
                                    {{ inv.invoice_number }} - {{ inv.guest?.full_name || inv.company?.name }} ({{ formatCurrency(inv.grand_total) }})
                                </option>
                            </select>
                        </div>

                        <div v-else class="flex justify-between items-center bg-indigo-50 p-4 rounded-md border border-indigo-100">
                            <div>
                                <p class="text-sm font-bold text-indigo-900">Source: {{ fromInvoice.invoice_number }}</p>
                                <div class="flex items-center mt-1 text-xs text-indigo-700">
                                    <UserIcon v-if="fromInvoice.guest" class="w-3 h-3 mr-1" />
                                    <BuildingIcon v-else class="w-3 h-3 mr-1" />
                                    {{ fromInvoice.guest?.full_name || fromInvoice.company?.name }}
                                </div>
                            </div>
                            <button type="button" @click="router.get(route('finance.invoice-transfers.create'))" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 uppercase">
                                Change
                            </button>
                        </div>
                    </div>

                    <div v-if="fromInvoice" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Step 2: Target Selection -->
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                                <div class="bg-emerald-100 p-2 rounded-full mr-3">
                                    <ArrowRightIcon class="w-4 h-4 text-emerald-600" />
                                </div>
                                Step 2: Select Destination
                            </h3>
                            
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Existing Invoice (Optional)</label>
                                    <select 
                                        v-model="form.to_invoice_id"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                    >
                                        <option value="">Create New Invoice</option>
                                        <option v-for="inv in invoices.filter(i => i.id !== fromInvoice.id)" :key="inv.id" :value="inv.id">
                                            {{ inv.invoice_number }} - {{ inv.guest?.full_name || inv.company?.name }}
                                        </option>
                                    </select>
                                    <p class="mt-1 text-xs text-gray-500">Leave blank to create a fresh folio/invoice for this guest.</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Reason for Transfer</label>
                                    <textarea 
                                        v-model="form.reason"
                                        rows="2"
                                        required
                                        placeholder="e.g. Guest requested to move charges to company folio"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                    ></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Step 3: Items -->
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                                <div class="bg-amber-100 p-2 rounded-full mr-3">
                                    <FileTextIcon class="w-4 h-4 text-amber-600" />
                                </div>
                                Step 3: Select Items
                            </h3>
                            
                            <div class="space-y-3 max-h-64 overflow-y-auto pr-2">
                                <div v-for="item in form.items" :key="item.id" class="flex items-center justify-between p-3 border rounded-lg hover:bg-gray-50 transition-colors" :class="{'border-indigo-200 bg-indigo-50': item.selected}">
                                    <div class="flex items-center">
                                        <input type="checkbox" v-model="item.selected" class="rounded text-indigo-600 focus:ring-indigo-500 h-4 w-4">
                                        <div class="ml-3">
                                            <p class="text-sm font-medium text-gray-900">{{ item.product_name }}</p>
                                            <p class="text-xs text-gray-500">{{ formatCurrency(item.unit_price) }} x {{ item.max_quantity }}</p>
                                        </div>
                                    </div>
                                    <div v-if="item.selected" class="flex items-center">
                                        <input 
                                            v-model.number="item.quantity"
                                            type="number" 
                                            :max="item.max_quantity"
                                            min="0.01"
                                            step="0.01"
                                            class="w-16 border-gray-300 rounded-md text-xs px-2 py-1 focus:ring-indigo-500 focus:border-indigo-500"
                                        >
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4 pt-4 border-t flex justify-between items-center">
                                <span class="text-sm font-medium text-gray-500">Total to Transfer:</span>
                                <span class="text-lg font-bold text-gray-900">{{ formatCurrency(totals) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div v-if="fromInvoice" class="flex justify-end space-x-3">
                        <button 
                            type="button" 
                            @click="router.get(route('finance.invoice-transfers.index'))"
                            class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
                        >
                            Cancel
                        </button>
                        <button 
                            type="submit" 
                            :disabled="form.processing || totals <= 0 || !form.reason"
                            class="inline-flex items-center px-6 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 disabled:opacity-50 transition ease-in-out duration-150"
                        >
                            <SaveIcon class="w-4 h-4 mr-2" />
                            Submit Transfer Request
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </Layout>
</template>
