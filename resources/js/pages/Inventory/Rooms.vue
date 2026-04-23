<template>
    <InventoryLayout>
        <!-- Stats Bar -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-[#fbcdab]/30 flex items-center">
                <div class="p-3 bg-[#e95a54]/10 rounded-xl mr-4">
                    <svg class="h-6 w-6 text-[#e95a54]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-[#8f9793] font-medium uppercase tracking-wider">Total Inventory</p>
                    <p class="text-2xl font-bold text-[#2a273c]">{{ stats.total }} Rooms</p>
                </div>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-[#fbcdab]/30 flex items-center">
                <div class="p-3 bg-yellow-50 rounded-xl mr-4">
                    <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-[#8f9793] font-medium uppercase tracking-wider">Dirty / Service</p>
                    <p class="text-2xl font-bold text-yellow-600">{{ stats.dirty }} Rooms</p>
                </div>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-[#fbcdab]/30 flex items-center">
                <div class="p-3 bg-red-50 rounded-xl mr-4">
                    <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-[#8f9793] font-medium uppercase tracking-wider">Out of Order</p>
                    <p class="text-2xl font-bold text-red-600">{{ stats.out_of_order }} Rooms</p>
                </div>
            </div>
        </div>

        <!-- Room Grid -->
        <div class="bg-white rounded-3xl shadow-xl border border-[#fbcdab]/20 overflow-hidden">
            <div class="p-8 border-b border-[#f2f0eb] flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="relative max-w-sm w-full">
                    <input 
                        v-model="search"
                        type="text" 
                        placeholder="Search rooms (e.g. 101, King)..."
                        class="pl-10 w-full rounded-xl border-[#8f9793]/30 focus:border-[#e95a54] focus:ring-[#e95a54]/20 transition-all text-sm py-3"
                    >
                    <svg class="h-5 w-5 text-[#8f9793] absolute left-3 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <button @click="showAddModal = true" class="bg-[#2a273c] text-[#fbcdab] px-6 py-3 rounded-xl font-bold hover:bg-[#e95a54] hover:text-white transition-all duration-300 flex items-center justify-center">
                    <span class="mr-2">+</span> Add New Room
                </button>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 p-8">
                <div v-for="room in rooms.data" :key="room.id" class="group bg-[#f2f0eb]/50 rounded-2xl border border-transparent hover:border-[#e95a54]/30 hover:bg-white hover:shadow-lg transition-all duration-300 p-6 flex flex-col h-full">
                    <div class="flex items-start justify-between mb-4">
                        <div class="h-14 w-14 bg-white rounded-xl shadow-sm border border-[#fbcdab]/30 flex items-center justify-center text-xl font-black text-[#2a273c] group-hover:bg-[#2a273c] group-hover:text-[#fbcdab] transition-colors">
                            {{ room.room_number }}
                        </div>
                        <span :class="[
                            'px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest border',
                            room.status === 'clean' ? 'bg-green-50 text-green-700 border-green-200' :
                            room.status === 'dirty' ? 'bg-yellow-50 text-yellow-700 border-yellow-200' :
                            room.status === 'inspecting' ? 'bg-blue-50 text-blue-700 border-blue-200' :
                            'bg-red-50 text-red-700 border-red-200'
                        ]">
                            {{ room.status.replace('_', ' ') }}
                        </span>
                    </div>

                    <h3 class="text-lg font-bold text-[#2a273c] mb-1 capitalize">{{ room.type }}</h3>
                    <p class="text-xs text-[#8f9793] mb-4 flex items-center">
                        <svg class="h-3 w-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        {{ room.floor }} • Capacity: {{ room.capacity }}
                    </p>

                    <div class="mt-auto pt-4 border-t border-[#fbcdab]/20 flex items-center justify-between">
                        <div>
                            <span class="text-[10px] text-[#8f9793] block uppercase tracking-tighter">Nightly Rate</span>
                            <span class="text-lg font-black text-[#e95a54]">{{ room.base_price }} <small class="text-xs font-medium">SAR</small></span>
                        </div>
                        <div class="flex space-x-1 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button @click="editRoom(room)" class="p-2 bg-white rounded-lg border border-[#fbcdab]/40 text-[#2a273c] hover:bg-[#2a273c] hover:text-white transition-colors">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </button>
                            <button @click="deleteRoom(room.id)" class="p-2 bg-white rounded-lg border border-[#fbcdab]/40 text-red-600 hover:bg-red-600 hover:text-white transition-colors">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-4v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="rooms.data.length === 0" class="py-20 text-center">
                <div class="h-24 w-24 bg-[#f2f0eb] rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="h-12 w-12 text-[#8f9793]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <h3 class="text-xl font-bold text-[#2a273c]">No Rooms Found</h3>
                <p class="text-[#8f9793]">Adjust your search or create a new room inventory.</p>
                <button @click="showAddModal = true" class="mt-6 text-[#e95a54] font-bold hover:underline">Add Your First Room</button>
            </div>

            <!-- Pagination -->
            <div class="px-8 py-6 bg-[#f2f0eb]/30 border-t border-[#f2f0eb] flex items-center justify-between">
                <span class="text-sm text-[#8f9793]">Showing {{ rooms.from }} to {{ rooms.to }} of {{ rooms.total }} rooms</span>
                <div class="flex space-x-1">
                    <Link 
                        v-for="link in rooms.links" 
                        :key="link.label"
                        :href="link.url || '#'"
                        v-html="link.label"
                        :class="[
                            'px-4 py-2 rounded-xl text-sm font-bold transition-all',
                            link.active ? 'bg-[#e95a54] text-white shadow-md' : 'bg-white border border-[#fbcdab]/30 text-[#8f9793] hover:border-[#e95a54] hover:text-[#e95a54]',
                            !link.url && 'opacity-50 cursor-not-allowed hidden'
                        ]"
                    />
                </div>
            </div>
        </div>

        <!-- Add/Edit Modal (Simplified for demo) -->
        <TransitionRoot appear :show="showAddModal" as="template">
            <Dialog as="div" @close="showAddModal = false" class="relative z-50">
                <TransitionChild as="template" enter="duration-300 ease-out" enter-from="opacity-0" enter-to="opacity-100" leave="duration-200 ease-in" leave-from="opacity-100" leave-to="opacity-0">
                    <div class="fixed inset-0 bg-[#2a273c]/80 backdrop-blur-sm" />
                </TransitionChild>
                <div class="fixed inset-0 overflow-y-auto">
                    <div class="flex min-h-full items-center justify-center p-4 text-center">
                        <TransitionChild as="template" enter="duration-300 ease-out" enter-from="opacity-0 scale-95" enter-to="opacity-100 scale-100" leave="duration-200 ease-in" leave-from="opacity-100 scale-100" leave-to="opacity-0 scale-95">
                            <DialogPanel class="w-full max-w-md transform overflow-hidden rounded-3xl bg-white p-8 text-left align-middle shadow-2xl transition-all border border-[#fbcdab]/50">
                                <DialogTitle as="h3" class="text-2xl font-black text-[#2a273c] mb-6 flex items-center justify-between">
                                    {{ form.id ? 'Edit Room' : 'Add New Room' }}
                                    <button @click="showAddModal = false" class="text-[#8f9793] hover:text-[#e95a54]">×</button>
                                </DialogTitle>
                                <form @submit.prevent="submit" class="space-y-4">
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-xs font-bold text-[#8f9793] uppercase mb-1">Room Number</label>
                                            <input v-model="form.room_number" type="text" class="w-full rounded-xl border-[#fbcdab]/30 focus:border-[#e95a54] focus:ring-[#e95a54]/20 py-2.5">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold text-[#8f9793] uppercase mb-1">Room Type</label>
                                            <select v-model="form.type" class="w-full rounded-xl border-[#fbcdab]/30 focus:border-[#e95a54] focus:ring-[#e95a54]/20 py-2.5">
                                                <option value="Standard Double">Standard Double</option>
                                                <option value="Deluxe King">Deluxe King</option>
                                                <option value="Executive Suite">Executive Suite</option>
                                                <option value="Royal Suite">Royal Suite</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-xs font-bold text-[#8f9793] uppercase mb-1">Floor</label>
                                            <input v-model="form.floor" type="text" class="w-full rounded-xl border-[#fbcdab]/30 focus:border-[#e95a54] focus:ring-[#e95a54]/20 py-2.5">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold text-[#8f9793] uppercase mb-1">Base Price (SAR)</label>
                                            <input v-model="form.base_price" type="number" class="w-full rounded-xl border-[#fbcdab]/30 focus:border-[#e95a54] focus:ring-[#e95a54]/20 py-2.5">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-[#8f9793] uppercase mb-1">Status</label>
                                        <select v-model="form.status" class="w-full rounded-xl border-[#fbcdab]/30 focus:border-[#e95a54] focus:ring-[#e95a54]/20 py-2.5">
                                            <option value="clean">Clean</option>
                                            <option value="dirty">Dirty</option>
                                            <option value="inspecting">Inspecting</option>
                                            <option value="out_of_order">Out of Order</option>
                                        </select>
                                    </div>
                                    <div class="pt-4">
                                        <button 
                                            type="submit" 
                                            class="w-full bg-[#e95a54] text-white py-4 rounded-xl font-black shadow-lg shadow-[#e95a54]/20 hover:bg-[#2a273c] transition-all duration-300 disabled:opacity-50"
                                            :disabled="form.processing"
                                        >
                                            {{ form.id ? 'UPDATE ROOM' : 'SAVE ROOM' }}
                                        </button>
                                    </div>
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
import { ref, watch } from 'vue';
import { useForm, Link, router } from '@inertiajs/vue3';
import InventoryLayout from '@/layouts/InventoryLayout.vue';
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue';
import { debounce } from 'lodash';

const props = defineProps({
    rooms: Object,
    stats: Object,
    filters: Object
});

const showAddModal = ref(false);
const search = ref(props.filters.search || '');

const form = useForm({
    id: null,
    room_number: '',
    type: 'Standard Double',
    floor: '',
    capacity: 2,
    base_price: 0,
    status: 'clean'
});

watch(search, debounce((value) => {
    router.get('/rooms', { search: value }, { preserveState: true, replace: true });
}, 500));

const editRoom = (room) => {
    form.id = room.id;
    form.room_number = room.room_number;
    form.type = room.type;
    form.floor = room.floor;
    form.capacity = room.capacity;
    form.base_price = room.base_price;
    form.status = room.status;
    showAddModal.value = true;
};

const deleteRoom = (id) => {
    if (confirm('Permanently remove this room from inventory?')) {
        router.delete(`/rooms/${id}`);
    }
};

const submit = () => {
    if (form.id) {
        form.put(`/rooms/${form.id}`, {
            onSuccess: () => {
                showAddModal.value = false;
                form.reset();
            }
        });
    } else {
        form.post('/rooms', {
            onSuccess: () => {
                showAddModal.value = false;
                form.reset();
            }
        });
    }
};
</script>
