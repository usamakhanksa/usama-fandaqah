<template>
    <Teleport to="body">
        <Transition name="modal">
            <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <!-- Backdrop -->
                <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="$emit('close')"></div>

                <!-- Panel -->
                <div class="relative z-10 w-full max-w-lg bg-white rounded-2xl shadow-2xl overflow-hidden">
                    <!-- Header -->
                    <div class="bg-gradient-to-r from-indigo-600 to-indigo-700 px-6 py-5 flex items-center justify-between">
                        <div>
                            <h3 class="text-white text-lg font-bold">Payment Correction</h3>
                            <p class="text-indigo-200 text-xs mt-0.5">TX#{{ transaction?.id }} · Original transaction is never modified</p>
                        </div>
                        <button @click="$emit('close')" class="text-indigo-200 hover:text-white transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Original transaction summary -->
                    <div v-if="transaction" class="px-6 py-4 bg-gray-50 border-b border-gray-100 flex gap-4 flex-wrap">
                        <div>
                            <span class="text-xs text-gray-400 uppercase tracking-wide">Original Amount</span>
                            <div class="text-base font-bold text-gray-800 mt-0.5">{{ formatMoney(transaction.amount / 100) }}</div>
                        </div>
                        <div>
                            <span class="text-xs text-gray-400 uppercase tracking-wide">Original Method</span>
                            <div class="text-base font-semibold text-gray-700 mt-0.5 capitalize">{{ transaction.meta?.payment_type || '—' }}</div>
                        </div>
                        <div>
                            <span class="text-xs text-gray-400 uppercase tracking-wide">Status</span>
                            <div class="mt-0.5">
                                <span class="px-2 py-0.5 bg-amber-100 text-amber-700 text-xs font-semibold rounded-full">🔒 Frozen</span>
                            </div>
                        </div>
                    </div>

                    <!-- Correction form -->
                    <form @submit.prevent="submit" class="px-6 py-5 space-y-5">

                        <!-- Correction type hint -->
                        <div v-if="correctionHint" class="flex items-start gap-3 p-3 rounded-xl border" :class="hintClass">
                            <svg class="w-5 h-5 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p class="text-sm font-medium">{{ correctionHint }}</p>
                        </div>

                        <!-- Payment method -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Correct Payment Method</label>
                            <select
                                v-model="form.correct_payment_type"
                                id="payment-method-select"
                                required
                                class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none bg-white shadow-sm"
                            >
                                <option value="">— Select method —</option>
                                <option value="cash">Cash</option>
                                <option value="mada">Mada</option>
                                <option value="bank-transfer">Bank Transfer</option>
                                <option value="credit">Credit Card</option>
                            </select>
                        </div>

                        <!-- Amount -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Correct Amount (SAR)</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-400 text-sm font-medium pointer-events-none">SAR</span>
                                <input
                                    v-model.number="form.correct_amount"
                                    id="correct-amount-input"
                                    type="number"
                                    step="0.01"
                                    min="0.01"
                                    required
                                    placeholder="0.00"
                                    class="w-full border border-gray-200 rounded-xl pl-14 pr-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none shadow-sm"
                                />
                            </div>
                        </div>

                        <!-- Reason -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Reason</label>
                            <textarea
                                v-model="form.reason"
                                id="correction-reason-textarea"
                                rows="3"
                                placeholder="Describe why this correction is needed..."
                                class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none resize-none shadow-sm"
                            ></textarea>
                        </div>

                        <!-- Error -->
                        <div v-if="errorMsg" class="flex items-start gap-2 p-3 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm">
                            <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                            {{ errorMsg }}
                        </div>

                        <!-- Actions -->
                        <div class="flex gap-3 pt-1">
                            <button
                                type="button"
                                @click="$emit('close')"
                                class="flex-1 px-4 py-2.5 border border-gray-200 rounded-xl text-sm font-medium text-gray-600 hover:bg-gray-50 transition-colors"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                id="submit-correction-btn"
                                :disabled="submitting"
                                class="flex-1 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white text-sm font-semibold rounded-xl transition-colors shadow-sm flex items-center justify-center gap-2"
                            >
                                <svg v-if="submitting" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                                </svg>
                                {{ submitting ? 'Applying...' : 'Apply Correction' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<script>
import api from '../../services/api';

export default {
    name: 'PaymentCorrectionModal',
    emits: ['close', 'success'],

    props: {
        show: { type: Boolean, default: false },
        transaction: { type: Object, default: null },
    },

    data() {
        return {
            form: {
                correct_payment_type: '',
                correct_amount: '',
                reason: '',
            },
            submitting: false,
            errorMsg: '',
        };
    },

    computed: {
        originalAmount() {
            return this.transaction ? Math.abs(this.transaction.amount) / 100 : 0;
        },

        correctionHint() {
            if (!this.form.correct_payment_type || !this.form.correct_amount) return '';

            const methodChanged = this.form.correct_payment_type !== (this.transaction?.meta?.payment_type || '');
            const diff = parseFloat(this.form.correct_amount) - this.originalAmount;
            const amountChanged = Math.abs(diff) >= 0.01;

            if (methodChanged && amountChanged) return '⚠️ Wrong method & amount — will withdraw original and post new deposit.';
            if (methodChanged) return '🔄 Wrong method — will reverse original and re-post under correct method.';
            if (amountChanged && diff < 0) return `💸 Overcharge detected — will withdraw SAR ${Math.abs(diff).toFixed(2)} difference.`;
            if (amountChanged && diff > 0) return `➕ Undercharge detected — will post supplementary deposit of SAR ${diff.toFixed(2)}.`;
            return '✅ No difference detected — please change at least the method or the amount.';
        },

        hintClass() {
            const hint = this.correctionHint;
            if (!hint) return '';
            if (hint.startsWith('⚠️')) return 'bg-purple-50 border-purple-200 text-purple-700';
            if (hint.startsWith('🔄')) return 'bg-orange-50 border-orange-200 text-orange-700';
            if (hint.startsWith('💸')) return 'bg-red-50 border-red-200 text-red-700';
            if (hint.startsWith('➕')) return 'bg-blue-50 border-blue-200 text-blue-700';
            return 'bg-gray-50 border-gray-200 text-gray-600';
        },
    },

    watch: {
        show(val) {
            if (val) {
                // Pre-fill with original values
                this.form.correct_payment_type = this.transaction?.meta?.payment_type || '';
                this.form.correct_amount = this.originalAmount;
                this.form.reason = '';
                this.errorMsg = '';
            }
        },
    },

    methods: {
        async submit() {
            this.errorMsg = '';
            this.submitting = true;
            try {
                const { data } = await api.post('/finance/payment-correction', {
                    frozen_transaction_id: this.transaction?.id,
                    correct_payment_type:  this.form.correct_payment_type,
                    correct_amount:        this.form.correct_amount,
                    reason:                this.form.reason || null,
                });
                this.$emit('success', data.data);
            } catch (err) {
                this.errorMsg = err.response?.data?.message
                    || err.response?.data?.errors?.correct_amount?.[0]
                    || 'An unexpected error occurred.';
            } finally {
                this.submitting = false;
            }
        },

        formatMoney(v) {
            return new Intl.NumberFormat('en-SA', { style: 'currency', currency: 'SAR' }).format(v ?? 0);
        },
    },
};
</script>

<style scoped>
.modal-enter-active,
.modal-leave-active {
    transition: opacity 0.25s ease;
}
.modal-enter-from,
.modal-leave-to {
    opacity: 0;
}
.modal-enter-active .relative,
.modal-leave-active .relative {
    transition: transform 0.25s ease;
}
.modal-enter-from .relative {
    transform: scale(0.95);
}
</style>
