<template>
  <form class="grid md:grid-cols-2 gap-4" @submit.prevent="$emit('submit')">
    <div class="space-y-2">
      <label class="text-xs">Company name</label><input v-model="model.company_name" class="input" required>
      <label class="text-xs">Mobile Number</label><CountryCodeInput :countries="countries" v-model:code="mobileCode" v-model:number="mobileNumber"/>
      <label class="text-xs">Responsible person name</label><input v-model="model.responsible_person_name" class="input">
      <label class="text-xs">Mobile number of responsible person</label><CountryCodeInput :countries="countries" v-model:code="respCode" v-model:number="respNumber"/>
      <label class="text-xs">ID Type</label><select v-model="model.id_type" class="input"><option>National ID</option><option>CR</option><option>Passport</option></select>
      <label class="text-xs">ID Number</label><input v-model="model.id_number" class="input">
      <label class="text-xs">Email</label><input v-model="model.email" class="input" type="email">
      <label class="text-xs">Tax number</label><input v-model="model.tax_number" class="input">
      <label class="text-xs">City</label><select v-model="model.city_id" class="input"><option v-for="city in cities" :key="city.id" :value="city.id">{{ city.name }}</option></select>
      <label class="text-xs">Address</label><textarea v-model="model.address" class="input min-h-20"></textarea>
    </div>
    <div><label class="text-xs">Upload Pictures</label><UploadDropzone @uploaded="$emit('uploaded',$event)"/></div>
    <div class="md:col-span-2 flex justify-end gap-2"><button type="button" class="btn-outline" @click="$emit('cancel')">Cancel</button><button type="button" class="btn-outline" @click="$emit('draft')">Save Draft</button><button class="btn-primary" type="submit">Add Company</button></div>
  </form>
</template>
<script setup>
import { computed } from 'vue';
import CountryCodeInput from './CountryCodeInput.vue';
import UploadDropzone from './UploadDropzone.vue';
const props = defineProps({ model: Object, countries: Array, cities: Array });
defineEmits(['submit', 'cancel', 'draft', 'uploaded']);
const mobileCode = computed({ get:()=> props.model.mobile_number?.split(' ')[0] || '+966', set:v=> props.model.mobile_number = `${v} ${props.model.mobile_number?.split(' ')[1] || ''}`.trim() });
const mobileNumber = computed({ get:()=> props.model.mobile_number?.split(' ')[1] || '', set:v=> props.model.mobile_number = `${mobileCode.value} ${v}`.trim() });
const respCode = computed({ get:()=> props.model.responsible_mobile_number?.split(' ')[0] || '+966', set:v=> props.model.responsible_mobile_number = `${v} ${props.model.responsible_mobile_number?.split(' ')[1] || ''}`.trim() });
const respNumber = computed({ get:()=> props.model.responsible_mobile_number?.split(' ')[1] || '', set:v=> props.model.responsible_mobile_number = `${respCode.value} ${v}`.trim() });
</script>
