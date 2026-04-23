<template>
    <div class="min-h-screen bg-[#f2f0eb]">
        <!-- Inventory Header -->
        <header class="bg-[#2a273c] text-white shadow-lg border-b border-[#e95a54]/20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col py-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-3xl font-bold tracking-tight text-[#fbcdab]">Inventory & Rooms</h1>
                            <p class="mt-1 text-[#8f9793]">Property Management & Operational Controls</p>
                        </div>
                        <div class="hidden sm:flex space-x-2">
                            <div class="px-4 py-2 bg-[#e95a54]/10 border border-[#e95a54]/30 rounded-lg">
                                <span class="text-sm text-[#fbcdab]">Property:</span>
                                <span class="ml-2 font-semibold">Al-Fandaqah Grand</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Sub Navigation -->
                    <nav class="mt-8 flex space-x-8 border-b border-white/10">
                        <Link 
                            v-for="item in nav" 
                            :key="item.name"
                            :href="item.href"
                            :class="[
                                $page.component.startsWith(item.component)
                                    ? 'border-[#e95a54] text-[#e95a54]'
                                    : 'border-transparent text-[#8f9793] hover:text-white hover:border-white/30',
                                'pb-4 px-1 border-b-2 font-medium text-sm transition-all duration-200'
                            ]"
                        >
                            {{ item.name }}
                        </Link>
                    </nav>
                </div>
            </div>
        </header>

        <main class="py-10">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <slot />
            </div>
        </main>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';

const nav = [
    { name: 'Room Management', href: '/rooms', component: 'Inventory/Rooms' },
    { name: 'Housekeeping', href: '/housekeeping', component: 'Inventory/Housekeeping' },
    { name: 'Restrictions', href: '/room-restrictions', component: 'Inventory/Restrictions' },
];
</script>
