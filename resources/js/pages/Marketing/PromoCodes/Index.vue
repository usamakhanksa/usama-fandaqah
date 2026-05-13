<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { 
    ConfirmationNumber as PromoIcon, 
    Add as AddIcon, 
    Edit as EditIcon, 
    ContentCopy as CopyIcon,
    CheckCircle as ActiveIcon,
    Cancel as InactiveIcon
} from '@mui/icons-material-runtime';

const props = defineProps({
    promoCodes: Object
});

const copyToClipboard = (code) => {
    navigator.clipboard.writeText(code);
    alert('Code copied: ' + code);
};

</script>

<template>
    <Head title="Promo Codes" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight flex items-center gap-2">
                    <PromoIcon class="text-blue-500" />
                    Promo Codes
                </h2>
                <Link
                    :href="route('marketing.promo-codes.create')"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition-all shadow-sm shadow-blue-200"
                >
                    <AddIcon />
                    Generate Code
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl border border-gray-100">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50/50 text-gray-500 text-xs uppercase tracking-wider">
                                <th class="px-6 py-4 font-semibold">Code</th>
                                <th class="px-6 py-4 font-semibold">Name</th>
                                <th class="px-6 py-4 font-semibold">Discount</th>
                                <th class="px-6 py-4 font-semibold">Usage</th>
                                <th class="px-6 py-4 font-semibold">Validity</th>
                                <th class="px-6 py-4 font-semibold text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="promo in promoCodes.data" :key="promo.id" class="hover:bg-blue-50/30 transition-colors group">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <code class="bg-blue-50 text-blue-700 px-3 py-1 rounded-md font-mono font-bold text-sm border border-blue-100">
                                            {{ promo.code }}
                                        </code>
                                        <button @click="copyToClipboard(promo.code)" class="text-gray-400 hover:text-blue-600 transition-colors opacity-0 group-hover:opacity-100">
                                            <CopyIcon fontSize="small" />
                                        </button>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900">{{ promo.name }}</div>
                                    <div class="text-xs text-gray-500 line-clamp-1">{{ promo.description || 'Global discount' }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        {{ promo.discount_type === 'percentage' ? promo.discount_value + '%' : 'SAR ' + promo.discount_value }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900">{{ promo.current_usage }} / {{ promo.max_usage || '∞' }}</div>
                                    <div class="w-24 h-1.5 bg-gray-100 rounded-full mt-1 overflow-hidden">
                                        <div class="h-full bg-blue-500 rounded-full" :style="{ width: promo.max_usage ? (promo.current_usage / promo.max_usage * 100) + '%' : '0%' }"></div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-xs text-gray-600">{{ promo.valid_from }} to</div>
                                    <div class="text-xs text-gray-600">{{ promo.valid_to }}</div>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-1">
                                        <Link :href="route('marketing.promo-codes.edit', promo.id)" class="p-2 text-gray-400 hover:text-blue-600 transition-colors">
                                            <EditIcon fontSize="small" />
                                        </Link>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
