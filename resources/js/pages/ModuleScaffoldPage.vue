<template>
  <div class="min-h-full bg-[#f2f0eb] p-6">
    <div class="mx-auto max-w-6xl space-y-6">
      <header class="rounded-2xl bg-[#2a273c] p-6 text-[#f2f0eb]">
        <p class="text-sm uppercase tracking-wider text-[#fbcdab]">{{ group }}</p>
        <h1 class="mt-2 text-3xl font-bold">{{ title }}</h1>
        <p class="mt-3 text-sm text-[#f2f0eb]/80">Empty scaffold page prepared. Backend dependencies listed below for migration, seeding, and Nova resource wiring.</p>
      </header>

      <section class="grid gap-4 md:grid-cols-3">
        <div class="rounded-xl border border-[#8f9793] bg-white p-4">
          <h2 class="text-xs font-semibold uppercase text-[#8f9793]">Table(s)</h2>
          <p class="mt-2 text-sm font-medium text-[#2a273c]">{{ table }}</p>
        </div>
        <div class="rounded-xl border border-[#8f9793] bg-white p-4">
          <h2 class="text-xs font-semibold uppercase text-[#8f9793]">Required Columns</h2>
          <p class="mt-2 text-sm text-[#2a273c]">{{ columns }}</p>
        </div>
        <div class="rounded-xl border border-[#8f9793] bg-white p-4">
          <h2 class="text-xs font-semibold uppercase text-[#8f9793]">Sample Seed</h2>
          <p class="mt-2 text-sm text-[#2a273c]">{{ seed }}</p>
        </div>
      </section>

      <section class="rounded-xl border border-dashed border-[#e95a54] bg-[#fbcdab]/30 p-5 text-[#2a273c]">
        <h3 class="font-semibold">Implementation Notes</h3>
        <ul class="mt-2 list-disc space-y-1 pl-5 text-sm">
          <li>Create Laravel migration for missing table/columns.</li>
          <li>Add Nova Resource/Lens/Metric for this route.</li>
          <li>Expose report endpoints when page is analytical.</li>
          <li>Keep UI tokens inside palette: #e95a54, #2a273c, #f2f0eb, #8f9793, #fbcdab.</li>
        </ul>
      </section>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  title: { type: String, required: true },
  group: { type: String, required: true },
  table: { type: String, required: true },
  columns: { type: String, required: true },
  seed: { type: String, required: true },
});
</script>
