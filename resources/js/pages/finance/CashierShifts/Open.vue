<template>
    <Head title="Open Cashier Shift" />

    <AuthenticatedLayout>
        <div class="p-6 flex justify-center">
            <div class="w-full max-w-lg">
                <div class="flex items-center gap-4 mb-6">
                    <Link :href="route('finance.cashier-shifts.index')" class="p-button p-component p-button-text p-button-secondary p-button-sm">
                        <i class="pi pi-arrow-left"></i>
                    </Link>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Open Cashier Shift</h1>
                </div>

                <div v-if="currentShift" class="mb-6 p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl flex gap-3">
                    <i class="pi pi-exclamation-triangle text-amber-600 dark:text-amber-400 text-xl mt-1"></i>
                    <div>
                        <h4 class="font-bold text-amber-900 dark:text-amber-100">You already have an open shift!</h4>
                        <p class="text-sm text-amber-700 dark:text-amber-300 mt-1">
                            Shift #{{ currentShift.shift_number }} is currently active. You must close it before opening a new one.
                        </p>
                        <Link :href="route('finance.cashier-shifts.show', currentShift.id)" class="inline-block mt-3 text-sm font-bold underline text-amber-900 dark:text-amber-100">
                            Go to Current Shift
                        </Link>
                    </div>
                </div>

                <Card class="shadow-lg border-none overflow-hidden ring-1 ring-gray-200 dark:ring-gray-700">
                    <template #content>
                        <form @submit.prevent="submit" class="space-y-6 py-4">
                            <div class="space-y-2 text-center pb-4 border-b border-gray-100 dark:border-gray-800">
                                <Avatar icon="pi pi-wallet" size="xlarge" shape="circle" class="bg-primary-50 text-primary mb-2" />
                                <h3 class="text-lg font-bold">Opening Balance</h3>
                                <p class="text-xs text-gray-500 uppercase tracking-widest">Enter the starting cash in your drawer</p>
                            </div>

                            <div class="space-y-1">
                                <label for="opening_balance" class="text-sm font-medium text-gray-700 dark:text-gray-300">Amount (SAR)</label>
                                <InputNumber v-model="form.opening_balance" inputId="opening_balance" 
                                    class="w-full" mode="currency" currency="SAR" locale="en-US"
                                    :min="0" :disabled="!!currentShift" autofocus
                                    :class="{ 'p-invalid': form.errors.opening_balance }" />
                                <small v-if="form.errors.opening_balance" class="p-error">{{ form.errors.opening_balance }}</small>
                            </div>

                            <div class="space-y-1">
                                <label for="notes" class="text-sm font-medium text-gray-700 dark:text-gray-300">Opening Notes</label>
                                <Textarea v-model="form.notes" id="notes" rows="3" class="w-full" 
                                    placeholder="Optional notes about the starting state..."
                                    :disabled="!!currentShift" />
                                <small v-if="form.errors.notes" class="p-error">{{ form.errors.notes }}</small>
                            </div>

                            <Button type="submit" label="Start Shift" icon="pi pi-play" class="w-full p-button-lg shadow-md" 
                                :loading="form.processing" :disabled="!!currentShift" />
                        </form>
                    </template>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from 'primevue/card';
import Button from 'primevue/button';
import InputNumber from 'primevue/inputnumber';
import Textarea from 'primevue/textarea';
import Avatar from 'primevue/avatar';

const props = defineProps({
    currentShift: Object
});

const form = useForm({
    opening_balance: 0,
    notes: ''
});

const submit = () => {
    form.post(route('finance.cashier-shifts.store'));
};
</script>
