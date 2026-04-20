<template>
  <div class="bg-white p-5 rounded-3xl border border-slate-100 shadow-sm overflow-hidden relative group">
    <div class="flex justify-between items-start mb-4 relative z-10">
      <div>
        <p class="text-[10px] font-black text-[#8f9793] uppercase tracking-widest mb-1">{{ title }}</p>
        <p class="text-2xl font-black text-[#2a273c]">{{ value }}</p>
      </div>
      <div 
        class="w-10 h-10 rounded-xl flex items-center justify-center"
        :style="{ backgroundColor: brandColor + '15' }"
      >
        <span class="text-sm font-bold" :style="{ color: brandColor }">↑</span>
      </div>
    </div>
    
    <div class="absolute inset-x-0 bottom-0 pointer-events-none opacity-40 group-hover:opacity-100 transition-opacity">
      <apexchart 
        type="area" 
        height="80" 
        :options="chartOptions" 
        :series="[{ data: [12, 18, 14, 25, 20, 28, 24] }]"
      />
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  title: String,
  value: [Number, String],
  color: String
});

const brandColor = computed(() => {
  const map = {
    pink: '#e95a54',
    purple: '#2a273c',
    green: '#8f9793',
    orange: '#fbcdab'
  };
  return map[props.color] || '#e95a54';
});

const chartOptions = {
  chart: {
    sparkline: { enabled: true },
    fontFamily: 'Inter, sans-serif'
  },
  stroke: { curve: 'smooth', width: 2.5 },
  colors: [brandColor.value],
  fill: {
    type: 'gradient',
    gradient: {
      shadeIntensity: 1,
      opacityFrom: 0.5,
      opacityTo: 0.1,
    }
  },
  tooltip: { enabled: false }
};
</script>
