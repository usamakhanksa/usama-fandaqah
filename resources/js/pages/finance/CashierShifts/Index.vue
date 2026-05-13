<template>
    <Head title="Cashier Shifts" />

    <AuthenticatedLayout>
        <div class="p-6 space-y-6">
            <!-- Header Section -->
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Cashier Shifts</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Track and manage cashier work periods and balances.</p>
                </div>
                <div class="flex items-center gap-3">
                    <Link :href="route('finance.cashier-shifts.my-shift')" class="p-button p-component p-button-primary">
                        <i class="pi pi-user mr-2"></i>
                        <span>My Current Shift</span>
                    </Link>
                    <Link :href="route('finance.cashier-shifts.open')" class="p-button p-component p-button-success">
                        <i class="pi pi-plus mr-2"></i>
                        <span>Open New Shift</span>
                    </Link>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <Card class="shadow-sm border-none overflow-hidden relative">
                    <template #content>
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Open Shifts</p>
                                <h3 class="text-3xl font-bold mt-1">{{ stats.open_shifts }}</h3>
                            </div>
                            <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-xl">
                                <i class="pi pi-clock text-blue-600 dark:text-blue-400 text-2xl"></i>
                            </div>
                        </div>
                    </template>
                </Card>

                <Card class="shadow-sm border-none overflow-hidden relative">
                    <template #content>
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Pending Approval</p>
                                <h3 class="text-3xl font-bold mt-1">{{ stats.pending_approval }}</h3>
                            </div>
                            <div class="p-3 bg-orange-50 dark:bg-orange-900/20 rounded-xl">
                                <i class="pi pi-exclamation-circle text-orange-600 dark:text-orange-400 text-2xl"></i>
                            </div>
                        </div>
                    </template>
                </Card>

                <Card class="shadow-sm border-none overflow-hidden relative">
                    <template #content>
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Total Today</p>
                                <h3 class="text-3xl font-bold mt-1">{{ stats.today_shifts }}</h3>
                            </div>
                            <div class="p-3 bg-green-50 dark:bg-green-900/20 rounded-xl">
                                <i class="pi pi-calendar text-green-600 dark:text-green-400 text-2xl"></i>
                            </div>
                        </div>
                    </template>
                </Card>
            </div>

            <!-- Filters & Data Table -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="p-4 border-b border-gray-100 dark:border-gray-700 flex flex-wrap items-center justify-between gap-4">
                    <div class="flex items-center gap-4 flex-1">
                        <span class="p-input-icon-left w-full md:w-64">
                            <i class="pi pi-search" />
                            <InputText v-model="filterStatus" placeholder="Search Status..." class="w-full" />
                        </span>
                        <!-- Date Range Filters would go here -->
                    </div>
                    <div class="flex items-center gap-2">
                        <Button type="button" icon="pi pi-filter-slash" label="Clear" class="p-button-outlined p-button-secondary" @click="clearFilters" />
                    </div>
                </div>

                <DataTable :value="shifts.data" :rows="15" dataKey="id" responsiveLayout="scroll" 
                    class="p-datatable-sm" stripedRows>
                    <Column field="shift_number" header="Shift #" sortable>
                        <template #body="{ data }">
                            <span class="font-mono font-medium">{{ data.shift_number }}</span>
                        </template>
                    </Column>
                    <Column field="cashier.name" header="Cashier" sortable>
                        <template #body="{ data }">
                            <div class="flex items-center gap-2">
                                <Avatar :label="data.cashier?.name?.charAt(0)" shape="circle" class="bg-primary text-white" />
                                <span>{{ data.cashier?.name }}</span>
                            </div>
                        </template>
                    </Column>
                    <Column field="opened_at" header="Opened" sortable>
                        <template #body="{ data }">
                            {{ formatDate(data.opened_at) }}
                        </template>
                    </Column>
                    <Column field="closed_at" header="Closed">
                        <template #body="{ data }">
                            {{ data.closed_at ? formatDate(data.closed_at) : '---' }}
                        </template>
                    </Column>
                    <Column field="opening_balance" header="Opening Bal.">
                        <template #body="{ data }">
                            {{ formatCurrency(data.opening_balance) }}
                        </template>
                    </Column>
                    <Column field="actual_closing_balance" header="Closing Bal.">
                        <template #body="{ data }">
                            {{ data.actual_closing_balance ? formatCurrency(data.actual_closing_balance) : '---' }}
                        </template>
                    </Column>
                    <Column field="variance" header="Variance">
                        <template #body="{ data }">
                            <span v-if="data.status !== 'open'" :class="getVarianceClass(data.variance)">
                                {{ formatCurrency(data.variance) }}
                            </span>
                            <span v-else>---</span>
                        </template>
                    </Column>
                    <Column field="status" header="Status">
                        <template #body="{ data }">
                            <Tag :value="data.status" :severity="getStatusSeverity(data.status)" rounded />
                        </template>
                    </Column>
                    <Column header="Actions" class="text-right">
                        <template #body="{ data }">
                            <div class="flex items-center justify-end gap-2">
                                <Link :href="route('finance.cashier-shifts.show', data.id)" class="p-button p-component p-button-sm p-button-text p-button-info">
                                    <i class="pi pi-eye"></i>
                                </Link>
                                <Button v-if="data.status === 'pending_approval' && $page.props.auth.permissions.includes('cashier_shifts.approve')" 
                                    icon="pi pi-check" class="p-button-sm p-button-text p-button-success" 
                                    @click="approveShift(data.id)" />
                            </div>
                        </template>
                    </Column>
                </DataTable>

                <div class="p-4 border-t border-gray-100 dark:border-gray-700">
                    <Pagination :links="shifts.links" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from 'primevue/card';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Tag from 'primevue/tag';
import Avatar from 'primevue/avatar';
import Pagination from '@/Components/Pagination.vue';
import dayjs from 'dayjs';

const props = defineProps({
    shifts: Object,
    filters: Object,
    stats: Object
});

const filterStatus = ref(props.filters.status || '');

const formatDate = (date) => {
    return dayjs(date).format('DD MMM YYYY, HH:mm');
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'SAR' }).format(value);
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
    if (variance < 0) return 'text-red-600 font-bold';
    if (variance > 0) return 'text-blue-600 font-bold';
    return 'text-green-600 font-bold';
};

const clearFilters = () => {
    filterStatus.value = '';
    router.get(route('finance.cashier-shifts.index'));
};

const approveShift = (id) => {
    if (confirm('Are you sure you want to approve this shift?')) {
        router.post(route('finance.cashier-shifts.approve', id));
    }
};

watch(filterStatus, (value) => {
    router.get(route('finance.cashier-shifts.index'), { status: value }, { preserveState: true, replace: true });
});
</script>
