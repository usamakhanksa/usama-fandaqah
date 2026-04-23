<script setup>
import { ref, watch, computed } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { 
    UserIcon, 
    HomeIcon, 
    ArrowLeftOnRectangleIcon, 
    Squares2X2Icon,
    MagnifyingGlassIcon,
    CheckCircleIcon,
    InformationCircleIcon,
    IdentificationIcon,
    BanknotesIcon,
    TagIcon
} from '@heroicons/vue/24/outline';
import SidebarNavigation from '@/components/SidebarNavigation.vue';
import HeaderBar from '@/components/HeaderBar.vue';
import BaseModal from '@/components/BaseModal.vue';
import { debounce } from 'lodash';

const props = defineProps({
    guests: Object,
    currentView: String,
    counts: Object,
    filters: Object
});

const search = ref(props.filters.search || '');
const isShowingProcessModal = ref(false);
const selectedGuest = ref(null);

const processForm = useForm({
    room_number: '',
    status: '',
    id_verified: false,
    deposit_collected: 0,
    notes: ''
});

const openProcessModal = (guest) => {
    selectedGuest.value = guest;
    processForm.room_number = guest.room_number || '';
    processForm.status = guest.status;
    processForm.id_verified = !!guest.id_verified;
    processForm.deposit_collected = guest.deposit_collected || 0;
    processForm.notes = guest.notes || '';
    isShowingProcessModal.value = true;
};

const submitProcess = () => {
    processForm.put(route('front-desk.process', selectedGuest.value.id), {
        onSuccess: () => isShowingProcessModal.value = false,
        preserveScroll: true
    });
};

watch(search, debounce((val) => {
    router.get(route('front-desk.index'), { search: val, view: props.currentView }, { preserveState: true, replace: true });
}, 300));

const getStatusBadgeClass = (status) => {
    switch (status) {
        case 'arrival': return 'bg-[#fbcdab]/20 text-[#2a273c] border-[#fbcdab]';
        case 'in_house': return 'bg-[#8f9793]/10 text-[#2a273c] border-[#8f9793]';
        case 'departure': return 'bg-[#e95a54]/10 text-[#e95a54] border-[#e95a54]';
        case 'checked_out': return 'bg-gray-100 text-gray-500 border-gray-200';
        default: return 'bg-gray-100 text-gray-500';
    }
};

const viewLabel = computed(() => {
    switch (props.currentView) {
        case 'arrivals': return 'New Arrivals';
        case 'in_house': return 'In-House Guests';
        case 'departures': return 'Departures';
        default: return 'Front Desk';
    }
});

const getHeroIcon = (view) => {
    if (view === 'arrivals') return IdentificationIcon;
    if (view === 'in_house') return HomeIcon;
    return ArrowLeftOnRectangleIcon;
};
</script>

<template>
    <Head :title="`Front Desk - ${viewLabel}`" />
    <div class="min-h-screen bg-[#f2f0eb] flex font-sans text-[#2a273c]">
        <SidebarNavigation />
        <div class="flex-1 flex flex-col">
            <HeaderBar :title="viewLabel" />
            
            <main class="p-8 max-w-7xl mx-auto w-full">
                <!-- Navigation Tabs / Counters -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
                    <button @click="router.get(route('front-desk.index'), { view: 'arrivals' })" 
                        class="relative group overflow-hidden p-8 rounded-[2.5rem] bg-white shadow-xl transition-all hover:translate-y-[-4px]"
                        :class="currentView === 'arrivals' ? 'ring-4 ring-[#e95a54]/20' : 'opacity-70 hover:opacity-100'">
                        <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:scale-110 transition-transform">
                            <IdentificationIcon class="w-24 h-24 text-[#e95a54]" />
                        </div>
                        <div class="relative z-10">
                            <span class="text-xs font-black text-[#8f9793] uppercase tracking-[0.2em]">Expected Arrivals</span>
                            <div class="flex items-baseline mt-2">
                                <span class="text-5xl font-black text-[#2a273c]">{{ counts.arrivals }}</span>
                                <span class="ml-2 text-sm font-bold text-[#e95a54]">Today</span>
                            </div>
                        </div>
                        <div v-if="currentView === 'arrivals'" class="absolute bottom-0 left-0 right-0 h-2 bg-[#e95a54]"></div>
                    </button>

                    <button @click="router.get(route('front-desk.index'), { view: 'in_house' })" 
                        class="relative group overflow-hidden p-8 rounded-[2.5rem] bg-white shadow-xl transition-all hover:translate-y-[-4px]"
                        :class="currentView === 'in_house' ? 'ring-4 ring-[#2a273c]/10' : 'opacity-70 hover:opacity-100'">
                        <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:scale-110 transition-transform">
                            <HomeIcon class="w-24 h-24 text-[#2a273c]" />
                        </div>
                        <div class="relative z-10">
                            <span class="text-xs font-black text-[#8f9793] uppercase tracking-[0.2em]">Guests In-House</span>
                            <div class="flex items-baseline mt-2">
                                <span class="text-5xl font-black text-[#2a273c]">{{ counts.in_house }}</span>
                                <span class="ml-2 text-sm font-bold text-[#8f9793]">Active</span>
                            </div>
                        </div>
                        <div v-if="currentView === 'in_house'" class="absolute bottom-0 left-0 right-0 h-2 bg-[#2a273c]"></div>
                    </button>

                    <button @click="router.get(route('front-desk.index'), { view: 'departures' })" 
                        class="relative group overflow-hidden p-8 rounded-[2.5rem] bg-white shadow-xl transition-all hover:translate-y-[-4px]"
                        :class="currentView === 'departures' ? 'ring-4 ring-[#fbcdab]/50' : 'opacity-70 hover:opacity-100'">
                        <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:scale-110 transition-transform">
                            <ArrowLeftOnRectangleIcon class="w-24 h-24 text-[#fbcdab]" />
                        </div>
                        <div class="relative z-10">
                            <span class="text-xs font-black text-[#8f9793] uppercase tracking-[0.2em]">Expected Departures</span>
                            <div class="flex items-baseline mt-2">
                                <span class="text-5xl font-black text-[#2a273c]">{{ counts.departures }}</span>
                                <span class="ml-2 text-sm font-bold text-[#fbcdab]">Pending</span>
                            </div>
                        </div>
                        <div v-if="currentView === 'departures'" class="absolute bottom-0 left-0 right-0 h-2 bg-[#fbcdab]"></div>
                    </button>
                </div>

                <!-- Main Content Area -->
                <div class="bg-white rounded-[3rem] shadow-2xl p-10 border border-[#8f9793]/10">
                    <div class="flex flex-col md:flex-row justify-between items-center mb-10 gap-6">
                        <div>
                            <h3 class="text-3xl font-black tracking-tighter text-[#2a273c]">
                                {{ currentView.charAt(0).toUpperCase() + currentView.slice(1) }} <span class="text-[#e95a54]">Manifest</span>
                            </h3>
                            <p class="text-[#8f9793] font-medium mt-1">Real-time occupancy and guest movement tracking.</p>
                        </div>
                        
                        <div class="flex gap-4 items-center w-full md:w-auto">
                            <div class="relative flex-1 md:w-80">
                                <input v-model="search" type="text" placeholder="Search by name, room or ref..." 
                                    class="w-full bg-[#f2f0eb] border-none rounded-2xl py-4 pl-12 text-[#2a273c] placeholder-[#8f9793] focus:ring-4 focus:ring-[#e95a54]/10 transition-all font-medium">
                                <MagnifyingGlassIcon class="w-6 h-6 absolute left-4 top-4 text-[#8f9793]" />
                            </div>
                            <Link href="/workspace" class="bg-[#2a273c] text-[#fbcdab] p-4 rounded-2xl hover:bg-[#e95a54] hover:text-white transition-all shadow-lg group">
                                <Squares2X2Icon class="w-6 h-6 group-hover:rotate-90 transition-transform duration-500" />
                            </Link>
                        </div>
                    </div>

                    <!-- Manifest Table -->
                    <div class="overflow-x-auto">
                        <table class="w-full border-separate border-spacing-y-4">
                            <thead>
                                <tr class="text-[#8f9793] text-xs font-black uppercase tracking-[0.2em] text-left">
                                    <th class="px-6 pb-2">Guest Detail</th>
                                    <th class="px-6 pb-2">Room</th>
                                    <th class="px-6 pb-2">Stay Period</th>
                                    <th class="px-6 pb-2">Verification</th>
                                    <th class="px-6 pb-2">Financials</th>
                                    <th class="px-6 pb-2 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="guest in guests.data" :key="guest.id" class="group">
                                    <td class="bg-[#f2f0eb]/30 rounded-l-[1.5rem] px-6 py-6 border-y border-l border-[#8f9793]/10">
                                        <div class="flex items-center gap-4">
                                            <div class="w-12 h-12 rounded-2xl bg-[#2a273c] flex items-center justify-center text-[#fbcdab] font-black text-xl">
                                                {{ guest.guest_name.charAt(0) }}
                                            </div>
                                            <div>
                                                <div class="font-black text-[#2a273c] text-lg leading-tight">{{ guest.guest_name }}</div>
                                                <div class="text-xs font-bold text-[#8f9793] mt-1">{{ guest.booking_reference }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="bg-[#f2f0eb]/30 px-6 py-6 border-y border-[#8f9793]/10">
                                        <div class="inline-flex items-center px-4 py-2 rounded-xl bg-white border border-[#8f9793]/20 shadow-sm">
                                            <span class="text-lg font-black text-[#2a273c]">{{ guest.room_number || '---' }}</span>
                                        </div>
                                    </td>
                                    <td class="bg-[#f2f0eb]/30 px-6 py-6 border-y border-[#8f9793]/10">
                                        <div class="text-sm font-bold text-[#2a273c]">
                                            {{ new Date(guest.expected_arrival_date).toLocaleDateString() }}
                                            <span class="text-[#8f9793] mx-1">→</span>
                                            {{ new Date(guest.expected_departure_date).toLocaleDateString() }}
                                        </div>
                                    </td>
                                    <td class="bg-[#f2f0eb]/30 px-6 py-6 border-y border-[#8f9793]/10">
                                        <div class="flex items-center gap-2">
                                            <component :is="guest.id_verified ? CheckCircleIcon : InformationCircleIcon" 
                                                class="w-5 h-5" :class="guest.id_verified ? 'text-[#8f9793]' : 'text-[#e95a54]'" />
                                            <span class="text-xs font-black uppercase tracking-wider" :class="guest.id_verified ? 'text-[#8f9793]' : 'text-[#e95a54]'">
                                                {{ guest.id_verified ? 'Verified' : 'Pending ID' }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="bg-[#f2f0eb]/30 px-6 py-6 border-y border-[#8f9793]/10">
                                        <div class="flex flex-col">
                                            <span class="text-xs font-black text-[#8f9793] uppercase tracking-tighter">Deposit</span>
                                            <span class="text-sm font-black text-[#2a273c]">SAR {{ parseFloat(guest.deposit_collected).toLocaleString() }}</span>
                                        </div>
                                    </td>
                                    <td class="bg-[#f2f0eb]/30 rounded-r-[1.5rem] px-6 py-6 border-y border-r border-[#8f9793]/10 text-right">
                                        <button @click="openProcessModal(guest)" 
                                            class="inline-flex items-center justify-center px-6 py-3 rounded-xl bg-[#2a273c] text-white font-black text-xs uppercase tracking-widest hover:bg-[#e95a54] transition-all shadow-lg">
                                            Manage Status
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-10 flex justify-center">
                        <div class="inline-flex bg-[#f2f0eb] p-2 rounded-2xl shadow-inner border border-[#8f9793]/10">
                            <Link v-for="(link, k) in guests.links" :key="k" 
                                :href="link.url || '#'" 
                                class="px-6 py-3 rounded-xl font-black text-xs uppercase tracking-widest transition-all"
                                :class="link.active 
                                    ? 'bg-[#2a273c] text-[#fbcdab] shadow-lg' 
                                    : 'text-[#8f9793] hover:text-[#2a273c] hover:bg-white'"
                                v-html="link.label" />
                        </div>
                    </div>
                </div>
            </main>
        </div>

        <!-- Processing Modal -->
        <BaseModal :show="isShowingProcessModal" @close="isShowingProcessModal = false" maxWidth="lg">
            <div class="p-8 bg-white overflow-hidden relative">
                <div class="absolute top-[-20%] right-[-10%] w-64 h-64 bg-[#fbcdab]/20 rounded-full blur-[80px]"></div>
                <div class="relative z-10">
                    <div class="flex justify-between items-start mb-8">
                        <div>
                            <span class="text-xs font-black text-[#e95a54] uppercase tracking-[0.3em]">Front Desk Action</span>
                            <h2 class="text-4xl font-black tracking-tighter text-[#2a273c] mt-2">Process Guest</h2>
                            <p class="text-[#8f9793] font-medium mt-1">Updating status for <span class="text-[#2a273c] font-black">{{ selectedGuest?.guest_name }}</span></p>
                        </div>
                        <div class="w-16 h-16 rounded-[1.5rem] bg-[#f2f0eb] flex items-center justify-center text-[#2a273c]">
                            <IdentificationIcon class="w-8 h-8" />
                        </div>
                    </div>

                    <form @submit.prevent="submitProcess" class="space-y-6">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <label class="text-[0.65rem] font-black text-[#8f9793] uppercase tracking-widest flex items-center gap-1">
                                    <HomeIcon class="w-3 h-3 text-[#e95a54]" /> Assigned Room
                                </label>
                                <input v-model="processForm.room_number" type="text" placeholder="e.g. 104"
                                    class="w-full bg-[#f2f0eb] border-none rounded-2xl py-4 px-6 text-[#2a273c] font-black placeholder-[#8f9793]/50 focus:ring-4 focus:ring-[#e95a54]/10">
                            </div>

                            <div class="space-y-2">
                                <label class="text-[0.65rem] font-black text-[#8f9793] uppercase tracking-widest flex items-center gap-1">
                                    <TagIcon class="w-3 h-3 text-[#e95a54]" /> Current Status
                                </label>
                                <select v-model="processForm.status"
                                    class="w-full bg-[#f2f0eb] border-none rounded-2xl py-4 px-6 text-[#2a273c] font-black focus:ring-4 focus:ring-[#e95a54]/10">
                                    <option value="arrival">Expected Arrival</option>
                                    <option value="in_house">In House (Check-in)</option>
                                    <option value="departure">Expected Departure</option>
                                    <option value="checked_out">Checked Out</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <label class="text-[0.65rem] font-black text-[#8f9793] uppercase tracking-widest flex items-center gap-1">
                                    <BanknotesIcon class="w-3 h-3 text-[#e95a54]" /> Deposit (SAR)
                                </label>
                                <input v-model="processForm.deposit_collected" type="number" step="0.01"
                                    class="w-full bg-[#f2f0eb] border-none rounded-2xl py-4 px-6 text-[#2a273c] font-black focus:ring-4 focus:ring-[#e95a54]/10">
                            </div>

                            <div class="flex items-center gap-4 bg-[#f2f0eb] p-2 rounded-2xl border border-[#8f9793]/10 mt-6 h-[58px]">
                                <input v-model="processForm.id_verified" type="checkbox" id="idv" class="w-6 h-6 rounded-lg text-[#e95a54] bg-white border-[#8f9793]/30 focus:ring-0 cursor-pointer ml-2">
                                <label for="idv" class="text-xs font-black text-[#2a273c] uppercase tracking-tighter cursor-pointer select-none">ID Verified</label>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="text-[0.65rem] font-black text-[#8f9793] uppercase tracking-widest">Internal Remarks</label>
                            <textarea v-model="processForm.notes" rows="3" placeholder="Notes for next shift..."
                                class="w-full bg-[#f2f0eb] border-none rounded-2xl py-4 px-6 text-[#2a273c] font-medium placeholder-[#8f9793]/50 focus:ring-4 focus:ring-[#e95a54]/10"></textarea>
                        </div>

                        <div class="flex gap-4 pt-4">
                            <button type="button" @click="isShowingProcessModal = false"
                                class="flex-1 py-4 bg-[#f2f0eb] text-[#8f9793] font-black text-xs uppercase tracking-widest rounded-2xl hover:bg-gray-200 transition-all">
                                Dismiss
                            </button>
                            <button type="submit" :disabled="processForm.processing"
                                class="flex-[2] py-4 bg-[#2a273c] text-[#fbcdab] font-black text-xs uppercase tracking-widest rounded-2xl hover:bg-[#e95a54] hover:text-white transition-all shadow-xl shadow-[#2a273c]/20">
                                {{ processForm.processing ? 'Syncing...' : 'Update Guest Matrix' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </BaseModal>
    </div>
</template>

<style scoped>
/* Custom scrollbar for manifest */
::-webkit-scrollbar { width: 4px; }
::-webkit-scrollbar-track { background: transparent; }
::-webkit-scrollbar-thumb { background: #8f979344; border-radius: 10px; }
</style>
