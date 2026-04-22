<script setup>
import { Link, usePage } from '@inertiajs/vue3'; import FoundationLayout from '@/Layouts/FoundationLayout.vue';
const { row } = usePage().props;
</script>
<template><FoundationLayout><div class='bg-beige p-6 rounded-xl border border-peach space-y-2'><h2 class='text-2xl font-bold'>{{ row.name_en }}</h2><p>{{ row.name_ar }} • {{ row.code }}</p><p>SAR {{ row.base_rate_sar }}</p><div class='space-x-3'><Link class='text-coral' :href='`/foundation/unit-categories/${row.id}/edit`'>Edit</Link><Link as='button' method='delete' :href='`/foundation/unit-categories/${row.id}`' class='text-coral'>Delete</Link></div></div></FoundationLayout></template>