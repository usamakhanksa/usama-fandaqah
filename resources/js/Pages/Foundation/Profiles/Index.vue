<script setup>
import { Link, usePage } from '@inertiajs/vue3'; import FoundationLayout from '@/Layouts/FoundationLayout.vue';
const { rows } = usePage().props;
</script>
<template><FoundationLayout><div class="bg-beige border border-peach rounded-xl p-6"><div class="flex justify-between"><h2 class="text-2xl font-bold">Profiles</h2><Link class="px-4 py-2 bg-coral text-beige rounded" href="/foundation/customers/create">Add</Link></div><table class="w-full mt-4"><tbody><tr v-for="r in rows.data" :key="r.id" class="border-t border-peach"><td>{{r.first_name_en}} {{r.last_name_en}}</td><td>{{r.phone}}</td><td><Link class="text-coral" :href="`/foundation/customers/${r.id}`">View</Link></td></tr></tbody></table></div></FoundationLayout></template>
