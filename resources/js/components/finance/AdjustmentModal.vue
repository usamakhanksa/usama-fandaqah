<template>
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black bg-opacity-50">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md overflow-hidden animate-fade-in-up">
            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gradient-to-r from-indigo-600 to-blue-600">
                <h3 class="text-lg font-bold text-white">Room Revenue Adjustment</h3>
                <button @click="$emit('close')" class="text-white hover:text-gray-200 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form @submit.prevent="submitAdjustment" class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Reservation ID</label>
                    <input v-model="form.reservation_id" type="text" readonly class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-gray-500 cursor-not-allowed" />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Business Date</label>
                        <input v-model="form.business_date" type="date" required class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Adjustment Type</label>
                        <select v-model="form.type" required class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                            <option value="charge">Extra Charge (+)</option>
                            <option value="rebate">Rebate (-)</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Amount</label>
                    <div class="relative">
                        <span class="absolute left-3 top-2.5 text-gray-400">$</span>
                        <input v-model="form.amount" type="number" step="0.01" min="0.01" required class="w-full pl-8 pr-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all" placeholder="0.00" />
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Reason / Description</label>
                    <textarea v-model="form.reason" rows="3" required class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all" placeholder="Explain why this adjustment is being made..."></textarea>
                </div>

                <div class="pt-4 flex space-x-3">
                    <button type="button" @click="$emit('close')" class="flex-1 px-4 py-2 border border-gray-200 text-gray-600 rounded-lg hover:bg-gray-50 transition-colors font-medium">
                        Cancel
                    </button>
                    <button type="submit" :disabled="loading" class="flex-1 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors font-medium shadow-md disabled:opacity-50 flex items-center justify-center">
                        <svg v-if="loading" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ loading ? 'Posting...' : 'Post Adjustment' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script>
import api from '../../services/api';
import Swal from 'sweetalert2';

export default {
    props: {
        show: Boolean,
        reservationId: [Number, String],
        initialDate: String
    },
    data() {
        return {
            loading: false,
            form: {
                reservation_id: '',
                business_date: '',
                amount: '',
                type: 'rebate',
                reason: ''
            }
        };
    },
    watch: {
        reservationId: {
            immediate: true,
            handler(val) {
                this.form.reservation_id = val;
            }
        },
        initialDate: {
            immediate: true,
            handler(val) {
                this.form.business_date = val;
            }
        }
    },
    methods: {
        async submitAdjustment() {
            this.loading = true;
            try {
                const response = await api.post('/adjustments', this.form);
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: response.data.message,
                    timer: 2000,
                    showConfirmButton: false
                });
                this.$emit('success', response.data.data);
                this.$emit('close');
                this.resetForm();
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.response?.data?.message || 'Failed to post adjustment.'
                });
            } finally {
                this.loading = false;
            }
        },
        resetForm() {
            this.form.amount = '';
            this.form.reason = '';
            this.form.type = 'rebate';
        }
    }
};
</script>

<style scoped>
.animate-fade-in-up {
    animation: fadeInUp 0.3s ease-out;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
