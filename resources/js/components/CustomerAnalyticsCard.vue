<template>
  <div class="p-6 h-full flex flex-col justify-between">
    <div>
      <div class="flex justify-between items-center mb-8">
        <div>
          <h3 class="text-xl font-black text-[#2a273c]">Segment Analytics</h3>
          <p class="text-xs font-bold text-[#8f9793]">Customer Loyalty Distribution</p>
        </div>
        <div class="w-10 h-10 bg-[#f2f0eb] rounded-xl flex items-center justify-center">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#2a273c" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        </div>
      </div>
      
      <div class="flex items-center justify-center py-4">
        <apexchart 
          type="donut" 
          height="280" 
          :options="options" 
          :series="series"
        />
      </div>
    </div>

    <div class="space-y-3 mt-4">
      <div v-for="(val, i) in series" :key="i" class="flex items-center gap-3">
        <div class="w-2.5 h-2.5 rounded-full" :style="{ backgroundColor: options.colors[i] }"></div>
        <span class="text-xs font-bold text-slate-500 flex-1">{{ options.labels[i] }}</span>
        <span class="text-xs font-black text-[#2a273c]">{{ val }}%</span>
      </div>
    </div>
  </div>
</template>

<script setup>
const series = [65, 25, 10]; // Returning, New, One-time

const options = {
  chart: {
    fontFamily: 'Inter, sans-serif',
  },
  labels: ['Returning Guests', 'New Bookings', 'Enterprise Leads'],
  colors: ['#e95a54', '#fbcdab', '#2a273c'],
  stroke: { width: 0 },
  dataLabels: { enabled: false },
  plotOptions: {
    pie: {
      donut: {
        size: '75%',
        labels: {
          show: true,
          name: { show: true, fontSize: '12px', fontWeight: 700, color: '#8f9793' },
          value: { show: true, fontSize: '24px', fontWeight: 900, color: '#2a273c' },
          total: {
            show: true,
            label: 'Total Retention',
            fontSize: '11px',
            fontWeight: 800,
            color: '#8f9793',
            formatter: () => '94%'
          }
        }
      }
    }
  },
  legend: { show: false },
  tooltip: {
    theme: 'dark',
    y: { formatter: (v) => `${v}%` }
  }
};
</script>
