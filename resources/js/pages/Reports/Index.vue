<script setup>
import Layout from '@/Layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    activeReport: String,
    dateRange: String,
    reportData: Object,
    savedReports: Array
});

const reportTypes = [
    { id: 'daily', name: 'Daily Report', icon: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z' },
    { id: 'occupancy', name: 'Occupancy Ratio', icon: 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4' },
    { id: 'revenues', name: 'Revenue & Taxes', icon: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z' },
    { id: 'cleaning', name: 'Cleaning Movement', icon: 'M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.628.293a2 2 0 01-.82.21H8a2 2 0 00-2 2v3m9-1.8c.27-.3.74-.3 1.01 0l3 3m0 0l-3 3m3-3H12' },
    { id: 'maintenance', name: 'Maintenance Log', icon: 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z' },
    { id: 'customers', name: 'Customer Analytics', icon: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z' },
    { id: 'contracts', name: 'Staff Contracts', icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z' },
];

const changeReport = (id) => {
    router.visit(route('analytics.index'), {
        data: { report: id, date_range: props.dateRange },
        preserveState: true,
        preserveScroll: true
    });
};

const changeRange = (range) => {
    router.visit(route('analytics.index'), {
        data: { report: props.activeReport, date_range: range },
        preserveState: true,
        preserveScroll: true
    });
};

const formatCurrency = (amt) => {
    return new Intl.NumberFormat('en-SA', { style: 'currency', currency: 'SAR' }).format(amt);
};

</script>

<template>
    <Head title="Reports & Analytics" />

    <Layout>
        <div class="p-6 bg-[#f2f0eb] min-h-screen">
            <!-- Header -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-[#2a273c]">Intelligence Hub</h1>
                    <p class="text-[#8f9793]">Strategic insights for Fandaqah Operations</p>
                </div>
                <div class="flex gap-2">
                    <Link :href="route('analytics.safe')" 
                          class="px-4 py-2 bg-[#e95a54] text-white rounded-xl shadow-lg hover:bg-opacity-90 transition-all font-medium flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        Safe Management
                    </Link>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                <!-- Sidebar Navigation -->
                <div class="lg:col-span-3 space-y-6">
                    <div class="bg-white p-4 rounded-2xl shadow-sm border border-[#fbcdab]/30">
                        <h3 class="text-xs font-bold text-[#8f9793] uppercase tracking-widest mb-4 px-2">Report Modules</h3>
                        <nav class="space-y-1">
                            <button v-for="report in reportTypes" 
                                    :key="report.id"
                                    @click="changeReport(report.id)"
                                    :class="[
                                        'w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all group',
                                        activeReport === report.id 
                                            ? 'bg-[#2a273c] text-white shadow-md shadow-[#2a273c]/20' 
                                            : 'text-[#8f9793] hover:bg-[#fbcdab]/10 hover:text-[#2a273c]'
                                    ]">
                                <svg class="w-5 h-5 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="report.icon" />
                                </svg>
                                {{ report.name }}
                            </button>
                        </nav>
                    </div>

                    <div class="bg-[#2a273c] p-6 rounded-2xl text-white shadow-xl relative overflow-hidden">
                        <div class="relative z-10">
                            <h4 class="text-lg font-bold mb-2">Favorites</h4>
                            <div v-if="savedReports.length" class="space-y-3">
                                <button v-for="saved in savedReports" :key="saved.id" 
                                        @click="changeReport(saved.report_type)"
                                        class="w-full text-left text-sm text-[#8f9793] hover:text-[#fbcdab] transition-colors flex items-center gap-2">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#e95a54]"></span>
                                    {{ saved.name }}
                                </button>
                            </div>
                            <p v-else class="text-xs text-[#8f9793]">No saved reports yet.</p>
                        </div>
                        <div class="absolute -right-4 -bottom-4 opacity-10">
                            <svg class="w-24 h-24 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z" /></svg>
                        </div>
                    </div>
                </div>

                <!-- Main Report Display -->
                <div class="lg:col-span-9 space-y-6">
                    <!-- Filters & Controls -->
                    <div class="bg-white p-4 rounded-2xl shadow-sm flex flex-wrap items-center justify-between gap-4 border border-[#fbcdab]/30">
                        <div class="flex bg-[#f2f0eb] p-1 rounded-xl">
                            <button v-for="range in ['day', 'week', 'month', 'year']" 
                                    :key="range"
                                    @click="changeRange(range)"
                                    :class="[
                                        'px-4 py-1.5 rounded-lg text-xs font-bold uppercase tracking-wider transition-all',
                                        dateRange === range ? 'bg-white text-[#2a273c] shadow-sm' : 'text-[#8f9793] hover:text-[#2a273c]'
                                    ]">
                                {{ range }}
                            </button>
                        </div>
                        <div class="flex gap-2">
                            <button class="p-2 text-[#8f9793] hover:bg-[#f2f0eb] rounded-lg transition-colors border border-transparent hover:border-[#fbcdab]">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                            </button>
                            <button class="p-2 text-[#8f9793] hover:bg-[#f2f0eb] rounded-lg transition-colors border border-transparent hover:border-[#fbcdab]">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                            </button>
                        </div>
                    </div>

                    <!-- Dynamic Report Content -->
                    <div class="animate-in fade-in slide-in-from-bottom-4 duration-500">
                        <!-- Revenue Report -->
                        <div v-if="activeReport === 'revenues'" class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="bg-white p-6 rounded-2xl shadow-sm border-l-4 border-[#e95a54]">
                                    <p class="text-xs font-bold text-[#8f9793] uppercase mb-1">Gross Revenue</p>
                                    <h4 class="text-2xl font-bold text-[#2a273c]">{{ formatCurrency(reportData.total_revenue) }}</h4>
                                </div>
                                <div class="bg-white p-6 rounded-2xl shadow-sm border-l-4 border-[#fbcdab]">
                                    <p class="text-xs font-bold text-[#8f9793] uppercase mb-1">VAT (15%)</p>
                                    <h4 class="text-2xl font-bold text-[#2a273c]">{{ formatCurrency(reportData.total_taxes) }}</h4>
                                </div>
                                <div class="bg-[#2a273c] p-6 rounded-2xl shadow-lg border-l-4 border-white">
                                    <p class="text-xs font-bold text-[#8f9793] uppercase mb-1 text-opacity-80">Net Revenue</p>
                                    <h4 class="text-2xl font-bold text-white">{{ formatCurrency(reportData.net_revenue) }}</h4>
                                </div>
                            </div>
                            
                            <div class="bg-white rounded-2xl shadow-sm overflow-hidden border border-[#fbcdab]/30">
                                <table class="w-full text-left border-collapse">
                                    <thead>
                                        <tr class="bg-[#f2f0eb]/50">
                                            <th class="p-4 text-xs font-bold text-[#8f9793] uppercase tracking-wider">Service Category</th>
                                            <th class="p-4 text-xs font-bold text-[#8f9793] uppercase tracking-wider text-right">Gross Amount</th>
                                            <th class="p-4 text-xs font-bold text-[#8f9793] uppercase tracking-wider text-right">Tax (VAT)</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-[#f2f0eb]">
                                        <tr v-for="item in reportData.breakdown" :key="item.category" class="hover:bg-[#f2f0eb]/10 transition-colors">
                                            <td class="p-4 font-medium text-[#2a273c]">{{ item.category }}</td>
                                            <td class="p-4 text-right text-[#2a273c]">{{ formatCurrency(item.amount) }}</td>
                                            <td class="p-4 text-right text-[#8f9793]">{{ formatCurrency(item.tax) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Occupancy Report -->
                        <div v-else-if="activeReport === 'occupancy'" class="space-y-6">
                            <div class="bg-white p-8 rounded-2xl shadow-sm border border-[#fbcdab]/30 flex flex-col items-center">
                                <div class="relative w-48 h-48 mb-6">
                                    <svg class="w-full h-full transform -rotate-90" viewBox="0 0 36 36">
                                        <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="#f2f0eb" stroke-width="3" />
                                        <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" :stroke="'#e95a54'" stroke-width="3" :stroke-dasharray="`${reportData.overall_ratio}, 100`" />
                                    </svg>
                                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                                        <span class="text-4xl font-black text-[#2a273c]">{{ reportData.overall_ratio }}%</span>
                                        <span class="text-[10px] font-bold text-[#8f9793] uppercase">Occupancy</span>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-8 w-full max-w-sm text-center">
                                    <div>
                                        <p class="text-sm font-bold text-[#2a273c]">{{ reportData.occupied_rooms }}</p>
                                        <p class="text-[10px] text-[#8f9793] uppercase">Occupied</p>
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-[#2a273c]">{{ reportData.available_rooms }}</p>
                                        <p class="text-[10px] text-[#8f9793] uppercase">Total Keys</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div v-for="item in reportData.breakdown" :key="item.type" class="bg-white p-6 rounded-2xl shadow-sm border border-[#fbcdab]/20">
                                    <h5 class="font-bold text-[#2a273c] mb-4">{{ item.type }}</h5>
                                    <div class="flex justify-between items-end mb-2">
                                        <span class="text-xs text-[#8f9793]">{{ item.occupied }} / {{ item.total }} Rooms</span>
                                        <span class="text-sm font-bold text-[#e95a54]">{{ item.ratio }}%</span>
                                    </div>
                                    <div class="w-full bg-[#f2f0eb] h-2 rounded-full overflow-hidden">
                                        <div class="bg-[#e95a54] h-full transition-all duration-1000" :style="{ width: item.ratio + '%' }"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Fallback / Simple Reports -->
                        <div v-else class="bg-white p-12 rounded-3xl shadow-sm border border-dashed border-[#fbcdab] text-center">
                            <div class="w-16 h-16 bg-[#fbcdab]/20 rounded-full flex items-center justify-center mx-auto mb-6">
                                <svg class="w-8 h-8 text-[#e95a54]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" /></svg>
                            </div>
                            <h3 class="text-xl font-bold text-[#2a273c] mb-2">{{ reportTypes.find(r => r.id === activeReport)?.name }}</h3>
                            <pre class="text-sm text-[#8f9793] text-left bg-[#f2f0eb] p-6 rounded-xl overflow-auto max-h-96">{{ JSON.stringify(reportData, null, 2) }}</pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Layout>
</template>

<style scoped>
.animate-in {
    animation: fadeIn 0.5s ease-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
