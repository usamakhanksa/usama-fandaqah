<script setup>
import { Link, usePage } from '@inertiajs/vue3'; import FoundationLayout from '@/Layouts/FoundationLayout.vue';
const { rows } = usePage().props;
</script>
<template><FoundationLayout><div class="bg-beige border border-peach rounded-xl p-6"><div class="flex justify-between"><h2 class="text-2xl font-bold">Unit Categories</h2><Link class="px-4 py-2 bg-coral text-beige rounded" href="/foundation/unit-categories/create">Add</Link></div><table class="w-full mt-4"><thead><tr class="text-left"><th>Name</th><th>Code</th><th>Rate</th><th></th></tr></thead><tbody><tr v-for="r in rows.data" :key="r.id" class="border-t border-peach"><td>{{r.name_en}}</td><td>{{r.code}}</td><td>SAR {{r.base_rate_sar}}</td><td><Link :href="`/foundation/unit-categories/${r.id}`" class="text-coral">View</Link></td></tr></tbody></table></div></FoundationLayout></template>
