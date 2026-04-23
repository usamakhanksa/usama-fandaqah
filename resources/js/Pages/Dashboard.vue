<script setup>
import { ref, onMounted } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { 
  BuildingOfficeIcon, 
  UserGroupIcon, 
  CurrencyDollarIcon, 
  WrenchScrewdriverIcon,
  BellIcon,
  InformationCircleIcon,
  ExclamationTriangleIcon,
  BoltIcon
} from '@heroicons/vue/24/outline';
import axios from 'axios';

const stats = ref([
  { name: 'Total Properties', value: '0', icon: BuildingOfficeIcon, change: '+4.75%', changeType: 'positive' },
  { name: 'Occupancy Rate', value: '0%', icon: UserGroupIcon, change: '+12.5%', changeType: 'positive' },
  { name: 'Monthly Revenue', value: 'SAR 0', icon: CurrencyDollarIcon, change: '+5.4%', changeType: 'positive' },
  { name: 'Pending Maintenance', value: '0', icon: WrenchScrewdriverIcon, change: '-2', changeType: 'negative' },
]);

const notices = ref([]);
const loading = ref(true);

const fetchDashboardData = async () => {
  try {
    const response = await axios.get('/api/dashboard/kpis');
    const { metrics, notices: dashboardNotices } = response.data;
    
    stats.value = [
      { name: 'Total Properties', value: metrics.total_properties.toString(), icon: BuildingOfficeIcon, change: '+4.75%', changeType: 'positive' },
      { name: 'Occupancy Rate', value: `${metrics.occupancy_rate}%`, icon: UserGroupIcon, change: '+12.5%', changeType: 'positive' },
      { name: 'Monthly Revenue', value: `SAR ${(metrics.monthly_revenue / 1000).toFixed(0)}k`, icon: CurrencyDollarIcon, change: '+5.4%', changeType: 'positive' },
      { name: 'Pending Maintenance', value: metrics.pending_maintenance.toString(), icon: WrenchScrewdriverIcon, change: '-2', changeType: 'negative' },
    ];
    
    notices.value = dashboardNotices;
  } catch (error) {
    console.error('Failed to fetch dashboard data:', error);
  } finally {
    loading.value = false;
  }
};

const getNoticeIcon = (type) => {
  switch (type) {
    case 'urgent': return BoltIcon;
    case 'warning': return ExclamationTriangleIcon;
    default: return InformationCircleIcon;
  }
};

const getNoticeColor = (type) => {
  switch (type) {
    case 'urgent': return 'text-coral';
    case 'warning': return 'text-amber-500';
    default: return 'text-navy';
  }
};

const getNoticeBg = (type) => {
  switch (type) {
    case 'urgent': return 'bg-coral/10';
    case 'warning': return 'bg-amber-50';
    default: return 'bg-slate-100';
  }
};

onMounted(() => {
  fetchDashboardData();
});
</script>

<template>
  <AppLayout title="Dashboard">
    <div class="px-4 sm:px-6 lg:px-8 py-8 space-y-8">
      <!-- Header -->
      <div class="md:flex md:items-center md:justify-between">
        <div class="min-w-0 flex-1">
          <h2 class="text-3xl font-extrabold leading-7 text-navy sm:truncate sm:text-4xl sm:tracking-tight">
            Fandaqah Overview
          </h2>
          <p class="mt-2 text-sm text-slate-500">
            Real-time insights for your property management ecosystem.
          </p>
        </div>
      </div>

      <!-- Stats Grid -->
      <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <div v-for="stat in stats" :key="stat.name" class="relative overflow-hidden rounded-2xl bg-white px-6 py-8 shadow-premium transition-all hover:scale-[1.02]">
          <dt>
            <div class="absolute rounded-xl bg-premium-beige p-3 text-coral">
              <component :is="stat.icon" class="h-8 w-8" aria-hidden="true" />
            </div>
            <p class="ml-16 truncate text-sm font-medium text-slate-500">{{ stat.name }}</p>
          </dt>
          <dd class="ml-16 flex items-baseline pb-0">
            <p class="text-2xl font-bold text-navy">{{ stat.value }}</p>
            <p :class="[stat.changeType === 'positive' ? 'text-green-600' : 'text-coral', 'ml-2 flex items-baseline text-xs font-semibold']">
              {{ stat.change }}
            </p>
          </dd>
        </div>
      </div>

      <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
        <!-- Notice Board -->
        <div class="lg:col-span-2 rounded-2xl bg-white shadow-premium overflow-hidden border border-slate-100">
          <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-navy text-white">
            <div class="flex items-center space-x-3">
              <BellIcon class="h-6 w-6 text-coral" />
              <h3 class="text-lg font-bold">Operational Notices</h3>
            </div>
            <span class="text-xs font-medium bg-coral/20 px-2 py-1 rounded-full text-coral">Live Updates</span>
          </div>
          
          <div v-if="loading" class="p-12 flex justify-center items-center">
            <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-coral"></div>
          </div>
          
          <div v-else-if="notices.length === 0" class="p-12 text-center">
            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-100">
              <InformationCircleIcon class="h-6 w-6 text-slate-400" />
            </div>
            <h3 class="mt-2 text-sm font-semibold text-navy">No new notices</h3>
            <p class="mt-1 text-sm text-slate-500">Everything is running smoothly today.</p>
          </div>

          <ul v-else role="list" class="divide-y divide-slate-100">
            <li v-for="notice in notices" :key="notice.id" class="px-6 py-5 hover:bg-slate-50 transition-colors">
              <div class="flex items-start space-x-4">
                <div :class="[getNoticeBg(notice.type), 'rounded-lg p-2 mt-1']">
                  <component :is="getNoticeIcon(notice.type)" :class="[getNoticeColor(notice.type), 'h-5 w-5']" />
                </div>
                <div class="min-w-0 flex-1">
                  <div class="flex items-center justify-between">
                    <p class="text-sm font-bold text-navy capitalize">{{ notice.title }}</p>
                    <time class="text-xs text-slate-400">{{ new Date(notice.created_at).toLocaleDateString() }}</time>
                  </div>
                  <p class="mt-1 text-sm text-slate-600 line-clamp-2">
                    {{ notice.content }}
                  </p>
                </div>
              </div>
            </li>
          </ul>
          
          <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 text-center">
             <button @click="$router.push('/dashboard-notices')" class="text-sm font-bold text-coral hover:text-coral/80 transition-colors">
                Manage Notices →
             </button>
          </div>
        </div>

        <!-- Quick Actions / Sidebar Info -->
        <div class="space-y-8">
            <div class="rounded-2xl bg-coral px-6 py-8 shadow-premium text-white relative overflow-hidden group">
                <div class="absolute -right-4 -bottom-4 opacity-10 transform rotate-12 transition-transform group-hover:scale-110">
                    <BoltIcon class="h-32 w-32" />
                </div>
                <h4 class="text-xl font-extrabold">Saudi Vision 2030</h4>
                <p class="mt-2 text-premium-beige opacity-90 text-sm leading-relaxed">
                    Elevating the hospitality standard in line with national tourism objectives.
                </p>
                <button class="mt-6 w-full rounded-xl bg-white/20 backdrop-blur-sm px-4 py-3 text-sm font-bold text-white hover:bg-white/30 transition-all">
                    View Strategic Roadmap
                </button>
            </div>

            <div class="rounded-2xl bg-premium-beige p-6 shadow-premium border border-fbcdab/30">
                <h4 class="text-lg font-bold text-navy flex items-center">
                    <UserGroupIcon class="h-5 w-5 mr-2 text-coral" />
                    Guest Satisfaction
                </h4>
                <div class="mt-6 flex items-center justify-between">
                    <div class="space-y-1">
                        <p class="text-3xl font-black text-navy">4.9</p>
                        <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Average Rating</p>
                    </div>
                    <div class="flex -space-x-2">
                        <img v-for="i in 4" :key="i" :src="`https://i.pravatar.cc/40?img=${i+10}`" class="h-10 w-10 rounded-full border-2 border-white" />
                        <div class="flex h-10 w-10 items-center justify-center rounded-full border-2 border-white bg-slate-100 text-xs font-bold text-slate-500">
                            +12k
                        </div>
                    </div>
                </div>
            </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
.shadow-premium {
  box-shadow: 0 10px 25px -5px rgba(42, 39, 60, 0.05), 0 8px 10px -6px rgba(42, 39, 60, 0.05);
}
</style>
