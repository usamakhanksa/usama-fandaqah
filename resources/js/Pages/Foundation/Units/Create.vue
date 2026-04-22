<script setup>
import { useForm, usePage } from '@inertiajs/vue3'; import FoundationLayout from '@/Layouts/FoundationLayout.vue';
const { categories } = usePage().props;
const form = useForm({unit_category_id:'',name:'',number:'',code:'',floor_no:1,max_occupancy:2,current_rate_sar:0,status:'active'});
</script>
<template><FoundationLayout><form @submit.prevent="form.post('/foundation/units')" class="space-y-3 bg-beige p-6 rounded-xl border border-peach"><h2 class="text-xl font-bold">Create Unit</h2><select v-model="form.unit_category_id" class="w-full border border-peach rounded p-2 bg-beige"><option value="">Select Category</option><option v-for="c in categories" :key="c.id" :value="c.id">{{c.name_en}}</option></select><input v-model="form.name" class="w-full border border-peach rounded p-2 bg-beige" placeholder="Name"/><input v-model="form.number" class="w-full border border-peach rounded p-2 bg-beige" placeholder="Number"/><input v-model="form.current_rate_sar" type="number" class="w-full border border-peach rounded p-2 bg-beige"/><button class="bg-coral text-beige px-4 py-2 rounded">Save</button></form></FoundationLayout></template>
