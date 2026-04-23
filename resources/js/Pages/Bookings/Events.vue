<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { 
    PlusIcon, 
    ArrowPathIcon,
    TrashIcon,
    PencilSquareIcon,
    CalendarDaysIcon,
    MapPinIcon
} from '@heroicons/vue/24/outline';
import HeaderBar from '@/components/HeaderBar.vue';
import SidebarNavigation from '@/components/SidebarNav.vue';
import PaginationControls from '@/components/PaginationControls.vue';
import SearchInput from '@/components/SearchInput.vue';
import BaseModal from '@/components/BaseModal.vue';
import { debounce } from 'lodash';

const props = defineProps({
    events: Object,
    filters: Object
});

const search = ref(props.filters.search || '');
const isShowingModal = ref(false);
const editingEvent = ref(null);

const form = ref({
    title: '',
    property_reference: '',
    start_time: '',
    end_time: '',
    type: 'viewing',
    description: ''
});

const openCreateModal = () => {
    editingEvent.value = null;
    form.value = {
        title: '',
        property_reference: '',
        start_time: '',
        end_time: '',
        type: 'viewing',
        description: ''
    };
    isShowingModal.value = true;
};

const openEditModal = (event) => {
    editingEvent.value = event.id;
    // Format dates for input[type="datetime-local"]
    const start = new Date(event.start_time).toISOString().slice(0, 16);
    const end = new Date(event.end_time).toISOString().slice(0, 16);
    form.value = { ...event, start_time: start, end_time: end };
    isShowingModal.value = true;
};

const submit = () => {
    if (editingEvent.value) {
        router.put(route('booking-events.update', editingEvent.value), form.value, {
            onSuccess: () => isShowingModal.value = false
        });
    } else {
        router.post(route('booking-events.store'), form.value, {
            onSuccess: () => isShowingModal.value = false
        });
    }
};

const deleteEvent = (id) => {
    if (confirm('Are you sure you want to delete this event?')) {
        router.delete(route('booking-events.destroy', id));
    }
};

watch(search, debounce(() => {
    router.get(route('booking-events.index'), { search: search.value }, { preserveState: true, replace: true });
}, 300));

const getTypeColor = (t) => {
    return {
        'viewing': 'bg-blue-100 text-blue-800 border-blue-200',
        'inspection': 'bg-purple-100 text-purple-800 border-purple-200',
        'public_event': 'bg-orange-100 text-orange-800 border-orange-200',
    }[t] || 'bg-gray-100 text-gray-800 border-gray-200';
};
</script>

<template>
    <Head title="Booking Events Management" />

    <div class="min-h-screen bg-[#f2f0eb] flex">
        <SidebarNavigation />

        <div class="flex-1 flex flex-col min-w-0">
            <HeaderBar title="Scheduling & Events" />

            <main class="flex-1 p-8 overflow-y-auto">
                <!-- Hero Section -->
                <div class="bg-gradient-to-r from-[#2a273c] to-[#3d395a] rounded-[2rem] p-8 mb-8 text-white relative overflow-hidden shadow-2xl">
                    <div class="relative z-10">
                        <h1 class="text-3xl font-bold mb-2 italic text-[#fbcdab]">Upcoming Events</h1>
                        <p class="text-white/70 max-w-sm font-light">Track viewings, property inspections, and open-house events across all locations.</p>
                    </div>
                    <CalendarDaysIcon class="absolute right-[-1rem] bottom-[-2rem] w-64 h-64 text-white/5 -rotate-6" />
                </div>

                <!-- Filters & Actions -->
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                    <div class="flex flex-1 items-center gap-4">
                        <div class="w-full max-w-md">
                            <SearchInput v-model="search" placeholder="Search events by title or property..." />
                        </div>
                    </div>
                    <button 
                        @click="openCreateModal"
                        class="bg-[#e95a54] hover:bg-[#d44d47] text-white px-6 py-2.5 rounded-xl font-semibold flex items-center justify-center transition-all shadow-lg hover:shadow-xl active:scale-95"
                    >
                        <PlusIcon class="w-5 h-5 mr-2" />
                        Schedule Event
                    </button>
                </div>

                <!-- Events List -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div v-for="event in events.data" :key="event.id" class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-shadow group relative overflow-hidden">
                        <div class="flex justify-between items-start mb-4">
                            <div class="flex-1">
                                <span class="px-2.5 py-0.5 rounded-lg text-[10px] font-bold uppercase tracking-widest border mb-2 inline-block shadow-sm" :class="getTypeColor(event.type)">
                                    {{ event.type.replace('_', ' ') }}
                                </span>
                                <h3 class="text-xl font-bold text-[#2a273c] leading-tight mb-1 group-hover:text-[#e95a54] transition-colors cursor-pointer">{{ event.title }}</h3>
                                <div class="flex items-center text-gray-500 text-xs font-medium">
                                    <MapPinIcon class="w-3.5 h-3.5 mr-1" />
                                    {{ event.property_reference || 'N/A' }}
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <button @click="openEditModal(event)" class="p-2 text-gray-400 hover:text-[#e95a54] transition-colors rounded-lg bg-gray-50">
                                    <PencilSquareIcon class="w-4 h-4" />
                                </button>
                                <button @click="deleteEvent(event.id)" class="p-2 text-gray-400 hover:text-red-500 transition-colors rounded-lg bg-gray-50">
                                    <TrashIcon class="w-4 h-4" />
                                </button>
                            </div>
                        </div>

                        <div class="flex items-center gap-6 mt-6 bg-[#f2f0eb]/50 p-4 rounded-2xl border border-dotted border-gray-200">
                            <div class="flex flex-col items-center justify-center min-w-[60px] border-r border-gray-200 pr-6">
                                <span class="text-xs font-bold text-gray-400 uppercase tracking-widest">{{ new Date(event.start_time).toLocaleString('en-US', { month: 'short' }) }}</span>
                                <span class="text-2xl font-black text-[#2a273c]">{{ new Date(event.start_time).getDate() }}</span>
                            </div>
                            <div class="flex-1">
                                <div class="text-xs font-bold text-[#2a273c]">
                                    {{ new Date(event.start_time).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) }} 
                                    <span class="text-gray-400 font-normal mx-2">to</span>
                                    {{ new Date(event.end_time).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) }}
                                </div>
                                <div class="text-xs text-gray-500 mt-1 italic line-clamp-1">
                                    {{ event.description || 'No additional details provided.' }}
                                </div>
                            </div>
                        </div>
                        
                        <div class="absolute bottom-0 left-0 w-full h-1 bg-[#e95a54] opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    </div>

                    <div v-if="events.data.length === 0" class="col-span-full py-20 text-center bg-white rounded-3xl border border-gray-100 shadow-inner">
                        <ArrowPathIcon class="w-12 h-12 text-gray-200 mx-auto mb-4" />
                        <p class="text-gray-400 italic">No scheduled events found.</p>
                    </div>
                </div>

                <div class="mt-8 flex justify-center">
                    <PaginationControls :links="events.links" />
                </div>
            </main>
        </div>

        <!-- Add/Edit Modal -->
        <BaseModal v-if="isShowingModal" @close="isShowingModal = false">
            <template #title>
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-[#e95a54]/10 rounded-xl flex items-center justify-center text-[#e95a54] mr-3">
                        <CalendarDaysIcon class="w-5 h-5" />
                    </div>
                    <span>{{ editingEvent ? 'Edit Scheduled Event' : 'Schedule New Event' }}</span>
                </div>
            </template>

            <form @submit.prevent="submit" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <label class="block text-sm font-bold text-gray-700 mb-1">Event Title</label>
                        <input v-model="form.title" type="text" class="w-full bg-gray-50 border-gray-100 rounded-xl focus:ring-[#e95a54] focus:border-[#e95a54] py-3 px-4 font-medium" placeholder="What is this event for?" required />
                    </div>
                    <div class="col-span-2 md:col-span-1">
                        <label class="block text-sm font-bold text-gray-700 mb-1">Property Reference</label>
                        <input v-model="form.property_reference" type="text" class="w-full bg-gray-50 border-gray-100 rounded-xl focus:ring-[#e95a54] focus:border-[#e95a54] py-3 px-4 font-medium" placeholder="Optional" />
                    </div>
                    <div class="col-span-2 md:col-span-1">
                        <label class="block text-sm font-bold text-gray-700 mb-1">Event Type</label>
                        <select v-model="form.type" class="w-full bg-gray-50 border-gray-100 rounded-xl focus:ring-[#e95a54] focus:border-[#e95a54] py-3 px-4 font-medium">
                            <option value="viewing">Property Viewing</option>
                            <option value="inspection">Staff Inspection</option>
                            <option value="public_event">Public/Promotional Event</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Start Time</label>
                        <input v-model="form.start_time" type="datetime-local" class="w-full bg-gray-50 border-gray-100 rounded-xl focus:ring-[#e95a54] focus:border-[#e95a54] py-3 px-4 font-medium" required />
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">End Time</label>
                        <input v-model="form.end_time" type="datetime-local" class="w-full bg-gray-50 border-gray-100 rounded-xl focus:ring-[#e95a54] focus:border-[#e95a54] py-3 px-4 font-medium" required />
                    </div>
                    <div class="col-span-2">
                        <label class="block text-sm font-bold text-gray-700 mb-1">Description</label>
                        <textarea v-model="form.description" rows="3" class="w-full bg-gray-50 border-gray-100 rounded-xl focus:ring-[#e95a54] focus:border-[#e95a54] py-3 px-4 font-medium" placeholder="Provide event details, attendees, etc."></textarea>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-6 border-t border-gray-100">
                    <button type="button" @click="isShowingModal = false" class="px-6 py-2.5 text-gray-500 font-bold hover:bg-gray-50 rounded-xl transition-colors">Cancel</button>
                    <button type="submit" class="bg-[#2a273c] text-white px-8 py-2.5 rounded-xl font-bold border-2 border-[#2a273c] hover:bg-white hover:text-[#2a273c] transition-all">
                        {{ editingEvent ? 'Save Changes' : 'Confirm & Schedule' }}
                    </button>
                </div>
            </form>
        </BaseModal>
    </div>
</template>
