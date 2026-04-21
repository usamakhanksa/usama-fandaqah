<template>
  <div class="min-h-full bg-[#f2f0eb] p-6 text-[#2a273c]">
    <div class="mx-auto max-w-6xl space-y-6">
      <header class="rounded-2xl bg-[#2a273c] p-6 text-[#f2f0eb]">
        <p class="text-sm uppercase tracking-wider text-[#fbcdab]">{{ group }}</p>
        <h1 class="mt-2 text-3xl font-bold">{{ title }}</h1>
        <p class="mt-3 text-sm text-[#f2f0eb]/80">Permission slug: <span class="font-semibold">{{ permission }}</span></p>
      </header>

      <section class="rounded-2xl border-2 border-dashed border-[#8f9793] bg-[#f2f0eb] p-10 text-center">
        <p class="text-lg font-semibold">No data yet – Click to add first record</p>
        <p class="mt-2 text-sm text-[#8f9793]">This page is intentionally empty on first load and ready for full CRUD wiring.</p>
        <button class="mt-6 rounded-xl bg-[#e95a54] px-6 py-3 font-semibold text-[#f2f0eb] transition hover:opacity-90">Add new</button>
      </section>

      <section class="grid gap-4 md:grid-cols-3">
        <div class="rounded-xl border border-[#8f9793] bg-[#f2f0eb] p-4">
          <h2 class="text-xs font-semibold uppercase text-[#8f9793]">Table(s)</h2>
          <p class="mt-2 text-sm font-medium">{{ table }}</p>
        </div>
        <div class="rounded-xl border border-[#8f9793] bg-[#f2f0eb] p-4">
          <h2 class="text-xs font-semibold uppercase text-[#8f9793]">Required Columns</h2>
          <p class="mt-2 text-sm">{{ columns }}</p>
        </div>
        <div class="rounded-xl border border-[#8f9793] bg-[#f2f0eb] p-4">
          <h2 class="text-xs font-semibold uppercase text-[#8f9793]">Sample Seed</h2>
          <p class="mt-2 text-sm">{{ seed }}</p>
        </div>
      </section>
    </div>
  </div>
</template>

<script setup>
defineProps({
  title: { type: String, required: true },
  group: { type: String, required: true },
  table: { type: String, required: true },
  columns: { type: String, required: true },
  seed: { type: String, required: true },
  permission: { type: String, required: true },
});
</script>
