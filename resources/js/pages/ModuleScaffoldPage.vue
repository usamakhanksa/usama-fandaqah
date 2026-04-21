<template>
  <div class="min-h-full bg-[var(--soft-beige)] p-6 text-[var(--dark-navy)]">
    <div class="mx-auto max-w-6xl space-y-6">
      <header class="rounded-2xl bg-[var(--dark-navy)] p-6 text-[var(--soft-beige)]">
        <p class="text-sm uppercase tracking-wider text-[var(--light-peach)]">{{ group }}</p>
        <h1 class="mt-2 text-3xl font-bold">{{ title }}</h1>
        <p class="mt-3 text-sm text-[var(--soft-beige)]/80">Permission slug: <span class="font-semibold">{{ permission }}</span></p>
      </header>

      <EmptyStatePanel
        :title="`No ${title} data yet`"
        description="Create your first record to initialize this module."
        :modal-title="`Add new ${title}`"
        @create="handleCreate"
      />

      <section class="grid gap-4 md:grid-cols-3">
        <div class="rounded-xl border border-[var(--muted-green)] bg-[var(--soft-beige)] p-4">
          <h2 class="text-xs font-semibold uppercase text-[var(--muted-green)]">Table(s)</h2>
          <p class="mt-2 text-sm font-medium">{{ table }}</p>
        </div>
        <div class="rounded-xl border border-[var(--muted-green)] bg-[var(--soft-beige)] p-4">
          <h2 class="text-xs font-semibold uppercase text-[var(--muted-green)]">Required Columns</h2>
          <p class="mt-2 text-sm">{{ columns }}</p>
        </div>
        <div class="rounded-xl border border-[var(--muted-green)] bg-[var(--soft-beige)] p-4">
          <h2 class="text-xs font-semibold uppercase text-[var(--muted-green)]">Sample Seed</h2>
          <p class="mt-2 text-sm">{{ seed }}</p>
        </div>
      </section>
    </div>
  </div>
</template>

<script setup>
import EmptyStatePanel from '../components/shared/EmptyStatePanel.vue';

defineProps({
  title: { type: String, required: true },
  group: { type: String, required: true },
  table: { type: String, required: true },
  columns: { type: String, required: true },
  seed: { type: String, required: true },
  permission: { type: String, required: true },
});

const handleCreate = (payload) => {
  // Placeholder for module-specific create endpoint wiring in each task.
  console.info('Create payload ready for API integration', payload);
};
</script>
