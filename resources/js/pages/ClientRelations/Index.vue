<script setup>
import { ref, watch } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { 
    MagnifyingGlassIcon, 
    PlusIcon, 
    FunnelIcon,
    UserGroupIcon,
    ArrowTopRightOnSquareIcon,
    EllipsisVerticalIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
    profiles: Object,
    filters: Object
})

const search = ref(props.filters.search || '')
const type = ref(props.filters.type || '')

watch([search, type], ([s, t]) => {
    router.get(route('client-relations.index'), { search: s, type: t }, { preserveState: true, replace: true })
})

const deleteProfile = (id) => {
    if (confirm('Are you sure you want to delete this profile?')) {
        router.delete(route('client-relations.destroy', id))
    }
}
</script>

<template>
    <Head title="Client Relations | Fandaqah" />

    <div class="min-h-screen bg-[#f2f0eb] p-6">
        <div class="max-w-7xl mx-auto">
            <!-- Header -->
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-[#2a273c]">Client Relations</h1>
                    <p class="text-[#8f9793]">Manage guest profiles, loyalty, and engagement</p>
                </div>
                <Link 
                    :href="route('client-relations.create')"
                    class="bg-[#e95a54] text-white px-6 py-3 rounded-lg font-semibold hover:bg-opacity-90 transition shadow-lg flex items-center gap-2"
                >
                    <PlusIcon class="w-5 h-5" />
                    New Profile
                </Link>
            </div>

            <!-- Stats Overview -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div v-for="(stat, i) in [
                    { label: 'Total Profiles', value: profiles.total, icon: UserGroupIcon, color: '#2a273c' },
                    { label: 'Investors', value: profiles.data.filter(p => p.type === 'investor').length, icon: UserGroupIcon, color: '#e95a54' },
                    { label: 'Recent Activities', value: 24, icon: UserGroupIcon, color: '#fbcdab' },
                    { label: 'Active Members', value: profiles.data.filter(p => p.membership).length, icon: UserGroupIcon, color: '#8f9793' }
                ]" :key="i" class="bg-white p-6 rounded-xl shadow-sm border border-[#fbcdab]/20">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-sm font-medium text-[#8f9793]">{{ stat.label }}</p>
                            <h3 class="text-2xl font-bold text-[#2a273c] mt-1">{{ stat.value }}</h3>
                        </div>
                        <component :is="stat.icon" class="w-6 h-6" :style="{ color: stat.color }" />
                    </div>
                </div>
            </div>

            <!-- Filters & Search -->
            <div class="bg-white p-4 rounded-xl shadow-sm mb-6 flex flex-wrap gap-4 items-center justify-between border border-[#fbcdab]/20">
                <div class="relative flex-1 max-w-md">
                    <MagnifyingGlassIcon class="w-5 h-5 absolute left-3 top-1/2 -translate-y-1/2 text-[#8f9793]" />
                    <input 
                        v-model="search"
                        type="text" 
                        placeholder="Search by name, email or phone..." 
                        class="w-full pl-10 pr-4 py-2 bg-[#f2f0eb]/50 border-none rounded-lg focus:ring-2 focus:ring-[#e95a54] placeholder-[#8f9793]"
                    />
                </div>
                <div class="flex gap-4">
                    <select v-model="type" class="bg-[#f2f0eb]/50 border-none rounded-lg focus:ring-2 focus:ring-[#e95a54] text-[#2a273c]">
                        <option value="">All Types</option>
                        <option value="tenant">Tenant</option>
                        <option value="buyer">Buyer</option>
                        <option value="investor">Investor</option>
                    </select>
                </div>
            </div>

            <!-- Profiles Table -->
            <div class="bg-white rounded-xl shadow-sm overflow-hidden border border-[#fbcdab]/20">
                <table class="w-full text-left">
                    <thead class="bg-[#2a273c] text-white">
                        <tr>
                            <th class="px-6 py-4 font-semibold uppercase text-xs tracking-wider">Client Name</th>
                            <th class="px-6 py-4 font-semibold uppercase text-xs tracking-wider">Type</th>
                            <th class="px-6 py-4 font-semibold uppercase text-xs tracking-wider">Contact</th>
                            <th class="px-6 py-4 font-semibold uppercase text-xs tracking-wider">Membership</th>
                            <th class="px-6 py-4 font-semibold uppercase text-xs tracking-wider text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#fbcdab]/10">
                        <tr v-for="profile in profiles.data" :key="profile.id" class="hover:bg-[#f2f0eb]/30 transition group">
                            <td class="px-6 py-4">
                                <Link :href="route('client-relations.show', profile.id)" class="block">
                                    <div class="font-bold text-[#2a273c] group-hover:text-[#e95a54]">{{ profile.first_name }} {{ profile.last_name }}</div>
                                    <div class="text-xs text-[#8f9793]">ID: {{ profile.national_id }}</div>
                                </Link>
                            </td>
                            <td class="px-6 py-4 text-[#2a273c]">
                                <span 
                                    class="px-3 py-1 rounded-full text-xs font-bold border"
                                    :class="{
                                        'bg-[#fbcdab]/20 text-[#e95a54] border-[#e95a54]/20': profile.type === 'investor',
                                        'bg-[#8f9793]/10 text-[#8f9793] border-[#8f9793]/20': profile.type === 'tenant',
                                        'bg-[#2a273c]/5 text-[#2a273c] border-[#2a273c]/20': profile.type === 'buyer'
                                    }"
                                >
                                    {{ profile.type }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-[#2a273c]">{{ profile.email }}</div>
                                <div class="text-xs text-[#8f9793]">{{ profile.phone }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div v-if="profile.membership" class="flex items-center gap-2">
                                    <div class="w-2 h-2 rounded-full" :class="profile.membership.tier === 'platinum' ? 'bg-[#e95a54]' : 'bg-[#fbcdab]'"></div>
                                    <span class="text-sm font-medium capitalize text-[#2a273c]">{{ profile.membership.tier }}</span>
                                    <span class="text-xs text-[#8f9793] opacity-0 group-hover:opacity-100 transition">({{ profile.membership.points }} pts)</span>
                                </div>
                                <div v-else class="text-xs text-[#8f9793]">None</div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-3 opacity-0 group-hover:opacity-100 transition">
                                    <Link :href="route('client-relations.show', profile.id)" class="text-[#8f9793] hover:text-[#e95a54]">
                                        <ArrowTopRightOnSquareIcon class="w-5 h-5" />
                                    </Link>
                                    <button @click="deleteProfile(profile.id)" class="text-[#8f9793] hover:text-red-500">
                                        <EllipsisVerticalIcon class="w-5 h-5" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
                
                <!-- Pagination -->
                <div v-if="profiles.data.length" class="px-6 py-4 bg-[#f2f0eb]/20 border-t border-[#fbcdab]/20 flex items-center justify-between">
                    <p class="text-sm text-[#8f9793]">Showing {{ profiles.from }} to {{ profiles.to }} of {{ profiles.total }} clients</p>
                    <div class="flex gap-2">
                        <Link 
                            v-for="link in profiles.links" 
                            :key="link.label" 
                            :href="link.url" 
                            v-html="link.label"
                            class="px-4 py-2 rounded-lg text-sm transition"
                            :class="link.active ? 'bg-[#e95a54] text-white shadow-md' : 'text-[#8f9793] hover:bg-[#fbcdab]/20'"
                        />
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="!profiles.data.length" class="py-20 text-center">
                    <div class="bg-[#f2f0eb] w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-4">
                        <UserGroupIcon class="w-10 h-10 text-[#8f9793]" />
                    </div>
                    <h3 class="text-xl font-bold text-[#2a273c]">No clients found</h3>
                    <p class="text-[#8f9793] mt-1 mb-6">No data yet – Add your first client to get started</p>
                    <Link :href="route('client-relations.create')" class="bg-[#e95a54] text-white px-8 py-3 rounded-xl font-bold shadow-lg hover:shadow-xl transition">
                        Add New Client Profile
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>
