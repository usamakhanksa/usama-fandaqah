<script setup>
import { useForm, usePage } from '@inertiajs/vue3'; import FoundationLayout from '@/Layouts/FoundationLayout.vue';
const row = usePage().props.row;
const form = useForm({...row});
</script>
<template><FoundationLayout><form @submit.prevent="form.put(`/foundation/customers/${row.id}`)" class="space-y-3 bg-beige p-6 rounded-xl border border-peach"><h2 class="text-xl font-bold">Edit Profile</h2><input v-model="form.first_name_en" class="w-full border border-peach rounded p-2 bg-beige" placeholder="First name"/><input v-model="form.last_name_en" class="w-full border border-peach rounded p-2 bg-beige" placeholder="Last name"/><input v-model="form.phone" class="w-full border border-peach rounded p-2 bg-beige" placeholder="+9665xxxxxxxx"/><input v-model="form.document_number" class="w-full border border-peach rounded p-2 bg-beige" placeholder="Document"/><button class="bg-coral text-beige px-4 py-2 rounded">Save</button></form></FoundationLayout></template>
