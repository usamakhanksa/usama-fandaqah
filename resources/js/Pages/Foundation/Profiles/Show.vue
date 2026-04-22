<script setup>
import { Link, usePage } from '@inertiajs/vue3'; import FoundationLayout from '@/Layouts/FoundationLayout.vue';
const { row } = usePage().props;
</script>
<template><FoundationLayout><div class='bg-beige p-6 rounded-xl border border-peach'><h2 class='text-2xl font-bold'>{{ row.first_name_en }} {{ row.last_name_en }}</h2><p>{{ row.phone }} • {{ row.city }}</p><div class='space-x-3'><Link class='text-coral' :href='`/foundation/customers/${row.id}/edit`'>Edit</Link><Link as='button' method='delete' :href='`/foundation/customers/${row.id}`' class='text-coral'>Delete</Link></div></div></FoundationLayout></template>