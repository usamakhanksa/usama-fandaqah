<template><div class="card"><h3>{{title}}</h3><div class="text-3xl font-bold">{{value}}</div><apexchart type="line" height="80" :options="opt" :series="[{data:[10,22,18,35,30,46]}]"/></div></template>
<script setup>const props=defineProps({title:String,value:[Number,String],color:String}); const opt={chart:{sparkline:{enabled:true}},stroke:{curve:'smooth'},colors:['#7c3aed']};</script>
