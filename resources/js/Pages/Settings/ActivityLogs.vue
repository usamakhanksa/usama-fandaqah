<template>
    <div class="space-y-6">
        <div class="bg-white rounded-2xl shadow-sm border border-[#8f9793]/10 overflow-hidden">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-[#2a273c] text-[#fbcdab]">
                        <th class="px-6 py-4 text-xs uppercase tracking-wider font-bold">User</th>
                        <th class="px-6 py-4 text-xs uppercase tracking-wider font-bold">Module</th>
                        <th class="px-6 py-4 text-xs uppercase tracking-wider font-bold">Action</th>
                        <th class="px-6 py-4 text-xs uppercase tracking-wider font-bold">Description</th>
                        <th class="px-6 py-4 text-xs uppercase tracking-wider font-bold text-right">Time</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#8f9793]/10">
                    <tr v-for="log in data.logs.data" :key="log.id" class="hover:bg-[#f2f0eb]/30 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="w-8 h-8 rounded-full bg-[#e95a54]/10 text-[#e95a54] flex items-center justify-center font-bold text-xs mr-3">
                                    {{ log.user?.name.charAt(0) }}
                                </div>
                                <span class="text-sm font-medium text-[#2a273c]">{{ log.user?.name }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 rounded-full bg-[#2a273c]/5 text-[#2a273c] text-[10px] font-bold uppercase">
                                {{ log.module }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-sm font-semibold" :class="getActionClass(log.action)">
                                {{ log.action.toUpperCase() }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-xs text-[#8f9793] max-w-xs truncate">{{ log.description }}</p>
                        </td>
                        <td class="px-6 py-4 text-right text-xs text-[#8f9793]">
                            {{ formatTime(log.created_at) }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div v-if="data.logs.links.length > 3" class="flex justify-center space-x-2">
            <Link
                v-for="(link, k) in data.logs.links"
                :key="k"
                :href="link.url || '#'"
                v-html="link.label"
                class="px-4 py-2 rounded-xl text-sm font-medium transition-all"
                :class="[
                    link.active ? 'bg-[#e95a54] text-white shadow-lg shadow-[#e95a54]/20' : 'bg-white text-[#8f9793] hover:bg-[#2a273c] hover:text-[#fbcdab]',
                    !link.url ? 'opacity-50 cursor-not-allowed' : ''
                ]"
            />
        </div>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    data: Object
});

const getActionClass = (action) => {
    switch (action) {
        case 'create': return 'text-green-600';
        case 'update': return 'text-[#e95a54]';
        case 'delete': return 'text-red-600';
        default: return 'text-[#2a273c]';
    }
};

const formatTime = (time) => {
    return new Date(time).toLocaleString();
};
</script>
