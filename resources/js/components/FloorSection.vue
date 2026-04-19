<template>
<div class="border rounded-xl p-3 space-y-3">
  <div class="flex justify-between"><div class="font-semibold">{{ floor.name }} <span class="text-xs text-slate-500">| {{ floor.count }}</span></div><button class="text-xs">See All</button></div>
  <div class="grid md:grid-cols-5 gap-3"><UnitCard v-for="u in floor.units" :key="u.id" :unit="u" @action="emit('action',$event)"/></div>
  <UnitsLegend :items="floor.legend"/>
</div>
</template>
<script setup>import UnitCard from './UnitCard.vue'; import UnitsLegend from './UnitsLegend.vue'; const props=defineProps({floor:Object}); const emit=defineEmits(['action']);</script>
