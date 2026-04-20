<template>
  <div class="p-6">
    <div class="flex justify-between items-center mb-6">
      <h3 class="text-lg font-black text-[#2a273c]">Unit Performance</h3>
      <div class="px-3 py-1 bg-[#f2f0eb] rounded-lg text-[10px] font-bold text-[#8f9793] uppercase">Real-time</div>
    </div>
    
    <div class="relative">
      <apexchart 
        type="area" 
        height="220" 
        :options="options" 
        :series="series"
      />
    </div>

    <div class="grid grid-cols-2 gap-4 mt-4">
      <div class="flex items-center gap-2">
        <div class="w-2 h-2 rounded-full bg-[#e95a54]"></div>
        <span class="text-xs font-bold text-slate-500">Utilization Rate</span>
        <span class="text-xs font-black text-[#2a273c] ml-auto">84%</span>
      </div>
      <div class="flex items-center gap-2">
        <div class="w-2 h-2 rounded-full bg-[#fbcdab]"></div>
        <span class="text-xs font-bold text-slate-500">Cleaning Index</span>
        <span class="text-xs font-black text-[#2a273c] ml-auto">92%</span>
      </div>
    </div>
  </div>
</template>

<script setup>
const series = [
  {
    name: 'Occupancy',
    data: [31, 40, 28, 51, 42, 109, 100]
  },
  {
    name: 'Available',
    data: [11, 32, 45, 32, 34, 52, 41]
  }
];

const options = {
  chart: {
    type: 'area',
    toolbar: { show: false },
    sparkline: { enabled: false },
    fontFamily: 'Inter, sans-serif'
  },
  colors: ['#e95a54', '#fbcdab'],
  dataLabels: { enabled: false },
  stroke: {
    curve: 'smooth',
    width: 3,
  },
  fill: {
    type: 'gradient',
    gradient: {
      shadeIntensity: 1,
      opacityFrom: 0.45,
      opacityTo: 0.05,
      stops: [20, 100, 100, 100]
    }
  },
  grid: {
    borderColor: '#f1f5f9',
    strokeDashArray: 4,
    padding: { left: 0, right: 0 }
  },
  xaxis: {
    categories: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
    axisBorder: { show: false },
    axisTicks: { show: false },
    labels: {
      style: { colors: '#94a3b8', fontSize: '10px', fontWeight: 600 }
    }
  },
  yaxis: {
    show: false
  },
  tooltip: {
    theme: 'light',
    x: { show: false }
  },
  legend: { show: false }
};
</script>
