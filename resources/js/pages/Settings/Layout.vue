<template>
    <div class="min-h-screen bg-[#f2f0eb] flex">
        <!-- Settings Sidebar -->
        <div class="w-72 bg-[#2a273c] text-white flex-shrink-0 shadow-xl overflow-y-auto">
            <div class="p-6 border-b border-[#8f9793]/20">
                <h1 class="text-xl font-bold text-[#fbcdab]">PMS Settings</h1>
                <p class="text-xs text-[#8f9793] mt-1">Control Center</p>
            </div>
            <nav class="p-4 space-y-1">
                <div v-for="section in menu" :key="section.title" class="mb-4">
                    <p class="px-3 text-[10px] uppercase tracking-widest text-[#8f9793] font-semibold mb-2">
                        {{ section.title }}
                    </p>
                    <Link
                        v-for="item in section.items"
                        :key="item.slug"
                        :href="route('settings.index', item.slug)"
                        class="flex items-center px-4 py-3 rounded-xl transition-all duration-300 group"
                        :class="currentGroup === item.slug ? 'bg-[#e95a54] text-white' : 'text-[#8f9793] hover:bg-white/5 hover:text-white'"
                    >
                        <component :is="item.icon" class="w-5 h-5 mr-3" :class="currentGroup === item.slug ? 'text-white' : 'text-[#8f9793] group-hover:text-[#fbcdab]'" />
                        <span class="text-sm font-medium">{{ item.label }}</span>
                    </Link>
                </div>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="h-16 bg-white border-b border-[#8f9793]/10 flex items-center justify-between px-8">
                <h2 class="text-lg font-bold text-[#2a273c]">{{ activeLabel }}</h2>
                <div class="flex items-center space-y-0 space-x-4">
                    <button class="p-2 text-[#8f9793] hover:text-[#e95a54] transition-colors">
                        <SearchIcon class="w-5 h-5" />
                    </button>
                    <button class="p-2 text-[#8f9793] hover:text-[#e95a54] transition-colors">
                        <BellIcon class="w-5 h-5" />
                    </button>
                    <div class="w-8 h-8 rounded-full bg-[#fbcdab] flex items-center justify-center text-[#2a273c] font-bold text-xs">
                        UK
                    </div>
                </div>
            </header>

            <main class="flex-1 overflow-y-auto p-8 custom-scrollbar">
                <div class="max-w-5xl mx-auto">
                    <slot v-if="$slots.default"></slot>
                    <component 
                        v-else
                        :is="contentComponent" 
                        :data="data" 
                        :group="currentGroup"
                    />
                </div>
            </main>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { 
    LayoutIcon, SettingsIcon, HomeIcon, AccessibilityIcon, LinkIcon, 
    UsersIcon, FileTextIcon, BellIcon, CreditCardIcon, ActivityIcon,
    HashIcon, LayersIcon, UserGroupIcon, GlobeIcon, StarIcon,
    ToolIcon, SearchIcon, CoffeeIcon
} from 'lucide-vue-next';
import FormGroup from './FormGroup.vue';
import DictionaryList from './DictionaryList.vue';
import ActivityLogs from './ActivityLogs.vue';

const props = defineProps({
    currentGroup: String,
    data: Object
});

const menu = [
    {
        title: 'Basic',
        items: [
            { label: 'Header & Slider', slug: 'header-slider', icon: LayoutIcon },
            { label: 'General Settings', slug: 'general', icon: SettingsIcon },
            { label: 'Facility Settings', slug: 'facility', icon: HomeIcon },
            { label: 'Hotel Amenities', slug: 'hotel-amenities', icon: AccessibilityIcon },
        ]
    },
    {
        title: 'System',
        items: [
            { label: 'Integration Settings', slug: 'integration', icon: LinkIcon },
            { label: 'Users & Roles', slug: 'users-roles', icon: UsersIcon },
            { label: 'Document Settings', slug: 'documents', icon: FileTextIcon },
            { label: 'Notifications', slug: 'notifications', icon: BellIcon },
        ]
    },
    {
        title: 'Operations',
        items: [
            { label: 'Finance Settings', slug: 'finance', icon: CreditCardIcon },
            { label: 'Activity Logs', slug: 'activity-logs', icon: ActivityIcon },
            { label: 'Ledger Numbers', slug: 'ledger-numbers', icon: HashIcon },
            { label: 'Reservation Resources', slug: 'reservation-resources', icon: LayersIcon },
        ]
    },
    {
        title: 'Advanced',
        items: [
            { label: 'Customer Groups', slug: 'customer-groups', icon: UserGroupIcon },
            { label: 'Website Settings', slug: 'website', icon: GlobeIcon },
            { label: 'Rating Settings', slug: 'rating', icon: StarIcon },
            { label: 'Included Services', slug: 'services-included', icon: CoffeeIcon },
            { label: 'Maintenance Settings', slug: 'maintenance', icon: ToolIcon },
        ]
    }
];

const activeLabel = computed(() => {
    for (const section of menu) {
        const item = section.items.find(i => i.slug === props.currentGroup);
        if (item) return item.label;
    }
    return 'Settings';
});

const contentComponent = computed(() => {
    if (props.currentGroup === 'activity-logs') return ActivityLogs;
    
    const dictionaryGroups = [
        'header-slider', 'hotel-amenities', 'ledger-numbers', 
        'customer-groups', 'reservation-resources', 'maintenance', 
        'services-included'
    ];
    
    if (dictionaryGroups.includes(props.currentGroup)) return DictionaryList;
    
    return FormGroup;
});

</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #8f979333;
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #e95a5455;
}
</style>
