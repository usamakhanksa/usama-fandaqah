<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { 
    ArrowLeftIcon, 
    PencilSquareIcon, 
    TrashIcon,
    UserIcon,
    PhoneIcon,
    EnvelopeIcon,
    MapPinIcon,
    BriefcaseIcon,
    CalendarIcon,
    CurrencyDollarIcon,
    TrophyIcon,
    DevicePhoneMobileIcon,
    ClockIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
    client: Object
})

const deleteProfile = () => {
    if (confirm('Are you sure you want to delete this profile?')) {
        router.delete(route('client-relations.destroy', props.client.id))
    }
}

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric'
    })
}

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-SA', { style: 'currency', currency: 'SAR' }).format(amount)
}
</script>

<template>
    <Head :title="`${client.first_name} ${client.last_name} | Fandaqah`" />

    <div class="min-h-screen bg-[#f2f0eb] p-6">
        <div class="max-w-7xl mx-auto">
            <!-- Breadcrumbs & Actions -->
            <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <Link :href="route('client-relations.index')" class="text-[#8f9793] hover:text-[#2a273c] flex items-center gap-2 mb-2 transition">
                        <ArrowLeftIcon class="w-4 h-4" />
                        Back to Profiles
                    </Link>
                    <h1 class="text-3xl font-bold text-[#2a273c]">{{ client.first_name }} {{ client.last_name }}</h1>
                </div>
                <div class="flex gap-4">
                    <button @click="deleteProfile" class="px-6 py-2 border-2 border-red-200 text-red-500 rounded-xl font-bold hover:bg-red-50 transition flex items-center gap-2">
                        <TrashIcon class="w-5 h-5" />
                        Delete
                    </button>
                    <Link :href="route('client-relations.edit', client.id)" class="px-6 py-2 bg-[#2a273c] text-white rounded-xl font-bold hover:bg-opacity-90 transition shadow-lg flex items-center gap-2">
                        <PencilSquareIcon class="w-5 h-5" />
                        Edit Profile
                    </Link>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Left Column: Profile Card -->
                <div class="lg:col-span-1 space-y-8">
                    <div class="bg-white rounded-3xl shadow-xl overflow-hidden border border-[#fbcdab]/20">
                        <div class="bg-gradient-to-br from-[#2a273c] to-[#e95a54] p-8 text-center">
                            <div class="w-24 h-24 bg-white/20 backdrop-blur-md rounded-full mx-auto mb-4 flex items-center justify-center border-4 border-white/30">
                                <UserIcon class="w-12 h-12 text-white" />
                            </div>
                            <h2 class="text-xl font-bold text-white">{{ client.first_name }} {{ client.last_name }}</h2>
                            <p class="text-white/70 text-sm capitalize">{{ client.type }}</p>
                        </div>
                        <div class="p-8 space-y-6">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-xl bg-[#f2f0eb] flex items-center justify-center text-[#2a273c]">
                                    <EnvelopeIcon class="w-5 h-5" />
                                </div>
                                <div>
                                    <p class="text-xs text-[#8f9793] uppercase font-bold tracking-wider">Email</p>
                                    <p class="text-[#2a273c] font-medium">{{ client.email }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-xl bg-[#f2f0eb] flex items-center justify-center text-[#2a273c]">
                                    <PhoneIcon class="w-5 h-5" />
                                </div>
                                <div>
                                    <p class="text-xs text-[#8f9793] uppercase font-bold tracking-wider">Phone</p>
                                    <p class="text-[#2a273c] font-medium">{{ client.phone }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-xl bg-[#f2f0eb] flex items-center justify-center text-[#2a273c]">
                                    <MapPinIcon class="w-5 h-5" />
                                </div>
                                <div>
                                    <p class="text-xs text-[#8f9793] uppercase font-bold tracking-wider">Location</p>
                                    <p class="text-[#2a273c] font-medium">{{ client.city }}, {{ client.address }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Membership Card -->
                    <div v-if="client.membership" class="bg-[#2a273c] rounded-3xl p-8 text-white shadow-xl relative overflow-hidden">
                        <div class="absolute -right-10 -top-10 w-40 h-40 bg-white/10 rounded-full blur-3xl"></div>
                        <div class="relative z-10">
                            <div class="flex justify-between items-start mb-6">
                                <TrophyIcon class="w-10 h-10 text-[#fbcdab]" />
                                <span class="bg-[#fbcdab] text-[#2a273c] px-3 py-1 rounded-full text-xs font-black uppercase tracking-tighter">
                                    {{ client.membership.tier }}
                                </span>
                            </div>
                            <p class="text-white/60 text-sm font-medium">Available Points</p>
                            <h3 class="text-4xl font-black mb-6">{{ client.membership.points }} <span class="text-lg font-normal text-white/50">pts</span></h3>
                            <div class="space-y-3">
                                <div class="flex justify-between text-sm">
                                    <span class="text-white/60">Joined:</span>
                                    <span>{{ formatDate(client.membership.joined_at) }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-white/60">Expires:</span>
                                    <span>{{ formatDate(client.membership.expires_at) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Activities & Sales -->
                <div class="lg:col-span-2 space-y-8">
                    <!-- Activities -->
                    <div class="bg-white rounded-3xl shadow-xl p-8 border border-[#fbcdab]/20">
                        <h3 class="text-xl font-bold text-[#2a273c] mb-6 flex items-center gap-3">
                            <ClockIcon class="w-6 h-6 text-[#e95a54]" />
                            Recent Activities
                        </h3>
                        <div v-if="client.activities.length" class="space-y-6">
                            <div v-for="activity in client.activities" :key="activity.id" class="flex gap-4 relative">
                                <div class="flex flex-col items-center">
                                    <div class="w-10 h-10 rounded-full bg-[#f2f0eb] flex items-center justify-center shrink-0">
                                        <component :is="DevicePhoneMobileIcon" class="w-5 h-5 text-[#2a273c]" />
                                    </div>
                                    <div class="w-0.5 h-full bg-[#f2f0eb] my-2"></div>
                                </div>
                                <div class="pb-6">
                                    <div class="flex items-center gap-3 mb-1">
                                        <span class="font-bold text-[#2a273c]">{{ activity.subject }}</span>
                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-widest border" :class="activity.status === 'completed' ? 'bg-green-50 text-green-600 border-green-200' : 'bg-yellow-50 text-yellow-600 border-yellow-200'">
                                            {{ activity.status }}
                                        </span>
                                    </div>
                                    <p class="text-sm text-[#8f9793]">{{ activity.description }}</p>
                                    <div class="flex items-center gap-2 mt-2 text-xs text-[#8f9793]">
                                        <CalendarIcon class="w-3.5 h-3.5" />
                                        {{ formatDate(activity.scheduled_at) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center py-10 text-[#8f9793]">No activities recorded.</div>
                    </div>

                    <!-- Sales -->
                    <div class="bg-white rounded-3xl shadow-xl p-8 border border-[#fbcdab]/20">
                        <h3 class="text-xl font-bold text-[#2a273c] mb-6 flex items-center gap-3">
                            <BriefcaseIcon class="w-6 h-6 text-[#e95a54]" />
                            Transaction History
                        </h3>
                        <div v-if="client.sales.length" class="overflow-x-auto">
                            <table class="w-full text-left">
                                <thead>
                                    <tr class="text-[#8f9793] text-xs uppercase font-bold tracking-widest border-b border-[#fbcdab]/10">
                                        <th class="pb-4">Property Ref</th>
                                        <th class="pb-4">Amount</th>
                                        <th class="pb-4 text-center">Status</th>
                                        <th class="pb-4 text-right">Closed At</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-[#fbcdab]/10">
                                    <tr v-for="sale in client.sales" :key="sale.id">
                                        <td class="py-4 font-bold text-[#2a273c]">{{ sale.property_reference }}</td>
                                        <td class="py-4 font-bold text-[#e95a54]">{{ formatCurrency(sale.amount) }}</td>
                                        <td class="py-4 text-center">
                                            <span class="px-3 py-1 rounded-full text-xs font-bold" :class="sale.status === 'closed' ? 'bg-green-100 text-green-700' : 'bg-orange-100 text-orange-700'">
                                                {{ sale.status }}
                                            </span>
                                        </td>
                                        <td class="py-4 text-right text-sm text-[#8f9793]">{{ sale.closed_at ? formatDate(sale.closed_at) : 'N/A' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div v-else class="text-center py-10 text-[#8f9793]">No sales transactions found.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
