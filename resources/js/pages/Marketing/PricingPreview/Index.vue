<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { 
    Calculate as CalculateIcon, 
    EventNote as CalendarIcon,
    CompareArrows as CompareIcon,
    Receipt as ReceiptIcon,
    Percent as DiscountIcon,
    LocalTax as TaxIcon
} from '@mui/icons-material-runtime';
import { ref, reactive } from 'vue';
import axios from 'axios';

const props = defineProps({
    roomTypes: Array,
    sources: Array
});

const form = reactive({
    room_type_id: '',
    start_date: '',
    end_date: '',
    guests: 1,
    source_id: '',
    promo_code: ''
});

const results = ref(null);
const loading = ref(false);
const error = ref('');

const calculate = async () => {
    loading.ref = true;
    error.value = '';
    results.value = null;
    
    try {
        const response = await axios.post(route('marketing.pricing-preview.calculate'), form);
        results.value = response.data;
    } catch (e) {
        error.value = e.response?.data?.message || 'Calculation failed. Please check your inputs.';
    } finally {
        loading.value = false;
    }
};

</script>

<template>
    <Head title="Pricing Preview" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight flex items-center gap-2">
                <CalculateIcon class="text-indigo-500" />
                Pricing Preview & Comparison
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Calculator Form -->
                    <div class="lg:col-span-1 bg-white p-6 rounded-2xl shadow-sm border border-gray-100 h-fit">
                        <h3 class="text-lg font-bold mb-6 flex items-center gap-2">
                            <ReceiptIcon class="text-gray-400" />
                            Parameters
                        </h3>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Room Type</label>
                                <select v-model="form.room_type_id" class="w-full rounded-xl border-gray-200 focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="" disabled>Select Room Type</option>
                                    <option v-for="type in roomTypes" :key="type.id" :value="type.id">{{ type.name }}</option>
                                </select>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Check-In</label>
                                    <input type="date" v-model="form.start_date" class="w-full rounded-xl border-gray-200" />
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Check-Out</label>
                                    <input type="date" v-model="form.end_date" class="w-full rounded-xl border-gray-200" />
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Guests</label>
                                    <input type="number" v-model="form.guests" min="1" class="w-full rounded-xl border-gray-200" />
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Source</label>
                                    <select v-model="form.source_id" class="w-full rounded-xl border-gray-200">
                                        <option value="">Direct / None</option>
                                        <option v-for="s in sources" :key="s.id" :value="s.id">{{ s.name }}</option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Promo Code</label>
                                <input type="text" v-model="form.promo_code" placeholder="Enter code..." class="w-full rounded-xl border-gray-200 placeholder:text-gray-300" />
                            </div>

                            <button 
                                @click="calculate"
                                :disabled="loading"
                                class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 rounded-xl transition-all shadow-lg shadow-indigo-100 disabled:opacity-50"
                            >
                                {{ loading ? 'Calculating...' : 'Calculate Breakdown' }}
                            </button>
                            
                            <p v-if="error" class="text-xs text-rose-500 text-center mt-2">{{ error }}</p>
                        </div>
                    </div>

                    <!-- Breakdown Results -->
                    <div class="lg:col-span-2 space-y-6">
                        <div v-if="results" class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 animate-in fade-in slide-in-from-bottom-4 duration-500">
                            <div class="flex justify-between items-start mb-8">
                                <div>
                                    <h3 class="text-2xl font-black text-gray-900">Total Price Breakdown</h3>
                                    <p class="text-gray-500">For {{ results.nights }} nights stay</p>
                                </div>
                                <div class="text-right">
                                    <div class="text-3xl font-black text-indigo-600">SAR {{ results.grand_total.toLocaleString() }}</div>
                                    <div class="text-xs text-gray-400 font-bold uppercase tracking-widest">Grand Total (Incl. VAT)</div>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <div class="flex justify-between items-center py-3 border-b border-gray-50">
                                    <span class="text-gray-600 flex items-center gap-2">
                                        <CalendarIcon class="text-gray-300" fontSize="small" />
                                        Base Room Rate Total
                                    </span>
                                    <span class="font-bold text-gray-900">SAR {{ results.base_rate_total.toLocaleString() }}</span>
                                </div>
                                <div class="flex justify-between items-center py-3 border-b border-gray-50 text-rose-500">
                                    <span class="flex items-center gap-2">
                                        <DiscountIcon fontSize="small" />
                                        Marketing Discounts & Offers
                                    </span>
                                    <span class="font-bold">- SAR {{ results.discounts_total.toLocaleString() }}</span>
                                </div>
                                <div class="flex justify-between items-center py-3 border-b border-gray-50">
                                    <span class="text-gray-600 font-semibold">Subtotal</span>
                                    <span class="font-bold text-gray-900">SAR {{ results.subtotal.toLocaleString() }}</span>
                                </div>
                                <div class="flex justify-between items-center py-3">
                                    <span class="text-gray-500 flex items-center gap-2 italic">
                                        <TaxIcon class="text-gray-300" fontSize="small" />
                                        Estimated VAT (15%)
                                    </span>
                                    <span class="font-medium text-gray-500">SAR {{ results.tax_amount.toLocaleString() }}</span>
                                </div>
                            </div>

                            <div class="mt-8 p-4 bg-indigo-50 rounded-xl border border-indigo-100">
                                <h4 class="text-xs font-bold text-indigo-600 uppercase mb-3">Daily Rate Preview</h4>
                                <div class="flex gap-2 overflow-x-auto pb-2">
                                    <div v-for="day in results.breakdown" :key="day.date" class="min-w-[100px] bg-white p-3 rounded-lg border border-indigo-200 text-center shadow-sm">
                                        <div class="text-[10px] text-gray-400 font-bold mb-1">{{ day.date }}</div>
                                        <div class="text-sm font-black text-gray-900">SAR {{ day.base }}</div>
                                        <div v-if="day.special_applied" class="text-[8px] bg-orange-100 text-orange-600 px-1 rounded inline-block">Special</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-else class="h-full flex flex-col items-center justify-center bg-gray-50 rounded-2xl border-2 border-dashed border-gray-200 py-20 px-10 text-center">
                            <CalculateIcon class="text-gray-200 !text-8xl mb-4" />
                            <h4 class="text-lg font-bold text-gray-400">Ready to Calculate?</h4>
                            <p class="text-gray-400 max-w-xs">Fill in the parameters on the left to see dynamic pricing breakdowns and comparisons.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
