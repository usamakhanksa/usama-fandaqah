<script setup>
import { useForm, usePage } from '@inertiajs/vue3'; import FoundationLayout from '@/Layouts/FoundationLayout.vue';
const { customers, units } = usePage().props;
const row = usePage().props.row;
const form = useForm({...row});
</script>
<template><FoundationLayout><form @submit.prevent="form.put(`/foundation/reservations/${row.id}`)" class="space-y-3 bg-beige p-6 rounded-xl border border-peach"><h2 class="text-xl font-bold">Edit Reservation</h2><select v-model="form.customer_id" class="w-full border border-peach rounded p-2 bg-beige"><option value="">Select Profile</option><option v-for="c in customers" :key="c.id" :value="c.id">{{c.first_name_en}} {{c.last_name_en}}</option></select><select v-model="form.unit_id" class="w-full border border-peach rounded p-2 bg-beige"><option value="">Select Unit</option><option v-for="u in units" :key="u.id" :value="u.id">{{u.number}} - {{u.name}}</option></select><input type="date" v-model="form.check_in_date" class="w-full border border-peach rounded p-2 bg-beige"/><input type="date" v-model="form.check_out_date" class="w-full border border-peach rounded p-2 bg-beige"/><input type="number" v-model="form.night_rate_sar" class="w-full border border-peach rounded p-2 bg-beige"/><button class="bg-coral text-beige px-4 py-2 rounded">Save</button></form></FoundationLayout></template>
