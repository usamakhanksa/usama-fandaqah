<script setup>
import { Head, Link } from '@inertiajs/vue3';
import Layout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    creditNote: Object,
});
</script>

<template>
    <Head title="Edit Credit Note" />

    <Layout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Edit Credit Note: {{ creditNote.credit_note_number }}
            </h2>
        </template>

        <div class="py-12 text-center">
            <p class="text-gray-500">Edit functionality is restricted to draft status.</p>
            <Link :href="route('finance.credit-notes.show', creditNote.id)" class="text-indigo-600 hover:underline mt-4 inline-block">
                Return to Show Page
            </Link>
        </div>
    </Layout>
</template>
