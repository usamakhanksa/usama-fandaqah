<template>
  <section class="rounded-xl border border-sage/30 bg-white p-8">
    <h1 class="text-2xl font-bold text-navy">{{ title }}</h1>
    <p class="mt-3 text-sage">No records yet. Start by adding your first item.</p>
    <button class="mt-6 rounded-md bg-coral px-4 py-2 text-sm font-semibold text-white" type="button">
      Add new
    </button>
  </section>
</template>

<script setup>
defineProps({
  title: {
    type: String,
    required: true,
  },
});
</script>
