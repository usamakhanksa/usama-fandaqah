<template>
  <div class="h-[250px] relative flex items-center justify-center">
    <apexchart
      type="donut"
      height="100%"
      :options="chartOptions"
      :series="series"
    ></apexchart>
    
    <div class="absolute flex flex-col items-center">
      <span class="text-3xl font-black text-[#2a273c]">82%</span>
      <span class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">Occupied</span>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const series = [44, 15, 13];

const chartOptions = computed(() => ({
  chart: {
    fontFamily: 'Outfit, sans-serif'
  },
  labels: ['Available', 'Booked', 'Cleaning'],
  colors: ['#e95a54', '#fbcdab', '#2a273c'],
  stroke: { show: false },
  dataLabels: { enabled: false },
  legend: {
    position: 'bottom',
    horizontalAlign: 'center',
    markers: { radius: 12 }
  },
  plotOptions: {
    pie: {
      donut: {
        size: '80%',
        labels: { show: false }
      }
    }
  }
}));
</script>
