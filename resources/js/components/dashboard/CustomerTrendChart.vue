<template>
  <div class="h-[300px]">
    <apexchart
      type="area"
      height="100%"
      :options="chartOptions"
      :series="series"
    ></apexchart>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const series = [
  {
    name: 'New Customers',
    data: [31, 40, 28, 51, 42, 109, 100]
  },
  {
    name: 'Recurring',
    data: [11, 32, 45, 32, 34, 52, 41]
  }
];

const chartOptions = computed(() => ({
  chart: {
    toolbar: { show: false },
    sparkline: { enabled: false },
    fontFamily: 'Outfit, sans-serif'
  },
  dataLabels: { enabled: false },
  stroke: {
    curve: 'smooth',
    width: 3
  },
  colors: ['#e95a54', '#fbcdab'],
  fill: {
    type: 'gradient',
    gradient: {
      shadeIntensity: 1,
      opacityFrom: 0.45,
      opacityTo: 0.05,
      stops: [20, 100]
    }
  },
  xaxis: {
    categories: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
    axisBorder: { show: false },
    axisTicks: { show: false },
    labels: { style: { colors: '#94a3b8' } }
  },
  yaxis: {
    show: false
  },
  grid: {
    borderColor: '#f1f5f9',
    strokeDashArray: 4,
    padding: { left: 0, right: 0 }
  },
  legend: {
    show: true,
    position: 'top',
    horizontalAlign: 'right'
  },
  tooltip: {
    theme: 'light',
    x: { show: false }
  }
}));
</script>
