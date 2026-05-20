<template>
  <div class="authenticated-layout">
    <header v-if="$slots.header" class="bg-white shadow-sm mb-6">
      <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
        <slot name="header" />
      </div>
    </header>
    <main>
      <slot />
    </main>
  </div>
</template>

<script setup>
// This is a shim for the missing AuthenticatedLayout.vue
</script>
