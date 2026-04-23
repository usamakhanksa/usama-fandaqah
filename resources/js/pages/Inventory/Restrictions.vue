<template>
    <InventoryLayout>
        <div class="bg-white rounded-3xl shadow-xl border border-[#fbcdab]/20 overflow-hidden">
            <div class="p-8 border-b border-[#f2f0eb] flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-black text-[#2a273c]">Yield Restrictions</h2>
                    <p class="text-[#8f9793] text-sm">Control availability and stay requirements</p>
                </div>
                <button @click="showAddModal = true" class="bg-[#2a273c] text-[#fbcdab] px-6 py-3 rounded-xl font-bold hover:bg-[#e95a54] hover:text-white transition-all">
                    Apply New Restriction
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-[#f2f0eb]/50 text-[#2a273c]">
                        <tr>
                            <th class="px-8 py-4 text-xs font-black uppercase">Room</th>
                            <th class="px-8 py-4 text-xs font-black uppercase">Type</th>
                            <th class="px-8 py-4 text-xs font-black uppercase">Dates</th>
                            <th class="px-8 py-4 text-xs font-black uppercase">Value</th>
                            <th class="px-8 py-4 text-xs font-black uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#f2f0eb]">
                        <tr v-for="res in restrictions.data" :key="res.id" class="hover:bg-[#f2f0eb]/20 transition-colors">
                            <td class="px-8 py-5">
                                <span class="font-bold text-[#2a273c]">#{{ res.room.room_number }}</span>
                            </td>
                            <td class="px-8 py-5">
                                <span :class="[
                                    'px-2 py-1 rounded text-[10px] font-black uppercase tracking-widest',
                                    res.restriction_type === 'closed' ? 'bg-red-600 text-white' : 'bg-[#e95a54]/10 text-[#e95a54]'
                                ]">
                                    {{ res.restriction_type.replace('_', ' ') }}
                                </span>
                            </td>
                            <td class="px-8 py-5 text-sm text-[#8f9793]">
                                {{ new Date(res.start_date).toLocaleDateString() }} - {{ new Date(res.end_date).toLocaleDateString() }}
                            </td>
                            <td class="px-8 py-5 font-bold text-[#2a273c]">
                                {{ res.value || '-' }}
                            </td>
                            <td class="px-8 py-5">
                                <button @click="deleteRestriction(res.id)" class="text-red-400 hover:text-red-600">
                                    Remove
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div v-if="restrictions.data.length === 0" class="p-20 text-center text-[#8f9793]">
                No restrictions active.
            </div>
        </div>

        <!-- Add Restriction Modal -->
        <TransitionRoot appear :show="showAddModal" as="template">
            <Dialog as="div" @close="showAddModal = false" class="relative z-50">
                <TransitionChild as="template" enter="duration-300 ease-out" enter-from="opacity-0" enter-to="opacity-100" leave="duration-200 ease-in" leave-from="opacity-100" leave-to="opacity-0">
                    <div class="fixed inset-0 bg-[#2a273c]/80 backdrop-blur-sm" />
                </TransitionChild>
                <div class="fixed inset-0 overflow-y-auto">
                    <div class="flex min-h-full items-center justify-center p-4">
                        <TransitionChild as="template" enter="duration-300 ease-out" enter-from="opacity-0 scale-95" enter-to="opacity-100 scale-100" leave="duration-200 ease-in" leave-from="opacity-100 scale-100" leave-to="opacity-0 scale-95">
                            <DialogPanel class="w-full max-w-md transform overflow-hidden rounded-3xl bg-white p-8 border border-[#fbcdab]/50 shadow-2xl">
                                <DialogTitle as="h3" class="text-2xl font-black text-[#2a273c] mb-6">Yield Control</DialogTitle>
                                <form @submit.prevent="submit" class="space-y-4">
                                    <div>
                                        <label class="block text-xs font-bold text-[#8f9793] uppercase mb-1">Target Room</label>
                                        <select v-model="form.room_id" class="w-full rounded-xl border-[#fbcdab]/30 py-2.5">
                                            <option v-for="room in rooms" :key="room.id" :value="room.id">Room {{ room.room_number }}</option>
                                        </select>
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-xs font-bold text-[#8f9793] uppercase mb-1">Start Date</label>
                                            <input v-model="form.start_date" type="date" class="w-full rounded-xl border-[#fbcdab]/30 py-2">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold text-[#8f9793] uppercase mb-1">End Date</label>
                                            <input v-model="form.end_date" type="date" class="w-full rounded-xl border-[#fbcdab]/30 py-2">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-[#8f9793] uppercase mb-1">Restriction Type</label>
                                        <select v-model="form.restriction_type" class="w-full rounded-xl border-[#fbcdab]/30 py-2.5">
                                            <option value="cta">Closed to Arrival (CTA)</option>
                                            <option value="ctd">Closed to Departure (CTD)</option>
                                            <option value="min_los">Minimum Length of Stay</option>
                                            <option value="max_los">Maximum Length of Stay</option>
                                            <option value="closed">Stop Sell (Closed)</option>
                                        </select>
                                    </div>
                                    <div v-if="['min_los', 'max_los'].includes(form.restriction_type)">
                                        <label class="block text-xs font-bold text-[#8f9793] uppercase mb-1">Days</label>
                                        <input v-model="form.value" type="number" class="w-full rounded-xl border-[#fbcdab]/30 py-2">
                                    </div>
                                    <button type="submit" class="w-full bg-[#e95a54] text-white py-4 rounded-xl font-black shadow-lg hover:bg-[#2a273c] transition-all">
                                        APPLY RESTRICTION
                                    </button>
                                </form>
                            </DialogPanel>
                        </TransitionChild>
                    </div>
                </div>
            </Dialog>
        </TransitionRoot>
    </InventoryLayout>
</template>

<script setup>
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import InventoryLayout from '@/layouts/InventoryLayout.vue';
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue';

const props = defineProps({
    restrictions: Object,
    rooms: Array
});

const showAddModal = ref(false);
const form = useForm({
    room_id: '',
    start_date: '',
    end_date: '',
    restriction_type: 'min_los',
    value: '',
    reason: 'Yield management'
});

const submit = () => {
    form.post('/room-restrictions', {
        onSuccess: () => {
            showAddModal.value = false;
            form.reset();
        }
    });
};

const deleteRestriction = (id) => {
    if (confirm('Lift this restriction?')) {
        router.delete(`/room-restrictions/${id}`);
    }
};
</script>
