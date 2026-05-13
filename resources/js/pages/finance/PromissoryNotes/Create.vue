<script setup>
import { ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import Layout from '@/Layouts/AuthenticatedLayout.vue';
import { 
    ArrowLeftIcon, 
    SaveIcon, 
    BuildingIcon, 
    UserIcon,
    CalendarIcon,
    FileTextIcon,
    ShieldCheckIcon
} from 'lucide-vue-next';

const props = defineProps({
    companies: Array,
    guests: Array,
});

const form = useForm({
    amount: '',
    issue_date: new Date().toISOString().split('T')[0],
    due_date: '',
    signatory_name: '',
    signatory_id_number: '',
    signatory_phone: '',
    company_id: '',
    guest_id: '',
    guarantor_name: '',
    guarantor_id_number: '',
    guarantor_phone: '',
    notes: '',
});

const submit = () => {
    form.post(route('finance.promissory-notes.store'));
};

</script>

<template>
    <Head title="Create Promissory Note" />

    <Layout>
        <template #header>
            <div class="flex items-center">
                <button @click="router.get(route('finance.promissory-notes.index'))" class="mr-4 text-gray-500 hover:text-gray-700">
                    <ArrowLeftIcon class="w-6 h-6" />
                </button>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Issue New Promissory Note
                </h2>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- General Info -->
                    <div class="bg-white p-8 rounded-xl shadow-sm border border-gray-200">
                        <h3 class="text-lg font-bold text-gray-900 mb-6 flex items-center">
                            <FileTextIcon class="w-5 h-5 mr-2 text-indigo-500" />
                            Note Financials
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="md:col-span-1">
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Amount (SAR)</label>
                                <input 
                                    v-model="form.amount"
                                    type="number" 
                                    step="0.01"
                                    required
                                    class="block w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-lg font-bold"
                                    placeholder="0.00"
                                >
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Issue Date</label>
                                <input 
                                    v-model="form.issue_date"
                                    type="date" 
                                    required
                                    class="block w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                >
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Due Date</label>
                                <input 
                                    v-model="form.due_date"
                                    type="date" 
                                    required
                                    class="block w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                >
                            </div>
                        </div>

                        <div class="mt-6">
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Beneficiary</label>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                                        <BuildingIcon class="w-4 h-4" />
                                    </span>
                                    <select v-model="form.company_id" class="block w-full pl-10 border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                                        <option value="">Select Company (Corporate)</option>
                                        <option v-for="c in companies" :key="c.id" :value="c.id">{{ c.name }}</option>
                                    </select>
                                </div>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                                        <UserIcon class="w-4 h-4" />
                                    </span>
                                    <select v-model="form.guest_id" class="block w-full pl-10 border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                                        <option value="">Select Guest (Individual)</option>
                                        <option v-for="g in guests" :key="g.id" :value="g.id">{{ g.full_name }}</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Signatory Info -->
                    <div class="bg-white p-8 rounded-xl shadow-sm border border-gray-200">
                        <h3 class="text-lg font-bold text-gray-900 mb-6 flex items-center">
                            <ShieldCheckIcon class="w-5 h-5 mr-2 text-indigo-500" />
                            Signatory Details
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Signatory Full Name</label>
                                <input v-model="form.signatory_name" type="text" required class="block w-full border-gray-300 rounded-lg">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">ID / Iqama Number</label>
                                <input v-model="form.signatory_id_number" type="text" required class="block w-full border-gray-300 rounded-lg">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Phone Number</label>
                                <input v-model="form.signatory_phone" type="text" class="block w-full border-gray-300 rounded-lg">
                            </div>
                        </div>
                    </div>

                    <!-- Guarantor (Optional) -->
                    <div class="bg-white p-8 rounded-xl shadow-sm border border-gray-200">
                        <h3 class="text-lg font-bold text-gray-900 mb-6 flex items-center">
                            <UserIcon class="w-5 h-5 mr-2 text-indigo-500" />
                            Guarantor Information (Optional)
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Guarantor Name</label>
                                <input v-model="form.guarantor_name" type="text" class="block w-full border-gray-300 rounded-lg">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">ID Number</label>
                                <input v-model="form.guarantor_id_number" type="text" class="block w-full border-gray-300 rounded-lg">
                            </div>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="bg-white p-8 rounded-xl shadow-sm border border-gray-200">
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Additional Notes</label>
                        <textarea v-model="form.notes" rows="3" class="block w-full border-gray-300 rounded-lg"></textarea>
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-end space-x-3">
                        <button type="button" @click="router.get(route('finance.promissory-notes.index'))" class="px-6 py-2 bg-white border border-gray-300 rounded-lg text-sm font-bold text-gray-700">Cancel</button>
                        <button type="submit" :disabled="form.processing" class="px-6 py-2 bg-indigo-600 rounded-lg text-sm font-bold text-white hover:bg-indigo-700 disabled:opacity-50 flex items-center">
                            <SaveIcon class="w-4 h-4 mr-2" />
                            Create & Issue Note
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </Layout>
</template>
