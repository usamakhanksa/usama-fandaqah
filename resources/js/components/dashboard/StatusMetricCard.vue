<template>
  <div 
    class="relative overflow-hidden custom-rounded p-8 shadow-sm border border-slate-50 transition-all hover:scale-[1.02]"
    :style="{ backgroundColor: color, color: textColor }"
  >
    <div class="space-y-4 relative z-10">
      <h4 class="text-sm font-semibold uppercase tracking-wider opacity-80">{{ title }}</h4>
      <div class="flex items-center gap-3">
        <span class="text-5xl font-black">{{ value }}</span>
        <div class="bg-white bg-opacity-20 rounded-lg p-1">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
        </div>
      </div>
    </div>
    <!-- Decorative Circle -->
    <div class="absolute -right-8 -bottom-8 w-32 h-32 rounded-full bg-white bg-opacity-10 pointer-events-none"></div>
  </div>
</template>

<script setup>
defineProps({
  title: String,
  value: [Number, String],
  color: { type: String, default: '#e95a54' },
  textColor: { type: String, default: '#ffffff' }
});
</script>
