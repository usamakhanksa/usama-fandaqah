<script setup>
import { useForm, Link } from '@inertiajs/vue3'; import FoundationLayout from '@/Layouts/FoundationLayout.vue';
import { usePage } from '@inertiajs/vue3';
const row = usePage().props.row;
const form = useForm({...row});
</script>
<template><FoundationLayout><form @submit.prevent="form.put(`/foundation/unit-categories/${row.id}`)" class="space-y-3 bg-beige p-6 rounded-xl border border-peach"><h2 class="text-xl font-bold">Edit Unit Category</h2><input v-model="form.name_en" class="w-full border border-peach rounded p-2 bg-beige" placeholder="Name EN"/><input v-model="form.name_ar" class="w-full border border-peach rounded p-2 bg-beige" placeholder="Name AR"/><input v-model="form.code" class="w-full border border-peach rounded p-2 bg-beige" placeholder="Code"/><input v-model="form.default_capacity" type="number" class="w-full border border-peach rounded p-2 bg-beige"/><input v-model="form.base_rate_sar" type="number" class="w-full border border-peach rounded p-2 bg-beige"/><button class="bg-coral text-beige px-4 py-2 rounded">Save</button> <Link :href="'/foundation/unit-categories'" class="text-navy">Cancel</Link></form></FoundationLayout></template>
