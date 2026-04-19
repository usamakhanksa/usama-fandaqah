<template><div><h3 class="font-bold">The Status Of The Units</h3><apexchart type="area" height="220" :options="opt" :series="[{name:'occupied',data:[50,100,90,160,70,120,150]}]"/></div></template>
<script setup>const opt={xaxis:{categories:['1 Dec','8 Dec','16 Dec','24 Dec','31 Dec']},colors:['#6d28d9']};</script>
