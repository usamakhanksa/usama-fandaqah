<template>
  <form class="space-y-3" @submit.prevent="$emit('submit')">
    <div><label class="text-xs">Name</label><input v-model="model.name" class="input" required></div>
    <div><label class="text-xs">Name</label><input v-model="model.display_name" class="input"></div>
    <div><label class="text-xs">Date</label><input v-model="model.date_of_birth" type="date" class="input"></div>
    <div><label class="text-xs">DropDown-civn</label><select v-model="model.drop_down_civn" class="input"><option>Drop Down</option><option>Option 1</option></select></div>
    <div><label class="text-xs">Mobile Number</label><CountryCodeInput :countries="countries" v-model:code="code" v-model:number="number"/></div>
    <div><label class="text-xs">Gender</label><div class="flex gap-4 text-sm"><label><input type="radio" value="male" v-model="model.gender"> Male</label><label><input type="radio" value="female" v-model="model.gender"> Female</label></div></div>
    <div><label class="text-xs">Address</label><textarea v-model="model.address" class="input"></textarea></div>
    <div><label class="text-xs">Read Only</label><input class="input bg-slate-50" v-model="model.read_only_field" readonly></div>
    <div class="flex justify-end"><button class="btn-primary">Save</button></div>
  </form>
</template>
<script setup>
import { computed } from 'vue';
import CountryCodeInput from './CountryCodeInput.vue';
const props = defineProps({ model: Object, countries: Array });
defineEmits(['submit']);
const code = computed({ get:()=> props.model.phone?.split(' ')[0] || '+966', set:v=> props.model.phone = `${v} ${props.model.phone?.split(' ')[1] || ''}`.trim() });
const number = computed({ get:()=> props.model.phone?.split(' ')[1] || '', set:v=> props.model.phone = `${code.value} ${v}`.trim() });
</script>
