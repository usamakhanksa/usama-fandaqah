<template>
    <Head :title="`Shift Details - ${shift.shift_number}`" />

    <AuthenticatedLayout>
        <div class="p-6 space-y-6">
            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="flex items-center gap-4">
                    <Link :href="route('finance.cashier-shifts.index')" class="p-button p-component p-button-text p-button-secondary p-button-sm">
                        <i class="pi pi-arrow-left"></i>
                    </Link>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ shift.shift_number }}</h1>
                            <Tag :value="shift.status" :severity="getStatusSeverity(shift.status)" rounded />
                        </div>
                        <p class="text-sm text-gray-500">Cashier: {{ shift.cashier?.name }} | Opened at: {{ formatDate(shift.opened_at) }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <Button v-if="shift.status === 'open'" label="Close Shift" icon="pi pi-lock" severity="warning" @click="showCloseDialog = true" />
                    <Button v-if="shift.status === 'pending_approval'" label="Approve" icon="pi pi-check" severity="success" @click="approveShift" />
                    <Button v-if="shift.status === 'pending_approval'" label="Reject" icon="pi pi-times" severity="danger" @click="showRejectDialog = true" />
                    <Link :href="route('finance.cashier-shifts.report', shift.id)" class="p-button p-component p-button-outlined">
                        <i class="pi pi-print mr-2"></i> Print Report
                    </Link>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Info -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Summary Card -->
                    <Card class="shadow-sm border-none">
                        <template #title>Financial Summary</template>
                        <template #content>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                                <div class="space-y-1">
                                    <p class="text-xs text-gray-500 uppercase">Opening Balance</p>
                                    <p class="text-lg font-bold">{{ formatCurrency(shift.opening_balance) }}</p>
                                </div>
                                <div class="space-y-1">
                                    <p class="text-xs text-gray-500 uppercase">Expected Closing</p>
                                    <p class="text-lg font-bold">{{ formatCurrency(shift.expected_closing_balance || 0) }}</p>
                                </div>
                                <div v-if="shift.status !== 'open'" class="space-y-1">
                                    <p class="text-xs text-gray-500 uppercase">Actual Closing</p>
                                    <p class="text-lg font-bold">{{ formatCurrency(shift.actual_closing_balance) }}</p>
                                </div>
                                <div v-if="shift.status !== 'open'" class="space-y-1">
                                    <p class="text-xs text-gray-500 uppercase">Variance</p>
                                    <p :class="getVarianceClass(shift.variance)" class="text-lg font-bold">{{ formatCurrency(shift.variance) }}</p>
                                </div>
                            </div>
                        </template>
                    </Card>

                    <!-- Transactions Card -->
                    <Card class="shadow-sm border-none">
                        <template #title>Transactions</template>
                        <template #content>
                            <DataTable :value="shift.transactions" :rows="10" paginator responsiveLayout="scroll" class="p-datatable-sm">
                                <Column field="number" header="Trans #"></Column>
                                <Column field="type" header="Type">
                                    <template #body="{ data }">
                                        <Tag :value="data.type" severity="info" plain />
                                    </template>
                                </Column>
                                <Column field="amount" header="Amount">
                                    <template #body="{ data }">
                                        {{ formatCurrency(data.amount) }}
                                    </template>
                                </Column>
                                <Column field="created_at" header="Time">
                                    <template #body="{ data }">
                                        {{ formatDate(data.created_at, 'HH:mm:ss') }}
                                    </template>
                                </Column>
                                <Column header="Action" class="text-right">
                                    <template #body="{ data }">
                                        <Button icon="pi pi-external-link" class="p-button-text p-button-sm" />
                                    </template>
                                </Column>
                            </DataTable>
                        </template>
                    </Card>
                </div>

                <!-- Sidebar Info -->
                <div class="space-y-6">
                    <Card class="shadow-sm border-none">
                        <template #title>Shift Log</template>
                        <template #content>
                            <div class="space-y-4">
                                <div class="flex flex-col gap-1 border-l-2 border-primary pl-4 py-1">
                                    <p class="text-xs text-gray-500">Opened By</p>
                                    <p class="text-sm font-medium">{{ shift.cashier?.name }}</p>
                                    <p class="text-[10px] text-gray-400">{{ formatDate(shift.opened_at) }}</p>
                                </div>
                                <div v-if="shift.closed_at" class="flex flex-col gap-1 border-l-2 border-warning pl-4 py-1">
                                    <p class="text-xs text-gray-500">Closed By</p>
                                    <p class="text-sm font-medium">{{ shift.cashier?.name }}</p>
                                    <p class="text-[10px] text-gray-400">{{ formatDate(shift.closed_at) }}</p>
                                </div>
                                <div v-if="shift.approved_at" class="flex flex-col gap-1 border-l-2 border-success pl-4 py-1">
                                    <p class="text-xs text-gray-500">Approved By</p>
                                    <p class="text-sm font-medium">{{ shift.approved_by?.name }}</p>
                                    <p class="text-[10px] text-gray-400">{{ formatDate(shift.approved_at) }}</p>
                                    <p v-if="shift.approval_notes" class="text-xs text-gray-600 mt-1 italic">"{{ shift.approval_notes }}"</p>
                                </div>
                                <div v-if="shift.rejected_at" class="flex flex-col gap-1 border-l-2 border-danger pl-4 py-1">
                                    <p class="text-xs text-gray-500">Rejected By</p>
                                    <p class="text-sm font-medium">{{ shift.rejected_by?.name }}</p>
                                    <p class="text-[10px] text-gray-400">{{ formatDate(shift.rejected_at) }}</p>
                                    <p v-if="shift.rejection_reason" class="text-xs text-red-600 mt-1 italic font-medium">"{{ shift.rejection_reason }}"</p>
                                </div>
                            </div>
                        </template>
                    </Card>

                    <Card v-if="shift.variance_reason" class="shadow-sm border-none bg-orange-50 dark:bg-orange-900/10">
                        <template #title>Variance Reason</template>
                        <template #content>
                            <p class="text-sm text-gray-700 dark:text-gray-300">{{ shift.variance_reason }}</p>
                        </template>
                    </Card>
                </div>
            </div>
        </div>

        <!-- Close Shift Dialog -->
        <Dialog v-model:visible="showCloseDialog" header="Close Cashier Shift" :style="{ width: '450px' }" modal>
            <div class="space-y-4 py-2">
                <div class="p-4 bg-gray-50 dark:bg-gray-900 rounded-lg space-y-2">
                    <div class="flex justify-between text-sm">
                        <span>Opening Balance:</span>
                        <span class="font-bold">{{ formatCurrency(shift.opening_balance) }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span>System Transactions:</span>
                        <span class="font-bold text-green-600">+ {{ formatCurrency(0) }}</span>
                    </div>
                    <hr class="border-gray-200 dark:border-gray-700">
                    <div class="flex justify-between text-base">
                        <span class="font-medium">Expected Balance:</span>
                        <span class="font-bold text-blue-600">{{ formatCurrency(shift.expected_closing_balance || 0) }}</span>
                    </div>
                </div>

                <div class="space-y-1">
                    <label class="text-sm font-medium">Actual Closing Balance</label>
                    <InputNumber v-model="closeForm.actual_closing_balance" class="w-full" mode="currency" currency="SAR" />
                </div>

                <div v-if="hasVariance" class="space-y-1">
                    <label class="text-sm font-medium text-red-600">Variance Reason (Required)</label>
                    <Textarea v-model="closeForm.variance_reason" rows="3" class="w-full" placeholder="Explain the difference..." />
                </div>

                <div class="space-y-1">
                    <label class="text-sm font-medium">Notes</label>
                    <Textarea v-model="closeForm.notes" rows="2" class="w-full" />
                </div>
            </div>
            <template #footer>
                <Button label="Cancel" icon="pi pi-times" class="p-button-text" @click="showCloseDialog = false" />
                <Button label="Confirm Close" icon="pi pi-check" class="p-button-warning" :loading="closeForm.processing" @click="submitClose" />
            </template>
        </Dialog>

        <!-- Reject Dialog -->
        <Dialog v-model:visible="showRejectDialog" header="Reject Shift" :style="{ width: '400px' }" modal>
            <div class="space-y-4 py-2">
                <div class="space-y-1">
                    <label class="text-sm font-medium">Reason for Rejection</label>
                    <Textarea v-model="rejectForm.rejection_reason" rows="3" class="w-full" placeholder="Why is this shift rejected?" />
                </div>
            </div>
            <template #footer>
                <Button label="Cancel" icon="pi pi-times" class="p-button-text" @click="showRejectDialog = false" />
                <Button label="Confirm Reject" icon="pi pi-times" class="p-button-danger" :loading="rejectForm.processing" @click="submitReject" />
            </template>
        </Dialog>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from 'primevue/card';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Dialog from 'primevue/dialog';
import InputNumber from 'primevue/inputnumber';
import Textarea from 'primevue/textarea';
import dayjs from 'dayjs';

const props = defineProps({
    shift: Object
});

const showCloseDialog = ref(false);
const showRejectDialog = ref(false);

const closeForm = useForm({
    actual_closing_balance: props.shift.expected_closing_balance || 0,
    variance_reason: '',
    notes: '',
    has_variance: false
});

const rejectForm = useForm({
    rejection_reason: ''
});

const hasVariance = computed(() => {
    const diff = closeForm.actual_closing_balance - (props.shift.expected_closing_balance || 0);
    return Math.abs(diff) > 0.01;
});

const formatDate = (date, format = 'DD MMM YYYY, HH:mm') => {
    return dayjs(date).format(format);
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'SAR' }).format(value || 0);
};

const getStatusSeverity = (status) => {
    switch (status) {
        case 'open': return 'info';
        case 'closed': return 'warning';
        case 'pending_approval': return 'warning';
        case 'approved': return 'success';
        case 'rejected': return 'danger';
        default: return 'secondary';
    }
};

const getVarianceClass = (variance) => {
    if (variance < -0.01) return 'text-red-600 font-bold';
    if (variance > 0.01) return 'text-blue-600 font-bold';
    return 'text-green-600 font-bold';
};

const submitClose = () => {
    closeForm.has_variance = hasVariance.value;
    closeForm.post(route('finance.cashier-shifts.close', props.shift.id), {
        onSuccess: () => showCloseDialog.value = false
    });
};

const approveShift = () => {
    if (confirm('Are you sure you want to approve this shift?')) {
        router.post(route('finance.cashier-shifts.approve', props.shift.id));
    }
};

const submitReject = () => {
    rejectForm.post(route('finance.cashier-shifts.reject', props.shift.id), {
        onSuccess: () => showRejectDialog.value = false
    });
};
</script>
