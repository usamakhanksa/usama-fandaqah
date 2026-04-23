<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { 
    PlusIcon, 
    MagnifyingGlassIcon, 
    CalendarIcon, 
    ShieldExclamationIcon,
    ArrowPathIcon,
    TrashIcon,
    PencilSquareIcon,
    EllipsisHorizontalIcon
} from '@heroicons/vue/24/outline';
import HeaderBar from '@/components/HeaderBar.vue';
import SidebarNavigation from '@/components/SidebarNav.vue';
import PaginationControls from '@/components/PaginationControls.vue';
import SearchInput from '@/components/SearchInput.vue';
import BaseModal from '@/components/BaseModal.vue';
import { debounce } from 'lodash';

const props = defineProps({
    bookings: Object,
    filters: Object,
    metrics: Object
});

const search = ref(props.filters.search || '');
const status = ref(props.filters.status || '');
const isShowingModal = ref(false);
const editingBooking = ref(null);

const form = ref({
    guest_name: '',
    guest_phone: '',
    property_reference: '',
    check_in: '',
    check_out: '',
    status: 'pending',
    total_amount: 0,
    notes: ''
});

const openCreateModal = () => {
    editingBooking.value = null;
    form.value = {
        guest_name: '',
        guest_phone: '',
        property_reference: '',
        check_in: '',
        check_out: '',
        status: 'pending',
        total_amount: 0,
        notes: ''
    };
    isShowingModal.value = true;
};

const openEditModal = (booking) => {
    editingBooking.value = booking.id;
    form.value = { ...booking };
    isShowingModal.value = true;
};

const submit = () => {
    if (editingBooking.value) {
        router.put(route('bookings.update', editingBooking.value), form.value, {
            onSuccess: () => isShowingModal.value = false
        });
    } else {
        router.post(route('bookings.store'), form.value, {
            onSuccess: () => isShowingModal.value = false
        });
    }
};

const deleteBooking = (id) => {
    if (confirm('Are you sure you want to cancel this booking?')) {
        router.delete(route('bookings.destroy', id));
    }
};

watch([search, status], debounce(() => {
    router.get(route('bookings.index'), { 
        search: search.value, 
        status: status.value 
    }, { preserveState: true, replace: true });
}, 300));

const getStatusColor = (s) => {
    return {
        'pending': 'bg-yellow-100 text-yellow-800 border-yellow-200',
        'confirmed': 'bg-green-100 text-green-800 border-green-200',
        'cancelled': 'bg-red-100 text-red-800 border-red-200',
        'completed': 'bg-blue-100 text-blue-800 border-blue-200',
    }[s] || 'bg-gray-100 text-gray-800 border-gray-200';
};
</script>

<template>
    <Head title="Bookings Management" />

    <div class="min-h-screen bg-[#f2f0eb] flex">
        <SidebarNavigation />

        <div class="flex-1 flex flex-col min-w-0">
            <HeaderBar title="Bookings & Reservations" />

            <main class="flex-1 p-8 overflow-y-auto">
                <!-- Metrics Section -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex items-center">
                        <div class="w-12 h-12 bg-[#fbcdab]/30 rounded-xl flex items-center justify-center text-[#e95a54] mr-4">
                            <CalendarIcon class="w-6 h-6" />
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 font-medium">Total Bookings</p>
                            <p class="text-2xl font-bold text-[#2a273c]">{{ bookings.total }}</p>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex items-center">
                        <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center text-blue-600 mr-4">
                            <ShieldExclamationIcon class="w-6 h-6" />
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 font-medium">Active Blocks</p>
                            <p class="text-2xl font-bold text-[#2a273c]">{{ metrics.active_blocks }}</p>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex items-center">
                        <div class="w-12 h-12 bg-purple-50 rounded-xl flex items-center justify-center text-purple-600 mr-4">
                            <ArrowPathIcon class="w-6 h-6" />
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 font-medium">Upcoming Events</p>
                            <p class="text-2xl font-bold text-[#2a273c]">{{ metrics.upcoming_events }}</p>
                        </div>
                    </div>
                </div>

                <!-- Filters & Actions -->
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                    <div class="flex flex-1 items-center gap-4">
                        <div class="w-full max-w-md">
                            <SearchInput v-model="search" placeholder="Search by name, reference or property..." />
                        </div>
                        <select 
                            v-model="status"
                            class="bg-white border-gray-200 rounded-xl px-4 py-2 text-sm focus:ring-[#e95a54] focus:border-[#e95a54] outline-none"
                        >
                            <option value="">All Statuses</option>
                            <option value="pending">Pending</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    <button 
                        @click="openCreateModal"
                        class="bg-[#e95a54] hover:bg-[#d44d47] text-white px-6 py-2.5 rounded-xl font-semibold flex items-center justify-center transition-all shadow-lg hover:shadow-xl active:scale-95"
                    >
                        <PlusIcon class="w-5 h-5 mr-2" />
                        New Reservation
                    </button>
                </div>

                <!-- Bookings Table -->
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="bg-gray-50/50 border-b border-gray-100/50">
                                <tr>
                                    <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Reference</th>
                                    <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Guest</th>
                                    <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Property</th>
                                    <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Dates</th>
                                    <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider text-right">Amount</th>
                                    <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                <tr v-for="booking in bookings.data" :key="booking.id" class="hover:bg-gray-50/50 transition-colors group">
                                    <td class="px-6 py-4 whitespace-nowrap font-mono text-xs text-[#e95a54] font-bold">
                                        {{ booking.reference_code }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-[#2a273c]">{{ booking.guest_name }}</div>
                                        <div class="text-xs text-gray-500">{{ booking.guest_phone }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600 font-medium">
                                        {{ booking.property_reference }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600 whitespace-nowrap">
                                        <div class="flex flex-col">
                                            <span>{{ booking.check_in }}</span>
                                            <span class="text-[10px] text-gray-400 font-bold uppercase tracking-tighter">to {{ booking.check_out }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span 
                                            class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider border"
                                            :class="getStatusColor(booking.status)"
                                        >
                                            {{ booking.status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right font-bold text-[#2a273c]">
                                        {{ booking.total_amount }} <span class="text-[10px] text-gray-400">SAR</span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <button @click="openEditModal(booking)" class="p-2 text-gray-400 hover:text-[#e95a54] transition-colors rounded-lg hover:bg-[#e95a54]/5">
                                                <PencilSquareIcon class="w-5 h-5" />
                                            </button>
                                            <button @click="deleteBooking(booking.id)" class="p-2 text-gray-400 hover:text-red-500 transition-colors rounded-lg hover:bg-red-50">
                                                <TrashIcon class="w-5 h-5" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="bookings.data.length === 0">
                                    <td colspan="7" class="px-6 py-20 text-center text-gray-400 italic">
                                        No bookings found matching your criteria.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-6 flex justify-center">
                    <PaginationControls :links="bookings.links" />
                </div>
            </main>
        </div>

        <!-- Add/Edit Modal -->
        <BaseModal v-if="isShowingModal" @close="isShowingModal = false">
            <template #title>
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-[#e95a54]/10 rounded-xl flex items-center justify-center text-[#e95a54] mr-3">
                        <CalendarIcon class="w-5 h-5" />
                    </div>
                    <span>{{ editingBooking ? 'Edit Reservation' : 'New Reservation' }}</span>
                </div>
            </template>

            <form @submit.prevent="submit" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <label class="block text-sm font-bold text-gray-700 mb-1">Guest Full Name</label>
                        <input v-model="form.guest_name" type="text" class="w-full bg-gray-50 border-gray-100 rounded-xl focus:ring-[#e95a54] focus:border-[#e95a54] py-3 px-4 font-medium" placeholder="Enter guest name..." required />
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Phone Number</label>
                        <input v-model="form.guest_phone" type="text" class="w-full bg-gray-50 border-gray-100 rounded-xl focus:ring-[#e95a54] focus:border-[#e95a54] py-3 px-4 font-medium" placeholder="+966 ..." required />
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Property Ref</label>
                        <input v-model="form.property_reference" type="text" class="w-full bg-gray-50 border-gray-100 rounded-xl focus:ring-[#e95a54] focus:border-[#e95a54] py-3 px-4 font-medium" placeholder="e.g. RYD-001" required />
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Check In</label>
                        <input v-model="form.check_in" type="date" class="w-full bg-gray-50 border-gray-100 rounded-xl focus:ring-[#e95a54] focus:border-[#e95a54] py-3 px-4 font-medium" required />
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Check Out</label>
                        <input v-model="form.check_out" type="date" class="w-full bg-gray-50 border-gray-100 rounded-xl focus:ring-[#e95a54] focus:border-[#e95a54] py-3 px-4 font-medium" required />
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Status</label>
                        <select v-model="form.status" class="w-full bg-gray-50 border-gray-100 rounded-xl focus:ring-[#e95a54] focus:border-[#e95a54] py-3 px-4 font-medium">
                            <option value="pending">Pending</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Total Amount (SAR)</label>
                        <input v-model="form.total_amount" type="number" step="0.01" class="w-full bg-gray-50 border-gray-100 rounded-xl focus:ring-[#e95a54] focus:border-[#e95a54] py-3 px-4 font-medium" required />
                    </div>
                    <div class="col-span-2">
                        <label class="block text-sm font-bold text-gray-700 mb-1">Internal Notes</label>
                        <textarea v-model="form.notes" rows="3" class="w-full bg-gray-50 border-gray-100 rounded-xl focus:ring-[#e95a54] focus:border-[#e95a54] py-3 px-4 font-medium" placeholder="Special requests, arrival time, etc."></textarea>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-6 border-t border-gray-100">
                    <button type="button" @click="isShowingModal = false" class="px-6 py-2.5 text-gray-500 font-bold hover:bg-gray-50 rounded-xl transition-colors">Cancel</button>
                    <button type="submit" class="bg-[#2a273c] text-white px-8 py-2.5 rounded-xl font-bold border-2 border-[#2a273c] hover:bg-white hover:text-[#2a273c] transition-all">
                        {{ editingBooking ? 'Update Reservation' : 'Confirm Reservation' }}
                    </button>
                </div>
            </form>
        </BaseModal>
    </div>
</template>

<style scoped>
/* Scoped styles if needed, though Tailwind handles most cases */
</style>
