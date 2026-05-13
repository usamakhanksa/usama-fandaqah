<template>
    <Head :title="`Shift Report - ${report.shift.shift_number}`" />

    <div class="min-h-screen bg-gray-50 p-8 print:p-0 print:bg-white flex justify-center">
        <div class="w-full max-w-4xl bg-white p-10 shadow-lg print:shadow-none border border-gray-100 print:border-none">
            <!-- Header -->
            <div class="flex justify-between items-start border-b-2 border-gray-900 pb-6 mb-8">
                <div>
                    <h1 class="text-3xl font-black uppercase tracking-tighter">Cashier Shift Report</h1>
                    <p class="text-sm font-bold text-gray-600 mt-1">Shift Reference: {{ report.shift.shift_number }}</p>
                </div>
                <div class="text-right">
                    <h2 class="text-xl font-bold">Fandaqah Hotel PMS</h2>
                    <p class="text-sm text-gray-500">Business Date: {{ formatDate(report.shift.opened_at, 'DD/MM/YYYY') }}</p>
                </div>
            </div>

            <!-- Meta Details -->
            <div class="grid grid-cols-2 gap-12 mb-10 text-sm">
                <div class="space-y-2">
                    <div class="flex justify-between border-b border-gray-100 pb-1">
                        <span class="text-gray-500">Cashier:</span>
                        <span class="font-bold">{{ report.shift.cashier?.name }}</span>
                    </div>
                    <div class="flex justify-between border-b border-gray-100 pb-1">
                        <span class="text-gray-500">Opened At:</span>
                        <span class="font-bold">{{ formatDate(report.shift.opened_at) }}</span>
                    </div>
                    <div class="flex justify-between border-b border-gray-100 pb-1">
                        <span class="text-gray-500">Closed At:</span>
                        <span class="font-bold">{{ report.shift.closed_at ? formatDate(report.shift.closed_at) : 'STILL OPEN' }}</span>
                    </div>
                    <div class="flex justify-between border-b border-gray-100 pb-1">
                        <span class="text-gray-500">Status:</span>
                        <span class="font-bold uppercase">{{ report.shift.status }}</span>
                    </div>
                </div>
                <div class="space-y-2">
                    <div class="flex justify-between border-b border-gray-100 pb-1">
                        <span class="text-gray-500">Opening Balance:</span>
                        <span class="font-bold">{{ formatCurrency(report.shift.opening_balance) }}</span>
                    </div>
                    <div class="flex justify-between border-b border-gray-100 pb-1">
                        <span class="text-gray-500">Expected Closing:</span>
                        <span class="font-bold text-blue-600">{{ formatCurrency(report.shift.expected_closing_balance) }}</span>
                    </div>
                    <div class="flex justify-between border-b border-gray-100 pb-1 font-black">
                        <span class="text-gray-900">Actual Closing:</span>
                        <span class="text-gray-900">{{ formatCurrency(report.shift.actual_closing_balance) }}</span>
                    </div>
                    <div class="flex justify-between border-b border-gray-100 pb-1 font-black">
                        <span class="text-gray-900">Variance:</span>
                        <span :class="report.shift.variance < 0 ? 'text-red-600' : 'text-green-600'">{{ formatCurrency(report.shift.variance) }}</span>
                    </div>
                </div>
            </div>

            <!-- Breakdown -->
            <div class="mb-10">
                <h3 class="text-sm font-black uppercase mb-4 border-b-2 border-gray-900 inline-block">Payment Method Breakdown</h3>
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 font-bold border-b-2 border-gray-200">
                        <tr>
                            <th class="py-3 px-4 text-left">Method</th>
                            <th class="py-3 px-4 text-right">Received</th>
                            <th class="py-3 px-4 text-right">Paid/Refund</th>
                            <th class="py-3 px-4 text-right">Net</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 border-b-2 border-gray-200">
                        <tr>
                            <td class="py-3 px-4 font-medium uppercase">Cash</td>
                            <td class="py-3 px-4 text-right">{{ formatCurrency(0) }}</td>
                            <td class="py-3 px-4 text-right">{{ formatCurrency(0) }}</td>
                            <td class="py-3 px-4 text-right font-bold">{{ formatCurrency(0) }}</td>
                        </tr>
                        <tr>
                            <td class="py-3 px-4 font-medium uppercase">Card (Visa/Master)</td>
                            <td class="py-3 px-4 text-right">{{ formatCurrency(0) }}</td>
                            <td class="py-3 px-4 text-right">{{ formatCurrency(0) }}</td>
                            <td class="py-3 px-4 text-right font-bold">{{ formatCurrency(0) }}</td>
                        </tr>
                        <tr>
                            <td class="py-3 px-4 font-medium uppercase">Other (Bank/Online)</td>
                            <td class="py-3 px-4 text-right">{{ formatCurrency(0) }}</td>
                            <td class="py-3 px-4 text-right">{{ formatCurrency(0) }}</td>
                            <td class="py-3 px-4 text-right font-bold">{{ formatCurrency(0) }}</td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr class="bg-gray-50 font-black">
                            <td class="py-3 px-4">TOTAL TRANSACTIONS</td>
                            <td class="py-3 px-4 text-right">{{ formatCurrency(0) }}</td>
                            <td class="py-3 px-4 text-right">{{ formatCurrency(0) }}</td>
                            <td class="py-3 px-4 text-right">{{ formatCurrency(0) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Notes -->
            <div v-if="report.shift.variance_reason || report.shift.notes" class="mb-12">
                <h3 class="text-sm font-black uppercase mb-2 border-b-2 border-gray-900 inline-block">Notes & Remarks</h3>
                <div class="bg-gray-50 p-4 border-l-4 border-gray-300">
                    <p v-if="report.shift.variance_reason" class="text-sm mb-2"><span class="font-bold">Variance Reason:</span> {{ report.shift.variance_reason }}</p>
                    <p v-if="report.shift.notes" class="text-sm"><span class="font-bold">General Notes:</span> {{ report.shift.notes }}</p>
                </div>
            </div>

            <!-- Signatures -->
            <div class="grid grid-cols-2 gap-24 mt-24">
                <div class="text-center border-t border-gray-400 pt-4">
                    <p class="text-sm font-bold uppercase tracking-widest">Cashier Signature</p>
                    <p class="text-xs text-gray-500 mt-1">{{ report.shift.cashier?.name }}</p>
                </div>
                <div class="text-center border-t border-gray-400 pt-4">
                    <p class="text-sm font-bold uppercase tracking-widest">Manager Approval</p>
                    <p class="text-xs text-gray-500 mt-1">{{ report.shift.approved_by?.name || '________________' }}</p>
                </div>
            </div>

            <!-- Print Actions -->
            <div class="fixed bottom-8 right-8 print:hidden flex gap-3">
                <Button label="Print Report" icon="pi pi-print" class="p-button-lg shadow-xl" @click="windowPrint" />
                <Button label="Back" icon="pi pi-arrow-left" class="p-button-secondary p-button-lg shadow-xl" @click="goBack" />
            </div>
        </div>
    </div>
</template>

<script setup>
import { Head, router } from '@inertiajs/vue3';
import Button from 'primevue/button';
import dayjs from 'dayjs';

const props = defineProps({
    report: Object
});

const formatDate = (date, format = 'DD MMM YYYY, HH:mm') => {
    return dayjs(date).format(format);
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'SAR' }).format(value || 0);
};

const windowPrint = () => {
    window.print();
};

const goBack = () => {
    window.history.back();
};
</script>

<style scoped>
@media print {
    .fixed { display: none !important; }
}
</style>
