<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { 
    PlusIcon, 
    MagnifyingGlassIcon, 
    ShieldExclamationIcon,
    TrashIcon,
    PencilSquareIcon,
    CalendarIcon
} from '@heroicons/vue/24/outline';
import HeaderBar from '@/components/HeaderBar.vue';
import SidebarNavigation from '@/components/SidebarNav.vue';
import PaginationControls from '@/components/PaginationControls.vue';
import SearchInput from '@/components/SearchInput.vue';
import BaseModal from '@/components/BaseModal.vue';
import { debounce } from 'lodash';

const props = defineProps({
    blocks: Object,
    filters: Object
});

const search = ref(props.filters.search || '');
const isShowingModal = ref(false);
const editingBlock = ref(null);

const form = ref({
    property_reference: '',
    start_date: '',
    end_date: '',
    reason: 'maintenance',
    notes: ''
});

const openCreateModal = () => {
    editingBlock.value = null;
    form.value = {
        property_reference: '',
        start_date: '',
        end_date: '',
        reason: 'maintenance',
        notes: ''
    };
    isShowingModal.value = true;
};

const openEditModal = (block) => {
    editingBlock.value = block.id;
    form.value = { ...block };
    isShowingModal.value = true;
};

const submit = () => {
    if (editingBlock.value) {
        router.put(route('booking-blocks.update', editingBlock.value), form.value, {
            onSuccess: () => isShowingModal.value = false
        });
    } else {
        router.post(route('booking-blocks.store'), form.value, {
            onSuccess: () => isShowingModal.value = false
        });
    }
};

const deleteBlock = (id) => {
    if (confirm('Are you sure you want to remove this block?')) {
        router.delete(route('booking-blocks.destroy', id));
    }
};

watch(search, debounce(() => {
    router.get(route('booking-blocks.index'), { search: search.value }, { preserveState: true, replace: true });
}, 300));

const getReasonColor = (r) => {
    return {
        'maintenance': 'bg-red-100 text-red-800 border-red-200',
        'owner_use': 'bg-[#fbcdab] text-[#2a273c] border-[#fbcdab]',
        'other': 'bg-gray-100 text-gray-800 border-gray-200',
    }[r] || 'bg-gray-100 text-gray-800 border-gray-200';
};
</script>

<template>
    <Head title="Room Blocks Management" />

    <div class="min-h-screen bg-[#f2f0eb] flex">
        <SidebarNavigation />

        <div class="flex-1 flex flex-col min-w-0">
            <HeaderBar title="Property & Room Blocks" />

            <main class="flex-1 p-8 overflow-y-auto">
                <!-- Hero Section -->
                <div class="bg-[#2a273c] rounded-[2rem] p-8 mb-8 text-white relative overflow-hidden shadow-2xl">
                    <div class="relative z-10">
                        <h1 class="text-3xl font-bold mb-2 italic">Managed Blocks</h1>
                        <p class="text-white/70 max-w-md">Maintain property availability by blocking rooms for maintenance, owner use, or special requirements.</p>
                    </div>
                    <ShieldExclamationIcon class="absolute right-[-2rem] bottom-[-2rem] w-64 h-64 text-white/5 rotate-12" />
                </div>

                <!-- Filters & Actions -->
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                    <div class="flex flex-1 items-center gap-4">
                        <div class="w-full max-w-md">
                            <SearchInput v-model="search" placeholder="Search by property reference or reason..." />
                        </div>
                    </div>
                    <button 
                        @click="openCreateModal"
                        class="bg-[#e95a54] hover:bg-[#d44d47] text-white px-6 py-2.5 rounded-xl font-semibold flex items-center justify-center transition-all shadow-lg hover:shadow-xl active:scale-95"
                    >
                        <PlusIcon class="w-5 h-5 mr-2" />
                        Add New Block
                    </button>
                </div>

                <!-- Blocks Table -->
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="bg-gray-50/50 border-b border-gray-100/50">
                                <tr>
                                    <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Property Reference</th>
                                    <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Period</th>
                                    <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Reason</th>
                                    <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Notes</th>
                                    <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                <tr v-for="block in blocks.data" :key="block.id" class="hover:bg-gray-50/50 transition-colors group">
                                    <td class="px-6 py-4 whitespace-nowrap font-bold text-[#2a273c]">
                                        {{ block.property_reference }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600 whitespace-nowrap">
                                        <div class="flex flex-col">
                                            <span>{{ block.start_date }}</span>
                                            <span class="text-[10px] text-gray-400 font-bold uppercase tracking-tighter">to {{ block.end_date }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span 
                                            class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider border capitalize"
                                            :class="getReasonColor(block.reason)"
                                        >
                                            {{ block.reason.replace('_', ' ') }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate">
                                        {{ block.notes || '---' }}
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <button @click="openEditModal(block)" class="p-2 text-gray-400 hover:text-[#e95a54] transition-colors rounded-lg hover:bg-[#e95a54]/5">
                                                <PencilSquareIcon class="w-5 h-5" />
                                            </button>
                                            <button @click="deleteBlock(block.id)" class="p-2 text-gray-400 hover:text-red-500 transition-colors rounded-lg hover:bg-red-50">
                                                <TrashIcon class="w-5 h-5" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="blocks.data.length === 0">
                                    <td colspan="5" class="px-6 py-20 text-center text-gray-400 italic">
                                        No active blocks found.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-6 flex justify-center">
                    <PaginationControls :links="blocks.links" />
                </div>
            </main>
        </div>

        <!-- Add/Edit Modal -->
        <BaseModal v-if="isShowingModal" @close="isShowingModal = false">
            <template #title>
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-[#e95a54]/10 rounded-xl flex items-center justify-center text-[#e95a54] mr-3">
                        <ShieldExclamationIcon class="w-5 h-5" />
                    </div>
                    <span>{{ editingBlock ? 'Edit Block' : 'Create New Block' }}</span>
                </div>
            </template>

            <form @submit.prevent="submit" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <label class="block text-sm font-bold text-gray-700 mb-1">Property Reference</label>
                        <input v-model="form.property_reference" type="text" class="w-full bg-gray-50 border-gray-100 rounded-xl focus:ring-[#e95a54] focus:border-[#e95a54] py-3 px-4 font-medium" placeholder="e.g. MKK-702" required />
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Start Date</label>
                        <input v-model="form.start_date" type="date" class="w-full bg-gray-50 border-gray-100 rounded-xl focus:ring-[#e95a54] focus:border-[#e95a54] py-3 px-4 font-medium" required />
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">End Date</label>
                        <input v-model="form.end_date" type="date" class="w-full bg-gray-50 border-gray-100 rounded-xl focus:ring-[#e95a54] focus:border-[#e95a54] py-3 px-4 font-medium" required />
                    </div>
                    <div class="col-span-2">
                        <label class="block text-sm font-bold text-gray-700 mb-1">Reason for Block</label>
                        <select v-model="form.reason" class="w-full bg-gray-50 border-gray-100 rounded-xl focus:ring-[#e95a54] focus:border-[#e95a54] py-3 px-4 font-medium">
                            <option value="maintenance">Routine Maintenance</option>
                            <option value="owner_use">Owner Personal Use</option>
                            <option value="other">Other / Administrative</option>
                        </select>
                    </div>
                    <div class="col-span-2">
                        <label class="block text-sm font-bold text-gray-700 mb-1">Additional Notes</label>
                        <textarea v-model="form.notes" rows="3" class="w-full bg-gray-50 border-gray-100 rounded-xl focus:ring-[#e95a54] focus:border-[#e95a54] py-3 px-4 font-medium" placeholder="Specify details here..."></textarea>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-6 border-t border-gray-100">
                    <button type="button" @click="isShowingModal = false" class="px-6 py-2.5 text-gray-500 font-bold hover:bg-gray-50 rounded-xl transition-colors">Cancel</button>
                    <button type="submit" class="bg-[#2a273c] text-white px-8 py-2.5 rounded-xl font-bold border-2 border-[#2a273c] hover:bg-white hover:text-[#2a273c] transition-all">
                        {{ editingBlock ? 'Update Block' : 'Confirm Block' }}
                    </button>
                </div>
            </form>
        </BaseModal>
    </div>
</template>
