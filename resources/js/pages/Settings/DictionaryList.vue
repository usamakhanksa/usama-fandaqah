<template>
    <div class="space-y-6">
        <!-- Add New Item -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-[#e95a54]/20 border-dashed">
            <div class="flex gap-4">
                <input 
                    v-model="newItem.label"
                    placeholder="Enter new item label..."
                    class="flex-1 bg-[#f2f0eb]/50 border-none rounded-xl px-4 py-3 text-[#2a273c] focus:ring-2 focus:ring-[#e95a54] transition-all"
                    @keyup.enter="addItem"
                >
                <button 
                    @click="addItem"
                    :disabled="!newItem.label || processing"
                    class="bg-[#2a273c] text-[#fbcdab] px-6 py-3 rounded-xl font-bold hover:bg-[#2a273c]/90 transition-all flex items-center shrink-0"
                >
                    <PlusIcon class="w-5 h-5 mr-2" />
                    Add Item
                </button>
            </div>
        </div>

        <!-- Items Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div 
                v-for="item in data.items" 
                :key="item.id"
                class="bg-white rounded-2xl p-5 shadow-sm border border-[#8f9793]/10 flex items-center justify-between group transition-all hover:border-[#e95a54]/30"
            >
                <div class="flex items-center space-x-4">
                    <div 
                        class="w-10 h-10 rounded-xl flex items-center justify-center transition-colors"
                        :class="item.is_active ? 'bg-[#e95a54]/10 text-[#e95a54]' : 'bg-[#8f9793]/10 text-[#8f9793]'"
                    >
                        <component :is="getIcon()" class="w-5 h-5" />
                    </div>
                    <div>
                        <p class="font-bold text-[#2a273c]">{{ item.label }}</p>
                        <p class="text-xs text-[#8f9793]">{{ item.is_active ? 'Active' : 'Inactive' }}</p>
                    </div>
                </div>
                
                <div class="flex items-center space-x-2 opacity-0 group-hover:opacity-100 transition-opacity">
                    <button 
                        @click="toggleActive(item)"
                        class="p-2 rounded-lg hover:bg-[#f2f0eb] text-[#8f9793] hover:text-[#2a273c]"
                        :title="item.is_active ? 'Deactivate' : 'Activate'"
                    >
                        <PowerIcon class="w-4 h-4" />
                    </button>
                    <button 
                        @click="deleteItem(item)"
                        class="p-2 rounded-lg hover:bg-red-50 text-[#8f9793] hover:text-red-500"
                    >
                        <TrashIcon class="w-4 h-4" />
                    </button>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-if="!data.items?.length" class="text-center py-20 bg-white rounded-2xl border border-dashed border-[#8f9793]/20">
            <div class="w-16 h-16 bg-[#f2f0eb] rounded-full flex items-center justify-center mx-auto mb-4">
                <InboxIcon class="w-8 h-8 text-[#8f9793]" />
            </div>
            <h3 class="text-[#2a273c] font-bold">No Items Found</h3>
            <p class="text-[#8f9793] text-sm">Start by adding a new item above.</p>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { 
    PlusIcon, TrashIcon, PowerIcon, InboxIcon, 
    CheckCircleIcon, TagIcon, LayoutIcon 
} from 'lucide-vue-next';

const props = defineProps({
    data: Object,
    group: String
});

const newItem = reactive({
    label: '',
    group: props.group === 'header-slider' ? 'sliders' : 
           props.group === 'hotel-amenities' ? 'amenities' :
           props.group === 'ledger-numbers' ? 'ledger_numbers' :
           props.group === 'customer-groups' ? 'customer_groups' :
           props.group === 'reservation-resources' ? 'reservation_resources' :
           props.group === 'maintenance' ? 'maintenance_types' : 'included_services'
});

const processing = ref(false);

const getIcon = () => {
    if (props.group === 'hotel-amenities') return TagIcon;
    if (props.group === 'header-slider') return LayoutIcon;
    return CheckCircleIcon;
};

const addItem = () => {
    if (!newItem.label) return;
    processing.value = true;
    router.post(route('settings.dictionary.store'), newItem, {
        onSuccess: () => {
            newItem.label = '';
            processing.value = false;
        },
        preserveScroll: true
    });
};

const toggleActive = (item) => {
    router.put(route('settings.dictionary.update', item.id), {
        is_active: !item.is_active
    }, { preserveScroll: true });
};

const deleteItem = (item) => {
    if (confirm('Are you sure you want to remove this item?')) {
        router.delete(route('settings.dictionary.destroy', item.id), {
            preserveScroll: true
        });
    }
};
</script>
