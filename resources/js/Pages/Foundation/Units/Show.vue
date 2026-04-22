<script setup>
import { Link, usePage } from '@inertiajs/vue3'; import FoundationLayout from '@/Layouts/FoundationLayout.vue';
const { row } = usePage().props;
</script>
<template><FoundationLayout><div class='bg-beige p-6 rounded-xl border border-peach'><h2 class='text-2xl font-bold'>Unit {{ row.number }}</h2><p>{{ row.name }} • SAR {{ row.current_rate_sar }}</p><div class='space-x-3'><Link class='text-coral' :href='`/foundation/units/${row.id}/edit`'>Edit</Link><Link as='button' method='delete' :href='`/foundation/units/${row.id}`' class='text-coral'>Delete</Link></div></div></FoundationLayout></template>