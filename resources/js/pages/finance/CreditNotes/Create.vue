<script setup>
import { ref, computed, watch } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import Layout from '@/Layouts/AuthenticatedLayout.vue';
import { 
    ArrowLeftIcon, 
    SaveIcon, 
    SearchIcon,
    AlertCircleIcon,
    InfoIcon,
    CheckIcon
} from 'lucide-vue-next';

const props = defineProps({
    invoice: Object,
    invoices: Array,
    reasons: Array,
});

const form = useForm({
    invoice_id: props.invoice?.id || '',
    reason: 'cancellation',
    reason_description: '',
    notes: '',
    items: props.invoice?.items.map(item => ({
        id: item.id,
        product_name: item.product_name,
        quantity: item.quantity,
        max_quantity: item.quantity,
        unit_price: item.unit_price,
        selected: true
    })) || []
});

const selectedInvoice = ref(props.invoice);

const selectInvoice = (invoice) => {
    router.get(route('finance.credit-notes.create'), { invoice_id: invoice.id }, { preserveState: true });
};

const submit = () => {
    form.post(route('finance.credit-notes.store'));
};

const totals = computed(() => {
    let subTotal = 0;
    form.items.forEach(item => {
        if (item.selected) {
            subTotal += (item.quantity * item.unit_price);
        }
    });
    const vat = subTotal * 0.15;
    return {
        sub_total: subTotal,
        vat_amount: vat,
        total_amount: subTotal + vat
    };
});

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-SA', { style: 'currency', currency: 'SAR' }).format(amount);
};

</script>

<template>
    <Head title="Create Credit Note" />

    <Layout>
        <template #header>
            <div class="flex justify-between items-center">
                <div class="flex items-center">
                    <button @click="router.get(route('finance.credit-notes.index'))" class="mr-4 text-gray-500 hover:text-gray-700">
                        <ArrowLeftIcon class="w-6 h-6" />
                    </button>
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                        Create Credit Note
                    </h2>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Invoice Selector -->
                    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                            <SearchIcon class="w-5 h-5 mr-2 text-indigo-500" />
                            Step 1: Select Original Invoice
                        </h3>
                        
                        <div v-if="!selectedInvoice" class="space-y-4">
                            <div class="relative">
                                <select 
                                    @change="e => selectInvoice({id: e.target.value})"
                                    class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                                >
                                    <option value="">Choose an invoice...</option>
                                    <option v-for="inv in invoices" :key="inv.id" :value="inv.id">
                                        {{ inv.invoice_number }} - {{ inv.guest?.full_name || inv.company?.name }} ({{ formatCurrency(inv.grand_total) }})
                                    </option>
                                </select>
                            </div>
                            <p class="text-sm text-gray-500">Only confirmed/paid invoices can be credited.</p>
                        </div>

                        <div v-else class="flex justify-between items-center bg-indigo-50 p-4 rounded-md border border-indigo-100">
                            <div>
                                <p class="text-sm font-medium text-indigo-900">Selected Invoice: {{ selectedInvoice.invoice_number }}</p>
                                <p class="text-xs text-indigo-700">Date: {{ selectedInvoice.invoice_date }} | Total: {{ formatCurrency(selectedInvoice.grand_total) }}</p>
                            </div>
                            <button type="button" @click="selectedInvoice = null; form.invoice_id = ''; form.items = []" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 uppercase tracking-wider">
                                Change
                            </button>
                        </div>
                        <div v-if="form.errors.invoice_id" class="mt-2 text-sm text-red-600">{{ form.errors.invoice_id }}</div>
                    </div>

                    <!-- Details -->
                    <div v-if="selectedInvoice" class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                            <InfoIcon class="w-5 h-5 mr-2 text-indigo-500" />
                            Step 2: Credit Note Details
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Reason for Credit</label>
                                <select 
                                    v-model="form.reason"
                                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                                >
                                    <option v-for="reason in reasons" :key="reason" :value="reason">
                                        {{ reason.charAt(0).toUpperCase() + reason.slice(1).replace('_', ' ') }}
                                    </option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Reason Description (Optional)</label>
                                <input 
                                    v-model="form.reason_description"
                                    type="text" 
                                    placeholder="e.g. Booking cancelled by guest"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                >
                            </div>
                        </div>

                        <div class="mt-4">
                            <label class="block text-sm font-medium text-gray-700">Additional Notes</label>
                            <textarea 
                                v-model="form.notes"
                                rows="2"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            ></textarea>
                        </div>
                    </div>

                    <!-- Items Selection -->
                    <div v-if="selectedInvoice" class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
                        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                            <h3 class="text-lg font-medium text-gray-900">Step 3: Select Items to Credit</h3>
                        </div>
                        
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left">
                                            <input type="checkbox" @change="e => form.items.forEach(i => i.selected = e.target.checked)" class="rounded text-indigo-600 focus:ring-indigo-500">
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Price</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Qty to Credit</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total (Excl. VAT)</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="(item, index) in form.items" :key="item.id">
                                        <td class="px-6 py-4">
                                            <input type="checkbox" v-model="item.selected" class="rounded text-indigo-600 focus:ring-indigo-500">
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900 font-medium">
                                            {{ item.product_name }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-500">
                                            {{ formatCurrency(item.unit_price) }}
                                        </td>
                                        <td class="px-6 py-4">
                                            <input 
                                                v-model.number="item.quantity"
                                                type="number" 
                                                :max="item.max_quantity"
                                                min="0.01"
                                                step="0.01"
                                                class="w-20 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                                :disabled="!item.selected"
                                            >
                                            <span class="ml-2 text-xs text-gray-400">/ {{ item.max_quantity }}</span>
                                        </td>
                                        <td class="px-6 py-4 text-right text-sm font-medium text-gray-900">
                                            {{ formatCurrency(item.quantity * item.unit_price) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Summary -->
                        <div class="px-6 py-6 bg-gray-50 flex justify-end border-t border-gray-200">
                            <div class="w-64 space-y-2">
                                <div class="flex justify-between text-sm text-gray-600">
                                    <span>Subtotal</span>
                                    <span>{{ formatCurrency(totals.sub_total) }}</span>
                                </div>
                                <div class="flex justify-between text-sm text-gray-600 font-medium">
                                    <span>VAT (15%)</span>
                                    <span>{{ formatCurrency(totals.vat_amount) }}</span>
                                </div>
                                <div class="pt-2 border-t border-gray-300 flex justify-between text-lg font-bold text-gray-900">
                                    <span>Credit Total</span>
                                    <span>{{ formatCurrency(totals.total_amount) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="flex justify-end space-x-3">
                        <button 
                            type="button" 
                            @click="router.get(route('finance.credit-notes.index'))"
                            class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        >
                            Cancel
                        </button>
                        <button 
                            type="submit" 
                            :disabled="form.processing || !selectedInvoice || totals.total_amount <= 0"
                            class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-50"
                        >
                            <SaveIcon class="w-4 h-4 mr-2" />
                            Create & Process Credit Note
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </Layout>
</template>
