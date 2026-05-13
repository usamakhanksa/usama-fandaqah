<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { 
    LocalOffer as LocalOfferIcon, 
    Add as AddIcon, 
    Edit as EditIcon, 
    Delete as DeleteIcon,
    Visibility as VisibilityIcon,
    ToggleOn as ToggleOnIcon,
    ToggleOff as ToggleOffIcon
} from '@mui/icons-material-runtime';
import { ref } from 'vue';

const props = defineProps({
    offers: Object
});

const toggleForm = useForm({});

const toggleStatus = (id) => {
    toggleForm.post(route('marketing.offers.toggle', id));
};

</script>

<template>
    <Head title="Marketing Offers" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight flex items-center gap-2">
                    <LocalOfferIcon class="text-orange-500" />
                    Marketing Offers
                </h2>
                <Link
                    :href="route('marketing.offers.create')"
                    class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition-all"
                >
                    <AddIcon />
                    Create Offer
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div v-for="offer in offers.data" :key="offer.id" 
                        class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-100 hover:shadow-md transition-shadow relative"
                    >
                        <div class="h-32 bg-gradient-to-r from-orange-400 to-rose-500 flex items-center justify-center text-white">
                            <span class="text-3xl font-bold">
                                {{ offer.offer_type === 'percentage_discount' ? offer.discount_value + '%' : 'SAR ' + offer.discount_value }}
                                OFF
                            </span>
                        </div>
                        <div class="p-6">
                            <div class="flex justify-between items-start mb-2">
                                <h3 class="text-lg font-bold text-gray-900">{{ offer.name }}</h3>
                                <button @click="toggleStatus(offer.id)" 
                                    :class="offer.is_active ? 'text-green-500' : 'text-gray-400'"
                                    class="transition-colors"
                                >
                                    <ToggleOnIcon v-if="offer.is_active" fontSize="large" />
                                    <ToggleOffIcon v-else fontSize="large" />
                                </button>
                            </div>
                            <p class="text-gray-600 text-sm mb-4 line-clamp-2">{{ offer.description || 'No description provided.' }}</p>
                            
                            <div class="flex items-center gap-4 text-xs text-gray-500 mb-6">
                                <span class="bg-gray-100 px-2 py-1 rounded">Min Nights: {{ offer.min_nights }}</span>
                                <span class="bg-gray-100 px-2 py-1 rounded">Valid Until: {{ offer.valid_to }}</span>
                            </div>

                            <div class="flex justify-end gap-2 border-t pt-4">
                                <Link :href="route('marketing.offers.edit', offer.id)" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">
                                    <EditIcon />
                                </Link>
                                <button class="p-2 text-rose-600 hover:bg-rose-50 rounded-lg transition-colors">
                                    <DeleteIcon />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div v-if="offers.data.length === 0" class="bg-white p-12 text-center rounded-xl shadow-sm border">
                    <LocalOfferIcon class="text-gray-300 !text-6xl mb-4" />
                    <p class="text-gray-500">No offers found. Create your first marketing offer!</p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
