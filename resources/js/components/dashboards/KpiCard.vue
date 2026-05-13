<template>
  <div class="kpi-card bg-white rounded-xl shadow-sm border border-slate-100 p-5 relative overflow-hidden transition-all duration-300 hover:shadow-md">
    <div class="flex justify-between items-start mb-4">
      <div>
        <p class="text-sm font-medium text-slate-500 mb-1">{{ title }}</p>
        <h3 class="text-2xl font-bold text-slate-800" dir="ltr">{{ formattedValue }}</h3>
      </div>
      <div class="p-3 rounded-lg" :class="iconBgClass">
        <component v-if="icon" :is="icon" class="w-6 h-6" :class="iconTextClass" />
      </div>
    </div>
    
    <div v-if="trend" class="flex items-center text-sm">
      <component :is="trendIcon" class="w-4 h-4 me-1" :class="trendColorClass" />
      <span class="font-medium" :class="trendColorClass" dir="ltr">{{ trend }}%</span>
      <span class="text-slate-400 ms-2 text-xs">{{ trendLabel }}</span>
    </div>

    <!-- Decorative background element -->
    <div class="absolute -end-4 -bottom-4 opacity-5 pointer-events-none">
      <component v-if="icon" :is="icon" class="w-24 h-24" />
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { ArrowUpRight, ArrowDownRight, Minus } from 'lucide-vue-next';

const props = defineProps({
  title: { type: String, required: true },
  value: { type: [Number, String], required: true },
  prefix: { type: String, default: '' },
  suffix: { type: String, default: '' },
  icon: { type: [Object, Function], required: true },
  color: { type: String, default: 'blue' }, // blue, emerald, amber, rose, indigo
  trend: { type: Number, default: null },
  trendLabel: { type: String, default: 'vs last month' }
});

const formattedValue = computed(() => {
  if (typeof props.value === 'number') {
    return `${props.prefix}${new Intl.NumberFormat().format(props.value)}${props.suffix}`;
  }
  return `${props.prefix}${props.value}${props.suffix}`;
});

const iconBgClass = computed(() => {
  const map = {
    blue: 'bg-blue-50',
    emerald: 'bg-emerald-50',
    amber: 'bg-amber-50',
    rose: 'bg-rose-50',
    indigo: 'bg-indigo-50',
    violet: 'bg-violet-50',
    slate: 'bg-slate-50'
  };
  return map[props.color] || 'bg-slate-50';
});

const iconTextClass = computed(() => {
  const map = {
    blue: 'text-blue-500',
    emerald: 'text-emerald-500',
    amber: 'text-amber-500',
    rose: 'text-rose-500',
    indigo: 'text-indigo-500',
    violet: 'text-violet-500',
    slate: 'text-slate-500'
  };
  return map[props.color] || 'text-slate-500';
});

const trendIcon = computed(() => {
  if (!props.trend) return Minus;
  return props.trend > 0 ? ArrowUpRight : ArrowDownRight;
});

const trendColorClass = computed(() => {
  if (!props.trend) return 'text-slate-400';
  return props.trend > 0 ? 'text-emerald-500' : 'text-rose-500';
});
</script>

<style scoped>
.kpi-card:hover {
  transform: translateY(-2px);
}
</style>
